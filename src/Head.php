<?php

namespace A17\LaravelAutoHeadTags;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use A17\LaravelAutoHeadTags\Behaviors\Macro;
use A17\LaravelAutoHeadTags\Behaviors\Config;

class Head
{
    use Macro, Config;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $data;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $config;

    /**
     * @var boolean
     */
    protected $firstLineRendered = false;

    /**
     * Head constructor.
     *
     * @param array|\Illuminate\Support\Collection $data
     */
    public function __construct($data)
    {
        $this->setData($data);

        $this->setConfig(config()->all());
    }

    /**
     * @param \Illuminate\Support\Collection $properties
     * @return \Illuminate\Support\Collection
     */
    protected function fillUpNonArrayProperties(Collection $properties)
    {
        $traversable = $properties->first(function ($property) {
            return is_traversable($property);
        });

        return $properties->map(function ($value) use ($traversable) {
            if (is_traversable($value)) {
                return $value;
            }

            return $traversable->map(function () use ($value) {
                return $value;
            });
        });
    }

    /**
     * @param \Illuminate\Support\Collection $properties
     * @return \Illuminate\Support\Collection
     */
    protected function generatePropertiesArray(Collection $properties)
    {
        if (!$this->propertiesContainsLoops($properties)) {
            return collect([$properties]);
        }

        $properties = $this->fillUpNonArrayProperties(
            $properties->map(function ($property) {
                return $this->explodePropertyLoop($property);
            })
        );

        $result = [];

        $properties->each(function (Collection $values, $propertyName) use (
            &$result
        ) {
            $values->each(function ($value, $key) use (
                $propertyName,
                &$result
            ) {
                Arr::set($result, "{$key}.{$propertyName}", $value);
            });
        });

        return collect($result);
    }

    /**
     * @param string $tagName
     * @param array|\Illuminate\Support\Collection $tags
     * @return \Illuminate\Support\Collection
     */
    protected function generateTagsWithProperties($tagName, $tags): Collection
    {
        return collect($tags)
            ->map(function ($properties) use ($tagName) {
                return $this->generatePropertiesArray($properties)->map(
                    function ($properties) use ($tagName) {
                        return $this->generateTag($tagName, $properties);
                    }
                );
            })
            ->filter();
    }

    /**
     * @param string $tagName
     * @param array|string|\Illuminate\Support\Collection $tags
     * @return \Illuminate\Support\Collection
     */
    protected function generateTagsWithoutProperties(
        $tagName,
        $tags
    ): Collection {
        return collect(
            $this->generateTag(
                $tagName,
                $tags,
                'tag.without_properties_format',
                'tag.value_only_format'
            )
        )->filter();
    }

    /**
     * @return string
     */
    protected function getFallbackDelimiter()
    {
        return $this->config('delimiters.fallback');
    }

    /**
     * @return string
     */
    protected function getIndent()
    {
        if (!$this->firstLineRendered) {
            $this->firstLineRendered = true;

            return '';
        }

        return str_repeat(' ', $this->config('tag.indent.spaces')) .
            str_repeat("\t", $this->config('tag.indent.tabs'));
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->config('tags')
            ->map(function ($properties, $tag) {
                return $this->generateTags($tag, $properties);
            })
            ->flatten()
            ->filter()
            ->implode($this->config('tag.line_break'));
    }

    /**
     * @param string $tagName
     * @param string|array|\Illuminate\Support\Collection $tags
     * @return \Illuminate\Support\Collection
     */
    public function generateTags($tagName, $tags)
    {
        return is_string($tags)
            ? $this->generateTagsWithoutProperties($tagName, $tags)
            : $this->generateTagsWithProperties($tagName, $tags);
    }

    /**
     * @param string $tagName
     * @param string|array|\Illuminate\Support\Collection $value
     * @param string $tagFormat
     * @param string $propertiesFormat
     * @return string|null
     */
    public function generateTag(
        $tagName,
        $value,
        $tagFormat = 'tag.with_properties_format',
        $propertiesFormat = 'tag.property_format'
    ) {
        $value = $this->generateProperties(
            $tagName,
            $value,
            $propertiesFormat
        )->implode(' ');

        if (filled($value)) {
            return $this->getIndent() .
                str_replace(
                    ['{tagName}', '{value}'],
                    [$tagName, $value],

                    $this->config($tagFormat)
                );
        }

        return null;
    }

    /**
     * @param string $tagName
     * @param string|array|\Illuminate\Support\Collection $properties
     * @param string $format
     * @return \Illuminate\Support\Collection
     */
    public function generateProperties(
        $tagName,
        $properties,
        $format = 'tag.property_format'
    ) {
        $properties = collect($properties)->map(function ($value, $key) use (
            $format
        ) {
            $value = $this->generateValue($value);

            return [
                'key' => $key,
                'value' => $value,
                'rendered' => filled($value)
                    ? str_replace(
                        ['{propertyName}', '{value}'],
                        [$key, $value],
                        $this->config($format)
                    )
                    : null
            ];
        });

        if (
            $this->metaIsBlank($properties, $tagName) ||
            $this->linkIsBlank($properties, $tagName)
        ) {
            return collect();
        }

        return $properties->map(function ($property) {
            return $property['rendered'];
        });
    }

    /**
     * @param \Illuminate\Support\Collection $properties
     * @param string $tagName
     * @return bool
     */
    protected function linkIsBlank($properties, $tagName): bool
    {
        return $tagName === 'link' &&
            isset($properties['href']) &&
            blank($properties['href']['value']);
    }

    /**
     * @param \Illuminate\Support\Collection $properties
     * @param string $tagName
     * @return bool
     */
    protected function metaIsBlank($properties, $tagName): bool
    {
        return $tagName === 'meta' &&
            isset($properties['content']) &&
            blank($properties['content']['value']);
    }

    /**
     * @param string $value
     * @return array|mixed|null
     */
    public function generateValue($value)
    {
        return collect(explode($this->getFallbackDelimiter(), $value))
            ->map(function ($value) {
                return $this->makeValue($value);
            })
            ->first(function ($value) {
                return filled($value);
            });
    }

    /**
     * @param array $config
     * @return Head
     */
    public function setConfig(array $config): Head
    {
        $this->config = to_collection_recursive($config);

        return $this;
    }

    /**
     * @param array|\Illuminate\Support\Collection $data
     * @return Head
     */
    public function setData($data): Head
    {
        $this->data = collect($data);

        return $this;
    }

    /**
     * @param string $value
     * @return array
     */
    public function splitDefault($value)
    {
        $delimiter = $this->getFallbackDelimiter();

        if (!Str::contains($value, $delimiter)) {
            return [$value, null];
        }

        return [
            Str::before($value, $delimiter),
            Str::after($value, $delimiter)
        ];
    }

    /**
     * @param string $value
     * @return array|mixed|null
     */
    public function makeValue($value)
    {
        if (blank($macro = $this->extractMacro($value))) {
            return $value;
        }

        return $this->makeValueFromMacro($macro);
    }
}

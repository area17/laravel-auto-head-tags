<?php

namespace A17\TwillHead;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Head
{
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
    private $firstLineRendered = false;

    /**
     * Head constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->setData($data);

        $this->setConfig(config()->all());
    }

    /**
     * @param $property
     * @return bool
     */
    private function containsLoop($property)
    {
        return filled($this->extractVarFromLoopMacro($property));
    }

    /**
     * @param $property
     * @return bool
     */
    private function containsMacro($property)
    {
        return filled($this->extractMacro($property));
    }

    /**
     * @param $property
     * @return \Illuminate\Support\Collection
     */
    private function explodePropertyLoop($property)
    {
        if (!$this->containsLoop($property)) {
            return $property;
        }

        $key = $this->extractVarFromLoopMacro($property);

        $macro = $this->extractMacroFromLoopMacro($property);

        if (blank($macro)) {
            return $property;
        }

        return collect($this->makeValueFromMacro($macro))
            ->map(function ($value, $key) {
                return compact('value', 'key');
            })
            ->map(function ($values) use ($key) {
                return $values[$key];
            });
    }

    /**
     * @param $property
     * @return string|null
     */
    private function extractAndCleanMacro($property)
    {
        if (blank($macro = $this->extractMacro($property))) {
            return null;
        }

        return $this->removeConfigKeyFromMacro($macro);
    }

    /**
     * @param $property
     * @return string|null
     */
    private function extractMacroFromLoopMacro($property)
    {
        $macro = $this->extractAndCleanMacro($property);

        if (!Str::contains($macro, $this->getLoopDelimiter())) {
            return null;
        }

        return Str::beforeLast($macro, $this->getLoopDelimiter());
    }

    /**
     * @param $property
     * @return string|null
     */
    private function extractVarFromLoopMacro($property)
    {
        $macro = $this->extractAndCleanMacro($property);

        if (!Str::contains($macro, $this->getLoopDelimiter())) {
            return null;
        }

        return Str::afterLast($macro, $this->getLoopDelimiter());
    }

    /**
     * @param $value
     * @return string|null
     */
    private function extractMacro($value)
    {
        preg_match_all($this->getMacroRegexParser(), $value, $matches);

        if (blank($matches[1][0] ?? null)) {
            return null;
        }

        return $matches[1][0];
    }

    /**
     * @param $properties
     * @return \Illuminate\Support\Collection
     */
    private function fillUpNonArrayProperties(Collection $properties)
    {
        $traversable = $properties->first(
            fn($property) => is_traversable($property),
        );

        return $properties->map(function ($value) use ($traversable) {
            if (is_traversable($value)) {
                return $value;
            }

            return $traversable->map(fn($_) => $value);
        });
    }

    /**
     * @param $properties
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
            }),
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

    private function generateTagWithValue(string $tagName, string $value)
    {
    }

    /**
     * @param $tagName
     * @param $tags
     * @return \Illuminate\Support\Collection
     */
    private function generateTagsWithProperties(
        $tagName,
        $tags
    ): \Illuminate\Support\Collection {
        return collect($tags)
            ->map(function ($properties) use ($tagName) {
                return $this->generatePropertiesArray($properties)->map(
                    fn($properties) => $this->generateTag(
                        $tagName,
                        $properties,
                    ),
                );
            })
            ->filter();
    }

    /**
     * @param $tagName
     * @param $tags
     * @return \Illuminate\Support\Collection
     */
    private function generateTagsWithoutProperties($tagName, $tags): Collection
    {
        return collect(
            $this->generateTag(
                $tagName,
                $tags,
                'tag.without_properties_format',
                'tag.value_only_format',
            ),
        )->filter();
    }

    /**
     * @return mixed
     */
    private function getConfigKey()
    {
        return $this->config('config.key');
    }

    /**
     * @return string
     */
    private function getFallbackDelimiter()
    {
        return $this->config('delimiters.fallback');
    }

    /**
     * @return string
     */
    private function getIndent()
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
    private function getLoopDelimiter()
    {
        return $this->config('delimiters.loop');
    }

    /**
     * @return string
     */
    private function getMacroRegexParser()
    {
        return $this->config('macro.regex_parser');
    }

    /**
     * @param $macro
     * @return array|mixed|null
     */
    private function makeValueFromMacro($macro)
    {
        return Str::contains($macro, '.')
            ? $this->makeValueFromMacroArray($macro)
            : $this->makeValueFromMacroVariable($macro);
    }

    /**
     * @param $macro
     * @return mixed|null
     */
    private function makeValueFromMacroVariable($macro)
    {
        return $this->data[$macro] ?? null;
    }

    /**
     * @param $properties
     * @return mixed
     */
    private function propertiesContainsLoops(Collection $properties)
    {
        return $properties->reduce(function ($keep, $property) {
            return $keep ||
                ($this->containsMacro($property) &&
                    $this->containsLoop($property));
        }, false);
    }

    /**
     * @param $macro
     * @return string
     */
    private function removeConfigKeyFromMacro($macro): string
    {
        $macro = Str::startsWith($macro, $this->getConfigKey())
            ? Str::after($macro, $this->getConfigKey())
            : $macro;

        return $macro;
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
     * @param $tagName
     * @param $tags
     * @return \Illuminate\Support\Collection
     */
    public function generateTags($tagName, $tags)
    {
        return is_string($tags)
            ? $this->generateTagsWithoutProperties($tagName, $tags)
            : $this->generateTagsWithProperties($tagName, $tags);
    }

    /**
     * @param $tagName
     * @param $value
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
            $propertiesFormat,
        )->implode(' ');

        if (filled($value)) {
            return $this->getIndent() .
                str_replace(
                    ['{tagName}', '{value}'],
                    [$tagName, $value],

                    $this->config($tagFormat),
                );
        }

        return null;
    }

    /**
     * @param $tagName
     * @param $properties
     * @param string $format
     * @return \Illuminate\Support\Collection
     */
    public function generateProperties(
        $tagName,
        $properties,
        $format = 'tag.property_format'
    ) {
        $properties = collect($properties)->map(function ($value, $key) use ($format) {
            $value = $this->generateValue($value);

            return [
                'key' => $key,
                'value' => $value,
                'rendered' => filled($value)
                    ? str_replace(
                        ['{propertyName}', '{value}'],
                        [$key, $value],
                        $this->config($format),
                    )
                    : null,
            ];
        });

        if (
            $this->metaIsBlank($properties, $tagName) ||
            $this->linkIsBlank($properties, $tagName)
        ) {
            return collect();
        }

        return $properties->map(fn($property) => $property['rendered']);
    }

    /**
     * @param $properties
     * @param $tagName
     * @return bool
     */
    protected function linkIsBlank($properties, $tagName): bool
    {
        return $tagName === 'link' &&
            isset($properties['href']) &&
            blank($properties['href']['value']);
    }

    /**
     * @param $properties
     * @param $tagName
     * @return bool
     */
    protected function metaIsBlank($properties, $tagName): bool
    {
        return $tagName === 'meta' &&
            isset($properties['content']) &&
            blank($properties['content']['value']);
    }

    /**
     * @param $value
     * @return array|mixed|null
     */
    public function generateValue($value)
    {
        return collect(explode($this->getFallbackDelimiter(), $value))
            ->map(fn($value) => $this->makeValue($value))
            ->first(fn($value) => filled($value));
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
     * @param array $data
     * @return Head
     */
    public function setData(array $data): Head
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $value
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
            Str::after($value, $delimiter),
        ];
    }

    /**
     * @param $value
     * @return array|mixed|null
     */
    public function makeValue($value)
    {
        if (blank($macro = $this->extractMacro($value))) {
            return $value;
        }

        return $this->makeValueFromMacro($macro);
    }

    /**
     * @param $variable
     * @return array|mixed
     */
    public function makeValueFromMacroArray($variable)
    {
        $keys = Str::after($variable, '.');

        $variable = Str::before($variable, '.');

        $value =
            $variable === $this->getConfigKey()
                ? $this->config
                : $this->data[$variable] ?? collect();

        if (is_traversable($value)) {
            return Arr::get(to_array($value), $keys);
        }

        return $value;
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function config($key = null)
    {
        if (blank($key)) {
            return $this->config['twill-head'];
        }

        return Arr::get($this->config, "twill-head.{$key}");
    }
}

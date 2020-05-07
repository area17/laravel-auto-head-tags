<?php

namespace A17\TwillHead;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class Head
{
    const DELIMITER = '|';

    /**
     * @var array
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function render()
    {
        return collect(config('twill-head'))
            ->map(function ($properties, $tag) {
                return $this->generateTags($tag, $properties);
            })
            ->flatten()
            ->implode("\n");
    }

    public function generateTags($tagName, $tags)
    {
        return collect($tags)
            ->map(function ($properties) use ($tagName) {
                $properties = $this->generateProperties(
                    $tagName,
                    $properties,
                )->implode(' ');

                if (filled($properties)) {
                    // https://www.w3.org/TR/xhtml1/#guidelines
                    return "<{$tagName} {$properties} />";
                }

                return null;
            })
            ->filter();
    }

    public function generateProperties($tagName, $properties)
    {
        $properties = collect($properties)->map(function ($value, $key) {
            $value = $this->generateValue($value);

            return [
                'key' => $key,
                'value' => $value,
                'rendered' => filled($value)
                    ? sprintf('%s="%s"', $key, $value)
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

    public function generateValue($value)
    {
        [$value, $default] = $this->splitDefault($value);

        return $this->makeValue($value) ?? $default;
    }

    public function splitDefault($value)
    {
        if (!Str::contains($value, self::DELIMITER)) {
            return [$value, null];
        }

        return [Str::before($value, '|||'), Str::after($value, '|||')];
    }

    public function makeValue($value)
    {
        preg_match_all('/^{(.*)}$/', $value, $matches);

        if (blank($matches[1][0] ?? null)) {
            return $value;
        }

        $variable = $matches[1][0];

        if (Str::contains($variable, '.')) {
            return $this->makeValueFromArray($variable);
        }

        return $this->data[$variable] ?? null;
    }

    public function makeValueFromArray($variable)
    {
        $keys = Str::after($variable, '.');

        $variable = Str::before($variable, '.');

        $value = $this->data[$variable];

        if (is_array($value)) {
            return Arr::get($value, $keys);
        }

        return $value;
    }
}

<?php

namespace A17\LaravelAutoHeadTags\Behaviors;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait Macro
{
    abstract public function config($key = null);

    abstract protected function getConfigKey();

    /**
     * @param string $property
     * @return bool
     */
    protected function containsMacro($property)
    {
        return filled($this->extractMacro($property));
    }

    /**
     * @param string $property
     * @return string|null
     */
    protected function extractAndCleanMacro($property)
    {
        if (blank($macro = $this->extractMacro($property))) {
            return null;
        }

        return $this->removeConfigKeyFromMacro($macro);
    }

    /**
     * @param string $property
     * @return string|null
     */
    protected function extractMacroFromLoopMacro($property)
    {
        $macro = $this->extractAndCleanMacro($property);

        if (!Str::contains($macro, $this->getLoopDelimiter())) {
            return null;
        }

        return Str::beforeLast($macro, $this->getLoopDelimiter());
    }

    /**
     * @param string $property
     * @return string|null
     */
    protected function extractVarFromLoopMacro($property)
    {
        $macro = $this->extractAndCleanMacro($property);

        if (!Str::contains($macro, $this->getLoopDelimiter())) {
            return null;
        }

        return Str::afterLast($macro, $this->getLoopDelimiter());
    }

    /**
     * @param string $value
     * @return string|null
     */
    protected function extractMacro($value)
    {
        preg_match_all($this->getMacroRegexParser(), $value, $matches);

        if (blank($matches[1][0] ?? null)) {
            return null;
        }

        return $matches[1][0];
    }

    /**
     * @return string
     */
    protected function getMacroRegexParser()
    {
        return $this->config('macro.regex_parser');
    }

    /**
     * @param string $macro
     * @return array|mixed|null
     */
    protected function makeValueFromMacro($macro)
    {
        return Str::contains($macro, '.')
            ? $this->makeValueFromMacroArray($macro)
            : $this->makeValueFromMacroVariable($macro);
    }

    /**
     * @param string $macro
     * @return mixed|null
     */
    protected function makeValueFromMacroVariable($macro)
    {
        return $this->data[$macro] ?? null;
    }

    /**
     * @param string $macro
     * @return string
     */
    protected function removeConfigKeyFromMacro($macro): string
    {
        $macro = Str::startsWith($macro, $this->getConfigKey())
            ? Str::after($macro, $this->getConfigKey())
            : $macro;

        return $macro;
    }

    /**
     * @param string $variable
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
     * @param string $property
     * @return bool
     */
    protected function containsLoop($property)
    {
        return filled($this->extractVarFromLoopMacro($property));
    }

    /**
     * @param string $property
     * @return string|\Illuminate\Support\Collection
     */
    protected function explodePropertyLoop($property)
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
     * @return string
     */
    protected function getLoopDelimiter()
    {
        return $this->config('delimiters.loop');
    }

    /**
     * @param \Illuminate\Support\Collection $properties
     * @return mixed
     */
    protected function propertiesContainsLoops(Collection $properties)
    {
        return $properties->reduce(function ($keep, $property) {
            return $keep ||
                ($this->containsMacro($property) &&
                    $this->containsLoop($property));
        }, false);
    }
}

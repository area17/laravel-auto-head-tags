<?php

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

if (!function_exists('to_collection_recursive')) {
    /**
     * @param array $array
     * @return \Illuminate\Support\Collection
     */
    function to_collection_recursive($array)
    {
        return collect($array)->map(function ($item) {
            if (is_array($item)) {
                return collect(to_collection_recursive($item));
            }

            return $item;
        });
    }
}

if (!function_exists('is_traversable')) {
    /**
     * @param mixed $subject
     * @return bool
     */
    function is_traversable($subject)
    {
        return is_array($subject) || $subject instanceof ArrayAccess;
    }
}

if (!function_exists('to_array')) {
    function to_array($collection)
    {
        if ($collection instanceof stdClass) {
            $collection = collect(json_decode(json_encode($collection), true));
        }

        if ($collection instanceof Model) {
            $collection = $collection->toArray();
        }

        if (!is_traversable($collection)) {
            return $collection;
        }

        if (is_array($collection)) {
            $collection = collect($collection);
        } else {
            $collection = collect($collection->toArray());
        }

        $collection = $collection->map(function ($item) {
            if (is_traversable($item)) {
                return to_array($item);
            }

            return $item;
        });

        if (keys_are_all_numeric($collection)) {
            $collection = $collection->values();
        }

        return $collection->toArray();
    }
}

if (!function_exists('keys_are_all_numeric')) {
    function keys_are_all_numeric($array)
    {
        return collect($array)
            ->keys()
            ->reduce(function ($keep, $key) {
                return $keep && is_integer($key);
            }, true);
    }
}

<?php

use Illuminate\Support\Str;

if (!function_exists('to_collection_recursive')) {
    /**
     * @param $array
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
     * @param $subject
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
        if (!is_traversable($collection)) {
            return $collection;
        }

        if (is_array($collection)) {
            $collection = collect($collection);
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

if (!function_exists('image_mime_type')) {
    function image_mime_type($image)
    {
        return image_type_to_mime_type(image_type_from_url($image));
    }
}

if (!function_exists('video_mime_type')) {
    function video_mime_type($video)
    {
        return video_type_to_mime_type(video_type_from_url($video));
    }
}

if (!function_exists('image_type_from_url')) {
    function image_type_from_url($url)
    {
        $extension = Str::lower(Str::afterLast($url, '.'));

        if (blank($extension)) {
            return null;
        }

        switch ($extension) {
            case 'gif':
                return IMAGETYPE_GIF;
            case 'jpeg':
            case 'jpg':
                return IMAGETYPE_JPEG;
            case 'png':
                return IMAGETYPE_PNG;
            case 'swd':
                return IMAGETYPE_SWF;
            case 'psd':
                return IMAGETYPE_PSD;
            case 'bmp':
                return IMAGETYPE_BMP;
            case 'tiff':
                return IMAGETYPE_TIFF_II;
            case 'tiff-m':
                return IMAGETYPE_TIFF_MM;
            case 'jpc':
                return IMAGETYPE_JPC;
            case 'jp2':
                return IMAGETYPE_JP2;
            case 'jpx':
                return IMAGETYPE_JPX;
            case 'jb2':
                return IMAGETYPE_JB2;
            case 'swc':
                return IMAGETYPE_SWC;
            case 'iff':
                return IMAGETYPE_IFF;
            case 'wbmp':
                return IMAGETYPE_WBMP;
            case 'xbm':
                return IMAGETYPE_XBM;
            case 'ico':
                return IMAGETYPE_ICO;
            case 'webp':
                return IMAGETYPE_WEBP;
        }
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

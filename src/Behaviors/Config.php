<?php

namespace A17\LaravelAutoHeadTags\Behaviors;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait Config
{
    /**
     * @param null|string $key
     * @return mixed
     */
    public function config($key = null)
    {
        if (blank($key)) {
            return $this->config['laravel-auto-head-tags'];
        }

        return Arr::get($this->config, "laravel-auto-head-tags.{$key}");
    }

    /**
     * @return mixed
     */
    protected function getConfigKey()
    {
        return $this->config('config.key');
    }
}

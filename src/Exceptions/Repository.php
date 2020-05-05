<?php

namespace A17\TwillMetas\Exceptions;

class Repository extends \Exception
{
    /**
     * @param $method
     * @throws \A17\TwillMetas\Exceptions\Transformer
     */
    public static function templateNameNotDefined($method)
    {
        throw new self(
            'The property $templateName must be defined on your repository.',
        );
    }
}

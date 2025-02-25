<?php

namespace Helpers;

use Flash;

class Old extends Flash
{
    public function __construct()
    {
        parent::__construct('form');
    }

    public function set($datas)
    {
        foreach ($datas as $key => $value) {
            $this->setKey($key, $value);
        }
    }

    public function get($key, $default = null)
    {
        return $this->getKey($key) ?: $default;
    }
}

<?php

namespace Helpers;

use Flash;

class Old extends Flash
{
    public $fromPrevious = [];

    public function __construct()
    {
        parent::__construct('form');

        if (isset($this->key) && ! empty($this->key)) {
            $this->fromPrevious = $this->key;
        }

        $this->key = [];
    }

    public function set($datas)
    {
        foreach ($datas as $key => $value) {
            $this->setKey($key, $value);
        }
    }

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->fromPrevious) ? $this->fromPrevious[$key] : $default;
    }
}

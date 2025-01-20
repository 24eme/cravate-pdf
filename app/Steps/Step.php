<?php

namespace Steps;

class Step
{
    public $name;
    public $active = false;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function activate()
    {
        $this->active = true;
    }

    public function desactivate()
    {
        $this->active = false;
    }

    public function isActive()
    {
        return $this->active === true;
    }

    public function get()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }
}

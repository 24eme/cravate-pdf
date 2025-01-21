<?php

namespace Steps;

class Step
{
    public $name;
    public $link;
    public $active = false;

    public function __construct($name, $link = null)
    {
        $this->name = $name;
        $this->link = $link;
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

    public function link()
    {
        return $this->link;
    }

    public function __toString()
    {
        return $this->name;
    }
}

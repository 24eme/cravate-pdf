<?php

namespace Steps;

use Steps\Step;

class Steps
{
    public $steps = [];
    public $args = [];

    public function __construct(/*ISteps */$steps)
    {
        $this->steps = $steps->generateSteps();
        $this->args = $steps->getArgs();

        $firstStep = current($this->steps);
        $firstStep->activate();
    }

    public function activate($name)
    {
        if (array_key_exists($name, $this->steps) === false) {
            throw new \DomainException("L'étape {$name} n'existe pas");
        }

        foreach ($this->steps as $step) {
            $step->desactivate();
        }

        $this->steps[$name]->activate();
    }

    public function getSteps()
    {
        return $this->steps;
    }

    public function getLinkArgs()
    {
        return $this->args;
    }
}

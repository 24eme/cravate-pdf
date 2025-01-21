<?php

namespace Steps;

use Steps\Step;

class Steps
{
    const STEP_FORM = 'Formulaire';
    const STEP_ANNEXES = 'Annexes';
    const STEP_VALIDATION = 'Validation';

    const stepsOrder = [
        self::STEP_FORM,
        self::STEP_ANNEXES,
        self::STEP_VALIDATION
    ];

    const stepsLinks = [
        self::STEP_FORM => 'record_submission_new',
        self::STEP_ANNEXES => 'record_attachment',
        self::STEP_VALIDATION => 'record_validation'
    ];

    public $steps = [];

    public function __construct()
    {
        foreach (self::stepsOrder as $step) {
            $this->steps[$step] = new Step($step, self::stepsLinks[$step]);
        }

        $firstStep = current($this->steps);
        $firstStep->activate();
    }

    public function activate($name)
    {
        if (array_key_exists($name, $this->steps) === false) {
            throw new \LogicException("L'étape {$name} n'existe pas");
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
}

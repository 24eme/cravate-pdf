<?php

namespace Steps;

use Model\Procedure;
use Model\Submission;
use Steps\Step;

class ProcedureSteps //implements ISteps
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
        self::STEP_FORM => 'procedure_edit',
        self::STEP_ANNEXES => 'procedure_attachment',
        self::STEP_VALIDATION => 'procedure_validation'
    ];

    private Procedure $procedure;
    private Submission $submission;

    public function __construct(Procedure $procedure, Submission $submission = null)
    {
        $this->procedure = $procedure;
        $this->submission = $submission;
    }

    public function generateSteps()
    {
        $s = [];
        foreach (self::stepsOrder as $step) {
            if ($this->submission->name !== null) {
                $s[$step] = new Step($step, self::stepsLinks[$step]);
            } else {
                $s[$step] = new Step($step, null);
            }
        }
        return $s;
    }

    public function getArgs()
    {
        $args = [
            'procedure' => $this->procedure->name,
            'submission' => $this->submission->name
        ];

        return $args;
    }
}

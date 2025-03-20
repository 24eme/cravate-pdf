<?php

namespace Steps;

use Records\Record;
use Records\Submission;
use Steps\Step;

class RecordsSteps //implements ISteps
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
        self::STEP_FORM => 'record_edit',
        self::STEP_ANNEXES => 'record_attachment',
        self::STEP_VALIDATION => 'record_validation'
    ];

    private Record $record;
    private Submission $submission;

    public function __construct(Record $record, Submission $submission = null)
    {
        $this->record = $record;
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
            'record' => $this->record->name,
            'submission' => $this->submission->id
        ];

        return $args;
    }
}

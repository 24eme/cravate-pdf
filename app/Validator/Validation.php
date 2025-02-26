<?php

namespace Validator;

use Records\Submission;

class Validation
{
    public $errors = [];
    public $warnings = [];

    public function checkSubmission(Submission $submission)
    {
        if ($submission->getAttachmentsNeeded()) {
           if (empty($submission->getAttachments())) {
               $this->errors[] = ['field' => 'ATTACHED_FILE', 'message' => "Vous n'avez pas soumis de pièce jointe"];
           } elseif (count($submission->getAttachmentsNeeded()) !== count($submission->getAttachments())) {
               $this->warnings[] = ['field' => 'ATTACHED_FILE', 'message' => "Il manque des pièces jointes"];
           }
        }
    }

    public function validate($submittedData, $validators)
    {
        $valid = true;

        foreach ($submittedData as $field => $submittedValue) {
            if (array_key_exists($field, $validators) === false) {
                continue;
            }

            $validator = $validators[$field];

            if ($validator === null) {
                continue;
            }

            foreach (explode('|', $validator) as $func) {
                $callback = strtok($func, ':');
                $arg = strtok(':');

                if ($arg !== false) {
                    $return = self::$callback($arg, $submittedValue);
                } else {
                    $return = self::$callback($submittedValue);
                }

                if ($return === false) {
                    $this->errors[] = ['field' => $field, 'message' => "[$callback] Format invalide : $submittedValue [$arg expected]"];
                }

                $valid = $return && $valid;
            }
        }

        return $valid;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    public function getWarnings()
    {
        return $this->warnings;
    }

    public function hasWarnings()
    {
        return count($this->warnings) > 0;
    }

    public function max($max, $value)
    {
        return is_numeric($value)
                ? $value <= $max
                : strlen($value) <= $max;
    }

    public function min($min, $value)
    {
        return is_numeric($value)
                ? $value >= $min
                : strlen($value) >= $min;
    }

    public function length($length, $value)
    {
        return strlen($value) === (int) $length;
    }

    public function regex($regex, $value)
    {
        return preg_match("/$regex/", $value) === 1;
    }

    public function email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}

<?php

namespace Validator;

use Model\Submission;

class Validation
{
    public $errors = [];
    public $warnings = [];

    public function formatData(array $configData, array $sentData)
    {
        $cleaned = [];

        foreach ($sentData as $fieldId => $value) {
            if (in_array($fieldId, array_keys($configData)) === false) {
                continue;
            }

            $config = $configData[$fieldId];

            if (isset($config['disabled']) && $config['disabled']) {
                continue;
            }

            if ($config['type'] === 'radio') {
                $value = array_key_exists($value, $config['choices']) ? $value : "Off";
            }

            $cleaned[$fieldId] = $value;
        }

        return $cleaned;
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

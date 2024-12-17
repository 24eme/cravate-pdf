<?php

namespace PDF;

use Web;

class PDFFormField
{
    const TYPE_TEXT = 'TEXT';
    const TYPE_SELECT = 'SELECT';
    const TYPE_RADIO = 'RADIO';
    const TYPE_CHECKBOX = 'CHECKBOX';

    protected $type;
    protected $label;
    protected $name;
    protected $id;
    protected $choices = [];
    protected $required = false;

    public function __construct($data)
    {
        $this->parseData($data);
    }

    protected function parseData($data)
    {
        if(isset($data['FieldStateOption'])) {
            foreach($data['FieldStateOption'] as $value) {
                if($value == "Off") {
                    continue;
                }
                $this->choices[$value] = $value;
            }
        }

        if($data['FieldType'] == 'Text') {
            $this->type = self::TYPE_TEXT;
        } elseif($data['FieldType'] == 'Choice') {
            $this->type = self::TYPE_SELECT;
        } elseif($data['FieldType'] == 'Button' && count($this->choices) == 1) {
            $this->type = self::TYPE_CHECKBOX;
        } elseif($data['FieldType'] == 'Button' && count($this->choices) > 1) {
            $this->type = self::TYPE_RADIO;
        }

        if(isset($data['FieldNameAlt']) && $data['FieldNameAlt']) {
            $this->label = $data['FieldNameAlt'];
        } else {
            $this->label = $data['FieldName'];
        }

        $this->name = $data['FieldName'];
        $this->id = strtolower(self::TYPE_TEXT).'_'.Web::instance()->slug($data['FieldName']);
        $this->required = ($data['FieldFlags'] == 2);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getChoices()
    {
        return $this->choices;
    }

    public function isRequired()
    {
        return $this->required;
    }

}

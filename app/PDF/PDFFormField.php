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

    public function __construct($data)
    {
        $this->parseData($data);
    }

    protected function parseData($data) {
        if($data['FieldType'] == 'Text') {
            $this->type = self::TYPE_TEXT;
        } elseif($data['FieldType'] == 'Choice') {
            $this->type = self::TYPE_SELECT;
        } elseif($data['FieldType'] == 'Button' && $data['FieldFlags'] > 0 && isset($data['FieldStateOption'])) {
            $this->type = self::TYPE_RADIO;
        } elseif($data['FieldType'] == 'Button' && $data['FieldFlags'] == 0 && isset($data['FieldStateOption'])) {
            $this->type = self::TYPE_CHECKBOX;
        }

        if(isset($data['FieldNameAlt']) && $data['FieldNameAlt']) {
            $this->label = $data['FieldNameAlt'];
        } else {
            $this->label = $data['FieldName'];
        }

        $this->name = Web::instance()->slug($this->label);
        $this->id = Web::instance()->slug($this->label);

        if(isset($data['FieldStateOption'])) {
            foreach($data['FieldStateOption'] as $value) {
                if($value == "Off") {
                    continue;
                }
                $this->choices[$value] = $value;
            }
        }
    }

    public function getType() {
        return $this->type;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getName() {
        return $this->name;
    }

    public function getId() {
        return $this->id;
    }

    public function getChoices() {
        return $this->choices;
    }

}

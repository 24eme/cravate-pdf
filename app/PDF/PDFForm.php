<?php

namespace PDF;

class PDFForm
{
    protected $pdfFile = null;
    protected $fields = [];

    public function __construct($pdfFile)
    {
        $this->pdfFile = $pdfFile;
        $this->parseDataFields();
    }

    protected function parseDataFields()
    {
        $dataFields = PDFtk::parseDataFieldsDump(PDFtk::dumpDataFields($this->pdfFile));
        foreach($dataFields as $dataField) {
            $field = new PDFFormField($dataField);
            $this->fields[] = $field;
        }
    }

    public function getFields() {
        return $this->fields;
    }
}

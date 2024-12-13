<?php

namespace PDF;

class PDFForm
{
    protected $pdfFile = null;
    protected $dataFields = [];

    public function __construct($pdfFile)
    {
        $this->pdfFile = $pdfFile;
        $this->parseForm();
    }

    protected function parseForm()
    {
        $i = -1;
        foreach(explode("\n", PDFtk::dumpDataFields($this->pdfFile)) as $line) {
            if($line === '---') {
                $i++;
                continue;
            }
            if(strpos($line, ': ') === false)  {
                continue;
            }
            $key = explode(": ", $line)[0];
            $value = explode(": ", $line)[1];

            if(isset($this->dataFields[$i][$key]) && !is_array($this->dataFields[$i][$key])) {
                $this->dataFields[$i][$key] = [$this->dataFields[$i][$key]];
            }
            if(isset($this->dataFields[$i][$key]) && is_array($this->dataFields[$i][$key])) {
                $this->dataFields[$i][$key][] = $value;
            } else {
                $this->dataFields[$i][$key] = $value;
            }
        }
    }

    public function getDataFields() {
        return $this->dataFields;
    }
}

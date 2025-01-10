<?php

namespace Records;

class Submission
{

    public $record;
    public $name;
    public $path;
    public $pdf;
    public $xfdf;

    const ATTACHMENTS_PATH = 'attachments/';

    public function __construct(Record $record, $name = null)
    {
        $this->record = $record;
        if ($name) {
            $this->load($name);
        }
    }

    public function load($name)
    {
        $this->name = $name;
        $this->path = $this->record->submissionsPath.$this->name.DIRECTORY_SEPARATOR;
        if (!file_exists($this->path)) {
            throw new \Exception("The < $this->path > submissions folder doesn't exist");
        }
        $files = scandir($this->path);
        foreach ($files as $file) {
            if (strpos($file, '.pdf') !== false) {
                $this->pdf = $this->path.$file;
            }
            if (strpos($file, '.xfdf') !== false) {
                $this->xfdf = $this->path.$file;
            }
        }
    }

    public function save($files)
    {
        $pdf = $files['pdf'];
        $xfdf = simplexml_load_file($files['xfdf']);

        $this->name = date('YmdHis');
        if (isset($this->record->config['SUBMISSION']) && isset($this->record->config['SUBMISSION']['format_dir'])) {
            $this->name = $this->record->config['SUBMISSION']['format_dir'];
            $this->name = str_replace('%DATE%', date('Ymd'), $this->name);
            $this->name = str_replace('%DATETIME%', date('YmdHis'), $this->name);
            if ($xfdf) {
                foreach ($xfdf->fields->field as $field) {
                    $this->name = str_replace('%'.((string)$field->attributes()['name']).'%', (string)$field->value, $this->name);
                }
            }
        }

        $this->path = $this->record->submissionsPath.$this->name.DIRECTORY_SEPARATOR;
        if (!file_exists($this->path)) {
            mkdir($this->path);
        }

        if (isset($this->record->config['SUBMISSION']) && isset($this->record->config['SUBMISSION']['filename'])) {
            $filename = $this->record->config['SUBMISSION']['filename'];
        } else {
            $filename = basename($pdf, '.pdf');
        }

        if (!rename($pdf, $this->path.$filename.'.pdf')) {
            throw new \Exception("pdf save failed");
        }
        $this->pdf =  $this->path.$filename.'.pdf';
        if (!rename($files['xfdf'], $this->path.$filename.'.xfdf')) {
            throw new \Exception("xfdf save failed");
        }
        $this->xfdf = $this->path.$filename.'.xfdf';
    }

    public function getAttachmentNeeded()
    {
        if (!$this->xfdf) {
            throw new \Exception("xfdf file needed");
        }
        $xfdf = simplexml_load_file($this->xfdf);
        $config = $this->record->config;
        if (!isset($config['ATTACHED_FILE'])) {
            return null;
        }
        foreach ($xfdf->fields->field as $field) {
            $name = (string)$field->attributes()['name'];
            $value = (string)$field->value;
            if (isset($config['ATTACHED_FILE'][$name][$value])) {
                return $config['ATTACHED_FILE'][$name][$value];
            }
        }
        return null;
    }

    public function getAttachmentsPath()
    {
        $attachmentsPath = $this->path.self::ATTACHMENTS_PATH;
        if (!file_exists($attachmentsPath)) {
            mkdir($attachmentsPath);
        }
        return $attachmentsPath;
    }

}

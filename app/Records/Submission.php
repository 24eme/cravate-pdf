<?php

namespace Records;

use DomainException;

class Submission
{
    const STATUS_TOUS = 'TOUS';
    const STATUS_DRAFT = 'BROUILLON';
    const STATUS_SUBMITTED = 'DÉPOSÉ';
    const STATUS_VALIDATED = 'APPROUVÉ';
    const STATUS_UNCOMPLETED = 'INCOMPLET';
    const STATUS_CANCELED = 'ANNULÉ';
    const STATUS_CLOSED = 'CLOTURÉ';

    public static $allStatus = [self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_VALIDATED, self::STATUS_UNCOMPLETED, self::STATUS_CANCELED, self::STATUS_CLOSED];
    public static $statusThemeColor = [self::STATUS_DRAFT => 'light', self::STATUS_SUBMITTED => 'secondary', self::STATUS_VALIDATED => 'success', self::STATUS_UNCOMPLETED => 'warning', self::STATUS_CANCELED => 'danger', self::STATUS_CLOSED => 'dark'];

    public $record;
    public $name;
    public $datetime;
    public $status;
    public $path;
    public $pdf;
    public $xfdf;
    public $datas = [];

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
                $this->pdf = $file;
            }
            if (strpos($file, '.xfdf') !== false) {
                $this->xfdf = $file;
                $this->loadXFDF();
            }
        }
        $pos = strpos($this->name, "_");
        if ($pos === false) {
            throw new \Exception("The name < $this->name > does not contain datetime");
        }
        $this->datetime = \DateTime::createFromFormat('YmdHis', substr($this->name, 0, $pos));
        if (!$this->datetime) {
            throw new \Exception("< ".substr($this->name, 0, $pos)." > is not a valid datetime");
        }
        $pos = strrpos($this->name, "_");
        if ($pos === false) {
            throw new \Exception("The name < $this->name > does not contain status");
        }
        $this->status = substr($this->name, $pos + 1);
        if (!in_array($this->status, self::$allStatus)) {
            throw new \Exception("< $this->status > is not a valid status");
        }
    }

    public function save($files)
    {
        $pdf = $files['pdf'];
        $xfdf = simplexml_load_file($files['xfdf']);

        $this->name = date('YmdHis');
        if (isset($this->record->config['SUBMISSION']) && isset($this->record->config['SUBMISSION']['format_dir'])) {
            $this->name .= '_'.$this->record->config['SUBMISSION']['format_dir'];
            if ($xfdf) {
                foreach ($xfdf->fields->field as $field) {
                    $this->name = str_replace('%'.((string)$field->attributes()['name']).'%', (string)$field->value, $this->name);
                }
            }
        }
        $this->name .= '_'.self::STATUS_DRAFT;
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

    public function setStatus($status)
    {
        if (in_array($status, self::$allStatus) === false) {
            throw new DomainException("{$status} n'est pas un status valide");
        }

        $oldStatus = $this->status;
        $newName = str_replace($oldStatus, $status, $this->name);

        if ($this->name === $newName) { // Pas de changement de status
            return true;
        }

        $newPath = str_replace($this->name, $newName, $this->path);
        if (!rename($this->path, $newPath)) {
            throw new \Exception("Submission folder rename failed");
        }
        $this->load($newName);

        return true;
    }

    public function getAttachmentNeeded()
    {
        if (!$this->xfdf) {
            throw new \Exception("xfdf file needed");
        }
        $xfdf = simplexml_load_file($this->path.preg_replace('|^.*/|', '', $this->xfdf));
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

    public function getAttachments()
    {
        $attachments = scandir($this->getAttachmentsPath());
        if (!$attachments) {
            return [];
        }
        $items = [];
        foreach($attachments as $attachment) {
            if (in_array($attachment, ['.', '..'])) {
                continue;
            }
            try {
                $items[] = $attachment;
            } catch (\Exception $e) {
                continue;
            }
        }
        return $items;
    }

    public function getStatusThemeColor()
    {
        return (isset(self::$statusThemeColor[$this->status]))? self::$statusThemeColor[$this->status] : '';
    }

    public function getLibelle()
    {
        return trim(str_replace([$this->datetime->format('YmdHis'), $this->status, '_'], ['', '', ' '], $this->name));
    }

    public function loadDatas(array $datas)
    {
        $this->datas = array_merge($this->datas, $datas);
    }

    public function loadXFDF()
    {
        if (! $this->xfdf) {
            return false;
        }

        $xfdf = simplexml_load_file($this->path.$this->xfdf);
        if ($xfdf) {
            foreach ($xfdf->fields->field as $field) {
                $this->name = str_replace('%'.((string)$field->attributes()['name']).'%', (string)$field->value, $this->name);
                $this->datas[((string)$field->attributes()['name'])] = (string)$field->value;
            }
        }
    }

    public function getDatas($key = null)
    {
        if (empty($this->datas)) {
            return [];
        }

        if ($key && array_key_exists($key, $this->datas)) {
            return $this->datas[$key];
        }

        return $this->datas;
    }

    public static function printStatus($status)
    {
        return ucfirst(mb_strtolower($status));
    }

    public function getForm() {

        return $this->record->config['form'];
    }

    public function getFields() {
        $fields = [];

        foreach($this->getForm() as $key => $field) {
            $fields[$field['category']][$key] = $field;
        }

        return $fields;
    }

}

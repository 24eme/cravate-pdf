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

    const ATTACHMENTS_PATH = 'attachments/';

    public static $allStatus = [self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_VALIDATED, self::STATUS_UNCOMPLETED, self::STATUS_CANCELED, self::STATUS_CLOSED];
    public static $statusThemeColor = [self::STATUS_DRAFT => 'light', self::STATUS_SUBMITTED => 'secondary', self::STATUS_VALIDATED => 'success', self::STATUS_UNCOMPLETED => 'warning', self::STATUS_CANCELED => 'danger', self::STATUS_CLOSED => 'dark'];

    public $record;
    public $name;

    public $filename;

    public $datetime;
    public $status;
    public $path;

    public $pdf;
    public $xfdf;
    public $json;

    public $datas = [];

    public function __construct(Record $record, $name = null)
    {
        $this->record = $record;
        $this->json = new \stdClass();
        if ($name) {
            $this->load($name);
        }
    }

    public function load($name)
    {
        $this->name = $name;
        $this->path = $this->record->submissionsPath.$this->name.DIRECTORY_SEPARATOR;
        if (!is_dir($this->path)) {
            mkdir($this->path);
        }
        $files = scandir($this->path);
        foreach ($files as $file) {
            if (strpos($file, '.pdf') !== false) {
                $this->pdf = $file;
            }
            if (strpos($file, '.json') !== false) {
                $this->loadJSON($file);
            }
        }

        $pos = strpos($this->name, "_");
        if ($pos === false) {
            /* throw new \Exception("The name < $this->name > does not contain datetime"); */
        }
        $this->datetime = \DateTime::createFromFormat('YmdHis', substr($this->name, 0, $pos));
        if (!$this->datetime) {
            /* throw new \Exception("< ".substr($this->name, 0, $pos)." > is not a valid datetime"); */
        }
        $pos = strrpos($this->name, "_");
        if ($pos === false) {
            /* throw new \Exception("The name < $this->name > does not contain status"); */
        }
        $this->status = substr($this->name, $pos + 1);
        if (!in_array($this->status, self::$allStatus)) {
            /* throw new \Exception("< $this->status > is not a valid status"); */
        }

        $this->filename = (isset($this->record->config['SUBMISSION']) && isset($this->record->config['SUBMISSION']['filename']))
                    ? $this->record->config['SUBMISSION']['filename']
                    : null;
    }

    /**
     * @param $data array<string, string> pour le json->form
     * @param $files array{pdf: string, xfdf: string} nom des deux fichiers
     */
    public function save($data, $files)
    {
        $pdf = $files['pdf'];
        $xfdf = $files['xfdf'];

        $oldPath = $this->path;

        $filename = $this->filename ?: basename($pdf, '.pdf');

        // fichier de tmp -> dans dossier
        if (!rename($pdf, $this->path.$filename.'.pdf')) {
            throw new \Exception("pdf save failed");
        }
        $this->pdf =  $this->path.$filename.'.pdf';

        // fichier de tmp -> dans dossier
        if (!rename($xfdf, $this->path.$filename.'.xfdf')) {
            throw new \Exception("xfdf save failed");
        }
        $this->xfdf = $this->path.$filename.'.xfdf';
        $this->json->form = json_decode(json_encode($data));
        file_put_contents($this->path.$filename.'.json', json_encode($this->json, JSON_PRETTY_PRINT));

        // on renomme le dossier
        $this->name = date('YmdHis');
        if (isset($this->record->config['SUBMISSION']) && isset($this->record->config['SUBMISSION']['format_dir'])) {
            $this->name .= '_'.$this->record->config['SUBMISSION']['format_dir'];
            foreach ($data as $field => $value) {
                $this->name = str_replace("%$field%", (string) $value, $this->name);
            }
        }

        $this->name .= '_'.self::STATUS_DRAFT;
        $this->path = $this->record->submissionsPath.$this->name.DIRECTORY_SEPARATOR;

        rename($oldPath, $this->path);
    }

    public function setStatus($status, $comment = null)
    {
        if (in_array($status, self::$allStatus) === false) {
            throw new DomainException("{$status} n'est pas un status valide");
        }

        $oldStatus = $this->status;
        $newName = str_replace($oldStatus, $status, $this->name);

        if ($this->name === $newName) { // Pas de changement de status
            return true;
        }

        $this->addHistory("Mis à jour vers le status $status", $comment);

        $newPath = str_replace($this->name, $newName, $this->path);
        if (!rename($this->path, $newPath)) {
            throw new \Exception("Submission folder rename failed");
        }
        $this->load($newName);

        return true;
    }

    public function addHistory($data, $comment = null)
    {
        $data = ['date' => (new \DateTime())->format('c'), 'entrie' => $data, 'comment' => $comment];
        $this->json->history[] = json_decode(json_encode($data));
        file_put_contents($this->path.$this->filename.'.json', json_encode($this->json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }

    public function getAttachmentNeeded()
    {
        $config = $this->record->config;
        if (!isset($config['ATTACHED_FILE'])) {
            return null;
        }
        foreach ($this->json->form as $name => $value) {
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

    public function getAttachmentByName($name) {
        $attachments = $this->getAttachments();

        foreach($attachments as $attachment) {
            if(pathinfo(basename($attachment))['filename'] == $name) {

                return $attachment;
            }
        }

        return null;
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

    public function loadJSON($file)
    {
        $this->json = json_decode(file_get_contents($this->path.$file));

        foreach ($this->json->form as $field => $value) {
            $this->datas[$field] = $value;
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

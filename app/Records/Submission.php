<?php

namespace Records;

use DomainException;
use PDF\PDFtk;

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

    public $id;
    public $createdAd;
    public $status;
    public $path;

    public $pdf;
    public $xfdf;
    public $json;

    public $datas = [];

    public function __construct(Record $record)
    {
        $this->record = $record;
        $this->status = Submission::STATUS_DRAFT;
        $this->id = date('YmdHis');
        $this->createdAt = new \DateTime();
        $this->json = new \stdClass();
        $this->name = $this->id;
        $this->path = $this->record->submissionsPath.$this->id.DIRECTORY_SEPARATOR;
        $this->filename = (isset($this->record->config['SUBMISSION']) && isset($this->record->config['SUBMISSION']['filename']))
                    ? $this->record->config['SUBMISSION']['filename']
                    : null;
    }

    public function load($name)
    {
        $this->name = $name;
        $this->path = $this->record->submissionsPath.$this->name.DIRECTORY_SEPARATOR;
        $files = scandir($this->path);
        foreach ($files as $file) {
            if (strpos($file, '.pdf') !== false) {
                $this->pdf = $file;
            }
            if (strpos($file, '.json') !== false) {
                $this->loadJSON($file);
            }
        }
    }

    public function setDatas($datas) {
        $this->datas = $datas;
        unset($this->datas['submission']);
        unset($this->datas['file']);
    }

    public function save()
    {
        if (!is_dir($this->path)) {
            mkdir($this->path);
        }

        $oldPath = $this->path;

        if(count($this->getDatas())) {
            $filename = $this->filename ?: basename($this->record->pdf, '.pdf');
            $this->pdf =  $this->path.$filename.'.pdf';
            $files = PDFTk::fillForm($this->record->pdf, $this->getDatas());
            // fichier de tmp -> dans dossier
            if (!rename($files['pdf'], $this->path.$filename.'.pdf')) {
                throw new \Exception("pdf save failed");
            }
            // fichier de tmp -> dans dossier
            if (!rename($files['xfdf'], $this->path.$filename.'.xfdf')) {
                throw new \Exception("xfdf save failed");
            }
            $this->xfdf = $this->path.$filename.'.xfdf';
        }

        $this->updateJSON();

        // on renomme le dossier
        $this->name = $this->id;
        if (isset($this->record->config['SUBMISSION']) && isset($this->record->config['SUBMISSION']['format_dir'])) {
            $this->name .= '_'.$this->record->config['SUBMISSION']['format_dir'];
            foreach ($this->getDatas() as $field => $value) {
                $this->name = str_replace("%$field%", (string) $value, $this->name);
            }
        }

        $this->name .= '_'.$this->status;
        $this->path = $this->record->submissionsPath.$this->name.DIRECTORY_SEPARATOR;

        rename($oldPath, $this->path);
    }

    public function setStatus($status, $comment = null, $force = false)
    {
        if (in_array($status, self::$allStatus) === false) {
            throw new DomainException("{$status} n'est pas un status valide");
        }

        if (!$force && $this->status === $status) {
            return;
        }

        $this->status = $status;
        $this->addHistory("Mis à jour vers le status $status", $comment);
    }

    public function updateJSON()
    {
        $date = (new \DateTime())->format('c');

        $this->json->form = $this->datas;

        if (property_exists($this->json, 'createdAt') === false) {
            $this->json->createdAt = $date;
        }

        $this->json->createdAt = $this->createdAt->format('Y-m-d H:i:s');
        $this->json->modifiedAt = $date;
        $this->json->status = $this->status;
        $this->json->id = $this->id;

        file_put_contents($this->path.$this->filename.'.json', json_encode($this->json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }

    public function addHistory($data, $comment = null)
    {
        $data = ['date' => (new \DateTime())->format('c'), 'entry' => $data, 'comment' => $comment];
        $this->json->history[] = json_decode(json_encode($data));
    }

    public function getAttachmentsNeeded()
    {
        $config = $this->record->config;
        if (!isset($config['ATTACHED_FILE'])) {
            return [];
        }
        $attachments = [];
        foreach ($config['ATTACHED_FILE'] as $attachment) {
            if($attachment['filter'] && $this->getDatas(explode(":", $attachment['filter'])[0]) != explode(":", $attachment['filter'])[1]) {
                continue;
            }
            $attachments[] = $attachment;
        }
        return $attachments;
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
        return trim(str_replace([$this->id, $this->status, '_'], ['', '', ' '], $this->name));
    }

    public function loadJSON($file)
    {
        $this->json = json_decode(file_get_contents($this->path.$file));
        $this->datas = [];
        if(isset($this->json->form)) {
            foreach ($this->json->form as $field => $value) {
                $this->datas[$field] = $value;
            }
        }

        if(isset($this->json->status)) {
            $this->status = $this->json->status;
        }
        if(isset($this->json->id)) {
            $this->id = $this->json->id;
        }
        if(isset($this->json->createdAt)) {
            $this->createdAt = new \DateTime($this->json->createdAt);
        }
    }

    public function getDatas($key = null, $default = null)
    {
        if (empty($this->datas)) {
            $this->datas = [];
        }

        if ($key && array_key_exists($key, $this->datas)) {
            return $this->datas[$key];
        }

        if ($key) {
            return $default;
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

    public function isEditable()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_UNCOMPLETED]);
    }

    public function getHistory()
    {
        if (property_exists($this->json, 'history')) {
            return array_reverse($this->json->history);
        }
        return [];
    }

    public function getHistoryForStatus($status)
    {
        foreach ($this->getHistory() as $item) {
            if (strpos($item->entry, self::STATUS_UNCOMPLETED)) {
                return $item;
            }
        }
        return null;
    }

    public function isAuthor($identifiant)
    {
        return (strpos($this->name, $identifiant) !== false);
    }

}

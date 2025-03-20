<?php

namespace Records;

use DomainException;
use Records\Record;

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

    public $json;

    public $datas = [];

    public function __construct(Record $record, $name)
    {
        $this->record = $record;
        $this->json = new \stdClass();
        $this->load($name);
    }

    public function load($name)
    {
        $this->name = $name;
        $this->path = $this->record->submissionsPath.$this->name.DIRECTORY_SEPARATOR;

        if (!is_dir($this->path)) {
            mkdir($this->path);
        }

        if (file_exists($this->path.Record::METAS_FILENAME)) {
            $this->loadJSON($this->path.Record::METAS_FILENAME);
        }

        $submissionConfig = $this->record->getConfigItem('SUBMISSION');

        if ($submissionConfig === null) {
            throw new \Exception("Missing configuration");
        }

        $this->filename = $this->record->config['SUBMISSION']['filename'];

        if (file_exists($this->path.$this->filename.".json")) {
            $this->loadJSON($this->path.$this->filename.".json");
        }

        $this->status = property_exists($this->json, 'status') ? $this->json->status : substr($this->name, strrpos($this->name, '_') + 1);
        $this->datetime = property_exists($this->json, 'createdAt')
            ? \DateTime::createFromFormat(\DateTimeInterface::ISO8601, $this->json->createdAt)
            : \DateTime::createFromFormat('YmdHis', substr($this->name, 0, strpos($this->name, '_')));
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

        // fichier de tmp -> dans dossier
        if (!rename($xfdf, $this->path.$filename.'.xfdf')) {
            throw new \Exception("xfdf save failed");
        }

        $this->json->form = json_decode(json_encode($data));
        $this->status = self::STATUS_DRAFT;
        $this->updateJSON();

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
        $this->status = $status;
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

        $this->updateJSON();

        return true;
    }

    public function updateJSON()
    {
        $date = (new \DateTime())->format('c');

        if (property_exists($this->json, 'createdAt') === false) {
            $this->json->createdAt = $date;
        }

        $this->json->modifiedAt = $date;
        $this->json->status = $this->status;

        file_put_contents($this->path.$this->filename.'.json', json_encode($this->json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }

    public function addHistory($data, $comment = null)
    {
        $data = ['date' => (new \DateTime())->format('c'), 'entry' => $data, 'comment' => $comment];
        $this->json->history[] = json_decode(json_encode($data));

        $this->updateJSON();
    }

    public function getAttachmentsNeeded()
    {
        $attachments = $this->record->getConfigItem('ATTACHED_FILE');

        if ($attachments === null) {
            return [];
        }

        $needed = [];
        foreach ($attachments as $attachment) {
            if ($attachment['filter']) {
                [$field, $requirement] = explode(":", $attachment['filter']);
                if ($this->getDatas($field) !== $requirement) {
                    continue;
                }
            }
            $needed[] = $attachment;
        }
        return $needed;
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
        if ($attachments === false) {
            return [];
        }

        $items = [];
        foreach($attachments as $attachment) {
            if (in_array($attachment, ['.', '..'])) {
                continue;
            }

            $items[] = $attachment;
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

    public function loadJSON($file)
    {
        $this->json = json_decode(file_get_contents($this->path.$file));

        foreach ($this->json->form as $field => $value) {
            $this->datas[$field] = $value;
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

    public function getDisabledFields()
    {
        $fields = [];

        foreach ($this->record->getConfigItem('form') as $fieldKey => $conf) {
            if (isset($conf['disabled'])) {
                $fields[$fieldKey] = $this->getDatas($fieldKey);
            }
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

    public function isAuthor($identifiant = null)
    {
        if ($identifiant === null) {
            return false;
        }

        return strpos($this->name, $identifiant) !== false;
    }

}

<?php

namespace Records;

use DomainException;
use Records\Record;
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

    const METAS_FILENAME = 'metas.json';
    const DATAS_FILENAME = 'datas.json';
    const ATTACHMENTS_PATH = 'attachments/';

    public static $allStatus = [self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_VALIDATED, self::STATUS_UNCOMPLETED, self::STATUS_CANCELED, self::STATUS_CLOSED];
    public static $statusThemeColor = [self::STATUS_DRAFT => 'light', self::STATUS_SUBMITTED => 'secondary', self::STATUS_VALIDATED => 'success', self::STATUS_UNCOMPLETED => 'warning', self::STATUS_CANCELED => 'danger', self::STATUS_CLOSED => 'dark'];

    public $record;
    public $folderName;

    public $id;
    public $userId;
    public $createdAt;
    public $submittedAt;
    public $modifieAt;
    public $status;
    public $path;
    public $json;

    public $datas = [];

    public static function create(Record $record, $userId) {
        $submission = new Submission($record, $userId);
        $submission->setStatus(Submission::STATUS_DRAFT, null, true);

        return $submission;
    }

    public static function find(Record $record, $id) {
        if(!preg_match('/^[0-9A-Za-z\-_]+$/', $id)) {
            throw new \Exception("id invalid");
        }
        foreach(glob($record->submissionsPath.$id.'*', GLOB_ONLYDIR) as $path) {
            break;
        }

        if(!isset($path) || !is_dir($path)) {
            throw new \Exception("path \"$path\" not exist");
        }

        $submission = new Submission($record);
        $submission->load(basename($path));

        return $submission;
    }

    public function __construct(Record $record, $userId = null)
    {
        $this->record = $record;
        $this->status = Submission::STATUS_DRAFT;
        $this->id = date('YmdHis').rand(1000,9999);
        $this->userId = $userId;
        $this->createdAt = new \DateTime();
	    $this->updatedAt = new \DateTime();
        $this->json = new \stdClass();
        $this->folderName = $this->id.'_'.$this->userId;
        $this->path = $this->record->submissionsPath.$this->folderName.DIRECTORY_SEPARATOR;
        $this->filename = (isset($this->record->config['SUBMISSION']) && isset($this->record->config['SUBMISSION']['filename']))
                    ? $this->record->config['SUBMISSION']['filename']
                    : null;
    }

    public function load($folderName)
    {
        $this->folderName = $folderName;
        $this->path = $this->record->submissionsPath.$this->folderName.DIRECTORY_SEPARATOR;
        if (file_exists($this->path.self::METAS_FILENAME)) {
            $this->loadJSON($this->path.self::METAS_FILENAME);
        }
        if (file_exists($this->path.self::DATAS_FILENAME)) {
            $this->loadJSON($this->path.self::DATAS_FILENAME);
        }
    }

    protected function loadJSON($file)
    {
        $this->json = json_decode(file_get_contents($file));
        $this->datas = [];
        if(isset($this->json->form)) {
            foreach ($this->json->form as $field => $value) {
                $this->datas[$field] = $value;
            }
        }

        $fieldsToLoad = ["status", "id", "userId"];
        foreach($fieldsToLoad as $field) {
            if(property_exists($this->json, $field)) {
                $this->{$field} = $this->json->{$field};
            }
        }
        $datesToLoad = ["createdAt", "submittedAt", "modifiedAt"];
        foreach($datesToLoad as $field) {
            if(property_exists($this->json, $field)) {
                $this->{$field} = new \DateTime($this->json->{$field});
            }
        }
    }

    public function setDatas($datas)
    {
        $this->datas = $datas;
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
        $this->json->form = $this->datas;

        $this->json->createdAt = $this->createdAt->format('c');
        $this->json->modifiedAt = (new \DateTime())->format('c');
        $this->json->status = $this->status;
        $this->json->id = $this->id;
        $this->json->userId = $this->userId;

        file_put_contents($this->path.self::DATAS_FILENAME, json_encode($this->json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
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
        return trim(str_replace([$this->id, $this->status, '_'], ['', '', ' '], $this->folderName));
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

    public function isAuthor($userId)
    {
        return $userId == $this->userId;
    }

    public function save()
    {
        if (!is_dir($this->path)) {
            mkdir($this->path);
            if($this->record->getConfigItem('initDossier')) {
                shell_exec($this->record->getConfigItem('initDossier')." ".$this->path);
            }
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
        }

        $this->updateJSON();

        // on renomme le dossier
        $this->folderName = $this->id.'_'.$this->userId;
        if (isset($this->record->config['SUBMISSION']) && isset($this->record->config['SUBMISSION']['format_dir'])) {
            $this->folderName .= '_'.$this->record->config['SUBMISSION']['format_dir'];
            foreach ($this->getDatas() as $field => $value) {
                $this->folderName = str_replace("%$field%", (string) $value, $this->folderName);
            }
        }

        $this->folderName .= '_'.$this->status;
        $this->path = $this->record->submissionsPath.$this->folderName.DIRECTORY_SEPARATOR;

        rename($oldPath, $this->path);
    }
}

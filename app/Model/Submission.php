<?php

namespace Model;

use DomainException;
use Model\Procedure;
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

    public Procedure $procedure;
    public $folderName;

    public $id;
    public $userId;
    public $createdAt;
    public $submittedAt;
    public $modifiedAt;
    public $status;
    public $path;
    public $json;

    public $datas = [];
    public $annexes = [];

    public static function create(Procedure $procedure, $userId) {
        $submission = new Submission($procedure, $userId);
        $submission->setStatus(Submission::STATUS_DRAFT, null, true);

        return $submission;
    }

    public static function find(Procedure $procedure, $id) {
        if(!preg_match('/^[0-9A-Za-z\-_]{18,}+$/', $id)) {
            throw new \Exception("id invalid");
        }

        $paths = glob($procedure->submissionsPath.$id.'*', GLOB_ONLYDIR);

        if(!count($paths) || count($paths) > 1) {
            return null;
        }

        $submission = new Submission($procedure);
        $submission->load(basename($paths[0]));

        return $submission;
    }

    /**
     * @return array{id: string, author: string, rs: string, status: string, themeColor: ?string, date: \DateTime, libelle: string}
     */
    public static function parseFolderName(string $folderName)
    {
        $id = strtok($folderName, '_');
        $author = strtok('_');
        $rs = str_replace("{$id}_{$author}_", '', $folderName);
        $rs = substr($rs, 0, strrpos($rs, '_'));
        $status = substr($folderName, strrpos($folderName, '_') + 1);

        $themeColor = isset(self::$statusThemeColor[$status]) ? self::$statusThemeColor[$status] : null;
        $date = \DateTime::createFromFormat('YmdHis', substr($id, 0, 14));
        $libelle = "$author $rs";

        return compact('id', 'author', 'rs', 'status', 'themeColor', 'date', 'libelle');
    }

    public function __construct(Procedure $procedure, $userId = null)
    {
        $this->procedure = $procedure;
        $this->status = Submission::STATUS_DRAFT;
        $this->id = date('YmdHis').rand(1000,9999);
        $this->userId = $userId;
        $this->createdAt = new \DateTime();
        $this->modifiedAt = new \DateTime();
        $this->json = new \stdClass();
        $this->folderName = $this->id.'_'.$this->userId;
        $this->path = $this->procedure->submissionsPath.$this->folderName.DIRECTORY_SEPARATOR;
    }

    public function load($folderName)
    {
        $this->folderName = $folderName;
        $this->path = $this->procedure->submissionsPath.$this->folderName.DIRECTORY_SEPARATOR;
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
        if(isset($this->json->form)) {
            foreach ($this->json->form as $field => $value) {
                $this->datas[$field] = $value;
            }
        }

        if(isset($this->json->annexes)) {
            foreach($this->json->annexes as $key => $annexe) {
                $this->annexes[$key] = !is_array($annexe) ? get_object_vars($annexe) : $annexe;
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
            throw new DomainException("{$status} n'est pas un statut valide");
        }

        if (!$force && $this->status === $status) {
            return;
        }

        $this->status = $status;
        $this->addHistory("Mis à jour vers le statut $status", $comment);
    }

    public function updateJSON()
    {
        $this->json->form = $this->datas;

        $this->json->createdAt = $this->createdAt->format('c');
        $this->json->modifiedAt = (new \DateTime())->format('c');
        $this->json->status = $this->status;
        $this->json->id = $this->id;
        $this->json->userId = $this->userId;
        $isCreation = !file_exists($this->path.self::DATAS_FILENAME);
        file_put_contents($this->path.self::DATAS_FILENAME, json_encode($this->json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        if ($isCreation) {
            chmod($this->path.self::DATAS_FILENAME, 0664);
        }
    }

    public function addHistory($data, $comment = null)
    {
        $data = ['date' => (new \DateTime())->format('c'), 'entry' => $data, 'comment' => $comment];
        $this->json->history[] = json_decode(json_encode($data));
    }

    public function getAnnexes()
    {

        return $this->annexes;
    }

    public function getAttachmentsConfig()
    {
        $attachments = [];
        foreach ($this->procedure->getConfigItem('ATTACHED_FILE', []) as $attachment) {
            $field = strtok($attachment['filter'], ':');
            $value = strtok(':');

            if($attachment['filter'] && $this->getDatas($field) != $value) {
                continue;
            }

            $attachments[] = $attachment;
        }
        return $attachments;
    }

    public function getAttachmentsCategoryConfig()
    {
        $attachments = $this->getAttachmentsConfig();
        $categories = [];
        foreach($attachments as $attachment) {
            $categories[$attachment['filename']] = $attachment['label'];
        }
        return $categories;
    }

    public function getAttachmentsPath()
    {
        $attachmentsPath = $this->path.self::ATTACHMENTS_PATH;
        if (!file_exists($attachmentsPath)) {
            mkdir($attachmentsPath);
        }
        return $attachmentsPath;
    }

    public function getAttachmentsByCategory($category) {
        $attachments = $this->getAttachments();

        if(!isset($attachments[$category])) {
            return [];
        }
        return $attachments[$category];
    }

    public function getAttachments()
    {
        $categories = scandir($this->getAttachmentsPath());
        if ($categories === false) {
            return [];
        }

        $items = [];
        foreach($categories as $category) {
            if (in_array($category, ['.', '..'])) {
                continue;
            }
            if(!is_dir($this->getAttachmentsPath().$category)) {
                continue;
            }
            $attachments = scandir($this->getAttachmentsPath().$category);
            foreach($attachments as $attachment) {
                if (in_array($attachment, ['.', '..'])) {
                    continue;
                }
                $items[$category][] = $attachment;
            }
        }
        $categories = array_keys($this->getAttachmentsCategoryConfig());
        uksort($items, function($a, $b) use ($categories) { $indexA = array_search($a, $categories); $indexB = array_search($b, $categories); ($indexA !== false) ?:-1; ($indexB !== false) ?:-1; return $indexA - $indexB; });
        return $items;
    }

    public function storeAttachment($category, $file)
    {
        if (!is_dir($this->getAttachmentsPath().$category)) {
            mkdir($this->getAttachmentsPath().$category);
        }
        move_uploaded_file($file['tmp_name'], $this->getAttachmentsPath().$category.DIRECTORY_SEPARATOR.$file['name']);
    }

    public function getStatusThemeColor()
    {
        return (isset(self::$statusThemeColor[$this->status]))? self::$statusThemeColor[$this->status] : '';
    }

    public function getLibelle()
    {
        return trim(str_replace([$this->id, $this->status, '_'], ['', '', ' '], $this->getFolderName()));
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

    public function getDatasPDF() {
        $datas = $this->getDatas();
        $datas['DATE_SUBMITTED'] = $this->getDateSubmitted()->format('d/m/Y');

        return $datas;
    }

    public static function printStatus($status)
    {
        return ucfirst(mb_strtolower($status));
    }

    public function getForm() {

        return $this->procedure->config['form'];
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

        foreach ($this->procedure->getConfigItem('form') as $fieldKey => $conf) {
            if (isset($conf['disabled'])) {
                $fields[$fieldKey] = $this->getDatas($fieldKey);
            }
        }

        return $fields;
    }

    public function getDateSubmitted()
    {
        return $this->getDateHistory(self::STATUS_SUBMITTED);
    }

    public function isEditable()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_UNCOMPLETED]);
    }

    public function getHistory($reverse = true)
    {
        if (property_exists($this->json, 'history')) {
            return ($reverse)? array_reverse($this->json->history) : $this->json->history;
        }
        return [];
    }

    public function getHistoryForStatus($status, $reverse = true)
    {
        foreach ($this->getHistory($reverse) as $item) {
            if (strpos($item->entry, $status)) {
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
            if($this->procedure->getConfigItem('initDossier')) {
                shell_exec($this->procedure->getConfigItem('initDossier')." ".$this->path);
            }
        }

        $oldPath = $this->path;

        $this->updateJSON();

        $this->path = $this->procedure->submissionsPath.$this->getFolderName().DIRECTORY_SEPARATOR;

        rename($oldPath, $this->path);
    }

    public function getFolderName()
    {
        $this->folderName = $this->id.'_'.$this->userId;
        if (isset($this->procedure->config['SUBMISSION']) && isset($this->procedure->config['SUBMISSION']['format_dir'])) {
            $this->folderName .= '_'.$this->procedure->config['SUBMISSION']['format_dir'];
            foreach ($this->getDatas() as $field => $value) {
                $this->folderName = str_replace("%$field%", (string) $value, $this->folderName);
            }
            $this->folderName = preg_replace("/%.*%/", '', $this->folderName);
        }
        $this->folderName .= '_'.$this->status;
        return $this->folderName;
    }

    public function getDateHistory($status)
    {
        if ($history = $this->getHistoryForStatus($status, false)) {
            return \DateTime::createFromFormat(\DateTimeInterface::ISO8601, $history->date);
        }
        return null;
    }

    public function getIdFormated()
    {
        return preg_replace('/^([0-9]{8})([0-9]{6})(.+)$/', '\1-\2-\3', $this->id);
    }
}

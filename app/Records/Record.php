<?php

namespace Records;

use Records\Submission;

class Record
{
    const PDF_FILENAME = 'form.pdf';
    const CONFIG_FILENAME = 'form.ini';
    const METAS_FILENAME = 'form.json';
    const SUBMISSIONS_PATH = 'submissions/';

    public $name;
    public $path;
    public $pdf;
    public $config;
    public $metas;
    public $submissionsPath;

    public static function getRecordPath($name)
    {
        return Records::FOLDER.$name.DIRECTORY_SEPARATOR;
    }

    public function __construct($name)
    {
        $path = self::getRecordPath($name);
        if (!file_exists($path)) {
            throw new \Exception("The < $name > record folder doesn't exist");

        }
        $this->path = $path;
        if (!file_exists($this->path.self::PDF_FILENAME)) {
            throw new \Exception("No PDF file for < $name > record folder");

        }
        $this->name = $name;
        $this->pdf = $this->path.self::PDF_FILENAME;
        $this->submissionsPath = $this->path.self::SUBMISSIONS_PATH;
        if (!file_exists($this->submissionsPath)) {
            mkdir($this->submissionsPath);
        }
        $this->loadConfig();
        $this->loadMetas();
    }

    private function loadConfig()
    {
        $this->config = [];
        if (file_exists($this->path.self::CONFIG_FILENAME)) {
            if ($config = parse_ini_file($this->path.self::CONFIG_FILENAME, true)) {
                $this->config = $config;
            }
        }
    }

    private function loadMetas()
    {
        $this->metas = [];
        if (file_exists($this->path.self::METAS_FILENAME)) {
            if ($content = file_get_contents($this->path.self::METAS_FILENAME)) {
                $this->metas = json_decode($content);
            }
        }
    }

    public function getSubmissions()
    {
        $submissions = scandir($this->submissionsPath);
        if (!$submissions) {
            return [];
        }
        $items = [];
        foreach($submissions as $submission) {
            if (in_array($submission, ['.', '..'])) {
                continue;
            }
            try {
                $items[] = new Submission($this, $submission);
            } catch (\Exception $e) {
                continue;
            }
        }
        return $items;
    }

    public function getLibelle()
    {
        return (isset($this->config['libelle']))? $this->config['libelle'] : $this->name;
    }
}

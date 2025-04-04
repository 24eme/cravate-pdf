<?php

namespace Model;

use Model\Submission;

class Procedure
{
    const PDF_FILENAME = 'form.pdf';
    const CONFIG_FILENAME = 'form.php';
    const SUBMISSIONS_PATH = 'submissions/';
    const FOLDER = __DIR__.'/../../procedures/';

    public $name;
    public $path;
    public $pdf;
    public $config;
    public $metas;
    public $submissionsPath;

    public static function getProcedures() {
        $procedures = scandir(self::FOLDER);
        if (!$procedures) {
            return [];
        }
        $items = [];
        foreach($procedures as $procedure) {
            if (in_array($procedure, ['.', '..'])) {
                continue;
            }
            $items[] = new Procedure($procedure);
        }
        return $items;
    }

    public function __construct($name)
    {
        $path = self::FOLDER.$name.DIRECTORY_SEPARATOR;

        if (!file_exists($path)) {
            throw new \Exception("The < $name > procedure folder doesn't exist");
        }

        $this->path = $path;

        if (!file_exists($this->path.self::PDF_FILENAME)) {
            throw new \Exception("No PDF file for < $name > procedure folder");
        }

        $this->name = $name;
        $this->pdf = $this->path.self::PDF_FILENAME;
        $this->submissionsPath = $this->path.self::SUBMISSIONS_PATH;

        if (!file_exists($this->submissionsPath)) {
            mkdir($this->submissionsPath);
        }

        $this->loadConfig();
    }

    private function loadConfig()
    {
        if (file_exists($this->path.self::CONFIG_FILENAME) === false) {
            throw new \RuntimeException("Missing config file for procedure");
        }

        $this->config = include($this->path.self::CONFIG_FILENAME);
    }

    public function getSubmissions($statusFilter = Submission::STATUS_TOUS, $identifiant = null)
    {
        $submissions = scandir($this->submissionsPath);

        if ($submissions === false) {
            return [];
        }

        $items = [];
        foreach($submissions as $submission) {
            if (in_array($submission, ['.', '..'])) {
                continue;
            }

            $infos = Submission::parseFolderName($submission);

            if ($statusFilter !== Submission::STATUS_TOUS && $statusFilter !== $infos['status']) {
                continue;
            }

            if ($identifiant && $infos['author'] !== $identifiant) {
                continue;
            }

            $items[$infos['id']] = $infos;
        }

        krsort($items);
        return $items;
    }

    /**
     * @return array<string, int>
     */
    public function countByStatus($identifiant = null)
    {
        $submissions = $this->getSubmissions(Submission::STATUS_TOUS, $identifiant);
        $result = array_fill_keys(Submission::$allStatus, 0);
        foreach ($submissions as $submission) {
            $result[$submission['status']]++;
        }
        return $result;
    }

    public function getConfigItem($key, $default = null) {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        if (strpos($key, '.') !== false) {
            $segments = explode('.', $key);
            $root = $this->config;

            foreach ($segments as $segment) {
                $root = $root[$segment];
            }

            $this->config[$key] = $root;
            return $root;
        }

        return $default;
    }

    public function getValidators()
    {
        return array_combine(
            array_keys($this->getConfigItem('form')),
            array_column($this->getConfigItem('form'), 'validate')
        );
    }
}

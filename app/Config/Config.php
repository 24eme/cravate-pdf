<?php

namespace Config;

class Config
{
    private static $_instance = null;
    protected $config = null;
    protected $f3 = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new Config();
        }
        return self::$_instance;
    }

    public function getConfigFile() {

        return $this->getRootDir().'/config/config.php';
    }

    public function __construct() {
        class_alias(__CLASS__, 'Config');

        $config = null;
        $this->f3 = \Base::instance();

        if(file_exists($this->getConfigFile())) {
            include($this->getConfigFile());
        }

        if (!$config) {
            $this->config = [];
        }else{
            $this->config = $config;
        }
        $this->setDefaults();
    }

    public function getRootDir() {

        return __DIR__.'/../..';
    }

    public function getConfig() {

        return $this->config;
    }

    public function get($key, $default = null) {
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

    public function exists($v) {
        return isset($this->config[$v]);
    }

    public function set($k, $v)
    {
        $this->config[$k] = $v;
    }

    public function getUrlbase() {
        if (!isset($this->config['urlbase'])) {
            $port = $this->f3->get('PORT');
            $this->config['urlbase'] = $this->f3->get('SCHEME').'://'.$_SERVER['SERVER_NAME'].(!in_array($port,[80,443])?(':'.$port):'').$this->f3->get('BASE');
        }
        return $this->config['urlbase'];
    }

    private function setDefaults() {

    }

    function getCommit() {
        if(!file_exists($this->getRootDir().'/.git/HEAD')) {
            return null;
        }

        $head = str_replace(["ref: ", "\n"], "", file_get_contents($this->getRootDir().'/.git/HEAD'));
        $commit = null;

        if(strpos($head, "refs/") !== 0) {
            $commit = $head;
        }

        if(file_exists($this->getRootDir().'/.git/'.$head)) {
            $commit = str_replace("\n", "", file_get_contents($this->getRootDir().'/.git/'.$head));
        }

        return substr($commit, 0, 7);
    }

    public function isSendingMail() {

        return $this->get('mail');
    }
}

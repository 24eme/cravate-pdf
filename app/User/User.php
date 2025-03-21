<?php

namespace User;

use Config\Config;

class User
{
    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new User();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(Config::getInstance()->get('session.name')) {
            session_name(Config::getInstance()->get('session.name'));
            session_start();
            if (!$this->getUserId() && Config::getInstance()->get('session.user_id_defaut')) {
                $_SESSION[Config::getInstance()->get('session.user_id')] = Config::getInstance()->get('session.user_id_defaut');
            }
        }
    }

    public function isAdmin() {
        if(!Config::getInstance()->get('session.name')) {
            return true;
        }

        if(!array_key_exists(Config::getInstance()->get('session.admin_key'), $_SESSION)) {
            return false;
        }

        $adminValue = $_SESSION[Config::getInstance()->get('session.admin_key')];

        if(is_array($adminValue)) {

            return in_array(Config::getInstance()->get('session.admin_value'), $adminValue);
        }

        return $adminValue == Config::getInstance()->get('session.admin_value');
    }

    public function getUserId() {
        if(!Config::getInstance()->get('session.name')) {
            return "ADMIN";
        }

        return (isset($_SESSION[Config::getInstance()->get('session.user_id')])) ? $_SESSION[Config::getInstance()->get('session.user_id')] : null;
    }
}

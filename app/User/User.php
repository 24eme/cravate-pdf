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
        }
    }

    public function isAdmin() {
        if(!Config::getInstance()->get('session.name')) {
            return true;
        }

        return $_SESSION[Config::getInstance()->get('session.credentials_key')];
    }

    public function getUserId() {
        if(!Config::getInstance()->get('session.name')) {
            return "ADMIN";
        }

        return $_SESSION[Config::getInstance()->get('session.user_id')];
    }
}

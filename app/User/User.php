<?php

namespace User;

use Prefab;
use Config\Config;

class User extends Prefab
{
    public static function isSessionExist() {

        return array_key_exists(Config::getInstance()->get('session.name'), $_COOKIE);
    }

    public function __construct() {
        if(Config::getInstance()->get('session.name') && self::isSessionExist()) {
            session_name(Config::getInstance()->get('session.name'));
            session_start();
        }
    }

    public function isAdmin() {
        if(!Config::getInstance()->get('session.name')) {
            return true;
        }

        if(session_status() != PHP_SESSION_ACTIVE) {
            return false;
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
        if(session_status() != PHP_SESSION_ACTIVE) {
            return false;
        }

        return $_SESSION[Config::getInstance()->get('session.user_id')];
    }
}

<?php

use Config\Config;
use DB\DBManager;

$f3 = Base::instance();

if(getenv("DEBUG")) {
    $f3->set('DEBUG', getenv("DEBUG"));
}
$f3->set('UI', '../views/');
$f3->set('URLBASE', Config::getInstance()->getUrlbase());
$f3->set('VERSION', Config::getInstance()->getCommit());
$f3->set('config', Config::getInstance());

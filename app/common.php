<?php

use Config\Config;
use User\User;
use Emails\Email;

$f3 = Base::instance();

if(getenv("DEBUG")) {
    $f3->set('DEBUG', getenv("DEBUG"));
}
$f3->set('ROOT', __DIR__.'/../');
$f3->set('UI', '../views/');
$f3->set('URLBASE', Config::getInstance()->getUrlbase());
$f3->set('VERSION', Config::getInstance()->getCommit());
$f3->set('config', Config::getInstance());

$mailConf = Config::getInstance()->get('mail');

if ($mailConf) {
    $smtp = new SMTP(
        $mailConf['host'],
        $mailConf['port'],
        $mailConf['scheme'],
        $mailConf['user'],
        $mailConf['pass']
    );
    $f3->set('mail', new Email($smtp, $f3->get('UI').'emails/'));
}

$f3->set('user', User::getInstance());

<?php

use Config\Config;
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

// intégration session externe
if (Config::getInstance()->get('session')) {
    session_name($f3->get('config')->get('session.name'));
    session_start();

    $credentials = (isset($_SESSION[$f3->get('config')->get('session.credentials_key')]) && is_array($_SESSION[$f3->get('config')->get('session.credentials_key')]))
        ? $_SESSION[$f3->get('config')->get('session.credentials_key')]
        : [];

    $_SESSION['is_admin'] = in_array("admin", $credentials);

    if (! $_SESSION['is_admin'] && in_array($f3->get('config')->get('session.default_is_admin'), [1, true, 'true', '1'], true)) {
        $_SESSION['is_admin'] = true;
    }

    if (! isset($_SESSION['etablissement_id']) && $f3->get('config')->get('session.default_etablissement_id')) {
        $_SESSION['etablissement_id'] = $f3->get('config')->get('session.default_etablissement_id');
    }

    if (isset($_SESSION['etablissement_id']) === false) {
        header('Location: /');
        exit;
    }
}

<?php

$f3 = require __DIR__.'/../vendor/fatfree-core/base.php';
$f3->set('AUTOLOAD', '../app/');

require __DIR__.'/../app/common.php';
require __DIR__.'/../app/routes.php';

$f3->run();

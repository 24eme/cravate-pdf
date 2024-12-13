<?php

// INIT
$f3 = require __DIR__.'/../vendor/fatfree-core/base.php';
$f3->set('AUTOLOAD', __DIR__.'/../app/');
$f3->set('DEBUG', 3);

$test = new Test();

return $test;

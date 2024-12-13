<?php

use Controllers\Main;

$f3 = Base::instance();

$f3->route('GET @index: /', Main::class.'->index');
$f3->route('GET @setup: /setup', Main::class.'->setup');

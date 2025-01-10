<?php

use Controllers\Main;
use Controllers\Record;

$f3 = Base::instance();

$f3->route('GET @index: /', Main::class.'->index');
$f3->route('GET @setup: /setup', Main::class.'->setup');
$f3->route('GET @form: /form', Main::class.'->form');
$f3->route('POST @fill: /fill', Main::class.'->fill');
$f3->route('GET @download: /download', Main::class.'->download');

$f3->route('GET @records: /records', Record::class.'->index');
$f3->route('GET|POST @record_attchment: /record/@record/submission/@submission/attachment', Record::class.'->attachment');

<?php

use Controllers\Main;
use Controllers\Record;

$f3 = Base::instance();

$f3->route('GET @index: /', Main::class.'->index');
$f3->route('GET @setup: /setup', Main::class.'->setup');
$f3->route('GET @form: /form', Main::class.'->form');
$f3->route('POST @fill: /fill', Main::class.'->fill');

$f3->route('GET @records: /records', Record::class.'->index');
$f3->route('GET @record_submissions: /record/@record/submissions', Record::class.'->submissions');
$f3->route('GET|POST @record_attachment: /record/@record/submission/@submission/attachment', Record::class.'->attachment');
$f3->route('GET @record_submission: /record/@record/submission/@submission/display', Record::class.'->submission');
$f3->route('GET @record_submission_getfile: /record/@record/submission/@submission/getfile', Record::class.'->getfile');

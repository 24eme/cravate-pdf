<?php

use Controllers\MainController;

$f3 = Base::instance();

$f3->route('GET @setup: /setup', MainController::class.'->setup');
$f3->route('GET @records: /', MainController::class.'->index');
$f3->route('GET @record_submissions: /record/@record/submissions', MainController::class.'->submissions');
$f3->route('GET @record_submission_new: /record/@record/submission/new', MainController::class.'->new');
$f3->route('GET|POST @record_edit: /record/@record/submission/@submission/edit', MainController::class.'->edit');
$f3->route('POST @record_fill: /record/@record/submission/@submission/fill', MainController::class.'->fill');
$f3->route('GET|POST @record_attachment: /record/@record/submission/@submission/attachment', MainController::class.'->attachment');
$f3->route('GET|POST @record_validation: /record/@record/submission/@submission/validation', MainController::class.'->validation');
$f3->route('GET @record_submission: /record/@record/submission/@submission/display', MainController::class.'->submission');
$f3->route('GET @record_submission_getfile: /record/@record/submission/@submission/getfile', MainController::class.'->getfile');
$f3->route('POST @record_submission_updatestatus: /record/@record/submission/@submission/status', MainController::class.'->updatestatus');

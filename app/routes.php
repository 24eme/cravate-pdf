<?php

use Controllers\MainController;

$f3 = Base::instance();

$f3->route('GET @setup: /setup', MainController::class.'->setup');
$f3->route('GET @procedures: /', MainController::class.'->index');
$f3->route('GET @procedure_submissions: /procedure/@procedure/submissions', MainController::class.'->submissions');
$f3->route('GET @procedure_submission_new: /procedure/@procedure/submission/new', MainController::class.'->new');
$f3->route('GET|POST @procedure_edit: /procedure/@procedure/submission/@submission/edit', MainController::class.'->edit');
$f3->route('POST @procedure_fill: /procedure/@procedure/submission/@submission/fill', MainController::class.'->fill');
$f3->route('GET|POST @procedure_attachment: /procedure/@procedure/submission/@submission/attachment', MainController::class.'->attachment');
$f3->route('GET|POST @procedure_validation: /procedure/@procedure/submission/@submission/validation', MainController::class.'->validation');
$f3->route('GET @procedure_submission: /procedure/@procedure/submission/@submission/submission', MainController::class.'->submission');
$f3->route('GET @procedure_submission_getfile: /procedure/@procedure/submission/@submission/getfile', MainController::class.'->getfile');
$f3->route('POST @procedure_submission_updatestatus: /procedure/@procedure/submission/@submission/status', MainController::class.'->updatestatus');

<?php

use Controllers\ProcedureController;

$f3 = Base::instance();

$f3->route('GET @setup: /setup', ProcedureController::class.'->setup');
$f3->route('GET @procedures: /', ProcedureController::class.'->index');
$f3->route([
    'GET @procedure_submissions: /procedure/@procedure/submissions',
    'GET @procedure_usersubmissions: /procedure/@procedure/submissions/@user',
], ProcedureController::class.'->submissions');
$f3->route('GET @procedure_submission_new: /procedure/@procedure/submission/new', ProcedureController::class.'->new');
$f3->route('GET|POST @procedure_edit: /procedure/@procedure/submission/@submission/edit', ProcedureController::class.'->edit');
$f3->route('GET|POST @procedure_attachment: /procedure/@procedure/submission/@submission/attachment', ProcedureController::class.'->attachment');
$f3->route('GET|POST @procedure_validation: /procedure/@procedure/submission/@submission/validation', ProcedureController::class.'->validation');
$f3->route('GET @procedure_submission: /procedure/@procedure/submission/@submission/submission', ProcedureController::class.'->submission');
$f3->route('GET @procedure_submission_downloadpdf: /procedure/@procedure/submission/@submission/downloadpdf', ProcedureController::class.'->downloadpdf');
$f3->route('GET @procedure_submission_downloadattachment: /procedure/@procedure/submission/@submission/downloadattachment', ProcedureController::class.'->downloadattachment');
$f3->route('POST @procedure_submission_updatestatus: /procedure/@procedure/submission/@submission/status', ProcedureController::class.'->updatestatus');

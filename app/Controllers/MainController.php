<?php

namespace Controllers;

use Base;
use Flash;
use View;
use Web;

use Config;

use Records\Records;
use Records\Submission;
use Records\Record;

use Steps\Steps;
use Steps\RecordsSteps;

use Emails\Email;

use PDF\PDFForm;
use PDF\PDFtk;
use Validator\Validation;

use Exception;
use Scrape\Declarvin;

class MainController
{
    private ?Record $record = null;
    private ?Submission $submission = null;

    public function beforeroute(Base $f3)
    {
        if ($f3->get('PARAMS.record')) {
            $this->record = new Record($f3->get('PARAMS.record'));
        }
        if ($f3->get('PARAMS.submission')) {
            $this->submission = $this->record->find($f3->get('PARAMS.submission'));
            $f3->set('steps', new Steps(new RecordsSteps($this->record, $this->submission)));
        }
    }

    public function index(Base $f3)
    {
        $f3->set('records', Records::getRecords());
        $f3->set('content', 'record/list.html.php');

        echo View::instance()->render('layout.html.php');
    }

    public function submissions(Base $f3)
    {
        $record = new Record($f3->get('PARAMS.record'));
        $statusFilter = $f3->get('GET.status') ?? Submission::STATUS_SUBMITTED;

        if (in_array($statusFilter, array_merge([Submission::STATUS_TOUS], Submission::$allStatus)) === false) {
            return $f3->error(404, "Status not found");
        }

        $f3->set('record', $record);
        $f3->set('content', 'record/submissions.html.php');
        $f3->set('statusFilter', $statusFilter);
        $f3->set('statusThemeColor', Submission::$statusThemeColor);

        echo View::instance()->render('layout.html.php');
    }

    public function new(Base $f3)
    {
        $record = Record::getInstance($f3->get('PARAMS.record'));
        $submission = $record->create();

        $submission->save();

        $f3->reroute(['record_edit', ['record' => $record->name, 'submission' => $submission->id]]);
    }

    public function edit(Base $f3)
    {
        $record = Record::getInstance($f3->get('PARAMS.record'));
        $submission = $record->find($f3->get('PARAMS.submission'));

        $f3->set('record', $this->record);
        $f3->set('submission', $this->submission);

        if (!$this->submission->isEditable()) {
            return $f3->error(403, "Submission not editable");
        }
        if (!$_SESSION['is_admin'] && !$this->submission->isAuthor($_SESSION['etablissement_id'])) {
            return $f3->error(403, "Etablissement forbidden");
        }
        $f3->set('content', 'record/form.html.php');

        echo View::instance()->render('layout.html.php');
    }

    public function fill(Base $f3)
    {
        $record = Record::getInstance($f3->get('PARAMS.record'));
        $submission = $record->find($f3->get('PARAMS.submission'));

        $postData = $f3->get('POST');
        $cleanedData = $postData;

        if (!$submission->isEditable()) {
            return $f3->error(403, "Submission not editable");
        }
        if (!$_SESSION['is_admin'] && !$submission->isAuthor($_SESSION['etablissement_id'])) {
            return $f3->error(403, "Etablissement forbidden");
        }

        $validator = new Validation();
        $valid = $validator->validate($cleanedData, $record->getValidation());

        if ($valid === false) {
            Flash::instance()->setKey('form-error', $validator->getErrors());
            \Helpers\Old::instance()->set($cleanedData);

            return $f3->reroute(['record_edit', ['record' => $record->name, 'submission' => $submission->id]]);
        }


        $submission->setDatas($cleanedData);
        $submission->save();

        return $f3->reroute(['record_attachment', ['record' => $record->name, 'submission' => $submission->id]]);
    }

    public function attachment(Base $f3)
    {
        $record = Record::getInstance($f3->get('PARAMS.record'));
        $submission = $record->find($f3->get('PARAMS.submission'));

        if (!$submission->isEditable()) {
            return $f3->error(403, "Submission not editable");
        }

        if (!$_SESSION['is_admin'] && !$submission->isAuthor($_SESSION['etablissement_id'])) {
            return $f3->error(403, "Etablissement forbidden");
        }

        if ($f3->get('VERB') === 'POST') {
            foreach($_FILES as $name => $file) {
                if ($file['error'] != UPLOAD_ERR_OK) {
                    continue;
                }
                move_uploaded_file($file['tmp_name'], $submission->getAttachmentsPath() . $name.".".pathinfo($file['name'])['extension']);
            }

            return $f3->reroute(['record_validation', [
               'record' => $record->name,
               'submission' => $submission->id,
            ]]);
        }

        $f3->set('record', $record);
        $f3->set('submission', $submission);
        $f3->set('content', 'record/attachmentForm.html.php');

        $f3->get('steps')->activate(RecordsSteps::STEP_ANNEXES);
        echo View::instance()->render('layout.html.php');
    }

    public function validation(Base $f3)
    {
        $record = Record::getInstance($f3->get('PARAMS.record'));
        $submission = $record->find($f3->get('PARAMS.submission'));
        if (!$submission->isEditable()) {
            return $f3->error(403, "Submission not editable");
        }
        if (!$_SESSION['is_admin'] && !$submission->isAuthor($_SESSION['etablissement_id'])) {
            return $f3->error(403, "Etablissement forbidden");
        }

        $validator = new Validation();
        $validator->checkSubmission($submission);

        if ($f3->get('VERB') === 'POST') {
            if ($validator->hasErrors()) {
                return $f3->reroute('@record_validation');
            }

            $submission->setStatus(Submission::STATUS_SUBMITTED);
            $submission->save();
            return $f3->reroute(['record_submission', [
                        'record' => $record->name,
                        'submission' => $submission->id
                    ]]);
        }

        $f3->get('steps')->activate(RecordsSteps::STEP_VALIDATION);
        $f3->set('record', $record);
        $f3->set('submission', $submission);
        $f3->set('validator', $validator);
        $f3->set('content', 'record/validation.html.php');

        $f3->set('readonly', true);

        echo View::instance()->render('layout.html.php');
    }

    public function submission(Base $f3)
    {
        $record = Record::getInstance($f3->get('PARAMS.record'));
        $submission = $record->find($f3->get('PARAMS.submission'));

        if (!$_SESSION['is_admin'] && !$submission->isAuthor($_SESSION['etablissement_id'])) {
            return $f3->error(403, "Etablissement forbidden");
        }
        if ($submission->status == Submission::STATUS_DRAFT) {
            return $f3->reroute(['record_validation', ['record' => $record->name, 'submission' => $submission->id]]);
        }
        $f3->set('submission', $submission);
        $f3->set('content', 'record/submission.html.php');
        $f3->set('displaypdf', $f3->get('GET.pdf'));

        echo View::instance()->render('layout.html.php');
    }

    public function getfile(Base $f3)
    {
        $record = Record::getInstance($f3->get('PARAMS.record'));
        $submission = $record->find($f3->get('PARAMS.submission'));

        if (!$_SESSION['is_admin'] && !$submission->isAuthor($_SESSION['etablissement_id'])) {
            return $f3->error(403, "Etablissement forbidden");
        }

        $disposition = $f3->get('GET.disposition');

        $file = realpath($submission->path.$f3->get('GET.file'));
        $path = realpath($submission->path);

        // si pas le path dans le chemin, on le rajoute
        if (strpos($file, $path) !== 0) {
            $file = $path.$file;
        }

        if (is_file($file) === false) {
            return $f3->error(404, "File not found");
        }

        if (!in_array($disposition, ['attachment', 'inline'])) {
            return $f3->error(404, "Disposition < $disposition > not allowed");
        }

        $download = $disposition === 'attachment';

        if(preg_match('/\.url$/', $file)) {

            return $f3->reroute(file_get_contents($file));
        }

        return Web::instance()->send($file, null, 0, $download, basename($file));
    }

    public function updatestatus(Base $f3)
    {
        if (!$_SESSION['is_admin']) {
            return $f3->error(403, "Only admin");
        }
        $record = Record::getInstance($f3->get('PARAMS.record'));
        $submission = $record->find($f3->get('PARAMS.submission'));

        $newStatus = $f3->get('POST.status');
        $comment = $f3->get('POST.comment');

        if (!in_array($newStatus, Submission::$allStatus)) {
            return $f3->error(404, "Status < $newStatus > not allowed");
        }
        $submission->setStatus($newStatus, $comment);
        $submission->save();
        /* try { */
        /*     $f3->get('mail') */
        /*        ->headers(['From' => Config::getInstance()->get('mail.host'), 'To' => $submission->getDatas('EMAIL'), 'Subject' => 'Changement de Status de votre dossier']) */
        /*        ->send('chgtstatus.eml', compact('submission')); */
        /* } catch (Exception $e) { } */

        return $f3->reroute(['record_submission', ['record' => $record->name, 'submission' => $submission->id]]);
    }
}

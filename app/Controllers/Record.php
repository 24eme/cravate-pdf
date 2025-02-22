<?php

namespace Controllers;

use Base;
use Flash;
use View;
use Web;

use Config;

use Records\Records;
use Records\Submission;
use Records\Record as Rec;

use Steps\Steps;
use Steps\RecordsSteps;

use Emails\Email;

use PDF\PDFForm;
use PDF\PDFtk;
use Validator\Validation;

use Exception;
use Scrape\Declarvin;

class Record
{
    private ?Rec $record = null;
    private ?Submission $submission = null;

    public function beforeroute(Base $f3)
    {
        if ($f3->get('PARAMS.record')) {
            $this->record = new Rec($f3->get('PARAMS.record'));
            $this->submission = new Submission($this->record, $f3->get('PARAMS.submission'));

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
        $record = new Rec($f3->get('PARAMS.record'));
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
        $dirname = (new \DateTime())->format('YmdHis')."_".$_SESSION['etablissement_id']."_RS_BROUILLON";
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $dirname);

        if($record->getConfigItem('initDossier')) {
            shell_exec($record->getConfigItem('initDossier')." $submission->path");
        }

        $submission = new Submission($record, $dirname);

        $f3->set('record', $record);
        $f3->set('submission', $submission);
        $f3->set('content', 'record/form.html.php');

        echo View::instance()->render('layout.html.php');
    }

    public function edit(Base $f3)
    {
        $f3->set('record', $this->record);
        $f3->set('submission', $this->submission);
        $f3->set('content', 'record/form.html.php');

        echo View::instance()->render('layout.html.php');
    }

    public function fill(Base $f3)
    {
        $postData = $f3->get('POST');

        if (isset($postData['file']) === false) {
            $f3->error(403, 'Missing file parameter');
        }

        $pdffile = $postData['file'];
        $pdfForm = new PDFForm($pdffile);

        $cleanedData = PDFtk::cleanData($pdfForm->getFields(), $postData);

        try {
            $submission = new Submission($this->record, $f3->get('POST.submission'));

            $validator = new Validation();
            $valid = $validator->validate($cleanedData, $this->record->getValidation());

            if ($valid === false) {
                Flash::instance()->setKey('form-error', $validator->getErrors());
                return $submission->name
                    ? $f3->reroute(['record_edit', ['record' => $this->record->name, 'submission' => $submission->name]])
                    : $f3->reroute(['record_submission_new', ['record' => $this->record->name]]);
            }

            $outputFile = PDFTk::fillForm($pdffile, $cleanedData);
            $submission->save($cleanedData, $outputFile);

            return $f3->reroute(['record_attachment', ['record' => $this->record->name, 'submission' => $submission->name]]);
        } catch(\Exception $e) {
            return $f3->reroute('@records');
        }
    }

    public function attachment(Base $f3)
    {
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $f3->get('PARAMS.submission'));

        if ($f3->get('VERB') === 'POST') {
            foreach($_FILES as $name => $file) {
                if ($file['error'] != UPLOAD_ERR_OK) {
                    continue;
                }
                $newUpload++;
                move_uploaded_file($file['tmp_name'], $submission->getAttachmentsPath() . $name.".".pathinfo($file['name'])['extension']);
            }

            return $f3->reroute(['record_validation', [
               'record' => $record->name,
               'submission' => $submission->name,
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
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $f3->get('PARAMS.submission'));

        if ($f3->get('VERB') === 'POST') {
            $submission->setStatus(Submission::STATUS_SUBMITTED);
            return $f3->reroute(['record_submission', [
                        'record' => $record->name,
                        'submission' => $submission->name
                    ]]);
        }

        $f3->get('steps')->activate(RecordsSteps::STEP_VALIDATION);
        $f3->set('record', $record);
        $f3->set('submission', $submission);
        $f3->set('content', 'record/validation.html.php');

        $f3->set('readonly', true);

        echo View::instance()->render('layout.html.php');
    }

    public function submission(Base $f3)
    {
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $f3->get('PARAMS.submission'));
        $f3->set('submission', $submission);
        $f3->set('content', 'record/submission.html.php');
        $f3->set('displaypdf', $f3->get('GET.pdf'));

        echo View::instance()->render('layout.html.php');
    }

    public function getfile(Base $f3)
    {
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $f3->get('PARAMS.submission'));
        $file = $submission->path.$f3->get('GET.file');
        $disposition = $f3->get('GET.disposition');

        if (file_exists($file) === false) {
            return $f3->error(404, "File not found");
        }

        if (!in_array($disposition, ['attachment', 'inline'])) {
            return $f3->error(404, "Disposition < $disposition > not allowed");
        }

        $download = $disposition === 'attachment';

        return Web::instance()->send($file, null, 0, $download, basename($file));
    }

    public function updatestatus(Base $f3)
    {
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $f3->get('PARAMS.submission'));
        $newStatus = $f3->get('POST.status');
        $comment = $f3->get('POST.comment');

        if (!in_array($newStatus, Submission::$allStatus)) {
            return $f3->error(404, "Status < $newStatus > not allowed");
        }
        $submission->setStatus($newStatus, $comment);

        /* try { */
        /*     $f3->get('mail') */
        /*        ->headers(['From' => Config::getInstance()->get('mail.host'), 'To' => $submission->getDatas('EMAIL'), 'Subject' => 'Changement de Status de votre dossier']) */
        /*        ->send('chgtstatus.eml', compact('submission')); */
        /* } catch (Exception $e) { } */

        return $f3->reroute(['record_submission', ['record' => $record->name, 'submission' => $submission->name]]);
    }
}

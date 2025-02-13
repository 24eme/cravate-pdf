<?php

namespace Controllers;

use Base;
use View;
use Web;

use Config;

use Records\Records;
use Records\Submission;
use Records\Record as Rec;

use Steps\Steps;
use Steps\RecordsSteps;

use Emails\Email;

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

        if (!in_array($statusFilter, [Submission::STATUS_TOUS]+Submission::$allStatus)) {
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
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record);

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

        if ($f3->get('GET.reload')) {
            $infos = Declarvin::instance()->retrieveInfo("CIVP001234");
            $this->submission->loadDatas($infos);
        }

        echo View::instance()->render('layout.html.php');
    }

    public function attachment(Base $f3)
    {
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $f3->get('PARAMS.submission'));


        if ($f3->get('VERB') === 'POST') {
            $attachment = $_FILES['attachment'];
            $f3->set('uploadError', true);
            if ($attachment['error'] == UPLOAD_ERR_OK) {
                if (move_uploaded_file($attachment['tmp_name'], $submission->getAttachmentsPath() . basename($attachment['name']))) {
                    $f3->get('mail')
                       ->headers(['From' => Config::getInstance()->get('mail.host'), 'To' => $submission->getDatas('EMAIL'), 'Subject' => 'Nouvelle pièce'])
                       ->send('newattachment.eml', compact('submission', 'f3'));

                    return $f3->reroute(['record_validation', [
                        'record' => $record->name,
                        'submission' => $submission->name,
                    ]]);
                }
            }
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

        if (!in_array($newStatus, Submission::$allStatus)) {
            return $f3->error(404, "Status < $newStatus > not allowed");
        }
        $submission->setStatus($newStatus);

        try {
            $f3->get('mail')
               ->headers(['From' => Config::getInstance()->get('mail.host'), 'To' => $submission->getDatas('EMAIL'), 'Subject' => 'Changement de Status de votre dossier'])
               ->send('chgtstatus.eml', compact('submission'));
        } catch (Exception $e) { }

        return $f3->reroute(['record_submission', ['record' => $record->name, 'submission' => $submission->name]]);
    }
}

<?php

namespace Controllers;

use Base;
use View;
use Web;

use Records\Records;
use Records\Submission;
use Records\Record as Rec;

class Record
{
    public function index(Base $f3)
    {
        $f3->set('records', Records::getRecords());
        $f3->set('content', 'record/list.html.php');

        echo View::instance()->render('layout.html.php');
    }

    public function submissions(Base $f3)
    {
        $record = new Rec($f3->get('PARAMS.record'));
        $statusFilter = $f3->get('GET.status');
        if ($statusFilter && !in_array($statusFilter, Submission::$allStatus)) {
            return $f3->error(404, "Status not found");
        }
        $f3->set('record', $record);
        $f3->set('content', 'record/submissions.html.php');
        $f3->set('statusFilter', $statusFilter);
        $f3->set('statusThemeColor', Submission::$statusThemeColor);

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
                    return $f3->reroute('records');
                }
            }
        }
        $f3->set('submission', $submission);
        $f3->set('content', 'record/attachmentForm.html.php');

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

        if (!in_array($disposition, ['attachment', 'inline'])) {
            return $f3->error(404, "Disposition < $disposition > not allowed");
        }

        if (file_exists($file)) {
            $mime = mime_content_type($file);
            if (!$mime) {
                return $f3->error(500, "Mime type undefined");
            }
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mime);
            header('Content-Disposition: '.$disposition.'; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: '.filesize($file));
            return readfile($file);
        } else {
            return $f3->error(404, "File not found");
        }
    }
}

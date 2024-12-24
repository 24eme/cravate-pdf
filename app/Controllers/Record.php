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
}

<?php

namespace Controllers;

use Base;
use Flash;
use View;
use Web;
use PDF\PDFForm;
use PDF\PDFtk;
use Records\Record;
use Records\Submission;
use Steps\Steps;
use Validator\Validation;

class Main
{
    public function index(Base $f3)
    {

        return $f3->reroute('@setup');
    }

    public function setup(Base $f3)
    {
        $f3->set('content', 'main/setup.html.php');

        echo View::instance()->render('layout.html.php');
    }

    public function form(Base $f3)
    {
        $pdf = $f3->get('GET.pdf');

        if ($f3->get('GET.record')) {
            try {
                $record = new Record($f3->get('GET.record'));
            } catch(\Exception $e) {
                return $f3->error(404, $e->getMessage());
            }
            $f3->set('record', $record);
            $pdf = $record->pdf;
        }

        if (is_null($pdf) || is_file($pdf) === false) {
            return $f3->error(404, "Invalid file");
        }

        $pdfForm = new PDFForm($pdf);

        $f3->set('pdfForm', $pdfForm);
        $f3->set('content', 'main/form.html.php');

        $f3->get('steps')->activate(Steps::STEP_FORM);
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

        if ($f3->get('GET.record')) {
            try {
                $record = new Record($f3->get('GET.record'));
                $submission = new Submission($record);

                $validator = new Validation();
                $valid = $validator->validate($cleanedData, $record->getValidation());

                if ($valid === false) {
                    Flash::instance()->setKey('form-error', $validator->getErrors());
                    return $f3->reroute(['record_edit', ['record' => $record->name, 'submission' => $submission->name]]);
                }

                $outputFile = PDFTk::fillForm($pdffile, $cleanedData);

                $submission->save($outputFile);
                if ($submission->getAttachmentNeeded()) {
                    return $f3->reroute(['record_attachment', ['record' => $record->name, 'submission' => $submission->name]]);
                } else {
                    return $f3->reroute('records');
                }
            } catch(\Exception $e) {
                return $f3->reroute('@records');
            }
        }

        $outputFile = PDFTk::fillForm($pdffile, $cleanedData);

        Web::instance()->send($outputFile['pdf'], 'application/pdf', null, true);
        unlink($outputFile['pdf']);
        unlink($outputFile['xfdf']);
    }
}

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
use stdClass;

class Record
{
    private ?Rec $record = null;
    private ?Submission $submission = null;
    private stdClass $user;

    public function beforeroute(Base $f3)
    {
        if ($f3->get('PARAMS.record')) {
            $this->record = new Rec($f3->get('PARAMS.record'));
            $this->submission = new Submission($this->record, $f3->get('PARAMS.submission'));

            $f3->set('steps', new Steps(new RecordsSteps($this->record, $this->submission)));

            $this->user = new stdClass();
            $this->user->isAdmin = $_SESSION['is_admin'] ?? false;
            $this->user->etablissement = $this->user->isAdmin === true ? null : $_SESSION['etablissement_id'];
        }
    }

    /**
     * Methode: GET
     * Liste les types de dossier
     */
    public function index(Base $f3)
    {
        $f3->set('records', Records::getRecords());
        $f3->set('content', 'record/list.html.php');

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET
     * Liste les dossiers soumis pour un type donné
     */
    public function submissions(Base $f3)
    {
        $status = $f3->get('GET.status') ?? Submission::STATUS_SUBMITTED;

        if (in_array($status, array_merge([Submission::STATUS_TOUS], Submission::$allStatus)) === false) {
            return $f3->error(404, "Status not found");
        }

        $submissions = $this->record->getSubmissions($status, $this->user->etablissement);
        $countByStatus = $this->record->countByStatus($this->user->etablissement);

        $f3->set('record', $this->record);
        $f3->set('submissions', $submissions);
        $f3->set('submissionsByStatus', $countByStatus);
        $f3->set('status', $status);
        $f3->set('content', 'record/submissions.html.php');
        $f3->set('statusThemeColor', Submission::$statusThemeColor);

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET
     * Créé un nouveau dossier
     */
    public function new(Base $f3)
    {
        if (isset($_SESSION['datetime']) === false) {
            $_SESSION['datetime'] = (new \DateTime())->format('YmdHis');
        }

        $dirname = $_SESSION['datetime'];
        $dirname .= $this->user->isAdmin ? "_ADMIN_" : "_".$this->user->etablissement."_";
        $dirname .= "_RS_BROUILLON";

        $submission = new Submission($this->record, $dirname);

        if($this->user->isAdmin === false && $this->record->getConfigItem('initDossier')) {
            shell_exec(
                escapeshellcmd($this->record->getConfigItem('initDossier')." $submission->path")
            );
        }

        // On recharge le dossier pour prendre en compte le json télécharger juste avant
        $submission = new Submission($this->record, $dirname);

        $f3->set('record', $this->record);
        $f3->set('submission', $submission);
        $f3->set('content', 'record/form.html.php');

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET
     * Edite un dossier
     */
    public function edit(Base $f3)
    {
        $f3->set('record', $this->record);
        $f3->set('submission', $this->submission);

        if (! $this->submission->isEditable()) {
            return $f3->error(403, "Submission not editable");
        }

        if ($this->user->isAdmin === false && !$this->submission->isAuthor($this->user->etablissement)) {
            return $f3->error(403, "Etablissement forbidden");
        }

        $f3->set('content', 'record/form.html.php');

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: POST
     * Prends les informations du form, les enregistrent dans l'objet et rempli le pdf
     */
    public function fill(Base $f3)
    {
        $postData = $f3->get('POST');

        if (isset($postData['file']) === false) {
            $f3->error(403, 'Missing file parameter');
        }

        $pdffile = $postData['file'];
        $formFields = $this->record->getConfigItem('form');

        $cleanedData = Validation::cleanData($formFields, $postData);

        try {
            $submission = new Submission($this->record, $f3->get('POST.submission'));
            $cleanedData = array_merge($cleanedData, $submission->getDisabledFields());

            if (! $submission->isEditable()) {
                return $f3->error(403, "Submission not editable");
            }

            if ($this->user->isAdmin === false && !$submission->isAuthor($this->user->etablissement)) {
                return $f3->error(403, "Etablissement forbidden");
            }

            $validator = new Validation();
            $valid = $validator->validate($cleanedData, $this->record->getValidation());

            if ($valid === false) {
                Flash::instance()->setKey('form-error', $validator->getErrors());
                \Helpers\Old::instance()->set($cleanedData);

                return $submission->name
                    ? $f3->reroute(['record_edit', ['record' => $this->record->name, 'submission' => $submission->name]])
                    : $f3->reroute(['record_submission_new', ['record' => $this->record->name]]);
            }

            unset($_SESSION['datetime']);

            $outputFile = PDFTk::fillForm($pdffile, $cleanedData);
            $submission->save($cleanedData, $outputFile);

            return $f3->reroute(['record_attachment', ['record' => $this->record->name, 'submission' => $submission->name]]);
        } catch(\Exception $e) {
            return $f3->reroute('@records');
        }
    }

    /**
     * Methode: GET
     * Ajoute un fichier complémentaire au dossier
     */
    public function attachment(Base $f3)
    {
        if (! $this->submission->isEditable()) {
            return $f3->error(403, "Submission not editable");
        }

        if ($this->user->isAdmin === false && ! $this->submission->isAuthor($this->user->etablissement)) {
            return $f3->error(403, "Etablissement forbidden");
        }

        if ($f3->get('VERB') === 'POST') {
            foreach($_FILES as $name => $file) {
                if ($file['error'] != UPLOAD_ERR_OK) {
                    continue;
                }
                move_uploaded_file($file['tmp_name'], $this->submission->getAttachmentsPath() . $name.".".pathinfo($file['name'])['extension']);
            }

            return $f3->reroute(['record_validation', [
               'record' => $this->record->name,
               'submission' => $this->submission->name,
            ]]);
        }

        $f3->set('record', $this->record);
        $f3->set('submission', $this->submission);
        $f3->set('content', 'record/attachmentForm.html.php');

        $f3->get('steps')->activate(RecordsSteps::STEP_ANNEXES);
        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET, POST
     * Page de validation du dossier
     */
    public function validation(Base $f3)
    {
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $f3->get('PARAMS.submission'));
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
            return $f3->reroute(['record_submission', [
                        'record' => $record->name,
                        'submission' => $submission->name
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

    /**
     * Methode: GET
     * Page de visualisation d'un dossier
     */
    public function submission(Base $f3)
    {
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $f3->get('PARAMS.submission'));
        if (!$_SESSION['is_admin'] && !$submission->isAuthor($_SESSION['etablissement_id'])) {
            return $f3->error(403, "Etablissement forbidden");
        }
        if ($submission->status == Submission::STATUS_DRAFT) {
            return $f3->reroute(['record_validation', ['record' => $record->name, 'submission' => $submission->name]]);
        }
        $f3->set('submission', $submission);
        $f3->set('content', 'record/submission.html.php');
        $f3->set('displaypdf', $f3->get('GET.pdf'));

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET
     * Télécharge un fichier d'un dossier
     */
    public function getfile(Base $f3)
    {
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $f3->get('PARAMS.submission'));

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

    /**
     * Methode: POST
     * Met à jour le status d'un dossier
     */
    public function updatestatus(Base $f3)
    {
        if (!$_SESSION['is_admin']) {
            return $f3->error(403, "Only admin");
        }
        $record = new Rec($f3->get('PARAMS.record'));
        $submission = new Submission($record, $f3->get('PARAMS.submission'));
        $newStatus = $f3->get('POST.status');
        $comment = $f3->get('POST.comment');

        if (!in_array($newStatus, Submission::$allStatus)) {
            return $f3->error(404, "Status < $newStatus > not allowed");
        }
        $submission->setStatus($newStatus, $comment);

        if ($f3->exists('mail')) {
            try {
                $f3->get('mail')
                   ->headers(['From' => Config::getInstance()->get('mail.host'), 'To' => $submission->getDatas('EMAIL'), 'Subject' => 'Changement de Status de votre dossier'])
                   ->send('chgtstatus.eml', compact('submission'));
            } catch (Exception $e) {
                // log message
            }
        }

        return $f3->reroute(['record_submission', ['record' => $record->name, 'submission' => $submission->name]]);
    }
}

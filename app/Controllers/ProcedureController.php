<?php

namespace Controllers;

use Base;
use Flash;
use View;
use Web;

use Config;
use User\User;

use Model\Submission;
use Model\Procedure;

use Steps\Steps;
use Steps\ProcedureSteps;

use Emails\Email;

use PDF\PDFtk;
use Validator\Validation;

use Exception;

class ProcedureController
{
    private ?Procedure $procedure = null;
    private ?Submission $submission = null;

    public function beforeroute(Base $f3)
    {
        if ($f3->get('PARAMS.procedure')) {
            $this->procedure = new Procedure($f3->get('PARAMS.procedure'));
        }
        if ($f3->get('PARAMS.submission')) {
            $this->submission = Submission::find($this->procedure, $f3->get('PARAMS.submission'));
            $f3->set('steps', new Steps(new ProcedureSteps($this->procedure, $this->submission)));
        }
        if(isset($this->submission) && ! User::getInstance()->isAdmin() && ! $this->submission->isAuthor(User::getInstance()->getUserId())) {
            return $f3->error(403, "Etablissement forbidden");
        }
    }

    public function setup(Base $f3)
    {
        $f3->set('content', 'procedure/setup.html.php');

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET
     * Liste les types de dossier
     */
    public function index(Base $f3)
    {
        $f3->set('procedures', Procedure::getProcedures());
        $f3->set('content', 'procedure/index.html.php');

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

        $submissions = $this->procedure->getSubmissions($status, User::getInstance()->getUserId());
        $countByStatus = $this->procedure->countByStatus(User::getInstance()->getUserId());

        $f3->set('procedure', $this->procedure);
        $f3->set('submissions', $submissions);
        $f3->set('submissionsByStatus', $countByStatus);
        $f3->set('status', $status);
        $f3->set('content', 'procedure/submissions.html.php');
        $f3->set('statusThemeColor', Submission::$statusThemeColor);

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET
     * Créé un nouveau dossier
     */
    public function new(Base $f3)
    {
        $submission = Submission::create($this->procedure, User::getInstance()->getUserId());
        $submission->save();

        $f3->reroute(['procedure_edit', ['procedure' => $this->procedure->name, 'submission' => $submission->id]]);
    }

    /**
     * Methode: GET
     * Edite un dossier
     */
    public function edit(Base $f3)
    {
        if (! $this->submission->isEditable()) {
            return $f3->error(403, "Submission not editable");
        }
        $f3->set('procedure', $this->procedure);
        $f3->set('submission', $this->submission);

        $f3->set('content', 'procedure/edit.html.php');

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: POST
     * Prends les informations du form, les enregistrent dans l'objet et rempli le pdf
     */
    public function fill(Base $f3)
    {
 	    if (! $this->submission->isEditable()) {
           return $f3->error(403, "Submission not editable");
        }

        $postData = $f3->get('POST');

        $formFields = $this->procedure->getConfigItem('form');

        $cleanedData = Validation::cleanData($formFields, $postData);

        $cleanedData = array_merge($cleanedData, $this->submission->getDisabledFields());

        $validator = new Validation();
        $valid = $validator->validate($cleanedData, $this->procedure->getValidation());

        if ($valid === false) {
            Flash::instance()->setKey('form-error', $validator->getErrors());
            \Helpers\Old::instance()->set($cleanedData);

            return $f3->reroute(['procedure_edit', ['procedure' => $this->procedure->name, 'submission' => $this->submission->id]]);
        }

        $this->submission->setDatas($cleanedData);
        $this->submission->save();

        return $f3->reroute(['procedure_attachment', ['procedure' => $this->procedure->name, 'submission' => $this->submission->id]]);
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

        if ($f3->get('VERB') === 'POST') {
            foreach($_FILES as $name => $file) {
                if ($file['error'] != UPLOAD_ERR_OK) {
                    continue;
                }
                move_uploaded_file($file['tmp_name'], $this->submission->getAttachmentsPath() . $name.".".pathinfo($file['name'])['extension']);
            }

            return $f3->reroute(['procedure_validation', [
               'procedure' => $this->procedure->name,
               'submission' => $this->submission->id,
            ]]);
        }

        $f3->set('procedure', $this->procedure);
        $f3->set('submission', $this->submission);
        $f3->set('content', 'procedure/attachment.html.php');

        $f3->get('steps')->activate(ProcedureSteps::STEP_ANNEXES);
        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET, POST
     * Page de validation du dossier
     */
    public function validation(Base $f3)
    {
        if (! $this->submission->isEditable()) {
            return $f3->error(403, "Submission not editable");
        }

        $validator = new Validation();
        $validator->checkSubmission($this->submission);

        if ($f3->get('VERB') === 'POST') {
            if ($validator->hasErrors()) {
            return $f3->reroute(['procedure_validation']);
            }

            $this->submission->setStatus(Submission::STATUS_SUBMITTED);
            $this->submission->save();
            return $f3->reroute(['procedure_submission', [
                        'procedure' => $this->procedure->name,
                        'submission' => $this->submission->id
                    ]]);
        }

        $f3->get('steps')->activate(ProcedureSteps::STEP_VALIDATION);
        $f3->set('procedure', $this->procedure);
        $f3->set('submission', $this->submission);
        $f3->set('validator', $validator);
        $f3->set('content', 'procedure/validation.html.php');

        $f3->set('readonly', true);

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET
     * Page de visualisation d'un dossier
     */
    public function submission(Base $f3)
    {
        if ($this->submission->status === Submission::STATUS_DRAFT) {
            return $f3->reroute(['procedure_validation', ['procedure' => $this->procedure->name, 'submission' => $this->submission->id]]);
        }

        $f3->set('submission', $this->submission);
        $f3->set('content', 'main/submission.html.php');
        $f3->set('displaypdf', $f3->get('GET.pdf'));

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET
     * Télécharge un fichier d'un dossier
     */
    public function getfile(Base $f3)
    {
        $disposition = $f3->get('GET.disposition');

        $file = realpath($this->submission->path.$f3->get('GET.file'));
        $path = realpath($this->submission->path);

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
        if (!User::getInstance()->isAdmin()) {
            return $f3->error(403, "Only admin");
        }

        $newStatus = $f3->get('POST.status');
        $comment = $f3->get('POST.comment');

        if (!in_array($newStatus, Submission::$allStatus)) {
            return $f3->error(404, "Status < $newStatus > not allowed");
        }

        $this->submission->setStatus($newStatus, $comment);
	    $this->submission->save();

        if ($f3->exists('mail')) {
            try {
                $f3->get('mail')
                   ->headers(['From' => Config::getInstance()->get('mail.host'), 'To' => $this->submission->getDatas('EMAIL'), 'Subject' => 'Changement de Status de votre dossier'])
                   ->send('chgtstatus.eml', ['submission' => $this->submission]);
            } catch (Exception $e) {
                // log message
            }
        }

        return $f3->reroute(['procedure_submission', ['procedure' => $this->procedure->name, 'submission' => $this->submission->id]]);
    }
}

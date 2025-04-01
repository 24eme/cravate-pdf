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
use Emails\SubmissionEmails;

use PDF\PDFtk;
use Validator\Validation;
use Validator\SubmissionValidation;

use Exception;

class ProcedureController
{
    private ?string $user = null;
    private ?Procedure $procedure = null;
    private ?Submission $submission = null;

    public function beforeroute(Base $f3)
    {
        if(Config::getInstance()->get('login_link') && !User::instance()->isAdmin() && !User::instance()->getUserId()) {
            return $f3->reroute(str_replace("%service%", urlencode($f3->get('REALM')),Config::getInstance()->get('login_link')));
        }
        if ($f3->get('PARAMS.procedure')) {
            $this->procedure = new Procedure($f3->get('PARAMS.procedure'));
        }
        if ($f3->get('PARAMS.submission')) {
            $this->submission = Submission::find($this->procedure, $f3->get('PARAMS.submission')) ?: $f3->error(404, "Numéro de dépôt inconnu");
            $f3->set('steps', new Steps(new ProcedureSteps($this->procedure, $this->submission)));
        }
        if ($f3->get('PARAMS.user')) {
            $this->user = $f3->get('PARAMS.user');
        } elseif ($this->submission && $this->submission->userId) {
            $this->user = $this->submission->userId;
        }
        if(isset($this->submission) && ! User::instance()->isAdmin() && ! $this->submission->isAuthor(User::instance()->getUserId())) {
            return $f3->error(403, "Etablissement forbidden");
        }
        if (isset($this->user) && !User::instance()->isAdmin() && $this->user !== User::instance()->getUserId()) {
            $f3->error(403, "Établissement forbidden");
        }
        if(isset($this->user)) {
            $f3->set('user', $this->user);
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
        if(isset($this->user)) {
            return $f3->reroute(['procedures', ['user' => $this->user]]);
        }

        foreach(Procedure::getProcedures() as $procedure) {
            return $f3->reroute(['procedure_submissions', ['procedure' => $procedure->name]]);
        }
    }

    public function procedures(Base $f3)
    {
        $f3->set('procedures', Procedure::getProcedures());
        $f3->set('content', 'procedure/procedures.html.php');

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Url: /procedure/@procedure/submissions
     * Url: /procedure/@procedure/submissions/@user
     * Alias: @procedure_submissions
     * Alias: @procedure_usersubmissions
     * Methode: GET
     *
     * Liste les dossiers soumis pour un type donné
     */
    public function submissions(Base $f3)
    {
        if (User::instance()->isAdmin() === false) {
            if ($f3->exists('PARAMS.user') === false) {
                $f3->reroute(['procedure_usersubmissions', ['procedure' => $this->procedure->name, 'user' => User::instance()->getUserId()], ['status' => $f3->get('GET.status')] ]);
            }

            if ($f3->get('PARAMS.user') !== User::instance()->getUserId()) {
                $f3->error(403, "Établissement forbidden");
            }
        }

        $forUser = $f3->exists('PARAMS.user') ? $f3->get('PARAMS.user') : null;

        $status = $f3->get('GET.status') ?? Submission::STATUS_SUBMITTED;

        if (in_array($status, array_merge([Submission::STATUS_TOUS], Submission::$allStatus)) === false) {
            return $f3->error(404, "Status not found");
        }

        $submissions = $this->procedure->getSubmissions($status, $forUser);
        $countByStatus = $this->procedure->countByStatus($forUser);

        if(isset($this->user)) {
            $f3->set('user', $this->user);
        }

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
        if (!User::instance()->isAdmin() && isset($this->user) && $this->user !== User::instance()->getUserId()) {
            $f3->error(403, "Établissement forbidden");
        }

        $submission = Submission::create($this->procedure, $this->user);
        $submission->save();

        $f3->reroute(['procedure_edit', ['procedure' => $this->procedure->name, 'submission' => $submission->id]]);
    }

    /**
     * Url: /procedure/@procedure/submission/@submission/edit
     * Alias: @precedure_edit
     * Methode: GET, POST
     * Affiche le formulaire d'edition d'un dossier, valide les valeurs, les transformes au format xfdf
     */
    public function edit(Base $f3)
    {
        if (! $this->submission->isEditable()) {
            return $f3->error(403, "Submission not editable");
        }

        if ($f3->get('VERB') === 'POST') {
            $postData = $f3->get('POST');

            $submissionValidator = new SubmissionValidation($this->submission, new Validation());
            $isValid = $submissionValidator->validate($postData);

            if ($isValid === false) {
                Flash::instance()->setKey('form-error', $submissionValidator->getValidation()->getErrors());
                \Helpers\Old::instance()->set($postData);

                return $f3->reroute(['procedure_edit', ['procedure' => $this->procedure->name, 'submission' => $this->submission->id]]);
            }

            $formattedData = $submissionValidator->formatData($postData);

            $this->submission->setDatas($formattedData);
            $this->submission->save();

            return $f3->reroute(['procedure_attachment', ['procedure' => $this->procedure->name, 'submission' => $this->submission->id]]);
        }

        $f3->set('procedure', $this->procedure);
        $f3->set('submission', $this->submission);

        $f3->set('content', 'procedure/edit.html.php');

        echo View::instance()->render('layout.html.php');
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
                $this->submission->storeAttachment($name, $file);
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
        $f3->set('content', 'procedure/submission.html.php');
        $f3->set('displaypdf', $f3->get('GET.pdf'));

        echo View::instance()->render('layout.html.php');
    }

    /**
     * Methode: GET
     * Génére et télécharge le pdf du dossier
     */
    public function downloadpdf(Base $f3)
    {
        $files = PDFTk::fillForm($this->procedure->pdf, $this->submission->getDatasPDF());
        $storePath = $this->submission->path.basename($files['pdf']);
        rename($files['pdf'], $storePath);
        unlink($files['xfdf']);

        $disposition = $f3->get('GET.disposition');
        if(!$disposition) {
            $disposition = 'attachment';
        }
        if (!in_array($disposition, ['attachment', 'inline'])) {
            return $f3->error(404, "Disposition < $disposition > not allowed");
        }

        $download = $disposition === 'attachment';

        return Web::instance()->send($storePath, null, 0, $download, $this->procedure->getConfigItem('SUBMISSION.filename').'.pdf');
    }

    /**
     * Methode: GET
     * Télécharge un fichier d'un dossier
     */
    public function downloadattachment(Base $f3)
    {
        $file = realpath($this->submission->getAttachmentsPath().str_replace('/', '', $f3->get('GET.category')).DIRECTORY_SEPARATOR.str_replace('/', '', $f3->get('GET.file')));

        if (is_file($file) === false) {
            return $f3->error(404, "File not found");
        }

        if(preg_match('/\.url$/', $file)) {

            return $f3->reroute(file_get_contents($file));
        }

        return Web::instance()->send($file, null, 0, true, basename($file));
    }

    /**
     * Methode: POST
     * Met à jour le status d'un dossier
     */
    public function updatestatus(Base $f3)
    {
        if (!User::instance()->isAdmin()) {
            return $f3->error(403, "Only admin");
        }

        $newStatus = $f3->get('POST.status');
        $comment = $f3->get('POST.comment');

        if (!in_array($newStatus, Submission::$allStatus)) {
            return $f3->error(404, "Status < $newStatus > not allowed");
        }

        $this->submission->setStatus($newStatus, $comment);
	    $this->submission->save();

        try {
            (new SubmissionEmails($this->submission))->chgtstatus();
        } catch (Exception $e) { }

        return $f3->reroute(['procedure_submission', ['procedure' => $this->procedure->name, 'submission' => $this->submission->id]]);
    }
}

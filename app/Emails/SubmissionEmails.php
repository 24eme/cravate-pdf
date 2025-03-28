<?php

namespace Emails;

use Base;
use Emails\Email;
use Model\Submission;
use Exception;

class SubmissionEmails
{
    private Base $f3;
    private ?Email $mail;
    private Submission $submission;
    private $headers = [];

    public function __construct(Submission $submission)
    {
        $this->f3 = Base::instance();
        $this->mail = $this->f3->exists('mail') ? $this->f3->get('mail') : null;
        $this->submission = $submission;

        if ($this->mail === null) {
            throw new Exception("Pas de configuration de mail");
        }

        $this->headers = [
            'From' => $this->f3->get('config')->get('mail.host'),
            'To'   => $this->submission->getDatas('EMAIL')
        ];

        $this->mail->headers($this->headers);
    }

    public function chgtstatus()
    {
        if ($this->mail === null) {
            return null;
        }

        $this->mail->header('Subject', "Changement de Status de votre dossier");

        return $this->mail->send('chgtstatus.eml', ['submission' => $this->submission]);
    }

    public function newSubmission()
    {
        if ($this->mail === null) {
            return null;
        }

        $this->mail->header('To', $this->f3->get('config')->get('mail.admin'));
        $this->mail->header('Subject', "Dépôt d'un nouveau dossier");

        return $this->mail->send('depot.eml', ['submission' => $this->submission]);
    }
}

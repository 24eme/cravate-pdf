<?php

namespace Emails;

use Base;
use Emails\Email;
use Model\Submission;
use Exception;

class SubmissionEmails
{
    private ?Email $mail;
    private Submission $submission;
    private $headers = [];

    public function __construct(Submission $submission)
    {
        $f3 = Base::instance();
        $this->mail = $f3->exists('mail') ? $f3->get('mail') : null;
        $this->submission = $submission;

        if ($this->mail === null) {
            throw new Exception("Pas de configuration de mail");
        }

        $this->headers = [
            'From' => $f3->get('config')->get('mail.host'),
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

        return $this->mail->send('chgtstatus', ['submission' => $this->submission]);
    }
}

<?php

namespace Emails;

use SMTP;
use Exception;
use View;
use Config\Config;

class Email
{
    private $smtp;
    private $emailsDir;
    private $headers = [];

    const requiredHeaders = [
        'From', 'To', 'Subject'
    ];

    public function __construct(SMTP $smtp, $ui = __DIR__)
    {
        $this->smtp = $smtp;
        $this->emailsDir = $ui;
    }

    public function headers($headers)
    {
        foreach ($headers as $key => $header) {
            $this->header($key, $header);
        }

        return $this;
    }

    public function header($key, $header)
    {
        $this->smtp->set($key, $header);
        $this->headers[$key] = $header;

        return $this;
    }

    public function send($template, $args)
    {
        if (is_file($this->emailsDir.$template) === false) {
            throw new Exception($template." n'existe pas");
        }

        if ($diff = array_diff(self::requiredHeaders, array_keys($this->headers))) {
            throw new Exception("Il manque des entêtes nécessaires: ".implode(', ', $diff));
        }

        $content = View::instance()->render($this->emailsDir.$template, 'text/plain', $args);

        return $this->smtp->send($content);
    }
}

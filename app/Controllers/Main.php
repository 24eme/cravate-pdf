<?php

namespace Controllers;

use Base;
use View;
use PDF\PDFForm;

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

        if (is_null($pdf) || is_file($pdf) === false) {
            return $f3->error(404, "Invalid file");
        }

        $pdfForm = new PDFForm($pdf);

        $f3->set('pdfForm', $pdfForm);
        $f3->set('content', 'main/form.html.php');

        echo View::instance()->render('layout.html.php');
    }
}

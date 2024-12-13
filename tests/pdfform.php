<?php

use PDF\PDFForm;
use PDF\PDFtk;

// INIT
$test = require __DIR__.'/_bootstrap.php';

// TESTS
$test->expect(preg_match('/FieldName: text/', PDFtk::dumpDataFields(__DIR__.'/resources/form.pdf')), "Récupération des champs avec pdftk");

$pdfForm = new PDFForm(__DIR__.'/resources/form.pdf');

$test->expect(count($pdfForm->getDataFields()) == 5, "5 champs ont été trouvés");
$test->expect($pdfForm->getDataFields()[0]['FieldType'] == 'Text', "Le 1er champs est de type `Text`");
$test->expect($pdfForm->getDataFields()[0]['FieldName'] == 'text', "Le 1er champs se nomme `text`");
$test->expect(count($pdfForm->getDataFields()[2]['FieldStateOption']) == 4, "Le 3ème champs a 4 options");
$test->expect(count($pdfForm->getDataFields()[2]['FieldStateOption']) == 4, "Le 3ème champs a 4 options");
$test->expect($pdfForm->getDataFields()[2]['FieldStateOption'][0] == 'Option 1', "La 1ère option du 3ème champs a pour valeur `Option 1`");

// Affichage des résultats
include __DIR__.'/_print.php';

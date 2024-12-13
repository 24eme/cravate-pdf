<?php

use PDF\PDFForm;
use PDF\PDFFormField;
use PDF\PDFtk;

// INIT
$test = require __DIR__.'/_bootstrap.php';

$pdfFile = __DIR__.'/resources/form.pdf';

// TESTS
$test->expect(preg_match('/FieldName: text/', PDFtk::dumpDataFields($pdfFile)), "Récupération des champs avec pdftk");

$test->message('Parsing des données formulaire extraites du PDF');

$dataFields = PDFtk::parseDataFieldsDump(PDFtk::dumpDataFields($pdfFile));
$test->expect(count($dataFields) == 5, "5 champs ont été trouvés");
$test->expect($dataFields[0]['FieldType'] == 'Text', "Le 1er champs est de type `Text`");
$test->expect($dataFields[0]['FieldName'] == 'text', "Le 1er champs se nomme `text`");
$test->expect(count($dataFields[2]['FieldStateOption']) == 4, "Le 3ème champs a 4 options");
$test->expect(count($dataFields[2]['FieldStateOption']) == 4, "Le 3ème champs a 4 options");
$test->expect($dataFields[2]['FieldStateOption'][0] == 'Option 1', "La 1ère option du 3ème champs a pour valeur `Option 1`");

$test->message('Mappage des données du formulaire dans une classe');

$pdfForm = new PDFForm($pdfFile);
$test->expect(count($pdfForm->getFields()) == 5, "5 champs ont été trouvés");
$test->expect($pdfForm->getFields()[0] instanceof PDFFormField, "Le 1er champs a pour classe `PDFFormField`");
$test->expect($pdfForm->getFields()[0]->getType() == PDFFormField::TYPE_TEXT, "Le 1er champs est de type `".PDFFormField::TYPE_TEXT."`");
$test->expect($pdfForm->getFields()[0]->getLabel() == "text", "Le 1er champs a pour label `text`");
$test->expect($pdfForm->getFields()[0]->getName() == "text", "Le 1er champs a pour name `text`");
$test->expect($pdfForm->getFields()[0]->getId() == "text", "Le 1er champs a pour nid `text`");

// Affichage des résultats
include __DIR__.'/_print.php';

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

$fields = $pdfForm->getFields();

$first = current($fields);
$test->expect($first instanceof PDFFormField, "Le 1er champs a pour classe `PDFFormField`");
$test->expect($first->getType() == PDFFormField::TYPE_TEXT, "Le 1er champs est de type `".PDFFormField::TYPE_TEXT."`");
$test->expect($first->getLabel() == "text", "Le 1er champs a pour label `text`");
$test->expect($first->getName() == "text", "Le 1er champs a pour name `text`");
$test->expect($first->getId() == "text_text", "Le 1er champs a pour id `text_text`");
$test->expect($first->isRequired(), "Le 1er champs est requis");

$second = next($fields);
$test->expect(! $second->isRequired(), "Le 2ème champs n'est pas requis");

// Affichage des résultats
include __DIR__.'/_print.php';

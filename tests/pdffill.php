<?php

use PDF\PDFForm;
use PDF\PDFtk;

$test = require __DIR__.'/_bootstrap.php';
$pdfFile = __DIR__.'/resources/form.pdf';

$testData = [
    'text' => 'another text / ~~ {} ä',
    'textarea' => 'abc\\n今日は',
    'Options' => 'Option 2',
    'Checkbox' => true,
    'select' => 'Option 4',
];

$pdf = new PDFForm($pdfFile);
$parsedData = $pdf->getFields();

$cleanedData = PDFtk::cleanData($parsedData, $testData);

$test->expect(count($cleanedData) === 5, "Il y a 5 entrées");
$test->expect(count(array_diff(array_keys($pdf->getFields()), array_keys($cleanedData))) === 0, "On retrouve bien les entrées du pdd");
$test->expect($cleanedData['text'] === "another text / ~~ {} ä", "Les données sont recopiées correctement");
$test->expect($cleanedData['Options'] === 'Option 2', "La bonne option est sélectionnée");
$test->expect($cleanedData['Checkbox'] === 'checkbox', "L'entrée checkbox est bien transformée avec la valeur du pdf");
$test->expect($cleanedData['select'] === null, "L'option du sélect était invalide, alors on le transforme en null");

$xfdfFile = PDFtk::generateXFDF($pdfFile, $testData);
$xml = XMLReader::XML($xfdfFile);
$xml->setParserProperty(XMLReader::VALIDATE, true);

$test->expect($xml->isValid(), "Le fichier généré est valide");

// Affichage des résultats
include __DIR__.'/_print.php';

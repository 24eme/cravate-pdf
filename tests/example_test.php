<?php

// INIT
$test = require __DIR__.'/_bootstrap.php';

// TESTS

$test->message("Exemple de message");
$test->expect(true, "Exemple de test");

// Affichage des résultats
include __DIR__.'/_print.php';

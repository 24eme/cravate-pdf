<?php
$f3 = require __DIR__.'/../vendor/fatfree-core/base.php';
$f3->set('AUTOLOAD', __DIR__.'/../app/');

require __DIR__.'/../app/common.php';

if (count($argv) < 3) {
    echo "ERREUR le script attend les arguments : $argv[0] #1_submission_folder #2_status_toapply #3_OPT_comment\n";
    exit;
}
$folder = (substr($argv[1], -1) !=  DIRECTORY_SEPARATOR)? $argv[1].DIRECTORY_SEPARATOR : $argv[1];
$status = $argv[2];
$comment = isset($argv[3])? $argv[3] : null;

if (!is_dir($folder)) {
    echo "ERREUR $folder n'est pas un dossier valide\n";
    exit;
}

if (!in_array($status, Records\Submission::$allStatus)) {
    echo "ERREUR le statut $status n'est pas connu\n";
    exit;
}

$pattern = '#records/([^/]+)/submissions/([^/]+)/#';
if (preg_match($pattern, $folder, $matches)) {
    $recordName = $matches[1];
    $submissionName = $matches[2];
} else {
    echo "ERREUR le dossier ne valide pas l'expression $pattern\n";
    exit;
}

try {
    $record = new Records\Record($recordName);
} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}

$submission = new Records\Submission($record, $submissionName);

try {
    $submission->setStatus($status, $comment);
} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}

echo "SUCCES de la mise à jour du dossier $submission->path\n";

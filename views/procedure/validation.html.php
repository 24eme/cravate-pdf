<?php echo View::instance()->render('global/etapes.html.php'); ?>

<h3 class="mt-3 mb-0">
  Validation du dossier
</h3>
<h4 class="mt-1 mb-3 h5">
n°<?php echo preg_replace('/^([0-9]{8})([0-9]{6})(.+)$/', '\1-\2-\3', $submission->id) ?><?php if($submission->userId): ?> / <?php echo $submission->userId ?><?php endif; ?>
</h4>

<?php if ($validator->hasErrors()): ?>
  <div class="alert alert-danger" role="alert">
    <h5>Points bloquants</h5>
    <ul class="mb-0">
    <?php foreach ($validator->getErrors() as $error): ?>
      <li><?php echo $error['message'] ?></li>
    <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<?php if ($validator->hasWarnings()): ?>
  <div class="alert alert-warning" role="alert">
    <h5>Points de vigilance</h5>
    <ul class="mb-0">
    <?php foreach ($validator->getWarnings() as $warn): ?>
      <li><?php echo $warn['message'] ?></li>
    <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<div class="row">
  <div class="col-8">
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <a class="nav-link active" aria-current="page" href="<?php echo Base::instance()->alias('procedure_submission') ?>">Données</a>
      </li>
    </ul>
    <?php echo View::instance()->render('procedure/_datas.html.php'); ?>
  </div>

  <div class="col-4">
    <?php echo View::instance()->render('procedure/_attachments.html.php','text/html',array('submission' => $submission, 'hidepdf' => true)); ?>
  </div>
</div>

<?php if ($validator->hasErrors() === false): ?>
  <div class="text-center">
    <form method="post">
      <button type=submit class="btn btn-primary"><i class="bi bi-upload"></i> Soumettre le dossier</button>
    </form>
  </div>
<?php endif ?>

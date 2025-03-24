<?php echo View::instance()->render('global/etapes.html.php'); ?>

<h2 class="mb-3 mt-4">
  <?php echo $submission->getLibelle() ?>
</h2>

<?php if ($validator->hasErrors()): ?>
  <div class="alert alert-danger" role="alert">
    <h5>Points de bloquant</h5>
    <ul class="list-unstyled mb-0">
    <?php foreach ($validator->getErrors() as $error): ?>
      <li><?php echo $error['message'] ?></li>
    <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<?php if ($validator->hasWarnings()): ?>
  <div class="alert alert-warning" role="alert">
    <h5>Points de vigilance</h5>
    <ul class="list-unstyled mb-0">
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
        <a class="nav-link<?php if (!$displaypdf): ?> active<?php endif; ?>" aria-current="page" href="<?php echo Base::instance()->alias('procedure_submission') ?>">Données</a>
      </li>
    </ul>
    <?php echo View::instance()->render('procedure/_datas.html.php'); ?>
  </div>

  <div class="col-4">
    <?php echo View::instance()->render('procedure/_attachments.html.php'); ?>
  </div>
</div>

<?php if ($validator->hasErrors() === false): ?>
  <div class="text-center">
    <form method="post">
      <button type=submit class="btn btn-primary"><i class="bi bi-upload"></i> Soumettre le dossier</button>
    </form>
  </div>
<?php endif ?>

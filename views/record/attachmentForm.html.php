<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('records') ?>">Dossiers</a></li>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('record_submissions') ?>"><?php echo $submission->record->getConfigItem('subtitle') ?></a></li>
      <li class="breadcrumb-item">Saisie</li>
    </ol>
  </div>
</nav>

<h1>Dossier <?php echo isset($record) ? $record->getConfigItem('title') : '' ?></h1>

<?php echo View::instance()->render('global/etapes.html.php'); ?>


<form method="POST" enctype="multipart/form-data" action="/record/<?php echo $submission->record->name ?>/submission/<?php echo $submission->name ?>/attachment">
  <div class="row justify-content-center">
    <div class="col-6">
    <h3>Joindre une pièce complémentaire</h3>
    <ul class="list-group mt-3">
    <?php foreach(["Registre de mise", "Déclaration de conditionnement", "Autre"] as $annexe): ?>
        <li class="list-group-item"><label><?php echo $annexe ?></label> <input type="file" class="form-control form-control-sm float-end w-50" name="<?php echo $annexe ?>" /></li>
    <?php endforeach; ?>
    </ul>
    <div class="text-end">
      <button type="submit" class="btn btn-primary mt-3">Continuer</button>
    </div>
    </div>
  </div>

</form>

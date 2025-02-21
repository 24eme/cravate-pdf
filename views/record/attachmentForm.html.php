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
    <h3 class="mt-3">Joindre une pièce complémentaire</h3>
    <ul class="list-group mt-4">
    <?php foreach(["Registre_de_mise", "Déclaration_de_conditionnement"] as $annexe): ?>
        <li class="list-group-item"><label><?php echo str_replace("_", " ", $annexe) ?></label> <input type="file" class="form-control form-control-sm float-end w-50" style="<?php if($submission->getAttachmentByName($annexe)): ?>display:none<?php endif; ?>" name="<?php echo $annexe ?>" />
        <?php if($submission->getAttachmentByName($annexe)): ?>
          <span class="float-end"><a href="" ><?php echo $submission->getAttachmentByName($annexe); ?></a> <button class="btn btn-link btn-sm"><i class="bi bi-pencil-square"></i></button></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
    </ul>
    <div class="text-end">
      <button type="submit" class="btn btn-primary mt-3">Continuer</button>
    </div>
    </div>
  </div>

</form>

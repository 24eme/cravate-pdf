<h1>Dossier <?php echo isset($record) ? $record->getConfigItem('title') : '' ?></h1>

<?php echo View::instance()->render('global/etapes.html.php'); ?>

<h3>Joindre une pièce complémentaire</h3>

<form method="POST" class="row" enctype="multipart/form-data" action="/record/<?php echo $submission->record->name ?>/submission/<?php echo $submission->name ?>/attachment">
<p class="text-center"><?php echo $this->raw($submission->getAttachmentNeeded()); ?></p>
<div class="col-6 offset-3 mt-3 justify-content-center">
  <?php if (isset($uploadError)): ?><p class="text-center text-danger">Une erreur est survenue</p><?php endif; ?>
  <div class="mb-3 text-center">
    <label class="form-label" for="attachment">Sélectionner un fichier</label>
    <input type="file" class="form-control form-control-lg" name="attachment" />
  </div>
  <div class="text-end">
    <button type="submit" class="btn btn-primary">Joindre</button>
  </div>
</div>
</form>

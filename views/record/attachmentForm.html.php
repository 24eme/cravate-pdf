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


<form method="POST" enctype="multipart/form-data" action="<?php echo Base::instance()->alias("record_attachment", ['record' => $submission->record->name, 'submission' => $submission->name]) ?>">
  <div class="row justify-content-center">
    <div class="col-6">
    <h3 class="mt-3">Joindre une pièce complémentaire</h3>
    <ul class="list-group mt-4">
    <?php foreach($submission->getAttachmentsNeeded() as $attachment): ?>
        <li class="list-group-item"><label><?php echo $attachment['label'] ?></label><input <?php if($attachment['required']): ?>required="required"<?php endif; ?> type="file" class="form-control form-control-sm float-end w-50" style="<?php if($submission->getAttachmentByName($attachment['filename'])): ?>display:none<?php endif; ?>" name="<?php echo $attachment['filename'] ?>" />
        <?php if($submission->getAttachmentByName($attachment['filename'])): ?>
          <span class="float-end"><a href="<?php echo Base::instance()->alias('record_submission_getfile', [], ['disposition' => 'attachment', 'file' => Records\Submission::ATTACHMENTS_PATH.$submission->getAttachmentByName($attachment['filename'])]) ?>"><i class="bi bi-file-earmark"></i> Voir le fichier</a> <button type="button" class="btn btn-link btn-sm btn-edit"><i class="bi bi-pencil-square"></i></button></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
    </ul>
    <div class="text-end">
      <button type="submit" class="btn btn-primary mt-3">Continuer</button>
    </div>
    </div>
  </div>
  <script>
    document.querySelectorAll('.btn-edit').forEach(function (item) {
      item.addEventListener('click', function (e) {
        this.parentNode.parentNode.querySelector('input').style.display = 'inherit';
        this.parentNode.style.display = 'none';
        this.parentNode.parentNode.querySelector('input').click();
      })
    });
  </script>
</form>

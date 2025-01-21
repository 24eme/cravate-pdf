<h1>Dossier <?php echo $record->getConfigItem('title') ?></h1>

<nav class="navbar navbar-expand-lg bg-body-tertiary mt-2" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <li class="breadcrumb-item"><a href="/records">Dossiers</a></li>
      <li class="breadcrumb-item"><a href="/record/<?php echo $submission->record->name ?>/submissions"><?php echo $submission->record->getConfigItem('subtitle') ?></a></li>
      <li class="breadcrumb-item"><?php echo $submission->getLibelle() ?></li>
    </ol>
  </div>
</nav>

<h1 class="pb-2 my-4">
  <?php echo $submission->getLibelle() ?>
  <div class="float-end badge text-bg-<?php echo $submission->getStatusThemeColor() ?> text-wrap"><?php echo Records\Submission::printStatus($submission->status) ?></div>
</h1>

<div class="row">

  <div class="col-8">
      <table class="table table-striped table-hover">
      <?php foreach($submission->getDatas() as $field => $value): ?>
        <tr>
          <th><?php echo $field ?> :</th>
          <td><?php echo $value ?></td>
        </tr>
      <?php endforeach; ?>
      </table>
  </div>

  <div class="col-4">
    <h2 class="pb-2 h3"><i class="bi bi-download"></i> Fichiers</h2>
    <ul class="list-group">
      <a class="list-group-item list-group-item-action" href="/record/<?php echo $submission->record->name ?>/submission/<?php echo $submission->name ?>/getfile?disposition=attachment&file=<?php echo $submission->pdf ?>" target="_blank">
        <i class="bi bi-filetype-pdf"></i> Formulaire complété
      </a>
      <?php foreach ($submission->getAttachments() as $i => $attachment): ?>
      <a class="list-group-item list-group-item-action" href="/record/<?php echo $submission->record->name ?>/submission/<?php echo $submission->name ?>/getfile?disposition=attachment&file=<?php echo Records\Submission::ATTACHMENTS_PATH.$attachment ?>" target="_blank">
        <i class="bi bi-file"></i> Annexe <?php echo $i+1 ?> : <small><?php echo basename($attachment) ?></small>
      </a>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<?php if ($submission->status === Records\Submission::STATUS_DRAFT): ?>
<div class="row">
  <form method="post">
    <button type=submit class="btn btn-primary"><i class="bi bi-upload"></i> Soumettre le dossier</button>
  </form>
</div>
<?php endif ?>

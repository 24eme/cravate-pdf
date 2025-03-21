<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedures') ?>">Dossiers</a></li>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedure_submissions') ?>"><?php echo $submission->procedure->getConfigItem('subtitle') ?></a></li>
      <li class="breadcrumb-item"><?php echo $submission->getLibelle() ?></li>
    </ol>
  </div>
</nav>

<h1>Dossier <?php echo $procedure->getConfigItem('title') ?></h1>

<?php echo View::instance()->render('global/etapes.html.php'); ?>

<div class="float-end badge text-bg-<?php echo $submission->getStatusThemeColor() ?> text-wrap fs-3 mt-3"><?php echo Model\Submission::printStatus($submission->status) ?></div>

<h1 class="pb-2 my-4">
  <?php echo $submission->getLibelle() ?>
</h1>

<?php if ($validator->hasErrors()): ?>
  <div class="alert alert-danger" role="alert">
    <ul class="list-unstyled mb-0">
    <?php foreach ($validator->getErrors() as $error): ?>
      <li><?php echo $error['message'] ?></li>
    <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<?php if ($validator->hasWarnings()): ?>
  <div class="alert alert-warning" role="alert">
    <ul class="list-unstyled mb-0">
    <?php foreach ($validator->getWarnings() as $warn): ?>
      <li><?php echo $warn['message'] ?></li>
    <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<div class="row">

  <div class="col-8">
      <table class="table table-striped table-hover">
      <?php $formConfig = $submission->procedure->getConfigItem('form'); ?>
      <?php foreach($submission->getDatas() as $field => $value): ?>
        <tr>
          <th><?php echo $field ?> :</th>
          <?php if (array_key_exists('format', $formConfig[$field])): ?>
            <td><?php echo preg_replace(
                             strtok($formConfig[$field]['format'], '#'),
                             strtok('#'),
                             $value
                           ) ?>
            </td>
          <?php else: ?>
            <td><?php echo $value ?></td>
          <?php endif ?>
        </tr>
      <?php endforeach; ?>
      </table>
      <?php if (property_exists($submission->json, "history")): ?>
      <h2 class="pb-2 h3 pt-2"><i class="bi bi-clock-history"></i> Historique</h2>
      <table class="table table-striped">
        <tbody>
            <?php foreach (array_reverse($submission->json->history) as $history): ?>
            <tr>
              <td><?php echo date('d/m/Y H:i', strtotime($history->date)) ?></td>
              <td><?php echo $history->entry ?></td>
              <td class="w-50"><?php echo $history->comment ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
  </div>

  <div class="col-4">
    <h2 class="pb-2 h3"><i class="bi bi-download"></i> Fichiers</h2>
    <ul class="list-group">
        <a class="list-group-item list-group-item-action" href="<?php echo Base::instance()->alias('procedure_submission_getfile', [], ['disposition' => 'attachment', 'file' => $submission->pdf]) ?>" target="_blank">
        <i class="bi bi-filetype-pdf"></i> Formulaire complété
      </a>
      <?php foreach ($submission->getAttachments() as $i => $attachment): ?>
        <a class="list-group-item list-group-item-action" href="<?php echo Base::instance()->alias('procedure_submission_getfile', [], ['disposition' => 'attachment', 'file' => Model\Submission::ATTACHMENTS_PATH.$attachment]) ?>" target="_blank">
          <i class="bi bi-file"></i> Annexe <?php echo $i+1 ?> :
            <small>
              <?php echo basename($attachment, '.url') ?>
              <?php if (strpos($attachment, '.url') === (strlen($attachment) - 4)): ?><i class="bi bi-box-arrow-up-right small ms-1"></i><?php endif ?>
            </small>
        </a>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<?php if ($validator->hasErrors() === false): ?>
  <div class="text-center">
    <form method="post">
      <button type=submit class="btn btn-primary"><i class="bi bi-upload"></i> Soumettre le dossier</button>
    </form>
  </div>
<?php endif ?>

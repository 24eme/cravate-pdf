<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedures') ?>">Dossiers</a></li>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedure_submissions') ?>"><i class="bi bi-folder2-open"></i>  <?php echo $submission->procedure->getConfigItem('title') ?></a></li>
      <li class="breadcrumb-item"><a href=""><?php echo $submission->getLibelle() ?></a></li>
    </ol>
  </div>
</nav>

<h3 class="mb-1 mt-3"><i class="bi bi-folder2-open"></i> <?php echo $submission->procedure->getConfigItem('title'); ?><span class="float-end badge text-bg-<?php echo $submission->getStatusThemeColor() ?> text-wrap fs-5"><?php echo Model\Submission::printStatus($submission->status) ?></span></h3>

<h4 class="mt-1 mb-3 h6">
N°<?php echo preg_replace('/^([0-9]{8})([0-9]{6})(.+)$/', '\1-\2-\3', $submission->id) ?><?php if($submission->userId): ?> / <?php echo $submission->userId ?><?php endif; ?>
<?php if($submittedDate = $submission->getDateHistory(Model\Submission::STATUS_SUBMITTED)): ?><span class="float-end">Déposé le <?php echo $submittedDate->format('d/m/Y H:i'); ?></span><?php endif; ?>
</h4>

<?php if ($submission->status == Model\Submission::STATUS_UNCOMPLETED): ?>
<?php $entry = $submission->getHistoryForStatus(Model\Submission::STATUS_UNCOMPLETED); ?>
<div class="alert alert-warning" role="alert">
<h5><i class="bi bi-exclamation-circle"></i> Votre dossier n'est pas complet <?php echo ($entry->comment)? ' : '.$entry->comment : '' ?></h5>
<ul class="mb-0">
  <li>Pour modifier les informations saisies : <a href="<?php echo Base::instance()->alias('procedure_edit', ['submission' => $submission->id ]) ?>">Modifier les informations</a></li>
  <li>Pour complétez les annexes : <a href="<?php echo Base::instance()->alias('procedure_attachment', ['submission' => $submission->id ]) ?>">Compléter les annexes</a></li>
</ul>
</div>
<?php endif; ?>

<div class="row">
  <div class="col-8">
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <a class="nav-link<?php if (!$displaypdf): ?> active<?php endif; ?>" aria-current="page" href="<?php echo Base::instance()->alias('procedure_submission') ?>">Données</a>
      </li>
      <li class="nav-item">
        <a class="nav-link<?php if ($displaypdf): ?> active<?php endif; ?>" href="<?php echo Base::instance()->alias('procedure_submission', [], ['pdf' => 1]) ?>">PDF</a>
      </li>
    </ul>
    <?php if ($displaypdf): ?>
      <object type="application/pdf" style="height: 75vh;" class="w-100" data="<?php echo Base::instance()->alias('procedure_submission_downloadpdf', [], ['disposition' => 'inline']) ?>#toolbar=0&navpanes=0&scrollbar=0&statusbar=0&messages=0&scrollbar=0"></object>
    <?php else: ?>
      <?php echo View::instance()->render('procedure/_datas.html.php'); ?>
    <?php endif; ?>
    <?php if ($history = $submission->getHistory()): ?>
    <h2 class="pb-2 h3 pt-2"><i class="bi bi-clock-history"></i> Historique</h2>
    <table class="table table-striped">
      <tbody>
          <?php foreach ($history as $item): ?>
          <tr>
            <td style="white-space: nowrap; width: 0;"><?php echo date('d/m/Y H:i', strtotime($item->date)) ?></td>
            <td><?php echo $item->entry ?></td>
            <td><?php echo $item->comment ?></td>
          </tr>
          <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="col-4">
    <?php if(User\User::instance()->isAdmin()): ?>
    <h2 class="pb-2 h3"><i class="bi bi-gear"></i> Statut</h2>
    <form action="<?php echo Base::instance()->alias('procedure_submission_updatestatus') ?>" method="post" class="row mb-4">
      <div class="col-12">
        <select name="status" class="form-select">
          <?php foreach(Model\Submission::$statusThemeColor as $status => $themeColor): ?>
            <option value="<?php echo $status ?>"<?php if ($status == $submission->status): ?> selected<?php endif ?>>
              <?php echo Model\Submission::printStatus($status) ?> <?php if ($status == $submission->status): ?>(actuel)<?php endif ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 mb-2 mt-2">
        <textarea class="form-control" rows="2" name="comment" placeholder="Commentaires lié au statut"></textarea>
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-warning w-100" type="submit">Changer le statut</button>
      </div>
    </form>
    <?php endif; ?>

    <?php echo View::instance()->render('procedure/_attachments.html.php'); ?>
  </div>
</div>

<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('records') ?>">Dossiers</a></li>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('record_submissions') ?>"><?php echo $submission->record->getConfigItem('subtitle') ?></a></li>
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
    <a href="<?php echo Base::instance()->alias('record_edit', ['submission' => $submission->name ]) ?>" class="btn btn-outline-secondary btn-sm float-end mt-1">Modifier</a>
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <a class="nav-link<?php if (!$displaypdf): ?> active<?php endif; ?>" aria-current="page" href="<?php echo Base::instance()->alias('record_submission') ?>">Données</a>
      </li>
      <li class="nav-item">
        <a class="nav-link<?php if ($displaypdf): ?> active<?php endif; ?>" href="<?php echo Base::instance()->alias('record_submission', [], ['pdf' => 1]) ?>">PDF</a>
      </li>
    </ul>
    <?php if ($displaypdf): ?>
      <object type="application/pdf" style="height: 75vh;" class="w-100" data="<?php echo Base::instance()->alias('record_submission_getfile', [], ['file' => $submission->pdf, 'disposition' => 'inline']) ?>#toolbar=0&navpanes=0&scrollbar=0&statusbar=0&messages=0&scrollbar=0"></object>
    <?php else: ?>
      <table class="table table-striped table-hover">
      <?php foreach($submission->getDatas() as $field => $value): ?>
        <tr>
          <th><?php echo $field ?> :</th>
          <td><?php echo $value ?></td>
        </tr>
      <?php endforeach; ?>
      </table>
    <?php endif; ?>
    <?php if ($history = $submission->getHistory()): ?>
    <h2 class="pb-2 h3 pt-2"><i class="bi bi-clock-history"></i> Historique</h2>
    <table class="table table-striped">
      <tbody>
          <?php foreach ($history as $item): ?>
          <tr>
            <td><?php echo date('d/m/Y H:i', strtotime($item->date)) ?></td>
            <td><?php echo $item->entrie ?></td>
            <td class="w-50"><?php echo $item->comment ?></td>
          </tr>
          <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="col-4">
    <p class="mt-4 fs-5 text-end">Dépot : <?php echo $submission->datetime->format('d/m/Y H:i'); ?></p>
    <h2 class="pb-2 h3"><i class="bi bi-gear"></i> Statut</h2>
    <form action="<?php echo Base::instance()->alias('record_submission_updatestatus') ?>" method="post" class="row mb-4">
      <div class="col-12">
        <select name="status" class="form-select">
          <?php foreach(Records\Submission::$statusThemeColor as $status => $themeColor): ?>
            <option value="<?php echo $status ?>"<?php if ($status == $submission->status): ?> selected<?php endif ?>>
              <?php echo Records\Submission::printStatus($status) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 mb-2 mt-2">
        <textarea class="form-control" rows="4" name="comment" placeholder="Commentaires lié au statut"></textarea>
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-warning w-75" type="submit">Changer le statut</button>
      </div>
    </form>
    <h2 class="pb-2 h3"><i class="bi bi-download"></i> Fichiers <a href="<?php echo Base::instance()->alias('record_attachment', ['submission' => $submission->name ]) ?>" class="btn btn-outline-secondary btn-sm float-end mt-1">Modifier</a></h2>
    <ul class="list-group">
      <a class="list-group-item list-group-item-action" href="<?php echo Base::instance()->alias('record_submission_getfile', [], ['disposition' => 'attachment', 'file' => $submission->pdf]) ?>" target="_blank">
        <i class="bi bi-filetype-pdf"></i> Formulaire complété
      </a>
      <?php foreach ($submission->getAttachments() as $i => $attachment): ?>
        <a class="list-group-item list-group-item-action" href="<?php echo Base::instance()->alias('record_submission_getfile', [], ['disposition' => 'attachment', 'file' => Records\Submission::ATTACHMENTS_PATH.$attachment]) ?>" target="_blank">
        <i class="bi bi-file"></i> Annexe <?php echo $i+1 ?> : <small><?php echo preg_replace('/\.url$/', '&nbsp;&nbsp;<i class="bi bi-box-arrow-up-right small"></i>', basename($attachment)) ?></small>
      </a>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

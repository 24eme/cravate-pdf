<?php use Records\Submission ?>

<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('records') ?>">Dossiers</a></li>
      <li class="breadcrumb-item"><?php echo $record->getConfigItem('subtitle') ?></li>
    </ol>
  </div>
</nav>

<h1 class="border-bottom pb-2 mt-4 clearfix"><span class="float-start pt-2">Dépôts</span><a href="<?php echo Base::instance()->alias('record_submission_new', ['record' => $record->name]) ?>" class="btn btn-light float-end" title="Saisir le dossier"><i class="bi bi-file-earmark-plus"></i> Saisir un dossier</a></h1>

<div class="row">
  <div class="col-9">
    <?php if ($submissions = $record->getSubmissions($statusFilter, (!$_SESSION['is_admin'])? $_SESSION['etablissement_id'] : null)): ?>
    <table class="table table-hover table-striped mt-3">
      <thead>
        <tr>
          <th scope="col" class="col-2">Statut</th>
          <th scope="col" class="col-2">Date<i class="bi bi-arrow-down-short"></i></th>
          <th scope="col">Libelle</th>
          <th class="col-1"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($submissions as $submission): ?>
      <tr>
        <td><i class="text-<?php echo $submission->getStatusThemeColor() ?> bi bi-circle-fill"></i> <?php echo Records\Submission::printStatus($submission->status) ?></td>
        <td><?php echo $submission->datetime->format('d/m/Y H:i') ?></td>
        <td><?php echo $submission->getLibelle() ?></td>
        <td class="text-end">
          <a href="<?php echo Base::instance()->alias('record_submission', ['submission' => $submission->name ]) ?>">
            <i class="bi bi-eye"></i>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <p>Aucuns dépôts</p>
    <?php endif; ?>
  </div>
  <div class="col-3 mt-5 pt-2">
    <?php $countByStatus = $record->countByStatus((!$_SESSION['is_admin'])? $_SESSION['etablissement_id'] : null); ?>
    <ul class="list-group">
      <a class="list-group-item list-group-item-action<?php if ($statusFilter === Submission::STATUS_TOUS): ?> active<?php endif; ?>" aria-current="page"
         href="<?php echo Base::instance()->alias('record_submissions', [], ['status' => Submission::STATUS_TOUS]) ?>">
          <span class="badge rounded-pill text-bg-primary"><?php echo array_sum($countByStatus) ?></span> Tous
      </a>
      <?php foreach($statusThemeColor as $status => $themeColor): ?>
        <a class="list-group-item list-group-item-action<?php if($statusFilter == $status): ?> active<?php endif; ?>"
          href="<?php echo Base::instance()->alias('record_submissions', [], ['status' => $status]) ?>">
            <span class="badge rounded-pill text-bg-<?php echo $themeColor ?>"><?php echo $countByStatus[$status] ?></span> <?php echo Records\Submission::printStatus($status) ?>
        </a>
      <?php endforeach; ?>
    </ul>
  </div>

</div>

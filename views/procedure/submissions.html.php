<?php use Model\Submission ?>

<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedures') ?>">Dossiers</a></li>
      <li class="breadcrumb-item"><?php echo $procedure->getConfigItem('subtitle') ?></li>
    </ol>
  </div>
</nav>

<h1 class="border-bottom pb-2 mt-4 clearfix"><span class="float-start pt-2">Dépôts</span><a href="<?php echo Base::instance()->alias('procedure_submission_new', ['procedure' => $procedure->name]) ?>" class="btn btn-light float-end" title="Saisir le dossier"><i class="bi bi-file-earmark-plus"></i> Saisir un dossier</a></h1>

<div class="row">
  <div class="col-9">
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
      <?php if (count($submissions)): ?>
        <?php foreach($submissions as $submission): ?>
        <tr>
          <td><i class="text-<?php echo $submission['themeColor'] ?> bi bi-circle-fill"></i> <?php echo Submission::printStatus($submission['status']) ?></td>
          <td><?php echo $submission['date']->format('d/m/Y H:i') ?></td>
          <td><?php echo $submission['libelle'] ?></td>
          <td class="text-end">
            <a href="<?php echo Base::instance()->alias('procedure_submission', ['submission' => $submission['id'] ]) ?>">
              <i class="bi bi-eye"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" class="text-center">Aucun dépôt</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="col-3 mt-5 pt-2">
    <ul class="list-group">
      <a class="list-group-item list-group-item-action<?php if ($status === Submission::STATUS_TOUS): ?> active<?php endif; ?>" aria-current="page"
         href="<?php echo Base::instance()->alias('procedure_usersubmissions', ['user' => Base::instance()->get('PARAMS.user')], ['status' => Submission::STATUS_TOUS]) ?>">
          <span class="badge rounded-pill text-bg-primary"><?php echo array_sum($submissionsByStatus) ?></span> Tous
      </a>
      <?php foreach($statusThemeColor as $statusKey => $themeColor): ?>
        <a class="list-group-item list-group-item-action<?php if($status == $statusKey): ?> active<?php endif; ?>"
          href="<?php echo Base::instance()->alias('procedure_usersubmissions', ['user' => Base::instance()->get('PARAMS.user')], ['status' => $statusKey]) ?>">
            <span class="badge rounded-pill text-bg-<?php echo $themeColor ?>"><?php echo $submissionsByStatus[$statusKey] ?></span> <?php echo Submission::printStatus($statusKey) ?>
        </a>
      <?php endforeach; ?>
    </ul>
  </div>

</div>

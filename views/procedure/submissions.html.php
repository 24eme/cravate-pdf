<?php use Model\Submission ?>

<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <?php if(\User\User::instance()->isAdmin()): ?>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('index') ?>">Administrateur</a></li>
      <?php endif; ?>
      <?php if(isset($user) && $user): ?>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedures', ['user' => $user]) ?>">Dossiers <?php echo $user ?></a></li>
      <?php endif; ?>
      <li class="breadcrumb-item"><i class="bi bi-folder2-open"></i> Liste des dépôts de <?php echo strtolower($procedure->getConfigItem('title')) ?></li>
    </ol>
  </div>
</nav>

<h1 class="border-bottom fs-2 pb-2 mb-0 mt-3 clearfix">Liste des dépôts <?php if(isset($user)): ?><small class="fs-6"><?php echo $user ?></small><a href="<?php echo Base::instance()->alias('procedure_submission_new', ['procedure' => $procedure->name]) ?>" class="btn btn-light float-end" title="Saisir le dossier"><i class="bi bi-file-earmark-plus"></i> Saisir un dossier</a><?php elseif(\User\User::instance()->isAdmin()): ?><small class="fs-6">Admin</small><?php endif; ?></h1>

<div class="row">
  <div class="col-9">
    <div class="card mt-3">
    <div class="card-header"><i class="bi bi-folder2-open"></i> <?php echo $procedure->getConfigItem('title') ?></div>
    <table class="table table-hover table-striped">
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
  </div>
  <div class="col-3">
    <div class="card mt-3">
    <div class="card-header">Filtrer par statut</div>
    <ul class="list-group list-group-flush">
      <a class="list-group-item list-group-item-action<?php if ($status === Submission::STATUS_TOUS): ?> active<?php endif; ?>" aria-current="page"
         href="<?php echo Base::instance()->alias(isset($user) ? 'procedure_usersubmissions' : 'procedure_submissions', ['user' => isset($user) ? $user : null], ['status' => Submission::STATUS_TOUS]) ?>">
          <span class="badge rounded-pill text-bg-primary"><?php echo array_sum($submissionsByStatus) ?></span> Tous
      </a>
      <?php foreach($statusThemeColor as $statusKey => $themeColor): ?>
        <a class="list-group-item list-group-item-action<?php if($status == $statusKey): ?> active<?php endif; ?>"
          href="<?php echo Base::instance()->alias(isset($user) ? 'procedure_usersubmissions' : 'procedure_submissions', ['user' => isset($user) ? $user : null], ['status' => $statusKey]) ?>">
            <span class="<?php if($submissionsByStatus[$statusKey] == 0): ?> opacity-50<?php endif; ?>"><span class="badge rounded-pill text-bg-<?php echo $themeColor ?>"><?php echo $submissionsByStatus[$statusKey] ?></span> <?php echo Submission::printStatus($statusKey) ?></span>
        </a>
      <?php endforeach; ?>
    </ul>
    </div>
  </div>
</div>

<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <?php if(\User\User::instance()->isAdmin()): ?>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('index') ?>">Administrateur</a></li>
      <?php endif; ?>
      <?php if(isset($user) && $user): ?>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedures', ['user' => $user]) ?>">Dossiers <?php echo $user ?></a></li>
      <?php endif; ?>
    </ol>
  </div>
</nav>

<h1 class="border-bottom fs-2 pb-2 mb-4 mt-3"><i class="bi bi-folder2-open"></i> Dossiers <small class="fs-6"><?php echo $user ?></small></h1>

<?php if ($procedures): ?>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-2">
  <?php foreach($procedures as $i => $procedure): ?>
  <div class="col">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title mb-5"><?php echo $procedure->getConfigItem('title') ?></h5>
        <div class="text-center">
          <a href="<?php echo Base::instance()->alias('procedure_submission_new', ['procedure' => $procedure->name], isset($user) ? ['user' => $user] : null) ?>" class="btn btn-primary mt-2 d-block"><i class="bi bi-file-earmark-plus"></i> Saisir le dossier</a>
          <a href="<?php echo Base::instance()->alias('procedure_usersubmissions', ['procedure' => $procedure->name, 'user' => $user]) ?>" class="mt-2 d-block">Liste des dépôts</a>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<p>Aucuns dossiers à saisir</p>
<?php endif; ?>

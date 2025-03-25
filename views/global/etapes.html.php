<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <?php if(\User\User::instance()->isAdmin()): ?>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('index') ?>">Administrateur</a></li>
      <?php endif; ?>
      <?php if(isset($user) && $user): ?>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedures', ['user' => $user]) ?>">Dossiers <?php echo $user ?></a></li>
      <li class="breadcrumb-item"><i class="bi bi-folder2-open"></i> <?php echo $submission->procedure->getConfigItem('title') ?> n°<?php echo $submission->getIdFormated() ?></li>
      <?php endif; ?>
    </ol>
  </div>
</nav>

<h1 class="fs-2 mb-1 mt-3"><i class="bi bi-folder2-open"></i> <?php echo isset($procedure) ? $procedure->getConfigItem('title') : '' ?></h1>

<ul class="step list-unstyled">
  <?php foreach ($steps->getSteps() as $step): ?>
    <li class="step-item<?php echo $step->isActive() ? ' active' : '' ?>">
    <?php if ($step->link()): ?>
      <a href="<?php echo Base::instance()->alias($step->link(), $steps->getLinkArgs()) ?>">
        <?php echo $step ?>
      </a>
    <?php else: echo $step; endif ?>
    </li>
  <?php endforeach ?>
</ul>

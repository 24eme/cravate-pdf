<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedures') ?>">Dossiers</a></li>
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedure_submissions') ?>"><i class="bi bi-folder2-open"></i>  <?php echo $procedure->getConfigItem('title') ?></a></li>
      <li class="breadcrumb-item"><a href="">Saisie</a></li>
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

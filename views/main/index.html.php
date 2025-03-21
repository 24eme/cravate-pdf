<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <li class="breadcrumb-item"><a href="<?php echo Base::instance()->alias('procedures') ?>">Dossiers</a></li>
    </ol>
  </div>
</nav>

<h1 class="border-bottom pb-2 mb-4 mt-4"><i class="bi bi-folder2-open"></i> Dossiers</h1>

<?php if ($procedures): ?>
<div class="row row-cols-1 row-cols-md-4 g-2">
  <?php foreach($procedures as $i => $procedure): ?>
  <div class="col">
    <div class="card h-100">
      <div class="card-header">
        <?php echo $procedure->getConfigItem('title') ?>
      </div>
      <div class="card-body">
        <h5 class="card-title"><?php echo $procedure->getConfigItem('subtitle') ?></h5>
        <p class="card-text"><?php echo $procedure->getConfigItem('text') ?></p>
        <p class="text-center">
          <a href="<?php echo Base::instance()->alias('procedure_submission_new', ['procedure' => $procedure->name]) ?>" class="btn btn-primary mt-2 d-block"><i class="bi bi-file-earmark-plus"></i> Saisir le dossier</a>
          <a href="<?php echo Base::instance()->alias('procedure_submissions', ['procedure' => $procedure->name]) ?>" class="mt-2 d-block">Liste des dépôts</a>
        </p>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<p>Aucuns dossiers à saisir</p>
<?php endif; ?>

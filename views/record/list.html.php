<h1 class="border-bottom pb-2 mb-4"><i class="bi bi-folder2-open"></i> Dossiers</h1>

<?php if ($records): ?>
<div class="row row-cols-1 row-cols-md-4 g-2">
  <?php foreach($records as $i => $record): ?>
  <div class="col">
    <div class="card h-100">
      <div class="card-header">
        <?php echo $record->getConfigItem('title') ?>
      </div>
      <div class="card-body">
        <h5 class="card-title"><?php echo $record->getConfigItem('subtitle') ?></h5>
        <p class="card-text"><?php echo $record->getConfigItem('text') ?></p>
        <p class="text-center">
          <a href="/form?record=<?php echo $record->name ?>" class="btn btn-primary mt-2 d-block"><i class="bi bi-file-earmark-plus"></i> Saisir le dossier</a>
          <a href="/record/<?php echo $record->name ?>/submissions" class="mt-2 d-block">Liste des dépôts</a>
        </p>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<p>Aucuns dossiers à saisir</p>
<?php endif; ?>

<h1 class="border-bottom pb-2 mb-4"><i class="bi bi-folder2-open"></i> Dossiers</h1>

<?php if ($records): ?>
<div class="row row-cols-1 row-cols-md-4 g-2">
  <?php foreach($records as $i => $record): ?>
  <div class="col">
    <div class="card h-100">
      <div class="card-header text-end">
        <div class="dropdown">
          <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-list"></i>
          </button>
          <ul class="dropdown-menu">
            <li><a href="/record/<?php echo $record->name ?>/submissions" class="dropdown-item"><i class="bi bi-journals"></i> Liste des dépôts</a></li>
          </ul>
        </div>
      </div>
      <div class="card-body text-center">
        <h5 class="card-title"><?php echo $record->getLibelle() ?></h5>
        <a href="/form?record=<?php echo $record->name ?>" class="btn btn-primary mt-2">Saisir le dossier</a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<p>Aucuns dossiers à saisir</p>
<?php endif; ?>

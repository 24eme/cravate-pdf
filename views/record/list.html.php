<h1>Dossiers</h1>
<?php if ($records): ?>
<div class="list-group w-50">
  <?php foreach($records as $record): ?>
  <a href="/form?record=<?php echo $record->name ?>" class="list-group-item list-group-item-action"><i class="bi bi-pencil-square"></i> <?php echo $record->name ?></a>
  <?php endforeach; ?>
</div>
<?php else: ?>
<p>Aucuns dossiers à saisir</p>
<?php endif; ?>

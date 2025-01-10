<h1 class="border-bottom pb-2 mb-4"><i class="bi bi-folder2-open"></i> <?php echo $record->getLibelle() ?></h1>

<nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="/records">Dossiers</a></li>
    <li class="breadcrumb-item"><a href="#"><?php echo $record->getLibelle() ?></a></li>
    <li class="breadcrumb-item active" aria-current="page">Dépôts</li>
  </ol>
</nav>

<?php if ($submissions = $record->getSubmissions()): ?>
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Dépôt</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach($submissions as $submission): ?>
  <tr>
    <td><?php echo $submission->name ?></td>
    <td class="text-end">
      <div class="dropdown">
        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-list"></i>
        </button>
        <ul class="dropdown-menu">
          <li><a href="/download?file=<?php echo $submission->pdf ?>" class="dropdown-item" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Formulaire</a></li>
          <?php foreach ($submission->getAttachments() as $i => $attachment): ?>
          <li><a href="/download?file=<?php echo $attachment ?>" class="dropdown-item" target="_blank"><i class="bi bi-file"></i> Annexe <?php echo $i+1 ?> : <small><?php echo basename($attachment) ?></small></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p>Aucuns dépôts</p>
<?php endif; ?>

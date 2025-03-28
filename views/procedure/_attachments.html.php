<h2 class="pb-2 h3"><i class="bi bi-download"></i> Fichiers</h2>

<div class="card">
  <?php if (!isset($hidepdf)||(isset($hidepdf) && !$hidepdf)): ?>
  <ul class="list-group list-group-flush">
  <div class="card-header fw-bold">Dossier</div>
    <a class="list-group-item list-group-item-action" href="<?php echo Base::instance()->alias('procedure_submission_downloadpdf') ?>" target="_blank">
    <i class="bi bi-filetype-pdf"></i> Formulaire complété
  </a>
  </ul>
  <?php endif; ?>
  <?php foreach ($submission->getAttachments() as $category => $attachments): ?>
    <div class="card-header fw-bold">Annexe : <?php echo isset($submission->getAttachmentsCategoryConfig()[$category]) ? $submission->getAttachmentsCategoryConfig()[$category] : $category; ?></div>
    <ul class="list-group list-group-flush">
    <?php foreach($attachments as $attachment): ?>
    <a class="list-group-item list-group-item-action" href="<?php echo Base::instance()->alias('procedure_submission_downloadattachment', [], ['file' => $attachment, 'category' => $category]) ?>" target="_blank">
      <?php if (strpos($attachment, '.url') === (strlen($attachment) - 4)): ?><i class="bi bi-box-arrow-up-right"></i><?php elseif (strpos($attachment, '.pdf') === (strlen($attachment) - 4)): ?><i class="bi bi-filetype-pdf"></i><?php else: ?><i class="bi bi-file-earmark"></i><?php endif ?> <?php echo basename($attachment, '.url') ?> </a>
    <?php endforeach; ?>
    </ul>
  <?php endforeach; ?>
</div>

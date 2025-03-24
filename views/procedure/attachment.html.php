<?php echo View::instance()->render('global/etapes.html.php'); ?>

<form method="POST" enctype="multipart/form-data" action="<?php echo Base::instance()->alias("procedure_attachment", ['procedure' => $submission->procedure->name, 'submission' => $submission->id]) ?>">
  <div class="row justify-content-center">
    <div class="col-6">
    <h3 class="mt-3">Joindre une pièce complémentaire</h3>
    <ul id="attachment-list" class="list-group mt-4">
    <?php foreach($submission->getAttachmentsConfig() as $attachmentConfig): ?>
        <li class="list-group-item attachment-item" style="cursor: pointer;"   <?php if(count($submission->getAttachmentsByCategory($attachmentConfig['filename']))): ?>data-existing="true"<?php endif; ?>><i class="bi bi-square me-2"></i> <label style="cursor: pointer;"><?php echo $attachmentConfig['label'] ?></label>
        <button id="<?php echo $attachmentConfig['filename'] ?>" type="button" class="btn btn-light btn-add float-end" data-bs-toggle="modal" data-bs-target="#<?php echo $attachmentConfig['filename'] ?>_modal">Ajouter</button>
      </li>
    <?php endforeach; ?>
    </ul>
    <div class="text-end">
      <button type="submit" class="btn btn-primary mt-3">Continuer</button>
    </div>
    </div>
  </div>
  <?php foreach($submission->getAttachmentsConfig() as $attachment): ?>
    <div id="<?php echo $attachment['filename'] ?>_modal" class="modal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?php echo $attachment['label'] ?></h5>
            <button id="<?php echo $attachment['filename'] ?>_close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php if(isset($submission->getAnnexes()[$attachment['filename']]) && count($submission->getAnnexes()[$attachment['filename']])): ?>
            <p>Choisissez parmi cette liste :</p>
            <div class="card">
              <div class="card-header">
                Liste des pièces existantes
              </div>
              <div id="<?php echo $attachment['filename'] ?>_list" class="list-group list-group-flush liste-existing">
              <?php foreach($submission->getAnnexes()[$attachment['filename']] as $label => $url): ?>
                  <a href="<?php echo $url ?>" class="list-group-item list-group-item-action" target="_blank"> <input type="radio" value="<?php echo $label ?>" /> <?php echo $label ?></a>
              <?php endforeach; ?>
              </div>
              </div>
              <p class="mt-3"><strong>Ou</strong></p>
              <?php endif; ?>
              <p>Choisissez un fichier sur votre ordinateur :</p>
              <input id="<?php echo $attachment['filename'] ?>_file" type="file" class="form-control" name="<?php echo $attachment['filename'] ?>" />
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <script>
    document.querySelectorAll('.btn-add').forEach(function (item) {
      item.addEventListener('click', function (e) {
        if(!document.querySelectorAll('#'+item.id+'_list a').length) {
          document.querySelector('#'+item.id+'_file').click();
        }
      })
    });

    document.querySelectorAll('input[type="file"]').forEach(function (item) {
      item.addEventListener('change', function (e) {
        document.querySelector('#'+item.id.replace('_file', '_close')).click();
        stateListe();
      })
    });

    document.querySelectorAll('.liste-existing a').forEach(function (item) {
      item.addEventListener('click', function (e) {
        let dataTransfer = new DataTransfer();
        dataTransfer.items.add(new File([item.href], item.querySelector('input').value +'.url', {type: "text/plain"}));
        document.querySelector('#'+item.parentNode.id.replace('_list', '_file')).files = dataTransfer.files;
        e.preventDefault();
        document.querySelector('#'+item.parentNode.id.replace('_list', '_close')).click();
        stateListe();
        return false;
      })
    });

    document.querySelector('#attachment-list').addEventListener('click', function (e) {
      if(e.target.closest('.attachment-item')) {
        e.target.closest('.attachment-item').querySelector('button').click();
      }
    });

    function stateListe() {
      document.querySelectorAll('.attachment-item').forEach(function(item) {
        let btnAdd = item.querySelector('.btn-add');
        let icon = item.querySelector('.bi');
        let file = document.querySelector('#'+btnAdd.id+'_file');
        if(file.files.length || item.dataset.existing) {
          btnAdd.innerText = 'Modifier';
          icon.classList.remove('bi-square');
          icon.classList.add('bi-check-square');
          icon.classList.add('text-primary');
        } else {
          btnAdd.innerText = 'Ajouter';
          icon.classList.add('bi-square');
          icon.classList.remove('bi-check-square');
          icon.classList.remove('text-primary');
        }
      });
    }

    stateListe();
  </script>
</form>

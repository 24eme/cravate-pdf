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
            <div class="card">
              <div class="card-header">
                Liste des pièces existantes
              </div>
              <div id="<?php echo $attachment['filename'] ?>_list" class="list-group list-group-flush liste-existing">
              <?php foreach($submission->getAnnexes()[$attachment['filename']] as $label => $url): ?>
                  <label class="list-group-item list-group-item-action" style="cursor: pointer;"><input type="checkbox" value="<?php echo $label ?>" data-url="<?php echo $url ?>" /> <?php echo $label ?></label>
              <?php endforeach; ?>
                  <button type="button" class="list-group-item text-start"><i class="bi bi-upload"></i> Ajouter un document depuis votre ordinateur</button>
              </div>
            </div>
            <?php endif; ?>
            <div class="<?php if(isset($submission->getAnnexes()[$attachment['filename']]) && count($submission->getAnnexes()[$attachment['filename']])): ?>d-none<?php endif; ?>">
              <p>Choisissez un fichier sur votre ordinateur :</p>
              <input id="<?php echo $attachment['filename'] ?>_file" type="file" class="form-control" name="<?php echo $attachment['filename'] ?>" multipl="true" />
            </div>
          </div>
          <div class="modal-footer text-end">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Close">Valider</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      <?php if(isset($submission->getAnnexes()[$attachment['filename']]) && count($submission->getAnnexes()[$attachment['filename']])): ?>
      document.getElementById("<?php echo $attachment['filename'] ?>_modal").addEventListener('hidden.bs.modal', event => {
        if(event.target.querySelectorAll('input[type="checkbox"]:checked').length) {
          let dataTransfer = new DataTransfer();
          event.target.querySelectorAll('input[type="checkbox"]:checked').forEach(function(item) {
              dataTransfer.items.add(new File([item.dataset.url], item.value +'.url', {type: "text/plain"}));
          });
          event.target.querySelector('input[type="file"]').files = dataTransfer.files;
        }
      })
      document.querySelector('#<?php echo $attachment['filename'] ?>_list button').addEventListener('click', function(e) {
        document.querySelector('#<?php echo $attachment['filename'] ?>_modal input[type="file"]').click();
      });
      <?php endif; ?>
    </script>
  <?php endforeach; ?>
  <script>
    document.querySelectorAll('.btn-add').forEach(function (item) {
      item.addEventListener('click', function (e) {
        if(!document.querySelectorAll('#'+item.id+'_list label').length) {
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

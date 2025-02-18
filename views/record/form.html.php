<nav class="navbar navbar-expand-lg bg-body-tertiary" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
  <div class="container-fluid">
    <ol class="my-1 breadcrumb">
      <li class="breadcrumb-item"><a href="/records">Dossiers</a></li>
      <li class="breadcrumb-item"><a href="/record/<?php echo $record->name ?>/submissions"><?php echo $record->getConfigItem('subtitle') ?></a></li>
      <li class="breadcrumb-item">Saisie</li>
    </ol>
  </div>
</nav>

<h1>Dossier <?php echo isset($record) ? $record->getConfigItem('title') : '' ?></h1>

<?php echo View::instance()->render('global/etapes.html.php'); ?>

<?php if (Flash::instance()->hasKey('form-error')): ?>
  <?php $errors = Flash::instance()->getKey('form-error'); ?>
  <div class="alert alert-danger">
    <ul>
    <?php foreach ($errors as $error): ?>
      <li><strong><?php echo $error['field'] ?></strong> <?php echo $error['message'] ?></li>
    <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<form method="POST" class="row" action="<?php echo Base::instance()->alias('record_fill') ?>">

<?php if (Base::instance()->get('PARAMS.submission')): ?>
  <input type="hidden" value="<?php echo $submission->name ?>" name="submission">
<?php endif ?>
<input type="hidden" value="<?php echo $record->pdf ?>" name="file">

<div class="col-6 offset-3 mt-3 justify-content-center">
  <?php foreach($submission->getFields() as $category => $fields): ?>
  <h3><?php echo $category; ?></h3>
  <?php foreach($fields as $id => $field): ?>
  <?php if ($field['type'] == 'text'): ?>
    <div class="form-floating mb-3">
    <input type="text" class="form-control" name="<?php echo $id ?>" id="<?php echo $field['type'].'_'.$id ?>" required="required" value="<?php echo isset($submission->getDatas()[$id]) ? $submission->getDatas()[$id] : null ?>">
      <label for="<?php echo $field['type'].'_'.$id ?>"><?php echo $field['label'] ?></label>
    </div>
  <?php endif; ?>
  <?php if ($field['type'] == 'select'): ?>
    <div class="form-floating mb-3">
      <select class="form-select" name="<?php echo $id ?>" id="floatingSelect" aria-label="<?php echo $field['label'] ?>">
        <option selected>Sélectionner une appellation</option>
        <?php foreach($field['choices'] as $choiceKey => $choiceLabel): ?>
        <option value="<?php echo $choiceKey ?>"><?php echo $choiceLabel ?></option>
        <?php endforeach; ?>
      </select>
      <label for="floatingSelect"><?php echo $field['label'] ?></label>
    </div>
  <?php endif; ?>
  <?php if ($field['type'] == 'radio'): ?>
    <div class="mb-3">
      <label class="form-label"><?php echo $field['label'] ?> :</label>
      <?php foreach($field['choices'] as $choiceKey => $choiceLabel): ?>
      <div class="form-check">
        <input class="form-check-input" name="<?php echo $id ?>" value="<?php echo $choiceKey ?>" id="option_<?php echo $id ?>_<?php echo $choiceKey ?>" type="radio" required="required" <?php echo isset($submission->getDatas()[$id]) && $submission->getDatas()[$id] === $choiceKey ? "checked" : null ?>>
        <label class="form-check-label" for="option_<?php echo $id ?>_<?php echo $choiceKey ?>">
          <?php echo $choiceLabel ?>
        </label>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php if ($field['type'] == 'checkbox'): ?>
    <div class="mb-3">
      <label class="form-label"><?php echo $field['label'] ?> :</label>
      <?php foreach($field['choices'] as $choiceKey => $choiceLabel): ?>
      <div class="form-check">
        <input class="form-check-input" name="<?php echo $id ?>" value="<?php echo $choiceKey ?>" id="option_<?php echo $id ?>_<?php echo $choiceKey ?>" type="checkbox" <?php echo isset($submission->getDatas()[$id]) && $submission->getDatas()[$id] === $choiceKey ? "checked" : null ?>>
        <label class="form-check-label" for="option_<?php echo $id ?>_<?php echo $choiceKey ?>">
          <?php echo $choiceLabel ?>
        </label>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php endforeach; ?>
  <?php endforeach; ?>

  <div class="text-end">
    <button type="submit" class="btn btn-primary">Valider</button>
  </div>

</div>

</form>

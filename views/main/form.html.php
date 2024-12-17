<form method="POST" class="row" action="/fill">

<input type="hidden" value="<?php echo Base::instance()->get('GET.pdf') ?>" name="file">

<div class="col-6 offset-3 mt-3 justify-content-center">
  <?php foreach($pdfForm->getFields() as $field): ?>
  <?php if ($field->getType() == \PDF\PDFFormField::TYPE_TEXT): ?>
    <div class="form-floating mb-3">
      <input type="text" class="form-control" name="<?php echo $field->getName() ?>" id="<?php echo $field->getId() ?>" <?php if($field->isRequired()): ?>required="required"<?php endif; ?>>
      <label for="<?php echo $field->getId() ?>"><?php echo $field->getLabel() ?></label>
    </div>
  <?php endif; ?>
  <?php if ($field->getType() == \PDF\PDFFormField::TYPE_SELECT): ?>
    <div class="form-floating mb-3">
      <select class="form-select" name="<?php echo $field->getName() ?>" id="floatingSelect" aria-label="<?php echo $field->getLabel() ?>" <?php if($field->isRequired()): ?>required="required"<?php endif; ?>>
        <option selected>Sélectionner une appellation</option>
        <?php foreach($field->getChoices() as $choice): ?>
        <option value="<?php echo $choice ?>"><?php echo $choice ?></option>
        <?php endforeach; ?>
      </select>
      <label for="floatingSelect"><?php echo $field->getLabel() ?></label>
    </div>
  <?php endif; ?>
  <?php if ($field->getType() == \PDF\PDFFormField::TYPE_RADIO): ?>
    <div class="mb-3">
      <label class="form-label"><?php echo $field->getLabel() ?></label>
      <?php foreach($field->getChoices() as $choice): ?>
      <div class="form-check">
        <input class="form-check-input" name="<?php echo $field->getName() ?>" value="<?php echo $choice ?>" id="option_<?php echo $choice ?>" type="radio" <?php if($field->isRequired()): ?>required="required"<?php endif; ?>>
        <label class="form-check-label" for="option_<?php echo $choice ?>">
          <?php echo $choice ?>
        </label>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php if ($field->getType() == \PDF\PDFFormField::TYPE_CHECKBOX): ?>
    <div class="mb-3">
      <label class="form-label"><?php echo $field->getLabel() ?></label>
      <?php foreach($field->getChoices() as $choice): ?>
      <div class="form-check">
        <input class="form-check-input" name="<?php echo $field->getName() ?>" id="option_<?php echo $choice ?>" type="checkbox" <?php if($field->isRequired()): ?>required="required"<?php endif; ?>>
        <label class="form-check-label" for="option_<?php echo $choice ?>">
          <?php echo $choice ?>
        </label>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($DEBUG): ?>
    <div class="mb-3 p-2 bg-body-tertiary border rounded-3 opacity-50">
      <?php echo implode(' ⋅ ', ["Nom : ".$field->getName(), "Identifiant : ".$field->getId(), "Label : ".$field->getLabel()]); ?>
    </div>
  <?php endif ?>

  <?php endforeach; ?>

  <div class="text-end">
    <button type="submit" class="btn btn-primary">Écrire</button>
  </div>

</div>

</form>

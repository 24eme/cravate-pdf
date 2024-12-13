<div class="row mt-3 justify-content-center">
  <div class="col-6">
  <?php foreach($pdfForm->getFields() as $field): ?>
  <?php if ($field->getType() == \PDF\PDFFormField::TYPE_TEXT): ?>
    <div class="form-floating mb-3">
      <input type="text" class="form-control" name="<?php echo $field->getName() ?>" id="<?php echo $field->getId() ?>">
      <label for="<?php echo $field->getId() ?>"><?php echo $field->getLabel() ?></label>
    </div>
  <?php endif; ?>
  <?php if ($field->getType() == \PDF\PDFFormField::TYPE_SELECT): ?>
    <div class="form-floating mb-3">
      <select class="form-select" id="floatingSelect" aria-label="Floating label select example">
        <option selected>Séléctionner une appellation</option>
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
        <input class="form-check-input" name="<?php echo $field->getName() ?>" id="option_<?php echo $choice ?>" type="radio">
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
        <input class="form-check-input" name="<?php echo $field->getName() ?>" id="option_<?php echo $choice ?>" type="checkbox">
        <label class="form-check-label" for="option_<?php echo $choice ?>">
          <?php echo $choice ?>
        </label>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php endforeach; ?>
  </div>
</div>

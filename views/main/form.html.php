<div class="row mt-3 justify-content-center">
  <div class="col-6">
<?php foreach($pdfForm->getDataFields() as $field): ?>
<?php if ($field['FieldType'] == 'Text'): ?>
  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="floatingInput" placeholder="">
    <label for="floatingInput"><?php echo $field['FieldName'] ?></label>
  </div>
<?php endif; ?>
<?php if ($field['FieldType'] == 'Choice'): ?>
  <div class="form-floating mb-3">
    <select class="form-select" id="floatingSelect" aria-label="Floating label select example">
      <option selected>Séléctionner une appellation</option>
      <?php foreach($field['FieldStateOption'] as $value): ?>
      <option value="<?php echo $value ?>"><?php echo $value ?></option>
      <?php endforeach; ?>
    </select>
    <label for="floatingSelect"><?php echo $field['FieldName'] ?></label>
  </div>
<?php endif; ?>
<?php if ($field['FieldType'] == 'Button' && $field['FieldFlags'] > 0 && isset($field['FieldStateOption'])): ?>
  <div class="mb-3">
  <label class="form-label"><?php echo $field['FieldName'] ?></label>
  <?php foreach($field['FieldStateOption'] as $value): ?>
  <?php if($value === "Off"): continue; endif; ?>
  <div class="form-check">
    <input class="form-check-input" name="<?php echo $field['FieldName'] ?>" id="option_<?php echo $value ?>" type="radio">
    <label class="form-check-label" for="option_<?php echo $value ?>">
      <?php echo $value ?>
    </label>
  </div>
  <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php if ($field['FieldType'] == 'Button' && $field['FieldFlags'] == 0 && isset($field['FieldStateOption'])): ?>
  <div class="mb-3">
  <label class="form-label"><?php echo $field['FieldName'] ?></label>
  <?php foreach($field['FieldStateOption'] as $value): ?>
  <?php if($value === "Off"): continue; endif; ?>
  <div class="form-check">
    <input class="form-check-input" name="<?php echo $field['FieldName'] ?>" id="option_<?php echo $value ?>" type="checkbox">
    <label class="form-check-label" for="option_<?php echo $value ?>">
      <?php echo $value ?>
    </label>
  </div>
  <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php endforeach; ?>
  </div>
</div>

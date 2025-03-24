<table class="table table-striped table-hover">
<?php $formConfig = $submission->procedure->getConfigItem('form'); ?>
<?php foreach($submission->getDatas() as $field => $value): ?>
<tr>
<th class="col-3"><?php echo isset($formConfig[$field]) ? $formConfig[$field]['label'] : $formConfig[$field] ?></th>
<?php if (array_key_exists('format', $formConfig[$field])): ?>
<td><?php echo preg_replace(
                 strtok($formConfig[$field]['format'], '#'),
                 strtok('#'),
                 $value
               ) ?>
</td>
<?php else: ?>
<td>
  <?php if (isset($formConfig[$field]['choices']) && isset($formConfig[$field]['choices'][$value])): ?>
    <?php echo $formConfig[$field]['choices'][$value]; ?>
  <?php else: ?>
    <?php echo $value; ?>
  <?php endif; ?>
</td>
<?php endif ?>
</tr>
<?php endforeach; ?>
</table>

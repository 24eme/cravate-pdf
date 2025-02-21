<?php if ($headerLink = $config->get('header_link')): ?>
<div class="container themeHeaderContainer">
  <?php echo file_get_contents(sprintf($headerLink, $_SESSION['etablissement_id'])); ?>
</div>
<?php endif; ?>

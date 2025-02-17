<?php if ($footerLink = $config->get('footer_link')): ?>
<div class="container themeFooterContainer">
  <?php echo file_get_contents($footerLink); ?>
</div>
<?php endif; ?>

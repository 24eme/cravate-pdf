<?php if ($headerLink = $config->get('header_link')): ?>
<div class="container themeHeaderContainer">
  <?php echo file_get_contents(sprintf($headerLink, User::instance()->getUserId(), User::instance()->isAdmin())); ?>
</div>
<?php endif; ?>

<ul class="step list-unstyled">
  <?php foreach ($steps as $step => $active): ?>
    <li class="step-item<?php echo $active ? ' active' : '' ?>">
      <a href="#"><?php echo $step ?></a>
    </li>
  <?php endforeach ?>
</ul>

<ul class="step list-unstyled">
  <?php foreach ($steps->getSteps() as $step): ?>
    <li class="step-item<?php echo $step->isActive() ? ' active' : '' ?>">
      <a href="<?php echo $step->link() ?: '#' ?>">
        <?php echo $step ?>
      </a>
    </li>
  <?php endforeach ?>
</ul>

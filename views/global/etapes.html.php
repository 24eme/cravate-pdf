<ul class="step list-unstyled">
  <?php foreach ($steps->getSteps() as $step): ?>
    <li class="step-item<?php echo $step->isActive() ? ' active' : '' ?>">
    <?php if ($step->link()): ?>
      <a href="<?php echo Base::instance()->alias($step->link(), $steps->getLinkArgs()) ?>">
        <?php echo $step ?>
      </a>
    <?php else: echo $step; endif ?>
    </li>
  <?php endforeach ?>
</ul>

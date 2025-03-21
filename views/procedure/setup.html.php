<?php use Config\Config; ?>
<h1>Configuration</h1>
<h2 class="my-4">Installation serveur</h2>
<table class="table">
  <tbody>
    <tr>
    <th class="align-top">module xxxx</th>
    <td class="text-muted">xxxx</td>
        <td>
            <i class="bi bi-check-square text-success"></i>
        </td>
    </tr>
    <tr>
    <th class="align-top">module xxxx</th>
    <td class="text-muted">xxxx</td>
        <td>
          <span class="text-warning">
          <i class="bi bi-exclamation-circle"></i>
          (xxx needed)
          </span>
        </td>
    </tr>
    <tr>
    <th class="align-top">module xxxx</th>
    <td class="text-muted">xxxx</td>
        <td>
            <span class="text-danger">
            <i class="bi bi-exclamation-octagon-fill"></i>
            (xxx needed)
            </span>
        </td>
    </tr>
<tr>
    <th class="align-top">configuration complète</th>
    <td class="text-muted">config/config.php</td>
    <td>
        <?php if (file_exists($config->getConfigFile())): ?>
        <i class="bi bi-check-square text-success"></i>
        <?php else: ?>
        <span class="text-danger">
        <i class="bi bi-exclamation-octagon-fill"></i>
        (voir plus bas)
        </span>
        <?php endif; ?>
    </td>
</tr>
</tbody>
</table>
<h2 class="my-4">Fichier de configuration</h2>
<div class="align-center">
<table class="table">
  <tbody>
    <tr>
        <th class="align-top">Mon option de configuration 1</th>
        <td class="text-muted">option_config_1</td>
        <td><?php echo Config::getInstance()->get('option_config_1', '<span class="text-danger">Non renseigné</span>'); ?></td>
    </tr>
    <tr>
        <th class="align-top">Données brutes</th>
        <td class="text-muted">config/config.php</td>
        <td><textarea readonly><?php if(file_exists($config->getConfigFile())): ?><?php echo file_get_contents($config->getConfigFile()); ?><?php endif; ?></textarea></td>
    </tr>
  </tbody>
</table>
</div>

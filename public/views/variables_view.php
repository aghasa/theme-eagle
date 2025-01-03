<?php defined('ABSPATH') or die(); ?>
<link rel="stylesheet" href="<?php echo URL_ROOT ?>public/css/daterium-admin.css" />
<script src="<?php echo URL_ROOT ?>public/script/admin_function.js"></script>
<div class="wrap">
  <h1 class="wp-heading-inline">Variables de entorno</h1>
</div>

<table class="wp-list-table widefat fixed striped table-view-list users">
  <thead>
    <tr>
      <th scope="col" class="manage-column column-name"><span>Nombre</span></th>
      <th scope="col" class="manage-column column-name">Valor</th>
      <th scope="col" class="manage-column column-posts num">Modificar</th>
    </tr>
  </thead>

  <tbody id="the-list">
    <?php
    foreach ($variables as $variable) {
    ?>
      <tr>
        <td class="name column-name" style="vertical-align: inherit;">
          <strong><?php echo $variable->codigo; ?></strong>
        </td>
        <td class="name column-name" style="vertical-align: inherit;">
          <strong><?php echo $variable->valor; ?></strong>
        </td>
        <td class="posts column-name" style="vertical-align: inherit;">
          <img class="daterium-admin-img-variable" onclick="open_modal('<?php echo $variable->codigo; ?>')" src="<?php echo URL_ROOT; ?>public/img/setting.svg" />
        </td>
      </tr>
      <div id="<?php echo $variable->codigo; ?>" class="daterium-admin-modalmask">
        <div class="daterium-admin-modalbox daterium-admin-movedown">
          <div class="daterium-admin-modalhead">
            <h1>Variable a modificar</h1>
            <img onclick="cerrar_modal('<?php echo $variable->codigo; ?>')" id="cerrar" src="<?php echo URL_ROOT ?>public/img/close.svg" alt="cerrar">
          </div>
          <p class="daterium-admin-text-variable"><?php echo $variable->codigo; ?></p>
          <h3>Introduzca nuevo valor</h3>
          <form class="form-table" action="" method="post" onclick="cerrar_modal('<?php echo $variable->codigo; ?>')" type="hidden" method="post">
            <input type="hidden" id="cod" name="cod" value="<?php echo $variable->codigo ?>" />
            <div class="daterium-admin-div-env-var">
              <input class="daterium-admin-input-env-var" type="text" id="valor_nuevo" name="valor_nuevo" value="" placeholder="..." required />
              <p class="daterium-admin-sin-mar-pad "><input type="submit" id="pulsado" name="pulsado" class="button button-primary" value="Enviar"></p>
            </div>
          </form>
        </div>
      </div>
    <?php }  ?>
  </tbody>
</table>



<?php

if (isset($_POST['pulsado'])) {
  require_once(DATERIUM_PLUGIN_DIR . 'variable_controller.php');

  if ($insertado == 1) {
    echo "<script>alert('Modificado correctamente');</script>";
    echo "<script>location.reload();</script>";
  } else {
    echo "<script>alert('Fallo al insertar el nuevo distribuidor');</script>";
  }
}
?>
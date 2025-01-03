<?php
defined('ABSPATH') or die();

if ($carga_bloque_erronea == false && count($familias) > 0) {
?>
    <div class="daterium-bloque-familias">
        <?php foreach ($familias as $familia) { ?>
            <a class="daterium-familia" href="productos/familia/<?php echo $familia['id'] ?>">
                <h5 class="daterium-familia-nombre">
                    <?php echo $familia['nombre']; ?>
                </h5>
            </a>
        <?php } ?>
    </div>
<?php } ?>

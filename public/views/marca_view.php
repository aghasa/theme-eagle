<?php
defined('ABSPATH') or exit;
?>
<script>
    let familyActive = 0;
</script>
<main id="primary" class="full-main">
    <?php if ($carga_marca_erronea == false) { ?>
        <header class="page-header<?php if (has_post_thumbnail()) { ?> page-header-thumbnail<?php } ?>">
            <div class="container-page-header">
                <div class="title-page-header">
                    <div class="wrap <?php if (has_post_thumbnail()) { ?>title-page-thumbnail<?php } ?>">
                    </div>
                </div>
            </div>
        </header>

        <div class="wrap">
        
            <div class="daterium-brand-box">
                <div class="daterium-brand-menu">
                    <div class="daterium-brand-menu-container">
                        <ul class="daterium-brand-menu-list">
                            <?php
                            $familiaId = 1;
                            foreach ($productos_hijos as $familia => $productos_familia) {
                                ?>
                                <li id="bmf<?php echo $familiaId ?>" class="daterium-brand-menu-list-item"
                                    onclick="mostrarFamilia('mf<?php echo $familiaId ?>')">
                                    <h5 class="daterium-brand-menu-list-title">
                                        <a alt="<?php echo $familia; ?>" data-original-text="<?php echo $familia; ?>">
                                            <?php echo $familia; ?>
                                        </a>
                                        
                                    </h5>
                                </li>
                                <?php
                                ++$familiaId;
                            }
                            ; ?>
                        </ul>
                    </div>
                    <?php
                    $familiaIdSubfamilia = 1;
                    foreach ($productos_hijos as $familia => $productos_familia) {
                        $subfamiliaId = 0; ?>

                        <ul class="daterium-subfamilia-list" id="smf<?php echo $familiaIdSubfamilia ?>">
                            <?php
                            foreach ($productos_familia as $subfamilia => $productos_subfamilia) {
                                ?>
                                <li id="menu-familia-<?php echo $subfamiliaId ?>" class="daterium-brand-menu-subitem">
                                    <a href="#mf<?php echo $familiaIdSubfamilia; ?>sf<?php echo $subfamiliaId; ?>" alt="<?php echo $subfamilia; ?>">
                                        <?php echo $subfamilia; ?>
                                    </a>
                                </li>
                                <?php
                                ++$subfamiliaId;
                            }
                            ?>

                        </ul><?php
                        ++$familiaIdSubfamilia;
                    }
                    ?>
                </div>
                <div class="daterium-brand">
                    <?php
                    $familiaId = 1;
                    foreach ($productos_hijos as $familia => $productos_familia) {
                        ?>
                        <div class="daterium-brand-family" data-menu-text="<?php echo $familia; ?>"
                            id="mf<?php echo $familiaId; ?>">
                            <h2 class="daterium-brand-title"><?php echo $familia; ?></h2>
                            <?php
                            $subfamiliaId = 0;
                            foreach ($productos_familia as $subfamilia => $productos_subfamilia) { ?>

                                <div class="daterium-brand-subfamily"
                                    id="mf<?php echo $familiaId; ?>sf<?php echo $subfamiliaId; ?>">
                                    <div class="daterium-brand-toggle"
                                        subfamily-id="mf<?php echo $familiaId; ?>sf<?php echo $subfamiliaId; ?>">
                                        <div class="daterium-brand-subtitle-box">
                                            <h4 class="daterium-brand-subtitle"><span>&#8213; </span><?php echo $subfamilia; ?></h4>
                                        </div>
                                    </div>
                                    <div class="daterium-lista-categorias-container">
                                        <div class="daterium-lista-categorias daterium-lista-marcas">
                                            <?php foreach ($productos_subfamilia as $apartado) { ?>
                                                <a href="<?php echo $apartado['url']; ?>" title="<?php echo $apartado['nombre']; ?>"
                                                    class="daterium-categoria" id="<?php echo $apartado['id']; ?>">
                                                    <div class="daterium-list-inner">
                                                        <img loading="auto" src="<?php echo $apartado['imagen']; ?>"
                                                            alt="<?php echo $apartado['nombre']; ?>" />
                                                        <div class="daterium-list-title-container">
                                                            <h5 class="daterium-list-title">
                                                                <?php echo $apartado['nombre']; ?>
                                                            </h5>
                                                            <div class="daterium-flecha-contenedor">
                                                                <span class="daterium-flecha-producto">→</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <?php $subfamiliaId++;
                            } ?>
                        </div>
                        <?php
                        ++$familiaId;
                    } ?>
                </div>

            </div>
        </div>
        <?php
        if ($param_familia != 0) {
            ?>
            <script>
                familyActive = <?php echo $param_familia ?>;
                mostrarFamilia('mf<?php echo $param_familia ?>');
                mostrarSubfamilias('mf<?php echo $param_familia ?>');
            </script>
            <?php
        }
        ?>
    <?php } else {
        echo '<h3 style="text-align: center;">No es posible conectar con el catálogo online</h3>';
    } ?>
</main>
<?php defined('ABSPATH') or die();

if ($carga_busqueda_erronea == false) {
    if (count($datos_productos) > 0) { ?>
        <h3 class="daterium-titulo-busqueda">Mostrando resultados de: <?php echo $info; ?></h3>
        <div class="wrap">
            <div class="daterium-lista-categorias-busqueda">
                <?php foreach ($datos_productos as $dato) { ?>
                    <div class="daterium-categoria" id="<?php echo $dato["pID"] ?>">
                        <a href="<?php echo get_permalink() ?>/<?php echo $dato["pID"] . '/' . $dato["url"]; ?>"
                            alt="<?php echo $dato["nombre"]; ?>">
                            <div class="daterium-list-inner">
                                <img src="<?php echo $dato["imagen"]; ?>" decoding="async" alt="<?php echo $dato["nombre"]; ?>" />
                                <div class="daterium-list-title-container">
                                    <h5 class="daterium-list-title">
                                        <?php echo $dato["nombre"]; ?>
                                    </h5>
                                    <div class="daterium-flecha-contenedor">
                                        <span class="daterium-flecha-producto">→</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>

    <?php } else { ?>
       <?php global $daterium_page_slug; ?> 
       <!-- es necesario??? -->
        <div class="vista-busqueda-erronea">


            <div class="main-error404">
                <div class="error-404 not-found">
                    <header class="page-header">
                        <h2 class="page-title">Producto no encontrado</h2>
                    </header>
                    <div class="error-content">
                        <br>
                        <h3 class="resaltado-buscador">La búsqueda de <span class="txt-resultado"><?php echo $info; ?></span> no
                            obtuvo ningún resultado.</h3>
                        <br>
                    </div>
                </div>

                <div class="daterium-buscador-search-page" id="daterium-buscador-container">
                    <form class="daterium-buscador" action="/<?php echo $daterium_page_slug ?>/" method="post">
                        <input autofocus required autocomplete="off" autocorrect="off" spellcheck="false"
                            class="daterium-buscador-input" type="text" id="daterium-input-search" name="daterium_search"
                            value="" alt="Buscar un producto..." title="Buscar un producto..." placeholder="Buscar un producto..." />
                    </form>
                </div>

            </div>
        <?php }
} else {
    echo '<h3 style="text-align: center;">No es posible conectar con el catálogo online</h3>';
}

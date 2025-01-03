<?php
defined('ABSPATH') or exit;
$pID = intval($producto);

include_once 'public/models/metodos_daterium.php';
$metodos_daterium = new Metodos_Daterium();

require_once DATERIUM_PLUGIN_DIR . 'public/models/metodos_bbdd.php';
$metodos_bbdd = new Metodos_bbdd();

global $daterium_userid;
global $daterium_id_marca;
global $daterium_idioma;

$url_producto = 'https://api.dateriumsystem.com/producto_fc_xml.php?pID=' . $pID . '&userID=' . $daterium_userid . '&lang=' . $daterium_idioma;

$xml_producto = $metodos_daterium->daterium_get_data_url($url_producto);
$carga_producto_erronea = true;

if ($xml_producto != 'error') {
    if (!empty($xml_producto)) {
        $imagenes_producto = $metodos_daterium->get_images($xml_producto);

        $nombre_producto = $xml_producto->nombre;

        $nombre_marca = $xml_producto->marca;
        $logo_marca = $xml_producto->logo_marca;
        $id_marca = $xml_producto->marca['id'];
        $atributo_discriminante = $xml_producto->atributos->atributo1;

        $familia = $xml_producto->familia;
        $subfamilia = $xml_producto->subfamilia;

        $rutas_para_categoria = $xml_producto->xpath('rutas/otrasrutas/ruta/paso');
        $pos_catID_producto = count($rutas_para_categoria) - 1;
        $catID = $rutas_para_categoria[$pos_catID_producto]->catID;

        $referencias_xml = $xml_producto->xpath('referencias/referencia');
        $referencias = [];
        $k = 0;
        foreach($referencias_xml as $referencia) {
            if(strval($referencia->estado_articulo->estado) == 'activo'){
                $referencias[$k] = $referencia;
                $k++;
            }
        }
        $puntos_clave = $metodos_daterium->get_puntos_clave($xml_producto);
        $descripcion = $metodos_daterium->get_descripcion($xml_producto);

        $descripcion_des = $metodos_daterium->clean_descripcion($descripcion);

        $descripcion_texto_plano = $metodos_daterium->clean_descripcion($xml_producto->descripcion_textoplano);

        $datos_referencias = $metodos_daterium->get_datos_referencias($xml_producto);
        $url = htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
        $script_schema_producto = $metodos_daterium->get_script_ProductGroup($nombre_marca, $descripcion_texto_plano, $url, $datos_referencias, $familia, $subfamilia);

        $ruta_aecoc = $metodos_daterium->get_clasificacion_aecoc($xml_producto);

        $adjuntos = $metodos_daterium->get_adjuntos($xml_producto);
        $numero_adjuntos = count($adjuntos);

        $relacionados_xml = $xml_producto->xpath('productos_relacionados/pID');
        $datos_relacionados = $metodos_daterium->get_related($relacionados_xml, $daterium_userid, 6);
        $numero_relacionados = count($datos_relacionados);

        $videos = $xml_producto->xpath('videos/video');
        $numero_videos = count($videos);
        $da_re_json = json_encode($datos_referencias);
        $codificado = base64_encode($da_re_json);
        ?>
        <script type="text/javascript">
            let jsVar_producto = <?php echo json_encode($codificado); ?>;
        </script>
        <?php

        if ($daterium_id_marca == $id_marca) {
            $pid_erroneo = false;
        } else {
            $pid_erroneo = true;
        }
    } else {
        $pid_erroneo = true;
    }
    $carga_producto_erronea = false;
} else {
    $carga_producto_erronea = true;
}
require_once DATERIUM_PLUGIN_DIR . 'public/views/producto_view.php';
?>
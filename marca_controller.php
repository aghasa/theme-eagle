<?php

defined('ABSPATH') or exit;
global $daterium_userid;
global $daterium_id_marca;
global $daterium_idioma;
$productos_hijos = [];

$carga_marca_erronea = true;

$url_marca = 'https://api.dateriumsystem.com/productos_marca_xml.php?idmarca=' . $daterium_id_marca . '&userID=' . $daterium_userid . '&lang=' . $daterium_idioma;

$xml_marca = $metodos_daterium->daterium_get_data_url($url_marca);

if ($xml_marca != 'error') {
    $productos_hijos = $metodos_daterium->daterium_get_list_products_marca($xml_marca);

    $carga_marca_erronea = false;

} else {
    echo 'Hubo un error al cargar la marca.<br>';
    $carga_marca_erronea = true;
}
include DATERIUM_PLUGIN_DIR . 'public/views/marca_view.php';
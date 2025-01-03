
<?php
defined('ABSPATH') or exit;

if (!empty($_POST['daterium_search'])) {
    global $daterium_userid;
    global $daterium_nombre_marca;
    

    $carga_busqueda_erronea = true;

    $datos_productos = [];
    $info = filter_var(trim(string: $_POST['daterium_search']), FILTER_SANITIZE_SPECIAL_CHARS);
    $info = substr($info, 0, 1000);
    $info_url = str_replace(' ', '%20', $info);
    if ($info != '') {

        $url_busqueda = 'https://api.dateriumsystem.com/busqueda_avanzada_fc_xml.php?marcas[]=' . $daterium_nombre_marca . '&userID=' . $daterium_userid . '&searchbox=' . $info_url . '&limite=300&ref=0';
        $xml_busqueda = $metodos_daterium->daterium_get_data_url($url_busqueda);

        if ($xml_busqueda != 'error') {
            $productos_xml = $xml_busqueda->xpath('resultados/ficha');
            foreach ($productos_xml as $producto) {
                $url_producto = $metodos_daterium->daterium_url_title($producto->nombre);
                array_push($datos_productos, ['pID' => $producto->id, 'nombre' => $producto->nombre, 'imagen' => $producto->img500x500, 'url' => $url_producto, 'descripcion' => $producto->descripcion]);
            }
            $carga_busqueda_erronea = false;
        } else {
            $carga_busqueda_erronea = true;
        }
    }
    include DATERIUM_PLUGIN_DIR . './public/views/busqueda_view.php';
} else {
    global $daterium_userid;
    $carga_categoria_erronea = true;

    $url_marca = 'https://api.dateriumsystem.com/productos_marca_xml.php?idmarca=' . $daterium_id_marca . '&userID=' . $daterium_userid;
    $xml_marca = $metodos_daterium->daterium_get_data_url($url_marca);


    if ($xml_marca != 'error') {
        $productos_hijos = $metodos_daterium->daterium_get_list_products_marca($xml_marca);

        $carga_marca_erronea = false;

    } else {
        echo 'Hubo un error al cargar la marca.<br>';
        $carga_marca_erronea = true;
    }
    // include DATERIUM_PLUGIN_DIR . './public/views/marca_view.php';
}
?>


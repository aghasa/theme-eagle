<?php

defined('ABSPATH') or die();


/**
 * Función para añadir el head
 */
function daterium_get_title($producto, $userID, $marcaID)
{
    global $imagen;

    if ($producto <> 0) {

        $xml = simplexml_load_file("https://api.dateriumsystem.com/producto_mini_fc_xml.php?pID=" . $producto . "&userID=" . $userID . "&ref=0");
        $xml_marca = $xml-> marca['id'];

        if($xml_marca == $marcaID){
            return isset($xml->nombre) ? strval($xml->nombre) : "Catálogo" ;
        }else{
            return "Producto no encontrado" /*. " – " . get_bloginfo("name")*/ ;
        }      

    } else {
        return "Productos" /*. " – " . get_bloginfo("name")*/ ;
    }
}

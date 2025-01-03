<?php

/**
 * Plugin Name: Daterium (Brand)
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Description: Conexión al catálogo de Daterium, solo para marca
 * Version: 1.0.1
 * Author: Alberto Rodríguez
 * Text Domain: daterium-brand
 */


/*****************************************************************
 * Definición de variables globales a utilizar en todo el plugin *
 ****************************************************************/
defined('ABSPATH') or die();

if (!defined('DATERIUM_PLUGIN_DIR'))
    define('DATERIUM_PLUGIN_DIR', plugin_dir_path(__FILE__));

define('URL_ROOT', plugins_url('/', __FILE__));


require_once(DATERIUM_PLUGIN_DIR . 'public/models/metodos_bbdd.php');
$metodos_bbdd = new Metodos_bbdd();
$daterium_userid;
$daterium_userid = $metodos_bbdd->get_id_rf();
$daterium_id_marca;
$daterium_id_marca = $metodos_bbdd->get_id_marca();
$daterium_nombre_marca;
$daterium_nombre_marca = $metodos_bbdd->get_nombre_marca();
$imagen = "";
$daterium_page_id;
$daterium_page_id = $metodos_bbdd->get_daterium_page_id();
$daterium_page_slug;
$daterium_page_slug = get_post_field('post_name', $daterium_page_id);
$daterium_idioma;
// $local = get_locale();
// echo 'locale' . $local;
// $posicion = strpos($local, '_');
// $daterium_idioma = substr($local, 0, $posicion);


/*******************************
 * Definición de los shortcode *
 ******************************/
add_shortcode('daterium_catalogo', 'daterium_print');
add_shortcode('daterium_bloque_familias', 'daterium_bloque_familias_print');


// Función para obtener el código del idioma actual con TranslatePress
function translatepress_cod_idioma_actual()
{
    global $daterium_idioma;
    
    $local = get_locale();
    $posicion = strpos($local, '_');
    $daterium_idioma = substr($local, 0, $posicion);
}

add_action('wp', 'translatepress_cod_idioma_actual');



/************************************************
 * Funciones para crear el bloque de familias *
 ***********************************************/
function daterium_bloque_familias()
{

    include_once(DATERIUM_PLUGIN_DIR . 'public/models/metodos_daterium.php');
    $metodos_daterium = new Metodos_Daterium();
    $carga_bloque_erronea = true;

    global $daterium_idioma;


    $url_bloque_familias = URL_ROOT . "public/daterium-familias.xml";

    if (file_exists(DATERIUM_PLUGIN_DIR . 'public/daterium-familias.xml')) {

        $xml_bloque_familias = simplexml_load_file(DATERIUM_PLUGIN_DIR . 'public/daterium-familias.xml');

        $familias = [];
        foreach ($xml_bloque_familias->familia as $familia) {
            $nombre = '';
            foreach ($familia->nombre as $nombre_xml) {
                if ((string) $nombre_xml['lang'] === $daterium_idioma) {
                    $nombre = (string) $nombre_xml;
                    break;
                }
            }
            if (empty($nombre)) {
                $nombre = (string) $familia->nombre[0];
            }

            $familias[] = [
                'id' => (int) $familia['id'],
                'nombre' => $nombre,
                'imagen' => (string) $familia->imagen
            ];
        }
        $carga_bloque_erronea = false;
    } else {
        $carga_bloque_erronea = true;
    }
    include_once(DATERIUM_PLUGIN_DIR . 'public/views/bloque_familia_view.php');
}

function daterium_bloque_familias_print()
{
    ob_start();
    daterium_bloque_familias();
    return ob_get_clean();
}

/**********************************************
 * Función principal que contrala el catálogo *
 *********************************************/
function daterium()
{
    global $daterium_page_id;

    global $producto_variable;
    include_once(DATERIUM_PLUGIN_DIR . 'public/models/metodos_daterium.php');
    $metodos_daterium = new Metodos_Daterium();

    $estado_daterium = $metodos_daterium->get_status_daterium();
    if ($estado_daterium == 'activo') {
        require_once(DATERIUM_PLUGIN_DIR . 'public/models/metodos_bbdd.php');
        $metodos_bbdd = new Metodos_bbdd();

        $producto = intval(get_query_var('producto'));
        $param_familia = intval(get_query_var('familia'));

        $request_uri = $_SERVER['REQUEST_URI'];

        //$id_pagina_idioma = icl_object_id($daterium_page_id, 'page', true, ICL_LANGUAGE_CODE);

        if (get_the_ID() == $daterium_page_id) {
            if (!empty($_POST['daterium_search'])) {
                require_once(DATERIUM_PLUGIN_DIR . 'busqueda_controller.php');
            } elseif ($producto != 0 && $producto != null) {
                require_once(DATERIUM_PLUGIN_DIR . 'producto_controller.php');
            } else {
                require_once(DATERIUM_PLUGIN_DIR . 'marca_controller.php');
            }
        }
    } else {
        echo '<h3 style="text-align: center;">No es posible conectar con el catálogo online</h3>';
    }
}

function daterium_print()
{
    ob_start();
    daterium();
    return ob_get_clean();
}


/*************************************************
 * Función para modificar el titulo de la página *
 ************************************************/
function set_title($title)
{
    global $daterium_userid;
    global $daterium_id_marca;

    $producto = intval(get_query_var('producto'));

    if ($producto != 0) {
        require_once(DATERIUM_PLUGIN_DIR . 'title_controller.php');
        $title = daterium_get_title($producto, $daterium_userid, $daterium_id_marca);
    }
    return $title;
}
add_filter("pre_get_document_title", "set_title", 20);


/*************************************************
 * Función para eliminar los links shortlink y 
 * canonical de la cabecera *
 ************************************************/
function daterium_remove_links()
{
    $producto = intval(get_query_var('producto'));

    if ($producto != 0) {
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'rel_canonical');
    }
}

add_filter("pre_get_shortlink", "daterium_remove_links", 10);



/**************************************************
 * Función para eliminar todas las etiquetas meta *
 *              que inserta yoast                 *
 *************************************************/
function wpseo_remove_opengraph($classes)
{
    $producto = intval(get_query_var('producto'));

    if ($producto != 0) {
        $classes = array_filter($classes, function ($class) {
            return strpos($class, 'Open_Graph') === false;
        });
    }

    return $classes;
}

add_filter('wpseo_frontend_presenter_classes', 'wpseo_remove_opengraph');


/***************************************************************
 *     Funciones para el uso del plugin The SEO Framework      * 
 **************************************************************/
function daterium_activo($quitar)
{
    $producto = intval(get_query_var('producto'));

    if ($producto != 0) {
        $quitar = false;
    }
    return $quitar;
}

function daterium_url()
{
    $producto = intval(get_query_var('producto'));

    if ($producto != 0) {
        return get_permalink() . $producto;

    }
}

function daterium_activo_array($quitar)
{
    $producto = intval(get_query_var('producto'));

    if ($producto != 0) {
        $quitar = array();
    }
    return $quitar;
}


add_filter('wpseo_json_ld_output', 'daterium_activo');

add_filter('the_seo_framework_title_from_custom_field', 'set_title');

add_filter('the_seo_framework_ogurl_output', 'daterium_url');

add_filter('the_seo_framework_rel_canonical_output', 'daterium_url');

add_filter('the_seo_framework_json_breadcrumb_output', 'daterium_activo');

add_filter('the_seo_framework_image_details', 'daterium_activo_array');

/******************************************
 * Función para añadir las meta etiquetas *
 *****************************************/
function set_meta_tags()
{
    global $daterium_userid;
    global $imagen;

    $producto = intval(get_query_var('producto'));

    if ($producto != 0) {
        echo '<link rel="canonical" href="' . get_permalink() . '/' . $producto . '">' . "\n";
        echo '<link rel="shortlink" href="' . get_permalink() . '/' . $producto . '">' . "\n";
        echo '<meta property="og:image" content="' . $imagen . '" />' . "\n";
    }
}
//add_action('wp_head', 'set_meta_tags');


/****************************************************
 * Función para añadir los scripts en los productos *
 ***************************************************/
function set_js()
{
    $producto = intval(get_query_var('producto'));
    if ($producto != 0) {
        wp_register_script('producto_function', plugins_url('public/script/producto_function.js', __FILE__), array(), '', true);
        wp_enqueue_script('producto_function');

        add_filter('script_loader_tag', 'add_defer_attribute', 10, 2);
    }
}

// asegura que los scripts se ejecuten después de que el contenido HTML esté cargado 
function add_defer_attribute($tag, $handle)
{
    if ('imagenes_function' === $handle || 'producto_function' === $handle) {
        return str_replace(' src', ' defer="defer" src', $tag);
    }
    return $tag;
}

add_action('wp_print_scripts', 'set_js');



/*********************************************************** 
 *  Funciones necesarias para la carga correcta del plugin *
 *         Que son llamadas a través de add_action         * 
 **********************************************************/
function daterium_endpoint()
{
    add_rewrite_endpoint('producto', EP_PAGES);
    add_rewrite_endpoint('familia', EP_PAGES);
    add_rewrite_endpoint('busqueda', EP_PAGES);

}

function daterium_rewrite()
{

    global $daterium_page_id;

    add_rewrite_rule(
        '^productos/familia/?/([^/]*)',
        'index.php?page_id=' . $daterium_page_id . '&familia=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^productos/?/([^/]*)',
        'index.php?page_id=' . $daterium_page_id . '&producto=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^productos',
        'index.php?page_id=' . $daterium_page_id,
        'top'
    );
}
/********************************************************* 
 * Acciones necesarias para la carga correcta del plugin * 
 ********************************************************/
add_action('init', 'daterium_endpoint');
add_action('init', 'daterium_rewrite');





/***********************************************************
 * Acciones  y funciones para la carga correcta del plugin *
 * de las secciones en el menu de administrador segun rol  * 
 **********************************************************/



/**
 * Función que muestra las variabbles de entorno
 */
function show_variables()
{
    include_once("public/models/metodos_bbdd.php");
    $metodos_bbdd = new Metodos_bbdd();
    $existe_tabla_variables = $metodos_bbdd->existe_tabla_variables_entorno();

    if ($existe_tabla_variables == false) {
        $metodos_bbdd->crear_tabla_variables('variable');
    } else {
        require_once(DATERIUM_PLUGIN_DIR . 'public/models/metodos_bbdd.php');
        $metodos_bbdd = new Metodos_bbdd();
        $variables = $metodos_bbdd->get_variables();
        require_once(DATERIUM_PLUGIN_DIR . 'public/views/variables_view.php');
    }
}


/**
 * Función para mostrar direntes items en el
 * menu de administrador dependiendo del rol
 */
function show_items_menu()
{
    $user = wp_get_current_user();


    if (in_array('administrator', (array) $user->roles)) {
        add_menu_page('Variables Entorno', 'Variables Entorno', 'administrator', 'variables_view.php', 'show_variables', 'dashicons-smiley');
    }
}
add_action('admin_menu', 'show_items_menu');


function get_idiomas_disponibles()
{
    global $lang_disponibles;

}

add_action('init', 'get_idiomas_disponibles');


/**
 * Función crear y actualizar el XML 
 * de las familias cada dia
 */

add_action('daterium_xml_familias_scheduled', 'daterium_xml_familias');
add_action('init', 'daterium_xml_familias');

if (!wp_next_scheduled('daterium_xml_familias_scheduled')) {
    wp_schedule_event(strtotime('today 03:00:00'), 'daily', 'daterium_xml_familias_scheduled');
}
function daterium_xml_familias()
{
    include_once(DATERIUM_PLUGIN_DIR . 'public/models/metodos_daterium.php');

    $metodos_daterium = new Metodos_Daterium();

    require_once(DATERIUM_PLUGIN_DIR . 'public/models/metodos_bbdd.php');
    $metodos_bbdd = new Metodos_bbdd();

    global $daterium_userid;
    global $daterium_id_marca;
    global $daterium_idioma;

    if (function_exists('trp_custom_language_switcher')) {

        $array_idiomas = trp_custom_language_switcher();
        foreach ($array_idiomas as $array_idioma => $info_idiomas) {
            $lang_disponibles[] = $info_idiomas['short_language_name'];
        }
    } else {
        $lang_disponibles[] = $daterium_idioma;
    }

    if (!empty($lang_disponibles)) {
        $familias = [];
        $i = 1;

        $productos_hijos = $metodos_daterium->daterium_obtener_productos($daterium_userid, $daterium_id_marca, $daterium_idioma);

        foreach ($productos_hijos as $familia => $productos_familia) {

            //imagen->ok
            $primera_imagen = '';
            foreach ($productos_familia as $producto) {
                foreach ($producto as $subproducto) {
                    if (isset($subproducto['imagen']) && !empty($subproducto['imagen']) && empty($primera_imagen)) {
                        $primera_imagen = $subproducto['imagen'];
                        break;
                    }
                }
            }

            $familias[$i] = [
                'id' => $i,
                'imagen' => $primera_imagen,
                'nombre' => array("es" => $familia)
            ];
            $i++;

        }

        foreach ($lang_disponibles as $language_code => $lang_info) {

            if ($lang_info <> 'es') {
                $productos_hijos_idioma = $metodos_daterium->daterium_obtener_productos($daterium_userid, $daterium_id_marca, $lang_info);
                $k = 1;
                foreach ($productos_hijos_idioma as $familia => $productos_familia) {
                    $familias[$k]['nombre'][$lang_info] = $familia;
                    $k++;
                }
            }
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><familias></familias>');

            foreach ($familias as $familia => $infoFamilia) {

                $familiaElemento = $xml->addChild('familia');
                // $familiaElemento->addAttribute('id', $infoFamilia['id']);
                $familiaElemento->addAttribute('id', isset($infoFamilia['id']) ? $infoFamilia['id'] : '');

                foreach ($infoFamilia['nombre'] as $idioma => $nombre) {
                    if (!empty($nombre)) {
                        $nombreElemento = $familiaElemento->addChild('nombre', htmlspecialchars($nombre));
                        $nombreElemento->addAttribute('lang', $idioma);
                    }
                }

                // $familiaElemento->addChild('imagen', htmlspecialchars($infoFamilia['imagen']));
                $familiaElemento->addChild('imagen', isset($infoFamilia['imagen']) ? htmlspecialchars($infoFamilia['imagen']) : '');


            }

            $filefamiliasXML = DATERIUM_PLUGIN_DIR . 'public/daterium-familias.xml';

            file_put_contents($filefamiliasXML, $xml->asXML());
        }
    }

}

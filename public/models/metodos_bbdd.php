<?php
defined('ABSPATH') or die();
class Metodos_bbdd
{

  /*
   * Constructor de la clase
   */
  public function __construct()
  {
  }


  /**
   * Función para comprobar si existe la tabla de distribuidores
   */
  function existe_tabla_distribuidores()
  {
    global $wpdb;
    $prefix = $wpdb->prefix . "daterium_";
    $tabla = $prefix . 'distribuidores';
    $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($tabla));

    if (!$wpdb->get_var($query) == $tabla) {
      return false;
    } else {
      return true;
    }
  }


  /**
   * Función para comprobar si existe la tabla de variables de entorno
   */
  function existe_tabla_variables_entorno()
  {
    global $wpdb;
    $prefix = $wpdb->prefix . "daterium_";
    $tabla = $prefix . 'variable';
    $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($tabla));

    if (!$wpdb->get_var($query) == $tabla) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Función para crear la tabla de variables de entorno
   */
  function crear_tabla_variables($mi_tabla)
  {
    global $wpdb;
    $prefix = $wpdb->prefix . "daterium_";
    $collate = $wpdb->collate;
    $nombre_tabla = $prefix . $mi_tabla;
    $sql = "CREATE TABLE {$nombre_tabla} (
          codigo varchar(30) NOT NULL,
          valor varchar(30),
          PRIMARY KEY  (codigo)
        ) 
        COLLATE {$collate}";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  /**
   * Función para crear la tabla de distribuidores
   */
  function crear_tabla_distribuidores($mi_tabla)
  {
    global $wpdb;
    $prefix = $wpdb->prefix . "daterium_";
    $collate = $wpdb->collate;
    $nombre_tabla = $prefix . $mi_tabla;
    $sql = "CREATE TABLE {$nombre_tabla} (
            id INT  NOT NULL AUTO_INCREMENT,
            nombre varchar(30),
            url_distribuidor varchar(255),
            url_logo varchar(255),
            orden INT, 
            url_web varchar(255), 
            mostrar_en_productos boolean, 
            PRIMARY KEY  (id)
          ) 
          COLLATE {$collate}";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }


  /**
   * Función para obtener las variables de entorno
   */
  function get_variables()
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'variable';
      $query = "SELECT * FROM {$tabla}";
      $variables = $wpdb->get_results($query);
      return $variables;
    } catch (Exception $e) {
      return 0;
    }
  }


  /**
   * Función para obtener los distribuidores
   */
  function get_distribuidores()
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'distribuidores';
      $query = "SELECT * FROM {$tabla} ORDER BY orden ASC";
      $distribuidores = $wpdb->get_results($query);
      return $distribuidores;
    } catch (Exception $e) {
      return 0;
    }
  }



  function get_distribuidores_productos()
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'distribuidores';
      $query = "SELECT * FROM {$tabla} WHERE mostrar_en_productos = 1 ORDER BY orden ASC";
      $distribuidores_productos = $wpdb->get_results($query);
      return $distribuidores_productos;
    } catch (Exception $e) {
      return 0;
    }
  }

  function get_distribuidores_online()
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'distribuidores';
      $query = "SELECT * FROM {$tabla} ORDER BY orden ASC";
      $distribuidores_online = $wpdb->get_results($query);
      return $distribuidores_online;
    } catch (Exception $e) {
      return 0;
    }
  }


  /**
   * Función para obtener el catalogo inicial
   */
  function get_id_marca()
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'variable';
      $query = "SELECT valor FROM {$tabla} WHERE codigo ='id-marca' LIMIT 1";
      $valor = $wpdb->get_results($query);
      return $valor[0]->valor;
    } catch (Exception $e) {
      return 0;
    }
  }

  /**
   * Función para obtener el nombre de la marca
   */
  function get_nombre_marca()
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'variable';
      $query = "SELECT valor FROM {$tabla} WHERE codigo ='nombre-marca' LIMIT 1";
      $valor = $wpdb->get_results($query);
      return $valor[0]->valor;
    } catch (Exception $e) {
      return 0;
    }
  }


  /**
   * Función para obtener el id de raiz ferretera
   */
  function get_id_rf()
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'variable';
      $query = "SELECT valor FROM {$tabla} WHERE codigo ='id-raiz-ferretera' LIMIT 1";
      $valor = $wpdb->get_results($query);
      return $valor[0]->valor;
    } catch (Exception $e) {
      return 0;
    }
  }

  /**
   * Función para obtener el stock
   */
  function get_stock($codiart)
  {
    try {
      global $wpdb;
      $tabla = 'stock_estados';
      $query = "SELECT estado FROM {$tabla} WHERE codiart='{$codiart}' LIMIT 1";
      $stock = $wpdb->get_results($query);
      if (count($stock) > 0) {
        return $stock[0]->estado;
      } else {
        return "0";
      }
    } catch (Exception $e) {
      return "0";
    }
  }

  /**
   * Función para saber si un artículo va a ser descatalogado
   */
  function get_compra($codiart)
  {
    try {
      global $wpdb;
      $tabla = 'stock_estados';
      $query = "SELECT compra FROM {$tabla} WHERE codiart='{$codiart}' LIMIT 1";
      $compra = $wpdb->get_results($query);
      if (count($compra) > 0) {
        return $compra[0]->compra;
      } else {
        return "1";
      }
    } catch (Exception $e) {
      return "1";
    }
  }


  /**
   * Función para modificar el valor de las variables
   */
  function modificar_valor($codigo, $valor)
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'variable';
      $datos = array(
        'codigo' => $codigo,
        'valor' => $valor
      );
      $where = array('codigo' => $codigo);
      $resultado = $wpdb->update($tabla, $datos, $where);
      return $resultado;
    } catch (Exception $e) {
      return 0;
    }
  }

  /**
   * Función para añadir un nuevo distribuidor
   */
  function nuevo_distribuidor($nombre, $url_dis, $url_logo, $orden, $url_web, $mostrar_en_productos)
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'distribuidores';
      $fila = array(
        'nombre' => $nombre,
        'url_distribuidor' => $url_dis,
        'url_logo' => $url_logo,
        'orden' => $orden,
        'url_web' => $url_web,
        'mostrar_en_productos' => $mostrar_en_productos
      );
      $resultado = $wpdb->insert($tabla, $fila);
      return $resultado;
    } catch (Exception $e) {
      return 0;
    }
  }


  /**
   * Función para borrar un distribuidor
   */
  function borrar_distribuidor($id)
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'distribuidores';
      $where = array('id' => $id);
      $resultado = $wpdb->delete($tabla, $where);
      return $resultado;
    } catch (Exception $e) {
      return 0;
    }
  }


  /**
   * Función para modificar el valor de los distribuidores
   */
  function modificar_valor_distribuidor($id, $nombre, $url_distribuidor, $url_logo, $orden, $url_web, $mostrar_en_productos)
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'distribuidores';
      $datos = array(
        //'id' => $id,
        'nombre' => $nombre,
        'url_distribuidor' => $url_distribuidor,
        'url_logo' => $url_logo,
        'orden' => $orden,
        'url_web' => $url_web,
        'mostrar_en_productos' => $mostrar_en_productos,
      );
      $where = array('id' => $id);
      $resultado = $wpdb->update($tabla, $datos, $where);
      return $resultado;
    } catch (Exception $e) {
      return 0;
    }
  }

  function get_daterium_page_id()
  {
    try {
      global $wpdb;
      $daterium_page_id = $wpdb->get_var('SELECT ID FROM ' . $wpdb->prefix . 'posts WHERE post_content LIKE "%[daterium_catalogo]%" AND post_status = "publish" ORDER BY post_date desc LIMIT 1');
      return $daterium_page_id;
    } catch (Exception $e) {
      return 0;
    }
  }

  /**
   * Función para obtener el idioma por defecto
   */
  function get_codigo_idioma()
  {
    try {
      global $wpdb;
      $prefix = $wpdb->prefix . "daterium_";
      $tabla = $prefix . 'variable';
      $query = "SELECT valor FROM {$tabla} WHERE codigo ='codigo-idioma' LIMIT 1";
      $valor = $wpdb->get_results($query);
      return $valor[0]->valor;
    } catch (Exception $e) {
      return 0;
    }
  }
}
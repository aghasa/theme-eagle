<?php

defined('ABSPATH') or exit;

/*
 * Clase para la estracción de datos de Daterium
 */
class Metodos_Daterium
{
    public $userID = '';

    public $marcaID = '';

    public $metodos = null;

    /*
     * Constructor de la clase vacio
     */
    public function __construct()
    {
        include_once 'metodos_bbdd.php';
        $this->metodos = new Metodos_bbdd();
        global $daterium_userid;
        $this->userID = $daterium_userid;
        global $daterium_id_marca;
        $this->marcaID = $daterium_id_marca;
    }

    /**
     * Función para obtener las imagenes de un producto.
     */
    public function get_images($xml)
    {
        $nodos_imagenes = $xml->xpath('imagenes/imagen');

        $imagenes = [];

        foreach ($nodos_imagenes as $imagen) {
            array_push($imagenes, ['path' => $imagen->img500x500]);
        }

        return $imagenes;
    }

    /**
     * Función para modificar la url.
     */
    public function daterium_url_title($string, $space = '-')
    {
        $string = htmlentities($string, ENT_COMPAT, 'UTF-8');
        $string = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde);/', '$1', $string);
        // $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');

        $string = preg_replace("/[^a-zA-Z0-9 \-]/", '', trim($string));
        $string = trim(preg_replace('/\\s+/', ' ', $string));
        $string = strtolower($string);
        $string = str_replace(' ', $space, $string);
        $string = trim(preg_replace('/\\-+/', '-', $string));

        return $string;
    }

    public function clean_descripcion($cadena)
    {
        $descripcion_clean = preg_replace('/[^ Á-Úá-úA-Za-z0-9.]+/', '', $cadena);
        $descripcion_clean = str_replace('br', '', $descripcion_clean);
        $descripcion_clean = str_replace('·', '', $descripcion_clean);
        $descripcion_clean = str_replace('Ø', '', $descripcion_clean);

        return $descripcion_clean;
    }

    /**
     * Función para obtener los nombres de las familias.
     */
    public function daterium_get_family_names($xml)
    {
        $filas = [];
        foreach ($xml->xpath('familia') as $nodo) {
            $caja = [
                'id' => intval($nodo['id']),
                'nombre' => strval($nodo->nombre),
            ];
            $filas[] = $caja;
        }

        return $filas;
    }


    /**
     * Función para obtener la lista de productos que tiene una marca.
     */
    public function daterium_get_list_products_marca($xml)
    {
        $i = 1;
        $filas = [];
        foreach ($xml->xpath('productos/producto') as $nodo) {
            $activo = true;
            $novedad = false;
            $total_obsoletos = 0;
            $total_referencias = 0;

            /* Para que esto funcione hay que llamar a RF con parametro ref=1
            foreach ($nodo->xpath("referencias/referencia") as $nodo_referencia) {
            $total_referencias++;
            $atributos = $nodo_referencia->attributes();
            $referencias[intval($atributos->id)] = $nodo_referencia;
            }
            */

            $activo = ($total_referencias > $total_obsoletos);

            $caja = [
                'pos' => $i++,
                'tipo' => 'vin',
                'id' => intval($nodo->pID),
                'nombre' => strval($nodo->nombre),
                'imagen' => strval($nodo->imagenes->imagen->img500x500),
                'descripcion' => strval($nodo->descripcion_corta),
                'extra' => '',
                'url' => site_url() . '/' . basename(get_permalink(get_the_ID())) . '/' . intval($nodo->pID) . '/' . $this->daterium_url_title($nodo->nombre),
                'activo' => $activo,
                'novedad' => $novedad,
                'familia' => strval($nodo->familia),
                'subfamilia' => strval($nodo->subfamilia),
            ];
            $filas[] = $caja;
        }

        $productos_familias = array();
        foreach ($filas as $filas_marca) {
            if (array_key_exists('familia', $filas_marca)) {
                $productos_familias[$filas_marca['familia']][] = $filas_marca;
            } else {
                $productos_familias[""][] = $filas_marca;
            }
        }

        foreach ($productos_familias as $nombre_familia => $filas_familia) {
            foreach ($filas_familia as $filas_subfamilia) {
                if (array_key_exists('familia', $filas_subfamilia)) {

                    $productos_subfamilias[$nombre_familia][$filas_subfamilia['subfamilia']][] = $filas_subfamilia;
                } else {
                    $productos_subfamilias[$nombre_familia][""][] = $filas_subfamilia;
                }
            }
        }

        return $productos_subfamilias;
    }

    /**
     * Función para obtener el nombre de la marca.
     */
    public function get_nombre_marca($idmarca)
    {

        global $daterium_userid;
        $xml = simplexml_load_file("https://api.dateriumsystem.com/marcas.php?userID=" . $daterium_userid);
        $nombre = strval($xml->xpath('//marca[@id="' . $idmarca . '"][1]/nombre[1]')[0] ?? "Catálogo");

        return $nombre;
    }



    /**
     * Función para obtener la clasificación AECOC.
     */
    public function get_clasificacion_aecoc($xml)
    {
        $ruta = '';
        $nodos = $xml->xpath('aecoc/ruta/paso');
        $tamanio = count($nodos);
        $i = 1;
        foreach ($nodos as $nodo) {
            if ($i == $tamanio) {
                $ruta = $ruta . $nodo->nombre;
            } else {
                $ruta = $ruta . $nodo->nombre . ' > ';
            }
            ++$i;
        }

        return $ruta;
    }

    /**
     * Función para sacar los datos adjuntos de un producto.
     */
    public function get_adjuntos($xml)
    {
        $adjuntos = [];
        $nodos = $xml->xpath('adjuntos/adjunto');
        foreach ($nodos as $nodo) {
            array_push($adjuntos, ['nombre' => $nodo->nombre, 'url' => $nodo->fichero, 'formato' => $nodo->formato]);
        }

        return $adjuntos;
    }

    /**
     * Función para comprobar que una cantidad no sea cero.
     */
    public function es_cero($cadena)
    {
        $cero = true;

        if ($cadena != '') {
            $pos = strpos($cadena, '0.00');

            if ($pos === false) {
                $cero = false;
            } else {
                $cero = true;
            }
        } else {
            $cero = true;
        }

        return $cero;
    }

    /**
     * Función para obtener el nombre de los atributos diferenciadores.
     */
    public function get_nombre_atributos_diferenciadores($xml)
    {
        $nombres_difereciadores[0] = $xml->atributos->atributo1;
        $nombres_difereciadores[1] = $xml->atributos->atributo2;
        $nombres_difereciadores[3] = $xml->atributos->atributo3;

        return $nombres_difereciadores;
    }

    /**
     * Función para obtener el nombre de los atributos diferenciadores.
     */
    public function get_datos_atributos_diferenciadores($referencia)
    {
        $datos_difereciadores[0] = $referencia->atributos->atributo1;
        $datos_difereciadores[1] = $referencia->atributos->atributo2;
        $datos_difereciadores[3] = $referencia->atributos->atributo3;

        return $datos_difereciadores;
    }

    /**
     * Función para obtener el nombre de las caracterícas de la referencia.
     */
    public function get_nombre_caracteristicas($xml)
    {
        $nombres[0] = $xml->caracteristicas_tecnicas->caracteristica_1;
        $nombres[1] = $xml->caracteristicas_tecnicas->caracteristica_2;
        $nombres[2] = $xml->caracteristicas_tecnicas->caracteristica_3;
        $nombres[3] = $xml->caracteristicas_tecnicas->caracteristica_4;
        $nombres[4] = $xml->caracteristicas_tecnicas->caracteristica_5;
        $nombres[5] = $xml->caracteristicas_tecnicas->caracteristica_6;
        $nombres[6] = $xml->caracteristicas_tecnicas->caracteristica_7;
        $nombres[7] = $xml->caracteristicas_tecnicas->caracteristica_8;
        $nombres[8] = $xml->caracteristicas_tecnicas->caracteristica_9;
        $nombres[9] = $xml->caracteristicas_tecnicas->caracteristica_10;
        $nombres[10] = $xml->caracteristicas_tecnicas->caracteristica_11;
        $nombres[11] = $xml->caracteristicas_tecnicas->caracteristica_12;
        $nombres[12] = $xml->caracteristicas_tecnicas->caracteristica_13;
        $nombres[13] = $xml->caracteristicas_tecnicas->caracteristica_14;

        return $nombres;
    }

    /**
     * Función para obtener los valores de las caractrerísticas de la referencia.
     */
    public function get_datos_caracteristicas($referencia)
    {
        $caracteristicas[0] = $referencia->caracteristicas_tecnicas->caracteristica_1;
        $caracteristicas[1] = $referencia->caracteristicas_tecnicas->caracteristica_2;
        $caracteristicas[2] = $referencia->caracteristicas_tecnicas->caracteristica_3;
        $caracteristicas[3] = $referencia->caracteristicas_tecnicas->caracteristica_4;
        $caracteristicas[4] = $referencia->caracteristicas_tecnicas->caracteristica_5;
        $caracteristicas[5] = $referencia->caracteristicas_tecnicas->caracteristica_6;
        $caracteristicas[6] = $referencia->caracteristicas_tecnicas->caracteristica_7;
        $caracteristicas[7] = $referencia->caracteristicas_tecnicas->caracteristica_8;
        $caracteristicas[8] = $referencia->caracteristicas_tecnicas->caracteristica_9;
        $caracteristicas[9] = $referencia->caracteristicas_tecnicas->caracteristica_10;
        $caracteristicas[10] = $referencia->caracteristicas_tecnicas->caracteristica_11;
        $caracteristicas[11] = $referencia->caracteristicas_tecnicas->caracteristica_12;
        $caracteristicas[12] = $referencia->caracteristicas_tecnicas->caracteristica_13;
        $caracteristicas[13] = $referencia->caracteristicas_tecnicas->caracteristica_14;

        return $caracteristicas;
    }

    /**
     * Función para obtener la pareja nombre-valor de la características.
     */
    public function get_datos_completos($nombres_carac, $valor_carac)
    {
        $datos_completos_caracteristicas = '';
        for ($i = 0; $i <= 13; ++$i) {
            if ($nombres_carac[$i] != '' && $valor_carac[$i] != '') {
                $datos_completos_caracteristicas = $datos_completos_caracteristicas . '<div class="info-div"><p class="daterium-producto-info"><span class="daterium-producto-info-cab">' . $nombres_carac[$i] . ':</span><span> ' . $valor_carac[$i] . '</span></p></div>';
            }
        }

        return $datos_completos_caracteristicas;
    }

    /**
     * Función para obtener las dimensiones de una referencia.
     */
    public function get_dimensiones($referencia)
    {
        $dimensiones = '';
        $peso = $referencia->dimensiones->articulo_peso;
        $largo = $referencia->dimensiones->articulo_largo;
        $ancho = $referencia->dimensiones->articulo_ancho;
        $alto = $referencia->dimensiones->articulo_alto;
        if ($this->es_cero($peso) == false) {
            $dimensiones = $dimensiones . '<div class="info-div"><p class="daterium-producto-info"><span class="daterium-producto-info-cab">Peso artículo(' . $referencia->dimensiones->articulo_peso['unidad'] . '):</span><span> ' . number_format((float) $peso, 4, ',', '') . '</span></p></div>';
        }
        if ($largo != '') {
            $dimensiones = $dimensiones . '<div class="info-div"><p class="daterium-producto-info"><span class="daterium-producto-info-cab">Largo del artículo (' . $referencia->dimensiones->articulo_largo['unidad'] . '):</span><span>  ' . number_format((float) $largo, 4, ',', '') . '</span></p></div>';
        }
        if ($ancho != '') {
            $dimensiones = $dimensiones . '<div class="info-div"><p class="daterium-producto-info"><span class="daterium-producto-info-cab">Ancho del artículo (' . $referencia->dimensiones->articulo_ancho['unidad'] . '):</span><span>  ' . number_format((float) $ancho, 4, ',', '') . '</span></p></div>';
        }
        if ($alto != '') {
            $dimensiones = $dimensiones . '<div class="info-div"><p class="daterium-producto-info"><span class="daterium-producto-info-cab">Ancho del artículo (' . $referencia->dimensiones->articulo_alto['unidad'] . '):</span><span>  ' . number_format((float) $alto, 4, ',', '') . '</span></p></div>';
        }

        return $dimensiones;
    }

    /**
     * Función para obtener los puntos clave de un producto.
     */
    public function get_puntos_clave($xml)
    {
        $datos_puntos = [];
        // Busco los nodos puntos_clave/punto
        $puntos = $xml->xpath('puntos_clave/punto');

        foreach ($puntos as $punto) {
            if ($punto != '') {
                array_push($datos_puntos, $punto);
            }
        }

        return $datos_puntos;
    }

    /**
     * Función para obtener la descripción de un producto.
     */
    public function get_descripcion($xml)
    {
        return $xml->descripcion;
    }

    public function get_script_ProductGroup($marca, $descripcion_texto_plano, $url, $referencias, $familia, $subfamilia)
    {
        $script = '<script type="application/ld+json">[';
        $i = 1;
        $tamanio_array = count($referencias);
        foreach ($referencias as $referencia) {
            $poner_coma = '';
            if ($i != $tamanio_array) {
                $poner_coma = ',';
            }

            $descripcion_referencia = json_encode($descripcion_texto_plano, JSON_UNESCAPED_UNICODE);
            if (isset($descripcion_referencia)) {
                $descripcion_referencia = json_encode($referencia['descripcion'], JSON_UNESCAPED_UNICODE);
            }
            $script = $script . '{"@context":"https://schema.org/",
                "@type":"Product", 
                "sku":"' . $referencia['ref'] . '",
                "name":' . json_encode($referencia['descripcion'], JSON_UNESCAPED_UNICODE) . ',
                "mpn":"' . $referencia['ref'] . '",
                "gtin13":"' . $referencia['ean'] . '",
                "brand":"' . $marca . '",
                "description":' . $descripcion_referencia . ',
                "offers":{"@type":"Offer",
                    "priceCurrency":"EUR",
                    "price":"' . $referencia['precio_script'] . '",
                    "eligibleQuantity":"' . $referencia['cant_cont'] . '",
                    "url":"https://' . $url . '",
                    "itemCondition":"https://schema.org/NewCondition",
                      "category":' . json_encode($familia . ' > ' . $subfamilia, JSON_UNESCAPED_UNICODE) . '
                },
                "image:":"' . $referencia['imagen'] . '"
            }' . $poner_coma;
            ++$i;
        }
        $script = $script . ']
        </script>' . "\n";

        return $script;
    }

    /**
     * Función para obtener los datos completos de las referencias de un producto.
     */
    public function get_datos_referencias($xml)
    {
        $datos_referencias = [];

        // Busco los nodos referencias/referencia

        $referencias = $xml->xpath('referencias/referencia');
        $i = 0;
        foreach ($referencias as $referencia) {
            $descripcion = strval($referencia->denoart);
            $ref = strval($referencia->ref);
            $ean = strval($referencia->ean);
            $codiart = strval($referencia->codiart);
            $imagen = strval($referencia->vinculos->imagenes->imagen->img280x240);
            $nombres_atributos_diferenciadores = $this->get_nombre_atributos_diferenciadores($xml);
            $datos_atributos_diferenciadores = $this->get_datos_atributos_diferenciadores($referencia);
            $nombres_caracteristicas = $this->get_nombre_caracteristicas($xml);
            $datos_caracteristicas = $this->get_datos_caracteristicas($referencia);
            $completo_caracteristica = $this->get_datos_completos($nombres_caracteristicas, $datos_caracteristicas);
            $dimensiones = $this->get_dimensiones($referencia);
            $pre_tarifa = number_format((float) $referencia->tarifa->precio_tarifa, 4, ',', '');
            $precio_script = number_format((float) $referencia->tarifa->precio_recomendado, 2, '.', '');
            $precio_recomendado = number_format((float) $referencia->tarifa->precio_recomendado, 2, ',', '');
            // $fam_descuento = strval($referencia->tarifa->familia_descuento);
            // $tipo_iva = strval($referencia->tarifa->tipo_iva);
            $unidad_precio = strval($referencia->tarifa->unidad_precio);
            $cantidad_minima = strval($referencia->tarifa->cantidad_minima);
            +$cant_cont = strval($referencia->datos_packaging->cantidad_contenido);
            $uni_cont = strval($referencia->datos_packaging->unidad_contenido);

            $cantidad_peso_pack = $this->es_cero(strval($referencia->datos_packaging->packaging_peso));
            if ($cantidad_peso_pack == false) {
                $peso = strval(number_format((float) $referencia->datos_packaging->packaging_peso, 4, ',', '') . '' . $referencia->datos_packaging->packaging_peso['unidad']);
            } else {
                $peso = '0';
            }

            $sin_largo = $this->es_cero(strval($referencia->datos_packaging->packaging_largo));
            if ($sin_largo == false) {
                $largo = strval(number_format((float) $referencia->datos_packaging->packaging_largo, 4, ',', '') . '' . $referencia->datos_packaging->packaging_largo['unidad']);
            } else {
                $largo = '0';
            }

            $sin_ancho = $this->es_cero(strval($referencia->datos_packaging->packaging_ancho));
            if ($sin_ancho == false) {
                $ancho = strval(number_format((float) $referencia->datos_packaging->packaging_ancho, 4, ',', '') . '' . $referencia->datos_packaging->packaging_ancho['unidad']);
            } else {
                $ancho = '0';
            }

            $sin_alto = $this->es_cero(strval($referencia->datos_packaging->packaging_alto));
            if ($sin_alto == false) {
                $alto = strval(number_format((float) $referencia->datos_packaging->packaging_alto, 4, ',', '') . '' . $referencia->datos_packaging->packaging_alto['unidad']);
            } else {
                $alto = '0';
            }

            $presentacion = strval($referencia->datos_packaging->presentacion);
            $un_entrega = strval($referencia->datos_logisticos->unidad_entrega->ue_cantidad);
            $up_ean = strval($referencia->datos_logisticos->unidad_preparacion->up_ean);
            $up_cantidad = strval($referencia->datos_logisticos->unidad_preparacion->up_cantidad);

            $cantidad_peso = $this->es_cero(strval($referencia->datos_logisticos->unidad_preparacion->up_peso));
            if ($cantidad_peso == false) {
                $up_peso = strval(number_format((float) $referencia->datos_logisticos->unidad_preparacion->up_peso, 4, ',', '') . ' ' . $referencia->datos_logisticos->unidad_preparacion->up_peso['unidad']);
            } else {
                $up_peso = '0';
            }

            $cantidad_largo = $this->es_cero($referencia->datos_logisticos->unidad_preparacion->up_largo);
            if ($cantidad_largo == false) {
                $up_largo = strval(number_format((float) $referencia->datos_logisticos->unidad_preparacion->up_largo, 4, ',', '') . ' ' . $referencia->datos_logisticos->unidad_preparacion->up_largo['unidad']);
            } else {
                $up_largo = '0';
            }

            $cantidad_ancho = $this->es_cero($referencia->datos_logisticos->unidad_preparacion->up_ancho);
            if ($cantidad_ancho == false) {
                $up_ancho = strval(number_format((float) $referencia->datos_logisticos->unidad_preparacion->up_ancho, 4, ',', '') . ' ' . $referencia->datos_logisticos->unidad_preparacion->up_ancho['unidad']);
            } else {
                $up_ancho = '0';
            }

            $cantidad_alto = $this->es_cero($referencia->datos_logisticos->unidad_preparacion->up_alto);
            if ($cantidad_alto == false) {
                $up_alto = strval(number_format((float) $referencia->datos_logisticos->unidad_preparacion->up_alto, 4, ',', '') . ' ' . $referencia->datos_logisticos->unidad_preparacion->up_alto['unidad']);
            } else {
                $up_alto = '0';
            }

            $dropshipping = strval($referencia->datos_logisticos->dropshipping);
            if ($dropshipping == 's') {
                $dropshipping = 'si';
            } elseif ($dropshipping == 'n') {
                $dropshipping = 'no';
            } else {
                $dropshipping = 'sin información';
            }

            $estado = strval($referencia->estado_articulo->estado);
            $estado_fecha = strval($referencia->estado_articulo->estado_fecha);

            $documentos = $this->get_documentos_ref($referencia);

            $datos_referencias[$i] = ['descripcion' => $descripcion, 'ref' => $ref, 'ean' => $ean, 'caracteristicas' => $completo_caracteristica, 'dimensiones' => $dimensiones, 'precio' => $pre_tarifa, 'precio_script' => $precio_script, 'precio_recomendado' => $precio_recomendado, 'uni_pre' => $unidad_precio, 'cant_min' => $cantidad_minima, 'cant_cont' => $cant_cont, 'uni_cont' => $uni_cont, 'peso' => $peso, 'largo' => $largo, 'ancho' => $ancho, 'alto' => $alto, 'presentacion' => $presentacion, 'un_entrega' => $un_entrega, 'up_ean' => $up_ean, 'up_cantidad' => $up_cantidad, 'up_peso' => $up_peso, 'up_largo' => $up_largo, 'up_ancho' => $up_ancho, 'up_alto' => $up_alto, 'dropshipping' => $dropshipping, 'estado' => $estado, 'estado_fecha' => $estado_fecha, 'codiart' => $codiart, 'docu_ref' => $documentos, 'imagen' => $imagen, 'nombre_caracteristicas' => $nombres_caracteristicas, 'datos_caracteristicas' => $datos_caracteristicas, 'nombres_atributos_diferenciadores' => $nombres_atributos_diferenciadores, 'datos_atributos_diferenciadores' => $datos_atributos_diferenciadores];

            $i = $i + 1;

            //  'fan_dto' => $fam_descuento, 'tipo_IVA' => $tipo_iva,
        }

        return $datos_referencias;
    }

    /**
     * Saca los datos documentos de una referencia.
     */
    public function get_documentos_ref($refe)
    {
        $adjunto_ref_string = '';

        $nodos = $refe->xpath('vinculos/documentos/documento');
        foreach ($nodos as $nodo) {
            $adjunto_ref_string = $adjunto_ref_string . $this->get_text_imagen() . '<a href="' . $nodo->fichero . '" alt="' . $nodo->nombre . '" target="_blank">' . $nodo->nombre . '</a>';
        }

        return $adjunto_ref_string;
    }

    /**
     * Función que me devuelve la imagen de los documentos
     * a nivel de referencia.
     */
    public function get_text_imagen()
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-external-link" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="#0b0b0b" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"/> <path d="M11 7h-5a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-5" /> <line x1="10" y1="14" x2="20" y2="4" /> <polyline points="15 4 20 4 20 9" /> </svg>';
    }

    /**
     * Función para obtener los productos relacionados.
     */
    public function get_related($relacionados_xml, $userID, $numero)
    {
        $datos_relacionados = [];

        if (count($relacionados_xml) > $numero) {
            for ($i = 0; $i < $numero; ++$i) {
                $url_llamada_relacionado = 'https://api.dateriumsystem.com/producto_mini_fc_xml.php?pID=' . $relacionados_xml[$i] . '&userID=' . $userID;
                $xml_producto_relacionado = simplexml_load_file($url_llamada_relacionado);
                array_push($datos_relacionados, ['pID' => $xml_producto_relacionado->id, 'nombre' => $xml_producto_relacionado->nombre, 'imagen' => $xml_producto_relacionado->imagenes->imagen->img280x240]);
            }

            return $datos_relacionados;
        } else {
            foreach ($relacionados_xml as $relacionado) {
                $url_llamada_relacionado = 'https://api.dateriumsystem.com/producto_mini_fc_xml.php?pID=' . $relacionado . '&userID=' . $userID;
                $xml_producto_relacionado = simplexml_load_file($url_llamada_relacionado);
                array_push($datos_relacionados, ['pID' => $relacionado, 'nombre' => $xml_producto_relacionado->nombre, 'imagen' => $xml_producto_relacionado->imagenes->imagen->img280x240]);
            }

            return $datos_relacionados;
        }
    }

    /**
     * Función para ver el estado de una URL.
     */
    public function get_status_url($url)
    {
        $response = wp_remote_head($url, ['timeout' => 60]);

        // Aceptar solo respuesta 200 (Ok)
        $accepted_response = [200];
        if (!is_wp_error($response) && in_array(wp_remote_retrieve_response_code($response), $accepted_response)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Función para ver el estado de
     * la API de RF.
     */
    public function get_status_daterium()
    {
        try {
            $url_llamada = 'https://api.dateriumsystem.com/estado.php';
            $estado_url = $this->get_status_url($url_llamada);
            if ($estado_url == true) {
                $xml_respuesta = simplexml_load_string(file_get_contents($url_llamada), 'SimpleXMLElement', LIBXML_NOWARNING | LIBXML_NOERROR);
                if ($xml_respuesta != false) {
                    return $xml_respuesta->estado;
                }
            } else {
                return 'error';
            }
        } catch (Exception $e) {
            return 'error';
        }
    }

    /**
     * Función para obtener los datos de una API.
     */
    public function daterium_get_data_url($url_llamada)
    {
        try {
            $estado_url = $this->get_status_url($url_llamada);
            if ($estado_url == true) {
                $xml_respuesta = simplexml_load_string(file_get_contents(str_replace(' ', '', $url_llamada)), 'SimpleXMLElement', LIBXML_NOWARNING | LIBXML_NOERROR);
                if ($xml_respuesta != false) {
                    return $xml_respuesta;
                } else {
                    return 'error';
                }
            } else {
                return 'error';
            }
        } catch (Exception $e) {
            return 'error';
        }
    }

    function daterium_obtener_productos($daterium_userid, $daterium_id_marca, $language_code)
    {

        $url_marca = 'https://api.dateriumsystem.com/productos_marca_xml.php?idmarca=' . $daterium_id_marca . '&userID=' . $daterium_userid . '&lang=' . $language_code;
        $xml_marca = $this->daterium_get_data_url($url_marca);
        $productos_hijos = $this->daterium_get_list_products_marca($xml_marca);

        return $productos_hijos;
    }

}

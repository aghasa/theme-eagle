var datos = "";

var slideIndex = 1;

/**
 * Función que muestra la division que entra como parámetro
 */
function mostrar_datos(parte, es_ref) {
  var ob = document.getElementById(parte);
  var id_arrow = "img-" + parte;
  var arrow = document.getElementById(id_arrow);

  if (es_ref == true) {
    if (ob.classList.contains("ocultar")) {
      ob.classList.remove("ocultar");
    }

    if (!arrow.classList.contains("girar-imagen")) {
      arrow.classList.add("girar-imagen");
    }
  } else {
    if (ob.classList.contains("ocultar")) {
      ob.classList.remove("ocultar");
    } else {
      ob.classList.add("ocultar");
    }

    if (arrow.classList.contains("girar-imagen")) {
      arrow.classList.remove("girar-imagen");
    } else {
      arrow.classList.add("girar-imagen");
    }
  }
}

/**
 * Función que muestra la division que entra como parámetro
 */
function copiar_portapapeles(valor) {
  navigator.clipboard.writeText(valor);
}

/**
 * Método para añadir los datos de los puntos
 */
function set_datos() {
  var var_codi = jsVar_producto;
  var decodificada = atob(var_codi);
  datos = JSON.parse(decodificada);
}

/**
 * Función para obtener los datos de los puntos
 */
function get_datos_producto() {
  return datos;
}

/**
 * Método para cambiar los datos al seleccionar una referencia
 * @param {*} id
 */
function ver_datos(id) {
  mostrar_datos("masinfo", true);

  document.getElementById("deno").innerHTML = datos[id].descripcion;
  document.getElementById("ref_in").innerHTML = datos[id].ref;
  document.getElementById("ean").innerHTML = datos[id].ean;
  //document.getElementById("precio").innerHTML =datos[id].precio+"€";
  if (datos[id].estado == "obsoleto" && datos[id].valor_stock == 0) {
    document.getElementById("precio").innerHTML =
      "Producto descatalogado no disponible para la compra.";
  } else {
    document.getElementById("precio").innerHTML =
      datos[id].precio_recomendado + "€";
  }
  
  var existe_uni_pre = document.getElementById("uni_pre");
  if (existe_uni_pre != null) {
    document.getElementById("uni_pre").innerHTML = datos[id].uni_pre;
  }

  document.getElementById("cant_cont").innerHTML = datos[id].cant_cont;
  document.getElementById("uni_cont").innerHTML = datos[id].uni_cont;

  var existe_peso = document.getElementById("peso");
  if (existe_peso != null) {
    document.getElementById("peso").innerHTML = datos[id].peso;
  }

  if (datos[id].docu_ref != "") {
    document.getElementById("docu_ref").innerHTML = datos[id].docu_ref;
  } else {
    var existe_docu_ref = document.getElementById("docu_ref");
    if (existe_docu_ref != null) {
      document.getElementById("docu_ref").innerHTML = "Sin documentos";
    }
  }

  let distribuidores = document.querySelectorAll("#ean_distri");
  if (datos[id].ean != "") {
    var x_union = datos[id].ean;
  } else {
    var x_union = datos[id].ref;
  }
  for (var distribuidor of distribuidores) {
    let te = distribuidor.href;
    let tamanio = te.length - 13;
    href_nuevo = te.substring(0, tamanio) + x_union;
    distribuidor.href = href_nuevo;
  }

  var dimension = document.getElementById("rf_dimensiones");
  if (dimension != null) {
    document.getElementById("rf_dimensiones").innerHTML = datos[id].dimensiones;
  }

  var caracteristicas = document.getElementById("carac");
  if (caracteristicas != null) {
    document.getElementById("carac").innerHTML = datos[id].caracteristicas;
  }

  var largo = document.getElementById("largo");
  if (largo != null) {
    document.getElementById("largo").innerHTML = datos[id].largo;
  }

  var ancho = document.getElementById("ancho");
  if (ancho != null) {
    document.getElementById("ancho").innerHTML = datos[id].ancho;
  }

  var alto = document.getElementById("alto");
  if (alto != null) {
    document.getElementById("alto").innerHTML = datos[id].alto;
  }

  var presentacion = document.getElementById("presentacion");
  if (presentacion != null) {
    document.getElementById("presentacion").innerHTML = datos[id].presentacion;
  }

  var un_entrega = document.getElementById("un_entrega");
  if (un_entrega != null) {
    document.getElementById("un_entrega").innerHTML = datos[id].un_entrega;
  }

  var up_ean = document.getElementById("up_ean");
  if (up_ean != null) {
    document.getElementById("up_ean").innerHTML = datos[id].up_ean;
  }

  var up_cantidad = document.getElementById("up_cantidad");
  if (up_cantidad != null) {
    document.getElementById("up_cantidad").innerHTML = datos[id].up_cantidad;
  }

  var up_peso = document.getElementById("up_peso");
  if (up_peso != null) {
    document.getElementById("up_peso").innerHTML = datos[id].up_peso;
  }

  var up_largo = document.getElementById("up_largo");
  if (up_largo != null) {
    document.getElementById("up_largo").innerHTML = datos[id].up_largo;
  }

  var up_ancho = document.getElementById("up_ancho");
  if (up_ancho != null) {
    document.getElementById("up_ancho").innerHTML = datos[id].up_ancho;
  }

  var up_alto = document.getElementById("up_alto");
  if (up_alto != null) {
    document.getElementById("up_alto").innerHTML = datos[id].up_alto;
  }

  var dropshipping = document.getElementById("dropshipping");
  if (dropshipping != null) {
    document.getElementById("dropshipping").innerHTML = datos[id].dropshipping;
  }

  var estado = document.getElementById("estado");
  if (estado != null) {
    document.getElementById("estado").innerHTML = datos[id].estado;
  }

  var estado_fecha = document.getElementById("estado_fecha");
  if (estado_fecha != null) {
    document.getElementById("estado_fecha").innerHTML = datos[id].estado_fecha;
  }

}

set_datos();

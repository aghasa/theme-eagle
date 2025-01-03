var slideIndex = 1;

/**
 * Función que muestra la division que entra como parámetro
 */
function mostrar_datos(parte){    
    
    var ob = document.getElementById(parte);
    if (ob.classList.contains("daterium-admin-ocultar")) {
        ob.classList.remove("daterium-admin-ocultar");
    }
}

function open_modal(codigo) {
    var ob = document.getElementById(codigo);
    ob.classList.add("daterium-admin-open_modal");
}

function cerrar_modal(codigo) {
    var ob = document.getElementById(codigo);
    ob.classList.remove("daterium-admin-open_modal");
}

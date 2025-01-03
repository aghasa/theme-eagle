<?php

defined('ABSPATH') or die();

$insertado = 0;

if (isset($_POST['valor_nuevo']) && $_POST['valor_nuevo'] != "") {
    $result = $metodos_bbdd->modificar_valor(trim($_POST['cod']), trim($_POST['valor_nuevo']));
    $insertado = $result;
}



?>
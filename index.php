<?php
require "init.php";
require 'vendor/autoload.php';

use base\controller\init;
use gamboamartin\errores\errores;


$data = (new init())->index();
if(errores::$error){
    $error = (new errores())->error(mensaje: 'Error al inicializar datos',data:  $data);
    print_r($error);
    die('Error');
}

$controlador = $data->controlador;
$link = $data->link;
$conf_generales = $data->conf_generales;
include "principal.php";
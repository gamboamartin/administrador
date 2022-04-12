<?php
include '../config/constantes.php';

require '../vendor/autoload.php';
use config\empresas;

$_SESSION['usuario_id'] = 2;

$empresas = new empresas();
$empresas_registradas = $empresas->empresas;
$host = $empresas_registradas[1]['host'];
$user = $empresas_registradas[1]['user'];
$pass = $empresas_registradas[1]['pass'];
$port = $empresas_registradas[1]['port'];
$nombre_base_datos = $empresas_registradas[1]['nombre_base_datos'];

$link = new PDO("mysql:host=$host;dbname=$nombre_base_datos", $user, $pass);
$link->query("SET NAMES 'utf8'");
$sql = "SET sql_mode = '';";
$link->query($sql);

use gamboamartin\errores\errores;
use models\prospecto_ubicacion;


$prospecto_ubicacion_modelo = new prospecto_ubicacion($link);

$ubicaciones = $prospecto_ubicacion_modelo->registros();
if(errores::$error){
    $error = (new errores())->error('Error al obtener ubicaciones',$ubicaciones);
    print_r($error);
    exit;
}

foreach ($ubicaciones as $ubicacion){
    $prospecto_ubicacion_modelo->registro['responsable_compra_id'] = $ubicacion['responsable_compra_id'];
    $link->beginTransaction();
    $regenera = $prospecto_ubicacion_modelo->transaccion_relaciones($ubicacion['prospecto_ubicacion_id']);
    if(errores::$error){
        $link->rollBack();
        $error = (new errores())->error('Error al regenerar',$regenera);
        print_r($error);
        exit;
    }
    $link->commit();
    print_r($regenera);
    echo "<br><br>";
}



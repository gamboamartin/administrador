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
use models\base_prospecto;


$base_prospecto_modelo = new base_prospecto($link);

$filtro['base_prospecto.asignable'] = 'inactivo';

$r = $base_prospecto_modelo->filtro_and(filtro:$filtro, order: array('base_prospecto.cp'=>'ASC'),limit:1000);

$registros = $r['registros'];

foreach ($registros as $reg){

    $result = $base_prospecto_modelo->actualiza_asignable($reg['base_prospecto_id']);
    if(errores::$error) {
        $error = (new errores())->error('Error al regenerar', $result);
        print_r($error);
        exit;
    }
    print_r($result);
    echo "<br><br>";
}
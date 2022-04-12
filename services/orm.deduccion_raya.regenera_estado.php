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
use models\deduccion_raya;


$deduccion_raya_modelo = new deduccion_raya($link);

$deducciones = $deduccion_raya_modelo->registros();
if(errores::$error){
    $error = (new errores())->error('Error al obtener deducciones',$deducciones);
    print_r($error);
    exit;
}

foreach ($deducciones as $deduccion){
    $link->beginTransaction();
    $regenera = $deduccion_raya_modelo->regenera_estado($deduccion['deduccion_raya_id']);
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



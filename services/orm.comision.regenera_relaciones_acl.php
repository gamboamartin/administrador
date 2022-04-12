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
use models\comision;


$comision_modelo = new comision($link);

$comision_modelo->limit = 100;
$comision_modelo->order = array('comision.fecha_ultima_regeneracion'=>'ASC');
$comisiones = $comision_modelo->registros();
if(errores::$error){
    $error = (new errores())->error('Error al obtener ubicaciones',$comisiones);
    print_r($error);
    exit;
}

foreach ($comisiones as $comision){
    $link->beginTransaction();
    $regenera = $comision_modelo->regenera_relaciones_acl($comision['comision_id']);
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
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
use models\documento_prospecto_ubicacion;
use models\prospecto_ubicacion;

$documento_prospecto_ubicacion_modelo= new documento_prospecto_ubicacion($link);

$filtro['documento_prospecto_ubicacion.rename_doc'] = 'inactivo';


$r_docs = $documento_prospecto_ubicacion_modelo->filtro_and(filtro: $filtro,order: array('documento_prospecto_ubicacion.id'=>'ASC'),limit: 3);
if(errores::$error){
    print_r($r_docs);exit;
}
$documentos = $r_docs['registros'];

foreach ($documentos as $documento){
    $link->beginTransaction();
    $dbx = $documento_prospecto_ubicacion_modelo->rename_doc($documento['documento_prospecto_ubicacion_id']);
    if (errores::$error) {
        $link->rollBack();
        $error = (new errores())->error('Error al renombrar', $dbx);
        $link->beginTransaction();
        print_r($error);
        exit;
    }
    $link->commit();
    print_r($dbx);
    echo "<br><br>";
}


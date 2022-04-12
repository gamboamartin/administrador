<?php
include '../config/constantes.php';


use base\compresor;
use config\empresas;
use gamboamartin\errores\errores;
use models\documento_prospecto_ubicacion;

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


$documento_prospecto_ubicacion = new documento_prospecto_ubicacion($link);
$filtro['documento_prospecto_ubicacion.redimencionado'] = 'inactivo';
$r_docs = $documento_prospecto_ubicacion->filtro_and(filtro:$filtro, order: array('documento_prospecto_ubicacion.id'=>'DESC'),limit: 3);
if(errores::$error){
    $error = (new errores())->error('Error al obtener',$r_docs);
    print_r($error);
    exit;
}
$docs = $r_docs['registros'];

foreach ($docs as $doc){
    errores::$error = false;
    $link->beginTransaction();
    $redimencion = (new \controllers\controlador_prospecto_ubicacion($link))->comprime_documento_prospecto_ubicacion($link,$doc['documento_prospecto_ubicacion_id']);
    if(errores::$error){
        $link->rollBack();
        $error = (new errores())->error('Error al redimencionar',$redimencion);
        print_r($error);
        echo "<br><br>";
        continue;
    }
    $link->commit();
    print_r($redimencion);
    echo "<br><br>";
}

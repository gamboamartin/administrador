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
use models\documento_prospecto;
errores::$error = false;
$modelo= new \models\documento_prospecto_ubicacion($link);



$documentos = $modelo->docs_sin_redimencionar(1);
if(errores::$error){
    print_r($documentos);exit;
}

foreach ($documentos as $documento){
    $link->beginTransaction();
    $dbx = $modelo->comprime_documento_calidad($documento[$modelo->tabla.'_id']);
    if (errores::$error) {
        $link->rollBack();
        $error = (new errores())->error('Error al redimencionar', $dbx);
        $link->beginTransaction();
        print_r($error);
        exit;
    }
    $link->commit();
    print_r($dbx);
    echo "<br><br>";
}


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
use base\images;
use models\foto_previa;


$foto_previa_modelo = new foto_previa($link);
$filtro['foto_previa.redimencionada'] = 'inactivo';
$r_fotos = $foto_previa_modelo->filtro_and(filtro:$filtro,limit: 30);
if(errores::$error){
    $error = (new errores())->error('Error al obtener',$r_fotos);
    print_r($error);
    exit;
}
$fotos = $r_fotos['registros'];

foreach ($fotos as $foto){
    $link->beginTransaction();
    $redimencion = (new images())->redimenciona_foto_previa($link,$foto['foto_previa_id']);
    if(errores::$error){
        $link->rollBack();
        $error = (new errores())->error('Error al redimencionar',$redimencion);
        print_r($error);
        exit;
    }
    $link->commit();
    print_r($redimencion);
    echo "<br><br>";
}



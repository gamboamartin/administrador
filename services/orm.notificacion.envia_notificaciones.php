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
use models\notificacion;

$notificacion_modelo = new notificacion($link);
$filtro['notificacion.status_envio'] = 'sin enviar';
$r_notificacion = $notificacion_modelo->filtro_and(filtro:$filtro,limit: 2);
if(errores::$error){
    $error = (new errores())->error('Error al obtener',$r_notificacion);
    print_r($error);
    exit;
}

$notificaciones = $r_notificacion['registros'];
foreach ($notificaciones as $notificacion){
    $link->beginTransaction();
    $notificacion_modelo->registro_id = $notificacion['notificacion_id'];
    $envio = $notificacion_modelo->envia_notificaciones();
    if(errores::$error){
        $link->rollBack();
        $error = (new errores())->error('Error al enviar correo',$envio);
        print_r($error);
        exit;
    }
    $link->commit();
    print_r($envio);
    echo "<br><br>";
}
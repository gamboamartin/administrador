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
use models\relacion_ubicacion;
use models\ubicacion;


$ubicacion_modelo = new ubicacion($link);

for($status_ubicacion_id = 1; $status_ubicacion_id<4;$status_ubicacion_id ++) {

    $filtro['status_ubicacion.id'] = $status_ubicacion_id;
    $r_ubicacion = $ubicacion_modelo->filtro_and($filtro);
    if (errores::$error) {
        $error = (new errores())->error('Error al obtener ubicaciones', $r_ubicacion);
        print_r($error);
        exit;
    }

    $ubicaciones = $r_ubicacion['registros'];
    foreach ($ubicaciones as $ubicacion) {
        $link->beginTransaction();
        $regenera = $ubicacion_modelo->regenera_total_ub_rel($ubicacion['ubicacion_id']);
        if (errores::$error) {
            $link->rollBack();
            $error = (new errores())->error('Error al regenerar', $regenera);
            print_r($error);
            exit;
        }
        $link->commit();
        print_r($regenera);
        echo "<br><br>";
    }
}



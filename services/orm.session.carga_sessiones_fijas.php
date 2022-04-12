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
use models\session;


$session_modelo = new session($link);
$result = $session_modelo->carga_sessiones_fijas();
print_r($result);
echo "<br><br>";

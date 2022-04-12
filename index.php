<?php
require "init.php";

use base\conexion;
use base\seguridad;
use config\generales;
use controllers\controlador_session;
use gamboamartin\errores\errores;
use gamboamartin\frontend\directivas;
use gamboamartin\frontend\templates;
use models\accion;
use models\elemento_lista;
use models\menu;
use models\session;

require 'vendor/autoload.php';

$con = new conexion();
$link = conexion::$link;


$session = (new session($link))->carga_data_session();
if(errores::$error){
    $error = (new errores())->error('Error al asignar session',$session);
    print_r($error);
    die('Error');
}

$conf_generales = new generales();
$seguridad = new seguridad();


$_SESSION['tiempo'] = time();


$seccion = $seguridad->seccion;
$accion = $seguridad->accion;
$webservice = $seguridad->webservice;



define('SECCION',$seccion);
define('ACCION',$accion);


if($link) {
    $modelo_accion = new accion($link);
    if (isset($_SESSION['grupo_id'])) {

        $permiso = $modelo_accion->valida_permiso(SECCION, ACCION);
        if(errores::$error){
            $error = $modelo_accion->error->error('Error al validar permisos',$permiso);
            print_r($error);
            die('Error');
        }
        if(ACCION === 'login' || ACCION === 'loguea'){
            $permiso = true;
        }

        if (!$permiso) {
            $seccion = 'session';
            $accion = 'denegado';
            $_GET['tipo_mensaje'] = 'error';
            $_GET['mensaje'] = 'Permiso denegado';
        }
        $n_acciones = $modelo_accion->cuenta_acciones();
        if(isset($n_acciones['error'])){
            $error = $modelo_accion->error->error('Error al contar acciones permitidas',$n_acciones);
            print_r($error);
            session_destroy();
            die('Error');
        }
        if ($n_acciones == 0) {
            session_destroy();
        }
    }
}

$directiva = new directivas();
$ws = false;
$header = true;
$view = false;
if(isset($_GET['ws'])){
    $header = false;
    $ws = true;

}
if(isset($_GET['view'])){
    $header = false;
    $ws = false;
    $view = true;
}

$name_ctl = 'controlador_'.$seccion;
$name_ctl = str_replace('controllers\\','',$name_ctl);
$name_ctl = 'controllers\\'.$name_ctl;
if(!class_exists($name_ctl)){
    $error = $directiva->errores->error('Error no existe la clase '.$name_ctl,$name_ctl);
    if($ws){
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode($error);
        exit;
    }
    print_r($error);
    die('Error');
}

if(SECCION === 'session') {
    $controlador = new controlador_session($link);
}
else{

    $controlador = new $name_ctl($link);
}

if($link) {
    $elm = new elemento_lista($link);
    $filtro = array();
    $filtro['seccion.descripcion'] = $seccion;
    $filtro['elemento_lista.status'] = 'activo';
    $filtro['elemento_lista.lista'] = 'activo';

    $resultado = $elm->obten_registros_filtro_and_ordenado(campo: 'elemento_lista.orden', filtros: $filtro,orden: 'ASC');
    if(errores::$error){
        $error =  (new errores())->error('Error al obtener obten_registros_filtro_and_ordenado',$resultado);
        print_r($error);
        die('Error');

    }
    $elementos_lista = $resultado->registros;

    $elm = new elemento_lista($controlador->link);
    $filtro = array();
    $filtro['seccion.descripcion'] = $seccion;
    $filtro['elemento_lista.filtro'] = 'activo';
    $filtro['elemento_lista.status'] = 'activo';

    $resultado = $elm->obten_registros_filtro_and_ordenado(campo: 'elemento_lista.orden', filtros: $filtro,orden: 'ASC');

    if(errores::$error){
        $error =  (new errores())->error('Error al obtener registros',$resultado);
        print_r($error);
        die('Error');
    }

    $elementos_lista_filtro = $resultado->registros;

    $template = new templates($link);
    if(errores::$error){
        $error =  (new errores())->error('Error al generar template',$template);
        print_r($error);
        die('Error');
    }
}


$data = $controlador->$accion($header, $ws);


if($ws && ($accion === 'denegado'))
{
    echo json_encode(array('mensaje'=>$_GET['mensaje'], 'error'=>True));
    exit;
}
if($ws){
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$nombre_empresa = '';


$errores_previos = array();
if(isset($_SESSION['error_resultado'])){
    $errores_previos = $_SESSION['error_resultado'];
}
$errores_transaccion = '';
if(count($errores_previos)>0) {
    $errores_html = '<div class="alert alert-danger no-margin-bottom alert-dismissible fade show" role="alert">';
    $errores_html .= '<h4 class="alert-heading">';
    $errores_html .= 'Error';
    $errores_html .= '</h4>';

    foreach ($errores_previos as $error_previo) {
        $errores_html .= $error_previo['mensaje'] ;
        $errores_html .= ' Line '.$error_previo['line'] ;
        $errores_html .= ' Funcion  '.$error_previo['function'] ;
        $errores_html .= ' Class '.$error_previo['class'] . '<br><br>';
    }
    $errores_html.='<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>';

    $errores_html.='<button type="button" class="btn btn-danger" data-toggle="collapse" data-target="#msj_error">Detalle</button>';
    $errores_html.='<div class="collapse" id="msj_error">';
    foreach ($errores_previos as $error_previo) {
        $errores_html.=print_r($error_previo,true);
        $errores_html.='<br><br>';
    }
    $errores_html.='</div>';

    $errores_html.='</div>';

    $errores_transaccion = $errores_html;
    if (isset($_SESSION['error_resultado'])) {
        unset($_SESSION['error_resultado']);
    }
}

$mensajes_exito = array();
if(isset($_SESSION['exito'])){
    $mensajes_exito = $_SESSION['exito'];
}

$exito_transaccion = '';
if(count($mensajes_exito)>0) {
    $exito_html =   '<div class="alert alert-success no-margin-bottom alert-dismissible fade show no-print" role="alert">';
    $exito_html  .=     '<h4 class="alert-heading">Exito</h4><hr>';
    $exito_html.=       '<button type="button" class="btn btn-success" data-toggle="collapse" data-target="#msj_exito">Detalle</button>';
    $exito_html.=       '<div class="collapse" id="msj_exito">';
    foreach ($mensajes_exito as $mensaje_exito) {
        $exito_html .=      '<p class="mb-0">'.$mensaje_exito['mensaje'] . '</p>';
    }
    $exito_html.=       '</div>';
    $exito_html.='        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>';
    $exito_html .=      '</div></div>';
    $exito_transaccion = $exito_html;
    if (isset($_SESSION['exito'])) {
        unset($_SESSION['exito']);
    }
}


if($view){
    ob_clean();
    $include = './views/'.$seccion.'/'.$accion.'.php';
    if(file_exists($include)){
        include($include);
    }
    elseif(ACCION == 'lista') {
        include('./views/vista_base/lista.php');
    }
    elseif (ACCION=='modifica'){
        include('./views/vista_base/modifica.php');
    }
    elseif (ACCION=='alta'){
        include('./views/vista_base/alta.php');
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="img/favicon/favicon.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo " Administrador "; ?></title>
    <link rel="stylesheet" href="node_modules/jquery-ui-dist/jquery-ui.css">
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap-grid.css">
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap-reboot.css">
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="node_modules/bootstrap-select/dist/css/bootstrap-select.css">
    <link rel="stylesheet" href="assets/css/layout.css">

    <script src="node_modules/jquery/dist/jquery.js"></script>
    <script src="https://cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
    <script src="node_modules/jquery-ui-dist/jquery-ui.js"></script>
    <script src="node_modules/popper.js/dist/umd/popper.js" ></script>
    <script src="node_modules/bootstrap/dist/js/bootstrap.js"></script>
    <script src="node_modules/bootstrap-select/dist/js/bootstrap-select.js"></script>
    <script src='node_modules/html5-qrcode/minified/html5-qrcode.min.js'></script>
    <script type="text/javascript" src="node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="node_modules/google-charts/dist/loader.js"></script>
    <script type="text/javascript" src="js/base.js"></script>
    <script type="text/javascript" src="js/checkbox.js"></script>

    <?php  if(file_exists('./css/'.$seccion.'.'.$accion.'.css')){
        ?>
        <link rel="stylesheet" href="./css/<?php echo $seccion.'.'.$accion.'.css'; ?>">
        <?php
    } ?>
    <?php if(file_exists('./js/'.$seccion.'.js')){
        ?>
        <script type="text/javascript" src="./js/<?php echo $seccion.'.js'; ?>"></script>
        <?php

    }
    ?>
    <?php if(file_exists('./js/'.$accion.'.js')){
        ?>
        <script type="text/javascript" src="./js/<?php echo $accion.'.js'; ?>"></script>
        <?php

    }
    ?>

</head>
<body>
<nav class="navbar sticky-top navbar-dark bg-info">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main_nav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="main_nav">
    <?php if($seguridad->menu){

        $modelo_menu = new menu($link);
        $r_menu = $modelo_menu->obten_menu_permitido();
        if(errores::$error){
            $error = $modelo_menu->error->error('Error al obtener menu',$r_menu);
            print_r($error);
            die('Error');
        }
        $menus = $r_menu['registros'];
        foreach($menus as $menu) {
            include $conf_generales->path_base . 'views/_templates/_principal_menu.php';
        }
    } ?>
    </div>
</nav>


<div>
    <?php
    if((string)$errores_transaccion!==''){
        echo $errores_transaccion;
    }
    if((string)$exito_transaccion!==''){
        echo $exito_transaccion;
    }
    ?>

    <div class="modal fade modal-error" id="modalError" tabindex="-1" aria-labelledby="errorLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="errorLabel">Error</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="mensaje_error_modal">
                   Mensaje
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>



    <?php

    echo $controlador->breadcrumbs;
    $include = './views/'.$seccion.'/'.$accion.'.php';

    if(file_exists($include)){
        include($include);
    }
    elseif(ACCION == 'lista') {
        include('./views/vista_base/lista.php');
    }
    elseif (ACCION=='modifica'){
        include('./views/vista_base/modifica.php');
    }
    elseif (ACCION=='alta'){
        include('./views/vista_base/alta.php');
    } ?>

</div>
</body>
</html>
<?php
require "init.php";
require 'vendor/autoload.php';

use base\conexion;
use base\controller\base_html;
use base\controller\errores_html;
use base\controller\init;
use base\controller\salida_data;
use base\frontend\directivas;
use base\seguridad;
use config\generales;
use gamboamartin\errores\errores;
use models\menu;
use models\session;

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
$directiva = new directivas();
$_SESSION['tiempo'] = time();


$seguridad = (new init())->permiso( link: $link,seguridad:   $seguridad);
if(errores::$error){
    $error = (new gamboamartin\errores\errores())->error('Error al verificar seguridad', $seguridad);
    print_r($error);
    die('Error');
}

$controlador = (new init())->controller(link:  $link,seccion:  $seguridad->seccion);
if(errores::$error){
    $error = (new gamboamartin\errores\errores())->error('Error al generar controlador', $controlador);
    print_r($error);
    die('Error');
}

$include_action = (new init())->include_action(seguridad: $seguridad);
if(errores::$error){
    $error = (new gamboamartin\errores\errores())->error('Error al generar include', $include_action);
    print_r($error);
    die('Error');
}


$out_ws = (new salida_data())->salida_ws(controlador:$controlador, include_action: $include_action,seguridad:  $seguridad);
if(errores::$error){
    $error = (new gamboamartin\errores\errores())->error('Error al generar salida', $out_ws);
    print_r($error);
    die('Error');
}

$nombre_empresa = '';


$errores_transaccion = (new errores_html())->errores_transaccion();
if(errores::$error){
    $error = (new gamboamartin\errores\errores())->error('Error al generar errores', $errores_transaccion);
    print_r($error);
    die('Error');
}


$mensajes_exito = $_SESSION['exito'] ?? array();

$exito_transaccion = '';
if(count($mensajes_exito)>0) {

    $close_btn = (new base_html())->close_btn();
    if(errores::$error){
        $error = (new gamboamartin\errores\errores())->error('Error al generar boton', $close_btn);
        print_r($error);
        die('Error');
    }

    $exito_html =   '<div class="alert alert-success no-margin-bottom alert-dismissible fade show no-print" role="alert">';
    $exito_html  .=     '<h4 class="alert-heading">Exito</h4><hr>';
    $exito_html.=       '<button type="button" class="btn btn-success" data-toggle="collapse" data-target="#msj_exito">Detalle</button>';
    $exito_html.=       '<div class="collapse" id="msj_exito">';
    foreach ($mensajes_exito as $mensaje_exito) {
        $exito_html .=      '<p class="mb-0">'.$mensaje_exito['mensaje'] . '</p>';
    }
    $exito_html.=       '</div>';
    $exito_html.= $close_btn;
    $exito_html .=      '</div></div>';
    $exito_transaccion = $exito_html;
    if (isset($_SESSION['exito'])) {
        unset($_SESSION['exito']);
    }
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

    <?php  if(file_exists('./css/'.$seguridad->seccion.'.'.$seguridad->accion.'.css')){
        ?>
        <link rel="stylesheet" href="./css/<?php echo $seguridad->seccion.'.'.$seguridad->accion.'.css'; ?>">
        <?php
    } ?>
    <?php if(file_exists('./js/'.$seguridad->seccion.'.js')){
        ?>
        <script type="text/javascript" src="./js/<?php echo $seguridad->seccion.'.js'; ?>"></script>
        <?php

    }
    ?>
    <?php if(file_exists('./js/'.$seguridad->accion.'.js')){
        ?>
        <script type="text/javascript" src="./js/<?php echo $seguridad->accion.'.js'; ?>"></script>
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
    if($errores_transaccion!==''){
        echo $errores_transaccion;
    }
    if($exito_transaccion!==''){
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
    include($include_action);
    ?>

</div>
</body>
</html>
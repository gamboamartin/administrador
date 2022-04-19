<?php
require "init.php";
require 'vendor/autoload.php';

use base\controller\init;
use gamboamartin\errores\errores;
use models\menu;

$data = (new init())->index();
if(errores::$error){
    $error = (new errores())->error('Error al inicializar datos', $data);
    print_r($error);
    die('Error');
}

$controlador = $data->controlador;
$link = $data->link;
$conf_generales = $data->conf_generales;


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

    <?php echo $data->css_custom; ?>
    <?php echo $data->js_seccion; ?>
    <?php echo $data->js_accion; ?>


</head>
<body>
<nav class="navbar sticky-top navbar-dark bg-info">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main_nav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="main_nav">
    <?php if($data->menu){

        $modelo_menu = new menu($data->link);
        $r_menu = $modelo_menu->obten_menu_permitido();
        if(errores::$error){
            $error = $modelo_menu->error->error('Error al obtener menu',$r_menu);
            print_r($error);
            die('Error');
        }
        $menus = $r_menu['registros'];
        foreach($menus as $menu) {
            include $data->path_base . 'views/_templates/_principal_menu.php';
        }
    } ?>
    </div>
</nav>


<div>
    <?php
    if($data->error_msj !== ''){
        echo $data->error_msj;
    }
    if($data->exito_msj !== ''){
        echo $data->exito_msj;
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
    echo $data->breadcrumbs;
    include($data->include_action);
    ?>

</div>
</body>
</html>
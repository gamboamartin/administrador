<?php
/** @var array $sub_menu */
/** @var PDO $link */

use gamboamartin\errores\errores;
use models\adm_accion_grupo;

$etiqueta_sub_menu = trim($sub_menu['descripcion']);
$etiqueta_sub_menu = str_replace('_',' ',$etiqueta_sub_menu);
$etiqueta_sub_menu = ucwords($etiqueta_sub_menu);

$modelo_accion_grupo = new adm_accion_grupo($link);
$r_action_grupo = $modelo_accion_grupo->obten_accion_permitida(seccion_menu_id: $sub_menu['id']);
if(errores::$error){
    $error =  $modelo_accion_grupo->error->error('Error al obtener accion permitida',$r_action_grupo);
    print_r($error);
    die('Error');
}
$id_children_sm = trim($sub_menu['descripcion']);
$id_children_sm .= "id_sm";
$actions = $r_action_grupo->registros;
?>
    <button type="button" class="btn btn-outline-dark" data-toggle="collapse" data-target="#<?php echo $id_children_sm; ?>"> <?php echo $etiqueta_sub_menu; ?></button>
    <div class="collapse padding-top-10 padding-bottom-10" id = "<?php echo $id_children_sm; ?>">
        <h6 class="card-subtitle"><?php echo $etiqueta_sub_menu; ?></h6>
        <?php
        foreach ($actions as $action){
            include $conf_generales->path_base . 'views/_templates/_action_menu.php';
        }
        ?>
    </div>
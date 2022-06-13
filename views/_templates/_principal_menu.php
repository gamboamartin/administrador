<?php
/** @var array $menu */
/** @var PDO $link */

use gamboamartin\errores\errores;
use models\adm_seccion;


$etiqueta_menu = trim($menu['descripcion']);
$etiqueta_menu = str_replace('_',' ',$etiqueta_menu);
$etiqueta_menu = ucwords($etiqueta_menu);
$modelo_seccion_menu = new adm_seccion($link);
$r_sub_menu = $modelo_seccion_menu->obten_submenu_permitido($menu['id']);
if(errores::$error){
    $error = $modelo_seccion_menu->error->error('Error al obtener sub menu',$r_sub_menu);
    print_r($error);
    die('Error');
}
$id_children = trim($menu['descripcion']);
$id_children .= '_children';
$sub_menus = $r_sub_menu['registros'];
?>
<button type="button" class="btn btn-outline-light" data-toggle="collapse" data-target="#<?php echo $id_children; ?>">
    <?php echo $etiqueta_menu; ?>
</button>
<div id="<?php echo $id_children; ?>" class="collapse border-bottom border-white padding-bottom-10 padding-top-10">
    <h5 class="card-title"><?php echo $etiqueta_menu; ?></h5>
    <?php
    foreach ($sub_menus as $sub_menu){
        include $conf_generales->path_base . 'views/_templates/_section_menu.php';
    }
    ?>
</div>
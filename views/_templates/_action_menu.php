<?php
/** @var array $action */
/** @var PDO $link */
use gamboamartin\errores\errores;
//print_r($action);exit;
$etiqueta_action = trim($action['adm_accion_descripcion']);
$etiqueta_action = str_replace('_',' ',$etiqueta_action);
$etiqueta_action = ucwords($etiqueta_action); ?>

<a class="btn btn btn-success" href="index.php?seccion=<?php echo $action['adm_seccion_descripcion']?>&accion=<?php echo $action['adm_accion_descripcion']?>&session_id=<?php echo $conf_generales->session_id; ?>">
    <?php echo $etiqueta_action; ?>
</a>
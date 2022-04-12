<?php /** @var base\controller\controlador_base $controlador */ ?>
<?php /** @var config\generales $conf_generales */?>

<?php
echo $controlador->lista_html;
include $conf_generales->path_base.'views/_templates/_rpt_head.php';
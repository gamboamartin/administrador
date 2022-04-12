<?php /** @var base\controller\controlador_base $controlador */ ?>
<div class="col-md-12 data-rpt">
    <hr>
    <span>Total de Registros: <b><?php echo $controlador->registros['n_registros']; ?></b></span>
    <span>Filtros: <?php echo $controlador->registros['filtros']; ?></span>
    <span>Usuario:  <b><?php echo $_SESSION['usuario_id']; ?></b></span>
    <span>Fecha Hora Reporte: <b><?php echo date('Y-m-d H:i:s'); ?></b></span>
    <span>Direccion IP: <b> <?php echo $controlador->registros['ip']; ?></b></span>
    <hr>
</div>

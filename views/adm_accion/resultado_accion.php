<?php /** @var controlador_accion $controlador */?>
<div class="col-md-12">
    <hr>
    <h3>Acciones</h3>
</div>

<table class="table table-striped no-print" style="max-width: 97%; margin: 50px auto;">
    <thead>
    <tr>
        <th scope="col">Menu</th>
        <th scope="col">Seccion</th>
        <th scope="col">Accion</th>
        <th scope="col">Ejecutar</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($controlador->acciones as $accion){
        ?>
        <tr>
            <td><?php echo $accion['menu_descripcion']; ?></td>
            <td><?php echo $accion['seccion_menu_descripcion']; ?></td>
            <td><?php echo $accion['accion_descripcion']; ?></td>
            <td><?php echo $accion['ejecuta']; ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
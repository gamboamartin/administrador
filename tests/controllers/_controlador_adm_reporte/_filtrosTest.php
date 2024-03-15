<?php
namespace gamboamartin\administrador\tests\controllers\_controlador_adm_reporte;


use gamboamartin\controllers\_controlador_adm_reporte\_filtros;
use gamboamartin\controllers\controlador_adm_reporte;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _filtrosTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/administrador/config/generales.php';
        $this->paths_conf->database = '/var/www/html/administrador/config/database.php';
        $this->paths_conf->views = '/var/www/html/administrador/config/views.php';
    }

    public function test_filtro_rango_post(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';


        $_filtro = new _filtros();
        $_filtro = new liberator($_filtro);

        $table = 'A';
        $resultado = $_filtro->filtro_rango_post($table);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }





}


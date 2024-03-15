<?php
namespace gamboamartin\administrador\tests\controllers\_controlador_adm_reporte;


use gamboamartin\controllers\_controlador_adm_reporte\_filtros;
use gamboamartin\controllers\_controlador_adm_reporte\_table;
use gamboamartin\controllers\controlador_adm_reporte;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _tableTest extends test {
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


    public function test_td(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'fc_factura';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';


        $html = new _table();
        $html = new liberator($html);

        $key_registro = 'a';
        $registro = array();
        $registro['a'] = '';
        $resultado = $html->td(false,$key_registro, $registro);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<td></td>",$resultado);
        $this->assertIsString($resultado);
        errores::$error = false;
    }





}


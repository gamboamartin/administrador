<?php
namespace gamboamartin\administrador\tests\controllers\_controlador_adm_reporte;


use gamboamartin\controllers\_controlador_adm_reporte\_fechas;
use gamboamartin\controllers\_controlador_adm_reporte\_filtros;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _fechasTest extends test {
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

    public function test_init_filtro_fecha(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'fc_factura';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';


        $_filtro = new _fechas();
        $_filtro = new liberator($_filtro);


        $resultado = $_filtro->init_filtro_fecha();

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertFalse($resultado->existe_alguna_fecha);
        $this->assertFalse($resultado->existe_fecha_inicial);
        $this->assertFalse($resultado->existe_fecha_final);


        errores::$error = false;
    }

    public function test_init_fecha_inicial(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'adm_accion';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';


        $_filtro = new _fechas();
        $_filtro = new liberator($_filtro);

        $_POST['fecha_inicial'] = '';
        $data = new stdClass();
        $resultado = $_filtro->init_fecha_inicial($data);
        //print_r($resultado);exit;

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado->existe_alguna_fecha);
        $this->assertTrue($resultado->existe_fecha_inicial);

        errores::$error = false;
    }


}


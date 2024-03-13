<?php
namespace tests\controllers;

use gamboamartin\administrador\instalacion\instalacion;
use gamboamartin\administrador\models\_instalacion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class instalacionTest extends test {
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

    public function test__add_adm_reporte(): void
    {

        errores::$error = false;
        $_SESSION['usuario_id'] = 2;


        $instalacion = new instalacion();
        $instalacion = new liberator($instalacion);
        $resultado = $instalacion->_add_adm_reporte(link: $this->link);
        $this->assertFalse(errores::$error);
        $this->assertIsObject($resultado);
        errores::$error = false;

        $resultado = (new _instalacion(link: $this->link))->describe_table(table: 'adm_reporte');
        $this->assertEquals('id', $resultado->registros[0]['Field']);
        $this->assertEquals('predeterminado', $resultado->registros[11]['Field']);
        errores::$error = false;


    }

    public function test_instala(): void
    {

        errores::$error = false;
        $_SESSION['usuario_id'] = 2;


        $instalacion = new instalacion();
       // $ctl = new liberator($ctl);
        $resultado = $instalacion->instala(link: $this->link);
        $this->assertFalse(errores::$error);
        $this->assertIsObject($resultado);
        errores::$error = false;

    }



}


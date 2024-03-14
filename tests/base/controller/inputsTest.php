<?php
namespace gamboamartin\administrador\tests\base\controller;

use base\controller\inputs;
use gamboamartin\controllers\controlador_adm_grupo;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class inputsTest extends test {
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

    public function test_inputs_view(): void
    {

        errores::$error = false;

        $in = new inputs();
        //$in = new liberator($in);


        $controler = new controlador_adm_grupo(link: $this->link, paths_conf: $this->paths_conf);
        $inputs = array();
        $key = 'key';
        $value = array();
        $value['type'] = 'value_type';
        $inputs['value_type'] = 'a_in';
        $controler->inputs = new stdClass();

        $resultado = $in->inputs_view($controler, $inputs, $key, $value);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_type(): void
    {

        errores::$error = false;

        $in = new inputs();
        $in = new liberator($in);

        $value = array();
        $value['type'] = 'x';
        $resultado = $in->type($value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado);
        errores::$error = false;
    }

    public function test_type_validado(): void
    {

        errores::$error = false;

        $in = new inputs();
        $in = new liberator($in);

        $value = array();
        $value['type'] = 'x';
        $inputs = array();
        $inputs['x'] = 'a';
        $resultado = $in->type_validado($inputs, $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado);
        errores::$error = false;

    }

}
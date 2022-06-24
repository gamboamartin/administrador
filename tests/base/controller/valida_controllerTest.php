<?php
namespace tests\base\controller;

use base\controller\valida_controller;
use gamboamartin\controllers\controlador_adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class valida_controllerTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();


        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/administrador/config/generales.php';
        $this->paths_conf->database = '/var/www/html/administrador/config/database.php';
        $this->paths_conf->views = '/var/www/html/administrador/config/views.php';
    }

    /**
     * @throws JsonException
     */
    public function test_valida_clase(){
        errores::$error = false;
        $val = new valida_controller();
        //$nm = new liberator($nm);

        $controler = new controlador_adm_seccion(link: $this->link, paths_conf: $this->paths_conf);
        $controler->seccion = 'a';
        $resultado = $val->valida_clase($controler);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_el(){
        errores::$error = false;
        $val = new valida_controller();
        //$nm = new liberator($nm);

        $campo = '';
        $seccion = '';
        $tabla_externa = '';
        $resultado = $val->valida_el($campo, $seccion, $tabla_externa);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error tabla_externa no puede venir vacio", $resultado['mensaje']);

        errores::$error = false;

        $campo = '';
        $seccion = '';
        $tabla_externa = 'a';
        $resultado = $val->valida_el($campo, $seccion, $tabla_externa);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'c';
        $seccion = '';
        $tabla_externa = 'a';
        $resultado = $val->valida_el($campo, $seccion, $tabla_externa);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $seccion no puede venir vacio', $resultado['mensaje']);
        errores::$error = false;

        $campo = 'c';
        $seccion = 'd';
        $tabla_externa = 'a';
        $resultado = $val->valida_el($campo, $seccion, $tabla_externa);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_post_alta(){
        errores::$error = false;
        $val = new valida_controller();
        //$nm = new liberator($nm);
        $_POST = array();

        $resultado = $val->valida_post_alta();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error el POST no puede venir vacio", $resultado['mensaje']);

        errores::$error = false;
        $_POST = array();
        $_POST['A'] = 'X';

        $resultado = $val->valida_post_alta();
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_post_modifica(){
        errores::$error = false;
        $val = new valida_controller();
        //$nm = new liberator($nm);
        $_POST = array();

        $resultado = $val->valida_post_modifica();

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("POST Debe tener info", $resultado['mensaje']);

        errores::$error = false;
        $_POST = array();
        $_POST[] = '';
        $resultado = $val->valida_post_modifica();
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);



        errores::$error = false;
    }




}
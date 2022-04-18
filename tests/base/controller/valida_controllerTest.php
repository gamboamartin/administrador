<?php
namespace tests\base\controller;

use base\controller\valida_controller;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class valida_controllerTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
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
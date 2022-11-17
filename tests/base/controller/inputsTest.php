<?php
namespace tests\base\controller;

use base\controller\controler;
use base\controller\filtros;
use base\controller\inputs;
use gamboamartin\administrador\models\adm_atributo;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class inputsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
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
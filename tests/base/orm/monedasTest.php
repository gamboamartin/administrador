<?php
namespace tests\base\orm;

use base\orm\monedas;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class monedasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_limpia_moneda_value(){

        errores::$error = false;
        $monedas = new monedas();
        $monedas = new liberator($monedas);
        $value = '';
        $resultado = $monedas->limpia_moneda_value($value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $value = '$$,,00';
        $resultado = $monedas->limpia_moneda_value($value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('00', $resultado);
        errores::$error = false;
    }

    public function test_limpia_monedas_values(){

        errores::$error = false;
        $monedas = new monedas();
        $monedas = new liberator($monedas);
        $tipo_dato = 'a';
        $tipos_moneda = array('a');
        $value = '$1.00';
        $resultado = $monedas->limpia_monedas_values($tipo_dato, $tipos_moneda, $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1.00', $resultado);
        errores::$error = false;
    }

    public function test_reasigna_value_moneda(){

        errores::$error = false;
        $monedas = new monedas();
        $monedas = new liberator($monedas);
        $campo = 'a';
        $modelo = new adm_accion_grupo($this->link);
        $tipos_moneda = array();
        $value = '';
        $resultado = $monedas->reasigna_value_moneda($campo, $modelo, $tipos_moneda, $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;
    }

    public function test_value_moneda(){

        errores::$error = false;
        $monedas = new monedas();
        //$monedas = new liberator($monedas);
        $campo = 'a';
        $modelo = new adm_accion_grupo($this->link);
        $tipos_moneda = array();
        $value = '';
        $resultado = $monedas->value_moneda($campo, $modelo, $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;
    }



}
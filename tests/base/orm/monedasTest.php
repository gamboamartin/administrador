<?php
namespace tests\base\orm;

use base\orm\monedas;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_accion;


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



}
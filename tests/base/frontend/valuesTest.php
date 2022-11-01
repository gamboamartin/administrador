<?php
namespace tests\base\frontend;

use base\frontend\values;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class valuesTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }



    public function test_valor_envio(): void
    {
        errores::$error = false;
        $val = new values();
        //$val = new liberator($val);

        $valor = '';
        $resultado = $val->valor_envio($valor);
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('-1', $resultado);

        errores::$error = false;

        $valor = '2';
        $resultado = $val->valor_envio($valor);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('2', $resultado);
        errores::$error = false;
    }

    public function test_valor_moneda(): void
    {
        errores::$error = false;
        $val = new values();
        //$inicializacion = new liberator($inicializacion);

        $valor = '';
        $resultado = $val->valor_moneda($valor);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('$0.00', $resultado);

        errores::$error = false;

        $valor = '01.000';
        $resultado = $val->valor_moneda($valor);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('$1.00', $resultado);

        errores::$error = false;

        $valor = '-$01.000';
        $resultado = $val->valor_moneda($valor);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('-$1.00', $resultado);

        errores::$error = false;
    }

}
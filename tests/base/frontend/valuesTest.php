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

    public function test_adapta_valor_campo(): void
    {
        errores::$error = false;
        $val = new values();
        $val = new liberator($val);

        $campos = array();
        $key = '';
        $registro = array();
        $resultado = $val->adapta_valor_campo(campos:$campos, key: $key, registro:  $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campos no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $campos = array();
        $key = '';
        $registro = array();
        $campos[] = '';
        $resultado = $val->adapta_valor_campo(campos:$campos, key: $key, registro:  $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $key no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $key = 'a';
        $registro = array();
        $campos[] = '';
        $resultado = $val->adapta_valor_campo(campos:$campos, key: $key, registro:  $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $registro no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $key = 'a';
        $registro = array();
        $campos[] = '';
        $registro[] = '';
        $resultado = $val->adapta_valor_campo(campos:$campos, key: $key, registro:  $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campos[a] no existe', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $key = 'a';
        $registro = array();
        $campos['a'] = '';
        $registro[] = '';
        $resultado = $val->adapta_valor_campo(campos:$campos, key: $key, registro:  $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campos[a][representacion] no existe', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $key = 'a';
        $registro = array();
        $campos['a']['representacion'] = 'moneda';
        $registro[] = '';
        $resultado = $val->adapta_valor_campo(campos:$campos, key: $key, registro:  $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $registro[a] debe ser un numero', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $key = 'a';
        $registro = array();
        $campos['a']['representacion'] = 'moneda';
        $registro['a'] = '1';
        $resultado = $val->adapta_valor_campo(campos:$campos, key: $key, registro:  $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('$1.00', $resultado['a']);
        errores::$error = false;
    }

    public function test_content_option_value(): void
    {
        errores::$error = false;
        $val = new values();
        //$val = new liberator($val);

        $tabla = '';
        $value = array();
        $resultado = $val->content_option_value($tabla, $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla esta vacia', $resultado['mensaje']);

        errores::$error = false;

        $tabla = 'a';
        $value = array();
        $resultado = $val->content_option_value($tabla, $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("value=''", $resultado);
        errores::$error = false;

    }

    public function test_data_extra_html_base(){
        errores::$error = false;
        $val = new values();
        //$inicializacion = new liberator($inicializacion);

        $data = '';
        $value = '';
        $resultado =  $val->data_extra_html_base(data: $data, value: $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al data esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $data = 'x';
        $value = '';
        $resultado =  $val->data_extra_html_base(data: $data, value: $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("data-x  =  '' ", $resultado);

        errores::$error = false;
    }

    public function test_datas_con_valor(){
        errores::$error = false;
        $val = new values();
        //$inicializacion = new liberator($inicializacion);

        $data_con_valor = array();
        $data_con_valor['x'] = '';
        $resultado = $val->datas_con_valor(data_con_valor: $data_con_valor);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("data-x  =  '' ", $resultado);

        errores::$error = false;

        $data_con_valor = array();
        $data_con_valor['x'] = 'a';
        $resultado = $val->datas_con_valor(data_con_valor: $data_con_valor);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("data-x  =  'a' ", $resultado);

        errores::$error = false;

        $data_con_valor = array();
        $resultado = $val->datas_con_valor(data_con_valor: $data_con_valor);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);

        errores::$error = false;
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

    public function test_value_fecha(): void{
        errores::$error = false;
        $val = new values();
        //$inicializacion = new liberator($inicializacion);

        $value = '';
        $tipo = '';
        $value_vacio = false;

        $resultado = $val->value_fecha(tipo:  $tipo, value: $value,value_vacio:  $value_vacio);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;


        $value = 'a';
        $tipo = '';

        $resultado = $val->value_fecha(tipo:  $tipo, value: $value,value_vacio:  $value_vacio);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado);

        $value = '';
        $tipo = 'date';

        $resultado = $val->value_fecha(tipo:  $tipo, value: $value,value_vacio:  $value_vacio);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase(date('Y'), $resultado);
        errores::$error = false;
    }


}
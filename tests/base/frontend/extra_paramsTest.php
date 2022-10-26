<?php
namespace tests\base\frontend;

use base\frontend\extra_params;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class extra_paramsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_data_extra_base(){
        errores::$error = false;
        $ep = new extra_params();
        $inicializacion = new liberator($ep);

        $data = '';
        $value[] = '';

        $resultado = $inicializacion->data_extra_base(data: $data, value: $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al data esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $data = 'x';
        $value[] = '';

        $resultado = $inicializacion->data_extra_base(data: $data, value: $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("data-x  =  '' ", $resultado);

        errores::$error = false;

        $data = 'x';
        $value['x'] = 'a';

        $resultado = $inicializacion->data_extra_base(data: $data, value: $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("data-x  =  'a' ", $resultado);

        errores::$error = false;
    }



    public function test_datas_extra(){
        errores::$error = false;
        $ep = new extra_params();
        //$inicializacion = new liberator($ep);

        $data_con_valor = array();
        $data_extra = array();
        $value = array();
        $resultado = $ep->datas_extra(data_con_valor: $data_con_valor, data_extra: $data_extra, value: $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(" ", $resultado);

        errores::$error = false;

        $data_con_valor['x'] = '';
        $data_extra = array();
        $value = array();
        $resultado = $ep->datas_extra(data_con_valor: $data_con_valor, data_extra: $data_extra, value: $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(" data-x  =  '' ", $resultado);

        errores::$error = false;

        $data_con_valor['x'] = '';
        $data_extra['y'] = '';
        $value = array();
        $resultado = $ep->datas_extra(data_con_valor: $data_con_valor, data_extra: $data_extra, value: $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al data esta vacio<",
            $resultado['data']['data']['mensaje']);

        errores::$error = false;

        $data_con_valor['x'] = '';
        $data_extra['y'] = 'a';
        $value['a'] = 'b';
        $resultado = $ep->datas_extra(data_con_valor: $data_con_valor, data_extra: $data_extra, value: $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("data-a  =  'b'  data-x  =  '' ", $resultado);

        errores::$error = false;
    }

    public function test_datas_extras(){
        errores::$error = false;
        $ep = new extra_params();
        $inicializacion = new liberator($ep);

        $data_extra['x'] = '';
        $value = array();
        $resultado = $inicializacion->datas_extras(data_extra: $data_extra, value: $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar data extra', $resultado['mensaje']);

        errores::$error = false;

        $data_extra['x'] = 'x';
        $value['x'] = 'a';
        $resultado = $inicializacion->datas_extras(data_extra: $data_extra, value: $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("data-x  =  'a' ", $resultado);

        errores::$error = false;

        $data_extra = array();
        $value = array();
        $resultado = $inicializacion->datas_extras(data_extra: $data_extra, value: $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);

        errores::$error = false;
    }
}
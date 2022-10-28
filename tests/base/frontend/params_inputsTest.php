<?php
namespace tests\base\frontend;

use base\frontend\params_inputs;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class params_inputsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }




    public function test_disabled_html()
    {
        errores::$error = false;
        $params = new params_inputs();
        //$params = new liberator($params);
        $disabled = false;

        $resultado = $params->disabled_html($disabled);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;

        $disabled = true;

        $resultado = $params->disabled_html($disabled);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('disabled',$resultado);

        errores::$error = false;
    }


    public function test_data_content_option(){
        errores::$error = false;
        $params = new params_inputs();
        //$params = new liberator($params);
        $data_con_valor = array();
        $data_extra = array();
        $tabla = 'a';
        $valor_envio = '1';
        $value = array();
        $resultado = $params->data_content_option($data_con_valor, $data_extra, $tabla, $valor_envio, $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar selected', $resultado['mensaje']);
        errores::$error = false;
    }


    public function test_multiple_html(){
        errores::$error = false;
        $params = new params_inputs();

        $resultado =  $params->multiple_html(multiple: false);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado->multiple);

        errores::$error = false;

        $resultado =  $params->multiple_html(multiple: true);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("multiple", $resultado->multiple);

        errores::$error = false;
    }


    public function test_required_html(): void
    {
        errores::$error = false;
        $params = new params_inputs();
        //$params = new liberator($params);

        $resultado = $params->required_html(required: false);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $resultado = $params->required_html(required: true);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('required', $resultado);
        errores::$error = false;

    }

    
}
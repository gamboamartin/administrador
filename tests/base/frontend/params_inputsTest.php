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

    public function test_base_input(){
        errores::$error = false;
        $params = new params_inputs();
        $params = new liberator($params);

        $campo = '';
        $clases_css = array();
        $data_extra = array();
        $ids_css = array();
        $pattern = '';
        $value = '';
        $resultado = $params->base_input(campo: $campo, clases_css: $clases_css, data_extra:  $data_extra,
            disabled: false, ids_css: $ids_css, pattern: $pattern, required: true, value: $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo no puede venir vacio',
            $resultado['mensaje']);

        errores::$error = false;

        $campo = 'x';
        $clases_css = array();
        $data_extra = array();
        $ids_css = array();
        $pattern = '';
        $value = '';
        $resultado = $params->base_input(campo: $campo, clases_css: $clases_css, data_extra:  $data_extra,
            disabled: false, ids_css: $ids_css, pattern: $pattern, required: true, value: $value);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado->ids);

        errores::$error = false;

        $campo = 'x';
        $clases_css = array();
        $data_extra = array();
        $ids_css = array();
        $pattern = 'a';
        $value = 'b';
        $resultado = $params->base_input(campo: $campo, clases_css: $clases_css, data_extra:  $data_extra,
            disabled: false, ids_css: $ids_css, pattern: $pattern, required: true, value: $value);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("pattern='a'", $resultado->pattern);

        errores::$error = false;
    }

    public function test_base_input_dinamic(){
        errores::$error = false;
        $params = new params_inputs();
        $params = new liberator($params);

        $campo = '';
        $etiqueta = '';
        $resultado = $params->base_input_dinamic(campo: $campo, etiqueta: $etiqueta);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo no puede venir vacio',
            $resultado['mensaje']);

        errores::$error = false;

        $campo = 'x';
        $etiqueta = '';
        $resultado = $params->base_input_dinamic(campo: $campo, etiqueta: $etiqueta);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado->name);

        errores::$error = false;

        $campo = 'x';
        $etiqueta = 'a';
        $resultado = $params->base_input_dinamic(campo: $campo, etiqueta: $etiqueta);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->campo_mostrable);

        errores::$error = false;
    }

    /*
    public function test_checked(){
        errores::$error = false;
        $params = new params_inputs();
        $params = new liberator($params);
        $valor = '';
        $resultado = $params->checked(valor: $valor);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;
        $valor = 'activo';
        $resultado = $params->checked(valor: $valor);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('checked', $resultado);

        errores::$error = false;
        $valor = 'inactivo';
        $resultado = $params->checked(valor: $valor);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;
    }*/

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

    public function test_disabled_html(){
        errores::$error = false;
        $params = new params_inputs();
        //$params = new liberator($params);

        $resultado = $params->disabled_html(disabled: false);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $resultado = $params->disabled_html(disabled: true);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('disabled', $resultado);
        errores::$error = false;
    }



    public function test_ids_html(){
        errores::$error = false;
        $params = new params_inputs();
        //$params = new liberator($params);
        $ids_css = array();
        $campo = '';
        $resultado = $params->ids_html(campo: $campo, ids_css: $ids_css);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo esta vacio', $resultado['mensaje']);

        errores::$error = false;
        $ids_css = array();
        $campo = 'a';
        $resultado = $params->ids_html(campo: $campo, ids_css: $ids_css);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado);

        errores::$error = false;
        $ids_css = array();
        $campo = 'a';
        $ids_css[] = 'b';
        $ids_css[] = 'c';
        $resultado = $params->ids_html(campo: $campo, ids_css: $ids_css);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a b c', $resultado);
        errores::$error = false;
    }

    public function test_limpia_obj(): void
    {
        errores::$error = false;
        $params = new params_inputs();
        $params = new liberator($params);
        $keys = array();
        $parametros = new stdClass();
        $resultado = $params->limpia_obj(keys: $keys, params: $parametros);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

        $keys = array();
        $keys[] = 'a';
        $parametros = new stdClass();
        $resultado = $params->limpia_obj(keys: $keys, params: $parametros);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_limpia_obj_btn()
    {
        errores::$error = false;
        $params = new params_inputs();
        $params = new liberator($params);

        $parametros = new stdClass();
        $resultado = $params->limpia_obj_btn(params: $parametros);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $parametros = new stdClass();
        $parametros->class = 'a';
        $resultado = $params->limpia_obj_btn(params: $parametros);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado->class);

        errores::$error = false;
    }

    public function test_limpia_obj_input(){
        errores::$error = false;
        $params = new params_inputs();

        $parametros = new stdClass();
        $resultado = $params->limpia_obj_input(params: $parametros);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $parametros = new stdClass();
        $parametros->class = 'a';
        $resultado = $params->limpia_obj_input(params: $parametros);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado->class);

        errores::$error = false;
    }

    public function test_ln()
    {
        errores::$error = false;
        $params = new params_inputs();

        $size = '';
        $resultado = $params->ln(ln: false, size: $size);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error size no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $size = 'a';
        $resultado = $params->ln(ln: false, size: $size);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $size = 'a';
        $resultado = $params->ln(ln: true, size: $size);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div class='col-a-12'></div>", $resultado);
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



    public function test_params_fecha()
    {
        errores::$error = false;
        $params = new params_inputs();

        $data_extra = array();
        $css = array();
        $ids = array();
        $campo = '';
        $resultado = $params->params_fecha(campo: $campo,  css: $css, data_extra: $data_extra, disabled: false,
            ids: $ids, required: false);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $data_extra = array();
        $css = array();
        $ids = array();
        $campo = 'z';
        $resultado = $params->params_fecha(campo: $campo,  css: $css, data_extra: $data_extra, disabled: false,
            ids: $ids, required: false);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->disabled);
        $this->assertEquals('', $resultado->required);
        $this->assertEquals('', $resultado->data_extra);
        $this->assertEquals('', $resultado->class);
        $this->assertEquals('z', $resultado->ids);

        errores::$error = false;


        $data_extra = array();
        $css = array();
        $ids = array();
        $campo = 'z';
        $disabled = false;
        $required = false;
        $css[] = 'p';
        $resultado = $params->params_fecha(campo: $campo,  css: $css, data_extra: $data_extra, disabled: false,
            ids: $ids, required: false);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->disabled);
        $this->assertEquals('', $resultado->required);
        $this->assertEquals('', $resultado->data_extra);
        $this->assertEquals(' p', $resultado->class);
        $this->assertEquals('z', $resultado->ids);

        errores::$error = false;
    }

    public function test_params_input(){
        errores::$error = false;
        $params = new params_inputs();

        $campo = '';
        $clases_css = array();
        $data_extra = array();
        $etiqueta = '';
        $ids_css = array();
        $pattern = '';
        $value = '';

        $resultado = $params->params_input(campo: $campo,clases_css: $clases_css, data_extra: $data_extra,
            disabled: false, etiqueta: $etiqueta, ids_css: $ids_css, pattern: $pattern, required: false,
            value: $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo no puede venir vacio',
            $resultado['mensaje']);

        errores::$error = false;

        $campo = 'x';
        $clases_css = array();
        $data_extra = array();
        $etiqueta = '';
        $ids_css = array();
        $pattern = '';
        $value = '';

        $resultado = $params->params_input(campo: $campo,clases_css: $clases_css, data_extra: $data_extra,
            disabled: false, etiqueta: $etiqueta, ids_css: $ids_css, pattern: $pattern, required: false,
            value: $value);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->campo_mostrable);
        $this->assertEquals('', $resultado->place_holder);
        $this->assertEquals('x', $resultado->name);
        $this->assertEquals('', $resultado->pattern);
        $this->assertEquals('', $resultado->class);

        errores::$error = false;

        $campo = 'x';
        $clases_css = array();
        $data_extra = array();
        $etiqueta = '';
        $ids_css = array();
        $pattern = '';
        $value = 'z';

        $resultado = $params->params_input(campo: $campo,clases_css: $clases_css, data_extra: $data_extra,
            disabled: false, etiqueta: $etiqueta, ids_css: $ids_css, pattern: $pattern, required: false,
            value: $value);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->campo_mostrable);
        $this->assertEquals('', $resultado->place_holder);
        $this->assertEquals('x', $resultado->name);
        $this->assertEquals('', $resultado->pattern);
        $this->assertEquals('z', $resultado->value);

        errores::$error = false;
    }

    public function test_pattern_html(){
        errores::$error = false;
        $params = new params_inputs();
        $params = new liberator($params);

        $pattern = '';
        $resultado = $params->pattern_html(pattern: $pattern);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $pattern = 'a';
        $resultado = $params->pattern_html(pattern: $pattern);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("pattern='a'", $resultado);

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
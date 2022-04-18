<?php
namespace tests\base\frontend;

use base\frontend\checkboxes;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class checkboxesTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }




    public function test_checkbox(): void
    {
        errores::$error = false;
        $chk = new checkboxes();
        //$inicializacion = new liberator($inicializacion);
        $salto = '';
        $div_chk = '';
        $etiqueta = '';
        $data_input = '';

        $resultado = $chk->checkbox( $data_input,$div_chk, $etiqueta,$salto);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);
        errores::$error = false;
        $salto = 'a';
        $div_chk = 'b';
        $etiqueta = 'c';
        $data_input = 'd';


        $resultado = $chk->checkbox($data_input,$div_chk, $etiqueta,$salto);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a b',$resultado);

        errores::$error = false;
        $salto = 'a';
        $div_chk = 'b';
        $etiqueta = '';
        $data_input = 'd';
        $resultado = $chk->checkbox($data_input,$div_chk, $etiqueta,$salto);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('d',$resultado);

        errores::$error = false;


    }

    public function test_data_chk(): void{
        errores::$error = false;
        $chk = new checkboxes();
        //$inicializacion = new liberator($inicializacion);
        $campo = '';
        $valor = '';
        $class = '';
        $id_html = '';
        $data_extra_html = '';
        $checked_html = '';
        $data_etiqueta = '';
        $cols = '13';

        $resultado = $chk->data_chk(campo: $campo,checked_html: $checked_html,class: $class, cols: $cols,
            data_etiqueta: $data_etiqueta,data_extra_html: $data_extra_html, disabled_html: '', id_html: $id_html,
            valor: $valor);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);

        errores::$error = false;
        $campo = 'a';
        $valor = '';
        $class = '';
        $id_html = '';
        $data_extra_html = '';
        $checked_html = '';
        $data_etiqueta = '';
        $cols = '13';

        $resultado = $chk->data_chk(campo: $campo,checked_html: $checked_html,class: $class, cols: $cols,
            data_etiqueta: $data_etiqueta,data_extra_html: $data_extra_html, disabled_html: '', id_html: $id_html,
            valor: $valor);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar cols', $resultado['mensaje']);

        errores::$error = false;
        $campo = 'a';
        $valor = '';
        $class = '';
        $id_html = '';
        $data_extra_html = '';
        $checked_html = '';
        $data_etiqueta = '';
        $cols = '12';

        $resultado = $chk->data_chk(campo: $campo,checked_html: $checked_html,class: $class, cols: $cols,
            data_etiqueta: $data_etiqueta,data_extra_html: $data_extra_html, disabled_html: '', id_html: $id_html,
            valor: $valor);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("input type='checkbox'", $resultado->data_input);
        $this->assertStringContainsStringIgnoringCase("addon checkbox_directiva", $resultado->div_chk);

    }

    public function test_data_span_chk(){
        errores::$error = false;
        $chk = new checkboxes();
        $chk = new liberator($chk);
        $campo = '';
        $valor = '';
        $class = '';
        $id_html = '';
        $data_extra_html = '';
        $checked_html = '';
        $resultado = $chk->data_span_chk($campo, $valor, $class, $id_html, $data_extra_html, $checked_html);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al llamar datos', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'z';
        $valor = '';
        $class = '';
        $id_html = '';
        $data_extra_html = '';
        $checked_html = '';
        $resultado = $chk->data_span_chk($campo, $valor, $class, $id_html, $data_extra_html, $checked_html,'');

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<input type='checkbox'  name='z' value='inactivo'", $resultado->data_input);
        $this->assertStringContainsStringIgnoringCase("<span class='input-group-addon checkbox_directiva'><input type=", $resultado->span_chk);
        errores::$error = false;
    }

    public function test_div_chk(): void{
        errores::$error = false;
        $chk = new checkboxes();
        $chk = new liberator($chk);
        $cols = 0;
        $span_btn_chk = '';
        $resultado = $chk->div_chk($cols, $span_btn_chk);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar cols', $resultado['mensaje']);
        errores::$error = false;
        $cols = 1;
        $span_btn_chk = '';
        $resultado = $chk->div_chk($cols, $span_btn_chk);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div class='form-group col-md-1'>", $resultado);
        errores::$error = false;
    }

    public function test_etiqueta_chk(): void{
        errores::$error = false;
        $chk = new checkboxes();
        $chk = new liberator($chk);

        $data_etiqueta = '';
        $span_chk = '';
        $cols = '0';
        $resultado = $chk->etiqueta_chk($cols, $data_etiqueta, $span_chk);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar cols', $resultado['mensaje']);

        errores::$error = false;

        $data_etiqueta = '';
        $span_chk = '';
        $cols = '1';
        $resultado = $chk->etiqueta_chk($cols, $data_etiqueta, $span_chk);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div class='form-group col-md-1'>", $resultado);

        errores::$error = false;
    }


}
<?php
namespace tests\base\frontend;

use base\frontend\forms;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class formsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_header_form(): void
    {
        errores::$error = false;
        $frm = new forms();
        //$inicializacion = new liberator($inicializacion);
        $seccion = '';
        $accion = '';
        $accion_request = '';
        $session_id = '';
        $resultado = $frm->header_form(accion:  $accion, accion_request: $accion_request, seccion: $seccion,
            session_id: $session_id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $seccion no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;
        $seccion = 'a';
        $accion = '';
        $accion_request = '';
        $session_id = '';
        $resultado = $frm->header_form(accion:  $accion, accion_request: $accion_request, seccion: $seccion,
            session_id: $session_id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $accion no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;
        $seccion = 'a';
        $accion = 'b';
        $accion_request = '';
        $session_id = '';
        $resultado = $frm->header_form(accion:  $accion, accion_request: $accion_request, seccion: $seccion,
            session_id: $session_id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $accion_request no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;
        $seccion = 'a';
        $accion = 'b';
        $accion_request = 'c';
        $session_id = 'd';
        $resultado = $frm->header_form(accion:  $accion, accion_request: $accion_request, seccion: $seccion,
            session_id: $session_id);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("action='./index.php?seccion=a&session_id=d&accion=c'", $resultado);

        errores::$error = false;

    }

    public function test_header_form_group(){
        errores::$error = false;
        $frm = new forms();
        //$inicializacion = new liberator($inicializacion);

        $cols = '0';
        $resultado = $frm->header_form_group(cols: $cols);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error cols debe ser mayor a 0", $resultado['data']['mensaje']);

        errores::$error = false;

        $cols = '13';
        $resultado = $frm->header_form_group(cols: $cols);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error cols debe ser menor a 13", $resultado['data']['mensaje']);

        errores::$error = false;

        $cols = '12';
        $resultado = $frm->header_form_group(cols: $cols);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="form-group col-md-12">', $resultado);

        errores::$error = false;
    }

    public function test_genera_form_group(): void
    {
        errores::$error = false;
        $frm = new forms();
        //$inicializacion = new liberator($inicializacion);
        $cols = '1';
        $campo = '1';
        $resultado = $frm->genera_form_group($cols, $campo);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<div class='form-group col-md-1 selector_1'>|label||input|</div>", $resultado);

        errores::$error = false;

    }




}
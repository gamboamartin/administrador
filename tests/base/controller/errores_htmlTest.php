<?php
namespace tests\base\controller;

use base\controller\errores_html;
use base\controller\exito_html;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class errores_htmlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_contenido_modal(): void
    {

        errores::$error = false;

        $html = new errores_html();
        $html = new liberator($html);

        $errores_previos = array();
        $errores_previos[0]['mensaje'] = 'a';
        $errores_previos[0]['line'] = 'a';
        $errores_previos[0]['function'] = 'a';
        $errores_previos[0]['class'] = 'a';
        $resultado = $html->contenido_modal($errores_previos);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('<button type="button" class="btn btn-danger" data-toggle="collapse" data-target="#msj_error">Detalle</button>',$resultado);
        errores::$error = false;
    }

    public function test_detalle_btn(): void
    {

        errores::$error = false;

        $html = new errores_html();
        $html = new liberator($html);

        $resultado = $html->detalle_btn();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<button type="button" class="btn btn-danger" data-toggle="collapse" data-target="#msj_error">Detalle</button>',$resultado);
        errores::$error = false;
    }

    public function test_error_previo(): void
    {

        errores::$error = false;

        $html = new errores_html();
        $html = new liberator($html);

        $error_previo = array();
        $error_previo['mensaje'] = 'a';
        $error_previo['line'] = 'a';
        $error_previo['function'] = 'a';
        $error_previo['class'] = 'a';
        $resultado = $html->error_previo($error_previo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a Line a Funcion  a Class a',$resultado);
        errores::$error = false;
    }

    public function test_error_previo_detalle(): void
    {

        errores::$error = false;

        $html = new errores_html();
        $html = new liberator($html);

        $error_previo = array();
        $resultado = $html->error_previo_detalle($error_previo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_errores_previos(): void
    {

        errores::$error = false;

        $html = new errores_html();
        $html = new liberator($html);

        $errores_previos = array();
        $errores_previos[0]['mensaje'] = 'a';
        $errores_previos[0]['line'] = 'a';
        $errores_previos[0]['function'] = 'a';
        $errores_previos[0]['class'] = 'a';
        $resultado = $html->errores_previos($errores_previos);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a Line a Funcion  a Class a<br><br>',$resultado);
        errores::$error = false;
    }

    public function test_errores_previos_detalle(): void
    {

        errores::$error = false;

        $html = new errores_html();
        $html = new liberator($html);

        $errores_previos = array();
        $errores_previos[] = array();
        $errores_previos[] = array();
        $resultado = $html->errores_previos_detalle($errores_previos);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_modal_btns(): void
    {

        errores::$error = false;

        $html = new errores_html();
        $html = new liberator($html);

        $resultado = $html->modal_btns();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('<button type="button" class="close" data-dismiss="alert" aria-label="Close">',$resultado);
        errores::$error = false;
    }

}
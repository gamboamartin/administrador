<?php
namespace gamboamartin\administrador\tests\base\controller;

use base\controller\errores_html;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class errores_htmlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
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

    public function test_data_modal_error(): void
    {

        errores::$error = false;

        $html = new errores_html();
        $html = new liberator($html);

        $errores_previos = array();
        $errores_previos[0]['mensaje'] = 'a';
        $errores_previos[0]['line'] = 'a';
        $errores_previos[0]['function'] = 'a';
        $errores_previos[0]['class'] = 'a';

        $resultado = $html->data_modal_error($errores_previos);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
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

    public function test_errores_transaccion(): void
    {

        errores::$error = false;

        $html = new errores_html();
        //$html = new liberator($html);
        $_SESSION['error_resultado'] = array();
        $_SESSION['error_resultado'][0] = array();
        $_SESSION['error_resultado'][0]['mensaje'] = 'a';
        $_SESSION['error_resultado'][0]['line'] = 'b';
        $_SESSION['error_resultado'][0]['function'] = 'c';
        $_SESSION['error_resultado'][0]['class'] = 'd';


        $resultado = $html->errores_transaccion();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_mensaje_error_detalle(): void
    {

        errores::$error = false;

        $html = new errores_html();
        $html = new liberator($html);

        $errores_previos = array();
        $errores_previos[] = array();

        $resultado = $html->mensaje_error_detalle($errores_previos);
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
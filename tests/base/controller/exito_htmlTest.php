<?php
namespace tests\base\controller;

use base\controller\exito_html;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class exito_htmlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_boton_exito(): void
    {

        errores::$error = false;

        $html = new exito_html();
        $html = new liberator($html);

        $resultado = $html->boton_exito();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<button type="button" class="btn btn-success" data-toggle="collapse" data-target="#msj_exito">Detalle</button>',$resultado);
        errores::$error = false;
    }

    public function test_exito_html(): void
    {

        errores::$error = false;

        $html = new exito_html();
        $html = new liberator($html);
        $html_ = true;
        $resultado = $html->exito_html($html_);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="alert alert-success no-margin-bottom alert-dismissible fade show no-print" role="alert">',$resultado);
        errores::$error = false;
    }

    public function test_exito_html_string(): void
    {

        errores::$error = false;

        $html = new exito_html();
        $html = new liberator($html);

        $close_btn = 'a';
        $exito_html = 'b';
        $resultado = $html->exito_html_string($close_btn, $exito_html);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ba</div>',$resultado);
        errores::$error = false;

    }

    public function test_genera_exito_html(): void
    {

        errores::$error = false;

        $html = new exito_html();
        $html = new liberator($html);
        $html_ = true;
        $resultado = $html->genera_exito_html($html_);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="alert alert-success no-margin-bottom alert-dismissible fade show no-print" role="alert"><h4 class="alert-heading">Exito</h4><button type="button" class="btn btn-success" data-toggle="collapse" data-target="#msj_exito">Detalle</button>',$resultado);
        errores::$error = false;

    }

    public function test_integra_boton(): void
    {

        errores::$error = false;

        $html = new exito_html();
        $html = new liberator($html);
        $exito_html = '';
        $html_ = true;
        $resultado = $html->integra_boton($exito_html, $html_);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<button type="button" class="btn btn-success" data-toggle="collapse" data-target="#msj_exito">Detalle</button>',$resultado);
        errores::$error = false;


    }

    public function test_mensaje(): void
    {

        errores::$error = false;

        $html = new exito_html();
        $html = new liberator($html);

        $mensaje_exito = array();
        $mensaje_exito['mensaje'] = 'Test';

        $resultado = $html->mensaje($mensaje_exito);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<p class="mb-0">Test</p>',$resultado);
        errores::$error = false;
    }

    public function test_mensajes(): void
    {

        errores::$error = false;

        $html = new exito_html();
        $html = new liberator($html);

        $mensajes_exito = array();
        $mensajes_exito[]['mensaje'] = 'Test';
        $mensajes_exito[]['mensaje'] = 'Test 2';


        $resultado = $html->mensajes($mensajes_exito);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<p class="mb-0">Test</p><p class="mb-0">Test 2</p>',$resultado);

        errores::$error = false;
    }

    public function test_mensajes_collapse(): void
    {

        errores::$error = false;

        $html = new exito_html();
        $html = new liberator($html);

        $mensajes_exito = array();
        $mensajes_exito[]['mensaje'] = 'a';
        $mensajes_exito[]['mensaje'] = 'b';

        $resultado = $html->mensajes_collapse($mensajes_exito);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="collapse" id="msj_exito"><p class="mb-0">a</p><p class="mb-0">b</p></div>',$resultado);
        errores::$error = false;
    }



}
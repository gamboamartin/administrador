<?php
namespace gamboamartin\administrador\tests\base\controller;

use base\controller\warning_html;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class warning_htmlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_carga_mensaje(): void
    {

        errores::$error = false;

        $html = new warning_html();
        $html = new liberator($html);

        $mensaje = array();
        $mensaje['mensaje'] = 'hola';
        $warning_transaccion = '';
        $resultado = $html->carga_mensaje($mensaje, $warning_transaccion);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' hola',$resultado);
        errores::$error = false;
    }

    public function test_carga_mensajes(): void
    {

        errores::$error = false;

        $html = new warning_html();
        $html = new liberator($html);

        $mensajes_warning = array();
        $warning_transaccion = '';
        $resultado = $html->carga_mensajes($mensajes_warning, $warning_transaccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;


        $mensajes_warning = array();
        $warning_transaccion = '';
        $mensajes_warning[]['mensaje'] = 'hola';
        $resultado = $html->carga_mensajes($mensajes_warning, $warning_transaccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' hola',$resultado);
        errores::$error = false;
    }

    public function test_mensajes(): void
    {

        errores::$error = false;

        $html = new warning_html();
        //$html = new liberator($html);


        $resultado = $html->mensajes();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;
        $_SESSION['warning'][]['mensaje'] = 'b';

        $resultado = $html->mensajes();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' b',$resultado);
        errores::$error = false;
    }

    public function test_warning_transaccion(): void
    {

        errores::$error = false;

        $html = new warning_html();
        $html = new liberator($html);

        $mensajes_warning = array();

        $resultado = $html->warning_transaccion($mensajes_warning);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;


        $mensajes_warning = array();
        $mensajes_warning[]['mensaje'] = 'a';
        $mensajes_warning[]['mensaje'] = 'b';

        $resultado = $html->warning_transaccion($mensajes_warning);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' a b',$resultado);
        errores::$error = false;
    }

}
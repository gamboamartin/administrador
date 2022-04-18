<?php
namespace tests\base\frontend;

use base\frontend\class_css;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class class_cssTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_class_css_html(){
        errores::$error = false;
        $cl = new class_css();
        //$inicializacion = new liberator($inicializacion);

        $clases_css = array();
        $resultado = $cl->class_css_html($clases_css);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);

        errores::$error = false;
        $clases_css = array();
        $clases_css[] = 'a';
        $resultado = $cl->class_css_html($clases_css);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(" a", $resultado);

        errores::$error = false;


    }

    public function test_inline_html(){
        errores::$error = false;
        $cl = new class_css();
        //$inicializacion = new liberator($inicializacion);

        $size = '';

        $resultado = $cl->inline_html( inline: false, size: $size);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error size no puede venir vacio", $resultado['mensaje']);

        errores::$error = false;

        $size = 'a';

        $resultado = $cl->inline_html(inline: false, size: $size);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("col-a-12", $resultado);

        errores::$error = false;

        $size = 'a';

        $resultado = $cl->inline_html(inline: true, size: $size);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("col-a-10", $resultado);
        errores::$error = false;
    }

    public function test_inline_html_lb(){
        errores::$error = false;
        $cl = new class_css();
        //$inicializacion = new liberator($inicializacion);

        $size = '';
        $resultado = $cl->inline_html_lb(inline: false, size: $size);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error size no puede venir vacio", $resultado['mensaje']);

        errores::$error = false;

        $size = 'md';
        $resultado = $cl->inline_html_lb(inline: false, size: $size);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("label-select", $resultado);

        errores::$error = false;

        $size = 'md';
        $resultado = $cl->inline_html_lb(inline: true, size: $size);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("col-md-2", $resultado);

        errores::$error = false;
    }


}
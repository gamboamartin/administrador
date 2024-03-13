<?php
namespace tests\base\ctl;

use gamboamartin\administrador\ctl\base_html;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class base_htmlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_close_btn(): void
    {

        errores::$error = false;

        $html = new base_html();
        $html = new liberator($html);


        $resultado = $html->close_btn();
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_head(): void
    {

        errores::$error = false;

        $html = new base_html();
        $html = new liberator($html);


        $titulo = 'a';
        $resultado = $html->head($titulo);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<h4 class="alert-heading">a</h4>',$resultado);
        errores::$error = false;
    }


}
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

}
<?php
namespace tests\base\frontend;

use base\frontend\etiquetas;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class etiquetasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }




    public function test_label_upload(){
        errores::$error = false;
        $etiquetas = new etiquetas();
        $inicializacion = new liberator($etiquetas);

        $codigo = '';
        $resultado = $inicializacion->label_upload(codigo: $codigo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error codigo esta vacio", $resultado['mensaje']);

        errores::$error = false;

        $codigo = 'x';
        $resultado = $inicializacion->label_upload(codigo: $codigo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="input-group-prepend"><span class="input-group-text" >x</span></div>',
            $resultado);

        errores::$error = false;
    }

    public function test_labels_multiple(){
        errores::$error = false;
        $etiquetas = new etiquetas();
        //$inicializacion = new liberator($inicializacion);

        $codigo = '';
        $etiqueta = '';
        $resultado = $etiquetas->labels_multiple(codigo: $codigo, etiqueta: $etiqueta);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error codigo esta vacio", $resultado['mensaje']);

        errores::$error = false;

        $codigo = 'x';
        $etiqueta = '';
        $resultado = $etiquetas->labels_multiple(codigo: $codigo, etiqueta: $etiqueta);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error etiqueta esta vacio", $resultado['mensaje']);

        errores::$error = false;

        $codigo = 'x';
        $etiqueta = 'x';
        $resultado = $etiquetas->labels_multiple(codigo: $codigo, etiqueta: $etiqueta);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<div class="input-group-prepend"><span class="input-group-text" >x</span></div>', $resultado->label_upload);

        errores::$error = false;
    }





}
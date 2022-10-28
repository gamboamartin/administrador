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



    public function test_etiqueta_label(){
        errores::$error = false;
        $etiquetas = new etiquetas();
        //$etiquetas = new liberator($etiquetas);

        $etiqueta = '';
        $tabla = '';
        $tipo_letra = '';
        $resultado = $etiquetas->etiqueta_label(etiqueta: $etiqueta, tabla: $tabla);
        $this->assertIsString($resultado);


        errores::$error = false;

        $etiqueta = '';
        $tabla = 'a';
        $tipo_letra = 'capitalize';
        $resultado = $etiquetas->etiqueta_label(etiqueta: $etiqueta, tabla: $tabla);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('A', $resultado);

        errores::$error = false;

        $etiqueta = 'x';
        $tabla = 'a';
        $tipo_letra = 'capitalize';
        $resultado = $etiquetas->etiqueta_label(etiqueta: $etiqueta, tabla: $tabla);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);

        errores::$error = false;

    }

    public function test_etiqueta_label_mostrable(){
        errores::$error = false;
        $etiquetas = new etiquetas();
        $inicializacion = new liberator($etiquetas);

        $etiqueta = '';
        $etiqueta_label = '';
        $resultado = $inicializacion->etiqueta_label_mostrable(etiqueta: $etiqueta, etiqueta_label: $etiqueta_label);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $etiqueta = '';
        $etiqueta_label = 'x';
        $resultado = $inicializacion->etiqueta_label_mostrable(etiqueta: $etiqueta, etiqueta_label: $etiqueta_label);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);

        errores::$error = false;

        $etiqueta = 'a';
        $etiqueta_label = 'd';
        $resultado = $inicializacion->etiqueta_label_mostrable(etiqueta: $etiqueta, etiqueta_label: $etiqueta_label);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado);
        errores::$error = false;
    }





    public function test_label_input_upload(){
        errores::$error = false;
        $etiquetas = new etiquetas();
        $inicializacion = new liberator($etiquetas);

        $etiqueta = '';
        $resultado = $inicializacion->label_input_upload(etiqueta: $etiqueta);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error etiqueta esta vacio", $resultado['mensaje']);

        errores::$error = false;

        $etiqueta = 'x';
        $resultado = $inicializacion->label_input_upload(etiqueta: $etiqueta);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<label class="custom-file-label" for="x">x</label>', $resultado);

        errores::$error = false;
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




    public function test_title(){
        errores::$error = false;
        $etiquetas = new etiquetas();
        //$inicializacion = new liberator($inicializacion);

        $txt = '';
        $resultado = $etiquetas->title(txt: $txt);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error title esta vacio", $resultado['mensaje']);

        errores::$error = false;

        $txt = 'xa';
        $resultado = $etiquetas->title(txt: $txt);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("Xa", $resultado);

        errores::$error = false;
    }


}
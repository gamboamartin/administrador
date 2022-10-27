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



    public function test_con_label(): void{
        errores::$error = false;

        $etiquetas = new etiquetas();

        $size = '';
        $campo = '';
        $campo_capitalize = '';
        $resultado = $etiquetas->con_label(campo: $campo, campo_capitalize: $campo_capitalize,
            con_label: true, size:  $size);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error size no puede venir vacio', $resultado['mensaje']);


        errores::$error = false;

        $size = 'a';
        $campo = '';
        $campo_capitalize = '';
        $resultado = $etiquetas->con_label(campo: $campo, campo_capitalize: $campo_capitalize,
            con_label: true, size:  $size);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $size = 'a';
        $campo = 'b';
        $campo_capitalize = '';
        $resultado = $etiquetas->con_label(campo: $campo, campo_capitalize: $campo_capitalize,
            con_label: true, size:  $size);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo_capitalize no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $size = 'a';
        $campo = 'b';
        $campo_capitalize = 'c';
        $resultado = $etiquetas->con_label(campo: $campo, campo_capitalize: $campo_capitalize,
            con_label: true, size:  $size);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<label class='col-form-label-a' for='b'>c</label>", $resultado);
        errores::$error = false;

    }

    public function test_etiqueta_campo_vista(): void{
        errores::$error = false;
        $etiquetas = new etiquetas();
        //$etiquetas = new liberator($etiquetas);

        $campo_busca = '';
        $resultado = $etiquetas->etiqueta_campo_vista(campo_busca: $campo_busca);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("", $resultado);

        errores::$error = false;


        $campo_busca = 'a_a';
        $resultado = $etiquetas->etiqueta_campo_vista(campo_busca: $campo_busca);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("a a", $resultado);
        errores::$error = false;
    }


    public function test_etiqueta_label(){
        errores::$error = false;
        $etiquetas = new etiquetas();
        $inicializacion = new liberator($etiquetas);

        $etiqueta = '';
        $tabla = '';
        $tipo_letra = '';
        $resultado = $etiquetas->etiqueta_label(etiqueta: $etiqueta, tabla: $tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar etiqueta', $resultado['mensaje']);

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

    public function test_genera_label(){
        errores::$error = false;
        $etiquetas = new etiquetas();
        //$inicializacion = new liberator($inicializacion);

        $campo = '';
        $tipo_letra = 'capitalize';
        $size = '';
        $resultado = $etiquetas->genera_label(aplica_etiqueta: true,campo: $campo,tipo_letra: $tipo_letra,size: $size);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'x';
        $tipo_letra = 'capitalize';
        $size = '';
        $resultado = $etiquetas->genera_label(aplica_etiqueta: false,campo: $campo,tipo_letra: $tipo_letra,size: $size);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);

        errores::$error = false;

        $campo = 'x';
        $tipo_letra = 'capitalize';
        $size = 'x';
        $resultado = $etiquetas->genera_label(aplica_etiqueta: true,campo: $campo,tipo_letra: $tipo_letra,size: $size);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_genera_texto_etiqueta(): void{
        errores::$error = false;
        $etiquetas = new etiquetas();
        //$inicializacion = new liberator($inicializacion);
        $texto = '';
        $tipo_letra = '';
        $resultado = $etiquetas->genera_texto_etiqueta(texto: $texto);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error texto vacio', $resultado['mensaje']);

        errores::$error = false;
        $texto = 'a';
        $tipo_letra = '';
        $resultado = $etiquetas->genera_texto_etiqueta(texto: $texto);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('a', $resultado);

        errores::$error = false;
        $texto = 'aa';
        $tipo_letra = 'capitalize';
        $resultado = $etiquetas->genera_texto_etiqueta(texto: $texto);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('aA', $resultado);
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
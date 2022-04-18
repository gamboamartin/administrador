<?php
namespace tests\base\orm;

use base\orm\where;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class whereTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_campo(): void
    {
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $data = '';
        $key = '';
        $resultado = $wh->campo(data: $data,key:  $key);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key vacio', $resultado['mensaje']);

        errores::$error = false;

        $data = '';
        $key = 'a';
        $resultado = $wh->campo(data: $data,key:  $key);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado);

        errores::$error = false;

        $data = array();
        $key = 'a';
        $data['b'] = '';

        $resultado = $wh->campo(data: $data,key:  $key);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado);

        errores::$error = false;

        $data = array();
        $key = 'a';
        $data['a'] = '';

        $resultado = $wh->campo(data: $data,key:  $key);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado);

        $data = array();
        $key = 'a';
        $data['campo'] = 'x';

        $resultado = $wh->campo(data: $data,key:  $key);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado);
        errores::$error = false;
    }

    public function test_comparacion(){

        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);
        $data = array();
        $resultado = $wh->comparacion(data: $data,default: '');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }

    public function test_comparacion_pura(){

        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);
        $data = array();
        $columnas_extra = array();
        $key = '';
        $resultado = $wh->comparacion_pura($columnas_extra, $data, $key);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key vacio', $resultado['mensaje']);

        errores::$error = false;


        $resultado = $wh->comparacion_pura($columnas_extra, $data, $key);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key vacio', $resultado['mensaje']);

        errores::$error = false;

        $data[] = '';
        $resultado = $wh->comparacion_pura($columnas_extra, $data, $key);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key vacio', $resultado['mensaje']);


        errores::$error = false;
        $data = array();
        $data['value'] = '';
        $key = 'x';
        $resultado = $wh->comparacion_pura($columnas_extra, $data, $key);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado->campo);
        $this->assertEquals('', $resultado->value);


        errores::$error = false;

        $resultado = $wh->comparacion_pura($columnas_extra, $data, $key);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado->campo);
        $this->assertEquals('', $resultado->value);

        errores::$error = false;


    }

    public function test_genera_and(){

        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);
        $filtro = array();
        $columnas_extra = array();
        $resultado = $wh->genera_and($columnas_extra, $filtro);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;

        $filtro[] = '';
        $resultado  = $wh->genera_and($columnas_extra, $filtro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Los key deben de ser campos asoci', $resultado['mensaje']);

        errores::$error = false;

        $filtro = array();
        $filtro['x'] = '';
        $resultado  = $wh->genera_and($columnas_extra, $filtro);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals( "x = ''", $resultado);
        errores::$error = false;
    }

    public function test_genera_and_textos(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $columnas_extra = array();
        $filtro = array();
        $resultado = $wh->genera_and_textos($columnas_extra, $filtro);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals( "", $resultado);
        errores::$error = false;
    }

    public function test_genera_sentencia_base(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $columnas_extra = array();
        $filtro = array();
        $tipo_filtro = 'numeros';
        $resultado = $wh->genera_sentencia_base($columnas_extra, $filtro, $tipo_filtro);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals( "", $resultado);

    }

    public function test_value(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $data = '';
        $resultado = $wh->value($data);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;
    }

    public function test_verifica_tipo_filtro(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $tipo_filtro = '';
        $resultado = $wh->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
        $tipo_filtro = 'a';
        $resultado = $wh->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el tipo filtro no es correcto', $resultado['mensaje']);

        errores::$error = false;
        $tipo_filtro = 'textos';
        $resultado = $wh->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }




}
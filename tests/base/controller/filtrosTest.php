<?php
namespace tests\base\controller;

use base\controller\controler;
use base\controller\filtros;
use gamboamartin\administrador\models\adm_atributo;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class filtrosTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_asigna_filtro_get(): void
    {

        errores::$error = false;

        $fl = new filtros($this->link);
        //$ctl = new liberator($ctl);

        $keys = array();
        $resultado = $fl->asigna_filtro_get($keys,'');


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $keys = array();
        $keys['campo'] = 'a';
        $resultado = $fl->asigna_filtro_get($keys, '');
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);


        errores::$error = false;

        $keys = array();
        $keys['pais'] = 'id';
        $_GET['pais_id'] = 1;
        $resultado = $fl->asigna_filtro_get($keys, '');
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);

        errores::$error = false;



        $keys = array();
        $keys['pais'] = array();
        $_GET['pais_id'] = 1;
        $resultado = $fl->asigna_filtro_get($keys, '');
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;



        $keys = array();
        $keys['pais'] = array('id');
        $_GET['pais_id'] = 1;
        $resultado = $fl->asigna_filtro_get($keys, 'x');
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['pais.id']);


        errores::$error = false;

        $keys = array();
        $keys['pais'] = array('id');
        $_GET['pais_id'] = 1;
        $seccion = 's';
        $resultado = $fl->asigna_filtro_get($keys, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['pais.id']);
        $this->assertEquals('activo',$resultado['s.status']);

        errores::$error = false;

    }

    public function test_key_filter(): void
    {

        errores::$error = false;

        $fl = new filtros($this->link);
        $fl = new liberator($fl);

        $campo ='a';
        $tabla ='d';
        $resultado = $fl->key_filter($campo, $tabla);


        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('d.a',$resultado);

        errores::$error = false;

        $campo ='1';
        $tabla ='     d      ';
        $resultado = $fl->key_filter($campo, $tabla);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('d.1',$resultado);

        errores::$error = false;


    }

    public function test_valida_data_filtro(): void
    {

        errores::$error = false;

        $fl = new filtros();
        $fl = new liberator($fl);

        $campo = 'a';
        $tabla = 'c';

        $resultado = $fl->valida_data_filtro($campo, 'x', $tabla);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }
}
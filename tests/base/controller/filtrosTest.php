<?php
namespace gamboamartin\administrador\tests\base\controller;

use base\controller\filtros;
use gamboamartin\controllers\controlador_adm_sistema;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class filtrosTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/administrador/config/generales.php';
        $this->paths_conf->database = '/var/www/html/administrador/config/database.php';
        $this->paths_conf->views = '/var/www/html/administrador/config/views.php';
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

    public function test_filtra(){
        errores::$error = false;
        $filtros = new filtros();
        //$filtros = new liberator($filtros);

        $controler = new controlador_adm_sistema(link: $this->link, paths_conf: $this->paths_conf);
        $filtros_ = array();

        $resultado = $filtros->filtra($controler, $filtros_);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

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

    public function test_valida_data_filtro_base(): void
    {

        errores::$error = false;

        $fl = new filtros();
        $fl = new liberator($fl);

        $campo = '';
        $tabla = '';

        $resultado = $fl->valida_data_filtro_base($campo, $tabla);
        //print_r($resultado);exit;
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error $campo esta vacio',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = 'a';
        $tabla = '';

        $resultado = $fl->valida_data_filtro_base($campo, $tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error $tabla esta vacio',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = 'a';
        $tabla = 'v';

        $resultado = $fl->valida_data_filtro_base($campo, $tabla);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        //print_r($resultado);exit;
        errores::$error = false;
    }
}
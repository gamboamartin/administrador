<?php
namespace tests\base\orm;

use base\orm\data_base;
use base\orm\data_format;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;



class data_baseTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_asigna_data_no_existe(){
        errores::$error = false;
        $database = new data_base();
        $database = new liberator($database);


        $data = array();
        $registro_previo = array();
        $registro_previo['a'] = 'g';
        $key = 'a';
        $resultado = $database->asigna_data_no_existe($data, $key, $registro_previo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('g',$resultado['a']);

        errores::$error = false;

        $data = array();
        $data['a'] = 'gg';
        $registro_previo = array();
        $registro_previo['a'] = 'g';
        $key = 'a';
        $resultado = $database->asigna_data_no_existe($data, $key, $registro_previo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('gg',$resultado['a']);
        errores::$error = false;
    }

    public function test_asigna_datas_no_existe(){
        errores::$error = false;
        $database = new data_base();
        $database = new liberator($database);


        $data = array();
        $registro_previo = array();
        $registro_previo['a'] = 'd';
        $keys = array('a');
        $resultado = $database->asigna_datas_no_existe($data, $keys, $registro_previo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('d',$resultado['a']);

        errores::$error = false;


        $data = array();
        $data['a'] = 'f';
        $registro_previo = array();
        $registro_previo['a'] = 'd';
        $keys = array('a');
        $resultado = $database->asigna_datas_no_existe($data, $keys, $registro_previo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('f',$resultado['a']);

        errores::$error = false;
    }

    public function test_valida_init_data(){
        errores::$error = false;
        $database = new data_base();
        $database = new liberator($database);


        $registro_previo = array();
        $key = '';
        $resultado = $database->valida_init_data($key, $registro_previo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error key esta vacio',$resultado['mensaje_limpio']);

        errores::$error = false;

        $registro_previo = array();
        $key = 'a';
        $resultado = $database->valida_init_data($key, $registro_previo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error al validar registro previo',$resultado['mensaje_limpio']);

        errores::$error = false;

        $registro_previo = array();
        $registro_previo['a'] = 'p';
        $key = 'a';
        $resultado = $database->valida_init_data($key, $registro_previo);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

        errores::$error = false;
    }


}
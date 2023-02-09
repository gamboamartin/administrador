<?php
namespace tests\base\orm;

use base\orm\data_base;
use base\orm\data_format;
use gamboamartin\administrador\models\adm_mes;
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

    public function test_asigna_data_row_previo(){
        errores::$error = false;
        $database = new data_base();
        $database = new liberator($database);


        $data = array();

        $id = 1;
        $modelo = new adm_mes($this->link);

        $resultado = $database->asigna_data_row_previo($data, $id, $modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['descripcion']);
        $this->assertEquals('1',$resultado['codigo']);
        errores::$error = false;
    }

    public function test_asigna_datas_base(){
        errores::$error = false;
        $database = new data_base();
        $database = new liberator($database);


        $data = array();
        $registro_previo = array();
        $registro_previo['descripcion'] = 'x';
        $registro_previo['codigo'] = 'u';


        $resultado = $database->asigna_datas_base($data, $registro_previo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado['descripcion']);
        $this->assertEquals('u',$resultado['codigo']);

        errores::$error = false;


        $data = array();
        $data['descripcion'] = 'ddd';
        $registro_previo = array();
        $registro_previo['descripcion'] = 'x';
        $registro_previo['codigo'] = 'u';


        $resultado = $database->asigna_datas_base($data, $registro_previo);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ddd',$resultado['descripcion']);
        $this->assertEquals('u',$resultado['codigo']);
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

    public function test_init_data_base(){
        errores::$error = false;
        $database = new data_base();
        //$database = new liberator($database);


        $data = array();

        $id = -1;
        $modelo = new adm_mes($this->link);

        $resultado = $database->init_data_base($data, $id, $modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }


}
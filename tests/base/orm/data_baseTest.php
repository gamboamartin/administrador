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
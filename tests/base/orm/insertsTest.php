<?php
namespace tests\base\orm;

use base\orm\inserts;
use gamboamartin\encripta\encriptador;
use gamboamartin\errores\errores;

use base\orm\inicializacion;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_seccion;

use stdClass;



class insertsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_slaches_campo(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);

        $value = null;
        $campo = 'a';
        $resultado = $ins->slaches_campo($campo, $value);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('NULL',$resultado->value);
        $this->assertEquals(true,$resultado->value_es_null);
        $this->assertEquals('a',$resultado->campo);
        errores::$error = false;
    }

    public function test_value(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);

        $value = null;
        $resultado = $ins->value($value);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('NULL',$resultado->value);
        $this->assertEquals(true,$resultado->value_es_null);

        errores::$error = false;

        $value = '';
        $resultado = $ins->value($value);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado->value);
        $this->assertEquals(false,$resultado->value_es_null);

        errores::$error = false;

        $value = '""';
        $resultado = $ins->value($value);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('\"\"',$resultado->value);
        $this->assertEquals(false,$resultado->value_es_null);
        errores::$error = false;


    }



}
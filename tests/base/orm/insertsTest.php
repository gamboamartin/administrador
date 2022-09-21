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

    public function test_campos_alta_sql(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);

        $campo = 'a';
        $campos = 'a';
        $resultado = $ins->campos_alta_sql($campo, $campos);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a,a',$resultado);
        errores::$error = false;
    }

    public function test_data_para_log(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);


        $tabla = 'adm_accion';


        $resultado = $ins->data_para_log($this->link, $tabla);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT count(usuario_alta_id) FROM adm_accion',$resultado->alta_valido->queryString);
        errores::$error = false;
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

    public function test_sql_alta_full(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);


        $registro = array();
        $registro['a'] = '';

        $resultado = $ins->sql_alta_full($registro);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("''",$resultado->valores);

        errores::$error = false;


        $registro = array();
        $registro['a'] = null;

        $resultado = $ins->sql_alta_full($registro);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("NULL",$resultado->valores);

        errores::$error = false;


        $registro = array();
        $registro['a'] = null;
        $registro['B'] = "''";

        $resultado = $ins->sql_alta_full($registro);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("NULL,'\'\''",$resultado->valores);

        errores::$error = false;
    }

    public function test_sql_base_alta(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);

        $campo = 'a';
        $campos = '';
        $valores = '';
        $value = '';

        $resultado = $ins->sql_base_alta($campo, $campos, $valores, $value);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
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
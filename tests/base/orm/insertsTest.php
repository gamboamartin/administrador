<?php
namespace tests\base\orm;

use base\orm\inserts;
use gamboamartin\encripta\encriptador;
use gamboamartin\errores\errores;

use base\orm\inicializacion;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_accion;
use models\adm_seccion;

use stdClass;



class insertsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_asigna_data_user_transaccion(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);

        $_SESSION = array();
        $_SESSION['usuario_id'] = 1;
        $resultado = $ins->asigna_data_user_transaccion();
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(',usuario_alta_id,usuario_update_id',$resultado['campos']);
        $this->assertEquals(',1,1',$resultado['valores']);
        errores::$error = false;
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

    public function test_data_log(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);

        $_SESSION = array();
        $_SESSION['usuario_id'] = 1;
        $alta_valido = true;
        $campos = 'a';
        $update_valido = true;
        $valores = 'b';
        $resultado = $ins->data_log($alta_valido, $campos, $update_valido, $valores);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a,usuario_alta_id,usuario_update_id',$resultado->campos);
        $this->assertEquals('b,1,1',$resultado->valores);
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

    public function test_genera_data_log(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);

        $tabla = 'adm_seccion';
        $registro = array();
        $registro['a'] = '';
        $_SESSION['usuario_id'] = 1;
        $resultado = $ins->genera_data_log($this->link, $registro, $tabla);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("'',1,1",$resultado->valores);
        errores::$error = false;
    }

    public function test_inserta_sql(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);


        $data_log = new stdClass();
        $modelo = new adm_accion($this->link);

        $modelo->elimina_bd(9999);

        $data_log->campos = 'id, adm_seccion_id';
        $data_log->valores = "9999, 1";
        $resultado = $ins->inserta_sql($data_log, $modelo);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('INSERT INTO adm_accion (id, adm_seccion_id) VALUES (9999, 1)',$resultado->sql);
        $this->assertEquals(9999,$resultado->registro_id);
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

    public function test_sql_alta(){
        errores::$error = false;
        $ins = new inserts();
        $ins = new liberator($ins);

        $tabla = 'a';
        $campos = 'a';
        $valores = 'c';

       // $_SESSION['usuario_id'] = 1;
        $resultado = $ins->sql_alta($campos, $tabla, $valores);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('INSERT INTO a (a) VALUES (c)',$resultado);
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
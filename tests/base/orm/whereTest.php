<?php
namespace tests\base\orm;

use base\orm\where;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;
use function PHPUnit\Framework\assertNotTrue;


class whereTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }



    public function test_data_filtros_full(){
        errores::$error = false;
        $wh = new where();
        //$wh = new liberator($wh);

        $keys_data_filter = array();
        $columnas_extra = array();
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_fecha = array();
        $filtro_rango = array();
        $not_in = array();
        $sql_extra = 'x';
        $tipo_filtro = '';
        $in = array();
        $resultado = $wh->data_filtros_full($columnas_extra, array(), $filtro, $filtro_especial, $filtro_extra, $filtro_fecha,
            $filtro_rango, $in, $keys_data_filter, $not_in, $sql_extra, $tipo_filtro);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

        $keys_data_filter = array();
        $columnas_extra = array();
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_fecha = array(array('campo_1'=>'a','campo_2'=>'b','fecha'=>'2020-01-01'));
        $filtro_rango = array();
        $not_in = array('llave'=>'a','values'=>array('a','c'));
        $sql_extra = 'x';
        $tipo_filtro = '';
        $in = array('llave'=>'a','values'=>array('a','c'));
        $resultado = $wh->data_filtros_full($columnas_extra, array(), $filtro, $filtro_especial, $filtro_extra, $filtro_fecha,
            $filtro_rango, $in, $keys_data_filter, $not_in, $sql_extra, $tipo_filtro);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->sentencia);
        $this->assertEquals("a IN ('a' ,'c')", $resultado->in);
        $this->assertEquals("a NOT IN ('a' ,'c')", $resultado->not_in);



        $keys_data_filter = array();
        $columnas_extra = array();
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_fecha = array(array('campo_1'=>'a','campo_2'=>'b','fecha'=>'2020-01-01'));
        $filtro_rango = array();
        $not_in = array('llave'=>'a','values'=>array('a','c'));
        $sql_extra = 'x';
        $tipo_filtro = '';
        $in = array('llave'=>'a','values'=>array('a','c'));
        $diferente_de['a'] = '';
        $diferente_de['b'] = '';
        $resultado = $wh->data_filtros_full($columnas_extra,$diferente_de, $filtro, $filtro_especial, $filtro_extra, $filtro_fecha,
            $filtro_rango, $in, $keys_data_filter, $not_in, $sql_extra, $tipo_filtro);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->sentencia);
        $this->assertEquals("a IN ('a' ,'c')", $resultado->in);
        $this->assertEquals("a NOT IN ('a' ,'c')", $resultado->not_in);
        $this->assertEquals("  a <> ''   AND  b <> '' ", $resultado->diferente_de);


        errores::$error = false;
    }




    public function test_diferente_de(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $campo = '';
        $diferente_de_sql = '';
        $value = '';
        $resultado = $wh->diferente_de($campo, $diferente_de_sql, $value);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $diferente_de_sql = '';
        $value = '';
        $resultado = $wh->diferente_de($campo, $diferente_de_sql, $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("  a <> '' ", $resultado);

        errores::$error = false;

        $campo = 'a';
        $diferente_de_sql = 'z';
        $value = '';
        $resultado = $wh->diferente_de($campo, $diferente_de_sql, $value);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("  AND  a <> '' ", $resultado);

        errores::$error = false;
    }

    public function test_diferente_de_sql(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $diferente_de = array();
        $resultado = $wh->diferente_de_sql($diferente_de);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);

        errores::$error = false;

        $diferente_de = array();
        $diferente_de['a'] = '';
        $resultado = $wh->diferente_de_sql($diferente_de);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("  a <> '' ", $resultado);

        errores::$error = false;

        $diferente_de = array();
        $diferente_de['a'] = 'x';
        $diferente_de['b'] = 'x';
        $resultado = $wh->diferente_de_sql($diferente_de);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("  a <> 'x'   AND  b <> 'x' ", $resultado);


        errores::$error = false;
    }



    public function test_filtro_especial_sql(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $filtro_especial = array();
        $filtro_especial[] = '';
        $resultado = $wh->filtro_especial_sql(array(),$filtro_especial);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error filtro debe ser un array filtro_especial[] = array()', $resultado['mensaje']);

        errores::$error = false;

        $filtro_especial = array();
        $filtro_especial[]['campo'] = array();
        $resultado = $wh->filtro_especial_sql(array(),$filtro_especial);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error filtro', $resultado['mensaje']);

        errores::$error = false;

        $filtro_especial = array();
        $filtro_especial[0]['x']['operador'] = 'x';
        $filtro_especial[0]['x']['valor'] = 'x';
        $resultado = $wh->filtro_especial_sql(array(),$filtro_especial);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }


    public function test_filtros_full(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $keys_data_filter = array();
        $filtros = new stdClass();
        $resultado = $wh->filtros_full($filtros, $keys_data_filter);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_filtros_vacios(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $keys_data_filter = array();
        $complemento = new stdClass();
        $resultado = $wh->filtros_vacios($complemento, $keys_data_filter);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

        $keys_data_filter = array();
        $complemento = new stdClass();
        $keys_data_filter[] = 'a';
        $resultado = $wh->filtros_vacios($complemento, $keys_data_filter);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;


        $keys_data_filter = array();
        $complemento = new stdClass();
        $keys_data_filter[] = 'a';
        $complemento->a = '';
        $resultado = $wh->filtros_vacios($complemento, $keys_data_filter);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

        $keys_data_filter = array();
        $complemento = new stdClass();
        $keys_data_filter[] = 'a';
        $complemento->a = 'x';
        $resultado = $wh->filtros_vacios($complemento, $keys_data_filter);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;


    }








    public function test_genera_filtros_iniciales(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $filtro_extra_sql = '';
        $filtro_especial_sql = '';
        $filtro_rango_sql = '';
        $keys_data_filter = array();
        $not_in_sql = '';
        $sentencia = 'z';
        $sql_extra = '';
        $in_sql = '';
        $resultado = $wh->genera_filtros_iniciales('',$filtro_especial_sql, $filtro_extra_sql, $filtro_rango_sql, $in_sql,
            $keys_data_filter, $not_in_sql, $sentencia, $sql_extra);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $filtro_extra_sql = '';
        $filtro_especial_sql = '';
        $filtro_rango_sql = '';
        $keys_data_filter = array();
        $not_in_sql = 'd';
        $sentencia = 'z';
        $sql_extra = '';
        $in_sql = 'a';
        $resultado = $wh->genera_filtros_iniciales('',$filtro_especial_sql, $filtro_extra_sql, $filtro_rango_sql, $in_sql,
            $keys_data_filter, $not_in_sql, $sentencia, $sql_extra);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado->in);
        $this->assertEquals('d',$resultado->not_in);
        $this->assertEquals('z',$resultado->sentencia);

        errores::$error = false;

        $filtro_extra_sql = '';
        $filtro_especial_sql = '';
        $filtro_rango_sql = '';
        $keys_data_filter = array();
        $not_in_sql = 'd';
        $sentencia = 'z';
        $sql_extra = '';
        $in_sql = 'a';
        $diferente_sql = 'zzzz';
        $resultado = $wh->genera_filtros_iniciales($diferente_sql,$filtro_especial_sql, $filtro_extra_sql,
            $filtro_rango_sql, $in_sql, $keys_data_filter, $not_in_sql, $sentencia, $sql_extra);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado->in);
        $this->assertEquals('d',$resultado->not_in);
        $this->assertEquals('z',$resultado->sentencia);
        $this->assertEquals('zzzz',$resultado->diferente_de);



        errores::$error = false;
    }

    public function test_genera_filtros_sql(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $columnas_extra = array();
        $filtro = array();
        $filtro_especial = array();
        $keys_data_filter = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $sql_extra = 'xx';
        $not_in = array();
        $tipo_filtro = '';
        $in = array();
        $resultado = $wh->genera_filtros_sql($columnas_extra, array(), $filtro, $filtro_especial, $filtro_extra, $filtro_rango, $in,
            $keys_data_filter, $not_in, $sql_extra, $tipo_filtro);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $columnas_extra = array();
        $filtro = array();
        $filtro_especial = array();
        $keys_data_filter = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $sql_extra = 'xx';
        $not_in = array();
        $tipo_filtro = '';
        $in = array('llave'=>'a','values'=>array('a','f'));
        $resultado = $wh->genera_filtros_sql($columnas_extra, array(), $filtro, $filtro_especial, $filtro_extra, $filtro_rango, $in,
            $keys_data_filter, $not_in, $sql_extra, $tipo_filtro);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a IN ('a' ,'f')",$resultado->in);
        $this->assertEquals('xx',$resultado->sql_extra);

        errores::$error = false;

        $columnas_extra = array();
        $filtro = array();
        $filtro_especial = array();
        $keys_data_filter = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $sql_extra = 'xx';
        $not_in = array();
        $tipo_filtro = '';
        $in = array('llave'=>'a','values'=>array('a','f'));
        $diferente_de['a'] = 'x';
        $diferente_de['b'] = 's';
        $resultado = $wh->genera_filtros_sql($columnas_extra, $diferente_de, $filtro, $filtro_especial, $filtro_extra, $filtro_rango, $in,
            $keys_data_filter, $not_in, $sql_extra, $tipo_filtro);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a IN ('a' ,'f')",$resultado->in);
        $this->assertEquals('xx',$resultado->sql_extra);
        $this->assertEquals("  a <> 'x'   AND  b <> 's' ",$resultado->diferente_de);


        errores::$error = false;
    }

    public function test_genera_in(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $in = array();
        $in['llave'] = 'a';
        $in['values'] = array('z');
        $resultado = $wh->genera_in($in);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a IN ('z')",$resultado);
        errores::$error = false;
    }

    public function test_genera_in_sql(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $in = array();
        $in['llave'] = 'a';
        $in['values'] = array('z');
        $resultado = $wh->genera_in_sql($in);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a IN ('z')",$resultado);
        errores::$error = false;
    }

    public function test_genera_in_sql_normalizado(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $in = array();
        $resultado = $wh->genera_in_sql_normalizado($in);
        //print_r($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("",$resultado);
        errores::$error = false;

        $in = array();
        $in['llave'] = 'a';
        $in['values'] = array('1',2);
        $resultado = $wh->genera_in_sql_normalizado($in);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a IN ('1' ,'2')",$resultado);
        errores::$error = false;

    }






    public function test_in_sql(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $values = array();
        $values[] = '1';
        $values[] = '2';
        $llave = 'a';
        $resultado = $wh->in_sql($llave, $values);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a IN ('1' ,'2')",$resultado);
        errores::$error = false;

    }

    public function test_init_params_sql(){
        errores::$error = false;
        $wh = new where();
       // $wh = new liberator($wh);

        $keys_data_filter = array();
        $complemento = new stdClass();
        $resultado = $wh->init_params_sql($complemento, $keys_data_filter);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }



    public function test_limpia_filtros(){
        errores::$error = false;
        $wh = new where();
        //$wh = new liberator($wh);

        $filtros = new stdClass();
        $keys_data_filter = array();
        $resultado = $wh->limpia_filtros($filtros, $keys_data_filter);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->isEmpty($resultado);

        errores::$error = false;

        $filtros = new stdClass();
        $keys_data_filter = array();
        $keys_data_filter[] = '';
        $resultado = $wh->limpia_filtros($filtros, $keys_data_filter);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase( "Error el key esta vacio", $resultado['mensaje']);

        errores::$error = false;

        $filtros = new stdClass();
        $keys_data_filter = array();
        $keys_data_filter['z'] = 'd';
        $resultado = $wh->limpia_filtros($filtros, $keys_data_filter);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals( '', $resultado->d);
        errores::$error = false;


        $filtros = new stdClass();
        $filtros->d = ' x ';
        $keys_data_filter = array();
        $keys_data_filter['z'] = 'd';
        $resultado = $wh->limpia_filtros($filtros, $keys_data_filter);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals( 'x', $resultado->d);

        errores::$error = false;


        $filtros = new stdClass();
        $filtros->d = ' x ';
        $filtros->diferente_de = ' sss ';
        $keys_data_filter = array();
        $keys_data_filter['z'] = 'd';
        $keys_data_filter['xxx'] = 'diferente_de';
        $resultado = $wh->limpia_filtros($filtros, $keys_data_filter);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals( 'x', $resultado->d);
        $this->assertEquals( 'sss', $resultado->diferente_de);


        errores::$error = false;



    }

    public function test_maqueta_filtro_especial(): void
    {
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $campo = '';
        $filtro = array();
        $resultado = $wh->maqueta_filtro_especial($campo, array(), $filtro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase( "Error al validar filtro", $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $filtro = array();
        $filtro['a']['operador'] = 'b';
        $resultado = $wh->maqueta_filtro_especial($campo, array() ,$filtro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase( "Error al validar filtro", $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $filtro = array();
        $filtro['a']['operador'] = 'b';
        $filtro['a']['valor'] = 'b';
        $resultado = $wh->maqueta_filtro_especial($campo, array(), $filtro);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase( "a b 'b'", $resultado);

        errores::$error = false;
    }



    public function test_obten_filtro_especial(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $filtro_esp = array();
        $filtro_especial_sql = '';
        $resultado = $wh->obten_filtro_especial(array(),$filtro_esp, $filtro_especial_sql);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error en filtro', $resultado['mensaje']);

        errores::$error = false;

        $filtro_esp = array();
        $filtro_especial_sql = '';
        $filtro_esp['x']['operador'] = 'x';
        $filtro_esp['x']['valor'] = 'x';
        $resultado = $wh->obten_filtro_especial(array(),$filtro_esp, $filtro_especial_sql);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_parentesis_filtro(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);


        $filtros = new stdClass();
        $keys_data_filter = array();
        $keys_data_filter[] = 'a';
        $keys_data_filter[] = 'c';
        $filtros->b = 'z';
        $filtros->c = 'k';
        $resultado = $wh->parentesis_filtro($filtros, $keys_data_filter);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->a);
        $this->assertEquals('z', $resultado->b);
        $this->assertEquals(' (k) ', $resultado->c);
        errores::$error = false;
    }





    public function test_verifica_where(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $complemento = new stdClass();
        $key_data_filter = array();
        $resultado = $wh->verifica_where($complemento, $key_data_filter);

        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

        $complemento = new stdClass();
        $complemento->where = 'where';
        $key_data_filter = array();
        $key_data_filter[] = 'z';
        $complemento->z = 'x';
        $resultado = $wh->verifica_where($complemento, $key_data_filter);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }

    public function test_where(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $keys_data_filter = array();
        $filtros = new stdClass();
        $resultado = $wh->where($filtros, $keys_data_filter);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $keys_data_filter = array();
        $filtros = new stdClass();
        $filtros->z = 'a';
        $keys_data_filter[] = 'z';
        $resultado = $wh->where($filtros, $keys_data_filter);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' WHERE ', $resultado);

        errores::$error = false;
    }

    public function test_where_base(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $complemento = new stdClass();
        $resultado = $wh->where_base($complemento);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

        $complemento = new stdClass();
        $resultado = $wh->where_base($complemento);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado->where);
        errores::$error = false;

        $complemento = new stdClass();
        $complemento->where = '';
        $resultado = $wh->where_base($complemento);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado->where);
        errores::$error = false;

        $complemento = new stdClass();
        $complemento->where = 'where';
        $resultado = $wh->where_base($complemento);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('WHERE',$resultado->where);
        errores::$error = false;

    }

    public function test_where_filtro(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $complemento = new stdClass();
        $key_data_filter = array();
        $resultado = $wh->where_filtro($complemento, $key_data_filter);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

        $complemento = new stdClass();
        $key_data_filter = array();
        $complemento->where = 'where';
        $key_data_filter[] = 's';
        $complemento->s = 'test';
        $resultado = $wh->where_filtro($complemento, $key_data_filter);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' WHERE ',$resultado->where);
        $this->assertEquals('test',$resultado->s);
        errores::$error = false;

    }

    public function test_where_mayus(){
        errores::$error = false;
        $wh = new where();
        $wh = new liberator($wh);

        $complemento = new stdClass();
        $resultado = $wh->where_mayus(complemento: $complemento);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->isEmpty($resultado);
        $rs = (array)$resultado;
        $this->assertArrayHasKey("where", $rs , "No existe la key where");

        $complemento = new stdClass();
        $complemento->where = 'a';
        $resultado = $wh->where_mayus(complemento: $complemento);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error where mal aplicado', $resultado['mensaje']);

        errores::$error = false;

        $complemento = new stdClass();
        $resultado = $wh->where_mayus(complemento: $complemento);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado->where);


        errores::$error = false;

        $complemento = new stdClass();
        $complemento->where = 'where';
        $resultado = $wh->where_mayus(complemento: $complemento);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('WHERE', $resultado->where);
        errores::$error = false;

    }

    public function test_where_suma(){
        errores::$error = false;
        $wh = new where();

        $filtro_sql = 'a = 2';
        $resultado = $wh->where_suma(filtro_sql: $filtro_sql);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
        $this->assertEquals(expected: ' WHERE a = 2', actual: $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }
}
<?php
namespace tests\base\orm;

use base\orm\filtros;

use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class filtrosTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_complemento(){
        errores::$error = false;
        $filtros = new filtros();
        $filtros = new liberator($filtros);

        $modelo = new adm_seccion($this->link);

        $complemento = new stdClass();
        $resultado = $filtros->complemento($complemento, $modelo);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_complemento_sql(){
        errores::$error = false;
        $filtros = new filtros();
        //$inicializacion = new liberator($inicializacion);

        $modelo = new adm_seccion($this->link);
        $aplica_seguridad = false;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array();
        $offset = 0;
        $order = array();
        $sql_extra = '';
        $tipo_filtro = '';
        $in = array();
        $resultado = $filtros->complemento_sql($aplica_seguridad, array(), $filtro, $filtro_especial, $filtro_extra,
            $filtro_rango, $group_by, $in, $limit, $modelo, $not_in, $offset, $order, $sql_extra, $tipo_filtro);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);
        $aplica_seguridad = false;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array();
        $offset = 0;
        $order = array();
        $sql_extra = '';
        $tipo_filtro = '';
        $in = array();
        $in['llave'] = 'a';
        $in['values'] = array('a','b');
        $resultado = $filtros->complemento_sql(aplica_seguridad: $aplica_seguridad, diferente_de: array(),filtro:  $filtro,
            filtro_especial:  $filtro_especial,filtro_extra:  $filtro_extra, filtro_rango: $filtro_rango,
            group_by:  $group_by,in:  $in, limit: $limit, modelo: $modelo, not_in: $not_in, offset: $offset,
            order:  $order,sql_extra:  $sql_extra,tipo_filtro:  $tipo_filtro);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("  ( (a  IN (a ,b)))",$resultado->in);
        $this->assertEquals("",$resultado->sentencia);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);
        $aplica_seguridad = false;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array();
        $offset = 0;
        $order = array();
        $sql_extra = '';
        $tipo_filtro = '';
        $in = array();
        $in['llave'] = 'a';
        $in['values'] = array('a','b');
        $diferente_de['zzz'] = 'z';
        $diferente_de['ppp'] = 'z';
        $resultado = $filtros->complemento_sql(aplica_seguridad: $aplica_seguridad, diferente_de: $diferente_de,
            filtro:  $filtro,
            filtro_especial:  $filtro_especial,filtro_extra:  $filtro_extra, filtro_rango: $filtro_rango,
            group_by:  $group_by,in:  $in, limit: $limit, modelo: $modelo, not_in: $not_in, offset: $offset,
            order:  $order,sql_extra:  $sql_extra,tipo_filtro:  $tipo_filtro);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("  ( (a  IN (a ,b)))",$resultado->in);
        $this->assertEquals("",$resultado->sentencia);
        $this->assertEquals("  AND  ( (zzz <> 'z'   AND  ppp <> 'z'))",$resultado->diferente_de);


        errores::$error = false;


    }

    public function test_consulta_full_and(){
        errores::$error = false;
        $filtros = new filtros();
        //$inicializacion = new liberator($inicializacion);

        $modelo = new adm_seccion($this->link);

        $complemento = new stdClass();
        $consulta = '';
        $resultado = $filtros->consulta_full_and($complemento, $consulta, $modelo);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error $consulta no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);

        $complemento = new stdClass();
        $consulta = 'a';

        $resultado = $filtros->consulta_full_and($complemento, $consulta, $modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('a              ',$resultado);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);

        $complemento = new stdClass();
        $consulta = 'a';
        $complemento->sql_extra = 'b';

        $resultado = $filtros->consulta_full_and($complemento, $consulta, $modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('a          b    ',$resultado);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);

        $complemento = new stdClass();
        $consulta = 'a';
        $complemento->sql_extra = 'b';
        $complemento->filtro_fecha = 'c';

        $resultado = $filtros->consulta_full_and($complemento, $consulta, $modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('a     c     b    ',$resultado);


        errores::$error = false;

        $modelo = new adm_seccion($this->link);

        $complemento = new stdClass();
        $consulta = 'a';
        $complemento->sql_extra = 'b';
        $complemento->filtro_fecha = 'c';
        $complemento->diferente_de = 'g';

        $resultado = $filtros->consulta_full_and($complemento, $consulta, $modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('a     c    g b    ',$resultado);


        errores::$error = false;
    }

    public function test_inicializa_complemento(){
        errores::$error = false;
        $filtros = new filtros();
        $filtros = new liberator($filtros);

        $complemento = new stdClass();
        $resultado = $filtros->inicializa_complemento($complemento);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('',$resultado->filtro_especial);
        $this->assertEquals('',$resultado->filtro_extra);

        errores::$error = false;
    }

    public function test_init_complemento(){
        errores::$error = false;
        $filtros = new filtros();
        $filtros = new liberator($filtros);

        $complemento = new stdClass();
        $keys = array();
        $resultado = $filtros->init_complemento($complemento, $keys);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error los keys de un complemento esta vacio',$resultado['mensaje']);

        errores::$error = false;
        $complemento = new stdClass();
        $keys = array('');
        $resultado = $filtros->init_complemento($complemento, $keys);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error el key esta vacio',$resultado['mensaje']);

        errores::$error = false;
        $complemento = new stdClass();
        $keys = array('a');
        $resultado = $filtros->init_complemento($complemento, $keys);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('',$resultado->a);
        errores::$error = false;
    }

    public function test_keys_complemento(){
        errores::$error = false;
        $filtros = new filtros();
        $filtros = new liberator($filtros);

        $resultado = $filtros->keys_complemento();
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('filtro_especial',$resultado[0]);
        $this->assertEquals('filtro_extra',$resultado[1]);
        $this->assertEquals('filtro_fecha',$resultado[2]);
        $this->assertEquals('filtro_rango',$resultado[3]);
        $this->assertEquals('in',$resultado[4]);
        $this->assertEquals('not_in',$resultado[5]);
        $this->assertEquals('sentencia',$resultado[6]);
        $this->assertEquals('sql_extra',$resultado[7]);
        errores::$error = false;

    }

    public function test_sql(){
        errores::$error = false;
        $filtros = new filtros();
        $filtros = new liberator($filtros);


        $complemento = new stdClass();
        $consulta_previa = '';
        $resultado = $filtros->sql($complemento, $consulta_previa);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }



}
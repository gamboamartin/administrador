<?php
namespace tests\base\orm;

use base\orm\sql;
use gamboamartin\administrador\models\adm_session;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class sqlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_describe_table(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $tabla = '';
        $resultado = $sql->describe_table($tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar tabla',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'a';
        $resultado = $sql->describe_table($tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('DESCRIBE a',$resultado);
        errores::$error = false;
    }

    public function test_in(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $llave = '';
        $values_sql = '';
        $resultado = $sql->in($llave, $values_sql);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;

        $llave = 'a';
        $values_sql = '';
        $resultado = $sql->in($llave, $values_sql);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar in',$resultado['mensaje']);

        errores::$error = false;

        $llave = '';
        $values_sql = 'a';
        $resultado = $sql->in($llave, $values_sql);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar in',$resultado['mensaje']);

        errores::$error = false;

        $llave = 'a';
        $values_sql = 'a';
        $resultado = $sql->in($llave, $values_sql);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('a  IN (a)',$resultado);

        errores::$error = false;
    }

    public function test_show_tables(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $tabla = '';
        $resultado = $sql->show_tables($tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SHOW TABLES',$resultado);
    }

    public function test_sql_select(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $consulta_base = 'a';
        $params_base = new stdClass();
        $params_base->seguridad = 'z';
        $sql_extra = '';

        $resultado = $sql->sql_select($consulta_base, $params_base, $sql_extra);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a  z    ',$resultado);
        errores::$error = false;
    }

    public function test_sql_select_init(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $aplica_seguridad = false;
        $columnas = array();
        $columnas_en_bruto = false;
        $extension_estructura = array();
        $group_by = array();
        $limit = 1;
        $modelo = new adm_session($this->link);
        $offset =1 ;
        $order = array();
        $renombres = array();
        $sql_where_previo = '';
        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, $extension_estructura,
            $group_by, $limit, $modelo, $offset, $order, $renombres, $sql_where_previo);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('SELECT adm_session.id AS ',$resultado->consulta_base);
        $this->assertStringContainsStringIgnoringCase('AS adm_session_id, adm_session.name AS adm_session_name,',$resultado->consulta_base);
        $this->assertStringContainsStringIgnoringCase('name, adm_session.adm_usuario_id AS adm_session_adm_usuario_id, adm_session.numero_empresa AS a',$resultado->consulta_base);
        $this->assertStringContainsStringIgnoringCase('AS adm_session_numero_empresa, adm_session.fecha AS adm_session_fecha, adm_session.fecha_ultim',$resultado->consulta_base);
        $this->assertStringContainsStringIgnoringCase('ultima_ejecucion AS adm_session_fecha_ultima_ejecucion, adm_session.usuario_alta_id AS adm_s',$resultado->consulta_base);
        $this->assertStringContainsStringIgnoringCase('S adm_session_usuario_alta_id, adm_session.usuario_update_id AS adm_session_usuario_updat',$resultado->consulta_base);
        $this->assertStringContainsStringIgnoringCase('ario ON adm_usuario.id = adm_session.adm_usuario_id LEFT JOIN adm',$resultado->consulta_base);
        $this->assertStringContainsStringIgnoringCase('upo AS adm_grupo ON adm_grupo.id = adm_usuario.adm_grupo_id',$resultado->consulta_base);

        errores::$error = false;

        $aplica_seguridad = true;
        $columnas = array();
        $columnas_en_bruto = false;
        $extension_estructura = array();
        $group_by = array();
        $limit = 1;
        $modelo = new adm_session($this->link);
        $offset =1 ;
        $order = array();
        $renombres = array();
        $sql_where_previo = '';
        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, $extension_estructura,
            $group_by, $limit, $modelo, $offset, $order, $renombres, $sql_where_previo);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener parametros bas',$resultado['mensaje']);

        errores::$error = false;

        $aplica_seguridad = false;
        $columnas = array();
        $columnas[] = '';
        $columnas_en_bruto = false;
        $extension_estructura = array();
        $group_by = array();
        $limit = 1;
        $modelo = new adm_session($this->link);
        $offset =1 ;
        $order = array();
        $renombres = array();
        $sql_where_previo = '';


        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, $extension_estructura,
            $group_by, $limit, $modelo, $offset, $order, $renombres, $sql_where_previo);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar consulta',$resultado['mensaje']);

        errores::$error = false;

        $aplica_seguridad = false;
        $columnas = array();
        $columnas_en_bruto = true;
        $extension_estructura = array();
        $group_by = array();
        $limit = 1;
        $modelo = new adm_session($this->link);
        $offset =1 ;
        $order = array();
        $renombres = array();
        $sql_where_previo = '';


        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, $extension_estructura,
            $group_by, $limit, $modelo, $offset, $order, $renombres, $sql_where_previo);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('ELECT adm_session.id AS id, adm_session.name AS name',$resultado->consulta_base);
        $this->assertStringContainsStringIgnoringCase(' adm_session.fecha AS fecha, adm_session.fecha_ultima_ejecucion AS',$resultado->consulta_base);

        errores::$error = false;

        $aplica_seguridad = false;
        $columnas = array();
        $columnas_en_bruto = false;
        $extension_estructura = array();
        $extension_estructura[] = '';
        $group_by = array();
        $limit = 1;
        $modelo = new adm_session($this->link);
        $offset =1 ;
        $order = array();
        $renombres = array();
        $sql_where_previo = '';


        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, $extension_estructura,
            $group_by, $limit, $modelo, $offset, $order, $renombres, $sql_where_previo);


        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar consulta',$resultado['mensaje']);

        errores::$error = false;

        $aplica_seguridad = false;
        $columnas = array();
        $columnas_en_bruto = false;
        $extension_estructura = array();
        $extension_estructura['adm_grupo']['key'] = 'id';
        $extension_estructura['adm_grupo']['enlace'] = 'adm_seccion';
        $extension_estructura['adm_grupo']['key_enlace'] = 'id';
        $group_by = array();
        $limit = 1;
        $modelo = new adm_session($this->link);
        $offset =1 ;
        $order = array();
        $renombres = array();
        $sql_where_previo = '';


        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, $extension_estructura,
            $group_by, $limit, $modelo, $offset, $order, $renombres, $sql_where_previo);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('uario.adm_grupo_id LEFT JOIN  adm_grupo AS adm_grupo  ON adm_grupo.id = adm_seccion.id',$resultado->consulta_base);
        $this->assertStringContainsStringIgnoringCase('rupo ON adm_grupo.id = adm_usuario.adm',$resultado->consulta_base);


        errores::$error = false;

        $aplica_seguridad = false;
        $columnas = array();
        $columnas_en_bruto = false;
        $extension_estructura = array();
        $extension_estructura['adm_grupo']['key'] = 'id';
        $extension_estructura['adm_grupo']['enlace'] = 'adm_seccion';
        $extension_estructura['adm_grupo']['key_enlace'] = 'id';
        $group_by = array();
        $limit = 1;
        $modelo = new adm_session($this->link);
        $offset =1 ;
        $order = array();
        $renombres = array();
        $renombres['x']['nombre_original'] = 'adm_accion';
        $renombres['x']['enlace'] = 'adm_accion';
        $renombres['x']['key'] = 'adm_accion.id';
        $renombres['x']['key_enlace'] = 'id';
        $sql_where_previo = '';


        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, $extension_estructura,
            $group_by, $limit, $modelo, $offset, $order, $renombres, $sql_where_previo);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('_seccion.id LEFT JOIN  adm_accion AS x  ON x.adm_accion.id = adm_accion.id',$resultado->consulta_base);



        errores::$error = false;
    }

    public function test_update(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $campos_sql = 'a';
        $id = '1';
        $tabla = 'a';
        $resultado = $sql->update($campos_sql, $id, $tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('UPDATE a SET a  WHERE id = 1',$resultado);
        errores::$error = false;
    }

    public function test_valida_in(): void
    {
        errores::$error = false;
        $sql = new sql();
        $sql = new liberator($sql);

        $llave = '';
        $values_sql = '';
        $resultado = $sql->valida_in($llave, $values_sql);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

        $llave = 'a';
        $values_sql = '';
        $resultado = $sql->valida_in($llave, $values_sql);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error si llave tiene info values debe tener info',$resultado['mensaje']);

        errores::$error = false;

        $llave = 'a';
        $values_sql = 'b';
        $resultado = $sql->valida_in($llave, $values_sql);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

        $llave = '';
        $values_sql = 'b';
        $resultado = $sql->valida_in($llave, $values_sql);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error si values_sql tiene info llave debe tener info',$resultado['mensaje']);
        errores::$error = false;

    }



}
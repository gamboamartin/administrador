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

    public function test_add_column(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $campo = 'campo';
        $table = 'table';
        $tipo_dato = 'tipo_dato';
        $default = '';
        $longitud = '';
        $resultado = $sql->add_column(campo:$campo,table:  $table,tipo_dato:  $tipo_dato,default: $default,longitud: $longitud);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE table ADD campo TIPO_DATO   NOT NULL;',$resultado);

        errores::$error = false;

        $campo = 'campo';
        $table = 'table';
        $tipo_dato = 'tipo_dato';
        $default = 'a';
        $longitud = '';
        $resultado = $sql->add_column(campo:$campo,table:  $table,tipo_dato:  $tipo_dato,default: $default,longitud: $longitud);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("ALTER TABLE table ADD campo TIPO_DATO  DEFAULT 'a' NOT NULL;",$resultado);

        errores::$error = false;

        errores::$error = false;

        $campo = 'campo';
        $table = 'table';
        $tipo_dato = 'tipo_dato';
        $default = 'a';
        $longitud = '1';
        $resultado = $sql->add_column(campo:$campo,table:  $table,tipo_dato:  $tipo_dato,default: $default,longitud: $longitud);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("ALTER TABLE table ADD campo TIPO_DATO (1) DEFAULT 'a' NOT NULL;",$resultado);
        errores::$error = false;
    }

    public function test_data_index(): void
    {
        errores::$error = false;
        $sql = new sql();
        $sql = new liberator($sql);
        $columna = 'columna';
        $columnas_index = '';
        $index_name = '';
        $resultado = $sql->data_index($columna, $columnas_index, $index_name);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('columna',$resultado->index_name);
        $this->assertEquals('columna',$resultado->index_name);

        errores::$error = false;

        $columna = 'columna';
        $columnas_index = 'a';
        $index_name = 'b';
        $resultado = $sql->data_index($columna, $columnas_index, $index_name);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('b_columna',$resultado->index_name);
        $this->assertEquals('a,columna',$resultado->columnas_index);
        errores::$error = false;


    }

    public function test_data_index_unique(): void
    {
        errores::$error = false;
        $sql = new sql();
        $sql = new liberator($sql);

        $table = 'v';
        $columnas = array();
        $columnas[] = 'a';
        $resultado = $sql->data_index_unique($columnas, $table);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('v_unique_a',$resultado->index_name);
        $this->assertEquals('a',$resultado->columnas_index);
        errores::$error = false;

        $table = 'v';
        $columnas = array();
        $columnas[] = 'a';
        $resultado = $sql->data_index_unique($columnas, $table,'z');
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('z',$resultado->index_name);
        $this->assertEquals('a',$resultado->columnas_index);
        errores::$error = false;


    }

    public function test_default(): void
    {
        errores::$error = false;
        $sql = new sql();
        $sql = new liberator($sql);

        $resultado = $sql->default('');
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;

        $resultado = $sql->default('a');
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("DEFAULT 'a'",$resultado);
        errores::$error = false;


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

    public function test_drop_column(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $campo = 'a';
        $table = 'v';
        $resultado = $sql->drop_column($campo, $table);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE v DROP COLUMN a;',$resultado);
        errores::$error = false;
    }

    public function test_foreign_key(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $relacion_table = 'relacion_table';
        $table = 'table';
        $resultado = $sql->foreign_key(table: $table,relacion_table:  $relacion_table);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE table ADD CONSTRAINT table__relacion_table_id FOREIGN KEY (relacion_table_id) REFERENCES relacion_table(id);',$resultado);
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
        $this->assertStringContainsStringIgnoringCase('a IN (a)',$resultado);

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
        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, true, $extension_estructura,
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
        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, true, $extension_estructura,
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


        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, true, $extension_estructura,
            $group_by, $limit, $modelo, $offset, $order, $renombres, $sql_where_previo);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('SELECT adm_session.id as adm_session_id FROM adm_session AS adm_session LEFT JOIN adm_usuario AS adm_usuario ON adm_usuario.id = adm_session.adm_usuario_id LEFT JOIN adm_grupo AS adm_grupo ON adm_grupo.id = adm_usuario.adm_grupo_id',$resultado->consulta_base);

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


        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, true, $extension_estructura,
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


        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, true, $extension_estructura,
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


        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, true, $extension_estructura,
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


        $resultado = $sql->sql_select_init($aplica_seguridad, $columnas, $columnas_en_bruto, true, $extension_estructura,
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
    public function test_valida_column(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $table = 'table';
        $tipo_dato = 'relacion_table';
        $campo = 'campo';
        $resultado = $sql->valida_column(campo: $campo, table: $table, tipo_dato: $tipo_dato);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }

    public function test_valida_column_base(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $campo = '';
        $table = '';
        $resultado = $sql->valida_column_base(campo: $campo, table: $table);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error campo esta vacio',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = '-1';
        $table = '';
        $resultado = $sql->valida_column_base(campo: $campo, table: $table);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error campo debe ser un texto',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = 'x';
        $table = '';
        $resultado = $sql->valida_column_base(campo: $campo, table: $table);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error table esta vacia',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = 'x';
        $table = '4';
        $resultado = $sql->valida_column_base(campo: $campo, table: $table);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error table debe ser un texto',$resultado['mensaje_limpio']);

        errores::$error = false;

        $campo = 'x';
        $table = 's';
        $resultado = $sql->valida_column_base(campo: $campo, table: $table);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

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
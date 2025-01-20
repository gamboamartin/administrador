<?php
namespace gamboamartin\administrador\tests\base\orm;


use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_dia;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_estado_civil;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\tests\base_test;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class modeloTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }


    public function test_activa(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);


        $modelo->registro_id = 1;
        $resultado = $modelo->activa_bd();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Registro activado con éxito en adm_seccion', $resultado->mensaje);

        errores::$error = false;
    }

    public function test_alta_bd(): void
    {
        errores::$error = false;
        $modelo = new adm_mes($this->link);
        //$modelo = new liberator($modelo);
        $_SESSION['usuario_id'] = 1;
        //$modelo->registro_id = 1;
        $modelo->registro['codigo'] = mt_rand(0,999999999999999);
        $modelo->registro['descripcion'] = mt_rand(0,999999999999999);
        $resultado = $modelo->alta_bd();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_alta_existente(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $filtro = array();
        $filtro['adm_seccion.id'] = '1';
        $resultado = $modelo->alta_existente($filtro);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado->registro_id);
        errores::$error = false;
    }

    public function test_alta_predeterminado(): void
    {
        $_SESSION['usuario_id'] = 2;
        errores::$error = false;
        $modelo = new adm_mes($this->link);
        $del = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $modelo = new liberator($modelo);


        $resultado = $modelo->alta_predeterminado();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Registro insertado con éxito', $resultado->mensaje);
        errores::$error = false;

        $resultado = $modelo->alta_predeterminado();
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al insertar prederminado', $resultado['mensaje']);
        errores::$error = false;

    }

    public function test_alta_registro(): void
    {
        errores::$error = false;
        $modelo = new adm_dia($this->link);
        //$modelo = new liberator($modelo);
        $_SESSION['usuario_id'] = 2;

        $registro = array();
        $registro['codigo'] = '1';
        $registro['descripcion'] = '1';
        $resultado = $modelo->alta_registro($registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al dar de alta registro', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $registro['codigo'] = mt_rand(100000000000,999999999999);
        $registro['descripcion'] = '1';
        $resultado = $modelo->alta_registro($registro);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('INSERT INTO adm_dia (codigo,descripcion,codigo_bis,descripcion_select,alias,status,usuario_alta_id,usuario_update_id) VALUES ', $resultado->sql);

        errores::$error = false;
    }

    public function test_cuenta(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $resultado = $modelo->cuenta();
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
        $filtro = array();
        $filtro['adm_seccion.id'] = 'adm_seccion.id';
        $resultado = $modelo->cuenta(filtro: $filtro);
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(0, $resultado);

        errores::$error = false;

        $extra_join = array();
        $extra_join['adm_accion']['key'] = 'adm_seccion_id';
        $extra_join['adm_accion']['enlace'] = 'adm_seccion';
        $extra_join['adm_accion']['key_enlace'] = 'id';
        $resultado = $modelo->cuenta(extra_join: $extra_join);
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric( $resultado);

        errores::$error = false;
    }

    public function test_cuenta_bis(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);


        $resultado = $modelo->cuenta_bis();

        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado);

        errores::$error = false;
    }

    public function test_data_result_transaccion(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $mensaje = '';
        $registro = array();
        $registro_ejecutado = array();
        $registro_id = -1;
        $sql = '';
        $resultado = $modelo->data_result_transaccion($mensaje, $registro, $registro_ejecutado, $registro_id, array(),
            new \stdClass(), $sql);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

    }

    public function test_data_sentencia(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);


        $campo = '';
        $value = '';
        $sentencia = '';
        $where = '';
        $resultado = $modelo->data_sentencia($campo, $sentencia, $value, $where);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $value = '';
        $sentencia = '';
        $where = '';
        $resultado = $modelo->data_sentencia($campo, $sentencia, $value, $where);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_elimina_bd(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_dia($this->link);
        //$modelo = new liberator($modelo);

        $del = (new adm_dia($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $id = 1;
        $resultado = $modelo->elimina_bd($id);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar transaccion activa en adm_dia', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $alta = (new adm_dia($this->link))->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }
        $id = 1;
        $resultado = $modelo->elimina_bd($id);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->registro_id);

        errores::$error = false;
    }

    public function test_elimina_con_filtro_and(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_dia($this->link);
        //$modelo = new liberator($modelo);

        $del = (new adm_dia($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $filtro = array();
        $filtro['adm_dia.id'] = 1;
        $resultado = $modelo->elimina_con_filtro_and(filtro: $filtro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_existe(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $filtro = array();
        $resultado = $modelo->existe($filtro);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }

    public function test_existe_predeterminado(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $resultado = $modelo->existe_predeterminado();
        //var_dump($resultado);exit;
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_filtro_and(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $resultado = $modelo->filtro_and();
        //print_r($resultado);exit;
        $this->assertIsObject( $resultado);
        $this->assertEquals('1',$resultado->registros[0]['adm_seccion_id']);

        errores::$error = false;

    }

    public function test_filtro_or(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $resultado = $modelo->filtro_or();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_genera_sql_filtro(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $columnas = array();
        $columnas_by_table = array();
        $columnas_en_bruto = false;
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
        $extra_join = array();
        $resultado = $modelo->genera_sql_filtro(columnas: $columnas, columnas_by_table: $columnas_by_table,
            columnas_en_bruto: $columnas_en_bruto, con_sq: true, diferente_de: array(), extra_join: $extra_join,
            filtro: $filtro, filtro_especial: $filtro_especial, filtro_extra: $filtro_extra,
            filtro_rango: $filtro_rango, group_by: $group_by, in: $in, limit: $limit, not_in: $not_in, offset: $offset, order: $order,
            sql_extra: $sql_extra, tipo_filtro: $tipo_filtro);

        //print_r($resultado);exit;

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);

        //$this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id, adm_seccion.codigo AS adm_seccion_codigo, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.status AS adm_seccion_status, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.descripcion_select AS adm_seccion_descripcion_select, adm_seccion.alias AS adm_seccion_alias, adm_seccion.codigo_bis AS adm_seccion_codigo_bis, adm_seccion.predeterminado AS adm_seccion_predeterminado, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.adm_namespace_id AS adm_seccion_adm_namespace_id, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.icono AS adm_seccion_icono, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id, adm_menu.codigo AS adm_menu_codigo, adm_menu.codigo_bis AS adm_menu_codigo_bis, adm_menu.descripcion_select AS adm_menu_descripcion_select, adm_menu.alias AS adm_menu_alias, adm_menu.titulo AS adm_menu_titulo, adm_menu.predeterminado AS adm_menu_predeterminado, adm_namespace.id AS adm_namespace_id, adm_namespace.codigo AS adm_namespace_codigo, adm_namespace.descripcion AS adm_namespace_descripcion, adm_namespace.status AS adm_namespace_status, adm_namespace.usuario_alta_id AS adm_namespace_usuario_alta_id, adm_namespace.usuario_update_id AS adm_namespace_usuario_update_id, adm_namespace.fecha_alta AS adm_namespace_fecha_alta, adm_namespace.fecha_update AS adm_namespace_fecha_update, adm_namespace.descripcion_select AS adm_namespace_descripcion_select, adm_namespace.alias AS adm_namespace_alias, adm_namespace.codigo_bis AS adm_namespace_codigo_bis, adm_namespace.predeterminado AS adm_namespace_predeterminado, adm_namespace.name AS adm_namespace_name,(SELECT COUNT(*) FROM adm_accion WHERE adm_accion.adm_seccion_id = adm_seccion.id) AS adm_seccion_n_acciones FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id',$resultado);
        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.status AS adm_seccion_status, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.icono AS adm_seccion_icono, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.codigo AS adm_seccion_codigo, adm_seccion.codigo_bis AS adm_seccion_codigo_bis, adm_seccion.descripcion_select AS adm_seccion_descripcion_select, adm_seccion.alias AS adm_seccion_alias, adm_seccion.adm_namespace_id AS adm_seccion_adm_namespace_id, adm_seccion.predeterminado AS adm_seccion_predeterminado, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id, adm_menu.codigo AS adm_menu_codigo, adm_menu.codigo_bis AS adm_menu_codigo_bis, adm_menu.descripcion_select AS adm_menu_descripcion_select, adm_menu.alias AS adm_menu_alias, adm_menu.titulo AS adm_menu_titulo, adm_menu.predeterminado AS adm_menu_predeterminado, adm_namespace.id AS adm_namespace_id, adm_namespace.descripcion AS adm_namespace_descripcion, adm_namespace.status AS adm_namespace_status, adm_namespace.usuario_update_id AS adm_namespace_usuario_update_id, adm_namespace.fecha_alta AS adm_namespace_fecha_alta, adm_namespace.fecha_update AS adm_namespace_fecha_update, adm_namespace.usuario_alta_id AS adm_namespace_usuario_alta_id, adm_namespace.codigo AS adm_namespace_codigo, adm_namespace.codigo_bis AS adm_namespace_codigo_bis, adm_namespace.descripcion_select AS adm_namespace_descripcion_select, adm_namespace.alias AS adm_namespace_alias, adm_namespace.predeterminado AS adm_namespace_predeterminado, adm_namespace.name AS adm_namespace_name,(SELECT COUNT(*) FROM adm_accion WHERE adm_accion.adm_seccion_id = adm_seccion.id) AS adm_seccion_n_acciones FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id',$resultado);


        errores::$error = false;


        $columnas = array();
        $columnas_by_table = array();
        $columnas_en_bruto = false;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array();
        $offset = 0;
        $order = array();
        $sql_extra = 'x';
        $tipo_filtro = '';
        $in = array();
        $extra_join = array();
        $resultado = $modelo->genera_sql_filtro(columnas: $columnas,columnas_by_table:  $columnas_by_table,
            columnas_en_bruto:  $columnas_en_bruto,con_sq:  true, diferente_de: array(), extra_join: $extra_join,filtro:  $filtro,
            filtro_especial: $filtro_especial, filtro_extra: $filtro_extra,filtro_rango:  $filtro_rango,
            group_by: $group_by,in:  $in, limit: $limit, not_in: $not_in,offset:  $offset,order:  $order, sql_extra: $sql_extra,
            tipo_filtro: $tipo_filtro);

        //print_r($resultado);exit;

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        //$this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.status AS adm_seccion_status, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.icono AS adm_seccion_icono, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.codigo AS adm_seccion_codigo, adm_seccion.codigo_bis AS adm_seccion_codigo_bis, adm_seccion.descripcion_select AS adm_seccion_descripcion_select, adm_seccion.alias AS adm_seccion_alias, adm_seccion.adm_namespace_id AS adm_seccion_adm_namespace_id,(SELECT COUNT(*) FROM adm_accion WHERE adm_accion.adm_seccion_id = adm_seccion.id) AS adm_seccion_n_acciones, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id, adm_menu.codigo AS adm_menu_codigo, adm_menu.codigo_bis AS adm_menu_codigo_bis, adm_menu.descripcion_select AS adm_menu_descripcion_select, adm_menu.alias AS adm_menu_alias, adm_menu.titulo AS adm_menu_titulo,(SELECT COUNT(*) FROM adm_accion WHERE adm_accion.adm_seccion_id = adm_seccion.id) AS adm_seccion_n_acciones, adm_namespace.id AS adm_namespace_id, adm_namespace.descripcion AS adm_namespace_descripcion, adm_namespace.status AS adm_namespace_status, adm_namespace.usuario_update_id AS adm_namespace_usuario_update_id, adm_namespace.fecha_alta AS adm_namespace_fecha_alta, adm_namespace.fecha_update AS adm_namespace_fecha_update, adm_namespace.usuario_alta_id AS adm_namespace_usuario_alta_id, adm_namespace.codigo AS adm_namespace_codigo, adm_namespace.codigo_bis AS adm_namespace_codigo_bis, adm_namespace.descripcion_select AS adm_namespace_descripcion_select, adm_namespace.alias AS adm_namespace_alias, adm_namespace.predeterminado AS adm_namespace_predeterminado, adm_namespace.name AS adm_namespace_name,(SELECT COUNT(*) FROM adm_accion WHERE adm_accion.adm_seccion_id = adm_seccion.id) AS adm_seccion_n_acciones FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id WHERE ((x))',$resultado);
        //$this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.status AS adm_seccion_status, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.icono AS adm_seccion_icono, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.codigo AS adm_seccion_codigo, adm_seccion.codigo_bis AS adm_seccion_codigo_bis, adm_seccion.descripcion_select AS adm_seccion_descripcion_select, adm_seccion.alias AS adm_seccion_alias, adm_seccion.adm_namespace_id AS adm_seccion_adm_namespace_id, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id, adm_menu.codigo AS adm_menu_codigo, adm_menu.codigo_bis AS adm_menu_codigo_bis, adm_menu.descripcion_select AS adm_menu_descripcion_select, adm_menu.alias AS adm_menu_alias, adm_menu.titulo AS adm_menu_titulo, adm_namespace.id AS adm_namespace_id, adm_namespace.descripcion AS adm_namespace_descripcion, adm_namespace.status AS adm_namespace_status, adm_namespace.usuario_update_id AS adm_namespace_usuario_update_id, adm_namespace.fecha_alta AS adm_namespace_fecha_alta, adm_namespace.fecha_update AS adm_namespace_fecha_update, adm_namespace.usuario_alta_id AS adm_namespace_usuario_alta_id, adm_namespace.codigo AS adm_namespace_codigo, adm_namespace.codigo_bis AS adm_namespace_codigo_bis, adm_namespace.descripcion_select AS adm_namespace_descripcion_select, adm_namespace.alias AS adm_namespace_alias, adm_namespace.predeterminado AS adm_namespace_predeterminado, adm_namespace.name AS adm_namespace_name,(SELECT COUNT(*) FROM adm_accion WHERE adm_accion.adm_seccion_id = adm_seccion.id) AS adm_seccion_n_acciones FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id WHERE ((x))',$resultado);
        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.status AS adm_seccion_status, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.icono AS adm_seccion_icono, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.codigo AS adm_seccion_codigo, adm_seccion.codigo_bis AS adm_seccion_codigo_bis, adm_seccion.descripcion_select AS adm_seccion_descripcion_select, adm_seccion.alias AS adm_seccion_alias, adm_seccion.adm_namespace_id AS adm_seccion_adm_namespace_id, adm_seccion.predeterminado AS adm_seccion_predeterminado, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id, adm_menu.codigo AS adm_menu_codigo, adm_menu.codigo_bis AS adm_menu_codigo_bis, adm_menu.descripcion_select AS adm_menu_descripcion_select, adm_menu.alias AS adm_menu_alias, adm_menu.titulo AS adm_menu_titulo, adm_menu.predeterminado AS adm_menu_predeterminado, adm_namespace.id AS adm_namespace_id, adm_namespace.descripcion AS adm_namespace_descripcion, adm_namespace.status AS adm_namespace_status, adm_namespace.usuario_update_id AS adm_namespace_usuario_update_id, adm_namespace.fecha_alta AS adm_namespace_fecha_alta, adm_namespace.fecha_update AS adm_namespace_fecha_update, adm_namespace.usuario_alta_id AS adm_namespace_usuario_alta_id, adm_namespace.codigo AS adm_namespace_codigo, adm_namespace.codigo_bis AS adm_namespace_codigo_bis, adm_namespace.descripcion_select AS adm_namespace_descripcion_select, adm_namespace.alias AS adm_namespace_alias, adm_namespace.predeterminado AS adm_namespace_predeterminado, adm_namespace.name AS adm_namespace_name,(SELECT COUNT(*) FROM adm_accion WHERE adm_accion.adm_seccion_id = adm_seccion.id) AS adm_seccion_n_acciones FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id WHERE ((x))',$resultado);


        errores::$error = false;


        $columnas = array();
        $columnas_by_table = array();
        $columnas_en_bruto = true;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array();
        $offset = 0;
        $order = array();
        $sql_extra = 'x';
        $tipo_filtro = '';
        $in = array();
         $extra_join = array();
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, true, array(), array(), $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);

        //print_r($resultado);exit;

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS id, adm_seccion.descripcion AS descripcion, adm_seccion.etiqueta_label AS etiqueta_label, adm_seccion.status AS status, adm_seccion.adm_menu_id AS adm_menu_id, adm_seccion.icono AS icono, adm_seccion.fecha_alta AS fecha_alta, adm_seccion.fecha_update AS fecha_update, adm_seccion.usuario_alta_id AS usuario_alta_id, adm_seccion.usuario_update_id AS usuario_update_id, adm_seccion.codigo AS codigo, adm_seccion.codigo_bis AS codigo_bis, adm_seccion.descripcion_select AS descripcion_select, adm_seccion.alias AS alias, adm_seccion.adm_namespace_id AS adm_namespace_id, adm_seccion.predeterminado AS predeterminado,(SELECT COUNT(*) FROM adm_accion WHERE adm_accion.adm_seccion_id = adm_seccion.id) AS adm_seccion_n_acciones FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id WHERE ((x))',$resultado);


        errores::$error = false;


        $columnas = array('adm_seccion_id');
        $columnas_by_table = array();
        $columnas_en_bruto = false;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array();
        $offset = 0;
        $order = array();
        $sql_extra = 'x';
        $tipo_filtro = '';
        $in = array();
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, true, array(), array(), $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);

        //print_r($resultado);exit;

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id WHERE ((x))',$resultado);


        errores::$error = false;


        $columnas = array('adm_seccion_id');
        $columnas_by_table = array();
        $columnas_en_bruto = false;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array('llave'=>'a','values'=>array('c','d'));
        $offset = 0;
        $order = array();
        $sql_extra = 'x';
        $tipo_filtro = '';
        $in = array('llave'=>'a','values'=>array('a','b'));
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, true, array(), array(), $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);

        //print_r($resultado);exit;

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("SELECT adm_seccion.id AS adm_seccion_id FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id WHERE ((a IN ('a' ,'b'))) AND ((a NOT IN ('c' ,'d'))) AND ((x))",$resultado);

        errores::$error = false;


        $columnas = array('adm_seccion_id');
        $columnas_by_table = array();
        $columnas_en_bruto = false;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array('llave'=>'a','values'=>array('c','d'));
        $offset = 0;
        $order = array();
        $sql_extra = 'x';
        $tipo_filtro = '';
        $in = array('llave'=>'a','values'=>array('a','b'));
        $diferente_de['a']= 'p';
        $diferente_de['g']= 'p';
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, true, $diferente_de, array(), $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);


        //print_r($resultado);exit;
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("SELECT adm_seccion.id AS adm_seccion_id FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id WHERE ((a IN ('a' ,'b'))) AND ((a NOT IN ('c' ,'d'))) AND ((a <> 'p' AND g <> 'p')) AND ((x))",$resultado);


        errores::$error = false;



        $columnas = array('adm_seccion_id');
        $columnas_by_table = array();
        $columnas_en_bruto = false;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array('llave'=>'a','values'=>array('c','d'));
        $offset = 0;
        $order = array();
        $sql_extra = 'x';
        $tipo_filtro = '';
        $in = array('llave'=>'a','values'=>array('a','b'));
        $diferente_de['a']= 'p';
        $diferente_de['g']= 'p';
        $extra_join = array();
        $extra_join['adm_seccion']['key'] = 'id';
        $extra_join['adm_seccion']['enlace'] = 'adm_seccion';
        $extra_join['adm_seccion']['key_enlace'] = 'id';
        $extra_join['adm_seccion']['renombre'] = 'adm_seccion_bis';
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, true, $diferente_de, $extra_join, $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);



        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("SELECT adm_seccion.id AS adm_seccion_id FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id LEFT JOIN adm_seccion AS adm_seccion_bis ON adm_seccion_bis.id = adm_seccion.id WHERE ((a IN ('a' ,'b'))) AND ((a NOT IN ('c' ,'d'))) AND ((a <> 'p' AND g <> 'p')) AND ((x))",$resultado);
        errores::$error = false;

        $columnas = array('adm_seccion_id');
        $columnas_by_table = array();
        $columnas_en_bruto = false;
        $filtro = array();
        $filtro_especial = array();
        $filtro_extra = array();
        $filtro_rango = array();
        $group_by = array();
        $limit = 0;
        $not_in = array('llave'=>'a','values'=>array('c','d'));
        $offset = 0;
        $order = array();
        $sql_extra = 'x';
        $tipo_filtro = '';
        $in = array('llave'=>'a','values'=>array('a','b'));
        $diferente_de['a']= 'p';
        $diferente_de['g']= 'p';
        $extra_join = array();
        $extra_join['adm_seccion']['key'] = 'id';
        $extra_join['adm_seccion']['enlace'] = 'adm_seccion';
        $extra_join['adm_seccion']['key_enlace'] = 'id';
        $extra_join['adm_seccion']['renombre'] = 'adm_seccion_bis';
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, true, $diferente_de, $extra_join, $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro, true);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("SELECT COUNT(*) AS total_registros FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id LEFT JOIN adm_seccion AS adm_seccion_bis ON adm_seccion_bis.id = adm_seccion.id WHERE ((a IN ('a' ,'b'))) AND ((a NOT IN ('c' ,'d'))) AND ((a <> 'p' AND g <> 'p')) AND ((x))",$resultado);
        errores::$error = false;


    }



    public function test_get_codigo_aleatorio(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $resultado = $modelo->get_codigo_aleatorio();
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $resultado = $modelo->get_codigo_aleatorio(-1);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);


        errores::$error = false;
    }

    public function test_get_data_descripcion(){
        errores::$error = false;
        $modelo = new adm_estado_civil($this->link);
        //$modelo = new liberator($modelo);


        $_SESSION['usuario_id'] = 2;

        $dato = '';
        $resultado = $modelo->get_data_descripcion(dato: $dato);

       // print_r($resultado);exit;

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        //$this->assertCount(5,$resultado->registros);

        errores::$error = false;

        $dato = 'S';
        $resultado = $modelo->get_data_descripcion(dato: $dato);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        //$this->assertCount(2,$resultado->registros);
        errores::$error = false;
    }

    public function test_get_data_lista(){
        errores::$error = false;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);


        $_SESSION['usuario_id'] = 2;

        $filtro['adm_accion_basica.descripcion'] = 'a';
        $del = (new adm_accion_basica($this->link))->elimina_con_filtro_and($filtro);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_campo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }



        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion_pertenece($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'adm_seccion';
        $adm_seccion['adm_menu_id'] = '1';
        $adm_seccion['adm_namespace_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_accion['id'] = 1;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['adm_seccion_id'] = '1';
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $modelo->get_data_lista();
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['n_registros']);

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);
        $filtro_especial[0]['adm_accion.id']['operador'] = 'LIKE';
        $filtro_especial[0]['adm_accion.id']['valor'] = "%1%";
        $resultado = $modelo->get_data_lista(filtro_especial: $filtro_especial);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['n_registros']);
        $this->assertIsNumeric($resultado['registros'][0]['adm_accion_id']);

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);
        $filtro_especial[0]['adm_accion.id']['operador'] = 'LIKE';
        $filtro_especial[0]['adm_accion.id']['valor'] = "%1%";
        $filtro_especial[0]['adm_accion.id']['comparacion'] = "OR";

        $filtro_especial[1]['adm_accion.descripcion']['operador'] = 'LIKE';
        $filtro_especial[1]['adm_accion.descripcion']['valor'] = "%1%";
        $filtro_especial[1]['adm_accion.descripcion']['comparacion'] = "OR";

        $resultado = $modelo->get_data_lista(filtro_especial: $filtro_especial);
        //print_r($resultado);exit;

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['n_registros']);


        $this->assertEquals("SELECT adm_accion.id AS adm_accion_id, adm_accion.descripcion AS adm_accion_descripcion, adm_accion.etiqueta_label AS adm_accion_etiqueta_label, adm_accion.adm_seccion_id AS adm_accion_adm_seccion_id, adm_accion.status AS adm_accion_status, adm_accion.icono AS adm_accion_icono, adm_accion.visible AS adm_accion_visible, adm_accion.inicio AS adm_accion_inicio, adm_accion.lista AS adm_accion_lista, adm_accion.seguridad AS adm_accion_seguridad, adm_accion.usuario_update_id AS adm_accion_usuario_update_id, adm_accion.usuario_alta_id AS adm_accion_usuario_alta_id, adm_accion.fecha_alta AS adm_accion_fecha_alta, adm_accion.fecha_update AS adm_accion_fecha_update, adm_accion.es_modal AS adm_accion_es_modal, adm_accion.es_view AS adm_accion_es_view, adm_accion.titulo AS adm_accion_titulo, adm_accion.css AS adm_accion_css, adm_accion.es_status AS adm_accion_es_status, adm_accion.descripcion_select AS adm_accion_descripcion_select, adm_accion.codigo AS adm_accion_codigo, adm_accion.codigo_bis AS adm_accion_codigo_bis, adm_accion.alias AS adm_accion_alias, adm_accion.es_lista AS adm_accion_es_lista, adm_accion.muestra_icono_btn AS adm_accion_muestra_icono_btn, adm_accion.muestra_titulo_btn AS adm_accion_muestra_titulo_btn, adm_accion.id_css AS adm_accion_id_css, adm_seccion.id AS adm_seccion_id, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.status AS adm_seccion_status, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.icono AS adm_seccion_icono, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.codigo AS adm_seccion_codigo, adm_seccion.codigo_bis AS adm_seccion_codigo_bis, adm_seccion.descripcion_select AS adm_seccion_descripcion_select, adm_seccion.alias AS adm_seccion_alias, adm_seccion.adm_namespace_id AS adm_seccion_adm_namespace_id, adm_seccion.predeterminado AS adm_seccion_predeterminado, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id, adm_menu.codigo AS adm_menu_codigo, adm_menu.codigo_bis AS adm_menu_codigo_bis, adm_menu.descripcion_select AS adm_menu_descripcion_select, adm_menu.alias AS adm_menu_alias, adm_menu.titulo AS adm_menu_titulo, adm_menu.predeterminado AS adm_menu_predeterminado,(SELECT COUNT(*) FROM adm_accion_grupo WHERE adm_accion_grupo.adm_accion_id = adm_accion.id) AS adm_accion_n_permisos FROM adm_accion AS adm_accion LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = adm_accion.adm_seccion_id LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id WHERE ((adm_accion.id LIKE '%1%' OR adm_accion.descripcion LIKE '%1%')) ORDER BY adm_accion.id DESC LIMIT 10",$resultado['data_result']->sql);

        errores::$error = false;
    }

    public function test_get_foraneas(){
        errores::$error = false;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);


        $_SESSION['usuario_id'] = 2;

        $resultado = $modelo->get_foraneas();

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('administrador',$resultado->adm_seccion_id->nombre_database);
        $this->assertEquals('adm_accion',$resultado->adm_seccion_id->nombre_tabla);
        $this->assertEquals('adm_seccion_id',$resultado->adm_seccion_id->columna_foranea);
        $this->assertEquals('adm_seccion',$resultado->adm_seccion_id->nombre_tabla_relacion);


        errores::$error = false;
    }

    public function test_id_predeterminado(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $resultado = $modelo->id_predeterminado();
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        errores::$error = false;
    }

    public function test_id_preferido_detalle(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $entidad_preferida = 'adm_menu';
        $resultado = $modelo->id_preferido_detalle($entidad_preferida);
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_in_llave(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);
        $in = array();
        $in[] = '';
        $resultado = $modelo->in_llave($in);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    /*public function test_init_archivos_tmp_model_exe(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);
        $resultado = $modelo->init_archivos_tmp_model_exe();
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }*/
    public function test_inserta_predeterminado(): void
    {
        $_SESSION['usuario_id'] = 2;
        errores::$error = false;
        $modelo = new adm_mes($this->link);


        $resultado = $modelo->inserta_predeterminado();

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }



    public function test_limpia_campos_extras(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $registro = array();
        $campos_limpiar = array();
        $campos_limpiar[] = 'a';
        $resultado = $modelo->limpia_campos_extras($registro, $campos_limpiar);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_limpia_campos_sin_bd(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $registro = array();

        $resultado = $modelo->limpia_campos_sin_bd($registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_modifica_bd(): void
    {
        $_SESSION['usuario_id'] = 2;
        errores::$error = false;
        $modelo = new adm_menu($this->link);
        //$modelo = new liberator($modelo);

        $registro = array();
        $registro['status'] = 'inactivo';

        $resultado = $modelo->modifica_bd(registro:  $registro,id: 1);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

        $registro = array();
        $registro['status'] = 'activo';

        $resultado = $modelo->modifica_bd(registro:  $registro,id: 1);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Exito al ejecutar sql del modelo adm_menu transaccion UPDATE', $resultado->mensaje);


        errores::$error = false;
    }

    public function test_obten_data(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $resultado = $modelo->obten_data();
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el id debe ser mayor a 0 en el modelo adm_seccion', $resultado['mensaje']);

        errores::$error = false;
        $modelo->registro_id = 1;
        $resultado = $modelo->obten_data();
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_obten_datos_ultimo_registro(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $filtro['adm_seccion.descripcion'] = 'x';
        $resultado = $modelo->obten_datos_ultimo_registro(filtro: $filtro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $order['adm_seccion.descripcion']='ASC';
        //$columnas[]='adm_seccion_id';
        $resultado = $modelo->obten_datos_ultimo_registro(columnas_en_bruto: true, order: $order);


        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);


        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $order['adm_seccion.descripcion']='ASC';
        //$columnas[]='adm_seccion_id';
        $filtro_extra[0]['adm_seccion.descripcion']['operador'] = '>=';
        $filtro_extra[0]['adm_seccion.descripcion']['valor'] = "'adm_m'";
        $filtro_extra[0]['adm_seccion.descripcion']['comparacion'] = 'AND';
        $resultado = $modelo->obten_datos_ultimo_registro(columnas_en_bruto: true, filtro_extra: $filtro_extra, order: $order);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);

        errores::$error = false;
    }

    public function test_obten_por_id(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $resultado = $modelo->obten_por_id();
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el id debe ser mayor a 0', $resultado['mensaje']);

        errores::$error = false;
        $modelo->registro_id = 1;
        $resultado = $modelo->obten_por_id();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_seccion(link: $this->link);
        $modelo->aplica_seguridad = true;
        $modelo->registro_id = 1;
        $modelo->columnas_extra['usuario_permitido_id'] = '1';

        $modelo = new liberator($modelo);

        $resultado = $modelo->obten_por_id();
        //print_r($resultado->sql);exit;
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.status AS adm_seccion_status, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.icono AS adm_seccion_icono, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.codigo AS adm_seccion_codigo, adm_seccion.codigo_bis AS adm_seccion_codigo_bis, adm_seccion.descripcion_select AS adm_seccion_descripcion_select, adm_seccion.alias AS adm_seccion_alias, adm_seccion.adm_namespace_id AS adm_seccion_adm_namespace_id, adm_seccion.predeterminado AS adm_seccion_predeterminado, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id, adm_menu.codigo AS adm_menu_codigo, adm_menu.codigo_bis AS adm_menu_codigo_bis, adm_menu.descripcion_select AS adm_menu_descripcion_select, adm_menu.alias AS adm_menu_alias, adm_menu.titulo AS adm_menu_titulo, adm_menu.predeterminado AS adm_menu_predeterminado, adm_namespace.id AS adm_namespace_id, adm_namespace.descripcion AS adm_namespace_descripcion, adm_namespace.status AS adm_namespace_status, adm_namespace.usuario_update_id AS adm_namespace_usuario_update_id, adm_namespace.fecha_alta AS adm_namespace_fecha_alta, adm_namespace.fecha_update AS adm_namespace_fecha_update, adm_namespace.usuario_alta_id AS adm_namespace_usuario_alta_id, adm_namespace.codigo AS adm_namespace_codigo, adm_namespace.codigo_bis AS adm_namespace_codigo_bis, adm_namespace.descripcion_select AS adm_namespace_descripcion_select, adm_namespace.alias AS adm_namespace_alias, adm_namespace.predeterminado AS adm_namespace_predeterminado, adm_namespace.name AS adm_namespace_name,(SELECT COUNT(*) FROM adm_accion WHERE adm_accion.adm_seccion_id = adm_seccion.id) AS adm_seccion_n_acciones,1 AS usuario_permitido_id FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id LEFT JOIN adm_namespace AS adm_namespace ON adm_namespace.id = adm_seccion.adm_namespace_id WHERE adm_seccion.id = 1 AND (1) = 2', $resultado->sql);


        errores::$error = false;

    }

    public function test_obten_registros(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $resultado = $modelo->obten_registros();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_obten_registros_activos(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $resultado = $modelo->obten_registros_activos();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_obten_registros_filtro_and_ordenado(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);


        $campo = '';
        $filtros = array();
        $orden = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo,false, array(), $filtros, $orden);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error los filtros no pueden venir vacios', $resultado['mensaje']);

        errores::$error = false;

        $campo = '';
        $filtros = array();
        $orden = '';
        $filtros[] = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo, false, array(), $filtros, $orden);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo no pueden venir vacios', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $filtros = array();
        $orden = '';
        $filtros['a'] = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo, false, array(), $filtros, $orden);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar sql', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'adm_seccion.id';
        $filtros = array();
        $orden = '';
        $filtros['adm_seccion.id'] = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo, false, array(), $filtros, $orden);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_obten_ultimo_registro(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);


        $resultado = $modelo->obten_ultimo_registro();
       // print_r($resultado);exit;
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

    }

    public function test_primer_id(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);


        $resultado = $modelo->primer_id();
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado);


        errores::$error = false;

    }

    public function test_registro(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $resultado = $modelo->registro(registro_id: 1);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_registro_by_codigo()
    {

        $_SESSION['usuario_id'] = 2;
        errores::$error = false;

        $del = (new base_test())->del_adm_seccion(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al del',data:  $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_adm_seccion(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar',data:  $alta);
            print_r($error);
            exit;
        }
            $modelo = new adm_seccion(link: $this->link);
        $codigo = 'adm_seccionaadministrador';
        $resultado = $modelo->registro_by_codigo($codigo);
        //print_r($resultado);exit;
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

        $codigo = 'adm_seccionaadministrador';
        $resultado = $modelo->registro_by_codigo(codigo: $codigo,columnas_en_bruto: true);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['id']);

        errores::$error = false;

        $codigo = 'adm_seccionaadministrador';
        $resultado = $modelo->registro_by_codigo(codigo: $codigo, columnas: array('adm_seccion_id'), columnas_en_bruto: true);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['id']);

        errores::$error = false;

        $codigo = 'adm_seccionaadministrador';
        $resultado = $modelo->registro_by_codigo(codigo: $codigo, columnas: array('adm_seccion_id'), columnas_en_bruto: true,retorno_obj: true);


        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado->id);

        errores::$error = false;
    }

    public function test_registros(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);

        $resultado = $modelo->registros();
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_registros_activos(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $resultado = $modelo->registros_activos();

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
        $resultado = $modelo->registros_activos(retorno_obj: true);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado[0]->adm_seccion_id);
        errores::$error = false;
    }

    public function test_result_ini(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);
        $resultado = $modelo->result_ini();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(0,$resultado->n_registros);
        $this->assertEmpty($resultado->registros);
        $this->assertEquals('',$resultado->sql);
        $this->assertEmpty($resultado->registros_obj);
        errores::$error = false;
    }

    public function test_seccion_menu_id(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;

        $seccion = '';
        $resultado = $modelo->seccion_menu_id($seccion);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error seccion no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $seccion = 'a';
        $resultado = $modelo->seccion_menu_id($seccion);


        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener seccion menu no existe', $resultado['mensaje']);

        errores::$error = false;

        $filtro['adm_accion_basica.descripcion'] = 'a';
        $del = (new adm_accion_basica($this->link))->elimina_con_filtro_and($filtro);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_campo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_bitacora($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $registro['id'] = 1;
        $registro['descripcion'] = 'adm_seccion';
        $registro['adm_menu_id'] = '1';
        $registro['adm_namespace_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $seccion = 'adm_seccion';
        $resultado = $modelo->seccion_menu_id($seccion);
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado);

        errores::$error = false;


    }

    public function test_sentencia_or(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $campo = '';
        $sentencia = '';
        $value = '';
        $resultado = $modelo->sentencia_or($campo, $sentencia, $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo esta vacio', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $sentencia = '';
        $value = '';
        $resultado = $modelo->sentencia_or($campo, $sentencia, $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("  a = ''", $resultado);
        errores::$error = false;
    }

    public function test_suma(): void
    {
        errores::$error = false;
        $modelo = new adm_menu($this->link);
        //$modelo = new liberator($modelo);

        $campos = array();
        $campos['a'] = 'adm_menu.id';
        $resultado = $modelo->suma($campos);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric($resultado['a']);
        errores::$error = false;
    }

    public function test_tiene_registros(): void
    {
        errores::$error = false;
        $modelo = new adm_mes($this->link);
        //$modelo = new liberator($modelo);

        $del = (new adm_mes(link: $this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al del', $del);
            print_r($error);
            exit;
        }

        $resultado = $modelo->tiene_registros();
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);
        errores::$error = false;
    }

    public function test_total_registros(): void
    {
        $_SESSION['usuario_id'] = 2;
        errores::$error = false;
        $modelo = new adm_mes($this->link);

        $del = (new adm_mes(link: $this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al del', $del);
            print_r($error);
            exit;
        }

        $adm_mes['id'] = 1;
        $adm_mes['descripcion'] = 1;
        $alta = (new adm_mes(link: $this->link))->alta_registro(registro: $adm_mes);
        if(errores::$error){
            $error = (new errores())->error('Error al alta', $alta);
            print_r($error);
            exit;
        }

        $resultado = $modelo->total_registros();
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado);
        errores::$error = false;
    }

    public function test_valida_predetermiando(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);


        $resultado = $modelo->valida_predetermiando();
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

        errores::$error = false;

        $modelo = new adm_seccion($this->link);
        $modelo->registro['predeterminado'] = 'activo';
        $modelo = new liberator($modelo);

        $resultado = $modelo->valida_predetermiando();
        //print_r($resultado);exit;
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        //$this->assertStringContainsStringIgnoringCase('Error al verificar si existe', $resultado['mensaje']);

        errores::$error = false;
    }






    public function test_where_suma(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);
        $campos = array();
        $campos['adm_seccion_id'] = 'adm_seccion.id';
        $filtro = array();
        $resultado = $modelo->suma($campos,$filtro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }




}
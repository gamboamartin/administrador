<?php
namespace tests\src;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_seccion;



class modeloTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
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

    public function test_filtro_and(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $resultado = $modelo->filtro_and();
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
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.status AS adm_seccion_status, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.icono AS adm_seccion_icono, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.codigo AS adm_seccion_codigo, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.observaciones AS adm_menu_observaciones, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id   FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id            ',$resultado);

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
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.status AS adm_seccion_status, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.icono AS adm_seccion_icono, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.codigo AS adm_seccion_codigo, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.observaciones AS adm_menu_observaciones, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id   FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id WHERE        ( (x))     ',$resultado);


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
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS id, adm_seccion.descripcion AS descripcion, adm_seccion.etiqueta_label AS etiqueta_label, adm_seccion.status AS status, adm_seccion.adm_menu_id AS adm_menu_id, adm_seccion.icono AS icono, adm_seccion.fecha_alta AS fecha_alta, adm_seccion.fecha_update AS fecha_update, adm_seccion.usuario_alta_id AS usuario_alta_id, adm_seccion.usuario_update_id AS usuario_update_id, adm_seccion.codigo AS codigo   FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id WHERE        ( (x))     ',$resultado);


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
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id   FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id WHERE        ( (x))     ',$resultado);


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
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo,false, $filtros, $orden);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error los filtros no pueden venir vacios', $resultado['mensaje']);

        errores::$error = false;

        $campo = '';
        $filtros = array();
        $orden = '';
        $filtros[] = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo, false, $filtros, $orden);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo no pueden venir vacios', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $filtros = array();
        $orden = '';
        $filtros['a'] = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo, false, $filtros, $orden);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar sql', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'adm_seccion.id';
        $filtros = array();
        $orden = '';
        $filtros['adm_seccion.id'] = '';
        $resultado = $modelo->obten_registros_filtro_and_ordenado($campo, false, $filtros, $orden);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
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

    public function test_seccion_menu_id(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

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

        $seccion = 'adm_seccion';
        $resultado = $modelo->seccion_menu_id($seccion);
        $this->assertIsInt( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(13, $resultado);

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




}
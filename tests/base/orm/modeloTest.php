<?php
namespace tests\src;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_dia;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;




class modeloTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
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
        $this->assertStringContainsStringIgnoringCase('INSERT INTO adm_dia (codigo,descripcion,status,usuario_alta_id,usuario_update_id) VALUE', $resultado->sql);

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
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar si existe', $resultado['mensaje']);
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
        $in = array();
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, array(), $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);


        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.status AS adm_seccion_status, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.icono AS adm_seccion_icono, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.codigo AS adm_seccion_codigo, adm_seccion.codigo_bis AS adm_seccion_codigo_bis, adm_seccion.descripcion_select AS adm_seccion_descripcion_select, adm_seccion.alias AS adm_seccion_alias, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.observaciones AS adm_menu_observaciones, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id, adm_menu.codigo AS adm_menu_codigo, adm_menu.codigo_bis AS adm_menu_codigo_bis, adm_menu.descripcion_select AS adm_menu_descripcion_select, adm_menu.alias AS adm_menu_alias   FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id              ',$resultado);

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
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, array(), $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);


        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id, adm_seccion.descripcion AS adm_seccion_descripcion, adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label, adm_seccion.status AS adm_seccion_status, adm_seccion.adm_menu_id AS adm_seccion_adm_menu_id, adm_seccion.icono AS adm_seccion_icono, adm_seccion.fecha_alta AS adm_seccion_fecha_alta, adm_seccion.fecha_update AS adm_seccion_fecha_update, adm_seccion.usuario_alta_id AS adm_seccion_usuario_alta_id, adm_seccion.usuario_update_id AS adm_seccion_usuario_update_id, adm_seccion.codigo AS adm_seccion_codigo, adm_seccion.codigo_bis AS adm_seccion_codigo_bis, adm_seccion.descripcion_select AS adm_seccion_descripcion_select, adm_seccion.alias AS adm_seccion_alias, adm_menu.id AS adm_menu_id, adm_menu.descripcion AS adm_menu_descripcion, adm_menu.etiqueta_label AS adm_menu_etiqueta_label, adm_menu.icono AS adm_menu_icono, adm_menu.observaciones AS adm_menu_observaciones, adm_menu.status AS adm_menu_status, adm_menu.usuario_update_id AS adm_menu_usuario_update_id, adm_menu.fecha_alta AS adm_menu_fecha_alta, adm_menu.fecha_update AS adm_menu_fecha_update, adm_menu.usuario_alta_id AS adm_menu_usuario_alta_id, adm_menu.codigo AS adm_menu_codigo, adm_menu.codigo_bis AS adm_menu_codigo_bis, adm_menu.descripcion_select AS adm_menu_descripcion_select, adm_menu.alias AS adm_menu_alias   FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id WHERE           ( (x))    ',$resultado);


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
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, array(), $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);



        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS id, adm_seccion.descripcion AS descripcion, adm_seccion.etiqueta_label AS etiqueta_label, adm_seccion.status AS status, adm_seccion.adm_menu_id AS adm_menu_id, adm_seccion.icono AS icono, adm_seccion.fecha_alta AS fecha_alta, adm_seccion.fecha_update AS fecha_update, adm_seccion.usuario_alta_id AS usuario_alta_id, adm_seccion.usuario_update_id AS usuario_update_id, adm_seccion.codigo AS codigo, adm_seccion.codigo_bis AS codigo_bis, adm_seccion.descripcion_select AS descripcion_select, adm_seccion.alias AS alias   FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id WHERE           ( (x))    ',$resultado);


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
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, array(), $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);


        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id   FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id WHERE           ( (x))    ',$resultado);


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
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, array(), $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_seccion.id AS adm_seccion_id   FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id WHERE        ( (a  IN (a ,b)))   AND  ( (a NOT IN (c ,d)))    AND  ( (x))    ',$resultado);

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
        $resultado = $modelo->genera_sql_filtro($columnas, $columnas_by_table, $columnas_en_bruto, $diferente_de, $filtro,
            $filtro_especial, $filtro_extra, $filtro_rango, $group_by, $in, $limit, $not_in, $offset, $order, $sql_extra,
            $tipo_filtro);

        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("SELECT adm_seccion.id AS adm_seccion_id   FROM adm_seccion AS adm_seccion LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id WHERE        ( (a  IN (a ,b)))   AND  ( (a NOT IN (c ,d)))   AND  ( (a <> 'p'   AND  g <> 'p'))   AND  ( (x))    ",$resultado);


        errores::$error = false;
    }

    public function test_get_data_lista(){
        errores::$error = false;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);
        $resultado = $modelo->get_data_lista();
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(255,$resultado['n_registros']);

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);
        $filtro_especial[0]['adm_accion.id']['operador'] = 'LIKE';
        $filtro_especial[0]['adm_accion.id']['valor'] = "%1%";
        $resultado = $modelo->get_data_lista(filtro_especial: $filtro_especial);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(80,$resultado['n_registros']);
        $this->assertEquals(1,$resultado['registros'][0]['adm_accion_id']);

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
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(80,$resultado['n_registros']);
        $this->assertStringContainsStringIgnoringCase("WHERE    ( (adm_accion.id LIKE '%1%'  OR  adm_accion.descripcion LIKE '%1%'))",$resultado['data_result']->sql);

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

    public function test_registros(): void
    {
        errores::$error = false;
        $modelo = new adm_seccion($this->link);

        $resultado = $modelo->registros();
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
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al verificar si existe', $resultado['mensaje']);

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
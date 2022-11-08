<?php
namespace tests\base\orm;

use base\orm\modelo_base;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_dia;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\encripta\encriptador;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class modelo_baseTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }



    public function test_ajusta_row_select(){


        errores::$error = false;
        $mb = new modelo_base($this->link);
        //$mb = new liberator($mb);

        $campos_encriptados = array('z');
        $modelos_hijos = array();
        $modelos_hijos['adm_dia']['nombre_estructura'] = 'adm_accion';
        $modelos_hijos['adm_dia']['namespace_model'] = 'gamboamartin\\administrador\\models';
        $modelos_hijos['adm_dia']['filtros'] = array();
        $modelos_hijos['adm_dia']['filtros_con_valor'] = array();
        $row = array();
        $row['z'] = 'PHDA/NloYgF1lc+UHzxaUw==';
        $resultado = $mb->ajusta_row_select($campos_encriptados, $modelos_hijos, $row);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_asigna_codigo(): void
    {


        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $mb = new modelo_base($this->link);
        $mb->usuario_id = 2;
        $mb->campos_sql = 1;
        $mb = new liberator($mb);
        $keys_registro = array();
        $keys_row = array();
        $modelo = new adm_dia($this->link);

        $r = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $r);
            print_r($error);
            exit;
        }

        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;

        $r = $modelo->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $r);
            print_r($error);
            exit;
        }

        $registro = array();
        $registro['adm_dia_id'] = 1;
        $resultado = $mb->asigna_codigo($keys_registro, $keys_row, $modelo, $registro);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_asigna_descripcion()
    {
        errores::$error = false;

        $_SESSION['usuario_id'] = 2;

        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);
        $modelo = new adm_mes($this->link);

        $del = (new adm_mes($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $mes_ins = array();
        $mes_ins['id'] = 1;
        $mes_ins['codigo'] = 1;
        $mes_ins['descripcion'] = 1;
        $alta = (new adm_mes($this->link))->alta_registro($mes_ins);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al dar de alta', data: $alta);
            print_r($error);
            exit;
        }

        $registro = array();
        $registro['adm_mes_id'] = 1;
        $resultado = $mb->asigna_descripcion($modelo, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['adm_mes_id']);
        $this->assertEquals(1,$resultado['descripcion']);
        errores::$error = false;
    }

    public function test_asigna_registros_hijo(){


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $name_modelo = '';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $namespace_model = 'gamboamartin\\administrador\\models';
        $resultado = $mb->asigna_registros_hijo(filtro: $filtro, name_modelo: $name_modelo,
            namespace_model: $namespace_model, nombre_estructura: $nombre_estructura, row: $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar entrada para modelo', $resultado['mensaje']);

        errores::$error = false;
        $name_modelo = 'adm_accion_grupo';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo, namespace_model: $namespace_model,
            nombre_estructura: $nombre_estructura,row:  $row);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error nombre estructura no puede venir vacia', $resultado['mensaje']);


        errores::$error = false;
        $name_modelo = 'pais';
        $filtro = array();
        $row = array();
        $nombre_estructura = '';
        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,namespace_model: $namespace_model,
            nombre_estructura: $nombre_estructura,row:  $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error nombre estructura no puede venir vacia', $resultado['mensaje']);



        errores::$error = false;
        $name_modelo = 'adm_seccion';
        $filtro = array();
        $row = array();
        $nombre_estructura = 'adm_seccion';

        $resultado = $mb->asigna_registros_hijo(filtro:  $filtro, name_modelo: $name_modelo,namespace_model: $namespace_model,
            nombre_estructura: $nombre_estructura,row:  $row);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['adm_seccion'][0]['adm_seccion_id']);

        errores::$error = false;


    }

    public function test_descripcion_alta()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);


        $modelo = new adm_accion($this->link);
        $registro = array();
        $registro['adm_accion_id'] = 1;
        $resultado = $mb->descripcion_alta($modelo, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('alta', $resultado);
        errores::$error = false;
    }

    public function test_ejecuta_sql()
    {
        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $resultado = $mb->ejecuta_sql('');
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error consulta vacia', $resultado['mensaje']);

        errores::$error = false;


        $resultado = $mb->ejecuta_sql('a');
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar sql', $resultado['mensaje']);

        errores::$error = false;
        $consulta = 'SELECT *FROM adm_seccion';

        $resultado = $mb->ejecuta_sql($consulta);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_genera_consulta_base(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);
        $columnas = array('adm_accion_id');
        $resultado = $modelo->genera_consulta_base($columnas);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SELECT adm_accion.id AS adm_accion_id   FROM adm_accion AS adm_accion LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = adm_accion.adm_seccion_id LEFT JOIN adm_menu AS adm_menu ON adm_menu.id = adm_seccion.adm_menu_id', $resultado);
        errores::$error = false;
    }

    public function test_genera_descripcion(){


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $modelo = new adm_accion($this->link);

        $registro = array();
        $registro['adm_accion_id'] = 2;
        $resultado = $mb->genera_descripcion($modelo, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('lista', $resultado);
        errores::$error = false;
    }

    public function test_genera_modelo()
    {
        errores::$error = false;
        $mb = new modelo_base($this->link);

        $modelo = '';
        $resultado = $mb->genera_modelo($modelo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar modelo', $resultado['mensaje']);

        errores::$error = false;
        $modelo = 'adm_accion';
        $resultado = $mb->genera_modelo($modelo,'gamboamartin\\administrador\\models');
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
        $modelo = 'adm_seccion';
        $resultado = $mb->genera_modelo($modelo,'gamboamartin\\administrador\\models');
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

    }

    public function test_genera_modelos_hijos()
    {
        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $resultado = $mb->genera_modelos_hijos();
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;

    }

    public function test_genera_registro_hijo(){

        errores::$error = false;
        $data_modelo = array();
        $row = array();

        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: '',row: $row);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);


        errores::$error = false;


        $data_modelo['nombre_estructura'] = '';
        $data_modelo['filtros'] = '';
        $data_modelo['filtros_con_valor'] = '';
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: '',row: $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);

        errores::$error = false;
        $data_modelo['nombre_estructura'] = '';
        $data_modelo['filtros'] = array();
        $data_modelo['filtros_con_valor'] = array();
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: '',row: $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);


        errores::$error = false;
        $data_modelo['nombre_estructura'] = 'x';
        $data_modelo['filtros'] = array();
        $data_modelo['filtros_con_valor'] = array();
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: 'x',row: $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);


        errores::$error = false;
        $data_modelo['nombre_estructura'] = 'x';
        $data_modelo['namespace_model'] = 'gamboamartin\\administrador\\models';
        $data_modelo['filtros'] = array();
        $data_modelo['filtros_con_valor'] = array();
        $resultado = $mb->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: 'adm_seccion',row: $row);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['x'][0]['adm_seccion_id']);
        errores::$error = false;

    }

    public function test_genera_registros_hijos(){

        errores::$error = false;

        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $modelos_hijos = array();
        $row = array();
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty( $resultado);

        errores::$error = false;

        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = '';
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error en datos', $resultado['mensaje']);

        errores::$error = false;

        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = array();
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);

        errores::$error = false;

        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = array();
        $modelos_hijos[0]['nombre_estructura'] = 'ne';
        $modelos_hijos[0]['filtros'] = 'ne';
        $modelos_hijos[0]['filtros_con_valor'] = 'ne';
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);

        errores::$error = false;
        $modelos_hijos = array();
        $row = array();
        $modelos_hijos[] = array();
        $modelos_hijos[0]['nombre_estructura'] = 'ne';
        $modelos_hijos[0]['filtros'] = array();
        $modelos_hijos[0]['filtros_con_valor'] = array();
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data_modelo', $resultado['mensaje']);

        errores::$error = false;
        $modelos_hijos = array();
        $row = array();
        $modelos_hijos['adm_seccion'] = array();
        $modelos_hijos['adm_seccion']['nombre_estructura'] = 'ne';
        $modelos_hijos['adm_seccion']['namespace_model'] = 'gamboamartin\\administrador\\models';
        $modelos_hijos['adm_seccion']['filtros'] = array();
        $modelos_hijos['adm_seccion']['filtros_con_valor'] = array();
        $resultado = $mb->genera_registros_hijos($modelos_hijos, $row);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['ne'][0]['adm_seccion_id']);
        errores::$error = false;


    }

    public function test_key_tmp()
    {


        errores::$error = false;
        $mb = new adm_seccion($this->link);
        $mb = new liberator($mb);

        $consulta = 'SELECTcatsatisridAScatsatisridcatsatisrdescripcionAScatsatisrdescripcioncatsatisrcodigoAScatsatisrcodigocatsatisrstatusAScatsatisrstatuscatsatisrusuarioaltaidAScatsatisrusuarioaltaidcatsatisrusuarioupdateidAScatsatisrusuarioupdateidcatsatisrfechaaltaAScatsatisrfechaaltacatsatisrfechaupdateAScatsatisrfechaupdatecatsatisrdescripcionselectAScatsatisrdescripcionselectcatsatisraliasAScatsatisraliascatsatisrcodigobisAScatsatisrcodigobiscatsatisrlimiteinferiorAScatsatisrlimiteinferiorcatsatisrlimitesuperiorAScatsatisrlimitesuperiorcatsatisrcuotafijaAScatsatisrcuotafijacatsatisrporcentajeexcedenteAScatsatisrporcentajeexcedentecatsatisrcatsatperiodicidadpagonomidAScatsatisrcatsatperiodicidadpagonomidcatsatisrfechainicioAScatsatisrfechainiciocatsatisrfechafinAScatsatisrfechafincatsatperiodicidadpagonomidAScatsatperiodicidadpagonomidcatsatperiodicidadpagonomdescripcionAScatsatperiodicidadpagonomdescripcioncatsatperiodicidadpagonomcodigoAScatsatperiodicidadpagonomcodigocatsatperiodicidadpagonomstatusAScatsatperiodicidadpagonomstatuscatsatperiodicidadpagonomusuarioaltaidAScatsatperiodicidadpagonomusuarioaltaidcatsatperiodicidadpagonomusuarioupdateidAScatsatperiodicidadpagonomusuarioupdateidcatsatperiodicidadpagonomfechaaltaAScatsatperiodicidadpagonomfechaaltacatsatperiodicidadpagonomfechaupdateAScatsatperiodicidadpagonomfechaupdatecatsatperiodicidadpagonomdescripcionselectAScatsatperiodicidadpagonomdescripcionselectcatsatperiodicidadpagonomaliasAScatsatperiodicidadpagonomaliascatsatperiodicidadpagonomcodigobisAScatsatperiodicidadpagonomcodigobiscatsatperiodicidadpagonomndiasAScatsatperiodicidadpagonomndiasFROMcatsatisrAScatsatisrLEFTJOINcatsatperiodicidadpagonomAScatsatperiodicidadpagonomONcatsatperiodicidadpagonomid=catsatisrcatsatperiodicidadpagonomidWHEREcatsatisrid=1';

        $resultado = $mb->key_tmp($consulta);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('75d700445ebf129b3195e1af7d6df67e', $resultado);
    }

    public function test_maqueta_arreglo_registros(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $r_sql =  $this->link->query(/** @lang text */ "SELECT *FROM adm_seccion");
        $modelos_hijos = array();
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado[0]['id']);

        errores::$error = false;

        $r_sql =  $this->link->query(/** @lang text */ "SELECT *FROM adm_seccion");
        $modelos_hijos = array();
        $campos_encriptados = array('descripcion');
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql,
            campos_encriptados: $campos_encriptados);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ajustar rows', $resultado['mensaje']);

        errores::$error = false;

        $vacio = (new encriptador())->encripta('');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al encriptar vacio', data: $vacio);
            print_r($error);exit;
        }


        $r_sql =  $this->link->query(/** @lang text */ "SELECT '$vacio' as descripcion FROM adm_seccion");
        $modelos_hijos = array();
        $campos_encriptados = array('descripcion');
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql,
            campos_encriptados: $campos_encriptados);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado[0]['descripcion']);

        errores::$error = false;

        $descripcion = (new encriptador())->encripta('esto es una descripcion');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al encriptar vacio', data: $vacio);
            print_r($error);exit;
        }


        $r_sql =  $this->link->query(/** @lang text */ "SELECT '$descripcion' as descripcion FROM adm_seccion");
        $modelos_hijos = array();
        $campos_encriptados = array('descripcion');
        $resultado = $mb->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql,
            campos_encriptados: $campos_encriptados);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('esto es una descripcion', $resultado[0]['descripcion']);


        errores::$error = false;

    }





    public function test_obten_nombre_tabla(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);
        $tabla_renombrada = '';
        $tabla_original = '';
        $resultado = $mb->obten_nombre_tabla($tabla_renombrada, $tabla_original);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no pueden venir vacios todos los parametros', $resultado['mensaje']);

        errores::$error = false;
        $tabla_renombrada = 'x';
        $tabla_original = '';
        $resultado = $mb->obten_nombre_tabla($tabla_renombrada, $tabla_original);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);

        errores::$error = false;
        $tabla_renombrada = 'x';
        $tabla_original = 'x';
        $resultado = $mb->obten_nombre_tabla($tabla_renombrada, $tabla_original);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);

        errores::$error = false;
        $tabla_renombrada = 'y';
        $tabla_original = 'x';
        $resultado = $mb->obten_nombre_tabla($tabla_renombrada, $tabla_original);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);
        errores::$error = false;
    }



    public function test_parsea_registros_envio(){

        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);

        $r_sql = $this->link->query(/** @lang text */ 'SELECT *FROM adm_seccion');
        $resultado = $mb->parsea_registros_envio($r_sql);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado[0]['id']);

        errores::$error = false;

    }

    public function test_registro_por_id()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        //$mb = new liberator($mb);


        $id = 1;
        $entidad = new adm_accion($this->link);
        $resultado = $mb->registro_por_id($entidad, $id);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado->adm_accion_id);
        errores::$error = false;

    }



    public function test_str_replace_first(){
        errores::$error = false;
        $modelo = new modelo_base($this->link);
        $resultado = $modelo->str_replace_first('','','');
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al content esta vacio', $resultado['mensaje']);

        errores::$error = false;
        $resultado = $modelo->str_replace_first('','','x');
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al content esta vacio', $resultado['mensaje']);

        errores::$error = false;
        $resultado = $modelo->str_replace_first('x','x','x');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty( $resultado);

        errores::$error = false;
        $resultado = $modelo->str_replace_first('x','x','x');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado);
        errores::$error = false;

    }







    public function test_valida_registro_modelo()
    {


        errores::$error = false;
        $mb = new modelo_base($this->link);
        $mb = new liberator($mb);


        $registro = array();
        $modelo = new adm_accion($this->link);
        $resultado = $mb->valida_registro_modelo($modelo, $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $registro['adm_accion_id'] = -1;
        $modelo = new adm_accion($this->link);
        $resultado = $mb->valida_registro_modelo($modelo, $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $registro['adm_accion_id'] = 1;
        $modelo = new adm_accion($this->link);
        $resultado = $mb->valida_registro_modelo($modelo, $registro);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }




}
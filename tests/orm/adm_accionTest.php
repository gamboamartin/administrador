<?php
namespace tests\orm;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class adm_accionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_acciones_por_id_grupo(){
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);


        $resultado = $modelo->acciones_id_por_grupo(1);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }

    public function test_alta_bd(){
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);

        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = $modelo->elimina_todo();
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

        $adm_seccion_ins['id'] = 2;
        $adm_seccion_ins['descripcion'] = 'adm_session';
        $adm_seccion_ins['adm_menu_id'] = 1;

        $alta = (new adm_seccion($this->link))->alta_registro(registro: $adm_seccion_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $accion_ins['descripcion'] = 'z';
        $accion_ins['adm_seccion_id'] = 2;
        $accion_ins['muestra_icono_btn'] = 'inactivo';


        $modelo->registro = $accion_ins;

        $resultado = $modelo->alta_bd();

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Z ADM_SESSION', $resultado->registro['adm_accion_descripcion_select']);
        errores::$error = false;
        errores::$error = false;
    }

    public function test_accion_registro(){

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

        $del = (new adm_elemento_lista($this->link))->elimina_todo();
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
        $del = (new adm_accion_basica($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $adm_accion_basica['id'] = 1;
        $adm_accion_basica['descripcion'] = 'alta';
        $adm_accion_basica['muestra_icono_btn'] = 'inactivo';
        $adm_accion_basica['muestra_titulo_btn'] = 'activo';

        $alta = (new adm_accion_basica($this->link))->alta_registro($adm_accion_basica);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'adm_seccion';
        $adm_seccion['adm_menu_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $accion= 'alta';
        $seccion= 'adm_seccion';
        $resultado = $modelo->accion_registro($accion, $seccion);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_accion_seccion(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);

        $accion= 'a';
        $seccion= 'b';
        $resultado = $modelo->accion_seccion($accion, $seccion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_acciones_id_maqueta(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;


        $adm_acciones_grupos= array();
        $adm_acciones_grupos[0]['adm_accion_id']= 1;
        $resultado = $modelo->acciones_id_maqueta($adm_acciones_grupos);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado[0]);
        errores::$error = false;
    }

    public function test_asigna_status(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);
        $key = 'a';
        $registro= array();
        $resultado = $modelo->asigna_status($key, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('activo', $resultado['a']);

        errores::$error = false;
    }

    public function test_acciones_permitidas(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        //$modelo = new liberator($modelo);
        $accion = '';
        $seccion= '';
        $resultado = $modelo->acciones_permitidas($accion, $modelo, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error debe existir grupo_id', $resultado['mensaje']);

        errores::$error = false;
        $_SESSION['grupo_id'] = 1;
        $accion = 'a';
        $seccion= 'a';
        $resultado = $modelo->acciones_permitidas($accion, $modelo, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error obtener seccion_menu_id', $resultado['mensaje']);

        errores::$error = false;
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

        $del = (new adm_elemento_lista($this->link))->elimina_todo();
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

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'adm_accion';
        $adm_seccion['adm_menu_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $_SESSION['grupo_id'] = 1;
        $accion = 'a';
        $seccion= 'adm_accion';
        $resultado = $modelo->acciones_permitidas($accion, $modelo, $seccion);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;
    }

    public function test_filtro_accion_seccion(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);
        $accion = '';
        $seccion= '';
        $resultado = $modelo->filtro_accion_seccion($accion, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar seccion', $resultado['mensaje']);

        errores::$error = false;

        $accion = 'c';
        $seccion= 'a';
        $resultado = $modelo->filtro_accion_seccion($accion, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado['adm_seccion.descripcion']);
        $this->assertEquals('c', $resultado['adm_accion.descripcion']);
        errores::$error = false;
    }

    public function test_filtro_permiso(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);
        $accion = '';
        $grupo_id= -1;
        $seccion= '';
        $resultado = $modelo->filtro_permiso($accion, $grupo_id, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error accion esta vacia', $resultado['mensaje']);

        errores::$error = false;
        $accion = 'a';
        $grupo_id= -1;
        $seccion= '';
        $resultado = $modelo->filtro_permiso($accion, $grupo_id, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $grupo_id debe ser mayor a 0', $resultado['mensaje']);

        errores::$error = false;
        $accion = 'a';
        $grupo_id= 1;
        $seccion= '';
        $resultado = $modelo->filtro_permiso($accion, $grupo_id, $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $seccion esta vacia', $resultado['mensaje']);

        errores::$error = false;
        $accion = 'a';
        $grupo_id= 1;
        $seccion= 'z';
        $resultado = $modelo->filtro_permiso($accion, $grupo_id, $seccion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['adm_accion_grupo.adm_grupo_id']);



        errores::$error = false;

    }

    public function test_filtro_seccion_grupo(){

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);

        $adm_grupo_id= 1;
        $adm_seccion_id= -1;
        $resultado = $modelo->filtro_seccion_grupo($adm_grupo_id, $adm_seccion_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['adm_grupo.id']);
        errores::$error = false;
    }

    public function test_grupos_id_por_accion(){

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

        $del = (new adm_elemento_lista($this->link))->elimina_todo();
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

        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'adm_seccion';
        $adm_seccion['adm_menu_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_accion['id'] = 4;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['titulo'] = 'test';
        $adm_accion['adm_seccion_id'] = '1';
        $adm_accion['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_accion_id= 4;
        $resultado = $modelo->grupos_id_por_accion($adm_accion_id);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2, $resultado[0]);

        errores::$error = false;
    }

    public function test_valida_alta_bd(){
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_accion($this->link);
        $modelo = new liberator($modelo);

        $registro = array();

        $resultado = $modelo->valida_alta_bd($registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error al validar registro', $resultado['mensaje_limpio']);

        errores::$error = false;

        $registro = array();
        $registro['adm_seccion_id'] = 1;

        $resultado = $modelo->valida_alta_bd($registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error al validar registro', $resultado['mensaje_limpio']);

        errores::$error = false;

        $registro = array();
        $registro['adm_seccion_id'] = 1;
        $registro['descripcion'] = 1;

        $resultado = $modelo->valida_alta_bd($registro);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }





}


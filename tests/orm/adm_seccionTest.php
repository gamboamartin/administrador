<?php
namespace tests\orm;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\administrador\tests\base_test;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class adm_seccionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_accion_maqueta_campo(){

        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;

        $accion = array();
        $accion_basica = array();
        $accion_basica['adm_accion_basica_a'] = 'x';
        $key = 'a';
        $resultado = $modelo->accion_maqueta_campo($accion, $accion_basica, $key);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado['a']);
        errores::$error = false;
    }

    public function test_acciones(){

        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $_SESSION['usuario_id']= 2;

        $filtro['adm_accion_basica.descripcion'] = 'a';
        $del = (new adm_accion_basica($this->link))->elimina_con_filtro_and($filtro);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_adm_seccion(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al del', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_adm_accion(link: $this->link, descripcion: 'lista');
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $modelo->acciones(1);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('lista',$resultado[0]['adm_accion_descripcion']);
        errores::$error = false;
    }

    public function test_elimina_bd(){

        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;

        $id = -1;
        $resultado = $modelo->elimina_bd($id);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error id debe se mayor a 0',$resultado['mensaje_limpio']);

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

        $del = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $id = 1;
        $resultado = $modelo->elimina_bd($id);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error al eliminar adm_seccion',$resultado['mensaje_limpio']);

        errores::$error = false;

        $registro['id'] = 1;
        $registro['descripcion'] = 'adm_grupo';
        $registro['adm_menu_id'] = '1';
        $registro['adm_namespace_id'] = '1';
        $alta = $modelo->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $id = 1;
        $resultado = $modelo->elimina_bd($id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado->registro['adm_seccion_id']);

        errores::$error = false;
    }

    public function test_modifica_bd(){

        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;


        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_seccion = array();
        $adm_seccion['id'] = 1;
        $adm_seccion['descripcion'] = 'z';
        $adm_seccion['adm_menu_id'] = '1';
        $adm_seccion['adm_namespace_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $registro = array();
        $registro['descripcion'] = 'x';
        $id = 1;
        $resultado = $modelo->modifica_bd($registro, $id);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_secciones_permitidas(){

        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $_SESSION['usuario_id']= 2;

        $filtro['adm_accion_basica.descripcion'] = 'a';
        $del = (new adm_accion_basica($this->link))->elimina_con_filtro_and($filtro);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_adm_namespace(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $alta = (new base_test())->alta_adm_seccion_pertenece(link: $this->link, adm_seccion_descripcion: 'adm_accion');
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_adm_accion(link: $this->link, descripcion: 'lista', visible: 'activo');
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $modelo->secciones_permitidas(1);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_accion',$resultado[0]['adm_seccion_descripcion']);


        errores::$error = false;
    }



}


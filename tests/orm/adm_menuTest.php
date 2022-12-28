<?php
namespace tests\orm;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\errores\errores;
use gamboamartin\test\test;



class adm_menuTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_menus_visibles_permitidos()
    {

        errores::$error = false;
        $modelo = new adm_menu($this->link);
        //$modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;

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

        $adm_accion['id'] = 1;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['adm_seccion_id'] = 1;
        $adm_accion['es_lista'] = 'inactivo';
        $adm_accion['es_status'] = 'inactivo';
        $adm_accion['visible'] = 'activo';

        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_accion['id'] = 2;
        $adm_accion['descripcion'] = 'test2';
        $adm_accion['adm_seccion_id'] = 1;
        $adm_accion['es_lista'] = 'inactivo';
        $adm_accion['es_status'] = 'inactivo';
        $adm_accion['visible'] = 'activo';

        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $modelo->menus_visibles_permitidos();

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertCount(1,$resultado);
        errores::$error = false;
    }

    public function test_menus_visibles_permitidos_full()
    {

        errores::$error = false;
        $modelo = new adm_menu($this->link);
        //$modelo = new liberator($modelo);

        $_SESSION['usuario_id'] = 2;

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

        $adm_accion['id'] = 1;
        $adm_accion['descripcion'] = 'test';
        $adm_accion['adm_seccion_id'] = 1;
        $adm_accion['es_lista'] = 'inactivo';
        $adm_accion['es_status'] = 'inactivo';
        $adm_accion['visible'] = 'activo';

        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_accion['id'] = 2;
        $adm_accion['descripcion'] = 'test2';
        $adm_accion['adm_seccion_id'] = 1;
        $adm_accion['es_lista'] = 'inactivo';
        $adm_accion['es_status'] = 'inactivo';
        $adm_accion['visible'] = 'activo';

        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_seccion_pertenece['id'] = 1;
        $adm_seccion_pertenece['adm_seccion_id'] = 1;
        $adm_seccion_pertenece['adm_sistema_id'] = 1;
        $alta = (new adm_seccion_pertenece($this->link))->alta_registro($adm_seccion_pertenece);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $modelo->menus_visibles_permitidos_full();



        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertCount(1,$resultado);
        $this->assertCount(1,$resultado[0]['adm_secciones']);
        $this->assertCount(2,$resultado[0]['adm_secciones'][0]['adm_acciones']);
        $this->assertEquals('1',$resultado[0]['adm_secciones'][0]['adm_acciones'][0]['adm_accion_id']);
        $this->assertEquals('2',$resultado[0]['adm_secciones'][0]['adm_acciones'][1]['adm_accion_id']);
        errores::$error = false;
    }

    public function test_registro(){

        errores::$error = false;
        $modelo = new adm_menu($this->link);
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

        $resultado = $modelo->registro(registro_id: 1);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['adm_menu_n_secciones']);
        errores::$error = false;
    }

    public function test_registros(){

        errores::$error = false;
        $modelo = new adm_menu($this->link);
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

        $resultado = $modelo->registros();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado[0]['adm_menu_n_secciones']);
        errores::$error = false;
    }

    public function test_secciones(){

        errores::$error = false;
        $modelo = new adm_menu($this->link);
        //$modelo = new liberator($modelo);

        $adm_menu_id= 1;
        $resultado = $modelo->secciones($adm_menu_id);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }



}


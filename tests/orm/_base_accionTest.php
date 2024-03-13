<?php
namespace tests\orm;

use gamboamartin\administrador\models\_base_accion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _base_accionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_asigna_accion_a_seccion(): void
    {

        errores::$error = false;
        $modelo = new _base_accion();
        $modelo = new liberator($modelo);

        $_SESSION = array();
        $_SESSION['usuario_id'] = 2;
        $adm_seccion['adm_seccion_id'] = '1';
        $adm_secciones = array();
        $key_seccion = '0';
        $link = $this->link;
        $resultado = $modelo->asigna_accion_a_seccion(adm_seccion: $adm_seccion,adm_secciones:  $adm_secciones, key_seccion: $key_seccion, link: $link);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_adm_secciones_permitidas(): void
    {

        errores::$error = false;
        $modelo = new _base_accion();
        $modelo = new liberator($modelo);

        $_SESSION = array();
        $_SESSION['usuario_id'] = 2;
        $adm_menu = array();
        $adm_menu['adm_menu_id'] = 1;
        $link = $this->link;
        $resultado = $modelo->adm_secciones_permitidas($adm_menu, $link);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_filtro_menu_visible(): void
    {

        errores::$error = false;
        $modelo = new _base_accion();
        $modelo = new liberator($modelo);


        $adm_grupo_id= 1;
        $id= 1;
        $table_filtro = 'a';
        $resultado = $modelo->filtro_menu_visible($adm_grupo_id, $id, $table_filtro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['a.id']);


        errores::$error = false;

    }

    public function test_filtro_menu_visible_permitido(): void
    {

        errores::$error = false;
        $modelo = new _base_accion();
        $modelo = new liberator($modelo);

        $_SESSION = array();
        $_SESSION['usuario_id'] = 2;
        $resultado = $modelo->filtro_menu_visible_permitido($this->link);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('inactivo',$resultado['adm_accion.es_lista']);
        $this->assertEquals('inactivo',$resultado['adm_accion.es_status']);
        $this->assertEquals('activo',$resultado['adm_accion.visible']);


        errores::$error = false;
    }

    public function test_init_css(): void
    {

        errores::$error = false;
        $modelo = new _base_accion();
        $modelo = new liberator($modelo);


        $registro= array();
        $registro_previo= new stdClass();
        $registro_previo->adm_accion_css = 'x';
        $resultado = $modelo->init_css($registro, $registro_previo, 'adm_accion');


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado['css']);


        errores::$error = false;
    }

    public function test_menus_visibles_permitidos(): void
    {

        errores::$error = false;
        $modelo = new _base_accion();
        //$modelo = new liberator($modelo);

        $_SESSION = array();
        $_SESSION['usuario_id'] = 2;
        $table = 'adm_menu';
        $resultado = $modelo->menus_visibles_permitidos($this->link, $table);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }



}


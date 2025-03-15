<?php
namespace gamboamartin\administrador\tests\base\controller;

use base\controller\controlador_base;
use gamboamartin\administrador\instalacion\instalacion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\administrador\models\adm_year;
use gamboamartin\administrador\tests\base_test;
use gamboamartin\controllers\controlador_adm_estado_civil;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class controlador_baseTest extends test {
    public errores $errores;
    public stdClass $paths_conf;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/administrador/config/generales.php';
        $this->paths_conf->database = '/var/www/html/administrador/config/database.php';
        $this->paths_conf->views = '/var/www/html/administrador/config/views.php';
    }

    public function test_a_menu(): void
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;

        $del = (new adm_accion($this->link))->elimina_todo();
        if (errores::$error) {
            $error = (new errores())->error('Error al eliminar accion', $del);
            print_r($error);
            die('Error');
        }

        $instala = (new instalacion())->instala(link: $this->link);
        if (errores::$error) {
            $error = (new errores())->error('Error al instala', $instala);
            print_r($error);
            die('Error');
        }



        $del = (new adm_usuario(link: $this->link))->elimina_todo();
        if (errores::$error) {
            $error = (new errores())->error('Error al eliminar usuario', $del);
            print_r($error);
            die('Error');
        }

        $usuario_ins['id'] = 2;
        $usuario_ins['adm_grupo_id'] = 2;
        $usuario_ins['user'] = 1;
        $usuario_ins['password'] = 2;
        $usuario_ins['email'] = 'a@a.com';
        $usuario_ins['telefono'] = 1235487596;
        $usuario_ins['nombre'] = 1235487596;
        $usuario_ins['ap'] = 1235487596;
        $usuario_ins['codigo'] = 1235487596;
        $r_alta_usuario = (new adm_usuario($this->link))->alta_registro($usuario_ins);
        if (errores::$error) {
            $error = (new errores())->error('Error al dar de alta usuario', $r_alta_usuario);
            print_r($error);
            die('Error');
        }

        $del = (new adm_elemento_lista($this->link))->elimina_todo();
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

        $modelo = new adm_year($this->link);


        $_POST = array();
        $_POST['id'] = 1;
        $_POST['codigo'] = 1;
        $_POST['descripcion'] = 1;
        $ctl = new controlador_base(link: $this->link, modelo: $modelo,paths_conf:$this->paths_conf );
        $ctl = new liberator($ctl);
        $ctl->seccion = 'a';
        $ctl->registro_id = '1';

        $menu = array();
        $menu['adm_menu_titulo'] = 'a';
        $menu['adm_menu_id'] = '1';
        $resultado = $ctl->a_menu($menu);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a href='index.php?seccion=adm_session&accion=inicio&session_id=1&adm_menu_id=1'>a</a>",$resultado);
        errores::$error = false;

    }

    public function test_activa_bd(): void
    {
        $_SESSION['usuario_id'] = 2;

        $alta = (new base_test())->alta_adm_accion(link: $this->link,adm_seccion_descripcion: 'adm_session',
            descripcion: 'login');
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);exit;
        }
        errores::$error = false;


        $_GET['registro_id'] = 1;
        $_GET['seccion'] = 'adm_session';
        $_GET['accion'] = 'login';
        $modelo = new adm_year($this->link);


        $ctl = new controlador_base(link: $this->link, modelo: $modelo,paths_conf:$this->paths_conf );
        //$ctl = new liberator($ctl);

        $resultado = $ctl->activa_bd(header: false);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_alta(): void
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_year($this->link);

        $alta = (new base_test())->alta_adm_accion(link: $this->link,adm_seccion_descripcion: 'adm_session',
            descripcion: 'login');
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);exit;
        }

        $ctl = new controlador_base(link: $this->link, modelo: $modelo,paths_conf:$this->paths_conf );
        //$ctl = new liberator($ctl);

        $resultado = $ctl->alta(false);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_alta_bd(): void
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_year($this->link);

        $del = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);exit;
        }

        $_POST = array();
        $_POST['id'] = 1;
        $_POST['codigo'] = 1;
        $_POST['descripcion'] = 1;
        $ctl = new controlador_base(link: $this->link, modelo: $modelo,paths_conf:$this->paths_conf );
        //$ctl = new liberator($ctl);
        $ctl->seccion = 'a';
        $ctl->registro_id = '1';

        $resultado = $ctl->alta_bd(false, false);
        

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_year($this->link);

        $del = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);exit;
        }

        $_POST = array();
        $_POST['id'] = 1;
        $_POST['codigo'] = 1;
        $_POST['descripcion'] = 1;
        $ctl = new controlador_base(link: $this->link, modelo: $modelo,paths_conf:$this->paths_conf );
        //$ctl = new liberator($ctl);
        $ctl->seccion = 'a';
        $ctl->registro_id = '1';

        $resultado = $ctl->alta_bd(false, false);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_elimina_bd(): void
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_year($this->link);

        $del = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);exit;
        }

        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $alta = $modelo->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);exit;
        }

        $ctl = new controlador_base(link: $this->link, modelo: $modelo,paths_conf:$this->paths_conf );
        //$ctl = new liberator($ctl);
        $ctl->seccion = 'a';
        $ctl->registro_id = '1';

        $resultado = $ctl->elimina_bd(false,false);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_get_data_descripcion(): void
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;


        $ctl = new controlador_adm_estado_civil(link: $this->link, paths_conf: $this->paths_conf);
        //$ctl = new liberator($ctl);

        $resultado = $ctl->get_data_descripcion(false, false);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_href_menu(): void
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = 1;
        $modelo = new adm_year($this->link);


        $_POST = array();
        $_POST['id'] = 1;
        $_POST['codigo'] = 1;
        $_POST['descripcion'] = 1;
        $ctl = new controlador_base(link: $this->link, modelo: $modelo,paths_conf:$this->paths_conf );
        $ctl = new liberator($ctl);
        $ctl->seccion = 'a';
        $ctl->registro_id = '1';

        $menu = array();
        $menu['adm_menu_id'] = 1;
        $resultado = $ctl->href_menu($menu);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('index.php?seccion=adm_session&accion=inicio&session_id=1&adm_menu_id=1',$resultado);

        errores::$error = false;


    }

    public function test_modifica(): void
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_year($this->link);
        $ctl = new controlador_base(link: $this->link, modelo: $modelo,paths_conf:$this->paths_conf );
        //$ctl = new liberator($ctl);
        $ctl->seccion = 'a';
        $ctl->registro_id = '1';

        $del = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);exit;
        }

        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $alta = $modelo->alta_registro($registro);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);exit;
        }

        $resultado = $ctl->modifica(false);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;



    }

    public function test_transaccion_previa(): void
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_year($this->link);
        $ctl = new controlador_base(link: $this->link, modelo: $modelo,paths_conf:$this->paths_conf );
        $ctl = new liberator($ctl);


        $resultado = $ctl->transaccion_previa();


        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;

        $this->link->beginTransaction();
        $resultado = $ctl->transaccion_previa();
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

    }






    }
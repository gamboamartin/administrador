<?php
namespace tests\base\controller;

use base\controller\controlador_base;
use base\controller\controler;
use gamboamartin\administrador\models\adm_atributo;
use gamboamartin\administrador\models\adm_year;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class controlador_baseTest extends test {
    public errores $errores;
    public stdClass $paths_conf;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/administrador/config/generales.php';
        $this->paths_conf->database = '/var/www/html/administrador/config/database.php';
        $this->paths_conf->views = '/var/www/html/administrador/config/views.php';
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

    public function test_modifica(): void
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $modelo = new adm_year($this->link);
        $ctl = new controlador_base(link: $this->link, modelo: $modelo,paths_conf:$this->paths_conf );
        //$ctl = new liberator($ctl);
        $ctl->seccion = 'a';
        $ctl->registro_id = '1';

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
        //$ctl = new liberator($ctl);


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
<?php
namespace controllers;

use base\controller\valida_controller;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\tests\base_test;
use gamboamartin\controllers\controlador_adm_mes;
use gamboamartin\controllers\controlador_adm_session;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class valida_controllerTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/administrador/config/generales.php';
        $this->paths_conf->database = '/var/www/html/administrador/config/database.php';
        $this->paths_conf->views = '/var/www/html/administrador/config/views.php';
    }

    public function test_valida_transaccion_status(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $_GET['seccion'] = 'adm_session';
        $_GET['accion'] = 'login';

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al $del', $del);
            print_r($error);exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al $del', $del);
            print_r($error);exit;
        }

        $alta = (new base_test())->alta_adm_accion(link: $this->link,adm_seccion_descripcion: 'adm_session',
            descripcion: 'login');
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);exit;
        }

        errores::$error = false;
        $_SESSION['usuario_id'] = 2;

        $val = new valida_controller();


        $ctl = new controlador_adm_mes(link:$this->link, paths_conf: $this->paths_conf);
        $ctl->registro_id = 1;


        $resultado = $val->valida_transaccion_status($ctl);

        $this->assertFalse(errores::$error);
        $this->assertIsBool($resultado);

        errores::$error = false;

    }



}


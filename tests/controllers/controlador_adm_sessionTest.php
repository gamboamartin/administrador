<?php
namespace tests\controllers;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\controllers\controlador_adm_session;
use gamboamartin\errores\errores;
use gamboamartin\test\test;
use stdClass;


class controlador_adm_sessionTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/administrador/config/generales.php';
        $this->paths_conf->database = '/var/www/html/administrador/config/database.php';
        $this->paths_conf->views = '/var/www/html/administrador/config/views.php';
    }

    public function test_denegado(): void
    {

        errores::$error = false;
        $ctl = new controlador_adm_session(link:$this->link, paths_conf: $this->paths_conf);
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
        $adm_seccion['descripcion'] = 'adm_session';
        $adm_seccion['adm_menu_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $adm_accion['id'] = 1;
        $adm_accion['descripcion'] = 'denegado';
        $adm_accion['titulo'] = 'denegado';
        $adm_accion['adm_seccion_id'] = '1';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        unset($_SESSION['grupo_id']);
        $resultado = $ctl->denegado(header: false);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Acceso denegado', $resultado['mensaje']);
        errores::$error = false;

        $url = 'http://localhost/administrador/index.php?seccion=adm_session&accion=denegado';

        $curl = curl_init();

        $opciones = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true
        );

        curl_setopt_array($curl, $opciones);

        $resultado  = curl_exec($curl);


        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('[error] => 1', $resultado);
        $this->assertStringContainsStringIgnoringCase('[mensaje] => <b><span style="color:red">Acceso denegado</span></b>', $resultado);
        $this->assertStringContainsStringIgnoringCase('[mensaje_limpio] => Acceso denegado', $resultado);

        errores::$error = false;

        $url = 'http://localhost/administrador/index.php?seccion=adm_session&accion=denegado';

        $curl = curl_init();

        $opciones = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true
        );

        curl_setopt_array($curl, $opciones);

        $resultado  = curl_exec($curl);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase('[error] => 1', $resultado);
        $this->assertStringContainsStringIgnoringCase('[mensaje] => <b><span style="color:red">Acceso denegado</span></b>', $resultado);
        $this->assertStringContainsStringIgnoringCase('[class] => <b>gamboamartin\controllers\controlador_adm_session</b>', $resultado);

        errores::$error = false;

        $url = 'http://localhost/administrador/index.php?seccion=adm_session&accion=denegado&ws=1';

        $curl = curl_init();

        $opciones = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true
        );

        curl_setopt_array($curl, $opciones);

        $resultado  = curl_exec($curl);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase('{"error":1,"mensaje":"<b><span style=\"c', $resultado);
        $this->assertStringContainsStringIgnoringCase('style=\"color:red\">Acceso denegado<\/span>', $resultado);
        $this->assertStringContainsStringIgnoringCase('negado<\/span><\/b>","mensaje_limpio":"Ac', $resultado);
        $this->assertStringContainsStringIgnoringCase('limpio":"Acceso denegado","file":"<b>\/var\/www\/html\/ad', $resultado);
        $this->assertStringContainsStringIgnoringCase('tml\/administrador\/controllers\/controlador_adm_sessio', $resultado);


        errores::$error = false;
    }







}


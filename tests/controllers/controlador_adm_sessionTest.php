<?php
namespace tests\controllers;

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

        unset($_SESSION['grupo_id']);

        $resultado = $ctl->denegado(header: false);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Acceso denegado', $resultado['mensaje']);
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

        //print_r($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('{"error":1,"mensaje":"<b><span style=\"color:red\">Acceso denegado<\/span><\/b>', $resultado);
        $this->assertStringContainsStringIgnoringCase('Acceso denegado<\/span><\/b>","file":"<b>\/var\/www\/html\/administr', $resultado);
        $this->assertStringContainsStringIgnoringCase('ion":"<b>denegado<\/b>","data":[],"params":[],"fix":""}', $resultado);

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
    }







}


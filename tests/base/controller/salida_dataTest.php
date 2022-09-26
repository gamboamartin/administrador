<?php
namespace tests\base\controller;

use base\controller\salida_data;
use base\controller\upd;
use gamboamartin\controllers\controlador_adm_mes;
use gamboamartin\controllers\controlador_adm_session;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_mes;
use stdClass;


class salida_dataTest extends test {
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

    /**
     */
    public function test_salida(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $sd = new salida_data();
        $sd = new liberator($sd);

        $header = false;
        $result = array();
        $ws = false;
        $resultado = $sd->salida($header, $result, $ws);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    /**
     */
    public function test_salida_get(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $sd = new salida_data();
        //$sd = new liberator($sd);

        $header = false;
        $controler =new controlador_adm_session(link: $this->link,paths_conf: $this->paths_conf);
        $ws = false;
        $filtro = array();
        $resultado = $sd->salida_get($controler, $filtro, $header, $ws);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }




}
<?php
namespace tests\base\controller;

use base\controller\upd;
use gamboamartin\controllers\controlador_adm_mes;
use gamboamartin\errores\errores;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class updTest extends test {
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
    public function test_asigna_datos_modifica(): void
    {
        errores::$error = false;
        $_SESSION['usuario_id'] = 1;
        $upd = new upd();

        $controler = new controlador_adm_mes(link: $this->link, paths_conf: $this->paths_conf);
        $controler->seccion = 'a';
        $controler->registro_id = 5;
        $resultado = $upd->asigna_datos_modifica($controler);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(5, $resultado['adm_mes_id']);
        errores::$error = false;
    }




}
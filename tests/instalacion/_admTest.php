<?php
namespace tests\controllers;

use gamboamartin\administrador\instalacion\_adm;
use gamboamartin\administrador\instalacion\instalacion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _admTest extends test {
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

    public function test_pr_tipo_proceso(): void
    {

        errores::$error = false;
        $_SESSION['usuario_id'] = 2;


        $_adm = new _adm();
        $_adm = new liberator($_adm);
        $codigo = 'a';
        $resultado = $_adm->pr_tipo_proceso($codigo);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado['codigo']);
        $this->assertEquals('a',$resultado['descripcion']);
        errores::$error = false;

    }



}


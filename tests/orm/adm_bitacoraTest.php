<?php
namespace gamboamartin\administrador\tests\orm;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class adm_bitacoraTest extends test {
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

    public function test_alta_bd(){

        errores::$error = false;
        $_SESSION['usuario_id'] = 1;
        $modelo = new adm_bitacora($this->link);
        //$modelo = new liberator($modelo);

        $modelo->registro['adm_seccion_id'] = 1;
        $modelo->registro['registro'] = 1;
        $modelo->registro['adm_usuario_id'] = 2;
        $modelo->registro['transaccion'] = 1;
        $modelo->registro['sql_data'] = 1;
        $modelo->registro['valor_id'] = 1;
        $resultado = $modelo->alta_bd();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }




}


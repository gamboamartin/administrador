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
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
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


}


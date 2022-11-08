<?php
namespace tests\orm;

use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class adm_seccion_perteneceTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_init_alta_bd(){

        errores::$error = false;
        $modelo = new adm_seccion_pertenece($this->link);
        $modelo = new liberator($modelo);

        $_SESSION['usuario_id']= 2;

        $registro = array();
        $registro['adm_seccion_id'] = 1;
        $registro['adm_sistema_id'] = 1;
        $resultado = $modelo->init_alta_bd($registro);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_accion',$resultado->adm_seccion->descripcion);


        errores::$error = false;
    }



}


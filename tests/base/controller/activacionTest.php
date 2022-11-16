<?php
namespace tests\base\controller;

use base\controller\activacion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\errores\errores;
use gamboamartin\test\test;



class activacionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_activa_bd_base(): void
    {

        errores::$error = false;

        $act = new activacion();
        //$html = new liberator($html);


        $modelo = new adm_mes($this->link);
        $registro_id = 1;
        $seccion = '';
        $resultado = $act->activa_bd_base(modelo: $modelo,registro_id:  $registro_id, seccion: $seccion);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }



}
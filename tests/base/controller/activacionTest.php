<?php
namespace tests\base\controller;

use base\controller\activacion;
use base\controller\base_html;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_accion;


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


        $modelo = new adm_accion($this->link);
        $registro_id = 1;
        $seccion = '';
        $resultado = $act->activa_bd_base($modelo, $registro_id, $seccion);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }



}
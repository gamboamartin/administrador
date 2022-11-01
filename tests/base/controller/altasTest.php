<?php
namespace tests\base\controller;

use base\controller\activacion;
use base\controller\altas;
use base\controller\base_html;
use base\controller\controler;
use gamboamartin\controllers\controlador_adm_mes;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_accion;
use models\adm_mes;


class altasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_alta_base(): void
    {

        errores::$error = false;

        $al = new altas();
        //$html = new liberator($html);

        $_SESSION['usuario_id'] = 1;

        $controler = new controler($this->link);
        $controler->seccion = 'adm_mes';
        $controler->tabla = 'adm_mes';
        $controler->modelo = new adm_mes($this->link);
        $registro = array();
        $registro['codigo'] = mt_rand(0,99999999);
        $registro['descripcion'] = mt_rand(0,99999999);
        $resultado = $al->alta_base($registro, $controler);


        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }



}
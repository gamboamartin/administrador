<?php
namespace tests\base\controller;

use base\controller\controler;
use base\controller\filtros;
use base\controller\not_in;
use gamboamartin\administrador\models\adm_atributo;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;



class not_inTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_not_in_post(): void
    {

        errores::$error = false;

        $ni = new not_in($this->link);
        $ni = new liberator($ni);

        $_POST['not_in']['llave'] = 'a';
        $_POST['not_in']['values'] = array('1');
        $resultado = $ni->not_in_post();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado['values'][0]);
        $this->assertEquals('a',$resultado['llave']);
        errores::$error = false;

    }
}
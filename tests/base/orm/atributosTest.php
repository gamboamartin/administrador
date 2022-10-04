<?php
namespace tests\base;

use base\controller\normalizacion;
use base\orm\activaciones;
use base\orm\atributos;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\adm_accion_grupo;
use models\adm_campo;
use models\adm_dia;
use models\atributo;


class atributosTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_atributos()
    {

        errores::$error = false;
        $attr = new atributos();
        $attr = new liberator($attr);
        $tabla = 'a';
        $resultado = $attr->atributos($this->link, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }

    public function test_class_attr()
    {

        errores::$error = false;
        $attr = new atributos();
        $attr = new liberator($attr);
        $tabla = 'a';
        $resultado = $attr->class_attr($tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('models\attr_a',$resultado);
        errores::$error = false;
    }

    public function test_valida_attr()
    {

        errores::$error = false;
        $attr = new atributos();
        $attr = new liberator($attr);
        $atributo = array();
        $keys = array();
        $registro_id = 1;
        $atributo['atributo_id'] = 1;

        $resultado = $attr->valida_attr($atributo, $keys, $registro_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        errores::$error = false;
    }




}
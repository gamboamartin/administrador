<?php
namespace tests\base;


use base\orm\atributos;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;




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

    public function test_data_inst_attr()
    {

        errores::$error = false;
        $attr = new atributos();
        $attr = new liberator($attr);
        $atributo = array();
        $modelo = new adm_campo($this->link);
        $registro_id = 1;
        $atributo['adm_atributo_descripcion'] = 'x';
        $atributo['adm_atributo_id'] = '1';
        $resultado = $attr->data_inst_attr($atributo, $modelo, $registro_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('x',$resultado['descripcion']);
        $this->assertEquals('activo',$resultado['status']);
        $this->assertEquals('1',$resultado['adm_atributo_id']);
        $this->assertEquals('1',$resultado['adm_campo_id']);
        $this->assertEquals('',$resultado['valor']);
        errores::$error = false;
    }

    public function test_ejecuta_insersion_attr()
    {

        errores::$error = false;
        $_SESSION['usuario_id'] = 1;
        $attr = new atributos();
        //$attr = new liberator($attr);
        $registro_id = 1;


        $modelo = new adm_accion_grupo($this->link);


        $resultado = $attr->ejecuta_insersion_attr($modelo, $registro_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        errores::$error = false;

    }

    public function test_inserta_atributo()
    {

        errores::$error = false;
        $_SESSION['usuario_id'] = 1;
        $attr = new atributos();
        $attr = new liberator($attr);
        $atributo = array();
        $keys = array();
        $registro_id = 1;
        $atributo['adm_atributo_id'] = 1;
        $atributo['adm_atributo_descripcion'] = 1;

        $modelo_base = new adm_accion_grupo($this->link);
        $tabla = 'adm_seccion';

        $resultado = $attr->inserta_atributo($atributo, $modelo_base, $registro_id, $tabla);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }

    public function test_inserta_atributos()
    {

        errores::$error = false;
        $_SESSION['usuario_id'] = 1;
        $attr = new atributos();
        $attr = new liberator($attr);
        $registro_id = 1;


        $modelo = new adm_accion_grupo($this->link);
        $tabla_attr = 'adm_seccion';

        $resultado = $attr->inserta_atributos($modelo, $registro_id, $tabla_attr);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }

    public function test_inserta_data_attr()
    {

        errores::$error = false;
        $_SESSION['usuario_id'] = 1;
        $attr = new atributos();
        $attr = new liberator($attr);
        $registro_id = 1;


        $modelo = new adm_accion_grupo($this->link);
        $clase_attr = 'adm_seccion';

        $resultado = $attr->inserta_data_attr($clase_attr, $modelo, $registro_id);

        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
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
        $atributo['adm_atributo_id'] = 1;

        $resultado = $attr->valida_attr($atributo, $keys, $registro_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        errores::$error = false;
    }




}
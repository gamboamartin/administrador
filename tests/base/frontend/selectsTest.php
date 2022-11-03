<?php
namespace tests\base\frontend;

use base\frontend\selects;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class selectsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }


    public function test_data_for_select(): void{
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);

        $valor = '';
        $tabla = '';
        $data_extra = array();
        $data_con_valor = array();
        $columnas = array();
        $resultado = $sl->data_for_select($columnas,$data_con_valor, $data_extra,$tabla,$valor);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);


        errores::$error = false;
    }


    public function test_obten_registros_select(): void{
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);
        $filtro = array();
        $link = $this->link;
        $name_modelo = 'models\\adm_menu';
        $select_vacio_alta = false;
        $resultado = $sl->obten_registros_select($filtro, $link, $name_modelo, $select_vacio_alta);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_registros_activos(){
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);

        $filtro = array();
        $name_modelo = 'models\\adm_mes';
        $link = $this->link;


        $resultado = $sl->registros_activos($filtro, $link, $name_modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);


    }

    public function test_registros_for_select(): void{
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);
        $filtro = array();
        $link = $this->link;

        $select_vacio_alta = false;
        $datos = new stdClass();
        $registros = array();
        $todos = true;
        $tabla = 'models\\adm_seccion';
        $datos->tabla = 'x';
        $resultado = $sl->registros_for_select( $filtro, $link, $registros, $tabla);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_registros_select(): void{
        errores::$error = false;
        $sl = new selects();
        $sl = new liberator($sl);
        $filtro = array();
        $link = $this->link;
        $name_modelo = 'models\\adm_accion';
        $registros = array();
        $select_vacio_alta = false;
        $todos = false;
        $resultado = $sl->registros_select($filtro, $link, $name_modelo, $registros, $select_vacio_alta, $todos);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }




}
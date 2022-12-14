<?php
namespace tests\orm;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class adm_basicaTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_alta_bd(){

        errores::$error = false;
        $_SESSION['usuario_id'] = 1;
        $modelo = new adm_accion_basica($this->link);
        //$modelo = new liberator($modelo);

        $del = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar',data:  $del);
            print_r($error);
            exit;
        }

        $modelo->registro['descripcion'] = 'a';
        $modelo->registro['codigo'] = 'b';
        $modelo->registro['muestra_icono_btn'] = 'activo';
        $modelo->registro['muestra_titulo_btn'] = 'activo';
        $resultado = $modelo->alta_bd();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_etiqueta_label(){

        errores::$error = false;
        $_SESSION['usuario_id'] = 1;
        $modelo = new adm_accion_basica($this->link);
        $modelo = new liberator($modelo);

        $registro = array();
        $registro['descripcion'] = 'zzz';
        $resultado = $modelo->etiqueta_label($registro);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Zzz',$resultado);
        errores::$error = false;

    }

    public function test_init_etiqueta_label(){

        errores::$error = false;
        $_SESSION['usuario_id'] = 1;
        $modelo = new adm_accion_basica($this->link);
        $modelo = new liberator($modelo);

        $registro = array();
        $registro['descripcion'] = 'x';

        $resultado = $modelo->init_etiqueta_label($registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado['descripcion']);
        $this->assertEquals('X',$resultado['etiqueta_label']);
        errores::$error = false;

    }



}


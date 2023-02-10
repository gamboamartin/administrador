<?php
namespace gamboamartin\administrador\tests\orm;


use gamboamartin\administrador\models\adm_tipo_dato;
use gamboamartin\administrador\tests\base_test;
use gamboamartin\errores\errores;
use gamboamartin\test\test;



class adm_tipo_datoTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_alta_bd(){

        errores::$error = false;
        $modelo = new adm_tipo_dato($this->link);
        //$modelo = new liberator($modelo);

        $_SESSION['usuario_id']= 2;

        $del = (new base_test())->del_adm_tipo_dato(link: $this->link);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al eliminar',data:  $del);
            print_r($error);
            exit;
        }

        $modelo->registro = array();
        $modelo->registro['descripcion'] = 'a';
        $resultado = $modelo->alta_bd();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_modifica_bd()
    {

        errores::$error = false;
        $modelo = new adm_tipo_dato($this->link);
        //$modelo = new liberator($modelo);
        $modelo->usuario_id = 2;

        $_SESSION['usuario_id'] = 2;

        $del = (new base_test())->del_adm_tipo_dato(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar',data: $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_adm_tipo_dato(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar',data: $alta);
            print_r($error);
            exit;
        }

        $registro = array();
        $registro['descripcion'] = 'a';
        $resultado = $modelo->modifica_bd(registro: $registro, id: 1);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }





}


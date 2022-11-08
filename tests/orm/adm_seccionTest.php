<?php
namespace tests\orm;

use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class adm_seccionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_secciones_permitidas(){

        errores::$error = false;
        $modelo = new adm_seccion($this->link);
        //$modelo = new liberator($modelo);

        $_SESSION['usuario_id']= 2;

        $del = (new adm_seccion_pertenece($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_sistema($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $sistema_ins['id'] = 1;
        $sistema_ins['descripcion'] = 'administrador';
        $sistema_ins['codigo'] = 'administrador';
        $alta = (new adm_sistema($this->link))->alta_registro($sistema_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $seccion_pertenece_ins['id'] = 1;
        $seccion_pertenece_ins['adm_seccion_id'] = 1;
        $seccion_pertenece_ins['adm_sistema_id'] = 1;
        $alta = (new adm_seccion_pertenece($this->link))->alta_registro($seccion_pertenece_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $resultado = $modelo->secciones_permitidas();

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_accion',$resultado[0]['adm_seccion_descripcion']);


        errores::$error = false;
    }



}


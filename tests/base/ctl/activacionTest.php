<?php
namespace tests\base\ctl;

use gamboamartin\administrador\ctl\activacion;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class activacionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_activa_bd_base(): void
    {

        errores::$error = false;

        $act = new activacion();
        //$html = new liberator($html);

        $_SESSION['usuario_id'] = 1;

        $del = (new adm_mes($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $adm_mes['id'] = 1;
        $adm_mes['descripcion'] = '1';
        $adm_mes['codigo'] = '1';

        $alta = (new adm_mes($this->link))->alta_registro($adm_mes);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $modelo = new adm_mes($this->link);
        $registro_id = 1;
        $seccion = '';
        $resultado = $act->activa_bd_base(modelo: $modelo,registro_id:  $registro_id, seccion: $seccion);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }



}
<?php
namespace validacion;
use base\controller\valida_controller;

class session extends valida_controller {

    /**
     * P INT P ORDER PROBADO
     * Verifica que vengan seteados en POST los datos de user y password
     * @return array|bool
     */
    public function valida_datos_recepcion():array|bool{
        if(!isset($_POST['user'])){
            return $this->error->error(mensaje: 'Error debe existir user',data: $_POST);

        }
        if(!isset($_POST['password'])){
            return $this->error->error(mensaje: 'Error debe existir password',data: $_POST);
        }
        return true;
    }
}

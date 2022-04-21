<?php
namespace validacion;
use base\controller\valida_controller;

class session extends valida_controller {

    /**
     * P INT P ORDER PROBADO
     * @return array|bool
     */
    public function valida_datos_recepcion():array|bool{
        if(!isset($_POST['user'])){
            return $this->error->error('Error debe existir user',$_POST);

        }
        if(!isset($_POST['password'])){
            return $this->error->error('Error debe existir password',$_POST);
        }
        return true;
    }
}

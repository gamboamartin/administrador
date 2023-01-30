<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 *
 */
namespace validacion;
use base\controller\valida_controller;

/**
 * Verifica datos para inicios de session
 */
class session extends valida_controller {

    /**
     * Verifica que vengan seteados en POST los datos de user y password
     * @version 1.0.0
     * @return array|bool
     */
    final public function valida_datos_recepcion():array|bool{
        if(!isset($_POST['user'])){
            return $this->error->error(mensaje: 'Error debe existir user',data: $_POST);

        }
        if(!isset($_POST['password'])){
            return $this->error->error(mensaje: 'Error debe existir password',data: $_POST);
        }
        return true;
    }
}

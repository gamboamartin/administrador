<?php
namespace validacion;
use base\controller\valida_controller;

class accion extends valida_controller {

    /**
     * Valida los datos de entrada de una accion para validar permisos
     * @param string $accion Accion a ejecutar
     * @param string $seccion Seccion a ejecutar
     * @return bool|array
     */
   final public function valida_accion_permitida(string $accion, string $seccion): bool|array
   {
       if($seccion === ''){
           return $this->error->error(mensaje: 'Error $seccion debe tener info',data: $seccion);
       }
       if($accion === ''){
           return $this->error->error(mensaje:'Error $accion debe tener info',data:$accion);
       }
       if(!isset($_SESSION['grupo_id'])){
           return $this->error->error(mensaje:'Error debe existir grupo_id en SESSION',data:$_SESSION);
       }
       return true;
   }
}

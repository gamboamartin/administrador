<?php
namespace validacion;
use base\controller\valida_controller;

class accion extends valida_controller {

    /**
     * P ORDER P INT PROBADO ERRORREV
     * @param string $accion
     * @param string $seccion
     * @return bool|array
     */
   public function valida_accion_permitida(string $accion, string $seccion): bool|array
   {
       if($seccion === ''){
           return $this->error->error(mensaje: 'Error $seccion debe tener info',data: $seccion,
               params: get_defined_vars());
       }
       if($accion === ''){
           return $this->error->error(mensaje:'Error $accion debe tener info',data:$accion, params: get_defined_vars());
       }
       if(!isset($_SESSION['grupo_id'])){
           return $this->error->error(mensaje:'Error debe existir grupo_id en SESSION',data:$_SESSION,
               params: get_defined_vars());
       }
       return true;
   }
}

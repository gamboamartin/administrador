<?php
namespace validacion;
use base\controller\valida_controller;

class accion extends valida_controller {

    /**
     * P ORDER P INT PROBADO
     * @param string $accion
     * @param string $seccion
     * @return bool|array
     */
   public function valida_accion_permitida(string $accion, string $seccion): bool|array
   {
       if($seccion === ''){
           return $this->error->error('Error $seccion debe tener info',$seccion);
       }
       if($accion === ''){
           return $this->error->error('Error $accion debe tener info',$accion);
       }
       if(!isset($_SESSION['grupo_id'])){
           return $this->error->error('Error debe existir grupo_id en SESSION',$_SESSION);
       }
       return true;
   }
}

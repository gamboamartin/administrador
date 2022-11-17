<?php
namespace gamboamartin\administrador\models;
use base\orm\_modelo_parent;
use gamboamartin\errores\errores;

use PDO;

class adm_hora extends _modelo_parent {
    public function __construct(PDO $link){
        $tabla = 'adm_hora';
        $columnas = array($tabla=>false);
        parent::__construct(link: $link,tabla:  $tabla,columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }
    public function hoy(){
        $hora = date('H');
        $filtro['hora.codigo'] = $hora;
        $r_hora = $this->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener dia', $r_hora);
        }
        if((int)$r_hora['n_registros'] === 0){
            return $this->error->error('Error no existe dia', $r_hora);
        }
        if((int)$r_hora['n_registros'] > 1){
            return $this->error->error('Error  existe mas de un dia', $r_hora);
        }
        return $r_hora['registros'][0];
    }
}
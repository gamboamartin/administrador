<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;

use PDO;

class adm_hora extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        parent::__construct(link: $link,tabla:  $tabla,columnas: $columnas);
    }
    public function hoy(){
        $hora = date('H');
        $filtro['hora.codigo'] = $hora;
        $r_hora = $this->filtro_and($filtro);
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
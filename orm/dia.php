<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class dia extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        parent::__construct(link: $link,tabla:  $tabla,columnas: $columnas);
    }
    public function hoy(){
        $dia = date('d');
        $filtro['dia.codigo'] = $dia;
        $r_dia = $this->filtro_and($filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener dia', $r_dia);
        }
        if((int)$r_dia['n_registros'] === 0){
            return $this->error->error('Error no existe dia', $r_dia);
        }
        if((int)$r_dia['n_registros'] > 1){
            return $this->error->error('Error  existe mas de un dia', $r_dia);
        }
        return $r_dia['registros'][0];
    }
}
<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class adm_dia extends modelo{
    public function __construct(PDO $link){
        $tabla = 'adm_dia';
        $columnas = array($tabla=>false);
        parent::__construct(link: $link,tabla:  $tabla,columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }
    public function hoy(){
        $dia = date('d');
        $filtro['adm_dia.codigo'] = $dia;
        $r_dia = $this->filtro_and(filtro: $filtro);
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
<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class year extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas);
    }


    public function hoy(){
        $year = date('Y');
        $filtro['year.codigo'] = $year;
        $r_year = $this->filtro_and($filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener year', $r_year);
        }
        if((int)$r_year['n_registros'] === 0){
            return $this->error->error('Error no existe year', $r_year);
        }
        if((int)$r_year['n_registros'] > 1){
            return $this->error->error('Error  existe mas de un year', $r_year);
        }
        return $r_year['registros'][0];
    }
}
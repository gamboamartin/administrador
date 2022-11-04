<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class adm_year extends modelo{
    public function __construct(PDO $link){
        $tabla = 'adm_year';
        $columnas = array($tabla=>false);
        parent::__construct(link: $link, tabla: $tabla, columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }


    public function hoy(){
        $year = date('Y');
        $filtro['year.codigo'] = $year;
        $r_year = $this->filtro_and(filtro:$filtro);
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
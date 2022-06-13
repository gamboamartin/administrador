<?php
namespace models;
use gamboamartin\errores\errores;
use base\orm\modelo;

use PDO;

class adm_minuto extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios = array(),
            columnas: $columnas, tipo_campos: $tipo_campos = array());
    }
    public function hoy(){
        $minuto = date('i');
        $filtro['minuto.codigo'] = $minuto;
        $r_minuto = $this->filtro_and($filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener minuto', $r_minuto);
        }
        if((int)$r_minuto['n_registros'] === 0){
            return $this->error->error('Error no existe minuto', $r_minuto);
        }
        if((int)$r_minuto['n_registros'] > 1){
            return $this->error->error('Error  existe mas de un minuto', $r_minuto);
        }
        return $r_minuto['registros'][0];
    }
}
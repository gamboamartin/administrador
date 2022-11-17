<?php
namespace gamboamartin\administrador\models;
use base\orm\_modelo_parent;
use gamboamartin\errores\errores;

use PDO;

class adm_minuto extends _modelo_parent {
    public function __construct(PDO $link){
        $tabla = 'adm_minuto';
        $columnas = array($tabla=>false);
        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios = array(),
            columnas: $columnas, tipo_campos: $tipo_campos = array());
        $this->NAMESPACE = __NAMESPACE__;
    }
    public function hoy(){
        $minuto = date('i');
        $filtro['minuto.codigo'] = $minuto;
        $r_minuto = $this->filtro_and(filtro:$filtro);
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
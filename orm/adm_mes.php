<?php
namespace gamboamartin\administrador\models;
use base\orm\_modelo_parent;
use gamboamartin\errores\errores;

use PDO;

class adm_mes extends _modelo_parent {
    public function __construct(PDO $link){
        $tabla = 'adm_mes';
        $columnas = array($tabla=>false);
        $no_duplicados = array('codigo');
        parent::__construct(link: $link,tabla:  $tabla, columnas: $columnas, no_duplicados: $no_duplicados);
        $this->NAMESPACE = __NAMESPACE__;
    }

    public function hoy(){
        $mes = date('m');
        $filtro['adm_mes.codigo'] = $mes;
        $r_mes = $this->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener mes', $r_mes);
        }
        if((int)$r_mes['n_registros'] === 0){
            return $this->error->error('Error no existe mes', $r_mes);
        }
        if((int)$r_mes['n_registros'] > 1){
            return $this->error->error('Error  existe mas de un mes', $r_mes);
        }
        return $r_mes['registros'][0];
    }
}
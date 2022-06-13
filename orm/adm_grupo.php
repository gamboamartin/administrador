<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;

use PDO;

class adm_grupo extends modelo{
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();
        parent::__construct(link: $link, tabla: $tabla,campos_obligatorios: $campos_obligatorios, columnas: $columnas);
    }


    /**
     * 
     * @return array
     */
    public function grupos_root(): array
    {
        $filtro['adm_grupo.root'] = 'activo';
        $r_grupo = $this->filtro_and($filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener grupos root',$r_grupo);
        }
        return $r_grupo->registros;
    }



}

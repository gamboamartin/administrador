<?php
namespace gamboamartin\administrador\models;
use base\orm\_modelo_parent;
use gamboamartin\errores\errores;

use PDO;

class adm_grupo extends _modelo_parent {
    public function __construct(PDO $link){
        $tabla = 'adm_grupo';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array('descripcion','root','descripcion_select','codigo','codigo_bis','alias');
        parent::__construct(link: $link, tabla: $tabla,campos_obligatorios: $campos_obligatorios, columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }


    /**
     * Obtiene los grupos de tipo root
     * @return array
     * @version 2.22.2
     */
    public function grupos_root(): array
    {
        $filtro['adm_grupo.root'] = 'activo';
        $r_grupo = $this->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener grupos root',data: $r_grupo);
        }
        return $r_grupo->registros;
    }

}

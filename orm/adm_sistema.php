<?php
namespace gamboamartin\administrador\models;


use base\orm\_modelo_parent_sin_codigo;
use gamboamartin\errores\errores;
use PDO;


class adm_sistema extends _modelo_parent_sin_codigo {
    public function __construct(PDO $link, array $childrens = array()){
        $tabla = 'adm_sistema';
        $columnas = array($tabla=>false);


        $childrens['adm_seccion_pertenece'] = "gamboamartin\\administrador\\models";
        parent::__construct(link: $link,tabla:  $tabla,columnas: $columnas, childrens: $childrens);
        $this->NAMESPACE = __NAMESPACE__;

        $this->etiqueta = 'Sistema';
    }


    /**
     * Obtiene las secciones filtradas por sistema
     * @param int $adm_sistema_id Sistema de filtro
     * @return array
     * @version 2.88.6
     */
    public function secciones_pertenece(int $adm_sistema_id): array
    {
        if($adm_sistema_id <= 0){
            return $this->error->error(mensaje: 'Error adm_sistema_id debe ser mayor a 0',data:  $adm_sistema_id);
        }
        $filtro['adm_sistema.id'] = $adm_sistema_id;
        $r_adm_seccion_pertenece = (new adm_seccion_pertenece($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener secciones',data:  $r_adm_seccion_pertenece);
        }
        return $r_adm_seccion_pertenece->registros;

    }

}
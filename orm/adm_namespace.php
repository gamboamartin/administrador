<?php
namespace gamboamartin\administrador\models;


use base\orm\_modelo_parent_sin_codigo;
use gamboamartin\errores\errores;
use PDO;


class adm_namespace extends _modelo_parent_sin_codigo {
    public function __construct(PDO $link, array $childrens = array()){
        $tabla = 'adm_namespace';
        $columnas = array($tabla=>false);

        $childrens['adm_seccion'] ="gamboamartin\administrador\models";

        $columnas_extra['adm_namespace_n_secciones'] = /** @lang sql */
            "(SELECT COUNT(*) FROM adm_seccion WHERE adm_seccion.adm_namespace_id = adm_namespace.id)";

        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: array(),
            columnas: $columnas, columnas_extra: $columnas_extra, childrens: $childrens);
        $this->NAMESPACE = __NAMESPACE__;
    }


    /**
     * Obtiene las secciones de un menu
     * @param int $adm_namespace_id
     * @return array
     * @version 0.545.51
     */
    public function secciones(int $adm_namespace_id): array
    {
        if($adm_namespace_id <= 0){
            return $this->error->error(mensaje: 'Error adm_namespace_id debe ser mayor a 0',data:  $adm_namespace_id);
        }
        $filtro['adm_namespace.id'] = $adm_namespace_id;
        $r_adm_seccion = (new adm_seccion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener secciones',data:  $r_adm_seccion);
        }
        return $r_adm_seccion->registros;
    }


}
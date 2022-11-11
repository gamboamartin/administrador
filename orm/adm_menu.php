<?php
namespace gamboamartin\administrador\models;

use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class adm_menu extends modelo{
    public function __construct(PDO $link){
        $tabla = 'adm_menu';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array('etiqueta_label');

        $columnas_extra['n_secciones'] = /** @lang sql */
            "SELECT COUNT(*) FROM adm_seccion WHERE adm_seccion.adm_menu_id = adm_menu.id";

        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, columnas_extra: $columnas_extra);
        $this->NAMESPACE = __NAMESPACE__;
    }


    /**
     * Obtiene las secciones de un menu
     * @param int $adm_menu_id Menu identificador
     * @return array
     * @version 0.545.51
     */
    public function secciones(int $adm_menu_id): array
    {
        if($adm_menu_id <= 0){
            return $this->error->error(mensaje: 'Error adm_menu_id debe ser mayor a 0',data:  $adm_menu_id);
        }
        $filtro['adm_menu.id'] = $adm_menu_id;
        $r_adm_seccion = (new adm_seccion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener secciones',data:  $r_adm_seccion);
        }
        return $r_adm_seccion->registros;
    }
}
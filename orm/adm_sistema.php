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
    }



    public function secciones_pertenece(int $adm_sistema_id): array
    {
        $filtro['adm_sistema.id'] = $adm_sistema_id;
        $r_adm_seccion_pertenece = (new adm_seccion_pertenece($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener secciones',data:  $r_adm_seccion_pertenece);
        }
        return $r_adm_seccion_pertenece->registros;

    }

}
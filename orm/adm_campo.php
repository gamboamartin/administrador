<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_campo extends modelo{
    public function __construct(PDO $link){
        $tabla = 'adm_campo';
        $columnas = array($tabla=>false,'adm_seccion'=>$tabla,'adm_tipo_dato'=>$tabla);
        $campos_obligatorios = array('adm_seccion_id','adm_tipo_dato_id','es_foranea');
        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,columnas: $columnas);
        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(): array|stdClass
    {
        if(!isset($this->registro['codigo'])){
            $codigo = $this->registro['adm_seccion_id'].'-'.$this->registro['adm_tipo_dato_id'].'-'.$this->registro['descripcion'];
            $this->registro['codigo'] = $codigo;
        }
        $alta_bd = parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar campo',data:  $alta_bd);
        }
        return $alta_bd;

    }

    final public function campos_by_seccion(string $adm_seccion_descripcion)
    {
        $filtro['adm_seccion.descripcion'] = $adm_seccion_descripcion;
        $r_adm_campo = (new adm_campo(link: $this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos',data:  $r_adm_campo);
        }

        if($r_adm_campo->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existen campos',data:  $r_adm_campo);
        }
        return $r_adm_campo->registros;

    }
}
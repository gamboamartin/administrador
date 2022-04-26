<?php
namespace models;

use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class accion_grupo extends modelo{ //PRUEBAS COMPLETAS
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'accion'=>$tabla,'grupo'=>$tabla,
            'seccion'=>'accion','menu'=>'seccion');
        $campos_obligatorios = array('accion_id');
        $tipo_campos['accion_id'] = 'id';
        $tipo_campos['grupo_id'] = 'id';
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas: $columnas,
            tipo_campos:  $tipo_campos);
    }

    /**
     * P INT P ORDER
     * @param int $seccion_menu_id
     * @return array|stdClass
     */
    public function obten_accion_permitida(int $seccion_menu_id):array|stdClass{
        $keys = array('grupo_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $_SESSION);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar session',data: $valida, params: get_defined_vars());
        }
        $grupo_id = $_SESSION['grupo_id'];

        $filtro['accion.status'] = 'activo';
        $filtro['grupo.status'] = 'activo';
        $filtro['accion_grupo.grupo_id'] = $grupo_id;
        $filtro['accion.seccion_id'] = $seccion_menu_id;
        $filtro['accion.visible'] = 'activo';


        $result = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener acciones permitidas',data: $result,
                params: get_defined_vars());
        }
        return $result;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $accion_id
     * @param int $grupo_id
     * @return array|int
     */
    public function obten_permiso_id(int $accion_id, int $grupo_id):array|int{ //FIN PROT

        if($accion_id <=0){
            return $this->error->error('Error accion_id debe ser mayor a 0',$accion_id);
        }
        if($grupo_id <=0){
            return $this->error->error('Error $grupo_id debe ser mayor a 0',$grupo_id);
        }

        $filtro['accion.id'] =$accion_id;
        $filtro['grupo.id'] =$grupo_id;

        $r_accion_grupo = $this->filtro_and($filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener accion grupo',$r_accion_grupo);
        }

        if((int)$r_accion_grupo['n_registros'] !==1){
            return $this->error->error('Error al obtener accion grupo n registros incongruente',$r_accion_grupo);
        }
        $this->registro_id = (int)$r_accion_grupo['registros'][0]['accion_grupo_id'];
        return $this->registro_id;
    }


}

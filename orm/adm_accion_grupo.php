<?php
namespace models;

use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_accion_grupo extends modelo{ //PRUEBAS COMPLETAS
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'adm_accion'=>$tabla,'adm_grupo'=>$tabla,
            'adm_seccion'=>'adm_accion','adm_menu'=>'adm_seccion');
        $campos_obligatorios = array('adm_accion_id');
        $tipo_campos['adm_accion_id'] = 'id';
        $tipo_campos['adm_grupo_id'] = 'id';
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas: $columnas,
            tipo_campos:  $tipo_campos);
    }

    /**
     * P INT P ORDER ERROREV
     * @param int $seccion_menu_id
     * @return array|stdClass
     */
    public function obten_accion_permitida(int $seccion_menu_id):array|stdClass{
        $keys = array('grupo_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $_SESSION);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar session',data: $valida);
        }
        $grupo_id = $_SESSION['grupo_id'];

        $filtro['adm_accion.status'] = 'activo';
        $filtro['adm_grupo.status'] = 'activo';
        $filtro['adm_accion_grupo.adm_grupo_id'] = $grupo_id;
        $filtro['adm_accion.adm_seccion_id'] = $seccion_menu_id;
        $filtro['adm_accion.visible'] = 'activo';


        $result = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener acciones permitidas',data: $result);
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

        $filtro['adm_accion.id'] =$accion_id;
        $filtro['adm_grupo.id'] =$grupo_id;

        $r_accion_grupo = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener accion grupo',$r_accion_grupo);
        }

        if((int)$r_accion_grupo->n_registros !==1){
            return $this->error->error('Error al obtener accion grupo n registros incongruente',$r_accion_grupo);
        }
        $this->registro_id = (int)$r_accion_grupo['registros'][0]['accion_grupo_id'];
        return $this->registro_id;
    }


}

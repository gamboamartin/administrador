<?php
namespace gamboamartin\administrador\models;

use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_accion_grupo extends modelo{ //PRUEBAS COMPLETAS
    public function __construct(PDO $link){
        $tabla = 'adm_accion_grupo';
        $columnas = array($tabla=>false,'adm_accion'=>$tabla,'adm_grupo'=>$tabla,
            'adm_seccion'=>'adm_accion','adm_menu'=>'adm_seccion');
        $campos_obligatorios = array('adm_accion_id');
        $tipo_campos['adm_accion_id'] = 'id';
        $tipo_campos['adm_grupo_id'] = 'id';
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas: $columnas,
            tipo_campos:  $tipo_campos);
        $this->NAMESPACE = __NAMESPACE__;
    }

    public function acciones_por_grupo(int $adm_grupo_id): array
    {
        if($adm_grupo_id <=0 ){
            return $this->error->error(mensaje: 'Error adm_grupo_id debe ser mayor a 0',data: $adm_grupo_id);
        }
        $filtro['adm_grupo.id'] = $adm_grupo_id;
        $r_adm_grupo_accion = $this->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener r_adm_grupo_accion',data: $r_adm_grupo_accion);
        }
        return $r_adm_grupo_accion->registros;


    }

    /**
     * Obtiene los grupos configurados por accion
     * @param int $adm_accion_id Identificador de accion
     * @return array
     * @version 1.578.51
     */
    public function grupos_por_accion(int $adm_accion_id): array
    {
        if($adm_accion_id <=0 ){
            return $this->error->error(mensaje: 'Error adm_accion_id debe ser mayor a 0',data: $adm_accion_id);
        }
        $filtro['adm_accion.id'] = $adm_accion_id;
        $r_adm_grupo_accion = $this->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener r_adm_grupo_accion',data: $r_adm_grupo_accion);
        }
        return $r_adm_grupo_accion->registros;


    }

    /**
     *
     *
     * Funcion utilizada para validar permisos basado en tu grupo y seccion. Devuelve un objeto de tipo result
     * En caso de error al validar, lanzará un mensaje
     *
     * @param int $seccion_menu_id Contiene un id de grupo.
     *
     * @return array|stdClass
     *
     *@functions $valida  = adm_accion_grupo->validacion->valida_ids  Usada para validar los
     * resultados de "$keys" y "$S_SESSION". En caso de error lanzará un mensaje
     *
     *@functions $result  = adm_accion_grupo->filtro_and. obtiene los resultados de los registros
     * encontrados. En caso de error, lanzará un mensaje.
     *
     * @version 1.356.41
     *
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
     *
     * Funcion que sirve para validar si un grupo puede realizar una accion. Devuelve un dato de tipo registro
     * En caso de que "$accion_id" o "$grupo_id" tengan un error, devolverá un error.
     * En caso de que "$r_accion_grupo" tenga un error al filtrar, devolverá un error
     * En caso de que los registros en "n" grupo sea diferente de 1 dará error
     *
     * @param int $accion_id contiene un entero que representa un id de acción
     *
     * @param int $grupo_id contiene un entero que represente el id de grupo
     *
     * @return array|int
     *
     *@functions $r_accion_grupo = adm_accion_grupo->filtro_and. Revisa los filtros para validar la acción de la funcion
     * En caso de error lanzará un mensaje
     *
     *@functions $adm_accion_grupo->registro_id = (int)$r_accion_grupo. Añade un valor a "r_accion_grupo", para
     * conservarlo en "registro_id". 
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

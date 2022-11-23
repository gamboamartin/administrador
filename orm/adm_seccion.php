<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;
use config\generales;
use gamboamartin\errores\errores;


use PDO;
use stdClass;

class adm_seccion extends modelo{
    /**
     * DEBUG INI
     * seccion_menu constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link, array $childrens = array()){
        $tabla = 'adm_seccion';
        $columnas = array($tabla=>false, 'adm_menu'=>$tabla);
        $campos_obligatorios = array('status','descripcion','adm_menu_id');


        $childrens['adm_accion'] = "gamboamartin\\administrador\\models";
        $childrens['adm_bitacora'] = "gamboamartin\\administrador\\models";
        $childrens['adm_elemento_lista'] = "gamboamartin\\administrador\\models";
        $childrens['adm_seccion_pertenece'] = "gamboamartin\\administrador\\models";
        $childrens['adm_campo'] = "gamboamartin\\administrador\\models";

        $columnas_extra['adm_seccion_n_acciones'] = /** @lang sql */
            "(SELECT COUNT(*) FROM adm_accion WHERE adm_accion.adm_seccion_id = adm_seccion.id)";


        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios,columnas:  $columnas,
            columnas_extra: $columnas_extra, childrens: $childrens);
        $this->NAMESPACE = __NAMESPACE__;
    }

    /**
     * Obtiene las acciones de una seccion
     * @param int $adm_seccion_id Seccion identificador
     * @return array
     * @version 2.47.4
     */
    public function acciones(int $adm_seccion_id): array
    {
        if($adm_seccion_id <= 0){
            return $this->error->error(mensaje: 'Error adm_seccion_id debe ser mayor a 0',data:  $adm_seccion_id);
        }
        $filtro['adm_seccion.id'] = $adm_seccion_id;
        $r_adm_accion = (new adm_accion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener acciones',data:  $r_adm_accion);
        }
        return $r_adm_accion->registros;
    }

    public function alta_bd(): array|stdClass
    {

        $keys = array('descripcion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }

        $this->registro = $this->campos_base(data:$this->registro,modelo: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo base',data: $this->registro);
        }


        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta seccion ',data: $r_alta_bd);
        }
        $registro_id = $r_alta_bd->registro_id;

        $r_accion_basica = (new adm_accion_basica($this->link))->obten_registros_activos();
        if (errores::$error){

            return  $this->error->error(mensaje: 'Error al obtener datos del registro',data: $r_accion_basica);

        }

        $acciones_basicas = $r_accion_basica->registros;

        /**
         * REFACTORIZAR
         */
        $accion = array();
        foreach ($acciones_basicas as $accion_basica) {
            $accion['descripcion'] = $accion_basica['adm_accion_basica_descripcion'];
            $accion['icono'] = $accion_basica['adm_accion_basica_icono'];
            $accion['visible'] = $accion_basica['adm_accion_basica_visible'];
            $accion['seguridad'] = $accion_basica['adm_accion_basica_seguridad'];
            $accion['inicio'] = $accion_basica['adm_accion_basica_inicio'];
            $accion['lista'] = $accion_basica['adm_accion_basica_lista'];
            $accion['status'] = $accion_basica['adm_accion_basica_status'];
            $accion['adm_seccion_id'] = $registro_id;
            $accion['es_view'] = $accion_basica['adm_accion_basica_es_view'];
            $accion['titulo'] =str_replace('_',' ',$accion_basica['adm_accion_basica_descripcion']);
            $accion['titulo'] =ucwords($accion['titulo']);
            $accion['css'] ='info';
            $adm_accion_modelo = new adm_accion($this->link);
            $adm_accion_modelo->registro = $accion;
            $r_alta_accion =$adm_accion_modelo->alta_bd();
            if (errores::$error){
               return  $this->error->error(mensaje: 'Error al dar de alta acciones basicas',data: $r_alta_accion);
            }
        }
        return $r_alta_bd;

    }

    /**
     * Elimina una seccion con sus hijos adm_accion, adm_seccion_pertenece, adm_elemento_lista, adm_campo, adm_bitacora
     * @param int $id Identificador de la seccion
     * @return array|stdClass
     * @version 2.46.4
     */
    public function elimina_bd(int $id): array|stdClass
    {
        if($id <=0){
            return $this->error->error('Error id debe se mayor a 0', $id);
        }
        $filtro['adm_seccion.id'] = $id;
        $r_adm_accion = (new adm_accion(link: $this->link))->elimina_con_filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al eliminar r_adm_accion', $r_adm_accion);
        }
        $r_adm_seccion_pertenece = (new adm_seccion_pertenece(link: $this->link))->elimina_con_filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al eliminar r_adm_seccion_pertenece', $r_adm_seccion_pertenece);
        }
        $r_adm_elemento_lista = (new adm_elemento_lista(link: $this->link))->elimina_con_filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al eliminar r_adm_accion', $r_adm_elemento_lista);
        }
        $r_adm_campo = (new adm_campo(link: $this->link))->elimina_con_filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al eliminar r_adm_campo', $r_adm_campo);
        }
        $r_adm_bitacora = (new adm_bitacora(link: $this->link))->elimina_con_filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al eliminar r_adm_bitacora', $r_adm_bitacora);
        }

        $r_elimina_bd = parent::elimina_bd($id); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al eliminar adm_seccion', $r_elimina_bd);
        }
        return $r_elimina_bd;
    }

    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array|stdClass
    {
        $registro = $this->campos_base(data: $registro, modelo: $this, id: $id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo base',data: $registro);
        }

        $r_modifica_bd = parent::modifica_bd($registro, $id, $reactiva); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al modificar adm_seccion', $r_modifica_bd);
        }
        return $r_modifica_bd;
    }

    /**
     *
     * @param int $menu_id
     * @return array
     */
    public function obten_submenu_permitido(int $menu_id): array
    { //FIN PROT
        $valida = $this->validacion->valida_estructura_menu($menu_id);
        if(errores::$error){
            return $this->error->error('Error al validar ',$valida);
        }

	    $grupo_id = $_SESSION['grupo_id'];

	    $where_menu = " AND adm_seccion.adm_menu_id = $menu_id";

	    $consulta = "SELECT 
               		adm_seccion.id AS id ,
                		adm_seccion.icono AS icono,
                		adm_seccion.descripcion AS descripcion,
                		adm_seccion.etiqueta_label AS etiqueta_label,
                		adm_seccion.adm_menu_id AS menu_id
                		FROM adm_seccion
                	INNER JOIN adm_accion  ON adm_accion.adm_seccion_id = adm_seccion.id
                	INNER JOIN adm_accion_grupo AS permiso ON permiso.adm_accion_id = adm_accion.id
                	INNER JOIN adm_grupo  ON adm_grupo.id = permiso.adm_grupo_id
                	INNER JOIN adm_menu  ON adm_menu.id = adm_seccion.adm_menu_id
                WHERE 
                	adm_seccion.status = 'activo' 
                	AND adm_accion.status = 'activo' 
                	AND adm_grupo.status = 'activo' 
                	AND permiso.adm_grupo_id = $grupo_id $where_menu
                        AND adm_accion.visible = 'activo'
                GROUP BY adm_seccion.id
                ";
	    $result = $this->link->query($consulta);
	    $n_registros = $result->rowCount();

	    if($this->link->errorInfo()[1]){
	        return $this->error->error(mensaje: 'Error al ejecutar sql',data: array(array($this->link->errorInfo(),$consulta)));
	    }

	    $new_array = array();
	    while( $row = $result->fetchObject()){
	        $new_array[] = (array)$row;
	    }
        $result->closeCursor();
	    return array('registros' => $new_array, 'n_registros' => $n_registros);
	}

    public function secciones_permitidas(): array
    {

        $r_adm_seccion = new stdClass();
        $r_adm_seccion->registros = array();

        $adm_usuario_id = -1;
        if(isset($_SESSION['usuario_id'])){
            $adm_usuario_id = $_SESSION['usuario_id'];
        }

        if($adm_usuario_id > 0) {

            $secciones_sistema = $this->secciones_sistema();
            if (errores::$error) {
                return $this->error->error('Error al obtener secciones ', $secciones_sistema);
            }

            if(count($secciones_sistema)>0) {

                $adm_usuario = (new adm_usuario(link: $this->link))->registro(
                    registro_id: $adm_usuario_id, columnas_en_bruto: true, retorno_obj: true);
                if (errores::$error) {
                    return $this->error->error('Error al obtener usuario ', $adm_usuario);
                }

                $adm_grupo = (new adm_grupo(link: $this->link))->registro(
                    registro_id: $adm_usuario->adm_grupo_id, columnas_en_bruto: true, retorno_obj: true);
                if (errores::$error) {
                    return $this->error->error('Error al obtener grupo ', $adm_grupo);
                }


                $seccion_sistema_in = array();
                foreach ($secciones_sistema as $seccion_sistema) {
                    $seccion_sistema_in[] = $seccion_sistema['adm_seccion_id'];
                }

                $filtro['adm_grupo.id'] = $adm_grupo->id;
                $group_by[] = 'adm_seccion.id';
                $in['llave'] = 'adm_seccion.id';
                $in['values'] = $seccion_sistema_in;

                $r_adm_seccion = (new adm_accion_grupo(link: $this->link))->filtro_and(
                    filtro: $filtro, group_by: $group_by, in: $in);
                if (errores::$error) {
                    return $this->error->error('Error al obtener secciones ', $r_adm_seccion);
                }
            }

        }

        return $r_adm_seccion->registros;

    }

    public function secciones_sistema(): array
    {
        $filtro['adm_sistema.descripcion'] = (new generales())->sistema;
        $r_seccion_pertenece = (new adm_seccion_pertenece(link: $this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener secciones ', data: $r_seccion_pertenece);
        }
        return $r_seccion_pertenece->registros;
    }
}
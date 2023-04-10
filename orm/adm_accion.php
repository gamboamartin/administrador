<?php
namespace gamboamartin\administrador\models;

use base\orm\_modelo_children;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;


class adm_accion extends _modelo_children {
    /**
     * DEBUG INI
     * accion constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link){
        $tabla = 'adm_accion';
        $columnas = array($tabla=>false, 'adm_seccion'=>$tabla, 'adm_menu'=>'adm_seccion');
        $campos_obligatorios = array('adm_seccion_id','inicio','lista','titulo','css');
        $tipo_campos['adm_seccion_id'] = 'id';
        $tipo_campos['visible'] = 'status';
        $tipo_campos['inicio'] = 'status';
        $tipo_campos['lista'] = 'status';
        $tipo_campos['muestra_icono_btn'] = 'status';
        $tipo_campos['muestra_titulo_btn'] = 'status';

        $columnas_extra['adm_accion_n_permisos'] = /** @lang sql */
            "(SELECT COUNT(*) FROM adm_accion_grupo WHERE adm_accion_grupo.adm_accion_id = adm_accion.id)";

        $parents_data['adm_seccion'] = array();
        $parents_data['adm_seccion']['namespace'] = 'gamboamartin\\administrador\\models';
        $parents_data['adm_seccion']['registro_id'] = -1;
        $parents_data['adm_seccion']['keys_parents'] = array('adm_seccion_descripcion');
        $parents_data['adm_seccion']['key_id'] = 'adm_seccion_id';

        $childrens['adm_accion_grupo'] = "gamboamartin\\administrador\\models";

        $defaults['css'] = 'info';


        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios, columnas: $columnas,
            columnas_extra: $columnas_extra, tipo_campos: $tipo_campos, childrens: $childrens, defaults: $defaults,
            parents_data: $parents_data);
        $this->NAMESPACE = __NAMESPACE__;
        $this->validacion = new \validacion\accion();

        $this->etiqueta = 'Accion';
    }

    /**
     *
     *
     * Funcion que obtiene y genera un registro de tipo acción. basado en los resultados de los
     * filtros recibidos (accion y seccion). Valida si hay registros no, Devuelve un error en caso de no encontrarlos.
     *
     * @param string $seccion filtra entre la seccion y accion een base a lo que obtenga retorna un objeto de tipo accion
     * @param string $accion filtra entre la seccion y accion een base a lo que obtenga retorna un objeto de tipo accion
     *
     * @return array
     *
     * @functions $valida   = adm_accion->validacion->seccion_accion. Usada para validar los resultados de la funcion "seccion_accion".
     *En caso de error lanzará un mensaje
     *
     * @functions $r_accion = adm_accion->accion_seccion. Usada para validar los resultados de la funcion "accion_seccion".
     * En caso de error lanzará un mensaje
     * @version 2.12.2.1
     */
    public function accion_registro(string $accion, string $seccion):array{
        $valida = $this->validacion->seccion_accion(accion: $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al validar seccion',data: $valida);
        }
        $r_accion = $this->accion_seccion(accion: $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener acciones',data: $r_accion);
        }
        if($r_accion->n_registros===0) {
            return  $this->error->error(mensaje: 'Error no existen acciones',data: $r_accion);
        }
        return $r_accion->registros[0];
    }

    /**
     * Funcion que valida entre la seccion y accion en base a lo que obtenga retorna un objeto de tipo accion".
     * En caso de error en "$valida", "$filtro" o "$r_accion" lanzará un mensaje de error.
     *
     *@param string $seccion filtra entre la seccion y accion een base a lo que obtenga retorna un objeto de tipo accion
     *
     *@param string $accion  filtra entre la seccion y accion een base a lo que obtenga retorna un objeto de tipo accion

     * @return array|stdClass
     * @functions $valida   = adm_accion->validacion->seccion_accion  Usada para validar los resultados de la funcion "seccion_accion". En caso de error lanzará un mensaje
     * @functions $filtro   = adm_accion->filtro_accion_seccion  Usada para validar los resultados de la funcion "filtro_accion_seccion". En caso de error lanzará un mensaje
     * @functions $r_accion = adm_accion->filtro_and  Usada para validar los resultados de la funcion "filtro_and". En caso de error lanzará un mensaje
     * @version 1.577.51
     */
    private function accion_seccion(string $accion, string $seccion ):array|stdClass{
        $valida = $this->validacion->seccion_accion(accion:  $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al validar seccion',data: $valida);
        }

        $filtro = $this->filtro_accion_seccion(accion: $accion, seccion: $seccion );
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener filtros',data: $filtro);
        }
        $r_accion = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener acciones',data: $r_accion);
        }
        return $r_accion;
    }

    final public function acciones_by_seccion_id(int $adm_seccion_id){
        $filtro['adm_seccion.id'] = $adm_seccion_id;
        $r_accion = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener acciones',data: $r_accion);
        }
        return $r_accion->registros;

    }

    /**
     * Maqueta un array con un conjunto de acciones id
     * @param array $adm_acciones_grupos Permisos
     * @return array
     * @version 2.49.4
     */
    private function acciones_id_maqueta(array $adm_acciones_grupos): array
    {
        $acciones = array();
        foreach ($adm_acciones_grupos as $adm_accion_grupo){

            $valida = $this->validacion->valida_array(value:  $adm_accion_grupo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar adm_accion_grupo',data:  $valida);
            }

            $keys = array('adm_accion_id');
            $valida = $this->validacion->valida_ids(keys:$keys,registro:  $adm_accion_grupo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar adm_accion_grupo', data: $valida);
            }

            $acciones[] = $adm_accion_grupo['adm_accion_id'];
        }
        return $acciones;
    }

    /**
     * Integra un array con acciones id filtradas por un grupo de usuarios
     * @param int $adm_grupo_id Grupo a filtrar
     * @param int $adm_seccion_id seccion a filtrar
     * @return array
     * @version 2.64.6
     */
    public function acciones_id_por_grupo(int $adm_grupo_id = -1, int $adm_seccion_id = -1): array
    {

        $filtro = $this->filtro_seccion_grupo(adm_grupo_id: $adm_grupo_id,adm_seccion_id:  $adm_seccion_id);
        if (errores::$error) {
            return $this->error->error('Error al obtener filtro', $filtro);
        }

        $group_by[] = 'adm_accion.id';
        $columnas = array('adm_accion_id');

        $r_acciones_grupo = (new adm_accion_grupo($this->link))->filtro_and(
            columnas: $columnas, filtro: $filtro, group_by: $group_by);
        if (errores::$error) {
            return $this->error->error('Error al obtener acciones', $r_acciones_grupo);
        }
        $adm_acciones_grupos = $r_acciones_grupo->registros;


        $acciones = $this->acciones_id_maqueta(adm_acciones_grupos: $adm_acciones_grupos);
        if (errores::$error) {
            return $this->error->error('Error al obtener acciones', $r_acciones_grupo);
        }

        return $acciones;

    }

    /**
     * Obtiene las acciones permitidas de una session
     * @param string $accion Accion a verificar
     * @param modelo $modelo llamada a la clase modelo
     *
     * @param string $seccion Seccion a verificar
     * @return array
     *
     * @functions $seccion = trim($seccion). Elimina espacios en blanco de "$seccion".
     *
     * @functions $seccion_menu_id = $modelo->seccion_menu_id(seccion: $seccion). Obtiene el menu_id de una seccion
     *
     * @functions $r_acciones = (new adm_accion_grupo($adm_accion->link))->obten_accion_permitida(seccion_menu_id: $seccion_menu_id)
     * Se utiliza para valida y maquetar un registro acciones. En caso de error, lanzará un mensaje.
     * @version 1.454.49
     */
    public function acciones_permitidas(string $accion, modelo $modelo, string $seccion):array{
        if(!isset($_SESSION['grupo_id']) && $seccion !== 'adm_session' && $accion !== 'login'){
            return $this->error->error(mensaje: 'Error debe existir grupo_id',data: $_SESSION);
        }
        if(isset($_SESSION['grupo_id'])&&(int)$_SESSION['grupo_id']<=0 && $seccion !== 'adm_session' && $accion !== 'login'){
            return $this->error->error(mensaje: 'Error grupo_id debe ser mayor o igual a 0',data: $_SESSION);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje: 'Error seccion no puede venir vacio',data: $accion);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion no puede venir vacio',data: $seccion);
        }
        $seccion_menu_id = $modelo->seccion_menu_id(seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error obtener seccion_menu_id',data: $seccion_menu_id);
        }

        $r_acciones = (new adm_accion_grupo($this->link))->obten_accion_permitida(seccion_menu_id: $seccion_menu_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error obtener acciones permitidas',data: $r_acciones);
        }
        return $r_acciones->registros;
    }

    /**
     * inserta un registro de tipo accion y agrega permisos a usuarios de tipo root
     * @return array|stdClass con datos del registro insertado
     * @example
     *      $r_alta_accion = $this->accion_modelo->alta_bd();
     *
     * @internal  $this->valida_campo_obligatorio();
     * @internal  $this->valida_estructura_campos();
     * @internal  $this->asigna_data_user_transaccion();
     * @internal  $this->bitacora($this->registro,__FUNCTION__,$consulta);
     * @internal  $grupo_modelo->filtro_and($filtro);
     * @internal  $accion_grupo_modelo->alta_bd();
     */
    public function alta_bd(): array|stdClass{


        $valida = $this->valida_alta_bd(registro:$this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }

        $registro = $this->init_row_alta(
            defaults: $this->defaults,parents_data: $this->parents_data, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar registro',data: $registro);
        }


        $valida = (new _base_accion())->valida_alta(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }

        $this->registro = $registro;


        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta accion',data: $r_alta_bd);
        }

        $r_accion_grupo = $this->inserta_grupos_permisos_root(adm_accion_id: $r_alta_bd->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar accion_grupo',data: $r_accion_grupo);
        }

        return $r_alta_bd;
    }

    protected function asigna_full_status_alta(array $registro): array
    {
        $keys = array('inicio','lista');

        $registro = $this->asigna_status_alta(keys:$keys,registro:  $registro, status: 'inactivo');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar status',data: $registro);
        }
        return $registro;
    }

    /**
     * Funcion usada para registrar la cantidad de acciones realizadas por un grupo.
     *
     * @return array|int
     *
     * @functions $n_permisos = (new adm_accion_grupo($adm_accion->link))->cuenta(filtro: $filtro); Valida y maqueta la
     * cantidad de acciones realizadas
     */
    final public function cuenta_acciones(): int|array
    {
        if(!isset($_SESSION['grupo_id'])){
            return $this->error->error('Error debe existir grupo_id',array($_SESSION));
        }

        $grupo_id = $_SESSION['grupo_id'];
        if(isset($_SESSION['n_permisos']) && (int)$_SESSION['n_permisos']>0){
            return $_SESSION['n_permisos'];
        }

        $filtro['adm_accion.status'] = 'activo';
        $filtro['adm_grupo.status'] = 'activo';
        $filtro['adm_seccion.status'] = 'activo';
        $filtro['adm_accion_grupo.adm_grupo_id'] = $grupo_id;
        $n_permisos = (new adm_accion_grupo($this->link))->cuenta(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al contar permisos', $n_permisos);
        }

        return (int)$n_permisos;
    }

    public function elimina_bd(int $id): array|stdClass
    {

        $filtro['adm_accion.id'] = $id;
        $r_adm_accion_grupo = (new adm_accion_grupo(link: $this->link))->elimina_con_filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al eliminar adm_accion_grupo', $r_adm_accion_grupo);
        }

        $r_elimina_bd = parent::elimina_bd($id); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al eliminar accion', $r_elimina_bd);
        }
        return $r_elimina_bd;
    }

    /**
     * Funcion para maquetar filtro de "adm_seccion.descripcion" y "adm_accion.descripcion"
     * @version 1.48.14
     * @param string $seccion Seccion o modelo o tabla
     * @param string $accion accion de ejecucion
     * @return array
     *
     * @functions $valida = $adm_accion->validacion->seccion_accion( accion: $accion, seccion: $seccion);
     * Valida que exista una accion comprobando "$seccion" y "accion". Mostrará un mensaje de error en caso
     * de que ocurra uno
     */
    private function filtro_accion_seccion(string $accion, string $seccion, ):array{

        $valida = $this->validacion->seccion_accion( accion: $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error('Error al validar seccion',$valida);
        }

        $filtro['adm_seccion.descripcion'] = strtolower(trim($seccion));
        $filtro['adm_accion.descripcion'] = strtolower(trim($accion));

        return $filtro;
    }

    /**
     * Obtiene el filtro para determinar permisos de ejecucion
     * @param string $accion Accion a ejecutar
     * @param int $grupo_id Grupo a verificar si tiene permiso
     * @param string $seccion Seccion a verificar
     * @return array
     * @version 10.15.2
     */
    private function filtro_permiso(string $accion, int $grupo_id, string $seccion): array
    {
        $valida = $this->valida_data_permiso(accion: $accion, grupo_id: $grupo_id,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar permiso',data: $valida);
        }

        $filtro['adm_accion.status'] = 'activo';
        $filtro['adm_grupo.status'] = 'activo';
        $filtro['adm_seccion.status'] = 'activo';
        $filtro['adm_accion_grupo.adm_grupo_id'] = $grupo_id;
        $filtro['adm_seccion.descripcion'] = $seccion;
        $filtro['adm_accion.descripcion'] = $accion;
        return $filtro;
    }

    /**
     * Genera un filtro para obtencion de acciones
     * @param int $adm_grupo_id Identificador de grupo
     * @param int $adm_seccion_id Identificador de seccion
     * @return array
     * @version 1.630.56
     */
    private function filtro_seccion_grupo(int $adm_grupo_id, int $adm_seccion_id): array
    {
        $filtro = array();
        if($adm_grupo_id > 0){
            $filtro['adm_grupo.id'] = $adm_grupo_id;
        }
        if($adm_seccion_id > 0){
            $filtro['adm_seccion.id'] = $adm_seccion_id;
        }
        if(count($filtro) === 0){
            $data = new stdClass();
            $data->adm_grupo_id = $adm_grupo_id;
            $data->adm_seccion_id = $adm_seccion_id;
            return $this->error->error(mensaje:'Error adm_grupo_id o adm_seccion_id deben der mayor a 0', data: $data);
        }
        return $filtro;
    }

    /**
     * Integra un permiso en una session si es valido
     * @param string $accion Accion a verificar
     * @param int $grupo_id Grupo de usuario
     * @param string $seccion Seccion
     * @return bool|array
     * @version 10.20.2
     */
    private function genera_permiso_valido(string $accion, int $grupo_id, string $seccion): bool|array
    {
        $valida = $this->valida_data_permiso(accion: $accion, grupo_id: $grupo_id,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar permiso',data: $valida);
        }

        $n_permisos = $this->n_permisos(accion: $accion, grupo_id: $grupo_id, seccion: $seccion);
        if (errores::$error) {
            return $this->error->error('Error al contar acciones', $n_permisos);
        }
        $permiso_valido = $this->permiso_valido(accion: $accion, grupo_id: $grupo_id, n_permisos: $n_permisos,
            seccion: $seccion);
        if (errores::$error) {
            return $this->error->error('Error al verificar permiso', $permiso_valido);
        }
        return $permiso_valido;
    }

    /**
     * Obtiene en un arreglo los grupos por accion
     * @param int $adm_accion_id Accion a buscar
     * @return array
     * @version 2.12.2.1
     */
    public function grupos_id_por_accion(int $adm_accion_id): array
    {
        if($adm_accion_id <=0){
            return $this->error->error(mensaje: 'Error adm_accion_id debe ser mayor a 0',data:  $adm_accion_id);
        }
        $filtro['adm_accion.id'] = $adm_accion_id;
        $group_by[] = 'adm_grupo.id';
        $columnas = array('adm_grupo_id');
        $r_acciones_grupo = (new adm_accion_grupo($this->link))->filtro_and(
            columnas: $columnas, filtro: $filtro, group_by: $group_by);
        if (errores::$error) {
            return $this->error->error('Error al obtener grupos', $r_acciones_grupo);
        }
        $adm_acciones_grupos = $r_acciones_grupo->registros;

        $grupos = array();
        foreach ($adm_acciones_grupos as $adm_accion_grupo){
            $grupos[] = $adm_accion_grupo['adm_grupo_id'];
        }

        return $grupos;

    }

    private function grupos_root(): array
    {
        $grupo_modelo = new adm_grupo($this->link);
        $filtro['adm_grupo.root'] = 'activo';

        $r_grupo = $grupo_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener grupo',data: $r_grupo);
        }
        return $r_grupo->registros;
    }

    protected function init_row_alta(array $defaults, array $parents_data, array $registro): array
    {

        $registro = parent::init_row_alta(defaults: $defaults,parents_data:  $parents_data, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar descripcion_select',data: $registro);
        }
        if(!isset($registro['titulo'])){
            $titulo = str_replace('_', ' ', $registro['descripcion']);
            $titulo = ucwords($titulo);
            $registro['titulo'] = trim($titulo);
        }

        return $registro;
    }

    private function inserta_accion_grupo(adm_accion_grupo $accion_grupo_modelo, int $adm_accion_id, int $adm_grupo_id): array|stdClass
    {
        $accion_grupo_row = $this->maqueta_row_accion_grupo(accion_grupo_modelo: $accion_grupo_modelo,
            adm_accion_id:  $adm_accion_id,adm_grupo_id:  $adm_grupo_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar row',data: $accion_grupo_row);
        }

        $r_accion_grupo = $accion_grupo_modelo->alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar accion a root',data: $r_accion_grupo);
        }
        return $r_accion_grupo;
    }

    private function inserta_accion_grupo_root(int $adm_accion_id, array $grupos): array
    {
        $accion_grupo_modelo = new adm_accion_grupo($this->link);
        $inserts = array();
        foreach($grupos as $grupo){

            $r_accion_grupo = $this->inserta_accion_grupo(accion_grupo_modelo: $accion_grupo_modelo,
                adm_accion_id:  $adm_accion_id,adm_grupo_id:  $grupo['adm_grupo_id']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al insertar accion_grupo',data: $r_accion_grupo);
            }
            $inserts[] = $r_accion_grupo;
        }
        return $inserts;
    }

    private function inserta_grupos_permisos_root(int $adm_accion_id): array
    {
        $grupos = $this->grupos_root();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener grupos',data: $grupos);
        }

        $r_accion_grupo = $this->inserta_accion_grupo_root(adm_accion_id: $adm_accion_id,grupos:  $grupos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar accion_grupo',data: $r_accion_grupo);
        }
        return $r_accion_grupo;
    }

    private function maqueta_row_accion_grupo(adm_accion_grupo $accion_grupo_modelo, int $adm_accion_id, int $adm_grupo_id): array
    {
        $accion_grupo_modelo->registro['adm_accion_id'] = $adm_accion_id;
        $accion_grupo_modelo->registro['adm_grupo_id'] = $adm_grupo_id;
        $accion_grupo_modelo->registro['status'] = 'activo';
        return $accion_grupo_modelo->registro;
    }



    public function modifica_bd(array $registro, int $id, bool $reactiva = false, array $keys = array('adm_seccion_id','descripcion')): array|stdClass
    {

        $registro = (new _base_accion())->registro_validado_css(id: $id, modelo: $this,registro: $registro );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar css',data: $registro);
        }

        $r_modifica_bd = parent::modifica_bd(registro: $registro,id:  $id,reactiva:  $reactiva,keys:  $keys); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error('Error al modificar adm_seccion', $r_modifica_bd);
        }

        $valida =(new _base_accion())->valida_icono_upd(id: $id,modelo:  $this);
        if(errores::$error){
            return $this->error->error('Error al validar registro', $valida);
        }


        return $r_modifica_bd;
    }

    /**
     * P INT ERROREV
     *
     * Funcion con la finalidad de validar que el grupo al que pertenece el usuario tenga permitido realizar la accion
     * enviada.
     *
     * @param string $seccion Seccion a verificar
     * @param string $accion Accion a verificar
     * @return bool|array
     *
     * @functions $valida = $adm_accion->validacion->valida_accion_permitida(accion: $accion,seccion:  $seccion);
     * Valida una accion realizada contemplando la seccion y accion
     *
     * @functions $existe = (new adm_accion_grupo($adm_accion->link))->existe(filtro: $filtro)
     * Valida que exista un registro a partir de los filtros enviados
     */
    public function obten_accion_permitida_session(string $seccion, string $accion): bool|array{


        $valida = $this->validacion->valida_accion_permitida(accion: $accion,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos',data: $valida);
        }

        $grupo_id = $_SESSION['grupo_id'];

        if(isset($_SESSION['accion_permitida'][$seccion][$grupo_id][$accion])){
            return $_SESSION['accion_permitida'][$seccion][$grupo_id][$accion];
        }

        $accion = strtolower(trim($accion));
        $seccion = strtolower(trim($seccion));


        $filtro = array();
        $filtro['adm_seccion.descripcion'] = $seccion;
        $filtro['adm_grupo.id'] = $grupo_id;
        $filtro['adm_accion.visible'] = 'inactivo';
        $filtro['adm_accion.descripcion'] = $accion;
        $filtro['adm_accion.status'] = 'activo';
        $filtro['adm_seccion.status'] = 'activo';
        $filtro['adm_grupo.status'] = 'activo';

        $existe = (new adm_accion_grupo($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: $existe);
        }

        $_SESSION['accion_permitida'][$seccion][$grupo_id][$accion] = $existe;

        return $existe;

    }


    /**
     * PRUEBAS FINALIZADAS
     * Obtiene el registro por busqueda de seccion_descripcion uy accion_descripcion
     *
     * @param string $seccion nombre de modelo seccion tabla
     * @param string  $accion accion de sistema
     * @example
     *      $accion = str_replace('_',' ', $etiqueta);
            $accion_registro = $accion_modelo->obten_accion_por_seccion_accion($this->seccion,$etiqueta);
     *
     * @return array registro encontrado
     * @throws errores seccion vacio
     * @throws errores $accion vacia
     * @throws errores la seccion no es una clase
     * @throws errores si no se encontro accion
     * @internal  $this->filtro_and($filtro,'numeros',array(),array(),0,0,array());
     * @uses  controler->genera_etiqueta_accion

     */
    public function obten_accion_por_seccion_accion(string $seccion, string $accion):array{ //FIN PROT
        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        $clase = $namespace.$seccion;
        if($seccion === ''){
            return  $this->error->error('Error seccion no puede venir vacia',array($seccion,$accion));
        }
        if($accion === ''){
            return  $this->error->error('Error accion no puede venir vacia',array($seccion,$accion));

        }
        if(!class_exists($clase)){
            return  $this->error->error('Error no existe la clase',$clase);
        }
        if(isset($_SESSION['acciones_breads'][$seccion][$accion])){
            return $_SESSION['acciones_breads'][$seccion][$accion];
        }
        $accion_registro = $this->accion_registro(accion: $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error('Error al obtener acciones',$accion_registro);
        }

        $_SESSION['acciones_breads'][$seccion][$accion] = $accion_registro;

        return $accion_registro;
    }


    /**
     * Obtiene el numero de permisos de un grupo de usuarios
     * @param string $accion Accion a validar
     * @param int $grupo_id Grupo a validar
     * @param string $seccion Seccion a validar
     * @return int|array
     * @version 10.15.2
     */
    private function n_permisos(string $accion, int $grupo_id, string $seccion): int|array
    {
        $valida = $this->valida_data_permiso(accion: $accion, grupo_id: $grupo_id,seccion:  $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar permiso',data: $valida);
        }

        $filtro = $this->filtro_permiso(accion: $accion,grupo_id:  $grupo_id, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro);
        }

        $n_permisos = (new adm_accion_grupo($this->link))->cuenta(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al contar acciones',data: $n_permisos);
        }
        return $n_permisos;
    }

    /**
     *
     * @return array
     */
    public function obten_acciones_iniciales():array{
        if(!isset($_SESSION['grupo_id'])){
            return $this->error->error('Error debe existir grupo_id',array($_SESSION));
        }
        $grupo_id = $_SESSION['grupo_id'];
        if(isset($_SESSION['acciones_iniciales'])){
            return $_SESSION['acciones_iniciales'];
        }

        $consulta = "SELECT 
                      adm_seccion.descripcion AS adm_seccion_descripcion,
                      adm_accion.descripcion AS adm_accion_descripcion,
                      adm_accion.icono as adm_accion_icono
                    FROM adm_accion 
                      INNER JOIN adm_accion_grupo ON adm_accion_grupo.adm_accion_id = adm_accion.id
                      INNER JOIN adm_seccion ON adm_seccion.id = adm_accion.adm_seccion_id
                      WHERE adm_accion_grupo.adm_grupo_id = $grupo_id AND adm_accion.inicio = 'activo'";

        $resultado = $this->ejecuta_consulta(consulta: $consulta,campos_encriptados:  $this->campos_encriptados);
        if(errores::$error){
            return $this->error->error('Error al ejecutar sql',$resultado);
        }
        $_SESSION['acciones_iniciales'] = $resultado;

        return $resultado;
    }

    /**
     * Valida si existe o no permiso para la ejecucion de una accion exepto login y loguea
     * @param string $accion Accion a verificar
     * @param string $seccion Seccion a verificar
     * @return array|bool
     * @version 10.22.2
     */
    final public function permiso(string $accion, string $seccion): bool|array
    {

        $permiso = $this->valida_permiso(accion: $accion, seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al validar permisos',$permiso);
        }
        if($accion === 'login' || $accion === 'loguea'){
            $permiso = true;
        }
        return $permiso;
    }

    /**
     *
     * Funcion que maqueta la variable SESSION con un permiso, siendo valido o invalido.
     *
     * @param string $accion Accion a verificar
     * @param int $grupo_id Identificador de un grupo
     * @param int $n_permisos Numero del permiso otorgado al grupo
     * @param string $seccion Seccion a verificar
     * @return bool
     * @version 10.18.2
     */
    private function permiso_valido(string $accion, int $grupo_id, int $n_permisos, string $seccion): bool
    {
        $permiso_valido = false;
        if($n_permisos === 1){
            $permiso_valido = true;
        }
        $_SESSION['valida_permiso'][$grupo_id][$seccion][$accion] = $permiso_valido;
        return $permiso_valido;
    }



    /**
     * Valida los elementos minimos necesarios para insertar una accion
     * @param array $registro Registro en proceso
     * @return bool|array
     * @version 2.66.6
     */
    protected function valida_alta_bd(array $registro): bool|array
    {
        $keys = array('adm_seccion_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }

        $valida = parent::valida_alta_bd(registro:$registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }

        return true;
    }

    /**
     * Valida los elementos de entrada de un permiso
     * @param string $accion Accion a validar
     * @param int $grupo_id Grupo a validar
     * @param string $seccion Seccion a validar
     * @return bool|array
     * @version 10.14.2
     */
    private function valida_data_permiso(string $accion, int $grupo_id, string $seccion): bool|array
    {
        $accion = trim($accion);
        if($accion === ''){
            return $this->error->error(mensaje:'Error accion esta vacia', data: $accion);
        }
        if($grupo_id<=0){
            return $this->error->error(mensaje:'Error $grupo_id debe ser mayor a 0', data: $grupo_id);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje:'Error $seccion esta vacia', data: $seccion);
        }
        return true;
    }

    /**
     * Funcion que genera un objeto de tipo $valida_permiso. Valida que los usuarios de un grupo puedan realizar acciones.
     * En caso de error en validacion de grupo o al generar permisos, lanzará un error
     *
     * @param string $seccion seccion a verificar
     * @param string $accion accion a verificar
     * @return array|bool
     * 
     * @functions $permiso_valido = $adm_accion->genera_permiso_valido(accion: $accion, grupo_id: $grupo_id,seccion:  $seccion);
     * Verifica y maqueta un "$permiso valido" en base a "$seccion" y "$accion" realizada por el usuario de un grupo. En caso de
     * error lanzará un mensaje
     * @version 10.21.2
     *
     */
	private function valida_permiso(string $accion, string $seccion): bool|array
    {
        if(!isset($_SESSION['grupo_id'])){
            return $this->error->error('Error debe existir grupo_id',array($_SESSION));
        }
        if($seccion === ''){
            return $this->error->error('Error seccion esta vacia',array($seccion, $accion));
        }
        if($accion === ''){
            return $this->error->error('Error accion esta vacia',array($seccion, $accion));
        }

        $grupo_id = $_SESSION['grupo_id'];

        if(isset($_SESSION['valida_permiso'][$grupo_id][$seccion][$accion])){
            $permiso_valido =  $_SESSION['valida_permiso'][$grupo_id][$seccion][$accion];

        }
        else {

            $permiso_valido = $this->genera_permiso_valido(accion: $accion, grupo_id: $grupo_id,seccion:  $seccion);
            if (errores::$error) {
                return $this->error->error('Error al verificar permiso', $permiso_valido);
            }
        }
        return $permiso_valido;

	}

}
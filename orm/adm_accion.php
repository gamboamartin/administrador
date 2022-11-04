<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;
use gamboamartin\errores\errores;

use PDO;
use stdClass;


class adm_accion extends modelo{
    /**
     * DEBUG INI
     * accion constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link){
        $tabla = 'adm_accion';
        $columnas = array($tabla=>false, 'adm_seccion'=>$tabla, 'adm_menu'=>'adm_seccion');
        $campos_obligatorios = array('adm_seccion_id','visible','inicio','lista','titulo','css');
        $tipo_campos['adm_seccion_id'] = 'id';
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas:  $columnas,
            tipo_campos:  $tipo_campos );
        $this->NAMESPACE = __NAMESPACE__;
        $this->validacion = new \validacion\accion();
    }

    /**
     * P INT
     *
     * Funcion que obtiene y genera un registro de tipo acción. basado en los resultados de los
     * filtros recibidos (accion y seccion). Valida si hay registros no, Devuelve un error en caso de no encontrarlos.
     *
     * @param string $seccion filtra entre la seccion y accion een base a lo que obtenga retorna un objeto de tipo accion
     * @param string $accion filtra entre la seccion y accion een base a lo que obtenga retorna un objeto de tipo accion
     *
     * @return array
     *
     *@functions $valida   = adm_accion->validacion->seccion_accion. Usada para validar los resultados de la funcion "seccion_accion".
     *En caso de error lanzará un mensaje
     *
     *@functions $r_accion = adm_accion->accion_seccion. Usada para validar los resultados de la funcion "accion_seccion".
     *En caso de error lanzará un mensaje
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

    private function acciones_id_maqueta(array $adm_acciones_grupos): array
    {
        $acciones = array();
        foreach ($adm_acciones_grupos as $adm_accion_grupo){
            $acciones[] = $adm_accion_grupo['adm_accion_id'];
        }
        return $acciones;
    }

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


        $registro = $this->init_row_alta(registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar registro',data: $registro);
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

    private function asigna_full_status_alta(array $registro): array
    {
        $keys = array('visible','inicio','lista');

        $registro = $this->asigna_status_alta(keys:$keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar status',data: $registro);
        }
        return $registro;
    }

    /**
     * Asigna activo init a row alta
     * @param string $key Key del row a inicializar
     * @param array $registro registro previo
     * @return array
     * @version 1.569.51
     */
    private function asigna_status(string $key, array $registro): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio',data: $key);
        }
        $registro[$key] = 'activo';
        return $registro;
    }

    private function asigna_status_alta(array $keys, array $registro): array
    {
        foreach ($keys as $key){
            $registro = $this->status_alta(key: $key,registro:  $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar status',data: $registro);
            }
        }
        return $registro;
    }

    private function codigo_alta_default(int $adm_seccion_id, string $adm_accion_descripcion): array|string
    {
        $adm_seccion = (new adm_seccion($this->link))->registro(registro_id:  $adm_seccion_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener seccion',data: $adm_seccion);
        }
        $codigo = $adm_seccion->adm_menu_descripcion.' '.$adm_seccion->adm_seccion_descripcion;
        $codigo .= ' '.$adm_accion_descripcion;
        return $codigo;
    }

    private function codigo_default(array $registro): array
    {
        if(!isset($registro['codigo'])){

            $codigo = $this->codigo_alta_default(adm_seccion_id: $registro['adm_seccion_id'],
                adm_accion_descripcion:  $registro['descripcion']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo',data: $codigo);
            }
            $registro['codigo'] = $codigo;
        }
        return $registro;
    }

    /**
     * Funcion usada para registrar la cantidad de acciones realizadas por un grupo.
     *
     * P INT P ORDER
     * @return array|int
     *
     * @functions $n_permisos = (new adm_accion_grupo($adm_accion->link))->cuenta(filtro: $filtro); Valida y maqueta la
     * cantidad de acciones realizadas
     */
    public function cuenta_acciones(): int|array
    { //FIN PROT
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
     * @version 1.12.8
     * @param string $accion Accion a ejecutar
     * @param int $grupo_id Grupo a verificar si tiene permiso
     * @param string $seccion Seccion a verificar
     * @return array
     */
    private function filtro_permiso(string $accion, int $grupo_id, string $seccion): array
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

    private function genera_permiso_valido(string $accion, int $grupo_id, string $seccion): bool|array
    {
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




    public function grupos_id_por_accion(int $adm_accion_id): array
    {
        if($adm_accion_id <=0){
            return $this->error->error('Error adm_accion_id debe ser mayor a 0', $adm_accion_id);
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

    private function init_row_alta(array $registro): array
    {
        $registro = $this->asigna_full_status_alta(registro:$registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar status',data: $registro);
        }


        if(!isset($registro['css'])){
            $registro['css'] = 'info';
        }

        $registro = $this->codigo_default(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar codigo',data: $registro);
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


    private function n_permisos(string $accion, int $grupo_id, string $seccion): int|array
    {
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
     * P INT
     * @param string $accion
     * @param string $seccion
     * @return array|bool
     */
    public function permiso(string $accion, string $seccion): bool|array
    {
        $permiso = $this->valida_permiso(seccion: $seccion,accion:  $accion);
        if(errores::$error){
            return $this->error->error('Error al validar permisos',$permiso);
        }
        if($accion === 'login' || $accion === 'loguea'){
            $permiso = true;
        }
        return $permiso;
    }

    /**
     * P ORDER P INT
     *
     * Funcion que maqueta la variable SESSION con un permiso, siendo valido o invalido.
     *
     * @param string $accion Accion a verificar
     * @param int $grupo_id Identificador de un grupo
     * @param int $n_permisos Numero del permiso otorgado al grupo
     * @param string $seccion Seccion a verificar
     * @return bool
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

    private function status_alta(string $key, array $registro): array
    {
        if(!isset($registro[$key])){
            $registro = $this->asigna_status(key: $key, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar status',data: $registro);
            }
        }
        return $registro;
    }

    /**
     * P INT
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
     *
     */
	private function valida_permiso(string $seccion, string $accion): bool|array
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
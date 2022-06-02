<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;


class accion extends modelo{ //FINALIZADAS
    /**
     * DEBUG INI
     * accion constructor.
     * @param PDO $link
     */
    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'seccion'=>'accion', 'menu'=>'seccion');
        $campos_obligatorios = array('seccion_id','visible','inicio','lista');
        $tipo_campos['seccion_id'] = 'id';
        parent::__construct(link: $link,tabla:  $tabla,campos_obligatorios: $campos_obligatorios, columnas:  $columnas,
            tipo_campos:  $tipo_campos );
        $this->validacion = new \validacion\accion();
    }

    /**
     * P INT P ORDER ERROREV
     * @param string $seccion
     * @param string $accion
     * @param modelo $modelo
     * @return array
     */
    public function acciones_permitidas(string $accion, modelo $modelo, string $seccion):array{
        if(!isset($_SESSION['grupo_id']) && $seccion !== 'session' && $accion !== 'login'){
            return $this->error->error(mensaje: 'Error debe existir grupo_id',data: $_SESSION);
        }
        if(isset($_SESSION['grupo_id'])&&(int)$_SESSION['grupo_id']<=0 && $seccion !== 'session' && $accion !== 'login'){
            return $this->error->error(mensaje: 'Error grupo_id debe ser mayor o igual a 0',data: $_SESSION);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error('Error seccion no puede venir vacio',$seccion);
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
     * inserta un registro de ti po accion y agrega permisos a usuarios de tipo root
     * @example
     *      $r_alta_accion = $this->accion_modelo->alta_bd();
     *
     * @return array con datos del registro insertado
     * @throws errores count($this->registro) === 0
     * @throws errores si algun key de registro es numerico
     * @throws errores definidos en internals
     * @internal  $this->valida_campo_obligatorio();
     * @internal  $this->valida_estructura_campos();
     * @internal  $this->asigna_data_user_transaccion();
     * @internal  $this->bitacora($this->registro,__FUNCTION__,$consulta);
     * @internal  $grupo_modelo->filtro_and($filtro);
     * @internal  $accion_grupo_modelo->alta_bd();
     */
    public function alta_bd(): array|stdClass{

        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta accion',data: $r_alta_bd,
                params: get_defined_vars());
        }

        $grupo_modelo = new grupo($this->link);
        $filtro['grupo.root']['campo'] = 'grupo.root';
        $filtro['grupo.root']['value'] = 'activo';

        $r_grupo = $grupo_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener grupo',data: $r_grupo, params: get_defined_vars());
        }
        $grupos = $r_grupo->registros;
        $accion_grupo_modelo = new accion_grupo($this->link);

        foreach($grupos as $grupo){
            $accion_grupo_modelo->registro['accion_id'] = $r_alta_bd->registro_id;
            $accion_grupo_modelo->registro['grupo_id'] = $grupo['grupo_id'];
            $accion_grupo_modelo->registro['status'] = 'activo';
            $r_accion_grupo = $accion_grupo_modelo->alta_bd();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al insertar accion a root',data: $r_accion_grupo,
                    params: get_defined_vars());
            }
        }

        return $r_alta_bd;
    }

    /**
     * P INT P ORDER
     * @return array|int
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

        $filtro['accion.status'] = 'activo';
        $filtro['grupo.status'] = 'activo';
        $filtro['seccion.status'] = 'activo';
        $filtro['adm_accion_grupo.grupo_id'] = $grupo_id;
        $n_permisos = (new adm_accion_grupo($this->link))->cuenta(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al contar permisos', $n_permisos);
        }

        return (int)$n_permisos;
    }

    /**
     * P INT ERROREV
     * @param string $seccion
     * @param string $accion
     * @return bool|array
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
        $filtro['seccion.descripcion'] = $seccion;
        $filtro['grupo.id'] = $grupo_id;
        $filtro['accion.visible'] = 'inactivo';
        $filtro['accion.descripcion'] = $accion;
        $filtro['accion.status'] = 'activo';
        $filtro['seccion.status'] = 'activo';
        $filtro['grupo.status'] = 'activo';

        $existe = (new adm_accion_grupo($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: $existe, params: get_defined_vars());
        }

        $_SESSION['accion_permitida'][$seccion][$grupo_id][$accion] = $existe;

        return $existe;

    }

    /**
     * P INT
     * @param string $seccion
     * @param string $accion
     * @return array
     */
    private function filtro_accion_seccion(string $seccion, string $accion):array{

        $valida = $this->validacion->seccion_accion( accion: $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error('Error al validar seccion',$valida);
        }

        $filtro['seccion.descripcion'] = strtolower(trim($seccion));
        $filtro['accion.descripcion'] = strtolower(trim($accion));

        return $filtro;
    }

    /**
     * P INT
     * @param string $seccion
     * @param string $accion
     * @return array|stdClass
     */
    private function accion_seccion(string $seccion, string $accion):array|stdClass{
        $valida = $this->validacion->seccion_accion(accion:  $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error('Error al validar seccion',$valida);
        }

        $filtro = $this->filtro_accion_seccion(seccion: $seccion, accion: $accion);
        if(errores::$error){
            return  $this->error->error('Error al obtener filtros',$filtro);
        }
        $r_accion = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return  $this->error->error('Error al obtener acciones',$r_accion);
        }
        return $r_accion;
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
        $accion_registro = $this->accion_registro($seccion, $accion);
        if(errores::$error){
            return  $this->error->error('Error al obtener acciones',$accion_registro);
        }

        $_SESSION['acciones_breads'][$seccion][$accion] = $accion_registro;

        return $accion_registro;
    }

    /**
     * P INT
     * @param string $seccion
     * @param string $accion
     * @return array
     */
    public function accion_registro(string $seccion, string $accion):array{
        $valida = $this->validacion->seccion_accion(accion: $accion, seccion: $seccion);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al validar seccion',data: $valida);
        }
        $r_accion = $this->accion_seccion(seccion: $seccion,accion: $accion);
        if(errores::$error){
            return  $this->error->error('Error al obtener acciones',$r_accion);
        }
        if($r_accion->n_registros===0) {
            return  $this->error->error('Error no existen acciones',$r_accion);
        }
        return $r_accion->registros[0];
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
        $filtro['accion.status'] = 'activo';
        $filtro['grupo.status'] = 'activo';
        $filtro['seccion.status'] = 'activo';
        $filtro['adm_accion_grupo.grupo_id'] = $grupo_id;
        $filtro['seccion.descripcion'] = $seccion;
        $filtro['accion.descripcion'] = $accion;
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

    private function n_permisos(string $accion, int $grupo_id, string $seccion): int|array
    {
        $filtro = $this->filtro_permiso(accion: $accion,grupo_id:  $grupo_id, seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al generar filtro',$filtro);
        }

        $n_permisos = (new adm_accion_grupo($this->link))->cuenta(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al contar acciones',$n_permisos);
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
                      seccion.descripcion AS seccion_descripcion,
                      accion.descripcion AS accion_descripcion,
                      accion.icono as accion_icono
                    FROM accion 
                      INNER JOIN accion_grupo ON accion_grupo.accion_id = accion.id
                      INNER JOIN seccion ON seccion.id = accion.seccion_id
                      WHERE accion_grupo.grupo_id = $grupo_id AND accion.inicio = 'activo'";

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
     * @param string $accion
     * @param int $grupo_id
     * @param int $n_permisos
     * @param string $seccion
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

    /**
     * P INT
     * @param string $seccion
     * @param string $accion
     * @return array|bool
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
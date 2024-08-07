<?php
namespace gamboamartin\administrador\models;

use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class adm_usuario extends modelo{ //PRUEBAS en proceso
    /**
     * DEBUG INI
     * usuario constructor.
     * @param PDO $link Conexion a la BD
     */
    public function __construct(PDO $link, array $childrens = array()){
        
        $tabla = 'adm_usuario';
        $columnas = array($tabla=>false,'adm_grupo'=>$tabla);

        $campos_obligatorios = array('user','password','email','adm_grupo_id','telefono','nombre','ap');


        $childrens['adm_bitacora'] = "gamboamartin\\administrador\\models";
        $childrens['adm_session'] = "gamboamartin\\administrador\\models";

        $tipo_campos = array();
        $tipo_campos['email'] = 'correo';
        $tipo_campos['telefono'] = 'telefono_mx';
        $tipo_campos['adm_grupo_id'] = 'id';

        $columnas_extra['adm_usuario_nombre_completo'] =
            "(CONCAT( ( IFNULL(adm_usuario.nombre,'') ),' ',( IFNULL(adm_usuario.ap,'') ),' ',( IFNULL(adm_usuario.am,'') )) )";

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, columnas_extra: $columnas_extra, tipo_campos: $tipo_campos, childrens: $childrens);
        $this->NAMESPACE = __NAMESPACE__;

        $this->etiqueta = 'Usuario';
    }

    public function alta_bd(): array|stdClass
    {
        $keys = array('user','adm_grupo_id');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }

        if(!isset($this->registro['codigo'])){
            $this->registro['codigo'] = $this->registro['user'];
        }

        if(!isset($this->registro['descripcion'])){
            $this->registro['descripcion'] = $this->registro['codigo'].' - '.$this->registro['nombre'];
        }

        $r_alta_bd = parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar usuario',data: $r_alta_bd);
        }
        return $r_alta_bd;

    }


    /**
     * Valida que el grupo que va a filtrar exista en la base de datos. En caso de que
     * halla un error en la búsqueda, que no exista o sea inconsistente la informacion. Mandará un error.
     *
     * @param array $filtro Verifica y valida los datos que se le ingresen
     * @return array
     *
     * @function $grupo_modelo = new adm_grupo($adm_usuario->link); Obtiene los datos de
     * un grupo por medio del enlace a una base de datos
     * @version 2.96.9
     */
    public function data_grupo(array $filtro): array
    {
        if(count($filtro) === 0){
            return $this->error->error(mensaje: 'Error filtro vacio',data: $filtro);
        }
        $grupo_modelo = new adm_grupo($this->link);
        $r_grupo = $grupo_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener grupo',data: $r_grupo);
        }
        if((int)$r_grupo->n_registros === 0){
            return $this->error->error(mensaje: 'Error al obtener grupo no existe',data: $r_grupo);
        }
        if((int)$r_grupo->n_registros > 1){
            return $this->error->error(mensaje: 'Error al obtener grupo inconsistencia existe mas de uno',
                data: $r_grupo);
        }
        return $r_grupo->registros[0];
    }

    /**
     * TOTAL
     * Maqueta en un objeto los elementos para validar un permiso
     *
     * Esta función recibe como argumentos el nombre de una acción y una sección. Luego, realiza una validación para cada una.
     * Si está vacía cualquiera de las dos, devolverá un error especificando qué elemento está vacío.
     *
     * Al final, devuelve un objeto con los elementos validados
     *
     * @param string $adm_accion    Acción a validar.
     * @param string $adm_seccion   Sección a validar.
     *
     * @return array|stdClass       Retorna un objeto con 'adm_seccion' y 'adm_accion' como propiedades si éstos son válidos.
     *                              Retorna un array si ocurre algun error, con el mensaje de error y los datos con los que se llamó a la función.
     *
     * @version 16.297.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.orm.adm_usuario.data_permiso.21.15.0
     */
    private function data_permiso(string $adm_accion, string $adm_seccion): array|stdClass
    {
        $adm_seccion = trim($adm_seccion);
        if($adm_seccion === ''){
            return $this->error->error(mensaje: 'Error adm_seccion esta vacia', data: $adm_seccion, es_final: true);
        }
        $adm_accion = trim($adm_accion);
        if($adm_accion === ''){
            return $this->error->error(mensaje: 'Error adm_accion esta vacia', data: $adm_accion, es_final: true);
        }
        $data = new stdClass();
        $data->adm_seccion = $adm_seccion;
        $data->adm_accion = $adm_accion;
        return $data;
    }

    /**
     * Elimina un registro de adm_usuario y las sessiones ligadas a ese usuario
     * @param int $id Id de usuario
     * @return array|stdClass
     * @version 3.1.0
     */
    public function elimina_bd(int $id): array|stdClass
    {
        if($id <=0){
            return $this->error->error('Error id debe se mayor a 0', $id);
        }
        $filtro['adm_usuario.id'] = $id;

        $r_adm_session = (new adm_session(link: $this->link))->elimina_con_filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar r_adm_session', data: $r_adm_session);
        }

        $r_elimina_bd =  parent::elimina_bd($id); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar usuario',data:  $r_elimina_bd);
        }
        return $r_elimina_bd;
    }

    final public function existe_user(string $user)
    {
        $filtro['adm_usuario.user'] = $user;
        $existe = $this->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe usuario',data:  $existe);
        }
        return $existe;

    }

    /**
     * TOTAL
     * Método para filtrar la acción y la sección del grupo.
     *
     * Este método acepta una acción, un grupo y una sección.
     * Realiza la validación y prepara un conjunto de parámetros.
     * Los parámetros se utilizan para realizar operaciones en otras partes del sistema:
     *
     * @param string $adm_accion Representa la acción que se validará.
     * @param int $adm_grupo_id Representa el ID del grupo de usuario que se validará.
     * @param string $adm_seccion Representa la sección que se validará.
     *
     * @return array
     * El método devuelve un array con los parámetros validados y preconfigurados.
     * Si algún parámetro no es válido, se devuelve un mensaje de error correspondiente.
     * @version 16.299.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.orm.adm_usuario.filtro.21.15.0
     */
    private function filtro(string $adm_accion, int $adm_grupo_id, string $adm_seccion): array
    {
        $adm_accion = trim($adm_accion);
        if($adm_accion === ''){
            return $this->error->error(mensaje: 'Error adm_accion esta vacia',data:  $adm_accion, es_final: true);
        }
        $adm_seccion = trim($adm_seccion);
        if($adm_seccion === ''){
            return $this->error->error(mensaje: 'Error adm_seccion esta vacia',data:  $adm_seccion, es_final: true);
        }

        if($adm_grupo_id <= 0){
            return $this->error->error(mensaje: 'Error adm_grupo_id debe ser mayor a 0',data:  $adm_grupo_id);
        }

        $filtro['adm_grupo.id'] = $adm_grupo_id;
        $filtro['adm_accion.descripcion'] = $adm_accion;
        $filtro['adm_grupo.status'] = 'activo';
        $filtro['adm_accion.status'] = 'activo';
        $filtro['adm_seccion.descripcion'] = $adm_seccion;
        $filtro['adm_seccion.status'] = 'activo';
        return $filtro;
    }

    /**
     * Genera un filtro en forma de array para integrarlo a la seguridad de datos. En caso de error al
     * validar la SESSION o al obtener al usuario activo lanzará un error.
     *
     * @return array
     *
     * @function $valida = $adm_usuario->validacion->valida_ids(keys: $keys, registro: $_SESSION);
     * Recibe los resultados de la validacion del usuario en base a la session y la llave.
     * @version 1.141.31
     */
    public function filtro_seguridad():array{
        $keys = array('usuario_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $_SESSION);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar SESSION',data: $valida);
        }

        $usuario = self::usuario(usuario_id: $_SESSION['usuario_id'], link: $this->link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener usuario activo',data: $usuario);
        }
        $filtro = array();
        $aplica_seg = true;
        if($usuario['adm_grupo_root']==='activo') {
            $aplica_seg = false;
        }


        if($aplica_seg){
            $filtro['usuario_permitido_id']['campo'] = 'usuario_permitido_id';
            $filtro['usuario_permitido_id']['value'] = $_SESSION['usuario_id'];
            $filtro['usuario_permitido_id']['es_sq'] = true;
            $filtro['usuario_permitido_id']['operador'] = 'AND';
        }


        return $filtro;
    }

    private function genera_session_permite(string $adm_accion, int $adm_grupo_id, string $adm_seccion){

        $valida = $this->valida_datos_permiso(adm_accion: $adm_accion,adm_grupo_id:  $adm_grupo_id,
            adm_seccion:  $adm_seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $data_permiso = $this->get_data_permiso(adm_accion: $adm_accion,adm_grupo_id:  $adm_grupo_id,
            adm_seccion:  $adm_seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener data_permiso', data: $data_permiso);
        }
        $session_permite = $this->session_permite(adm_grupo_id: $adm_grupo_id,data_permiso:  $data_permiso);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar permite en session', data: $session_permite);
        }
        return $data_permiso;
    }

    /**
     * Obtiene los permisos de una interaccion el el sistema
     * @param string $adm_accion Accion a validar
     * @param int $adm_grupo_id Grupo de usuario a validar
     * @param string $adm_seccion Seccion a validar
     * @return array|stdClass
     */
    private function get_data_permiso(string $adm_accion, int $adm_grupo_id, string $adm_seccion): array|stdClass
    {


        $valida = $this->valida_datos_permiso(adm_accion: $adm_accion,adm_grupo_id:  $adm_grupo_id,
            adm_seccion:  $adm_seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $data_permiso = $this->data_permiso(adm_accion: $adm_accion, adm_seccion: $adm_seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener data_permiso', data: $data_permiso);
        }

        $data = $this->get_val_session(adm_grupo_id: $adm_grupo_id,data_permiso:  $data_permiso);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener val_session', data: $data);
        }
        $data_permiso->val_session = $data->val_session;
        $data_permiso->existe = $data->existe;
        return $data_permiso;
    }

    /**
     * Obtiene los elementos de una session
     * @param int $adm_grupo_id Grupo de usuario
     * @param stdClass $data_permiso datos previos de permiso a validar
     * @return array|stdClass
     */
    private function get_val_session(int $adm_grupo_id, stdClass $data_permiso): array|stdClass
    {

        $keys = array('adm_accion','adm_seccion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $data_permiso);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar $data_permiso', data: $valida);
        }

        if($adm_grupo_id <= 0){
            return $this->error->error(mensaje: 'Error adm_grupo_id debe ser mayor a 0',data:  $adm_grupo_id,
                es_final: true);
        }

        $filtro = $this->filtro(adm_accion: $data_permiso->adm_accion,adm_grupo_id: $adm_grupo_id,
            adm_seccion: $data_permiso->adm_seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener filtro', data: $filtro);
        }

        $data = $this->val_session_existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener val_session', data: $data);
        }

        return $data;
    }

    /**
     * Integra en un a variable de SESSION un bool de validacion de permiso
     * @param int $adm_grupo_id Grupo a integrar
     * @param stdClass $data_permiso Permiso obtenido
     * @return array
     */
    private function session_permite(int $adm_grupo_id, stdClass $data_permiso): array
    {
        $_SESSION['permite'][$adm_grupo_id][$data_permiso->adm_seccion][$data_permiso->adm_accion]
            = (int)$data_permiso->val_session;

        return $_SESSION['permite'][$adm_grupo_id];
    }

    /**
     * Verifica si el usuario en ejecucion tiene permiso
     * @param string $adm_accion Accion en ejecucion
     * @param string $adm_seccion Seccion en ejecucion
     * @return array|bool
     */
    final public function tengo_permiso(string $adm_accion, string $adm_seccion): array|bool
    {
        $adm_usuario_id = -1;
        if(isset($_SESSION['usuario_id'])) {
            $adm_usuario_id = $_SESSION['usuario_id'];
        }
        $data_permiso = new stdClass();
        $data_permiso->existe = false;

        if((int)$adm_usuario_id > 0) {

            $adm_grupo_id = -1;
            if(isset($_SESSION['grupo_id'])){
                $adm_grupo_id = (int)$_SESSION['grupo_id'];
            }

            if($adm_grupo_id > 0) {

                if (isset($_SESSION['permite'][$adm_grupo_id][$adm_seccion][$adm_accion])) {
                    if ((int)$_SESSION['permite'][$adm_grupo_id][$adm_seccion][$adm_accion] === 1) {
                        $data_permiso->existe = true;
                    }
                }
                else {
                    $valida = $this->valida_datos_permiso(adm_accion: $adm_accion,adm_grupo_id:  $adm_grupo_id,
                        adm_seccion:  $adm_seccion);
                    if (errores::$error) {
                        return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
                    }
                    $data_permiso = $this->genera_session_permite(adm_accion: $adm_accion,adm_grupo_id:  $adm_grupo_id,
                        adm_seccion:  $adm_seccion);
                    if (errores::$error) {
                        return $this->error->error(mensaje: 'Error al asignar permite en session', data: $data_permiso);
                    }
                }
            }
        }
        return $data_permiso->existe;

    }

    /**
     * Obtiene un usuario por id
     * @version 1.138.31
     * @param int $usuario_id Usuario a obtener
     * @param PDO $link Conexion a base de datos
     * @return array
     */
    public static function usuario(int $usuario_id, PDO $link):array{
       if($usuario_id <=0){
           return (new errores())->error('Error usuario_id debe ser mayor a 0',$usuario_id);
       }
        $usuario_modelo = new adm_usuario($link);
        $usuario_modelo->registro_id = $usuario_id;
        $usuario = $usuario_modelo->obten_data();
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener usuario',data: $usuario);
        }

        return $usuario;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función devuelve un array con la información del usuario activo.
     *
     * @return array - Devuelve un array con información sobre el usuario activo en caso de éxito.
     *                 En caso de error (no existe un ID de usuario en la sesión o el ID es negativo),
     *                 devuelve un mensaje de error.
     *
     * @version 17.19.0
     */
    final public function usuario_activo():array{
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error no existe session usuario id',data: $_SESSION);
        }

        if((int)$_SESSION['usuario_id'] < 0){
            return  $this->error->error(mensaje: 'Error el id debe ser mayor a 0 en el modelo '.$this->tabla,
                data: $_SESSION['usuario_id']);
        }

        $this->registro_id = $_SESSION['usuario_id'];
        $usuario = $this->obten_data();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener usuario activo',data: $usuario);
        }
        return $usuario;
    }

    /**
     * @param int $adm_grupo_id
     * @return array
     */
    final public function usuarios_por_grupo(int $adm_grupo_id): array
    {
        if($adm_grupo_id <=0 ){
            return $this->error->error(mensaje: 'Error adm_grupo_id debe ser mayor a 0',data: $adm_grupo_id);
        }
        $filtro['adm_grupo.id'] = $adm_grupo_id;
        $r_usuario = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener usuarios',data: $r_usuario);
        }
        return $r_usuario->registros;
    }

    /**
     * Valida e integra una validacion de existe para session
     * @param bool $existe Si true val session existe
     * @return int
     * @version 10.41.2
     */
    private function val_session(bool $existe): int
    {
        $val_session = 0;
        if ($existe) {
            $val_session = 1;
        }
        return $val_session;
    }

    /**
     * Verifica si una session existe en base de datos asi como su permiso
     * @param array $filtro Filtro a integrar para validacion
     * @return array|stdClass
     */
    private function val_session_existe(array $filtro): array|stdClass
    {
        if(count($filtro) === 0){
            return $this->error->error(mensaje: 'Error filtro esta vacio', data: $filtro, es_final: true);
        }
        $existe = (new adm_accion_grupo(link: $this->link))->existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe', data: $existe);
        }

        $val_session = $this->val_session(existe: $existe);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener val_session', data: $val_session);
        }
        $data = new stdClass();
        $data->existe = $existe;
        $data->val_session = $val_session;
        return $data;
    }

    /**
     * TOTAL
     * Valida los datos del permiso sean correctos a nivel parametros.
     *
     * Esta función recibe los datos que representan a un permiso de un grupo de usuario y realiza validaciones
     * sobre los mismos antes de permitir operaciones adicionales. En caso de encontrar un error, detendrá
     * la operación y generará un error.
     *
     * @param string $adm_accion Acción del administrador a ser validada.
     * @param int    $adm_grupo_id ID del grupo al que pertenece el administrador.
     * @param string $adm_seccion Sección del administrador a ser validada.
     *
     * @return true|array Resultado de las validaciones. En caso de un error, se genera un error.
     * @version 16.31.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.orm.adm_usuario.valida_datos_permiso.24.15.0
     */
    private function valida_datos_permiso(string $adm_accion, int $adm_grupo_id, string $adm_seccion): true|array
    {
        $adm_seccion = trim($adm_seccion);
        if($adm_seccion === ''){
            return $this->error->error(mensaje: 'Error adm_seccion esta vacia', data: $adm_seccion, es_final: true);
        }
        $adm_accion = trim($adm_accion);
        if($adm_accion === ''){
            return $this->error->error(mensaje: 'Error adm_accion esta vacia', data: $adm_accion, es_final: true);
        }
        if($adm_grupo_id <= 0){
            return $this->error->error(mensaje: 'Error adm_grupo_id debe ser mayor a 0',data:  $adm_grupo_id,
                es_final: true);
        }
        return true;
    }

    /**
     * Valida que un usuario y un password exista
     *
     * @param string $password Contraseña a verificar
     * @param string $usuario Usuario a verificar
     * @param string $accion_header elemento para regresar a accion especifica en el controlador
     * @param string $seccion_header elemento para regresar a seccion especifica en el controlador
     * @return array
     *
     * @function $r_usuario = $adm_usuario->filtro_and(filtro: $filtro); maqueta los datos obtenidos de un
     * usuario, antes siendo revisados por un filtro.
     * @version 2.25.3
     */
    public function valida_usuario_password(string $password, string $usuario, string $accion_header = '',
                                            string $seccion_header = ''): array
    {
        if($usuario === ''){
            return $this->error->error(mensaje: 'El usuario no puede ir vacio',data: $usuario,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }
        if($password === ''){
            return $this->error->error(mensaje: 'El $password no puede ir vacio',data: $password,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }

        $filtro['adm_usuario.user'] = $usuario;
        $filtro['adm_usuario.password'] = $password;
        $filtro['adm_usuario.status'] = 'activo';
        $r_usuario = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener usuario',data: $r_usuario,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }

        if((int)$r_usuario->n_registros === 0){
            return $this->error->error(mensaje: 'Error al validar usuario y pass ',data: $usuario,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }
        if((int)$r_usuario->n_registros > 1){
            return $this->error->error(mensaje: 'Error al validar usuario y pass ',data: $usuario,
                seccion_header: $seccion_header, accion_header: $accion_header);
        }
        return $r_usuario->registros[0];
	}
}
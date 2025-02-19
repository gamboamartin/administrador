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
     * REG
     * Genera un objeto con la información de un permiso de usuario.
     *
     * Esta función recibe como parámetros una acción y una sección del sistema, los valida y devuelve un objeto `stdClass`
     * con estos valores. Si alguno de los valores está vacío, retorna un error detallado.
     *
     * ---
     *
     * ### Ejemplo de Uso:
     * ```php
     * $adm_usuario = new adm_usuario($pdo);
     * $resultado = $adm_usuario->data_permiso('modifica', 'usuarios');
     *
     * if ($resultado instanceof stdClass) {
     *     echo "Acción: " . $resultado->adm_accion . "\n";
     *     echo "Sección: " . $resultado->adm_seccion . "\n";
     * } else {
     *     echo "Error: " . $resultado['mensaje'];
     * }
     * ```
     *
     * ---
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Entrada válida:**
     * ```php
     * $adm_accion = "alta";
     * $adm_seccion = "productos";
     * ```
     * **Salida esperada (`stdClass`):**
     * ```php
     * stdClass Object
     * (
     *     [adm_seccion] => productos
     *     [adm_accion] => alta
     * )
     * ```
     *
     * **Entrada con acción vacía (Error):**
     * ```php
     * $adm_accion = "";
     * $adm_seccion = "clientes";
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error adm_accion esta vacia',
     *     'data' => '',
     *     'es_final' => true
     * ]
     * ```
     *
     * **Entrada con sección vacía (Error):**
     * ```php
     * $adm_accion = "modifica";
     * $adm_seccion = "";
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error adm_seccion esta vacia',
     *     'data' => '',
     *     'es_final' => true
     * ]
     * ```
     *
     * ---
     *
     * @param string $adm_accion Acción del administrador a validar.
     * @param string $adm_seccion Sección del sistema donde se aplicará la acción.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con los valores si la validación es exitosa.
     *                        En caso de error, devuelve un array con el mensaje y los datos del error.
     *
     * @throws array Si algún parámetro no es válido, devuelve un error con los detalles.
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
     * REG
     * Genera un filtro de búsqueda para validar permisos de un usuario en una sección específica.
     *
     * Esta función construye un array con los criterios de filtrado necesarios para validar si un grupo de usuarios
     * tiene acceso a una determinada acción dentro de una sección específica del sistema.
     *
     * **Validaciones realizadas:**
     * - `adm_accion`: No debe estar vacío.
     * - `adm_seccion`: No debe estar vacío.
     * - `adm_grupo_id`: Debe ser un número entero mayor a 0.
     *
     * En caso de error en las validaciones, devuelve un array con un mensaje de error.
     * Si los datos son correctos, retorna un array con los parámetros de filtrado.
     *
     * ---
     *
     * ### Ejemplo de Uso:
     * ```php
     * $adm_usuario = new adm_usuario($pdo);
     * $filtro = $adm_usuario->filtro('modifica', 3, 'usuarios');
     *
     * if (isset($filtro['mensaje'])) {
     *     echo "Error: " . $filtro['mensaje'];
     * } else {
     *     print_r($filtro);
     * }
     * ```
     *
     * ---
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Entrada válida:**
     * ```php
     * $adm_accion = "alta";
     * $adm_grupo_id = 5;
     * $adm_seccion = "productos";
     * ```
     * **Salida esperada (Array de filtros):**
     * ```php
     * [
     *     "adm_grupo.id" => 5,
     *     "adm_accion.descripcion" => "alta",
     *     "adm_grupo.status" => "activo",
     *     "adm_accion.status" => "activo",
     *     "adm_seccion.descripcion" => "productos",
     *     "adm_seccion.status" => "activo"
     * ]
     * ```
     *
     * **Entrada con acción vacía (Error):**
     * ```php
     * $adm_accion = "";
     * $adm_grupo_id = 2;
     * $adm_seccion = "clientes";
     * ```
     * **Salida esperada (Array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error adm_accion esta vacia',
     *     'data' => '',
     *     'es_final' => true
     * ]
     * ```
     *
     * **Entrada con sección vacía (Error):**
     * ```php
     * $adm_accion = "modifica";
     * $adm_grupo_id = 1;
     * $adm_seccion = "";
     * ```
     * **Salida esperada (Array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error adm_seccion esta vacia',
     *     'data' => '',
     *     'es_final' => true
     * ]
     * ```
     *
     * **Entrada con `adm_grupo_id` inválido (Error):**
     * ```php
     * $adm_accion = "elimina";
     * $adm_grupo_id = 0;
     * $adm_seccion = "usuarios";
     * ```
     * **Salida esperada (Array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error adm_grupo_id debe ser mayor a 0',
     *     'data' => 0
     * ]
     * ```
     *
     * ---
     *
     * @param string $adm_accion Acción del sistema a validar.
     * @param int $adm_grupo_id ID del grupo de usuarios que se validará.
     * @param string $adm_seccion Sección en la que se ejecutará la acción.
     *
     * @return array Retorna un array con los criterios de filtrado si la validación es correcta.
     *               En caso de error, devuelve un array con un mensaje de error y el dato inválido.
     *
     * @throws array Si algún parámetro no es válido, devuelve un array con detalles del error.
     */
    private function filtro(string $adm_accion, int $adm_grupo_id, string $adm_seccion): array
    {
        $adm_accion = trim($adm_accion);
        if($adm_accion === ''){
            return $this->error->error(mensaje: 'Error adm_accion esta vacia', data: $adm_accion, es_final: true);
        }
        $adm_seccion = trim($adm_seccion);
        if($adm_seccion === ''){
            return $this->error->error(mensaje: 'Error adm_seccion esta vacia', data: $adm_seccion, es_final: true);
        }

        if($adm_grupo_id <= 0){
            return $this->error->error(mensaje: 'Error adm_grupo_id debe ser mayor a 0', data: $adm_grupo_id);
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
     * REG
     * Valida los datos requeridos para verificar un permiso de usuario en el sistema.
     *
     * Esta función verifica que los parámetros proporcionados sean válidos antes de continuar con
     * la validación de permisos de un usuario. Comprueba lo siguiente:
     * - `adm_accion`: No debe estar vacío.
     * - `adm_seccion`: No debe estar vacío.
     * - `adm_grupo_id`: Debe ser un número entero mayor que 0.
     *
     * Si alguno de estos valores es inválido, la función devuelve un error detallado con la información incorrecta.
     * En caso contrario, retorna `true`, indicando que la validación fue exitosa.
     *
     * ---
     *
     * ### Ejemplo de Uso:
     * ```php
     * $adm_usuario = new adm_usuario($pdo);
     * $resultado = $adm_usuario->valida_datos_permiso('modifica', 3, 'usuarios');
     *
     * if ($resultado === true) {
     *     echo "Permiso validado correctamente.";
     * } else {
     *     echo "Error: " . $resultado['mensaje'];
     * }
     * ```
     *
     * ---
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Entrada válida:**
     * ```php
     * $adm_accion = "alta";
     * $adm_grupo_id = 5;
     * $adm_seccion = "productos";
     * ```
     * **Salida esperada:**
     * ```php
     * true
     * ```
     *
     * **Entrada con acción vacía (Error):**
     * ```php
     * $adm_accion = "";
     * $adm_grupo_id = 2;
     * $adm_seccion = "clientes";
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'mensaje' => 'Error adm_accion esta vacia',
     *     'data' => '',
     *     'es_final' => true
     * ]
     * ```
     *
     * **Entrada con sección vacía (Error):**
     * ```php
     * $adm_accion = "modifica";
     * $adm_grupo_id = 1;
     * $adm_seccion = "";
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'mensaje' => 'Error adm_seccion esta vacia',
     *     'data' => '',
     *     'es_final' => true
     * ]
     * ```
     *
     * **Entrada con `adm_grupo_id` inválido (Error):**
     * ```php
     * $adm_accion = "elimina";
     * $adm_grupo_id = 0;
     * $adm_seccion = "usuarios";
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'mensaje' => 'Error adm_grupo_id debe ser mayor a 0',
     *     'data' => 0,
     *     'es_final' => true
     * ]
     * ```
     *
     * ---
     *
     * @param string $adm_accion Acción del administrador a validar.
     * @param int $adm_grupo_id ID del grupo de usuario al que pertenece.
     * @param string $adm_seccion Sección del sistema donde se aplicará la acción.
     *
     * @return true|array Retorna `true` si todos los valores son válidos.
     *                    En caso de error, devuelve un array con el mensaje y los datos del error.
     *
     * @throws array Si algún parámetro no es válido, devuelve un error con los detalles.
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
            return $this->error->error(mensaje: 'Error adm_grupo_id debe ser mayor a 0', data: $adm_grupo_id,
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
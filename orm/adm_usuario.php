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

    /**
     * REG
     * Genera y almacena en la sesión un permiso basado en la acción, grupo y sección especificados.
     *
     * Esta función valida los datos del permiso, obtiene la información necesaria y la almacena en la sesión.
     * Si alguno de los pasos falla, se genera un mensaje de error detallado.
     *
     * ---
     *
     * ### **Proceso de la función:**
     * 1. **Valida los parámetros:** Se verifica que `$adm_accion`, `$adm_grupo_id` y `$adm_seccion` sean correctos.
     * 2. **Obtiene los datos del permiso:** Se extrae la información relevante mediante `get_data_permiso()`.
     * 3. **Registra el permiso en sesión:** Se guarda en `$_SESSION['permite']` utilizando `session_permite()`.
     * 4. **Devuelve el objeto `$data_permiso`** si todo es correcto.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $adm_usuario = new adm_usuario($pdo);
     *
     * $permiso = $adm_usuario->genera_session_permite('modifica', 3, 'usuarios');
     *
     * if ($permiso instanceof stdClass) {
     *     echo "Permiso registrado con éxito.";
     * } else {
     *     echo "Error: " . $permiso['mensaje'];
     * }
     * ```
     *
     * ---
     *
     * ### **Ejemplo de Entrada y Salida:**
     *
     * **Entrada válida:**
     * ```php
     * $adm_accion = "alta";
     * $adm_grupo_id = 5;
     * $adm_seccion = "productos";
     * ```
     * **Salida esperada (`stdClass` con datos de permiso y `$_SESSION` actualizada):**
     * ```php
     * stdClass Object
     * (
     *     [adm_accion] => alta
     *     [adm_seccion] => productos
     *     [val_session] => 1
     *     [existe] => true
     * )
     * $_SESSION['permite'][5]['productos']['alta'] = 1;
     * ```
     *
     * **Entrada con `adm_accion` vacía (Error):**
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
     * @param string $adm_accion Acción a validar.
     * @param int $adm_grupo_id ID del grupo de usuarios a validar.
     * @param string $adm_seccion Sección donde se ejecutará la acción.
     *
     * @return stdClass|array Retorna un objeto con los datos del permiso si la validación es exitosa.
     *                        En caso de error, devuelve un array con el mensaje de error y el dato inválido.
     *
     * @throws array Si algún parámetro no es válido, devuelve un error con los detalles.
     */
    private function genera_session_permite(string $adm_accion, int $adm_grupo_id, string $adm_seccion): array|stdClass
    {
        // 1. Validar los datos de permiso
        $valida = $this->valida_datos_permiso(adm_accion: $adm_accion, adm_grupo_id: $adm_grupo_id,
            adm_seccion: $adm_seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        // 2. Obtener la información del permiso
        $data_permiso = $this->get_data_permiso(adm_accion: $adm_accion, adm_grupo_id: $adm_grupo_id,
            adm_seccion: $adm_seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener data_permiso', data: $data_permiso);
        }

        // 3. Registrar el permiso en sesión
        $session_permite = $this->session_permite(adm_grupo_id: $adm_grupo_id, data_permiso: $data_permiso);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar permite en session', data: $session_permite);
        }

        // 4. Retornar la información del permiso
        return $data_permiso;
    }


    /**
     * REG
     * Obtiene y valida los permisos de un grupo de usuarios en una acción y sección específica.
     *
     * Esta función realiza varias validaciones para asegurarse de que los parámetros proporcionados son correctos.
     * Luego, genera los datos del permiso y verifica si la sesión está validada en la base de datos.
     * Finalmente, devuelve un objeto con la información del permiso y la validación de sesión.
     *
     * ---
     *
     * ### **Validaciones realizadas:**
     * - Verifica que `adm_accion`, `adm_seccion` y `adm_grupo_id` sean correctos (`valida_datos_permiso`).
     * - Genera un objeto con la acción y la sección (`data_permiso`).
     * - Obtiene el estado de la sesión validada (`get_val_session`).
     * - Devuelve un objeto `stdClass` con los datos del permiso y el estado de la sesión (`val_session` y `existe`).
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $adm_usuario = new adm_usuario($pdo);
     *
     * $resultado = $adm_usuario->get_data_permiso('modifica', 3, 'usuarios');
     *
     * if ($resultado instanceof stdClass) {
     *     echo "Acción: " . $resultado->adm_accion . "\n";
     *     echo "Sección: " . $resultado->adm_seccion . "\n";
     *     echo "Permiso existe: " . ($resultado->existe ? 'Sí' : 'No') . "\n";
     *     echo "Validación de sesión: " . $resultado->val_session . "\n";
     * } else {
     *     echo "Error: " . $resultado['mensaje'];
     * }
     * ```
     *
     * ---
     *
     * ### **Ejemplo de Entrada y Salida:**
     *
     * **Entrada válida con datos existentes:**
     * ```php
     * $adm_accion = "alta";
     * $adm_grupo_id = 5;
     * $adm_seccion = "productos";
     * ```
     * **Salida esperada (`stdClass` con datos):**
     * ```php
     * stdClass Object
     * (
     *     [adm_accion] => alta
     *     [adm_seccion] => productos
     *     [existe] => true
     *     [val_session] => 1
     * )
     * ```
     *
     * **Entrada con `adm_grupo_id` inválido (Error):**
     * ```php
     * $adm_accion = "modifica";
     * $adm_grupo_id = 0;
     * $adm_seccion = "clientes";
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error adm_grupo_id debe ser mayor a 0',
     *     'data' => 0,
     *     'es_final' => true
     * ]
     * ```
     *
     * **Entrada con `adm_accion` vacía (Error):**
     * ```php
     * $adm_accion = "";
     * $adm_grupo_id = 2;
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
     * **Entrada con `adm_seccion` vacía (Error):**
     * ```php
     * $adm_accion = "modifica";
     * $adm_grupo_id = 3;
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
     * @param int $adm_grupo_id ID del grupo de usuario al que pertenece.
     * @param string $adm_seccion Sección del sistema donde se aplicará la acción.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con:
     *                        - `adm_accion`: Acción validada.
     *                        - `adm_seccion`: Sección validada.
     *                        - `existe`: `true` si el permiso existe, `false` si no.
     *                        - `val_session`: `1` si la sesión es válida, `0` si no.
     *                        Si hay un error, retorna un array con un mensaje detallado.
     *
     * @throws array Si algún parámetro no es válido, devuelve un error con los detalles.
     */
    private function get_data_permiso(string $adm_accion, int $adm_grupo_id, string $adm_seccion): array|stdClass
    {
        // Validación de los datos de permiso
        $valida = $this->valida_datos_permiso(
            adm_accion: $adm_accion,
            adm_grupo_id: $adm_grupo_id,
            adm_seccion: $adm_seccion
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        // Genera el objeto con los datos del permiso
        $data_permiso = $this->data_permiso(adm_accion: $adm_accion, adm_seccion: $adm_seccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener data_permiso', data: $data_permiso);
        }

        // Obtiene el estado de validación de sesión
        $data = $this->get_val_session(adm_grupo_id: $adm_grupo_id, data_permiso: $data_permiso);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener val_session', data: $data);
        }

        // Agrega la información de la sesión al objeto del permiso
        $data_permiso->val_session = $data->val_session;
        $data_permiso->existe = $data->existe;

        return $data_permiso;
    }


    /**
     * REG
     * Obtiene el estado de validación de sesión para un grupo de usuarios en una acción y sección específica.
     *
     * Esta función valida los datos del permiso (`data_permiso`), verifica la existencia de la sesión en la base de datos
     * y determina si el grupo de usuarios tiene acceso a la acción y sección indicadas.
     *
     * ---
     *
     * ### **Validaciones realizadas:**
     * - Comprueba que `data_permiso` contenga las claves necesarias (`adm_accion`, `adm_seccion`).
     * - Verifica que `adm_grupo_id` sea un número entero mayor a `0`.
     * - Genera un filtro de búsqueda basado en la acción, grupo y sección.
     * - Verifica si la sesión de permisos existe en la base de datos.
     * - Devuelve un objeto `stdClass` con `existe` (booleano) y `val_session` (entero).
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $adm_usuario = new adm_usuario($pdo);
     *
     * $data_permiso = new stdClass();
     * $data_permiso->adm_accion = 'modifica';
     * $data_permiso->adm_seccion = 'usuarios';
     *
     * $resultado = $adm_usuario->get_val_session(3, $data_permiso);
     *
     * if ($resultado instanceof stdClass) {
     *     echo "Existe: " . ($resultado->existe ? 'Sí' : 'No') . "\n";
     *     echo "Validación de sesión: " . $resultado->val_session . "\n";
     * } else {
     *     echo "Error: " . $resultado['mensaje'];
     * }
     * ```
     *
     * ---
     *
     * ### **Ejemplo de Entrada y Salida:**
     *
     * **Entrada válida con datos existentes:**
     * ```php
     * $adm_grupo_id = 5;
     * $data_permiso = (object) [
     *     'adm_accion' => 'alta',
     *     'adm_seccion' => 'productos'
     * ];
     * ```
     * **Salida esperada (`stdClass` con datos):**
     * ```php
     * stdClass Object
     * (
     *     [existe] => true
     *     [val_session] => 1
     * )
     * ```
     *
     * **Entrada con `adm_grupo_id` inválido (Error):**
     * ```php
     * $adm_grupo_id = 0;
     * $data_permiso = (object) [
     *     'adm_accion' => 'modifica',
     *     'adm_seccion' => 'clientes'
     * ];
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error adm_grupo_id debe ser mayor a 0',
     *     'data' => 0,
     *     'es_final' => true
     * ]
     * ```
     *
     * **Entrada con datos incompletos en `$data_permiso` (Error):**
     * ```php
     * $adm_grupo_id = 3;
     * $data_permiso = (object) [
     *     'adm_accion' => 'modifica'
     * ];
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error al validar $data_permiso',
     *     'data' => [...],
     *     'es_final' => true
     * ]
     * ```
     *
     * ---
     *
     * @param int $adm_grupo_id ID del grupo de usuarios que se validará.
     * @param stdClass $data_permiso Objeto con los datos de permiso (`adm_accion`, `adm_seccion`).
     *
     * @return array|stdClass Retorna un objeto `stdClass` con:
     *                        - `existe` (bool): Indica si el permiso existe (`true`) o no (`false`).
     *                        - `val_session` (int): `1` si la sesión es válida, `0` si no.
     *                        Si hay un error, retorna un array con un mensaje detallado.
     *
     * @throws array Si `adm_grupo_id` es inválido o los datos de `data_permiso` son incorrectos, retorna un error con detalles.
     */
    private function get_val_session(int $adm_grupo_id, stdClass $data_permiso): array|stdClass
    {
        $keys = array('adm_accion', 'adm_seccion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $data_permiso);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar $data_permiso', data: $valida);
        }

        if ($adm_grupo_id <= 0) {
            return $this->error->error(mensaje: 'Error adm_grupo_id debe ser mayor a 0', data: $adm_grupo_id, es_final: true);
        }

        $filtro = $this->filtro(
            adm_accion: $data_permiso->adm_accion,
            adm_grupo_id: $adm_grupo_id,
            adm_seccion: $data_permiso->adm_seccion
        );

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
     * REG
     * Registra un permiso en la sesión del usuario basado en un grupo y datos de permiso.
     *
     * Esta función valida que los parámetros sean correctos y, si todo es válido,
     * almacena en `$_SESSION` si un grupo de usuarios tiene permitido ejecutar una acción en una sección específica.
     *
     * ---
     *
     * ### **Validaciones realizadas:**
     * - Verifica que `$adm_grupo_id` sea mayor que `0`.
     * - Verifica que `$data_permiso->adm_seccion` y `$data_permiso->adm_accion` existan y no estén vacíos.
     * - Si `val_session` no está definido en `$data_permiso`, se establece en `0`.
     * - Se almacena la validación en la variable de sesión `$_SESSION['permite']`.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $adm_usuario = new adm_usuario($pdo);
     *
     * $data_permiso = new stdClass();
     * $data_permiso->adm_seccion = "productos";
     * $data_permiso->adm_accion = "modifica";
     * $data_permiso->val_session = 1;
     *
     * $resultado = $adm_usuario->session_permite(3, $data_permiso);
     *
     * print_r($_SESSION['permite'][3]); // Muestra los permisos almacenados en la sesión
     * ```
     *
     * ---
     *
     * ### **Ejemplo de Entrada y Salida:**
     *
     * **Entrada válida:**
     * ```php
     * $adm_grupo_id = 5;
     * $data_permiso = new stdClass();
     * $data_permiso->adm_seccion = "clientes";
     * $data_permiso->adm_accion = "alta";
     * $data_permiso->val_session = 1;
     * ```
     * **Salida esperada (`$_SESSION` actualizada con permiso registrado):**
     * ```php
     * $_SESSION['permite'][5] = [
     *     "clientes" => [
     *         "alta" => 1
     *     ]
     * ];
     * ```
     *
     * **Entrada con `adm_grupo_id` inválido (Error):**
     * ```php
     * $adm_grupo_id = 0;
     * $data_permiso = new stdClass();
     * $data_permiso->adm_seccion = "productos";
     * $data_permiso->adm_accion = "modifica";
     * ```
     * **Salida esperada (Array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error $adm_grupo_id debe ser mayor a 0',
     *     'data' => 0,
     *     'es_final' => true
     * ]
     * ```
     *
     * **Entrada con `adm_seccion` vacía (Error):**
     * ```php
     * $adm_grupo_id = 3;
     * $data_permiso = new stdClass();
     * $data_permiso->adm_seccion = "";
     * $data_permiso->adm_accion = "modifica";
     * ```
     * **Salida esperada (Array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error $data_permiso->adm_seccion esta vacia',
     *     'data' => $data_permiso,
     *     'es_final' => true
     * ]
     * ```
     *
     * **Entrada sin `val_session` definido:**
     * ```php
     * $adm_grupo_id = 2;
     * $data_permiso = new stdClass();
     * $data_permiso->adm_seccion = "ventas";
     * $data_permiso->adm_accion = "consulta";
     * ```
     * **Salida esperada (`val_session` se establece en `0` automáticamente):**
     * ```php
     * $_SESSION['permite'][2] = [
     *     "ventas" => [
     *         "consulta" => 0
     *     ]
     * ];
     * ```
     *
     * ---
     *
     * @param int $adm_grupo_id ID del grupo de usuario.
     * @param stdClass $data_permiso Datos del permiso a registrar en sesión.
     *
     * @return array Retorna el arreglo actualizado con los permisos del grupo de usuarios.
     *               En caso de error, devuelve un array con el mensaje de error.
     *
     * @throws array Si algún parámetro no es válido, devuelve un error con los detalles.
     */
    private function session_permite(int $adm_grupo_id, stdClass $data_permiso): array
    {
        // Validar que el ID del grupo sea mayor a 0
        if ($adm_grupo_id <= 0) {
            return $this->error->error(
                mensaje: 'Error $adm_grupo_id debe ser mayor a 0',
                data: $adm_grupo_id,
                es_final: true
            );
        }

        // Validar que la sección exista en el objeto de permisos
        if (!isset($data_permiso->adm_seccion)) {
            return $this->error->error(
                mensaje: 'Error $data_permiso->adm_seccion debe existir',
                data: $data_permiso,
                es_final: true
            );
        }

        // Limpiar espacios en blanco de la sección
        $data_permiso->adm_seccion = trim($data_permiso->adm_seccion);
        if ($data_permiso->adm_seccion === '') {
            return $this->error->error(
                mensaje: 'Error $data_permiso->adm_seccion esta vacia',
                data: $data_permiso,
                es_final: true
            );
        }

        // Validar que la acción exista en el objeto de permisos
        if (!isset($data_permiso->adm_accion)) {
            return $this->error->error(
                mensaje: 'Error $data_permiso->adm_accion debe existir',
                data: $data_permiso,
                es_final: true
            );
        }

        // Limpiar espacios en blanco de la acción
        $data_permiso->adm_accion = trim($data_permiso->adm_accion);
        if ($data_permiso->adm_accion === '') {
            return $this->error->error(
                mensaje: 'Error $data_permiso->adm_accion esta vacia',
                data: $data_permiso,
                es_final: true
            );
        }

        // Si no está definido `val_session`, se establece en `0`
        if (!isset($data_permiso->val_session)) {
            $data_permiso->val_session = 0;
        }

        // Registrar permiso en la variable de sesión
        $_SESSION['permite'][$adm_grupo_id][$data_permiso->adm_seccion][$data_permiso->adm_accion]
            = (int)$data_permiso->val_session;

        return $_SESSION['permite'][$adm_grupo_id];
    }


    /**
     * REG
     * Verifica si el usuario actual tiene permiso para realizar una acción en una sección específica.
     *
     * Esta función consulta los permisos almacenados en la sesión del usuario. Si no encuentra un permiso previo,
     * intenta generarlo dinámicamente. Si el permiso existe, retorna `true`; de lo contrario, retorna `false`.
     *
     * ---
     *
     * ### **Proceso de la función:**
     * 1. **Obtener el ID del usuario:** Verifica si `$_SESSION['usuario_id']` está definido.
     * 2. **Obtener el ID del grupo:** Si el usuario tiene un grupo asociado (`$_SESSION['grupo_id']`), lo recupera.
     * 3. **Verificar en `$_SESSION` si el permiso ya está almacenado.**
     * 4. **Si el permiso no existe, lo genera con `genera_session_permite()`.**
     * 5. **Retorna `true` si el usuario tiene permiso, `false` si no lo tiene.**
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $adm_usuario = new adm_usuario($pdo);
     *
     * $tiene_permiso = $adm_usuario->tengo_permiso('modifica', 'usuarios');
     *
     * if ($tiene_permiso === true) {
     *     echo "El usuario tiene permiso.";
     * } else {
     *     echo "El usuario NO tiene permiso.";
     * }
     * ```
     *
     * ---
     *
     * ### **Ejemplo de Entrada y Salida:**
     *
     * **Caso: Usuario con permiso almacenado en sesión**
     * ```php
     * $_SESSION['usuario_id'] = 1;
     * $_SESSION['grupo_id'] = 2;
     * $_SESSION['permite'][2]['usuarios']['modifica'] = 1;
     * ```
     * **Salida esperada:**
     * ```php
     * true
     * ```
     *
     * **Caso: Usuario sin permiso almacenado, pero válido**
     * ```php
     * $_SESSION['usuario_id'] = 3;
     * $_SESSION['grupo_id'] = 5;
     * ```
     * **Salida esperada (Después de generarlo dinámicamente):**
     * ```php
     * true
     * ```
     *
     * **Caso: Usuario sin permiso y sin generación válida**
     * ```php
     * $_SESSION['usuario_id'] = 4;
     * $_SESSION['grupo_id'] = 6;
     * ```
     * **Salida esperada:**
     * ```php
     * false
     * ```
     *
     * ---
     *
     * ### **Casos de Error:**
     *
     * **Caso: Usuario sin sesión activa**
     * ```php
     * unset($_SESSION['usuario_id']);
     * ```
     * **Salida esperada:**
     * ```php
     * false
     * ```
     *
     * **Caso: Grupo de usuario no definido**
     * ```php
     * $_SESSION['usuario_id'] = 7;
     * unset($_SESSION['grupo_id']);
     * ```
     * **Salida esperada:**
     * ```php
     * false
     * ```
     *
     * ---
     *
     * @param string $adm_accion Acción que se quiere validar.
     * @param string $adm_seccion Sección en la que se aplicará la acción.
     *
     * @return bool|array Retorna `true` si el usuario tiene permiso, `false` si no lo tiene.
     *                    En caso de error, devuelve un array con el mensaje de error.
     *
     * @throws array Si ocurre un error en la validación o en la generación del permiso, se devuelve un array de error.
     */
    final public function tengo_permiso(string $adm_accion, string $adm_seccion): array|bool
    {
        // 1. Obtener el ID del usuario de la sesión
        $adm_usuario_id = -1;
        if (isset($_SESSION['usuario_id'])) {
            $adm_usuario_id = $_SESSION['usuario_id'];
        }

        // Inicializar el objeto de permiso
        $data_permiso = new stdClass();
        $data_permiso->existe = false;

        // 2. Verificar si el usuario tiene un ID válido
        if ((int)$adm_usuario_id > 0) {

            // 3. Obtener el ID del grupo de usuario
            $adm_grupo_id = -1;
            if (isset($_SESSION['grupo_id'])) {
                $adm_grupo_id = (int)$_SESSION['grupo_id'];
            }

            // 4. Si el grupo de usuario es válido
            if ($adm_grupo_id > 0) {

                // 5. Verificar si el permiso ya está en sesión
                if (isset($_SESSION['permite'][$adm_grupo_id][$adm_seccion][$adm_accion])) {
                    if ((int)$_SESSION['permite'][$adm_grupo_id][$adm_seccion][$adm_accion] === 1) {
                        $data_permiso->existe = true;
                    }
                } else {
                    // 6. Validar los datos del permiso antes de generarlo
                    $valida = $this->valida_datos_permiso(adm_accion: $adm_accion, adm_grupo_id: $adm_grupo_id,
                        adm_seccion: $adm_seccion);
                    if (errores::$error) {
                        return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
                    }

                    // 7. Generar el permiso si no estaba almacenado en sesión
                    $data_permiso = $this->genera_session_permite(adm_accion: $adm_accion, adm_grupo_id: $adm_grupo_id,
                        adm_seccion: $adm_seccion);
                    if (errores::$error) {
                        return $this->error->error(mensaje: 'Error al asignar permiso en sesión', data: $data_permiso);
                    }
                }
            }
        }

        // 8. Retornar si el usuario tiene permiso o no
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
     * REG
     * Convierte un valor booleano en un valor entero representando la validez de una sesión.
     *
     * Esta función recibe un booleano (`true` o `false`) y lo convierte en un entero `1` o `0`,
     * respectivamente. Se utiliza para determinar si una sesión de usuario tiene un permiso
     * válido en la aplicación.
     *
     * ---
     *
     * ### Ejemplo de Uso:
     * ```php
     * $adm_usuario = new adm_usuario($pdo);
     * $resultado = $adm_usuario->val_session(true);
     * echo $resultado; // 1
     *
     * $resultado = $adm_usuario->val_session(false);
     * echo $resultado; // 0
     * ```
     *
     * ---
     *
     * ### Ejemplo de Entrada y Salida:
     *
     * **Entrada válida con `true`:**
     * ```php
     * $existe = true;
     * ```
     * **Salida esperada:**
     * ```php
     * 1
     * ```
     *
     * **Entrada válida con `false`:**
     * ```php
     * $existe = false;
     * ```
     * **Salida esperada:**
     * ```php
     * 0
     * ```
     *
     * ---
     *
     * @param bool $existe Indica si la sesión es válida (`true`) o no (`false`).
     *
     * @return int Retorna `1` si la sesión es válida, `0` si no lo es.
     */
    private function val_session(bool $existe): int
    {
        return $existe ? 1 : 0;
    }


    /**
     * REG
     * Verifica si una sesión existe en la base de datos y obtiene su estado de validación.
     *
     * Esta función recibe un conjunto de filtros para buscar en la base de datos si una acción de grupo
     * (`adm_accion_grupo`) existe. Si la búsqueda es exitosa, determina si la sesión es válida (`1`) o no (`0`).
     *
     * ---
     *
     * ### **Validaciones realizadas:**
     * - Verifica que `$filtro` no esté vacío.
     * - Ejecuta una consulta para determinar si el filtro encuentra un registro en `adm_accion_grupo`.
     * - Convierte la existencia del registro en una sesión válida (`1` para `true`, `0` para `false`).
     * - Retorna un objeto `stdClass` con las propiedades `existe` (booleano) y `val_session` (entero).
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $adm_usuario = new adm_usuario($pdo);
     * $filtro = ['adm_accion.descripcion' => 'modifica', 'adm_grupo.id' => 3];
     *
     * $resultado = $adm_usuario->val_session_existe($filtro);
     *
     * if ($resultado instanceof stdClass) {
     *     echo "Existe: " . ($resultado->existe ? 'Sí' : 'No') . "\n";
     *     echo "Validación de sesión: " . $resultado->val_session . "\n";
     * } else {
     *     echo "Error: " . $resultado['mensaje'];
     * }
     * ```
     *
     * ---
     *
     * ### **Ejemplo de Entrada y Salida:**
     *
     * **Entrada válida con filtro existente:**
     * ```php
     * $filtro = [
     *     'adm_accion.descripcion' => 'alta',
     *     'adm_grupo.id' => 5
     * ];
     * ```
     * **Salida esperada (`stdClass` con datos):**
     * ```php
     * stdClass Object
     * (
     *     [existe] => true
     *     [val_session] => 1
     * )
     * ```
     *
     * **Entrada con filtro vacío (Error):**
     * ```php
     * $filtro = [];
     * ```
     * **Salida esperada (array con error):**
     * ```php
     * [
     *     'mensaje' => 'Error filtro esta vacio',
     *     'data' => [],
     *     'es_final' => true
     * ]
     * ```
     *
     * **Entrada con filtro sin coincidencias en la BD:**
     * ```php
     * $filtro = [
     *     'adm_accion.descripcion' => 'elimina',
     *     'adm_grupo.id' => 99
     * ];
     * ```
     * **Salida esperada (`stdClass` indicando que no existe):**
     * ```php
     * stdClass Object
     * (
     *     [existe] => false
     *     [val_session] => 0
     * )
     * ```
     *
     * ---
     *
     * @param array $filtro Filtro de búsqueda para verificar la existencia de una sesión en `adm_accion_grupo`.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con:
     *                        - `existe` (bool): Indica si el registro existe (`true`) o no (`false`).
     *                        - `val_session` (int): `1` si existe, `0` si no.
     *                        Si hay un error, retorna un array con un mensaje detallado.
     *
     * @throws array Si el filtro está vacío o ocurre un error en la consulta, retorna un array con información del error.
     */
    private function val_session_existe(array $filtro): array|stdClass
    {
        if (count($filtro) === 0) {
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
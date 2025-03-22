<?php
namespace base;
use config\database;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;
use Throwable;
use validacion\confs\configuraciones;

class conexion{
	public static PDO $link;
    private errores $error;
    private array $motores_validos = array('MYSQL','MARIADB','MSSQL');


    /**
     * @param stdClass $paths_conf Archivos de configuracion
     * @param string $motor
     */
    public function __construct(stdClass $paths_conf = new stdClass(), string $motor = 'MYSQL'){
        $error = new errores();
        $this->error = new errores();

        $valida = (new configuraciones())->valida_confs(paths_conf: $paths_conf);
        if(errores::$error){
            $error_ = $error->error(mensaje: "Error al validar configuraciones",data:$valida);
            print_r($error_);
            exit;
        }

        $link = $this->genera_link(motor: $motor);
        if(errores::$error){
            $error_ = $error->error(mensaje: "Error al generar link",data: $link);
            print_r($error_);
            exit;
        }

        self::$link = $link;

	}

    /**
     * REG
     * Asigna la codificación de caracteres a la conexión PDO mediante `SET NAMES`.
     *
     * Este método ejecuta una consulta SQL para establecer la codificación de caracteres utilizada
     * en la conexión a base de datos. Es comúnmente usado para asegurar que los datos sean enviados y recibidos
     * correctamente, especialmente en bases de datos que manejan UTF-8 u otras codificaciones.
     *
     * @param PDO $link Objeto de conexión activa a la base de datos.
     * @param string $set_name Cadena con la codificación deseada (por ejemplo: `'utf8'`, `'utf8mb4'`, `'latin1'`, etc).
     *
     * @return PDO|array Devuelve el mismo objeto `$link` si la asignación fue exitosa.
     *                   Si ocurre un error, se retorna un arreglo con información detallada del error.
     *
     * @example Asignar codificación UTF-8:
     * ```php
     * $conexion = new conexion();
     * $pdo = $conexion->genera_link('MYSQL');
     * if ($pdo instanceof PDO) {
     *     $pdo = $conexion->asigna_set_names($pdo, 'utf8');
     * }
     * ```
     *
     * @example Error por codificación vacía:
     * ```php
     * $pdo = new PDO(...); // conexión previa
     * $resultado = $conexion->asigna_set_names($pdo, '');
     * // Resultado:
     * // [
     * //   'mensaje' => 'Error $set_name no puede venir vacio',
     * //   'data' => '',
     * //   'es_final' => true,
     * //   ...
     * // ]
     * ```
     *
     * @version 1.0.0
     * @author Gamboa
     * @see https://dev.mysql.com/doc/refman/8.0/en/set-names.html
     */
    private function asigna_set_names(PDO $link, string $set_name): PDO|array
    {
        $set_name = trim($set_name);
        if($set_name === ''){
            return $this->error->error(mensaje: 'Error $set_name no puede venir vacio',data:$set_name,es_final: true);
        }
        try {
            $link->query("SET NAMES '$set_name'");
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al ejecutar SQL',data:$e, es_final: true);
        }
        return $link;
    }

    /**
     * REG
     * Asigna el modo SQL (`sql_mode`) a la conexión activa de base de datos.
     *
     * Esta función permite configurar restricciones y comportamientos especiales en MySQL/MariaDB,
     * como `STRICT_TRANS_TABLES`, `ONLY_FULL_GROUP_BY`, entre otros, usando el comando
     * `SET sql_mode = ...`.
     *
     * Esta configuración es útil para ajustar el nivel de estrictitud con que el motor de base de datos
     * interpreta las consultas SQL.
     *
     * @param PDO $link Objeto de conexión activa a la base de datos.
     * @param string $sql_mode Cadena con el modo SQL que se desea aplicar.
     *                         Ejemplo: `'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'`.
     *
     * @return PDO|array Devuelve el objeto `$link` si se ejecuta correctamente.
     *                   En caso de error, retorna un array con el mensaje y detalles del error.
     *
     * @example Asignar modo SQL estricto:
     * ```php
     * $conexion = new conexion();
     * $pdo = $conexion->genera_link('MYSQL');
     * if ($pdo instanceof PDO) {
     *     $pdo = $conexion->asigna_sql_mode($pdo, 'STRICT_TRANS_TABLES,NO_ZERO_DATE');
     * }
     * ```
     *
     * @example Error durante ejecución (modo inválido o conexión incorrecta):
     * ```php
     * $resultado = $conexion->asigna_sql_mode($pdo, 'modo_invalido');
     * if (is_array($resultado)) {
     *     echo "Error: " . $resultado['mensaje'];
     * }
     * ```
     *
     * @version 1.522.51
     * @author Gamboa
     * @see https://dev.mysql.com/doc/refman/8.0/en/sql-mode.html Documentación oficial sobre sql_mode
     */
    private function asigna_sql_mode(PDO $link, string $sql_mode): PDO|array
    {
        $sql = "SET sql_mode = '$sql_mode';";
        try {
            $link->query($sql);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al ejecutar SQL sql_mode',data:$e);
        }
        return $link;
    }

    /**
     * REG
     * Asigna el tiempo de espera (`timeout`) para bloqueos en transacciones InnoDB.
     *
     * Esta función ejecuta la instrucción SQL `SET innodb_lock_wait_timeout = valor` para definir
     * cuánto tiempo (en segundos) esperará una transacción antes de abortar si no puede obtener un bloqueo.
     * Es útil en entornos con alta concurrencia para evitar que las consultas queden colgadas.
     *
     * @param PDO $link Conexión activa a la base de datos mediante PDO.
     * @param int $time_out Tiempo de espera en segundos. Valor recomendado entre 5 y 120.
     *                      Por defecto en MySQL es 50 segundos.
     *
     * @return PDO|array Retorna el objeto PDO con la conexión si la asignación fue exitosa.
     *                   En caso de error (por ejemplo, sintaxis SQL incorrecta o conexión inválida),
     *                   devuelve un array con el mensaje y los detalles del error.
     *
     * @example Establecer timeout de 30 segundos:
     * ```php
     * $conexion = new conexion();
     * $pdo = $conexion->genera_link('MYSQL');
     * if ($pdo instanceof PDO) {
     *     $pdo = $conexion->asigna_timeout($pdo, 30);
     * }
     * ```
     *
     * @example Error por conexión inválida:
     * ```php
     * $pdo = null;
     * $resultado = $conexion->asigna_timeout($pdo, 60);
     * if (is_array($resultado)) {
     *     echo "Error al aplicar timeout: " . $resultado['mensaje'];
     * }
     * ```
     *
     * @version 1.523.51
     * @see https://dev.mysql.com/doc/refman/8.0/en/innodb-parameters.html#sysvar_innodb_lock_wait_timeout
     * @author Gamboa
     */
    private function asigna_timeout(PDO $link, int $time_out): PDO|array
    {
        $sql = "SET innodb_lock_wait_timeout=$time_out;";
        try {
            $link->query($sql);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al ejecutar SQL sql_mode',data:$e);
        }
        return $link;
    }

    /**
     * REG
     * Asigna múltiples parámetros de configuración a una conexión PDO.
     *
     * Esta función centraliza la aplicación de configuraciones necesarias sobre una conexión activa
     * a base de datos. Aplica en orden:
     * 1. La codificación de caracteres (`SET NAMES`).
     * 2. El modo SQL (`sql_mode`) para definir reglas estrictas o permisivas.
     * 3. El tiempo de espera de bloqueo InnoDB (`innodb_lock_wait_timeout`).
     *
     * Si alguna de las configuraciones falla, retorna un error con detalles.
     *
     * @param PDO $link Objeto PDO que representa la conexión activa a la base de datos.
     * @param string $set_name Codificación a aplicar, por ejemplo `'utf8mb4'`, `'utf8'`, `'latin1'`.
     *                         No puede venir vacío.
     * @param string $sql_mode Modo de operación SQL. Por ejemplo: `'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE'`.
     *                         Define cómo se comporta el motor ante datos erróneos.
     * @param int $time_out Tiempo máximo de espera (en segundos) para bloquear recursos InnoDB.
     *                      Usualmente entre 30 y 300.
     *
     * @return PDO|array Devuelve el objeto PDO si todos los parámetros fueron asignados correctamente.
     *                   En caso de error en alguna etapa, devuelve un arreglo con el mensaje y datos del error.
     *
     * @example Uso típico:
     * ```php
     * $conexion = new conexion();
     * $pdo = $conexion->genera_link('MYSQL');
     * if ($pdo instanceof PDO) {
     *     $pdo = $conexion->asigna_parametros_query(
     *         link: $pdo,
     *         set_name: 'utf8mb4',
     *         sql_mode: 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE',
     *         time_out: 60
     *     );
     *     if (is_array($pdo)) {
     *         echo "Error: " . $pdo['mensaje'];
     *     } else {
     *         echo "Parámetros aplicados correctamente.";
     *     }
     * }
     * ```
     *
     * @example Error si `$set_name` viene vacío:
     * ```php
     * $pdo = $conexion->asigna_parametros_query($pdo, '', 'STRICT_TRANS_TABLES', 30);
     * // Salida:
     * // [
     * //   'mensaje' => 'Error $set_name no puede venir vacio',
     * //   'data' => $pdo,
     * //   'es_final' => true
     * // ]
     * ```
     *
     * @version 1.0.0
     * @author Gamboa
     * @see https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html
     */
    private function asigna_parametros_query(PDO $link, string $set_name, string $sql_mode, int $time_out): PDO|array
    {
        $set_name = trim($set_name);
        if($set_name === ''){
            return $this->error->error(mensaje: 'Error $set_name no puede venir vacio',data:$link,
                es_final: true);
        }
        $link = $this->asigna_set_names(link: $link, set_name: $set_name);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al asignar codificacion en bd",data:$link);
        }

        $link = $this->asigna_sql_mode(link: $link, sql_mode: $sql_mode);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al asignar sql mode en bd",data:$link);
        }

        $link = $this->asigna_timeout(link:$link, time_out: $time_out);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al asignar sql mode en bd",data:$link);
        }

        return $link;
    }

    /**
     * REG
     * Establece la conexión con la base de datos usando PDO según el motor especificado.
     *
     * Este método intenta conectarse a una base de datos utilizando las credenciales y parámetros proporcionados
     * en `$conf_database`, y el motor especificado (`MYSQL`, `MARIADB` o `MSSQL`). Realiza validaciones previas
     * sobre los datos requeridos y retorna un objeto PDO en caso de éxito o un arreglo con información de error.
     *
     * @param database|stdClass $conf_database Objeto con los datos de conexión:
     *  - `db_host`: Host de la base de datos (e.g. 'localhost').
     *  - `db_name`: Nombre de la base de datos.
     *  - `db_user`: Usuario con acceso a la base de datos.
     *  - `db_password`: Contraseña del usuario.
     *  - `db_port` (opcional para MSSQL): Puerto de conexión.
     *
     * @param string $motor Motor de base de datos a utilizar. Valores válidos:
     *  - `'MYSQL'`
     *  - `'MARIADB'`
     *  - `'MSSQL'` (SQL Server)
     *
     * @return PDO|array|false Retorna:
     *  - Objeto PDO si la conexión es exitosa.
     *  - `array` con mensaje de error si ocurre alguna falla.
     *  - `false` en caso de error inesperado durante la conexión.
     *
     * @example Conexión a MySQL:
     * ```php
     * $conf = new stdClass();
     * $conf->db_host = 'localhost';
     * $conf->db_name = 'mi_base';
     * $conf->db_user = 'root';
     * $conf->db_password = 'secret';
     *
     * $conexion = new conexion();
     * $pdo = $conexion->conecta($conf, 'MYSQL');
     * if ($pdo instanceof PDO) {
     *     echo "Conexión exitosa.";
     * } else {
     *     print_r($pdo); // Muestra error si ocurre
     * }
     * ```
     *
     * @example Conexión a MSSQL con puerto personalizado:
     * ```php
     * $conf = new stdClass();
     * $conf->db_host = '192.168.0.100';
     * $conf->db_name = 'mi_bd_sqlserver';
     * $conf->db_user = 'admin';
     * $conf->db_password = 'clave123';
     * $conf->db_port = '1433';
     *
     * $conexion = new conexion();
     * $pdo = $conexion->conecta($conf, 'MSSQL');
     * ```
     *
     * @version 1.0.0
     * @author Gamboa
     * @see conexion::conexion()
     * @see conexion::genera_link()
     */
    private function conecta(database|stdClass $conf_database, string $motor): PDO|array|false
    {
        $link = false;
        $keys = array('db_host','db_name','db_user','db_password');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $conf_database);
        if(errores::$error){
            return $this->error->error(mensaje:  'Error al validar conf_database',data: $valida);
        }

        if(!in_array($motor, $this->motores_validos)){
            return $this->error->error(mensaje:  'Error ingrese un motor valido',data: $motor, es_final: true);
        }

        if($motor === 'MYSQL' || $motor === 'MARIADB') {
            try {
                $link = new PDO("mysql:host=$conf_database->db_host;dbname=$conf_database->db_name",
                    $conf_database->db_user, $conf_database->db_password);
            } catch (Throwable $e) {
                return $this->error->error(mensaje: 'Error al conectar', data: $e, es_final: true);
            }
        }
        if($motor === 'MSSQL') {
            try {
                if(!isset($conf_database->db_port) || $conf_database->db_port === '' ){
                    $conf_database->db_port = '1443';
                }
                $dns = "sqlsrv:server=$conf_database->db_host,1443;database=$conf_database->db_name";
                $link = new PDO($dns, $conf_database->db_user, $conf_database->db_password);
            } catch (Throwable $e) {
                return $this->error->error(mensaje: 'Error al conectar', data: $e, es_final: true);
            }
        }
        return $link;
    }

    /**
     * REG
     * Establece y configura una conexión PDO a la base de datos.
     *
     * Esta función realiza el proceso completo de conexión a una base de datos, validando los datos de configuración,
     * creando la conexión PDO y asignando parámetros adicionales como codificación (`SET NAMES`), modo SQL (`sql_mode`)
     * y tiempo de espera (`innodb_lock_wait_timeout`). También ejecuta el comando `USE` para seleccionar la base de datos.
     *
     * @param stdClass|database $conf_database Objeto que contiene la configuración de conexión. Debe incluir:
     * - `db_host`: Dirección del servidor de base de datos (por ejemplo: 'localhost').
     * - `db_name`: Nombre de la base de datos a utilizar.
     * - `db_user`: Usuario con permisos para conectarse a la base.
     * - `db_password`: Contraseña del usuario.
     * - `set_name`: Codificación para `SET NAMES` (ej. 'utf8').
     * - `sql_mode`: Configuración de SQL mode (ej. 'STRICT_ALL_TABLES').
     * - `time_out`: Tiempo de espera para bloqueo de transacciones (ej. 50).
     *
     * @param string $motor Motor de base de datos a usar: 'MYSQL', 'MARIADB' o 'MSSQL'.
     *
     * @return PDO|array Devuelve una instancia de `PDO` si la conexión y la configuración son exitosas.
     *                   Si ocurre algún error, se devuelve un array con información del error.
     *
     * @version 1.0.0
     *
     * @example Ejemplo de conexión exitosa:
     * ```php
     * $conf = new stdClass();
     * $conf->db_host = 'localhost';
     * $conf->db_name = 'mi_base';
     * $conf->db_user = 'root';
     * $conf->db_password = '1234';
     * $conf->set_name = 'utf8';
     * $conf->sql_mode = 'STRICT_ALL_TABLES';
     * $conf->time_out = 50;
     *
     * $conexion = new \base\conexion();
     * $link = $conexion->genera_link_custom($conf, 'MYSQL');
     *
     * if ($link instanceof PDO) {
     *     echo "Conexión exitosa.";
     * } else {
     *     echo "Error: " . $link['mensaje'];
     * }
     * ```
     *
     * @example Ejemplo de error por motor no válido:
     * ```php
     * $link = $conexion->genera_link_custom($conf, 'POSTGRES');
     * // Salida:
     * // [
     * //   'mensaje' => 'Error ingrese un motor valido',
     * //   'data' => 'POSTGRES',
     * //   'es_final' => true
     * // ]
     * ```
     *
     * @example Ejemplo de error por parámetros faltantes:
     * ```php
     * unset($conf->db_password);
     * $link = $conexion->genera_link_custom($conf, 'MYSQL');
     * // Salida esperada: error por parámetro faltante
     * ```
     *
     * @see conecta()
     * @see asigna_parametros_query()
     * @see usa_base_datos()
     */
    private function conexion(stdClass|database $conf_database, string $motor): PDO|array
    {
        $keys = array('db_host','db_name','db_user','db_password');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $conf_database);
        if(errores::$error){
            return $this->error->error(mensaje:  'Error al validar conf_database',data: $valida);
        }
        if(!in_array($motor, $this->motores_validos)){
            return $this->error->error(mensaje:  'Error ingrese un motor valido',data: $motor, es_final: true);
        }

        $link = $this->conecta(conf_database: $conf_database, motor: $motor);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al conectar",data:$link);
        }

        $keys = array('set_name','time_out', 'sql_mode');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $conf_database,
            valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje:  'Error al validar conf_database',data: $valida);
        }

        $link = $this->asigna_parametros_query(link: $link, set_name: $conf_database->set_name,
            sql_mode: $conf_database->sql_mode,time_out: $conf_database->time_out);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al asignar parametros", data:$link);
        }

        $link = $this->usa_base_datos(link: $link, db_name: $conf_database->db_name);
        if(errores::$error){
            return $this->error->error(mensaje: "Error usar base de datos", data:$link);
        }

        return $link;
    }

    /**
     * @param string $motor Motor de bd
     * @return PDO|array
     */
    private function genera_link(string $motor): PDO|array
    {
        $conf_database = new database();

        $link = $this->conexion(conf_database: $conf_database,motor:  $motor);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al conectar",data:$link);
        }


        return $link;
    }

    final public function genera_link_custom(stdClass $conf_database, string $motor): PDO|array
    {
        $keys = array('db_host','db_name','db_user','db_password');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $conf_database);
        if(errores::$error){
            return $this->error->error(mensaje:  'Error al validar conf_database',data: $valida);
        }
        if(!in_array($motor, $this->motores_validos)){
            return $this->error->error(mensaje:  'Error ingrese un motor valido',data: $motor);
        }

        $link = $this->conexion(conf_database: $conf_database,motor:  $motor);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al conectar",data:$link);
        }

        return $link;
    }

    /**
     * REG
     * Selecciona la base de datos activa sobre una conexión PDO mediante el comando `USE`.
     *
     * Esta función ejecuta una sentencia SQL `USE nombre_base_datos` para cambiar la base activa en
     * la conexión. Es útil cuando se ha conectado al servidor sin especificar explícitamente una base
     * de datos o se desea cambiarla dinámicamente.
     *
     * Realiza validación del nombre de la base y manejo de errores en caso de que falle la consulta.
     *
     * @param PDO $link Objeto PDO que representa la conexión activa a la base de datos.
     * @param string $db_name Nombre de la base de datos que se desea utilizar. No debe estar vacío.
     *
     * @return PDO|array Devuelve el objeto PDO si la base se seleccionó correctamente.
     *                   Si ocurre un error (como nombre vacío o fallo en la ejecución), devuelve un array con mensaje y datos del error.
     *
     * @example Ejemplo de uso exitoso:
     * ```php
     * $conexion = new conexion();
     * $pdo = $conexion->genera_link('MYSQL');
     * $resultado = $conexion->usa_base_datos($pdo, 'mi_base_datos');
     * if ($resultado instanceof PDO) {
     *     echo "Base de datos seleccionada correctamente.";
     * } else {
     *     echo "Error: " . $resultado['mensaje'];
     * }
     * ```
     *
     * @example Error por base vacía:
     * ```php
     * $resultado = $conexion->usa_base_datos($pdo, '');
     * // Resultado esperado:
     * // [
     * //     'mensaje' => 'Error $db_name esta vacio',
     * //     'data' => '',
     * //     'es_final' => true
     * // ]
     * ```
     *
     * @version 1.0.0
     * @author Gamboa
     */
    private function usa_base_datos(PDO $link, string $db_name): PDO|array
    {
        $db_name = trim($db_name);
        if($db_name === ''){
            return $this->error->error(mensaje: 'Error $db_name esta vacio' ,data:$db_name, es_final: true);
        }

        $consulta = "USE ".$db_name;
        try {
            $link->query($consulta);
        }
        catch (Throwable $e) {
            return $this->error->error(mensaje: 'Error al ejecutar conexion' ,data:$e, es_final: true);
        }

        return $link;
    }


}
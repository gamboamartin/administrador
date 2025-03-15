<?php
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use PDO;
use PDOStatement;
use stdClass;
use Throwable;

class inserts{
    private errores $error;

    #[Pure] public function __construct(){
        $this->error = new errores();

    }

    /**
     * REG
     * Asigna los datos del usuario en sesión a una transacción.
     *
     * Este método obtiene el ID del usuario desde la sesión activa y genera las columnas y valores
     * correspondientes para la inserción en la base de datos (`usuario_alta_id`, `usuario_update_id`).
     *
     * Si no hay una sesión activa o el usuario no es válido, devuelve un error.
     *
     * @return array Retorna un array asociativo con las claves:
     *  - `campos`: String con los nombres de las columnas (`,usuario_alta_id,usuario_update_id`).
     *  - `valores`: String con los valores del usuario en sesión (ej. `,1,1` si `usuario_id` es 1).
     *
     * @throws errores Si no hay sesión iniciada, si `usuario_id` no está definido en `$_SESSION`,
     *                 o si `usuario_id` es un valor inválido (≤ 0).
     *
     * @example
     * ```php
     * // Ejemplo con un usuario en sesión
     * $_SESSION['usuario_id'] = 5;
     * $data = $this->asigna_data_user_transaccion();
     * print_r($data);
     * // Salida esperada:
     * // Array (
     * //    [campos] => ,usuario_alta_id,usuario_update_id
     * //    [valores] => ,5,5
     * // )
     * ```
     *
     * @example
     * ```php
     * // Ejemplo sin sesión iniciada
     * unset($_SESSION);
     * $data = $this->asigna_data_user_transaccion();
     * // Salida esperada:
     * // Array (
     * //    [error] => "Error no hay session iniciada"
     * //    [data] => Array()
     * // )
     * ```
     *
     * @example
     * ```php
     * // Ejemplo con usuario inválido
     * $_SESSION['usuario_id'] = -1;
     * $data = $this->asigna_data_user_transaccion();
     * // Salida esperada:
     * // Array (
     * //    [error] => "Error USUARIO INVALIDO"
     * //    [data] => -1
     * // )
     * ```
     */
    private function asigna_data_user_transaccion(): array
    {
        if(!isset($_SESSION)){
            return $this->error->error(mensaje: 'Error no hay session iniciada',data: array(), es_final: true);
        }
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error existe usuario',data: $_SESSION, es_final: true);
        }
        if($_SESSION['usuario_id'] <= 0){
            return $this->error->error(mensaje: 'Error USUARIO INVALIDO',data: $_SESSION['usuario_id'], es_final: true);
        }

        $usuario_alta_id = $_SESSION['usuario_id'];
        $usuario_upd_id = $_SESSION['usuario_id'];
        $campos = ',usuario_alta_id,usuario_update_id';
        $valores = ','.$usuario_alta_id.','.$usuario_upd_id;

        return array('campos'=>$campos,'valores'=>$valores);
    }

    /**
     * REG
     * Agrega un campo a la lista de campos SQL para una inserción.
     *
     * Esta función toma un nombre de campo y lo concatena a una cadena de campos SQL,
     * asegurándose de que el formato sea correcto para una consulta `INSERT`.
     *
     * @param string $campo Nombre del campo a insertar. No puede estar vacío.
     * @param string $campos Cadena con los campos acumulados previamente. Si está vacía, se asigna directamente el `$campo`.
     *
     * @return string|array Retorna la cadena de campos actualizada si la operación es exitosa.
     *                      En caso de error, devuelve un array con un mensaje de error.
     *
     * @throws errores Si el campo está vacío, se genera un error y se detiene la ejecución.
     *
     * @example
     * ```php
     * $resultado = campos_alta_sql("nombre", "");
     * var_dump($resultado);
     * // Salida esperada:
     * // string(6) "nombre"
     * ```
     *
     * ```php
     * $resultado = campos_alta_sql("apellido", "nombre");
     * var_dump($resultado);
     * // Salida esperada:
     * // string(14) "nombre,apellido"
     * ```
     *
     * ```php
     * $resultado = campos_alta_sql("", "nombre");
     * // Salida esperada: Error
     * // array(3) {
     * //   ["mensaje"]=> string(23) "Error campo esta vacio"
     * //   ["data"]=> string(0) ""
     * //   ["es_final"]=> bool(true)
     * // }
     * ```
     */
    private function campos_alta_sql(string $campo, string $campos): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo, es_final: true);
        }
        $campos .= $campos === '' ? $campo : ",$campo";
        return $campos;
    }

    /**
     * REG
     * Genera un objeto con los campos y valores necesarios para el registro de un log de transacción.
     *
     * Este método procesa los datos de una transacción en función de la validez de las operaciones
     * de inserción y actualización. Si ambas operaciones (`alta_valido` y `update_valido`) son exitosas,
     * se añaden los campos del usuario en sesión (`usuario_alta_id`, `usuario_update_id`).
     *
     * @param bool|PDOStatement $alta_valido Indica si la operación de alta es válida o devuelve un resultado PDO.
     * @param string $campos Lista de campos en formato SQL.
     * @param bool|PDOStatement $update_valido Indica si la operación de actualización es válida o devuelve un resultado PDO.
     * @param string $valores Lista de valores en formato SQL.
     *
     * @return array|stdClass Devuelve un objeto con las claves:
     *  - `campos`: String con los nombres de los campos procesados.
     *  - `valores`: String con los valores correspondientes.
     *
     * @throws errores Si los campos o valores están vacíos, si no hay sesión iniciada,
     *                 si `usuario_id` no está definido en `$_SESSION` o es un valor inválido (≤ 0).
     *
     * @example
     * ```php
     * // Ejemplo con una transacción válida y usuario en sesión
     * $_SESSION['usuario_id'] = 10;
     * $data_log = $this->data_log(true, 'nombre, edad', true, "'Juan', 25");
     * print_r($data_log);
     * // Salida esperada:
     * // stdClass Object (
     * //    [campos] => "nombre, edad, usuario_alta_id, usuario_update_id"
     * //    [valores] => "'Juan', 25, 10, 10"
     * // )
     * ```
     *
     * @example
     * ```php
     * // Ejemplo con valores vacíos
     * $data_log = $this->data_log(true, '', true, '');
     * // Salida esperada:
     * // Array (
     * //    [error] => "Error campos esta vacio"
     * //    [data] => ""
     * // )
     * ```
     *
     * @example
     * ```php
     * // Ejemplo sin sesión iniciada
     * unset($_SESSION);
     * $data_log = $this->data_log(true, 'nombre', true, "'Juan'");
     * // Salida esperada:
     * // Array (
     * //    [error] => "Error no hay session iniciada"
     * //    [data] => array()
     * // )
     * ```
     */
    private function data_log(
        bool|PDOStatement $alta_valido, string $campos, bool|PDOStatement $update_valido,
        string $valores): array|stdClass
    {

        $campos = trim($campos);
        if($campos === ''){
            return $this->error->error(mensaje: 'Error campos esta vacio',data: $campos, es_final: true);
        }
        $valores = trim($valores);
        if($valores === ''){
            return $this->error->error(mensaje: 'Error valores esta vacio',data: $valores, es_final: true);
        }

        if($alta_valido &&  $update_valido ){
            if(!isset($_SESSION)){
                return $this->error->error(mensaje: 'Error no hay session iniciada',data: array(), es_final: true);
            }
            if(!isset($_SESSION['usuario_id'])){
                return $this->error->error(mensaje: 'Error existe usuario',data: $_SESSION, es_final: true);
            }
            if($_SESSION['usuario_id'] <= 0){
                return $this->error->error(mensaje: 'Error USUARIO INVALIDO',data: $_SESSION['usuario_id'],
                    es_final: true);
            }

            $data_asignacion = $this->asigna_data_user_transaccion();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar datos de transaccion', data: $data_asignacion);
            }
            $campos .= $data_asignacion['campos'];
            $valores .= $data_asignacion['valores'];
        }

        $data = new stdClass();
        $data->campos = $campos;
        $data->valores = $valores;
        return $data;
    }

    /**
     * REG
     * Genera información de validación sobre la existencia de los campos `usuario_alta_id` y `usuario_update_id`
     * en una tabla de base de datos.
     *
     * Este método ejecuta dos consultas SQL para verificar si la tabla contiene los campos `usuario_alta_id`
     * y `usuario_update_id`, devolviendo los resultados en un objeto. Si `$integra_datos_base` es `false`,
     * retorna un objeto con valores vacíos.
     *
     * @param bool $integra_datos_base Indica si se deben realizar las consultas de validación.
     * - `true`: Ejecuta las consultas SQL para verificar la existencia de los campos `usuario_alta_id` y `usuario_update_id`.
     * - `false`: Retorna un objeto vacío sin ejecutar consultas.
     *
     * @param PDO $link Conexión activa a la base de datos.
     * @param string $tabla Nombre de la tabla en la que se ejecutarán las validaciones.
     *
     * @return stdClass|array Devuelve un objeto con los atributos:
     *  - `alta_valido` (PDOStatement|string): Resultado de la consulta de validación para `usuario_alta_id`.
     *  - `update_valido` (PDOStatement|string): Resultado de la consulta de validación para `usuario_update_id`.
     *
     * En caso de error, retorna un array con la información del error.
     *
     * @throws errores Si `$tabla` está vacía o si hay un error en la ejecución de la consulta SQL.
     *
     * @example
     * ```php
     * $pdo = new PDO("mysql:host=localhost;dbname=mi_base", "usuario", "password");
     * $resultado = $this->data_para_log(true, $pdo, "usuarios");
     *
     * var_dump($resultado);
     * // Salida esperada (si la tabla existe y tiene los campos):
     * // object(stdClass)#1 (2) {
     * //   ["alta_valido"]=> object(PDOStatement)#2 {...}
     * //   ["update_valido"]=> object(PDOStatement)#3 {...}
     * // }
     * ```
     *
     * @example
     * ```php
     * $resultado = $this->data_para_log(false, $pdo, "usuarios");
     * // Salida esperada:
     * // object(stdClass)#1 (2) {
     * //   ["alta_valido"]=> string(0) ""
     * //   ["update_valido"]=> string(0) ""
     * // }
     * ```
     *
     * @example
     * ```php
     * $resultado = $this->data_para_log(true, $pdo, "");
     * // Salida esperada:
     * // array(2) {
     * //   ["error"]=> string(25) "Error tabla esta vacía"
     * //   ["data"]=> string(0) ""
     * // }
     * ```
     */
    private function data_para_log(bool $integra_datos_base, PDO $link, string $tabla): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje:'Error tabla esta vacia', data: $tabla, es_final: true);
        }

        if(!$integra_datos_base){
            $data = new stdClass();
            $data->alta_valido = '';
            $data->update_valido = '';
            return $data;
        }

        $existe_alta_id = /** @lang MYSQL */"SELECT count(usuario_alta_id) FROM " . $tabla;
        $existe_update_id = /** @lang MYSQL */"SELECT count(usuario_alta_id) FROM $tabla";

        try {
            $alta_valido = $link->query($existe_alta_id);
        }
        catch (Throwable $e){
            $data_error = new stdClass();
            $data_error->e = $e;
            $data_error->sql = $existe_alta_id;
            return $this->error->error(mensaje:'Error al ejecutar sql', data: $data_error);
        }
        try {
            $update_valido = $link->query($existe_update_id);
        }
        catch (Throwable $e){
            $data_error = new stdClass();
            $data_error->e = $e;
            $data_error->sql = $existe_update_id;
            return $this->error->error(mensaje:'Error al ejecutar sql', data: $data_error);
        }

        $data = new stdClass();
        $data->alta_valido = $alta_valido;
        $data->update_valido = $update_valido;
        return $data;
    }

    /**
     * REG
     * Genera los datos necesarios para el registro de logs en una transacción de base de datos.
     *
     * Este método se encarga de validar el registro, la sesión del usuario y de estructurar los datos
     * que serán utilizados en la inserción del log de la transacción en la base de datos. También
     * valida la existencia de datos base y los integra en el log si es necesario.
     *
     * @param bool $integra_datos_base Indica si se deben integrar los datos base en la transacción.
     * @param PDO $link Conexión activa a la base de datos.
     * @param array $registro Datos del registro a procesar para la transacción.
     * @param string $tabla Nombre de la tabla en la base de datos.
     *
     * @return array|stdClass Devuelve un objeto con los datos del log estructurados:
     *  - `campos`: String con los nombres de los campos procesados.
     *  - `valores`: String con los valores correspondientes.
     *
     * @throws errores Si el registro está vacío, si la tabla no es válida, si no hay sesión iniciada,
     *                 si `usuario_id` no está definido en `$_SESSION` o es un valor inválido (≤ 0).
     *
     * @example
     * ```php
     * // Ejemplo de uso con un registro válido
     * $_SESSION['usuario_id'] = 15;
     * $pdo = new PDO("mysql:host=localhost;dbname=test", "user", "password");
     * $registro = ['nombre' => 'Juan', 'edad' => 30];
     * $data_log = $this->genera_data_log(true, $pdo, $registro, 'usuarios');
     * print_r($data_log);
     * // Salida esperada:
     * // stdClass Object (
     * //    [campos] => "nombre, edad, usuario_alta_id, usuario_update_id"
     * //    [valores] => "'Juan', 30, 15, 15"
     * // )
     * ```
     *
     * @example
     * ```php
     * // Ejemplo con un registro vacío
     * $data_log = $this->genera_data_log(true, $pdo, [], 'usuarios');
     * // Salida esperada:
     * // Array (
     * //    [error] => "Error registro vacio"
     * //    [data] => array()
     * // )
     * ```
     *
     * @example
     * ```php
     * // Ejemplo sin sesión iniciada
     * unset($_SESSION);
     * $data_log = $this->genera_data_log(true, $pdo, ['nombre' => 'Ana'], 'usuarios');
     * // Salida esperada:
     * // Array (
     * //    [error] => "Error no hay session iniciada"
     * //    [data] => array()
     * // )
     * ```
     */
    private function genera_data_log(bool $integra_datos_base, PDO $link, array $registro,
                                     string $tabla): array|stdClass
    {
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error registro vacio',data:  $registro, es_final: true);
        }
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje:'Error tabla esta vacia', data: $tabla, es_final: true);
        }
        if(!isset($_SESSION)){
            return $this->error->error(mensaje: 'Error no hay session iniciada',data: array(), es_final: true);
        }
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error existe usuario',data: $_SESSION, es_final: true);
        }
        if($_SESSION['usuario_id'] <= 0){
            return $this->error->error(mensaje: 'Error USUARIO INVALIDO',data: $_SESSION['usuario_id'], es_final: true);
        }

        $sql_data_alta = $this->sql_alta_full(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql ', data: $sql_data_alta);
        }

        $datas = $this->data_para_log(integra_datos_base: $integra_datos_base,link:$link,tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data log', data: $datas);
        }

        $data_log = $this->data_log(alta_valido: $datas->alta_valido, campos:  $sql_data_alta->campos,
            update_valido:  $datas->update_valido,valores:  $sql_data_alta->valores);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data log', data: $data_log);
        }

        return $data_log;
    }

    /**
     * Obtiene los datos de la session al ejecutar un alta
     * @param int $registro_id Registro insertado
     * @param string $tabla Tabla o entidad
     * @return  array
     * @version 1.559.51
     */
    private function data_session_alta(int $registro_id, string $tabla): array
    {
        if($tabla === ''){
            return  $this->error->error(mensaje: 'Error this->tabla esta vacia',data: $tabla);
        }
        if($registro_id <=0){
            return  $this->error->error(mensaje: 'Error $this->registro_id debe ser mayor a 0',data: $registro_id);
        }
        $_SESSION['exito'][]['mensaje'] = $tabla.' se agrego con el id '.$registro_id;
        return $_SESSION['exito'];
    }

    /**
     * REG
     * Inserta un registro en la base de datos utilizando los datos generados en `$data_log`.
     *
     * Esta función valida que `$data_log` contenga los valores requeridos (`campos` y `valores`),
     * construye la consulta `INSERT` usando `sql_alta()`, y la ejecuta mediante `ejecuta_sql()`
     * en el modelo correspondiente.
     *
     * @param stdClass $data_log Objeto que contiene los datos a insertar en la base de datos.
     *                           Debe incluir las propiedades `campos` (nombres de columnas) y `valores` (valores a insertar).
     * @param modelo $modelo Instancia del modelo sobre el cual se ejecutará la inserción.
     *
     * @return array|stdClass Devuelve el resultado de la ejecución SQL en caso de éxito.
     *                        Si ocurre un error, devuelve un array con la descripción del error.
     *
     * @throws errores Si `$data_log` no tiene las claves necesarias (`campos`, `valores`), o si estas están vacías.
     * @throws errores Si la generación del SQL falla (`sql_alta` retorna un error).
     * @throws errores Si la ejecución del SQL falla (`ejecuta_sql` retorna un error).
     *
     * @example
     * ```php
     * // Ejemplo de uso con valores correctos
     * $data_log = new stdClass();
     * $data_log->campos = 'nombre, edad, correo';
     * $data_log->valores = "'Juan', 30, 'juan@example.com'";
     *
     * $modelo = new modelo();
     * $resultado = $this->inserta_sql($data_log, $modelo);
     *
     * if (isset($resultado->id)) {
     *     echo "Registro insertado con ID: " . $resultado->id;
     * }
     * ```
     *
     * @example
     * ```php
     * // Ejemplo con `data_log` sin la clave `campos`
     * $data_log = new stdClass();
     * $data_log->valores = "'Pedro', 25, 'pedro@example.com'";
     *
     * $modelo = new modelo();
     * $resultado = $this->inserta_sql($data_log, $modelo);
     *
     * // Salida esperada:
     * // Array (
     * //    [error] => "Error no existe data_log->campos"
     * //    [data] => (stdClass Object)
     * // )
     * ```
     *
     * @example
     * ```php
     * // Ejemplo con un `data_log->campos` vacío
     * $data_log = new stdClass();
     * $data_log->campos = '';
     * $data_log->valores = "'Pedro', 25, 'pedro@example.com'";
     *
     * $modelo = new modelo();
     * $resultado = $this->inserta_sql($data_log, $modelo);
     *
     * // Salida esperada:
     * // Array (
     * //    [error] => "Error esta vacio data_log->campos"
     * //    [data] => (stdClass Object)
     * // )
     * ```
     */
    private function inserta_sql(stdClass $data_log, modelo $modelo): array|stdClass
    {
        $keys = array('campos','valores');
        foreach($keys as $key){
            if(!isset($data_log->$key)){
                return $this->error->error(mensaje: 'Error no existe data_log->'.$key, data: $data_log, es_final: true);
            }
        }
        foreach($keys as $key){
            if(trim($data_log->$key) === ''){
                return $this->error->error(mensaje:'Error esta vacio data_log->'.$key, data: $data_log, es_final: true);
            }
        }

        $modelo->transaccion = 'INSERT';

        $sql = $this->sql_alta(campos: $data_log->campos,tabla: $modelo->tabla, valores: $data_log->valores);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data:  $sql);
        }

        $resultado = $modelo->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql',data:  $resultado);
        }
        return $resultado;
    }

    /**
     * REG
     * Aplica `addslashes()` al nombre del campo y procesa el valor asociado para su inserción en SQL.
     *
     * Esta función limpia el nombre del campo y le aplica `addslashes()` para evitar problemas de seguridad
     * en consultas SQL. Luego, utiliza el método `value()` para procesar el valor, escapándolo si es necesario
     * o convirtiéndolo en `NULL` en caso de que sea nulo.
     *
     * @param string $campo Nombre del campo a procesar. No puede estar vacío.
     * @param mixed $value Valor asociado al campo, que puede ser `string`, `int`, `float`, `bool` o `null`.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con las siguientes propiedades:
     *                        - `campo` (string): Nombre del campo con `addslashes()` aplicado.
     *                        - `value` (string): Valor procesado, con `addslashes()` si es necesario.
     *                        - `value_es_null` (bool): Indica si el valor original era `NULL`.
     *
     * @throws errores Si el nombre del campo está vacío o si hay un error en el procesamiento del valor.
     *
     * @example
     * ```php
     * $obj = slaches_campo("nombre", "O'Reilly");
     * var_dump($obj);
     * // Salida esperada:
     * // object(stdClass)#1 (3) {
     * //   ["campo"]=> string(6) "nombre"
     * //   ["value"]=> string(9) "O\'Reilly"
     * //   ["value_es_null"]=> bool(false)
     * // }
     * ```
     *
     * ```php
     * $obj = slaches_campo("descripcion", null);
     * var_dump($obj);
     * // Salida esperada:
     * // object(stdClass)#1 (3) {
     * //   ["campo"]=> string(11) "descripcion"
     * //   ["value"]=> string(4) "NULL"
     * //   ["value_es_null"]=> bool(true)
     * // }
     * ```
     *
     * ```php
     * $obj = slaches_campo("", "valor");
     * // Salida esperada: Error
     * // array(2) {
     * //   ["mensaje"]=> string(34) "Error el campo no puede venir vacio"
     * //   ["data"]=> string(0) ""
     * // }
     * ```
     */
    private function slaches_campo(string $campo, mixed $value): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo no puede venir vacio',data:  $campo);
        }

        $campo = addslashes($campo);


        $data_value = $this->value(value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar value',data:  $data_value);
        }

        $data = new stdClass();
        $data->campo = $campo;
        $data->value = $data_value->value;
        $data->value_es_null = $data_value->value_es_null;
        return $data;
    }

    /**
     * REG
     * Genera una consulta SQL de inserción (`INSERT INTO`) basada en los campos y valores proporcionados.
     *
     * Esta función construye una consulta SQL de inserción a partir de los nombres de los campos, el nombre de la tabla
     * y los valores correspondientes. Se encarga de validar que los parámetros sean correctos antes de generar la consulta.
     *
     * @param string $campos Lista de nombres de los campos a insertar, separados por comas.
     * @param string $tabla Nombre de la tabla en la cual se realizará la inserción.
     * @param string $valores Lista de valores correspondientes a los campos, separados por comas.
     *
     * @return string|array Devuelve un `string` con la consulta SQL generada si los parámetros son correctos.
     *                      En caso de error, devuelve un `array` con el mensaje de error detallado.
     *
     * @throws errores Si `$tabla`, `$campos` o `$valores` están vacíos.
     *
     * @example
     * ```php
     * // Ejemplo de uso con valores válidos
     * $sql = $this->sql_alta('nombre, edad, correo', 'usuarios', "'Juan', 30, 'juan@example.com'");
     * echo $sql;
     * // Salida esperada:
     * // "INSERT INTO usuarios (nombre, edad, correo) VALUES ('Juan', 30, 'juan@example.com')"
     * ```
     *
     * @example
     * ```php
     * // Ejemplo con la tabla vacía
     * $sql = $this->sql_alta('nombre, edad', '', "'Maria', 25");
     * // Salida esperada:
     * // Array (
     * //    [error] => "Error $this tabla no puede venir vacio"
     * //    [data] => ""
     * // )
     * ```
     *
     * @example
     * ```php
     * // Ejemplo con valores vacíos
     * $sql = $this->sql_alta('nombre, edad', 'usuarios', '');
     * // Salida esperada:
     * // Array (
     * //    [error] => "Error valores esta vacio"
     * //    [data] => ""
     * // )
     * ```
     */
    private function sql_alta(string $campos,string $tabla, string $valores): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error $this tabla no puede venir vacio',data:  $tabla, es_final: true);
        }
        if($campos === ''){
            return $this->error->error(mensaje:'Error campos esta vacio', data:$campos, es_final: true);
        }
        if($valores === ''){
            return $this->error->error(mensaje:'Error valores esta vacio',data: $valores, es_final: true);
        }


        return /** @lang mysql */ 'INSERT INTO '. $tabla.' ('.$campos.') VALUES ('.$valores.')';
    }

    /**
     * REG
     * Genera una estructura SQL para inserción a partir de un registro asociativo.
     *
     * Este método toma un array asociativo `$registro`, donde las claves representan los nombres de las columnas
     * y los valores representan los datos a insertar. Luego, procesa los campos y valores para generar una
     * estructura lista para ser usada en una consulta SQL `INSERT INTO`.
     *
     * @param array $registro Un array asociativo donde:
     *  - **Clave (string)**: Nombre del campo en la tabla.
     *  - **Valor (mixed)**: Valor correspondiente al campo.
     *
     * @return array|stdClass Retorna un objeto con los atributos:
     *  - `campos` (string): Lista de nombres de campos formateada para SQL.
     *  - `valores` (string): Lista de valores formateada para SQL.
     *  En caso de error, retorna un array con un mensaje de error.
     *
     * @throws errores Si el `$registro` está vacío, si una clave es numérica o si un campo es inválido.
     *
     * @example
     * ```php
     * $registro = [
     *   "nombre" => "Juan",
     *   "edad" => 30,
     *   "correo" => "juan@example.com"
     * ];
     *
     * $resultado = $this->sql_alta_full($registro);
     * var_dump($resultado);
     * // Salida esperada:
     * // object(stdClass)#1 (2) {
     * //   ["campos"]=> string(19) "nombre,edad,correo"
     * //   ["valores"]=> string(29) "'Juan','30','juan@example.com'"
     * // }
     * ```
     *
     * @example
     * ```php
     * $registro = [];
     * $resultado = $this->sql_alta_full($registro);
     * // Salida esperada:
     * // array(2) {
     * //   ["error"]=> string(18) "Error registro vacío"
     * //   ["data"]=> array(0) {}
     * // }
     * ```
     *
     * @example
     * ```php
     * $registro = [123 => "Juan"];
     * $resultado = $this->sql_alta_full($registro);
     * // Salida esperada:
     * // array(2) {
     * //   ["error"]=> string(26) "Error el campo no es válido"
     * //   ["data"]=> int(123)
     * // }
     * ```
     */
    private function sql_alta_full(array $registro): array|stdClass
    {
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error registro vacio',data:  $registro, es_final: true);
        }
        $campos = '';
        $valores = '';
        foreach ($registro as $campo => $value) {
            if(is_numeric($campo)){
                return $this->error->error(mensaje: 'Error el campo no es valido',data:  $campo, es_final: true);
            }
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error el campo no puede venir vacio',data:  $campo,
                    es_final: true);
            }

            $sql_base = $this->sql_base_alta(campo: $campo, campos:  $campos, valores:  $valores, value:  $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql ',data:  $sql_base);
            }
            $campos = $sql_base->campos;
            $valores = $sql_base->valores;
        }

        $datas = new stdClass();
        $datas->campos = $campos;
        $datas->valores = $valores;
        return $datas;
    }

    /**
     * REG
     * Genera los valores base para una consulta SQL de inserción.
     *
     * Este método toma un campo y su valor correspondiente y los prepara para ser
     * insertados en una consulta SQL `INSERT`. Se asegura de que los nombres de
     * los campos sean válidos y formatea los valores correctamente.
     *
     * @param string $campo Nombre del campo que será insertado en la consulta SQL.
     * @param string $campos Cadena acumulada de nombres de campos que se están insertando.
     * @param string $valores Cadena acumulada de valores que se están insertando.
     * @param mixed $value Valor correspondiente al campo proporcionado.
     *
     * @return array|stdClass Devuelve un objeto con los campos y valores formateados.
     *                        En caso de error, devuelve un array con un mensaje de error.
     *
     * @throws errores Si el campo es numérico, está vacío o si ocurre un error en el procesamiento.
     *
     * @example
     * ```php
     * $resultado = $this->sql_base_alta("nombre", "", "", "Juan");
     * var_dump($resultado);
     * // Salida esperada:
     * // object(stdClass)#1 (2) {
     * //   ["campos"]=> string(6) "nombre"
     * //   ["valores"]=> string(6) "'Juan'"
     * // }
     * ```
     *
     * ```php
     * $resultado = $this->sql_base_alta("edad", "nombre", "'Juan'", 25);
     * var_dump($resultado);
     * // Salida esperada:
     * // object(stdClass)#1 (2) {
     * //   ["campos"]=> string(11) "nombre,edad"
     * //   ["valores"]=> string(11) "'Juan','25'"
     * // }
     * ```
     *
     * ```php
     * $resultado = $this->sql_base_alta("", "nombre", "'Juan'", "Perez");
     * // Salida esperada:
     * // array(2) {
     * //   ["error"]=> string(32) "Error el campo no puede venir vacio"
     * //   ["data"]=> string(0) ""
     * // }
     * ```
     */
    private function sql_base_alta(string $campo, string $campos, string $valores, mixed $value): array|stdClass
    {
        if(is_numeric($campo)){
            return $this->error->error(mensaje: 'Error el campo no es valido',data:  $campo, es_final: true);
        }
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo no puede venir vacio',data:  $campo, es_final: true);
        }

        $slacheados = $this->slaches_campo(campo: $campo,value:  $value);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ajustar campo ', data:$slacheados);
        }


        $campos_r = $this->campos_alta_sql(campo:  $slacheados->campo, campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar campo ', data:$campos_r);
        }

        $valores_r = $this->valores_sql_alta(valores: $valores,value:  $slacheados->value,
            value_es_null: $slacheados->value_es_null);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar valor ',data: $valores_r);
        }
        $data = new stdClass();
        $data->campos = $campos_r;
        $data->valores = $valores_r;
        return $data;
    }

    /**
     * Genera las transacciones en sql
     * @param modelo $modelo Modelo en ejecucion
     * @return array|stdClass
     * Genera las transacciones para un alta
     */
    final public function transacciones(modelo $modelo): array|stdClass
    {
        if(count($modelo->registro) === 0){
            return $this->error->error(mensaje: 'Error registro vacio',data:  $modelo->registro, es_final: true);
        }

        if(!isset($_SESSION)){
            return $this->error->error(mensaje: 'Error no hay session iniciada',data: array(), es_final: true);
        }
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error existe usuario',data: $_SESSION, es_final: true);
        }
        if($_SESSION['usuario_id'] <= 0){
            return $this->error->error(mensaje: 'Error USUARIO INVALIDO',data: $_SESSION['usuario_id'], es_final: true);
        }
        $data_log = $this->genera_data_log(integra_datos_base: $modelo->integra_datos_base,link: $modelo->link,
            registro: $modelo->registro,tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data log', data: $data_log);
        }

        $resultado = $this->inserta_sql(data_log: $data_log, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $resultado);
        }

        $transacciones = $this->transacciones_default(consulta: $resultado->sql, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transacciones',data:  $transacciones);
        }

        $resultado->transacciones = $transacciones;

        return $resultado;
    }

    /**
     *Ejecuta transacciones para alta
     * @param string $consulta texto en forma de SQL
     * @param modelo $modelo Modelo en ejecucion
     * @return array|stdClass
     */
    private function transacciones_default(string $consulta, modelo $modelo): array|stdClass
    {
        if($modelo->registro_id<=0){
            return $this->error->error(mensaje: 'Error this->registro_id debe ser mayor a 0', data: $modelo->registro_id, es_final: true);
        }

        $bitacora = (new bitacoras())->bitacora(consulta: $consulta, funcion: __FUNCTION__, modelo: $modelo,
            registro: $modelo->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar bitacora',data:  $bitacora);
        }

        $r_ins = (new atributos())->ejecuta_insersion_attr(modelo: $modelo, registro_id: $modelo->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al insertar atributos', data: $r_ins);
        }

        $data_session = $this->data_session_alta(registro_id:$modelo->registro_id,tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al asignar dato de SESSION', data: $data_session);
        }

        $datos = new stdClass();
        $datos->bitacora = $bitacora;
        $datos->attr = $r_ins;
        $datos->session = $data_session;
        return $datos;
    }

    /**
     * REG
     * Genera la cadena de valores SQL para una inserción.
     *
     * Este método formatea los valores a insertar en una consulta SQL `INSERT`,
     * asegurando que los valores de tipo string sean encapsulados en comillas simples.
     * Si el valor es `NULL`, se maneja adecuadamente sin comillas.
     *
     * @param string $valores Cadena de valores acumulados previamente en la consulta SQL.
     * @param string $value Valor actual a agregar a la consulta.
     * @param bool $value_es_null Indica si el valor es `NULL`. Si es `true`, el valor se insertará como `NULL` sin comillas.
     *
     * @return string|array Devuelve la cadena de valores SQL actualizada si es exitosa.
     *                      En caso de error, devuelve un array con un mensaje de error.
     *
     * @example
     * ```php
     * $resultado = valores_sql_alta("", "Juan", false);
     * var_dump($resultado);
     * // Salida esperada:
     * // string(6) "'Juan'"
     * ```
     *
     * ```php
     * $resultado = valores_sql_alta("'Juan'", "Perez", false);
     * var_dump($resultado);
     * // Salida esperada:
     * // string(14) "'Juan','Perez'"
     * ```
     *
     * ```php
     * $resultado = valores_sql_alta("", "NULL", true);
     * var_dump($resultado);
     * // Salida esperada:
     * // string(4) "NULL"
     * ```
     *
     * ```php
     * $resultado = valores_sql_alta("'Juan'", "NULL", true);
     * var_dump($resultado);
     * // Salida esperada:
     * // string(9) "'Juan',NULL"
     * ```
     */
    private function valores_sql_alta(string $valores, string $value, bool $value_es_null): string|array
    {
        $value_aj = "'$value'";
        if($value_es_null){
            $value_aj = $value;
        }
        $value_aj = trim($value_aj);
        $valores .= $valores === '' ? $value_aj : ",$value_aj";
        return $valores;
    }

    /**
     * REG
     * Procesa un valor para su inserción en SQL, aplicando escape de caracteres o convirtiéndolo a NULL si es necesario.
     *
     * Este método toma un valor de entrada y lo ajusta para su inserción en una consulta SQL.
     * Si el valor es `NULL`, se convierte en la cadena `'NULL'` y se marca como `value_es_null = true`.
     * Si el valor no es `NULL`, se aplica `addslashes()` para evitar problemas de seguridad con caracteres especiales.
     *
     * @param mixed $value Valor que se va a procesar. Puede ser un `string`, `int`, `float`, `bool` o `null`.
     *
     * @return array|stdClass Retorna un objeto `stdClass` con dos propiedades:
     *                         - `value` (string): El valor procesado, listo para ser usado en una consulta SQL.
     *                         - `value_es_null` (bool): Indica si el valor era `NULL` antes del procesamiento.
     *
     * @throws errores En caso de que ocurra un error al procesar el valor, se captura la excepción y se devuelve un error estructurado.
     *
     * @example
     * ```php
     * $obj = value("O'Reilly");
     * var_dump($obj);
     * // Salida esperada:
     * // object(stdClass)#1 (2) {
     * //   ["value"]=> string(9) "O\'Reilly"
     * //   ["value_es_null"]=> bool(false)
     * // }
     * ```
     *
     * ```php
     * $obj = value(null);
     * var_dump($obj);
     * // Salida esperada:
     * // object(stdClass)#1 (2) {
     * //   ["value"]=> string(4) "NULL"
     * //   ["value_es_null"]=> bool(true)
     * // }
     * ```
     *
     * ```php
     * $obj = value("Hola 'Mundo'");
     * var_dump($obj);
     * // Salida esperada:
     * // object(stdClass)#1 (2) {
     * //   ["value"]=> string(13) "Hola \'Mundo\'"
     * //   ["value_es_null"]=> bool(false)
     * // }
     * ```
     */
    private function value(mixed $value): array|stdClass
    {
        $value_es_null = false;
        try {
            if(is_null($value)){
                $value_es_null = true;
                $value = 'NULL';
            }
            else{
                $value = addslashes($value);
            }

        }
        catch (Throwable  $e){
            return $this->error->error(mensaje: 'Error al asignar value de campo '.$campo, data: $e);
        }
        $data = new stdClass();
        $data->value = $value;
        $data->value_es_null = $value_es_null;
        return $data;
    }



}

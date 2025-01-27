<?php
namespace gamboamartin\administrador\modelado;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

/**
 * PRUEBAS FINALIZADAS FUNCIONES REVISADAS FINAL
 */
class joins{

    public errores $error;
    public validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * REG
     * Ajusta el nombre del modelo basado en una tabla dada, formateando la cadena
     * y generando el nombre completo de la clase para el modelo.
     *
     * @param string $tabla Nombre de la tabla que se desea ajustar y convertir en el nombre del modelo.
     *                      Este parámetro no puede estar vacío.
     *
     * @return stdClass|array Retorna un objeto `stdClass` con dos propiedades:
     *                        - `tabla`: El nombre ajustado de la tabla.
     *                        - `name_model`: El nombre completo de la clase modelo (incluyendo su namespace).
     *                        En caso de error, retorna un array con los detalles del mismo.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $tabla = 'usuarios';
     *
     * $resultado = $miClase->ajusta_name_model($tabla);
     *
     * print_r($resultado);
     * // Resultado esperado:
     * // stdClass Object
     * // (
     * //     [tabla] => usuarios
     * //     [name_model] => models\usuarios
     * // )
     * ```
     *
     * ### Ejemplo de error:
     *
     * - Caso: La tabla viene como una cadena vacía.
     *
     * ```php
     * $tabla = '';
     *
     * $resultado = $miClase->ajusta_name_model($tabla);
     *
     * print_r($resultado);
     * // Resultado esperado:
     * // Array
     * // (
     * //     [error] => 1
     * //     [mensaje] => Error tabla no puede venir vacia
     * //     [data] =>
     * // )
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$tabla`**:
     *   Nombre de la tabla a procesar. Este valor será ajustado y usado para generar
     *   el nombre del modelo.
     *   Ejemplo válido: `'usuarios'`.
     *   Ejemplo inválido: `''` (cadena vacía).
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Retorna un objeto `stdClass` con las siguientes claves:
     *   - `tabla`: Nombre ajustado de la tabla (se eliminan referencias a `models\\`).
     *   - `name_model`: Nombre completo del modelo con el namespace prefijado (`models\\`).
     *
     * - **Error**:
     *   Si la tabla está vacía, retorna un array con los detalles del error, incluyendo
     *   el mensaje descriptivo y los datos proporcionados.
     *
     * ### Notas adicionales:
     *
     * - Esta función asegura que el nombre del modelo siempre tenga el prefijo `models\\`.
     * - Es útil para casos en los que se necesita mapear dinámicamente nombres de tablas
     *   a nombres de clases modelo en un contexto estructurado.
     */

    private function ajusta_name_model(string $tabla): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla no puede venir vacia', data: $tabla, es_final: true);
        }

        $tabla = str_replace('models\\','',$tabla);
        $class = 'models\\'.$tabla;

        $data = new stdClass();
        $data->tabla = $tabla;
        $data->name_model = $class;
        return $data;
    }

    /**
     * REG
     * Ajusta los nombres de los modelos para dos tablas específicas, generando los nombres
     * completos de las clases de modelo para ambas tablas.
     *
     * @param string $tabla Nombre de la tabla base que se desea ajustar y convertir en el nombre del modelo.
     *                      Este parámetro no puede estar vacío.
     * @param string $tabla_enlace Nombre de la tabla de enlace que se desea ajustar y convertir en el nombre del modelo.
     *                             Este parámetro no puede estar vacío.
     *
     * @return stdClass|array Retorna un objeto `stdClass` con las siguientes propiedades:
     *                        - `tabla`: Objeto `stdClass` resultante de ajustar el nombre del modelo para `$tabla`.
     *                        - `tabla_enlace`: Objeto `stdClass` resultante de ajustar el nombre del modelo para `$tabla_enlace`.
     *                        En caso de error, retorna un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles_usuarios';
     *
     * $resultado = $miClase->ajusta_name_models($tabla, $tabla_enlace);
     *
     * print_r($resultado);
     * // Resultado esperado:
     * // stdClass Object
     * // (
     * //     [tabla] => stdClass Object
     * //         (
     * //             [tabla] => usuarios
     * //             [name_model] => models\usuarios
     * //         )
     * //
     * //     [tabla_enlace] => stdClass Object
     * //         (
     * //             [tabla] => roles_usuarios
     * //             [name_model] => models\roles_usuarios
     * //         )
     * // )
     * ```
     *
     * ### Ejemplo de error:
     *
     * - Caso: Uno de los nombres de las tablas está vacío.
     *
     * ```php
     * $tabla = '';
     * $tabla_enlace = 'roles_usuarios';
     *
     * $resultado = $miClase->ajusta_name_models($tabla, $tabla_enlace);
     *
     * print_r($resultado);
     * // Resultado esperado:
     * // Array
     * // (
     * //     [error] => 1
     * //     [mensaje] => Error tabla no puede venir vacia
     * //     [data] =>
     * // )
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$tabla`**:
     *   Nombre de la tabla base. Este valor será ajustado para generar el nombre del modelo.
     *   Ejemplo válido: `'usuarios'`.
     *   Ejemplo inválido: `''` (cadena vacía).
     *
     * - **`$tabla_enlace`**:
     *   Nombre de la tabla de enlace. Este valor será ajustado para generar el nombre del modelo.
     *   Ejemplo válido: `'roles_usuarios'`.
     *   Ejemplo inválido: `''` (cadena vacía).
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Retorna un objeto `stdClass` con las siguientes claves:
     *   - `tabla`: Resultado de ajustar el nombre del modelo para `$tabla`.
     *   - `tabla_enlace`: Resultado de ajustar el nombre del modelo para `$tabla_enlace`.
     *
     * - **Error**:
     *   Si alguno de los parámetros está vacío, retorna un array con los detalles del error, incluyendo
     *   el mensaje descriptivo y los datos proporcionados.
     *
     * ### Notas adicionales:
     *
     * - Esta función es útil para mapear dinámicamente los nombres de tablas a nombres de modelos en un entorno estructurado.
     * - Utiliza la función interna `ajusta_name_model` para realizar los ajustes individuales de cada tabla.
     */

    private function ajusta_name_models(string $tabla, string $tabla_enlace): array|stdClass
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla no puede venir vacia', data: $tabla,es_final: true);
        }
        $tabla_enlace = trim($tabla_enlace);
        if($tabla_enlace === ''){
            return $this->error->error(mensaje: 'Error $tabla_enlace no puede venir vacia', data: $tabla_enlace,
                es_final: true);
        }

        $data_model_tabla = $this->ajusta_name_model(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar nombre del modelo', data: $data_model_tabla);
        }

        $data_model_tabla_enl = $this->ajusta_name_model(tabla:$tabla_enlace);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar nombre del modelo', data: $data_model_tabla_enl);
        }

        $data = new stdClass();
        $data->tabla = $data_model_tabla;
        $data->tabla_enlace = $data_model_tabla_enl;
        return $data;
    }

    /**
     * TOTAL
     * Ajusta las tablas para las consultas SQL con JOINs.
     *
     * Esta función toma una cadena que representa una tabla primaria y un array de 'joins' para ajustar
     * adecuadamente las tablas que se usarán en la consulta. Si se produce un error durante el proceso,
     * se maneja y se devuelve un error específico.
     *
     * @param string $tablas     La tabla primaria que se usará en la consulta.
     * @param array  $tablas_join Las tablas adicionales que se unirán a la consulta.
     *
     * @return array|string     Devuelve las tablas ajustadas si no hay errores, de lo contrario devuelve un error.
     *
     * @throws errores Si se produjo un error al generar las tablas de unión.
     * @version 16.28.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.ajusta_tablas.21.16.0
     */
    private function ajusta_tablas( string $tablas, array $tablas_join): array|string
    {
        $tablas_env = $tablas;
        foreach ($tablas_join as $key=>$tabla_join){
            $tablas_env = $this->data_tabla_sql(key: $key, tabla_join: $tabla_join,tablas:  $tablas);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar data join',data:  $tablas_env);
            }
            $tablas = (string)$tablas_env;
        }
        return $tablas_env;
    }

    /**
     * REG
     * Procesa y valida los datos necesarios para construir una estructura de unión (join) en una consulta SQL.
     *
     * Esta función toma un array que describe los parámetros de una unión y lo valida. También normaliza valores
     * faltantes en los datos de entrada, retornando un objeto con la estructura lista para usar.
     *
     * @param array $tabla_join Array asociativo con los datos de la unión. Debe incluir las claves:
     *                          - `tabla_base` (string): Nombre de la tabla principal.
     *                          - `tabla_enlace` (string): Nombre de la tabla que se enlaza.
     *                          Opcionalmente puede incluir:
     *                          - `tabla_renombrada` (string): Alias para renombrar la tabla enlazada.
     *                          - `campo_tabla_base_id` (string): Nombre del campo en la tabla base que actúa como ID.
     *                          - `campo_renombrado` (string): Alias del campo renombrado en la unión.
     *
     * @return stdClass|array Retorna un objeto `stdClass` con los datos procesados de la unión si todo es correcto.
     *                        En caso de error, retorna un array con detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $tabla_join = [
     *     'tabla_base' => 'usuarios',
     *     'tabla_enlace' => 'roles',
     *     'tabla_renombrada' => 'roles_usuario',
     *     'campo_tabla_base_id' => 'usuario_id',
     *     'campo_renombrado' => 'id_rol'
     * ];
     *
     * $resultado = $miClase->data_join($tabla_join);
     *
     * print_r($resultado);
     * // Resultado esperado:
     * // stdClass Object
     * // (
     * //     [tabla_base] => usuarios
     * //     [tabla_enlace] => roles
     * //     [tabla_renombre] => roles_usuario
     * //     [campo_renombrado] => id_rol
     * //     [campo_tabla_base_id] => usuario_id
     * // )
     * ```
     *
     * ### Ejemplo de error:
     *
     * - Caso: Faltan claves requeridas en `$tabla_join`.
     *
     * ```php
     * $tabla_join = [
     *     'tabla_enlace' => 'roles'
     * ];
     *
     * $resultado = $miClase->data_join($tabla_join);
     *
     * print_r($resultado);
     * // Resultado esperado:
     * // Array
     * // (
     * //     [error] => 1
     * //     [mensaje] => Error al validar $tabla_join
     * //     [data] => Array
     * //         (
     * //             [error] => 1
     * //             [mensaje] => Falta la clave requerida 'tabla_base'
     * //         )
     * // )
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$tabla_join['tabla_base']`**:
     *   Tabla principal sobre la que se realizará la unión. Este parámetro es obligatorio.
     *   Ejemplo: `'usuarios'`.
     *
     * - **`$tabla_join['tabla_enlace']`**:
     *   Tabla que se enlaza con la tabla base. Este parámetro es obligatorio.
     *   Ejemplo: `'roles'`.
     *
     * - **`$tabla_join['tabla_renombrada']`**:
     *   Alias opcional para la tabla enlazada. Valor por defecto: `''`.
     *   Ejemplo: `'roles_usuario'`.
     *
     * - **`$tabla_join['campo_tabla_base_id']`**:
     *   Campo ID en la tabla base utilizado en la unión. Este parámetro es opcional.
     *   Ejemplo: `'usuario_id'`.
     *
     * - **`$tabla_join['campo_renombrado']`**:
     *   Alias opcional para el campo renombrado en la unión. Valor por defecto: `''`.
     *   Ejemplo: `'id_rol'`.
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Un objeto `stdClass` con las claves procesadas:
     *   - `tabla_base`: Nombre de la tabla base.
     *   - `tabla_enlace`: Nombre de la tabla enlazada.
     *   - `tabla_renombre`: Alias de la tabla enlazada.
     *   - `campo_tabla_base_id`: Campo ID de la tabla base.
     *   - `campo_renombrado`: Alias del campo renombrado.
     *
     * - **Error**:
     *   Un array con detalles del error si no se cumplen las validaciones.
     */

    private function data_join(array $tabla_join): stdClass|array
    {
        $keys = array('tabla_base','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $tabla_join',data: $valida);
        }

        if(!isset($tabla_join['tabla_renombrada'])){
            $tabla_join['tabla_renombrada'] = '';
        }
        $data = new stdClass();
        $data->tabla_base = $tabla_join['tabla_base'];
        $data->tabla_enlace = $tabla_join['tabla_enlace'];
        $data->tabla_renombre = $tabla_join['tabla_renombrada'];
        $data->campo_renombrado = '';
        $data->campo_tabla_base_id  = '';

        if(isset($tabla_join['campo_tabla_base_id'])) {
            $data->campo_tabla_base_id = $tabla_join['campo_tabla_base_id'];
        }
        if(isset($tabla_join['campo_renombrado'])){
            $data->campo_renombrado = $tabla_join['campo_renombrado'];
        }

        return $data;

    }

    /**
     * TOTAL
     * Genera la información requerida para realizar un "JOIN" en una consulta SQL.
     *
     * @param string $id_renombrada El identificador de la tabla renombrada.
     * @param stdClass $init Contiene los datos iniciales necesarios para crear el "JOIN".
     * @param string $join Indica el tipo de "JOIN" a realizar (INNER JOIN, LEFT JOIN, RIGHT JOIN, etc).
     * @param string $renombrada El nombre que se le asignará a la tabla después del renombramiento.
     *
     * @return stdClass|array Retorna un objeto stdClass con la descripción del "JOIN" a realizar,
     * o un arreglo en caso de que se produzca un error.
     *
     * El objeto retornado tiene la siguiente estructura:
     * - 'join_tabla': La declaración del "JOIN" realizada con la tabla y su alias.
     * - 'on_join': La condición bajo la cual se realizará el "JOIN".
     * - 'asignacion_tabla': La asignación de renombramiento de la tabla.
     * @version 15.40.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.data_for_rename.21.12.0
     */
    private function data_for_rename(string $id_renombrada, stdClass $init, string $join,
                                    string $renombrada): stdClass|array
    {
        $keys = array('tabla','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $init',data: $valida);
        }

        $join_tabla = $join.' JOIN '.$init->tabla;
        $on_join = $renombrada.$id_renombrada.' = '.$init->tabla_enlace;
        $asignacion_tabla = $join_tabla.' AS '.$renombrada;

        $data = new stdClass();
        $data->join_tabla = $join_tabla;
        $data->on_join = $on_join;
        $data->asignacion_tabla = $asignacion_tabla;
        return $data;
    }

    /**
     * TOTAL
     * Esta función obtiene los JOINs entre las tablas proporcionadas como array.
     * Valida la existencia de las claves necesarias en $tabla_join.
     * Si hay un error durante la validación, se retorna una cadena con el mensaje de error.
     *
     * Luego, intenta generar los datos de la unión.
     * Si hay un error en este proceso, se retorna una cadena con el detalle del error.
     *
     * Finalmente, intenta generar el join en sí. Si hay un error en este proceso, se retorna una cadena con el error.
     *
     * @param array $tabla_join Contiene las tablas a unir.
     * @return array|string Devuelve una matriz con los datos de la unión o una cadena con el detalle del error, en caso de que haya uno.
     * @throws errores Esta función arroja excepciones de la clase errores en caso de incidentes.
     *
     * @example
     * $instance->data_para_join(['tabla_base' => 'clientes', 'tabla_enlace' => 'pedidos']);
     *
     * @version v15.57.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.data_para_join.21.13.0
     */
    private function data_para_join(array $tabla_join): array|string
    {
        $keys = array('tabla_base','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys( keys:$keys, registro: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $tabla_join',data: $valida);
        }

        $data_join = $this->data_join(tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data',data:  $data_join);
        }

        $data = $this->genera_join(tabla: $data_join->tabla_base, tabla_enlace: $data_join->tabla_enlace,
            campo_renombrado: $data_join->campo_renombrado, campo_tabla_base_id: $data_join->campo_tabla_base_id,
            renombrada: $data_join->tabla_renombre);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join', data: $data);
        }
        return $data;
    }

    /**
     * TOTAL
     * Genera una estructura de JOIN a partir de una clave y una tabla.
     *
     * @param string $key La clave que será usada para el JOIN.
     * @param string $tabla_join La tabla con la que se hará el JOIN.
     *
     * @return array|string Si todo va bien, devuelve una estructura de JOIN.
     * En caso de error durante la validación con `valida_tabla_join` o durante la generación del JOIN con `genera_join`,
     * devuelve un mensaje con la descripción del error.
     * @version 16.13.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.data_para_join_esp.21.16.0
     */
    private function data_para_join_esp(string $key, string $tabla_join): array|string
    {
        $key = trim($key);
        $tabla_join = trim($tabla_join);

        $valida = (new validaciones())->valida_tabla_join(key: $key, tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar join', data: $valida);
        }

        $data = $this->genera_join(tabla:$key, tabla_enlace: $tabla_join );
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar join', data:$data);
        }
        return $data;
    }

    /**
     * TOTAL
     * Genera datos para una operación JOIN en SQL desde varias tablas.
     *
     * Esta función permite unir varias tablas de una base de datos SQL.
     * Admite tanto operaciones de unión básicas como personalizadas.
     *
     * @param string $key Llave utilizada para identificar un JOIN específico.
     * @param array|string $tabla_join Representa la(s) tabla(s) para realizar la operación JOIN.
     * Esto podría ser un array que contenga los nombres de múltiples tablas a unir o una cadena que contenga
     * el nombre de una sola tabla.
     * @param string $tablas Nombre de las tablas base donde se realizará la operación JOIN.
     *
     * @return array|string Dependiendo del escenario, esta función puede devolver una array de datos generados
     * para la operación JOIN, o una string que representa un mensaje de error, en caso de que se produzca
     * un error durante el proceso.
     *
     * @throws errores En caso de que ocurra un error durante el proceso, este método lanzará una excepción.
     * @version 16.26.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.data_tabla_sql.21.16.0
     */
    private function data_tabla_sql(string $key, array|string $tabla_join, string $tablas): array|string
    {
        $tablas_env = $tablas;
        if(is_array($tabla_join)){
            $tablas_env = $this->tablas_join_base(tabla_join: $tabla_join, tablas: $tablas);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar data join', data: $tablas_env);
            }
        }
        else if ($tabla_join) {
            $tablas_env = $this->tablas_join_esp(key: $key,tabla_join:  $tabla_join, tablas: $tablas);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar join', data: $tablas_env);
            }
        }
        return $tablas_env;
    }

    /**
     * TOTAL
     * Este método se utiliza para unir extensiones de estructura en una tabla base.
     *
     * Este método recorre cada extensión en la estructura proporcionada, llamando al método join_base()
     * para cada extensión y agregándola a la tabla base.
     *
     * @param array  $extension_estructura Datos que describen cómo se deben unir las extensiones a la tabla.
     * @param string $modelo_tabla El modelo de la tabla a la que se unirán las extensiones.
     * @param string $tablas Un string que detalla las tablas a las que se unirán las extensiones.
     *
     * @return array|string Las tablas actualizadas con las extensiones unidas se devuelven si todo ha ido bien.
     * En caso de errores, se devuelve un mensaje indicando el error específico.
     *
     * @throws errores Se lanzará una excepción si los datos no son un array o si se encontró un error
     * al validar los datos o generar la unión.
     *
     * @version 16.76.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.extensiones_join.21.24.0
     */
    private function extensiones_join(array $extension_estructura, string $modelo_tabla, string $tablas): array|string
    {
        $tablas_env = $tablas;
        foreach($extension_estructura as $tabla=>$data){
            if(!is_array($data)){
                return $this->error->error(mensaje: 'Error data debe ser un array', data: $data, es_final: true);
            }
            $valida = (new validaciones())->valida_keys_sql(data: $data, tabla: $modelo_tabla);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al validar data', data:$valida);
            }
            if(is_numeric($tabla)){
                return $this->error->error(mensaje:'Error $tabla debe ser un texto', data:$tabla, es_final: true);
            }

            $tablas_env = $this->join_base(data: $data, modelo_tabla: $modelo_tabla,tabla:  $tabla, tablas: $tablas);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al generar join',data: $tablas);
            }
            $tablas = (string)$tablas_env;
        }
        return $tablas_env;
    }

    /**
     * TOTAL
     * Esta función renombra un `id` en la tabla base de acuerdo al valor proporcionado.
     *
     * @param string $campo_tabla_base_id Es el nombre que se asignará al `id` de la tabla base.
     * Si este parámetro está vacío, se asignará el valor '.id' por defecto.
     *
     * @return string Retorna la `id` de la tabla base modificada.
     * Si el parámetro de entrada está vacío, retorna '.id', en otro caso concatena '.' al principio
     * del valor de entrada, y lo retorna.
     *
     * Ejemplo:
     *
     * code:
     * // el valor del $campo_tabla_base_id es 'user'
     * $id_renombrada = id_renombrada('user');
     * echo $id_renombrada;
     * // Retorna: '.user'
     *
     * // el valor del $campo_tabla_base_id es ''
     * $id_renombrada2 = id_renombrada('');
     * echo $id_renombrada2;
     * // Retorna: '.id'
     * @version 15.34.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.id_renombrada.21.12.0
     */
    private function id_renombrada(string $campo_tabla_base_id): string
    {
        $campo_tabla_base_id = trim($campo_tabla_base_id);
        $id_renombrada = '.id';
        if($campo_tabla_base_id!==''){
            $id_renombrada = '.'.$campo_tabla_base_id;
        }
        return $id_renombrada;
    }

    /**
     * TOTAL
     * Genera una instrucción SQL JOIN.
     *
     * Esta función genera una instrucción SQL JOIN entre dos tablas, pudiendo además renombrar campos.
     * Retorna un string con la instrucción SQL generada en caso de éxito, y un arreglo en caso de error.
     *
     * @param  string $tabla El nombre completo de la tabla base para el JOIN. No puede estar vacío.
     * @param  string $tabla_enlace El nombre completo de la tabla con la que se establecerá el JOIN. No puede estar vacío.
     * @param  string $campo_renombrado (Opcional) El nombre del campo que se desea renombrar. Si se omite, no se renombrará ningún campo.
     * @param  string $campo_tabla_base_id (Opcional)  El nombre del campo en la tabla base que se usará para el JOIN. Si se omite, se usa el campo id por defecto.
     * @param  string $renombrada (Opcional) El nuevo nombre para $campo_renombrado. Se usa solo si $campo_renombrado está presente.
     * @return string|array Retorna una cadena con la instrucción SQL JOIN en caso de éxito, y un arreglo con el error en caso contrario.
     * @throws errores Se lanza una excepción si alguno de los parámetros requeridos (tabla o tabla_enlace) está vacío.
     * @version 15.56.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.genera_join.21.12.0
     */
    private function genera_join(string $tabla, string $tabla_enlace, string $campo_renombrado = '',
                                 string $campo_tabla_base_id = '', string $renombrada = '' ):array|string{

        $tabla = str_replace('models\\','',$tabla);
        $tabla_enlace = str_replace('models\\','',$tabla_enlace);

        if($tabla === ''){
            return $this->error->error(mensaje: 'La tabla no puede ir vacia', data: $tabla, es_final: true);
        }
        if($tabla_enlace === ''){
            return $this->error->error(mensaje: 'El $tabla_enlace no puede ir vacio', data: $tabla_enlace,
                es_final: true);
        }

        $sql = $this->sql_join(campo_renombrado: $campo_renombrado, campo_tabla_base_id: $campo_tabla_base_id,
            renombrada: $renombrada, tabla: $tabla, tabla_enlace: $tabla_enlace);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al genera sql',data:  $sql);
        }

        return $sql;
    }

    /**
     * TOTAL
     * Genera una cláusula JOIN personalizada en una consulta SQL.
     *
     * @param string $campo_renombrado El nombre del campo que se va a renombrar.
     * @param string $campo_tabla_base_id El nombre del campo en la tabla base.
     * @param string $join La cláusula JOIN que se va a utilizar (por ejemplo, LEFT JOIN, INNER JOIN, etc.).
     * @param string $renombrada El nombre de la tabla donde se encuentra el campo que se va a renombrar.
     * @param string $tabla La tabla base para la cláusula JOIN.
     * @param string $tabla_enlace La tabla que se va a unir a la tabla base.
     *
     * @return array|string Si se genera un error durante el proceso, se devuelve un mensaje de error. Si todo va bien, se devuelve la cláusula JOIN generada.
     * @version 15.41.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.genera_join_renombrado.21.12.0
     */
    private function genera_join_renombrado(string $campo_renombrado, string $campo_tabla_base_id, string $join,
                                            string $renombrada, string $tabla, string $tabla_enlace):array|string{


        $init = $this->init_renombre(tabla: $tabla, tabla_enlace:$tabla_enlace);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar ', data: $init);
        }

        $valida = (new validaciones())->valida_renombres(campo_renombrado: $campo_renombrado,join:  $join,
            renombrada: $renombrada,tabla:  $init->tabla,
            tabla_enlace:  $init->tabla_enlace);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar ', data: $valida);
        }

        $id_renombrada = $this->id_renombrada(campo_tabla_base_id: $campo_tabla_base_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'El al obtener renombrada ',data:  $id_renombrada);
        }

        $data_rename = $this->data_for_rename(id_renombrada: $id_renombrada,init: $init,join: $join,
            renombrada: $renombrada);
        if(errores::$error){
            return $this->error->error(mensaje: 'El al obtener datos ', data: $data_rename);
        }


        return ' '.$data_rename->asignacion_tabla.' ON '.$data_rename->on_join.'.'.$campo_renombrado;
    }

    /**
     * TOTAL
     * Este método genera una cadena SQL JOIN.
     *
     * @param array $data Los datos que se utilizarán en la cláusula SQL JOIN.
     * @param string $modelo_tabla El nombre del modelo de la tabla con la que se realizará la operación JOIN.
     * @param string $tabla El nombre de la tabla con la que se realizará la operación JOIN.
     * @param string $tablas Cadena que representa la consulta SQL actualmente en construcción.
     *
     * @return string|array Devuelve la cadena SQL JOIN generada o un error si ocurre alguna excepción.
     *
     * @throws errores Si los datos proporcionados son incorrectos, lanza una excepción con un mensaje de error.
     *
     * @example
     * // Crear una instancia de la clase en la que se encuentra el método
     * $joins = new Joins();
     *
     * // Llamar al método con los datos necesarios
     * $tablas = $joins->join_base(
     *      ["columna1" => "valor1", "columna2" => "valor2"],
     *      "modelo_tabla",
     *      "nombre_tabla",
     *      "SELECT * FROM nombre_tabla"
     * );
     *
     * // Ahora, $tablas contiene una cadena SQL JOIN completa con los datos proporcionados
     *
     * @version 16.75.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.join_base.21.24.0
     */
    private function join_base(array $data, string $modelo_tabla, string $tabla, string $tablas): array|string
    {
        $valida = (new validaciones())->valida_keys_sql(data: $data, tabla: $modelo_tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data',data:  $valida);
        }

        if($tabla === ''){
            return $this->error->error(mensaje:'Error $tabla no puede venir vacia', data:$tabla, es_final: true);
        }

        $left_join = $this->left_join_str(tablas: $tablas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join', data:$left_join);
        }

        $tablas.=$left_join;

        $tabla_renombrada = $this->tabla_renombrada(data: $data,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar tabla_renombrada', data:$tabla_renombrada);
        }

        $str_join = $this->string_sql_join(data:  $data, modelo_tabla: $modelo_tabla, tabla: $tabla,
            tabla_renombrada:  $tabla_renombrada);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data:$str_join);
        }

        $tablas .= ' '.$str_join;
        return $tablas;
    }


    /**
     * TOTAL
     * Método para gestionar los joins de SQL en PHP.
     *
     * @param array $data Representa los datos para el join. Debe tener las claves 'nombre_original' y 'enlace'.
     * @param string $modelo_tabla Las tablas en las que se realiza el join.
     * @param string $tabla_renombrada El nombre bajo el que se referenciará la tabla tras el join.
     * @param string $tablas += ' '.$str_join;
     *
     * Este método realiza varias validaciones sobre los datos proporcionados y devuelve errores en caso de que no sean válidos.
     *
     * @return array|string Devuelve un string que representa la consulta SQL con los joins, o un array con información de error.
     *
     * @throws validaciones()->valida_keys_renombre En caso de que los datos no sean válidos
     * @throws validaciones()->valida_keys_sql En caso de que los datos SQL no sean válidos
     * @throws errores Si hay un error al generar el string SQL para el join
     * @throws errores Si hay un error al generar la consulta SQL completa
     * @version 16.83.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.join_renombres.21.25.0
     */
    private function join_renombres(array $data, string $modelo_tabla, string $tabla_renombrada,
                                    string $tablas): array|string
    {
        $namespace = 'models\\';
        $tabla_renombrada = str_replace($namespace,'',$tabla_renombrada);

        $valida = (new validaciones())->valida_keys_renombre(data:$data,tabla_renombrada:  $tabla_renombrada);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $data['nombre_original'] = trim($data['nombre_original']);
        $tabla_renombrada = trim($tabla_renombrada);

        $data['enlace'] = str_replace($namespace,'',$data['enlace'] );


        $valida = (new validaciones())->valida_keys_sql(data: $data,tabla:  $modelo_tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar data',data: $valida);
        }


        $left_join = $this->left_join_str(tablas: $tablas);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar join', data:$left_join);
        }

        $tablas.=$left_join;

        $str_join = $this->string_sql_join(data:  $data, modelo_tabla: $modelo_tabla, tabla: $data['nombre_original'],
            tabla_renombrada:  $tabla_renombrada);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data: $str_join);
        }

        $tablas .= ' '.$str_join;
        return $tablas;
    }

    /**
     * TOTAL
     * Función para inicializar y renombrar tablas y modelos en una operación join.
     *
     * Esta función toma como entrada cuatro argumentos los que corresponden a nombres de tablas y devolviéndolos en un
     * objeto estandar de PHP o un array en caso de error.
     *
     * @param string $tabla Un nombre de tabla para renombrar.
     * @param string $tabla_enlace Un nombre de tabla de enlace para renombrar.
     *
     * @return stdClass|array Devuelve un objeto stdClass en caso de éxito con las propiedades 'tabla', 'class', 'tabla_enlace',
     * y 'class_enlace' establecidas a sus nuevos nombres correspondientes, cada uno de tipo string.
     * En caso de error, se devuelve un array con información relacionada con el error.
     *
     * @throws errores se lanza ninguna excepción explícitamente, sin embargo, internamente maneja los errores llamando al
     * método error del objeto error si ocurren durante el proceso de renombrado.
     *
     * @example
     * <code>
     * // Crear un objeto de la clase que contiene init_renombre
     * $obj = new Joins();
     * // Llamar a alguna función pública que a su vez llama a init_renombre
     * $array = ['tabla' => 'nombre_tabla', 'tabla_enlace' => 'nombre_tabla_enlace'];
     * $resultado = $obj->algunMetodoPublico($array);
     * </code>
     *
     * @version 15.28.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.init_renombre.21.11.0
     */
    private function init_renombre(string $tabla, string $tabla_enlace): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla no puede venir vacia', data: $tabla,es_final: true);
        }
        $tabla_enlace = trim($tabla_enlace);
        if($tabla_enlace === ''){
            return $this->error->error(mensaje: 'Error $tabla_enlace no puede venir vacia', data: $tabla_enlace,
                es_final: true);
        }

        $data_models = $this->ajusta_name_models(tabla: $tabla, tabla_enlace: $tabla_enlace);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar nombre del modelo', data: $data_models);
        }

        $data = new stdClass();
        $data->tabla = $data_models->tabla->tabla;
        $data->class = $data_models->tabla->name_model;
        $data->tabla_enlace = $data_models->tabla_enlace->tabla;
        $data->class_enlace = $data_models->tabla_enlace->name_model;
        return $data;
    }

    /**
     * TOTAL
     * Crea una cadena de texto para realizar un LEFT JOIN en una consulta SQL, si la cadena $tablas no está vacía.
     *
     * @param string $tablas Una cadena que contiene el nombre de las tablas a unir.
     *
     * @return string La cadena ' LEFT JOIN ' si $tablas no está vacía, de lo contrario devuelve una cadena vacía.
     * @version 16.63.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.left_join_str.21.24.0
     */
    private function left_join_str(string $tablas): string
    {
        $left_join = '';
        if(trim($tablas) !== '') {
            $left_join =' LEFT JOIN ';
        }
        return $left_join;
    }

    /**
     * TOTAL
     * Función para obtener las tablas completas.
     *
     * Esta función toma un array de columnas join y el nombre de una tabla, y devuelve un array que representa las tablas completas.
     *
     * @param array $columnas_join Un array que contiene las columnas de join.
     * @param string $tabla El nombre de la tabla a partir de la cual se generaran las tablas completas.
     *
     * @return array|string Las tablas completas obtenidas o un error en caso de que algo salga mal durante la generación de las tablas.
     *
     * @throws errores En caso de que la tabla enviada esté vacía o si hubo un error al generar las tablas.
     * @version 16.29.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.obten_tablas_completas.21.16.0
     */
    final public function obten_tablas_completas(array $columnas_join, string $tabla):array|string{
        $tabla = str_replace('models\\','',$tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'La tabla no puede ir vacia', data: $tabla, es_final: true);
        }

        $tablas = $tabla.' AS '.$tabla;
        $tablas_join = $columnas_join;

        $tablas = $this->ajusta_tablas(tablas: $tablas, tablas_join: $tablas_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data join', data: $tablas);
        }
        return $tablas;
    }

    /**
     * TOTAL
     * Esta función se usa para renombrar múltiples tablas en una operación Join.
     *
     * @param string $modelo_tabla La tabla principal que se está uniendo.
     * @param array $renombradas Un array asociativo de tablas para renombrar y sus correspondientes datos.
     * @param string $tablas Las tablas a las que se unirán.
     *
     * @return array|string Devuelve un string actualizado de tablas renombradas para el comando Join.
     *                      Si ocurre un error, devuelve un array que contiene información del error.
     * @version 16.87.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.renombres_join.21.25.0
     */
    private function renombres_join(string $modelo_tabla, array $renombradas, string $tablas): array|string
    {
        $tablas_env = $tablas;
        foreach($renombradas as $tabla_renombrada=>$data){
            if(!is_array($data)){
                return $this->error->error(mensaje: 'Error data debe ser un array', data: $data, es_final: true);
            }
            $tablas_env = $this->join_renombres(data: $data,modelo_tabla: $modelo_tabla,
                tabla_renombrada: $tabla_renombrada, tablas:  $tablas);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al generar join', data:$tablas_env);
            }
            $tablas = (string)$tablas_env;

        }
        return $tablas_env;
    }

    /**
     * TOTAL
     * Esta función se utiliza para generar una declaración de Join SQL.
     *
     * @param string $campo_renombrado  El campo a renombrar.
     * @param string $campo_tabla_base_id El campo ID de la tabla base.
     * @param string $renombrada  La tabla a renombrar.
     * @param string $tabla  La tabla para unirse.
     * @param string $tabla_enlace  La tabla de enlace.
     *
     * @return array|string  Devuelve una cadena SQL de Join o un error si hay problemas.
     *
     * @throws errores Dispara una excepción si la tabla o tabla de enlace están vacías.
     *
     * Estructura del código de error:
     * [
     *    'mensaje' => string, // Descripción del error
     *    'data' => mixed, // Datos relacionados con el error
     * ]
     *
     * Ejemplo de uso:
     *
     * $result = $instance->sql_join("campo1", "id", "tabla1", "tabla2", "tabla3");
     * if (is_array($result)) {
     *     // Manejo de error
     *     var_dump($result);
     * } else {
     *     // Uso del resultado de la consulta
     *     var_dump($result);
     * }
     *
     * Posibles resultados:
     *
     * 1) Devuelve una cadena SQL de Join en caso de éxito. Por ejemplo :
     *    ' LEFT JOIN tabla2 AS tabla2 ON tabla2.id = tabla3.tabla2_id'
     *
     * 2) Devuelve un error si alguno de los nombres de las tablas ($tabla, $tabla_enlace) está vacío. Por ejemplo :
     *    [
     *       'mensaje' => 'Error $tabla esta vacia',
     *       'data' => '   '
     *    ]
     * @version 15.42.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.sql_join.21.12.0
     */
    private function sql_join(string $campo_renombrado, string $campo_tabla_base_id, string $renombrada, string $tabla,
                              string $tabla_enlace): array|string
    {
        $join = 'LEFT';
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error $tabla esta vacia',data:  $tabla, es_final: true);
        }
        $tabla_enlace = trim($tabla_enlace);
        if($tabla_enlace === ''){
            return $this->error->error(mensaje: 'Error $tabla_enlace esta vacia', data: $tabla_enlace, es_final: true);
        }

        if($renombrada !==''){
            $sql = $this->genera_join_renombrado(campo_renombrado: $campo_renombrado,
                campo_tabla_base_id: $campo_tabla_base_id,join: $join, renombrada: $renombrada,tabla: $tabla,
                tabla_enlace: $tabla_enlace);
            if(errores::$error ){
                return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
            }
        }
        else {

            $sql = ' '.$join.' JOIN ' . $tabla . ' AS ' . $tabla . ' ON ' . $tabla . '.id = ' . $tabla_enlace . '.'
                . $tabla . '_id';
        }

        return $sql;
    }

    /**
     * TOTAL
     * Genera una cadena SQL para una operación JOIN con las condiciones especificadas.
     *
     * @param array  $data          Los datos utilizados para generar la cláusula JOIN. Esta es una matriz asociativa
     * que contiene los siguientes elementos: 'key' (la clave utilizada en la tabla principal),
     * 'enlace' (la tabla con la que se realiza la unión) y 'key_enlace' (la clave utilizada en la tabla de enlace).
     * @param string $modelo_tabla  El nombre del modelo de tabla en el que se basará la condición JOIN.
     * @param string $tabla         El nombre de la tabla principal.
     * @param string $tabla_renombrada El alias de la tabla con el que se realizará la unión.
     *
     * @return string|array Una cadena que representa la cláusula JOIN en SQL o una matriz con información de
     * error si ocurre un problema.
     *
     * Las verificaciones de error en la función incluyen:
     * - Error al validar data
     * - $tabla no puede estar vacía
     * - $tabla_renombrada no puede estar vacía
     * - $tabla debe ser un texto (no puede ser numérico)
     * - $tabla_renombrada debe ser texto
     *
     * Ejemplo de uso:
     * -> string_sql_join(['key' => 'id', 'enlace' => 'usuarios', 'key_enlace' => 'usuario_id'], 'usuarios', 'pedidos', 'p')
     * @version 16.65.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.string_sql_join.21.24.0
     */
    private function string_sql_join( array $data, string $modelo_tabla, string $tabla,
                                      string $tabla_renombrada): string|array
    {
        $valida = (new validaciones())->valida_keys_sql(data:$data, tabla: $modelo_tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }
        $tabla = trim($tabla);
        $tabla_renombrada = trim($tabla_renombrada);

        if($tabla === ''){
            return $this->error->error(mensaje:'Error $tabla no puede venir vacia', data:$tabla, es_final: true);
        }
        if($tabla_renombrada === ''){
            return $this->error->error(mensaje:'Error $tabla_renombrada no puede venir vacia', data:$tabla_renombrada,
                es_final: true);
        }

        if(is_numeric($tabla)){
            return $this->error->error(mensaje:'Error $tabla debe ser un texto', data:$tabla, es_final: true);
        }
        if(is_numeric($tabla_renombrada)){
            return $this->error->error(mensaje:'Error $tabla debe ser un texto', data:$tabla, es_final: true);
        }

        return "$tabla AS $tabla_renombrada  ON $tabla_renombrada.$data[key] = $data[enlace].$data[key_enlace]";
    }

    /**
     * TOTAL
     * Esta función sirve para renombrar una tabla si recibe el parámetro de 'renombre'.
     *
     * @param array $data Contiene la información sobre el renombramiento.
     * @param string $tabla Es el nombre original de la tabla.
     *
     * @return string|array Retorna el nombre renombrado de la tabla si se proporciona 'renombre'.
     *                      Retorna un error si el nombre de la tabla proporcionado está vacío.
     * @version 16.69.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.tabla_renombrada.21.24.0
     */
    private function tabla_renombrada(array $data, string $tabla): string|array
    {
        $tabla = trim($tabla);

        if($tabla === ''){
            return $this->error->error(mensaje:'Error $tabla esta vacia', data:$tabla, es_final: true);
        }
        $tabla_renombrada = $tabla;
        if(isset($data['renombre'])){
            $data['renombre'] = trim($data['renombre']);
            if($data['renombre'] !== ''){
                $tabla_renombrada = $data['renombre'];
            }
        }
        return trim($tabla_renombrada);

    }

    /**
     * TOTAL
     * Este método se utiliza para generar un conjunto de tablas para una consulta SQL JOIN.
     *
     * Acepta una serie de parámetros que definen la estructura de la consulta SQL y devuelve un array con las tablas resultantes.
     * Si se encuentra algún error durante el proceso, se devuelve una cadena con el mensaje de error.
     *
     * @param array $columnas Un array que contiene las columnas que se van a seleccionar en la consulta SQL.
     * @param array $extension_estructura Un array que contiene información adicional sobre la estructura de la consulta.
     * @param array $extra_join Un array con información adicional para el JOIN.
     * @param string $modelo_tabla La tabla base que se va a usar en la consulta SQL JOIN.
     * @param array $renombradas Un array con los nuevos nombres que se van a asignar a las columnas seleccionadas.
     * @param string $tabla El nombre de la tabla a considerar.
     *
     * @return array|string Retorna una matriz con las tablas generadas para la consulta SQL JOIN.
     * Si ocurre un error, retorna una cadena con el mensaje de error.
     *
     * @throws errores Se lanza una excepción si detecta que la tabla está vacía.
     * Otros errores posibles que se pueden generar están relacionados con las operaciones de la función interna 'error'.
     *
     * @example
     * $res = $instance->tablas(['col1', 'col2'], [], [], 'tabla1', [], 'tabla2');
     * print_r($res);
     * @version 16.88.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.tablas.21.25.0
     */
    final public function tablas(array $columnas, array $extension_estructura, array $extra_join, string $modelo_tabla,
                                 array $renombradas, string $tabla): array|string
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'La tabla no puede ir vacia',data:  $tabla, es_final: true);
        }
        $tablas = $this->obten_tablas_completas(columnas_join:  $columnas, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener tablas',data:  $tablas);
        }

        $tablas = $this->extensiones_join(extension_estructura: $extension_estructura, modelo_tabla: $modelo_tabla,
            tablas:  $tablas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join',data:  $tablas);
        }

        $tablas = $this->extensiones_join(extension_estructura: $extra_join, modelo_tabla: $modelo_tabla,
            tablas:  $tablas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join',data:  $tablas);
        }

        $tablas = $this->renombres_join(modelo_tabla:$modelo_tabla,renombradas: $renombradas, tablas: $tablas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join', data: $tablas);
        }
        return $tablas;
    }


    /**
     * TOTAL
     * La función tablas_join_base forma la base para realizar operaciones de unión entre tablas en una base de datos.
     *
     * @param array $tabla_join   Este parámetro es un arreglo que contiene información sobre las tablas
     *                            que se unirán. Debe contener claves como 'tabla_base' y 'tabla_enlace'.
     *
     * @param string $tablas      Este parámetro es una cadena que representa las tablas que ya están en la unión.
     *                            Esta cadena se actualizará para incluir las nuevas tablas de la operación de unión.
     *
     * La función primero verifica si el arreglo $tabla_join contiene las claves necesarias,
     * 'tabla_base' y 'tabla_enlace', a través del método valida_existencia_keys del objeto validacion.
     * Si $tabla_join no tiene las claves necesarias, la función devuelve un error.
     *
     * Si $tabla_join tiene las claves necesarias, entonces la función procede a generar "datos para unirse"
     * a través del método data_para_join. Si hay un error al generar estos datos, la función devuelve un error.
     *
     * Si los "datos para unirse" se generan con éxito, se agregan a la cadena $tablas.
     *
     * @return array|string   La función devuelve la cadena $tablas actualizada o un arreglo de error si se encuentra alguno.
     * @version 15.59.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.tablas_join_base.21.15.0
     */
    private function tablas_join_base(array $tabla_join, string $tablas): array|string
    {
        $keys = array('tabla_base','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $tabla_join',data: $valida);
        }

        $data = $this->data_para_join(tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data join', data: $data);
        }
        $tablas .=  $data;
        return $tablas;
    }

    /**
     * TOTAL
     * tablas_join_esp Genera una string que representa una operación JOIN en SQL en español.
     *
     * @param string $key Clave que se utiliza para buscar y unir la tabla especificada.
     * @param string $tabla_join Nombre de la tabla que se está uniendo.
     * @param string $tablas Variable acumulativa en la cual se van guardando las condiciones de union de las tablas.
     *
     * @return array|string Devuelve una cadena de texto que representa las condiciones de unión de las tablas si todo sale bien.
     *                     Si ocurre un error en las validaciones o en la generación del JOIN, se devuelve un array con información sobre el error.
     *
     * ## Ejemplos de uso
     * ```php
     * $instancia = new ClaseDondeEstaDefinidaEstaFuncion();
     * $joins = $instancia->tablas_join_esp('clave', 'tabla_a_unirse', 'otras_tablas_join');
     * if (is_array($joins)) {
     *     // Hubo un error, manejarlo aquí.
     * } else {
     *     // $joins es una string con las condiciones de JOIN SQL, utilizarla en el query.
     * }
     * ```
     *
     * @see validaciones::valida_tabla_join() para la validación utilizada.
     * @see data_para_join_esp() se utiliza para recopilar los datos necesarios para el JOIN.
     *
     * @throws errores si ocurre un error durante la validación o la generación del JOIN.
     * @version 16.25.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.joins.tablas_join_esp.21.16.0
     */
    private function tablas_join_esp(string $key, string $tabla_join, string $tablas): array|string
    {
        $key = trim($key);
        $tabla_join = trim($tabla_join);

        $valida = (new validaciones())->valida_tabla_join(key: $key, tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar join', data: $valida);
        }
        $data = $this->data_para_join_esp(key: $key, tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join',data:  $data);
        }
        $tablas .=  $data;
        return $tablas;
    }
}
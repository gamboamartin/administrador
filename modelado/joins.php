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
     * REG
     * Ajusta las tablas en una consulta SQL, generando los JOINs necesarios basados en las configuraciones dadas.
     *
     * @param string $tablas Cadena acumulativa de las tablas y sus respectivos JOINs ya generados.
     *                       - Puede comenzar como una cadena vacía si no hay tablas previas.
     * @param array $tablas_join Arreglo que define las relaciones entre tablas para construir los JOINs.
     *                           - La clave del arreglo debe ser el nombre de la tabla base.
     *                           - El valor puede ser:
     *                             - Una cadena que representa la tabla de unión.
     *                             - Un arreglo con información detallada para construir el JOIN.
     *
     * @return array|string Devuelve una cadena con los JOINs generados o un array de error si ocurre un fallo.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * // Caso con $tablas_join como array de detalles
     * $tablas = '';
     * $tablas_join = [
     *     'usuarios' => [
     *         'tabla_base' => 'usuarios',
     *         'tabla_enlace' => 'roles',
     *         'campo_tabla_base_id' => 'id',
     *         'campo_renombrado' => 'roles_id',
     *     ],
     *     'roles' => 'permisos'
     * ];
     *
     * $resultado = $this->ajusta_tablas($tablas, $tablas_join);
     * echo $resultado;
     * // Resultado esperado:
     * // ' LEFT JOIN roles ON usuarios.id = roles.roles_id LEFT JOIN permisos ON roles.id = permisos.roles_id'
     * ```
     *
     * ### Proceso de la función:
     * 1. **Inicialización de `$tablas_env`:**
     *    - Se asigna el valor inicial de `$tablas` a `$tablas_env`.
     * 2. **Iteración sobre `$tablas_join`:**
     *    - Recorre cada elemento del arreglo `$tablas_join`, donde:
     *      - La clave (`$key`) representa la tabla base.
     *      - El valor (`$tabla_join`) contiene los detalles del JOIN.
     *    - Llama a la función `data_tabla_sql` para generar el SQL de las tablas y sus JOINs.
     * 3. **Validación de errores:**
     *    - Verifica si la ejecución de `data_tabla_sql` produce errores y los maneja adecuadamente.
     * 4. **Acumulación de resultados:**
     *    - Concatena los JOINs generados en `$tablas_env`.
     * 5. **Retorno del resultado:**
     *    - Devuelve la cadena SQL acumulativa con los JOINs generados.
     *
     * ### Ejemplo de errores:
     * **Error por `$tablas_join` mal estructurado:**
     * ```php
     * $tablas = '';
     * $tablas_join = [
     *     'usuarios' => [
     *         // Falta la clave 'tabla_base'
     *         'tabla_enlace' => 'roles',
     *         'campo_tabla_base_id' => 'id',
     *         'campo_renombrado' => 'roles_id',
     *     ],
     * ];
     *
     * $resultado = $this->ajusta_tablas($tablas, $tablas_join);
     * print_r($resultado);
     * // Resultado esperado:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error al generar data join',
     * //     'data' => [
     * //         'error' => 1,
     * //         'mensaje' => 'Error al validar $tabla_join',
     * //         'data' => [...]
     * //     ]
     * // ]
     * ```
     *
     * **Error por `$key` vacío:**
     * ```php
     * $tablas = '';
     * $tablas_join = [
     *     '' => 'roles',
     * ];
     *
     * $resultado = $this->ajusta_tablas($tablas, $tablas_join);
     * print_r($resultado);
     * // Resultado esperado:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error al generar data join',
     * //     'data' => [
     * //         'error' => 1,
     * //         'mensaje' => 'Error el key no puede ser un numero',
     * //         'data' => ''
     * //     ]
     * // ]
     * ```
     *
     * ### Casos de uso:
     * - Construcción dinámica de consultas SQL que involucren múltiples tablas y relaciones.
     * - Uso en sistemas con esquemas complejos de bases de datos y JOINs condicionales.
     *
     * ### Consideraciones:
     * - El arreglo `$tablas_join` debe estar correctamente estructurado para evitar errores.
     * - La función depende de `data_tabla_sql` para generar el SQL de las relaciones entre tablas.
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
     * REG
     * Genera la estructura de datos necesaria para renombrar una tabla en una consulta SQL,
     * incluyendo instrucciones JOIN y asignaciones con alias.
     *
     * @param string $id_renombrada Identificador de la tabla base. Por ejemplo, puede ser `".id"` o `".campo_id"`.
     * @param stdClass $init Objeto con información inicial sobre las tablas a utilizar en la construcción.
     *                       Debe contener las claves:
     *                       - `tabla`: Nombre de la tabla principal.
     *                       - `tabla_enlace`: Nombre de la tabla de enlace.
     * @param string $join Tipo de JOIN a utilizar en la consulta (por ejemplo: `INNER`, `LEFT`, `RIGHT`).
     * @param string $renombrada Alias o nuevo nombre que se asignará a la tabla en la consulta.
     *
     * @return stdClass|array Devuelve un objeto con los siguientes atributos:
     *                        - `join_tabla`: Instrucción JOIN para la tabla.
     *                        - `on_join`: Condición ON del JOIN.
     *                        - `asignacion_tabla`: Alias asignado a la tabla.
     *                        En caso de error, devuelve un array con detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * // Datos iniciales
     * $id_renombrada = '.id';
     * $init = new stdClass();
     * $init->tabla = 'usuarios';
     * $init->tabla_enlace = 'roles.id_usuario';
     * $join = 'INNER';
     * $renombrada = 'u';
     *
     * // Llamada a la función
     * $resultado = $this->data_for_rename(
     *     id_renombrada: $id_renombrada,
     *     init: $init,
     *     join: $join,
     *     renombrada: $renombrada
     * );
     *
     * // Salida esperada
     * // $resultado->join_tabla: "INNER JOIN usuarios"
     * // $resultado->on_join: "u.id = roles.id_usuario"
     * // $resultado->asignacion_tabla: "INNER JOIN usuarios AS u"
     * ```
     *
     * ### Detalles de validación:
     * - La función valida que `$init` contenga las claves `tabla` y `tabla_enlace`.
     * - Si algún valor requerido está vacío, devuelve un error con detalles.
     *
     * ### Ejemplo de integración:
     * ```php
     * class joins {
     *     private function data_for_rename(string $id_renombrada, stdClass $init, string $join, string $renombrada): stdClass|array {
     *         $keys = array('tabla', 'tabla_enlace');
     *         $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $init);
     *         if (errores::$error) {
     *             return $this->error->error(mensaje: 'Error al validar $init', data: $valida);
     *         }
     *
     *         $join_tabla = $join . ' JOIN ' . $init->tabla;
     *         $on_join = $renombrada . $id_renombrada . ' = ' . $init->tabla_enlace;
     *         $asignacion_tabla = $join_tabla . ' AS ' . $renombrada;
     *
     *         $data = new stdClass();
     *         $data->join_tabla = $join_tabla;
     *         $data->on_join = $on_join;
     *         $data->asignacion_tabla = $asignacion_tabla;
     *         return $data;
     *     }
     * }
     * ```
     *
     * ### Consideraciones:
     * - Asegúrate de que los valores de `$join` sean válidos (`INNER`, `LEFT`, `RIGHT`) antes de llamarla.
     * - Esta función se puede usar para construir consultas dinámicas y mantener consistencia en alias y relaciones entre tablas.
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
     * REG
     * Genera una cláusula SQL JOIN basada en la estructura proporcionada en `$tabla_join`.
     * Valida la existencia de claves necesarias y construye la relación entre las tablas base y de enlace.
     *
     * @param array $tabla_join Array que contiene la información necesaria para generar el JOIN.
     *                          Debe incluir las claves:
     *                          - `tabla_base`: Nombre de la tabla base.
     *                          - `tabla_enlace`: Nombre de la tabla de enlace.
     *                          Opcionalmente puede incluir:
     *                          - `tabla_renombrada`: Alias para la tabla base en el SQL.
     *                          - `campo_tabla_base_id`: Campo en la tabla base que se utiliza en la relación.
     *                          - `campo_renombrado`: Campo renombrado utilizado en la relación.
     *
     * @return array|string Retorna la cláusula SQL generada como una cadena en caso de éxito,
     *                      o un array con el detalle del error si ocurre algún problema.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $tabla_join = [
     *     'tabla_base' => 'usuarios',
     *     'tabla_enlace' => 'roles',
     *     'tabla_renombrada' => 'u',
     *     'campo_tabla_base_id' => 'id',
     *     'campo_renombrado' => 'usuario_id'
     * ];
     *
     * $sql_join = $this->data_para_join($tabla_join);
     *
     * // Resultado esperado:
     * // " LEFT JOIN usuarios AS u ON u.id = roles.usuario_id"
     * ```
     *
     * ### Validaciones realizadas:
     * - Verifica que el array `$tabla_join` contenga las claves requeridas (`tabla_base` y `tabla_enlace`).
     * - Llama a la función `data_join` para estructurar la información base del JOIN.
     * - Llama a `genera_join` para construir la cláusula SQL completa con los datos estructurados.
     *
     * ### Detalles de la implementación:
     * 1. **Validación del array `$tabla_join`:**
     *    - Verifica que existan las claves `tabla_base` y `tabla_enlace`.
     *    - Si faltan claves o los valores no son válidos, retorna un error.
     * 2. **Generación de la estructura de datos para el JOIN:**
     *    - Llama a `data_join` para obtener un objeto con las propiedades necesarias (`tabla`, `tabla_enlace`, etc.).
     * 3. **Construcción de la cláusula JOIN:**
     *    - Usa `genera_join` con los datos obtenidos para construir la cláusula SQL final.
     *
     * ### Ejemplo de integración en un sistema:
     * ```php
     * class GeneradorSQL {
     *     public function genera_query() {
     *         $tabla_join = [
     *             'tabla_base' => 'productos',
     *             'tabla_enlace' => 'categorias',
     *             'tabla_renombrada' => 'p',
     *             'campo_tabla_base_id' => 'id',
     *             'campo_renombrado' => 'producto_id'
     *         ];
     *
     *         $sql_join = $this->data_para_join($tabla_join);
     *         echo $sql_join;
     *         // Resultado: " LEFT JOIN productos AS p ON p.id = categorias.producto_id"
     *     }
     * }
     * ```
     *
     * ### Posibles errores y sus causas:
     * - **Faltan claves en `$tabla_join`:** Si no están presentes `tabla_base` o `tabla_enlace`, se retorna un error.
     * - **Error en `data_join`:** Si la estructura del JOIN no puede generarse correctamente.
     * - **Error en `genera_join`:** Si la cláusula SQL no puede generarse debido a valores inválidos o inconsistentes.
     *
     * ### Consideraciones:
     * - Asegúrate de que `$tabla_join` tenga todas las claves necesarias para evitar errores.
     * - Es ideal para construir relaciones SQL dinámicas entre tablas con validaciones estrictas.
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
     * REG
     * Genera la estructura necesaria para un SQL JOIN entre dos tablas, validando previamente los parámetros.
     *
     * @param string $key Nombre de la tabla base del JOIN.
     *                    - No debe ser numérico.
     *                    - No debe estar vacío.
     * @param string $tabla_join Nombre de la tabla que se unirá en el JOIN.
     *                           - No debe ser numérico.
     *                           - No debe estar vacío.
     *
     * @return array|string Retorna una cadena con el SQL JOIN generado o un array con detalles del error si ocurre una validación fallida.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $key = 'usuarios';
     * $tabla_join = 'roles';
     *
     * $resultado = $this->data_para_join_esp($key, $tabla_join);
     *
     * if (is_string($resultado)) {
     *     echo 'SQL JOIN generado: ' . $resultado;
     * } else {
     *     print_r($resultado); // Mostrará el detalle del error si ocurre.
     * }
     * // Resultado esperado:
     * // SQL JOIN generado: ' LEFT JOIN roles ON usuarios.id = roles.usuarios_id'
     * ```
     *
     * ### Validaciones realizadas:
     * 1. **Validación de `$key` y `$tabla_join`:**
     *    - Verifica que no sean numéricos ni cadenas vacías.
     *    - Utiliza la función `valida_tabla_join` para realizar estas validaciones.
     * 2. **Generación del JOIN:**
     *    - Utiliza la función `genera_join` para construir el SQL JOIN entre las tablas especificadas.
     *
     * ### Ejemplo de errores:
     * **Error por `$key` vacío:**
     * ```php
     * $key = '';
     * $tabla_join = 'roles';
     *
     * $resultado = $this->data_para_join_esp($key, $tabla_join);
     * print_r($resultado);
     * // Resultado esperado:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error key esta vacio',
     * //     'data' => ''
     * // ]
     * ```
     *
     * **Error por `$tabla_join` numérico:**
     * ```php
     * $key = 'usuarios';
     * $tabla_join = '123';
     *
     * $resultado = $this->data_para_join_esp($key, $tabla_join);
     * print_r($resultado);
     * // Resultado esperado:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error el $tabla_join no puede ser un numero',
     * //     'data' => '123'
     * // ]
     * ```
     *
     * ### Detalles de implementación:
     * - La función utiliza `trim` para limpiar los espacios en blanco de los parámetros `$key` y `$tabla_join`.
     * - Llama a `valida_tabla_join` para validar la estructura de las tablas antes de generar el JOIN.
     * - Si las validaciones son exitosas, utiliza `genera_join` para construir el SQL JOIN entre las tablas.
     *
     * ### Casos de uso:
     * - Generar dinámicamente un JOIN entre dos tablas en una consulta SQL, asegurando que los parámetros sean válidos.
     * - Validar los nombres de las tablas antes de realizar operaciones en la base de datos.
     *
     * ### Consideraciones:
     * - Los nombres de tablas deben estar bien definidos y ser coherentes con la estructura de la base de datos.
     * - Esta función es ideal para sistemas que construyen consultas SQL de manera dinámica y necesitan robustez en la validación de entradas.
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
     * REG
     * Genera la estructura SQL de las tablas para una consulta, incluyendo los JOINs necesarios.
     *
     * @param string $key Nombre de la tabla base en el JOIN.
     *                    - Debe ser una cadena no vacía.
     * @param array|string $tabla_join Datos para generar el JOIN.
     *                                  - Puede ser un arreglo con detalles para la construcción del JOIN.
     *                                  - O una cadena con el nombre de la tabla a unir.
     * @param string $tablas Cadena acumulativa que contiene los JOINs previamente generados.
     *
     * @return array|string Devuelve la cadena SQL con los JOINs generados, o un array de error si ocurre un fallo.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * // Caso con $tabla_join como array
     * $key = 'usuarios';
     * $tabla_join = [
     *     'tabla_base' => 'usuarios',
     *     'tabla_enlace' => 'roles',
     *     'campo_tabla_base_id' => 'id',
     *     'campo_renombrado' => 'roles_id',
     * ];
     * $tablas = '';
     *
     * $resultado = $this->data_tabla_sql($key, $tabla_join, $tablas);
     * echo $resultado;
     * // Resultado esperado:
     * // ' LEFT JOIN roles ON usuarios.id = roles.roles_id'
     *
     * // Caso con $tabla_join como string
     * $key = 'usuarios';
     * $tabla_join = 'roles';
     * $tablas = '';
     *
     * $resultado = $this->data_tabla_sql($key, $tabla_join, $tablas);
     * echo $resultado;
     * // Resultado esperado:
     * // ' LEFT JOIN roles ON usuarios.id = roles.usuarios_id'
     * ```
     *
     * ### Proceso de la función:
     * 1. **Inicialización de `$tablas_env`:**
     *    - Inicializa la variable `$tablas_env` con el valor del parámetro `$tablas`.
     * 2. **Validación y procesamiento de `$tabla_join`:**
     *    - Si `$tabla_join` es un array:
     *      - Llama a la función `tablas_join_base` para generar los JOINs en base al contenido del arreglo.
     *      - Verifica errores tras la ejecución.
     *    - Si `$tabla_join` es una cadena:
     *      - Llama a la función `tablas_join_esp` para generar un JOIN simple basado en la tabla base y la de unión.
     *      - Verifica errores tras la ejecución.
     * 3. **Retorno del resultado:**
     *    - Devuelve la cadena SQL acumulativa con los JOINs generados.
     *
     * ### Ejemplo de errores:
     * **Error por `$key` vacío:**
     * ```php
     * $key = '';
     * $tabla_join = 'roles';
     * $tablas = '';
     *
     * $resultado = $this->data_tabla_sql($key, $tabla_join, $tablas);
     * print_r($resultado);
     * // Resultado esperado:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error al generar join',
     * //     'data' => 'Error key esta vacio'
     * // ]
     * ```
     *
     * **Error por `$tabla_join` mal estructurado (array):**
     * ```php
     * $key = 'usuarios';
     * $tabla_join = [
     *     'tabla_enlace' => 'roles', // Falta la clave 'tabla_base'
     * ];
     * $tablas = '';
     *
     * $resultado = $this->data_tabla_sql($key, $tabla_join, $tablas);
     * print_r($resultado);
     * // Resultado esperado:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error al validar $tabla_join',
     * //     'data' => ['tabla_enlace' => 'roles']
     * // ]
     * ```
     *
     * ### Casos de uso:
     * - Generación dinámica de consultas SQL que involucran múltiples tablas y JOINs.
     * - Construcción flexible para casos con configuraciones complejas de tablas relacionadas.
     *
     * ### Consideraciones:
     * - La función depende de las funciones `tablas_join_base` y `tablas_join_esp` para generar los JOINs.
     * - Los nombres de las tablas deben ser válidos y coincidir con las definiciones en la base de datos.
     * - Si `$tabla_join` es un array, debe contener las claves necesarias para definir el JOIN.
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
     * REG
     * Genera una cadena que representa un identificador renombrado basado en un campo de tabla base.
     *
     * Si se proporciona un valor en `$campo_tabla_base_id`, se utiliza como parte del identificador renombrado.
     * De lo contrario, el identificador por defecto será `".id"`.
     *
     * @param string $campo_tabla_base_id Nombre del campo de la tabla base que se utilizará para generar el identificador renombrado.
     *                                    Si está vacío, se usará `".id"` como valor por defecto.
     *
     * @return string Retorna la cadena del identificador renombrado.
     *                Ejemplo: `".id"` o `".<campo_tabla_base_id>"`.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * // Caso 1: Se proporciona un campo válido.
     * $campo_tabla_base_id = 'usuario_id';
     * $resultado = $miClase->id_renombrada($campo_tabla_base_id);
     * echo $resultado; // Salida esperada: ".usuario_id"
     *
     * // Caso 2: Se proporciona una cadena vacía.
     * $campo_tabla_base_id = '';
     * $resultado = $miClase->id_renombrada($campo_tabla_base_id);
     * echo $resultado; // Salida esperada: ".id"
     * ```
     *
     * ### Detalles de validación:
     * - **Espacios en blanco:** El parámetro `$campo_tabla_base_id` será recortado para eliminar espacios al inicio y al final.
     * - Si `$campo_tabla_base_id` está vacío después del recorte, se devolverá `".id"` como valor predeterminado.
     *
     * ### Consideraciones:
     * - Útil para generar identificadores únicos y personalizados en consultas SQL o estructuras de datos.
     * - Asegura que siempre se devuelva un valor válido, incluso si el parámetro de entrada está vacío.
     *
     * ### Ejemplo de integración:
     * ```php
     * class Ejemplo {
     *     private function id_renombrada(string $campo_tabla_base_id): string {
     *         $campo_tabla_base_id = trim($campo_tabla_base_id);
     *         $id_renombrada = '.id';
     *         if ($campo_tabla_base_id !== '') {
     *             $id_renombrada = '.' . $campo_tabla_base_id;
     *         }
     *         return $id_renombrada;
     *     }
     * }
     *
     * // Uso de la función
     * $obj = new Ejemplo();
     * echo $obj->id_renombrada('orden_id'); // Salida: ".orden_id"
     * echo $obj->id_renombrada('');         // Salida: ".id"
     * ```
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
     * REG
     * Genera una cláusula SQL de tipo JOIN para relacionar dos tablas, con soporte para alias y campos específicos.
     *
     * @param string $tabla Nombre de la tabla base. Este parámetro no puede estar vacío.
     * @param string $tabla_enlace Nombre de la tabla de enlace que se relaciona con la tabla base. Este parámetro no puede estar vacío.
     * @param string $campo_renombrado Nombre del campo renombrado para la relación (opcional). Si se especifica, se usa en la cláusula `ON`.
     * @param string $campo_tabla_base_id Nombre del campo de la tabla base que se utiliza en la relación (opcional). Por defecto, utiliza `.id`.
     * @param string $renombrada Alias para la tabla base en la cláusula SQL (opcional). Si se especifica, se usa como alias en el SQL.
     *
     * @return array|string Retorna la cláusula SQL generada como una cadena en caso de éxito, o un array de error si ocurre alguna falla.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * // Caso 1: Generar un JOIN básico
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles';
     *
     * $sql = $this->genera_join(
     *     tabla: $tabla,
     *     tabla_enlace: $tabla_enlace
     * );
     *
     * // Resultado esperado:
     * // " LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id"
     *
     * // Caso 2: Generar un JOIN con alias y campos renombrados
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles';
     * $campo_renombrado = 'id_usuario';
     * $campo_tabla_base_id = 'id';
     * $renombrada = 'u';
     *
     * $sql = $this->genera_join(
     *     tabla: $tabla,
     *     tabla_enlace: $tabla_enlace,
     *     campo_renombrado: $campo_renombrado,
     *     campo_tabla_base_id: $campo_tabla_base_id,
     *     renombrada: $renombrada
     * );
     *
     * // Resultado esperado:
     * // " LEFT JOIN usuarios AS u ON u.id = roles.id_usuario"
     * ```
     *
     * ### Validaciones realizadas:
     * - Verifica que los parámetros `$tabla` y `$tabla_enlace` no estén vacíos.
     * - Remueve el prefijo `models\` de los nombres de tabla para garantizar un formato limpio.
     * - Valida la generación de la cláusula SQL a través de la función `sql_join`.
     *
     * ### Detalles de la generación:
     * - Si se especifican `$campo_renombrado`, `$campo_tabla_base_id` o `$renombrada`, se generan cláusulas SQL más complejas con alias y campos personalizados.
     * - Si no se especifican estos parámetros opcionales, se genera una relación básica entre las tablas.
     *
     * ### Ejemplo de integración en un sistema:
     * ```php
     * class GeneradorSQL {
     *     public function genera_query() {
     *         $sql = $this->genera_join(
     *             tabla: 'productos',
     *             tabla_enlace: 'categorias',
     *             campo_renombrado: 'id_producto',
     *             campo_tabla_base_id: 'id',
     *             renombrada: 'p'
     *         );
     *         echo $sql;
     *         // Resultado: " LEFT JOIN productos AS p ON p.id = categorias.id_producto"
     *     }
     * }
     * ```
     *
     * ### Posibles errores y sus causas:
     * - **Error `$tabla` vacía:** Ocurre si no se proporciona el nombre de la tabla base.
     * - **Error `$tabla_enlace` vacía:** Ocurre si no se proporciona el nombre de la tabla de enlace.
     * - **Error en la generación del SQL:** Si la función `sql_join` encuentra un problema al generar la cláusula SQL.
     *
     * ### Consideraciones:
     * - Útil para construir dinámicamente relaciones entre tablas con validaciones estrictas de parámetros.
     * - Simplifica la creación de consultas SQL complejas con alias y claves específicas.
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
     * REG
     * Genera una cláusula SQL de tipo JOIN con alias y condiciones renombradas para relacionar tablas.
     *
     * @param string $campo_renombrado Nombre del campo a utilizar como clave renombrada en la relación (ejemplo: `id_usuario`).
     * @param string $campo_tabla_base_id Nombre del campo de ID en la tabla base. Si está vacío, se utiliza `.id` por defecto.
     * @param string $join Tipo de JOIN a aplicar (`INNER`, `LEFT`, `RIGHT`).
     * @param string $renombrada Alias que se asignará a la tabla relacionada.
     * @param string $tabla Nombre de la tabla base para la relación.
     * @param string $tabla_enlace Nombre de la tabla de enlace con la que se relaciona la tabla base.
     *
     * @return array|string Devuelve la cláusula JOIN generada como una cadena SQL o un array con detalles del error en caso de fallos.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * // Parámetros de entrada
     * $campo_renombrado = 'id_usuario';
     * $campo_tabla_base_id = 'id';
     * $join = 'INNER';
     * $renombrada = 'u';
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles.id_usuario';
     *
     * // Llamada a la función
     * $resultado = $this->genera_join_renombrado(
     *     campo_renombrado: $campo_renombrado,
     *     campo_tabla_base_id: $campo_tabla_base_id,
     *     join: $join,
     *     renombrada: $renombrada,
     *     tabla: $tabla,
     *     tabla_enlace: $tabla_enlace
     * );
     *
     * // Salida esperada
     * // INNER JOIN usuarios AS u ON u.id = roles.id_usuario.id_usuario
     * ```
     *
     * ### Validaciones realizadas:
     * - La función valida que `$tabla`, `$tabla_enlace`, `$campo_renombrado`, `$renombrada` y `$join` no estén vacíos.
     * - Asegura que `$join` sea uno de los valores válidos (`INNER`, `LEFT`, `RIGHT`).
     * - Verifica la estructura de los datos iniciales para evitar inconsistencias.
     *
     * ### Detalles de la generación:
     * - Llama a `init_renombre` para obtener la estructura inicial de las tablas y sus nombres renombrados.
     * - Valida las entradas con `valida_renombres`.
     * - Genera el identificador renombrado con `id_renombrada`.
     * - Construye la cláusula JOIN con `data_for_rename`.
     *
     * ### Ejemplo de integración en un sistema:
     * ```php
     * class GeneradorSQL {
     *     public function genera_query() {
     *         $campo_renombrado = 'id_usuario';
     *         $campo_tabla_base_id = 'id';
     *         $join = 'LEFT';
     *         $renombrada = 'u';
     *         $tabla = 'usuarios';
     *         $tabla_enlace = 'roles.id_usuario';
     *
     *         $query = $this->genera_join_renombrado(
     *             campo_renombrado: $campo_renombrado,
     *             campo_tabla_base_id: $campo_tabla_base_id,
     *             join: $join,
     *             renombrada: $renombrada,
     *             tabla: $tabla,
     *             tabla_enlace: $tabla_enlace
     *         );
     *
     *         echo $query;
     *         // Resultado: " LEFT JOIN usuarios AS u ON u.id = roles.id_usuario.id_usuario"
     *     }
     * }
     * ```
     *
     * ### Posibles errores y sus causas:
     * - **Error al inicializar:** Si `$tabla` o `$tabla_enlace` están vacíos, devuelve un mensaje indicando la ausencia de datos.
     * - **Error en los datos:** Si `$join` contiene un valor no válido (que no sea `INNER`, `LEFT`, `RIGHT`).
     * - **Error en datos de entrada:** Si las claves esperadas no están presentes en los datos de entrada.
     *
     * ### Consideraciones:
     * - Útil para generar dinámicamente consultas SQL con alias y relaciones claras entre tablas.
     * - Facilita la construcción de consultas complejas manteniendo consistencia y validación previa.
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
     * REG
     * Inicializa y ajusta los nombres y clases asociadas a dos tablas especificadas. Verifica que los valores
     * proporcionados no estén vacíos y ajusta los nombres de las tablas y sus modelos correspondientes.
     *
     * @param string $tabla Nombre de la tabla base. Debe ser una cadena no vacía.
     * @param string $tabla_enlace Nombre de la tabla de enlace. Debe ser una cadena no vacía.
     *
     * @return stdClass|array Retorna un objeto con los nombres y clases ajustadas si el proceso es exitoso:
     * - **tabla**: Nombre ajustado de la tabla base.
     * - **class**: Nombre completo del modelo asociado a la tabla base.
     * - **tabla_enlace**: Nombre ajustado de la tabla de enlace.
     * - **class_enlace**: Nombre completo del modelo asociado a la tabla de enlace.
     *
     * En caso de error, retorna un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles';
     *
     * $resultado = $miClase->init_renombre(tabla: $tabla, tabla_enlace: $tabla_enlace);
     *
     * print_r($resultado);
     * // Resultado esperado:
     * // stdClass Object
     * // (
     * //     [tabla] => usuarios
     * //     [class] => models\usuarios
     * //     [tabla_enlace] => roles
     * //     [class_enlace] => models\roles
     * // )
     * ```
     *
     * ### Ejemplo de error:
     *
     * 1. **Caso:** La tabla base está vacía.
     * ```php
     * $tabla = '';
     * $tabla_enlace = 'roles';
     *
     * $resultado = $miClase->init_renombre(tabla: $tabla, tabla_enlace: $tabla_enlace);
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
     * 2. **Caso:** La tabla de enlace está vacía.
     * ```php
     * $tabla = 'usuarios';
     * $tabla_enlace = '';
     *
     * $resultado = $miClase->init_renombre(tabla: $tabla, tabla_enlace: $tabla_enlace);
     *
     * print_r($resultado);
     * // Resultado esperado:
     * // Array
     * // (
     * //     [error] => 1
     * //     [mensaje] => Error $tabla_enlace no puede venir vacia
     * //     [data] =>
     * // )
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$tabla`**:
     *   - Debe ser un nombre válido de tabla como cadena.
     *   - No debe estar vacía o contener solo espacios en blanco.
     *   - Ejemplo válido: `'usuarios'`.
     *   - Ejemplo inválido: `''`.
     *
     * - **`$tabla_enlace`**:
     *   - Debe ser un nombre válido de tabla de enlace como cadena.
     *   - No debe estar vacía o contener solo espacios en blanco.
     *   - Ejemplo válido: `'roles'`.
     *   - Ejemplo inválido: `''`.
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Un objeto `stdClass` con los nombres de las tablas y sus clases ajustadas.
     *
     * - **Error**:
     *   Retorna un array con detalles del error si alguno de los parámetros está vacío o si ocurre un error
     *   al ajustar los nombres de los modelos.
     *
     * ### Notas adicionales:
     *
     * - La función utiliza internamente `ajusta_name_models` para ajustar los nombres de las tablas y las clases.
     * - Asegura que los nombres de las tablas no sean cadenas vacías o valores no válidos.
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
     * REG
     * Genera las tablas completas con sus respectivos alias y relaciones JOIN para una consulta SQL.
     *
     * @param array $columnas_join Arreglo que define las relaciones de las tablas (JOINs).
     *                             - Clave: nombre de la tabla base.
     *                             - Valor:
     *                               - Cadena: nombre de la tabla de enlace.
     *                               - Arreglo: detalles específicos para construir el JOIN.
     * @param string $tabla Nombre de la tabla principal que se incluirá en la consulta SQL.
     *                      - La tabla debe ser una cadena no vacía.
     *
     * @return array|string Devuelve una cadena con las tablas y sus relaciones JOIN generadas,
     *                      o un array de error en caso de fallo.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * // Caso con $columnas_join como detalles específicos
     * $columnas_join = [
     *     'usuarios' => [
     *         'tabla_base' => 'usuarios',
     *         'tabla_enlace' => 'roles',
     *         'campo_tabla_base_id' => 'id',
     *         'campo_renombrado' => 'roles_id',
     *     ],
     *     'roles' => 'permisos'
     * ];
     * $tabla = 'usuarios';
     *
     * $resultado = $this->obten_tablas_completas($columnas_join, $tabla);
     * echo $resultado;
     * // Resultado esperado:
     * // 'usuarios AS usuarios LEFT JOIN roles ON usuarios.id = roles.roles_id LEFT JOIN permisos ON roles.id = permisos.roles_id'
     * ```
     *
     * ### Proceso de la función:
     * 1. **Normalización del nombre de la tabla:**
     *    - Elimina el prefijo `models\` del nombre de la tabla.
     *    - Valida que `$tabla` no esté vacía.
     * 2. **Inicialización de la cadena de tablas:**
     *    - Comienza con el nombre de la tabla principal y su alias (`tabla AS tabla`).
     * 3. **Ajuste de tablas con JOINs:**
     *    - Llama a `ajusta_tablas` para generar los JOINs basados en `$columnas_join`.
     * 4. **Validación de errores:**
     *    - Verifica si `ajusta_tablas` produce errores y los maneja adecuadamente.
     * 5. **Retorno del resultado:**
     *    - Devuelve la cadena con las tablas y sus relaciones JOIN generadas.
     *
     * ### Ejemplo de errores:
     * **Error por tabla vacía:**
     * ```php
     * $columnas_join = [
     *     'usuarios' => [
     *         'tabla_base' => 'usuarios',
     *         'tabla_enlace' => 'roles',
     *         'campo_tabla_base_id' => 'id',
     *         'campo_renombrado' => 'roles_id',
     *     ]
     * ];
     * $tabla = '';
     *
     * $resultado = $this->obten_tablas_completas($columnas_join, $tabla);
     * print_r($resultado);
     * // Resultado esperado:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'La tabla no puede ir vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * **Error por `$columnas_join` mal estructurado:**
     * ```php
     * $columnas_join = [
     *     'usuarios' => [
     *         // Falta la clave 'tabla_base'
     *         'tabla_enlace' => 'roles',
     *         'campo_tabla_base_id' => 'id',
     *         'campo_renombrado' => 'roles_id',
     *     ]
     * ];
     * $tabla = 'usuarios';
     *
     * $resultado = $this->obten_tablas_completas($columnas_join, $tabla);
     * print_r($resultado);
     * // Resultado esperado:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error al generar data join',
     * //     'data' => [...]
     * // ]
     * ```
     *
     * ### Casos de uso:
     * - Generación de consultas SQL dinámicas con múltiples tablas y relaciones.
     * - Uso en sistemas que requieren consultas complejas con combinaciones (JOINs).
     *
     * ### Consideraciones:
     * - El arreglo `$columnas_join` debe estar correctamente estructurado.
     * - La función depende de `ajusta_tablas` para construir las relaciones entre tablas.
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
     * REG
     * Genera una cláusula SQL de tipo JOIN, ya sea básica o renombrada, para establecer relaciones entre tablas.
     *
     * @param string $campo_renombrado Nombre del campo a utilizar como clave renombrada en la relación (ejemplo: `id_usuario`).
     * @param string $campo_tabla_base_id Nombre del campo de ID en la tabla base. Si está vacío, se utiliza `.id` por defecto.
     * @param string $renombrada Alias que se asignará a la tabla relacionada. Si está vacío, no se aplica un alias.
     * @param string $tabla Nombre de la tabla base para la relación.
     * @param string $tabla_enlace Nombre de la tabla de enlace con la que se relaciona la tabla base.
     *
     * @return array|string Devuelve la cláusula SQL generada como una cadena o un array con detalles del error en caso de fallos.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * // Caso 1: Generar un JOIN con tabla renombrada
     * $campo_renombrado = 'id_usuario';
     * $campo_tabla_base_id = 'id';
     * $renombrada = 'u';
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles';
     *
     * $resultado = $this->sql_join(
     *     campo_renombrado: $campo_renombrado,
     *     campo_tabla_base_id: $campo_tabla_base_id,
     *     renombrada: $renombrada,
     *     tabla: $tabla,
     *     tabla_enlace: $tabla_enlace
     * );
     *
     * // Resultado esperado:
     * // " LEFT JOIN usuarios AS u ON u.id = roles.id_usuario"
     *
     * // Caso 2: Generar un JOIN sin alias renombrado
     * $campo_renombrado = '';
     * $campo_tabla_base_id = '';
     * $renombrada = '';
     * $tabla = 'productos';
     * $tabla_enlace = 'categorias';
     *
     * $resultado = $this->sql_join(
     *     campo_renombrado: $campo_renombrado,
     *     campo_tabla_base_id: $campo_tabla_base_id,
     *     renombrada: $renombrada,
     *     tabla: $tabla,
     *     tabla_enlace: $tabla_enlace
     * );
     *
     * // Resultado esperado:
     * // " LEFT JOIN productos AS productos ON productos.id = categorias.productos_id"
     * ```
     *
     * ### Validaciones realizadas:
     * - La función verifica que `$tabla` y `$tabla_enlace` no estén vacíos.
     * - Si `$renombrada` no está vacío, se llama a la función `genera_join_renombrado` para construir el JOIN con alias.
     * - Si `$renombrada` está vacío, se genera un JOIN básico utilizando el nombre de la tabla directamente.
     *
     * ### Detalles de la generación:
     * - Utiliza el tipo de JOIN `LEFT` de forma predeterminada.
     * - Si se proporciona un alias renombrado (`$renombrada`), se genera una cláusula JOIN con alias.
     * - En caso contrario, se genera un JOIN sin alias, utilizando el nombre de la tabla base y la tabla de enlace.
     *
     * ### Ejemplo de integración en un sistema:
     * ```php
     * class GeneradorSQL {
     *     public function genera_query() {
     *         $sql = $this->sql_join(
     *             campo_renombrado: 'id_usuario',
     *             campo_tabla_base_id: 'id',
     *             renombrada: 'u',
     *             tabla: 'usuarios',
     *             tabla_enlace: 'roles'
     *         );
     *         echo $sql;
     *         // Resultado: " LEFT JOIN usuarios AS u ON u.id = roles.id_usuario"
     *     }
     * }
     * ```
     *
     * ### Posibles errores y sus causas:
     * - **Error `$tabla` vacía:** Si `$tabla` está vacía, la función devuelve un mensaje indicando la ausencia de este parámetro.
     * - **Error `$tabla_enlace` vacía:** Si `$tabla_enlace` está vacía, se notifica el error con un mensaje descriptivo.
     * - **Error en generación de SQL:** Si ocurre un error en la generación del SQL renombrado con `genera_join_renombrado`, se devuelve el error correspondiente.
     *
     * ### Consideraciones:
     * - Esta función es útil para generar dinámicamente relaciones entre tablas, tanto básicas como con alias personalizados.
     * - Facilita la creación de consultas SQL complejas con validación previa de los parámetros.
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
     * REG
     * Genera y agrega una cláusula SQL JOIN basada en la estructura proporcionada en `$tabla_join` a la cadena `$tablas`.
     *
     * @param array $tabla_join Array que contiene la información necesaria para construir el JOIN.
     *                          Debe incluir las claves:
     *                          - `tabla_base`: Nombre de la tabla base.
     *                          - `tabla_enlace`: Nombre de la tabla de enlace.
     *                          Opcionalmente puede incluir:
     *                          - `tabla_renombrada`: Alias para la tabla base en el SQL.
     *                          - `campo_tabla_base_id`: Campo en la tabla base que se utiliza en la relación.
     *                          - `campo_renombrado`: Campo renombrado utilizado en la relación.
     * @param string $tablas Cadena que contiene las cláusulas SQL JOIN acumuladas. Se agregará el nuevo JOIN generado.
     *
     * @return array|string Retorna la cadena `$tablas` con el JOIN agregado en caso de éxito,
     *                      o un array con el detalle del error si ocurre algún problema.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $tabla_join = [
     *     'tabla_base' => 'usuarios',
     *     'tabla_enlace' => 'roles',
     *     'tabla_renombrada' => 'u',
     *     'campo_tabla_base_id' => 'id',
     *     'campo_renombrado' => 'usuario_id'
     * ];
     *
     * $tablas = '';
     * $tablas = $this->tablas_join_base($tabla_join, $tablas);
     *
     * // Resultado esperado:
     * // " LEFT JOIN usuarios AS u ON u.id = roles.usuario_id"
     * ```
     *
     * ### Validaciones realizadas:
     * 1. **Validación de `$tabla_join`:**
     *    - Verifica que el array `$tabla_join` contenga las claves requeridas `tabla_base` y `tabla_enlace`.
     *    - Si faltan claves o los valores no son válidos, retorna un error.
     * 2. **Generación del JOIN:**
     *    - Llama a `data_para_join` para construir la cláusula SQL JOIN con los datos proporcionados.
     * 3. **Acumulación de la cláusula JOIN:**
     *    - Agrega el resultado del JOIN generado a la cadena `$tablas`.
     *
     * ### Detalles de la implementación:
     * - **Validación de claves requeridas:** La función utiliza `valida_existencia_keys` para asegurarse de que las claves necesarias están presentes en `$tabla_join`.
     * - **Uso de `data_para_join`:** Llama a esta función para generar la cláusula JOIN basada en los datos validados.
     * - **Concatenación de JOIN:** El JOIN generado se agrega a `$tablas` para construir una cadena acumulativa de cláusulas JOIN.
     *
     * ### Ejemplo de integración en un sistema:
     * ```php
     * class GeneradorSQL {
     *     public function genera_query() {
     *         $tabla_join = [
     *             'tabla_base' => 'productos',
     *             'tabla_enlace' => 'categorias',
     *             'tabla_renombrada' => 'p',
     *             'campo_tabla_base_id' => 'id',
     *             'campo_renombrado' => 'producto_id'
     *         ];
     *
     *         $tablas = '';
     *         $tablas = $this->tablas_join_base($tabla_join, $tablas);
     *         echo $tablas;
     *         // Resultado: " LEFT JOIN productos AS p ON p.id = categorias.producto_id"
     *     }
     * }
     * ```
     *
     * ### Posibles errores y sus causas:
     * - **Faltan claves en `$tabla_join`:** Si no están presentes las claves `tabla_base` o `tabla_enlace`, se genera un error.
     * - **Error en `data_para_join`:** Si no puede generarse correctamente el JOIN debido a datos inconsistentes o inválidos.
     *
     * ### Consideraciones:
     * - Asegúrate de que `$tabla_join` esté correctamente estructurado para evitar errores.
     * - Útil para construir dinámicamente relaciones SQL JOIN entre tablas con validaciones estrictas.
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
     * REG
     * Genera y agrega un SQL JOIN entre dos tablas a una cadena de tablas de consulta SQL.
     *
     * @param string $key Nombre de la tabla base del JOIN.
     *                    - Debe ser una cadena no vacía.
     *                    - No puede ser un número.
     * @param string $tabla_join Nombre de la tabla que se unirá en el JOIN.
     *                           - Debe ser una cadena no vacía.
     *                           - No puede ser un número.
     * @param string $tablas Cadena acumulativa que contiene los JOINs generados previamente.
     *
     * @return array|string Devuelve la cadena acumulativa con el nuevo JOIN añadido, o un array de error si falla alguna validación.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $key = 'usuarios';
     * $tabla_join = 'roles';
     * $tablas = '';
     *
     * $resultado = $this->tablas_join_esp($key, $tabla_join, $tablas);
     *
     * if (is_string($resultado)) {
     *     echo 'JOINs generados: ' . $resultado;
     * } else {
     *     print_r($resultado); // Mostrará el detalle del error si ocurre.
     * }
     * // Resultado esperado:
     * // JOINs generados: ' LEFT JOIN roles ON usuarios.id = roles.usuarios_id'
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación de parámetros (`key` y `tabla_join`):**
     *    - Se asegura de que ambos parámetros sean cadenas no vacías y no numéricas.
     *    - Utiliza la función `valida_tabla_join` para realizar estas validaciones.
     * 2. **Generación del JOIN:**
     *    - Llama a la función `data_para_join_esp` para construir el JOIN entre las tablas especificadas.
     * 3. **Acumulación del JOIN:**
     *    - Si el JOIN se genera correctamente, se concatena con la cadena `$tablas`.
     *
     * ### Ejemplo de errores:
     * **Error por `$key` vacío:**
     * ```php
     * $key = '';
     * $tabla_join = 'roles';
     * $tablas = '';
     *
     * $resultado = $this->tablas_join_esp($key, $tabla_join, $tablas);
     * print_r($resultado);
     * // Resultado esperado:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error key esta vacio',
     * //     'data' => ''
     * // ]
     * ```
     *
     * **Error por `$tabla_join` numérico:**
     * ```php
     * $key = 'usuarios';
     * $tabla_join = '123';
     * $tablas = '';
     *
     * $resultado = $this->tablas_join_esp($key, $tabla_join, $tablas);
     * print_r($resultado);
     * // Resultado esperado:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error el $tabla_join no puede ser un numero',
     * //     'data' => '123'
     * // ]
     * ```
     *
     * ### Casos de uso:
     * - Construcción dinámica de consultas SQL con múltiples JOINs.
     * - Validación robusta de los nombres de tablas antes de generar la consulta.
     *
     * ### Consideraciones:
     * - El parámetro `$tablas` debe contener previamente cualquier otro JOIN acumulado.
     * - Los nombres de las tablas deben coincidir con los definidos en la base de datos para evitar errores de sintaxis.
     *
     * ### Detalles de implementación:
     * - Llama a `valida_tabla_join` para garantizar la validez de los nombres de las tablas.
     * - Utiliza `data_para_join_esp` para obtener el JOIN generado dinámicamente.
     * - Concatena el resultado del JOIN con la cadena `$tablas`.
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
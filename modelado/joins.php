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
     * Ajusta y construye una cadena SQL de sentencias `JOIN` a partir de un conjunto de tablas base y relaciones.
     *
     * La función procesa un array de relaciones de tablas (`$tablas_join`) y genera una cadena completa de sentencias
     * `JOIN` que se concatenan con las tablas previamente definidas en `$tablas`.
     *
     * @param string $tablas Cadena de sentencias SQL previamente generadas para las tablas.
     *                       Puede ser una cadena vacía si no se tienen sentencias iniciales.
     *                       Ejemplo: `'INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id'`.
     * @param array $tablas_join Array asociativo que define las relaciones entre las tablas.
     *                           La clave del array es el nombre de la tabla base, y el valor es:
     *                           - Un array con los detalles de la relación.
     *                           - Una cadena con el nombre de la tabla a enlazar.
     *                           Ejemplo:
     *                           ```php
     *                           [
     *                               'usuarios' => [
     *                                   'tabla_base' => 'usuarios',
     *                                   'tabla_enlace' => 'roles'
     *                               ],
     *                               'roles' => 'permisos'
     *                           ]
     *                           ```
     *
     * @return array|string Devuelve la cadena completa de sentencias SQL `JOIN` ajustadas con las relaciones especificadas.
     *                      Si ocurre un error, devuelve un array con el mensaje y los detalles del problema.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $tablas = 'INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id';
     * $tablas_join = [
     *     'usuarios' => [
     *         'tabla_base' => 'usuarios',
     *         'tabla_enlace' => 'roles'
     *     ],
     *     'roles' => 'permisos'
     * ];
     *
     * $resultado = $this->ajusta_tablas(tablas: $tablas, tablas_join: $tablas_join);
     *
     * // Resultado esperado:
     * // 'INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id
     * //  LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id
     * //  LEFT JOIN roles AS roles ON roles.id = permisos.roles_id'
     * ```
     *
     * ### Casos de uso:
     *
     * 1. **Construcción desde cero**:
     *    ```php
     *    $tablas = '';
     *    $tablas_join = [
     *        'usuarios' => [
     *            'tabla_base' => 'usuarios',
     *            'tabla_enlace' => 'roles'
     *        ]
     *    ];
     *
     *    $resultado = $this->ajusta_tablas(tablas: $tablas, tablas_join: $tablas_join);
     *
     *    // Resultado esperado:
     *    // ' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     *    ```
     *
     * 2. **Concatenación con sentencias previas**:
     *    ```php
     *    $tablas = 'INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id';
     *    $tablas_join = [
     *        'usuarios' => [
     *            'tabla_base' => 'usuarios',
     *            'tabla_enlace' => 'roles'
     *        ]
     *    ];
     *
     *    $resultado = $this->ajusta_tablas(tablas: $tablas, tablas_join: $tablas_join);
     *
     *    // Resultado esperado:
     *    // 'INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id
     *    //  LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     *    ```
     *
     * ### Validaciones:
     *
     * - Se asegura que `$tablas_join` esté correctamente definido y que cada relación tenga los datos necesarios.
     * - Valida que las claves del array `$tablas_join` (nombres de tablas) no sean numéricas ni estén vacías.
     * - Gestiona errores mediante la función auxiliar `data_tabla_sql`.
     *
     * ### Casos de error:
     *
     * 1. **`$tablas_join` mal estructurado**:
     *    ```php
     *    $tablas = '';
     *    $tablas_join = [
     *        'usuarios' => 'roles', // Correcto.
     *        123 => 'permisos'     // Incorrecto (clave numérica).
     *    ];
     *
     *    $resultado = $this->ajusta_tablas(tablas: $tablas, tablas_join: $tablas_join);
     *    // Resultado esperado: array con mensaje de error "Error al validar $tabla_join".
     *    ```
     *
     * 2. **Errores en `data_tabla_sql`**:
     *    Si ocurre un error durante el procesamiento de una relación, el flujo se interrumpe y se retorna el error.
     *
     * ### Resultado esperado:
     * - Devuelve una cadena concatenada de sentencias SQL `JOIN`.
     * - Si ocurre un error, devuelve un array con el mensaje descriptivo y los datos del problema.
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
     * Genera los datos necesarios para renombrar y establecer las relaciones entre tablas en una consulta SQL.
     *
     * Esta función construye los componentes de un `JOIN` en una consulta SQL, incluyendo el tipo de `JOIN`,
     * las condiciones de unión y el alias para la tabla renombrada. Valida los datos de entrada antes de generar el resultado.
     *
     * @param string $id_renombrada Identificador renombrado que se usará en la condición del `JOIN`.
     *                              Usualmente incluye el prefijo de la tabla seguido de un identificador (`.id`).
     * @param stdClass $init Objeto que contiene las propiedades necesarias para construir los datos de la relación.
     *                       Debe incluir las claves `tabla` y `tabla_enlace`.
     * @param string $join Tipo de `JOIN` a utilizar (por ejemplo: `INNER`, `LEFT`, `RIGHT`).
     * @param string $renombrada Alias para la tabla renombrada que se usará en la consulta SQL.
     *
     * @return stdClass|array Retorna un objeto con las siguientes propiedades en caso de éxito:
     *                        - `join_tabla`: Sentencia del `JOIN` con el tipo y la tabla base.
     *                        - `on_join`: Condición para la unión entre tablas.
     *                        - `asignacion_tabla`: Alias para la tabla renombrada.
     *                        Si ocurre un error, retorna un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $id_renombrada = '.id';
     * $init = new stdClass();
     * $init->tabla = 'usuarios';
     * $init->tabla_enlace = 'roles.usuario_id';
     * $join = 'INNER';
     * $renombrada = 'usuarios_renombrados';
     *
     * $resultado = $this->data_for_rename(
     *     id_renombrada: $id_renombrada,
     *     init: $init,
     *     join: $join,
     *     renombrada: $renombrada
     * );
     *
     * // Resultado esperado:
     * // $resultado->join_tabla => 'INNER JOIN usuarios'
     * // $resultado->on_join => 'usuarios_renombrados.id = roles.usuario_id'
     * // $resultado->asignacion_tabla => 'INNER JOIN usuarios AS usuarios_renombrados'
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$id_renombrada`**:
     *   Representa el identificador renombrado que se usará en la condición `ON` del `JOIN`.
     *   Ejemplo: `'.id'`.
     *
     * - **`$init`**:
     *   Objeto que debe incluir las claves:
     *     - `tabla`: Nombre de la tabla principal. Ejemplo: `'usuarios'`.
     *     - `tabla_enlace`: Campo que define la relación con la tabla principal. Ejemplo: `'roles.usuario_id'`.
     *
     * - **`$join`**:
     *   Tipo de unión a utilizar en la consulta SQL. Debe ser uno de los valores válidos como `INNER`, `LEFT` o `RIGHT`.
     *   Ejemplo: `'LEFT'`.
     *
     * - **`$renombrada`**:
     *   Alias que se asignará a la tabla principal en la consulta SQL.
     *   Ejemplo: `'usuarios_renombrados'`.
     *
     * ### Resultado esperado:
     *
     * - **`join_tabla`**: Sentencia que indica el tipo de `JOIN` y la tabla a unirse.
     *   Ejemplo: `'INNER JOIN usuarios'`.
     *
     * - **`on_join`**: Condición de unión para el `JOIN`.
     *   Ejemplo: `'usuarios_renombrados.id = roles.usuario_id'`.
     *
     * - **`asignacion_tabla`**: Sentencia del `JOIN` con el alias asignado a la tabla.
     *   Ejemplo: `'INNER JOIN usuarios AS usuarios_renombrados'`.
     *
     * ### Casos de error:
     *
     * - Si el parámetro `$init` no incluye las claves necesarias (`tabla`, `tabla_enlace`), se devuelve un array con detalles del error.
     * - Si algún parámetro está vacío, se genera un mensaje de error indicando el problema.
     *
     * ### Notas adicionales:
     *
     * - Esta función está diseñada para facilitar la creación dinámica de `JOIN` en consultas SQL.
     * - Asegura que los datos requeridos para la consulta estén correctamente formateados y validados.
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
     * Genera la cláusula SQL necesaria para realizar un `JOIN` entre tablas con validaciones previas.
     *
     * Esta función se encarga de validar los datos de configuración necesarios para un `JOIN`, construir la
     * estructura requerida y finalmente generar la sentencia SQL del `JOIN` con los datos proporcionados.
     *
     * @param array $tabla_join Configuración de las tablas que participarán en el `JOIN`.
     *                          Este array debe contener las claves:
     *                          - `'tabla_base'`: Nombre de la tabla principal. Ejemplo: `'usuarios'`.
     *                          - `'tabla_enlace'`: Nombre de la tabla relacionada (enlace). Ejemplo: `'roles'`.
     *                          - `'tabla_renombrada'` (opcional): Alias para la tabla base. Ejemplo: `'usuarios_renombrados'`.
     *                          - `'campo_tabla_base_id'` (opcional): Campo de la tabla base utilizado en la cláusula `ON`.
     *                            Ejemplo: `'usuario_id'`.
     *                          - `'campo_renombrado'` (opcional): Campo clave de la tabla de enlace para el `JOIN`.
     *                            Ejemplo: `'id_usuario'`.
     *
     * @return array|string La cláusula `JOIN` generada en caso de éxito. En caso de error, devuelve un array con los
     *                      detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $tabla_join = array(
     *     'tabla_base' => 'usuarios',
     *     'tabla_enlace' => 'roles',
     *     'tabla_renombrada' => 'usuarios_renombrados',
     *     'campo_tabla_base_id' => 'usuario_id',
     *     'campo_renombrado' => 'id_usuario'
     * );
     *
     * $resultado = $this->data_para_join(tabla_join: $tabla_join);
     *
     * // Resultado esperado:
     * // ' LEFT JOIN usuarios AS usuarios_renombrados ON usuarios_renombrados.usuario_id = roles.id_usuario'
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$tabla_join`**:
     *   Array que contiene las configuraciones necesarias para construir el `JOIN`.
     *   Ejemplo:
     *   ```php
     *   array(
     *       'tabla_base' => 'usuarios',
     *       'tabla_enlace' => 'roles',
     *       'tabla_renombrada' => 'usuarios_renombrados',
     *       'campo_tabla_base_id' => 'usuario_id',
     *       'campo_renombrado' => 'id_usuario'
     *   );
     *   ```
     *
     * ### Casos de error:
     *
     * - Si el array `$tabla_join` no contiene las claves `'tabla_base'` o `'tabla_enlace'`, se genera un error indicando que
     *   las claves son obligatorias.
     * - Si ocurre un problema al validar o generar la cláusula `JOIN`, se devuelve un array con los detalles del error.
     *
     * ### Resultado esperado:
     *
     * - Genera una cláusula SQL `JOIN` en formato correcto.
     *   Ejemplo:
     *   `' LEFT JOIN usuarios AS usuarios_renombrados ON usuarios_renombrados.usuario_id = roles.id_usuario'`.
     *
     * ### Notas adicionales:
     *
     * - Esta función utiliza validaciones estrictas para garantizar que la estructura de entrada sea correcta y evitar errores.
     * - Es útil para construir consultas SQL dinámicas en aplicaciones que manejan relaciones complejas entre tablas.
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
     * Genera los datos necesarios para construir una sentencia SQL `JOIN` específica.
     *
     * Esta función valida las tablas proporcionadas (`key` y `tabla_join`) y genera una sentencia `JOIN`
     * utilizando los parámetros especificados.
     *
     * @param string $key Clave principal o alias de la tabla base utilizada en el `JOIN`.
     *                    Debe ser una cadena no vacía y no numérica.
     *                    Ejemplo: `'usuarios'`.
     * @param string $tabla_join Nombre de la tabla de enlace utilizada en el `JOIN`.
     *                           Debe ser una cadena no vacía y no numérica.
     *                           Ejemplo: `'roles'`.
     *
     * @return array|string Devuelve un string con la sentencia `JOIN` generada si no hay errores.
     *                      En caso de error, devuelve un array con detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $key = 'usuarios';
     * $tabla_join = 'roles';
     *
     * $resultado = $this->data_para_join_esp(key: $key, tabla_join: $tabla_join);
     *
     * // Resultado esperado:
     * // ' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$key`**:
     *   Representa el alias o nombre de la tabla base utilizada en el `JOIN`.
     *   Requisitos:
     *   - Debe ser una cadena no vacía.
     *   - No puede ser numérico.
     *   Ejemplo válido:
     *   ```php
     *   'usuarios'
     *   ```
     *   Ejemplo inválido:
     *   ```php
     *   ''  // Está vacío.
     *   123 // Es numérico.
     *   ```
     *
     * - **`$tabla_join`**:
     *   Es el nombre de la tabla de enlace que participa en el `JOIN`.
     *   Requisitos:
     *   - Debe ser una cadena no vacía.
     *   - No puede ser numérico.
     *   Ejemplo válido:
     *   ```php
     *   'roles'
     *   ```
     *   Ejemplo inválido:
     *   ```php
     *   ''   // Está vacío.
     *   456  // Es numérico.
     *   ```
     *
     * ### Casos de uso exitoso:
     *
     * 1. Generar un `JOIN` entre las tablas `'usuarios'` y `'roles'`:
     *    ```php
     *    $key = 'usuarios';
     *    $tabla_join = 'roles';
     *
     *    $resultado = $this->data_para_join_esp(key: $key, tabla_join: $tabla_join);
     *
     *    // Resultado esperado:
     *    // ' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     *    ```
     *
     * ### Casos de error:
     *
     * 1. **`$key` está vacío**:
     *    ```php
     *    $resultado = $this->data_para_join_esp(key: '', tabla_join: 'roles');
     *    // Resultado esperado: array con mensaje de error "Error key esta vacio".
     *    ```
     *
     * 2. **`$tabla_join` está vacío**:
     *    ```php
     *    $resultado = $this->data_para_join_esp(key: 'usuarios', tabla_join: '');
     *    // Resultado esperado: array con mensaje de error "Error $tabla_join esta vacio".
     *    ```
     *
     * 3. **`$key` es numérico**:
     *    ```php
     *    $resultado = $this->data_para_join_esp(key: '123', tabla_join: 'roles');
     *    // Resultado esperado: array con mensaje de error "Error el key no puede ser un numero".
     *    ```
     *
     * 4. **Error al generar el `JOIN`**:
     *    Si ocurre un error en la construcción del `JOIN`, se devuelve un array con detalles del problema.
     *
     * ### Resultado esperado:
     * - Devuelve un string con la sentencia `JOIN` generada correctamente.
     * - Si ocurre un error, devuelve un array con los detalles y el mensaje del problema.
     *
     * ### Notas adicionales:
     * - La validación de las tablas se realiza a través de la función `valida_tabla_join`.
     * - La generación de la sentencia SQL `JOIN` utiliza `genera_join` internamente.
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
     * Genera y ajusta una cadena de sentencias SQL de tipo `JOIN` con base en la configuración de tablas proporcionadas.
     *
     * Esta función construye las relaciones necesarias entre tablas para una consulta SQL,
     * utilizando diferentes métodos según el tipo de `$tabla_join` (array o string).
     *
     * @param string $key Nombre o alias de la tabla base que servirá como referencia para la relación `JOIN`.
     *                    Debe ser una cadena no vacía.
     *                    Ejemplo: `'usuarios'`.
     * @param array|string $tabla_join Configuración de las tablas a relacionar.
     *                                 Puede ser:
     *                                 - Un **array** con la estructura de la relación.
     *                                 - Un **string** representando la tabla de enlace.
     *                                 Ejemplo array:
     *                                 ```php
     *                                 [
     *                                     'tabla_base' => 'usuarios',
     *                                     'tabla_enlace' => 'roles'
     *                                 ]
     *                                 ```
     *                                 Ejemplo string:
     *                                 ```php
     *                                 'roles'
     *                                 ```
     * @param string $tablas Cadena de tablas o relaciones ya generadas, a la que se agregarán las nuevas sentencias `JOIN`.
     *                       Puede estar vacía para comenzar desde cero.
     *                       Ejemplo: `'INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id'`.
     *
     * @return array|string Retorna la cadena actualizada de tablas con las relaciones `JOIN` añadidas.
     *                      En caso de error, retorna un array con los detalles del problema.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $key = 'usuarios';
     * $tabla_join = [
     *     'tabla_base' => 'usuarios',
     *     'tabla_enlace' => 'roles'
     * ];
     * $tablas = '';
     *
     * $resultado = $this->data_tabla_sql(key: $key, tabla_join: $tabla_join, tablas: $tablas);
     *
     * // Resultado esperado:
     * // ' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     * ```
     *
     * ### Parámetros:
     *
     * - **`$key`**:
     *   Nombre o alias de la tabla base para la relación `JOIN`.
     *   Ejemplo válido:
     *   ```php
     *   'usuarios'
     *   ```
     *   Ejemplo inválido:
     *   ```php
     *   '' // Está vacío.
     *   ```
     *
     * - **`$tabla_join`**:
     *   Relación de tablas o nombre de la tabla de enlace.
     *   Puede ser un **array** o un **string**.
     *   Ejemplo de array válido:
     *   ```php
     *   [
     *       'tabla_base' => 'usuarios',
     *       'tabla_enlace' => 'roles'
     *   ]
     *   ```
     *   Ejemplo de string válido:
     *   ```php
     *   'roles'
     *   ```
     *
     * - **`$tablas`**:
     *   Cadena de tablas o relaciones SQL ya generadas.
     *   Se concatenará con las nuevas relaciones `JOIN`.
     *   Ejemplo inicial:
     *   ```php
     *   ''
     *   ```
     *   Ejemplo con datos:
     *   ```php
     *   'INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id'
     *   ```
     *
     * ### Casos de uso exitoso:
     *
     * 1. Generar un `JOIN` con un array de relación:
     *    ```php
     *    $key = 'usuarios';
     *    $tabla_join = [
     *        'tabla_base' => 'usuarios',
     *        'tabla_enlace' => 'roles'
     *    ];
     *    $tablas = '';
     *
     *    $resultado = $this->data_tabla_sql(key: $key, tabla_join: $tabla_join, tablas: $tablas);
     *
     *    // Resultado esperado:
     *    // ' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     *    ```
     *
     * 2. Generar un `JOIN` con una tabla de enlace (string):
     *    ```php
     *    $key = 'usuarios';
     *    $tabla_join = 'roles';
     *    $tablas = '';
     *
     *    $resultado = $this->data_tabla_sql(key: $key, tabla_join: $tabla_join, tablas: $tablas);
     *
     *    // Resultado esperado:
     *    // ' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     *    ```
     *
     * 3. Concatenar a una cadena existente:
     *    ```php
     *    $key = 'usuarios';
     *    $tabla_join = 'roles';
     *    $tablas = 'INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id';
     *
     *    $resultado = $this->data_tabla_sql(key: $key, tabla_join: $tabla_join, tablas: $tablas);
     *
     *    // Resultado esperado:
     *    // 'INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id
     *    //  LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     *    ```
     *
     * ### Casos de error:
     *
     * 1. `$key` está vacío:
     *    ```php
     *    $resultado = $this->data_tabla_sql(key: '', tabla_join: 'roles', tablas: '');
     *    // Resultado esperado: array con mensaje de error "Error key esta vacio".
     *    ```
     *
     * 2. `$tabla_join` no es un array ni un string:
     *    ```php
     *    $resultado = $this->data_tabla_sql(key: 'usuarios', tabla_join: 123, tablas: '');
     *    // Resultado esperado: array con mensaje de error "Error al validar $tabla_join".
     *    ```
     *
     * 3. Error interno en generación del `JOIN`:
     *    Si ocurre un problema en `tablas_join_base` o `tablas_join_esp`, se retorna el error correspondiente.
     *
     * ### Resultado esperado:
     * - Devuelve una cadena concatenada de sentencias SQL `JOIN`.
     * - Si ocurre un error, retorna un array descriptivo del problema.
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
     * Genera un identificador renombrado basado en el campo proporcionado.
     *
     * Esta función toma un nombre de campo y retorna una cadena con el identificador
     * ajustado. Si se proporciona un nombre de campo no vacío, se usa como parte del
     * identificador renombrado; de lo contrario, se usa el valor predeterminado `.id`.
     *
     * @param string $campo_tabla_base_id Nombre del campo de la tabla base utilizado para
     *                                    construir el identificador renombrado. Si está vacío,
     *                                    se utiliza el valor `.id` por defecto.
     *
     * @return string Retorna el identificador renombrado, prefijado por un punto (`.`).
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * // Caso: Se proporciona un campo no vacío.
     * $campo_tabla_base_id = 'usuario_id';
     * $resultado = $this->id_renombrada($campo_tabla_base_id);
     * echo $resultado;
     * // Resultado esperado:
     * // .usuario_id
     *
     * // Caso: Se proporciona un campo vacío.
     * $campo_tabla_base_id = '';
     * $resultado = $this->id_renombrada($campo_tabla_base_id);
     * echo $resultado;
     * // Resultado esperado:
     * // .id
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$campo_tabla_base_id`**:
     *   Este parámetro representa el nombre del campo que será utilizado para formar
     *   el identificador renombrado. Si está vacío, el valor predeterminado `.id` será usado.
     *   Ejemplo válido: `'usuario_id'`.
     *   Ejemplo vacío: `''`.
     *
     * ### Resultado esperado:
     *
     * - Si se proporciona un nombre de campo no vacío (`$campo_tabla_base_id`), el retorno será una cadena
     *   con el formato `.{campo_tabla_base_id}`.
     *   Ejemplo: `.usuario_id`.
     *
     * - Si el parámetro está vacío, la función retorna el valor predeterminado `.id`.
     *
     * ### Notas adicionales:
     *
     * - Está diseñada para ser utilizada en la construcción de identificadores renombrados en consultas SQL.
     * - Es una función auxiliar que asegura consistencia en el formato del identificador.
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
     * Genera una cláusula SQL `JOIN` dinámica para combinar tablas en una consulta.
     *
     * Esta función construye una sentencia SQL `LEFT JOIN`, permitiendo el uso de alias para las tablas y
     * configuraciones personalizadas en los campos utilizados en la cláusula `ON`. Se asegura de que los
     * parámetros sean válidos antes de generar el SQL.
     *
     * @param string $tabla Nombre de la tabla principal que participa en el `JOIN`.
     *                      Ejemplo: `'usuarios'`.
     * @param string $tabla_enlace Nombre de la tabla relacionada que se une con la tabla principal.
     *                             Ejemplo: `'roles'`.
     * @param string $campo_renombrado (Opcional) Campo específico utilizado como clave en la cláusula `ON`.
     *                                  Por defecto está vacío. Ejemplo: `'id_usuario'`.
     * @param string $campo_tabla_base_id (Opcional) Campo de la tabla base que será referenciado en la condición `ON`.
     *                                     Por defecto se utiliza `'id'` si este parámetro está vacío.
     *                                     Ejemplo: `'usuario_id'`.
     * @param string $renombrada (Opcional) Alias para la tabla principal en la consulta. Si está vacío, no se aplica un alias.
     *                            Ejemplo: `'usuarios_renombrados'`.
     *
     * @return array|string Retorna la cláusula `LEFT JOIN` como una cadena en caso de éxito. En caso de error,
     *                      devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso con alias para la tabla:
     *
     * ```php
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles';
     * $campo_renombrado = 'id_usuario';
     * $campo_tabla_base_id = 'usuario_id';
     * $renombrada = 'usuarios_renombrados';
     *
     * $resultado = $this->genera_join(
     *     tabla: $tabla,
     *     tabla_enlace: $tabla_enlace,
     *     campo_renombrado: $campo_renombrado,
     *     campo_tabla_base_id: $campo_tabla_base_id,
     *     renombrada: $renombrada
     * );
     *
     * // Resultado esperado:
     * // ' LEFT JOIN usuarios AS usuarios_renombrados ON usuarios_renombrados.usuario_id = roles.id_usuario'
     * ```
     *
     * ### Ejemplo de uso exitoso sin alias:
     *
     * ```php
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles';
     *
     * $resultado = $this->genera_join(
     *     tabla: $tabla,
     *     tabla_enlace: $tabla_enlace
     * );
     *
     * // Resultado esperado:
     * // ' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$tabla`**:
     *   Nombre de la tabla principal en la consulta. No debe estar vacío.
     *   Ejemplo: `'usuarios'`.
     *
     * - **`$tabla_enlace`**:
     *   Nombre de la tabla relacionada (enlace) que será unida con la tabla principal.
     *   Ejemplo: `'roles'`.
     *
     * - **`$campo_renombrado`**:
     *   Campo específico que será utilizado como clave en la cláusula `ON`. Es opcional.
     *   Ejemplo: `'id_usuario'`.
     *
     * - **`$campo_tabla_base_id`**:
     *   Campo de la tabla base que se referenciará en la condición `ON`. Por defecto se utiliza `'id'`.
     *   Ejemplo: `'usuario_id'`.
     *
     * - **`$renombrada`**:
     *   Alias de la tabla principal para la consulta. Si está vacío, no se aplica un alias. Es opcional.
     *   Ejemplo: `'usuarios_renombrados'`.
     *
     * ### Casos de error:
     *
     * - Si `$tabla` o `$tabla_enlace` están vacíos, se genera un error indicando que son parámetros obligatorios.
     * - Si ocurre un problema al generar la cláusula SQL, se devuelve un array con los detalles del error.
     *
     * ### Resultado esperado:
     *
     * - Genera una cláusula `LEFT JOIN` con las condiciones `ON` especificadas.
     * - Ejemplo con alias:
     *   `' LEFT JOIN usuarios AS usuarios_renombrados ON usuarios_renombrados.usuario_id = roles.id_usuario'`.
     * - Ejemplo sin alias:
     *   `' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'`.
     *
     * ### Notas adicionales:
     *
     * - Esta función es útil para construir consultas SQL dinámicas en aplicaciones con múltiples relaciones entre tablas.
     * - Garantiza validaciones estrictas para evitar errores comunes al construir sentencias SQL manualmente.
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
     * Genera una cláusula `JOIN` renombrada para consultas SQL.
     *
     * Esta función construye dinámicamente una sentencia SQL `JOIN` incluyendo el alias de tabla,
     * las condiciones de unión y el tipo de `JOIN` especificado. Valida todos los parámetros de entrada
     * para asegurar que la salida sea válida y funcional.
     *
     * @param string $campo_renombrado Campo renombrado que será parte de la condición `ON` del `JOIN`.
     *                                  Ejemplo: `'id_usuario'`.
     * @param string $campo_tabla_base_id Identificador de la tabla base utilizado en la condición `ON`.
     *                                    Puede ser una clave específica o `'id'`.
     * @param string $join Tipo de `JOIN` a generar. Puede ser `INNER`, `LEFT` o `RIGHT`.
     * @param string $renombrada Alias para la tabla renombrada. Este alias se utiliza en la consulta SQL.
     * @param string $tabla Nombre de la tabla base en la consulta.
     * @param string $tabla_enlace Nombre de la tabla relacionada (enlace) para la unión.
     *
     * @return array|string Devuelve la cláusula completa de `JOIN` como una cadena en caso de éxito.
     *                      Si ocurre un error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $campo_renombrado = 'id_usuario';
     * $campo_tabla_base_id = 'usuario_id';
     * $join = 'LEFT';
     * $renombrada = 'usuarios_renombrados';
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles';
     *
     * $resultado = $this->genera_join_renombrado(
     *     campo_renombrado: $campo_renombrado,
     *     campo_tabla_base_id: $campo_tabla_base_id,
     *     join: $join,
     *     renombrada: $renombrada,
     *     tabla: $tabla,
     *     tabla_enlace: $tabla_enlace
     * );
     *
     * // Resultado esperado:
     * // ' LEFT JOIN usuarios AS usuarios_renombrados ON usuarios_renombrados.usuario_id = roles.id_usuario'
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$campo_renombrado`**:
     *   Campo que se usará como clave en la condición `ON`.
     *   Ejemplo: `'id_usuario'`.
     *
     * - **`$campo_tabla_base_id`**:
     *   Campo de la tabla base que se usará para enlazar en la condición `ON`.
     *   Ejemplo: `'usuario_id'`. Si está vacío, se usará `.id` por defecto.
     *
     * - **`$join`**:
     *   Tipo de unión SQL. Puede ser:
     *     - `'INNER'`: Unión estricta.
     *     - `'LEFT'`: Unión que incluye todos los registros de la tabla izquierda.
     *     - `'RIGHT'`: Unión que incluye todos los registros de la tabla derecha.
     *
     * - **`$renombrada`**:
     *   Alias que se asignará a la tabla base. Este alias se utiliza en la consulta para simplificar referencias.
     *   Ejemplo: `'usuarios_renombrados'`.
     *
     * - **`$tabla`**:
     *   Nombre de la tabla base en la consulta SQL.
     *   Ejemplo: `'usuarios'`.
     *
     * - **`$tabla_enlace`**:
     *   Nombre de la tabla relacionada que se usará para enlazar con la tabla base.
     *   Ejemplo: `'roles'`.
     *
     * ### Resultado esperado:
     *
     * - Cláusula `JOIN` completa:
     *   Ejemplo: `' LEFT JOIN usuarios AS usuarios_renombrados ON usuarios_renombrados.usuario_id = roles.id_usuario'`.
     *
     * ### Casos de error:
     *
     * - Si alguno de los parámetros obligatorios está vacío, la función devolverá un array con los detalles del error.
     * - Si el tipo de `JOIN` no es válido (`INNER`, `LEFT`, `RIGHT`), se generará un error.
     * - Si las claves necesarias en `$init` no están presentes (`tabla`, `tabla_enlace`), se generará un error.
     *
     * ### Notas adicionales:
     *
     * - Esta función facilita la generación de consultas SQL complejas con múltiples uniones, asegurando consistencia y validación.
     * - Asegúrate de que las tablas y campos utilizados existan en la base de datos para evitar errores en la ejecución de la consulta.
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
     * Inicializa los nombres y clases de modelo para las tablas base y de enlace, ajustando su estructura
     * y asegurando que ambos nombres sean válidos.
     *
     * @param string $tabla Nombre de la tabla base que se desea procesar. No puede estar vacío.
     * @param string $tabla_enlace Nombre de la tabla de enlace asociada. No puede estar vacío.
     *
     * @return stdClass|array Retorna un objeto `stdClass` con las siguientes propiedades en caso de éxito:
     *                        - `tabla`: Nombre ajustado de la tabla base.
     *                        - `class`: Nombre completo de la clase modelo para la tabla base.
     *                        - `tabla_enlace`: Nombre ajustado de la tabla de enlace.
     *                        - `class_enlace`: Nombre completo de la clase modelo para la tabla de enlace.
     *                        En caso de error, retorna un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles_usuarios';
     *
     * $resultado = $miClase->init_renombre($tabla, $tabla_enlace);
     *
     * print_r($resultado);
     * // Resultado esperado:
     * // stdClass Object
     * // (
     * //     [tabla] => usuarios
     * //     [class] => models\usuarios
     * //     [tabla_enlace] => roles_usuarios
     * //     [class_enlace] => models\roles_usuarios
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
     * $resultado = $miClase->init_renombre($tabla, $tabla_enlace);
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
     *   Retorna un objeto `stdClass` con las claves:
     *   - `tabla`: Nombre ajustado de la tabla base.
     *   - `class`: Nombre completo de la clase modelo para la tabla base.
     *   - `tabla_enlace`: Nombre ajustado de la tabla de enlace.
     *   - `class_enlace`: Nombre completo de la clase modelo para la tabla de enlace.
     *
     * - **Error**:
     *   Si alguno de los parámetros está vacío, retorna un array con los detalles del error, incluyendo
     *   un mensaje descriptivo y los datos proporcionados.
     *
     * ### Notas adicionales:
     *
     * - La función utiliza `ajusta_name_models` para procesar los nombres de las tablas y generar los nombres de las clases de modelo.
     * - Está diseñada para asegurar que las tablas base y de enlace estén correctamente estructuradas antes de su uso en otros procesos.
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
     * Genera una cadena de tablas completas con alias y sentencias `JOIN` a partir de una tabla base y relaciones definidas.
     *
     * Esta función toma una tabla base y un conjunto de columnas relacionadas (joins) para construir una cadena SQL
     * que incluye la tabla base y las tablas relacionadas mediante sentencias `JOIN`.
     *
     * @param array $columnas_join Array asociativo que define las relaciones entre las tablas.
     *                             La clave es el nombre de la tabla base o relacionada, y el valor es:
     *                             - Un array con los detalles del join (relación).
     *                             - Una cadena con el nombre de la tabla relacionada.
     *                             Ejemplo:
     *                             ```php
     *                             [
     *                                 'usuarios' => [
     *                                     'tabla_base' => 'usuarios',
     *                                     'tabla_enlace' => 'roles'
     *                                 ],
     *                                 'roles' => 'permisos'
     *                             ]
     *                             ```
     * @param string $tabla Nombre de la tabla base para la consulta.
     *                      Se agrega como la tabla inicial con alias.
     *                      Ejemplo: `'usuarios'`.
     *
     * @return array|string Devuelve la cadena de tablas y relaciones formateadas para la consulta SQL.
     *                      Si ocurre un error, devuelve un array con el mensaje descriptivo del problema.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas_join = [
     *     'usuarios' => [
     *         'tabla_base' => 'usuarios',
     *         'tabla_enlace' => 'roles'
     *     ],
     *     'roles' => [
     *         'tabla_base' => 'roles',
     *         'tabla_enlace' => 'permisos'
     *     ]
     * ];
     * $tabla = 'usuarios';
     *
     * $resultado = $this->obten_tablas_completas(columnas_join: $columnas_join, tabla: $tabla);
     *
     * // Resultado esperado:
     * // 'usuarios AS usuarios
     * //  LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id
     * //  LEFT JOIN roles AS roles ON roles.id = permisos.roles_id'
     * ```
     *
     * ### Casos de uso:
     *
     * 1. **Generar tablas con múltiples relaciones**:
     *    ```php
     *    $columnas_join = [
     *        'usuarios' => [
     *            'tabla_base' => 'usuarios',
     *            'tabla_enlace' => 'roles'
     *        ],
     *        'roles' => [
     *            'tabla_base' => 'roles',
     *            'tabla_enlace' => 'permisos'
     *        ]
     *    ];
     *    $tabla = 'usuarios';
     *
     *    $resultado = $this->obten_tablas_completas(columnas_join: $columnas_join, tabla: $tabla);
     *    // Resultado esperado:
     *    // 'usuarios AS usuarios
     *    //  LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id
     *    //  LEFT JOIN roles AS roles ON roles.id = permisos.roles_id'
     *    ```
     *
     * 2. **Tabla sin relaciones**:
     *    ```php
     *    $columnas_join = [];
     *    $tabla = 'productos';
     *
     *    $resultado = $this->obten_tablas_completas(columnas_join: $columnas_join, tabla: $tabla);
     *    // Resultado esperado:
     *    // 'productos AS productos'
     *    ```
     *
     * ### Validaciones:
     *
     * - Se verifica que `$tabla` no esté vacía.
     * - Se asegura que `$tabla` esté correctamente formateada y no sea numérica.
     * - Se gestiona el ajuste de tablas y relaciones mediante la función `ajusta_tablas`.
     *
     * ### Casos de error:
     *
     * 1. **Tabla vacía**:
     *    ```php
     *    $columnas_join = [];
     *    $tabla = '';
     *
     *    $resultado = $this->obten_tablas_completas(columnas_join: $columnas_join, tabla: $tabla);
     *    // Resultado esperado: array con mensaje de error "La tabla no puede ir vacía".
     *    ```
     *
     * 2. **Error en las relaciones**:
     *    Si ocurre un error en `ajusta_tablas`, se devuelve un mensaje descriptivo con los detalles del problema.
     *
     * ### Resultado esperado:
     * - Devuelve una cadena SQL completa con tablas y relaciones (`JOIN`) para consultas complejas.
     * - Si hay errores, se devuelve un array con el mensaje y los datos relevantes.
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
     * Genera una cláusula SQL `JOIN` para la construcción de consultas con alias y condiciones de unión.
     *
     * Esta función permite crear una sentencia `LEFT JOIN` con la posibilidad de agregar un alias
     * (`renombrada`) para la tabla base. Valida los parámetros y genera el SQL correspondiente, ya sea
     * renombrado o con el nombre original de la tabla.
     *
     * @param string $campo_renombrado Campo que será utilizado como clave en la condición `ON` del `JOIN`.
     *                                  Ejemplo: `'id_usuario'`.
     * @param string $campo_tabla_base_id Campo de la tabla base que será referenciado en la condición `ON`.
     *                                     Por defecto se utiliza `'id'` si este parámetro está vacío.
     * @param string $renombrada Alias para renombrar la tabla base en la consulta. Si está vacío,
     *                           no se aplica un alias.
     * @param string $tabla Nombre de la tabla base que se utilizará en el `JOIN`.
     * @param string $tabla_enlace Nombre de la tabla relacionada (enlace) que se unirá con la tabla base.
     *
     * @return array|string Devuelve la cláusula completa de `JOIN` como una cadena en caso de éxito.
     *                      Si ocurre un error, retorna un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso con tabla renombrada:
     *
     * ```php
     * $campo_renombrado = 'id_usuario';
     * $campo_tabla_base_id = 'usuario_id';
     * $renombrada = 'usuarios_renombrados';
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
     * // ' LEFT JOIN usuarios AS usuarios_renombrados ON usuarios_renombrados.usuario_id = roles.id_usuario'
     * ```
     *
     * ### Ejemplo de uso exitoso sin renombrar la tabla:
     *
     * ```php
     * $campo_renombrado = 'id_usuario';
     * $campo_tabla_base_id = 'usuario_id';
     * $renombrada = ''; // No se aplica alias
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
     * // ' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$campo_renombrado`**:
     *   Especifica el campo utilizado como clave en la condición `ON`.
     *   Ejemplo: `'id_usuario'`.
     *
     * - **`$campo_tabla_base_id`**:
     *   Campo de la tabla base que será referenciado en la condición `ON`.
     *   Ejemplo: `'usuario_id'`. Si está vacío, se asume como `'id'`.
     *
     * - **`$renombrada`**:
     *   Alias de la tabla base para la consulta. Si está vacío, no se aplica un alias.
     *   Ejemplo: `'usuarios_renombrados'`.
     *
     * - **`$tabla`**:
     *   Nombre de la tabla base que participa en el `JOIN`.
     *   Ejemplo: `'usuarios'`.
     *
     * - **`$tabla_enlace`**:
     *   Nombre de la tabla relacionada (enlace) que se une con la tabla base.
     *   Ejemplo: `'roles'`.
     *
     * ### Casos de error:
     *
     * - Si `$tabla` o `$tabla_enlace` están vacíos, se genera un error indicando que estos parámetros son obligatorios.
     * - Si ocurre un problema durante la generación del `JOIN` renombrado, se devuelve un array con los detalles del error.
     *
     * ### Resultado esperado:
     *
     * - La función genera una cláusula `LEFT JOIN` con las condiciones `ON` especificadas.
     * - Ejemplo con alias:
     *   `' LEFT JOIN usuarios AS usuarios_renombrados ON usuarios_renombrados.usuario_id = roles.id_usuario'`.
     * - Ejemplo sin alias:
     *   `' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'`.
     *
     * ### Notas adicionales:
     *
     * - La función es flexible y soporta tanto consultas simples como aquellas que requieren alias para tablas.
     * - Es ideal para construir consultas SQL dinámicas y evitar errores manuales al escribir cláusulas `JOIN`.
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
     * Genera y agrega la cláusula SQL necesaria para un `JOIN` entre tablas a partir de los datos proporcionados.
     *
     * Esta función valida la configuración de las tablas involucradas en un `JOIN`, genera la estructura del `JOIN`
     * y la concatena con las tablas previamente procesadas en la consulta SQL.
     *
     * @param array $tabla_join Configuración de las tablas que participan en el `JOIN`. Este array debe contener:
     *                          - `'tabla_base'`: Nombre de la tabla principal. Ejemplo: `'usuarios'`.
     *                          - `'tabla_enlace'`: Nombre de la tabla relacionada. Ejemplo: `'roles'`.
     *                          - `'tabla_renombrada'` (opcional): Alias para la tabla principal. Ejemplo: `'usuarios_renombrados'`.
     *                          - `'campo_tabla_base_id'` (opcional): Campo de la tabla principal usado en la cláusula `ON`.
     *                            Ejemplo: `'usuario_id'`.
     *                          - `'campo_renombrado'` (opcional): Campo clave de la tabla de enlace para el `JOIN`.
     *                            Ejemplo: `'id_usuario'`.
     * @param string $tablas Una cadena que contiene las tablas ya procesadas en la consulta SQL. La cláusula generada
     *                       será concatenada a esta cadena.
     *
     * @return array|string Una cadena con las tablas actualizadas incluyendo el nuevo `JOIN`, o un array con los detalles
     *                      del error si ocurre alguna falla.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $tabla_join = array(
     *     'tabla_base' => 'usuarios',
     *     'tabla_enlace' => 'roles',
     *     'tabla_renombrada' => 'usuarios_renombrados',
     *     'campo_tabla_base_id' => 'usuario_id',
     *     'campo_renombrado' => 'id_usuario'
     * );
     * $tablas = '';
     *
     * $resultado = $this->tablas_join_base(tabla_join: $tabla_join, tablas: $tablas);
     *
     * // Resultado esperado:
     * // ' LEFT JOIN usuarios AS usuarios_renombrados ON usuarios_renombrados.usuario_id = roles.id_usuario'
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$tabla_join`**:
     *   Un array que define las configuraciones necesarias para construir la cláusula `JOIN`.
     *   Ejemplo:
     *   ```php
     *   array(
     *       'tabla_base' => 'usuarios',
     *       'tabla_enlace' => 'roles',
     *       'tabla_renombrada' => 'usuarios_renombrados',
     *       'campo_tabla_base_id' => 'usuario_id',
     *       'campo_renombrado' => 'id_usuario'
     *   );
     *   ```
     * - **`$tablas`**:
     *   Una cadena que contiene las tablas o `JOINs` previamente generados en la consulta SQL.
     *   Ejemplo:
     *   ```php
     *   'SELECT * FROM clientes LEFT JOIN direcciones ON clientes.id = direcciones.cliente_id'
     *   ```
     *
     * ### Casos de error:
     *
     * - Si el array `$tabla_join` no contiene las claves `'tabla_base'` o `'tabla_enlace'`, se devuelve un error indicando
     *   que dichas claves son obligatorias.
     * - Si ocurre un problema al generar la estructura del `JOIN` en la función `data_para_join`, se devuelve un array con
     *   los detalles del error.
     *
     * ### Resultado esperado:
     *
     * - Devuelve una cadena con las tablas y la nueva cláusula `JOIN` concatenada.
     *   Ejemplo:
     *   ```sql
     *   ' LEFT JOIN usuarios AS usuarios_renombrados ON usuarios_renombrados.usuario_id = roles.id_usuario'
     *   ```
     *
     * ### Notas adicionales:
     *
     * - Esta función es útil para construir consultas SQL dinámicas que manejan relaciones entre múltiples tablas.
     * - La validación previa asegura que los datos proporcionados cumplen con los requisitos mínimos para evitar errores en
     *   la generación del SQL.
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
     * Genera y agrega una sentencia SQL de tipo `JOIN` a una cadena de tablas existentes.
     *
     * Esta función valida las tablas proporcionadas (`key` y `tabla_join`) y genera una sentencia SQL `JOIN`.
     * Posteriormente, concatena esta sentencia a la cadena de tablas existente.
     *
     * @param string $key Nombre o alias de la tabla base para el `JOIN`.
     *                    Debe ser una cadena no vacía y no numérica.
     *                    Ejemplo: `'usuarios'`.
     * @param string $tabla_join Nombre de la tabla de enlace utilizada en el `JOIN`.
     *                           Debe ser una cadena no vacía y no numérica.
     *                           Ejemplo: `'roles'`.
     * @param string $tablas Cadena de tablas ya generadas, a la que se añadirá el nuevo `JOIN`.
     *                       Puede ser una cadena vacía inicial o contener una lista de tablas ya procesadas.
     *
     * @return array|string Devuelve la cadena actualizada de tablas con la sentencia `JOIN` añadida si no hay errores.
     *                      En caso de error, devuelve un array con los detalles del problema.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $key = 'usuarios';
     * $tabla_join = 'roles';
     * $tablas = '';
     *
     * $resultado = $this->tablas_join_esp(key: $key, tabla_join: $tabla_join, tablas: $tablas);
     *
     * // Resultado esperado:
     * // ' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$key`**:
     *   Nombre o alias de la tabla base utilizada en el `JOIN`.
     *   Requisitos:
     *   - Debe ser una cadena no vacía.
     *   - No puede ser numérico.
     *   Ejemplo válido:
     *   ```php
     *   'usuarios'
     *   ```
     *   Ejemplo inválido:
     *   ```php
     *   ''  // Está vacío.
     *   123 // Es numérico.
     *   ```
     *
     * - **`$tabla_join`**:
     *   Nombre de la tabla de enlace que participa en el `JOIN`.
     *   Requisitos:
     *   - Debe ser una cadena no vacía.
     *   - No puede ser numérico.
     *   Ejemplo válido:
     *   ```php
     *   'roles'
     *   ```
     *   Ejemplo inválido:
     *   ```php
     *   ''   // Está vacío.
     *   456  // Es numérico.
     *   ```
     *
     * - **`$tablas`**:
     *   Cadena de tablas generadas anteriormente.
     *   Se concatenará con la nueva sentencia SQL `JOIN`.
     *   Ejemplo inicial:
     *   ```php
     *   '' // Cadena vacía para una nueva construcción.
     *   ```
     *   Ejemplo con datos:
     *   ```php
     *   ' INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id'
     *   ```
     *
     * ### Casos de uso exitoso:
     *
     * 1. Generar un `JOIN` entre las tablas `'usuarios'` y `'roles'` con una cadena inicial vacía:
     *    ```php
     *    $key = 'usuarios';
     *    $tabla_join = 'roles';
     *    $tablas = '';
     *
     *    $resultado = $this->tablas_join_esp(key: $key, tabla_join: $tabla_join, tablas: $tablas);
     *
     *    // Resultado esperado:
     *    // ' LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     *    ```
     *
     * 2. Agregar un nuevo `JOIN` a una cadena existente:
     *    ```php
     *    $key = 'usuarios';
     *    $tabla_join = 'roles';
     *    $tablas = ' INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id';
     *
     *    $resultado = $this->tablas_join_esp(key: $key, tabla_join: $tabla_join, tablas: $tablas);
     *
     *    // Resultado esperado:
     *    // ' INNER JOIN departamentos ON usuarios.departamento_id = departamentos.id
     *    //   LEFT JOIN usuarios AS usuarios ON usuarios.id = roles.usuarios_id'
     *    ```
     *
     * ### Casos de error:
     *
     * 1. **`$key` está vacío**:
     *    ```php
     *    $resultado = $this->tablas_join_esp(key: '', tabla_join: 'roles', tablas: '');
     *    // Resultado esperado: array con mensaje de error "Error key esta vacio".
     *    ```
     *
     * 2. **`$tabla_join` está vacío**:
     *    ```php
     *    $resultado = $this->tablas_join_esp(key: 'usuarios', tabla_join: '', tablas: '');
     *    // Resultado esperado: array con mensaje de error "Error $tabla_join esta vacio".
     *    ```
     *
     * 3. **Error al generar el `JOIN`**:
     *    Si ocurre un error en la generación del `JOIN`, se devuelve un array con detalles del problema.
     *
     * ### Resultado esperado:
     * - Devuelve una cadena con las tablas y el nuevo `JOIN` añadido correctamente.
     * - En caso de error, devuelve un array con los detalles y el mensaje del problema.
     *
     * ### Notas adicionales:
     * - Valida las tablas con `valida_tabla_join`.
     * - Genera la sentencia `JOIN` mediante `data_para_join_esp`.
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
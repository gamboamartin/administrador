<?php
namespace gamboamartin\administrador\modelado;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;


class params_sql{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * REG
     * Genera una condición SQL para validar la seguridad de datos basándose en el usuario permitido.
     *
     * Esta función verifica si el modelo incluye la clave `usuario_permitido_id` y valida la existencia
     * de la sesión del usuario actual. Luego, genera una condición SQL para asegurar que solo se acceda
     * a los datos permitidos al usuario activo.
     *
     * @param array $modelo_columnas_extra Arreglo que contiene información del modelo, incluyendo la clave
     *                                      `usuario_permitido_id` necesaria para la validación.
     *                                      Ejemplo:
     *                                      ```php
     *                                      [
     *                                          'usuario_permitido_id' => 'tabla.usuario_id'
     *                                      ]
     *                                      ```
     * @param string $sql_where_previo Condición SQL previa. Si está vacía, se generará automáticamente
     *                                 una cláusula `WHERE`.
     *
     * @return array|string Devuelve la condición SQL completa en formato de cadena si la validación es exitosa.
     *                      En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example Uso exitoso con datos válidos:
     * ```php
     * $modelo_columnas_extra = ['usuario_permitido_id' => 'tabla.usuario_id'];
     * $sql_where_previo = '';
     * $resultado = $this->asigna_seguridad_data($modelo_columnas_extra, $sql_where_previo);
     * // Resultado:
     * // " WHERE (tabla.usuario_id) = $_SESSION[usuario_id] "
     * ```
     *
     * @example Uso exitoso con condición SQL previa:
     * ```php
     * $modelo_columnas_extra = ['usuario_permitido_id' => 'tabla.usuario_id'];
     * $sql_where_previo = 'estado = "activo"';
     * $resultado = $this->asigna_seguridad_data($modelo_columnas_extra, $sql_where_previo);
     * // Resultado:
     * // " estado = "activo" AND (tabla.usuario_id) = $_SESSION[usuario_id] "
     * ```
     *
     * @throws errores Retorna un error si no se valida correctamente:
     * - Que `$modelo_columnas_extra` incluya `usuario_permitido_id`.
     * - Que la sesión contenga `usuario_id`.
     *
     * @internal
     * - Verifica la seguridad de los datos usando `valida_seguridad`.
     * - Agrega la cláusula `WHERE` si no existe previamente.
     * - Construye la condición con `usuario_permitido_id`.
     */
    private function asigna_seguridad_data(array $modelo_columnas_extra, string $sql_where_previo): array|string
    {
        $valida = $this->valida_seguridad(modelo_columnas_extra: $modelo_columnas_extra);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar $modelo->columnas_extra', data: $valida);
        }

        $where = $this->where(sql_where_previo: $sql_where_previo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar where', data: $where);
        }

        $sq_seg = $modelo_columnas_extra['usuario_permitido_id'];
        return " $where ($sq_seg) = $_SESSION[usuario_id] ";
    }


    /**
     * REG
     * Genera la cláusula SQL GROUP BY a partir de un array de campos.
     *
     * @param array $group_by Array que contiene los nombres de los campos para agrupar en la consulta SQL.
     *
     * @return string|array Retorna una cadena con la cláusula GROUP BY si la generación fue exitosa.
     *                      Si ocurre un error, retorna un array con los detalles del problema.
     *
     * @throws errores Si uno de los campos está vacío o no es un texto.
     *
     * @example Generar una cláusula GROUP BY con múltiples campos:
     * ```php
     * $group_by = ['campo1', 'campo2', 'campo3'];
     *
     * $resultado = $this->group_by_sql(group_by: $group_by);
     * // Resultado:
     * // " GROUP BY campo1, campo2, campo3 "
     * ```
     *
     * @example Generar una cláusula GROUP BY con un solo campo:
     * ```php
     * $group_by = ['campo1'];
     *
     * $resultado = $this->group_by_sql(group_by: $group_by);
     * // Resultado:
     * // " GROUP BY campo1 "
     * ```
     *
     * @example Manejo de error si un campo está vacío:
     * ```php
     * $group_by = ['campo1', '', 'campo3'];
     *
     * $resultado = $this->group_by_sql(group_by: $group_by);
     * // Resultado:
     * // Array con detalles del error, indicando que uno de los campos está vacío.
     * ```
     *
     * @example Manejo de error si un campo no es un texto:
     * ```php
     * $group_by = ['campo1', 123, 'campo3'];
     *
     * $resultado = $this->group_by_sql(group_by: $group_by);
     * // Resultado:
     * // Array con detalles del error, indicando que un campo no es un texto.
     * ```
     */
    private function group_by_sql(array $group_by): string|array
    {
        $group_by_sql = '';
        foreach ($group_by as $campo) {
            $campo = trim($campo);
            if ($campo === '') {
                return $this->error->error(
                    mensaje: 'Error el campo no puede venir vacío',
                    data: $group_by,
                    es_final: true
                );
            }
            if (is_numeric($campo)) {
                return $this->error->error(
                    mensaje: 'Error el campo debe ser un texto',
                    data: $campo,
                    es_final: true
                );
            }
            if ($group_by_sql === '') {
                $group_by_sql .= ' GROUP BY ' . $campo . ' ';
            } else {
                $group_by_sql .= ',' . $campo . ' ';
            }
        }
        return $group_by_sql;
    }


    /**
     * REG
     * Genera la cláusula SQL LIMIT a partir de un entero proporcionado.
     *
     * @param int $limit Valor entero que representa el número máximo de registros a devolver.
     *                   Debe ser mayor o igual a 0.
     *
     * @return string|array Retorna una cadena con la cláusula LIMIT si la generación fue exitosa.
     *                      Si ocurre un error, retorna un array con los detalles del problema.
     *
     * @throws errores Si `$limit` es menor que 0, indicando que no es un valor válido.
     *
     * @example Generar una cláusula LIMIT con un valor positivo:
     * ```php
     * $limit = 10;
     *
     * $resultado = $this->limit_sql(limit: $limit);
     * // Resultado:
     * // " LIMIT 10 "
     * ```
     *
     * @example Generar una cláusula LIMIT sin límite (0 o no se aplica):
     * ```php
     * $limit = 0;
     *
     * $resultado = $this->limit_sql(limit: $limit);
     * // Resultado:
     * // ""
     * ```
     *
     * @example Manejo de error si `$limit` es negativo:
     * ```php
     * $limit = -5;
     *
     * $resultado = $this->limit_sql(limit: $limit);
     * // Resultado:
     * // Array con detalles del error, indicando que `$limit` debe ser mayor o igual a 0.
     * ```
     */
    private function limit_sql(int $limit): string|array
    {
        if ($limit < 0) {
            return $this->error->error(
                mensaje: 'Error limit debe ser mayor o igual a 0',
                data: $limit,
                es_final: true
            );
        }
        $limit_sql = '';
        if ($limit > 0) {
            $limit_sql .= ' LIMIT ' . $limit;
        }
        return $limit_sql;
    }


    /**
     * REG
     * Genera la cláusula SQL OFFSET a partir de un valor entero proporcionado.
     *
     * @param int $offset Valor entero que representa el número de registros a omitir en la consulta.
     *                    Debe ser mayor o igual a 0.
     *
     * @return string|array Retorna una cadena con la cláusula OFFSET si la generación fue exitosa.
     *                      Si ocurre un error, retorna un array con los detalles del problema.
     *
     * @throws errores Si `$offset` es menor que 0, indicando que no es un valor válido.
     *
     * @example Generar una cláusula OFFSET con un valor positivo:
     * ```php
     * $offset = 20;
     *
     * $resultado = $this->offset_sql(offset: $offset);
     * // Resultado:
     * // " OFFSET 20 "
     * ```
     *
     * @example Generar una cláusula OFFSET sin omisión (0):
     * ```php
     * $offset = 0;
     *
     * $resultado = $this->offset_sql(offset: $offset);
     * // Resultado:
     * // ""
     * ```
     *
     * @example Manejo de error si `$offset` es negativo:
     * ```php
     * $offset = -10;
     *
     * $resultado = $this->offset_sql(offset: $offset);
     * // Resultado:
     * // Array con detalles del error, indicando que `$offset` debe ser mayor o igual a 0.
     * ```
     */
    private function offset_sql(int $offset): string|array
    {
        if ($offset < 0) {
            return $this->error->error(
                mensaje: 'Error $offset debe ser mayor o igual a 0',
                data: $offset,
                es_final: true
            );
        }

        $offset_sql = '';
        if ($offset > 0) {
            $offset_sql .= ' OFFSET ' . $offset;
        }
        return $offset_sql;
    }


    /**
     * TOTAL
     * Prepara y asegura las cláusulas SQL para su ejecución segura.
     *
     * @param bool $aplica_seguridad Si es true, entonces aplica los procedimientos de seguridad a la consulta SQL.
     * @param array $group_by Arreglo que contiene las columnas para la cláusula GROUP BY.
     * @param int $limit Número entero para la cláusula LIMIT.
     * @param array $modelo_columnas_extra Arreglo que contiene columnas adicionales para el modelo.
     * @param int $offset Número entero para la cláusula OFFSET.
     * @param array $order Arreglo que contiene las columnas para la cláusula ORDER BY.
     * @param string $sql_where_previo Cadena de texto con una declaración SQL WHERE anterior.
     *
     * @return array|stdClass Devuelve un objeto stdClass o un array que contienen los componentes SQL preparados.
     *                        Si se encuentra un error durante el proceso, devuelve un mensaje de error.
     * @version 15.58.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.params_sql.params_sql
     */
    final public function params_sql(bool $aplica_seguridad, array $group_by, int $limit, array $modelo_columnas_extra,
                                     int $offset, array $order, string $sql_where_previo): array|stdClass
    {
        if($limit<0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0',data:  $limit, es_final: true);
        }
        if($offset<0){
            return $this->error->error(mensaje: 'Error $offset debe ser mayor o igual a 0',data: $offset,
                es_final: true);

        }

        $group_by_sql = $this->group_by_sql(group_by: $group_by);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data:$group_by_sql);
        }

        $order_sql = $this->order_sql(order: $order);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar order',data:$order_sql);
        }

        $limit_sql = $this->limit_sql(limit: $limit);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar limit',data:$limit_sql);
        }

        $offset_sql = $this->offset_sql(offset: $offset);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar offset',data:$offset_sql);
        }

        $seguridad = $this->seguridad(aplica_seguridad:$aplica_seguridad, modelo_columnas_extra: $modelo_columnas_extra,
            sql_where_previo:  $sql_where_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql de seguridad', data: $seguridad);
        }

        $params = new stdClass();
        $params->group_by = $group_by_sql;
        $params->order = $order_sql;
        $params->limit = $limit_sql;
        $params->offset = $offset_sql;
        $params->seguridad = $seguridad;

        return $params;

    }

    /**
     * REG
     * Genera la cláusula SQL ORDER BY a partir de un array de campos y tipos de orden.
     *
     * @param array $order Array asociativo donde las claves son los nombres de los campos
     *                     y los valores son los tipos de orden (ASC o DESC).
     *
     * @return string|array Retorna una cadena con la cláusula ORDER BY si la generación fue exitosa.
     *                      Si ocurre un error, retorna un array con los detalles del problema.
     *
     * @throws errores Si una clave (campo) es numérica, indicando que no es un texto válido.
     *
     * @example Generar una cláusula ORDER BY con múltiples campos:
     * ```php
     * $order = [
     *     'campo1' => 'ASC',
     *     'campo2' => 'DESC'
     * ];
     *
     * $resultado = $this->order_sql(order: $order);
     * // Resultado:
     * // " ORDER BY campo1 ASC, campo2 DESC "
     * ```
     *
     * @example Generar una cláusula ORDER BY con un solo campo:
     * ```php
     * $order = [
     *     'campo1' => 'ASC'
     * ];
     *
     * $resultado = $this->order_sql(order: $order);
     * // Resultado:
     * // " ORDER BY campo1 ASC "
     * ```
     *
     * @example Manejo de error si una clave (campo) es numérica:
     * ```php
     * $order = [
     *     123 => 'ASC',
     *     'campo2' => 'DESC'
     * ];
     *
     * $resultado = $this->order_sql(order: $order);
     * // Resultado:
     * // Array con detalles del error, indicando que la clave no es un texto válido.
     * ```
     */
    private function order_sql(array $order): array|string
    {
        $order_sql = '';
        foreach ($order as $campo => $tipo_order) {
            if (is_numeric($campo)) {
                return $this->error->error(
                    mensaje: 'Error $campo debe ser txt',
                    data: $order,
                    es_final: true
                );
            }
            if ($order_sql === '') {
                $order_sql .= ' ORDER BY ' . $campo . ' ' . $tipo_order;
            } else {
                $order_sql .= ',' . $campo . ' ' . $tipo_order;
            }
        }
        return $order_sql;
    }


    /**
     * REG
     * Genera una cláusula SQL de seguridad para restringir el acceso a los datos según el usuario actual.
     *
     * Esta función permite aplicar una condición de seguridad basada en el usuario logueado, validando
     * que el modelo contenga la clave `usuario_permitido_id` en su estructura. Si `aplica_seguridad` es
     * `true`, se genera una condición SQL que asegura que solo se accede a los datos permitidos para el
     * usuario activo.
     *
     * @param bool $aplica_seguridad Indica si se debe aplicar la seguridad:
     *                               - `true`: Se genera la cláusula SQL de seguridad.
     *                               - `false`: No se aplica seguridad y se devuelve una cadena vacía.
     * @param array $modelo_columnas_extra Arreglo que contiene información del modelo, incluyendo la clave
     *                                      `usuario_permitido_id` necesaria para la validación.
     *                                      Ejemplo:
     *                                      ```php
     *                                      [
     *                                          'usuario_permitido_id' => 'tabla.usuario_id'
     *                                      ]
     *                                      ```
     * @param string $sql_where_previo Condición SQL previa que será usada como base. Si está vacía, se generará
     *                                 automáticamente una cláusula `WHERE`.
     *
     * @return array|string Devuelve la condición SQL de seguridad en formato de cadena si la validación es exitosa.
     *                      En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example Uso exitoso aplicando seguridad:
     * ```php
     * $aplica_seguridad = true;
     * $modelo_columnas_extra = ['usuario_permitido_id' => 'tabla.usuario_id'];
     * $sql_where_previo = '';
     * $resultado = $this->seguridad($aplica_seguridad, $modelo_columnas_extra, $sql_where_previo);
     * // Resultado:
     * // " WHERE (tabla.usuario_id) = $_SESSION[usuario_id] "
     * ```
     *
     * @example Uso exitoso sin aplicar seguridad:
     * ```php
     * $aplica_seguridad = false;
     * $modelo_columnas_extra = ['usuario_permitido_id' => 'tabla.usuario_id'];
     * $sql_where_previo = 'estado = "activo"';
     * $resultado = $this->seguridad($aplica_seguridad, $modelo_columnas_extra, $sql_where_previo);
     * // Resultado:
     * // ""
     * ```
     *
     * @throws errores Retorna un error si:
     * - `modelo_columnas_extra` no incluye `usuario_permitido_id`.
     * - La sesión no contiene `usuario_id`.
     * - Ocurre un error al generar la cláusula SQL.
     *
     * @internal
     * - Valida la seguridad usando `valida_seguridad`.
     * - Genera la cláusula de seguridad con `asigna_seguridad_data`.
     * - Integra la cláusula en función del parámetro `aplica_seguridad`.
     */
    final public function seguridad(
        bool $aplica_seguridad, array $modelo_columnas_extra, string $sql_where_previo): array|string
    {
        $seguridad = '';
        if ($aplica_seguridad) {

            $valida = $this->valida_seguridad(modelo_columnas_extra: $modelo_columnas_extra);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar $modelo->columnas_extra', data: $valida);
            }

            $seguridad = $this->asigna_seguridad_data(modelo_columnas_extra: $modelo_columnas_extra,
                sql_where_previo: $sql_where_previo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar sql de seguridad', data: $seguridad);
            }
        }
        return $seguridad;
    }


    /**
     * REG
     * Valida la seguridad de las operaciones verificando la presencia de claves específicas
     * en el arreglo `$modelo_columnas_extra` y en la superglobal `$_SESSION`.
     *
     * Esta función asegura que la información necesaria para validar permisos y contexto de usuario
     * esté correctamente configurada antes de continuar con otras operaciones.
     *
     * @param array $modelo_columnas_extra Arreglo que debe contener la clave `usuario_permitido_id`,
     *                                     la cual se utiliza para determinar los permisos de seguridad.
     *
     * @return true|array Retorna `true` si todas las validaciones son exitosas. En caso de error,
     *                    retorna un array con los detalles del problema encontrado.
     *
     * @throws errores Si falta alguna clave requerida en `$modelo_columnas_extra` o en `$_SESSION`,
     *                 o si las claves necesarias están vacías.
     *
     * @example Validación exitosa de seguridad:
     * ```php
     * $_SESSION['usuario_id'] = 1;
     * $modelo_columnas_extra = [
     *     'usuario_permitido_id' => 5
     * ];
     *
     * $resultado = $this->valida_seguridad($modelo_columnas_extra);
     * // Resultado:
     * // true
     * ```
     *
     * @example Error por falta de clave en `$modelo_columnas_extra`:
     * ```php
     * $_SESSION['usuario_id'] = 1;
     * $modelo_columnas_extra = [];
     *
     * $resultado = $this->valida_seguridad($modelo_columnas_extra);
     * // Resultado:
     * // Array con detalles del error indicando que falta `usuario_permitido_id`.
     * ```
     *
     * @example Error por sesión no configurada:
     * ```php
     * // No se define `$_SESSION['usuario_id']`.
     * $modelo_columnas_extra = [
     *     'usuario_permitido_id' => 5
     * ];
     *
     * $resultado = $this->valida_seguridad($modelo_columnas_extra);
     * // Resultado:
     * // Array con detalles del error indicando que `$_SESSION['usuario_id']` no está definida.
     * ```
     */
    private function valida_seguridad(array $modelo_columnas_extra): true|array
    {
        $keys = array('usuario_permitido_id');
        $valida = (new validacion())->valida_existencia_keys(
            keys: $keys,
            registro: $modelo_columnas_extra,
            valida_vacio: false
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar $modelo->columnas_extra',
                data: $valida
            );
        }

        if (!isset($_SESSION['usuario_id'])) {
            return $this->error->error(
                mensaje: 'Error al validar $_SESSION no esta definida',
                data: array(),
                es_final: true
            );
        }

        $keys = array('usuario_id');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $_SESSION);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar $_SESSION',
                data: $valida
            );
        }

        return true;
    }


    /**
     * REG
     * Genera la cláusula inicial `WHERE` para una consulta SQL, en caso de que no exista una cláusula previa.
     *
     * Esta función evalúa si una cadena de condiciones SQL (`$sql_where_previo`) está vacía. Si es así,
     * retorna la palabra clave `WHERE`, indicando el inicio de las condiciones para la consulta SQL.
     *
     * @param string $sql_where_previo Cadena con condiciones SQL ya existentes o vacía si no hay condiciones previas.
     *
     * @return string Retorna `' WHERE '` si `$sql_where_previo` está vacío. En caso contrario, retorna una cadena vacía.
     *
     * @example Uso con condiciones SQL previas vacías:
     * ```php
     * $sql_where_previo = '';
     * $resultado = $this->where($sql_where_previo);
     * // Resultado:
     * // ' WHERE '
     * ```
     *
     * @example Uso con condiciones SQL ya definidas:
     * ```php
     * $sql_where_previo = 'id = 5';
     * $resultado = $this->where($sql_where_previo);
     * // Resultado:
     * // ''
     * ```
     */
    private function where(string $sql_where_previo): string
    {
        $where = '';
        if ($sql_where_previo === '') {
            $where = ' WHERE ';
        }
        return $where;
    }


}


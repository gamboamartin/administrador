<?php
namespace base\orm;

use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;


class where{

    public errores $error;
    public validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }


    /**
     * REG
     * Genera un objeto completo de filtros SQL a partir de múltiples parámetros.
     *
     * Este método integra diversas condiciones y cláusulas para construir un filtro SQL completo, el cual
     * se utiliza en consultas de la base de datos. Se combinan las siguientes cláusulas:
     *
     * - **Diferente de:** Condiciones que excluyen registros que cumplan ciertos valores.
     * - **Filtro básico:** Condiciones simples definidas en el parámetro `$filtro`.
     * - **Filtro especial:** Condiciones con operadores y comparaciones personalizadas (por ejemplo,
     *   para condiciones complejas o subconsultas).
     * - **Filtro extra:** Condiciones adicionales que se concatenan a la sentencia principal.
     * - **Filtro de fecha:** Condiciones basadas en rangos de fecha.
     * - **Filtro de rango:** Condiciones que utilizan el operador BETWEEN.
     * - **Filtro IN:** Condiciones que filtran registros que contengan determinados valores.
     * - **Filtro NOT IN:** Condiciones que excluyen registros con ciertos valores.
     * - **SQL extra:** Cualquier cadena SQL adicional que se desee concatenar a la consulta.
     *
     * Además, el método valida el tipo de filtro (`$tipo_filtro`), que puede ser:
     * - **'numeros':** Para filtros exactos (sin comodines).
     * - **'textos':** Para filtros que permiten coincidencias parciales (se integran comodines `%`).
     *
     * El proceso de generación se realiza en los siguientes pasos:
     * 1. Se valida que el tipo de filtro sea correcto usando `verifica_tipo_filtro()`.
     * 2. Se genera la sentencia base para los filtros simples mediante `genera_sentencia_base()`.
     * 3. Se generan las cláusulas específicas:
     *    - **Filtro especial:** mediante `filtro_especial_sql()`.
     *    - **Filtro de rango:** mediante `filtro_rango_sql()`.
     *    - **Filtro extra:** mediante `filtro_extra_sql()`.
     *    - **Filtro NOT IN:** mediante `genera_not_in_sql()`.
     *    - **Filtro IN:** mediante `genera_in_sql_normalizado()`.
     *    - **Filtro de fecha:** mediante `filtro_fecha()`.
     *    - **Diferente de:** mediante `diferente_de_sql()`.
     * 4. Se integran todas las cláusulas anteriores en un objeto de filtros completo usando
     *    `genera_filtros_iniciales()`.
     * 5. Se genera la cláusula WHERE (si corresponde) a partir de las claves definidas en `$keys_data_filter`
     *    mediante el método `where()`.
     * 6. Finalmente, se envuelven los filtros en paréntesis y se asigna la cláusula WHERE al objeto de filtros.
     *
     * @param array $columnas_extra Arreglo con columnas adicionales definidas para el modelo (por ejemplo, para subconsultas).
     *                                  Ejemplo: `['tabla.campo' => 'alias_campo']`
     * @param array $diferente_de Arreglo asociativo que define condiciones "diferente de" (<>).
     *                                  Ejemplo: `['tabla.estado' => 'inactivo']`
     * @param array $filtro Filtros básicos para la consulta, en forma de array asociativo.
     *                                  Ejemplo: `['tabla.nombre' => 'Juan']`
     * @param array $filtro_especial Filtros especiales para condiciones complejas.
     *                                  Ejemplo:
     *                                  ```php
     *                                  [
     *                                      'tabla.edad' => [
     *                                          'operador' => '>',
     *                                          'valor' => '30',
     *                                          'comparacion' => 'AND'
     *                                      ]
     *                                  ]
     *                                  ```
     * @param array $filtro_extra Filtros adicionales para la consulta.
     *                                  Ejemplo:
     *                                  ```php
     *                                  [
     *                                      'tabla.sueldo' => [
     *                                          'operador' => '<',
     *                                          'valor' => '5000',
     *                                          'comparacion' => 'OR'
     *                                      ]
     *                                  ]
     *                                  ```
     * @param array $filtro_fecha (Opcional) Arreglo adicional de filtros de fecha si se requiere.
     *
     * @param array $filtro_rango Filtros de rango para consultas BETWEEN.
     *                                  Ejemplo:
     *                                  ```php
     *                                  [
     *                                      'tabla.puntaje' => [
     *                                          'valor1' => 1,
     *                                          'valor2' => 10,
     *                                          'valor_campo' => false
     *                                      ]
     *                                  ]
     *                                  ```
     * @param array $in Arreglo para la cláusula IN. Debe incluir la llave y los valores.
     *                                  Ejemplo: `['llave' => 'tabla.id', 'values' => [1,2,3]]`
     * @param array $keys_data_filter Array de claves que se utilizarán para identificar y procesar los filtros.
     *                                  Ejemplo: `['nombre', 'edad', 'sueldo', 'fecha']`
     * @param array $not_in Arreglo para la cláusula NOT IN.
     *                                  Ejemplo: `['llave' => 'tabla.categoria_id', 'values' => [4,5,6]]`
     * @param string $sql_extra SQL adicional que se desea concatenar a la consulta.
     *                                  Ejemplo: `" AND activo = '1' "`
     * @param string $tipo_filtro Tipo de filtro a aplicar: 'numeros' para filtros exactos o 'textos' para filtros con comodines.
     *                                  Ejemplo: `'numeros'`
     * @return array|stdClass Devuelve un objeto `stdClass` que contiene todas las cláusulas y componentes SQL generados:
     *                        - `where`: La cláusula WHERE resultante (si se generó alguna condición).
     *                        - `sentencia`: La sentencia SQL base.
     *                        - Otros componentes como `filtro_especial`, `filtro_extra`, `filtro_rango`, `in`, `not_in`, etc.
     *
     * En caso de error, retorna un array con los detalles del error.
     *
     * @example Ejemplo 1: Generación de filtros completos sin filtros de fecha adicionales
     * ```php
     * $columnas_extra = ['tabla.campo' => 'alias_campo'];
     * $diferente_de   = ['tabla.estado' => 'inactivo'];
     * $filtro         = ['tabla.nombre' => 'Juan'];
     * $filtro_especial = [
     *     'tabla.edad' => [
     *         'operador' => '>',
     *         'valor' => '30',
     *         'comparacion' => 'AND'
     *     ]
     * ];
     * $filtro_extra   = [
     *     'tabla.sueldo' => [
     *         'operador' => '<',
     *         'valor' => '5000',
     *         'comparacion' => 'OR'
     *     ]
     * ];
     * $filtro_fecha   = [];  // Sin filtros de fecha
     * $filtro_rango   = [
     *     'tabla.puntaje' => [
     *         'valor1' => 1,
     *         'valor2' => 10,
     *         'valor_campo' => false
     *     ]
     * ];
     * $in             = ['llave' => 'tabla.id', 'values' => [1,2,3]];
     * $keys_data_filter = ['nombre', 'edad', 'sueldo'];
     * $not_in         = ['llave' => 'tabla.categoria_id', 'values' => [4,5,6]];
     * $sql_extra      = " AND activo = '1' ";
     * $tipo_filtro    = 'numeros';
     *
     * $filtros = $this->data_filtros_full(
     *      $columnas_extra,
     *      $diferente_de,
     *      $filtro,
     *      $filtro_especial,
     *      $filtro_extra,
     *      $filtro_fecha,
     *      $filtro_rango,
     *      $in,
     *      $keys_data_filter,
     *      $not_in,
     *      $sql_extra,
     *      $tipo_filtro
     * );
     *
     * // Resultado esperado: Objeto stdClass con propiedades como:
     * // ->where: " WHERE " (si se generó alguna condición)
     * // ->sentencia: " alias_campo = 'Juan' "
     * // ->filtro_especial: " AND ( tabla.edad > '30' ) "
     * // ->filtro_extra: " OR ( tabla.sueldo < '5000' ) "
     * // ->filtro_rango: " AND ( tabla.puntaje BETWEEN '1' AND '10' ) "
     * // ->in: " alias_campo IN (1,2,3) " (según la implementación)
     * // ->not_in: " alias_campo NOT IN (4,5,6) "
     * // ->sql_extra: " AND activo = '1' "
     * ```
     *
     * @example Ejemplo 2: Generación de filtros completos con filtros de fecha
     * ```php
     * $columnas_extra = ['tabla.fecha' => 'alias_fecha'];
     * $diferente_de   = [];
     * $filtro         = [];
     * $filtro_especial = [];
     * $filtro_extra   = [];
     * $filtro_fecha   = [
     *     'fecha' => [
     *         'campo_1' => '2023-01-01',
     *         'campo_2' => '2023-12-31',
     *         'fecha' => '2023-06-15'
     *     ]
     * ];
     * $filtro_rango   = [];
     * $in             = [];
     * $keys_data_filter = ['fecha'];
     * $not_in         = [];
     * $sql_extra      = "";
     * $tipo_filtro    = 'numeros';
     *
     * $filtros = $this->data_filtros_full(
     *      $columnas_extra,
     *      $diferente_de,
     *      $filtro,
     *      $filtro_especial,
     *      $filtro_extra,
     *      $filtro_fecha,
     *      $filtro_rango,
     *      $in,
     *      $keys_data_filter,
     *      $not_in,
     *      $sql_extra,
     *      $tipo_filtro
     * );
     *
     * // Resultado esperado: Objeto stdClass que incluye la cláusula WHERE generada para el filtro de fecha,
     * // por ejemplo: " WHERE ( alias_fecha BETWEEN '2023-01-01' AND '2023-12-31' ) "
     * ```
     *
     */
    final public function data_filtros_full(
        array $columnas_extra,
        array $diferente_de,
        array $filtro,
        array $filtro_especial,
        array $filtro_extra,
        array $filtro_fecha,
        array $filtro_rango,
        array $in,
        array $keys_data_filter,
        array $not_in,
        string $sql_extra,
        string $tipo_filtro
    ): array|stdClass {
        $verifica_tf = (new \gamboamartin\where\where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar tipo_filtro', data: $verifica_tf);
        }
        $filtros = $this->genera_filtros_sql(
            columnas_extra: $columnas_extra,
            diferente_de: $diferente_de,
            filtro: $filtro,
            filtro_especial: $filtro_especial,
            filtro_extra: $filtro_extra,
            filtro_rango: $filtro_rango,
            in: $in,
            keys_data_filter: $keys_data_filter,
            not_in: $not_in,
            sql_extra: $sql_extra,
            tipo_filtro: $tipo_filtro,
            filtro_fecha: $filtro_fecha
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar filtros', data: $filtros);
        }

        $where = $this->where(filtros: $filtros, keys_data_filter: $keys_data_filter);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar where', data: $where);
        }

        $filtros = $this->filtros_full(filtros: $filtros, keys_data_filter: $keys_data_filter);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar filtros', data: $filtros);
        }
        $filtros->where = $where;
        return $filtros;
    }




    /**
     * REG
     * Genera una cláusula SQL que verifica que el valor de un campo sea diferente de un valor específico.
     *
     * Esta función construye una condición SQL de la forma:
     *
     *     [AND] campo <> 'value'
     *
     * donde:
     * - **campo**: es el nombre del atributo o columna del modelo, debidamente escapado.
     * - **value**: es el valor con el que se comparará, también escapado.
     * - **AND**: se añade si la cadena pasada en el parámetro `$diferente_de_sql` no está vacía. Se utiliza
     *   el método `and_filtro_fecha()` para determinar si se debe anteponer el operador lógico.
     *
     * Se realizan las siguientes validaciones:
     * - Se recorta y verifica que `$campo` no sea una cadena vacía.
     * - Se comprueba que `$campo` no sea numérico, ya que se espera que sea un nombre de atributo.
     * - Se utiliza el método `and_filtro_fecha()` para integrar un operador lógico ("AND") si es necesario,
     *   basándose en el contenido de `$diferente_de_sql`.
     * - Se escapan tanto el `$campo` como el `$value` utilizando `addslashes()` para prevenir inyección SQL.
     *
     * @param string $campo             Nombre de la columna o atributo del modelo. Debe ser un texto no vacío y no numérico.
     *                                  Ejemplo: `"precio"`.
     * @param string $diferente_de_sql  Cadena que puede contener condiciones previas para determinar si se antepone
     *                                  el operador lógico "AND". Si está vacía, no se agrega el operador.
     *                                  Ejemplo: `" AND "` o una cadena vacía `""`.
     * @param string $value             Valor con el cual se compara el campo para asegurar que sean diferentes.
     *                                  Se espera un valor en forma de cadena.
     *                                  Ejemplo: `"100"`.
     *
     * @return string|array             Retorna una cadena SQL que representa la condición, por ejemplo:
     *                                  <pre>" AND precio <> '100'"</pre>
     *                                  o, en caso de error, retorna un array con la información del error.
     *
     * @example Ejemplo 1: Uso correcto con condiciones previas
     * <pre>
     * $campo = "precio";
     * $diferente_de_sql = " AND "; // Se desea concatenar la condición con un AND previo
     * $value = "100";
     *
     * $resultado = $this->diferente_de($campo, $diferente_de_sql, $value);
     * // Resultado esperado:
     * // " AND precio <> '100'"
     * </pre>
     *
     * @example Ejemplo 2: Uso correcto sin condiciones previas
     * <pre>
     * $campo = "precio";
     * $diferente_de_sql = ""; // No hay condiciones previas, por lo que no se antepone "AND"
     * $value = "100";
     *
     * $resultado = $this->diferente_de($campo, $diferente_de_sql, $value);
     * // Resultado esperado:
     * // "  precio <> '100'" (notar que la función internamente agrega espacios en la concatenación)
     * </pre>
     *
     * @example Ejemplo 3: Error por campo vacío
     * <pre>
     * $campo = "";
     * $diferente_de_sql = " AND ";
     * $value = "100";
     *
     * $resultado = $this->diferente_de($campo, $diferente_de_sql, $value);
     * // Resultado esperado: Un array de error con el mensaje "Error campo esta vacio"
     * </pre>
     *
     * @example Ejemplo 4: Error por campo numérico
     * <pre>
     * $campo = "123";
     * $diferente_de_sql = " AND ";
     * $value = "100";
     *
     * $resultado = $this->diferente_de($campo, $diferente_de_sql, $value);
     * // Resultado esperado: Un array de error con el mensaje "Error campo debe ser un atributo del modelo no un numero"
     * </pre>
     */
    private function diferente_de(string $campo, string $diferente_de_sql, string $value): string|array
    {
        $campo = trim($campo);
        if ($campo === '') {
            return $this->error->error(
                mensaje: "Error campo esta vacio",
                data: $campo,
                es_final: true
            );
        }
        if (is_numeric($campo)) {
            return $this->error->error(
                mensaje: "Error campo debe ser un atributo del modelo no un numero",
                data: $campo,
                es_final: true
            );
        }
        $and = (new \gamboamartin\where\where())->and_filtro_fecha(txt: $diferente_de_sql);
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al integrar AND",
                data: $and
            );
        }

        $campo = addslashes($campo);
        $value = addslashes($value);

        return " $and $campo <> '$value' ";
    }


    /**
     * REG
     * Genera una cláusula SQL que concatena condiciones "diferente de" para múltiples campos.
     *
     * Esta función recorre un arreglo asociativo en el que cada clave representa el nombre de un campo (atributo del modelo)
     * y cada valor es el valor contra el cual se debe evaluar la condición de desigualdad. Por cada par clave/valor, la función:
     *
     * - Recorta y valida que la clave no sea una cadena vacía.
     * - Verifica que la clave no sea numérica (se espera que sea el nombre de un atributo, no un número).
     * - Llama al método `diferente_de()` para generar la condición SQL que compara el campo con el valor mediante el operador
     *   de diferencia (`<>`). Se integra además, un operador lógico ("AND") si corresponde, basado en el contenido acumulado en
     *   la variable `$diferente_de_sql`.
     * - Concatena la condición generada a la variable `$diferente_de_sql`.
     *
     * Si el arreglo `$diferente_de` está vacío, la función retorna una cadena vacía.
     *
     * @param array $diferente_de Array asociativo que define las condiciones de desigualdad. Cada elemento debe tener la forma:
     *                            - **clave** (string): El nombre del campo a evaluar.
     *                              Ejemplo: `"precio"`.
     *                            - **valor** (string): El valor contra el cual se compara, para verificar que el campo sea diferente.
     *                              Ejemplo: `"100"`.
     *
     * @return string|array Retorna una cadena SQL que representa la concatenación de todas las condiciones "diferente de".
     *                      Por ejemplo, si se pasa:
     *                      <pre>
     *                      [
     *                          "precio" => "100",
     *                          "stock"  => "50"
     *                      ]
     *                      </pre>
     *                      el resultado podría ser:
     *                      <pre>
     *                      " AND precio <> '100'  AND stock <> '50' "
     *                      </pre>
     *                      En caso de error en alguna validación, retorna un array con la información del error.
     *
     * @example Ejemplo 1: Uso correcto con condiciones para "precio" y "stock"
     * <pre>
     * $diferente_de = [
     *     "precio" => "100",
     *     "stock"  => "50"
     * ];
     *
     * $resultado = $this->diferente_de_sql($diferente_de);
     * // Resultado esperado:
     * // " AND precio <> '100'  AND stock <> '50' "
     * </pre>
     *
     * @example Ejemplo 2: Error por campo vacío
     * <pre>
     * $diferente_de = [
     *     "" => "100"
     * ];
     *
     * $resultado = $this->diferente_de_sql($diferente_de);
     * // Resultado esperado: Array de error con mensaje "Error campo esta vacio"
     * </pre>
     *
     * @example Ejemplo 3: Error por campo numérico
     * <pre>
     * $diferente_de = [
     *     "123" => "100"
     * ];
     *
     * $resultado = $this->diferente_de_sql($diferente_de);
     * // Resultado esperado: Array de error con mensaje "Error campo debe ser un atributo del modelo no un numero"
     * </pre>
     */
    private function diferente_de_sql(array $diferente_de): array|string
    {
        $diferente_de_sql = '';
        if (count($diferente_de) > 0) {

            foreach ($diferente_de as $campo => $value) {

                $campo = trim($campo);
                if ($campo === '') {
                    return $this->error->error(
                        mensaje: "Error campo esta vacio",
                        data: $campo,
                        es_final: true
                    );
                }
                if (is_numeric($campo)) {
                    return $this->error->error(
                        mensaje: "Error campo debe ser un atributo del modelo no un numero",
                        data: $campo,
                        es_final: true
                    );
                }

                $sql = $this->diferente_de(campo: $campo, diferente_de_sql: $diferente_de_sql, value: $value);
                if (errores::$error) {
                    return $this->error->error(
                        mensaje: "Error al integrar sql",
                        data: $sql
                    );
                }

                $diferente_de_sql .= $sql;
            }
        }
        return $diferente_de_sql;
    }


    /**
     * REG
     * Genera la cláusula SQL para filtros especiales a partir de un conjunto de filtros.
     *
     * Este método recorre el array de filtros especiales ($filtro_especial) y, para cada filtro, realiza lo siguiente:
     *
     * 1. Valida que el filtro especial (cada elemento de $filtro_especial) sea un array. Si no lo es, retorna un error
     *    indicando que el filtro debe definirse como un array.
     * 2. Llama al método {@see obten_filtro_especial()} para procesar el filtro especial actual y actualizar
     *    la cláusula SQL acumulada ($filtro_especial_sql). Este método integra la condición SQL generada a partir
     *    del filtro especial en la cláusula existente.
     * 3. Si ocurre algún error durante la integración, se retorna un array con la información del error.
     * 4. Al finalizar el recorrido, retorna la cláusula SQL resultante que integra todos los filtros especiales.
     *
     * @param array $columnas_extra Array asociativo que define las columnas adicionales o alias para los campos.
     *                                Ejemplo:
     *                                ```php
     *                                [
     *                                    'tabla.precio' => 'productos.precio'
     *                                ]
     *                                ```
     * @param array $filtro_especial  Array de filtros especiales. Cada entrada debe tener la siguiente estructura:
     *                                ```php
     *                                [
     *                                    'tabla.precio' => [
     *                                        'operador'    => '>',    // Operador de comparación
     *                                        'valor'       => 100,    // Valor a comparar
     *                                        'comparacion' => 'AND'   // Operador lógico para concatenar (por ejemplo, "AND" u "OR")
     *                                    ]
     *                                ]
     *                                ```
     *
     * @return array|string Devuelve una cadena SQL que resulta de concatenar todas las condiciones especiales
     *                      de filtro. En caso de error, retorna un array con los detalles del error.
     *
     * @example Ejemplo 1: Filtro especial único sin condición previa
     * ```php
     * $columnas_extra = ['tabla.precio' => 'productos.precio'];
     * $filtro_especial = [
     *     'tabla.precio' => [
     *         'operador' => '>',
     *         'valor' => 100,
     *         'comparacion' => 'AND'
     *     ]
     * ];
     *
     * $resultado = $obj->filtro_especial_sql($columnas_extra, $filtro_especial);
     * // Resultado esperado:
     * // "productos.precio > '100'"
     * ```
     *
     * @example Ejemplo 2: Múltiples filtros especiales concatenados
     * ```php
     * $columnas_extra = [
     *     'tabla.precio' => 'productos.precio',
     *     'tabla.stock'  => 'productos.stock'
     * ];
     * $filtro_especial = [
     *     'tabla.precio' => [
     *         'operador' => '>',
     *         'valor' => 100,
     *         'comparacion' => 'AND'
     *     ],
     *     'tabla.stock' => [
     *         'operador' => '<',
     *         'valor' => 50,
     *         'comparacion' => 'AND'
     *     ]
     * ];
     *
     * $resultado = $obj->filtro_especial_sql($columnas_extra, $filtro_especial);
     * // Resultado esperado (ejemplo):
     * // "productos.precio > '100' AND productos.stock < '50'"
     * ```
     *
     * @example Ejemplo 3: Error cuando un filtro especial no es un array
     * ```php
     * $columnas_extra = ['tabla.precio' => 'productos.precio'];
     * $filtro_especial = [
     *     'tabla.precio' => "no_es_un_array"
     * ];
     *
     * $resultado = $obj->filtro_especial_sql($columnas_extra, $filtro_especial);
     * // Resultado esperado: Array de error indicando que el filtro especial debe ser un array.
     * ```
     */
    final public function filtro_especial_sql(array $columnas_extra, array $filtro_especial): array|string {
        $filtro_especial_sql = '';
        foreach ($filtro_especial as $campo => $filtro_esp) {
            if (!is_array($filtro_esp)) {
                return $this->error->error(
                    mensaje: "Error filtro debe ser un array filtro_especial[] = array()",
                    data: $filtro_esp,
                    es_final: true
                );
            }

            $filtro_especial_sql = $this->obten_filtro_especial(
                columnas_extra: $columnas_extra,
                filtro_esp: $filtro_esp,
                filtro_especial_sql: $filtro_especial_sql
            );
            if (errores::$error) {
                return $this->error->error(
                    mensaje: "Error filtro",
                    data: $filtro_especial_sql
                );
            }
        }
        return $filtro_especial_sql;
    }



    /**
     * REG
     * Limpia y envuelve en paréntesis cada filtro definido en el objeto recibido.
     *
     * Esta función procesa un objeto de filtros (`$filtros`) utilizando un arreglo de claves
     * (`$keys_data_filter`). Primero, se limpian los filtros llamando al método `limpia_filtros()`
     * para asegurarse de que todas las claves indicadas existan en el objeto y estén inicializadas.
     * Luego, se recorre el arreglo de claves y, para cada clave cuyo valor no sea una cadena vacía,
     * se envuelve el filtro en paréntesis. Si se procesan múltiples filtros no vacíos, se van
     * concatenando utilizando la cláusula "AND".
     *
     * **Flujo de Ejecución:**
     * 1. Se copia el objeto `$filtros` en `$filtros_` y se llama a `limpia_filtros()`, pasando `$filtros_`
     *    y `$keys_data_filter` como parámetros para asegurar que cada clave esté definida.
     * 2. Si ocurre un error durante la limpieza, se retorna un error con los detalles.
     * 3. Se inicializa la variable `$and` como una cadena vacía.
     * 4. Se itera sobre cada clave en `$keys_data_filter`:
     *    - Si el valor correspondiente en `$filtros_` no es una cadena vacía, se actualiza ese valor
     *      envolviéndolo en paréntesis y, si ya se había procesado algún filtro previamente, se antepone
     *      la palabra "AND".
     *    - Después de procesar el primer filtro no vacío, se establece `$and` a " AND " para los siguientes.
     * 5. Finalmente, se retorna el objeto `$filtros_` modificado.
     *
     * **Parámetros:**
     * @param stdClass $filtros Objeto que contiene los filtros. Cada propiedad representa un filtro que se
     *                          aplicará en la consulta SQL.
     * @param array    $keys_data_filter Arreglo de cadenas que contiene los nombres de las propiedades (campos)
     *                                   a procesar dentro del objeto `$filtros`.
     *
     * **Valor de Retorno:**
     * @return stdClass Devuelve el objeto de filtros modificado, en el que cada filtro no vacío ha sido envuelto
     *                  entre paréntesis y, en caso de existir múltiples, concatenado con "AND".
     *
     * **Ejemplos de Uso:**
     *
     * *Ejemplo 1: Filtro único no vacío*
     * ```php
     * $filtros = new stdClass();
     * $filtros->nombre = "Juan";
     * $filtros->edad = "";
     *
     * $keys_data_filter = ['nombre', 'edad'];
     *
     * // Se espera que solo el filtro "nombre" se envuelva en paréntesis.
     * $filtrosFull = $this->filtros_full(filtros: $filtros, keys_data_filter: $keys_data_filter);
     * // Resultado esperado:
     * // $filtrosFull->nombre == " ( Juan ) "
     * // $filtrosFull->edad == ""
     * ```
     *
     * *Ejemplo 2: Múltiples filtros no vacíos*
     * ```php
     * $filtros = new stdClass();
     * $filtros->nombre = "Juan";
     * $filtros->apellido = "Pérez";
     *
     * $keys_data_filter = ['nombre', 'apellido'];
     *
     * // Se espera que ambos filtros se envuelvan en paréntesis y se concatenen con "AND".
     * $filtrosFull = $this->filtros_full(filtros: $filtros, keys_data_filter: $keys_data_filter);
     * // Resultado esperado:
     * // $filtrosFull->nombre   == " ( Juan ) "
     * // $filtrosFull->apellido == " AND ( Pérez ) "
     * ```
     *
     * @throws array Devuelve un array con los detalles del error si ocurre un fallo durante la limpieza de filtros.
     */
    private function filtros_full(stdClass $filtros, array $keys_data_filter): stdClass
    {
        $filtros_ = $filtros;
        $filtros_ = $this->limpia_filtros(filtros: $filtros_, keys_data_filter: $keys_data_filter);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar filtros', data: $filtros_);
        }

        $and = '';
        foreach ($keys_data_filter as $key) {
            if ($filtros_->$key !== '') {
                $filtros_->$key = " $and ( " . $filtros_->$key . ")";
                $and = " AND ";
            }
        }

        return $filtros_;
    }



    /**
     * REG
     * Verifica si todas las claves especificadas en el arreglo de filtros están vacías en el objeto complemento.
     *
     * Este método recorre un arreglo de claves ($keys_data_filter) y, para cada clave, realiza lo siguiente:
     * <ul>
     *   <li>
     *     Comprueba si la clave existe en el objeto <code>$complemento</code>. Si no existe, la inicializa con
     *     una cadena vacía (<code>''</code>).
     *   </li>
     *   <li>
     *     Aplica <code>trim()</code> al valor de la clave en el objeto <code>$complemento</code> y verifica
     *     si el resultado es distinto de una cadena vacía. Si se encuentra al menos un valor no vacío, se
     *     concluye que no todos los filtros están vacíos.
     *   </li>
     * </ul>
     * Si después de recorrer todas las claves, ninguno de los valores contiene información (después de limpiar
     * espacios en blanco), se considera que todos los filtros están vacíos y el método retorna <code>true</code>;
     * de lo contrario, retorna <code>false</code>.
     *
     * @param stdClass $complemento     Objeto que contiene los filtros o parámetros a verificar.
     * @param array    $keys_data_filter Arreglo de claves (strings) que se deben comprobar en el objeto <code>$complemento</code>.
     *
     * @return bool Retorna <code>true</code> si todos los filtros indicados están vacíos; <code>false</code> si al menos
     *              uno de ellos contiene un valor distinto de una cadena vacía.
     *
     * @example
     * <pre>
     * // Ejemplo 1: Todos los filtros están vacíos
     * $complemento = new stdClass();
     * $complemento->nombre = '';
     * $complemento->apellido = '';
     * $keys_data_filter = ['nombre', 'apellido'];
     * $resultado = $this->filtros_vacios($complemento, $keys_data_filter);
     * // $resultado será true, ya que ambos filtros están vacíos.
     *
     * // Ejemplo 2: Al menos un filtro contiene información
     * $complemento = new stdClass();
     * $complemento->nombre = 'Carlos';
     * $complemento->apellido = '';
     * $keys_data_filter = ['nombre', 'apellido'];
     * $resultado = $this->filtros_vacios($complemento, $keys_data_filter);
     * // $resultado será false, pues "nombre" contiene "Carlos".
     * </pre>
     */
    private function filtros_vacios(stdClass $complemento, array $keys_data_filter): bool
    {
        $filtros_vacios = true;
        foreach ($keys_data_filter as $key) {
            if (!isset($complemento->$key)) {
                $complemento->$key = '';
            }
            if (trim($complemento->$key) !== '') {
                $filtros_vacios = false;
                break;
            }
        }
        return $filtros_vacios;
    }



    /**
     * REG
     * Genera los filtros iniciales para la consulta SQL.
     *
     * Este método integra y prepara los diferentes componentes de un filtro SQL a partir de múltiples cadenas de condiciones
     * y cláusulas. El proceso se realiza en tres pasos:
     *
     * 1. **Asignación de datos de filtro:**
     *    Se invoca el método `asigna_data_filtro()` de la clase `\gamboamartin\where\where`, el cual recibe las siguientes
     *    cadenas y parámetros, y devuelve un objeto `stdClass` con las propiedades correspondientes a cada componente del filtro:
     *    - **$diferente_de_sql:** Condición SQL para filtrar registros que sean "diferentes de" un valor dado.
     *    - **$filtro_especial_sql:** Cláusula SQL con condiciones especiales (por ejemplo, comparaciones específicas).
     *    - **$filtro_extra_sql:** Cláusula SQL con filtros adicionales.
     *    - **$filtro_fecha_sql:** Cláusula SQL para condiciones relacionadas con fechas.
     *    - **$filtro_rango_sql:** Cláusula SQL para condiciones de rango (por ejemplo, BETWEEN).
     *    - **$in_sql:** Cláusula SQL para condiciones de inclusión (IN).
     *    - **$not_in_sql:** Cláusula SQL para condiciones de exclusión (NOT IN).
     *    - **$sentencia:** Sentencia SQL base a la cual se integrarán los filtros.
     *    - **$sql_extra:** SQL adicional a integrar.
     *
     * 2. **Limpieza de los filtros:**
     *    Se limpia el objeto resultante usando el método `limpia_filtros()`, que se encarga de recorrer el objeto y
     *    asegurar que todas las claves definidas en `$keys_data_filter` existan y tengan un valor asignado (vacío en caso contrario).
     *
     * 3. **Aplicación de paréntesis:**
     *    Finalmente, se aplica el método `parentesis_filtro()` para envolver en paréntesis cada uno de los filtros definidos,
     *    lo cual puede ser útil para garantizar el correcto orden de evaluación en la consulta SQL final.
     *
     * Si ocurre algún error durante cualquiera de estos pasos, se retorna un array de error con los detalles.
     *
     * @param string $diferente_de_sql   Cadena SQL que define la condición "diferente de".
     *                                   Ejemplo: `"campo <> 'valor'"`.
     * @param string $filtro_especial_sql  Cláusula SQL con condiciones especiales.
     *                                   Ejemplo: `"campo > '10'"`.
     * @param string $filtro_extra_sql     Cláusula SQL con filtros adicionales.
     *                                   Ejemplo: `"campo LIKE '%abc%'"`.
     * @param string $filtro_rango_sql     Cláusula SQL para filtros de rango.
     *                                   Ejemplo: `"campo BETWEEN '100' AND '200'"`.
     * @param string $in_sql               Cláusula SQL para la condición IN.
     *                                   Ejemplo: `"campo IN ('A','B','C')"`.
     * @param array  $keys_data_filter     Array de claves que se utilizarán para identificar y limpiar los filtros en el objeto.
     *                                   Ejemplo: `['filtro_especial', 'filtro_extra', 'filtro_fecha', 'filtro_rango', 'in', 'not_in', 'sentencia', 'sql_extra']`.
     * @param string $not_in_sql           Cláusula SQL para la condición NOT IN.
     *                                   Ejemplo: `"campo NOT IN ('X','Y')"`.
     * @param string $sentencia            Sentencia SQL base que se integrará con los filtros.
     *                                   Ejemplo: `"SELECT * FROM tabla"`.
     * @param string $sql_extra            SQL adicional a integrar en la consulta.
     *                                   Ejemplo: `"ORDER BY campo1 DESC"`.
     * @param string $filtro_fecha_sql     (Opcional) Cláusula SQL para filtros basados en fechas.
     *                                   Por defecto es una cadena vacía.
     *
     * @return array|stdClass Devuelve un objeto `stdClass` con los filtros integrados y listos para usarse en una consulta SQL.
     *                        Si ocurre un error, retorna un array con los detalles del error.
     *
     * @example Ejemplo 1: Generación exitosa de filtros iniciales
     * <pre>
     * $diferente_de_sql   = "campo1 <> 'valor1'";
     * $filtro_especial_sql = "campo2 > '10'";
     * $filtro_extra_sql    = "campo3 LIKE '%abc%'";
     * $filtro_rango_sql    = "campo4 BETWEEN '100' AND '200'";
     * $in_sql              = "campo5 IN ('A','B','C')";
     * $keys_data_filter    = ['filtro_especial','filtro_extra','filtro_fecha','filtro_rango','in','not_in','sentencia','sql_extra'];
     * $not_in_sql          = "campo6 NOT IN ('X','Y')";
     * $sentencia           = "SELECT * FROM tabla";
     * $sql_extra           = "ORDER BY campo1 DESC";
     * $filtro_fecha_sql    = "campo7 = '2023-01-01'";
     *
     * $filtros = $this->genera_filtros_iniciales(
     *      $diferente_de_sql,
     *      $filtro_especial_sql,
     *      $filtro_extra_sql,
     *      $filtro_rango_sql,
     *      $in_sql,
     *      $keys_data_filter,
     *      $not_in_sql,
     *      $sentencia,
     *      $sql_extra,
     *      $filtro_fecha_sql
     * );
     *
     * // Resultado esperado: Un objeto stdClass con las propiedades:
     * //  - filtro_especial: "campo2 > '10'"
     * //  - filtro_extra: "campo3 LIKE '%abc%'"
     * //  - filtro_fecha: "campo7 = '2023-01-01'"
     * //  - filtro_rango: "campo4 BETWEEN '100' AND '200'"
     * //  - in: "campo5 IN ('A','B','C')"
     * //  - not_in: "campo6 NOT IN ('X','Y')"
     * //  - sentencia: "SELECT * FROM tabla"
     * //  - sql_extra: "ORDER BY campo1 DESC"
     * // Cada filtro se encuentra debidamente limpio y, en el caso de las claves especificadas, envuelto en paréntesis.
     * </pre>
     *
     * @example Ejemplo 2: Error al asignar filtros (caso $diferente_de_sql vacío)
     * <pre>
     * $diferente_de_sql = "";
     * // Llamada a la función:
     * $filtros = $this->genera_filtros_iniciales(
     *      $diferente_de_sql,
     *      $filtro_especial_sql,
     *      $filtro_extra_sql,
     *      $filtro_rango_sql,
     *      $in_sql,
     *      $keys_data_filter,
     *      $not_in_sql,
     *      $sentencia,
     *      $sql_extra,
     *      $filtro_fecha_sql
     * );
     *
     * // Resultado esperado: Un array de error indicando "Error al asignar filtros" con los detalles pertinentes.
     * </pre>
     */
    private function genera_filtros_iniciales(
        string $diferente_de_sql,
        string $filtro_especial_sql,
        string $filtro_extra_sql,
        string $filtro_rango_sql,
        string $in_sql,
        array $keys_data_filter,
        string $not_in_sql,
        string $sentencia,
        string $sql_extra,
        string $filtro_fecha_sql = ''
    ): array|stdClass {
        $filtros = (new \gamboamartin\where\where())->asigna_data_filtro(
            diferente_de_sql: $diferente_de_sql,
            filtro_especial_sql: $filtro_especial_sql,
            filtro_extra_sql: $filtro_extra_sql,
            filtro_fecha_sql: $filtro_fecha_sql,
            filtro_rango_sql: $filtro_rango_sql,
            in_sql: $in_sql,
            not_in_sql: $not_in_sql,
            sentencia: $sentencia,
            sql_extra: $sql_extra
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar filtros',
                data: $filtros
            );
        }

        $filtros = $this->limpia_filtros(
            filtros: $filtros,
            keys_data_filter: $keys_data_filter
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al limpiar filtros',
                data: $filtros
            );
        }

        $filtros = $this->parentesis_filtro(
            filtros: $filtros,
            keys_data_filter: $keys_data_filter
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar filtros',
                data: $filtros
            );
        }
        return $filtros;
    }


    /**
     * REG
     * Genera la estructura completa de filtros SQL integrados a partir de diversos componentes.
     *
     * Este método integra y organiza distintos componentes de filtros SQL, tales como:
     * - Filtros base.
     * - Filtros especiales (condiciones adicionales con operadores específicos).
     * - Filtros extra (condiciones concatenadas mediante operadores lógicos).
     * - Filtros de rango (para valores entre dos límites).
     * - Cláusulas IN y NOT IN.
     * - Filtros de fecha.
     * - Condiciones "diferente de" (para excluir ciertos valores).
     *
     * El proceso de generación se realiza en los siguientes pasos:
     *
     * 1. **Validación del Tipo de Filtro:**
     *    Se verifica que el valor de `$tipo_filtro` sea válido mediante el método
     *    `verifica_tipo_filtro()` de la clase `\gamboamartin\where\where`. Esto determina si los
     *    filtros se procesarán para datos numéricos o textos.
     *
     * 2. **Generación de la Sentencia Base:**
     *    Se construye la sentencia base SQL usando `genera_sentencia_base()`, que integra las columnas
     *    extra y el filtro base (`$filtro`) según el tipo especificado.
     *
     * 3. **Generación de Filtros Especiales:**
     *    Se genera la cláusula de filtros especiales a partir del arreglo `$filtro_especial` mediante el método
     *    `filtro_especial_sql()`, el cual procesa condiciones particulares (por ejemplo, "tabla.campo < '10'").
     *
     * 4. **Generación del Filtro de Rango:**
     *    Se procesa el arreglo `$filtro_rango` con el método `filtro_rango_sql()`, generando condiciones del tipo
     *    `BETWEEN 'valor1' AND 'valor2'`.
     *
     * 5. **Generación del Filtro Extra:**
     *    Se construye la cláusula de filtros extra con el método `filtro_extra_sql()`, que agrupa condiciones
     *    adicionales para la consulta.
     *
     * 6. **Generación de la Cláusula NOT IN:**
     *    Se genera la condición NOT IN a partir del arreglo `$not_in` utilizando el método `genera_not_in_sql()`.
     *
     * 7. **Generación de la Cláusula IN Normalizada:**
     *    Se genera la cláusula IN a partir del arreglo `$in` llamando a `genera_in_sql_normalizado()`.
     *
     * 8. **Generación del Filtro de Fecha:**
     *    Se procesa el arreglo `$filtro_fecha` mediante `filtro_fecha()` para obtener condiciones basadas en fechas.
     *
     * 9. **Generación del Filtro "Diferente de":**
     *    Se construye la condición para excluir ciertos valores mediante `diferente_de_sql()`, a partir del arreglo
     *    `$diferente_de`.
     *
     * 10. **Integración de Todos los Filtros:**
     *     Se integran todos los componentes anteriores en un único objeto de filtros utilizando el método
     *     `genera_filtros_iniciales()`, el cual se encarga de limpiar, agrupar y ajustar cada parte (por ejemplo, encapsulándola
     *     en paréntesis donde sea necesario).
     *
     * Si en cualquiera de los pasos ocurre un error, se retorna un array con los detalles del error generado.
     *
     * @param array  $columnas_extra    Arreglo asociativo con columnas extras definidas en el modelo.
     *                                  Ejemplo: `['campo1' => 'tabla.campo1', 'campo2' => 'tabla.campo2']`.
     * @param array  $diferente_de      Arreglo de condiciones "diferente de" para excluir registros.
     *                                  Ejemplo: `['tabla.campo1' => 'valor_no_permitido']`.
     * @param array  $filtro            Arreglo con el filtro base.
     *                                  Ejemplo: `['tabla.campo2' => 'valor']`.
     * @param array  $filtro_especial   Arreglo con condiciones especiales adicionales.
     *                                  Ejemplo:
     *                                  ```php
     *                                  [
     *                                      'tabla.campo3' => [
     *                                          'operador' => '<',
     *                                          'valor' => '10'
     *                                      ]
     *                                  ]
     *                                  ```
     * @param array  $filtro_extra      Arreglo con condiciones extra concatenables.
     *                                  Ejemplo:
     *                                  ```php
     *                                  [
     *                                      'tabla.campo4' => [
     *                                          'operador' => '>',
     *                                          'valor' => '100',
     *                                          'comparacion' => 'AND'
     *                                      ]
     *                                  ]
     *                                  ```
     * @param array  $filtro_rango      Arreglo con condiciones de rango.
     *                                  Ejemplo:
     *                                  ```php
     *                                  [
     *                                      'tabla.campo5' => [
     *                                          'valor1' => '1',
     *                                          'valor2' => '5'
     *                                      ]
     *                                  ]
     *                                  ```
     * @param array  $in                Arreglo para la cláusula IN.
     *                                  Ejemplo: `['llave' => 'tabla.campo6', 'values' => ['A','B','C']]`.
     * @param array  $keys_data_filter  Array de claves que se utilizarán para limpiar y agrupar los filtros.
     *                                  Ejemplo: `['filtro_especial','filtro_extra','filtro_fecha','filtro_rango','in','not_in','sentencia','sql_extra']`.
     * @param array  $not_in            Arreglo para la cláusula NOT IN.
     *                                  Ejemplo: `['llave' => 'tabla.campo7', 'values' => ['X','Y']]`.
     * @param string $sql_extra         Cadena SQL extra a concatenar (por ejemplo, para ordenamientos).
     *                                  Ejemplo: `"ORDER BY tabla.campo8 DESC"`.
     * @param string $tipo_filtro       Tipo de filtro a aplicar; valores típicos son `'numeros'` o `'textos'`.
     * @param array  $filtro_fecha      (Opcional) Arreglo con condiciones basadas en fechas.
     *                                  Ejemplo:
     *                                  ```php
     *                                  [
     *                                      'tabla.campo9' => [
     *                                          'valor' => '2023-01-01',
     *                                          'operador' => '>='
     *                                      ]
     *                                  ]
     *                                  ```
     *
     * @return array|stdClass Devuelve un objeto `stdClass` con las siguientes propiedades:
     *                         - `filtro_especial`: Condición SQL de filtros especiales.
     *                         - `filtro_extra`: Cláusula SQL de filtros extra.
     *                         - `filtro_fecha`: Condición SQL de filtros basados en fecha.
     *                         - `filtro_rango`: Cláusula SQL de filtros de rango.
     *                         - `in`: Cláusula SQL IN.
     *                         - `not_in`: Cláusula SQL NOT IN.
     *                         - `sentencia`: La sentencia SQL base generada.
     *                         - `sql_extra`: La cadena SQL extra.
     *
     *                         En caso de error, retorna un array con detalles del error.
     *
     * @example Ejemplo de uso exitoso:
     * <pre>
     * $columnas_extra = [
     *     'campo1' => 'tabla.campo1',
     *     'campo2' => 'tabla.campo2'
     * ];
     * $diferente_de = ['tabla.campo1' => 'valor_no_permitido'];
     * $filtro = ['tabla.campo2' => 'valor'];
     * $filtro_especial = [
     *     'tabla.campo3' => [
     *         'operador' => '<',
     *         'valor' => '10'
     *     ]
     * ];
     * $filtro_extra = [
     *     'tabla.campo4' => [
     *         'operador' => '>',
     *         'valor' => '100',
     *         'comparacion' => 'AND'
     *     ]
     * ];
     * $filtro_rango = [
     *     'tabla.campo5' => [
     *         'valor1' => '1',
     *         'valor2' => '5'
     *     ]
     * ];
     * $in = ['llave' => 'tabla.campo6', 'values' => ['A','B','C']];
     * $keys_data_filter = ['filtro_especial','filtro_extra','filtro_fecha','filtro_rango','in','not_in','sentencia','sql_extra'];
     * $not_in = ['llave' => 'tabla.campo7', 'values' => ['X','Y']];
     * $sql_extra = "ORDER BY tabla.campo8 DESC";
     * $tipo_filtro = 'numeros';
     * $filtro_fecha = [
     *     'tabla.campo9' => [
     *         'valor' => '2023-01-01',
     *         'operador' => '>='
     *     ]
     * ];
     *
     * $filtros = $this->genera_filtros_iniciales(
     *      "tabla.campo1 <> 'valor_no_permitido'",
     *      "tabla.campo3 < '10'",
     *      "tabla.campo4 > '100'",
     *      "tabla.campo5 BETWEEN '1' AND '5'",
     *      "tabla.campo6 IN ('A','B','C')",
     *      $keys_data_filter,
     *      "tabla.campo7 NOT IN ('X','Y')",
     *      "SELECT * FROM tabla",
     *      $sql_extra,
     *      "tabla.campo9 >= '2023-01-01'"
     * );
     *
     * // Resultado esperado:
     * // stdClass {
     * //     filtro_especial  => "tabla.campo3 < '10'",
     * //     filtro_extra     => "tabla.campo4 > '100'",
     * //     filtro_fecha     => "tabla.campo9 >= '2023-01-01'",
     * //     filtro_rango     => "tabla.campo5 BETWEEN '1' AND '5'",
     * //     in               => "tabla.campo6 IN ('A','B','C')",
     * //     not_in           => "tabla.campo7 NOT IN ('X','Y')",
     * //     sentencia        => "SELECT * FROM tabla",
     * //     sql_extra        => "ORDER BY tabla.campo8 DESC"
     * // }
     * </pre>
     *
     * @example Ejemplo de error: Fallo en la asignación de filtros
     * <pre>
     * $diferente_de_sql = ""; // Cadena vacía no es válida
     * $filtros = $this->genera_filtros_iniciales(
     *      $diferente_de_sql,
     *      $filtro_especial_sql,
     *      $filtro_extra_sql,
     *      $filtro_rango_sql,
     *      $in_sql,
     *      $keys_data_filter,
     *      $not_in_sql,
     *      $sentencia,
     *      $sql_extra,
     *      $filtro_fecha_sql
     * );
     *
     * // Resultado esperado: Array de error con un mensaje indicando "Error al asignar filtros" junto con los datos relevantes.
     * </pre>
     */
    private function genera_filtros_sql(
        array $columnas_extra,
        array $diferente_de,
        array $filtro,
        array $filtro_especial,
        array $filtro_extra,
        array $filtro_rango,
        array $in,
        array $keys_data_filter,
        array $not_in,
        string $sql_extra,
        string $tipo_filtro,
        array $filtro_fecha = array()
    ): array|stdClass {
        $verifica_tf = (new \gamboamartin\where\where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar tipo_filtro',
                data: $verifica_tf
            );
        }
        $sentencia = (new \gamboamartin\where\where())->genera_sentencia_base(
            columnas_extra: $columnas_extra,
            filtro: $filtro,
            tipo_filtro: $tipo_filtro
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar sentencia',
                data: $sentencia
            );
        }

        $filtro_especial_sql = $this->filtro_especial_sql(
            columnas_extra: $columnas_extra,
            filtro_especial: $filtro_especial
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar filtro especial',
                data: $filtro_especial_sql
            );
        }
        $filtro_rango_sql = (new \gamboamartin\where\where())->filtro_rango_sql(filtro_rango: $filtro_rango);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar filtro de rango',
                data: $filtro_rango_sql
            );
        }
        $filtro_extra_sql = (new \gamboamartin\where\where())->filtro_extra_sql(filtro_extra: $filtro_extra);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar filtro extra',
                data: $filtro_extra_sql
            );
        }

        $not_in_sql = (new \gamboamartin\where\where())->genera_not_in_sql(not_in: $not_in);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar cláusula NOT IN',
                data: $not_in_sql
            );
        }

        $in_sql = $this->genera_in_sql_normalizado(in: $in);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar cláusula IN',
                data: $in_sql
            );
        }

        $filtro_fecha_sql = (new \gamboamartin\where\where())->filtro_fecha(filtro_fecha: $filtro_fecha);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar filtro de fecha',
                data: $filtro_fecha_sql
            );
        }

        $diferente_de_sql = $this->diferente_de_sql(diferente_de: $diferente_de);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar condición "diferente de"',
                data: $diferente_de_sql
            );
        }

        $filtros = $this->genera_filtros_iniciales(
            diferente_de_sql: $diferente_de_sql,
            filtro_especial_sql: $filtro_especial_sql,
            filtro_extra_sql: $filtro_extra_sql,
            filtro_rango_sql: $filtro_rango_sql,
            in_sql: $in_sql,
            keys_data_filter: $keys_data_filter,
            not_in_sql: $not_in_sql,
            sentencia: $sentencia,
            sql_extra: $sql_extra,
            filtro_fecha_sql: $filtro_fecha_sql
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar filtros SQL completos',
                data: $filtros
            );
        }

        return $filtros;
    }


    /**
     * REG
     * Genera una cláusula SQL IN a partir de un arreglo de entrada.
     *
     * Esta función recibe como parámetro un arreglo asociativo `$in` que debe contener las siguientes claves:
     * - **llave**: (string) El nombre de la columna (o campo) sobre la cual se aplicará la cláusula SQL IN.
     * - **values**: (array) Un arreglo de valores que se incluirán en la cláusula IN.
     *
     * El método realiza los siguientes pasos:
     *
     * 1. **Validación de claves obligatorias**:
     *    Utiliza el método `valida_existencia_keys()` de la instancia `$this->validacion` para asegurar que el arreglo `$in`
     *    contenga las claves "llave" y "values". Si alguna de estas claves no existe, se retorna un error.
     *
     * 2. **Verificación del tipo de "values"**:
     *    Comprueba que el valor asociado a la clave "values" sea un arreglo. Si no lo es, retorna un error indicando que
     *    "values debe ser un array".
     *
     * 3. **Generación de datos para la cláusula IN**:
     *    Llama al método `data_in()` (de la misma clase o instancia de where) para obtener un objeto `stdClass` que contenga:
     *      - `llave`: El nombre del campo (posiblemente procesado o modificado).
     *      - `values`: El arreglo de valores a utilizar.
     *
     * 4. **Construcción de la cláusula IN**:
     *    Con el objeto obtenido, se invoca el método `in_sql()` (de la clase `sql` en el namespace `gamboamartin\src\sql`)
     *    para generar la cadena SQL en el formato:
     *       `"campo IN ('valor1','valor2',...)"`.
     *
     * Si en alguno de los pasos se produce un error (por ejemplo, claves faltantes, "values" no es un array o error en la
     * generación de la cadena SQL), la función retorna un array con la información del error.
     *
     * @param array $in Arreglo asociativo que debe tener la siguiente estructura:
     * <pre>
     * [
     *   'llave'  => 'nombre_de_la_columna',
     *   'values' => ['valor1', 'valor2', 'valor3']
     * ]
     * </pre>
     *
     * @return array|string Devuelve una cadena SQL que representa la cláusula IN, por ejemplo:
     * <pre>
     * "categoria_id IN ('10','20','30')"
     * </pre>
     * o bien, un array con la información del error si falla alguna validación.
     *
     * @example Ejemplo 1: Uso correcto
     * <pre>
     * $in = [
     *     'llave'  => 'categoria_id',
     *     'values' => ['10', '20', '30']
     * ];
     *
     * $resultado = $this->genera_in($in);
     * // Resultado esperado:
     * // "categoria_id IN ('10','20','30')"
     * </pre>
     *
     * @example Ejemplo 2: Error por "values" no siendo un array
     * <pre>
     * $in = [
     *     'llave'  => 'categoria_id',
     *     'values' => '10,20,30'
     * ];
     *
     * $resultado = $this->genera_in($in);
     * // Resultado esperado:
     * // Array de error indicando "Error values debe ser un array"
     * </pre>
     *
     * @example Ejemplo 3: Error por falta de la clave "llave"
     * <pre>
     * $in = [
     *     'values' => ['10', '20', '30']
     * ];
     *
     * $resultado = $this->genera_in($in);
     * // Resultado esperado:
     * // Array de error indicando "Error al validar not_in" debido a que falta la clave 'llave'
     * </pre>
     */
    private function genera_in(array $in): array|string
    {
        $keys = array('llave','values');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar not_in', data: $valida);
        }
        $values = $in['values'];

        if(!is_array($values)){
            return $this->error->error(mensaje: 'Error values debe ser un array', data: $values, es_final: true);
        }

        $data_in = (new \gamboamartin\where\where())->data_in(in: $in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data in', data: $data_in);
        }

        $in_sql = $this->in_sql(llave:  $data_in->llave, values: $data_in->values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $in_sql);
        }
        return $in_sql;
    }


    /**
     * REG
     * Genera una cadena SQL normalizada para la cláusula IN a partir de un arreglo de entrada.
     *
     * Este método construye una cadena SQL que se utiliza para formar una cláusula IN en una consulta SQL.
     * Para ello, el arreglo de entrada `$in` debe contener las claves obligatorias:
     *
     * - **llave**: (string) El nombre de la columna sobre la que se aplicará la cláusula IN.
     * - **values**: (array) Un arreglo de valores que se incluirán en la cláusula.
     *
     * El proceso de generación se realiza en los siguientes pasos:
     *
     * 1. Se inicializa la variable `$in_sql` como una cadena vacía.
     *
     * 2. Si el arreglo `$in` contiene uno o más elementos (`count($in) > 0`):
     *    - Se define un arreglo `$keys` con las claves `'llave'` y `'values'`.
     *    - Se valida que estas claves existan en `$in` mediante el método `valida_existencia_keys()`.
     *      Si la validación falla, se retorna un array de error con la información correspondiente.
     *    - Se extrae el valor asociado a la clave `'values'` y se verifica que sea un arreglo; de lo contrario,
     *      se retorna un error indicando que "values debe ser un array".
     *    - Se invoca el método `genera_in()` pasando el arreglo `$in` para generar la parte inicial de la cadena SQL IN.
     *      Si ocurre un error, se retorna el error correspondiente.
     *    - La cadena resultante se limpia utilizando el método `limpia_espacios_dobles()` de la clase `sql`,
     *      con el fin de eliminar espacios dobles o redundantes.
     *    - Se aplica un reemplazo adicional para corregir posibles duplicados de paréntesis, transformando
     *      cualquier aparición de la subcadena `"( ("` en `"(("`.
     *
     * 3. Independientemente del contenido de `$in`, se realiza una limpieza final de la cadena `$in_sql` utilizando
     *    nuevamente `limpia_espacios_dobles()`, y se vuelve a aplicar el reemplazo de `"( ("` por `"(("`.
     *
     * 4. Se retorna la cadena SQL final normalizada para la cláusula IN. En caso de error en cualquiera de los
     *    pasos, se retorna un array con los detalles del error.
     *
     * @param array $in Arreglo asociativo que debe tener la siguiente estructura:
     * <pre>
     * [
     *     'llave'  => 'nombre_de_la_columna',
     *     'values' => ['valor1', 'valor2', 'valor3', ...]
     * ]
     * </pre>
     *
     * @return array|string Devuelve una cadena SQL que representa la cláusula IN, por ejemplo:
     * <pre>
     * "categoria_id IN ('10','20','30')"
     * </pre>
     * o un array con la información del error en caso de que alguna validación falle.
     *
     * @example Ejemplo 1: Uso correcto con datos válidos
     * <pre>
     * $in = [
     *     'llave'  => 'categoria_id',
     *     'values' => ['10', '20', '30']
     * ];
     *
     * $resultado = $this->genera_in_sql($in);
     * // Resultado esperado:
     * // "categoria_id IN ('10','20','30')"
     * </pre>
     *
     * @example Ejemplo 2: Error porque "values" no es un array
     * <pre>
     * $in = [
     *     'llave'  => 'categoria_id',
     *     'values' => "10,20,30"  // Error: "values" debe ser un array
     * ];
     *
     * $resultado = $this->genera_in_sql($in);
     * // Resultado esperado:
     * // Array de error indicando "Error values debe ser un array"
     * </pre>
     *
     * @example Ejemplo 3: Arreglo vacío
     * <pre>
     * $in = [];
     *
     * $resultado = $this->genera_in_sql($in);
     * // Resultado esperado: Cadena vacía ("")
     * </pre>
     */
    private function genera_in_sql(array $in): array|string
    {
        $in_sql = '';
        if (count($in) > 0) {
            $keys = array('llave', 'values');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $in);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar in', data: $valida);
            }
            $values = $in['values'];

            if (!is_array($values)) {
                return $this->error->error(mensaje: 'Error values debe ser un array', data: $values, es_final: true);
            }
            $in_sql = $this->genera_in(in: $in);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar sql', data: $in_sql);
            }
            $in_sql = (new sql())->limpia_espacios_dobles(txt: $in_sql);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar sql', data: $in_sql);
            }

            $in_sql = str_replace('( (', '((', $in_sql);
        }
        $in_sql = (new sql())->limpia_espacios_dobles(txt: $in_sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar sql', data: $in_sql);
        }

        return str_replace('( (', '((', $in_sql);
    }


    /**
     * REG
     * Genera y normaliza la cláusula SQL para la sentencia IN.
     *
     * Este método genera una cadena SQL para una cláusula IN a partir de un arreglo asociativo que contiene
     * la información necesaria y luego aplica una limpieza para eliminar espacios redundantes y corregir la
     * estructura de paréntesis. Es decir, llama al método {@see genera_in_sql()} para obtener la cadena SQL
     * inicial y, posteriormente, utiliza el método {@see sql::limpia_espacios_dobles()} para asegurar que la
     * cadena no contenga espacios duplicados. Finalmente, se reemplaza cualquier aparición de la subcadena
     * "( (" por "((" para garantizar que la sintaxis SQL sea correcta.
     *
     * @param array $in Arreglo asociativo con la siguiente estructura:
     * <pre>
     * [
     *     'llave'  => 'nombre_de_la_columna', // (string) Nombre de la columna para la cláusula IN.
     *     'values' => ['valor1', 'valor2', 'valor3', ...] // (array) Array de valores a incluir en la cláusula.
     * ]
     * </pre>
     *
     * @return string|array Retorna una cadena SQL normalizada que representa la cláusula IN, por ejemplo:
     * <pre>
     * "categoria_id IN ('10','20','30')"
     * </pre>
     * o, en caso de error, un array con la información detallada del error.
     *
     * @example Ejemplo 1: Uso correcto con datos válidos
     * <pre>
     * $in = [
     *     'llave'  => 'categoria_id',
     *     'values' => ['10', '20', '30']
     * ];
     *
     * $resultado = $this->genera_in_sql_normalizado($in);
     * // Resultado esperado:
     * // "categoria_id IN ('10','20','30')"
     * </pre>
     *
     * @example Ejemplo 2: Uso con arreglo vacío
     * <pre>
     * $in = [];
     *
     * $resultado = $this->genera_in_sql_normalizado($in);
     * // Resultado esperado: Cadena vacía ("")
     * </pre>
     *
     * @example Ejemplo 3: Error debido a que "values" no es un array
     * <pre>
     * $in = [
     *     'llave'  => 'categoria_id',
     *     'values' => "10,20,30" // Error: "values" debe ser un array
     * ];
     *
     * $resultado = $this->genera_in_sql_normalizado($in);
     * // Resultado esperado: Array de error con el mensaje "Error values debe ser un array"
     * </pre>
     */
    private function genera_in_sql_normalizado(array $in): string|array
    {
        $in_sql = $this->genera_in_sql(in: $in);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $in_sql);
        }

        $in_sql = (new sql())->limpia_espacios_dobles(txt: $in_sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar in_sql', data: $in_sql);
        }
        return str_replace('( (', '((', $in_sql);
    }



    /**
     * REG
     * Genera una cláusula SQL "IN" a partir de una llave (nombre de columna) y un arreglo de valores.
     *
     * Este método realiza las siguientes operaciones:
     * - Recorta la llave ($llave) para eliminar espacios en blanco y verifica que no esté vacía.
     * - Utiliza el método `values_sql_in` de la clase `\gamboamartin\where\where` para generar una cadena SQL
     *   que represente los valores a incluir en la cláusula IN. La cadena resultante contendrá los valores
     *   formateados (por ejemplo, `'10','20','30'`).
     * - Valida la coherencia entre la llave y la cadena de valores generada mediante el método `valida_in`
     *   de la clase `sql`. Esto asegura que, si la llave tiene contenido, la cadena de valores también lo tenga,
     *   y viceversa.
     * - Finalmente, construye la cláusula SQL IN utilizando el método `in` de la clase `\gamboamartin\src\sql`.
     *
     * En cada paso, si se produce un error (por ejemplo, si la llave está vacía o la validación falla), se retorna
     * un array de error con información detallada del fallo.
     *
     * @param string $llave  El nombre de la columna sobre la cual se aplicará la cláusula IN. Debe ser una cadena no vacía.
     * @param array  $values Un arreglo de valores que se incluirán en la cláusula IN.
     *
     * @return array|string Devuelve la cláusula SQL IN generada, por ejemplo:
     *                      "columna IN ('valor1','valor2','valor3')".
     *                      En caso de error, retorna un array con detalles del error.
     *
     * @example
     * // Ejemplo 1: Uso exitoso
     * $llave = "categoria_id";
     * $values = ["10", "20", "30"];
     * $in_sql = $obj->in_sql($llave, $values);
     * // Resultado esperado:
     * // "categoria_id IN ('10','20','30')"
     *
     * @example
     * // Ejemplo 2: Error al pasar una llave vacía
     * $llave = "";
     * $values = ["10", "20"];
     * $in_sql = $obj->in_sql($llave, $values);
     * // Resultado esperado: Array de error con mensaje "Error la llave esta vacia"
     *
     * @example
     * // Ejemplo 3: Error en la generación de la cadena de valores
     * $llave = "categoria_id";
     * $values = []; // Arreglo vacío
     * $in_sql = $obj->in_sql($llave, $values);
     * // Resultado esperado: Array de error indicando "Error al generar sql" o "Error al validar in"
     *
     * @see \gamboamartin\where\where::values_sql_in() Para la generación de la cadena de valores.
     * @see \gamboamartin\src\sql::valida_in() Para la validación de la coherencia entre la llave y la cadena de valores.
     * @see \gamboamartin\src\sql::in() Para la construcción final de la cláusula SQL IN.
     */
    final public function in_sql(string $llave, array $values): array|string
    {
        $llave = trim($llave);
        if ($llave === '') {
            return $this->error->error(mensaje: 'Error la llave esta vacia', data: $llave, es_final: true);
        }

        $values_sql = (new \gamboamartin\where\where())->values_sql_in(values: $values);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $values_sql);
        }

        $valida = (new sql())->valida_in(llave: $llave, values_sql: $values_sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar in', data: $valida);
        }

        $in_sql = (new \gamboamartin\src\sql())->in(llave: $llave, values_sql: $values_sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $in_sql);
        }

        return $in_sql;
    }


    /**
     * REG
     * Inicializa los parámetros SQL del complemento.
     *
     * Este método se encarga de procesar y ajustar el objeto complementario `$complemento` para que incluya
     * la propiedad `params` necesaria para la generación de consultas SQL. El proceso se realiza en dos pasos:
     *
     * 1. Se llama al método `where_filtro()`, el cual procesa el objeto `$complemento` utilizando los campos
     *    definidos en `$keys_data_filter` para construir y validar la cláusula WHERE. Si ocurre algún error durante
     *    este proceso, se retorna un array con la información del error.
     *
     * 2. Se crea una instancia de la clase `inicializacion` y se invoca el método `ajusta_params()`, que inicializa
     *    o ajusta la propiedad `params` del complemento (por ejemplo, definiendo los valores predeterminados para
     *    `offset`, `group_by`, `order` y `limit`). Si se produce algún error en este paso, se retorna un array con
     *    el error.
     *
     * Al finalizar, se retorna el objeto `$complemento` actualizado con la propiedad `params` adecuadamente
     * inicializada, junto con cualquier otra modificación realizada por `where_filtro()`.
     *
     * @param stdClass $complemento Objeto complementario que contiene la información para construir la consulta SQL.
     *                              Este objeto puede incluir una cláusula WHERE y otros datos necesarios para la consulta.
     * @param array    $keys_data_filter Array de claves que definen los campos de filtro a utilizar en la cláusula WHERE.
     *                                   Estos keys se utilizan para validar y ajustar la cláusula mediante el método `where_filtro()`.
     *
     * @return stdClass|array Devuelve el objeto `$complemento` actualizado con la propiedad `params` inicializada
     *                        si el proceso es exitoso; en caso de error, retorna un array con los detalles del error.
     *
     * @example Ejemplo 1: Complemento sin cláusula WHERE previa
     * <pre>
     * // Se crea un objeto complemento vacío (sin la propiedad "where")
     * $complemento = new stdClass();
     *
     * // Se define un array de keys para los filtros (por ejemplo, 'nombre', 'edad', 'email')
     * $keys_data_filter = ['nombre', 'edad', 'email'];
     *
     * // Se invoca el método para inicializar los parámetros SQL
     * $complemento_actualizado = $this->init_params_sql($complemento, $keys_data_filter);
     *
     * // Resultado esperado:
     * // $complemento_actualizado es un objeto stdClass que tendrá:
     * //   - where: "" (cadena vacía, ya que no se definió ningún filtro)
     * //   - params: un objeto stdClass con:
     * //         offset   => ""
     * //         group_by => ""
     * //         order    => ""
     * //         limit    => ""
     * </pre>
     *
     * @example Ejemplo 2: Complemento con cláusula WHERE definida
     * <pre>
     * // Se crea un objeto complemento con una cláusula WHERE inicial
     * $complemento = new stdClass();
     * $complemento->where = "WHERE status = 'activo'";
     *
     * // Array de keys para filtros; en este caso, se espera que 'status' sea el único filtro relevante
     * $keys_data_filter = ['status'];
     *
     * // Se invoca el método para ajustar los parámetros SQL
     * $complemento_actualizado = $this->init_params_sql($complemento, $keys_data_filter);
     *
     * // Resultado esperado:
     * // $complemento_actualizado es un objeto stdClass con:
     * //   - where: " WHERE status = 'activo' " (se añaden espacios adicionales)
     * //   - params: un objeto stdClass con:
     * //         offset   => ""
     * //         group_by => ""
     * //         order    => ""
     * //         limit    => ""
     * </pre>
     *
     * @example Ejemplo 3: Error en la inicialización
     * <pre>
     * // Supongamos que se define un array de keys de filtro que no es válido para el objeto complemento
     * $complemento = new stdClass();
     * $keys_data_filter = ['campo_invalido'];
     *
     * // Al invocar el método, se producirá un error en la validación de la cláusula WHERE
     * $resultado = $this->init_params_sql($complemento, $keys_data_filter);
     *
     * // Resultado esperado (en caso de error):
     * // [
     * //     'error'   => 1,
     * //     'mensaje' => "Error ajustar where" (o "Error al inicializar params"),
     * //     'data'    => (detalles del objeto complemento)
     * // ]
     * </pre>
     *
     * @version 1.0.0
     */
    final public function init_params_sql(stdClass $complemento, array $keys_data_filter): array|stdClass
    {
        // Se procesa el complemento para ajustar la cláusula WHERE según los filtros definidos
        $complemento_w = $this->where_filtro(complemento: $complemento, key_data_filter: $keys_data_filter);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error ajustar where', data: $complemento_w);
        }

        // Se inicializan o ajustan los parámetros del complemento (offset, group_by, order, limit)
        $complemento_r = (new inicializacion())->ajusta_params(complemento: $complemento_w);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar params', data: $complemento_r);
        }
        return $complemento_r;
    }




    /**
     * REG
     * Limpia y organiza los filtros proporcionados.
     *
     * Este método recibe un objeto `stdClass` que contiene varios filtros (por ejemplo, condiciones
     * SQL para una consulta) y un array de claves que indican cuáles propiedades del objeto deben
     * considerarse para la generación de la consulta. La función realiza las siguientes operaciones:
     *
     * 1. **Recorrido y validación de claves:**
     *    - Para cada clave en `$keys_data_filter`, se elimina cualquier espacio en blanco con `trim()`.
     *    - Si alguna clave resulta vacía, se retorna un error indicando "Error el key esta vacio" junto con
     *      el array de claves.
     *    - Si el objeto `$filtros` no contiene la propiedad correspondiente a la clave, se inicializa esa
     *      propiedad en una cadena vacía.
     *
     * 2. **Limpieza de valores:**
     *    - Luego, se recorre nuevamente el array de claves y se aplica `trim()` a cada propiedad del objeto
     *      `$filtros` para eliminar espacios en blanco al inicio o final de los valores.
     *
     * El resultado es un objeto `$filtros` en el cual cada propiedad definida en `$keys_data_filter` contiene
     * un valor limpio (sin espacios redundantes) o una cadena vacía si no se había definido previamente.
     *
     * @param stdClass $filtros         Objeto que contiene los filtros (por ejemplo, condiciones SQL) a limpiar.
     * @param array    $keys_data_filter Array de claves (strings) que indican qué propiedades del objeto `$filtros`
     *                                   deben ser procesadas y limpiadas.
     *
     * @return stdClass|array           Devuelve el objeto `$filtros` modificado con las propiedades especificadas
     *                                   limpias y organizadas. En caso de error (por ejemplo, si alguna clave es vacía),
     *                                   retorna un array con la información del error.
     *
     * @example Ejemplo 1: Filtros definidos y limpieza exitosa
     * <pre>
     * // Supongamos que se tiene el siguiente objeto de filtros:
     * $filtros = new stdClass();
     * $filtros->nombre   = "  Juan Pérez  ";
     * $filtros->apellido = "  López ";
     *
     * // Y un array de claves que se esperan en el objeto:
     * $keys_data_filter = ["nombre", "apellido", "email"];
     *
     * // Al llamar a la función:
     * $filtros_limpios = $this->limpia_filtros($filtros, $keys_data_filter);
     *
     * // El resultado será un objeto stdClass con:
     * // $filtros_limpios->nombre   == "Juan Pérez"   (espacios recortados)
     * // $filtros_limpios->apellido == "López"        (espacios recortados)
     * // $filtros_limpios->email    == ""             (inicializado porque no existía)
     * </pre>
     *
     * @example Ejemplo 2: Error por clave vacía en el array de claves
     * <pre>
     * // Si se proporciona un array de claves que contiene un elemento vacío:
     * $keys_data_filter = ["nombre", "", "email"];
     *
     * // La función retornará un array de error similar a:
     * // [
     * //    "error" => 1,
     * //    "mensaje" => "Error el key esta vacio",
     * //    "data" => ["nombre", "", "email"],
     * //    "es_final" => true
     * // ]
     * </pre>
     */
    final public function limpia_filtros(stdClass $filtros, array $keys_data_filter): stdClass|array
    {
        foreach($keys_data_filter as $key){
            $key = trim($key);
            if($key === ''){
                return $this->error->error(
                    mensaje: 'Error el key esta vacio',
                    data: $keys_data_filter,
                    es_final: true
                );
            }
            if(!isset($filtros->$key)){
                $filtros->$key = '';
            }
        }
        foreach($keys_data_filter as $key){
            $filtros->$key = trim($filtros->$key);
        }

        return $filtros;
    }


    /**
     * REG
     * Genera la parte SQL para un filtro especial basado en un campo dado, utilizando información adicional
     * de columnas extra y un arreglo de filtro específico.
     *
     * Esta función realiza los siguientes pasos:
     *
     * 1. Recibe el nombre del campo ($campo), lo recorta (elimina espacios en blanco al inicio y al final)
     *    y lo utiliza como base para la construcción del filtro.
     *
     * 2. Valida la estructura del filtro especial mediante el método
     *    {@see \gamboamartin\administrador\modelado\validaciones::valida_data_filtro_especial()},
     *    pasando el campo y el arreglo de filtro. Si la validación falla, se retorna un error.
     *
     * 3. Verifica que el arreglo de filtro, en la posición correspondiente al campo, contenga la clave
     *    'valor' utilizando el método {@see \gamboamartin\validacion\validacion::valida_existencia_keys()}.
     *    Si la clave 'valor' no está presente, se retorna un error.
     *
     * 4. Define una variable auxiliar ($campo_filtro) que guarda el valor original del campo.
     *
     * 5. Llama al método {@see \gamboamartin\where\where::campo_filtro_especial()} de la clase `where`
     *    (del namespace `\gamboamartin\where\where`) para obtener el nombre del campo a utilizar en el filtro especial,
     *    usando las columnas extra proporcionadas. Si ocurre un error en este paso, se retorna un error.
     *
     * 6. Con el campo modificado y el valor original, se invoca el método
     *    {@see \gamboamartin\where\where::data_sql()} para generar la instrucción SQL correspondiente al filtro especial,
     *    pasando el campo resultante, el campo original ($campo_filtro) y el arreglo de filtro. Si ocurre algún error,
     *    se retorna un error.
     *
     * 7. Finalmente, retorna la cadena SQL generada que representa el filtro especial.
     *
     * @param string $campo          El nombre del campo sobre el cual se va a aplicar el filtro especial.
     *                               Se recomienda que sea el nombre de una columna de la base de datos.
     * @param array  $columnas_extra Array asociativo que contiene columnas adicionales (por ejemplo, subqueries o alias)
     *                               que pueden ser necesarias para formar el filtro. Por ejemplo:
     *                               ```php
     *                               [
     *                                   'precio' => 'productos.precio'
     *                               ]
     *                               ```
     * @param array  $filtro         Arreglo que contiene los detalles del filtro especial para el campo.
     *                               Debe incluir al menos la clave 'valor' y puede incluir otros parámetros como 'operador'.
     *                               Ejemplo:
     *                               ```php
     *                               [
     *                                   'precio' => [
     *                                       'operador' => '>',
     *                                       'valor' => '100',
     *                                       // Opcionalmente, 'comparacion' => 'AND' u otra comparación.
     *                                   ]
     *                               ]
     *                               ```
     *
     * @return array|string          Devuelve una cadena SQL que representa el filtro especial generado
     *                               si la operación es exitosa; de lo contrario, retorna un array con los detalles del error.
     *
     * @example Ejemplo de uso exitoso:
     * ```php
     * // Supongamos que tenemos:
     * $_SESSION['usuario_id'] = 10; // Aunque este método no utiliza directamente la sesión, otros métodos lo hacen.
     *
     * $campo = 'precio';
     * $columnas_extra = ['precio' => 'productos.precio'];
     * $filtro = [
     *     'precio' => [
     *         'operador' => '>',
     *         'valor' => '100'
     *     ]
     * ];
     *
     * // Se invoca el método maqueta_filtro_especial:
     * $sql_filtro = $obj->maqueta_filtro_especial($campo, $columnas_extra, $filtro);
     *
     * // Supongamos que la función campo_filtro_especial() retorna "productos.precio" y data_sql() construye:
     * // "productos.precio > '100'"
     *
     * // Entonces, $sql_filtro contendrá:
     * // "productos.precio > '100'"
     * ```
     *
     * @example Caso de validación fallida:
     * ```php
     * // Si el arreglo $filtro no incluye la clave 'valor' para el campo 'precio':
     * $campo = 'precio';
     * $columnas_extra = ['precio' => 'productos.precio'];
     * $filtro = [
     *     'precio' => [
     *         'operador' => '>',
     *         // 'valor' falta aquí
     *     ]
     * ];
     *
     * // La llamada al método:
     * $sql_filtro = $obj->maqueta_filtro_especial($campo, $columnas_extra, $filtro);
     *
     * // Retornará un array con detalles del error, por ejemplo:
     * // [
     * //   'error' => true,
     * //   'mensaje' => 'Error al validar filtro',
     * //   'data' => 'Detalles de la validación'
     * // ]
     * ```
     */
    private function maqueta_filtro_especial(string $campo, array $columnas_extra, array $filtro): array|string {
        // Elimina espacios en blanco del campo.
        $campo = trim($campo);

        // Valida que el filtro especial tenga la estructura correcta para el campo dado.
        $valida = (new validaciones())->valida_data_filtro_especial(campo: $campo, filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar filtro', data: $valida);
        }

        // Valida la existencia de la clave 'valor' en el arreglo de filtro para el campo.
        $keys = array('valor');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $filtro[$campo]);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar filtro', data: $valida);
        }

        // Guarda el nombre original del campo para referencia.
        $campo_filtro = $campo;

        // Obtiene el nombre del campo formateado para el filtro especial usando columnas extra.
        $campo = (new \gamboamartin\where\where())->campo_filtro_especial(campo: $campo, columnas_extra: $columnas_extra);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener campo', data: $campo);
        }

        // Genera la instrucción SQL basada en el campo y el filtro proporcionado.
        $data_sql = (new \gamboamartin\where\where())->data_sql(campo: $campo, campo_filtro: $campo_filtro, filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $data_sql);
        }

        return $data_sql;
    }



    /**
     * REG
     * Obtiene y actualiza la cláusula SQL especial integrando la condición generada a partir de un filtro especial.
     *
     * Este método realiza las siguientes operaciones:
     *
     * 1. Extrae el campo (clave) del array de filtro especial ($filtro_esp) mediante la función key() y lo recorta con trim().
     * 2. Valida la estructura del filtro especial para el campo obtenido, usando el método
     *    {@see \gamboamartin\src\validaciones::valida_data_filtro_especial()}. Si la validación falla, se retorna un error.
     * 3. Llama a {@see maqueta_filtro_especial()} para generar la condición SQL ($data_sql) basada en el filtro especial,
     *    utilizando las columnas extra proporcionadas.
     * 4. Si ocurre algún error al generar $data_sql, se retorna el error correspondiente.
     * 5. Integra la condición generada ($data_sql) en la cláusula SQL especial previa ($filtro_especial_sql)
     *    mediante el método {@see genera_filtro_especial()}. Este método combina la condición utilizando el operador lógico
     *    especificado en el filtro (por ejemplo, "AND" u "OR").
     * 6. Si ocurre algún error durante la integración, se retorna un error.
     * 7. Finalmente, retorna la cláusula SQL especial final actualizada.
     *
     * @param array  $columnas_extra      Array asociativo con definiciones adicionales para los campos (por ejemplo, subqueries o alias).
     *                                    Ejemplo:
     *                                    ```php
     *                                    [
     *                                        'tabla.precio' => 'productos.precio'
     *                                    ]
     *                                    ```
     * @param array  $filtro_esp          Array asociativo que define el filtro especial para un campo. Debe tener la estructura:
     *                                    ```php
     *                                    [
     *                                        'tabla.precio' => [
     *                                            'operador'    => '>',    // Operador de comparación (por ejemplo, ">", "<", "=")
     *                                            'valor'       => 100,    // Valor a comparar
     *                                            'comparacion' => 'AND'   // Operador lógico para concatenar condiciones
     *                                        ]
     *                                    ]
     *                                    ```
     * @param string $filtro_especial_sql La cláusula SQL especial previa a la que se desea agregar la nueva condición.
     *                                    Si está vacía, se inicializa con la condición generada.
     *
     * @return array|string              Devuelve la cláusula SQL especial final, combinando la cláusula previa y la condición generada,
     *                                    o un array con información del error en caso de fallo.
     *
     * @example Ejemplo 1: Sin cláusula SQL previa
     * ```php
     * $columnas_extra = ['tabla.precio' => 'productos.precio'];
     * $filtro_esp = [
     *     'tabla.precio' => [
     *         'operador' => '>',
     *         'valor' => 100,
     *         'comparacion' => 'AND'
     *     ]
     * ];
     * $filtro_especial_sql = "";
     * $resultado = $obj->obten_filtro_especial($columnas_extra, $filtro_esp, $filtro_especial_sql);
     * // Resultado esperado: "productos.precio > '100'"
     * ```
     *
     * @example Ejemplo 2: Con cláusula SQL previa
     * ```php
     * $columnas_extra = ['tabla.precio' => 'productos.precio'];
     * $filtro_esp = [
     *     'tabla.precio' => [
     *         'operador' => '<',
     *         'valor' => 200,
     *         'comparacion' => 'OR'
     *     ]
     * ];
     * $filtro_especial_sql = "productos.precio = '150'";
     * $resultado = $obj->obten_filtro_especial($columnas_extra, $filtro_esp, $filtro_especial_sql);
     * // Resultado esperado: "productos.precio = '150' OR productos.precio < '200'"
     * ```
     *
     * @example Ejemplo 3: Error en la validación del filtro especial
     * ```php
     * // Si $filtro_esp no contiene la clave 'operador' para el campo:
     * $columnas_extra = ['tabla.precio' => 'productos.precio'];
     * $filtro_esp = [
     *     'tabla.precio' => [
     *         // Falta 'operador'
     *         'valor' => 100
     *     ]
     * ];
     * $filtro_especial_sql = "";
     * $resultado = $obj->obten_filtro_especial($columnas_extra, $filtro_esp, $filtro_especial_sql);
     * // Resultado esperado: Array de error indicando que $filtro_esp['tabla.precio']['operador'] debe existir.
     * ```
     */
    private function obten_filtro_especial(
        array $columnas_extra,
        array $filtro_esp,
        string $filtro_especial_sql
    ): array|string {
        $campo = key($filtro_esp);
        $campo = trim($campo);

        $valida = (new validaciones())->valida_data_filtro_especial(campo: $campo, filtro: $filtro_esp);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error en filtro ", data: $valida);
        }

        $data_sql = $this->maqueta_filtro_especial(campo: $campo, columnas_extra: $columnas_extra, filtro: $filtro_esp);
        if (errores::$error) {
            return $this->error->error(mensaje:"Error filtro", data: $data_sql);
        }

        $filtro_especial_sql_r = (new \gamboamartin\where\where())->genera_filtro_especial(
            campo:  $campo,
            data_sql: $data_sql,
            filtro_esp: $filtro_esp,
            filtro_especial_sql: $filtro_especial_sql
        );
        if (errores::$error) {
            return $this->error->error(mensaje:"Error filtro", data: $filtro_especial_sql_r);
        }

        return $filtro_especial_sql_r;
    }


    /**
     * REG
     * Aplica paréntesis a los filtros especificados en un objeto.
     *
     * Este método recibe un objeto de filtros y un array de claves (keys) que indican cuáles de los
     * filtros contenidos en el objeto deben ser encerrados entre paréntesis. Primero, se limpia el objeto
     * de filtros mediante el método `limpia_filtros()`, para asegurarse de que cada clave definida en
     * `$keys_data_filter` exista en el objeto y tenga un valor (vacío o no). Posteriormente, para cada
     * clave especificada en `$keys_data_filter`, si el valor correspondiente en el objeto no es una cadena
     * vacía, se modifica su valor envolviéndolo entre paréntesis.
     *
     * Esta funcionalidad es útil para organizar y delimitar visualmente condiciones en consultas SQL o en
     * otros procesos de filtrado, garantizando que se apliquen correctamente las precedencias lógicas.
     *
     * @param stdClass $filtros         Objeto que contiene los filtros a procesar. Se espera que sus propiedades
     *                                  correspondan a diferentes condiciones o cláusulas que pueden ser concatenadas.
     * @param array    $keys_data_filter Array de claves (strings) que indican los nombres de las propiedades en
     *                                  el objeto `$filtros` a las cuales se les aplicará el formato con paréntesis.
     *
     * @return stdClass|array Devuelve el objeto `$filtros` modificado, en el que cada propiedad especificada en
     *                        `$keys_data_filter` que no sea una cadena vacía ha sido encerrada entre paréntesis.
     *                        En caso de error durante la limpieza de filtros, se retorna un array con los detalles
     *                        del error.
     *
     * @example Ejemplo 1: Aplicación de paréntesis a filtros no vacíos
     * <pre>
     * // Supongamos que tenemos un objeto $filtros con las siguientes propiedades:
     * $filtros = new stdClass();
     * $filtros->nombre = "nombre = 'Juan'";
     * $filtros->edad   = "";
     *
     * // Y el array de claves para aplicar paréntesis es:
     * $keys_data_filter = ['nombre', 'edad'];
     *
     * // Llamada al método:
     * $filtros_modificados = $this->parentesis_filtro($filtros, $keys_data_filter);
     *
     * // Resultado esperado:
     * // $filtros_modificados->nombre = " (nombre = 'Juan') "
     * // $filtros_modificados->edad   = "" (sin cambios, ya que está vacío)
     * </pre>
     *
     * @example Ejemplo 2: Objeto de filtros sin cambios
     * <pre>
     * // Si el objeto $filtros tiene todas sus propiedades vacías:
     * $filtros = new stdClass();
     * $filtros->nombre = "";
     * $filtros->edad   = "";
     *
     * $keys_data_filter = ['nombre', 'edad'];
     *
     * $filtros_modificados = $this->parentesis_filtro($filtros, $keys_data_filter);
     *
     * // Resultado esperado:
     * // Tanto $filtros_modificados->nombre como $filtros_modificados->edad permanecerán como cadenas vacías.
     * </pre>
     *
     * @see limpia_filtros() Método que se encarga de limpiar y asegurar la existencia de las claves especificadas
     *                         en el objeto de filtros.
     */
    private function parentesis_filtro(stdClass $filtros, array $keys_data_filter): stdClass|array
    {
        $filtros_ = $filtros;
        $filtros_ = $this->limpia_filtros(filtros: $filtros_, keys_data_filter: $keys_data_filter);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al limpiar filtros',
                data: $filtros_
            );
        }

        foreach ($keys_data_filter as $key) {
            if ($filtros_->$key !== '') {
                $filtros_->$key = ' (' . $filtros_->$key . ') ';
            }
        }

        return $filtros_;
    }



    /**
     * REG
     * Verifica que la propiedad "where" del objeto complemento contenga al menos un filtro válido.
     *
     * Este método se encarga de comprobar la existencia y el contenido de la propiedad <code>where</code> en el objeto
     * <code>$complemento</code>, que representa la cláusula <em>WHERE</em> de una consulta SQL. El procedimiento es el siguiente:
     *
     * <ol>
     *     <li>
     *         Se verifica si el objeto <code>$complemento</code> tiene definida la propiedad <code>where</code>.
     *         Si no está definida, se inicializa como una cadena vacía (<code>''</code>).
     *     </li>
     *     <li>
     *         Si la propiedad <code>where</code> no está vacía, se utiliza el método <code>filtros_vacios()</code> para
     *         determinar si todos los filtros indicados en el array <code>$key_data_filter</code> están vacíos.
     *     </li>
     *     <li>
     *         Si se detecta que el <code>where</code> tiene contenido pero todos los filtros están vacíos, se considera
     *         un error, ya que no se debe tener una cláusula <em>WHERE</em> sin ningún filtro efectivo.
     *     </li>
     * </ol>
     *
     * En resumen, el método retorna:
     * <ul>
     *     <li>
     *         <code>true</code> si:
     *         <ul>
     *             <li>
     *                 La propiedad <code>where</code> está vacía (lo cual es aceptable), o
     *             </li>
     *             <li>
     *                 La propiedad <code>where</code> tiene contenido y, además, al menos uno de los filtros
     *                 definidos en <code>$key_data_filter</code> contiene un valor no vacío.
     *             </li>
     *         </ul>
     *     </li>
     *     <li>
     *         Un array de error si:
     *         <ul>
     *             <li>
     *                 Ocurre algún error durante la verificación de los filtros (por ejemplo, errores internos en
     *                 <code>filtros_vacios()</code>), o
     *             </li>
     *             <li>
     *                 La propiedad <code>where</code> tiene contenido, pero todos los filtros están vacíos.
     *             </li>
     *         </ul>
     *     </li>
     * </ul>
     *
     * @param stdClass $complemento     Objeto que contiene la cláusula <em>WHERE</em> y otros parámetros relacionados con la consulta SQL.
     * @param array    $key_data_filter  Arreglo de claves (strings) que indican los nombres de los filtros a evaluar en el objeto
     *                                   <code>$complemento</code>.
     *
     * @return bool|array  Retorna <code>true</code> si la verificación es exitosa; de lo contrario, retorna un array con la
     *                     información del error generado mediante el método <code>$this->error->error()</code>.
     *
     * @example Ejemplo 1: Cláusula WHERE vacía
     * <pre>
     * $complemento = new stdClass();
     * // No se define la propiedad "where", por lo que se inicializa a '' automáticamente.
     * $key_data_filter = ['nombre', 'apellido'];
     * $resultado = $this->verifica_where($complemento, $key_data_filter);
     * // $resultado será true, ya que no existe un WHERE y no se requiere validar filtros vacíos.
     * </pre>
     *
     * @example Ejemplo 2: Cláusula WHERE con filtros definidos correctamente
     * <pre>
     * $complemento = new stdClass();
     * $complemento->where = "WHERE nombre = 'Carlos'";
     * $key_data_filter = ['nombre', 'apellido'];
     * // Supongamos que $complemento->nombre contiene "Carlos" y $complemento->apellido contiene algún valor no vacío.
     * $resultado = $this->verifica_where($complemento, $key_data_filter);
     * // $resultado será true, ya que se encontró al menos un filtro con contenido.
     * </pre>
     *
     * @example Ejemplo 3: Cláusula WHERE con contenido pero filtros vacíos
     * <pre>
     * $complemento = new stdClass();
     * $complemento->where = "WHERE";
     * // No se han definido valores para las claves de filtro
     * $key_data_filter = ['nombre', 'apellido'];
     * $resultado = $this->verifica_where($complemento, $key_data_filter);
     * // $resultado será un array de error indicando "Error si existe where debe haber al menos un filtro".
     * </pre>
     */
    private function verifica_where(stdClass $complemento, array $key_data_filter): bool|array
    {
        if (!isset($complemento->where)) {
            $complemento->where = '';
        }
        if ($complemento->where !== '') {
            $filtros_vacios = $this->filtros_vacios(complemento: $complemento, keys_data_filter: $key_data_filter);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error validar filtros', data: $filtros_vacios);
            }
            if ($filtros_vacios) {
                return $this->error->error(
                    mensaje: 'Error si existe where debe haber al menos un filtro',
                    data: $complemento,
                    es_final: true
                );
            }
        }
        return true;
    }


    /**
     * REG
     * Genera la cláusula SQL WHERE a partir de un objeto de filtros.
     *
     * Esta función analiza el objeto `$filtros` y un arreglo de claves (`$keys_data_filter`) que representan los nombres
     * de los campos de filtro. Recorre cada clave proporcionada y, si encuentra que al menos uno de los campos correspondientes
     * en `$filtros` tiene un valor distinto de una cadena vacía, define la cláusula WHERE para la consulta SQL. De lo contrario,
     * retorna una cadena vacía.
     *
     * **Flujo de Ejecución:**
     * 1. Se llama al método `limpia_filtros()` para asegurarse de que todas las claves definidas en `$keys_data_filter` existan
     *    en el objeto `$filtros` y estén inicializadas (en caso de que no existan, se les asigna una cadena vacía).
     * 2. Se inicializa la variable `$where` con una cadena vacía.
     * 3. Se recorre el arreglo `$keys_data_filter`. Si para alguna de las claves el valor correspondiente en `$filtros` no es una
     *    cadena vacía, se asigna a `$where` la cadena `" WHERE "`.
     * 4. Se retorna el valor de `$where`.
     *
     * **Parámetros:**
     * @param stdClass $filtros Objeto que contiene los filtros en formato clave-valor. Se espera que cada clave corresponda a un
     *                          campo de la base de datos y su valor sea la condición a aplicar.
     * @param array    $keys_data_filter Arreglo de cadenas. Cada elemento es el nombre de un campo a verificar dentro del objeto `$filtros`.
     *
     * **Valor de Retorno:**
     * @return string|array Retorna la cadena `" WHERE "` si al menos uno de los campos definidos en `$keys_data_filter` en `$filtros`
     *                      tiene un valor distinto de una cadena vacía; de lo contrario, retorna una cadena vacía.
     *                      En caso de error durante la limpieza de filtros, se devuelve un array con los detalles del error.
     *
     * **Ejemplos:**
     *
     * *Ejemplo 1: Al menos un filtro activo*
     * ```php
     * // Crear un objeto de filtros con un valor en el campo "nombre"
     * $filtros = new stdClass();
     * $filtros->nombre = "Juan";
     * $filtros->edad   = "";
     *
     * // Definir las claves a verificar
     * $keys_data_filter = ["nombre", "edad"];
     *
     * // Se asume que limpia_filtros() inicializa correctamente los campos faltantes.
     * $where = $this->where(filtros: $filtros, keys_data_filter: $keys_data_filter);
     * // Resultado esperado:
     * // $where = " WHERE "
     * ```
     *
     * *Ejemplo 2: Todos los filtros vacíos*
     * ```php
     * // Objeto de filtros sin ningún valor asignado
     * $filtros = new stdClass();
     * $filtros->nombre = "";
     * $filtros->edad   = "";
     *
     * // Claves a verificar
     * $keys_data_filter = ["nombre", "edad"];
     *
     * $where = $this->where(filtros: $filtros, keys_data_filter: $keys_data_filter);
     * // Resultado esperado:
     * // $where = ""
     * ```
     *
     * @throws array Devuelve un array con los detalles del error si ocurre algún fallo durante la limpieza de filtros.
     */
    private function where(stdClass $filtros, array $keys_data_filter): string|array
    {
        $filtros_ = $filtros;
        $filtros_ = $this->limpia_filtros(filtros: $filtros_, keys_data_filter: $keys_data_filter);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar filtros', data: $filtros_);
        }
        $where = '';
        foreach ($keys_data_filter as $key) {
            if ($filtros_->$key !== '') {
                $where = " WHERE ";
            }
        }
        return $where;
    }


    /**
     * REG
     * Genera la cláusula WHERE base a partir de un objeto complemento.
     *
     * Este método se encarga de asegurar que el objeto complemento tenga definida la propiedad `where`.
     * Si no existe, la inicializa como una cadena vacía. Luego, se normaliza el contenido de dicha
     * propiedad convirtiéndola a mayúsculas mediante el método `where_mayus()`. Si durante la
     * normalización se produce algún error, se retorna un array con la información del error; de lo
     * contrario, se retorna el objeto complemento con la cláusula WHERE ya ajustada.
     *
     * @param stdClass $complemento Objeto que contiene la cláusula SQL a procesar en la propiedad `where`.
     *                              Este objeto puede incluir otros atributos, pero es imprescindible que se
     *                              gestione correctamente la propiedad `where` para la construcción de la consulta.
     *
     * @return stdClass|array Devuelve el objeto complemento con la propiedad `where` normalizada (en mayúsculas)
     *                        si el proceso es exitoso; en caso de error, retorna un array con los detalles del error.
     *
     * @example Ejemplo 1: Objeto complemento sin la propiedad `where` definida
     * <pre>
     * // Se crea un objeto complemento sin definir la propiedad "where"
     * $complemento = new stdClass();
     *
     * // Al llamar a where_base(), se inicializa "where" como una cadena vacía
     * $resultado = $this->where_base($complemento);
     *
     * // Resultado esperado: El objeto $resultado tendrá la propiedad:
     * // $resultado->where === ""
     * </pre>
     *
     * @example Ejemplo 2: Objeto complemento con la cláusula WHERE en minúsculas
     * <pre>
     * // Se crea un objeto complemento con "where" definido en minúsculas
     * $complemento = new stdClass();
     * $complemento->where = "where";
     *
     * // Al llamar a where_base(), la función convierte el valor a mayúsculas
     * $resultado = $this->where_base($complemento);
     *
     * // Resultado esperado: $resultado->where === "WHERE"
     * </pre>
     *
     * @example Ejemplo 3: Error por cláusula WHERE mal aplicada
     * <pre>
     * // Se crea un objeto complemento con una cláusula WHERE que contiene más información de la esperada
     * $complemento = new stdClass();
     * $complemento->where = "where condiciones adicionales";
     *
     * // Al llamar a where_base(), el método where_mayus() convierte la cadena a mayúsculas ("WHERE CONDICIONES ADICIONALES")
     * // y, al no ser exactamente "WHERE", se genera un error.
     * $resultado = $this->where_base($complemento);
     *
     * // Resultado esperado: Se retorna un array de error con un mensaje similar a:
     * // [
     * //    'error' => 1,
     * //    'mensaje' => "Error ajustar where",
     * //    'data' => "WHERE CONDICIONES ADICIONALES",
     * //    'es_final' => true
     * // ]
     * </pre>
     */
    private function where_base(stdClass $complemento): array|stdClass
    {
        if (!isset($complemento->where)) {
            $complemento->where = '';
        }
        $complemento_r = $this->where_mayus(complemento: $complemento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error ajustar where', data: $complemento_r);
        }
        return $complemento_r;
    }


    /**
     * REG
     * Genera y valida la cláusula WHERE para un complemento SQL.
     *
     * Este método recibe un objeto <code>$complemento</code> que contiene datos SQL complementarios (incluida la cláusula <em>WHERE</em>)
     * y un arreglo de claves <code>$key_data_filter</code> que indica cuáles propiedades del objeto deben ser consideradas como filtros.
     * El proceso se realiza en dos pasos principales:
     *
     * <ol>
     *     <li>
     *         Se invoca el método <code>where_base()</code> para inicializar o ajustar la propiedad <code>where</code> del objeto
     *         <code>$complemento</code>. El resultado se almacena en la variable <code>$complemento_r</code>. Si ocurre algún error
     *         durante este proceso, se retorna un array de error.
     *     </li>
     *     <li>
     *         Se verifica que la propiedad <code>where</code> de <code>$complemento_r</code> contenga al menos uno de los filtros
     *         definidos en el arreglo <code>$key_data_filter</code>, mediante el método <code>verifica_where()</code>. Si la cláusula
     *         <code>where</code> existe pero todos los filtros están vacíos, se retorna un array de error indicando que, si existe
     *         un WHERE, debe haber al menos un filtro.
     *     </li>
     * </ol>
     *
     * Finalmente, se ajusta la propiedad <code>where</code> añadiéndole un espacio al inicio y al final para asegurar su correcta
     * integración en la consulta SQL, y se retorna el objeto complementado.
     *
     * @param stdClass $complemento    Objeto que contiene la cláusula <em>WHERE</em> y otros datos SQL complementarios.
     * @param array    $key_data_filter Arreglo de claves (strings) que indican los nombres de los filtros a evaluar en el objeto.
     *
     * @return array|stdClass Devuelve el objeto complementado (<code>$complemento</code>) con la propiedad <code>where</code> ajustada,
     *                         o un array con la información del error si ocurre algún fallo en el proceso.
     *
     * @example Ejemplo 1: Complemento sin cláusula WHERE definida
     * <pre>
     * // $complemento no tiene la propiedad "where" definida.
     * $complemento = new stdClass();
     * $key_data_filter = ['nombre', 'apellido'];
     * $resultado = $this->where_filtro($complemento, $key_data_filter);
     *
     * // Resultado esperado:
     * // El objeto $complemento se actualiza con la propiedad where igual a "  " (dos espacios),
     * // lo que indica que no se han definido filtros, y se considera válido.
     * </pre>
     *
     * @example Ejemplo 2: Complemento con cláusula WHERE definida y filtros válidos
     * <pre>
     * $complemento = new stdClass();
     * $complemento->where = "WHERE nombre = 'Carlos'";
     * $key_data_filter = ['nombre'];
     * $resultado = $this->where_filtro($complemento, $key_data_filter);
     *
     * // Resultado esperado:
     * // Se retorna el objeto $complemento con la propiedad where ajustada a " WHERE nombre = 'Carlos' "
     * // (con espacios agregados al inicio y al final).
     * </pre>
     *
     * @example Ejemplo 3: Error por cláusula WHERE presente pero sin filtros definidos
     * <pre>
     * $complemento = new stdClass();
     * $complemento->where = "WHERE";
     * $key_data_filter = ['nombre', 'apellido'];
     * $resultado = $this->where_filtro($complemento, $key_data_filter);
     *
     * // Resultado esperado:
     * // Se retorna un array de error indicando "Error si existe where debe haber al menos un filtro",
     * // ya que la propiedad where tiene contenido ("WHERE") pero ninguno de los filtros (clave "nombre" o "apellido")
     * // posee un valor distinto de cadena vacía.
     * </pre>
     *
     * @since 17.7.0
     */
    private function where_filtro(stdClass $complemento, array $key_data_filter): array|stdClass
    {
        $complemento_r = $this->where_base(complemento: $complemento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error ajustar where', data: $complemento_r);
        }

        $verifica = $this->verifica_where(complemento: $complemento_r, key_data_filter: $key_data_filter);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error validar where', data: $verifica);
        }

        $complemento_r->where = ' ' . $complemento_r->where . ' ';
        return $complemento_r;
    }


    /**
     * REG
     * Convierte a mayúsculas la cláusula SQL almacenada en la propiedad `where` del objeto complemento.
     *
     * Este método se encarga de procesar la propiedad `where` de un objeto de tipo `stdClass` que representa
     * un complemento de datos SQL. La función realiza las siguientes operaciones:
     *
     * 1. Verifica si la propiedad `where` está definida en el objeto `$complemento`; si no lo está, la inicializa como una cadena vacía.
     * 2. Elimina los espacios en blanco al inicio y al final del valor de `$complemento->where` mediante `trim()`.
     * 3. Si la propiedad `where` no está vacía, la convierte a mayúsculas utilizando `strtoupper()`.
     * 4. Finalmente, si el valor resultante de `where` no es una cadena vacía y no es exactamente igual a `"WHERE"`,
     *    se registra un error indicando que la cláusula where está mal aplicada.
     *
     * @param stdClass $complemento Objeto que contiene la propiedad `where` a procesar. Se espera que dicha propiedad
     *                              contenga una cláusula SQL "WHERE" (ya sea en minúsculas o mayúsculas) o esté vacía.
     *
     * @return stdClass|array Devuelve el objeto `$complemento` con la propiedad `where` convertida a mayúsculas si la validación es exitosa;
     *                        en caso contrario, retorna un array con la información del error generado.
     *
     * @example Ejemplo 1: Complemento sin cláusula where definida
     * <pre>
     * $complemento = new stdClass();
     * // No se define $complemento->where
     * $resultado = $this->where_mayus($complemento);
     * // Resultado esperado: $resultado->where es una cadena vacía ("").
     * </pre>
     *
     * @example Ejemplo 2: Complemento con cláusula where correcta
     * <pre>
     * $complemento = new stdClass();
     * $complemento->where = "where";
     * $resultado = $this->where_mayus($complemento);
     * // Resultado esperado: $resultado->where es "WHERE".
     * </pre>
     *
     * @example Ejemplo 3: Complemento con cláusula where incorrecta
     * <pre>
     * $complemento = new stdClass();
     * $complemento->where = "where condiciones"; // Contiene más información que "WHERE"
     * $resultado = $this->where_mayus($complemento);
     * // Resultado esperado: Se retorna un array de error con el mensaje "Error where mal aplicado"
     * // ya que, después de convertir a mayúsculas, el valor es "WHERE CONDICIONES", lo cual no es igual a "WHERE".
     * </pre>
     */
    private function where_mayus(stdClass $complemento): array|stdClass
    {
        if (!isset($complemento->where)) {
            $complemento->where = '';
        }
        $complemento->where = trim($complemento->where);
        if ($complemento->where !== '') {
            $complemento->where = strtoupper($complemento->where);
        }
        if ($complemento->where !== '' && $complemento->where !== 'WHERE') {
            return $this->error->error(
                mensaje: 'Error where mal aplicado',
                data: $complemento->where,
                es_final: true
            );
        }
        return $complemento;
    }


    /**
     * REG
     * Genera una cláusula SQL `WHERE` basada en un filtro proporcionado.
     *
     * Esta función toma un filtro SQL en forma de cadena, valida que no esté vacío y genera una cláusula
     * `WHERE` con dicho filtro. Si el filtro está vacío, retorna una cadena vacía, evitando generar un
     * `WHERE` sin contenido.
     *
     * @param string $filtro_sql Cadena que representa la condición del filtro para la cláusula `WHERE`.
     *                           Ejemplo: `"monto > 1000 AND estado = 'activo'"`.
     *
     * @return string Devuelve una cadena con la cláusula `WHERE` generada.
     *                Ejemplo: `" WHERE monto > 1000 AND estado = 'activo'"`.
     *                Si `$filtro_sql` está vacío, retorna una cadena vacía (`''`).
     *
     * ### Ejemplo de uso exitoso:
     *
     * 1. **Generar una cláusula `WHERE` con un filtro válido**:
     *    ```php
     *    $filtro = "monto > 1000 AND estado = 'activo'";
     *    $resultado = $this->where_suma(filtro_sql: $filtro);
     *
     *    // Resultado esperado:
     *    // " WHERE monto > 1000 AND estado = 'activo'"
     *    ```
     *
     * 2. **Generar una cláusula `WHERE` con un filtro vacío**:
     *    ```php
     *    $filtro = "";
     *    $resultado = $this->where_suma(filtro_sql: $filtro);
     *
     *    // Resultado esperado:
     *    // ""
     *    ```
     *
     * 3. **Usar con filtros simples**:
     *    ```php
     *    $filtro = "id = 10";
     *    $resultado = $this->where_suma(filtro_sql: $filtro);
     *
     *    // Resultado esperado:
     *    // " WHERE id = 10"
     *    ```
     *
     * ### Casos de validación:
     *
     * - Si el filtro está vacío, no se genera la cláusula `WHERE`:
     *    ```php
     *    $filtro = "";
     *    $resultado = $this->where_suma(filtro_sql: $filtro);
     *    // Resultado esperado: Cadena vacía ("").
     *    ```
     *
     * - Si el filtro contiene solo espacios en blanco:
     *    ```php
     *    $filtro = "   ";
     *    $resultado = $this->where_suma(filtro_sql: $filtro);
     *    // Resultado esperado: Cadena vacía ("").
     *    ```
     *
     * ### Uso en consultas SQL dinámicas:
     * Esta función es útil para generar consultas SQL dinámicas en las que el filtro puede o no estar definido.
     * Por ejemplo:
     * ```php
     * $filtro = "usuario_id = 5";
     * $where = $this->where_suma(filtro_sql: $filtro);
     * $sql = "SELECT * FROM usuarios" . $where;
     *
     * // Resultado:
     * // $sql = "SELECT * FROM usuarios WHERE usuario_id = 5";
     * ```
     *
     * ### Resultado esperado:
     * - Si el filtro no está vacío: Retorna una cláusula `WHERE` correctamente formada.
     * - Si el filtro está vacío: Retorna una cadena vacía.
     */

    final public function where_suma(string $filtro_sql): string
    {
        $where = '';
        if(trim($filtro_sql) !== '' ){
            $where = ' WHERE '. $filtro_sql;
        }
        return $where;

    }

}
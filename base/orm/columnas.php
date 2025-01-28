<?php
namespace base\orm;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use stdClass;

class columnas{
    private errores $error;
    private validaciones $validacion;
    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * REG
     * Genera una expresión SQL para sumar un campo y asignarle un alias.
     *
     * Esta función crea una cláusula SQL que utiliza la función `SUM` para sumar los valores de un campo y asegura
     * que si el resultado es nulo (`NULL`), se devuelve un valor predeterminado de 0. Además, se asigna un alias
     * al resultado.
     *
     * @param string $alias Alias que se asignará al resultado de la suma del campo.
     *                      Este alias será utilizado para referenciar el resultado en la consulta SQL.
     *                      Ejemplo: `'total_ingresos'`.
     * @param string $campo Nombre del campo en la tabla que será sumado.
     *                      Debe ser un nombre de columna válido en la base de datos.
     *                      Ejemplo: `'ventas.monto'`.
     *
     * @return string|array Devuelve una cadena SQL con la expresión generada.
     *                      Ejemplo: `"IFNULL( SUM(ventas.monto) ,0) AS total_ingresos"`.
     *                      Si ocurre un error (como campos vacíos), devuelve un array con los detalles del problema.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $alias = 'total_ingresos';
     * $campo = 'ventas.monto';
     *
     * $resultado = $this->add_column(alias: $alias, campo: $campo);
     *
     * // Resultado esperado:
     * // 'IFNULL( SUM(ventas.monto) ,0) AS total_ingresos'
     * ```
     *
     * ### Casos de uso:
     *
     * 1. **Generar columna con suma y alias**:
     *    ```php
     *    $alias = 'total_pagos';
     *    $campo = 'pagos.cantidad';
     *
     *    $resultado = $this->add_column(alias: $alias, campo: $campo);
     *    // Resultado esperado:
     *    // 'IFNULL( SUM(pagos.cantidad) ,0) AS total_pagos'
     *    ```
     *
     * 2. **Uso en una consulta SQL dinámica**:
     *    ```php
     *    $alias = 'total_ventas';
     *    $campo = 'ventas.monto';
     *
     *    $columna_sql = $this->add_column(alias: $alias, campo: $campo);
     *    $consulta = "SELECT $columna_sql FROM ventas";
     *
     *    // Consulta generada:
     *    // SELECT IFNULL( SUM(ventas.monto) ,0) AS total_ventas FROM ventas
     *    ```
     *
     * ### Validaciones:
     *
     * - Se asegura que `$campo` no esté vacío. Si está vacío, se devuelve un error:
     *   ```php
     *   $alias = 'total_ingresos';
     *   $campo = '';
     *   $resultado = $this->add_column(alias: $alias, campo: $campo);
     *   // Resultado esperado: array con mensaje de error "Error $campo no puede venir vacio".
     *   ```
     * - Se valida que `$alias` no esté vacío. Si está vacío, se devuelve un error:
     *   ```php
     *   $alias = '';
     *   $campo = 'ventas.monto';
     *   $resultado = $this->add_column(alias: $alias, campo: $campo);
     *   // Resultado esperado: array con mensaje de error "Error $alias no puede venir vacio".
     *   ```
     *
     * ### Casos de error:
     *
     * 1. **Campo vacío**:
     *    ```php
     *    $alias = 'total_ventas';
     *    $campo = '';
     *    $resultado = $this->add_column(alias: $alias, campo: $campo);
     *    // Resultado esperado: array con mensaje de error "Error $campo no puede venir vacio".
     *    ```
     *
     * 2. **Alias vacío**:
     *    ```php
     *    $alias = '';
     *    $campo = 'ventas.monto';
     *    $resultado = $this->add_column(alias: $alias, campo: $campo);
     *    // Resultado esperado: array con mensaje de error "Error $alias no puede venir vacio".
     *    ```
     *
     * ### Resultado esperado:
     * - Devuelve una cadena SQL para la suma y alias del campo.
     * - Gestiona errores si los parámetros no son válidos, devolviendo detalles del problema en un array.
     */
    final public function add_column(string $alias, string $campo): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error $campo no puede venir vacio', data: $campo, es_final: true);
        }
        $alias = trim($alias);
        if($alias === ''){
            return $this->error->error(mensaje:'Error $alias no puede venir vacio', data: $alias, es_final: true);
        }
        return 'IFNULL( SUM('. $campo .') ,0)AS ' . $alias;
    }

    /**
     * REG
     * Ajusta y combina columnas completas para consultas SQL.
     *
     * Esta función genera columnas completas para una tabla SQL, renombrando las tablas si es necesario y
     * permite combinar columnas ya existentes con columnas generadas en bruto o procesadas.
     *
     * @param string $columnas Columnas existentes en la consulta SQL.
     * @param bool $columnas_en_bruto Indica si las columnas deben generarse sin alias o formato adicional.
     * @param array $columnas_sql Array con las columnas específicas a incluir en la consulta.
     * @param modelo_base $modelo Instancia del modelo base que contiene la lógica de la tabla.
     * @param string $tabla Nombre de la tabla original.
     * @param string $tabla_renombrada Nombre alternativo para renombrar la tabla en la consulta.
     *
     * @return array|string Devuelve la cadena final de columnas ajustadas para la consulta SQL en caso de éxito.
     *                      Devuelve un array con detalles del error en caso de fallas.
     *
     * @example
     * // Caso 1: Generar columnas de una tabla con alias renombrado.
     * $columnas = '';
     * $columnas_en_bruto = false;
     * $columnas_sql = ['id', 'nombre', 'fecha_creacion'];
     * $modelo = new modelo_base($link);
     * $tabla = 'usuarios';
     * $tabla_renombrada = 'u';
     * $resultado = $this->ajusta_columnas_completas(
     *     columnas: $columnas,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     modelo: $modelo,
     *     tabla: $tabla,
     *     tabla_renombrada: $tabla_renombrada
     * );
     * // Resultado esperado:
     * // $resultado = 'u.id AS usuarios_id, u.nombre AS usuarios_nombre, u.fecha_creacion AS usuarios_fecha_creacion';
     *
     * @example
     * // Caso 2: Generar columnas en bruto sin alias para una tabla.
     * $columnas = '';
     * $columnas_en_bruto = true;
     * $columnas_sql = ['id', 'nombre'];
     * $modelo = new modelo_base($link);
     * $tabla = 'productos';
     * $tabla_renombrada = '';
     * $resultado = $this->ajusta_columnas_completas(
     *     columnas: $columnas,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     modelo: $modelo,
     *     tabla: $tabla,
     *     tabla_renombrada: $tabla_renombrada
     * );
     * // Resultado esperado:
     * // $resultado = 'productos.id, productos.nombre';
     *
     * @example
     * // Caso 3: Manejo de errores al pasar un número como tabla.
     * $columnas = '';
     * $columnas_en_bruto = false;
     * $columnas_sql = [];
     * $modelo = new modelo_base($link);
     * $tabla = '123'; // Valor inválido
     * $tabla_renombrada = '';
     * $resultado = $this->ajusta_columnas_completas(
     *     columnas: $columnas,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     modelo: $modelo,
     *     tabla: $tabla,
     *     tabla_renombrada: $tabla_renombrada
     * );
     * // Resultado esperado:
     * // $resultado = ['error' => true, 'mensaje' => 'Error $tabla no puede ser un numero', 'data' => '123'];
     */
    private function ajusta_columnas_completas(
        string $columnas,
        bool $columnas_en_bruto,
        array $columnas_sql,
        modelo_base $modelo,
        string $tabla,
        string $tabla_renombrada
    ): array|string {
        $tabla = str_replace('models\\', '', $tabla);
        if (is_numeric($tabla)) {
            return $this->error->error(mensaje: 'Error $tabla no puede ser un numero', data: $tabla, es_final: true);
        }

        $resultado_columnas = $this->genera_columnas_consulta(
            columnas_en_bruto: $columnas_en_bruto,
            modelo: $modelo,
            tabla_original: $tabla,
            tabla_renombrada: $tabla_renombrada,
            columnas: $columnas_sql
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar columnas', data: $resultado_columnas);
        }

        $columnas_env = $this->integra_columnas_por_data(
            columnas: $columnas,
            resultado_columnas: $resultado_columnas
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas_env);
        }

        return $columnas_env;
    }



    /**
     * REG
     * Determina si se deben aplicar columnas basadas en la tabla proporcionada.
     *
     * Este método verifica si el array `$columnas_by_table` contiene elementos, indicando
     * que se deben aplicar columnas específicas para la tabla. Si el array está vacío,
     * retorna `false`, de lo contrario, retorna `true`.
     *
     * @param array $columnas_by_table Arreglo que contiene las columnas definidas para una tabla específica.
     *                                 Puede ser un array vacío si no hay columnas asignadas.
     *
     * @return bool Retorna:
     *  - `true` si `$columnas_by_table` contiene al menos un elemento.
     *  - `false` si `$columnas_by_table` está vacío.
     *
     * @example
     *  Ejemplo 1: Array con columnas
     *  -----------------------------------------------
     *  $columnas_by_table = ['id', 'nombre', 'email'];
     *  $resultado = $this->aplica_columnas_by_table($columnas_by_table);
     *  // $resultado será true, ya que el array contiene elementos.
     *
     * @example
     *  Ejemplo 2: Array vacío
     *  -----------------------------------------------
     *  $columnas_by_table = [];
     *  $resultado = $this->aplica_columnas_by_table($columnas_by_table);
     *  // $resultado será false, ya que el array está vacío.
     */
    private function aplica_columnas_by_table(array $columnas_by_table): bool
    {
        $aplica_columnas_by_table = false;

        // Verifica si hay elementos en el array
        if (count($columnas_by_table) > 0) {
            $aplica_columnas_by_table = true;
        }

        return $aplica_columnas_by_table;
    }


    /**
     * REG
     * Asigna los detalles de una columna a un arreglo de columnas completas.
     *
     * Este método:
     * 1. Valida que el atributo no esté vacío.
     * 2. Verifica que las claves esenciales (`Type`, `Null`) existan en el arreglo `$columna`.
     * 3. Completa la información de la columna en el arreglo `$columnas_completas`, incluyendo el nombre del atributo,
     *    el tipo, la clave (`Key`), y si permite valores nulos (`Null`).
     *
     * @param string $atributo Nombre de la columna que se desea agregar.
     * @param array $columna Arreglo con los detalles de la columna, incluyendo las claves `Type` y `Null`.
     * @param array $columnas_completas Arreglo que contiene las columnas completas previamente definidas.
     *
     * @return array
     *   - Retorna el arreglo `$columnas_completas` actualizado con los detalles de la columna.
     *   - En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Asignación de una columna completa
     *  ---------------------------------------------
     *  $atributo = 'nombre';
     *  $columna = [
     *      'Type' => 'varchar(255)',
     *      'Null' => 'YES',
     *      'Key' => 'PRI'
     *  ];
     *  $columnas_completas = [];
     *
     *  $resultado = $this->asigna_columna_completa($atributo, $columna, $columnas_completas);
     *  // $resultado será:
     *  // [
     *  //   'nombre' => [
     *  //       'campo' => 'nombre',
     *  //       'Type' => 'varchar(255)',
     *  //       'Key' => 'PRI',
     *  //       'Null' => 'YES'
     *  //   ]
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error por atributo vacío
     *  -----------------------------------
     *  $atributo = '';
     *  $columna = [
     *      'Type' => 'varchar(255)',
     *      'Null' => 'YES'
     *  ];
     *  $columnas_completas = [];
     *
     *  $resultado = $this->asigna_columna_completa($atributo, $columna, $columnas_completas);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error atributo no puede venir vacio',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error por claves faltantes en `$columna`
     *  ---------------------------------------------------
     *  $atributo = 'nombre';
     *  $columna = ['Type' => 'varchar(255)']; // Falta la clave 'Null'
     *  $columnas_completas = [];
     *
     *  $resultado = $this->asigna_columna_completa($atributo, $columna, $columnas_completas);
     *  // Retorna un error indicando que falta la clave 'Null'.
     *
     * @throws array Retorna un arreglo con detalles del error si ocurre algún problema durante la validación.
     */
    private function asigna_columna_completa(string $atributo, array $columna, array $columnas_completas): array
    {
        // Validar que el atributo no esté vacío
        $atributo = trim($atributo);
        if ($atributo === '') {
            return $this->error->error(
                mensaje: 'Error atributo no puede venir vacio',
                data: $atributo,
                es_final: true
            );
        }

        // Validar que las claves esenciales existan en la columna
        $keys = array('Type', 'Null');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $columna);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al validar $columna',
                data: $valida
            );
        }

        // Asignar valores predeterminados si la clave 'Key' no está definida
        if (!isset($columna['Key'])) {
            $columna['Key'] = '';
        }

        // Completar los detalles de la columna en el arreglo de columnas completas
        $columnas_completas[$atributo]['campo'] = $atributo;
        $columnas_completas[$atributo]['Type'] = $columna['Type'];
        $columnas_completas[$atributo]['Key'] = $columna['Key'];
        $columnas_completas[$atributo]['Null'] = $columna['Null'];

        return $columnas_completas;
    }


    /**
     * REG
     * Asigna las columnas de una tabla desde la sesión al modelo proporcionado.
     *
     * Este método verifica si las columnas de una tabla específica están disponibles en la sesión
     * (`$_SESSION`). Si existen, utiliza el método `asigna_data_columnas` para asignarlas al modelo.
     * De lo contrario, retorna `false`.
     *
     * @param modelo_base $modelo   Instancia del modelo donde se asignarán las columnas.
     *                              El modelo debe tener una propiedad `data_columnas` para almacenar
     *                              los datos de las columnas.
     * @param string      $tabla_bd Nombre de la tabla para la cual se buscarán y asignarán las columnas.
     *
     * @return bool|array Retorna:
     *   - `true` si las columnas se asignan exitosamente al modelo.
     *   - `false` si las columnas no existen en la sesión.
     *   - Un `array` con detalles del error si ocurre una validación fallida.
     *
     * @throws array Si:
     *   - `$tabla_bd` está vacío.
     *   - Ocurre un error al intentar asignar las columnas mediante `asigna_data_columnas`.
     *
     * @example
     *  Ejemplo 1: Asignación exitosa de columnas desde la sesión
     *  ---------------------------------------------------------
     *  $_SESSION['campos_tabla']['usuarios'] = ['id', 'nombre', 'email'];
     *  $_SESSION['columnas_completas']['usuarios'] = ['id', 'nombre', 'email', 'fecha_creacion'];
     *
     *  $modelo = new modelo_base();
     *  $tabla_bd = "usuarios";
     *
     *  $resultado = $this->asigna_columnas_en_session($modelo, $tabla_bd);
     *
     *  // Resultado:
     *  // $resultado => true
     *  // $modelo->data_columnas->columnas_parseadas => ['id', 'nombre', 'email']
     *  // $modelo->data_columnas->columnas_completas => ['id', 'nombre', 'email', 'fecha_creacion']
     *
     * @example
     *  Ejemplo 2: Error al no encontrar columnas en la sesión
     *  -------------------------------------------------------
     *  $_SESSION['campos_tabla']['usuarios'] = null;
     *  $_SESSION['columnas_completas']['usuarios'] = null;
     *
     *  $modelo = new modelo_base();
     *  $tabla_bd = "usuarios";
     *
     *  $resultado = $this->asigna_columnas_en_session($modelo, $tabla_bd);
     *
     *  // Resultado:
     *  // $resultado => false
     *
     * @example
     *  Ejemplo 3: Error al pasar una tabla vacía
     *  -----------------------------------------
     *  $modelo = new modelo_base();
     *  $tabla_bd = "";
     *
     *  $resultado = $this->asigna_columnas_en_session($modelo, $tabla_bd);
     *
     *  // Resultado:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error tabla_bd no puede venir vacia',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     */
    private function asigna_columnas_en_session(modelo_base $modelo, string $tabla_bd): bool|array
    {
        // Validación: tabla_bd no puede estar vacía
        $tabla_bd = trim($tabla_bd);
        if ($tabla_bd === '') {
            return $this->error->error(
                mensaje: 'Error tabla_bd no puede venir vacia',
                data: $tabla_bd,
                es_final: true
            );
        }

        $data = new stdClass();

        // Verifica si las columnas existen en la sesión
        if (isset($_SESSION['campos_tabla'][$tabla_bd], $_SESSION['columnas_completas'][$tabla_bd])) {
            // Asigna las columnas al objeto data
            $data = $this->asigna_data_columnas(data: $data, tabla_bd: $tabla_bd);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al generar columnas',
                    data: $data
                );
            }

            // Asigna el objeto data al modelo
            $modelo->data_columnas = $data;
            return true;
        }

        return false;
    }


    /**
     * REG
     * Agrega un atributo a la lista de columnas parseadas.
     *
     * Este método:
     * 1. Valida que el nombre del atributo no esté vacío.
     * 2. Agrega el atributo proporcionado al arreglo de columnas parseadas.
     *
     * @param string $atributo Nombre del atributo que se desea agregar a las columnas parseadas.
     * @param array $columnas_parseadas Arreglo que contiene las columnas parseadas previamente.
     *
     * @return array
     *   - Retorna el arreglo actualizado con el nuevo atributo incluido.
     *   - En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Agregar un atributo válido
     *  -------------------------------------
     *  $atributo = 'nombre';
     *  $columnas_parseadas = ['id', 'descripcion'];
     *
     *  $resultado = $this->asigna_columnas_parseadas($atributo, $columnas_parseadas);
     *  // $resultado será:
     *  // ['id', 'descripcion', 'nombre']
     *
     * @example
     *  Ejemplo 2: Error por atributo vacío
     *  ------------------------------------
     *  $atributo = '';
     *  $columnas_parseadas = ['id', 'descripcion'];
     *
     *  $resultado = $this->asigna_columnas_parseadas($atributo, $columnas_parseadas);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error atributo no puede venir vacio',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     *
     * @throws array Retorna un arreglo con el error si ocurre algún problema durante la validación.
     */
    private function asigna_columnas_parseadas(string $atributo, array $columnas_parseadas): array
    {
        // Validar que el atributo no esté vacío
        $atributo = trim($atributo);
        if ($atributo === '') {
            return $this->error->error(
                mensaje: 'Error atributo no puede venir vacio',
                data: $atributo,
                es_final: true
            );
        }

        // Agregar el atributo al arreglo de columnas parseadas
        $columnas_parseadas[] = $atributo;
        return $columnas_parseadas;
    }


    /**
     * REG
     * Asigna las columnas de una tabla a la sesión para su uso posterior en el modelo.
     *
     * Este método:
     * 1. Verifica que el nombre de la tabla no esté vacío ni sea numérico.
     * 2. Obtiene las columnas de la tabla utilizando el método `genera_columnas_field`.
     * 3. Asigna las columnas parseadas y completas a la sesión bajo las claves `campos_tabla` y `columnas_completas`, respectivamente.
     * 4. Retorna las columnas de la tabla en un objeto `stdClass` que contiene las columnas parseadas y completas.
     *
     * @param modelo_base $modelo El modelo que contiene la lógica para interactuar con la base de datos.
     * @param string $tabla_bd El nombre de la tabla en la base de datos.
     *
     * @return array|stdClass
     *   - Retorna un objeto `stdClass` con las propiedades `columnas_parseadas` y `columnas_completas` que contienen las columnas procesadas.
     *   - En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Asignando columnas de una tabla a la sesión
     *  -----------------------------------------------------
     *  $tabla_bd = 'usuarios';
     *  $modelo = new modelo_base();
     *
     *  $resultado = $this->asigna_columnas_session_new($modelo, $tabla_bd);
     *  // $resultado->columnas_parseadas contendrá los nombres de las columnas
     *  // $resultado->columnas_completas tendrá los detalles de cada columna (tipo, nulidad, etc.)
     *  // Las columnas también estarán disponibles en $_SESSION['campos_tabla']['usuarios'] y $_SESSION['columnas_completas']['usuarios'].
     *
     * @example
     *  Ejemplo 2: Error debido a una tabla vacía
     *  -----------------------------------------
     *  $tabla_bd = '';
     *  $modelo = new modelo_base();
     *
     *  $resultado = $this->asigna_columnas_session_new($modelo, $tabla_bd);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error $tabla_bd esta vacia',
     *  //   'data' => ''
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error debido a que el nombre de la tabla es numérico
     *  --------------------------------------------------------------
     *  $tabla_bd = '123';
     *  $modelo = new modelo_base();
     *
     *  $resultado = $this->asigna_columnas_session_new($modelo, $tabla_bd);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error $tabla_bd no puede ser un numero',
     *  //   'data' => '123'
     *  // ]
     */
    private function asigna_columnas_session_new(modelo_base $modelo, string $tabla_bd): array|stdClass
    {
        // Verifica que el nombre de la tabla no esté vacío ni sea numérico
        $tabla_bd = trim($tabla_bd);
        if ($tabla_bd === '') {
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia', data: $tabla_bd, es_final: true);
        }

        if (is_numeric($tabla_bd)) {
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero', data: $tabla_bd,
                es_final: true);
        }

        // Obtiene las columnas de la tabla de base de datos
        $columnas_field = $this->genera_columnas_field(modelo: $modelo, tabla_bd: $tabla_bd);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas_field);
        }

        // Asigna las columnas parseadas y completas a la sesión
        $_SESSION['campos_tabla'][$tabla_bd] = $columnas_field->columnas_parseadas;
        $_SESSION['columnas_completas'][$tabla_bd] = $columnas_field->columnas_completas;

        // Asigna las columnas al modelo y retorna el objeto
        $modelo->data_columnas = $columnas_field;
        return $modelo->data_columnas;
    }


    /**
     * REG
     * Asigna datos de columnas parseadas y completas desde la sesión a un objeto proporcionado.
     *
     * Este método utiliza datos almacenados en la sesión (`$_SESSION`) para asignar información
     * de columnas parseadas y completas a las propiedades de un objeto `stdClass`. Valida
     * que las claves necesarias existan y que la tabla no esté vacía antes de realizar la asignación.
     *
     * @param stdClass $data     Objeto donde se asignarán las columnas parseadas y completas.
     *                           Las siguientes propiedades se agregarán al objeto:
     *                           - `columnas_parseadas`: Columnas parseadas de la tabla.
     *                           - `columnas_completas`: Columnas completas de la tabla.
     * @param string   $tabla_bd Nombre de la tabla para la cual se buscan las columnas.
     *
     * @return stdClass|array Retorna:
     *   - Un objeto `stdClass` con las propiedades `columnas_parseadas` y `columnas_completas` asignadas.
     *   - Un array con detalles del error si ocurre alguna validación fallida.
     *
     * @throws array Si alguna de las validaciones falla:
     *   - Si `$tabla_bd` está vacío.
     *   - Si no existe `$_SESSION['campos_tabla']`.
     *   - Si no existe `$_SESSION['campos_tabla'][$tabla_bd]`.
     *   - Si no existe `$_SESSION['columnas_completas']`.
     *   - Si no existe `$_SESSION['columnas_completas'][$tabla_bd]`.
     *
     * @example
     *  Ejemplo 1: Asignar columnas de una tabla válida
     *  ------------------------------------------------
     *  // Supongamos que la sesión tiene los siguientes datos:
     *  $_SESSION['campos_tabla']['usuarios'] = ['id', 'nombre', 'email'];
     *  $_SESSION['columnas_completas']['usuarios'] = ['id', 'nombre', 'email', 'fecha_creacion'];
     *
     *  $data = new stdClass();
     *  $tabla_bd = "usuarios";
     *
     *  $resultado = $this->asigna_data_columnas($data, $tabla_bd);
     *
     *  // Resultado:
     *  // $resultado->columnas_parseadas = ['id', 'nombre', 'email'];
     *  // $resultado->columnas_completas = ['id', 'nombre', 'email', 'fecha_creacion'];
     *
     * @example
     *  Ejemplo 2: Error al no encontrar la tabla en la sesión
     *  --------------------------------------------------------
     *  $data = new stdClass();
     *  $tabla_bd = "productos";
     *
     *  // Supongamos que la sesión no tiene datos para 'productos':
     *  $resultado = $this->asigna_data_columnas($data, $tabla_bd);
     *
     *  // Resultado:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => 'Error debe existir SESSION[campos_tabla][productos]',
     *  //     'data' => $_SESSION,
     *  //     ...
     *  // ]
     */
    private function asigna_data_columnas(stdClass $data, string $tabla_bd): stdClass|array
    {
        $tabla_bd = trim($tabla_bd);

        // Validación: tabla_bd no puede estar vacía
        if ($tabla_bd === '') {
            return $this->error->error(
                mensaje: 'Error tabla_bd no puede venir vacia',
                data: $tabla_bd,
                es_final: true
            );
        }

        // Validaciones: existencia de datos en la sesión
        if (!isset($_SESSION['campos_tabla'])) {
            return $this->error->error(
                mensaje: 'Error debe existir SESSION[campos_tabla]',
                data: $_SESSION,
                es_final: true
            );
        }
        if (!isset($_SESSION['campos_tabla'][$tabla_bd])) {
            return $this->error->error(
                mensaje: 'Error debe existir SESSION[campos_tabla][' . $tabla_bd . ']',
                data: $_SESSION,
                es_final: true
            );
        }
        if (!isset($_SESSION['columnas_completas'])) {
            return $this->error->error(
                mensaje: 'Error debe existir SESSION[columnas_completas]',
                data: $_SESSION,
                es_final: true
            );
        }
        if (!isset($_SESSION['columnas_completas'][$tabla_bd])) {
            return $this->error->error(
                mensaje: 'Error debe existir SESSION[columnas_completas][' . $tabla_bd . ']',
                data: $_SESSION,
                es_final: true
            );
        }

        // Asignación de columnas desde la sesión
        $data->columnas_parseadas = $_SESSION['campos_tabla'][$tabla_bd];
        $data->columnas_completas = $_SESSION['columnas_completas'][$tabla_bd];

        return $data;
    }


    /**
     * TOTAL
     * Este método recibe dos arrays $campos_no_upd y $registro y devuelve un array $registro después de eliminar
     * elementos que existen en ambos arrays.
     * El propósito principal de este método es filtrar ciertos campos de un registro que no se deben actualizar.
     *
     * @param array $campos_no_upd Array que contiene los nombres de los campos que no deben actualizarse.
     * @param array $registro Array que contiene el registro original (por ejemplo, una fila de la base de datos).
     *
     * @return array $registro Devuelve el array original $registro después de eliminar aquellos elementos cuyos
     * nombres de campo estaban presentes en el array $campos_no_upd.
     *
     * @throws errores
     *       Se arroja una excepción con un mensaje de error si el $campo_no_upd está vacío o si es numérico.
     * @version 16.119.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.columnas.campos_no_upd
     */
    final public function campos_no_upd(array $campos_no_upd, array $registro): array
    {
        foreach ($campos_no_upd as $campo_no_upd){
            $campo_no_upd = trim($campo_no_upd);
            if($campo_no_upd === ''){
                $fix = 'Se tiene que mandar un campo del modelo indicado';
                $fix .= ' $campo_no_upd[] debe ser un campo ejemplo $campo_no_upd[] = status';
                return $this->error->error(mensaje: 'Error $campo_no_upd esta vacio', data: $campo_no_upd,
                    es_final: true, fix: $fix);
            }
            if(is_numeric($campo_no_upd)){
                $fix = 'Se tiene que mandar un campo del modelo indicado';
                $fix .= ' $campo_no_upd[] debe ser un campo ejemplo $campo_no_upd[] = status';
                return $this->error->error(mensaje: 'Error $campo_no_upd debe ser un texto', data: $campo_no_upd,
                    es_final: true, fix: $fix);
            }
            if(array_key_exists($campo_no_upd, $registro)){
                unset($registro[$campo_no_upd]);
            }
        }
        return $registro;
    }

    /**
     * REG
     * Obtiene los campos de una tabla y los asigna al modelo.
     *
     * Este método:
     * 1. Valida que el nombre de la tabla no esté vacío.
     * 2. Obtiene las columnas de la tabla utilizando el método `obten_columnas`.
     * 3. Asigna los campos parseados de la tabla al modelo.
     * 4. Retorna los campos de la tabla asignados al modelo.
     *
     * @param modelo $modelo El modelo que contiene la lógica para interactuar con la base de datos.
     * @param string $tabla El nombre de la tabla de la cual se desean obtener los campos.
     *
     * @return array
     *   - Retorna un arreglo con los nombres de los campos parseados de la tabla.
     *   - En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Obtener campos de una tabla
     *  --------------------------------------
     *  $modelo = new modelo_base();
     *  $tabla = 'usuarios';
     *
     *  $resultado = $this->campos_tabla($modelo, $tabla);
     *  // $resultado contendrá un arreglo con los nombres de los campos de la tabla "usuarios".
     *
     * @example
     *  Ejemplo 2: Tabla vacía
     *  -----------------------
     *  $modelo = new modelo_base();
     *  $tabla = '';
     *
     *  $resultado = $this->campos_tabla($modelo, $tabla);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error al obtener columnas de ',
     *  //   'data' => 'Error tabla original no puede venir vacia'
     *  // ]
     */
    final public function campos_tabla(modelo $modelo, string $tabla): array
    {
        // Valida que el nombre de la tabla no esté vacío
        if ($tabla !== '') {
            // Obtiene las columnas de la tabla
            $data = $this->obten_columnas(modelo: $modelo, tabla_original: $tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener columnas de ' . $tabla, data: $data);
            }

            // Asigna los campos parseados al modelo
            $modelo->campos_tabla = $data->columnas_parseadas;
        }

        // Retorna los campos de la tabla asignados al modelo
        return $modelo->campos_tabla;
    }



    /**
     * REG
     * Carga y ajusta columnas SQL, permitiendo renombrar las tablas si es necesario.
     *
     * Esta función valida los datos proporcionados, genera las columnas SQL ajustadas
     * y permite renombrar las tablas utilizando la información proporcionada.
     *
     * @param string $columnas Columnas iniciales en formato SQL.
     *                         Puede ser una cadena vacía si no hay columnas iniciales.
     * @param array $columnas_sql Lista de columnas a procesar.
     *                            Contiene los nombres de las columnas que se deben incluir en la consulta SQL.
     * @param array $data Datos de configuración que incluyen el nombre original de la tabla.
     *                    Debe contener la clave 'nombre_original'.
     * @param modelo_base $modelo Instancia del modelo base que se utiliza para las operaciones.
     *                            Proporciona las funcionalidades de interacción con la base de datos.
     * @param string $tabla Nombre de la tabla renombrada. Si no se proporciona, se utiliza el nombre original.
     *
     * @return array|string Retorna una cadena con las columnas SQL ajustadas. En caso de error,
     *                      devuelve un array con detalles del error.
     *
     * @throws errores Si los datos proporcionados no son válidos o si ocurre un error en el proceso.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas = '';
     * $columnas_sql = ['id', 'nombre', 'estatus'];
     * $data = [
     *     'nombre_original' => 'usuarios'
     * ];
     * $tabla = 'usuarios_activos';
     *
     * $modelo = new modelo_base($link); // Instancia del modelo base
     *
     * $resultado = $miClase->carga_columna_renombre(
     *     columnas: $columnas,
     *     columnas_sql: $columnas_sql,
     *     data: $data,
     *     modelo: $modelo,
     *     tabla: $tabla
     * );
     *
     * if (is_string($resultado)) {
     *     echo "Columnas SQL ajustadas: " . $resultado;
     * } else {
     *     print_r($resultado); // Detalle del error
     * }
     * ```
     *
     * ### Ejemplo de uso con error en los datos:
     *
     * ```php
     * $columnas = '';
     * $columnas_sql = ['id', 'nombre', 'estatus'];
     * $data = []; // Falta la clave 'nombre_original'
     * $tabla = 'usuarios_activos';
     *
     * $resultado = $miClase->carga_columna_renombre(
     *     columnas: $columnas,
     *     columnas_sql: $columnas_sql,
     *     data: $data,
     *     modelo: $modelo,
     *     tabla: $tabla
     * );
     *
     * if (is_array($resultado)) {
     *     print_r($resultado); // Detalle del error
     * }
     * ```
     */
    private function carga_columna_renombre(
        string $columnas, array $columnas_sql, array $data, modelo_base $modelo, string $tabla): array|string
    {
        $valida = $this->validacion->valida_data_columna(data: $data, tabla: $tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }

        $r_columnas = $this->ajusta_columnas_completas(
            columnas: $columnas,
            columnas_en_bruto: false,
            columnas_sql: $columnas_sql,
            modelo: $modelo,
            tabla: $data['nombre_original'],
            tabla_renombrada: $tabla
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $r_columnas);
        }

        return (string)$r_columnas;
    }


    /**
     * REG
     * Genera columnas SQL dinámicas basadas en las configuraciones de tablas, modelo y estructura de datos.
     *
     * Esta función permite generar una cadena de columnas SQL dependiendo de si se aplica `columnas_by_table` o
     * si se utilizan columnas base. Valida las configuraciones de entrada y construye la salida acorde al modelo y las estructuras proporcionadas.
     *
     * @param bool $aplica_columnas_by_table Indica si se deben utilizar las columnas de `columnas_by_table`.
     *                                        - `true`: Se utiliza `columnas_by_table` para generar columnas.
     *                                        - `false`: Se generan columnas base según las estructuras y el modelo.
     * @param array $columnas_by_table Lista de tablas para generar columnas específicas.
     *                                  Ejemplo: `['usuarios', 'ordenes']`.
     * @param bool $columnas_en_bruto Indica si las columnas se procesan en su forma original (sin alias o transformaciones).
     * @param array $columnas_sql Lista de columnas SQL predefinidas.
     *                             Ejemplo: `['usuarios.id', 'usuarios.nombre']`.
     * @param array $extension_estructura Estructura adicional para generar columnas extendidas.
     * @param array $extra_join Configuración adicional para generar columnas relacionadas con uniones (`JOIN`).
     * @param modelo_base $modelo Instancia del modelo base que representa la entidad principal de la consulta SQL.
     * @param array $renombres Reglas para renombrar columnas o tablas en la consulta.
     * @param array $tablas_select Tablas seleccionadas para generar las columnas base.
     *                              Ejemplo: `['usuarios' => false, 'ordenes' => true]`.
     *
     * @return array|string Una cadena de columnas SQL generada o, en caso de error, un array con detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $aplica_columnas_by_table = false;
     * $columnas_by_table = [];
     * $columnas_en_bruto = false;
     * $columnas_sql = ['usuarios.id', 'usuarios.nombre'];
     * $extension_estructura = [];
     * $extra_join = [];
     * $modelo = new modelo_base($link);
     * $modelo->tabla = 'usuarios';
     * $renombres = [];
     * $tablas_select = ['usuarios' => false];
     *
     * $resultado = $miClase->columnas(
     *     aplica_columnas_by_table: $aplica_columnas_by_table,
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     modelo: $modelo,
     *     renombres: $renombres,
     *     tablas_select: $tablas_select
     * );
     *
     * echo $resultado;
     *
     * // Salida esperada:
     * // "usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre"
     * ```
     *
     * ### Ejemplo de error:
     *
     * - Caso: `$aplica_columnas_by_table` es `true`, pero `$columnas_by_table` está vacío.
     *
     * ```php
     * $aplica_columnas_by_table = true;
     * $columnas_by_table = [];
     * $columnas_en_bruto = false;
     * $columnas_sql = [];
     * $extension_estructura = [];
     * $extra_join = [];
     * $modelo = new modelo_base($link);
     * $modelo->tabla = 'usuarios';
     * $renombres = [];
     * $tablas_select = [];
     *
     * $resultado = $miClase->columnas(
     *     aplica_columnas_by_table: $aplica_columnas_by_table,
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     modelo: $modelo,
     *     renombres: $renombres,
     *     tablas_select: $tablas_select
     * );
     *
     * print_r($resultado);
     *
     * // Salida esperada:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error columnas_by_table esta vacia en usuarios',
     * //     'data' => [],
     * //     'es_final' => true,
     * //     'fix' => 'Si $aplica_columnas_by_table es true debe haber columnas_by_table con datos columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = nombre_tabla'
     * // ]
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$aplica_columnas_by_table`**:
     *   Indica si se utiliza `columnas_by_table` para generar columnas específicas.
     *
     * - **`$columnas_by_table`**:
     *   Lista de tablas para columnas específicas.
     *   **Ejemplo válido**: `['usuarios', 'ordenes']`.
     *   **Ejemplo inválido**: `[]` (si `$aplica_columnas_by_table` es `true`).
     *
     * - **`$columnas_en_bruto`**:
     *   Si es `true`, las columnas no tendrán alias ni transformaciones.
     *
     * - **`$modelo`**:
     *   Instancia del modelo base que representa la entidad principal.
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Devuelve una cadena de columnas SQL generada según las configuraciones.
     *
     * - **Error**:
     *   Devuelve un array con detalles si los parámetros de entrada no son válidos.
     */

    private function columnas(bool $aplica_columnas_by_table, array $columnas_by_table, bool $columnas_en_bruto,
                              array $columnas_sql, array $extension_estructura, array $extra_join, modelo_base $modelo,
                              array $renombres, array $tablas_select): array|string
    {
        if(!$aplica_columnas_by_table) {

            if(count($columnas_by_table) > 0){
                $fix = 'Si !$aplica_columnas_by_table $columnas_by_table debe ser vacio';
                return $this->error->error(mensaje: 'Error columnas_by_table tiene datos en modelo '.$modelo->tabla,
                    data: $columnas_by_table, es_final: true, fix: $fix);
            }

            $columnas = $this->columnas_base(columnas_en_bruto: $columnas_en_bruto, columnas_sql: $columnas_sql,
                extension_estructura: $extension_estructura, extra_join: $extra_join, modelo: $modelo,
                renombres: $renombres, tablas_select: $tablas_select);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas base en '.$modelo->tabla,
                    data: $columnas);
            }

        }
        else{
            if(count($columnas_by_table) === 0){
                $fix = 'Si $aplica_columnas_by_table es true debe haber columnas_by_table con datos';
                $fix .= ' columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = ';
                $fix.= "nombre_tabla";
                return $this->error->error(mensaje: 'Error columnas_by_table esta vacia en '.$modelo->tabla,
                    data: $columnas_by_table, es_final: true, fix: $fix);
            }
            $columnas = $this->columnas_by_table(columnas_by_table: $columnas_by_table,
                columnas_en_bruto: $columnas_en_bruto, modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas by table en '.$modelo->tabla,
                    data: $columnas);
            }
        }

        $columnas = trim($columnas);
        if($columnas === ''){
            $columnas = "$modelo->key_filtro_id as $modelo->key_id";
        }

        return $columnas;
    }

    /**
     * REG
     * Procesa un conjunto de columnas y atributos, asignando las columnas a las listas correspondientes (parseadas y completas).
     *
     * Este método:
     * 1. Itera sobre las columnas y atributos proporcionados.
     * 2. Si el atributo pertenece a un campo 'Field', lo agrega a las listas de columnas parseadas y completas.
     * 3. Retorna un objeto con las columnas parseadas y completas actualizadas.
     *
     * @param array $columna Un array que contiene las columnas y sus atributos.
     *                       Las claves son los nombres de los campos y los valores son los atributos correspondientes.
     * @param array $columnas_completas Un array donde se almacenan las columnas completas (con información adicional como el tipo, nulo, etc.).
     * @param array $columnas_parseadas Un array donde se almacenan las columnas parseadas (los nombres de las columnas).
     *
     * @return array|stdClass
     *   - Retorna un objeto `stdClass` con las propiedades `columnas_parseadas` y `columnas_completas`.
     *   - En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Procesando columnas con atributos
     *  -------------------------------------------
     *  $columna = [
     *      'campo1' => 'varchar(255)',
     *      'campo2' => 'int(11)'
     *  ];
     *  $columnas_completas = [];
     *  $columnas_parseadas = [];
     *
     *  $resultado = $this->columnas_attr($columna, $columnas_completas, $columnas_parseadas);
     *  // $resultado será un objeto con las propiedades:
     *  // $resultado->columnas_parseadas => ['campo1', 'campo2']
     *  // $resultado->columnas_completas => [
     *  //     'campo1' => ['campo' => 'campo1', 'Type' => 'varchar(255)', 'Key' => '', 'Null' => 'YES'],
     *  //     'campo2' => ['campo' => 'campo2', 'Type' => 'int(11)', 'Key' => '', 'Null' => 'YES']
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error al procesar las columnas
     *  -----------------------------------------
     *  $columna = [
     *      'campo1' => 'varchar(255)',
     *      'campo2' => ''
     *  ];
     *  $columnas_completas = [];
     *  $columnas_parseadas = [];
     *
     *  $resultado = $this->columnas_attr($columna, $columnas_completas, $columnas_parseadas);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error al obtener columnas',
     *  //   'data' => ...
     *  // ]
     */
    private function columnas_attr(array $columna, array $columnas_completas, array $columnas_parseadas): array|stdClass
    {
        foreach($columna as $campo => $atributo){
            // Procesa cada columna y atributo
            $columnas_field = $this->columnas_field(
                atributo: $atributo,
                campo: $campo,
                columna: $columna,
                columnas_completas: $columnas_completas,
                columnas_parseadas: $columnas_parseadas
            );

            // Verifica si hubo un error al procesar las columnas
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas_field);
            }

            // Actualiza las listas de columnas parseadas y completas
            $columnas_parseadas = $columnas_field->columnas_parseadas;
            $columnas_completas = $columnas_field->columnas_completas;
        }

        // Retorna las columnas parseadas y completas
        $data = new stdClass();
        $data->columnas_parseadas = $columnas_parseadas;
        $data->columnas_completas = $columnas_completas;
        return $data;
    }


    /**
     * REG
     * Genera las columnas base para una consulta SQL, integrando las tablas seleccionadas, extensiones,
     * uniones adicionales y renombres según las configuraciones proporcionadas.
     *
     * Esta función combina múltiples componentes relacionados con las columnas para construir
     * una representación completa de las mismas en una consulta SQL.
     *
     * @param bool $columnas_en_bruto Indica si las columnas deben procesarse sin alias o directamente
     *                                en bruto. Si es verdadero, se omiten alias y ajustes complejos.
     * @param array $columnas_sql Array de columnas SQL previamente parseadas.
     *                            Ejemplo: ['id', 'nombre', 'email'].
     * @param array $extension_estructura Configuración de las tablas que se deben extender.
     *                                    Ejemplo: ['usuarios' => ['nombre', 'email']].
     * @param array $extra_join Configuración de uniones adicionales. Cada tabla puede incluir un
     *                          renombre u opciones específicas.
     *                          Ejemplo: ['usuarios' => ['renombre' => 'clientes']].
     * @param modelo_base $modelo Instancia del modelo base utilizada para generar las columnas.
     * @param array $renombres Configuración de las tablas renombradas.
     *                         Ejemplo: ['usuarios' => ['nombre_original' => 'clientes']].
     * @param array $tablas_select Configuración de las tablas seleccionadas.
     *                             Ejemplo: ['usuarios', 'ordenes'].
     *
     * @return array|string Retorna las columnas generadas como una cadena lista para la consulta SQL.
     *                      Si ocurre un error, retorna un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas_sql = ['id', 'nombre', 'email'];
     * $extension_estructura = ['usuarios' => ['nombre', 'email']];
     * $extra_join = ['ordenes' => ['renombre' => 'pedidos']];
     * $renombres = ['usuarios' => ['nombre_original' => 'clientes']];
     * $tablas_select = ['usuarios', 'ordenes'];
     *
     * $modelo = new modelo_base($link);
     *
     * $resultado = $miClase->columnas_base(
     *     columnas_en_bruto: false,
     *     columnas_sql: $columnas_sql,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     modelo: $modelo,
     *     renombres: $renombres,
     *     tablas_select: $tablas_select
     * );
     *
     * echo $resultado;
     * // Salida esperada:
     * // "usuarios.id AS clientes_id, usuarios.nombre AS clientes_nombre, usuarios.email AS clientes_email,
     * //  ordenes.id AS pedidos_id, ordenes.nombre AS pedidos_nombre"
     * ```
     *
     * ### Ejemplo de uso con error:
     *
     * - Caso: `$tablas_select` contiene un índice numérico.
     * ```php
     * $tablas_select = [0 => 'usuarios', 1 => 'ordenes'];
     *
     * $resultado = $miClase->columnas_base(
     *     columnas_en_bruto: false,
     *     columnas_sql: $columnas_sql,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     modelo: $modelo,
     *     renombres: $renombres,
     *     tablas_select: $tablas_select
     * );
     *
     * print_r($resultado);
     * // Salida esperada:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error $key no puede ser un numero',
     * //     'data' => 0,
     * //     'es_final' => true
     * // ]
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$columnas_en_bruto`** (bool):
     *   Si es verdadero, las columnas no se procesan con alias. Se genera una lista de columnas básicas.
     *   **Ejemplo válido**: `true` o `false`.
     *
     * - **`$columnas_sql`** (array):
     *   Lista de columnas que serán utilizadas en la consulta.
     *   **Ejemplo válido**: `['id', 'nombre', 'email']`.
     *
     * - **`$extension_estructura`** (array):
     *   Define las tablas que deben extenderse con columnas adicionales.
     *   **Ejemplo válido**: `['usuarios' => ['nombre', 'email']]`.
     *
     * - **`$extra_join`** (array):
     *   Configuración para tablas adicionales y sus opciones de unión.
     *   **Ejemplo válido**: `['ordenes' => ['renombre' => 'pedidos']]`.
     *
     * - **`$modelo`** (modelo_base):
     *   Instancia de la clase `modelo_base` utilizada para manejar las operaciones de base de datos.
     *
     * - **`$renombres`** (array):
     *   Configuración para renombrar tablas en las consultas SQL.
     *   **Ejemplo válido**: `['usuarios' => ['nombre_original' => 'clientes']]`.
     *
     * - **`$tablas_select`** (array):
     *   Lista de tablas que deben incluirse en la consulta SQL.
     *   **Ejemplo válido**: `['usuarios', 'ordenes']`.
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Una cadena con las columnas generadas para la consulta SQL.
     *   **Ejemplo**: `"usuarios.id AS clientes_id, usuarios.nombre AS clientes_nombre"`.
     *
     * - **Error**:
     *   Un array con los detalles del error, como:
     *   ```php
     *   [
     *       'error' => 1,
     *       'mensaje' => 'Error $key no puede ser un numero',
     *       'data' => 0,
     *       'es_final' => true
     *   ]
     *   ```
     */
    private function columnas_base(bool $columnas_en_bruto, array $columnas_sql, array $extension_estructura,
                                   array $extra_join, modelo_base $modelo, array $renombres,
                                   array $tablas_select): array|string
    {
        $columnas = $this->columnas_tablas_select(columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $columnas_sql, modelo: $modelo, tablas_select: $tablas_select);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }

        $columnas = $this->columnas_extension(columnas: $columnas, columnas_sql: $columnas_sql,
            extension_estructura: $extension_estructura, modelo: $modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }

        $columnas = $this->columnas_extra(columnas: $columnas, columnas_sql: $columnas_sql, extra_join: $extra_join,
            modelo: $modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }

        $columnas = $this->columnas_renombre(columnas: $columnas, columnas_sql: $columnas_sql, modelo: $modelo,
            renombres: $renombres);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }

        return $columnas;
    }


    /**
     * REG
     * Obtiene las columnas de una tabla en la base de datos utilizando la consulta `DESCRIBE`.
     *
     * Este método:
     * 1. Valida que el nombre de la tabla no esté vacío y no sea numérico.
     * 2. Genera una consulta SQL para describir la tabla proporcionada.
     * 3. Ejecuta la consulta en el modelo dado para obtener las columnas de la tabla.
     *
     * @param modelo_base $modelo Instancia del modelo base utilizado para ejecutar la consulta.
     * @param string $tabla_bd Nombre de la tabla en la base de datos que se desea describir.
     *
     * @return array
     *   - Retorna un arreglo con las columnas de la tabla si la operación es exitosa.
     *   - En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Obtener columnas de una tabla válida
     *  -----------------------------------------------
     *  $modelo = new modelo_base($link);
     *  $tabla_bd = 'productos';
     *
     *  $resultado = $this->columnas_bd_native($modelo, $tabla_bd);
     *  // $resultado contendrá un arreglo con las columnas de la tabla 'productos':
     *  // [
     *  //   ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', ...],
     *  //   ['Field' => 'nombre', 'Type' => 'varchar(255)', 'Null' => 'YES', ...],
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error por tabla vacía
     *  --------------------------------
     *  $modelo = new modelo_base($link);
     *  $tabla_bd = '';
     *
     *  $resultado = $this->columnas_bd_native($modelo, $tabla_bd);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error $tabla_bd esta vacia',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error por tabla no existente
     *  ---------------------------------------
     *  $modelo = new modelo_base($link);
     *  $tabla_bd = 'tabla_inexistente';
     *
     *  $resultado = $this->columnas_bd_native($modelo, $tabla_bd);
     *  // Retorna un error indicando que no existen columnas en la tabla:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error no existen columnas',
     *  //   'data' => [...],
     *  //   ...
     *  // ]
     *
     * @throws array Retorna un arreglo con el error si ocurre algún problema durante la validación, ejecución de la consulta o procesamiento del resultado.
     */
    final public function columnas_bd_native(modelo_base $modelo, string $tabla_bd): array
    {
        // Validar que el nombre de la tabla no esté vacío y no sea numérico
        $tabla_bd = trim($tabla_bd);
        if ($tabla_bd === '') {
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia', data: $tabla_bd, es_final: true);
        }
        if (is_numeric($tabla_bd)) {
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero', data: $tabla_bd, es_final: true);
        }

        // Generar consulta SQL para describir la tabla
        $sql = (new sql())->describe_table(tabla: $tabla_bd);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sql', data: $sql);
        }

        // Ejecutar la consulta en el modelo proporcionado
        $result = $modelo->ejecuta_consulta(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }

        // Validar que existan columnas en la tabla
        if ((int)$result->n_registros === 0) {
            return $this->error->error(mensaje: 'Error no existen columnas', data: $result, es_final: true);
        }

        // Retornar las columnas de la tabla
        return $result->registros;
    }


    /**
     * REG
     * Genera columnas SQL basadas en una lista de tablas y un modelo proporcionado.
     *
     * Esta función permite construir una cadena de columnas SQL basada en las tablas especificadas en
     * `$columnas_by_table` y el modelo indicado. Verifica que las tablas sean válidas y genera las columnas correspondientes,
     * teniendo en cuenta si deben ser procesadas en bruto o no.
     *
     * @param array $columnas_by_table Lista de nombres de tablas que se utilizarán para generar las columnas SQL.
     *                                 Ejemplo: `['usuarios', 'ordenes']`.
     * @param bool $columnas_en_bruto Indica si las columnas deben procesarse en su forma original sin alias o transformaciones.
     *                                - `true`: Procesa las columnas en su forma original.
     *                                - `false`: Genera columnas con alias y transformaciones.
     * @param modelo_base $modelo Instancia del modelo base que representa la entidad sobre la cual se genera la consulta SQL.
     *
     * @return array|string Una cadena de columnas SQL generadas. En caso de error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas_by_table = ['usuarios', 'ordenes'];
     * $columnas_en_bruto = false;
     * $modelo = new modelo_base($link);
     *
     * $resultado = $miClase->columnas_by_table(
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     modelo: $modelo
     * );
     *
     * echo $resultado;
     *
     * // Salida esperada (ejemplo):
     * // "usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre, ordenes.id AS ordenes_id, ordenes.total AS ordenes_total"
     * ```
     *
     * ### Ejemplo de error:
     *
     * - Caso: `$columnas_by_table` está vacío.
     *
     * ```php
     * $columnas_by_table = [];
     * $columnas_en_bruto = false;
     * $modelo = new modelo_base($link);
     *
     * $resultado = $miClase->columnas_by_table(
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     modelo: $modelo
     * );
     *
     * print_r($resultado);
     *
     * // Salida esperada:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error debe columnas_by_table esta vacia',
     * //     'data' => [],
     * //     'es_final' => true,
     * //     'fix' => 'columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = "nombre_tabla"'
     * // ]
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$columnas_by_table`** (array):
     *   Una lista de nombres de tablas para generar columnas SQL.
     *   **Ejemplo válido**: `['usuarios', 'ordenes']`.
     *   **Ejemplo inválido**: `[]` (genera un error).
     *
     * - **`$columnas_en_bruto`** (bool):
     *   Si es `true`, las columnas se procesan en su forma original. Si es `false`, se agregan alias y transformaciones.
     *
     * - **`$modelo`** (modelo_base):
     *   Una instancia del modelo base que contiene la lógica para interactuar con la base de datos.
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Devuelve una cadena de columnas SQL generadas con alias y transformaciones según la configuración.
     *
     * - **Error**:
     *   Devuelve un array detallando el error si la entrada no es válida.
     */
    private function columnas_by_table(
        array $columnas_by_table, bool $columnas_en_bruto, modelo_base $modelo): array|string
    {
        if (count($columnas_by_table) === 0) {
            $fix = 'columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = "nombre_tabla"';
            return $this->error->error(
                mensaje: 'Error debe columnas_by_table esta vacia',
                data: $columnas_by_table,
                es_final: true,
                fix: $fix
            );
        }

        $init = $this->init_columnas_by_table(columnas_by_table: $columnas_by_table);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al inicializa datos de columnas by table',
                data: $init
            );
        }

        $columnas = $this->columnas_tablas_select(
            columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $init->columnas_sql,
            modelo: $modelo,
            tablas_select: $init->tablas_select
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al integrar columnas',
                data: $columnas
            );
        }

        return $columnas;
    }

    /**
     * REG
     * Combina las columnas principales y adicionales en una cadena SQL.
     *
     * Esta función se encarga de gestionar la combinación de las columnas principales de una consulta SQL
     * con las columnas adicionales, formateando correctamente la salida para su uso en una sentencia SQL.
     *
     * @param string $columnas_extra_sql Columnas adicionales que se deben agregar a la consulta SQL.
     *                                   Estas columnas pueden ser cálculos o valores adicionales.
     * @param string $columnas_sql Columnas principales generadas previamente para la consulta SQL.
     *
     * @return string Cadena de texto que combina las columnas principales y adicionales separadas por comas.
     *
     * @example
     * // Caso 1: Solo columnas principales
     * $columnas_sql = 'tabla.id, tabla.nombre';
     * $columnas_extra_sql = '';
     * $resultado = $this->columnas_envio(columnas_extra_sql: $columnas_extra_sql, columnas_sql: $columnas_sql);
     * // Resultado: 'tabla.id, tabla.nombre'
     *
     * @example
     * // Caso 2: Solo columnas adicionales
     * $columnas_sql = '';
     * $columnas_extra_sql = 'SUM(tabla.total) AS total_sumado';
     * $resultado = $this->columnas_envio(columnas_extra_sql: $columnas_extra_sql, columnas_sql: $columnas_sql);
     * // Resultado: 'SUM(tabla.total) AS total_sumado'
     *
     * @example
     * // Caso 3: Combinación de columnas principales y adicionales
     * $columnas_sql = 'tabla.id, tabla.nombre';
     * $columnas_extra_sql = 'SUM(tabla.total) AS total_sumado';
     * $resultado = $this->columnas_envio(columnas_extra_sql: $columnas_extra_sql, columnas_sql: $columnas_sql);
     * // Resultado: 'tabla.id, tabla.nombre, SUM(tabla.total) AS total_sumado'
     *
     * @example
     * // Caso 4: Ambas cadenas vacías
     * $columnas_sql = '';
     * $columnas_extra_sql = '';
     * $resultado = $this->columnas_envio(columnas_extra_sql: $columnas_extra_sql, columnas_sql: $columnas_sql);
     * // Resultado: ''
     */
    private function columnas_envio(string $columnas_extra_sql, string $columnas_sql): string
    {
        if (trim($columnas_sql) === '' && trim($columnas_extra_sql) !== '') {
            $columnas_envio = $columnas_extra_sql;
        } else {
            $columnas_envio = $columnas_sql;
            if ($columnas_extra_sql !== '') {
                $columnas_envio .= ',' . $columnas_extra_sql;
            }
        }
        return $columnas_envio;
    }


    /**
     * REG
     * Genera y ajusta las columnas SQL para una estructura de extensión basada en múltiples tablas.
     *
     * Este método procesa las tablas de una estructura de extensión para generar las columnas SQL correspondientes.
     * Permite ajustar las columnas de múltiples tablas relacionadas utilizando un modelo base.
     *
     * @param string $columnas Cadena inicial de columnas SQL a ajustar o expandir.
     *                         Puede estar vacía o contener columnas previamente definidas.
     * @param array $columnas_sql Lista de nombres de columnas que se deben procesar.
     *                            Cada elemento del array representa una columna específica a incluir.
     * @param array $extension_estructura Estructura de extensión que contiene las tablas adicionales a procesar.
     *                                    El array debe tener los nombres de las tablas como claves, con datos asociados como valores.
     * @param modelo_base $modelo Instancia del modelo base que se utiliza para interactuar con la base de datos.
     *
     * @return array|string Una cadena con las columnas SQL ajustadas si el proceso es exitoso.
     *                      En caso de error, devuelve un array con los detalles del error.
     *
     * @throws errores Si alguno de los parámetros es inválido o se produce un error durante el ajuste de columnas.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas = ''; // Cadena inicial de columnas
     * $columnas_sql = ['id', 'nombre', 'estatus']; // Columnas a procesar
     * $extension_estructura = [
     *     'usuarios' => ['campo_adicional' => 'valor'],
     *     'perfiles' => ['campo_adicional' => 'valor']
     * ];
     * $modelo = new modelo_base($link); // Instancia del modelo base
     *
     * $resultado = $miClase->columnas_extension(
     *     columnas: $columnas,
     *     columnas_sql: $columnas_sql,
     *     extension_estructura: $extension_estructura,
     *     modelo: $modelo
     * );
     *
     * if (is_string($resultado)) {
     *     echo "Columnas SQL generadas: " . $resultado;
     * } else {
     *     print_r($resultado); // Detalle del error
     * }
     * ```
     *
     * ### Ejemplo con datos inválidos:
     *
     * ```php
     * $columnas = ''; // Cadena inicial de columnas
     * $columnas_sql = ['id', 'nombre']; // Columnas a procesar
     * $extension_estructura = [
     *     123 => ['campo_adicional' => 'valor'] // Error: La clave del array no puede ser un número.
     * ];
     * $modelo = new modelo_base($link);
     *
     * $resultado = $miClase->columnas_extension(
     *     columnas: $columnas,
     *     columnas_sql: $columnas_sql,
     *     extension_estructura: $extension_estructura,
     *     modelo: $modelo
     * );
     *
     * if (is_array($resultado)) {
     *     print_r($resultado); // Muestra el detalle del error
     * }
     * ```
     *
     * ### Detalle de parámetros:
     *
     * - **`$columnas`** (string):
     *   Cadena inicial de columnas SQL. Puede estar vacía o contener columnas ya generadas.
     *
     *   **Ejemplo**: `'usuarios.id, usuarios.nombre'`.
     *
     * - **`$columnas_sql`** (array):
     *   Lista de columnas específicas a incluir en la consulta SQL. Cada elemento es el nombre de una columna.
     *
     *   **Ejemplo**: `['id', 'nombre', 'estatus']`.
     *
     * - **`$extension_estructura`** (array):
     *   Estructura que define las tablas adicionales para procesar columnas.
     *   Las claves del array representan los nombres de las tablas, y los valores son arrays con datos adicionales.
     *
     *   **Ejemplo**:
     *   ```php
     *   [
     *       'usuarios' => ['campo_adicional' => 'valor'],
     *       'perfiles' => ['campo_adicional' => 'valor']
     *   ]
     *   ```
     *
     * - **`$modelo`** (modelo_base):
     *   Instancia del modelo base que interactúa con la base de datos.
     *
     *   **Ejemplo**: `$modelo = new modelo_base($link);`.
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Devuelve una cadena SQL como:
     *   ```sql
     *   usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre, perfiles.id AS perfiles_id
     *   ```
     *
     * - **Error**:
     *   Devuelve un array detallando el problema, como:
     *   ```php
     *   [
     *       'error' => 1,
     *       'mensaje' => 'Error ingrese un array valido usuarios',
     *       'data' => [
     *           'usuarios' => ['campo_adicional' => 'valor']
     *       ]
     *   ]
     *   ```
     */
    private function columnas_extension(
        string $columnas, array $columnas_sql, array $extension_estructura, modelo_base $modelo): array|string
    {
        $columnas_env = $columnas;
        foreach ($extension_estructura as $tabla => $data) {
            $tabla = str_replace('models\\', '', $tabla);
            if (is_numeric($tabla)) {
                return $this->error->error(
                    mensaje: 'Error ingrese un array valido ' . $tabla,
                    data: $extension_estructura,
                    es_final: true
                );
            }

            $columnas_env = $this->ajusta_columnas_completas(
                columnas: $columnas_env,
                columnas_en_bruto: false,
                columnas_sql: $columnas_sql,
                modelo: $modelo,
                tabla: $tabla,
                tabla_renombrada: ''
            );
            if (errores::$error) {
                return $this->error->error(
                    mensaje: 'Error al integrar envio',
                    data: $columnas_env
                );
            }
        }
        return $columnas_env;
    }


    /**
     * REG
     * Genera y ajusta las columnas adicionales para una consulta SQL basándose en un conjunto de tablas
     * y configuraciones de unión adicionales.
     *
     * Esta función recorre un array de uniones (`extra_join`) y ajusta las columnas para cada tabla especificada.
     * Valida que las tablas y los datos asociados sean válidos antes de integrarlas en la consulta SQL.
     *
     * @param string $columnas Columnas iniciales de la consulta. Puede ser una cadena vacía si no hay columnas iniciales.
     * @param array $columnas_sql Array de columnas SQL parseadas previamente.
     * @param array $extra_join Array asociativo que contiene las tablas adicionales y sus configuraciones.
     *                          Cada tabla debe tener un array con sus configuraciones específicas.
     * @param modelo_base $modelo Instancia del modelo base que se utiliza para ajustar las columnas.
     *
     * @return array|string Retorna las columnas ajustadas como una cadena. En caso de error, retorna un array con
     *                      los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas = '';
     * $columnas_sql = ['id', 'nombre', 'email'];
     * $extra_join = [
     *     'usuarios' => ['renombre' => 'clientes'],
     *     'ordenes' => []
     * ];
     * $modelo = new modelo_base($link);
     *
     * $resultado = $miClase->columnas_extra(
     *     columnas: $columnas,
     *     columnas_sql: $columnas_sql,
     *     extra_join: $extra_join,
     *     modelo: $modelo
     * );
     * echo $resultado;
     * // Salida esperada: "usuarios.id AS clientes_id, usuarios.nombre AS clientes_nombre, usuarios.email AS clientes_email, ordenes.id AS ordenes_id, ordenes.nombre AS ordenes_nombre, ordenes.email AS ordenes_email"
     * ```
     *
     * ### Ejemplo de datos inválidos:
     *
     * ```php
     * // Caso: La tabla es numérica
     * $extra_join = [
     *     123 => ['renombre' => 'clientes']
     * ];
     * $resultado = $miClase->columnas_extra(
     *     columnas: '',
     *     columnas_sql: ['id', 'nombre'],
     *     extra_join: $extra_join,
     *     modelo: $modelo
     * );
     * print_r($resultado);
     * // Salida:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error ingrese un array valido 123',
     * //     'data' => $extra_join,
     * //     'es_final' => true
     * // ]
     *
     * // Caso: El dato asociado a la tabla no es un array
     * $extra_join = [
     *     'usuarios' => 'clientes'
     * ];
     * $resultado = $miClase->columnas_extra(
     *     columnas: '',
     *     columnas_sql: ['id', 'nombre'],
     *     extra_join: $extra_join,
     *     modelo: $modelo
     * );
     * print_r($resultado);
     * // Salida:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error data debe ser un array',
     * //     'data' => 'clientes',
     * //     'es_final' => true
     * // ]
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$columnas`** (string):
     *   Las columnas iniciales en la consulta. Si no hay columnas iniciales, puede ser una cadena vacía.
     *   **Ejemplo válido**: `''` o `'id, nombre'`.
     *
     * - **`$columnas_sql`** (array):
     *   Un array que contiene las columnas parseadas previamente.
     *   **Ejemplo válido**: `['id', 'nombre', 'email']`.
     *
     * - **`$extra_join`** (array):
     *   Array asociativo que define las tablas adicionales y sus configuraciones.
     *   Cada clave representa el nombre de la tabla, y su valor debe ser un array con las configuraciones opcionales, como `renombre`.
     *   **Ejemplo válido**:
     *   ```php
     *   [
     *       'usuarios' => ['renombre' => 'clientes'],
     *       'ordenes' => []
     *   ]
     *   ```
     *
     * - **`$modelo`** (modelo_base):
     *   Instancia de la clase modelo base utilizada para generar las columnas.
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Una cadena con las columnas ajustadas para la consulta SQL.
     *   **Ejemplo**: `'usuarios.id AS clientes_id, usuarios.nombre AS clientes_nombre'`.
     *
     * - **Error**:
     *   Un array con los detalles del error, como:
     *   ```php
     *   [
     *       'error' => 1,
     *       'mensaje' => 'Error data debe ser un array',
     *       'data' => 'clientes',
     *       'es_final' => true
     *   ]
     *   ```
     */
    private function columnas_extra(
        string $columnas, array $columnas_sql, array $extra_join, modelo_base $modelo): array|string
    {
        $columnas_env = $columnas;
        foreach ($extra_join as $tabla => $data) {
            $tabla = str_replace('models\\', '', $tabla);

            if (is_numeric($tabla)) {
                return $this->error->error(mensaje: 'Error ingrese un array valido ' . $tabla,
                    data: $extra_join, es_final: true);
            }
            if (!is_array($data)) {
                return $this->error->error(mensaje: 'Error data debe ser un array ',
                    data: $data, es_final: true);
            }

            $tabla_renombrada = $this->tabla_renombrada_extra(data: $data, tabla: $tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar tabla_renombrada', data: $tabla_renombrada);
            }

            $columnas_env = $this->ajusta_columnas_completas(columnas: $columnas_env, columnas_en_bruto: false,
                columnas_sql: $columnas_sql, modelo: $modelo, tabla: $tabla, tabla_renombrada: $tabla_renombrada);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas_env);
            }
        }
        return $columnas_env;
    }



    /**
     * REG
     * Procesa las columnas de una tabla, asignando valores a las columnas parseadas y completas cuando el campo es 'Field'.
     *
     * Este método:
     * 1. Verifica si el campo es 'Field' y si es así, asigna la columna al arreglo de columnas parseadas y completas.
     * 2. Si hay errores durante el proceso, los captura y retorna un mensaje de error.
     * 3. Retorna un objeto con las columnas parseadas y completas.
     *
     * @param string|null $atributo El nombre del atributo que se desea asignar a las columnas parseadas y completas.
     * @param string $campo El nombre del campo que se va a procesar. Si es 'Field', se realiza el procesamiento.
     * @param array $columna Los detalles de la columna que se están procesando.
     * @param array $columnas_completas El arreglo donde se almacenan las columnas completas.
     * @param array $columnas_parseadas El arreglo donde se almacenan las columnas parseadas.
     *
     * @return array|stdClass
     *   - Retorna un objeto `stdClass` con las propiedades `columnas_parseadas` y `columnas_completas`.
     *   - En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Procesando una columna de tipo 'Field'
     *  -------------------------------------------------
     *  $atributo = 'nombre';
     *  $campo = 'Field';
     *  $columna = [
     *      'Type' => 'varchar(255)',
     *      'Null' => 'YES',
     *      'Key' => 'PRI'
     *  ];
     *  $columnas_completas = [];
     *  $columnas_parseadas = [];
     *
     *  $resultado = $this->columnas_field($atributo, $campo, $columna, $columnas_completas, $columnas_parseadas);
     *  // $resultado será un objeto con las columnas parseadas y completas actualizadas:
     *  // $resultado->columnas_parseadas => ['nombre']
     *  // $resultado->columnas_completas => [
     *  //   'nombre' => [
     *  //       'campo' => 'nombre',
     *  //       'Type' => 'varchar(255)',
     *  //       'Key' => 'PRI',
     *  //       'Null' => 'YES'
     *  //   ]
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error en columnas parseadas
     *  --------------------------------------
     *  $atributo = 'nombre';
     *  $campo = 'Field';
     *  $columna = [
     *      'Type' => 'varchar(255)',
     *      'Null' => 'YES'
     *  ];
     *  $columnas_completas = [];
     *  $columnas_parseadas = 'no es un array';
     *
     *  $resultado = $this->columnas_field($atributo, $campo, $columna, $columnas_completas, $columnas_parseadas);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error al obtener columnas parseadas',
     *  //   'data' => 'no es un array'
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error al obtener columnas completas
     *  ---------------------------------------------
     *  $atributo = 'nombre';
     *  $campo = 'Field';
     *  $columna = [
     *      'Type' => 'varchar(255)',
     *      'Null' => 'YES',
     *  ];
     *  $columnas_completas = [];
     *  $columnas_parseadas = [];
     *
     *  $resultado = $this->columnas_field($atributo, $campo, $columna, $columnas_completas, $columnas_parseadas);
     *  // Retorna un error indicando que hay un problema al obtener las columnas completas:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error al obtener columnas completas',
     *  //   'data' => ...
     *  // ]
     */
    private function columnas_field(string|null $atributo, string $campo, array $columna, array $columnas_completas,
                                    array $columnas_parseadas): array|stdClass
    {
        // Si el campo es 'Field', procesa la columna
        if ($campo === 'Field') {
            // Asigna la columna al arreglo de columnas parseadas
            $columnas_parseadas = $this->asigna_columnas_parseadas(atributo: $atributo,
                columnas_parseadas: $columnas_parseadas);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener columnas parseadas', data: $columnas_parseadas);
            }

            // Asigna la columna completa al arreglo de columnas completas
            $columnas_completas = $this->asigna_columna_completa(atributo: $atributo, columna: $columna,
                columnas_completas: $columnas_completas);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener columnas completas', data: $columnas_completas);
            }
        }

        // Devuelve las columnas parseadas y completas
        $data = new stdClass();
        $data->columnas_parseadas = $columnas_parseadas;
        $data->columnas_completas = $columnas_completas;
        return $data;
    }


    /**
     * REG
     * Genera columnas completas para una consulta SQL, basándose en diferentes configuraciones y estructuras de datos.
     *
     * Esta función evalúa si se deben aplicar columnas específicas por tabla (`columnas_by_table`) o si se deben utilizar
     * columnas generales, y construye la cadena de columnas SQL según las reglas establecidas.
     *
     * @param array $columnas_by_table Listado de tablas para las cuales se generarán columnas específicas.
     *                                  Ejemplo: `['usuarios', 'ordenes']`.
     * @param bool $columnas_en_bruto Indica si las columnas deben procesarse en su forma original sin alias ni modificaciones.
     * @param array $columnas_sql Columnas SQL predefinidas para construir la consulta.
     *                             Ejemplo: `['usuarios.id', 'usuarios.nombre']`.
     * @param array $extension_estructura Estructuras adicionales para extender las columnas de la consulta.
     * @param array $extra_join Configuración para unir columnas adicionales provenientes de otras tablas relacionadas.
     * @param modelo_base $modelo Instancia del modelo base que representa la entidad principal de la consulta.
     *                            Ejemplo: `$modelo->tabla = 'usuarios';`.
     * @param array $renombres Reglas para renombrar tablas o columnas en la consulta.
     * @param array $tablas_select Listado de tablas que serán seleccionadas en la consulta.
     *                              Ejemplo: `['usuarios' => false, 'ordenes' => true]`.
     *
     * @return array|string Una cadena de columnas SQL generada o, en caso de error, un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas_by_table = ['usuarios'];
     * $columnas_en_bruto = false;
     * $columnas_sql = ['usuarios.id', 'usuarios.nombre'];
     * $extension_estructura = [];
     * $extra_join = [];
     * $modelo = new modelo_base($link);
     * $modelo->tabla = 'usuarios';
     * $renombres = [];
     * $tablas_select = ['usuarios' => false];
     *
     * $resultado = $miClase->columnas_full(
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     modelo: $modelo,
     *     renombres: $renombres,
     *     tablas_select: $tablas_select
     * );
     *
     * echo $resultado;
     * // Salida esperada:
     * // "usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre"
     * ```
     *
     * ### Ejemplo de error:
     *
     * - Caso: `$columnas_by_table` está vacío pero debería contener datos.
     *
     * ```php
     * $columnas_by_table = [];
     * $columnas_en_bruto = false;
     * $columnas_sql = [];
     * $extension_estructura = [];
     * $extra_join = [];
     * $modelo = new modelo_base($link);
     * $modelo->tabla = 'usuarios';
     * $renombres = [];
     * $tablas_select = [];
     *
     * $resultado = $miClase->columnas_full(
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     modelo: $modelo,
     *     renombres: $renombres,
     *     tablas_select: $tablas_select
     * );
     *
     * print_r($resultado);
     * // Salida esperada:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error al verificar aplicacion de columnas en modelo usuarios',
     * //     'data' => null
     * // ]
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$columnas_by_table`**:
     *   Lista de tablas para columnas específicas. Ejemplo: `['usuarios', 'ordenes']`.
     *
     * - **`$columnas_en_bruto`**:
     *   Si es `true`, las columnas no tendrán alias ni transformaciones.
     *
     * - **`$modelo`**:
     *   Instancia del modelo base que representa la entidad principal. Ejemplo: `$modelo->tabla = 'usuarios';`.
     *
     * - **`$renombres`**:
     *   Reglas de renombramiento para tablas o columnas. Ejemplo: `['usuarios' => 'usuarios_renombrados']`.
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Devuelve una cadena de columnas SQL generada según las configuraciones.
     *
     * - **Error**:
     *   Devuelve un array con detalles del error si los parámetros de entrada no son válidos.
     */

    private function columnas_full(array $columnas_by_table, bool $columnas_en_bruto, array $columnas_sql,
                                   array $extension_estructura, array $extra_join, modelo_base $modelo,
                                   array $renombres, array $tablas_select): array|string
    {

        $aplica_columnas_by_table = $this->aplica_columnas_by_table(columnas_by_table: $columnas_by_table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al verificar aplicacion de columnas en modelo '.$modelo->tabla,
                data: $aplica_columnas_by_table);
        }

        $columnas = $this->columnas(aplica_columnas_by_table: $aplica_columnas_by_table,
            columnas_by_table: $columnas_by_table, columnas_en_bruto: $columnas_en_bruto, columnas_sql: $columnas_sql,
            extension_estructura: $extension_estructura, extra_join: $extra_join, modelo: $modelo,
            renombres: $renombres, tablas_select: $tablas_select);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas en modelo '.$modelo->tabla,
                data: $columnas);
        }


        return $columnas;


    }

    /**
     * REG
     * Genera las columnas SQL ajustadas para un conjunto de tablas renombradas.
     *
     * Esta función procesa un conjunto de tablas renombradas y sus configuraciones asociadas
     * para generar una cadena de columnas SQL ajustadas, asegurándose de que los datos de entrada sean válidos.
     *
     * @param string $columnas Columnas iniciales en formato SQL. Puede ser una cadena vacía.
     * @param array $columnas_sql Lista de columnas a procesar, especificadas por nombre.
     *                            Cada elemento representa un nombre de columna a incluir.
     * @param modelo_base $modelo Instancia del modelo base que gestiona la interacción con la base de datos.
     * @param array $renombres Conjunto de tablas renombradas con sus configuraciones asociadas.
     *                         Cada clave representa el nombre de la tabla y el valor debe ser un array que
     *                         incluya la clave `nombre_original`.
     *
     * @return array|string Una cadena con las columnas SQL ajustadas si el proceso es exitoso.
     *                      En caso de error, devuelve un array con detalles del error.
     *
     * @throws errores Si algún dato de entrada es inválido o ocurre un problema durante la generación.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas = '';
     * $columnas_sql = ['id', 'nombre', 'estatus'];
     * $modelo = new modelo_base($link); // Instancia del modelo base
     * $renombres = [
     *     'usuarios_activos' => ['nombre_original' => 'usuarios'],
     *     'productos_disponibles' => ['nombre_original' => 'productos']
     * ];
     *
     * $resultado = $miClase->columnas_renombre(
     *     columnas: $columnas,
     *     columnas_sql: $columnas_sql,
     *     modelo: $modelo,
     *     renombres: $renombres
     * );
     *
     * if (is_string($resultado)) {
     *     echo "Columnas SQL generadas: " . $resultado;
     * } else {
     *     print_r($resultado); // Detalle del error
     * }
     * ```
     *
     * ### Ejemplo de uso con datos inválidos:
     *
     * ```php
     * $columnas = '';
     * $columnas_sql = ['id', 'nombre', 'estatus'];
     * $modelo = new modelo_base($link);
     * $renombres = [
     *     'usuarios_activos' => 'usuarios' // Error: el valor debe ser un array
     * ];
     *
     * $resultado = $miClase->columnas_renombre(
     *     columnas: $columnas,
     *     columnas_sql: $columnas_sql,
     *     modelo: $modelo,
     *     renombres: $renombres
     * );
     *
     * if (is_array($resultado)) {
     *     print_r($resultado); // Muestra el detalle del error
     * }
     * ```
     */
    private function columnas_renombre(
        string $columnas, array $columnas_sql, modelo_base $modelo, array $renombres): array|string
    {
        foreach ($renombres as $tabla => $data) {
            if (!is_array($data)) {
                return $this->error->error(
                    mensaje: 'Error data debe ser array ' . $tabla,
                    data: $data,
                    es_final: true
                );
            }
            $r_columnas = $this->carga_columna_renombre(
                columnas: $columnas,
                columnas_sql: $columnas_sql,
                data: $data,
                modelo: $modelo,
                tabla: $tabla
            );
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas', data: $r_columnas);
            }
            $columnas = (string)$r_columnas;
        }

        return $columnas;
    }


    /**
     * REG
     * Construye una cadena SQL para columnas con alias y formato opcional en bruto.
     *
     * Esta función genera una cadena SQL para columnas, permitiendo la inclusión de alias y el uso de
     * formato "en bruto" si se requiere. Se asegura de validar las entradas y agregar las columnas de forma adecuada
     * con sus correspondientes alias y tabla de origen.
     *
     * @param string $alias_columnas Alias para la columna en la consulta SQL.
     * @param string $columna_parseada Nombre de la columna que se procesará.
     * @param bool $columnas_en_bruto Indica si se deben usar las columnas sin alias (en bruto).
     * @param string $columnas_sql Cadena acumulativa que contiene las columnas SQL generadas previamente.
     * @param string $tabla_nombre Nombre de la tabla de donde proviene la columna.
     *
     * @return array|string Devuelve la cadena SQL actualizada con las nuevas columnas procesadas.
     *                      En caso de error, devuelve un array con los detalles del problema.
     *
     * @example
     * // Caso 1: Agregar una columna con alias
     * $resultado = $this->columnas_sql(
     *     alias_columnas: 'alias_id',
     *     columna_parseada: 'id',
     *     columnas_en_bruto: false,
     *     columnas_sql: '',
     *     tabla_nombre: 'usuarios'
     * );
     * // Resultado: 'usuarios.id AS alias_id'
     *
     * @example
     * // Caso 2: Agregar múltiples columnas en una cadena acumulativa
     * $resultado = $this->columnas_sql(
     *     alias_columnas: 'alias_nombre',
     *     columna_parseada: 'nombre',
     *     columnas_en_bruto: false,
     *     columnas_sql: 'usuarios.id AS alias_id',
     *     tabla_nombre: 'usuarios'
     * );
     * // Resultado: 'usuarios.id AS alias_id, usuarios.nombre AS alias_nombre'
     *
     * @example
     * // Caso 3: Uso de columnas en bruto (sin alias)
     * $resultado = $this->columnas_sql(
     *     alias_columnas: '',
     *     columna_parseada: 'nombre',
     *     columnas_en_bruto: true,
     *     columnas_sql: '',
     *     tabla_nombre: 'usuarios'
     * );
     * // Resultado: 'usuarios.nombre AS nombre'
     *
     * @throws array En caso de que alguno de los parámetros no sea válido o esté vacío, se retorna un array
     *               con los detalles del error.
     */
    private function columnas_sql(string $alias_columnas, string $columna_parseada, bool $columnas_en_bruto,
                                  string $columnas_sql, string $tabla_nombre): array|string
    {
        $valida = $this->valida_columnas_sql(
            alias_columnas: $alias_columnas,
            columna_parseada: $columna_parseada,
            tabla_nombre: $tabla_nombre
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos de entrada', data: $valida);
        }

        $coma = $this->coma(columnas_sql: $columnas_sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar coma', data: $coma);
        }

        if ($columnas_en_bruto) {
            $alias_columnas = $columna_parseada;
        }

        $columnas_sql .= $coma . $tabla_nombre . '.' . $columna_parseada . ' AS ' . $alias_columnas;

        return $columnas_sql;
    }


    /**
     * REG
     * Procesa un array de columnas y las organiza en dos categorías: columnas parseadas y columnas completas.
     *
     * Este método:
     * 1. Itera sobre un conjunto de columnas que se espera sea un array de arrays.
     * 2. Para cada columna, se valida y organiza en dos arrays: uno para las columnas parseadas (solo nombres) y otro para las columnas completas (información detallada como tipo, clave, nulidad, etc.).
     * 3. Retorna un objeto con las columnas parseadas y completas.
     *
     * @param array $columnas Un array que contiene arrays con información sobre las columnas (por ejemplo, nombre, tipo, etc.).
     *
     * @return array|stdClass
     *   - Retorna un objeto `stdClass` con las propiedades `columnas_parseadas` (nombres de las columnas) y `columnas_completas` (información detallada de las columnas).
     *   - En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Procesando un conjunto de columnas
     *  -------------------------------------------
     *  $columnas = [
     *      ['Field' => 'id', 'Type' => 'int', 'Null' => 'NO', 'Key' => 'PRI'],
     *      ['Field' => 'nombre', 'Type' => 'varchar(255)', 'Null' => 'YES', 'Key' => '']
     *  ];
     *
     *  $resultado = $this->columnas_sql_array($columnas);
     *  // $resultado->columnas_parseadas será ['id', 'nombre']
     *  // $resultado->columnas_completas será [
     *  //     'id' => ['Field' => 'id', 'Type' => 'int', 'Null' => 'NO', 'Key' => 'PRI'],
     *  //     'nombre' => ['Field' => 'nombre', 'Type' => 'varchar(255)', 'Null' => 'YES', 'Key' => '']
     *  // ]
     *
     * @example
     *  Ejemplo 2: Error por tipo de columna no válido
     *  ---------------------------------------------
     *  $columnas = [
     *      'id' => ['Field' => 'id', 'Type' => 'int', 'Null' => 'NO', 'Key' => 'PRI']  // Error: debe ser un array
     *  ];
     *
     *  $resultado = $this->columnas_sql_array($columnas);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error $columna debe ser un array',
     *  //   'data' => $columnas
     *  // ]
     */
    private function columnas_sql_array(array $columnas): array|stdClass
    {
        // Inicializa los arrays de columnas parseadas y completas
        $columnas_parseadas = array();
        $columnas_completas = array();

        // Itera sobre cada columna
        foreach($columnas as $columna){
            // Verifica que cada columna sea un array
            if(!is_array($columna)){
                return $this->error->error(
                    mensaje: 'Error $columna debe ser un array',
                    data: $columnas,
                    es_final: true
                );
            }

            // Procesa la columna y obtiene las columnas parseadas y completas
            $columnas_field = $this->columnas_attr(
                columna: $columna,
                columnas_completas: $columnas_completas,
                columnas_parseadas: $columnas_parseadas
            );

            // Verifica si hubo un error al obtener las columnas
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas_field);
            }

            // Actualiza las listas de columnas parseadas y completas
            $columnas_parseadas = $columnas_field->columnas_parseadas;
            $columnas_completas = $columnas_field->columnas_completas;
        }

        // Crea un objeto con las propiedades de columnas parseadas y completas
        $data = new stdClass();
        $data->columnas_parseadas = $columnas_parseadas;
        $data->columnas_completas = $columnas_completas;

        return $data;
    }


    /**
     * REG
     * Inicializa y genera una cadena SQL con columnas de una tabla, aplicando alias y validaciones según los parámetros.
     *
     * Esta función recorre un conjunto de columnas parseadas y genera una cadena SQL con las columnas seleccionadas,
     * asignando alias y aplicando filtros si es necesario. También permite manejar columnas en formato en bruto.
     *
     * @param array $columnas Lista de columnas permitidas para incluir en la consulta SQL. Si está vacío, se incluirán todas.
     * @param bool $columnas_en_bruto Indica si se deben usar columnas en formato en bruto, sin alias personalizados.
     * @param array $columnas_parseadas Columnas procesadas que se incluirán en la cadena SQL.
     * @param string $tabla_nombre Nombre de la tabla que contiene las columnas.
     *
     * @return array|string Devuelve una cadena SQL con las columnas generadas o un array con detalles de error en caso de fallo.
     *
     * @example
     * // Caso 1: Generar columnas SQL con alias
     * $columnas = ['usuarios_id', 'usuarios_nombre'];
     * $columnas_parseadas = ['id', 'nombre'];
     * $tabla_nombre = 'usuarios';
     * $resultado = $this->columnas_sql_init(
     *     columnas: $columnas,
     *     columnas_en_bruto: false,
     *     columnas_parseadas: $columnas_parseadas,
     *     tabla_nombre: $tabla_nombre
     * );
     * // Resultado: 'usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre'
     *
     * @example
     * // Caso 2: Generar columnas SQL sin restricciones específicas (todas las columnas)
     * $columnas = [];
     * $columnas_parseadas = ['id', 'nombre', 'email'];
     * $tabla_nombre = 'usuarios';
     * $resultado = $this->columnas_sql_init(
     *     columnas: $columnas,
     *     columnas_en_bruto: false,
     *     columnas_parseadas: $columnas_parseadas,
     *     tabla_nombre: $tabla_nombre
     * );
     * // Resultado: 'usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre, usuarios.email AS usuarios_email'
     *
     * @example
     * // Caso 3: Generar columnas SQL en formato en bruto
     * $columnas = [];
     * $columnas_parseadas = ['id', 'nombre'];
     * $tabla_nombre = 'usuarios';
     * $resultado = $this->columnas_sql_init(
     *     columnas: $columnas,
     *     columnas_en_bruto: true,
     *     columnas_parseadas: $columnas_parseadas,
     *     tabla_nombre: $tabla_nombre
     * );
     * // Resultado: 'usuarios.id AS id, usuarios.nombre AS nombre'
     *
     * @throws array Devuelve un array con los detalles del error si $tabla_nombre está vacío
     *               o si ocurre un error al generar la cadena SQL.
     */
    private function columnas_sql_init(array $columnas, bool $columnas_en_bruto, array $columnas_parseadas,
                                       string $tabla_nombre): array|string
    {
        if ($tabla_nombre === '') {
            return $this->error->error(mensaje: 'Error $tabla_nombre no puede venir vacia', data: $tabla_nombre, es_final: true);
        }
        $columnas_sql = '';
        foreach ($columnas_parseadas as $columna_parseada) {
            $alias_columnas = $tabla_nombre . '_' . $columna_parseada;
            if ((count($columnas) > 0) && !in_array($alias_columnas, $columnas, true)) {
                continue;
            }
            $columnas_sql = $this->columnas_sql(
                alias_columnas: $alias_columnas,
                columna_parseada: $columna_parseada,
                columnas_en_bruto: $columnas_en_bruto,
                columnas_sql: $columnas_sql,
                tabla_nombre: $tabla_nombre
            );
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener columnas sql', data: $columnas_sql);
            }
        }
        return $columnas_sql;
    }


    /**
     * REG
     * Genera una cadena SQL con las columnas seleccionadas de múltiples tablas.
     *
     * Este método construye una cadena SQL que incluye las columnas de las tablas indicadas en `$tablas_select`.
     * Las columnas se procesan de acuerdo con las configuraciones de `$columnas_en_bruto` y `$columnas_sql`.
     *
     * @param bool $columnas_en_bruto Determina si las columnas deben incluirse en su formato bruto o con alias.
     *                                - `true`: Incluye las columnas sin alias.
     *                                - `false`: Incluye las columnas con alias.
     * @param array $columnas_sql Lista de nombres de columnas que se deben procesar.
     *                            Cada elemento representa una columna a incluir.
     * @param modelo_base $modelo Instancia del modelo base que se utiliza para interactuar con la base de datos.
     * @param array $tablas_select Lista de tablas de las cuales se seleccionarán las columnas.
     *                             El array debe tener el nombre de las tablas como claves y valores.
     *
     * @return array|string Una cadena con las columnas SQL generadas si el proceso es exitoso.
     *                      En caso de error, devuelve un array con los detalles del error.
     *
     * @throws errores Si alguno de los parámetros es inválido o se produce un error durante la generación.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas_en_bruto = false;
     * $columnas_sql = ['id', 'nombre', 'estatus'];
     * $modelo = new modelo_base($link); // Instancia del modelo base
     * $tablas_select = [
     *     'usuarios' => 'usuarios',
     *     'perfiles' => 'perfiles'
     * ];
     *
     * $resultado = $miClase->columnas_tablas_select(
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     modelo: $modelo,
     *     tablas_select: $tablas_select
     * );
     *
     * if (is_string($resultado)) {
     *     echo "Columnas SQL generadas: " . $resultado;
     * } else {
     *     print_r($resultado); // Detalle del error
     * }
     * ```
     *
     * ### Ejemplo de uso con columnas en bruto:
     *
     * ```php
     * $columnas_en_bruto = true;
     * $columnas_sql = [];
     * $modelo = new modelo_base($link); // Instancia del modelo base
     * $tablas_select = []; // No es necesario especificar tablas en este caso.
     *
     * $resultado = $miClase->columnas_tablas_select(
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     modelo: $modelo,
     *     tablas_select: $tablas_select
     * );
     *
     * if (is_string($resultado)) {
     *     echo "Columnas SQL generadas: " . $resultado;
     * } else {
     *     print_r($resultado); // Detalle del error
     * }
     * ```
     *
     * ### Ejemplo con datos inválidos:
     *
     * ```php
     * $columnas_en_bruto = false;
     * $columnas_sql = ['id', 'nombre'];
     * $modelo = new modelo_base($link);
     * $tablas_select = [
     *     123 => 'usuarios' // Error: La clave del array no puede ser un número.
     * ];
     *
     * $resultado = $miClase->columnas_tablas_select(
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     modelo: $modelo,
     *     tablas_select: $tablas_select
     * );
     *
     * if (is_array($resultado)) {
     *     print_r($resultado); // Muestra el detalle del error
     * }
     * ```
     *
     * ### Detalle de parámetros:
     *
     * - **`$columnas_en_bruto`** (bool):
     *   Indica si las columnas deben generarse en su formato original o incluir alias.
     *   - `true`: Genera las columnas sin alias, tomando como referencia solo el nombre bruto.
     *   - `false`: Genera las columnas con alias formateados.
     *
     * - **`$columnas_sql`** (array):
     *   Lista de nombres de columnas a incluir en la consulta SQL.
     *
     *   **Ejemplo**: `['id', 'nombre', 'estatus']`.
     *
     * - **`$modelo`** (modelo_base):
     *   Instancia del modelo base que interactúa con la base de datos.
     *
     *   **Ejemplo**: `$modelo = new modelo_base($link);`.
     *
     * - **`$tablas_select`** (array):
     *   Lista de tablas de las cuales se generarán las columnas.
     *   Las claves y los valores del array deben ser nombres de tablas.
     *
     *   **Ejemplo**:
     *   ```php
     *   [
     *       'usuarios' => 'usuarios',
     *       'perfiles' => 'perfiles'
     *   ]
     *   ```
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Devuelve una cadena SQL como:
     *   ```sql
     *   usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre, perfiles.id AS perfiles_id
     *   ```
     *
     * - **Error**:
     *   Devuelve un array detallando el problema, como:
     *   ```php
     *   [
     *       'error' => 1,
     *       'mensaje' => 'Error $key no puede ser un numero',
     *       'data' => 123
     *   ]
     *   ```
     */
    private function columnas_tablas_select(
        bool $columnas_en_bruto, array $columnas_sql, modelo_base $modelo, array $tablas_select): array|string
    {
        if ($columnas_en_bruto) {
            $tablas_select = [];
            $tablas_select[$modelo->tabla] = $modelo->tabla;
        }

        $columnas = '';

        foreach ($tablas_select as $key => $tabla_select) {
            if (is_numeric($key)) {
                return $this->error->error(
                    mensaje: 'Error $key no puede ser un numero',
                    data: $key,
                    es_final: true
                );
            }

            $result = $this->genera_columna_tabla(
                columnas: $columnas,
                columnas_en_bruto: $columnas_en_bruto,
                columnas_sql: $columnas_sql,
                key: $key,
                modelo: $modelo
            );
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas', data: $result);
            }
            $columnas = (string)$result;
        }

        return $columnas;
    }


    /**
     * REG
     * Determina si se debe agregar una coma al construir una lista de columnas SQL.
     *
     * Esta función verifica si la cadena proporcionada contiene columnas SQL. Si la cadena no está vacía,
     * devuelve una coma seguida de un espacio (`, `). Si está vacía, devuelve una cadena vacía.
     *
     * @param string $columnas_sql Cadena que representa las columnas SQL procesadas.
     *
     * @return string Devuelve `", "` si `$columnas_sql` no está vacío, de lo contrario devuelve una cadena vacía.
     *
     * @example
     * // Caso 1: La cadena contiene columnas
     * $resultado = $this->coma('id, nombre, correo');
     * // Resultado: ', '
     *
     * @example
     * // Caso 2: La cadena está vacía
     * $resultado = $this->coma('');
     * // Resultado: ''
     *
     * @example
     * // Caso 3: La cadena tiene espacios en blanco al inicio y final
     * $resultado = $this->coma('   id, nombre   ');
     * // Resultado: ', '
     */
    private function coma(string $columnas_sql): string
    {
        $columnas_sql = trim($columnas_sql);
        $coma = '';
        if ($columnas_sql !== '') {
            $coma = ', ';
        }
        return $coma;
    }


    /**
     * REG
     * Genera datos necesarios para construir columnas SQL en una consulta.
     *
     * Esta función se encarga de generar las columnas SQL a partir de los parámetros proporcionados, validando los datos
     * de entrada y retornando una estructura que incluye las columnas principales y columnas adicionales si aplica.
     *
     * @param array $columnas Lista de columnas específicas a incluir en la consulta. Si está vacío, se incluirán todas.
     * @param bool $columnas_en_bruto Indica si las columnas deben generarse en formato sin alias.
     * @param modelo_base $modelo Instancia del modelo base que interactúa con la tabla.
     * @param string $tabla_original Nombre original de la tabla en la base de datos.
     * @param string $tabla_renombrada Alias o nombre renombrado de la tabla para uso en SQL.
     *
     * @return array|stdClass Devuelve un objeto con las columnas generadas y cualquier columna adicional, o un array de error.
     *
     * @example
     * // Caso 1: Generar columnas SQL con alias personalizados
     * $columnas = ['id', 'nombre'];
     * $modelo = new modelo_base($link);
     * $tabla_original = 'usuarios';
     * $tabla_renombrada = '';
     * $resultado = $this->data_for_columnas_envio(
     *     columnas: $columnas,
     *     columnas_en_bruto: false,
     *     modelo: $modelo,
     *     tabla_original: $tabla_original,
     *     tabla_renombrada: $tabla_renombrada
     * );
     * // Resultado esperado:
     * // $resultado->columnas_sql = 'usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre';
     * // $resultado->columnas_extra_sql = '';
     *
     * @example
     * // Caso 2: Generar columnas SQL en bruto sin alias
     * $columnas = [];
     * $modelo = new modelo_base($link);
     * $tabla_original = 'productos';
     * $tabla_renombrada = '';
     * $resultado = $this->data_for_columnas_envio(
     *     columnas: $columnas,
     *     columnas_en_bruto: true,
     *     modelo: $modelo,
     *     tabla_original: $tabla_original,
     *     tabla_renombrada: $tabla_renombrada
     * );
     * // Resultado esperado:
     * // $resultado->columnas_sql = 'productos.id AS id, productos.nombre AS nombre';
     * // $resultado->columnas_extra_sql = '';
     *
     * @example
     * // Caso 3: Generar columnas SQL con alias de tabla renombrada
     * $columnas = ['id', 'nombre'];
     * $modelo = new modelo_base($link);
     * $tabla_original = 'clientes';
     * $tabla_renombrada = 'cli';
     * $resultado = $this->data_for_columnas_envio(
     *     columnas: $columnas,
     *     columnas_en_bruto: false,
     *     modelo: $modelo,
     *     tabla_original: $tabla_original,
     *     tabla_renombrada: $tabla_renombrada
     * );
     * // Resultado esperado:
     * // $resultado->columnas_sql = 'cli.id AS clientes_id, cli.nombre AS clientes_nombre';
     * // $resultado->columnas_extra_sql = '';
     *
     * @throws array Devuelve un array con los detalles del error si:
     *  - `$tabla_original` está vacío o es numérico.
     *  - Ocurre un error al generar las columnas SQL.
     */
    private function data_for_columnas_envio(
        array $columnas,
        bool $columnas_en_bruto,
        modelo_base $modelo,
        string $tabla_original,
        string $tabla_renombrada
    ): array|stdClass {
        $tabla_original = str_replace('models\\', '', $tabla_original);

        if ($tabla_original === '') {
            return $this->error->error(mensaje: 'Error tabla original no puede venir vacia', data: $tabla_original,
                es_final: true);
        }
        if (is_numeric($tabla_original)) {
            return $this->error->error(mensaje: 'Error $tabla_original no puede ser un numero', data: $tabla_original,
                es_final: true);
        }

        $columnas_sql = $this->genera_columnas_tabla(
            columnas_en_bruto: $columnas_en_bruto,
            modelo: $modelo,
            tabla_original: $tabla_original,
            tabla_renombrada: $tabla_renombrada,
            columnas: $columnas
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar columnas', data: $columnas_sql);
        }

        $columnas_extra_sql = '';

        $data = new stdClass();
        $data->columnas_sql = $columnas_sql;
        $data->columnas_extra_sql = $columnas_extra_sql;
        return $data;
    }


    /**
     * REG
     * Genera una cadena SQL con las columnas de una tabla específica.
     *
     * Este método ajusta y compone una cadena SQL que incluye las columnas de una tabla específica.
     * Se asegura de que los datos de entrada sean válidos y procesa las columnas según las configuraciones indicadas.
     *
     * @param string $columnas Columnas iniciales en formato SQL. Puede ser una cadena vacía si no hay columnas previas.
     * @param bool $columnas_en_bruto Determina si las columnas deben incluirse en su formato bruto o con alias.
     *                                - `true`: Incluye las columnas sin alias.
     *                                - `false`: Incluye las columnas con alias.
     * @param array $columnas_sql Lista de nombres de columnas que se deben procesar.
     *                            Cada elemento representa una columna a incluir.
     * @param string $key Nombre de la tabla a procesar. Se asegura de limpiar prefijos no necesarios como `models\`.
     * @param modelo_base $modelo Instancia del modelo base que se utiliza para interactuar con la base de datos.
     *
     * @return array|string Una cadena con las columnas SQL ajustadas si el proceso es exitoso.
     *                      En caso de error, devuelve un array con los detalles del error.
     *
     * @throws errores Si alguno de los parámetros es inválido o se produce un error durante la generación.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas = '';
     * $columnas_en_bruto = false;
     * $columnas_sql = ['id', 'nombre', 'estatus'];
     * $key = 'usuarios';
     * $modelo = new modelo_base($link); // Instancia del modelo base
     *
     * $resultado = $miClase->genera_columna_tabla(
     *     columnas: $columnas,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     key: $key,
     *     modelo: $modelo
     * );
     *
     * if (is_string($resultado)) {
     *     echo "Columnas SQL generadas: " . $resultado;
     * } else {
     *     print_r($resultado); // Detalle del error
     * }
     * ```
     *
     * ### Ejemplo de uso con datos inválidos:
     *
     * ```php
     * $columnas = '';
     * $columnas_en_bruto = false;
     * $columnas_sql = ['id', 'nombre', 'estatus'];
     * $key = '123'; // Error: el key no puede ser un número.
     * $modelo = new modelo_base($link);
     *
     * $resultado = $miClase->genera_columna_tabla(
     *     columnas: $columnas,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     columnas_sql: $columnas_sql,
     *     key: $key,
     *     modelo: $modelo
     * );
     *
     * if (is_array($resultado)) {
     *     print_r($resultado); // Muestra el detalle del error
     * }
     * ```
     *
     * ### Detalle de parámetros:
     *
     * - **`$columnas`** (string):
     *   Contiene las columnas iniciales en formato SQL. Puede ser una cadena vacía.
     *
     *   **Ejemplo**: `'id, nombre'`.
     *
     * - **`$columnas_en_bruto`** (bool):
     *   Indica si las columnas deben generarse en su formato original o incluir alias.
     *
     *   **Ejemplo**:
     *   - `true`: `'usuarios.id, usuarios.nombre'`.
     *   - `false`: `'usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre'`.
     *
     * - **`$columnas_sql`** (array):
     *   Lista de nombres de columnas a incluir en la consulta SQL.
     *
     *   **Ejemplo**: `['id', 'nombre', 'estatus']`.
     *
     * - **`$key`** (string):
     *   Nombre de la tabla que se procesará. Elimina prefijos como `models\`.
     *
     *   **Ejemplo**: `'usuarios'`.
     *
     * - **`$modelo`** (modelo_base):
     *   Instancia del modelo base para gestionar las operaciones en la base de datos.
     *
     *   **Ejemplo**: `$modelo = new modelo_base($link);`.
     *
     * ### Resultado esperado:
     *
     * - **Éxito**: Devuelve una cadena SQL como:
     *   ```sql
     *   usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre, usuarios.estatus AS usuarios_estatus
     *   ```
     *
     * - **Error**: Devuelve un array detallando el problema, como:
     *   ```php
     *   [
     *       'error' => 1,
     *       'mensaje' => 'Error $key no puede ser un numero',
     *       'data' => '123'
     *   ]
     *   ```
     */
    private function genera_columna_tabla(
        string $columnas, bool $columnas_en_bruto, array $columnas_sql, string $key, modelo_base $modelo): array|string
    {
        $key = str_replace('models\\', '', $key);
        if (is_numeric($key)) {
            return $this->error->error(
                mensaje: 'Error $key no puede ser un numero',
                data: $key,
                es_final: true
            );
        }

        $result = $this->ajusta_columnas_completas(
            columnas: $columnas,
            columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $columnas_sql,
            modelo: $modelo,
            tabla: $key,
            tabla_renombrada: ''
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $result);
        }

        return (string)$result;
    }


    /**
     * REG
     * Genera una cadena SQL con las columnas necesarias para una consulta.
     *
     * Esta función combina las columnas principales y adicionales de una tabla, ya sea en su formato bruto
     * o con nombres renombrados, para formar una cadena SQL que puede ser usada en consultas.
     *
     * @param bool $columnas_en_bruto Indica si las columnas deben mantenerse en su formato original
     *                                (sin alias) o deben ser renombradas.
     * @param modelo_base $modelo Instancia del modelo base que contiene la lógica para manejar la base de datos.
     * @param string $tabla_original Nombre original de la tabla de la que se obtendrán las columnas.
     * @param string $tabla_renombrada Nombre renombrado de la tabla para las consultas, si aplica.
     * @param array $columnas Lista de columnas específicas que se desean incluir en la consulta. Si está vacío,
     *                        se incluyen todas las columnas parseadas.
     *
     * @return array|string Devuelve una cadena con las columnas formateadas para una consulta SQL.
     *                      En caso de error, devuelve un array con los detalles del mismo.
     *
     * @example
     * // Caso 1: Generar columnas con formato bruto para la tabla "usuarios".
     * $columnas = array();
     * $tabla_original = "usuarios";
     * $tabla_renombrada = "";
     * $resultado = $this->genera_columnas_consulta(
     *     columnas_en_bruto: true,
     *     modelo: $modelo,
     *     tabla_original: $tabla_original,
     *     tabla_renombrada: $tabla_renombrada,
     *     columnas: $columnas
     * );
     * // Resultado esperado: "usuarios.id, usuarios.nombre, usuarios.email"
     *
     * @example
     * // Caso 2: Generar columnas con alias personalizados para la tabla "productos".
     * $columnas = array('productos_id', 'productos_nombre');
     * $tabla_original = "productos";
     * $tabla_renombrada = "prod";
     * $resultado = $this->genera_columnas_consulta(
     *     columnas_en_bruto: false,
     *     modelo: $modelo,
     *     tabla_original: $tabla_original,
     *     tabla_renombrada: $tabla_renombrada,
     *     columnas: $columnas
     * );
     * // Resultado esperado: "prod.id AS productos_id, prod.nombre AS productos_nombre"
     *
     * @example
     * // Caso 3: Error por tabla original vacía.
     * $tabla_original = "";
     * $resultado = $this->genera_columnas_consulta(
     *     columnas_en_bruto: false,
     *     modelo: $modelo,
     *     tabla_original: $tabla_original,
     *     tabla_renombrada: $tabla_renombrada,
     *     columnas: $columnas
     * );
     * // Resultado esperado: Array con error: "Error $tabla_original no puede venir vacía".
     *
     * @example
     * // Caso 4: Combinación de columnas principales y adicionales.
     * $columnas = array('usuarios_id', 'usuarios_nombre');
     * $columnas_extra_sql = 'SUM(usuarios.saldo) AS saldo_total';
     * $tabla_original = "usuarios";
     * $tabla_renombrada = "";
     * $resultado = $this->genera_columnas_consulta(
     *     columnas_en_bruto: false,
     *     modelo: $modelo,
     *     tabla_original: $tabla_original,
     *     tabla_renombrada: $tabla_renombrada,
     *     columnas: $columnas
     * );
     * // Resultado esperado: "usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre, SUM(usuarios.saldo) AS saldo_total"
     */
    private function genera_columnas_consulta(
        bool $columnas_en_bruto,
        modelo_base $modelo,
        string $tabla_original,
        string $tabla_renombrada,
        array $columnas = array()
    ): array|string {
        $tabla_original = str_replace('models\\', '', $tabla_original);

        if (is_numeric($tabla_original)) {
            return $this->error->error(mensaje: 'Error $tabla_original no puede ser un número', data: $tabla_original, es_final: true);
        }

        $data = $this->data_for_columnas_envio(
            columnas: $columnas,
            columnas_en_bruto: $columnas_en_bruto,
            modelo: $modelo,
            tabla_original: $tabla_original,
            tabla_renombrada: $tabla_renombrada
        );

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener datos para columnas', data: $data);
        }

        $columnas_envio = $this->columnas_envio(
            columnas_extra_sql: $data->columnas_extra_sql,
            columnas_sql: $data->columnas_sql
        );

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar columnas', data: $columnas_envio);
        }

        return $columnas_envio;
    }


    /**
     * REG
     * Genera una cadena SQL que incluye columnas adicionales basadas en subconsultas definidas en el modelo.
     * Cada subconsulta se asocia con un alias y se agrega a la cadena resultante.
     *
     * @param array $columnas Lista de columnas específicas que se desean incluir en la consulta.
     *                        Si está vacía, se incluirán todas las columnas definidas en el modelo.
     * @param modelo_base $modelo El modelo que contiene la propiedad `columnas_extra`,
     *                            la cual define subconsultas con sus respectivos alias.
     *
     * @return array|string Una cadena SQL con las columnas adicionales generadas.
     *                      En caso de error, devuelve un array detallando el problema encontrado.
     *
     * @throws errores Si las claves o valores en `columnas_extra` no cumplen con las validaciones requeridas.
     *
     * @example Uso exitoso:
     * ```php
     * $columnas = ['total_ventas', 'productos_disponibles'];
     * $modelo = new modelo_base();
     * $modelo->columnas_extra = [
     *     'total_ventas' => '(SELECT SUM(precio) FROM ventas WHERE estado = "aprobado")',
     *     'productos_disponibles' => '(SELECT COUNT(*) FROM productos WHERE stock > 0)'
     * ];
     *
     * $resultado = $this->genera_columnas_extra(columnas: $columnas, modelo: $modelo);
     * // Resultado esperado:
     * // "(SELECT SUM(precio) FROM ventas WHERE estado = "aprobado") AS total_ventas,
     * // (SELECT COUNT(*) FROM productos WHERE stock > 0) AS productos_disponibles"
     * ```
     *
     * @example Sin columnas específicas (incluir todas):
     * ```php
     * $columnas = [];
     * $modelo->columnas_extra = [
     *     'ventas_aprobadas' => '(SELECT SUM(precio) FROM ventas WHERE estado = "aprobado")',
     *     'productos_en_stock' => '(SELECT COUNT(*) FROM productos WHERE stock > 0)'
     * ];
     *
     * $resultado = $this->genera_columnas_extra(columnas: $columnas, modelo: $modelo);
     * // Resultado esperado:
     * // "(SELECT SUM(precio) FROM ventas WHERE estado = "aprobado") AS ventas_aprobadas,
     * // (SELECT COUNT(*) FROM productos WHERE stock > 0) AS productos_en_stock"
     * ```
     *
     * @example Error por clave vacía:
     * ```php
     * $modelo->columnas_extra = [
     *     '' => '(SELECT SUM(precio) FROM ventas WHERE estado = "aprobado")'
     * ];
     *
     * $resultado = $this->genera_columnas_extra(columnas: [], modelo: $modelo);
     * // Resultado esperado: Array indicando que la clave no puede estar vacía.
     * ```
     *
     * @example Error por valor SQL vacío:
     * ```php
     * $modelo->columnas_extra = [
     *     'ventas_totales' => ''
     * ];
     *
     * $resultado = $this->genera_columnas_extra(columnas: [], modelo: $modelo);
     * // Resultado esperado: Array indicando que el SQL no puede estar vacío.
     * ```
     */
    final public function genera_columnas_extra(array $columnas, modelo_base $modelo): array|string
    {
        $columnas_sql = '';
        $columnas_extra = $modelo->columnas_extra;
        foreach ($columnas_extra as $sub_query => $sql) {
            if ((count($columnas) > 0) && !in_array($sub_query, $columnas, true)) {
                continue;
            }
            if (is_numeric($sub_query)) {
                return $this->error->error(mensaje: 'Error el key debe ser el nombre de la subquery',
                    data: $columnas_extra, es_final: true);
            }
            if ((string)$sub_query === '') {
                return $this->error->error(mensaje: 'Error el key no puede venir vacio', data: $columnas_extra,
                    es_final: true);
            }
            if ((string)$sql === '') {
                return $this->error->error(mensaje: 'Error el sql no puede venir vacio', data: $columnas_extra,
                    es_final: true);
            }
            $columnas_sql .= $columnas_sql === '' ? "$sql AS $sub_query" : ",$sql AS $sub_query";
        }
        return $columnas_sql;
    }


    /**
     * REG
     * Genera las columnas de una tabla de base de datos en un formato adecuado para su uso en un modelo.
     *
     * Este método:
     * 1. Verifica que el nombre de la tabla no esté vacío ni sea un número.
     * 2. Obtiene las columnas de la tabla mediante una consulta a la base de datos utilizando la función `columnas_bd_native`.
     * 3. Procesa las columnas obtenidas, convirtiéndolas en un formato adecuado mediante la función `columnas_sql_array`.
     * 4. Retorna un objeto con las columnas parseadas y completas.
     *
     * @param modelo_base $modelo El modelo que contiene la lógica para ejecutar consultas en la base de datos.
     * @param string $tabla_bd El nombre de la tabla en la base de datos.
     *
     * @return array|stdClass
     *   - Retorna un objeto `stdClass` con las propiedades `columnas_parseadas` (nombres de las columnas) y `columnas_completas` (información detallada de las columnas).
     *   - En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Generando columnas para una tabla
     *  ---------------------------------------------
     *  $tabla_bd = 'usuarios';
     *  $modelo = new modelo_base();
     *
     *  $resultado = $this->genera_columnas_field($modelo, $tabla_bd);
     *  // $resultado->columnas_parseadas será el array con los nombres de las columnas
     *  // $resultado->columnas_completas tendrá los detalles de cada columna (tipo, nulidad, etc.)
     *
     * @example
     *  Ejemplo 2: Error debido a una tabla vacía
     *  -----------------------------------------
     *  $tabla_bd = '';
     *  $modelo = new modelo_base();
     *
     *  $resultado = $this->genera_columnas_field($modelo, $tabla_bd);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error $tabla_bd esta vacia',
     *  //   'data' => ''
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error debido a que el nombre de la tabla es numérico
     *  --------------------------------------------------------------
     *  $tabla_bd = '123';
     *  $modelo = new modelo_base();
     *
     *  $resultado = $this->genera_columnas_field($modelo, $tabla_bd);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error $tabla_bd no puede ser un numero',
     *  //   'data' => '123'
     *  // ]
     */
    private function genera_columnas_field(modelo_base $modelo, string $tabla_bd): array|stdClass
    {
        $tabla_bd = trim($tabla_bd);

        // Verifica que el nombre de la tabla no esté vacío ni sea numérico
        if ($tabla_bd === '') {
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia', data: $tabla_bd, es_final: true);
        }

        if (is_numeric($tabla_bd)) {
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero', data: $tabla_bd,
                es_final: true);
        }

        // Obtiene las columnas de la tabla de base de datos
        $columnas = $this->columnas_bd_native(modelo: $modelo, tabla_bd: $tabla_bd);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas);
        }

        // Procesa las columnas obtenidas en el formato adecuado
        $columnas_field = $this->columnas_sql_array(columnas: $columnas);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas_field);
        }

        return $columnas_field;
    }


    /**
     * REG
     * Genera una cadena SQL con columnas de una tabla, aplicando alias y validaciones según los parámetros.
     *
     * Esta función permite generar una cadena SQL con las columnas de una tabla específica, utilizando alias para
     * renombrar columnas si es necesario. También valida los datos de entrada y maneja casos como tablas renombradas.
     *
     * @param bool $columnas_en_bruto Indica si se deben usar las columnas en formato en bruto, sin alias personalizados.
     * @param modelo_base $modelo Instancia del modelo base para interactuar con la tabla.
     * @param string $tabla_original Nombre original de la tabla en la base de datos.
     * @param string $tabla_renombrada Nombre renombrado de la tabla (puede ser un alias SQL).
     * @param array $columnas Lista de columnas permitidas para incluir en la consulta SQL. Si está vacío, se incluirán todas.
     *
     * @return array|string Devuelve una cadena SQL con las columnas generadas o un array con detalles de error en caso de fallo.
     *
     * @example
     * // Caso 1: Generar columnas SQL con alias personalizados
     * $columnas = ['usuarios_id', 'usuarios_nombre'];
     * $modelo = new modelo_base($link);
     * $resultado = $this->genera_columnas_tabla(
     *     columnas_en_bruto: false,
     *     modelo: $modelo,
     *     tabla_original: 'usuarios',
     *     tabla_renombrada: '',
     *     columnas: $columnas
     * );
     * // Resultado: 'usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre'
     *
     * @example
     * // Caso 2: Generar columnas SQL sin alias (en bruto)
     * $columnas = [];
     * $modelo = new modelo_base($link);
     * $resultado = $this->genera_columnas_tabla(
     *     columnas_en_bruto: true,
     *     modelo: $modelo,
     *     tabla_original: 'usuarios',
     *     tabla_renombrada: '',
     *     columnas: $columnas
     * );
     * // Resultado: 'usuarios.id AS id, usuarios.nombre AS nombre'
     *
     * @example
     * // Caso 3: Usar una tabla renombrada con columnas específicas
     * $columnas = ['clientes_id', 'clientes_nombre'];
     * $modelo = new modelo_base($link);
     * $resultado = $this->genera_columnas_tabla(
     *     columnas_en_bruto: false,
     *     modelo: $modelo,
     *     tabla_original: 'clientes',
     *     tabla_renombrada: 'cli',
     *     columnas: $columnas
     * );
     * // Resultado: 'cli.id AS clientes_id, cli.nombre AS clientes_nombre'
     *
     * @throws array Devuelve un array con los detalles del error si alguno de los parámetros es inválido
     *               o si ocurre un error al obtener las columnas SQL.
     */
    private function genera_columnas_tabla(
        bool $columnas_en_bruto,
        modelo_base $modelo,
        string $tabla_original,
        string $tabla_renombrada,
        array $columnas = array()
    ): array|string {
        $tabla_original = str_replace('models\\', '', $tabla_original);

        if ($tabla_original === '') {
            return $this->error->error(mensaje: 'Error tabla original no puede venir vacia', data: $tabla_original,
                es_final: true);
        }

        if (is_numeric($tabla_original)) {
            return $this->error->error(mensaje: 'Error $tabla_original no puede ser un numero', data: $tabla_original,
                es_final: true);
        }

        $data = $this->obten_columnas(modelo: $modelo, tabla_original: $tabla_original);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener columnas', data: $data);
        }
        $columnas_parseadas = $data->columnas_parseadas;

        $tabla_nombre = $modelo->obten_nombre_tabla(
            tabla_original: $tabla_original, tabla_renombrada: $tabla_renombrada);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nombre de tabla', data: $tabla_nombre);
        }

        $columnas_sql = $this->columnas_sql_init(
            columnas: $columnas,
            columnas_en_bruto: $columnas_en_bruto,
            columnas_parseadas: $columnas_parseadas,
            tabla_nombre: $tabla_nombre
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener columnas sql', data: $columnas_sql);
        }

        return $columnas_sql;
    }


    /**
     * REG
     * Inicializa la estructura de columnas y tablas seleccionadas para su uso en consultas SQL.
     *
     * Esta función procesa una lista de nombres de tablas, asignando una configuración inicial para las columnas
     * y las tablas seleccionadas. Si no se proporciona ninguna tabla, genera un error indicando la estructura esperada.
     *
     * @param array $columnas_by_table Lista de nombres de tablas que se utilizarán en la consulta SQL.
     *                                 Ejemplo: `['usuarios', 'ordenes']`.
     *
     * @return stdClass|array Retorna un objeto `stdClass` con las propiedades:
     *                        - `columnas_sql`: Un array inicializado vacío para las columnas SQL.
     *                        - `tablas_select`: Un array asociativo donde las claves son los nombres de las tablas
     *                          y los valores están inicializados en `false`.
     *                        En caso de error, retorna un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $columnas_by_table = ['usuarios', 'ordenes'];
     *
     * $resultado = $miClase->init_columnas_by_table(columnas_by_table: $columnas_by_table);
     *
     * echo '<pre>';
     * print_r($resultado);
     * echo '</pre>';
     *
     * // Salida esperada:
     * // stdClass Object
     * // (
     * //     [columnas_sql] => Array
     * //         (
     * //         )
     * //
     * //     [tablas_select] => Array
     * //         (
     * //             [usuarios] => false
     * //             [ordenes] => false
     * //         )
     * // )
     * ```
     *
     * ### Ejemplo de error:
     *
     * - Caso: `$columnas_by_table` está vacío.
     * ```php
     * $columnas_by_table = [];
     *
     * $resultado = $miClase->init_columnas_by_table(columnas_by_table: $columnas_by_table);
     *
     * print_r($resultado);
     *
     * // Salida esperada:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error debe columnas_by_table esta vacia',
     * //     'data' => [],
     * //     'es_final' => true,
     * //     'fix' => 'columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = "nombre_tabla"'
     * // ]
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$columnas_by_table`** (array):
     *   Lista de tablas que serán utilizadas en la configuración inicial.
     *   **Ejemplo válido**: `['usuarios', 'ordenes']`.
     *   **Ejemplo inválido**: `[]` (genera un error).
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Un objeto `stdClass` con las propiedades `columnas_sql` y `tablas_select`.
     *   `columnas_sql` es un array vacío listo para llenarse con columnas.
     *   `tablas_select` contiene cada tabla con un valor inicial de `false`.
     *
     * - **Error**:
     *   Un array detallando el error si la entrada no es válida.
     */
    private function init_columnas_by_table(array $columnas_by_table): stdClass|array
    {
        if (count($columnas_by_table) === 0) {
            $fix = 'columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = "nombre_tabla"';
            return $this->error->error(
                mensaje: 'Error debe columnas_by_table esta vacia',
                data: $columnas_by_table,
                es_final: true,
                fix: $fix
            );
        }
        $columnas_sql = array();
        $tablas_select = array();
        foreach ($columnas_by_table as $tabla) {
            $tablas_select[$tabla] = false;
        }

        $data = new stdClass();
        $data->columnas_sql = $columnas_sql;
        $data->tablas_select = $tablas_select;
        return $data;
    }


    /**
     * Integra un campo obligatorio para validacion
     * @param string $campo Campo a integrar
     * @param array $campos_obligatorios Campos obligatorios precargados
     * @return array
     * @version 2.114.12
     */
    private function integra_campo_obligatorio(string $campo, array $campos_obligatorios): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo no puede ser vacio', data: $campo);
        }
        $campos_obligatorios[]=$campo;
        return $campos_obligatorios;
    }

    private function integra_campo_obligatorio_existente(string $campo, array $campos_obligatorios, array $campos_tabla): array
    {
        if(in_array($campo, $campos_tabla, true)){

            $campos_obligatorios = $this->integra_campo_obligatorio(campo: $campo,campos_obligatorios:  $campos_obligatorios);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar campos obligatorios ', data: $campos_obligatorios);
            }
        }
        return $campos_obligatorios;
    }

    final public function integra_campos_obligatorios(array $campos_obligatorios, array $campos_tabla): array
    {
        $campos_obligatorios_parciales = array('accion_id','codigo','descripcion','grupo_id','seccion_id');


        foreach($campos_obligatorios_parciales as $campo){

            $campos_obligatorios = $this->integra_campo_obligatorio_existente(
                campo: $campo,campos_obligatorios:  $campos_obligatorios,campos_tabla:  $campos_tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar campos obligatorios ', data: $campos_obligatorios);

            }

        }
        return $campos_obligatorios;
    }

    /**
     * REG
     * Integra columnas SQL en una cadena de columnas para una consulta.
     *
     * Esta función combina las columnas existentes con un nuevo conjunto de columnas resultado,
     * verificando si están vacías para determinar cómo proceder. Devuelve un objeto con las
     * columnas resultantes y un indicador de si se debe continuar procesando.
     *
     * @param string $columnas Cadena que representa las columnas actuales en la consulta SQL.
     * @param string $resultado_columnas Cadena que contiene nuevas columnas a integrar.
     *
     * @return stdClass Objeto que contiene:
     *                  - `columnas` (string): La cadena de columnas integrada.
     *                  - `continue` (bool): Indicador de si se debe continuar procesando.
     *
     * @example
     * // Caso 1: Integra columnas en una cadena vacía.
     * $columnas = '';
     * $resultado_columnas = 'id, nombre';
     * $resultado = $this->integra_columnas(columnas: $columnas, resultado_columnas: $resultado_columnas);
     * // Resultado esperado:
     * // $resultado->columnas = 'id, nombre';
     * // $resultado->continue = false;
     *
     * @example
     * // Caso 2: Integra columnas adicionales a una cadena existente.
     * $columnas = 'id';
     * $resultado_columnas = 'nombre';
     * $resultado = $this->integra_columnas(columnas: $columnas, resultado_columnas: $resultado_columnas);
     * // Resultado esperado:
     * // $resultado->columnas = 'id, nombre';
     * // $resultado->continue = false;
     *
     * @example
     * // Caso 3: No integra columnas si las columnas resultado están vacías.
     * $columnas = 'id, nombre';
     * $resultado_columnas = '';
     * $resultado = $this->integra_columnas(columnas: $columnas, resultado_columnas: $resultado_columnas);
     * // Resultado esperado:
     * // $resultado->columnas = 'id, nombre';
     * // $resultado->continue = true;
     *
     * @example
     * // Caso 4: Agrega columnas cuando la cadena inicial está vacía.
     * $columnas = '';
     * $resultado_columnas = 'nombre, edad';
     * $resultado = $this->integra_columnas(columnas: $columnas, resultado_columnas: $resultado_columnas);
     * // Resultado esperado:
     * // $resultado->columnas = 'nombre, edad';
     * // $resultado->continue = false;
     */
    private function integra_columnas(string $columnas, string $resultado_columnas): stdClass
    {
        $data = new stdClass();
        $continue = false;

        if ($columnas === '') {
            $columnas .= $resultado_columnas;
        } else {
            if ($resultado_columnas === '') {
                $continue = true;
            }
            if (!$continue) {
                $columnas .= ', ' . $resultado_columnas;
            }
        }

        $data->columnas = $columnas;
        $data->continue = $continue;

        return $data;
    }


    /**
     * REG
     * Integra columnas de consulta SQL a partir de dos cadenas de columnas.
     *
     * Esta función combina columnas existentes con nuevas columnas resultado, asegurándose de
     * manejar errores durante la integración y devolviendo la cadena de columnas resultante.
     *
     * @param string $columnas Cadena que representa las columnas actuales en una consulta SQL.
     * @param string $resultado_columnas Cadena que contiene las nuevas columnas a integrar.
     *
     * @return array|string Devuelve una cadena con las columnas integradas si el proceso es exitoso.
     *                      En caso de error, retorna un array con los detalles del error.
     *
     * @example
     * // Caso 1: Integra columnas en una cadena vacía.
     * $columnas = '';
     * $resultado_columnas = 'id, nombre';
     * $resultado = $this->integra_columnas_por_data(columnas: $columnas, resultado_columnas: $resultado_columnas);
     * // Resultado esperado:
     * // $resultado = 'id, nombre';
     *
     * @example
     * // Caso 2: Integra columnas adicionales a una cadena existente.
     * $columnas = 'id';
     * $resultado_columnas = 'nombre';
     * $resultado = $this->integra_columnas_por_data(columnas: $columnas, resultado_columnas: $resultado_columnas);
     * // Resultado esperado:
     * // $resultado = 'id, nombre';
     *
     * @example
     * // Caso 3: No agrega nada si las columnas resultado están vacías.
     * $columnas = 'id, nombre';
     * $resultado_columnas = '';
     * $resultado = $this->integra_columnas_por_data(columnas: $columnas, resultado_columnas: $resultado_columnas);
     * // Resultado esperado:
     * // $resultado = 'id, nombre';
     *
     * @example
     * // Caso 4: Manejo de errores durante la integración.
     * $columnas = '';
     * $resultado_columnas = null; // Valor inválido
     * $resultado = $this->integra_columnas_por_data(columnas: $columnas, resultado_columnas: $resultado_columnas);
     * // Resultado esperado:
     * // $resultado = ['error' => true, 'mensaje' => 'Error al integrar columnas', 'data' => ...];
     */
    private function integra_columnas_por_data(string $columnas, string $resultado_columnas): array|string
    {
        $data = $this->integra_columnas(columnas: $columnas, resultado_columnas: $resultado_columnas);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $data);
        }
        return $data->columnas;
    }


    /**
     * REG
     * Obtiene las columnas de una tabla y las asigna al modelo, verificando si ya están almacenadas en la sesión.
     *
     * Este método:
     * 1. Normaliza el nombre de la tabla, eliminando prefijos como `models\`.
     * 2. Valida que el nombre de la tabla no esté vacío ni sea numérico.
     * 3. Comprueba si las columnas de la tabla ya están almacenadas en la sesión mediante `asigna_columnas_en_session`.
     * 4. Si no están en la sesión, las asigna utilizando `asigna_columnas_session_new`.
     * 5. Retorna las columnas de la tabla en el modelo.
     *
     * @param modelo_base $modelo El modelo que contiene la lógica para interactuar con la base de datos.
     * @param string $tabla_original El nombre original de la tabla en la base de datos (puede incluir prefijos).
     *
     * @return array|stdClass
     *   - Retorna un objeto `stdClass` con las propiedades `columnas_parseadas` y `columnas_completas` que contienen las columnas procesadas.
     *   - En caso de error, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Obtener columnas de una tabla
     *  ----------------------------------------
     *  $tabla_original = 'models\usuarios';
     *  $modelo = new modelo_base();
     *
     *  $resultado = $this->obten_columnas($modelo, $tabla_original);
     *  // $resultado->columnas_parseadas contendrá los nombres de las columnas.
     *  // $resultado->columnas_completas tendrá los detalles de cada columna (tipo, nulidad, etc.).
     *
     * @example
     *  Ejemplo 2: Error debido a tabla vacía
     *  -------------------------------------
     *  $tabla_original = '';
     *  $modelo = new modelo_base();
     *
     *  $resultado = $this->obten_columnas($modelo, $tabla_original);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error tabla original no puede venir vacia',
     *  //   'data' => ''
     *  // ]
     *
     * @example
     *  Ejemplo 3: Error debido a nombre de tabla numérico
     *  --------------------------------------------------
     *  $tabla_original = '123';
     *  $modelo = new modelo_base();
     *
     *  $resultado = $this->obten_columnas($modelo, $tabla_original);
     *  // Retorna un error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error $tabla_bd no puede ser un numero',
     *  //   'data' => '123'
     *  // ]
     */
    private function obten_columnas(modelo_base $modelo, string $tabla_original): array|stdClass
    {
        // Normaliza el nombre de la tabla
        $tabla_original = trim(str_replace('models\\', '', $tabla_original));
        $tabla_bd = $tabla_original;

        // Validación de la tabla
        if ($tabla_bd === '') {
            return $this->error->error(mensaje: 'Error tabla original no puede venir vacia', data: $tabla_bd,
                es_final: true);
        }

        if (is_numeric($tabla_bd)) {
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero', data: $tabla_bd,
                es_final: true);
        }

        // Comprueba si las columnas ya están asignadas en la sesión
        $se_asignaron_columnas = $this->asigna_columnas_en_session(modelo: $modelo, tabla_bd: $tabla_bd);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar columnas', data: $se_asignaron_columnas);
        }

        // Si no están asignadas, las asigna a la sesión
        if (!$se_asignaron_columnas) {
            $columnas_field = $this->asigna_columnas_session_new(modelo: $modelo, tabla_bd: $tabla_bd);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas_field);
            }
        }

        // Retorna las columnas asignadas al modelo
        return $modelo->data_columnas;
    }


    /**
     * REG
     * Genera las columnas completas para una consulta SQL basándose en configuraciones y estructuras definidas.
     *
     * Esta función combina columnas específicas por tabla, columnas adicionales, extensiones de estructura,
     * uniones adicionales y reglas de renombramiento para producir una cadena de columnas SQL final.
     *
     * @param modelo_base $modelo Instancia del modelo base que representa la tabla principal de la consulta.
     *                            Ejemplo: `$modelo->tabla = 'usuarios';`.
     * @param array $columnas_by_table Listado de tablas para las cuales se generarán columnas específicas.
     *                                  Ejemplo: `['usuarios', 'ordenes']`.
     * @param bool $columnas_en_bruto Indica si las columnas deben procesarse en su forma original sin alias ni modificaciones.
     *                                Valor por defecto: `false`.
     * @param array $columnas_sql Columnas SQL predefinidas para construir la consulta.
     *                             Ejemplo: `['usuarios.id', 'usuarios.nombre']`.
     * @param array $extension_estructura Estructuras adicionales para extender las columnas de la consulta.
     * @param array $extra_join Configuración para unir columnas adicionales provenientes de otras tablas relacionadas.
     * @param array $renombres Reglas para renombrar tablas o columnas en la consulta.
     *
     * @return array|string Devuelve una cadena de columnas SQL generada o, en caso de error, un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $modelo = new modelo_base($link);
     * $modelo->tabla = 'usuarios';
     * $columnas_by_table = ['usuarios'];
     * $columnas_sql = ['usuarios.id', 'usuarios.nombre'];
     * $extension_estructura = [];
     * $extra_join = [];
     * $renombres = [];
     *
     * $resultado = $miClase->obten_columnas_completas(
     *     modelo: $modelo,
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: false,
     *     columnas_sql: $columnas_sql,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     renombres: $renombres
     * );
     *
     * echo $resultado;
     * // Salida esperada:
     * // "usuarios.id AS usuarios_id, usuarios.nombre AS usuarios_nombre "
     * ```
     *
     * ### Ejemplo de error:
     *
     * - Caso: `$columnas_by_table` está vacío pero las columnas específicas son requeridas.
     *
     * ```php
     * $modelo = new modelo_base($link);
     * $modelo->tabla = 'usuarios';
     * $columnas_by_table = [];
     * $columnas_sql = [];
     * $extension_estructura = [];
     * $extra_join = [];
     * $renombres = [];
     *
     * $resultado = $miClase->obten_columnas_completas(
     *     modelo: $modelo,
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: false,
     *     columnas_sql: $columnas_sql,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     renombres: $renombres
     * );
     *
     * print_r($resultado);
     * // Salida esperada:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error al integrar columnas en usuarios',
     * //     'data' => null
     * // ]
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$modelo`**:
     *   Instancia del modelo base que representa la tabla principal. Ejemplo: `$modelo->tabla = 'usuarios';`.
     *
     * - **`$columnas_by_table`**:
     *   Lista de tablas para generar columnas específicas. Ejemplo: `['usuarios', 'ordenes']`.
     *
     * - **`$columnas_en_bruto`**:
     *   Si es `true`, las columnas no tendrán alias ni transformaciones.
     *
     * - **`$columnas_sql`**:
     *   Columnas SQL predefinidas para incluir en la consulta.
     *
     * - **`$extension_estructura`**:
     *   Columnas adicionales provenientes de estructuras relacionadas.
     *
     * - **`$extra_join`**:
     *   Configuración para unir tablas adicionales con columnas relacionadas.
     *
     * - **`$renombres`**:
     *   Reglas para renombrar tablas o columnas.
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Devuelve una cadena de columnas SQL generada según las configuraciones proporcionadas.
     *
     * - **Error**:
     *   Devuelve un array con detalles del error si los parámetros de entrada no son válidos.
     */

    final public function obten_columnas_completas(modelo_base $modelo, array $columnas_by_table = array(),
                                                   bool $columnas_en_bruto = false, array $columnas_sql = array(),
                                                   array $extension_estructura = array(), array $extra_join = array(),
                                                   array $renombres = array()):array|string{


        $tablas_select = (new inicializacion())->tablas_select(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar tablas select en '.$modelo->tabla,
                data:  $tablas_select);
        }

        $columnas = $this->columnas_full(columnas_by_table: $columnas_by_table, columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $columnas_sql, extension_estructura: $extension_estructura, extra_join: $extra_join,
            modelo: $modelo, renombres: $renombres, tablas_select: $tablas_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas en '.$modelo->tabla, data: $columnas);
        }

        return $columnas.' ';
    }

    /**
     * REG
     * Genera una cadena SQL para un subquery con un alias especificado.
     * Valida que tanto el subquery como el alias sean cadenas no vacías antes de construir la sentencia SQL.
     *
     * @param string $alias El alias que se asignará al subquery en la sentencia SQL.
     *                      Este alias permite referenciar el resultado del subquery en consultas superiores.
     * @param string $sub_query La sentencia SQL que representa el subquery a incluir.
     *                           Debe ser una cadena SQL válida y no vacía.
     *
     * @return string|array Retorna una cadena con la sentencia SQL del subquery y su alias.
     *                      En caso de error, devuelve un array detallando el problema encontrado.
     *
     * @throws errores Si `$sub_query` o `$alias` están vacíos, devuelve un error con los detalles relevantes.
     *
     * @example Uso exitoso:
     * ```php
     * $alias = 'subconsulta_ventas';
     * $sub_query = '(SELECT SUM(precio) FROM ventas WHERE estado = "aprobado")';
     *
     * $resultado = $this->sub_query_str(alias: $alias, sub_query: $sub_query);
     * // Resultado esperado: "(SELECT SUM(precio) FROM ventas WHERE estado = "aprobado") AS subconsulta_ventas"
     * ```
     *
     * @example Error por alias vacío:
     * ```php
     * $alias = '';
     * $sub_query = '(SELECT SUM(precio) FROM ventas WHERE estado = "aprobado")';
     *
     * $resultado = $this->sub_query_str(alias: $alias, sub_query: $sub_query);
     * // Resultado esperado: Array indicando que el alias está vacío.
     * ```
     *
     * @example Error por subquery vacío:
     * ```php
     * $alias = 'subconsulta_ventas';
     * $sub_query = '';
     *
     * $resultado = $this->sub_query_str(alias: $alias, sub_query: $sub_query);
     * // Resultado esperado: Array indicando que el subquery está vacío.
     * ```
     */
    private function sub_query_str(string $alias, string $sub_query): string|array
    {
        $sub_query = trim($sub_query);
        if ($sub_query === '') {
            return $this->error->error(mensaje: 'Error sub_query esta vacio ', data: $sub_query, es_final: true);
        }
        $alias = trim($alias);
        if ($alias === '') {
            return $this->error->error(mensaje: 'Error alias esta vacio ', data: $alias, es_final: true);
        }
        return $sub_query . ' AS ' . $alias;
    }


    /**
     * REG
     * Genera una cadena SQL que incluye subconsultas (subqueries) con sus respectivos alias.
     * Valida que los subqueries y sus alias sean válidos antes de generar la sentencia SQL.
     *
     * @param string $columnas Las columnas actuales de la consulta, utilizadas para determinar la inclusión de una coma al inicio.
     * @param modelo_base $modelo El modelo que contiene los subqueries definidos en su propiedad `sub_querys`.
     * @param array $columnas_seleccionables (Opcional) Lista de alias permitidos para los subqueries.
     *                                        Si se proporciona, solo los subqueries cuyos alias están en esta lista serán procesados.
     *
     * @return array|string Una cadena SQL que incluye las subconsultas con sus alias.
     *                      En caso de error, devuelve un array detallando el problema encontrado.
     *
     * @throws errores Si un subquery o su alias están vacíos, o si un alias es numérico.
     *
     * @example Uso exitoso:
     * ```php
     * $columnas = 'id, nombre';
     * $modelo = new modelo_base();
     * $modelo->sub_querys = [
     *     'ventas_totales' => '(SELECT SUM(precio) FROM ventas WHERE estado = "aprobado")',
     *     'productos_stock' => '(SELECT COUNT(*) FROM productos WHERE stock > 0)'
     * ];
     * $columnas_seleccionables = ['ventas_totales'];
     *
     * $resultado = $this->sub_querys(columnas: $columnas, modelo: $modelo, columnas_seleccionables: $columnas_seleccionables);
     * // Resultado esperado: " , (SELECT SUM(precio) FROM ventas WHERE estado = "aprobado") AS ventas_totales"
     * ```
     *
     * @example Error por subquery vacío:
     * ```php
     * $columnas = 'id, nombre';
     * $modelo = new modelo_base();
     * $modelo->sub_querys = [
     *     'ventas_totales' => ''
     * ];
     *
     * $resultado = $this->sub_querys(columnas: $columnas, modelo: $modelo);
     * // Resultado esperado: Array indicando que el subquery está vacío.
     * ```
     *
     * @example Error por alias vacío:
     * ```php
     * $columnas = 'id, nombre';
     * $modelo = new modelo_base();
     * $modelo->sub_querys = [
     *     '' => '(SELECT SUM(precio) FROM ventas WHERE estado = "aprobado")'
     * ];
     *
     * $resultado = $this->sub_querys(columnas: $columnas, modelo: $modelo);
     * // Resultado esperado: Array indicando que el alias está vacío.
     * ```
     */
    final public function sub_querys(
        string $columnas,
        modelo_base $modelo,
        array $columnas_seleccionables = array()
    ): array|string {
        $sub_querys_sql = '';
        foreach ($modelo->sub_querys as $alias => $sub_query) {
            if ($sub_query === '') {
                return $this->error->error(mensaje: "Error el sub query no puede venir vacio",
                    data: $modelo->sub_querys, es_final: true);
            }
            if (trim($alias) === '') {
                return $this->error->error(mensaje: "Error el alias no puede venir vacio", data: $modelo->sub_querys,
                    es_final: true);
            }
            if (is_numeric($alias)) {
                return $this->error->error(mensaje: "Error el alias no puede ser un numero", data: $modelo->sub_querys,
                    es_final: true);
            }
            if ((count($columnas_seleccionables) > 0) && !in_array($alias, $columnas_seleccionables, true)) {
                continue;
            }
            $sub_query_str = $this->sub_query_str(alias: $alias, sub_query: $sub_query);
            if (errores::$error) {
                return $this->error->error(mensaje: "Error generar subquery con alias", data: $sub_query_str);
            }

            $coma = '';
            if ($sub_querys_sql === '' && $columnas === '') {
                $coma = ' , ';
            }

            $sub_querys_sql .= $coma . $sub_query_str;
        }

        return $sub_querys_sql;
    }


    /**
     * REG
     * Obtiene el nombre de una tabla renombrada a partir de los datos proporcionados.
     *
     * Esta función permite verificar si existe un valor de renombramiento para una tabla dentro del array `$data`.
     * Si el valor existe y no está vacío, devuelve el nombre renombrado. De lo contrario, devuelve el nombre original.
     *
     * @param array $data Datos que pueden contener el nombre renombrado de la tabla.
     *                    Debe incluir la clave opcional `renombre`.
     * @param string $tabla Nombre original de la tabla. No puede estar vacío.
     *
     * @return string|array El nombre renombrado de la tabla, o un array con los detalles del error si ocurre algún problema.
     *
     * @throws errores Si `$tabla` está vacío.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * // Caso donde no existe renombramiento
     * $data = [];
     * $tabla = 'usuarios';
     * $resultado = $miClase->tabla_renombrada_extra(data: $data, tabla: $tabla);
     * echo $resultado; // Salida: 'usuarios'
     *
     * // Caso donde se proporciona un renombramiento
     * $data = ['renombre' => 'clientes'];
     * $tabla = 'usuarios';
     * $resultado = $miClase->tabla_renombrada_extra(data: $data, tabla: $tabla);
     * echo $resultado; // Salida: 'clientes'
     * ```
     *
     * ### Ejemplo de datos inválidos:
     *
     * ```php
     * // Caso donde la tabla está vacía
     * $data = [];
     * $tabla = '';
     * $resultado = $miClase->tabla_renombrada_extra(data: $data, tabla: $tabla);
     * print_r($resultado);
     * // Salida:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error tabla esta vacia',
     * //     'data' => ''
     * // ]
     * ```
     *
     * ### Detalle de parámetros:
     *
     * - **`$data`** (array):
     *   Array asociativo que puede incluir la clave `renombre` para especificar un nuevo nombre de tabla.
     *   Si `renombre` no está presente o está vacío, se devuelve el nombre original.
     *
     *   **Ejemplo válido**:
     *   ```php
     *   ['renombre' => 'clientes']
     *   ```
     *
     * - **`$tabla`** (string):
     *   Nombre original de la tabla. Debe ser un string no vacío.
     *
     *   **Ejemplo válido**: `'usuarios'`
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Devuelve el nombre de la tabla, ya sea renombrado o el original.
     *   **Ejemplo**: `'clientes'` o `'usuarios'`
     *
     * - **Error**:
     *   Devuelve un array con los detalles del error, como:
     *   ```php
     *   [
     *       'error' => 1,
     *       'mensaje' => 'Error tabla esta vacia',
     *       'data' => ''
     *   ]
     *   ```
     */
    private function tabla_renombrada_extra(array $data, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if ($tabla === '') {
            return $this->error->error(
                mensaje: "Error tabla esta vacia",
                data: $tabla,
                es_final: true
            );
        }
        $tabla_renombrada = $tabla;
        if (isset($data['renombre'])) {
            $data['renombre'] = trim($data['renombre']);
            if ($data['renombre'] !== '') {
                $tabla_renombrada = $data['renombre'];
            }
        }
        return $tabla_renombrada;
    }



    /**
     * REG
     * Valida los parámetros requeridos para construir una consulta SQL.
     *
     * Esta función se encarga de verificar que los valores proporcionados para el nombre de la tabla,
     * la columna parseada y el alias de las columnas no estén vacíos. Si alguno de estos valores está vacío,
     * devuelve un error detallado. En caso contrario, devuelve `true` indicando que los valores son válidos.
     *
     * @param string $alias_columnas Alias utilizado para las columnas en la consulta SQL.
     * @param string $columna_parseada Nombre de la columna procesada o parseada.
     * @param string $tabla_nombre Nombre de la tabla asociada.
     *
     * @return true|array Devuelve `true` si todos los parámetros son válidos. Si algún parámetro no es válido, retorna
     * un array con detalles del error.
     *
     * @example
     * // Caso 1: Todos los parámetros son válidos
     * $resultado = $this->valida_columnas_sql(
     *     alias_columnas: 'c',
     *     columna_parseada: 'nombre',
     *     tabla_nombre: 'usuarios'
     * );
     * // Resultado: true
     *
     * @example
     * // Caso 2: Parámetro $tabla_nombre vacío
     * $resultado = $this->valida_columnas_sql(
     *     alias_columnas: 'c',
     *     columna_parseada: 'nombre',
     *     tabla_nombre: ''
     * );
     * // Resultado:
     * // [
     * //     'mensaje' => 'Error $tabla_nombre no puede venir vacia',
     * //     'data' => '',
     * //     'es_final' => true
     * // ]
     *
     * @example
     * // Caso 3: Parámetro $columna_parseada vacío
     * $resultado = $this->valida_columnas_sql(
     *     alias_columnas: 'c',
     *     columna_parseada: '',
     *     tabla_nombre: 'usuarios'
     * );
     * // Resultado:
     * // [
     * //     'mensaje' => 'Error $columna_parseada no puede venir vacia',
     * //     'data' => '',
     * //     'es_final' => true
     * // ]
     *
     * @example
     * // Caso 4: Parámetro $alias_columnas vacío
     * $resultado = $this->valida_columnas_sql(
     *     alias_columnas: '',
     *     columna_parseada: 'nombre',
     *     tabla_nombre: 'usuarios'
     * );
     * // Resultado:
     * // [
     * //     'mensaje' => 'Error $alias_columnas no puede venir vacia',
     * //     'data' => '',
     * //     'es_final' => true
     * // ]
     */
    private function valida_columnas_sql(
        string $alias_columnas,
        string $columna_parseada,
        string $tabla_nombre
    ): true|array {
        if ($tabla_nombre === '') {
            return $this->error->error(
                mensaje: 'Error $tabla_nombre no puede venir vacia',
                data: $tabla_nombre,
                es_final: true
            );
        }
        if ($columna_parseada === '') {
            return $this->error->error(
                mensaje: 'Error $columna_parseada no puede venir vacia',
                data: $columna_parseada,
                es_final: true
            );
        }
        if ($alias_columnas === '') {
            return $this->error->error(
                mensaje: 'Error $alias_columnas no puede venir vacia',
                data: $alias_columnas,
                es_final: true
            );
        }
        return true;
    }


}

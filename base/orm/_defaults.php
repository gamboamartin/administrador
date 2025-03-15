<?php
namespace base\orm;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;

class _defaults{

    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }

    /**
     * REG
     * Ajusta y limpia los datos de un catálogo eliminando registros duplicados según los campos clave.
     *
     * Esta función recorre los registros del catálogo y verifica la existencia de duplicados
     * en la base de datos utilizando los campos `id`, `descripcion` y `codigo`.
     * Si un registro ya existe en la base de datos, se elimina del catálogo.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param array $catalogo Lista de registros a procesar.
     *                        - **Ejemplo de entrada:**
     *                          ```php
     *                          $catalogo = [
     *                              ['id' => 1, 'codigo' => 'ABC123', 'descripcion' => 'Producto A'],
     *                              ['id' => 2, 'codigo' => 'DEF456', 'descripcion' => 'Producto B']
     *                          ];
     *                          ```
     *
     * @param modelo $modelo Instancia del modelo que representa la tabla en la base de datos.
     *                       Se utiliza para verificar la existencia de registros.
     *                       - **Ejemplo de uso:**
     *                         ```php
     *                         $modelo = new producto_modelo($pdo);
     *                         $modelo->tabla = 'productos';
     *                         ```
     *
     * ---
     *
     * @return array Retorna el catálogo actualizado sin los registros duplicados si estos ya existen en la base de datos.
     *               Si un registro no existe en la base de datos, se mantiene en el catálogo.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     *
     * ```php
     * $modelo = new producto_modelo($pdo);
     * $modelo->tabla = 'productos';
     *
     * $catalogo = [
     *     ['id' => 1, 'codigo' => 'ABC123', 'descripcion' => 'Producto A'],
     *     ['id' => 2, 'codigo' => 'DEF456', 'descripcion' => 'Producto B']
     * ];
     *
     * $catalogo_actualizado = $this->ajusta_data_catalogo(catalogo: $catalogo, modelo: $modelo);
     * print_r($catalogo_actualizado);
     * ```
     *
     * **Salida esperada si los productos ya existen en la base de datos:**
     * ```php
     * []
     * ```
     *
     * **Salida esperada si 'ABC123' no existe en la base de datos pero 'DEF456' sí existe:**
     * ```php
     * [
     *     ['id' => 1, 'codigo' => 'ABC123', 'descripcion' => 'Producto A']
     * ]
     * ```
     *
     * ---
     *
     * ### **Manejo de Errores**
     *
     * **Ejemplo 1: Si hay un error al limpiar el catálogo**
     * ```php
     * $catalogo_actualizado = $this->ajusta_data_catalogo(catalogo: [], modelo: $modelo);
     * ```
     * **Salida esperada (si hay un error interno):**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al limpiar catalogo',
     *     'data' => []
     * ]
     * ```
     *
     * ---
     *
     * **Ejemplo 2: Si falta un campo clave en los registros**
     * ```php
     * $catalogo = [
     *     ['id' => 1, 'codigo' => 'ABC123'],
     *     ['id' => 2, 'descripcion' => 'Producto B']
     * ];
     *
     * $catalogo_actualizado = $this->ajusta_data_catalogo(catalogo: $catalogo, modelo: $modelo);
     * ```
     * **Salida esperada (si falta el campo `descripcion` o `codigo` en algún registro):**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al limpiar catalogo',
     *     'data' => [...]
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si ocurre un problema con la limpieza del catálogo.
     */
    private function ajusta_data_catalogo(array $catalogo, modelo $modelo): array
    {

        $campos = array('id','descripcion','codigo');
        foreach ($campos as $campo) {
            $catalogo = $this->ajusta_datas_catalogo(catalogo: $catalogo,campo:  $campo,modelo:  $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar catalogo', data: $catalogo);
            }
        }
        return $catalogo;
    }

    /**
     * REG
     * Ajusta los datos de un catálogo eliminando registros duplicados si ya existen en la base de datos.
     *
     * Esta función recorre el catálogo y verifica si los registros contienen un campo específico.
     * Si el campo está presente, se genera un filtro para determinar si el registro ya existe en la base de datos.
     * Si el registro existe, se elimina del catálogo.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param array $catalogo Lista de registros a procesar.
     *                        - **Ejemplo:**
     *                          ```php
     *                          $catalogo = [
     *                              ['id' => 1, 'codigo' => 'ABC123', 'nombre' => 'Producto A'],
     *                              ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     *                          ];
     *                          ```
     *
     * @param string $campo Nombre del campo a utilizar como clave de verificación en la base de datos.
     *                      - **Ejemplo:** `'codigo'`
     *
     * @param modelo $modelo Instancia del modelo que representa la tabla en la base de datos.
     *                       Se utiliza para verificar la existencia de registros.
     *                       - **Ejemplo:** `$modelo->tabla = 'productos';`
     *
     * ---
     *
     * @return array Retorna el catálogo actualizado sin los registros duplicados si estos ya existen en la base de datos.
     *               Si el campo no está presente o el registro no existe en la base de datos, devuelve el catálogo sin cambios.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     *
     * ```php
     * $modelo = new producto_modelo($pdo);
     * $modelo->tabla = 'productos';
     *
     * $catalogo = [
     *     ['id' => 1, 'codigo' => 'ABC123', 'nombre' => 'Producto A'],
     *     ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     * ];
     *
     * $campo = 'codigo';
     *
     * $catalogo_actualizado = $this->ajusta_datas_catalogo(catalogo: $catalogo, campo: $campo, modelo: $modelo);
     * print_r($catalogo_actualizado);
     * ```
     *
     * **Salida esperada si 'ABC123' y 'DEF456' existen en la base de datos:**
     * ```php
     * []
     * ```
     *
     * **Salida esperada si 'ABC123' no existe en la base de datos pero 'DEF456' sí existe:**
     * ```php
     * [
     *     ['id' => 1, 'codigo' => 'ABC123', 'nombre' => 'Producto A']
     * ]
     * ```
     *
     * ---
     *
     * ### **Manejo de Errores**
     *
     * **Ejemplo 1: Si el campo está vacío**
     * ```php
     * $catalogo_actualizado = $this->ajusta_datas_catalogo(catalogo: $catalogo, campo: '', modelo: $modelo);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error $campo esta vacio',
     *     'data' => ''
     * ]
     * ```
     *
     * ---
     *
     * **Ejemplo 2: Si hay un error al limpiar el catálogo**
     * ```php
     * $catalogo_actualizado = $this->ajusta_datas_catalogo(catalogo: [], campo: 'codigo', modelo: $modelo);
     * ```
     * **Salida esperada (si hay un error interno):**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al limpiar catalogo',
     *     'data' => []
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si ocurre un problema con el campo vacío o la limpieza del catálogo.
     */
    private function ajusta_datas_catalogo(array $catalogo, string $campo, modelo $modelo): array
    {

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error $campo esta vacio', data: $campo, es_final: true);
        }

        foreach ($catalogo as $indice => $row) {
            $catalogo = $this->ajusta_row(campo: $campo, catalogo: $catalogo, indice: $indice, modelo: $modelo,
                row: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar catalogo', data: $catalogo);
            }
        }
        return $catalogo;
    }

    /**
     * REG
     * Ajusta un registro dentro de un catálogo si un campo específico está presente.
     *
     * Esta función verifica si un campo determinado está presente en un registro (`$row`) dentro del catálogo.
     * Si el campo existe, genera un filtro basado en su valor y lo utiliza para verificar si el registro ya existe
     * en la base de datos. Si el registro ya existe, se elimina del catálogo.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param string $campo Nombre del campo a verificar y ajustar dentro del registro.
     *                      - **Ejemplo:** `'codigo'`
     *
     * @param array $catalogo Array de registros en el catálogo a limpiar.
     *                        - **Ejemplo:**
     *                          ```php
     *                          $catalogo = [
     *                              ['id' => 1, 'codigo' => 'ABC123', 'nombre' => 'Producto A'],
     *                              ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     *                          ];
     *                          ```
     *
     * @param int $indice Índice del registro dentro del `$catalogo` que se verificará y, si existe en la base de datos, se eliminará.
     *                    - **Ejemplo:** `0` (para eliminar el primer registro en el catálogo).
     *
     * @param modelo $modelo Instancia del modelo que representa la tabla en la base de datos.
     *                       Se utilizará para realizar la verificación de existencia.
     *                       - **Ejemplo:** `$modelo->tabla = 'productos';`
     *
     * @param array $row Registro individual del catálogo a verificar y limpiar.
     *                   - **Ejemplo:**
     *                     ```php
     *                     $row = ['codigo' => 'ABC123', 'nombre' => 'Producto A'];
     *                     ```
     *
     * ---
     *
     * @return array Retorna el catálogo actualizado sin el registro eliminado si ya existía en la base de datos.
     *               Si el campo no está presente o el registro no existe en la base de datos, devuelve el catálogo sin cambios.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     *
     * ```php
     * $modelo = new producto_modelo($pdo);
     * $modelo->tabla = 'productos';
     *
     * $catalogo = [
     *     ['id' => 1, 'codigo' => 'ABC123', 'nombre' => 'Producto A'],
     *     ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     * ];
     *
     * $row = ['codigo' => 'ABC123', 'nombre' => 'Producto A'];
     * $campo = 'codigo';
     * $indice = 0;
     *
     * $catalogo_actualizado = $this->ajusta_row(campo: $campo, catalogo: $catalogo, indice: $indice, modelo: $modelo, row: $row);
     * print_r($catalogo_actualizado);
     * ```
     *
     * **Salida esperada si el producto con código 'ABC123' existe en la base de datos:**
     * ```php
     * [
     *     ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     * ]
     * ```
     *
     * **Salida esperada si el producto con código 'ABC123' NO existe en la base de datos:**
     * ```php
     * [
     *     ['id' => 1, 'codigo' => 'ABC123', 'nombre' => 'Producto A'],
     *     ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     * ]
     * ```
     *
     * ---
     *
     * ### **Manejo de Errores**
     *
     * **Ejemplo 1: Si el campo está vacío**
     * ```php
     * $catalogo_actualizado = $this->ajusta_row(campo: '', catalogo: $catalogo, indice: 0, modelo: $modelo, row: $row);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error $campo esta vacio',
     *     'data' => ''
     * ]
     * ```
     *
     * ---
     *
     * **Ejemplo 2: Si hay un error al generar el filtro**
     * ```php
     * $catalogo_actualizado = $this->ajusta_row(campo: 'codigo', catalogo: $catalogo, indice: 0, modelo: $modelo, row: []);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al generar filtro',
     *     'data' => null
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si ocurre un problema con el campo vacío, el filtro o la limpieza del catálogo.
     */
    private function ajusta_row(string $campo, array $catalogo, int $indice, modelo $modelo, array $row): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error $campo esta vacio', data: $campo, es_final: true);
        }
        if(isset($row[$campo])) {
            $filtro = $this->filtro(campo: $campo, modelo: $modelo, row: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
            }

            $catalogo = $this->limpia_si_existe(catalogo: $catalogo, filtro: $filtro, indice: $indice,
                modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar catalogo', data: $catalogo);
            }
        }
        return $catalogo;
    }

    /**
     * REG
     * Inserta registros por defecto en la base de datos si no existen previamente.
     *
     * Esta función recorre un catálogo de registros y verifica si cada uno ya existe en la base de datos.
     * Si un registro no existe, se inserta en la tabla correspondiente. También ajusta los datos antes de la inserción.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param array $catalogo Lista de registros a insertar si no existen en la base de datos.
     *                        - **Ejemplo de entrada:**
     *                          ```php
     *                          $catalogo = [
     *                              ['id' => 1, 'codigo' => 'ABC123', 'descripcion' => 'Producto A'],
     *                              ['id' => 2, 'codigo' => 'DEF456', 'descripcion' => 'Producto B']
     *                          ];
     *                          ```
     *
     * @param modelo $entidad Instancia del modelo que representa la tabla en la base de datos.
     *                        Se utiliza para verificar la existencia de registros e insertarlos si es necesario.
     *                        - **Ejemplo de uso:**
     *                          ```php
     *                          $entidad = new producto_modelo($pdo);
     *                          $entidad->tabla = 'productos';
     *                          ```
     *
     * @param array $filtro (Opcional) Filtros adicionales para verificar si un registro ya existe antes de insertarlo.
     *                      Si está vacío, se generará automáticamente un filtro basado en `codigo`.
     *                      - **Ejemplo:** `['productos.status' => 'activo']`
     *
     * ---
     *
     * @return array Retorna el catálogo actualizado con los registros que han sido insertados o ya existían.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     *
     * ```php
     * $entidad = new producto_modelo($pdo);
     * $entidad->tabla = 'productos';
     *
     * $catalogo = [
     *     ['id' => 1, 'codigo' => 'ABC123', 'descripcion' => 'Producto A'],
     *     ['id' => 2, 'codigo' => 'DEF456', 'descripcion' => 'Producto B']
     * ];
     *
     * $catalogo_actualizado = $this->alta_defaults(catalogo: $catalogo, entidad: $entidad);
     * print_r($catalogo_actualizado);
     * ```
     *
     * **Salida esperada si los productos ya existen en la base de datos:**
     * ```php
     * []
     * ```
     *
     * **Salida esperada si 'ABC123' no existe en la base de datos pero 'DEF456' sí existe:**
     * ```php
     * [
     *     ['id' => 1, 'codigo' => 'ABC123', 'descripcion' => 'Producto A']
     * ]
     * ```
     *
     * ---
     *
     * ### **Manejo de Errores**
     *
     * **Ejemplo 1: Si hay un error al limpiar el catálogo**
     * ```php
     * $catalogo_actualizado = $this->alta_defaults(catalogo: [], entidad: $entidad);
     * ```
     * **Salida esperada (si hay un error interno):**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al ajustar catalogo',
     *     'data' => []
     * ]
     * ```
     *
     * ---
     *
     * **Ejemplo 2: Si falta un campo clave en los registros**
     * ```php
     * $catalogo = [
     *     ['id' => 1, 'codigo' => 'ABC123'],
     *     ['id' => 2, 'descripcion' => 'Producto B']
     * ];
     *
     * $catalogo_actualizado = $this->alta_defaults(catalogo: $catalogo, entidad: $entidad);
     * ```
     * **Salida esperada (si falta el campo `descripcion` o `codigo` en algún registro):**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al limpiar catalogo',
     *     'data' => [...]
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si ocurre un problema con la limpieza del catálogo o la inserción de registros.
     */
    final public function alta_defaults(array $catalogo, modelo $entidad, array $filtro = array()): array
    {

        $catalogo = $this->ajusta_data_catalogo(catalogo: $catalogo,modelo:  $entidad);
        if (errores::$error) {
            $error = $this->error->error(mensaje: 'Error al ajustar catalogo', data: $catalogo);
            print_r($error);
            exit;
        }

        foreach ($catalogo as $row) {
            $r_alta_bd = $this->inserta_default(entidad: $entidad,row:  $row, filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar', data: $r_alta_bd);
            }
        }
        return $catalogo;
    }

    /**
     * REG
     * Verifica si un código específico ya existe en la base de datos dentro de la tabla de la entidad proporcionada.
     *
     * Esta función revisa si un código (`codigo`) ya está registrado en la base de datos para una entidad específica.
     * Si el código existe en `$row`, se genera un filtro de búsqueda basado en `codigo` y se consulta la base de datos
     * utilizando el método `existe()` del modelo.
     *
     * @param modelo $entidad Instancia del modelo donde se realizará la búsqueda.
     *                        Se utilizará la propiedad `$tabla` de esta entidad para formar el filtro.
     *                        Ejemplo:
     *                        ```php
     *                        $modelo = new modelo();
     *                        $modelo->tabla = 'clientes';
     *                        ```
     *
     * @param array $row Registro que contiene los datos a validar, incluyendo el campo `codigo` si está presente.
     *                   Ejemplo:
     *                   ```php
     *                   $row = [
     *                       'clientes.codigo' => 'CLI123',
     *                       'nombre' => 'Empresa XYZ'
     *                   ];
     *                   ```
     *
     * @param array $filtro (Opcional) Filtro adicional que puede ser usado en la consulta.
     *                      Si está vacío, se generará automáticamente con base en el campo `codigo`.
     *                      Ejemplo:
     *                      ```php
     *                      $filtro = ['clientes.status' => 'activo'];
     *                      ```
     *
     * @return bool|array Retorna `true` si el código ya existe en la base de datos,
     *                    `false` si no existe, o un `array` con un mensaje de error si ocurre una falla.
     *
     * @example
     * // Ejemplo 1: Código existente en la base de datos
     * $modelo = new modelo();
     * $modelo->tabla = 'clientes';
     *
     * $row = [
     *     'clientes.codigo' => 'CLI123',
     *     'nombre' => 'Empresa XYZ'
     * ];
     *
     * $resultado = $this->existe_cod_default(entidad: $modelo, row: $row);
     * print_r($resultado);
     *
     * // Salida esperada:
     * // true (si el código ya existe)
     *
     * @example
     * // Ejemplo 2: Código inexistente en la base de datos
     * $modelo = new modelo();
     * $modelo->tabla = 'clientes';
     *
     * $row = [
     *     'clientes.codigo' => 'CLI999', // Código no registrado
     *     'nombre' => 'Empresa ABC'
     * ];
     *
     * $resultado = $this->existe_cod_default(entidad: $modelo, row: $row);
     * print_r($resultado);
     *
     * // Salida esperada:
     * // false (si el código no existe)
     *
     * @example
     * // Ejemplo 3: Error por tabla vacía
     * $modelo = new modelo();
     * $modelo->tabla = ''; // Error: La tabla está vacía
     *
     * $row = [
     *     'clientes.codigo' => 'CLI123',
     *     'nombre' => 'Empresa XYZ'
     * ];
     *
     * $resultado = $this->existe_cod_default(entidad: $modelo, row: $row);
     * print_r($resultado);
     *
     * // Salida esperada:
     * // [
     * //     'error' => 1,
     * //     'mensaje' => 'Error $entidad->tabla esta vacia',
     * //     'data' => ''
     * // ]
     */
    private function existe_cod_default(modelo $entidad, array $row, array $filtro = array()): bool|array
    {
        $entidad->tabla = trim($entidad->tabla);
        if($entidad->tabla === ''){
            return $this->error->error(mensaje: 'Error $entidad->tabla esta vacia', data: $entidad->tabla);
        }
        $existe = false;
        if(isset($row[$entidad->tabla.'.codigo'])) {
            $filtro = $this->filtro_default(entidad: $entidad, row: $row, filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
            }

            $existe = $entidad->existe(filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar si existe cat_sat_tipo_de_comprobante',
                    data: $existe);
            }
        }
        return $existe;
    }

    /**
     * REG
     * Genera un filtro para la consulta en la base de datos basado en el campo y la tabla del modelo.
     *
     * Esta función construye un filtro en forma de array asociativo, donde la clave es la combinación de
     * la tabla del modelo y el campo especificado, y el valor es el contenido del campo en el array `$row`.
     * Si el campo no existe en `$row`, se asigna un valor vacío `''`.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param string $campo Nombre del campo a utilizar en el filtro.
     *                      - **Ejemplo:** `'codigo'`
     *
     * @param modelo $modelo Instancia del modelo donde se realizará la búsqueda.
     *                       La tabla del modelo debe estar definida y no vacía.
     *                       - **Ejemplo:** `$modelo->tabla = 'clientes';`
     *
     * @param array $row Registro de datos del cual se extraerá el valor del campo.
     *                   - **Ejemplo:**
     *                     ```php
     *                     $row = [
     *                         'codigo' => 'CLI123',
     *                         'nombre' => 'Empresa XYZ'
     *                     ];
     *                     ```
     *
     * ---
     *
     * @return array Retorna un array con el filtro generado para la consulta en la base de datos.
     *               En caso de error, retorna un array con el mensaje de error.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     *
     * ```php
     * $modelo = new adm_usuario($pdo);
     * $modelo->tabla = 'clientes';
     *
     * $row = [
     *     'codigo' => 'CLI123',
     *     'nombre' => 'Empresa XYZ'
     * ];
     *
     * $filtro = $this->filtro(campo: 'codigo', modelo: $modelo, row: $row);
     * print_r($filtro);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     'clientes.codigo' => 'CLI123'
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo cuando el campo no existe en `$row`:**
     *
     * ```php
     * $row = [
     *     'nombre' => 'Empresa XYZ'
     * ];
     *
     * $filtro = $this->filtro(campo: 'codigo', modelo: $modelo, row: $row);
     * print_r($filtro);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     'clientes.codigo' => ''
     * ]
     * ```
     *
     * ---
     *
     * ### **Manejo de Errores**
     *
     * **Ejemplo 1: Si la tabla del modelo está vacía**
     * ```php
     * $modelo->tabla = ''; // Error
     * $resultado = $this->filtro(campo: 'codigo', modelo: $modelo, row: $row);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error $modelo->tabla esta vacio',
     *     'data' => ''
     * ]
     * ```
     *
     * **Ejemplo 2: Si el campo está vacío**
     * ```php
     * $campo = ''; // Error
     * $resultado = $this->filtro(campo: $campo, modelo: $modelo, row: $row);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error $campo esta vacio',
     *     'data' => ''
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si la tabla del modelo o el campo están vacíos.
     */
    private function filtro(string $campo, modelo $modelo, array $row): array
    {
        $modelo->tabla = trim($modelo->tabla);
        if($modelo->tabla === ''){
            return $this->error->error(mensaje: 'Error $modelo->tabla esta vacio', data: $modelo->tabla,
                es_final: true);
        }

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error $campo esta vacio', data: $campo, es_final: true);
        }

        if(!isset($row[$campo])){
            $row[$campo] = '';
        }

        $filtro = array();
        $filtro[$modelo->tabla.'.'.$campo] = $row[$campo];
        return $filtro;
    }

    /**
     * REG
     * Genera un filtro para validar la existencia de un registro en la base de datos.
     *
     * Esta función crea un filtro basado en el valor del campo `codigo` dentro del registro `$row`.
     * Si no se proporciona un filtro preexistente (`$filtro` está vacío), se validará que el campo `codigo` esté presente en `$row`.
     * Luego, se construirá un filtro para verificar la existencia del registro en la tabla de la entidad proporcionada.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param modelo $entidad Instancia del modelo que representa la entidad en la base de datos.
     *                        Se utilizará su propiedad `$tabla` para formar el filtro.
     *                        - **Ejemplo:** `new adm_usuario($pdo)` representa la tabla `adm_usuario`.
     *
     * @param array $row Registro que contiene los datos a validar, incluyendo el campo `codigo`.
     *                   - **Ejemplo:** `['codigo' => 'USR123', 'nombre' => 'Juan Pérez']`
     *
     * @param array $filtro (Opcional) Filtro preexistente que puede ser combinado con el generado.
     *                      Si está vacío, se generará un filtro con base en `codigo`.
     *                      - **Ejemplo:** `['adm_usuario.email' => 'usuario@example.com']`
     *
     * ---
     *
     * @return array Retorna un array con el filtro generado para validar la existencia del registro.
     *               Si `$filtro` ya contenía valores, se conservarán y se añadirá el nuevo filtro.
     *               En caso de error, la función devolverá un array con el mensaje de error.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     * ```php
     * $modelo = new adm_usuario($pdo);
     * $row = ['codigo' => 'USR123', 'nombre' => 'Juan Pérez'];
     * $filtro = $modelo->filtro_default(entidad: $modelo, row: $row);
     * print_r($filtro);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     'adm_usuario.codigo' => 'USR123'
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con Filtro Preexistente:**
     * ```php
     * $modelo = new adm_usuario($pdo);
     * $row = ['codigo' => 'USR123', 'nombre' => 'Juan Pérez'];
     * $filtro_existente = ['adm_usuario.email' => 'usuario@example.com'];
     * $filtro = $modelo->filtro_default(entidad: $modelo, row: $row, filtro: $filtro_existente);
     * print_r($filtro);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     'adm_usuario.email' => 'usuario@example.com',
     *     'adm_usuario.codigo' => 'USR123'
     * ]
     * ```
     *
     * ---
     *
     * ### **Manejo de Errores**
     *
     * **Ejemplo 1: Si el campo `codigo` no está presente en `$row`**
     * ```php
     * $row = ['nombre' => 'Juan Pérez'];
     * $filtro = $modelo->filtro_default(entidad: $modelo, row: $row);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al validar row',
     *     'data' => ['nombre' => 'Juan Pérez']
     * ]
     * ```
     *
     * **Ejemplo 2: Si la tabla está vacía**
     * ```php
     * $modelo->tabla = ''; // Forzar un error
     * $filtro = $modelo->filtro_default(entidad: $modelo, row: ['codigo' => 'USR123']);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error tabla esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si la tabla está vacía o si el campo `codigo` no está presente en `$row`.
     */
    private function filtro_default(modelo $entidad, array $row, array $filtro = array()): array
    {
        // Validar que la tabla de la entidad no esté vacía
        $tabla = trim($entidad->tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla esta vacia', data: $tabla);
        }

        // Si no se ha pasado un filtro preexistente, validar la existencia del campo 'codigo' en el registro
        if(count($filtro) === 0) {
            $keys = array('codigo');
            $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar row', data: $valida);
            }

            // Generar filtro basado en el campo 'codigo'
            $filtro[$tabla . '.codigo'] = $row['codigo'];
        }

        return $filtro;
    }


    /**
     * REG
     * Inserta un registro en la base de datos si no existe previamente en la entidad.
     *
     * Esta función verifica si un registro ya existe en la base de datos mediante su código (`codigo`).
     * Si no existe, procede a insertar el registro utilizando el método `alta_registro` del modelo proporcionado.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param modelo $entidad Instancia del modelo donde se realizará la inserción.
     *                        La entidad debe contener la propiedad `$tabla` definida.
     *                        - **Ejemplo:** `$modelo = new adm_usuario($pdo);`
     *
     * @param array $row Datos del registro que se intentará insertar.
     *                   Debe contener al menos el campo `codigo`.
     *                   - **Ejemplo:**
     *                     ```php
     *                     $row = [
     *                         'codigo' => 'USR001',
     *                         'nombre' => 'Juan Pérez'
     *                     ];
     *                     ```
     *
     * @param array $filtro (Opcional) Filtro adicional para comprobar la existencia del registro.
     *                      Si está vacío, se generará un filtro basado en el campo `codigo`.
     *                      - **Ejemplo:**
     *                        ```php
     *                        $filtro = ['status' => 'activo'];
     *                        ```
     *
     * ---
     *
     * @return array Retorna el mismo `$row` en caso de éxito.
     *               Si ocurre un error, devuelve un array con el mensaje de error.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     *
     * ```php
     * $modelo = new adm_usuario($pdo);
     * $row = [
     *     'codigo' => 'USR001',
     *     'nombre' => 'Juan Pérez'
     * ];
     *
     * $resultado = $this->inserta_default(entidad: $modelo, row: $row);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada:**
     * ```php
     * [
     *     'codigo' => 'USR001',
     *     'nombre' => 'Juan Pérez'
     * ]
     * ```
     *
     * ---
     *
     * ### **Ejemplo con Registro Existente:**
     *
     * ```php
     * $modelo = new adm_usuario($pdo);
     * $row = [
     *     'codigo' => 'USR001',
     *     'nombre' => 'Juan Pérez'
     * ];
     *
     * $resultado = $this->inserta_default(entidad: $modelo, row: $row);
     * print_r($resultado);
     * ```
     *
     * **Salida esperada si el usuario ya existe:**
     * ```php
     * [
     *     'codigo' => 'USR001',
     *     'nombre' => 'Juan Pérez'
     * ]
     * ```
     *
     * ---
     *
     * ### **Manejo de Errores**
     *
     * **Ejemplo 1: Si la tabla de la entidad está vacía**
     * ```php
     * $modelo->tabla = ''; // Error
     * $resultado = $this->inserta_default(entidad: $modelo, row: $row);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error $entidad->tabla esta vacia',
     *     'data' => ''
     * ]
     * ```
     *
     * **Ejemplo 2: Si el campo `codigo` no está presente en `$row`**
     * ```php
     * $row = ['nombre' => 'Juan Pérez'];
     * $resultado = $this->inserta_default(entidad: $modelo, row: $row);
     * ```
     * **Salida esperada:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al validar row',
     *     'data' => ['nombre' => 'Juan Pérez']
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si la entidad no está definida correctamente
     *               o si el registro no cumple con los requisitos mínimos.
     */
    private function inserta_default(modelo $entidad, array $row, array $filtro = array()): array
    {
        $existe = $this->existe_cod_default(entidad: $entidad,row:  $row, filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe entidad'.$entidad->tabla, data: $existe);
        }

        if (!$existe) {
            $r_alta_bd = $entidad->alta_registro(registro: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar', data: $r_alta_bd);
            }
        }
        return $row;
    }

    /**
     * REG
     * Elimina un registro del catálogo si ya existe en la base de datos.
     *
     * Esta función verifica si un registro determinado ya existe en la base de datos con base en un filtro dado.
     * Si el registro existe, se elimina del array `$catalogo`. Si no existe, el catálogo se devuelve sin cambios.
     *
     * ---
     *
     * ### **Parámetros:**
     *
     * @param array $catalogo Array de registros en el catálogo a limpiar.
     *                        - **Ejemplo:**
     *                          ```php
     *                          $catalogo = [
     *                              ['id' => 1, 'codigo' => 'ABC123', 'nombre' => 'Producto A'],
     *                              ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     *                          ];
     *                          ```
     *
     * @param array $filtro Filtro utilizado para verificar la existencia del registro en la base de datos.
     *                      Debe estar estructurado como un array asociativo con los campos y valores a buscar.
     *                      - **Ejemplo:**
     *                        ```php
     *                        $filtro = ['productos.codigo' => 'ABC123'];
     *                        ```
     *
     * @param int $indice Índice del registro dentro del `$catalogo` que se verificará y, si existe en la base de datos, se eliminará.
     *                    - **Ejemplo:** `0` (para eliminar el primer registro en el catálogo).
     *
     * @param modelo $modelo Instancia del modelo que representa la tabla en la base de datos.
     *                       Debe implementar el método `existe()`, que permite verificar si el registro ya está almacenado.
     *                       - **Ejemplo:** `$modelo->tabla = 'productos';`
     *
     * ---
     *
     * @return array Retorna el catálogo actualizado sin el registro eliminado si ya existía en la base de datos.
     *               Si no existía, devuelve el catálogo original sin cambios.
     *
     * ---
     *
     * ### **Ejemplo de Uso:**
     *
     * ```php
     * $modelo = new producto_modelo($pdo);
     * $modelo->tabla = 'productos';
     *
     * $catalogo = [
     *     ['id' => 1, 'codigo' => 'ABC123', 'nombre' => 'Producto A'],
     *     ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     * ];
     *
     * $filtro = ['productos.codigo' => 'ABC123'];
     *
     * $catalogo_actualizado = $this->limpia_si_existe(catalogo: $catalogo, filtro: $filtro, indice: 0, modelo: $modelo);
     * print_r($catalogo_actualizado);
     * ```
     *
     * **Salida esperada si el producto con código 'ABC123' existe en la base de datos:**
     * ```php
     * [
     *     ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     * ]
     * ```
     *
     * **Salida esperada si el producto con código 'ABC123' NO existe en la base de datos:**
     * ```php
     * [
     *     ['id' => 1, 'codigo' => 'ABC123', 'nombre' => 'Producto A'],
     *     ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     * ]
     * ```
     *
     * ---
     *
     * ### **Manejo de Errores**
     *
     * **Ejemplo 1: Si hay un error al verificar la existencia del registro en la base de datos**
     * ```php
     * $resultado = $this->limpia_si_existe(catalogo: $catalogo, filtro: $filtro, indice: 0, modelo: $modelo);
     * ```
     * **Salida esperada si ocurre un error:**
     * ```php
     * [
     *     'error' => true,
     *     'mensaje' => 'Error al verificar si existe',
     *     'data' => null
     * ]
     * ```
     *
     * ---
     *
     * **Ejemplo 2: Si el índice no existe en el catálogo**
     * ```php
     * $resultado = $this->limpia_si_existe(catalogo: $catalogo, filtro: $filtro, indice: 10, modelo: $modelo);
     * ```
     * **Salida esperada (catálogo sin cambios, ya que el índice no existe):**
     * ```php
     * [
     *     ['id' => 1, 'codigo' => 'ABC123', 'nombre' => 'Producto A'],
     *     ['id' => 2, 'codigo' => 'DEF456', 'nombre' => 'Producto B']
     * ]
     * ```
     *
     * ---
     *
     * @throws array Devuelve un array con un mensaje de error si hay problemas al verificar la existencia del registro.
     */
    private function limpia_si_existe(array $catalogo, array $filtro, int $indice, modelo $modelo): array
    {
        $existe = $modelo->existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al verificar si existe', data: $existe);
        }
        if($existe){
            unset($catalogo[$indice]);
        }
        return $catalogo;
    }
}

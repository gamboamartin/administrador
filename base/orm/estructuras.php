<?php
namespace base\orm;
use config\database;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;

class estructuras{
    private errores  $error;
    public stdClass $estructura_bd;
    private PDO $link;

    private validacion $validacion;
    public function __construct(PDO $link){
        $this->error = new errores();
        $this->estructura_bd = new stdClass();
        $this->link = $link;
        $this->validacion = new validacion();
    }

    /**
     * REG
     * Asigna la información estructural de un campo dentro de la estructura de base de datos del modelo.
     *
     * Esta función valida y estructura los datos de un campo dentro del modelo correspondiente en la base de datos,
     * asegurando que contenga todas las propiedades esenciales como su tipo de dato, si es clave primaria,
     * foránea, auto_increment, permite valores nulos, entre otros.
     *
     * @param array $campo Datos del campo en la base de datos.
     *                     Debe contener las claves `Field`, `Type`, `Default`, `Extra`, `Key` y `Null`.
     *                     - Ejemplo de estructura esperada:
     *                     ```php
     *                     [
     *                         'Field' => 'cliente_id',
     *                         'Type' => 'int(11)',
     *                         'Null' => 'YES',
     *                         'Default' => null,
     *                         'Extra' => '',
     *                         'Key' => 'MUL'
     *                     ]
     *                     ```
     * @param array $keys_no_foraneas Lista de claves que no deben considerarse foráneas.
     *                                 - Ejemplo:
     *                                 ```php
     *                                 ['usuario_alta', 'usuario_update']
     *                                 ```
     * @param string $name_modelo Nombre del modelo al cual pertenece el campo.
     *                            - No puede estar vacío.
     *                            - Ejemplo: `'facturas'`.
     *
     * @return array|stdClass Devuelve el objeto `estructura_bd` con la información del campo asignada.
     *                        En caso de error, devuelve un array con detalles del problema.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo = [
     *     'Field' => 'cliente_id',
     *     'Type' => 'int(11)',
     *     'Null' => 'YES',
     *     'Default' => null,
     *     'Extra' => '',
     *     'Key' => 'MUL'
     * ];
     *
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     * $name_modelo = 'facturas';
     *
     * $resultado = $this->asigna_dato_estructura($campo, $keys_no_foraneas, $name_modelo);
     * // Resultado esperado:
     * // $this->estructura_bd->facturas->data_campos->cliente_id = {
     * //   'tabla_foranea' => 'clientes',
     * //   'es_foranea' => true,
     * //   'permite_null' => true,
     * //   'campo_name' => 'cliente_id',
     * //   'tipo_dato' => 'int(11)',
     * //   'es_primaria' => false,
     * //   'valor_default' => null,
     * //   'extra' => '',
     * //   'es_auto_increment' => false,
     * //   'tipo_llave' => 'MUL'
     * // }
     * ```
     *
     * ### Ejemplo de error (`name_modelo` vacío):
     * ```php
     * $resultado = $this->asigna_dato_estructura($campo, $keys_no_foraneas, '');
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $name_modelo esta vacio',
     * //   'data' => '',
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validaciones iniciales:**
     *    - Verifica que `$name_modelo` no esté vacío.
     *    - Verifica que `$campo` contenga las claves `Field`, `Type`, `Default`, `Extra`, `Key` y `Null`.
     *    - Llama a `valida_existencia_keys` para asegurarse de que `Field` exista en `$campo`.
     * 2. **Inicialización de la estructura en `$this->estructura_bd`:**
     *    - Llama a `init_estructura_campo` para registrar el campo en la estructura del modelo.
     * 3. **Registro de la información del campo:**
     *    - Llama a `inicializa_campo` para obtener las propiedades estructurales del campo.
     *    - Llama a `maqueta_estructura` para almacenar la información en `estructura_bd`.
     * 4. **Retorno del resultado:**
     *    - Devuelve `estructura_bd` con la información agregada.
     *    - Si ocurre un error, devuelve un array con detalles del problema.
     *
     * ### Casos de uso:
     * - **Contexto:** Definición de la estructura de modelos en una aplicación ORM.
     * - **Ejemplo real:** Registrar un campo `cliente_id` en la estructura de la tabla `facturas`.
     *
     * ### Consideraciones:
     * - Asegúrate de que `$campo` y `$campo_init` contengan la información esperada antes de llamar a esta función.
     * - La función maneja errores mediante la clase `errores`, proporcionando mensajes detallados.
     */
    private function asigna_dato_estructura(array $campo, array $keys_no_foraneas, string $name_modelo): array|stdClass
    {
        $name_modelo = trim($name_modelo);
        if ($name_modelo === '') {
            return $this->error->error(mensaje: 'Error $name_modelo esta vacio', data: $name_modelo);
        }

        $keys = ['Field'];
        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $campo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar keys', data: $valida);
        }

        $required_keys = ['Null', 'Key', 'Field', 'Type', 'Default', 'Extra'];
        foreach ($required_keys as $key) {
            if (!isset($campo[$key])) {
                return $this->error->error(mensaje: "Error \$campo[$key] no existe", data: $campo, es_final: true);
            }
        }

        $init = $this->init_estructura_campo(campo: $campo, name_modelo: $name_modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $init);
        }

        $campo_init = $this->inicializa_campo(campo: $campo, keys_no_foraneas: $keys_no_foraneas);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializar campo', data: $campo_init);
        }

        $estructura_bd = $this->maqueta_estructura(campo: $campo, campo_init: $campo_init, name_modelo: $name_modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
        }

        return $estructura_bd;
    }



    /**
     * @param string $name_db Nombre de la base de datos
     * @return array|stdClass
     */
    final public function asigna_datos_estructura(string $name_db): array|stdClass
    {
        $modelos = $this->modelos(name_db: $name_db);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener modelos', data: $modelos);
        }
        $keys_no_foraneas = array('usuario_alta','usuario_update');
        $estructura_bd = $this->genera_estructura(keys_no_foraneas: $keys_no_foraneas, modelos:$modelos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
        }

        $estructura_bd = $this->asigna_foraneas(estructura_bd: $estructura_bd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd);
        }


        $this->estructura_bd = $estructura_bd;

        return $estructura_bd;
    }

    /**
     * REG
     * Asigna el nombre de una tabla de base de datos a un arreglo de modelos.
     *
     * Esta función valida el nombre de la base de datos, genera la clave correspondiente para identificar
     * la tabla en los resultados de la consulta `SHOW TABLES`, y extrae el valor del arreglo `$row` para
     * agregarlo al arreglo de `$modelos`.
     *
     * @param array $modelos Arreglo de modelos donde se asignarán los nombres de las tablas.
     *                       - Ejemplo: `['tabla_1', 'tabla_2']`.
     * @param string $name_db Nombre de la base de datos.
     *                        - No puede estar vacío.
     * @param array $row Arreglo que contiene los resultados de las tablas obtenidas de `SHOW TABLES`.
     *                   - Debe contener la clave generada en formato `Tables_in_{name_db}`.
     *
     * @return array Devuelve el arreglo actualizado de modelos con el nombre de la tabla agregada.
     *               En caso de error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $modelos = ['tabla_1'];
     * $name_db = 'mi_base_datos';
     * $row = [
     *     'Tables_in_mi_base_datos' => 'tabla_2'
     * ];
     *
     * $resultado = $this->asigna_data_modelo(modelos: $modelos, name_db: $name_db, row: $row);
     *
     * // Resultado esperado:
     * // ['tabla_1', 'tabla_2']
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: $name_db vacío
     * $modelos = ['tabla_1'];
     * $name_db = '';
     * $row = [
     *     'Tables_in_mi_base_datos' => 'tabla_2'
     * ];
     *
     * $resultado = $this->asigna_data_modelo(modelos: $modelos, name_db: $name_db, row: $row);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error name db esta vacio',
     * //   'data' => ''
     * // ]
     *
     * // Caso 2: $row no contiene la clave generada
     * $modelos = ['tabla_1'];
     * $name_db = 'mi_base_datos';
     * $row = [];
     *
     * $resultado = $this->asigna_data_modelo(modelos: $modelos, name_db: $name_db, row: $row);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error no existe $row[$key]',
     * //   'data' => 'Tables_in_mi_base_datos'
     * // ]
     *
     * // Caso 3: $row contiene la clave, pero está vacía
     * $modelos = ['tabla_1'];
     * $name_db = 'mi_base_datos';
     * $row = [
     *     'Tables_in_mi_base_datos' => ''
     * ];
     *
     * $resultado = $this->asigna_data_modelo(modelos: $modelos, name_db: $name_db, row: $row);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error esta vacio $row[$key]',
     * //   'data' => 'Tables_in_mi_base_datos'
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación de parámetros:**
     *    - `$name_db` no debe estar vacío.
     *    - `$row` debe contener la clave generada basada en `$name_db`.
     * 2. **Generación de la clave de tabla:**
     *    - Usa `key_table` para generar la clave en formato `Tables_in_{name_db}`.
     * 3. **Validación de existencia de la clave en `$row`:**
     *    - Verifica que `$row` contenga la clave generada y que no esté vacía.
     * 4. **Asignación del nombre de la tabla:**
     *    - Agrega el valor correspondiente de `$row[$key]` al arreglo `$modelos`.
     * 5. **Retorno del resultado:**
     *    - Devuelve el arreglo `$modelos` actualizado.
     *    - En caso de error, devuelve un array con los detalles del error.
     *
     * ### Casos de uso:
     * - **Contexto:** Procesamiento de los resultados de consultas `SHOW TABLES` en bases de datos.
     * - **Ejemplo real:** Extraer los nombres de las tablas de `mi_base_datos` y asignarlos a un arreglo de modelos.
     *
     * ### Consideraciones:
     * - Asegúrate de que `$name_db` sea válido y que `$row` contenga las claves esperadas antes de llamar a esta función.
     * - La función maneja errores utilizando la clase `errores`, asegurando una retroalimentación clara y detallada.
     */
    private function asigna_data_modelo(array $modelos, string $name_db, array $row): array
    {
        $name_db = trim($name_db);
        if ($name_db === '') {
            return $this->error->error(mensaje: 'Error name db esta vacio', data: $name_db);
        }

        $key = $this->key_table(name_db: $name_db);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar key', data: $key);
        }

        if (!isset($row[$key])) {
            return $this->error->error(mensaje: 'Error no existe $row[$key] ', data: $key);
        }
        if (trim($row[$key]) === '') {
            return $this->error->error(mensaje: 'Error esta vacio $row[$key] ', data: $key);
        }

        $data = $row[$key];
        $modelos[] = $data;
        return $modelos;
    }


    /**
     * REG
     * Asigna la información de una clave foránea dentro de la estructura del modelo.
     *
     * Esta función valida la existencia de una relación foránea en la estructura del modelo y la registra en el objeto `foraneas`,
     * asegurando que la entidad tenga correctamente identificadas sus dependencias en la base de datos.
     *
     * @param stdClass $data Datos del campo que contiene la referencia foránea.
     *                       Debe contener la clave `tabla_foranea`.
     *                       - Ejemplo:
     *                       ```php
     *                       (object) [
     *                           'tabla_foranea' => 'clientes'
     *                       ]
     *                       ```
     * @param stdClass $estructura_bd Estructura general de la base de datos donde se almacena la relación.
     * @param stdClass $foraneas Objeto que almacena las claves foráneas asignadas.
     * @param string $modelo Nombre del modelo donde se asignará la clave foránea.
     *                       - No puede estar vacío.
     *                       - Ejemplo: `'facturas'`
     *
     * @return stdClass|array Devuelve la estructura general con la relación foránea asignada.
     *                        En caso de error, devuelve un array con detalles del problema.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $data = (object) ['tabla_foranea' => 'clientes'];
     * $estructura_bd = new stdClass();
     * $foraneas = new stdClass();
     * $modelo = 'facturas';
     *
     * $resultado = $this->asigna_dato_foranea($data, $estructura_bd, $foraneas, $modelo);
     *
     * // Resultado esperado:
     * // $estructura_bd->facturas->tiene_foraneas = true;
     * // $foraneas->clientes = new stdClass();
     * ```
     *
     * ### Ejemplo de error (`modelo` vacío):
     * ```php
     * $resultado = $this->asigna_dato_foranea($data, $estructura_bd, $foraneas, '');
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $modelo esta vacio',
     * //   'data' => '',
     * //   'es_final' => true
     * // ]
     * ```
     *
     * ### Ejemplo de error (`tabla_foranea` no existe en `$data`):
     * ```php
     * $data = (object) [];
     * $resultado = $this->asigna_dato_foranea($data, $estructura_bd, $foraneas, 'facturas');
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $data->tabla_foranea no existe',
     * //   'data' => (object) [],
     * //   'es_final' => true
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validaciones iniciales:**
     *    - Verifica que `$modelo` no esté vacío.
     *    - Confirma que `$data` contenga la clave `tabla_foranea`.
     *    - Verifica que `tabla_foranea` no esté vacía.
     * 2. **Registro de la clave foránea en `estructura_bd`:**
     *    - Crea la estructura de `$modelo` si no existe en `estructura_bd`.
     *    - Marca que el modelo tiene claves foráneas (`tiene_foraneas = true`).
     * 3. **Registro en `foraneas`:**
     *    - Agrega la tabla referenciada como una clave foránea en el objeto `foraneas`.
     * 4. **Retorno del resultado:**
     *    - Devuelve `estructura_bd` con la relación asignada.
     *    - Si ocurre un error, devuelve un array con detalles del problema.
     *
     * ### Casos de uso:
     * - **Contexto:** Registrar claves foráneas en la estructura de modelos dentro de un ORM.
     * - **Ejemplo real:** Asignar la tabla `clientes` como clave foránea en el modelo `facturas`.
     *
     * ### Consideraciones:
     * - Asegúrate de que `$data->tabla_foranea` contenga un valor válido antes de llamar a esta función.
     * - La función maneja errores mediante la clase `errores`, proporcionando mensajes detallados.
     */
    private function asigna_dato_foranea(
        stdClass $data,
        stdClass $estructura_bd,
        stdClass $foraneas,
        string $modelo
    ): stdClass|array {
        $modelo = trim($modelo);
        if ($modelo === '') {
            return $this->error->error(mensaje: 'Error $modelo esta vacio', data: $modelo, es_final: true);
        }

        if (!isset($data->tabla_foranea)) {
            return $this->error->error(mensaje: 'Error $data->tabla_foranea no existe', data: $data, es_final: true);
        }

        $data->tabla_foranea = trim($data->tabla_foranea);
        if ($data->tabla_foranea === '') {
            return $this->error->error(mensaje: 'Error $data->tabla_foranea esta vacia', data: $data, es_final: true);
        }

        if (!isset($estructura_bd->$modelo)) {
            $estructura_bd->$modelo = new stdClass();
        }

        $tabla_foranea = $data->tabla_foranea;
        $foraneas->$tabla_foranea = new stdClass();
        $estructura_bd->$modelo->tiene_foraneas = true;

        return $estructura_bd;
    }


    /**
     * REG
     * Asigna y estructura los datos de los campos de un modelo en la base de datos.
     *
     * Esta función recorre los campos de una tabla y los integra en la estructura del modelo correspondiente,
     * validando la presencia de claves esenciales y asegurando que cada campo esté correctamente definido
     * dentro de la estructura de la base de datos.
     *
     * @param array $data_table Lista de campos de la tabla en formato array asociativo.
     *                          Cada elemento debe contener las claves `Field`, `Type`, `Null`, `Default`, `Extra` y `Key`.
     *                          - Ejemplo:
     *                          ```php
     *                          [
     *                              [
     *                                  'Field' => 'id',
     *                                  'Type' => 'int(11)',
     *                                  'Null' => 'NO',
     *                                  'Default' => null,
     *                                  'Extra' => 'auto_increment',
     *                                  'Key' => 'PRI'
     *                              ],
     *                              [
     *                                  'Field' => 'cliente_id',
     *                                  'Type' => 'int(11)',
     *                                  'Null' => 'YES',
     *                                  'Default' => null,
     *                                  'Extra' => '',
     *                                  'Key' => 'MUL'
     *                              ]
     *                          ]
     *                          ```
     * @param array $keys_no_foraneas Lista de claves que no deben considerarse foráneas.
     *                                 - Ejemplo:
     *                                 ```php
     *                                 ['usuario_alta', 'usuario_update']
     *                                 ```
     * @param string $name_modelo Nombre del modelo al cual pertenecen los campos.
     *                            - No puede estar vacío.
     *                            - Ejemplo: `'facturas'`.
     *
     * @return array|stdClass Devuelve la estructura del modelo con los datos asignados.
     *                        En caso de error, devuelve un array con detalles del problema.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $data_table = [
     *     [
     *         'Field' => 'id',
     *         'Type' => 'int(11)',
     *         'Null' => 'NO',
     *         'Default' => null,
     *         'Extra' => 'auto_increment',
     *         'Key' => 'PRI'
     *     ],
     *     [
     *         'Field' => 'cliente_id',
     *         'Type' => 'int(11)',
     *         'Null' => 'YES',
     *         'Default' => null,
     *         'Extra' => '',
     *         'Key' => 'MUL'
     *     ]
     * ];
     *
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     * $name_modelo = 'facturas';
     *
     * $resultado = $this->asigna_datos_modelo($data_table, $keys_no_foraneas, $name_modelo);
     *
     * // Resultado esperado:
     * // $this->estructura_bd->facturas->data_campos->id = {
     * //   'tabla_foranea' => '',
     * //   'es_foranea' => false,
     * //   'permite_null' => false,
     * //   'campo_name' => 'id',
     * //   'tipo_dato' => 'int(11)',
     * //   'es_primaria' => true,
     * //   'valor_default' => null,
     * //   'extra' => 'auto_increment',
     * //   'es_auto_increment' => true,
     * //   'tipo_llave' => 'PRI'
     * // }
     * ```
     *
     * ### Ejemplo de error (`name_modelo` vacío):
     * ```php
     * $resultado = $this->asigna_datos_modelo($data_table, $keys_no_foraneas, '');
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $name_modelo esta vacio',
     * //   'data' => ''
     * // ]
     * ```
     *
     * ### Ejemplo de error (`data_table` contiene un elemento que no es un array):
     * ```php
     * $data_table = [
     *     'Field' => 'id',
     *     'Type' => 'int(11)',
     *     'Null' => 'NO',
     *     'Default' => null,
     *     'Extra' => 'auto_increment',
     *     'Key' => 'PRI'
     * ];
     *
     * $resultado = $this->asigna_datos_modelo($data_table, $keys_no_foraneas, 'facturas');
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $campo en $data_table debe ser un array',
     * //   'data' => [...],
     * //   'es_final' => true
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validaciones iniciales:**
     *    - Verifica que `$name_modelo` no esté vacío.
     *    - Recorre cada campo en `$data_table` y valida que sea un array.
     *    - Verifica que cada campo contenga las claves `Field`, `Type`, `Null`, `Default`, `Extra` y `Key`.
     * 2. **Asignación de los datos a la estructura:**
     *    - Llama a `asigna_dato_estructura` para procesar y registrar cada campo en la estructura.
     * 3. **Retorno del resultado:**
     *    - Devuelve la estructura del modelo con los datos de los campos asignados.
     *    - Si ocurre un error, devuelve un array con detalles del problema.
     *
     * ### Casos de uso:
     * - **Contexto:** Definir la estructura de los modelos en una aplicación ORM.
     * - **Ejemplo real:** Registrar los campos de la tabla `facturas` en la estructura de datos del sistema.
     *
     * ### Consideraciones:
     * - Asegúrate de que `$data_table` sea un array de arrays y que cada campo contenga las claves requeridas.
     * - La función maneja errores mediante la clase `errores`, proporcionando mensajes detallados.
     */
    private function asigna_datos_modelo(
        array $data_table, array $keys_no_foraneas, string $name_modelo
    ): array|stdClass {
        $name_modelo = trim($name_modelo);
        if ($name_modelo === '') {
            return $this->error->error(mensaje: 'Error $name_modelo esta vacio', data: $name_modelo);
        }

        $estructura_bd = array();
        foreach ($data_table as $campo) {
            if (!is_array($campo)) {
                return $this->error->error(
                    mensaje: 'Error $campo en $data_table debe ser un array',
                    data: $estructura_bd,
                    es_final: true
                );
            }

            $keys = ['Field'];
            $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $campo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar keys', data: $valida);
            }

            if(!isset($campo['Default'])){
                $campo['Default'] = '';
            }

            $required_keys = ['Null', 'Key', 'Type', 'Default', 'Extra'];
            foreach ($required_keys as $key) {
                if (!isset($campo[$key])) {
                    return $this->error->error(
                        mensaje: "Error \$campo[$key] no existe",
                        data: $campo,
                        es_final: true
                    );
                }
            }

            $estructura_bd = $this->asigna_dato_estructura(
                campo: $campo,
                keys_no_foraneas: $keys_no_foraneas,
                name_modelo: $name_modelo
            );

            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
            }
        }

        return $estructura_bd;
    }


    /**
     * @param stdClass $estructura_bd
     * @return array|stdClass
     */
    private function asigna_foraneas(stdClass $estructura_bd): array|stdClass
    {
        $estructura_bd_r = $estructura_bd;
        foreach ($estructura_bd as $modelo=>$estructura){
            $estructura_bd_r = $this->calcula_foranea(estructura: $estructura,estructura_bd: $estructura_bd_r,modelo: $modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd);
            }
        }
        return $estructura_bd_r;
    }

    /**
     * @param stdClass $estructura
     * @param stdClass $estructura_bd
     * @param string $modelo
     * @return array|stdClass
     */
    private function calcula_foranea(stdClass $estructura, stdClass $estructura_bd, string $modelo): array|stdClass
    {
        $estructura_bd_r = $estructura_bd;

        $estructura_bd_r->$modelo->tiene_foraneas = false;
        $data_campos = $estructura->data_campos;
        $foraneas = new stdClass();

        $estructura_bd_r = $this->genera_foranea(data_campos: $data_campos,estructura_bd: $estructura_bd_r,
            foraneas: $foraneas,modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd_r);
        }

        $estructura_bd_r->$modelo->foraneas = $foraneas;
        return $estructura_bd_r;
    }

    /**
     * @param string $name_db
     * @return array
     */
    final public function entidades(string $name_db): array
    {

        if(!isset($_SESSION['entidades_bd'])){
            $data = $this->asigna_datos_estructura(name_db: $name_db);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener estructura', data: $data);
            }
            $entidades = array();
            foreach ($data as $entidad=>$data_ent){
                $entidades[] = $entidad;
            }
            $_SESSION['entidades_bd'] = $entidades;
        }

        return $_SESSION['entidades_bd'];
    }


    /**
     * REG
     * Determina si un campo de la base de datos es auto_incremental.
     *
     * Esta función verifica si la clave `Extra` en el array de datos del campo contiene el valor `'auto_increment'`,
     * lo que indica que el campo es auto_incremental.
     *
     * @param array $campo Datos del campo en la base de datos.
     *                     Puede contener la clave `Extra` con el valor `'auto_increment'` si el campo lo es.
     *                     - Ejemplo de estructura esperada:
     *                     ```php
     *                     [
     *                         'Field' => 'id',
     *                         'Type' => 'int(11)',
     *                         'Extra' => 'auto_increment'
     *                     ]
     *                     ```
     *
     * @return bool|array Retorna `true` si el campo es auto_incremental, `false` si no lo es,
     *                    o un array de error si hay problemas con los datos de entrada.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo = [
     *     'Field' => 'id',
     *     'Type' => 'int(11)',
     *     'Extra' => 'auto_increment'
     * ];
     *
     * $resultado = $this->es_auto_increment($campo);
     * // Resultado esperado:
     * // true
     * ```
     *
     * ```php
     * $campo = [
     *     'Field' => 'nombre',
     *     'Type' => 'varchar(255)',
     *     'Extra' => ''
     * ];
     *
     * $resultado = $this->es_auto_increment($campo);
     * // Resultado esperado:
     * // false
     * ```
     *
     * ### Ejemplo con `Extra` no definido:
     * ```php
     * $campo = [
     *     'Field' => 'edad',
     *     'Type' => 'int(3)'
     * ];
     *
     * $resultado = $this->es_auto_increment($campo);
     * // Resultado esperado:
     * // false (se asigna '' a 'Extra' por defecto)
     * ```
     *
     * ### Proceso de la función:
     * 1. **Verificación de la existencia de la clave `Extra` en `$campo`:**
     *    - Si `Extra` no está presente, se inicializa como una cadena vacía (`''`).
     * 2. **Determinación de si es auto_incremental:**
     *    - Si `Extra` es `'auto_increment'`, el campo es auto_incremental y retorna `true`.
     *    - En cualquier otro caso, retorna `false`.
     * 3. **Retorno del resultado:**
     *    - Devuelve `true` si el campo es auto_incremental.
     *    - Devuelve `false` si no es auto_incremental.
     *
     * ### Casos de uso:
     * - **Contexto:** Validación de estructuras de base de datos en un ORM.
     * - **Ejemplo real:** Determinar si el campo `id` en una tabla de usuarios es auto_incremental.
     *
     * ### Consideraciones:
     * - Si `Extra` no está definido en `$campo`, se asigna como `''` en lugar de generar un error.
     * - La función no maneja errores explícitamente, pero asegura que `Extra` siempre tenga un valor válido.
     */
    private function es_auto_increment(array $campo): bool|array
    {
        if (!isset($campo['Extra'])) {
            $campo['Extra'] = '';
        }

        $es_auto_increment = false;
        if ($campo['Extra'] === 'auto_increment') {
            $es_auto_increment = true;
        }

        return $es_auto_increment;
    }


    /**
     * REG
     * Determina si un campo de la base de datos es una clave foránea.
     *
     * Esta función analiza el nombre del campo para determinar si representa una clave foránea.
     * Se considera una clave foránea si su nombre termina en `_id` y no está presente en la lista de claves excluidas (`keys_no_foraneas`).
     *
     * @param array $campo Datos del campo en la base de datos.
     *                     Debe contener la clave `Field`, que representa el nombre del campo en la tabla.
     *                     - Ejemplo de estructura esperada:
     *                     ```php
     *                     [
     *                         'Field' => 'cliente_id',
     *                         'Type' => 'int(11)'
     *                     ]
     *                     ```
     * @param array $keys_no_foraneas Lista de claves que no deben considerarse foráneas.
     *                                 - Ejemplo:
     *                                 ```php
     *                                 ['usuario_alta', 'usuario_update']
     *                                 ```
     *
     * @return bool|array Retorna `true` si el campo es una clave foránea, `false` si no lo es,
     *                    o un array de error si la clave `Field` no está definida en `$campo`.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo = [
     *     'Field' => 'cliente_id',
     *     'Type' => 'int(11)'
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->es_foranea($campo, $keys_no_foraneas);
     * // Resultado esperado:
     * // true
     * ```
     *
     * ```php
     * $campo = [
     *     'Field' => 'nombre',
     *     'Type' => 'varchar(255)'
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->es_foranea($campo, $keys_no_foraneas);
     * // Resultado esperado:
     * // false
     * ```
     *
     * ```php
     * $campo = [
     *     'Field' => 'usuario_update',
     *     'Type' => 'int(11)'
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->es_foranea($campo, $keys_no_foraneas);
     * // Resultado esperado:
     * // false (ya que está en keys_no_foraneas)
     * ```
     *
     * ### Ejemplo de error (clave `Field` no definida):
     * ```php
     * $campo = [
     *     'Type' => 'int(11)'
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->es_foranea($campo, $keys_no_foraneas);
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error al campo[Field] no existe',
     * //   'data' => [
     * //       'Type' => 'int(11)'
     * //   ]
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Verificación de la existencia de la clave `Field` en `$campo`:**
     *    - Si `Field` no está presente, se retorna un error.
     * 2. **Análisis del nombre del campo:**
     *    - Se divide el nombre en partes separadas por `_id` utilizando `explode('_id', $campo['Field'])`.
     * 3. **Verificación de si es una clave foránea:**
     *    - Si el nombre tiene `_id` al final y no está en la lista `keys_no_foraneas`, se considera foránea (`true`).
     *    - Si está en `keys_no_foraneas`, no se considera foránea (`false`).
     * 4. **Retorno del resultado:**
     *    - Devuelve `true` si el campo es una clave foránea.
     *    - Devuelve `false` si no es clave foránea.
     *    - Devuelve un array de error si la clave `Field` no está definida en `$campo`.
     *
     * ### Casos de uso:
     * - **Contexto:** Validación de estructuras de base de datos en un ORM.
     * - **Ejemplo real:** Determinar si el campo `cliente_id` en una tabla de facturas es una clave foránea.
     *
     * ### Consideraciones:
     * - Asegúrate de que el array `$campo` contiene la clave `Field` antes de llamar a esta función.
     * - La función maneja errores mediante la clase `errores`, asegurando que los errores sean informados correctamente.
     */
    private function es_foranea(array $campo, array $keys_no_foraneas): bool|array
    {
        if (!isset($campo['Field'])) {
            return $this->error->error(mensaje: 'Error al campo[Field] no existe', data: $campo);
        }

        $es_foranea = false;
        $explode_campo = explode('_id', $campo['Field']);

        if ((count($explode_campo) > 1) && $explode_campo[1] === '') {
            $es_no_foranea = in_array($explode_campo[0], $keys_no_foraneas, true);
            if (!$es_no_foranea) {
                $es_foranea = true;
            }
        }

        return $es_foranea;
    }


    /**
     * REG
     * Determina si un campo es una clave primaria en la base de datos.
     *
     * Esta función verifica si el campo especificado tiene la clave `Key` con el valor `'PRI'`,
     * lo que indica que es una clave primaria.
     *
     * @param array $campo Datos del campo en la base de datos.
     *                     Debe contener la clave `Key` con los valores esperados `'PRI'` para claves primarias.
     *                     - Ejemplo de estructura esperada:
     *                     ```php
     *                     [
     *                         'Field' => 'id',
     *                         'Type' => 'int(11)',
     *                         'Key' => 'PRI'
     *                     ]
     *                     ```
     *
     * @return bool|array Retorna `true` si el campo es clave primaria, `false` si no lo es,
     *                    o un array de error si la clave `Key` no está definida en `$campo`.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo = [
     *     'Field' => 'id',
     *     'Type' => 'int(11)',
     *     'Key' => 'PRI'
     * ];
     *
     * $resultado = $this->es_primaria($campo);
     * // Resultado esperado:
     * // true
     * ```
     *
     * ```php
     * $campo = [
     *     'Field' => 'nombre',
     *     'Type' => 'varchar(255)',
     *     'Key' => ''
     * ];
     *
     * $resultado = $this->es_primaria($campo);
     * // Resultado esperado:
     * // false
     * ```
     *
     * ### Ejemplo de error (clave `Key` no definida):
     * ```php
     * $campo = [
     *     'Field' => 'nombre',
     *     'Type' => 'varchar(255)'
     * ];
     *
     * $resultado = $this->es_primaria($campo);
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error campo[Key] debe existir',
     * //   'data' => [
     * //       'Field' => 'nombre',
     * //       'Type' => 'varchar(255)'
     * //   ]
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Verificación de la existencia de la clave `Key` en `$campo`:**
     *    - Si la clave `Key` no está presente, se retorna un error.
     * 2. **Determinación de si es clave primaria:**
     *    - Si `Key` es `'PRI'`, significa que el campo es una clave primaria, por lo que retorna `true`.
     *    - En cualquier otro caso, se considera que no es una clave primaria y retorna `false`.
     * 3. **Retorno del resultado:**
     *    - Devuelve `true` si el campo es clave primaria.
     *    - Devuelve `false` si no es clave primaria.
     *    - Devuelve un array de error si la clave `Key` no está definida en `$campo`.
     *
     * ### Casos de uso:
     * - **Contexto:** Validación de estructuras de base de datos en un ORM.
     * - **Ejemplo real:** Determinar si el campo `id` en una tabla de usuarios es una clave primaria.
     *
     * ### Consideraciones:
     * - Asegúrate de que el array `$campo` contiene la clave `Key` antes de llamar a esta función.
     * - La función maneja errores mediante la clase `errores`, asegurando que los errores sean informados correctamente.
     */
    private function es_primaria(array $campo): bool|array
    {
        if (!isset($campo['Key'])) {
            return $this->error->error(mensaje: 'Error campo[Key] debe existir', data: $campo);
        }

        $es_primaria = false;
        if ($campo['Key'] === 'PRI') {
            $es_primaria = true;
        }

        return $es_primaria;
    }


    /**
     * REG
     * Verifica si una entidad (tabla) existe en la base de datos.
     *
     * Este método:
     * 1. Valida que el nombre de la entidad no esté vacío.
     * 2. Genera una consulta `SHOW TABLES LIKE` para buscar la entidad en la base de datos.
     * 3. Ejecuta la consulta y verifica si se encuentran registros.
     *
     * @param string $entidad El nombre de la entidad (tabla) que se desea verificar.
     *
     * @return bool|array
     *   - `true`: Si la entidad existe en la base de datos.
     *   - `false`: Si la entidad no existe en la base de datos.
     *   - `array`: Si ocurre un error durante el proceso, retorna un arreglo con los detalles del error.
     *
     * @example
     *  Ejemplo 1: Verificar la existencia de una tabla existente
     *  ---------------------------------------------------------
     *  $entidad = 'usuarios';
     *  $existe = $this->existe_entidad($entidad);
     *  // Resultado:
     *  // true (si la tabla "usuarios" existe)
     *
     * @example
     *  Ejemplo 2: Verificar una tabla inexistente
     *  ------------------------------------------
     *  $entidad = 'tabla_inexistente';
     *  $existe = $this->existe_entidad($entidad);
     *  // Resultado:
     *  // false (si la tabla no existe)
     *
     * @example
     *  Ejemplo 3: Error por entidad vacía
     *  -----------------------------------
     *  $entidad = '';
     *  $existe = $this->existe_entidad($entidad);
     *  // Resultado:
     *  // [
     *  //   'error' => true,
     *  //   'mensaje' => 'Error entidad vacia',
     *  //   'data' => ''
     *  // ]
     */
    final public function existe_entidad(string $entidad): bool|array
    {
        // Limpia el nombre de la entidad
        $entidad = trim($entidad);

        // Valida que la entidad no esté vacía
        if ($entidad === '') {
            return $this->error->error(mensaje: 'Error entidad vacia', data: $entidad, es_final: true);
        }

        // Genera la consulta SQL para buscar la entidad
        $sql = (new sql())->show_tables(entidad: $entidad);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sql', data: $sql);
        }

        // Ejecuta la consulta
        $result = (new modelo_base($this->link))->ejecuta_consulta(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }

        // Verifica si la consulta devolvió resultados
        $existe_entidad = false;
        if ($result->n_registros > 0) {
            $existe_entidad = true;
        }

        return $existe_entidad;
    }


    /**
     * REG
     * Genera la estructura de los modelos a partir de los datos de la base de datos.
     *
     * Esta función recorre la lista de modelos proporcionados, inicializa la estructura de cada modelo y
     * asigna los datos de sus respectivos campos, validando la existencia y la correcta estructura de la información.
     *
     * @param array $keys_no_foraneas Lista de claves que no deben considerarse foráneas.
     *                                - Ejemplo:
     *                                ```php
     *                                ['usuario_alta', 'usuario_update']
     *                                ```
     * @param array $modelos Lista de nombres de los modelos (tablas) a procesar.
     *                       - No puede contener nombres vacíos o numéricos.
     *                       - Ejemplo:
     *                       ```php
     *                       ['facturas', 'clientes', 'productos']
     *                       ```
     *
     * @return array|stdClass Devuelve la estructura generada con los datos asignados de cada modelo.
     *                        En caso de error, devuelve un array con detalles del problema.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     * $modelos = ['facturas', 'clientes', 'productos'];
     *
     * $resultado = $this->genera_estructura($keys_no_foraneas, $modelos);
     *
     * // Resultado esperado:
     * // $this->estructura_bd->facturas->data_campos->id = {
     * //   'tabla_foranea' => '',
     * //   'es_foranea' => false,
     * //   'permite_null' => false,
     * //   'campo_name' => 'id',
     * //   'tipo_dato' => 'int(11)',
     * //   'es_primaria' => true,
     * //   'valor_default' => null,
     * //   'extra' => 'auto_increment',
     * //   'es_auto_increment' => true,
     * //   'tipo_llave' => 'PRI'
     * // }
     * ```
     *
     * ### Ejemplo de error (`name_modelo` vacío):
     * ```php
     * $resultado = $this->genera_estructura($keys_no_foraneas, ['']);
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error name_modelo esta vacio',
     * //   'data' => '',
     * //   'es_final' => true
     * // ]
     * ```
     *
     * ### Ejemplo de error (`name_modelo` es numérico):
     * ```php
     * $resultado = $this->genera_estructura($keys_no_foraneas, ['123']);
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error name_modelo no puede ser un numero',
     * //   'data' => '123',
     * //   'es_final' => true
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Recorre los modelos a procesar:**
     *    - Valida que `$name_modelo` no esté vacío.
     *    - Verifica que `$name_modelo` no sea un número.
     * 2. **Inicializa la estructura del modelo:**
     *    - Llama a `init_dato_estructura` para obtener los datos de los campos del modelo desde la base de datos.
     * 3. **Asigna los datos a la estructura:**
     *    - Llama a `asigna_datos_modelo` para integrar la información en `estructura_bd`.
     * 4. **Retorno del resultado:**
     *    - Devuelve `estructura_bd` con los datos de los modelos asignados.
     *    - Si ocurre un error, devuelve un array con detalles del problema.
     *
     * ### Casos de uso:
     * - **Contexto:** Creación de una estructura ORM a partir de los modelos de la base de datos.
     * - **Ejemplo real:** Obtener la estructura de los modelos `facturas`, `clientes` y `productos`.
     *
     * ### Consideraciones:
     * - Asegúrate de que `$modelos` contenga nombres de modelos válidos y no vacíos o numéricos.
     * - La función maneja errores mediante la clase `errores`, proporcionando mensajes detallados.
     */
    private function genera_estructura(array $keys_no_foraneas, array $modelos): array|stdClass
    {
        $estructura_bd = array();
        $modelo_base = new modelo_base($this->link);

        foreach ($modelos as $name_modelo) {
            $name_modelo = trim($name_modelo);

            if ($name_modelo === '') {
                return $this->error->error(mensaje: 'Error name_modelo esta vacio', data: $name_modelo, es_final: true);
            }
            if (is_numeric($name_modelo)) {
                return $this->error->error(mensaje: 'Error name_modelo no puede ser un numero', data: $name_modelo,
                    es_final: true);
            }

            $data_table = $this->init_dato_estructura(modelo_base: $modelo_base, name_modelo: $name_modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $data_table);
            }

            $estructura_bd = $this->asigna_datos_modelo(
                data_table: $data_table,
                keys_no_foraneas: $keys_no_foraneas,
                name_modelo: $name_modelo
            );

            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al maquetar estructura', data: $estructura_bd);
            }
        }

        return $estructura_bd;
    }


    /**
     * @param stdClass $data_campos
     * @param stdClass $estructura_bd
     * @param stdClass $foraneas
     * @param string $modelo
     * @return array|stdClass
     */
    private function genera_foranea(stdClass $data_campos, stdClass $estructura_bd, stdClass $foraneas,
                                    string $modelo): array|stdClass
    {
        $modelo = trim($modelo);
        if ($modelo === '') {
            return $this->error->error(mensaje: 'Error $modelo esta vacio', data: $modelo, es_final: true);
        }

        foreach ($data_campos as $data){
            if(!is_object($data)){
                return $this->error->error(mensaje: 'Error $data debe ser un objeto', data: $data_campos);
            }
            if(!isset($data->es_foranea)){
                $data->es_foranea = false;
            }
            if($data->es_foranea){

                if (!isset($data->tabla_foranea)) {
                    return $this->error->error(mensaje: 'Error $data->tabla_foranea no existe', data: $data,
                        es_final: true);
                }

                $estructura_bd = $this->asigna_dato_foranea(data: $data,estructura_bd: $estructura_bd,
                    foraneas: $foraneas,modelo: $modelo);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al maquetar foraneas', data: $estructura_bd);
                }
            }
        }
        return $estructura_bd;
    }

    /**
     * REG
     * Obtiene la lista de tablas de la base de datos mediante una consulta SQL.
     *
     * Esta función ejecuta un comando SQL para listar todas las tablas existentes en la base de datos
     * y devuelve el resultado en un array de registros. Si no se encuentran tablas, devuelve un error.
     *
     * @return array Devuelve un array con los registros de las tablas existentes en la base de datos.
     *               Cada registro corresponde a una tabla.
     *               En caso de error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $resultado = $this->get_tables_sql();
     *
     * // Resultado esperado (ejemplo):
     * // [
     * //   ['Tables_in_mi_base_datos' => 'usuarios'],
     * //   ['Tables_in_mi_base_datos' => 'productos'],
     * //   ['Tables_in_mi_base_datos' => 'pedidos'],
     * // ]
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: Error al generar la consulta SQL
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error al obtener sql',
     * //   'data' => [...]
     * // ]
     *
     * // Caso 2: No hay tablas en la base de datos
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error no existen entidades en la bd mi_base_datos',
     * //   'data' => 'SHOW TABLES',
     * //   'es_final' => true
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Generación de la consulta SQL:**
     *    - Usa `show_tables` de la clase `sql` para obtener el comando `SHOW TABLES`.
     * 2. **Ejecución de la consulta:**
     *    - Usa `ejecuta_consulta` de la clase `modelo_base` para ejecutar la consulta.
     * 3. **Validación de resultados:**
     *    - Si no se encuentran registros, lanza un error indicando que no hay tablas en la base de datos.
     * 4. **Retorno del resultado:**
     *    - Devuelve un array con los registros de las tablas encontradas.
     *
     * ### Casos de uso:
     * - **Contexto:** Obtener dinámicamente la lista de tablas de la base de datos para construir consultas o analizar la estructura.
     * - **Ejemplo real:** Listar todas las tablas disponibles en una base de datos llamada `mi_base_datos`.
     *
     * ### Consideraciones:
     * - Asegúrate de que la conexión a la base de datos esté configurada correctamente antes de llamar a esta función.
     * - La función utiliza la clase `errores` para manejar y devolver errores detallados.
     */
    private function get_tables_sql(): array
    {
        $sql = (new sql())->show_tables();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sql', data: $sql);
        }

        $result = (new modelo_base($this->link))->ejecuta_consulta(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }
        if ($result->n_registros === 0) {
            return $this->error->error(
                mensaje: 'Error no existen entidades en la bd ' . (new database())->db_name,
                data: $sql,
                es_final: true
            );
        }

        return $result->registros;
    }


    /**
     * REG
     * Inicializa un campo con sus propiedades estructurales dentro de la base de datos.
     *
     * Esta función valida la existencia de claves esenciales en el array del campo (`Field`, `Null`, `Key`),
     * y determina sus características principales como si permite valores nulos, si es clave primaria,
     * si es auto_increment, si es una clave foránea y la tabla foránea asociada.
     *
     * @param array $campo Datos del campo en la base de datos.
     *                     Debe contener al menos las claves `Field`, `Null` y `Key`.
     *                     - Ejemplo de estructura esperada:
     *                     ```php
     *                     [
     *                         'Field' => 'cliente_id',
     *                         'Type' => 'int(11)',
     *                         'Null' => 'YES',
     *                         'Key' => 'PRI'
     *                     ]
     *                     ```
     * @param array $keys_no_foraneas Lista de claves que no deben considerarse foráneas.
     *                                 - Ejemplo:
     *                                 ```php
     *                                 ['usuario_alta', 'usuario_update']
     *                                 ```
     *
     * @return array|stdClass Devuelve un objeto `stdClass` con las características del campo:
     *   - `permite_null` (bool): Indica si el campo permite valores nulos.
     *   - `es_primaria` (bool): Indica si el campo es clave primaria.
     *   - `es_auto_increment` (bool): Indica si el campo es auto_increment.
     *   - `es_foranea` (bool): Indica si el campo es clave foránea.
     *   - `tabla_foranea` (string): Nombre de la tabla foránea asociada si es una clave foránea, vacío si no lo es.
     *
     * Si algún dato no es válido, devuelve un array con información del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo = [
     *     'Field' => 'cliente_id',
     *     'Type' => 'int(11)',
     *     'Null' => 'YES',
     *     'Key' => '',
     *     'Extra' => ''
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->inicializa_campo($campo, $keys_no_foraneas);
     *
     * // Resultado esperado:
     * // (stdClass) {
     * //   permite_null => true,
     * //   es_primaria => false,
     * //   es_auto_increment => false,
     * //   es_foranea => true,
     * //   tabla_foranea => 'cliente'
     * // }
     * ```
     *
     * ### Ejemplo con clave primaria y auto_increment:
     * ```php
     * $campo = [
     *     'Field' => 'id',
     *     'Type' => 'int(11)',
     *     'Null' => 'NO',
     *     'Key' => 'PRI',
     *     'Extra' => 'auto_increment'
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->inicializa_campo($campo, $keys_no_foraneas);
     *
     * // Resultado esperado:
     * // (stdClass) {
     * //   permite_null => false,
     * //   es_primaria => true,
     * //   es_auto_increment => true,
     * //   es_foranea => false,
     * //   tabla_foranea => ''
     * // }
     * ```
     *
     * ### Ejemplo de error (clave `Field` no definida):
     * ```php
     * $campo = [
     *     'Null' => 'YES',
     *     'Key' => 'PRI'
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->inicializa_campo($campo, $keys_no_foraneas);
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error al campo[Field] no existe',
     * //   'data' => [
     * //       'Null' => 'YES',
     * //       'Key' => 'PRI'
     * //   ]
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación de la existencia de las claves necesarias (`Field`, `Null`, `Key`)**:
     *    - Si falta alguna, devuelve un error.
     * 2. **Determinación de características del campo**:
     *    - Llama a `permite_null` para determinar si el campo permite valores nulos.
     *    - Llama a `es_primaria` para verificar si el campo es clave primaria.
     *    - Llama a `es_auto_increment` para determinar si es auto_increment.
     *    - Llama a `es_foranea` para verificar si el campo es clave foránea.
     *    - Llama a `tabla_foranea` para obtener la tabla relacionada si el campo es clave foránea.
     * 3. **Retorno del resultado**:
     *    - Devuelve un objeto `stdClass` con las características del campo.
     *    - En caso de error, devuelve un array con detalles del problema.
     *
     * ### Casos de uso:
     * - **Contexto:** Validación de estructuras de base de datos en un ORM.
     * - **Ejemplo real:** Determinar las características de un campo `cliente_id` en una tabla de facturas.
     *
     * ### Consideraciones:
     * - Asegúrate de que el array `$campo` contiene las claves `Field`, `Null` y `Key` antes de llamar a esta función.
     * - La función maneja errores mediante la clase `errores`, asegurando que los errores sean informados correctamente.
     */
    private function inicializa_campo(array $campo, array $keys_no_foraneas): array|stdClass
    {
        if (!isset($campo['Null'])) {
            return $this->error->error(mensaje: 'Error campo[Null] debe existir', data: $campo);
        }
        if (!isset($campo['Key'])) {
            return $this->error->error(mensaje: 'Error campo[Key] debe existir', data: $campo);
        }
        if (!isset($campo['Field'])) {
            return $this->error->error(mensaje: 'Error al campo[Field] no existe', data: $campo);
        }

        $permite_null = $this->permite_null(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar permite null', data: $permite_null);
        }
        $es_primaria = $this->es_primaria(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $es_primaria', data: $es_primaria);
        }
        $es_auto_increment = $this->es_auto_increment(campo: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $es_auto_increment', data: $es_auto_increment);
        }
        $es_foranea = $this->es_foranea(campo: $campo, keys_no_foraneas: $keys_no_foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $es_foranea', data: $es_foranea);
        }
        $tabla_foranea = $this->tabla_foranea(campo: $campo, keys_no_foraneas: $keys_no_foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar $tabla_foranea', data: $tabla_foranea);
        }

        $data = new stdClass();
        $data->permite_null = $permite_null;
        $data->es_primaria = $es_primaria;
        $data->es_auto_increment = $es_auto_increment;
        $data->es_foranea = $es_foranea;
        $data->tabla_foranea = $tabla_foranea;

        return $data;
    }


    /**
     * REG
     * Inicializa la estructura de datos para un modelo y obtiene los campos de la base de datos.
     *
     * Esta función valida el nombre del modelo, recupera los campos de la tabla asociada a través del modelo base,
     * y establece una estructura inicial para el modelo en el atributo `estructura_bd`.
     *
     * @param modelo_base $modelo_base Instancia del modelo base que contiene la lógica para interactuar con la base de datos.
     * @param string $name_modelo Nombre del modelo que se utilizará para inicializar la estructura.
     *                            - No puede estar vacío.
     *                            - No puede ser un valor numérico.
     *                            - Ejemplo: 'usuarios'.
     *
     * @return array Devuelve un array con los datos de los campos de la tabla obtenidos de la base de datos.
     *               Si ocurre un error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $modelo_base = new modelo_base($link);
     * $name_modelo = 'usuarios';
     *
     * $resultado = $this->init_dato_estructura(modelo_base: $modelo_base, name_modelo: $name_modelo);
     *
     * // Resultado esperado:
     * // [
     * //   ['Field' => 'id', 'Type' => 'int(11)', ...],
     * //   ['Field' => 'nombre', 'Type' => 'varchar(255)', ...],
     * //   ...
     * // ]
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: $name_modelo vacío
     * $name_modelo = '';
     * $resultado = $this->init_dato_estructura(modelo_base: $modelo_base, name_modelo: $name_modelo);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error name_modelo esta vacio',
     * //   'data' => ''
     * // ]
     *
     * // Caso 2: $name_modelo es un número
     * $name_modelo = '123';
     * $resultado = $this->init_dato_estructura(modelo_base: $modelo_base, name_modelo: $name_modelo);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error name_modelo no puede ser un numero',
     * //   'data' => '123'
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación de `$name_modelo`:**
     *    - Verifica que no esté vacío.
     *    - Asegura que no sea un valor numérico.
     * 2. **Obtención de campos de la base de datos:**
     *    - Utiliza el método `columnas_bd_native` de la clase `columnas` para obtener los campos de la tabla asociada al modelo.
     * 3. **Inicialización de la estructura del modelo:**
     *    - Llama a la función `init_estructura_modelo` para crear la estructura inicial en `estructura_bd`.
     * 4. **Retorno del resultado:**
     *    - Devuelve un array con los campos de la base de datos si todo es exitoso.
     *    - En caso de error, devuelve un array con los detalles del error.
     *
     * ### Casos de uso:
     * - **Contexto:** Inicializar datos y estructura para un modelo en el sistema ORM.
     * - **Ejemplo real:** Para el modelo `usuarios`, obtener y establecer la estructura:
     *   ```php
     *   $resultado = $this->init_dato_estructura(modelo_base: $modelo_base, name_modelo: 'usuarios');
     *   // Resultado:
     *   // [
     *   //   ['Field' => 'id', 'Type' => 'int(11)', ...],
     *   //   ['Field' => 'nombre', 'Type' => 'varchar(255)', ...],
     *   //   ...
     *   // ]
     *   ```
     *
     * ### Consideraciones:
     * - Asegúrate de que el modelo base esté correctamente configurado para interactuar con la base de datos.
     * - Proporciona un nombre de modelo válido que coincida con una tabla en la base de datos.
     * - Maneja los errores de manera adecuada para obtener retroalimentación clara en caso de problemas.
     */
    private function init_dato_estructura(modelo_base $modelo_base, string $name_modelo): array
    {
        $name_modelo = trim($name_modelo);
        if ($name_modelo === '') {
            return $this->error->error(mensaje: 'Error name_modelo esta vacio', data: $name_modelo);
        }
        if (is_numeric($name_modelo)) {
            return $this->error->error(mensaje: 'Error name_modelo no puede ser un numero', data: $name_modelo);
        }

        $data_table = (new columnas())->columnas_bd_native(modelo: $modelo_base, tabla_bd: $name_modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener campos', data: $data_table);
        }

        $init = $this->init_estructura_modelo(name_modelo: $name_modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $init);
        }

        return $data_table;
    }



    /**
     * REG
     * Inicializa la estructura para un campo específico dentro de un modelo en la estructura general.
     *
     * Esta función valida que el modelo y el campo sean válidos, luego agrega una estructura inicial para el campo
     * en el modelo especificado dentro del atributo `estructura_bd`.
     *
     * @param array $campo Datos del campo a inicializar. Debe contener al menos la clave `Field`.
     *                     - Ejemplo: `['Field' => 'nombre_campo', ...]`.
     * @param string $name_modelo Nombre del modelo donde se inicializará la estructura del campo.
     *                            - No puede estar vacío.
     *                            - Ejemplo: 'usuarios'.
     *
     * @return stdClass|array Devuelve el objeto actualizado de la estructura general `estructura_bd`.
     *                        Si ocurre un error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo = ['Field' => 'nombre'];
     * $name_modelo = 'usuarios';
     *
     * $resultado = $this->init_estructura_campo(campo: $campo, name_modelo: $name_modelo);
     *
     * // Resultado esperado:
     * // $this->estructura_bd = (object)[
     * //   'usuarios' => (object)[
     * //     'data_campos' => (object)[
     * //       'nombre' => (object)[]
     * //     ]
     * //   ]
     * // ];
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: $name_modelo vacío
     * $campo = ['Field' => 'nombre'];
     * $name_modelo = '';
     *
     * $resultado = $this->init_estructura_campo(campo: $campo, name_modelo: $name_modelo);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $name_modelo esta vacio',
     * //   'data' => ''
     * // ]
     *
     * // Caso 2: Falta la clave 'Field' en $campo
     * $campo = [];
     * $name_modelo = 'usuarios';
     *
     * $resultado = $this->init_estructura_campo(campo: $campo, name_modelo: $name_modelo);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error al validar valida',
     * //   'data' => [...]
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación de `$name_modelo`:**
     *    - Verifica que no esté vacío.
     * 2. **Validación del campo:**
     *    - Comprueba que el array `$campo` contenga la clave `Field`.
     * 3. **Inicialización de la estructura:**
     *    - Si el modelo no existe en `estructura_bd`, se crea.
     *    - Si `data_campos` no existe en el modelo, se inicializa como un objeto vacío.
     *    - Se agrega el campo especificado a `data_campos` como un objeto vacío.
     * 4. **Retorno del resultado:**
     *    - Devuelve el objeto actualizado `estructura_bd` si todo es exitoso.
     *    - En caso de error, devuelve un array con detalles del problema.
     *
     * ### Casos de uso:
     * - **Contexto:** Gestión dinámica de la estructura de modelos y sus campos dentro de una arquitectura ORM.
     * - **Ejemplo real:** Para el modelo `usuarios` y el campo `nombre`, inicializar la estructura:
     *   ```php
     *   $campo = ['Field' => 'nombre'];
     *   $name_modelo = 'usuarios';
     *   $resultado = $this->init_estructura_campo(campo: $campo, name_modelo: $name_modelo);
     *   // Resultado esperado:
     *   // $this->estructura_bd = (object)[
     *   //   'usuarios' => (object)[
     *   //     'data_campos' => (object)[
     *   //       'nombre' => (object)[]
     *   //     ]
     *   //   ]
     *   // ];
     *   ```
     *
     * ### Consideraciones:
     * - Asegúrate de que `$campo` sea un array válido con la clave `Field`.
     * - El nombre del modelo `$name_modelo` debe ser una cadena no vacía.
     * - La función maneja errores mediante la clase `errores`, proporcionando mensajes claros y detallados.
     */
    private function init_estructura_campo(array $campo, string $name_modelo): stdClass|array
    {
        $name_modelo = trim($name_modelo);
        if ($name_modelo === '') {
            return $this->error->error(mensaje: 'Error $name_modelo esta vacio', data: $name_modelo);
        }

        $keys = array('Field');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $campo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar keys', data: $valida);
        }

        if (!isset($this->estructura_bd->$name_modelo)) {
            $this->estructura_bd->$name_modelo = new stdClass();
        }

        if (!isset($this->estructura_bd->$name_modelo->data_campos)) {
            $this->estructura_bd->$name_modelo->data_campos = new stdClass();
        }

        $campo_name = $campo['Field'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name = new stdClass();

        return $this->estructura_bd;
    }


    /**
     * REG
     * Inicializa la estructura de un modelo en la base de datos.
     *
     * Esta función crea una estructura base en el atributo `$this->estructura_bd` para el modelo especificado.
     * La estructura incluye un objeto con las propiedades `campos` (un arreglo vacío) y `data_campos` (un objeto vacío).
     *
     * @param string $name_modelo Nombre del modelo para inicializar su estructura.
     *                            - No puede estar vacío.
     *                            - Ejemplo: 'usuario'.
     *
     * @return stdClass|array Devuelve el objeto `$this->estructura_bd` actualizado con la estructura del modelo.
     *                        Si ocurre un error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $name_modelo = 'usuario';
     *
     * $resultado = $this->init_estructura_modelo(name_modelo: $name_modelo);
     *
     * // Resultado esperado:
     * // $this->estructura_bd = {
     * //   'usuario' => {
     * //     'campos' => [],
     * //     'data_campos' => {}
     * //   }
     * // }
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: $name_modelo vacío
     * $name_modelo = '';
     *
     * $resultado = $this->init_estructura_modelo(name_modelo: $name_modelo);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $name_modelo esta vacio',
     * //   'data' => ''
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación del parámetro `$name_modelo`:**
     *    - Verifica que `$name_modelo` no esté vacío.
     * 2. **Inicialización de la estructura:**
     *    - Crea un nuevo objeto en `$this->estructura_bd` para el modelo.
     *    - Inicializa las propiedades:
     *        - `campos`: Un arreglo vacío.
     *        - `data_campos`: Un objeto vacío (`stdClass`).
     * 3. **Retorno del resultado:**
     *    - Devuelve `$this->estructura_bd` actualizado si no hay errores.
     *    - Si ocurre un error, devuelve un array con los detalles del error.
     *
     * ### Casos de uso:
     * - **Contexto:** Definir una estructura base para almacenar información sobre los modelos de la base de datos.
     * - **Ejemplo real:** Inicializar la estructura de un modelo llamado `usuario`:
     *   ```php
     *   $resultado = $this->init_estructura_modelo(name_modelo: 'usuario');
     *   // Resultado:
     *   // $this->estructura_bd = {
     *   //   'usuario' => {
     *   //     'campos' => [],
     *   //     'data_campos' => {}
     *   //   }
     *   // }
     *   ```
     *
     * ### Consideraciones:
     * - Asegúrate de proporcionar un nombre de modelo válido y no vacío.
     * - La función maneja errores mediante la clase `errores`, proporcionando mensajes claros.
     */
    private function init_estructura_modelo(string $name_modelo): stdClass|array
    {
        $name_modelo = trim($name_modelo);
        if ($name_modelo === '') {
            return $this->error->error(mensaje: 'Error $name_modelo esta vacio', data: $name_modelo);
        }
        $this->estructura_bd->$name_modelo = new stdClass();
        $this->estructura_bd->$name_modelo->campos = array();
        $this->estructura_bd->$name_modelo->data_campos = new stdClass();
        return $this->estructura_bd;
    }


    /**
     * REG
     * Estructura y asigna los datos de un campo dentro de la estructura del modelo en la base de datos.
     *
     * Esta función toma la información de un campo de la base de datos y lo integra dentro de la estructura del modelo,
     * asegurando que contenga todas las propiedades esenciales, tales como si es clave primaria, foránea, permite nulos,
     * su tipo de dato, valores por defecto, entre otros.
     *
     * @param array $campo Datos del campo en la base de datos.
     *                     Debe contener las claves `Field`, `Type`, `Default`, `Extra` y `Key`.
     *                     - Ejemplo de estructura esperada:
     *                     ```php
     *                     [
     *                         'Field' => 'cliente_id',
     *                         'Type' => 'int(11)',
     *                         'Null' => 'YES',
     *                         'Default' => null,
     *                         'Extra' => '',
     *                         'Key' => 'MUL'
     *                     ]
     *                     ```
     * @param stdClass $campo_init Estructura inicializada del campo, que contiene sus propiedades analizadas.
     *                              - Debe contener:
     *                                - `tabla_foranea` (string)
     *                                - `es_foranea` (bool)
     *                                - `permite_null` (bool)
     *                                - `es_primaria` (bool)
     *                                - `es_auto_increment` (bool)
     *                              - Ejemplo:
     *                              ```php
     *                              (object) [
     *                                  'tabla_foranea' => 'clientes',
     *                                  'es_foranea' => true,
     *                                  'permite_null' => true,
     *                                  'es_primaria' => false,
     *                                  'es_auto_increment' => false
     *                              ]
     *                              ```
     * @param string $name_modelo Nombre del modelo al cual pertenece el campo.
     *                            - No puede estar vacío.
     *                            - Ejemplo: `'facturas'`.
     *
     * @return stdClass|array Devuelve el objeto `estructura_bd` con la información del campo asignada.
     *                        En caso de error, devuelve un array con detalles del problema.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo = [
     *     'Field' => 'cliente_id',
     *     'Type' => 'int(11)',
     *     'Null' => 'YES',
     *     'Default' => null,
     *     'Extra' => '',
     *     'Key' => 'MUL'
     * ];
     *
     * $campo_init = (object) [
     *     'tabla_foranea' => 'clientes',
     *     'es_foranea' => true,
     *     'permite_null' => true,
     *     'es_primaria' => false,
     *     'es_auto_increment' => false
     * ];
     *
     * $name_modelo = 'facturas';
     *
     * $resultado = $this->maqueta_estructura($campo, $campo_init, $name_modelo);
     * // Resultado esperado:
     * // $this->estructura_bd->facturas->data_campos->cliente_id = {
     * //   'tabla_foranea' => 'clientes',
     * //   'es_foranea' => true,
     * //   'permite_null' => true,
     * //   'campo_name' => 'cliente_id',
     * //   'tipo_dato' => 'int(11)',
     * //   'es_primaria' => false,
     * //   'valor_default' => null,
     * //   'extra' => '',
     * //   'es_auto_increment' => false,
     * //   'tipo_llave' => 'MUL'
     * // }
     * ```
     *
     * ### Ejemplo de error (`name_modelo` vacío):
     * ```php
     * $resultado = $this->maqueta_estructura($campo, $campo_init, '');
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error $name_modelo esta vacio',
     * //   'data' => '',
     * //   'es_final' => true
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validaciones iniciales:**
     *    - Verifica que `$name_modelo` no esté vacío.
     *    - Verifica que `$campo` contenga las claves `Field`, `Type`, `Default`, `Extra` y `Key`.
     *    - Verifica que `$campo_init` contenga `tabla_foranea`, `es_foranea`, `permite_null`, `es_primaria`, `es_auto_increment`.
     * 2. **Inicialización de la estructura en `$this->estructura_bd`:**
     *    - Crea las claves necesarias en `estructura_bd` si no existen (`$name_modelo`, `data_campos`).
     * 3. **Registro de la información del campo:**
     *    - Se almacena en `data_campos->$campo_name` la información del campo, incluyendo:
     *      - `tabla_foranea`, `es_foranea`, `permite_null`, `tipo_dato`, `es_primaria`, `valor_default`, `extra`, `es_auto_increment`, `tipo_llave`.
     * 4. **Retorno del resultado:**
     *    - Devuelve `estructura_bd` con la información agregada.
     *    - Si ocurre un error, devuelve un array con detalles del problema.
     *
     * ### Casos de uso:
     * - **Contexto:** Definición de la estructura de modelos en una aplicación ORM.
     * - **Ejemplo real:** Registrar un campo `cliente_id` en la estructura de la tabla `facturas`.
     *
     * ### Consideraciones:
     * - Asegúrate de que `$campo` y `$campo_init` contengan la información esperada antes de llamar a esta función.
     * - La función maneja errores mediante la clase `errores`, proporcionando mensajes detallados.
     */
    private function maqueta_estructura(array $campo, stdClass $campo_init, string $name_modelo): stdClass|array
    {
        $name_modelo = trim($name_modelo);
        if ($name_modelo === '') {
            return $this->error->error(mensaje: 'Error $name_modelo esta vacio', data: $name_modelo,
                es_final: true);
        }

        $required_keys = ['Field', 'Type', 'Default', 'Extra', 'Key'];
        foreach ($required_keys as $key) {
            if (!isset($campo[$key])) {
                return $this->error->error(mensaje: "Error \$campo[$key] no existe", data: $campo,
                    es_final: true);
            }
        }

        $required_init_keys = ['tabla_foranea', 'es_foranea', 'permite_null', 'es_primaria', 'es_auto_increment'];
        foreach ($required_init_keys as $key) {
            if (!isset($campo_init->$key)) {
                return $this->error->error(mensaje: "Error \$campo_init->$key no existe", data: $campo_init,
                    es_final: true);
            }
        }

        if (!isset($this->estructura_bd->$name_modelo)) {
            $this->estructura_bd->$name_modelo = new stdClass();
        }
        if (!isset($this->estructura_bd->$name_modelo->data_campos)) {
            $this->estructura_bd->$name_modelo->data_campos = new stdClass();
        }

        $campo_name = trim($campo['Field']);
        if ($campo_name === '') {
            return $this->error->error(mensaje: 'Error $campo_name esta vacio', data: $campo_init, es_final: true);
        }

        if (!isset($this->estructura_bd->$name_modelo->data_campos->$campo_name)) {
            $this->estructura_bd->$name_modelo->data_campos->$campo_name = new stdClass();
        }

        $this->estructura_bd->$name_modelo->campos[] = $campo_name;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->tabla_foranea = $campo_init->tabla_foranea;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->es_foranea = $campo_init->es_foranea;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->permite_null = $campo_init->permite_null;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->campo_name = $campo_name;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->tipo_dato = $campo['Type'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->es_primaria = $campo_init->es_primaria;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->valor_default = $campo['Default'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->extra = $campo['Extra'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->es_auto_increment = $campo_init->es_auto_increment;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->tipo_llave = $campo['Key'];

        return $this->estructura_bd;
    }


    /**
     * REG
     * Obtiene una lista de modelos a partir de las tablas existentes en una base de datos.
     *
     * Esta función toma el nombre de una base de datos, consulta las tablas existentes y genera
     * un arreglo de modelos representando los nombres de las tablas en la base de datos.
     *
     * @param string $name_db Nombre de la base de datos.
     *                        - No puede estar vacío.
     *                        - Ejemplo: 'mi_base_datos'.
     *
     * @return array|stdClass Devuelve un arreglo con los nombres de las tablas de la base de datos.
     *                        Si ocurre un error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $name_db = 'mi_base_datos';
     *
     * $resultado = $this->modelos(name_db: $name_db);
     *
     * // Resultado esperado:
     * // [
     * //   'tabla_1',
     * //   'tabla_2',
     * //   'tabla_3'
     * // ]
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: $name_db vacío
     * $name_db = '';
     *
     * $resultado = $this->modelos(name_db: $name_db);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error name db esta vacio',
     * //   'data' => ''
     * // ]
     *
     * // Caso 2: Error al obtener las tablas de la base de datos
     * $name_db = 'mi_base_datos';
     *
     * // Supongamos que la consulta SQL falla.
     * $resultado = $this->modelos(name_db: $name_db);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error al ejecutar sql',
     * //   'data' => [...]
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación de `$name_db`:**
     *    - Verifica que no esté vacío.
     * 2. **Obtención de tablas:**
     *    - Usa `get_tables_sql` para obtener las tablas de la base de datos.
     *    - Si ocurre un error en la consulta, devuelve los detalles del error.
     * 3. **Generación de modelos:**
     *    - Llama a `maqueta_modelos` para transformar las tablas en un arreglo de modelos.
     *    - Si ocurre un error durante la transformación, devuelve los detalles del error.
     * 4. **Retorno del resultado:**
     *    - Devuelve el arreglo de modelos generado.
     *
     * ### Casos de uso:
     * - **Contexto:** Listar dinámicamente las tablas existentes en una base de datos para su posterior uso.
     * - **Ejemplo real:** Obtener modelos basados en las tablas de la base de datos `mi_base_datos`:
     *   ```php
     *   $resultado = $this->modelos(name_db: 'mi_base_datos');
     *   // Resultado:
     *   // ['tabla_1', 'tabla_2', 'tabla_3']
     *   ```
     *
     * ### Consideraciones:
     * - Asegúrate de que `$name_db` sea el nombre de una base de datos válida y que exista en el servidor.
     * - La función maneja errores mediante la clase `errores`, proporcionando información clara sobre cualquier fallo.
     */
    final public function modelos(string $name_db): array|stdClass
    {
        $name_db = trim($name_db);
        if ($name_db === '') {
            return $this->error->error(mensaje: 'Error name db esta vacio', data: $name_db);
        }

        $rows = $this->get_tables_sql();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $rows);
        }

        $modelos = $this->maqueta_modelos(name_db: $name_db, rows: $rows);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al maquetar modelos', data: $modelos);
        }

        return $modelos;
    }


    /**
     * REG
     * Genera el nombre de la clave utilizada para identificar tablas en una base de datos específica.
     *
     * Esta función construye el prefijo estándar `Tables_in_` seguido del nombre de la base de datos proporcionado,
     * que es comúnmente utilizado en los resultados de la consulta `SHOW TABLES`.
     *
     * @param string $name_db Nombre de la base de datos.
     *                        - No puede estar vacío.
     *
     * @return string|array Devuelve una cadena con el prefijo concatenado al nombre de la base de datos.
     *                      En caso de error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $name_db = 'mi_base_datos';
     * $resultado = $this->key_table(name_db: $name_db);
     *
     * // Resultado esperado:
     * // "Tables_in_mi_base_datos"
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: Nombre de base de datos vacío
     * $name_db = '';
     * $resultado = $this->key_table(name_db: $name_db);
     *
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error name db esta vacio',
     * //   'data' => ''
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación del parámetro:**
     *    - Se asegura de que `$name_db` no esté vacío.
     * 2. **Construcción del resultado:**
     *    - Agrega el prefijo `Tables_in_` al nombre de la base de datos proporcionado.
     * 3. **Retorno del resultado:**
     *    - Si no hay errores, devuelve la cadena construida.
     *    - Si ocurre un error, devuelve un array con los detalles del error.
     *
     * ### Casos de uso:
     * - **Contexto:** Esta función es útil para interpretar correctamente los resultados de consultas como `SHOW TABLES`
     *   en las que las claves de las tablas contienen el prefijo `Tables_in_` seguido del nombre de la base de datos.
     * - **Ejemplo real:** Construir la clave que identifica tablas en la base de datos `mi_base_datos`:
     *   ```php
     *   $key = $this->key_table(name_db: 'mi_base_datos');
     *   // Resultado: "Tables_in_mi_base_datos"
     *   ```
     *
     * ### Consideraciones:
     * - Asegúrate de proporcionar un nombre de base de datos válido, ya que la función no maneja nombres incorrectos
     *   más allá de verificar si están vacíos.
     * - La función maneja errores utilizando la clase `errores` para proporcionar retroalimentación clara.
     */
    private function key_table(string $name_db): string|array
    {
        $name_db = trim($name_db);
        if ($name_db === '') {
            return $this->error->error(mensaje: 'Error name db esta vacio', data: $name_db);
        }

        $pref = 'Tables_in_';
        return $pref . $name_db;
    }


    /**
     * REG
     * Crea una lista de modelos a partir de los resultados de una consulta de tablas en una base de datos.
     *
     * Esta función toma el nombre de una base de datos y un arreglo de filas obtenidas de una consulta `SHOW TABLES`,
     * y genera un arreglo con los nombres de las tablas como modelos. Valida que los parámetros sean válidos
     * y utiliza la función `asigna_data_modelo` para asignar cada tabla al arreglo de modelos.
     *
     * @param string $name_db Nombre de la base de datos.
     *                        - No puede estar vacío.
     *                        - Ejemplo: 'mi_base_datos'.
     * @param array $rows Arreglo de filas obtenidas de la consulta `SHOW TABLES`.
     *                    - Cada fila debe contener una clave en el formato `Tables_in_{name_db}`.
     *                    - Ejemplo:
     *                      ```php
     *                      [
     *                          ['Tables_in_mi_base_datos' => 'tabla_1'],
     *                          ['Tables_in_mi_base_datos' => 'tabla_2']
     *                      ]
     *                      ```
     *
     * @return array Devuelve un arreglo con los nombres de las tablas de la base de datos.
     *               En caso de error, devuelve un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $name_db = 'mi_base_datos';
     * $rows = [
     *     ['Tables_in_mi_base_datos' => 'tabla_1'],
     *     ['Tables_in_mi_base_datos' => 'tabla_2']
     * ];
     *
     * $resultado = $this->maqueta_modelos(name_db: $name_db, rows: $rows);
     *
     * // Resultado esperado:
     * // ['tabla_1', 'tabla_2']
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: $name_db vacío
     * $name_db = '';
     * $rows = [
     *     ['Tables_in_mi_base_datos' => 'tabla_1'],
     *     ['Tables_in_mi_base_datos' => 'tabla_2']
     * ];
     *
     * $resultado = $this->maqueta_modelos(name_db: $name_db, rows: $rows);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error name db esta vacio',
     * //   'data' => ''
     * // ]
     *
     * // Caso 2: Clave ausente en $rows
     * $name_db = 'mi_base_datos';
     * $rows = [
     *     ['Tables_in_mi_base_datos_erroneo' => 'tabla_1']
     * ];
     *
     * $resultado = $this->maqueta_modelos(name_db: $name_db, rows: $rows);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error no existe $row[$key]',
     * //   'data' => 'Tables_in_mi_base_datos'
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación de `$name_db`:**
     *    - Verifica que no esté vacío.
     * 2. **Inicialización del arreglo `$modelos`:**
     *    - Comienza con un arreglo vacío.
     * 3. **Recorrido de `$rows`:**
     *    - Para cada fila en `$rows`, llama a `asigna_data_modelo` para agregar el nombre de la tabla a `$modelos`.
     *    - Si ocurre un error, devuelve los detalles del error.
     * 4. **Retorno del resultado:**
     *    - Devuelve el arreglo de modelos con los nombres de las tablas.
     *
     * ### Casos de uso:
     * - **Contexto:** Creación dinámica de modelos a partir de los resultados de una consulta `SHOW TABLES`.
     * - **Ejemplo real:** Transformar los resultados de `SHOW TABLES` en un arreglo de nombres de tablas.
     *
     * ### Consideraciones:
     * - Asegúrate de que `$name_db` sea válido y `$rows` tenga las claves esperadas antes de llamar a esta función.
     * - La función maneja errores utilizando la clase `errores` para proporcionar retroalimentación detallada.
     */
    private function maqueta_modelos(string $name_db, array $rows): array
    {
        $name_db = trim($name_db);
        if ($name_db === '') {
            return $this->error->error(mensaje: 'Error name db esta vacio', data: $name_db);
        }

        $modelos = array();
        foreach ($rows as $row) {
            $modelos = $this->asigna_data_modelo(modelos: $modelos, name_db: $name_db, row: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar modelo', data: $modelos);
            }
        }
        return $modelos;
    }


    /**
     * REG
     * Determina si un campo permite valores nulos en la base de datos.
     *
     * Esta función verifica la clave `Null` en el array de datos de un campo y determina si el campo
     * puede aceptar valores nulos. Si el campo tiene el valor `'NO'` en `Null`, se considera que no permite nulos.
     *
     * @param array $campo Datos del campo en la base de datos.
     *                     Debe contener la clave `Null` con los valores esperados `'YES'` o `'NO'`.
     *                     - Ejemplo de estructura esperada:
     *                     ```php
     *                     [
     *                         'Field' => 'nombre',
     *                         'Type' => 'varchar(255)',
     *                         'Null' => 'YES'
     *                     ]
     *                     ```
     *
     * @return bool|array Retorna `true` si el campo permite nulos, `false` si no los permite,
     *                    o un array de error si la clave `Null` no está definida en `$campo`.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo = [
     *     'Field' => 'nombre',
     *     'Type' => 'varchar(255)',
     *     'Null' => 'YES'
     * ];
     *
     * $resultado = $this->permite_null($campo);
     * // Resultado esperado:
     * // true
     * ```
     *
     * ```php
     * $campo = [
     *     'Field' => 'id',
     *     'Type' => 'int(11)',
     *     'Null' => 'NO'
     * ];
     *
     * $resultado = $this->permite_null($campo);
     * // Resultado esperado:
     * // false
     * ```
     *
     * ### Ejemplo de error (clave `Null` no definida):
     * ```php
     * $campo = [
     *     'Field' => 'nombre',
     *     'Type' => 'varchar(255)'
     * ];
     *
     * $resultado = $this->permite_null($campo);
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error campo[Null] debe existir',
     * //   'data' => [
     * //       'Field' => 'nombre',
     * //       'Type' => 'varchar(255)'
     * //   ]
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Verificación de la existencia de la clave `Null` en `$campo`:**
     *    - Si la clave no está presente, se retorna un error.
     * 2. **Determinación del valor de `permite_null`:**
     *    - Si `Null` es `'NO'`, significa que el campo no permite nulos, por lo que retorna `false`.
     *    - En cualquier otro caso (por ejemplo, `'YES'`), se considera que permite nulos y retorna `true`.
     * 3. **Retorno del resultado:**
     *    - Devuelve `true` si permite nulos.
     *    - Devuelve `false` si no los permite.
     *    - Devuelve un array de error si la clave `Null` no está definida en `$campo`.
     *
     * ### Casos de uso:
     * - **Contexto:** Validación de estructuras de base de datos en un ORM.
     * - **Ejemplo real:** Determinar si un campo `nombre` en una tabla de usuarios permite valores nulos.
     *
     * ### Consideraciones:
     * - Asegúrate de que el array `$campo` contiene la clave `Null` antes de llamar a esta función.
     * - En bases de datos, algunos motores como MySQL pueden definir valores por defecto para `Null`, verifica siempre su estructura.
     */
    private function permite_null(array $campo): bool|array
    {
        if (!isset($campo['Null'])) {
            return $this->error->error(mensaje: 'Error campo[Null] debe existir', data: $campo);
        }

        $permite_null = true;
        if ($campo['Null'] === 'NO') {
            $permite_null = false;
        }

        return $permite_null;
    }


    /**
     * REG
     * Obtiene el nombre de la tabla foránea asociada a un campo de base de datos.
     *
     * Esta función analiza el nombre del campo para determinar si representa una clave foránea.
     * Se considera una clave foránea si su nombre termina en `_id` y no está presente en la lista de claves excluidas (`keys_no_foraneas`).
     * Si el campo es una clave foránea, la función devuelve el nombre de la tabla relacionada.
     *
     * @param array $campo Datos del campo en la base de datos.
     *                     Debe contener la clave `Field`, que representa el nombre del campo en la tabla.
     *                     - Ejemplo de estructura esperada:
     *                     ```php
     *                     [
     *                         'Field' => 'cliente_id',
     *                         'Type' => 'int(11)'
     *                     ]
     *                     ```
     * @param array $keys_no_foraneas Lista de claves que no deben considerarse foráneas.
     *                                 - Ejemplo:
     *                                 ```php
     *                                 ['usuario_alta', 'usuario_update']
     *                                 ```
     *
     * @return string|array Devuelve el nombre de la tabla foránea si el campo es una clave foránea,
     *                      una cadena vacía `''` si no es una clave foránea,
     *                      o un array de error si la clave `Field` no está definida en `$campo`.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo = [
     *     'Field' => 'cliente_id',
     *     'Type' => 'int(11)'
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->tabla_foranea($campo, $keys_no_foraneas);
     * // Resultado esperado:
     * // "cliente"
     * ```
     *
     * ```php
     * $campo = [
     *     'Field' => 'nombre',
     *     'Type' => 'varchar(255)'
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->tabla_foranea($campo, $keys_no_foraneas);
     * // Resultado esperado:
     * // ""
     * ```
     *
     * ```php
     * $campo = [
     *     'Field' => 'usuario_update',
     *     'Type' => 'int(11)'
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->tabla_foranea($campo, $keys_no_foraneas);
     * // Resultado esperado:
     * // "" (ya que está en keys_no_foraneas)
     * ```
     *
     * ### Ejemplo de error (clave `Field` no definida):
     * ```php
     * $campo = [
     *     'Type' => 'int(11)'
     * ];
     * $keys_no_foraneas = ['usuario_alta', 'usuario_update'];
     *
     * $resultado = $this->tabla_foranea($campo, $keys_no_foraneas);
     * // Resultado esperado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error al campo[Field] no existe',
     * //   'data' => [
     * //       'Type' => 'int(11)'
     * //   ]
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Verificación de la existencia de la clave `Field` en `$campo`:**
     *    - Si `Field` no está presente, se retorna un error.
     * 2. **Análisis del nombre del campo:**
     *    - Se divide el nombre en partes separadas por `_id` utilizando `explode('_id', $campo['Field'])`.
     * 3. **Verificación de si es una clave foránea:**
     *    - Si el nombre tiene `_id` al final y no está en la lista `keys_no_foraneas`, se considera foránea (`true`).
     *    - Si está en `keys_no_foraneas`, se devuelve una cadena vacía `''`.
     * 4. **Retorno del resultado:**
     *    - Devuelve el nombre de la tabla foránea si el campo es una clave foránea.
     *    - Devuelve `''` si no es una clave foránea.
     *    - Devuelve un array de error si la clave `Field` no está definida en `$campo`.
     *
     * ### Casos de uso:
     * - **Contexto:** Validación de estructuras de base de datos en un ORM.
     * - **Ejemplo real:** Determinar la tabla relacionada para un campo `cliente_id` en una tabla de facturas.
     *
     * ### Consideraciones:
     * - Asegúrate de que el array `$campo` contiene la clave `Field` antes de llamar a esta función.
     * - La función maneja errores mediante la clase `errores`, asegurando que los errores sean informados correctamente.
     */
    private function tabla_foranea(array $campo, array $keys_no_foraneas): string|array
    {
        if (!isset($campo['Field'])) {
            return $this->error->error(mensaje: 'Error al campo[Field] no existe', data: $campo);
        }

        $tabla_foranea = '';
        $explode_campo = explode('_id', $campo['Field']);
        if ((count($explode_campo) > 1) && $explode_campo[1] === '') {
            $es_no_foranea = in_array($explode_campo[0], $keys_no_foraneas, true);
            if (!$es_no_foranea) {
                $tabla_foranea = $explode_campo[0];
            }
        }

        return $tabla_foranea;
    }


}

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
     * @param array $campo
     * @param array $keys_no_foraneas
     * @param string $name_modelo
     * @return array|stdClass
     */
    private function asigna_dato_estructura(array $campo, array $keys_no_foraneas, string $name_modelo): array|stdClass
    {
        $init = $this->init_estructura_campo(campo: $campo,name_modelo: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $init);
        }

        $campo_init = $this->inicializa_campo(campo: $campo, keys_no_foraneas: $keys_no_foraneas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campo', data: $campo_init);
        }

        $estructura_bd = $this->maqueta_estructura(campo: $campo,campo_init: $campo_init,name_modelo: $name_modelo);
        if(errores::$error){
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
     * @param stdClass $data
     * @param stdClass $estructura_bd
     * @param stdClass $foraneas
     * @param string $modelo
     * @return stdClass
     */
    private function asigna_dato_foranea(stdClass $data, stdClass $estructura_bd, stdClass $foraneas,
                                         string $modelo): stdClass
    {
        $tabla_foranea = $data->tabla_foranea;
        $foraneas->$tabla_foranea = new stdClass();
        $estructura_bd->$modelo->tiene_foraneas = true;
        return $estructura_bd;
    }

    /**
     * @param array $data_table
     * @param array $keys_no_foraneas
     * @param string $name_modelo
     * @return array|stdClass
     */
    private function asigna_datos_modelo(array $data_table, array $keys_no_foraneas, string $name_modelo): array|stdClass
    {
        $estructura_bd = array();
        foreach ($data_table as $campo){

            $estructura_bd = $this->asigna_dato_estructura(campo: $campo, keys_no_foraneas: $keys_no_foraneas,
                name_modelo: $name_modelo);
            if(errores::$error){
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
     * Integra si el campo es autoincrement o no
     * @param array $campo Campo a validar
     * @return bool|array
     * @version 11.1.0
     */
    private function es_auto_increment(array $campo): bool|array
    {
        if(!isset($campo['Extra'])){
            $campo['Extra'] = '';
        }
        $es_auto_increment = false;
        if($campo['Extra'] === 'auto_increment'){
            $es_auto_increment = true;
        }
        return $es_auto_increment;
    }

    /**
     * Verifica si el campo es una llave foranea o no
     * @param array $campo Campo a validar
     * @param array $keys_no_foraneas Keys previos con foraneas
     * @return bool|array
     * @version 11.23.0
     */
    private function es_foranea(array $campo, array $keys_no_foraneas): bool|array
    {
        if(!isset($campo['Field'])){
            return $this->error->error(mensaje: 'Error al campo[Field] no existe', data: $campo);
        }
        $es_foranea = false;
        $explode_campo = explode('_id', $campo['Field']);

        if((count($explode_campo) > 1) && $explode_campo[1] === '') {
            $es_no_foranea = in_array($explode_campo[0], $keys_no_foraneas, true);
            if(!$es_no_foranea){
                $es_foranea = true;
            }

        }
        return $es_foranea;
    }

    /**
     * Asigna verdadero si el campo es una llave primaria
     * @param array $campo Campo a validar
     * @return bool|array
     * @version 10.97.4
     */
    private function es_primaria(array $campo): bool|array
    {
        if(!isset($campo['Key'])){
            return $this->error->error(mensaje: 'Error campo[Key] debe existir', data: $campo);
        }
        $es_primaria = false;
        if($campo['Key'] === 'PRI'){
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
     * @param array $keys_no_foraneas
     * @param array $modelos
     * @return array|stdClass
     */
    private function genera_estructura(array $keys_no_foraneas, array $modelos): array|stdClass
    {
        $estructura_bd = array();
        $modelo_base = new modelo_base($this->link);
        foreach ($modelos as $name_modelo){

            $data_table = $this->init_dato_estructura(modelo_base: $modelo_base,name_modelo: $name_modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializa_estructura', data: $data_table);
            }
            $estructura_bd = $this->asigna_datos_modelo(data_table: $data_table, keys_no_foraneas: $keys_no_foraneas,
                name_modelo: $name_modelo);
            if(errores::$error){
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
        foreach ($data_campos as $data){
            if($data->es_foranea){
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
     * Inicializa un campo con loa datos de estructura de bd
     * @param array $campo campo a inicializar
     * @param array $keys_no_foraneas Keys integrados como no foraneas
     * @return array|stdClass
     */
    private function inicializa_campo(array $campo, array $keys_no_foraneas): array|stdClass
    {
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
            return $this->error->error(mensaje: 'Error al validar valida', data: $valida);
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
     * @param array $campo
     * @param stdClass $campo_init
     * @param string $name_modelo
     * @return stdClass
     */
    private function maqueta_estructura(array $campo, stdClass $campo_init, string $name_modelo): stdClass
    {
        $campo_name = $campo['Field'];

        $this->estructura_bd->$name_modelo->campos[] = $campo['Field'];
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->tabla_foranea =  $campo_init->tabla_foranea;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->es_foranea = $campo_init->es_foranea;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->permite_null = $campo_init->permite_null;
        $this->estructura_bd->$name_modelo->data_campos->$campo_name->campo_name = $campo['Field'];
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
     * Integra permite null
     * @param array $campo Datos del campo
     * @return bool|array
     * @version 10.78.3
     */
    private function permite_null(array $campo): bool|array
    {
        if(!isset($campo['Null'])){
            return $this->error->error(mensaje: 'Error campo[Null] debe existir', data: $campo);
        }

        $permite_null = true;
        if($campo['Null'] === 'NO'){
            $permite_null = false;
        }
        return $permite_null;
    }

    /**
     * Integra el nombre de la tabla foranea ligada a la entidad
     * @param array $campo Datos del campo
     * @param array $keys_no_foraneas Key de foraneas
     * @return string|array
     * @version 11.25.0
     */
    private function tabla_foranea(array $campo, array $keys_no_foraneas): string|array
    {
        if(!isset($campo['Field'])){
            return $this->error->error(mensaje: 'Error al campo[Field] no existe', data: $campo);
        }

        $tabla_foranea = '';
        $explode_campo = explode('_id', $campo['Field']);
        if((count($explode_campo) > 1) && $explode_campo[1] === '') {
            $es_no_foranea = in_array($explode_campo[0], $keys_no_foraneas, true);
            if(!$es_no_foranea){
                $tabla_foranea = $explode_campo[0];
            }

        }
        return $tabla_foranea;
    }

}

<?php
namespace gamboamartin\administrador\modelado;
use base\orm\estructuras;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;


class validaciones extends validacion{

    /**
     * Valida si existe una tabla enm la estructura general de la base de datos
     * @param PDO $link Conexion a la base de datos
     * @param string $name_bd Nombre de la base de datos
     * @param string $tabla Tabla o estructura a validar
     * @version 1.202.34
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-07-25 17:23
     * @return bool|array
     */
    public function existe_tabla(PDO $link, string$name_bd, string $tabla): bool|array
    {
        $name_db = trim($name_bd);
        if($name_db === ''){
            return $this->error->error(mensaje: 'Error name db esta vacio', data: $name_db);
        }
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error $tabla db esta vacio', data: $tabla);
        }

        $tablas = (new estructuras(link: $link))->modelos(name_db: $name_bd);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al obtener tablas", data: $tablas);
        }

        $existe = false;
        foreach ($tablas as $tabla_existente){
            if($tabla_existente === $tabla){
                $existe = true;
                break;
            }
        }
        return $existe;

    }

    /**
     * Valida los elementos bases de un alta en base de datos
     * @param array $registro Registro a validar
     * @param string $tabla Nombre de tabla a validar
     * @return bool|array
     * @fecha 2022-08-01 16:39
     * @author mgamboa
     */
    final public function valida_alta_bd(array $registro, string $tabla): bool|array
    {
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error registro no puede venir vacio', data: $registro);
        }

        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error $tabla esta vacia'.$tabla, data: $tabla);
        }

        return true;
    }

    /**
     * Valida loa campos de un elemento lista
     * @version 1.82.18
     * @param array $campo Campo a validar elementos
     * @param array $bools Campos de tipo bool activo inactivo
     * @return bool|array
     */
    public function valida_campo_envio(array $bools, array $campo): bool|array
    {
        $keys = array('adm_elemento_lista_campo','adm_elemento_lista_cols','adm_elemento_lista_tipo',
            'adm_elemento_lista_tabla_externa', 'adm_elemento_lista_etiqueta','adm_elemento_lista_descripcion',
            'adm_elemento_lista_id');
        $valida = $this->valida_existencia_keys( keys: $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar campo", data: $valida);
        }

        $keys = array('con_label','required','ln','select_vacio_alta');

        $valida = $this->valida_existencia_keys(keys:  $keys, registro: $bools);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar bools", data: $valida);
        }

        return true;
    }

    /**
     * REG
     * Valida los datos de una columna específica en un conjunto de datos.
     *
     * Esta función verifica la existencia de ciertas claves requeridas en un array de datos
     * y valida que el nombre de la tabla proporcionada sea una cadena válida no numérica.
     *
     * @param array $data Array de datos a validar. Debe contener al menos la clave 'nombre_original'.
     * @param string $tabla Nombre de la tabla asociada a los datos. No puede ser numérica ni vacía.
     *
     * @return true|array Retorna `true` si la validación es exitosa. En caso de error, retorna un array
     *                    con información detallada del error.
     *
     * @throws errores::$error Si alguno de los parámetros no cumple con los criterios de validación.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $data = [
     *     'nombre_original' => 'columna_ejemplo'
     * ];
     * $tabla = 'mi_tabla';
     *
     * $resultado = $miClase->valida_data_columna($data, $tabla);
     * if ($resultado === true) {
     *     echo "Validación exitosa.";
     * } else {
     *     print_r($resultado); // En caso de error
     * }
     * ```
     *
     * ### Ejemplo de uso con error en el array de datos:
     *
     * ```php
     * $data = []; // Falta la clave 'nombre_original'
     * $tabla = 'mi_tabla';
     *
     * $resultado = $miClase->valida_data_columna($data, $tabla);
     * if (is_array($resultado)) {
     *     print_r($resultado); // Detalle del error
     * }
     * ```
     *
     * ### Ejemplo de uso con error en el nombre de la tabla:
     *
     * ```php
     * $data = [
     *     'nombre_original' => 'columna_ejemplo'
     * ];
     * $tabla = 12345; // Nombre de tabla inválido
     *
     * $resultado = $miClase->valida_data_columna($data, $tabla);
     * if (is_array($resultado)) {
     *     print_r($resultado); // Detalle del error
     * }
     * ```
     */
    final public function valida_data_columna(array $data, string $tabla): true|array
    {
        $keys = array('nombre_original');
        $valida = $this->valida_existencia_keys(keys: $keys, registro: $data);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }

        if (is_numeric($tabla)) {
            return $this->error->error(mensaje: 'Error ingrese un array valido ' . $tabla, data: $tabla, es_final: true);
        }

        return true;
    }


    /**
     * REG
     * Valida la estructura y el contenido de un filtro especial para asegurarse de que cumple con los requisitos necesarios.
     *
     * @param string $campo Nombre del campo que será evaluado dentro del filtro. Debe ser un string no vacío.
     * @param array $filtro Array asociativo que contiene los criterios del filtro. Debe incluir:
     *  - `$filtro[$campo]['valor_es_campo']`: Indicador opcional para validar si el valor es un campo (puede faltar).
     *  - `$filtro[$campo]['operador']`: Operador que define la lógica de comparación (debe existir).
     *  - `$filtro[$campo]['valor']`: Valor del campo. Si no está definido, se inicializa como una cadena vacía. No puede ser un array.
     *
     * @return true|array Devuelve `true` si la validación es exitosa. Si ocurre un error, devuelve un array con los detalles.
     *
     * @throws errores Si:
     * - `$campo` está vacío.
     * - `$filtro[$campo]['operador']` no está definido.
     * - `$filtro[$campo]['valor']` es un array (no se permite).
     * - `$campo` es numérico cuando no existe `$filtro[$campo]['valor_es_campo']`.
     *
     * ### Ejemplos de uso:
     *
     * 1. **Validación exitosa de un filtro con todos los parámetros**:
     *    ```php
     *    $campo = 'nombre';
     *    $filtro = [
     *        'nombre' => [
     *            'valor_es_campo' => true,
     *            'operador' => '=',
     *            'valor' => 'John Doe'
     *        ]
     *    ];
     *    $resultado = $modelo->valida_data_filtro_especial(campo: $campo, filtro: $filtro);
     *    // Resultado esperado: true
     *    ```
     *
     * 2. **Validación exitosa con `valor` inicializado automáticamente**:
     *    ```php
     *    $campo = 'edad';
     *    $filtro = [
     *        'edad' => [
     *            'operador' => '>',
     *        ]
     *    ];
     *    $resultado = $modelo->valida_data_filtro_especial(campo: $campo, filtro: $filtro);
     *    // Resultado esperado: true (se inicializa $filtro['edad']['valor'] como una cadena vacía)
     *    ```
     *
     * 3. **Error por `$campo` vacío**:
     *    ```php
     *    $campo = '';
     *    $filtro = [
     *        'edad' => [
     *            'operador' => '>',
     *            'valor' => 30
     *        ]
     *    ];
     *    $resultado = $modelo->valida_data_filtro_especial(campo: $campo, filtro: $filtro);
     *    // Resultado esperado: Array con el mensaje "Error campo vacio".
     *    ```
     *
     * 4. **Error por falta de `$filtro[$campo]['operador']`**:
     *    ```php
     *    $campo = 'edad';
     *    $filtro = [
     *        'edad' => [
     *            'valor' => 30
     *        ]
     *    ];
     *    $resultado = $modelo->valida_data_filtro_especial(campo: $campo, filtro: $filtro);
     *    // Resultado esperado: Array con el mensaje "Error debe existir $filtro[campo][operador]".
     *    ```
     *
     * 5. **Error por `$filtro[$campo]['valor']` como array**:
     *    ```php
     *    $campo = 'edad';
     *    $filtro = [
     *        'edad' => [
     *            'operador' => '>',
     *            'valor' => [30, 40]
     *        ]
     *    ];
     *    $resultado = $modelo->valida_data_filtro_especial(campo: $campo, filtro: $filtro);
     *    // Resultado esperado: Array con el mensaje "Error $filtro[edad]['valor'] debe ser un dato".
     *    ```
     *
     * ### Proceso de la función:
     * 1. Valida que `$campo` no esté vacío.
     * 2. Verifica si `$filtro[$campo]['valor_es_campo']` está definido y si `$campo` es un texto válido.
     * 3. Comprueba la existencia de `$filtro[$campo]['operador']`.
     * 4. Inicializa `$filtro[$campo]['valor']` como cadena vacía si no está definido.
     * 5. Valida que `$filtro[$campo]['valor']` no sea un array.
     *
     * ### Resultado esperado:
     * - **Éxito**: `true` si todos los criterios son válidos.
     * - **Error**: Array con detalles específicos del error.
     */

    final public function valida_data_filtro_especial(string $campo, array $filtro): true|array
    {
        if($campo === ''){
            return $this->error->error(mensaje: "Error campo vacio", data: $campo, es_final: true);
        }
        if(!isset($filtro[$campo]['valor_es_campo']) && is_numeric($campo)){
            return $this->error->error(mensaje:'Error el campo debe ser un string $filtro[campo]', data:$filtro,
                es_final: true);
        }
        if(!isset($filtro[$campo]['operador'])){
            return $this->error->error(mensaje:'Error debe existir $filtro[campo][operador]', data:$filtro,
                es_final: true);
        }
        if(!isset($filtro[$campo]['valor'])){
            $filtro[$campo]['valor'] = '';
        }
        if(is_array($filtro[$campo]['valor'])){
            return $this->error->error(mensaje:'Error $filtro['.$campo.'][\'valor\'] debe ser un dato', data:$filtro,
                es_final: true);
        }
        return true;
    }

    /**
     * P INT P ORDER PROBADO
     * Valida que $filtro_esp contenga un campo con $campo enviado y este tenga un dato en valor
     * @param string $campo este no debe ser vacio, debe existir en $filtro_esp
     * @param array $filtro_esp este filtro debe tener $campo, debe existir y contener un dato en  $filtro_esp[$campo][valor]
     * @return bool|array verdadero si el $campo no es vacio, existe y $filtro_esp[$campo]['valor'] existe y tiene un dato
     */
    public function valida_dato_filtro_especial(string $campo, array $filtro_esp): bool|array
    {
        $campo = trim($campo);
        if(trim($campo) === ''){
            return $this->error->error("Error campo vacio", $campo);
        }
        if(!isset($filtro_esp[$campo])){
            return $this->error->error('Error $filtro_esp['.$campo.'] debe existir', $filtro_esp);
        }
        if(!is_array($filtro_esp[$campo])){
            return $this->error->error('Error $filtro_esp['.$campo.'] debe ser un array', $filtro_esp);
        }
        if(!isset($filtro_esp[$campo]['valor'])){
            return $this->error->error('Error $filtro_esp['.$campo.'][valor] debe existir', $filtro_esp);
        }
        if(is_array($filtro_esp[$campo]['valor'])){
            return $this->error->error('Error $filtro_esp['.$campo.'][valor] debe ser un dato', $filtro_esp);
        }
        return true;
    }



    /**
     * P INT P ORDER
     * @param string $campo
     * @param array $filtro_esp
     * @return bool|array
     */
    public function valida_full_filtro_especial(string $campo, array $filtro_esp): bool|array
    {
        $valida = $this->valida_dato_filtro_especial(campo: $campo, filtro_esp: $filtro_esp);
        if(errores::$error){
            return $this->error->error("Error en filtro_esp", $valida);
        }

        $valida = $this->valida_filtro_especial(campo: $campo,filtro: $filtro_esp[$campo]);
        if(errores::$error){
            return $this->error->error("Error en filtro", $valida);
        }
        return true;
    }

    /**
     * REG
     * Valida la estructura de un array de datos y una tabla renombrada para asegurar que cumplan con los requisitos
     * necesarios para realizar un proceso de renombre en una consulta SQL.
     *
     * @param array $data Datos a validar. Debe contener las siguientes claves:
     *  - `enlace`: Nombre de la tabla con la que se establece el enlace.
     *  - `nombre_original`: Nombre original de la tabla o campo. No debe estar vacío.
     * @param string $tabla_renombrada Nombre de la tabla renombrada. No puede estar vacío.
     *
     * @return true|array Devuelve `true` si todas las validaciones son exitosas. En caso de error, retorna un array con
     * los detalles del mismo.
     *
     * @throws errores Si:
     * - Falta la clave `enlace` en `$data`.
     * - Falta la clave `nombre_original` en `$data` o si esta clave está vacía.
     * - `$tabla_renombrada` está vacía o no es válida.
     *
     * ### Ejemplos de uso:
     *
     * 1. **Validación exitosa**:
     *    ```php
     *    $data = [
     *        'enlace' => 'tabla_enlace',
     *        'nombre_original' => 'tabla_original'
     *    ];
     *    $tabla_renombrada = 'tabla_renombrada';
     *
     *    $resultado = $modelo->valida_keys_renombre(data: $data, tabla_renombrada: $tabla_renombrada);
     *    // Resultado esperado: true
     *    ```
     *
     * 2. **Clave faltante en `$data`**:
     *    ```php
     *    $data = [
     *        'nombre_original' => 'tabla_original'
     *    ];
     *    $tabla_renombrada = 'tabla_renombrada';
     *
     *    $resultado = $modelo->valida_keys_renombre(data: $data, tabla_renombrada: $tabla_renombrada);
     *    // Resultado esperado: Array con error indicando que falta la clave `enlace`.
     *    ```
     *
     * 3. **Clave `nombre_original` vacía**:
     *    ```php
     *    $data = [
     *        'enlace' => 'tabla_enlace',
     *        'nombre_original' => ''
     *    ];
     *    $tabla_renombrada = 'tabla_renombrada';
     *
     *    $resultado = $modelo->valida_keys_renombre(data: $data, tabla_renombrada: $tabla_renombrada);
     *    // Resultado esperado: Array con error indicando que `nombre_original` no puede estar vacía.
     *    ```
     *
     * 4. **Tabla renombrada vacía**:
     *    ```php
     *    $data = [
     *        'enlace' => 'tabla_enlace',
     *        'nombre_original' => 'tabla_original'
     *    ];
     *    $tabla_renombrada = '';
     *
     *    $resultado = $modelo->valida_keys_renombre(data: $data, tabla_renombrada: $tabla_renombrada);
     *    // Resultado esperado: Array con error indicando que `$tabla_renombrada` no puede venir vacía.
     *    ```
     *
     * ### Proceso de validación:
     * 1. Verifica que la clave `enlace` exista en `$data`.
     * 2. Verifica que la clave `nombre_original` exista y no esté vacía en `$data`.
     * 3. Valida que `$tabla_renombrada` no esté vacía y sea un texto válido.
     *
     * ### Resultado esperado:
     * - **Validación exitosa**: `true`.
     * - **Error**: Array con los detalles del error si alguna validación falla.
     */

    final public function valida_keys_renombre(array $data, string $tabla_renombrada): true|array
    {
        if(!isset($data['enlace'])){
            return $this->error->error(mensaje: 'Error data[enlace] debe existir', data: $data, es_final: true);
        }
        if(!isset($data['nombre_original'])){
            return $this->error->error(mensaje:'Error data[nombre_original] debe existir', data:$data, es_final: true);
        }
        $data['nombre_original'] = trim($data['nombre_original']);
        if($data['nombre_original'] === ''){
            return $this->error->error(mensaje:'Error data[nombre_original] no puede venir vacia',data: $data,
                es_final: true);
        }
        $tabla_renombrada = trim($tabla_renombrada);
        if($tabla_renombrada === ''){
            return $this->error->error(mensaje:'Error $tabla_renombrada no puede venir vacia', data:$tabla_renombrada,
                es_final: true);
        }
        return true;
    }

    /**
     * REG
     * Valida la existencia y contenido de claves necesarias en un array relacionado con SQL.
     *
     * Esta función asegura que el array `$data` contiene las claves necesarias (`key`, `enlace`, `key_enlace`)
     * y que dichas claves no estén vacías. En caso de que alguna clave no exista o esté vacía, se retorna un error.
     *
     * @param array $data Array que contiene los datos a validar. Debe incluir las claves:
     *                    - `key`: Clave principal de la tabla.
     *                    - `enlace`: Clave del enlace SQL.
     *                    - `key_enlace`: Clave relacionada al enlace.
     * @param string $tabla Nombre de la tabla asociada con los datos validados. Se utiliza para mensajes de error.
     *
     * @return true|array Retorna `true` si la validación es exitosa. Si ocurre un error, retorna un array de error
     *                    con detalles del problema.
     *
     * @throws array Si las claves requeridas no existen, están vacías o los valores no son válidos.
     *
     * ### Ejemplos de uso:
     *
     * 1. **Validación exitosa**:
     *    ```php
     *    $data = [
     *        'key' => 'id_usuario',
     *        'enlace' => 'usuarios',
     *        'key_enlace' => 'id_perfil'
     *    ];
     *    $tabla = 'usuarios';
     *
     *    $resultado = $modelo->valida_keys_sql(data: $data, tabla: $tabla);
     *    // Resultado esperado: true
     *    ```
     *
     * 2. **Error por falta de claves en `$data`**:
     *    ```php
     *    $data = [
     *        'key' => 'id_usuario',
     *        'enlace' => 'usuarios'
     *    ];
     *    $tabla = 'usuarios';
     *
     *    $resultado = $modelo->valida_keys_sql(data: $data, tabla: $tabla);
     *    // Resultado esperado:
     *    // [
     *    //     'error' => 1,
     *    //     'mensaje' => 'Error data[key_enlace] debe existir',
     *    //     'data' => [
     *    //         'key' => 'id_usuario',
     *    //         'enlace' => 'usuarios'
     *    //     ]
     *    // ]
     *    ```
     *
     * 3. **Error por claves vacías**:
     *    ```php
     *    $data = [
     *        'key' => '',
     *        'enlace' => 'usuarios',
     *        'key_enlace' => 'id_perfil'
     *    ];
     *    $tabla = 'usuarios';
     *
     *    $resultado = $modelo->valida_keys_sql(data: $data, tabla: $tabla);
     *    // Resultado esperado:
     *    // [
     *    //     'error' => 1,
     *    //     'mensaje' => 'Error data[key] esta vacio usuarios',
     *    //     'data' => [
     *    //         'key' => '',
     *    //         'enlace' => 'usuarios',
     *    //         'key_enlace' => 'id_perfil'
     *    //     ]
     *    // ]
     *    ```
     *
     * ### Proceso de validación:
     * 1. Verifica que las claves `key`, `enlace` y `key_enlace` existan en `$data`.
     * 2. Asegura que las claves no estén vacías después de aplicar `trim`.
     * 3. En caso de errores, retorna un array con el mensaje descriptivo y los datos relacionados.
     *
     * ### Resultados esperados:
     * - **Validación exitosa**: Retorna `true`.
     * - **Error**: Retorna un array de error con información detallada del problema.
     */

    final public function valida_keys_sql(array $data, string $tabla): true|array
    {
        if(!isset($data['key'])){
            return $this->error->error(mensaje: 'Error data[key] debe existir en '.$tabla, data: $data, es_final: true);
        }
        if(!isset($data['enlace'])){
            return $this->error->error(mensaje:'Error data[enlace] debe existir',data: $data, es_final: true);
        }
        if(!isset($data['key_enlace'])){
            return $this->error->error(mensaje:'Error data[key_enlace] debe existir',data: $data, es_final: true);
        }
        $data['key'] = trim($data['key']);
        $data['enlace'] = trim($data['enlace']);
        $data['key_enlace'] = trim($data['key_enlace']);
        if($data['key'] === ''){
            return $this->error->error(mensaje:'Error data[key] esta vacio '.$tabla, data:$data, es_final: true);
        }
        if($data['enlace'] === ''){
            return $this->error->error(mensaje:'Error data[enlace] esta vacio '.$tabla, data:$data, es_final: true);
        }
        if($data['key_enlace'] === ''){
            return $this->error->error(mensaje:'Error data[key_enlace] esta vacio '.$tabla, data:$data, es_final: true);
        }
        return true;
    }

    /**
     *
     * Valida que una expresion regular se cumpla en un registro
     * @param string $key campo de un registro o this->registro
     * @param array $registro Registro a validar
     * @param string $tipo_campo tipo de pattern a validar en this->patterns
     *
     * @return array|bool
     * @example
     *      foreach($this->tipo_campos as $key =>$tipo_campo){
     * $valida_campos = $this->valida_pattern_campo($key,$tipo_campo);
     * if(isset($valida_campos['error'])){
     * return $this->error->error('Error al validar campos',$valida_campos);
     * }
     * }
     *
     * @uses modelo_basico->valida_estructura_campos
     * @internal  $this->valida_pattern($key,$tipo_campo);
     * @version 1.286.41
     */
    public function valida_pattern_campo(string $key, array $registro, string $tipo_campo):array|bool{
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error el registro no no puede venir vacio',  data: $registro);
        }
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio ', data:  $key);
        }
        if(isset($registro[$key])&&(string)$registro[$key] !==''){
            $valida_data = $this->valida_pattern_model(key:$key,registro: $registro, tipo_campo: $tipo_campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar', data: $valida_data);
            }
        }

        return true;
    }


    /**
     *
     * Valida que una expresion regular se cumpla en un registro
     * @param string $key campo de un registro o this->registro
     * @param array $registro Registro a validar
     * @param string $tipo_campo tipo de pattern a validar en this->patterns

     * @return array|bool
     * @example
     *      $valida_data = $this->valida_pattern($key,$tipo_campo);
     *
     * @uses modelo_basico->valida_pattern_campo
     * @version 1.286.41
     */
    private function valida_pattern_model(string $key, array $registro, string $tipo_campo):array|bool{

        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio ',  data: $key);
        }
        if(!isset($registro[$key])){
            return $this->error->error(mensaje: 'Error no existe el campo '.$key, data: $registro);
        }
        if(!isset($this->patterns[$tipo_campo])){
            return $this->error->error(mensaje: 'Error no existe el pattern '.$tipo_campo,data: $registro);
        }
        $value = trim($registro[$key]);
        $pattern = trim($this->patterns[$tipo_campo]);

        if(!preg_match($pattern, $value)){
            return $this->error->error(mensaje: 'Error el campo '.$key.' es invalido',
                data: array($registro[$key],$pattern));
        }

        return true;
    }

    /**
     * TOTAL
     * Valida cada campo de entrada contra una expresión regular.
     *
     * Esta función recorre cada campo en $tipo_campos y les aplica una validación.
     * Si el campo no está en $registro_upd o está vacío, se omite la validación para ese campo.
     * De lo contrario, se invoca a la función `valida_regex_campo` con parametros correspondientes.
     * Si `valida_regex_campo` causa un error, se regresa el error.
     * Si no hay errores, se regresa true.
     *
     * @param array $tipo_campos Un array asociativo donde cada clave es el nombre de un campo y su valor
     *  una expresión regular para la validación.
     * @param array $registro_upd Un array asociativo que contiene el estado actual del registro. Cada clave en
     * este array corresponde con el nombre del campo y su valor es el valor del campo.
     * @return true|array Regresa un valor verdadero (TRUE) si todas las validaciones son exitosas, de lo
     * contrario se devuelve un array de errores.
     * @version 16.123.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.validaciones.valida_regex
     */
    private function valida_regex(array $tipo_campos, array $registro_upd): true|array
    {
        foreach ($tipo_campos as $campo =>$tipo_campo){
            if(!isset($registro_upd[$campo])){
                continue;
            }
            if(trim($registro_upd[$campo]) === ''){
                continue;
            }
            $valida = $this->valida_regex_campo(campo: $campo,registro_upd: $registro_upd,tipo_campo: $tipo_campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar',data:  $valida);
            }
        }
        return true;
    }

    /**
     * TOTAL
     * Valida un campo especificado en el arreglo de registros proporcionado según un patrón especificado en $tipo_campo.
     *
     * @param string $campo Nombre del campo del registro a validar.
     * @param array $registro_upd Arreglo del registro que contiene el campo a validar.
     * @param string $tipo_campo Patrón de la expresión regular que se va a utilizar para la validación.
     *
     * @return true|array Devuelve true si la validación es exitosa. Si hay un error, devuelve un arreglo con la información del error.
     *
     * @throws errores Si $campo está vacío, si $tipo_campo está vacío,
     *               si el campo especificado no existe en el registro proporcionado $registro_upd,
     *               si ocurre un error durante la validación.
     *
     * @version 16.120.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.validaciones.valida_regex_campo
     */
    private function valida_regex_campo(string $campo, array $registro_upd, string $tipo_campo): true|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio',data:  $campo);
        }
        $tipo_campo = trim($tipo_campo);
        if($tipo_campo === ''){
            return $this->error->error(mensaje: 'Error el tipo_campo esta vacio',data:  $tipo_campo);
        }
        if(!isset($registro_upd[$campo])){
            return $this->error->error(mensaje: 'Error no existe el campo en el registro '.$campo,data:  $registro_upd);
        }
        $valida = (new validacion())->valida_pattern(key: $tipo_campo,txt:  $registro_upd[$campo]);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar ',data:  $valida);
        }
        if(!$valida){
            return $this->error->error(mensaje: 'Error al validar '.$campo.' debe tener formato '.$tipo_campo,
                data: $registro_upd[$campo]);
        }
        return true;
    }

    /**
     * REG
<<<<<<< HEAD
     * Valida que los valores proporcionados para renombrar columnas, tablas y tipos de joins sean correctos y estén completos.
     *
     * @param string $campo_renombrado Nombre del campo renombrado. Debe ser una cadena no vacía.
     * @param string $join Tipo de unión (join) para la consulta SQL. Valores permitidos: `'INNER'`, `'LEFT'`, `'RIGHT'`.
     * @param string $renombrada Nombre de la tabla renombrada. Debe ser una cadena no vacía.
     * @param string $tabla Nombre de la tabla base. No debe estar vacía.
     * @param string $tabla_enlace Nombre de la tabla de enlace. No debe estar vacía.
     *
     * @return true|array Devuelve `true` si todos los valores son válidos. En caso de error, retorna un array con los detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo_renombrado = 'usuario_id';
     * $join = 'INNER';
     * $renombrada = 'usuarios_renombrados';
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles';
=======
     * Valida los parámetros relacionados con las configuraciones de renombre de tablas y joins en consultas SQL.
     *
     * @param string $campo_renombrado Nombre del campo que se utilizará para el renombre. No puede estar vacío.
     * @param string $join Tipo de JOIN que se aplicará en la consulta. Debe ser uno de los siguientes: `INNER`, `LEFT`, `RIGHT`.
     * @param string $renombrada Nombre de la tabla renombrada. No puede estar vacío.
     * @param string $tabla Nombre de la tabla original. No puede estar vacío.
     * @param string $tabla_enlace Nombre de la tabla de enlace asociada. No puede estar vacío.
     *
     * @return true|array Retorna `true` si todos los parámetros son válidos. En caso de error, retorna un array con
     *                    los detalles del error, incluyendo un mensaje descriptivo y los datos que generaron el error.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $campo_renombrado = 'usuario_id';
     * $join = 'INNER';
     * $renombrada = 'usuarios_alias';
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles_usuarios';
>>>>>>> 49a610360774f77119bfa2ab68481482a093b2ee
     *
     * $resultado = $miClase->valida_renombres(
     *     campo_renombrado: $campo_renombrado,
     *     join: $join,
     *     renombrada: $renombrada,
     *     tabla: $tabla,
     *     tabla_enlace: $tabla_enlace
     * );
     *
<<<<<<< HEAD
     * var_dump($resultado); // Resultado esperado: true
     * ```
     *
     * ### Ejemplo de errores comunes:
     *
     * 1. **Caso:** El nombre de la tabla está vacío.
     * ```php
     * $resultado = $miClase->valida_renombres(
     *     campo_renombrado: 'usuario_id',
     *     join: 'INNER',
     *     renombrada: 'usuarios_renombrados',
     *     tabla: '',
     *     tabla_enlace: 'roles'
     * );
     *
     * var_dump($resultado);
     * // Resultado esperado:
     * // Array
     * // (
     * //     [error] => 1
     * //     [mensaje] => La tabla no puede ir vacia
     * //     [data] =>
     * //     [es_final] => true
     * // )
     * ```
     *
     * 2. **Caso:** Tipo de join inválido.
     * ```php
     * $resultado = $miClase->valida_renombres(
     *     campo_renombrado: 'usuario_id',
     *     join: 'CROSS',
     *     renombrada: 'usuarios_renombrados',
     *     tabla: 'usuarios',
     *     tabla_enlace: 'roles'
     * );
     *
     * var_dump($resultado);
=======
     * var_dump($resultado);
     * // Resultado esperado:
     * // true
     * ```
     *
     * ### Ejemplo de error:
     *
     * - Caso: El tipo de JOIN no es válido.
     *
     * ```php
     * $campo_renombrado = 'usuario_id';
     * $join = 'FULL'; // Error: No es un tipo de JOIN permitido.
     * $renombrada = 'usuarios_alias';
     * $tabla = 'usuarios';
     * $tabla_enlace = 'roles_usuarios';
     *
     * $resultado = $miClase->valida_renombres(
     *     campo_renombrado: $campo_renombrado,
     *     join: $join,
     *     renombrada: $renombrada,
     *     tabla: $tabla,
     *     tabla_enlace: $tabla_enlace
     * );
     *
     * print_r($resultado);
>>>>>>> 49a610360774f77119bfa2ab68481482a093b2ee
     * // Resultado esperado:
     * // Array
     * // (
     * //     [error] => 1
     * //     [mensaje] => Error join invalido debe ser INNER, LEFT O RIGTH
<<<<<<< HEAD
     * //     [data] => CROSS
     * //     [es_final] => true
     * // )
     * ```
     *
     * ### Validaciones realizadas:
     *
     * 1. **Campos obligatorios:**
     *    - `$tabla`: No debe estar vacía.
     *    - `$join`: No debe estar vacío y debe ser `'INNER'`, `'LEFT'`, o `'RIGHT'`.
     *    - `$renombrada`: No debe estar vacía.
     *    - `$tabla_enlace`: No debe estar vacía.
     *    - `$campo_renombrado`: No debe estar vacío.
     *
     * 2. **Validación de tipos de joins:**
     *    - Solo se aceptan `'INNER'`, `'LEFT'` y `'RIGHT'`.
     *
     * ### Detalles de los parámetros:
     *
     * - **`$campo_renombrado`**: Nombre del campo que será renombrado en la consulta.
     *   - Ejemplo válido: `'usuario_id'`.
     *   - Ejemplo inválido: `''`.
     *
     * - **`$join`**: Tipo de unión para la consulta SQL.
     *   - Valores válidos: `'INNER'`, `'LEFT'`, `'RIGHT'`.
     *   - Ejemplo válido: `'INNER'`.
     *   - Ejemplo inválido: `'CROSS'`.
     *
     * - **`$renombrada`**: Nombre de la tabla renombrada.
     *   - Ejemplo válido: `'usuarios_renombrados'`.
     *   - Ejemplo inválido: `''`.
     *
     * - **`$tabla`**: Nombre de la tabla base.
     *   - Ejemplo válido: `'usuarios'`.
     *   - Ejemplo inválido: `''`.
     *
     * - **`$tabla_enlace`**: Nombre de la tabla de enlace.
     *   - Ejemplo válido: `'roles'`.
     *   - Ejemplo inválido: `''`.
     *
     * ### Resultado esperado:
     * - Devuelve `true` si todos los parámetros son válidos.
     * - Retorna un array con detalles del error si alguno de los valores no es válido.
=======
     * //     [data] => FULL
     * // )
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$campo_renombrado`**:
     *   Nombre del campo que será utilizado para realizar el renombre. Este valor no puede estar vacío.
     *   Ejemplo válido: `'usuario_id'`.
     *   Ejemplo inválido: `''` (cadena vacía).
     *
     * - **`$join`**:
     *   Tipo de JOIN que se utilizará en la consulta. Este debe ser uno de los siguientes valores: `INNER`, `LEFT`, `RIGHT`.
     *   Ejemplo válido: `'INNER'`.
     *   Ejemplo inválido: `'FULL'` o `''`.
     *
     * - **`$renombrada`**:
     *   Nombre de la tabla renombrada. Este valor no puede estar vacío.
     *   Ejemplo válido: `'usuarios_alias'`.
     *   Ejemplo inválido: `''` (cadena vacía).
     *
     * - **`$tabla`**:
     *   Nombre de la tabla original. Este valor no puede estar vacío.
     *   Ejemplo válido: `'usuarios'`.
     *   Ejemplo inválido: `''` (cadena vacía).
     *
     * - **`$tabla_enlace`**:
     *   Nombre de la tabla de enlace asociada. Este valor no puede estar vacío.
     *   Ejemplo válido: `'roles_usuarios'`.
     *   Ejemplo inválido: `''` (cadena vacía).
     *
     * ### Resultado esperado:
     *
     * - **Éxito**:
     *   Retorna `true` si todos los parámetros son válidos.
     *
     * - **Error**:
     *   Si algún parámetro no cumple con las condiciones, se retorna un array con los detalles del error:
     *   - `error`: Indicador de error (`1`).
     *   - `mensaje`: Descripción del error.
     *   - `data`: Datos asociados al error.
     *
     * ### Notas adicionales:
     *
     * - Está diseñada para ser utilizada en la validación previa a la construcción de consultas SQL complejas.
     * - Asegura que los valores de renombres y tipos de JOIN sean válidos antes de su uso en la lógica de negocio.
>>>>>>> 49a610360774f77119bfa2ab68481482a093b2ee
     */

    final public function valida_renombres(string $campo_renombrado, string $join, string $renombrada,
                                     string $tabla, string $tabla_enlace): true|array
    {
        if($tabla === ''){
            return$this->error->error(mensaje: 'La tabla no puede ir vacia', data: $tabla, es_final: true);
        }
        if($join === ''){
            return $this->error->error(mensaje:'El join no puede ir vacio', data:$tabla, es_final: true);
        }
        if($renombrada === ''){
            return $this->error->error(mensaje:'El $renombrada no puede ir vacio', data:$tabla, es_final: true);
        }
        if($tabla_enlace === ''){
            return $this->error->error(mensaje:'El $tabla_enlace no puede ir vacio',data: $tabla, es_final: true);
        }
        if($campo_renombrado === ''){
            return $this->error->error(mensaje:'El $campo_renombrado no puede ir vacio',data: $tabla, es_final: true);
        }

        if(trim($join) !=='LEFT' && trim($join) !=='RIGHT' && trim($join) !=='INNER'){
            return $this->error->error(mensaje: 'Error join invalido debe ser INNER, LEFT O RIGTH ',data: $join,
                es_final: true);
        }

        return true;
    }

    /**
     * REG
<<<<<<< HEAD
     * Valida que los parámetros proporcionados `$key` y `$tabla_join` sean válidos para construir una relación SQL JOIN.
     *
     * @param string $key Identificador único de la tabla base en el JOIN.
     *                    - No debe ser numérico.
     *                    - No debe estar vacío.
     * @param string $tabla_join Nombre de la tabla que se unirá en el JOIN.
     *                           - No debe ser numérico.
     *                           - No debe estar vacío.
     *
     * @return true|array Retorna `true` si los valores son válidos.
     *                    Retorna un array con los detalles del error en caso de validación fallida.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $key = 'usuarios';
     * $tabla_join = 'roles';
     * $resultado = $this->valida_tabla_join($key, $tabla_join);
     *
     * if ($resultado === true) {
     *     echo 'Validación exitosa';
     * } else {
     *     print_r($resultado); // Mostrará el detalle del error si ocurre.
     * }
     * // Resultado: 'Validación exitosa'
     * ```
     *
     * ### Validaciones realizadas:
     * 1. **Validación de `$key`:**
     *    - Comprueba que `$key` no sea numérico.
     *    - Verifica que `$key` no esté vacío.
     * 2. **Validación de `$tabla_join`:**
     *    - Comprueba que `$tabla_join` no sea numérico.
     *    - Verifica que `$tabla_join` no esté vacío.
     *
     * ### Ejemplo de error:
     * **Error por `$key` vacío:**
     * ```php
     * $key = '';
     * $tabla_join = 'roles';
     * $resultado = $this->valida_tabla_join($key, $tabla_join);
     *
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
     * $resultado = $this->valida_tabla_join($key, $tabla_join);
     *
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
     * - Utiliza `trim` para eliminar espacios en blanco al inicio y al final de los parámetros.
     * - Verifica que los valores no sean numéricos ni cadenas vacías.
     * - En caso de error, utiliza `$this->error->error` para registrar y retornar el detalle del problema.
     *
     * ### Casos de uso:
     * - Validar datos antes de construir dinámicamente una consulta SQL JOIN.
     * - Garantizar que los identificadores y nombres de tablas sean válidos en sistemas que construyen consultas SQL.
     *
     * ### Consideraciones:
     * - Esta función es útil como paso previo para cualquier método que construya relaciones entre tablas en SQL.
     * - Asegúrate de que los parámetros `$key` y `$tabla_join` provengan de fuentes confiables y sean limpiados antes de su uso.
=======
     * Valida los parámetros necesarios para procesar una tabla en un `JOIN` SQL.
     *
     * Esta función verifica que las claves `key` y `tabla_join` proporcionadas sean válidas. Garantiza que no sean números
     * y que no estén vacías, ya que son esenciales para la construcción de `JOINs` en consultas SQL.
     *
     * @param string $key Clave principal o identificador que representa un alias o el nombre de una tabla.
     *                    Debe ser una cadena no vacía y no numérica.
     *                    Ejemplo: `'usuarios'`.
     * @param string $tabla_join Nombre de la tabla que será utilizada en el `JOIN`.
     *                           Debe ser una cadena no vacía y no numérica.
     *                           Ejemplo: `'roles'`.
     *
     * @return true|array Retorna `true` si ambos parámetros son válidos. En caso de error, devuelve un array con los detalles
     *                    del error, indicando el problema específico.
     *
     * ### Ejemplo de uso exitoso:
     *
     * ```php
     * $key = 'usuarios';
     * $tabla_join = 'roles';
     *
     * $resultado = $this->valida_tabla_join(key: $key, tabla_join: $tabla_join);
     *
     * // Resultado esperado:
     * // true
     * ```
     *
     * ### Detalles de los parámetros:
     *
     * - **`$key`**:
     *   Representa el alias o identificador de una tabla.
     *   Requisitos:
     *   - No puede ser numérico.
     *   - No puede estar vacío.
     *   Ejemplo válido:
     *   ```php
     *   'usuarios'
     *   ```
     *   Ejemplo inválido:
     *   ```php
     *   123 // Es numérico.
     *   ''
     *   ```
     *
     * - **`$tabla_join`**:
     *   Es el nombre de la tabla que se utilizará en el `JOIN`.
     *   Requisitos:
     *   - No puede ser numérico.
     *   - No puede estar vacío.
     *   Ejemplo válido:
     *   ```php
     *   'roles'
     *   ```
     *   Ejemplo inválido:
     *   ```php
     *   456 // Es numérico.
     *   ''
     *   ```
     *
     * ### Casos de error:
     *
     * 1. **`$key` es numérico**:
     *    ```php
     *    $resultado = $this->valida_tabla_join(key: '123', tabla_join: 'roles');
     *    // Resultado esperado: array con mensaje de error "Error el key no puede ser un numero".
     *    ```
     *
     * 2. **`$key` está vacío**:
     *    ```php
     *    $resultado = $this->valida_tabla_join(key: '', tabla_join: 'roles');
     *    // Resultado esperado: array con mensaje de error "Error key esta vacio".
     *    ```
     *
     * 3. **`$tabla_join` es numérico**:
     *    ```php
     *    $resultado = $this->valida_tabla_join(key: 'usuarios', tabla_join: '123');
     *    // Resultado esperado: array con mensaje de error "Error el $tabla_join no puede ser un numero".
     *    ```
     *
     * 4. **`$tabla_join` está vacío**:
     *    ```php
     *    $resultado = $this->valida_tabla_join(key: 'usuarios', tabla_join: '');
     *    // Resultado esperado: array con mensaje de error "Error $tabla_join esta vacio".
     *    ```
     *
     * ### Resultado esperado:
     *
     * - Si ambos parámetros son válidos, retorna `true`.
     * - Si alguno de los parámetros no cumple los requisitos, retorna un array con el mensaje de error y los detalles del problema.
     *
     * ### Notas adicionales:
     *
     * - Esta función es útil para validar entradas antes de construir dinámicamente consultas SQL que incluyan `JOINs`.
     * - Garantiza que se eviten errores comunes en la generación de SQL debido a parámetros inválidos.
>>>>>>> 49a610360774f77119bfa2ab68481482a093b2ee
     */

    final public function valida_tabla_join(string $key, string $tabla_join ): true|array
    {
        $key = trim($key);
        if(is_numeric($key)){
            return $this->error->error(mensaje: 'Error el key no puede ser un numero', data: $key, es_final: true);
        }
        if($key === ''){
            return $this->error->error(mensaje:'Error key esta vacio', data:$key, es_final: true);
        }
        $tabla_join = trim($tabla_join);
        if(is_numeric($tabla_join)){
            return $this->error->error(mensaje:'Error el $tabla_join no puede ser un numero',data: $tabla_join,
                es_final: true);
        }
        if($tabla_join === ''){
            return $this->error->error(mensaje:'Error $tabla_join esta vacio',data: $tabla_join, es_final: true);
        }

        return true;
    }

    /**
     * TOTAL
     * Esta función valida los datos del registro que se va a actualizar.
     *
     * @param int $id Id del registro que se va a actualizar. Debe ser mayor que 0.
     * @param array $registro_upd Array con los datos del registro a actualizar.
     * @param array $tipo_campos (opcional) Array con los tipos de campos para cada dato del registro que se va a
     *  actualizar.
     * @param bool $valida_row_vacio (opcional) Valor para indicar si se debe validar que el registro no debe ser
     * un array vacío. Por defecto el valor es verdadero (true).
     *
     * @return bool|array Retorna verdadero (true) si la validación es correcta. Si la validación falla,
     * retorna un array con la información del error.
     *
     * @throws errores Si ocurre algún error durante la validación.
     * @version 16.124.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.validaciones.valida_upd_base
     */
    final public function valida_upd_base(int $id, array $registro_upd, array $tipo_campos = array(),
                                          bool $valida_row_vacio = true): bool|array
    {
        if($id <=0){
            return $this->error->error(mensaje: 'Error el id debe ser mayor a 0',data: $id);
        }
        if($valida_row_vacio) {
            if (count($registro_upd) === 0) {
                return $this->error->error(mensaje: 'El registro no puede venir vacio', data: $registro_upd);
            }
        }
        $valida_regex = $this->valida_regex(tipo_campos: $tipo_campos,registro_upd: $registro_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data:  $valida_regex);
        }

        return true;
    }


}

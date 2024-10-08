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
     * TOTAL
     * Valida los datos de una columna específica.
     *
     * @param array $data  Los datos que se van a validar.
     * @param string $tabla  El nombre de la tabla que contiene la columna a validar.
     *
     * @return true|array  Devuelve verdadero si los datos son válidos, de lo contrario devuelve un array de errores.
     *
     * @throws errores  Se lanza una excepción si encuentra un error durante la validación.
     * @version 16.1.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.valida_data_columna
     */
    final public function valida_data_columna(array $data, string $tabla): true|array
    {

        $keys = array('nombre_original');
        $valida = $this->valida_existencia_keys(keys:$keys, registro: $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }

        if(is_numeric($tabla)){
            return $this->error->error(mensaje:'Error ingrese un array valido '.$tabla, data: $tabla, es_final: true);
        }

        return true;
    }

    /**
     * TOTAL
     * Valida la data del filtro especial proporcionado.
     *
     * Esta función recibe dos parámetros, un string que representa el campo y un array que representa el filtro.
     * Verifica si el campo está vacío, si el valor es un campo en el filtro, si existe un operador y si existe un valor.
     * Si alguna de estas condiciones no se cumple, la función retorna un error. Si todas las condiciones se cumplen retorna true.
     *
     * @param string $campo El nombre del campo a validar.
     * @param array $filtro El filtro a validar.
     *
     * @return true|array Devuelve `true` si la validación es correcta, si no, devuelve un array con información sobre el error.
     *
     * @throws errores Si el campo está vacío, si el valor del filtro no es un campo, si no existe un operador o si el valor es un array.
     * @version 16.104.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.validaciones.valida_data_filtro_especial
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
     * TOTAL
     * Esta función verifica y valida los campos clave en el proceso de renombramiento.
     *
     * @param array $data - Los datos proporcionados por el usuario que contienen 'enlace' y 'nombre_original'
     * @param string $tabla_renombrada - El nombre de la tabla renombrada
     *
     * @return true|array - Regresa verdadero si todas las validaciones son exitosas,
     *                      de lo contrario, devuelve un array con información de error generada por la función error()
     *
     * ### Proceso de validación:
     * 1.   Verifica si existe la clave 'enlace' en la matriz de datos proporcionada.
     *      Si no existe, genera un error y devuelve la respuesta del error.
     * 2.   Verifica si existe la clave 'nombre_original' en la matriz de datos proporcionada.
     *      Si no existe, genera un error y devuelve la respuesta del error.
     * 3.   Realiza un trim() en el valor de 'nombre_original' y verifica si está vacío.
     *      Si está vacío, genera un error y devuelve la respuesta del error.
     * 4.   Realiza un trim() en el valor de la variable $tabla_renombrada y verifica si está vacío.
     *      Si está vacío, genera un error y devuelve la respuesta del error.
     *
     * @version 16.81.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.validaciones.valida_keys_renombre.21.25.0
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
     * TOTAL
     * Función valida_keys_sql
     *
     * Esta función valida los parámetros entregados para una consulta SQL.
     * Los parámetros a validar son 'key', 'enlace', 'key_enlace'. Estos deben existir y no deben estar vacíos.
     *
     * @param array $data    Datos a validar. Debe tener los indices 'key', 'enlace' y 'key_enlace'.
     * @param string $tabla  La tabla SQL en la que se está trabajando para el contexto del mensaje de error.
     *
     * @return true|array    Retorna verdadero si los datos son válidos. En caso contrario retorna un array con el error.
     * @version 16.55.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.validaciones.valida_keys_sql.21.24.0
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
     * TOTAL
     * Valida los parámetros necesarios para llevar a cabo operaciones de renombrado en consultas.
     *
     * @param string $campo_renombrado El campo que se desea renombrar.
     * @param string $join La operación JOIN a realizar.
     * @param string $renombrada El nuevo nombre del campo.
     * @param string $tabla El nombre de la tabla.
     * @param string $tabla_enlace La tabla con la que se realiza el JOIN.
     * @return true|array Devuelve true si todas las validaciones son correctas, de lo contrario, devuelve un arreglo con un mensaje de error.
     * @version 15.33.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.validaciones.valida_renombres.21.11.0
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
     * TOTAL
     * Valida los parámetros de entrada de una junta de tablas (_table join_).
     *
     * @final
     *
     * @param string $key La clave que se va a validar. No puede ser un número ni una cadena vacía.
     * @param string $tabla_join El nombre de la tabla que se va a unir (_join_). No puede ser un número ni una cadena vacía.
     *
     * @return true|array Retorna verdadero si la validación fue exitosa. Si ocurre un error, retorna un arreglo con información sobre el error.
     *
     * @throws errores Si algún parámetro no cumple las condiciones, se lanza un error con la descripción del problema.
     * @version 15.68.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.modelado.validaciones.valida_tabla_join.21.15.0
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

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
     * @version 1.244.39
     * @version 1.1.0
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
     * POR DOCUMENTAR EN WIKI
     * Valida los datos de una columna específica.
     *
     * @param array $data  Los datos que se van a validar.
     * @param string $tabla  El nombre de la tabla que contiene la columna a validar.
     *
     * @return true|array  Devuelve verdadero si los datos son válidos, de lo contrario devuelve un array de errores.
     *
     * @throws errores  Se lanza una excepción si encuentra un error durante la validación.
     * @version 16.1.0
     */
    final public function valida_data_columna(array $data, string $tabla): true|array
    {

        $keys = array('nombre_original');
        $valida = $this->valida_existencia_keys(keys:$keys, registro: $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }

        if(is_numeric($tabla)){
            return $this->error->error(mensaje:'Error ingrese un array valido '.$tabla, data: $tabla);
        }

        return true;
    }

    /**
     * Valida que los datos de un filtro especial sean correctos para la integracion de un WHERE
     * @version 1.127.29
     * @param string $campo Campo en ejecucion para integrarlo al filtro
     * @param array $filtro Filtro a ajustar de manera recursiva
     * @return bool|array
     */
    public function valida_data_filtro_especial(string $campo, array $filtro): bool|array
    {
        if($campo === ''){
            return $this->error->error(mensaje: "Error campo vacio", data: $campo);
        }
        if(!isset($filtro[$campo]['valor_es_campo']) && is_numeric($campo)){
            return $this->error->error(mensaje:'Error el campo debe ser un string $filtro[campo]', data:$filtro);
        }
        if(!isset($filtro[$campo]['operador'])){
            return $this->error->error(mensaje:'Error debe existir $filtro[campo][operador]', data:$filtro);
        }
        if(!isset($filtro[$campo]['valor'])){
            $filtro[$campo]['valor'] = '';
        }
        if(is_array($filtro[$campo]['valor'])){
            return $this->error->error(mensaje:'Error $filtro['.$campo.'][\'valor\'] debe ser un dato', data:$filtro);
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
     * Valida que los datos para ejecutar un renombre de tabla sean correctos
     * @version 1.66.17
     * @param array $data $data[enlace,nombre_original] Datos para JOIN
     * @param string $tabla_renombrada nombre nuevo de la tabla
     * @return bool|array
     */
    public function valida_keys_renombre(array $data, string $tabla_renombrada): bool|array
    {
        if(!isset($data['enlace'])){
            return $this->error->error(mensaje: 'Error data[enlace] debe existir', data: $data);
        }
        if(!isset($data['nombre_original'])){
            return $this->error->error(mensaje:'Error data[nombre_original] debe existir', data:$data);
        }
        $data['nombre_original'] = trim($data['nombre_original']);
        if($data['nombre_original'] === ''){
            return $this->error->error(mensaje:'Error data[nombre_original] no puede venir vacia',data: $data);
        }
        $tabla_renombrada = trim($tabla_renombrada);
        if($tabla_renombrada === ''){
            return $this->error->error(mensaje:'Error $tabla_renombrada no puede venir vacia', data:$tabla_renombrada);
        }
        return true;
    }

    /**
     * Valida que existan los elementos necesarios para un JOIN
     * @param array $data data[key,enlace,key_enlace]
     * @param string $tabla Tabla en ejecucion
     * @return bool|array
     */
    public function valida_keys_sql(array $data, string $tabla): bool|array
    {
        if(!isset($data['key'])){
            return $this->error->error(mensaje: 'Error data[key] debe existir en '.$tabla, data: $data);
        }
        if(!isset($data['enlace'])){
            return $this->error->error(mensaje:'Error data[enlace] debe existir',data: $data);
        }
        if(!isset($data['key_enlace'])){
            return $this->error->error(mensaje:'Error data[key_enlace] debe existir',data: $data);
        }
        $data['key'] = trim($data['key']);
        $data['enlace'] = trim($data['enlace']);
        $data['key_enlace'] = trim($data['key_enlace']);
        if($data['key'] === ''){
            return $this->error->error(mensaje:'Error data[key] esta vacio '.$tabla, data:$data);
        }
        if($data['enlace'] === ''){
            return $this->error->error(mensaje:'Error data[enlace] esta vacio '.$tabla, data:$data);
        }
        if($data['key_enlace'] === ''){
            return $this->error->error(mensaje:'Error data[key_enlace] esta vacio '.$tabla, data:$data);
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
     * Valida un regex basado en el tipo de campo
     * @param array $tipo_campos Tipos de campo a verificar aplicacion de regex
     * @param array $registro_upd
     * @return bool|array
     */
    private function valida_regex(array $tipo_campos, array $registro_upd): bool|array
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
     * Valida un regex con patter de campo
     * @param string $campo Campo a validar
     * @param array $registro_upd Registro a validar
     * @param string $tipo_campo Tipo de campo para aplicacion de regex
     * @return bool|array
     */
    private function valida_regex_campo(string $campo, array $registro_upd, string $tipo_campo): bool|array
    {
        $valida = (new validacion())->valida_pattern(key: $tipo_campo,txt:  $registro_upd[$campo]);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar',data:  $valida);
        }
        if(!$valida){
            return $this->error->error(mensaje: 'Error al validar '.$campo.' debe tener formato'.$tipo_campo,
                data: $registro_upd[$campo]);
        }
        return true;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Valida los parámetros necesarios para llevar a cabo operaciones de renombrado en consultas.
     *
     * @param string $campo_renombrado El campo que se desea renombrar.
     * @param string $join La operación JOIN a realizar.
     * @param string $renombrada El nuevo nombre del campo.
     * @param string $tabla El nombre de la tabla.
     * @param string $tabla_enlace La tabla con la que se realiza el JOIN.
     * @return true|array Devuelve true si todas las validaciones son correctas, de lo contrario, devuelve un arreglo con un mensaje de error.
     * @version 15.33.1
     */
    final public function valida_renombres(string $campo_renombrado, string $join, string $renombrada,
                                     string $tabla, string $tabla_enlace): true|array
    {
        if($tabla === ''){
            return$this->error->error(mensaje: 'La tabla no puede ir vacia', data: $tabla);
        }
        if($join === ''){
            return $this->error->error(mensaje:'El join no puede ir vacio', data:$tabla);
        }
        if($renombrada === ''){
            return $this->error->error(mensaje:'El $renombrada no puede ir vacio', data:$tabla);
        }
        if($tabla_enlace === ''){
            return $this->error->error(mensaje:'El $tabla_enlace no puede ir vacio',data: $tabla);
        }
        if($campo_renombrado === ''){
            return $this->error->error(mensaje:'El $campo_renombrado no puede ir vacio',data: $tabla);
        }

        if(trim($join) !=='LEFT' && trim($join) !=='RIGHT' && trim($join) !=='INNER'){
            return $this->error->error(mensaje: 'Error join invalido debe ser INNER, LEFT O RIGTH ',data: $join);
        }

        return true;
    }

    /**
     * POR DOCUMENTAR EN WIKI
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
     */
    final public function valida_tabla_join(string $key, string $tabla_join ): true|array
    {
        $key = trim($key);
        if(is_numeric($key)){
            return $this->error->error(mensaje: 'Error el key no puede ser un numero', data: $key);
        }
        if($key === ''){
            return $this->error->error(mensaje:'Error key esta vacio', data:$key);
        }
        $tabla_join = trim($tabla_join);
        if(is_numeric($tabla_join)){
            return $this->error->error(mensaje:'Error el $tabla_join no puede ser un numero',data: $tabla_join);
        }
        if($tabla_join === ''){
            return $this->error->error(mensaje:'Error $tabla_join esta vacio',data: $tabla_join);
        }

        return true;
    }

    /**
     * Valida los elementos basicos de un upd
     * @param int $id Identificador a modificar
     * @param array $registro_upd Registro a modificar
     * @param array $tipo_campos Tipos de campo a verificar aplicacion de regex
     * @return array|bool
     * @version 1.77.17
     * @verfuncion 1.2.0 Se integra tipo campos para validar con regex
     * @author mgamboa
     * @fecha 2022-08-08 12:27
     */
    final public function valida_upd_base(int $id, array $registro_upd, array $tipo_campos = array()): bool|array
    {
        if($id <=0){
            return $this->error->error(mensaje: 'Error el id debe ser mayor a 0',data: $id);
        }
        if(count($registro_upd) === 0){
            return $this->error->error(mensaje: 'El registro no puede venir vacio',data: $registro_upd);
        }
        $valida_regex = $this->valida_regex(tipo_campos: $tipo_campos,registro_upd: $registro_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data:  $valida_regex);
        }

        return true;
    }


}

<?php
namespace base\orm;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;

class validaciones extends validacion{

    /**
     * P INT P ORDER PROBADO ERRORREV
     * @param array $registro
     * @param string $tabla
     * @return bool|array
     */
    public function valida_alta_bd(array $registro, string $tabla): bool|array
    {
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error registro no puede venir vacio', data: $registro,
                params: get_defined_vars());
        }

        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error $tabla esta vacia'.$tabla, data: $tabla,
                params: get_defined_vars());
        }

        return true;
    }

    /**
     * P ORDER P INT
     * @param array $campo
     * @param array $bools
     * @return bool|array
     */
    public function valida_campo_envio(array $bools, array $campo): bool|array
    {
        $keys = array('elemento_lista_campo','elemento_lista_cols','elemento_lista_tipo',
            'elemento_lista_tabla_externa',
            'elemento_lista_etiqueta','elemento_lista_descripcion','elemento_lista_id');
        $valida = $this->valida_existencia_keys( keys: $keys, registro: $campo);
        if(errores::$error){
            return $this->error->error("Error al validar campo", $valida);
        }

        $keys = array('con_label','required','ln','select_vacio_alta');

        $valida = $this->valida_existencia_keys(keys:  $keys, registro: $bools);
        if(errores::$error){
            return $this->error->error("Error al validar bools", $valida);
        }

        return true;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $data
     * @param string $tabla
     * @return bool|array
     */
    public function valida_data_columna(array $data, string $tabla): bool|array
    {

        $keys = array('nombre_original');
        $valida = $this->valida_existencia_keys(keys:$keys, registro: $data);
        if(errores::$error){
            return $this->error->error('Error al validar data', $valida);
        }

        $data['nombre_original'] = str_replace('models\\','',$data['nombre_original']);
        $class = 'models\\'.$data['nombre_original'];
        if(is_numeric($tabla)){
            return $this->error->error('Error ingrese un array valido '.$tabla, $tabla);
        }
        if(!class_exists($class)){
            return $this->error->error('Error no existe el modelo '.$class, $data);
        }
        return true;
    }

    /**
     * P INT P ORDER PROBADO
     * @param string $campo
     * @param array $filtro
     * @return bool|array
     */
    public function valida_data_filtro_especial(string $campo, array $filtro): bool|array
    {
        if($campo === ''){
            return $this->error->error("Error campo vacio", $campo);
        }
        if(!isset($filtro[$campo]['valor_es_campo']) && is_numeric($campo)){
            return $this->error->error('Error el campo debe ser un string $filtro[campo]', $filtro);
        }
        if(!isset($filtro[$campo]['operador'])){
            return $this->error->error('Error debe existir $filtro[campo][operador]', $filtro);
        }
        if(!isset($filtro[$campo]['valor'])){
            $filtro[$campo]['valor'] = '';
        }
        if(is_array($filtro[$campo]['valor'])){
            return $this->error->error('Error $filtro['.$campo.'][\'valor\'] debe ser un dato', $filtro);
        }
        return true;
    }

    /**
     * P INT P ORDER PROBADO
     * @param string $campo
     * @param array $filtro_esp
     * @return bool|array
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
     * P ORDER P INT PROBADO
     * @param array $data
     * @param string $tabla_renombrada
     * @return bool|array
     */
    public function valida_keys_renombre(array $data, string $tabla_renombrada): bool|array
    {
        if(!isset($data['enlace'])){
            return $this->error->error('Error data[enlace] debe existir', $data);
        }
        if(!isset($data['nombre_original'])){
            return $this->error->error('Error data[nombre_original] debe existir', $data);
        }
        $data['nombre_original'] = trim($data['nombre_original']);
        if($data['nombre_original'] === ''){
            return $this->error->error('Error data[nombre_original] no puede venir vacia', $data);
        }
        $tabla_renombrada = trim($tabla_renombrada);
        if($tabla_renombrada === ''){
            return $this->error->error('Error $tabla_renombrada no puede venir vacia', $tabla_renombrada);
        }
        return true;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $data
     * @param string $tabla
     * @return bool|array
     */
    public function valida_keys_sql(array $data, string $tabla): bool|array
    {
        if(!isset($data['key'])){
            return $this->error->error('Error data[key] debe existir en '.$tabla, $data);
        }
        if(!isset($data['enlace'])){
            return $this->error->error('Error data[enlace] debe existir', $data);
        }
        if(!isset($data['key_enlace'])){
            return $this->error->error('Error data[key_enlace] debe existir', $data);
        }
        $data['key'] = trim($data['key']);
        $data['enlace'] = trim($data['enlace']);
        $data['key_enlace'] = trim($data['key_enlace']);
        if($data['key'] === ''){
            return $this->error->error('Error data[key] esta vacio '.$tabla, $data);
        }
        if($data['enlace'] === ''){
            return $this->error->error('Error data[enlace] esta vacio '.$tabla, $data);
        }
        if($data['key_enlace'] === ''){
            return $this->error->error('Error data[key_enlace] esta vacio '.$tabla, $data);
        }
        return true;
    }

    /**
     * P INT P ORDER PROBADO
     * Valida que una expresion regular se cumpla en un registro
     * @param string $key campo de un registro o this->registro
     * @param array $registro
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
     */
    public function valida_pattern_campo(string $key, array $registro, string $tipo_campo):array|bool{
        if(count($registro) === 0){
            return $this->error->error('Error el registro no no puede venir vacio',  $registro);
        }
        $key = trim($key);
        if($key === ''){
            return $this->error->error('Error key esta vacio ',  $key);
        }
        if(isset($registro[$key])&&(string)$registro[$key] !==''){
            $valida_data = $this->valida_pattern_model(key:$key,registro: $registro, tipo_campo: $tipo_campo);
            if(errores::$error){
                return $this->error->error('Error al validar', $valida_data);
            }
        }

        return true;
    }


    /**
     * P ORDER P INT PROBADO
     * Valida que una expresion regular se cumpla en un registro
     * @param string $key campo de un registro o this->registro
     * @param array $registro
     * @param string $tipo_campo tipo de pattern a validar en this->patterns
     *
     * @return array|bool
     * @example
     *      $valida_data = $this->valida_pattern($key,$tipo_campo);
     *
     * @uses modelo_basico->valida_pattern_campo
     */
    private function valida_pattern_model(string $key, array $registro, string $tipo_campo):array|bool{

        $key = trim($key);
        if($key === ''){
            return $this->error->error('Error key esta vacio ',  $key);
        }
        if(!isset($registro[$key])){
            return $this->error->error('Error no existe el campo '.$key,  $registro);
        }
        if(!isset($this->patterns[$tipo_campo])){
            return $this->error->error('Error no existe el pattern '.$tipo_campo, $registro);
        }
        $value = trim($registro[$key]);
        $pattern = trim($this->patterns[$tipo_campo]);

        if(!preg_match($pattern, $value)){
            return $this->error->error('Error el campo '.$key.' es invalido', array($registro[$key],$pattern));
        }

        return true;
    }

    /** P ORDER P INT PROBADO
     * @param string $campo_renombrado
     * @param string $class
     * @param string $class_enlace
     * @param string $join
     * @param string $renombrada
     * @param string $tabla
     * @param string $tabla_enlace
     * @return bool|array
     */
    public function valida_renombres(string $campo_renombrado, string $class, string $class_enlace, string $join,
                                     string $renombrada, string $tabla, string $tabla_enlace): bool|array
    {
        if($tabla === ''){
            return$this->error->error('La tabla no puede ir vacia', $tabla);
        }
        if($join === ''){
            return $this->error->error('El join no puede ir vacio', $tabla);
        }
        if($renombrada === ''){
            return $this->error->error('El $renombrada no puede ir vacio', $tabla);
        }
        if($tabla_enlace === ''){
            return $this->error->error('El $tabla_enlace no puede ir vacio', $tabla);
        }
        if($campo_renombrado === ''){
            return $this->error->error('El $campo_renombrado no puede ir vacio', $tabla);
        }
        if(!class_exists($class)){
            return $this->error->error('El no existe el modelo '.$class, $class);
        }
        if(trim($join) !=='LEFT' && trim($join) !=='RIGHT' && trim($join) !=='INNER'){
            return $this->error->error('Error join invalido debe ser INNER, LEFT O RIGTH ', $join);
        }
        if(!class_exists($class_enlace)){
            return $this->error->error('El no existe el modelo '.$class_enlace, $class_enlace);
        }
        return true;
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $key
     * @param string $tabla_join
     * @return bool|array
     */
    public function valida_tabla_join(string $key, string $tabla_join ): bool|array
    {
        $key = trim($key);
        if(is_numeric($key)){
            return $this->error->error('Error el key no puede ser un numero', $key);
        }
        if($key === ''){
            return $this->error->error('Error key esta vacio', $key);
        }
        $tabla_join = trim($tabla_join);
        if(is_numeric($tabla_join)){
            return $this->error->error('Error el $tabla_join no puede ser un numero', $tabla_join);
        }
        if($tabla_join === ''){
            return $this->error->error('Error $tabla_join esta vacio', $tabla_join);
        }
        return true;
    }
}

<?php
namespace base\orm;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;
use ReflectionFunction;
use stdClass;


class where{

    public errores $error;
    public validacion $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * P ORDER P INT ERRORREV
     * @param string $filtro_fecha_sql
     * @return string
     */
    private function and_filtro_fecha(string $filtro_fecha_sql): string
    {
        $and = '';
        if($filtro_fecha_sql !== ''){
            $and = ' AND ';
        }
        return $and;
    }

    /**
     * P INT P ORDER ERROREV
     * @param string $sentencia
     * @param string $filtro_especial_sql
     * @param string $filtro_rango_sql
     * @param string $filtro_extra_sql
     * @param string $not_in_sql
     * @param string $sql_extra
     * @param string $filtro_fecha_sql
     * @return stdClass
     */
    #[Pure] private function asigna_data_filtro(string $filtro_especial_sql, string $filtro_extra_sql,
                                                string $filtro_fecha_sql, string $filtro_rango_sql, string $not_in_sql,
                                                string $sentencia, string $sql_extra): stdClass
    {
        $filtros = new stdClass();
        $filtros->sentencia = $sentencia ;
        $filtros->filtro_especial = $filtro_especial_sql;
        $filtros->filtro_rango = $filtro_rango_sql;
        $filtros->filtro_extra = $filtro_extra_sql;
        $filtros->not_in = $not_in_sql;
        $filtros->sql_extra = $sql_extra;
        $filtros->filtro_fecha = $filtro_fecha_sql;
        return $filtros;
    }

    /**
     * FULL
     * @param array|string|null $data dato para la asignacion de un nombre de un campo si es array debe ser
     * $data[(string)campo] sino un string
     * @param string $key valor de campo de asignacion de campo name si es un array data busca valor en data
     * @return string|array
     */
    private function campo(array|string|null $data, string $key):string|array{
        if($key === ''){
            return $this->error->error(mensaje: "Error key vacio",data:  $key, params: get_defined_vars());
        }
        $campo = $data['campo'] ?? $key;
        return addslashes($campo);
    }

    /**
     * FULL
     * @param array|string|null $data $data dato para la asignacion de un nombre de un campo si es array debe ser
     * $data[(string)campo] $data[(string)value] data[(string)comparacion] sino un string
     * @param string $default
     * @return string
     */
    private function comparacion(array|string|null $data, string $default):string{
        return $data['comparacion'] ?? $default;
    }

    /**
     * FULL
     * @param array $columnas_extra
     * @param array|string|null $data $data dato para la asignacion de un nombre de un campo si es array debe ser
     * $data[(string)campo] $data[(string)value] sino un string
     * @param string $key valor de campo de asignacion de campo name si es un array data busca valor en data
     * @return array|stdClass
     */
    private function comparacion_pura(array $columnas_extra, array|string|null $data, string $key):array|stdClass{

        if($key === ''){
            return $this->error->error(mensaje: "Error key vacio", data: $key, params: get_defined_vars());
        }
        if(is_array($data) && count($data) === 0){
            return $this->error->error(mensaje:"Error datos vacio",data: $data, params: get_defined_vars());
        }
        $datas = new stdClass();
        $datas->campo = $this->campo(data: $data,key:  $key);
        if(errores::$error){
            return $this->error->error(mensaje:"Error al maquetar campo",data: $datas->campo,
                params: get_defined_vars());
        }
        $datas->value = $this->value(data: $data);
        if(errores::$error){
            return $this->error->error(mensaje:"Error al validar maquetacion",data: $datas->value,
                params: get_defined_vars());
        }
        if(isset($data['es_sq']) && $data['es_sq']){
            $datas->campo = $columnas_extra[$key];
        }

        return $datas;
    }



    /**
     * P ORDER P INT ERRORREV
     * @param array $fil_fecha
     * @return stdClass|array
     */
    private function data_filtro_fecha(array $fil_fecha): stdClass|array
    {

        $valida = $this->valida_data_filtro_fecha(fil_fecha: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha',data: $valida, params: get_defined_vars());
        }

        $campo_1 = $fil_fecha['campo_1'];
        $campo_2 = $fil_fecha['campo_2'];
        $fecha = $fil_fecha['fecha'];
        $data = new stdClass();
        $data->campo_1 = $campo_1;
        $data->campo_2 = $campo_2;
        $data->fecha = $fecha;
        return $data;
    }

    /**
     * P INT P ORDER ERRROREV
     * @param array $columnas_extra
     * @param array $keys_data_filter
     * @param string $tipo_filtro
     * @param array $filtro
     * @param array $filtro_especial
     * @param array $filtro_rango
     * @param array $filtro_extra
     * @param array $not_in
     * @param string $sql_extra
     * @param array $filtro_fecha
     * @return array|stdClass
     */
    public function data_filtros_full(array $columnas_extra, array $filtro, array $filtro_especial, array $filtro_extra,
                                      array $filtro_fecha, array $filtro_rango, array $keys_data_filter, array $not_in,
                                      string $sql_extra, string $tipo_filtro): array|stdClass
    {
        $verifica_tf = $this->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf,
                params: get_defined_vars());
        }
        $filtros = $this->genera_filtros_sql(columnas_extra: $columnas_extra, filtro:  $filtro,
            filtro_especial:  $filtro_especial, filtro_extra:  $filtro_extra, filtro_rango:  $filtro_rango,
            keys_data_filter: $keys_data_filter, not_in: $not_in, sql_extra: $sql_extra, tipo_filtro: $tipo_filtro,
            filtro_fecha: $filtro_fecha);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtros', data:$filtros, params: get_defined_vars());
        }


        $where = $this->where(filtros: $filtros, keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar where',data:$where, params: get_defined_vars());
        }

        $filtros = $this->filtros_full(filtros: $filtros, keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtros',data:$filtros, params: get_defined_vars());
        }
        $filtros->where = $where;
        return $filtros;
    }

    /**
     * FULL
     * Genera las condiciones sql de un filtro especial
     *
     * @param array $filtro_especial //arreglo con las condiciones $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x')
     *
     * @example
     *      Ej 1
     *      $filtro_especial[0][tabla.campo]['operador'] = '>';
     *      $filtro_especial[0][tabla.campo]['valor'] = 'x';
     *
     *      $resultado = filtro_especial_sql($filtro_especial);
     *      $resultado =  tabla.campo > 'x'
     *
     *      Ej 2
     *      $filtro_especial[0][tabla.campo]['operador'] = '<';
     *      $filtro_especial[0][tabla.campo]['valor'] = 'x';
     *
     *      $resultado = filtro_especial_sql($filtro_especial);
     *      $resultado =  tabla.campo < 'x'
     *
     *      Ej 3
     *      $filtro_especial[0][tabla.campo]['operador'] = '<';
     *      $filtro_especial[0][tabla.campo]['valor'] = 'x';
     *
     *      $filtro_especial[1][tabla.campo2]['operador'] = '>=';
     *      $filtro_especial[1][tabla.campo2]['valor'] = 'x';
     *      $filtro_especial[1][tabla.campo2]['comparacion'] = 'OR ';
     *
     *      $resultado = filtro_especial_sql($filtro_especial);
     *      $resultado =  tabla.campo < 'x' OR tabla.campo2  >= x
     *
     *
     * @return array|string
     * @throws errores $filtro_especial_sql != '' $filtro_esp[$campo]['comparacion'] no existe, Debe existir $filtro_esp[$campo]['comparacion']
     * @throws errores $filtro_especial_sql != '' = $data_sql = '',  data_sql debe tener info
     */
    PUBLIC function filtro_especial_sql(array $filtro_especial):array|string{ //DEBUG

        $filtro_especial_sql = '';
        foreach ($filtro_especial as $campo=>$filtro_esp){
            if(!is_array($filtro_esp)){

                return $this->error->error(mensaje: "Error filtro debe ser un array filtro_especial[] = array()",
                    data: $filtro_esp, params: get_defined_vars());
            }

            $filtro_especial_sql = $this->obten_filtro_especial(filtro_esp: $filtro_esp,filtro_especial_sql: $filtro_especial_sql);
            if(errores::$error){
                return $this->error->error(mensaje:"Error filtro", data: $filtro_especial_sql,
                    params: get_defined_vars());
            }
        }
        return $filtro_especial_sql;
    }

    /**
     * FULL
     * Funcion que genera las condiciones de sql de un filtro extra
     *
     * @param array $filtro_extra arreglo que contiene las condiciones
     * $filtro_extra[0]['tabla.campo']=array('operador'=>'>','valor'=>'x','comparacion'=>'AND');
     * @example
     *      $filtro_extra[0][tabla.campo]['operador'] = '<';
     *      $filtro_extra[0][tabla.campo]['valor'] = 'x';
     *
     *      $filtro_extra[0][tabla2.campo]['operador'] = '>';
     *      $filtro_extra[0][tabla2.campo]['valor'] = 'x';
     *      $filtro_extra[0][tabla2.campo]['comparacion'] = 'OR';
     *
     *      $resultado = filtro_extra_sql($filtro_extra);
     *      $resultado =  tabla.campo < 'x' OR tabla2.campo > 'x'
     *
     * @return array|string
     * @uses filtro_and()
     *
     */
    private function filtro_extra_sql(array $filtro_extra):array|string{
        $filtro_extra_sql = '';
        foreach($filtro_extra as $data_filtro){
            if(!is_array($data_filtro)){
                return $this->error->error(mensaje: 'Error $data_filtro debe ser un array',data: $filtro_extra,
                    params: get_defined_vars());
            }
            $campo = key($data_filtro);
            $campo = trim($campo);

            if(!isset($data_filtro[$campo]['operador'])){
                return $this->error->error(mensaje:'Error data_filtro['.$campo.'][operador] debe existir',
                    data:$data_filtro, params: get_defined_vars());
            }

            $operador = $data_filtro[$campo]['operador'];
            if($operador===''){
                return $this->error->error(mensaje:'Error el operador debe de existir',data:$operador,
                    params: get_defined_vars());
            }

            if(!isset($data_filtro[$campo]['valor'])){
                return $this->error->error(mensaje:'Error data_filtro['.$campo.'][valor] debe existir',
                    data:$data_filtro, params: get_defined_vars());
            }
            if(!isset($data_filtro[$campo]['comparacion'])){
                return $this->error->error(mensaje:'Error data_filtro['.$campo.'][comparacion] debe existir',
                    data:$data_filtro, params: get_defined_vars());
            }

            $valor = $data_filtro[$campo]['valor'];
            if($valor===''){
                return $this->error->error(mensaje:'Error el operador debe de existir',data:$valor,
                    params: get_defined_vars());
            }
            $comparacion = $data_filtro[$campo]['comparacion'];
            $condicion = $campo.$operador.$valor;

            if($filtro_extra_sql === ''){
                $filtro_extra_sql .= $condicion;
            }
            else {
                $filtro_extra_sql .=  $comparacion . $condicion;
            }
        }

        return $filtro_extra_sql;
    }

    /**
     * P ORDER P INT ERROREV
     * @param array $filtro_fecha
     * @return array|string
     */
    private function filtro_fecha(array $filtro_fecha):array|string{


        $filtro_fecha_sql = $this->filtro_fecha_base(filtro_fecha: $filtro_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sql',data: $filtro_fecha_sql,
                params: get_defined_vars());
        }

        if($filtro_fecha_sql !==''){
            $filtro_fecha_sql = "($filtro_fecha_sql)";
        }

        return $filtro_fecha_sql;
    }

    /**
     * P ORDER P INT ERRORREV
     * @param array $filtro_fecha
     * @return array|string
     */
    private function filtro_fecha_base(array $filtro_fecha): array|string
    {
        $filtro_fecha_sql = '';
        foreach ($filtro_fecha as $fil_fecha){
            if(!is_array($fil_fecha)){
                return $this->error->error(mensaje: 'Error $fil_fecha debe ser un array',data: $fil_fecha,
                    params: get_defined_vars());
            }

            $valida = $this->valida_filtro_fecha(fil_fecha: $fil_fecha);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar filtro',data: $valida, params: get_defined_vars());
            }

            $sql = $this->genera_sql_filtro_fecha(fil_fecha: $fil_fecha, filtro_fecha_sql: $filtro_fecha_sql);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener sql',data: $sql, params: get_defined_vars());
            }

            $filtro_fecha_sql.= $sql;

        }
        return $filtro_fecha_sql;
    }

    /**
     * FULL
     * Devuelve un conjunto de condiciones de tipo BETWEEN en forma de sql
     *
     * @param array $filtro_rango
     *                  Opcion1.- Debe ser un array con la siguiente forma array('valor1'=>'valor','valor2'=>'valor')
     *                  Opcion2.-
     *                      Debe ser un array con la siguiente forma
     *                          array('valor1'=>'valor','valor2'=>'valor','valor_campo'=>true)
     * @example
     *      $entrada = array();
     *      $resultado = filtro_rango_sql($entrada)
     *      //return = string ''
     *      $entrada['x'] = array('''valor1'=>'1','valor2=>2);
     *      $resultado = filtro_rango_sql($entrada)
     *      //return string x = BETWEEN '1' AND '2'
     *      $entrada['x'] = array('''valor1'=>'1','valor2=>2,'valor_campo'=>true);
     *      $resultado = filtro_rango_sql($entrada)
     *      //return string 'x' = BETWEEN 1 AND 2
     *      $entrada['x'] = array('''valor1'=>'1','valor2=>2,'valor_campo'=>true);
     *      $entrada['y'] = array('''valor1'=>'2','valor2=>3,'valor_campo'=>false);
     *      $entrada['z'] = array('''valor1'=>'4','valor2=>5);
     *      $resultado = filtro_rango_sql($entrada)
     *      //return string 'x' = BETWEEN 1 AND 2 AND y BETWEEN 2 AND 3 AND z BETWEEN 4 AND 5
     * @return array|string
     * @throws errores Si $filtro_rango[0] != array
     * @throws errores Si filtro[0] = array('valor1'=>'1') Debe existir valor2
     * @throws errores Si filtro[0] = array('valor2'=>'1') Debe existir valor1
     * @throws errores Si filtro[0] = array('valor1'=>'1','valor2'=>'2') key debe ser tabla.campo error sql
     */
    private function filtro_rango_sql(array $filtro_rango):array|string{//DOC DEBUG
        $filtro_rango_sql = '';
        foreach ($filtro_rango as $campo=>$filtro){
            if(!is_array($filtro)){
                return  $this->error->error(mensaje: 'Error $filtro debe ser un array',data: $filtro,
                    params: get_defined_vars());
            }
            if(!isset($filtro['valor1'])){
                return  $this->error->error(mensaje:'Error $filtro[valor1] debe existir',data:$filtro,
                    params: get_defined_vars());
            }
            if(!isset($filtro['valor2'])){
                return  $this->error->error(mensaje:'Error $filtro[valor2] debe existir',data:$filtro,
                    params: get_defined_vars());
            }
            $campo = trim($campo);
            if(is_numeric($campo)){
                return  $this->error->error(mensaje:'Error campo debe ser un string',data:$campo,
                    params: get_defined_vars());
            }
            $valor_campo = false;

            if(isset($filtro['valor_campo']) && $filtro['valor_campo']){
                $valor_campo = true;
            }
            $filtro_rango_sql = $this->genera_filtro_rango_base(campo: $campo,filtro: $filtro,
                filtro_rango_sql: $filtro_rango_sql,valor_campo: $valor_campo);
            if(errores::$error){
                return  $this->error->error(mensaje:'Error $filtro_rango_sql al generar',data:$filtro_rango_sql,
                    params: get_defined_vars());
            }
        }

        return $filtro_rango_sql;
    }

    /**
     * P ORDER P INT ERRORREV
     * @param stdClass $filtros
     * @param array $keys_data_filter
     * @return stdClass
     */
    private function filtros_full(stdClass $filtros, array $keys_data_filter): stdClass
    {
        $filtros = $this->limpia_filtros(filtros: $filtros, keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar filtros',data: $filtros, params: get_defined_vars());
        }

        $and = '';
        foreach ($keys_data_filter as $key){
            if($filtros->$key !=='') {
                $filtros->$key = " $and ( " . $filtros->$key . ")";
                $and = " AND ";
            }
        }

        return $filtros;
    }


    /**
     * P ORDER P INT ERRREV
     * @param stdClass $complemento
     * @param array $keys_data_filter
     * @return bool
     */
    private function filtros_vacios(stdClass $complemento, array $keys_data_filter): bool
    {
        $filtros_vacios = true;
        foreach ($keys_data_filter as $key) {
            if(!isset($complemento->$key)){
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
     * FULL
     * Devuelve un conjunto de condiciones de tipo AND en forma de sql
     * @param array $columnas_extra
     * @param array $filtro parametros para maquetar filtro[data] =  $data $data dato para la asignacion de un nombre de un campo si es array debe ser
     * $data[(string)campo] $data[(string)value] data[(string)comparacion] sino un string
     * @return array|string
     * @example
     *      $sentencia = $this->genera_and();
     * if(isset($sentencia['error'])){
     * return $this->error->error('Error al generar and',__LINE__,
     * __FILE__,$sentencia);
     * }
     * $consulta = "DELETE FROM $tabla WHERE $sentencia";
     * @uses $this->genera_sentencia_base
     * @uses $this->elimina_con_filtro_and
     * @uses $this->modifica_con_filtro_and
     * @uses $this->suma
     */
    public function genera_and(array $columnas_extra, array $filtro):array|string{ //DEBUG
        $sentencia = '';
        foreach ($filtro as $key => $data) {
            if(is_numeric($key)){
                return $this->error->error(
                    mensaje: 'Los key deben de ser campos asociativos con referencia a tabla.campo',data: $filtro,
                    params: get_defined_vars());
            }
            $data_comparacion = $this->comparacion_pura(columnas_extra: $columnas_extra, data: $data, key: $key);
            if(errores::$error){
                return $this->error->error(mensaje:"Error al maquetar campo",data:$data_comparacion,
                    params: get_defined_vars());
            }

            $comparacion = $this->comparacion(data: $data,default: '=');
            if(errores::$error){
                return $this->error->error(mensaje:"Error al maquetar",data:$comparacion,
                    params: get_defined_vars());
            }

            $operador = $data['operador'] ?? ' AND ';
            if(trim($operador) !=='AND' && trim($operador) !=='OR'){
                return $this->error->error(mensaje:'El operador debe ser AND u OR',data:$operador,
                    params: get_defined_vars());
            }

            $data_sql = "$data_comparacion->campo $comparacion '$data_comparacion->value'";

            $sentencia .= $sentencia === ''? $data_sql :" $operador $data_sql";
        }

        return $sentencia;

    }

    /**
     * FULL
     * Devuelve un conjunto de condiciones de tipo AND en forma de sql  con LIKE
     * @param array $columnas_extra
     * @param array $filtro filtros para la maquetacion de filtros
     * @return array|string
     * @example
     *      $sentencia = $this->genera_and_textos($this->filtro);
     * @uses modelo_basico
     */
    private function genera_and_textos(array $columnas_extra, array $filtro):array|string{

        $sentencia = '';
        foreach ($filtro as $key => $data) {
            if(is_numeric($key)){
                return $this->error->error(
                    mensaje: 'Los key deben de ser campos asociativos con referencia a tabla.campo',data: $filtro,
                    params: get_defined_vars());
            }

            $data_comparacion = $this->comparacion_pura(columnas_extra: $columnas_extra, data: $data,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: "Error al maquetar",data:$data_comparacion,
                    params: get_defined_vars());
            }

            $comparacion = $this->comparacion(data: $data,default: 'LIKE');
            if(errores::$error){
                return $this->error->error(mensaje:"Error al maquetar",data:$comparacion, params: get_defined_vars());
            }

            $txt = '%';
            $operador = 'AND';
            if(isset($data['operador']) && $data['operador']!==''){
                $operador = $data['operador'];
                $txt= '';
            }

            $sentencia .= $sentencia === ""?"$data_comparacion->campo $comparacion '$txt$data_comparacion->value$txt'":
                " $operador $data_comparacion->campo $comparacion '$txt$data_comparacion->value$txt'";
        }


        return $sentencia;

    }

    /**
     * FULL
     * Genera la condicion sql de un filtro especial
     *
     *
     *
     * @param string $filtro_especial_sql //condicion en forma de sql
     * @param string $data_sql //condicion en forma de sql
     * @param array $filtro_esp //array con datos del filtro array('tabla.campo','AND')
     * @param string  $campo //string con el nombre del campo
     *
     * @example
     *      Ej 1
     *      $filtro_especial_sql = '';
     *      $data_sql = '';
     *      $filtro_esp = array();
     *      $campo = '';
     *      $resultado = genera_filtro_especial($filtro_especial_sql, $data_sql,$filtro_esp,$campo);
     *      $resultado = string vacio
     *
     *
     *      Ej 2
     *      $filtro_especial_sql = 'tabla.campo = 1';
     *      $data_sql = 'tabla.campo2 = 1';
     *      $filtro_esp['tabla.campo2']['comparacion'] = 'OR'
     *      $campo = 'tabla.campo2';
     *      $resultado = genera_filtro_especial($filtro_especial_sql, $data_sql,$filtro_esp,$campo);
     *      $resultado = tabla.campo = 1 OR tabla.campo2 = 1
     *
     *      Ej 3
     *      $filtro_especial_sql = 'tabla.campo = 1';
     *      $data_sql = 'tabla.campo2 = 1';
     *      $filtro_esp['tabla.campo2']['comparacion'] = 'AND'
     *      $campo = 'tabla.campo2';
     *      $resultado = genera_filtro_especial($filtro_especial_sql, $data_sql,$filtro_esp,$campo);
     *      $resultado = tabla.campo = 1 AND tabla.campo2 = 1
     *
     *
     * @return array|string
     * @throws errores $filtro_especial_sql != '' $filtro_esp[$campo]['comparacion'] no existe, Debe existir $filtro_esp[$campo]['comparacion']
     * @throws errores $filtro_especial_sql != '' = $data_sql = '',  data_sql debe tener info
     */

    private function genera_filtro_especial(string $campo, string $data_sql, array $filtro_esp,
                                            string $filtro_especial_sql):array|string{//FIN //DEBUG
        if($filtro_especial_sql === ''){
            $filtro_especial_sql .= $data_sql;
        }
        else{
            if(!isset($filtro_esp[$campo]['comparacion'])){
                return $this->error->error(mensaje: 'Error $filtro_esp[$campo][\'comparacion\'] debe existir',
                    data: $filtro_esp, params: get_defined_vars());
            }
            if(trim($data_sql) === ''){
                return $this->error->error(mensaje:'Error $data_sql no puede venir vacio', data:$data_sql,
                    params: get_defined_vars());
            }

            $filtro_especial_sql .= ' '.$filtro_esp[$campo]['comparacion'].' '.$data_sql;
        }

        return $filtro_especial_sql;
    }

    /**
     * FULL
     * Devuelve una condicion en forma de sql validando si se tiene que precragar un AND o solo la sentencia
     *
     * @param string $campo
     *                  Opcion 1.-Si valor_es_campo = false,
     *                      El valor definido debe ser un campo de la base de datos con la siguiente forma tabla.campo
     *                  Opcion 2.-Si valor_es_campo = true,
     *                      El valor definido debe ser un valor del registro del rango a buscar
     *
     * @param array $filtro Debe ser un array con la siguiente forma array('valor1'=>'valor','valor2'=>'valor')
     * @param string $filtro_rango_sql debe ser un sql con una condicion
     * @param bool $valor_campo
     *                  Opcion1.- true, Es utilizado para definir el campo para una comparacion como valor
     *                  Opcion2.- false, Es utilizado para definir el campo a comparar el rango de valores
     * @example
     *      $resultado = genera_filtro_rango_base('',array(),'');
     *      //return = array errores
     *      $resultado = genera_filtro_rango_base('x',array(),'');
     *      //return = array errores
     *      $resultado = genera_filtro_rango_base('x',array('valor1'=>x,'valor2'=>'y'),'');
     *      //return = string 'x BETWEEN 'x' AND 'y' ;
     *      $resultado = genera_filtro_rango_base('x',array('valor1'=>x,'valor2'=>'y'),'tabla.campo = 1');
     *      //return = string tabla.campo = 1 AND  x BETWEEN 'x' AND 'y' ;
     *      $resultado = genera_filtro_rango_base('x',array('valor1'=>x,'valor2'=>'y'),'tabla.campo = 1',true);
     *      //return = string tabla.campo = 1 AND  'x' BETWEEN x AND y ;
     * @return array|string
     * @throws errores Si $campo = vacio
     * @throws errores Si filtro[valor1] = vacio
     * @throws errores Si filtro[valor2] = vacio
     */
    PUBLIC function genera_filtro_rango_base(string $campo, array $filtro, string $filtro_rango_sql,
                                              bool $valor_campo = false):array|string{ //DOC DEBUG
        $campo = trim($campo);
        if($campo === ''){
            return  $this->error->error(mensaje: 'Error $campo no puede venir vacio',data: $campo,
                params: get_defined_vars());
        }
        $keys = array('valor1','valor2');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $filtro);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al validar filtro',data: $valida,
                params: get_defined_vars());
        }

        $condicion = $campo . ' BETWEEN ' ."'" .$filtro['valor1'] . "'"." AND "."'".$filtro['valor2'] . "'";

        if($valor_campo){
            $condicion = "'".$campo."'" . ' BETWEEN '  .$filtro['valor1'] ." AND ".$filtro['valor2'];
        }
        $filtro_rango_sql_r = $this->setea_filtro_rango(condicion: $condicion, filtro_rango_sql: $filtro_rango_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error $filtro_rango_sql al setear',data: $filtro_rango_sql_r,
                params: get_defined_vars());
        }

        return $filtro_rango_sql_r;
    }

    /**
     * P INT P ORDER ERROREV
     * @param string $sentencia
     * @param string $filtro_especial_sql
     * @param string $filtro_rango_sql
     * @param string $filtro_extra_sql
     * @param string $not_in_sql
     * @param array $keys_data_filter
     * @param string $sql_extra
     * @param string $filtro_fecha_sql
     * @return array|stdClass
     */
    private function genera_filtros_iniciales(string $filtro_especial_sql, string $filtro_extra_sql,
                                              string $filtro_rango_sql, array $keys_data_filter, string $not_in_sql,
                                              string $sentencia, string $sql_extra, string $filtro_fecha_sql = ''): array|stdClass
    {
        $filtros = $this->asigna_data_filtro(filtro_especial_sql:  $filtro_especial_sql,
            filtro_extra_sql: $filtro_extra_sql, filtro_fecha_sql:  $filtro_fecha_sql,
            filtro_rango_sql:  $filtro_rango_sql, not_in_sql: $not_in_sql,sentencia: $sentencia, sql_extra:  $sql_extra);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar filtros',data: $filtros, params: get_defined_vars());
        }

        $filtros = $this->limpia_filtros(filtros: $filtros, keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al limpiar filtros',data:$filtros, params: get_defined_vars());
        }

        $filtros = $this->parentesis_filtro(filtros: $filtros,keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtros',data:$filtros, params: get_defined_vars());
        }
        return $filtros;
    }

    /**
     * P INT P ORDER ERRROEV
     * @param array $columnas_extra
     * @param array $keys_data_filter
     * @param string $tipo_filtro
     * @param array $filtro
     * @param array $filtro_especial
     * @param array $filtro_rango
     * @param array $filtro_extra
     * @param array $not_in
     * @param string $sql_extra
     * @param array $filtro_fecha
     * @return array|stdClass
     */
    private function genera_filtros_sql(array $columnas_extra, array $filtro, array $filtro_especial,
                                        array $filtro_extra, array $filtro_rango, array $keys_data_filter,
                                        array $not_in, string $sql_extra, string $tipo_filtro,
                                        array $filtro_fecha = array()): array|stdClass
    {
        $verifica_tf = $this->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf,
                params: get_defined_vars());
        }
        $sentencia = $this->genera_sentencia_base(columnas_extra: $columnas_extra, filtro: $filtro,
            tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sentencia', data:$sentencia,
                params: get_defined_vars());
        }

        $filtro_especial_sql = $this->filtro_especial_sql(filtro_especial: $filtro_especial);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtro',data: $filtro_especial_sql,
                params: get_defined_vars());
        }
        $filtro_rango_sql = $this->filtro_rango_sql(filtro_rango: $filtro_rango);
        if(errores::$error){
            return $this->error->error(mensaje:'Error $filtro_rango_sql al generar',data:$filtro_rango_sql,
                params: get_defined_vars());
        }
        $filtro_extra_sql = $this->filtro_extra_sql(filtro_extra: $filtro_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtro extra',data:$filtro_extra_sql,
                params: get_defined_vars());
        }

        $not_in_sql = $this->genera_not_in_sql(not_in: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data:$not_in_sql, params: get_defined_vars());
        }

        $filtro_fecha_sql = $this->filtro_fecha(filtro_fecha: $filtro_fecha);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtro_fecha',data:$filtro_fecha_sql,
                params: get_defined_vars());
        }

        $filtros = $this->genera_filtros_iniciales(filtro_especial_sql:  $filtro_especial_sql,
            filtro_extra_sql: $filtro_extra_sql, filtro_rango_sql: $filtro_rango_sql,
            keys_data_filter:  $keys_data_filter,not_in_sql:  $not_in_sql, sentencia: $sentencia,
            sql_extra:  $sql_extra,filtro_fecha_sql:  $filtro_fecha_sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtros',data:$filtros, params: get_defined_vars());
        }


        return $filtros;

    }

    /**
     * P INT P ORDER ERRORREV
     * @param array $not_in
     * @return array|string
     */
    private function genera_not_in(array $not_in): array|string
    {
        $keys = array('llave','values');
        $valida = $this->validacion->valida_existencia_keys( keys:$keys, registro: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar not_in',data: $valida, params: get_defined_vars());
        }

        $llave = $not_in['llave'];
        $values = $not_in['values'];

        if(!is_array($values)){
            return $this->error->error(mensaje: 'Error values debe ser un array',data: $values,
                params: get_defined_vars());
        }

        $not_in_sql = $this->not_in_sql(llave:  $llave, values:$values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $not_in_sql, params: get_defined_vars());
        }
        return $not_in_sql;
    }

    /**
     * P INT P ORDER ERRORREV
     * @param array $not_in
     * @return array|string
     */
    private function genera_not_in_sql(array $not_in): array|string
    {
        $not_in_sql = '';
        if(count($not_in)>0){
            $keys = array('llave','values');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $not_in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar not_in',data: $valida, params: get_defined_vars());
            }
            $not_in_sql = $this->genera_not_in(not_in: $not_in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql',data: $not_in_sql, params: get_defined_vars());
            }

        }
        return $not_in_sql;
    }

    /**
     * FULL
     * Devuelve un conjunto de condiciones de tipo AND en forma de sql  con LIKE o =
     * @param string $tipo_filtro numeros = textos LIKE
     * @param array $filtro parametros para generar sentencia
     * @return array|string con sentenccia en SQL
     * @throws errores $this->filtro[key] es un numero
     * @example
     *      $sentencia = $this->genera_sentencia_base($tipo_filtro);
     * @uses modelo
     */
    private function genera_sentencia_base(array $columnas_extra,  array $filtro, string $tipo_filtro):array|string{
        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf,
                params: get_defined_vars());
        }
        $sentencia = '';
        if($tipo_filtro === 'numeros') {
            $sentencia = $this->genera_and(columnas_extra: $columnas_extra, filtro: $filtro);
            if(errores::$error){
                return $this->error->error(mensaje: "Error en and",data:$sentencia, params: get_defined_vars());
            }
        }
        elseif ($tipo_filtro==='textos'){
            $sentencia = $this->genera_and_textos(columnas_extra: $columnas_extra,filtro: $filtro);
            if(errores::$error){
                return $this->error->error(mensaje: "Error en texto",data:$sentencia, params: get_defined_vars());
            }
        }
        return $sentencia;
    }

    /**
     * P ORDER P INT ERRORREV
     * @param array $fil_fecha
     * @param string $filtro_fecha_sql
     * @return array|string
     */
    private function genera_sql_filtro_fecha(array $fil_fecha, string $filtro_fecha_sql): array|string
    {
        $valida = $this->valida_data_filtro_fecha(fil_fecha: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha',data: $valida, params: get_defined_vars());
        }

        $data = $this->data_filtro_fecha(fil_fecha: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar datos',data:$data, params: get_defined_vars());
        }

        $and = $this->and_filtro_fecha(filtro_fecha_sql: $filtro_fecha_sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener and',data:$and, params: get_defined_vars());
        }

        $sql = $this->sql_fecha(and:$and,data:  $data);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener sql',data:$sql, params: get_defined_vars());
        }
        return $sql;
    }

    /**
     * P ORDER P INT ERROREV
     * @param stdClass $complemento
     * @param array $keys_data_filter
     * @return array|stdClass
     */
    public function init_params_sql(stdClass $complemento, array $keys_data_filter): array|stdClass
    {
        $complemento_w = $this->where_filtro(complemento: $complemento,key_data_filter:  $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error ajustar where',data: $complemento_w, params: get_defined_vars());
        }

        $complemento_r = (new inicializacion())->ajusta_params(complemento: $complemento_w);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al inicializar params',data:$complemento_r,
                params: get_defined_vars());
        }
        return $complemento_r;
    }

    /**
     * P INT P ORDER ERRORREV
     * @param stdClass $filtros
     * @param array $keys_data_filter
     * @return stdClass
     */
    public function limpia_filtros(stdClass $filtros, array $keys_data_filter): stdClass
    {
        foreach($keys_data_filter as $key){
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
     * FULL
     * Genera la condicion sql de un filtro especial
     *
     *
     *
     * @param string $campo campo de una tabla tabla.campo
     * @param array $filtro filtro a validar
     *
     * @example
     *      Ej 1
     *      $campo = 'x';
     *      $filtro['x'] = array('operador'=>'x','valor'=>'x');
     *      $resultado = maqueta_filtro_especial($campo, $filtro);
     *      $resultado = x>'x'
     *
     *      Ej 2
     *      $campo = 'x';
     *      $filtro['x'] = array('operador'=>'x','valor'=>'x','es_campo'=>true);
     *      $resultado = maqueta_filtro_especial($campo, $filtro);
     *      $resultado = 'x'> x
     *
     * @return array|string
     * @throws errores $campo = '', Campo no puede venir vacio
     * @throws errores $campo = int cualquier numero,  Campo no puede ser un numero
     * @throws errores $filtro = array(), filtro[operador] debe existir
     * @throws errores $filtro = array('operador'=>'x'), filtro[valor] debe existir
     * @uses modelo_basico->obten_filtro_especial
     */
    private function maqueta_filtro_especial(string $campo, array $filtro):array|string{
        $campo = trim($campo);

        $valida = (new validaciones())->valida_data_filtro_especial(campo: $campo,filtro:  $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro', data: $valida, params: get_defined_vars());
        }

        $keys = array('valor');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $filtro[$campo]);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar filtro',  data:$valida, params: get_defined_vars());
        }

        $data_sql = $campo . $filtro[$campo]['operador'] . "'" . $filtro[$campo]['valor'] . "'";

        if(isset($filtro[$campo]['valor_es_campo']) && $filtro[$campo]['valor_es_campo']){

            $data_sql = "'".$campo."'".$filtro[$campo]['operador'].$filtro[$campo]['valor'];
        }

        return $data_sql;
    }

    /**
     * FULL
     * @param array $values
     * @param string $llave
     * @return array|string
     */
    private function not_in_sql(string $llave, array $values): array|string
    {
        $llave = trim($llave);
        if($llave === ''){
            return $this->error->error(mensaje: 'Error la llave esta vacia',data: $llave, params: get_defined_vars());
        }

        $not_in_sql = '';
        $values_sql = $this->values_sql_in(values:$values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $values_sql, params: get_defined_vars());
        }

        if($values_sql!==''){
            $not_in_sql.="$llave NOT IN ($values_sql)";
        }

        return $not_in_sql;
    }

    /**
     * FULL
     * Genera la condicion sql de un filtro especial
     *
     * @param string $filtro_especial_sql //condicion en forma de sql
     * @param array $filtro_esp //array con datos del filtro $filtro_esp[tabla.campo]= array('operador'=>'AND','valor'=>'x');
     *
     * @example
     *      Ej 1
     *      $filtro_esp[tabla.campo]['operador'] = '>';
     *      $filtro_esp[tabla.campo]['valor'] = 'x';
     *      $filtro_especial_sql = '';
     *      $resultado = obten_filtro_especial($filtro_esp, $filtro_especial_sql);
     *      $resultado =  tabla.campo > 'x'
     *
     *      Ej 2
     *      $filtro_esp[tabla.campo]['operador'] = '>';
     *      $filtro_esp[tabla.campo]['valor'] = 'x';
     *      $filtro_esp[tabla.campo]['comparacion'] = ' AND ';
     *      $filtro_especial_sql = ' tabla.campo2 = 1';
     *      $resultado = obten_filtro_especial($filtro_esp, $filtro_especial_sql);
     *      $resultado =  tabla.campo > 'x' AND tabla.campo2 = 1
     *
     *
     * @return array|string
     * @throws errores $filtro_especial_sql != '' $filtro_esp[$campo]['comparacion'] no existe, Debe existir $filtro_esp[$campo]['comparacion']
     * @throws errores $filtro_especial_sql != '' = $data_sql = '',  data_sql debe tener info
     * @throws errores $filtro_esp[$campo] != array() $filtro_esp[$campo] debe ser un array
     */

    private function obten_filtro_especial(array $filtro_esp, string $filtro_especial_sql):array|string{
        $campo = key($filtro_esp);
        $campo = trim($campo);

        $valida =(new validaciones())->valida_data_filtro_especial(campo: $campo,filtro:  $filtro_esp);
        if(errores::$error){
            return $this->error->error(mensaje: "Error en filtro ", data: $valida, params: get_defined_vars());
        }
        $data_sql = $this->maqueta_filtro_especial(campo: $campo,filtro: $filtro_esp);
        if(errores::$error){
            return $this->error->error(mensaje:"Error filtro", data:$data_sql, params: get_defined_vars());
        }
        $filtro_especial_sql_r = $this->genera_filtro_especial(campo:  $campo, data_sql: $data_sql,
            filtro_esp: $filtro_esp, filtro_especial_sql: $filtro_especial_sql);
        if(errores::$error){
            return $this->error->error(mensaje:"Error filtro",data: $filtro_especial_sql_r, params: get_defined_vars());
        }

        return $filtro_especial_sql_r;
    }

    /**
     * P INT P ORDER ERROREV
     * @param stdClass $filtros
     * @param array $keys_data_filter
     * @return stdClass|array
     */
    private function parentesis_filtro(stdClass $filtros, array $keys_data_filter): stdClass|array
    {
        $filtros = $this->limpia_filtros(filtros: $filtros, keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar filtros', data: $filtros, params: get_defined_vars());
        }

        foreach($keys_data_filter as $key){
            if($filtros->$key!==''){
                $filtros->$key = ' ('.$filtros->$key.') ';
            }
        }


        return $filtros;
    }

    /**
     * FULL
     * Devuelve una condicion en forma de sql validando si se tiene que precragar un AND o solo la sentencia
     *
     * @access public
     * @param string $filtro_rango_sql debe ser un sql con una condicion
     * @param string $condicion debe ser un sql con una condicion
     * @example
     *      $filtro = setea_filtro_rango('','');
     *      //return = string ''
     *      $filtro = setea_filtro_rango('var1 = 1','');
     *      //return = array errores
     *      $filtro = setea_filtro_rango('var1 = 1','var2 = 2');
     *      //return = string 'var1 = 1 AND var2 = 2'
     *      $filtro = setea_filtro_rango('','var2 = 2');
     *      //return = string 'var2 = 2'
     * @return array|string
     * @throws errores Si $filtro_rango_sql es diferente de vacio y condicion es igual a vacio
     */
    private function setea_filtro_rango(string $condicion, string $filtro_rango_sql):array|string{
        $filtro_rango_sql = trim($filtro_rango_sql);
        $condicion = trim($condicion);

        if(trim($filtro_rango_sql) !=='' && trim($condicion) === ''){

            return  $this->error->error(
                'Error if filtro_rango tiene info $condicion no puede venir vacio',get_defined_vars(),
                get_defined_vars());
        }

        $and = '';
        if($filtro_rango_sql !==''){
            $and = ' AND ';
        }

        $filtro_rango_sql.= $and.$condicion;

        return $filtro_rango_sql;
    }

    /**
     * P ORDER P INT ERRORREV
     * @param string $and
     * @param stdClass $data
     * @return string|array
     */
    private function sql_fecha(string $and, stdClass $data): string|array
    {
        $keys = array('fecha','campo_1','campo_2');
        foreach($keys as $key){
            if(!isset($data->$key)){
                return $this->error->error(mensaje: 'error no existe $data->'.$key, data: $data,
                    params: get_defined_vars());
            }
            if(trim($data->$key) === ''){
                return $this->error->error(mensaje:'error esta vacio $data->'.$key, data:$data,
                    params: get_defined_vars());
            }
        }
        $keys = array('fecha');
        foreach($keys as $key){
            $valida = $this->validacion->valida_fecha(fecha: $data->$key);
            if(errores::$error){
                return $this->error->error(mensaje:'error al validar '.$key,data: $valida, params: get_defined_vars());
            }
        }

        return "$and('$data->fecha' >= $data->campo_1 AND '$data->fecha' <= $data->campo_2)";
    }

    /**
     * P ORDER P INT ERRORREV
     * @param array $fil_fecha
     * @return bool|array
     */
    private function valida_data_filtro_fecha(array $fil_fecha): bool|array
    {
        $keys = array('campo_1','campo_2','fecha');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida, params: get_defined_vars());
        }
        $valida = $this->validacion->valida_fecha(fecha: $fil_fecha['fecha']);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar fecha',data:$valida, params: get_defined_vars());
        }
        return true;
    }

    /**
     * P ORDER P INT ERRORREV
     * @param array $fil_fecha
     * @return bool|array
     */
    private function valida_filtro_fecha(array $fil_fecha): bool|array
    {

        $keys = array('campo_1','campo_2','fecha');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida, params: get_defined_vars());
        }

        $keys = array('fecha');
        $valida = $this->validacion->fechas_in_array(data:  $fil_fecha, keys: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida, params: get_defined_vars());
        }
        return true;
    }



    /**
     * Verifica que un tipo filtro sea valido
     * @version 1.0.0
     * @param string $tipo_filtro validos son numeros y textos
     * @return bool|array
     */
    public function verifica_tipo_filtro(string $tipo_filtro): bool|array
    {
        $tipo_filtro = trim($tipo_filtro);
        if($tipo_filtro === ''){
            $tipo_filtro = 'numeros';
        }
        $tipos_permitidos = array('numeros','textos');
        if(!in_array($tipo_filtro,$tipos_permitidos)){
            return $this->error->error(
                mensaje: 'Error el tipo filtro no es correcto los filtros pueden ser o numeros o textos',
                data: $tipo_filtro);
        }
        return true;
    }

    /**
     * FULL
     * @param array|string|null $data dato para la asignacion de un nombre de un campo si es array debe ser
     * $data[(string)campo] $data[(string)value] sino un string
     * @return string|array
     */
    private function value(array|string|null $data):string|array{
        $value = $data;
        if(is_array($data) && isset($data['value'])){
            $value = trim($data['value']);
        }
        if(is_array($data) && count($data) === 0){
            return $this->error->error(mensaje: "Error datos vacio",data: $data, params: get_defined_vars());
        }
        if(is_array($data) && !isset($data['value'])){
            return $this->error->error(mensaje:"Error no existe valor",data: $data, params: get_defined_vars());
        }
        return addslashes($value);
    }

    /**
     * FULL
     * @param string $value
     * @param string $values_sql
     * @return array|stdClass
     */
    private function value_coma(string $value, string $values_sql): array|stdClass
    {
        $values_sql = trim($values_sql);
        $value = trim($value);
        if($value === ''){
            return $this->error->error(mensaje: 'Error value esta vacio',data: $value, params: get_defined_vars());
        }

        $coma = '';
        if($values_sql !== ''){
            $coma = ' ,';
        }

        $data = new stdClass();
        $data->value = $value;
        $data->coma = $coma;
        return $data;
    }

    /**
     * FULL
     * @param array $values
     * @return string|array
     */
    private function values_sql_in(array $values): string|array
    {
        $values_sql = '';
        foreach ($values as $value){
            $data = $this->value_coma(value:$value, values_sql: $values_sql);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error obtener datos de value',data: $data,
                    params: get_defined_vars());
            }
            $values_sql.="$data->coma$data->value";
        }
        return $values_sql;
    }

    /**
     * P ORDER P INT ERROREV
     * @param stdClass $complemento
     * @param array $key_data_filter
     * @return bool|array
     */
    private function verifica_where(stdClass $complemento, array $key_data_filter): bool|array
    {
        if(!isset($complemento->where)){
            $complemento->where = '';
        }
        if($complemento->where!==''){
            $filtros_vacios = $this->filtros_vacios(complemento: $complemento, keys_data_filter: $key_data_filter);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error validar filtros',data: $filtros_vacios,
                    params: get_defined_vars());
            }
            if($filtros_vacios){
                return $this->error->error(mensaje: 'Error si existe where debe haber al menos un filtro',
                    data: $complemento, params: get_defined_vars());
            }
        }
        return true;
    }

    /**
     * P INT P ORDER ERRROREV
     * @param stdClass $filtros
     * @param array $keys_data_filter
     * @return string
     */
    private function where(stdClass $filtros, array $keys_data_filter): string
    {

        $filtros = $this->limpia_filtros(filtros: $filtros,keys_data_filter:  $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar filtros', data: $filtros, params: get_defined_vars());
        }
        $where='';
        foreach($keys_data_filter as $key){
            if($filtros->$key!==''){
                $where = " WHERE ";
            }
        }

        return $where;
    }

    /**
     * P ORDER P INT ERRORREV
     * @param stdClass $complemento
     * @return array|stdClass
     */
    private function where_base(stdClass $complemento): array|stdClass
    {
        if(!isset($complemento->where)){
            $complemento->where = '';
        }
        $complemento_r = $this->where_mayus(complemento: $complemento);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error ajustar where',data: $complemento_r, params: get_defined_vars());
        }
        return $complemento_r;
    }

    /**
     * P ORDER P INT ERROREV
     * @param stdClass $complemento
     * @param array $key_data_filter
     * @return array|stdClass
     */
    private function where_filtro(stdClass $complemento, array $key_data_filter): array|stdClass
    {
        $complemento_r = $this->where_base(complemento: $complemento);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error ajustar where',data: $complemento_r, params: get_defined_vars());
        }

        $verifica = $this->verifica_where(complemento: $complemento_r,key_data_filter: $key_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error validar where',data:$verifica, params: get_defined_vars());
        }

        $complemento_r->where = ' '.$complemento_r->where.' ';
        return $complemento_r;
    }

    /**
     * P ORDER P INT ERROREV
     * @param stdClass $complemento
     * @return array|stdClass
     */
    private function where_mayus(stdClass $complemento): array|stdClass
    {
        if(!isset($complemento->where)){
            $complemento->where = '';
        }
        $complemento->where = trim($complemento->where);
        if($complemento->where !== '' ){
            $complemento->where = strtoupper($complemento->where);
        }
        if($complemento->where!=='' && $complemento->where !=='WHERE'){
            return $this->error->error(mensaje: 'Error where mal aplicado',data: $complemento->where,
                params: get_defined_vars());
        }
        return $complemento;
    }

    /**
     *
     * @param string $filtro_sql
     * @return string
     */
    public function where_suma(string $filtro_sql): string
    {
        $where = '';
        if(trim($filtro_sql) !== '' ){
            $where = ' WHERE '. $filtro_sql;
        }
        return $where;

    }




}
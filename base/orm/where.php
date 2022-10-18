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
     * Asigna and  A FILTRO
     * @version 1.150.31
     * @param string $filtro_fecha_sql Filtro en forma de sql
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
     * Asigna los filtros a un objeto para ser utilizado en WHEREs de SQL FORMA select
     * @version 1.191.34
     * @verfuncion 1.0.0 Se integra prueba
     * @author mgamboa
     * @fecha 2022-07-25 11:25
     * @param string $sentencia Sentencia SQL previamente maquetada
     * @param string $filtro_especial_sql Filtro en forma de SQL
     * @param string $filtro_rango_sql Filtro en forma de rango en SQL
     * @param string $filtro_extra_sql Filtro enviado desde el origen
     * @param string $not_in_sql Filtro forma SQL NOT IN
     * @param string $sql_extra Sql Generado manualmente desde llamadas a esta funcion
     * @param string $filtro_fecha_sql Filtros en forma de fecha
     * @param string $in_sql Filtro en forma de  IN SQL
     * @return stdClass
     */
    #[Pure] private function asigna_data_filtro(string $filtro_especial_sql, string $filtro_extra_sql,
                                                string $filtro_fecha_sql, string $filtro_rango_sql, string $in_sql,
                                                string $not_in_sql, string $sentencia, string $sql_extra): stdClass
    {
        $filtros = new stdClass();
        $filtros->sentencia = $sentencia ;
        $filtros->filtro_especial = $filtro_especial_sql;
        $filtros->filtro_rango = $filtro_rango_sql;
        $filtros->filtro_extra = $filtro_extra_sql;
        $filtros->in = $in_sql;
        $filtros->not_in = $not_in_sql;
        $filtros->sql_extra = $sql_extra;
        $filtros->filtro_fecha = $filtro_fecha_sql;
        return $filtros;
    }

    /**
     * Funcion que ajusta el valor de un  campo de un registro y agregarle comillas
     * @version 1.12.8
     * @param array|string|null $data dato para la asignacion de un nombre de un campo si es array debe ser
     * $data[(string)campo] sino un string
     * @param string $key valor de campo de asignacion de campo name si es un array data busca valor en data
     * @return string|array
     */
    private function campo(array|string|null $data, string $key):string|array{
        if($key === ''){
            return $this->error->error(mensaje: "Error key vacio",data:  $key);
        }
        $campo = $data['campo'] ?? $key;
        return addslashes($campo);
    }

    /**
     * Funcion que asigna valor default en comparacion para filtro
     * @version 1.25.14
     * @param array|string|null $data $data dato para la asignacion de un nombre de un campo si es array debe ser
     * $data[(string)campo] $data[(string)value] data[(string)comparacion] sino un string
     * @param string $default
     * @return string
     */
    private function comparacion(array|string|null $data, string $default):string{
        return $data['comparacion'] ?? $default;
    }

    /**
     * Funcion para asignar el valor de una comparacion para filtro
     * @version 1.25.14
     * @param array $columnas_extra Columnas en forma de Subquery para se ejecutado en un SELECT
     * @param array|string|null $data $data dato para la asignacion de un nombre de un campo si es array debe ser
     * $data[(string)campo] $data[(string)value] sino un string
     * @param string $key valor de campo de asignacion de campo name si es un array data busca valor en data
     * @return array|stdClass
     */
    private function comparacion_pura(array $columnas_extra, array|string|null $data, string $key):array|stdClass{

        if($key === ''){
            return $this->error->error(mensaje: "Error key vacio", data: $key);
        }
        if(is_array($data) && count($data) === 0){
            return $this->error->error(mensaje:"Error datos vacio",data: $data);
        }
        $datas = new stdClass();
        $datas->campo = $this->campo(data: $data,key:  $key);
        if(errores::$error){
            return $this->error->error(mensaje:"Error al maquetar campo",data: $datas->campo);
        }
        $datas->value = $this->value(data: $data);
        if(errores::$error){
            return $this->error->error(mensaje:"Error al validar maquetacion",data: $datas->value);
        }
        $es_sq = false;
        if(isset($columnas_extra[$key])){
            $es_sq = true;
        }
        if($es_sq){
            $datas->campo = $columnas_extra[$key];
        }

        return $datas;
    }



    /**
     * Genera los datos de una fecha en forma de objeto
     * @version 1.149.31
     * @param array $fil_fecha Filtro a generar
     * @return stdClass|array
     */
    private function data_filtro_fecha(array $fil_fecha): stdClass|array
    {

        $valida = $this->valida_data_filtro_fecha(fil_fecha: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha',data: $valida);
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
     * Crea los datos de los diferentes tipos de filtro en forma de SQL
     * @param array $columnas_extra Columnas para subquerys declarados en el modelo
     * @param array $keys_data_filter Keys de los filtros
     * @param string $tipo_filtro Validos son numeros o textos
     * @param array $filtro Filtros basicos
     * @param array $filtro_especial arreglo con las condiciones $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x')
     * @param array $filtro_rango
     *                  Opcion1.- Debe ser un array con la siguiente forma array('valor1'=>'valor','valor2'=>'valor')
     *                  Opcion2.-
     *                      Debe ser un array con la siguiente forma
     *                          array('valor1'=>'valor','valor2'=>'valor','valor_campo'=>true)
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
     * @param array $not_in Conjunto de valores para not_in not_in[llave] = string, not_in['values'] = array()
     * @param string $sql_extra SQL maquetado de manera manual para su integracion en un WHERE
     * @param array $filtro_fecha Filtros de fecha para sql filtro[campo_1], filtro[campo_2], filtro[fecha]
     * @param array $in Arreglo con los elementos para integrar un IN en SQL in[llave] = tabla.campo, in['values'] = array()
     * @author mgamboa
     * @fecha 2022-07-25 16:41
     * @return array|stdClass
     */
    public function data_filtros_full(array $columnas_extra, array $filtro, array $filtro_especial, array $filtro_extra,
                                      array $filtro_fecha, array $filtro_rango, array $in, array $keys_data_filter,
                                      array $not_in, string $sql_extra, string $tipo_filtro): array|stdClass
    {

        $verifica_tf = $this->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }
        $filtros = $this->genera_filtros_sql(columnas_extra: $columnas_extra, filtro:  $filtro,
            filtro_especial:  $filtro_especial, filtro_extra:  $filtro_extra, filtro_rango:  $filtro_rango, in: $in,
            keys_data_filter: $keys_data_filter, not_in: $not_in, sql_extra: $sql_extra, tipo_filtro: $tipo_filtro,
            filtro_fecha: $filtro_fecha);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtros', data:$filtros);
        }


        $where = $this->where(filtros: $filtros, keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar where',data:$where);
        }

        $filtros = $this->filtros_full(filtros: $filtros, keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtros',data:$filtros);
        }
        $filtros->where = $where;
        return $filtros;
    }

    /**
     *
     * Genera las condiciones sql de un filtro especial
     * @param array $columnas_extra Conjunto de columnas en forma de subquery
     * @param array $filtro_especial //arreglo con las condiciones $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x')
     *
     * @return array|string
     * @version 1.130.30
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
     */
    private function filtro_especial_sql(array $columnas_extra, array $filtro_especial):array|string{ //DEBUG

        $filtro_especial_sql = '';
        foreach ($filtro_especial as $campo=>$filtro_esp){
            if(!is_array($filtro_esp)){

                return $this->error->error(mensaje: "Error filtro debe ser un array filtro_especial[] = array()",
                    data: $filtro_esp);
            }

            $filtro_especial_sql = $this->obten_filtro_especial(columnas_extra: $columnas_extra,
                filtro_esp: $filtro_esp, filtro_especial_sql: $filtro_especial_sql);
            if(errores::$error){
                return $this->error->error(mensaje:"Error filtro", data: $filtro_especial_sql);
            }
        }
        return $filtro_especial_sql;
    }

    /**
     *
     * Funcion que genera las condiciones de sql de un filtro extra
     * @version 1.132.30
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
                return $this->error->error(mensaje: 'Error $data_filtro debe ser un array',data: $filtro_extra);
            }
            $campo = key($data_filtro);
            $campo = trim($campo);

            if(!isset($data_filtro[$campo]['operador'])){
                return $this->error->error(mensaje:'Error data_filtro['.$campo.'][operador] debe existir',
                    data:$data_filtro);
            }

            $operador = $data_filtro[$campo]['operador'];
            if($operador===''){
                return $this->error->error(mensaje:'Error el operador debe de existir',data:$operador);
            }

            if(!isset($data_filtro[$campo]['valor'])){
                return $this->error->error(mensaje:'Error data_filtro['.$campo.'][valor] debe existir',
                    data:$data_filtro);
            }
            if(!isset($data_filtro[$campo]['comparacion'])){
                return $this->error->error(mensaje:'Error data_filtro['.$campo.'][comparacion] debe existir',
                    data:$data_filtro);
            }

            $valor = $data_filtro[$campo]['valor'];
            if($valor===''){
                return $this->error->error(mensaje:'Error el operador debe de existir',data:$valor);
            }
            $valor = addslashes($valor);
            $comparacion = $data_filtro[$campo]['comparacion'];
            $condicion = $campo.$operador."'$valor'";

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
     * Genera el SQL de filtro fecha
     * @version 1.180.34
     * @param array $filtro_fecha Filtros de fecha para sql filtro[campo_1], filtro[campo_2], filtro[fecha]
     * @return array|string
     */
    private function filtro_fecha(array $filtro_fecha):array|string{


        $filtro_fecha_sql = $this->filtro_fecha_base(filtro_fecha: $filtro_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sql',data: $filtro_fecha_sql);
        }

        if($filtro_fecha_sql !==''){
            $filtro_fecha_sql = "($filtro_fecha_sql)";
        }

        return $filtro_fecha_sql;
    }

    /**
     *
     * Obtiene los filtros en forma sql de una fecha
     * @version 1.171.33
     * @param array $filtro_fecha Filtros de fecha para sql
     * @return array|string
     */
    private function filtro_fecha_base(array $filtro_fecha): array|string
    {
        $filtro_fecha_sql = '';
        foreach ($filtro_fecha as $fil_fecha){
            if(!is_array($fil_fecha)){
                return $this->error->error(mensaje: 'Error $fil_fecha debe ser un array',data: $fil_fecha);
            }

            $valida = $this->valida_filtro_fecha(fil_fecha: $fil_fecha);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
            }

            $sql = $this->genera_sql_filtro_fecha(fil_fecha: $fil_fecha, filtro_fecha_sql: $filtro_fecha_sql);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener sql',data: $sql);
            }

            $filtro_fecha_sql.= $sql;

        }
        return $filtro_fecha_sql;
    }

    /**
     *
     * Devuelve un conjunto de condiciones de tipo BETWEEN en forma de sql
     * @version 1.130.30
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
                return  $this->error->error(mensaje: 'Error $filtro debe ser un array',data: $filtro);
            }
            if(!isset($filtro['valor1'])){
                return  $this->error->error(mensaje:'Error $filtro[valor1] debe existir',data:$filtro);
            }
            if(!isset($filtro['valor2'])){
                return  $this->error->error(mensaje:'Error $filtro[valor2] debe existir',data:$filtro);
            }
            $campo = trim($campo);
            if(is_numeric($campo)){
                return  $this->error->error(mensaje:'Error campo debe ser un string',data:$campo);
            }
            $valor_campo = false;

            if(isset($filtro['valor_campo']) && $filtro['valor_campo']){
                $valor_campo = true;
            }
            $filtro_rango_sql = $this->genera_filtro_rango_base(campo: $campo,filtro: $filtro,
                filtro_rango_sql: $filtro_rango_sql,valor_campo: $valor_campo);
            if(errores::$error){
                return  $this->error->error(mensaje:'Error $filtro_rango_sql al generar',data:$filtro_rango_sql);
            }
        }

        return $filtro_rango_sql;
    }

    /**
     * Genera un AND y une los filtros
     * @param stdClass $filtros Filtros a ejecutar en un where
     * @param array $keys_data_filter Key a verificar
     * @version 1.198.34
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-07-25 16:27
     * @return stdClass
     */
    private function filtros_full(stdClass $filtros, array $keys_data_filter): stdClass
    {
        $filtros_ = $filtros;
        $filtros_ = $this->limpia_filtros(filtros: $filtros_, keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar filtros',data: $filtros_);
        }

        $and = '';
        foreach ($keys_data_filter as $key){
            if($filtros_->$key !=='') {
                $filtros_->$key = " $and ( " . $filtros_->$key . ")";
                $and = " AND ";
            }
        }

        return $filtros_;
    }


    /**
     * Inicializa los key del filtro como vacios
     * @param stdClass $complemento Complemento de datos SQL a incializar
     * @param array $keys_data_filter Keys a limpiar o validar
     * @return bool
     * @version 1.237.39
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-08-01 13:07
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
     *
     * Devuelve un conjunto de condiciones de tipo AND en forma de sql
     * @version 1.26.14
     * @param array $columnas_extra Columnas en forma de Subquery para se ejecutado en un SELECT
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
    public function genera_and(array $columnas_extra, array $filtro):array|string{
        $sentencia = '';
        foreach ($filtro as $key => $data) {
            if(is_numeric($key)){
                return $this->error->error(
                    mensaje: 'Los key deben de ser campos asociativos con referencia a tabla.campo',data: $filtro);
            }
            $data_comparacion = $this->comparacion_pura(columnas_extra: $columnas_extra, data: $data, key: $key);
            if(errores::$error){
                return $this->error->error(mensaje:"Error al maquetar campo",data:$data_comparacion);
            }

            $comparacion = $this->comparacion(data: $data,default: '=');
            if(errores::$error){
                return $this->error->error(mensaje:"Error al maquetar",data:$comparacion);
            }

            $operador = $data['operador'] ?? ' AND ';
            if(trim($operador) !=='AND' && trim($operador) !=='OR'){
                return $this->error->error(mensaje:'El operador debe ser AND u OR',data:$operador);
            }

            $data_sql = "$data_comparacion->campo $comparacion '$data_comparacion->value'";

            $sentencia .= $sentencia === ''? $data_sql :" $operador $data_sql";
        }

        return $sentencia;

    }

    /**
     *
     * Devuelve un conjunto de condiciones de tipo AND en forma de sql  con LIKE
     * @version 1.123.29
     * @param array $columnas_extra Columnas para subquerys declarados en el modelo
     * @param array $filtro filtros para la maquetacion de filtros
     * @return array|string Sentencia del lado WHERE aplicado con %% para textos
     * @example
     *      $sentencia = $this->genera_and_textos($this->filtro);
     * @uses modelo_basico
     */
    private function genera_and_textos(array $columnas_extra, array $filtro):array|string{

        $sentencia = '';
        foreach ($filtro as $key => $data) {
            if(is_numeric($key)){
                return $this->error->error(
                    mensaje: 'Los key deben de ser campos asociativos con referencia a tabla.campo',data: $filtro);
            }

            $data_comparacion = $this->comparacion_pura(columnas_extra: $columnas_extra, data: $data,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: "Error al maquetar",data:$data_comparacion);
            }

            $comparacion = $this->comparacion(data: $data,default: 'LIKE');
            if(errores::$error){
                return $this->error->error(mensaje:"Error al maquetar",data:$comparacion);
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
     *
     * Genera la condicion sql de un filtro especial
     * @version 1.130.30
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
                    data: $filtro_esp);
            }
            if(trim($data_sql) === ''){
                return $this->error->error(mensaje:'Error $data_sql no puede venir vacio', data:$data_sql);
            }

            $filtro_especial_sql .= ' '.$filtro_esp[$campo]['comparacion'].' '.$data_sql;
        }

        return $filtro_especial_sql;
    }

    /**
     *
     * Devuelve una condicion en forma de sql validando si se tiene que precragar un AND o solo la sentencia
     * @version 1.130.30
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
                                              bool $valor_campo = false):array|string{
        $campo = trim($campo);
        if($campo === ''){
            return  $this->error->error(mensaje: 'Error $campo no puede venir vacio',data: $campo);
        }
        $keys = array('valor1','valor2');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $filtro);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }

        $condicion = $campo . ' BETWEEN ' ."'" .$filtro['valor1'] . "'"." AND "."'".$filtro['valor2'] . "'";

        if($valor_campo){
            $condicion = "'".$campo."'" . ' BETWEEN '  .$filtro['valor1'] ." AND ".$filtro['valor2'];
        }
        $filtro_rango_sql_r = $this->setea_filtro_rango(condicion: $condicion, filtro_rango_sql: $filtro_rango_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error $filtro_rango_sql al setear',data: $filtro_rango_sql_r);
        }

        return $filtro_rango_sql_r;
    }

    /**
     * Ajusta los filtros con parentesis y limpieza para su correcta ejecucion
     * @param string $sentencia Sentencia SQL previamente maquetada
     * @param string $filtro_especial_sql Filtro en forma de SQL
     * @param string $filtro_rango_sql Filtro en forma de rango en SQL
     * @param string $filtro_extra_sql Filtro enviado desde el origen
     * @param string $not_in_sql Filtro en forma de NOT IN SQL
     * @param array $keys_data_filter Keys de los filtros
     * @param string $sql_extra Sql generado de forma manual para la funcion en ejecucion
     * @param string $filtro_fecha_sql Filtro de fecha en forma de sql
     * @param string $in_sql Filtro en forma de  IN SQL
     * @version 1.195.34
     * @verfuncion 1.0.0
     * @author mgamboa
     * @fecha 2022-07-25 12:16
     * @return array|stdClass
     */
    private function genera_filtros_iniciales(string $filtro_especial_sql, string $filtro_extra_sql,
                                              string $filtro_rango_sql, string $in_sql, array $keys_data_filter,
                                              string $not_in_sql, string $sentencia, string $sql_extra,
                                              string $filtro_fecha_sql = ''): array|stdClass
    {
        $filtros = $this->asigna_data_filtro(filtro_especial_sql:  $filtro_especial_sql,
            filtro_extra_sql: $filtro_extra_sql, filtro_fecha_sql:  $filtro_fecha_sql,
            filtro_rango_sql:  $filtro_rango_sql, in_sql: $in_sql, not_in_sql: $not_in_sql,sentencia: $sentencia,
            sql_extra:  $sql_extra);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar filtros',data: $filtros);
        }

        $filtros = $this->limpia_filtros(filtros: $filtros, keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al limpiar filtros',data:$filtros);
        }

        $filtros = $this->parentesis_filtro(filtros: $filtros,keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtros',data:$filtros);
        }
        return $filtros;
    }

    /**
     * Genera los filtros en forma de sql
     * @param array $columnas_extra Columnas para subquerys declarados en el modelo
     * @param array $keys_data_filter Keys de los filtros
     * @param string $tipo_filtro Validos son numeros o textos
     * @param array $filtro Conjunto de filtros para ejecucion de where
     * @param array $filtro_especial arreglo con las condiciones $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x')
     * @param array $filtro_rango
     *                  Opcion1.- Debe ser un array con la siguiente forma array('valor1'=>'valor','valor2'=>'valor')
     *                  Opcion2.-
     *                      Debe ser un array con la siguiente forma
     *                          array('valor1'=>'valor','valor2'=>'valor','valor_campo'=>true)
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
     * @param array $not_in Conjunto de valores para not_in not_in[llave] = string, not_in['values'] = array()
     * @param string $sql_extra SQL maquetado de manera manual para su integracion en un WHERE
     * @param array $filtro_fecha Filtros de fecha para sql filtro[campo_1], filtro[campo_2], filtro[fecha]
     * @param array $in Arreglo con los elementos para integrar un IN en SQL in[llave] = tabla.campo, in['values'] = array()
     * @author mgamboa
     * @fecha 2022-25-07 12:22
     * @return array|stdClass
     */
    private function genera_filtros_sql(array $columnas_extra, array $filtro, array $filtro_especial,
                                        array $filtro_extra, array $filtro_rango, array $in, array $keys_data_filter,
                                        array $not_in, string $sql_extra, string $tipo_filtro,
                                        array $filtro_fecha = array()): array|stdClass
    {
        $verifica_tf = $this->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }
        $sentencia = $this->genera_sentencia_base(columnas_extra: $columnas_extra, filtro: $filtro,
            tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sentencia', data:$sentencia);
        }

        $filtro_especial_sql = $this->filtro_especial_sql(
            columnas_extra: $columnas_extra, filtro_especial: $filtro_especial);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtro',data: $filtro_especial_sql);
        }
        $filtro_rango_sql = $this->filtro_rango_sql(filtro_rango: $filtro_rango);
        if(errores::$error){
            return $this->error->error(mensaje:'Error $filtro_rango_sql al generar',data:$filtro_rango_sql);
        }
        $filtro_extra_sql = $this->filtro_extra_sql(filtro_extra: $filtro_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtro extra',data:$filtro_extra_sql);
        }

        $not_in_sql = $this->genera_not_in_sql(not_in: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data:$not_in_sql);
        }


        $in_sql = $this->genera_in_sql(in: $in);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data:$in_sql);
        }

        $filtro_fecha_sql = $this->filtro_fecha(filtro_fecha: $filtro_fecha);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtro_fecha',data:$filtro_fecha_sql);
        }

        $filtros = $this->genera_filtros_iniciales(filtro_especial_sql:  $filtro_especial_sql,
            filtro_extra_sql: $filtro_extra_sql, filtro_rango_sql: $filtro_rango_sql, in_sql:$in_sql,
            keys_data_filter:  $keys_data_filter,not_in_sql:  $not_in_sql, sentencia: $sentencia,
            sql_extra:  $sql_extra,filtro_fecha_sql:  $filtro_fecha_sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtros',data:$filtros);
        }


        return $filtros;

    }

    private function genera_in(array $in): array|string
    {
        $keys = array('llave','values');
        $valida = $this->validacion->valida_existencia_keys( keys:$keys, registro: $in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar not_in',data: $valida);
        }

        $llave = $in['llave'];
        $values = $in['values'];

        if(!is_array($values)){
            return $this->error->error(mensaje: 'Error values debe ser un array',data: $values);
        }

        $in_sql = $this->in_sql(llave:  $llave, values:$values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $in_sql);
        }
        return $in_sql;
    }

    private function genera_in_sql(array $in): array|string
    {
        $in_sql = '';
        if(count($in)>0){

            $keys = array('llave','values');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar in',data: $valida);
            }
            $in_sql = $this->genera_in(in: $in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql',data: $in_sql);
            }

        }
        return $in_sql;
    }

    /**
     * Genera un not in para sql
     * @version 1.135.31
     * @param array $not_in Conjunto de valores para not_in not_in[llave] = string, not_in['values'] = array()
     * @return array|string
     */
    private function genera_not_in(array $not_in): array|string
    {
        $keys = array('llave','values');
        $valida = $this->validacion->valida_existencia_keys( keys:$keys, registro: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar not_in',data: $valida);
        }

        $llave = $not_in['llave'];
        $values = $not_in['values'];

        if(!is_array($values)){
            return $this->error->error(mensaje: 'Error values debe ser un array',data: $values);
        }

        $not_in_sql = $this->not_in_sql(llave:  $llave, values:$values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $not_in_sql);
        }
        return $not_in_sql;
    }

    /**
     * Genera el SQL a aplicar en un where en forma de NOT IN
     * @version 1.137.31
     * @param array $not_in Conjunto de valores para not_in not_in[llave] = string, not_in['values'] = array()
     * @return array|string
     */
    private function genera_not_in_sql(array $not_in): array|string
    {
        $not_in_sql = '';
        if(count($not_in)>0){
            $keys = array('llave','values');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $not_in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar not_in',data: $valida);
            }
            $not_in_sql = $this->genera_not_in(not_in: $not_in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql',data: $not_in_sql);
            }

        }
        return $not_in_sql;
    }

    /**
     *
     * Devuelve un conjunto de condiciones de tipo AND en forma de sql  con LIKE o =
     * @param string $tipo_filtro numeros = textos LIKE
     * @param array $filtro parametros para generar sentencia
     * @param array $columnas_extra Columnas para subquerys declarados en el modelo
     * @return array|string con sentencia en SQL
     * @throws errores $this->filtro[key] es un numero
     * @example
     *      $sentencia = $this->genera_sentencia_base($tipo_filtro);
     * @uses modelo
     * @version 1.123.29
     */
    private function genera_sentencia_base(array $columnas_extra,  array $filtro, string $tipo_filtro):array|string{
        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }
        $sentencia = '';
        if($tipo_filtro === 'numeros') {
            $sentencia = $this->genera_and(columnas_extra: $columnas_extra, filtro: $filtro);
            if(errores::$error){
                return $this->error->error(mensaje: "Error en and",data:$sentencia);
            }
        }
        elseif ($tipo_filtro==='textos'){
            $sentencia = $this->genera_and_textos(columnas_extra: $columnas_extra,filtro: $filtro);
            if(errores::$error){
                return $this->error->error(mensaje: "Error en texto",data:$sentencia);
            }
        }
        return $sentencia;
    }

    /**
     * Genera un filtro de fecha para sql
     * @version 1.170.33
     * @param array $fil_fecha Filtro a validar
     * @param string $filtro_fecha_sql Filtro a generar
     * @return array|string
     */
    private function genera_sql_filtro_fecha(array $fil_fecha, string $filtro_fecha_sql): array|string
    {
        $valida = $this->valida_data_filtro_fecha(fil_fecha: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha',data: $valida);
        }

        $data = $this->data_filtro_fecha(fil_fecha: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar datos',data:$data);
        }

        $and = $this->and_filtro_fecha(filtro_fecha_sql: $filtro_fecha_sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener and',data:$and);
        }

        $sql = $this->sql_fecha(and:$and,data:  $data);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener sql',data:$sql);
        }
        return $sql;
    }

    /**
     * Genera un IN SQL
     * @param string $llave Llave o campo
     * @param array $values Valores a integrar a IN
     * @return array|string

     */
    private function in_sql(string $llave, array $values): array|string
    {
        $llave = trim($llave);
        if($llave === ''){
            return $this->error->error(mensaje: 'Error la llave esta vacia',data: $llave);
        }

        $values_sql = $this->values_sql_in(values:$values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $values_sql);
        }

        $in_sql = (new sql())->in(llave: $llave,values_sql:  $values_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $in_sql);
        }

        return $in_sql;
    }

    /**
     * Inicializa los parametros de un complemento para where
     * @param stdClass $complemento Complemento de datos sql
     * @param array $keys_data_filter Keys para filtros
     * @return array|stdClass
     * @version 1.260.40
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-08-02 14:46
     */
    public function init_params_sql(stdClass $complemento, array $keys_data_filter): array|stdClass
    {
        $complemento_w = $this->where_filtro(complemento: $complemento,key_data_filter:  $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error ajustar where',data: $complemento_w);
        }

        $complemento_r = (new inicializacion())->ajusta_params(complemento: $complemento_w);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al inicializar params',data:$complemento_r);
        }
        return $complemento_r;
    }

    /**
     * Limpia los elementos para usar en un filtro para WHERE en SQL
     * @version 1.52.31
     * @param stdClass $filtros Filtros a limpiar o inicializar
     * @param array $keys_data_filter keys de los filtros
     * @return stdClass|array
     */
    public function limpia_filtros(stdClass $filtros, array $keys_data_filter): stdClass|array
    {
        foreach($keys_data_filter as $key){
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error el key esta vacio', data: $keys_data_filter);
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
     *
     * Genera la condicion sql de un filtro especial
     *
     * @param string $campo campo de una tabla tabla.campo
     * @param array $columnas_extra Campos en forma de subquery del modelo
     * @param array $filtro filtro a validar
     *
     * @return array|string
     * @version 1.127.29
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
     * @uses modelo_basico->obten_filtro_especial
     */
    private function maqueta_filtro_especial(string $campo, array $columnas_extra, array $filtro):array|string{
        $campo = trim($campo);

        $valida = (new validaciones())->valida_data_filtro_especial(campo: $campo,filtro:  $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro', data: $valida);
        }

        $keys = array('valor');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $filtro[$campo]);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar filtro',  data:$valida);
        }

        /**
         * REFACTORIZAR
         */
        $campo_filtro = $campo;
        $es_subquery = false;
        if(isset($columnas_extra[$campo])){
            $es_subquery = true;
        }

        if($es_subquery){
            $campo = $columnas_extra[$campo];
        }

        $data_sql = " ".$campo." " . $filtro[$campo_filtro]['operador'] . " '" . $filtro[$campo_filtro]['valor'] . "' ";

        if(isset($filtro[$campo_filtro]['valor_es_campo']) && $filtro[$campo_filtro]['valor_es_campo']){

            $data_sql = "'".$campo."'".$filtro[$campo_filtro]['operador'].$filtro[$campo_filtro]['valor'];
        }

        return $data_sql;
    }

    /**
     * Genera los elementos not in de un sql
     * @version 1.134.31
     * @param array $values Valores a ajustar en sql in
     * @param string $llave campo a aplicar not in
     * @return array|string
     */
    private function not_in_sql(string $llave, array $values): array|string
    {
        $llave = trim($llave);
        if($llave === ''){
            return $this->error->error(mensaje: 'Error la llave esta vacia',data: $llave);
        }

        $not_in_sql = '';
        $values_sql = $this->values_sql_in(values:$values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $values_sql);
        }

        if($values_sql!==''){
            $not_in_sql.="$llave NOT IN ($values_sql)";
        }

        return $not_in_sql;
    }

    /**
     *
     * Genera la condicion sql de un filtro especial
     * @param array $columnas_extra Conjunto de columnas en forma de subquery
     * @param array $filtro_esp //array con datos del filtro $filtro_esp[tabla.campo]= array('operador'=>'AND','valor'=>'x');
     *
     * @param string $filtro_especial_sql //condicion en forma de sql
     * @return array|string
     * @version 1.130.30
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
     */

    private function obten_filtro_especial(
        array $columnas_extra, array $filtro_esp, string $filtro_especial_sql):array|string{
        $campo = key($filtro_esp);
        $campo = trim($campo);

        $valida =(new validaciones())->valida_data_filtro_especial(campo: $campo,filtro:  $filtro_esp);
        if(errores::$error){
            return $this->error->error(mensaje: "Error en filtro ", data: $valida);
        }
        $data_sql = $this->maqueta_filtro_especial(campo: $campo, columnas_extra: $columnas_extra,filtro: $filtro_esp);
        if(errores::$error){
            return $this->error->error(mensaje:"Error filtro", data:$data_sql);
        }
        $filtro_especial_sql_r = $this->genera_filtro_especial(campo:  $campo, data_sql: $data_sql,
            filtro_esp: $filtro_esp, filtro_especial_sql: $filtro_especial_sql);
        if(errores::$error){
            return $this->error->error(mensaje:"Error filtro",data: $filtro_especial_sql_r);
        }

        return $filtro_especial_sql_r;
    }

    /**
     * Asigna los parentesis  de un conjunto de filtros para SQL
     * @param stdClass $filtros Conjunto de filtros
     * @param array $keys_data_filter Keys de los filtros
     * @version 1.193.34
     * @verfuncion 1.0.0
     * @author mgamboa
     * @fecha 2022-07-25 11:46
     * @return stdClass|array
     */
    private function parentesis_filtro(stdClass $filtros, array $keys_data_filter): stdClass|array
    {
        $filtros_ = $filtros;
        $filtros_ = $this->limpia_filtros(filtros: $filtros_, keys_data_filter: $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar filtros', data: $filtros_);
        }

        foreach($keys_data_filter as $key){
            if($filtros_->$key!==''){
                $filtros_->$key = ' ('.$filtros_->$key.') ';
            }
        }


        return $filtros_;
    }

    /**
     *
     * Devuelve una condicion en forma de sql validando si se tiene que precragar un AND o solo la sentencia
     * @version 1.130.30
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

            return  $this->error->error(mensaje: 'Error if filtro_rango tiene info $condicion no puede venir vacio',
                data: $filtro_rango_sql);
        }

        $and = '';
        if($filtro_rango_sql !==''){
            $and = ' AND ';
        }

        $filtro_rango_sql.= $and.$condicion;

        return $filtro_rango_sql;
    }

    /**
     * Genera el sql de una fecha
     * @version 1.151.31
     * @param string $and AND O ''
     * @param stdClass $data Datos de fecha
     * @return string|array
     */
    private function sql_fecha(string $and, stdClass $data): string|array
    {
        $keys = array('fecha','campo_1','campo_2');
        foreach($keys as $key){
            if(!isset($data->$key)){
                return $this->error->error(mensaje: 'error no existe $data->'.$key, data: $data);
            }
            if(trim($data->$key) === ''){
                return $this->error->error(mensaje:'error esta vacio $data->'.$key, data:$data);
            }
        }
        $keys = array('fecha');
        foreach($keys as $key){
            $valida = $this->validacion->valida_fecha(fecha: $data->$key);
            if(errores::$error){
                return $this->error->error(mensaje:'error al validar '.$key,data: $valida);
            }
        }

        return "$and('$data->fecha' >= $data->campo_1 AND '$data->fecha' <= $data->campo_2)";
    }

    /**
     * Valida los datos de una fecha
     * @version 1.146.31
     * @param array $fil_fecha Filtro a validar
     * @return bool|array
     */
    private function valida_data_filtro_fecha(array $fil_fecha): bool|array
    {
        $keys = array('campo_1','campo_2','fecha');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }
        $valida = $this->validacion->valida_fecha(fecha: $fil_fecha['fecha']);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar fecha',data:$valida);
        }
        return true;
    }

    /**
     * Valida los datos de un filtro de tipo fecha
     * @version 1.143.31
     * @param array $fil_fecha Filtro fecha a validar
     * @return bool|array
     */
    private function valida_filtro_fecha(array $fil_fecha): bool|array
    {

        $keys = array('campo_1','campo_2','fecha');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $fil_fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
        }

        $keys = array('fecha');
        $valida = $this->validacion->fechas_in_array(data:  $fil_fecha, keys: $keys);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro',data: $valida);
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
     * @param array|string|null $data dato para la asignacion de un nombre de un campo si es array debe ser
     * @version 1.25.14
     * $data[(string)campo] $data[(string)value] sino un string
     * @return string|array
     */
    private function value(array|string|null $data):string|array{
        $value = $data;
        if(is_array($data) && isset($data['value'])){
            $value = trim($data['value']);
        }
        if(is_array($data) && count($data) === 0){
            return $this->error->error(mensaje: "Error datos vacio",data: $data);
        }
        if(is_array($data) && !isset($data['value'])){
            return $this->error->error(mensaje:"Error no existe valor",data: $data);
        }
        return addslashes($value);
    }

    /**
     * Genera comas o values para ser utilizados en un SELECT
     * @version 1.134.31
     * @param string $value Valor a reasignar coma
     * @param string $values_sql Valores previos sql
     * @return array|stdClass
     */
    private function value_coma(string $value, string $values_sql): array|stdClass
    {
        $values_sql = trim($values_sql);
        $value = trim($value);
        if($value === ''){
            return $this->error->error(mensaje: 'Error value esta vacio',data: $value);
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
     * Obtiene los valores de un sql para generar un where
     * @version 1.134.31
     * @param array $values Valores a ajustar en sql in
     * @return string|array
     */
    private function values_sql_in(array $values): string|array
    {
        $values_sql = '';
        foreach ($values as $value){
            $data = $this->value_coma(value:$value, values_sql: $values_sql);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error obtener datos de value',data: $data);
            }
            $values_sql.="$data->coma$data->value";
        }
        return $values_sql;
    }

    /**
     * Verifica que la estructura de un complemento sql sea la correcta
     *
     * @param stdClass $complemento Complemento de datos SQL a incializar
     * @param array $key_data_filter Filtros a limpiar o validar
     * @return bool|array
     * @version 1.245.39
     * @verfuncion 1.1.0
     * @fecha 2022-08-01 16:47
     * @author mgamboa
     */
    private function verifica_where(stdClass $complemento, array $key_data_filter): bool|array
    {
        if(!isset($complemento->where)){
            $complemento->where = '';
        }
        if($complemento->where!==''){
            $filtros_vacios = $this->filtros_vacios(complemento: $complemento, keys_data_filter: $key_data_filter);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error validar filtros',data: $filtros_vacios);
            }
            if($filtros_vacios){
                return $this->error->error(mensaje: 'Error si existe where debe haber al menos un filtro',
                    data: $complemento);
            }
        }
        return true;
    }

    /**
     * Genera un WHERE validado por el numero de parametros
     * @param stdClass $filtros Filtros a utilizar enb un WHERE
     * @param array $keys_data_filter Key de los filtros a limpiar o validar para convertir en obj
     * @version 1.197.34
     * @verfuncion 1.0.0
     * @author mgamboa
     * @fecha 2022-07-25 12:33
     * @return string
     */
    private function where(stdClass $filtros, array $keys_data_filter): string
    {
        $filtros_ = $filtros;
        $filtros_ = $this->limpia_filtros(filtros: $filtros_,keys_data_filter:  $keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar filtros', data: $filtros_);
        }
        $where='';
        foreach($keys_data_filter as $key){
            if($filtros_->$key!==''){
                $where = " WHERE ";
            }
        }

        return $where;
    }

    /**
     * Genera un where base aplicando un estilo correcto SQL
     * @param stdClass $complemento Complemento de datos sql
     * @return array|stdClass
     * @version 1.236.39
     * @verfuncion 1.1.0
     * @fecha 2022-08-01 14:42
     * @author mgamboa
     */
    private function where_base(stdClass $complemento): array|stdClass
    {
        if(!isset($complemento->where)){
            $complemento->where = '';
        }
        $complemento_r = $this->where_mayus(complemento: $complemento);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error ajustar where',data: $complemento_r);
        }
        return $complemento_r;
    }

    /**
     * Genera un filtro de tipo where valido
     * @param stdClass $complemento Complemento de datos sql
     * @param array $key_data_filter Keys de filtros para where
     * @return array|stdClass
     * @version 1.251.39
     * @verfuncion 1.1.0
     * @fecha 2022-08-02 09:43
     */
    private function where_filtro(stdClass $complemento, array $key_data_filter): array|stdClass
    {
        $complemento_r = $this->where_base(complemento: $complemento);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error ajustar where',data: $complemento_r);
        }

        $verifica = $this->verifica_where(complemento: $complemento_r,key_data_filter: $key_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error validar where',data:$verifica);
        }

        $complemento_r->where = ' '.$complemento_r->where.' ';
        return $complemento_r;
    }

    /**
     *
     * CONVIERTE EN MAYUSCULAS EL ELEMENTO WHERE EN SQL
     * @version 1.212.37
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-07-29 13:42
     * @param stdClass $complemento Complemento de datos sql
     * @return array|stdClass
     *
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
            return $this->error->error(mensaje: 'Error where mal aplicado',data: $complemento->where);
        }
        return $complemento;
    }

    /**
     * Antepone la palabra WHERE al filtro mandado por parametros
     * @param string $filtro_sql filtro por aplicar
     * @return string filtro enviado por parametros anteponiendo la palabra WHERE
     * @version 1.489.49
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
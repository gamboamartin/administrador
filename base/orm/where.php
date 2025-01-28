<?php
namespace base\orm;

use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;


class where{

    public errores $error;
    public validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }


    /**
     * TOTAL
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
     * @param array $diferente_de Arreglo con los elementos para integrar un diferente de
     * @author mgamboa
     * @fecha 2022-07-25 16:41
     * @return array|stdClass
     * @version 17.20.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.data_filtros_full
     */
    final public function data_filtros_full(array $columnas_extra, array $diferente_de, array $filtro,
                                      array $filtro_especial, array $filtro_extra, array $filtro_fecha,
                                      array $filtro_rango, array $in, array $keys_data_filter, array $not_in,
                                      string $sql_extra, string $tipo_filtro): array|stdClass
    {

        $verifica_tf = (new \gamboamartin\where\where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }
        $filtros = $this->genera_filtros_sql(columnas_extra: $columnas_extra, diferente_de: $diferente_de,
            filtro:  $filtro, filtro_especial:  $filtro_especial, filtro_extra:  $filtro_extra,
            filtro_rango:  $filtro_rango, in: $in, keys_data_filter: $keys_data_filter, not_in: $not_in,
            sql_extra: $sql_extra, tipo_filtro: $tipo_filtro, filtro_fecha: $filtro_fecha);
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
     * TOTAL
     * Esta función genera una declaración SQL para verificar si un campo es
     * diferente de un valor dado.
     *
     * @param string $campo              El campo de la tabla SQL.
     * @param string $diferente_de_sql   String SQL que determina las condiciones bajo las cuales las entradas se consideran diferentes.
     * @param string $value              El valor que no debería coincidir con el campo.
     *
     * @return string|array              Devuelve una cadena que representa la declaración SQL generada,
     *                                   o un array que representa un mensaje de error.
     *
     * @version 16.219.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.diferente_de
     */
    private function diferente_de(string $campo, string $diferente_de_sql, string $value): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: "Error campo esta vacio", data: $campo, es_final: true);
        }
        if(is_numeric($campo)){
            return $this->error->error(mensaje: "Error campo debe ser un atributo del modelo no un numero",
                data: $campo, es_final: true);
        }
        $and = (new \gamboamartin\where\where())->and_filtro_fecha(txt: $diferente_de_sql);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al integrar AND", data: $and);
        }

        $campo = addslashes($campo);
        $value = addslashes($value);

        return " $and $campo <> '$value' ";
    }

    /**
     * TOTAL
     * Crea una declaración SQL para la condición WHERE en una consulta, basándose en un array de términos que deben ser diferentes.
     *
     * @param array $diferente_de Un array asociativo donde las claves son los nombres de las columnas en la base de datos, y los
     *        valores son los valores especificados que deben ser diferentes. El array debe tener al menos un elemento.
     *
     * @return array|string Si la operación es exitosa, regresa una cadena que contiene la parte WHERE de la declaración SQL.
     *         Si ocurre algún error durante el proceso, regresa un array con el mensaje de error y los detalles correspondientes.
     *
     * @throws errores Si el nombre del campo está vacío, si es un número, o si ocurre un error al generar la declaración SQL,
     *         se lanza un error con un mensaje detallado.
     *
     * Ejemplo de uso:
     * ```
     * $condiciones = array(
     *     'nombre' => 'Juan',
     *     'edad' => '30'
     * );
     * $resultado = diferente_de_sql($condiciones);
     * ```
     *
     * @version 16.314.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.diferente_de_sql
     */
    private function diferente_de_sql(array $diferente_de): array|string
    {
        $diferente_de_sql = '';
        if(count($diferente_de)>0){

            foreach ($diferente_de as $campo=>$value){

                $campo = trim($campo);
                if($campo === ''){
                    return $this->error->error(mensaje: "Error campo esta vacio", data: $campo, es_final: true);
                }
                if(is_numeric($campo)){
                    return $this->error->error(mensaje: "Error campo debe ser un atributo del modelo no un numero",
                        data: $campo, es_final: true);
                }

                $sql = $this->diferente_de(campo:$campo,diferente_de_sql:  $diferente_de_sql,value:  $value);
                if(errores::$error){
                    return $this->error->error(mensaje: "Error al integrar sql", data: $sql);
                }

                $diferente_de_sql .= $sql;
            }

        }
        return $diferente_de_sql;
    }

    /**
     *
     * TOTAL
     * Genera las condiciones sql de un filtro especial
     * @param array $columnas_extra Conjunto de columnas en forma de subquery
     * @param array $filtro_especial //arreglo con las condiciones $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x')
     *
     * @return array|string
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
     * @version 16.204.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.filtro_especial_sql
     */
    private function filtro_especial_sql(array $columnas_extra, array $filtro_especial):array|string
    {

        $filtro_especial_sql = '';
        foreach ($filtro_especial as $campo=>$filtro_esp){
            if(!is_array($filtro_esp)){

                return $this->error->error(mensaje: "Error filtro debe ser un array filtro_especial[] = array()",
                    data: $filtro_esp, es_final: true);
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
     * TOTAL
     * Esta función realiza una serie de filtros completos dados los parámetros proporcionados.
     *
     * @param stdClass $filtros       - Objeto que contiene los filtros que se aplicarán.
     * @param array $keys_data_filter - Claves del array que se utilizarán en los filtros.
     *
     * @return stdClass - Devuelve los filtros después de haber aplicado todas las operaciones.
     *
     * @throws errores Si hay un error al limpiar los filtros.
     * @version 17.17.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.filtros_full
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
     * TOTAL
     * Inicializa los key del filtro como vacios
     * @param stdClass $complemento Complemento de datos SQL a incializar
     * @param array $keys_data_filter Keys a limpiar o validar
     * @return bool
     * @version 1.237.39
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-08-01 13:07
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.filtros_vacios
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
     * TOTAL
     * Genera filtros iniciales para una consulta SQL.
     *
     * Este método genera filtros SQL iniciales a partir de varias entradas
     * y devuelve un array con los filtros generados o un objeto estándar
     * en caso de un error.
     *
     * @param string $diferente_de_sql       Un fragmento de consulta SQL para iniciadores de la condición "NOT EQUALS TO".
     * @param string $filtro_especial_sql    Un fragmento de consulta SQL para condiciones especiales.
     * @param string $filtro_extra_sql       Un fragmento de consulta SQL para condiciones extras.
     * @param string $filtro_rango_sql       Un fragmento de consulta SQL para condiciones de rango.
     * @param string $in_sql                 Un fragmento de consulta SQL para la cláusula "IN"
     * @param array  $keys_data_filter       Un array que contiene las claves de los datos para la ejecución del filtro.
     * @param string $not_in_sql             Un fragmento de consulta SQL para la cláusula "NOT IN"
     * @param string $sentencia              Un fragmento de consulta SQL para otras sentencias ad hoc.
     * @param string $sql_extra              Un fragmento de consulta SQL adicional.
     * @param string $filtro_fecha_sql       Un fragmento de consulta SQL para condiciones de fecha. Predeterminado es ''.
     *
     * @return array|stdClass                Retorna un array con los filtros generados. En caso de un error, devuelve un objeto estándar con detalles del error.
     *
     * @throws errores                     Este método puede lanzar una excepción si ocurre un error durante la generación de los filtros.
     *
     * @version 16.320.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.genera_filtros_iniciales
     */
    private function genera_filtros_iniciales(string $diferente_de_sql, string $filtro_especial_sql,
                                              string $filtro_extra_sql, string $filtro_rango_sql, string $in_sql,
                                              array $keys_data_filter, string $not_in_sql, string $sentencia,
                                              string $sql_extra, string $filtro_fecha_sql = ''): array|stdClass
    {
        $filtros = (new \gamboamartin\where\where())->asigna_data_filtro(diferente_de_sql: $diferente_de_sql,
            filtro_especial_sql:  $filtro_especial_sql, filtro_extra_sql: $filtro_extra_sql,
            filtro_fecha_sql:  $filtro_fecha_sql, filtro_rango_sql:  $filtro_rango_sql, in_sql: $in_sql,
            not_in_sql: $not_in_sql,sentencia: $sentencia, sql_extra:  $sql_extra);
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
     * TOTAL
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
     * @param array $diferente_de Arreglo con los elementos para integrar un diferente de
     * @author mgamboa
     * @fecha 2022-25-07 12:22
     * @return array|stdClass
     * @version 17.6.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.genera_filtros_sql
     */
    private function genera_filtros_sql(array $columnas_extra, array $diferente_de, array $filtro,
                                        array $filtro_especial, array $filtro_extra, array $filtro_rango, array $in,
                                        array $keys_data_filter, array $not_in, string $sql_extra, string $tipo_filtro,
                                        array $filtro_fecha = array()): array|stdClass
    {
        $verifica_tf = (new \gamboamartin\where\where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }
        $sentencia = (new \gamboamartin\where\where())->genera_sentencia_base(columnas_extra: $columnas_extra,
            filtro: $filtro, tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sentencia', data:$sentencia);
        }

        $filtro_especial_sql = $this->filtro_especial_sql(
            columnas_extra: $columnas_extra, filtro_especial: $filtro_especial);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtro',data: $filtro_especial_sql);
        }
        $filtro_rango_sql = (new \gamboamartin\where\where())->filtro_rango_sql(filtro_rango: $filtro_rango);
        if(errores::$error){
            return $this->error->error(mensaje:'Error $filtro_rango_sql al generar',data:$filtro_rango_sql);
        }
        $filtro_extra_sql = (new \gamboamartin\where\where())->filtro_extra_sql(filtro_extra: $filtro_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtro extra',data:$filtro_extra_sql);
        }

        $not_in_sql = (new \gamboamartin\where\where())->genera_not_in_sql(not_in: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data:$not_in_sql);
        }

        $in_sql = $this->genera_in_sql_normalizado(in: $in);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar in_sql',data:$in_sql);
        }

        $filtro_fecha_sql = (new \gamboamartin\where\where())->filtro_fecha(filtro_fecha: $filtro_fecha);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtro_fecha',data:$filtro_fecha_sql);
        }

        $diferente_de_sql = $this->diferente_de_sql(diferente_de: $diferente_de);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data:$diferente_de_sql);
        }


        $filtros = $this->genera_filtros_iniciales(diferente_de_sql: $diferente_de_sql,
            filtro_especial_sql: $filtro_especial_sql, filtro_extra_sql: $filtro_extra_sql,
            filtro_rango_sql: $filtro_rango_sql, in_sql: $in_sql, keys_data_filter: $keys_data_filter,
            not_in_sql: $not_in_sql, sentencia: $sentencia, sql_extra: $sql_extra,
            filtro_fecha_sql: $filtro_fecha_sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar filtros',data:$filtros);
        }


        return $filtros;

    }

    /**
     * TOTAL
     * Genera una cadena SQL para la cláusula IN en una consulta SQL.
     *
     * Esta función toma un array asociativo $in que debe tener las claves:
     * - 'llave': representa el nombre de columna en la cláusula SQL IN
     * - 'values': un array de valores para la cláusula SQL IN
     *
     * Luego realiza las siguientes operaciones:
     * 1. Valida la existencia de las claves 'llave' y 'values' en el array proporcionado. Si algún de los claves no existe, retorna un error.
     * 2. Genera los datos `$data_in` basados en el array dado. Si ocurre un error mientras se genera `$data_in`, retorna un error.
     * 3. Genera la cadena SQL para la cláusula IN basado en `$data_in`. Si ocurre un error mientras se genera la cláusula SQL IN, retorna un error.
     * 4. Si todos los pasos anteriores se completan con éxito, retorna la cadena SQL para la cláusula IN.
     *
     * @param array $in  'llave': string, 'values': array } Array asociativo con las claves 'llave' y 'values'
     * @return string|array Retorna una cadena SQL para la cláusula IN si no hubo errores. En caso de error, devuelve un array que describe el error.
     *
     * @example genera_in(['llave' => 'id', 'values' => [1, 2, 3]]) retorna 'id IN (1,2,3)'
     *
     * @version 16.293.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.genera_in
     */
    private function genera_in(array $in): array|string
    {
        $keys = array('llave','values');
        $valida = $this->validacion->valida_existencia_keys( keys:$keys, registro: $in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar not_in',data: $valida);
        }
        $values = $in['values'];

        if(!is_array($values)){
            return $this->error->error(mensaje: 'Error values debe ser un array',data: $values, es_final: true);
        }

        $data_in = (new \gamboamartin\where\where())->data_in(in: $in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data in',data: $data_in);
        }

        $in_sql = $this->in_sql(llave:  $data_in->llave, values:$data_in->values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $in_sql);
        }
        return $in_sql;
    }

    /**
     * TOTAL
     * Genera una cadena SQL basada en los datos proporcionados.
     *
     * Este método toma como entrada un arreglo que especifica los datos a incluir
     * en la declaración SQL y retorna la cadena SQL resultante o un error si ocurre alguna falla.
     *
     * @param array $in Matriz asociativa que contiene los datos para la consulta SQL.
     *   Esta matriz debe contener las claves 'llave' y 'values'.
     *
     * @return array|string La cadena SQL construida si todo va bien o un error en caso contrario.
     *
     * @throws errores En caso de que alguna validación falle, como cuando faltan las claves
     *   requeridas en la matriz de entrada o cuando hay un problema al generar la secuencia SQL.
     *
     * @version 16.300.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.genera_in_sql
     */
    private function genera_in_sql(array $in): array|string
    {
        $in_sql = '';
        if(count($in)>0){
            $keys = array('llave','values');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar in',data: $valida);
            }
            $values = $in['values'];

            if(!is_array($values)){
                return $this->error->error(mensaje: 'Error values debe ser un array',data: $values, es_final: true);
            }
            $in_sql = $this->genera_in(in: $in);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql',data: $in_sql);
            }
            $in_sql = (new sql())->limpia_espacios_dobles(txt: $in_sql);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al limpiar sql',data: $in_sql);
            }

            $in_sql = str_replace('( (', '((', $in_sql);

        }
        $in_sql = (new sql())->limpia_espacios_dobles(txt: $in_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar sql',data: $in_sql);
        }

        return str_replace('( (', '((', $in_sql);
    }

    /**
     * TOTAL
     * Función que genera una instrucción SQL normalizada a partir de un arreglo
     *
     * @param array $in Arreglo de elementos con los que se va a generar la instrucción SQL
     * @return string|array $in_sql devuelve la instrucción SQL normalizada
     * @version 17.6.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.genera_in_sql_normalizado
     */
    private function genera_in_sql_normalizado(array $in): string|array
    {
        $in_sql = $this->genera_in_sql(in: $in);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data:$in_sql);
        }

        $in_sql = (new sql())->limpia_espacios_dobles(txt: $in_sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al limpiar in_sql',data:$in_sql);
        }
        return str_replace('( (', '((', $in_sql);
    }


    /**
     * TOTAL
     * La función in_sql genera y valida una instrucción SQL IN.
     *
     * @param string $llave El nombre del campo que se utilizará en la instrucción IN.
     * @param array $values Un array con los valores que se usarán en la instrucción IN.
     *
     * @return array|string Regresa una instrucción SQL IN si todo sale bien.
     * Regresa un mensaje de error si se detecta algún problema durante la generación o validación del SQL.
     *
     * La función sigue los siguientes pasos:
     * - Primero, verifica que la $llave no sea una cadena vacía.
     * - Luego, intenta generar una cadena con los valores para la instrucción IN.
     * - Después valida la instrucción `IN` generada.
     * - Finalmente, intenta generar una instrucción SQL `IN` completa y la retorna.
     *
     * Notas:
     * - Si se encuentra algún error durante el proceso, la función retorna inmediatamente un mensaje de error.
     * - Cada paso de generación y validación puede disparar un error, así que se comprueba después de cada paso.
     *
     * @version 16.291.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.in_sql
     */
    private function in_sql(string $llave, array $values): array|string
    {
        $llave = trim($llave);
        if($llave === ''){
            return $this->error->error(mensaje: 'Error la llave esta vacia',data: $llave, es_final: true);
        }

        $values_sql = (new \gamboamartin\where\where())->values_sql_in(values:$values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $values_sql);
        }
        $valida = (new sql())->valida_in(llave: $llave, values_sql: $values_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar in', data: $valida);
        }

        $in_sql = (new sql())->in(llave: $llave,values_sql:  $values_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $in_sql);
        }

        return $in_sql;
    }

    /**
     * TOTAL
     * Inicializa los parametros de un complemento para where
     * @param stdClass $complemento Complemento de datos sql
     * @param array $keys_data_filter Keys para filtros
     * @return array|stdClass
     * @author mgamboa
     * @fecha 2022-08-02 14:46
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.init_params_sql
     */
    final public function init_params_sql(stdClass $complemento, array $keys_data_filter): array|stdClass
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
     * TOTAL
     * La función limpia_filtros limpia y organiza los filtros proveídos para la consulta SQL.
     *
     * @param stdClass $filtros El objeto que contiene los filtros a limpiar y organizar.
     * @param array $keys_data_filter Un arreglo con las llaves que se van a utilizar para filtrar los datos.
     *
     * @return stdClass|array Retorna el objeto de filtros limpio y organizado, si ocurre un error retorna un arreglo con la información del error.
     *
     * @throws errores Si alguna llave del filtro está vacía, se lanza una excepción con el mensaje de error y el arreglo de datos del filtro.
     *
     * @version 16.316.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.limpia_filtros
     */
    final public function limpia_filtros(stdClass $filtros, array $keys_data_filter): stdClass|array
    {
        foreach($keys_data_filter as $key){
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error el key esta vacio', data: $keys_data_filter, es_final: true);
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
     * REG
     * Genera una parte de filtro SQL dinámico basado en un campo, columnas adicionales y valores de filtro.
     *
     * Esta función se encarga de validar y construir la cláusula SQL para un filtro especial basado en un campo específico,
     * utilizando columnas adicionales para determinar si hay ajustes necesarios en el nombre del campo.
     *
     * @param string $campo Nombre del campo a filtrar.
     *                      - Debe ser una cadena no vacía.
     * @param array $columnas_extra Array de columnas adicionales que podrían afectar la estructura del filtro.
     *                              - Ejemplo: `['columna_alias' => 'tabla.columna']`.
     * @param array $filtro Filtro con las condiciones y valores a aplicar.
     *                      - Debe contener una clave asociativa con el nombre del campo y, dentro de ella, la clave `valor`.
     *                      - Ejemplo: `['campo1' => ['valor' => 'valor1'], 'campo2' => ['valor' => 'valor2']]`.
     *
     * @return array|string Devuelve una cadena con el SQL del filtro generado o un array con detalles del error.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $campo = 'estatus';
     * $columnas_extra = ['estatus' => 'tabla.estatus'];
     * $filtro = [
     *     'estatus' => ['valor' => 'activo']
     * ];
     *
     * $resultado = $this->maqueta_filtro_especial(campo: $campo, columnas_extra: $columnas_extra, filtro: $filtro);
     *
     * // Resultado esperado:
     * // "tabla.estatus = 'activo'"
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: Campo vacío.
     * $campo = '';
     * $columnas_extra = ['estatus' => 'tabla.estatus'];
     * $filtro = [
     *     'estatus' => ['valor' => 'activo']
     * ];
     *
     * $resultado = $this->maqueta_filtro_especial(campo: $campo, columnas_extra: $columnas_extra, filtro: $filtro);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error al validar filtro',
     * //   'data' => [...]
     * // ]
     *
     * // Caso 2: Falta la clave `valor` en el filtro.
     * $campo = 'estatus';
     * $columnas_extra = ['estatus' => 'tabla.estatus'];
     * $filtro = [
     *     'estatus' => [] // Falta el índice 'valor'.
     * ];
     *
     * $resultado = $this->maqueta_filtro_especial(campo: $campo, columnas_extra: $columnas_extra, filtro: $filtro);
     * // Resultado:
     * // [
     * //   'error' => 1,
     * //   'mensaje' => 'Error al validar filtro',
     * //   'data' => [...]
     * // ]
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación del campo:**
     *    - Comprueba que `$campo` no esté vacío y que sea una cadena válida.
     * 2. **Validación del filtro:**
     *    - Verifica que `$filtro` contenga la clave `$campo` y dentro de ella, la clave `valor`.
     * 3. **Ajuste del campo:**
     *    - Si `$campo` está en `$columnas_extra`, utiliza la columna ajustada para construir el filtro.
     * 4. **Generación del SQL:**
     *    - Utiliza la función `data_sql` para construir la cláusula SQL basada en el campo y el valor del filtro.
     * 5. **Retorno:**
     *    - Devuelve la cláusula SQL generada o un array con detalles del error en caso de fallo.
     *
     * ### Casos de uso:
     * - Construcción dinámica de filtros en consultas SQL basados en condiciones complejas.
     * - Manejo de nombres de columnas ajustados mediante un mapeo en `$columnas_extra`.
     * - Simplificación de generación de SQL para aplicaciones con múltiples filtros y validaciones.
     *
     * ### Consideraciones:
     * - Asegúrate de proporcionar un `$campo` válido y de que el `$filtro` incluya la clave `valor` para evitar errores.
     * - `$columnas_extra` es opcional, pero si se usa, debe estar correctamente mapeado.
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

        $campo_filtro = $campo;

        $campo = (new \gamboamartin\where\where())->campo_filtro_especial(campo: $campo,
            columnas_extra:  $columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener campo',  data:$campo);
        }

        $data_sql = (new \gamboamartin\where\where())->data_sql(campo: $campo,campo_filtro:  $campo_filtro,
            filtro:  $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al genera sql',  data:$data_sql);
        }


        return $data_sql;
    }


    /**
     * TOTAL
     * Genera la condicion sql de un filtro especial
     * @param array $columnas_extra Conjunto de columnas en forma de subquery
     * @param array $filtro_esp //array con datos del filtro $filtro_esp[tabla.campo]= array('operador'=>'AND','valor'=>'x');
     *
     * @param string $filtro_especial_sql //condicion en forma de sql
     * @return array|string
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
     * @version 16.195.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.obten_filtro_especial
     *
     */

    private function obten_filtro_especial(
        array $columnas_extra, array $filtro_esp, string $filtro_especial_sql):array|string
    {
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
        $filtro_especial_sql_r = (new \gamboamartin\where\where())->genera_filtro_especial(campo:  $campo,
            data_sql: $data_sql, filtro_esp: $filtro_esp, filtro_especial_sql: $filtro_especial_sql);
        if(errores::$error){
            return $this->error->error(mensaje:"Error filtro",data: $filtro_especial_sql_r);
        }

        return $filtro_especial_sql_r;
    }

    /**
     * TOTAL
     * Aplica el filtro de paréntesis a un conjunto de filtros proporcionados.
     *
     * @param stdClass $filtros El objeto de filtros a procesar.
     * @param array $keys_data_filter Un arreglo de claves para aplicar el filtro.
     *
     * @return stdClass|array Devuelve el objeto de filtros con las modificaciones aplicadas,
     *  o un arreglo en caso de que ocurra un error.
     *
     * @version 16.318.1
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.parentesis_filtro
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
     * TOTAL
     * Verifica que la estructura de un complemento sql sea la correcta
     * @param stdClass $complemento Complemento de datos SQL a incializar
     * @param array $key_data_filter Filtros a limpiar o validar
     * @return bool|array
     * @version 1.245.39
     * @verfuncion 1.1.0
     * @fecha 2022-08-01 16:47
     * @author mgamboa
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.verifica_where
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
                    data: $complemento, es_final: true);
            }
        }
        return true;
    }

    /**
     * TOTAL
     * Genera un WHERE validado por el numero de parametros
     * @param stdClass $filtros Filtros a utilizar enb un WHERE
     * @param array $keys_data_filter Key de los filtros a limpiar o validar para convertir en obj
     * @author mgamboa
     * @fecha 2022-07-25 12:33
     * @return string|array
     * @version 17.7.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.where
     */
    private function where(stdClass $filtros, array $keys_data_filter): string|array
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
     * TOTAL
     * Genera un where base aplicando un estilo correcto SQL
     * @param stdClass $complemento Complemento de datos sql
     * @return array|stdClass
     * @fecha 2022-08-01 14:42
     * @author mgamboa
     * @version 20.7.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.where_base
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
     * TOTAL
     * Genera un filtro de tipo where valido
     * @param stdClass $complemento Complemento de datos sql
     * @param array $key_data_filter Keys de filtros para where
     * @return array|stdClass
     * @fecha 2022-08-02 09:43
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.where_filtro
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
     * TOTAL
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.where_mayus
     * Esta función convierte a mayúsculas la cláusula WHERE de un objeto complemento.
     * Si el WHERE no está definido o es una cadena vacía, no se realizará ninguna conversión.
     * El método devolverá un error si la cláusula WHERE es distinta de una cadena vacía y de 'WHERE'.
     *
     * @param stdClass $complemento El objeto que contiene la cláusula WHERE a convertir.
     * @return array|stdClass El objeto complemento con la cláusula WHERE convertida a mayúsculas o un error.
     * @version 17.49.0
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
            return $this->error->error(mensaje: 'Error where mal aplicado',data: $complemento->where, es_final: true);
        }
        return $complemento;
    }

    /**
     * REG
     * Genera una cláusula `WHERE` para una consulta SQL.
     *
     * Esta función toma un filtro en formato de cadena SQL y lo envuelve en una cláusula `WHERE`,
     * si el filtro no está vacío. Si el filtro está vacío, devuelve una cadena vacía.
     *
     * @param string $filtro_sql Cadena con el filtro SQL a aplicar.
     *                           - Puede contener condiciones como: `"campo = valor"`, `"campo > valor"`, etc.
     *                           - Si está vacío, no se genera la cláusula `WHERE`.
     *
     * @return string Devuelve la cláusula `WHERE` con el filtro proporcionado o una cadena vacía
     *                si el filtro está vacío.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * $filtro_sql = "monto > 100";
     * $resultado = $this->where_suma(filtro_sql: $filtro_sql);
     *
     * // Resultado esperado:
     * // "WHERE monto > 100"
     * ```
     *
     * ### Ejemplo de uso con filtro vacío:
     * ```php
     * $filtro_sql = "";
     * $resultado = $this->where_suma(filtro_sql: $filtro_sql);
     *
     * // Resultado esperado:
     * // ""
     * ```
     *
     * ### Proceso de la función:
     * 1. **Validación del filtro:**
     *    - Si `$filtro_sql` no está vacío, se genera la cláusula `WHERE`.
     *    - Si `$filtro_sql` está vacío, se retorna una cadena vacía.
     * 2. **Construcción de la cláusula:**
     *    - Si aplica, la cláusula `WHERE` se concatena con el filtro SQL.
     * 3. **Retorno del resultado:**
     *    - Una cadena con la cláusula `WHERE` o una cadena vacía.
     *
     * ### Casos de uso:
     * - Útil para agregar dinámicamente filtros a consultas SQL.
     * - Facilita la construcción de condiciones SQL sin repetir código.
     *
     * ### Consideraciones:
     * - Asegúrate de que el filtro SQL proporcionado sea válido y seguro para prevenir inyecciones SQL.
     */

    final public function where_suma(string $filtro_sql): string
    {
        $where = '';
        if (trim($filtro_sql) !== '') {
            $where = ' WHERE ' . $filtro_sql;
        }
        return $where;
    }


}
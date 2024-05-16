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
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     */
    final public function data_filtros_full(array $columnas_extra, array $diferente_de, array $filtro,
                                      array $filtro_especial, array $filtro_extra, array $filtro_fecha,
                                      array $filtro_rango, array $in, array $keys_data_filter, array $not_in,
                                      string $sql_extra, string $tipo_filtro): array|stdClass
    {

        $verifica_tf = $this->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función se utiliza para procesar los datos entrantes ($in) y los organiza en un formato específico.
     *
     * @param array $in Datos entrantes que se deben procesar.
     *                  Debe contener las claves 'llave' y 'values'.
     * @return array|stdClass Devuelve un objeto que contiene los datos procesados.
     *                        Si hay un error durante la validación, devuelve un array con detalles del error.
     *
     * @throws errores Si los datos entrantes no contienen las claves requeridas,
     *               o si 'values' no es un array. En caso de error, se devuelve un array con detalles del error.
     *
     * @version 16.259.1
     */
    private function data_in(array $in): array|stdClass
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
        $data = new stdClass();
        $data->llave = $in['llave'];
        $data->values = $in['values'];
        return $data;
    }


    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función gestiona un array asociativo que implementa filtros especiales para la consulta SQL
     * que está siendo generada.
     *
     * @param array $data_filtro El array contiene múltiples campos para filtrar.
     *
     * @return stdClass|array Retorna un objeto con 5 propiedades: campo, operador, valor, comparacion,
     *                         y condicion si la operación fue exitosa. En caso de error, devuelve un objeto Error.
     *
     * @throws errores si el array $data_filtro está vacío.
     * @throws errores si el campo `operador` no existe en cada campo del array $data_filtro.
     * @throws errores si el campo `valor` no existe en cada campo del array $data_filtro.
     * @throws errores si el campo `comparacion` no existe en cada campo del array $data_filtro.
     *
     * @example
     * $where = new Where();
     * $filtrado = $where->datos_filtro_especial([
     *     'age' => [
     *         'operador' => '>',
     *         'valor' => '21',
     *         'comparacion' => 'AND',
     *     ],
     * ]);
     * // Resultado:
     * // stdClass Object
     * // (
     * //    [campo] => age
     * //    [operador] => >
     * //    [valor] => 21
     * //    [comparacion] => AND
     * //    [condicion] => age>'21'
     * // )
     * @version 16.248.1
     */
    private function datos_filtro_especial(array $data_filtro):array|stdClass
    {
        if(count($data_filtro) === 0){
            return $this->error->error(mensaje:'Error data_filtro esta vacio',  data:$data_filtro, es_final: true);
        }
        $campo = (new \gamboamartin\src\where())->campo_data_filtro(data_filtro: $data_filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener campo',data:  $campo);
        }

        if(!isset($data_filtro[$campo]['operador'])){
            return $this->error->error(mensaje:'Error data_filtro['.$campo.'][operador] debe existir',
                data:$data_filtro, es_final: true);
        }

        $operador = $data_filtro[$campo]['operador'];
        if($operador===''){
            return $this->error->error(mensaje:'Error el operador debe de existir',data:$operador, es_final: true);
        }

        if(!isset($data_filtro[$campo]['valor'])){
            return $this->error->error(mensaje:'Error data_filtro['.$campo.'][valor] debe existir',
                data:$data_filtro, es_final: true);
        }
        if(!isset($data_filtro[$campo]['comparacion'])){
            return $this->error->error(mensaje:'Error data_filtro['.$campo.'][comparacion] debe existir',
                data:$data_filtro, es_final: true);
        }

        $valor = $data_filtro[$campo]['valor'];
        if($valor===''){
            return $this->error->error(mensaje:'Error el operador debe de existir',data:$valor, es_final: true);
        }
        $valor = addslashes($valor);
        $comparacion = $data_filtro[$campo]['comparacion'];
        $condicion = $campo.$operador."'$valor'";

        $datos = new stdClass();
        $datos->campo = $campo;
        $datos->operador = $operador;
        $datos->valor = $valor;
        $datos->comparacion = $comparacion;
        $datos->condicion = $condicion;

        return $datos;

    }


    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
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
        $and = (new \gamboamartin\src\where())->and_filtro_fecha(txt: $diferente_de_sql);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al integrar AND", data: $and);
        }

        $campo = addslashes($campo);
        $value = addslashes($value);

        return " $and $campo <> '$value' ";
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     */
    private function filtro_especial_sql(array $columnas_extra, array $filtro_especial):array|string{ //DEBUG

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
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     * @version 16.258.1
     *
     */
    private function filtro_extra_sql(array $filtro_extra):array|string{
        $filtro_extra_sql = '';
        foreach($filtro_extra as $data_filtro){
            if(!is_array($data_filtro)){
                return $this->error->error(mensaje: 'Error $data_filtro debe ser un array',data: $filtro_extra,
                    es_final: true);
            }
            $filtro_extra_sql = $this->integra_filtro_extra(
                data_filtro: $data_filtro, filtro_extra_sql: $filtro_extra_sql);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar filtro',data:  $filtro_extra_sql);
            }
        }

        return $filtro_extra_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función filtro_extra_sql_genera
     *
     * @param string $comparacion La cadena de texto utilizada para comparar
     * @param string $condicion La cadena de texto que representa la condición
     * @param string $filtro_extra_sql Una expresión SQL adicional que se añadirá al filtro
     * @return string $filtro_extra_sql Retorna la cadena de texto SQL actualizada
     *
     * Esta función genera un filtro SQL adicional a partir de las condiciones y la cadena de comparación proporcionadas.
     * Si el filtro SQL adicional ya está establecido, la función añadirá la condición a este utilizando la cadena de comparación.
     * Sin embargo, si el filtro SQL adicional no está establecido, la función simplemente añadirá la condición a este.
     * Finalmente, la función devuelve el filtro SQL adicional actualizado.
     * @version 16.252.1
     */
    private function filtro_extra_sql_genera(string $comparacion, string $condicion, string $filtro_extra_sql): string
    {
        if($filtro_extra_sql === ''){
            $filtro_extra_sql .= $condicion;
        }
        else {
            $filtro_extra_sql .=  $comparacion . $condicion;
        }
        return $filtro_extra_sql;

    }


    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función realiza una serie de filtros completos dados los parámetros proporcionados.
     *
     * @param stdClass $filtros       - Objeto que contiene los filtros que se aplicarán.
     * @param array $keys_data_filter - Claves del array que se utilizarán en los filtros.
     *
     * @return stdClass - Devuelve los filtros después de haber aplicado todas las operaciones.
     *
     * @throws errores Si hay un error al limpiar los filtros.
     * @version 17.17.0
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
     *
     * Inicializa los key del filtro como vacios
     * @param stdClass $complemento Complemento de datos SQL a incializar
     * @param array $keys_data_filter Keys a limpiar o validar
     * @return bool
     * @version 1.237.39
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-08-01 13:07
     * @url https://github.com/gamboamartin/administrador/wiki/administrador-base-orm-where#funci%C3%B3n-filtros_vacios
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera la condicion sql de un filtro especial
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
     * @throws errores $filtro_especial_sql != '' $filtro_esp[$campo]['comparacion'] no existe,
     *  Debe existir $filtro_esp[$campo]['comparacion']
     * @throws errores $filtro_especial_sql != '' = $data_sql = '',  data_sql debe tener info
     * @version 16.182.0
     */

    private function genera_filtro_especial(string $campo, string $data_sql, array $filtro_esp,
                                            string $filtro_especial_sql):array|string{//FIN //DEBUG
        if($filtro_especial_sql === ''){
            $filtro_especial_sql .= $data_sql;
        }
        else{
            if(!isset($filtro_esp[$campo]['comparacion'])){
                return $this->error->error(mensaje: 'Error $filtro_esp[$campo][\'comparacion\'] debe existir',
                    data: $filtro_esp, es_final: true);
            }
            if(trim($data_sql) === ''){
                return $this->error->error(mensaje:'Error $data_sql no puede venir vacio', data:$data_sql,
                    es_final: true);
            }

            $filtro_especial_sql .= ' '.$filtro_esp[$campo]['comparacion'].' '.$data_sql;
        }

        return $filtro_especial_sql;
    }



    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     */
    private function genera_filtros_iniciales(string $diferente_de_sql, string $filtro_especial_sql,
                                              string $filtro_extra_sql, string $filtro_rango_sql, string $in_sql,
                                              array $keys_data_filter, string $not_in_sql, string $sentencia,
                                              string $sql_extra, string $filtro_fecha_sql = ''): array|stdClass
    {
        $filtros = (new \gamboamartin\src\where())->asigna_data_filtro(diferente_de_sql: $diferente_de_sql,
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
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     */
    private function genera_filtros_sql(array $columnas_extra, array $diferente_de, array $filtro,
                                        array $filtro_especial, array $filtro_extra, array $filtro_rango, array $in,
                                        array $keys_data_filter, array $not_in, string $sql_extra, string $tipo_filtro,
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
        $filtro_rango_sql = (new \gamboamartin\src\where())->filtro_rango_sql(filtro_rango: $filtro_rango);
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


        $in_sql = $this->genera_in_sql_normalizado(in: $in);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar in_sql',data:$in_sql);
        }

        $filtro_fecha_sql = (new \gamboamartin\src\where())->filtro_fecha(filtro_fecha: $filtro_fecha);
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
     * POR DOCUMENTAR EN WIKI FINAL REV
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

        $data_in = $this->data_in(in: $in);
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
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función que genera una instrucción SQL normalizada a partir de un arreglo
     *
     * @param array $in Arreglo de elementos con los que se va a generar la instrucción SQL
     * @return string|array $in_sql devuelve la instrucción SQL normalizada
     * @version 17.6.0
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método privado que genera una cláusula NOT IN SQL a partir de un arreglo proporcionado.
     *
     * @param array $not_in Arreglo de elementos a ser excluidos en la consulta SQL.
     *
     * @return array|string Regresa la cláusula NOT IN SQL generada o un mensaje de error en caso de un error
     * detectado en la generación de la cláusula.
     *
     * @throws errores Lanza una excepción de tipo Error en caso de error en la generación de la cláusula SQL.
     * @version 16.276.1
     */
    private function genera_not_in(array $not_in): array|string
    {
        $data_in = $this->data_in(in: $not_in);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data in',data: $data_in);
        }

        $not_in_sql = $this->not_in_sql(llave:  $data_in->llave, values:$data_in->values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $not_in_sql);
        }
        return $not_in_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera la cláusula SQL NOT IN basada en los valores proporcionados.
     *
     * Esta función toma una matriz asociativa como parámetro, donde `llave` es el nombre del campo y `values` es una
     * matriz de valores que se utilizarán en la cláusula NOT IN en una sentencia SQL. Luego, genera la cláusula SQL
     * NOT IN correspondiente.
     *
     * Si ocurre algún error durante la validación de los parámetros o la generación de la cláusula SQL NOT IN,
     * la función devolverá un mensaje de error.
     *
     * @param array $not_in Matriz asociativa con los claves 'llave' y 'values'.
     *        Ejemplo: ['llave' => 'miCampo', 'values' => [1, 2, 3]]
     *
     * @return string|array Devuelve la cláusula SQL NOT IN como una cadena si la función se ejecuta correctamente.
     *                      En caso de error, devuelve una matriz con los detalles del error.
     *
     * @version 16.278.1
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera la sentencia SQL base de filtro según el tipo de filtro
     * proporcionado (numeros o textos).
     *
     * @param array $columnas_extra Array de columnas adicionales para incluir en la consulta.
     * @param array $filtro Array de condiciones para ser incluidos en la cláusula WHERE de la sentencia.
     * @param string $tipo_filtro Define el tipo del filtro. Puede ser "numeros" o "textos".
     *
     * @return array|string Retorna la sentencia generada o un string describiendo un error si sucede alguno.
     *
     * @throws errores si el tipo de filtro no es válido.
     * @version 16.102.0
     */
    private function genera_sentencia_base(array $columnas_extra,  array $filtro, string $tipo_filtro):array|string{
        $verifica_tf = $this->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }
        $sentencia = '';
        if($tipo_filtro === 'numeros') {
            $sentencia = (new \gamboamartin\src\where())->genera_and(columnas_extra: $columnas_extra, filtro: $filtro);
            if(errores::$error){
                return $this->error->error(mensaje: "Error en and",data:$sentencia);
            }
        }
        elseif ($tipo_filtro==='textos'){
            $sentencia = (new \gamboamartin\src\where())->genera_and_textos(columnas_extra: $columnas_extra,filtro: $filtro);
            if(errores::$error){
                return $this->error->error(mensaje: "Error en texto",data:$sentencia);
            }
        }
        return $sentencia;
    }



    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     */
    private function in_sql(string $llave, array $values): array|string
    {
        $llave = trim($llave);
        if($llave === ''){
            return $this->error->error(mensaje: 'Error la llave esta vacia',data: $llave, es_final: true);
        }

        $values_sql = $this->values_sql_in(values:$values);
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
     * Inicializa los parametros de un complemento para where
     * @param stdClass $complemento Complemento de datos sql
     * @param array $keys_data_filter Keys para filtros
     * @return array|stdClass
     * @author mgamboa
     * @fecha 2022-08-02 14:46
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función toma un filtro adicional y lo integra a la consulta SQL actual.
     * Recibe una matriz de datos del filtro y una cadena que representa el filtro SQL extra.
     *
     * @param array $data_filtro La matriz de datos del filtro. La función devuelve un error si la matriz está vacía.
     * @param string $filtro_extra_sql La cadena que representa el filtro extra para la consulta SQL.
     *
     * Si se produce algún error durante el proceso, la función retornará detalles sobre el error.
     *
     * @return object|string|array Retorna el filtro SQL extra integrado en caso de éxito. Si ocurre un error,
     *  retorna un objeto de error.
     * @version 16.257.1
     */
    private function integra_filtro_extra(array $data_filtro, string $filtro_extra_sql): object|string|array
    {
        if(count($data_filtro) === 0){
            return $this->error->error(mensaje:'Error data_filtro esta vacio',  data:$data_filtro, es_final: true);
        }

        $datos = $this->datos_filtro_especial(data_filtro: $data_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos de filtro',data:  $datos);
        }

        $filtro_extra_sql = $this->filtro_extra_sql_genera(comparacion: $datos->comparacion,
            condicion:  $datos->condicion,filtro_extra_sql:  $filtro_extra_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro',data:  $filtro_extra_sql);
        }

        return $filtro_extra_sql;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     *
     * Genera la condicion sql de un filtro especial
     *
     * @param string $campo campo de una tabla tabla.campo
     * @param array $columnas_extra Campos en forma de subquery del modelo
     * @param array $filtro filtro a validar
     *
     * @return array|string
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
     * @version 16.164.0
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

        $campo = (new \gamboamartin\src\where())->campo_filtro_especial(campo: $campo,columnas_extra:  $columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener campo',  data:$campo);
        }

        $data_sql = (new \gamboamartin\src\where())->data_sql(campo: $campo,campo_filtro:  $campo_filtro,filtro:  $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al genera sql',  data:$data_sql);
        }


        return $data_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera una cláusula SQL NOT IN a partir de una llave y valores proporcionados.
     *
     * @param string $llave Clave que será usada en la cláusula NOT IN.
     * @param array $values Valores que serán incorporados en la cláusula NOT IN.
     *
     * @return string|array Devuelve una cadena que contiene una cláusula SQL NOT IN si la operación es exitosa.
     * Si ocurre un error, devuelve un array conteniendo detalles sobre el error.
     *
     * ## Uso:
     * ```php
     * not_in_sql("id", [1, 2, 3])
     * ```
     *
     * ## Ejemplo de respuesta en caso de éxito:
     * ```sql
     * "id NOT IN (1, 2, 3)"
     * ```
     *
     * ## Ejemplo de respuesta en caso de error:
     * ```php
     * [
     *     "codigo" => "ERR_CODE",
     *     "mensaje" => "Descripción detallada del error"
     * ]
     * ```
     * @version 16.272.1
     */
    private function not_in_sql(string $llave, array $values): array|string
    {
        $llave = trim($llave);
        if($llave === ''){
            return $this->error->error(mensaje: 'Error la llave esta vacia',data: $llave, es_final: true);
        }

        $values_sql = $this->values_sql_in(values:$values);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $values_sql);
        }

        $not_in_sql = '';
        if($values_sql!==''){
            $not_in_sql.="$llave NOT IN ($values_sql)";
        }

        return $not_in_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Aplica el filtro de paréntesis a un conjunto de filtros proporcionados.
     *
     * @param stdClass $filtros El objeto de filtros a procesar.
     * @param array $keys_data_filter Un arreglo de claves para aplicar el filtro.
     *
     * @return stdClass|array Devuelve el objeto de filtros con las modificaciones aplicadas,
     *  o un arreglo en caso de que ocurra un error.
     *
     * @version 16.318.1
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
     * Verifica el tipo de filtro proporcionado.
     *
     * @param string $tipo_filtro El tipo de filtro a verificar.
     * @return true|array Devuelve true si el tipo de filtro es correcto,
     *         si no, devuelve un array con un error.
     *
     * La función realiza las siguientes acciones:
     * 1. Limpia el tipo de filtro ingresado.
     * 2. Si el tipo de filtro es una cadena vacía, se establece como 'numeros'.
     * 3. Define los tipos permitidos de filtro como 'numeros' y 'textos'.
     * 4. Verifica si el tipo de filtro ingresado pertenece a los tipos permitidos.
     *    Si no es así, crea un nuevo objeto stdClass y establece la propiedad
     *    tipo_filtro con el valor ingresado y retorna un error con el mensaje y los datos correspondientes.
     * @version 13.8.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.where.verifica_tipo_filtro.21.10.0
     */
    final public function verifica_tipo_filtro(string $tipo_filtro): true|array
    {
        $tipo_filtro = trim($tipo_filtro);
        if($tipo_filtro === ''){
            $tipo_filtro = 'numeros';
        }
        $tipos_permitidos = array('numeros','textos');
        if(!in_array($tipo_filtro,$tipos_permitidos)){

            $params = new stdClass();
            $params->tipo_filtro = $tipo_filtro;

            return $this->error->error(
                mensaje: 'Error el tipo filtro no es correcto los filtros pueden ser o numeros o textos',
                data: $params, es_final: true);
        }
        return true;
    }



    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Este método comprueba si el valor proporcionado está vacío y, en caso de que no lo esté,
     * añade una coma al final de la cadena de valores SQL existente.
     *
     * @param string $value El valor para comprobar y añadir a la cadena SQL.
     * @param string $values_sql La cadena SQL existente que se actualizará.
     *
     * @return array|stdClass Devuelve un objeto con el valor y la coma si todo está bien,
     *                        de lo contrario retorna un mensaje de error.
     *
     * @throws errores Si el valor está vacío.
     * @version 16.261.1
     */
    private function value_coma(string $value, string $values_sql): array|stdClass
    {
        $values_sql = trim($values_sql);
        $value = trim($value);
        if($value === ''){
            return $this->error->error(mensaje: 'Error value esta vacio',data: $value, es_final: true);
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función privada toma un array de valores y los procesa para formar una parte de una consulta SQL.
     *
     * Recorre cada valor en el conjunto de valores proporcionado para escapar y formatear correctamente el valor en
     * una representación de cadena que puede ser utilizada en una consulta SQL.
     * Los valores son escapados para seguridad y entre comillas para representarlos como cadenas en SQL.
     * Finalmente, cada valor procesado se concatena a la cadena $values_sql con una coma y el valor.
     *
     * Si ocurre algún error durante este proceso, se devolverá un mensaje de error.
     *
     * @param array $values Un conjunto de valores que se deben formatear y escapar para su uso en una consulta SQL.
     * @return string|array Una cadena que representa la parte de una consulta SQL con valores formateados y escapados,
     * o un mensaje de error si se encuentra algún problema.
     * @version 16.262.1
     */
    private function values_sql_in(array $values): string|array
    {
        $values_sql = '';
        foreach ($values as $value){
            $value = trim($value);
            if($value === ''){
                return $this->error->error(mensaje: 'Error value esta vacio',data: $value, es_final: true);
            }
            $data = $this->value_coma(value:$value, values_sql: $values_sql);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error obtener datos de value',data: $data);
            }

            $value = addslashes($value);
            $value = "'$value'";

            $values_sql.="$data->coma$value";
        }
        return $values_sql;
    }

    /**
     * @url https://github.com/gamboamartin/administrador/wiki/administrador-base-orm-where#funci%C3%B3n-verifica_where
     * Verifica que la estructura de un complemento sql sea la correcta
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
                    data: $complemento, es_final: true);
            }
        }
        return true;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera un WHERE validado por el numero de parametros
     * @param stdClass $filtros Filtros a utilizar enb un WHERE
     * @param array $keys_data_filter Key de los filtros a limpiar o validar para convertir en obj
     * @author mgamboa
     * @fecha 2022-07-25 12:33
     * @return string|array
     * @version 17.7.0
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
     * Genera un where base aplicando un estilo correcto SQL
     * @param stdClass $complemento Complemento de datos sql
     * @return array|stdClass
     * @fecha 2022-08-01 14:42
     * @author mgamboa
     * @version 20.7.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador-base-orm-where#funci%C3%B3n-where_base
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
     * @fecha 2022-08-02 09:43
     * @url https://github.com/gamboamartin/administrador/wiki/administrador-base-orm-where#funci%C3%B3n-where_filtro
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
     * @url https://github.com/gamboamartin/administrador/wiki/administrador-base-orm-where#funci%C3%B3n-where_mayus
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
     * Antepone la palabra WHERE al filtro mandado por parametros
     * @param string $filtro_sql filtro por aplicar
     * @return string filtro enviado por parametros anteponiendo la palabra WHERE
     * @version 1.489.49
     */
    final public function where_suma(string $filtro_sql): string
    {
        $where = '';
        if(trim($filtro_sql) !== '' ){
            $where = ' WHERE '. $filtro_sql;
        }
        return $where;

    }

}
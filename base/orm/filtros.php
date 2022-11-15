<?php
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class filtros{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * Genera un complemento con datos para filtro
     * @param stdClass $complemento Complemento previo
     * @param modelo $modelo Modelo en ejecucion
     * @return array|stdClass
     * @version 1.560.51
     */
    private function complemento(stdClass $complemento, modelo $modelo): array|stdClass
    {
        $complemento_ = (new where())->limpia_filtros(filtros: $complemento,keys_data_filter:  $modelo->columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al limpiar filtros',data:$complemento_);
        }

        $complemento_r = (new where())->init_params_sql(complemento: $complemento_,
            keys_data_filter: $modelo->keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al inicializar params',data:$complemento_r);
        }

        $complemento_r = $this->inicializa_complemento(complemento: $complemento_r);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al inicializar complemento',data:$complemento_r);
        }
        return $complemento_r;
    }

    /**
     * Genera el complemento completo para la ejecucion de un SELECT en forma de SQL
     * @param bool $aplica_seguridad si aplica seguridad verifica que el usuario tenga acceso
     * @param array $filtro Filtro base para ejecucion de WHERE genera ANDS
     * @param array $filtro_especial arreglo con las condiciones $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x')
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
     * @param array $filtro_rango
     *                  Opcion1.- Debe ser un array con la siguiente forma array('valor1'=>'valor','valor2'=>'valor')
     *                  Opcion2.-
     *                      Debe ser un array con la siguiente forma
     *                          array('valor1'=>'valor','valor2'=>'valor','valor_campo'=>true)
     * @param array $group_by Es un array con la forma array(0=>'tabla.campo', (int)N=>(string)'tabla.campo')
     * @param int $limit Numero de registros a mostrar
     * @param modelo $modelo modelo en ejecucion
     * @param array $not_in Conjunto de valores para not_in not_in[llave] = string, not_in['values'] = array()
     * @param int $offset Numero de inicio de registros
     * @param array  $order con parametros para generar sentencia
     * @param string $sql_extra Sql previo o extra si existe forzara la integracion de un WHERE
     * @param string $tipo_filtro Si es numero es un filtro exacto si es texto es con %%
     * @param array $filtro_fecha Filtros de fecha para sql filtro[campo_1], filtro[campo_2], filtro[fecha]
     * @param array $in Arreglo con los elementos para integrar un IN en SQL in[llave] = tabla.campo, in['values'] = array()
     * @param array $diferente_de Arreglo con los elementos para integrar un diferente de en SQL
     * @author mgamboa
     * @fecha 2022-07-27 11:07
     * @return array|stdClass
     * @version 1.575.51
     */
    public function complemento_sql(bool $aplica_seguridad, array $diferente_de, array $filtro, array $filtro_especial,
                                    array $filtro_extra, array $filtro_rango, array $group_by, array $in, int $limit,
                                    modelo $modelo, array $not_in, int $offset, array $order, string $sql_extra,
                                    string $tipo_filtro, array $filtro_fecha = array()): array|stdClass
    {

        if($limit<0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0',data:  $limit);
        }
        if($offset<0){
            return $this->error->error(mensaje: 'Error $offset debe ser mayor o igual a 0',data: $offset);

        }
        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data:$verifica_tf);
        }

        $params = (new params_sql())->params_sql(aplica_seguridad: $aplica_seguridad, group_by: $group_by,
            limit:  $limit,modelo: $modelo,offset:  $offset, order:  $order,sql_where_previo: $sql_extra);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar parametros sql',data:$params);
        }


        $filtros = (new where())->data_filtros_full(columnas_extra: $modelo->columnas_extra,
            diferente_de: $diferente_de, filtro: $filtro, filtro_especial:  $filtro_especial,
            filtro_extra:  $filtro_extra, filtro_fecha:  $filtro_fecha, filtro_rango:  $filtro_rango, in: $in,
            keys_data_filter: $modelo->keys_data_filter, not_in: $not_in, sql_extra: $sql_extra,
            tipo_filtro: $tipo_filtro);

        if(!isset($filtros->in)){
            $filtros->in = '';
        }

        $filtros->in = str_replace('( (', '((', $filtros->in);
        $filtros->in = str_replace('  ', ' ', $filtros->in);
        $filtros->in = str_replace('  ', ' ', $filtros->in);
        $filtros->in = str_replace('  ', ' ', $filtros->in);
        $filtros->in = str_replace('  ', ' ', $filtros->in);
        $filtros->in = str_replace('( (', '((', $filtros->in);
        $filtros->in = trim($filtros->in);


        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtros',data:$filtros);
        }
        $filtros->params = $params;
        return $filtros;
    }

    /**
     * Genera el sql completo para una sentencia select con wheres
     * @param stdClass $complemento Complemento de filtros a integrar en un select
     * @param string $consulta SQL PREVIO
     * @param modelo $modelo Modelo en ejecucion
     * @return string|array
     * @fecha 2022-08-02 15:53
     * @author mgamboa
     * @version 1.562.51
     */
    public function consulta_full_and(stdClass $complemento, string $consulta, modelo $modelo): string|array
    {

        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error(mensaje: 'Error $consulta no puede venir vacia',data: $consulta);
        }

        $complemento_r = $this->complemento(complemento: $complemento, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al inicializar complemento',data:$complemento_r);
        }


        $sql = $this->sql(complemento: $complemento_r, consulta_previa: $consulta);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data:$sql);
        }
        $sql = trim($sql);
        $modelo->consulta = $sql;
        return $modelo->consulta;
    }



    /**
     *
     * @param string $fecha
     * @param modelo_base $modelo
     * @return array
     */
    public function filtro_fecha_final(string $fecha, modelo_base $modelo): array
    {
        $name_modelo = $this->init_name_model(fecha: $fecha,modelo:  $modelo);
        if(errores::$error){
            return $this->error->error("Error al inicializa name model", $name_modelo);
        }


        $filtro[$fecha]['valor'] = $name_modelo.'.fecha_final';
        $filtro[$fecha]['operador'] = '<=';
        $filtro[$fecha]['comparacion'] = 'AND';
        $filtro[$fecha]['valor_es_campo'] = true;

        return $filtro;
    }

    /**
     *
     * @param string $fecha
     * @param modelo_base $modelo
     * @return array
     */
    public function filtro_fecha_inicial(string $fecha, modelo_base $modelo): array
    {

        $name_modelo = $this->init_name_model(fecha: $fecha,modelo:  $modelo);
        if(errores::$error){
            return $this->error->error("Error al inicializa name model", $name_modelo);
        }

        $filtro[$fecha]['valor'] = $name_modelo.'.fecha_inicial';
        $filtro[$fecha]['operador'] = '>=';
        $filtro[$fecha]['valor_es_campo'] = true;

        return $filtro;

    }



    /**
     * PRUEBAS FINALIZADAS
     * @param string $monto
     * @param string $campo
     * @param modelo_base $modelo
     * @return array
     */
    public function filtro_monto_ini(string $monto, string $campo, modelo_base $modelo): array
    {

        $data_filtro = $this->init_filtro_monto(campo: $campo,modelo:  $modelo,monto:  $monto);
        if(errores::$error){
            return $this->error->error("Error inicializa filtros", $data_filtro);
        }

        $filtro["$monto"]['valor'] = $data_filtro->tabla.'.'.$data_filtro->campo;
        $filtro["$monto"]['operador'] = '>=';
        $filtro["$monto"]['comparacion'] = 'AND';
        $filtro["$monto"]['valor_es_campo'] = true;

        return $filtro;
    }

    public function filtro_monto_fin(string $monto, string $campo, modelo_base $modelo): array
    {
        $data_filtro = $this->init_filtro_monto(campo: $campo,modelo:  $modelo,monto:  $monto);
        if(errores::$error){
            return $this->error->error("Error inicializa filtros", $data_filtro);
        }

        $filtro["$monto"]['valor'] = $data_filtro->tabla.'.'.$data_filtro->campo;
        $filtro["$monto"]['operador'] = '<=';
        $filtro["$monto"]['comparacion'] = 'AND';
        $filtro["$monto"]['valor_es_campo'] = true;

        return $filtro;
    }


    /**
     * Inicializa los datos de un complemento
     * @param stdClass $complemento Complemento previamente cargado
     * @return array|stdClass
     * @version 1.555.51
     */
    private function inicializa_complemento(stdClass $complemento): array|stdClass
    {
        $keys = $this->keys_complemento();
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener keys',data:$keys);
        }

        $complemento = $this->init_complemento(complemento:$complemento,keys: $keys);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al inicializar complemento',data:$complemento);
        }
        return $complemento;
    }

    /**
     * Inicializa los keys de un complemento para filtro
     * @param stdClass $complemento complemento previo
     * @param array $keys Keys a incializar
     * @return stdClass|array
     * @version 1.554.51
     */
    private function init_complemento(stdClass $complemento, array $keys): stdClass|array
    {
        if(count($keys) === 0){
            return $this->error->error(mensaje:'Error los keys de un complemento esta vacio',data:$keys);
        }
        foreach ($keys as $key){
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje:'Error el key esta vacio',data:$key);
            }
            if(!isset($complemento->$key)){
                $complemento->$key = '';
            }
        }
        return $complemento;
    }

    private function init_filtro_monto(string $campo, modelo_base $modelo, float $monto): array|stdClass
    {
        if($monto<0.0){
            return $this->error->error("Error el monto es menor a 0", $monto);
        }
        if($modelo->tabla === ''){
            return $this->error->error("Error tabla vacia", $modelo->tabla);
        }
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);

        if($modelo->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$modelo->tabla);
        }

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error("Error campo vacio", $campo);
        }

        $data = new stdClass();
        $data->campo = $campo;
        $data->tabla = $modelo->tabla;
        return $data;

    }

    private function init_name_model(string $fecha, modelo_base $modelo): array|string
    {
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return $this->error->error("Error fecha", $valida);
        }
        if($modelo->tabla === ''){
            return $this->error->error("Error tabla vacia", $modelo->tabla);
        }
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);

        if($modelo->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$modelo->tabla);
        }
        return $modelo->tabla;
    }

    /**
     * Obtiene los keys de un complemento de filtros para AND
     * @return string[]
     * @version 1.553.51
     */
    private function keys_complemento(): array
    {
        return array('filtro_especial','filtro_extra','filtro_fecha','filtro_rango','in','not_in','sentencia','sql_extra');
    }

    /**
     * Integra el resultado de complemento para un SQL
     * @param stdClass $complemento Complemento
     * @param string $consulta_previa Sql previo
     * @return string
     * @version 1.561.51
     */
    private function sql(stdClass $complemento, string $consulta_previa): string
    {
        $keys = array('filtro_especial','filtro_extra','filtro_fecha','filtro_rango','in','not_in','diferente_de',
            'sentencia', 'sql_extra','where');

        foreach ($keys as $key){
            if(!isset($complemento->$key)){
                $complemento->$key = '';
            }
        }

        if(!isset($complemento->params)){
            $complemento->params = new stdClass();
        }

        $keys = array('group_by','limit','offset','order');

        foreach ($keys as $key){
            if(!isset($complemento->params->$key)){
                $complemento->params->$key = '';
            }
        }

        $sql = $consulta_previa.$complemento->where.$complemento->sentencia.' '. $complemento->filtro_especial.' ';

        $sql.= $complemento->filtro_rango.' '.$complemento->filtro_fecha.' ';

        $sql.= $complemento->filtro_extra.' '.$complemento->in.' '.$complemento->not_in.' '.$complemento->diferente_de.' '.
            $complemento->sql_extra.' ';

        $sql.= $complemento->params->group_by.' '.$complemento->params->order.' ';
        $sql.= $complemento->params->limit.' '.$complemento->params->offset;

        $sql = str_replace('  ', ' ', $sql);
        $sql = str_replace('  ', ' ', $sql);
        $sql = str_replace('  ', ' ', $sql);
        $sql = str_replace('  ', ' ', $sql);
        $sql = str_replace('( (', '((', $sql);

        return str_replace('  ', ' ', $sql);
    }

}

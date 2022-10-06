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
     * @author mgamboa
     * @fecha 2022-07-27 11:07
     * @return array|stdClass
     */
    public function complemento_sql(bool $aplica_seguridad, array $filtro, array $filtro_especial,
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


        $filtros = (new where())->data_filtros_full(columnas_extra: $modelo->columnas_extra, filtro: $filtro,
            filtro_especial:  $filtro_especial, filtro_extra:  $filtro_extra, filtro_fecha:  $filtro_fecha,
            filtro_rango:  $filtro_rango, in: $in, keys_data_filter: $modelo->keys_data_filter, not_in: $not_in,
            sql_extra: $sql_extra, tipo_filtro: $tipo_filtro);


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
     */
    public function consulta_full_and(stdClass $complemento, string $consulta, modelo $modelo): string|array
    {

        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error(mensaje: 'Error $consulta no puede venir vacia',data: $consulta);
        }

        $complemento_ = (new where())->limpia_filtros(filtros: $complemento,keys_data_filter:  $modelo->columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al limpiar filtros',data:$complemento_);
        }

        $complemento_r = (new where())->init_params_sql(complemento: $complemento_,
            keys_data_filter: $modelo->keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al inicializar params',data:$complemento_r);
        }


        $keys = array('filtro_especial','filtro_extra','filtro_fecha','filtro_rango','in','not_in','sentencia','sql_extra');

        foreach ($keys as $key){
            if(!isset($complemento_r->$key)){
                $complemento_r->$key = '';
            }
        }

        /**
         * REFACTORIZAR
         */

        $modelo->consulta = $consulta.$complemento_r->where.$complemento_r->sentencia.' '.
            $complemento_r->filtro_especial.' ';

        $modelo->consulta.= $complemento_r->filtro_rango.' '.$complemento_r->filtro_fecha.' ';
        $modelo->consulta.= $complemento_r->filtro_extra.' '.$complemento_r->in.' '.$complemento_r->not_in.' '.
            $complemento_r->sql_extra.' ';

        $modelo->consulta.= $complemento_r->params->group_by.' '.$complemento_r->params->order.' ';
        $modelo->consulta.= $complemento_r->params->limit.' '.$complemento_r->params->offset;
        return $modelo->consulta;
    }

    /**
     *
     * Devuelve un arreglo que contiene un texto que indica el exito de la sentencia, tambien la consulta inicial de sql y por
     * @param string $filtro_especial_sql sql previo
     * @return array|string
     * @example
     *      $data_filtro_especial_final = $this->filtro_especial_final($filtro_especial_sql,$where);
     *
     * @uses modelo
     */

    private function filtro_especial_final(string $filtro_especial_sql):array|string{
        $filtro_especial_sql_env = $filtro_especial_sql;
        if($filtro_especial_sql !=='') {
            $data_filtro_especial = $this->maqueta_filtro_especial_final($filtro_especial_sql);
            if(errores::$error){
                return  $this->error->error('Error al maquetar sql',$data_filtro_especial);
            }
            $filtro_especial_sql_env = $data_filtro_especial;
        }

        return $filtro_especial_sql_env;
    }

    /**
     *
     * @param string $fecha
     * @param modelo_base $modelo
     * @return array
     */
    public function filtro_fecha_final(string $fecha, modelo_base $modelo): array
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
        $clase = $namespace.$modelo->tabla;
        if($modelo->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$modelo->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }

        $filtro[$fecha]['valor'] = $modelo->tabla.'.fecha_final';
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
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return $this->error->error("Error fecha", $valida);
        }
        if($modelo->tabla === ''){
            return $this->error->error("Error tabla vacia", $modelo->tabla);
        }
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);
        $clase = $namespace.$modelo->tabla;
        if($modelo->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$modelo->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        $filtro[$fecha]['valor'] = $modelo->tabla.'.fecha_inicial';
        $filtro[$fecha]['operador'] = '>=';
        $filtro[$fecha]['valor_es_campo'] = true;

        return $filtro;

    }

    /**
     *
     * @param string $fecha
     * @param array $filtro
     * @param modelo_base $modelo
     * @return array
     */
    private function filtro_fecha_rango(string $fecha, array $filtro, modelo_base $modelo): array
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
        $clase = $namespace.$modelo->tabla;
        if($modelo->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$modelo->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }

        $filtro_ini = (new filtros())->filtro_fecha_inicial($fecha, $modelo);
        if(errores::$error){
            return $this->error->error('Error al generar filtro fecha', $filtro_ini);
        }

        $filtro_fin = (new filtros())->filtro_fecha_final($fecha,$modelo);
        if(errores::$error){
            return $this->error->error('Error al generar filtro fecha', $filtro_fin);
        }
        $filtro[] = $filtro_ini;
        $filtro[] = $filtro_fin;

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
        if((float)$monto<0.0){
            return $this->error->error("Error el monto es menor a 0", $monto);
        }
        if($modelo->tabla === ''){
            return $this->error->error("Error tabla vacia", $modelo->tabla);
        }
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);
        $clase = $namespace.$modelo->tabla;
        if($modelo->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$modelo->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error("Error campo vacio", $campo);
        }

        $filtro["$monto"]['valor'] = $modelo->tabla.'.'.$campo;
        $filtro["$monto"]['operador'] = '>=';
        $filtro["$monto"]['comparacion'] = 'AND';
        $filtro["$monto"]['valor_es_campo'] = true;

        return $filtro;
    }

    public function filtro_monto_fin(string $monto, string $campo, modelo_base $modelo): array
    {
        if((float)$monto<0.0){
            return $this->error->error("Error el monto es menor a 0", $monto);
        }
        if($modelo->tabla === ''){
            return $this->error->error("Error tabla vacia", $modelo->tabla);
        }
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);
        $clase = $namespace.$modelo->tabla;
        if($modelo->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$modelo->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error("Error campo vacio", $campo);
        }

        $filtro["$monto"]['valor'] = $modelo->tabla.'.'.$campo;
        $filtro["$monto"]['operador'] = '<=';
        $filtro["$monto"]['comparacion'] = 'AND';
        $filtro["$monto"]['valor_es_campo'] = true;

        return $filtro;
    }

    /**
     *
     * @param string $monto
     * @param stdClass $campos
     * @param array $filtro
     * @param modelo_base $modelo
     * @return array
     */
    private function filtro_monto_rango(string $monto, stdClass $campos, array $filtro, modelo_base $modelo): array
    {
        $campos_arr = (array)$campos;
        $keys = array('inf','sup');
        $valida = $this->validacion->valida_existencia_keys($campos_arr, $keys);
        if(errores::$error){
            return $this->error->error("Error validar campos", $valida);
        }

        if($modelo->tabla === ''){
            return $this->error->error("Error tabla vacia", $modelo->tabla);
        }
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);
        $clase = $namespace.$modelo->tabla;
        if($modelo->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$modelo->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        if((float)$monto<0.0){
            return $this->error->error("Error el monto es menor a 0", $monto);
        }

        $filtro_monto_ini = (new filtros())->filtro_monto_ini($monto, $campos->inf, $modelo);
        if(errores::$error){
            return $this->error->error('Error al generar filtro monto', $filtro_monto_ini);
        }

        $filtro_monto_fin = (new filtros())->filtro_monto_fin($monto, $campos->sup, $modelo);
        if(errores::$error){
            return $this->error->error('Error al generar filtro monto', $filtro_monto_fin);
        }

        $filtro[] = $filtro_monto_ini;
        $filtro[] = $filtro_monto_fin;

        return $filtro;
    }

    /**
     *
     * Devuelve un arreglo con la sentencia de sql que indica si se aplicaran una o dos condiciones
     *
     * @param string $filtro_especial_sql cadena que contiene una sentencia de sql a aplicar el filtro
     * @return array|string
     * @example
     *      $data_filtro_especial = $this->maqueta_filtro_especial_final($filtro_especial_sql);
     *
     * @uses modelo_basico->filtro_especial_final(string $filtro_especial_sql);
     */
    private function maqueta_filtro_especial_final( string $filtro_especial_sql):array|string{//FIN
        if($filtro_especial_sql===''){
            return  $this->error->error('Error el filtro especial no puede venir vacio',$filtro_especial_sql);
        }

        return $filtro_especial_sql;
    }




}

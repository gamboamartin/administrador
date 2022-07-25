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
     * P INT P ORDER ERRROEV
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
     * @param array $filtro_rango Filtro de tipo rango
     * @param array $group_by Es un array con la forma array(0=>'tabla.campo', (int)N=>(string)'tabla.campo')
     * @param int $limit Numero de registros a mostrar
     * @param modelo $modelo modelo en ejecucion
     * @param array $not_in Conjunto de valores para not_in not_in[llave] = string, not_in['values'] = array()
     * @param int $offset Numero de inicio de registros
     * @param array  $order con parametros para generar sentencia
     * @param string $sql_extra Sql previo o extra si existe forzara la integracion de un WHERE
     * @param string $tipo_filtro Si es numero es un filtro exacto si es texto es con %%
     * @param array $filtro_fecha Filtros de fecha para sql filtro[campo_1], filtro[campo_2], filtro[fecha]
     * @return array|stdClass
     */
    public function complemento_sql(bool $aplica_seguridad, array $filtro, array $filtro_especial,
                                    array $filtro_extra, array $filtro_rango, array $group_by, int $limit,
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
            filtro_rango:  $filtro_rango, keys_data_filter: $modelo->keys_data_filter, not_in: $not_in,
            sql_extra: $sql_extra, tipo_filtro: $tipo_filtro);


        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtros',data:$filtros);
        }
        $filtros->params = $params;
        return $filtros;
    }

    /**
     * P ORDER P INT ERRORREV
     * @param string $consulta
     * @param stdClass $complemento Complemento de filtros a integrar en un select
     * @return string|array
     */
    public function consulta_full_and(stdClass $complemento, string $consulta, modelo $modelo): string|array
    {

        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error(mensaje: 'Error $consulta no puede venir vacia',data: $consulta);
        }

        $complemento = (new where())->limpia_filtros(filtros: $complemento,keys_data_filter:  $modelo->columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al limpiar filtros',data:$complemento);
        }

        $complemento_r = (new where())->init_params_sql(complemento: $complemento,keys_data_filter: $modelo->keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al inicializar params',data:$complemento_r);
        }


        $modelo->consulta = $consulta.$complemento_r->where.$complemento_r->sentencia.' '.$complemento_r->filtro_especial.' ';
        $modelo->consulta.= $complemento_r->filtro_rango.' '.$complemento_r->filtro_fecha.' ';
        $modelo->consulta.= $complemento_r->filtro_extra.' '.$complemento_r->sql_extra.' '.$complemento_r->not_in.' ';
        $modelo->consulta.= $complemento_r->params->group_by.' '.$complemento_r->params->order.' ';
        $modelo->consulta.= $complemento_r->params->limit.' '.$complemento_r->params->offset;
        return $modelo->consulta;
    }




}

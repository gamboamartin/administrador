<?php
namespace gamboamartin\administrador\modelado;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;


class params_sql{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Asigna un where con seguridad por datos a sql
     * @param array $modelo_columnas_extra
     * @param string $sql_where_previo Sql previo
     * @return array|string
     */
    private function asigna_seguridad_data(array $modelo_columnas_extra, string $sql_where_previo): array|string
    {
        $valida = $this->valida_seguridad(modelo_columnas_extra: $modelo_columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $modelo->columnas_extra', data:$valida);
        }

        $where = $this->where(sql_where_previo: $sql_where_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar where', data: $where);
        }

        $sq_seg = $modelo_columnas_extra['usuario_permitido_id'];
        return " $where ($sq_seg) = $_SESSION[usuario_id] ";
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Genera la cadena SQL para la instrucción GROUP BY.
     *
     * @param array $group_by El arreglo que contiene los campos por los cuales agrupar.
     *
     * @return string|array La cadena SQL para la instrucción GROUP BY.
     * @version 13.10.0
     */
    private function group_by_sql(array $group_by): string|array
    {
        $group_by_sql = '';
        foreach ($group_by as $campo){
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error el campo no puede venir vacio', data: $group_by);
            }
            if(is_numeric($campo)){
                return $this->error->error(mensaje:'Error el campo debe ser un texto', data: $campo);
            }
            if($group_by_sql === ''){
                $group_by_sql.=' GROUP BY '.$campo.' ';
            }
            else {
                $group_by_sql .= ',' . $campo.' ';
            }
        }
        return $group_by_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función se utiliza para limitar las consultas SQL.
     *
     * @param int $limit El límite de la consulta SQL.
     *
     * @return string|array Devuelve la consulta SQL limitada como cadena si $limit es positivo.
     * Devuelve una matriz con un error si $limit es negativo.
     * @version 13.12.0
     *
     */
    private function limit_sql(int $limit): string|array
    {
        if($limit<0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0', data: $limit);
        }
        $limit_sql = '';
        if($limit > 0){
            $limit_sql.=' LIMIT '.$limit;
        }
        return $limit_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función genera una cadena SQL para un desplazamiento (OFFSET) dado
     * un parámetro específico. Esto se usa para definir el número de registros
     * que se van a omitir antes de empezar a devolver los registros en una consulta SQL.
     *
     * @param int $offset Es el número de registros que se van a omitir.
     *
     * @return string|array Si el valor de $offset es mayor a 0,
     * devuelve una cadena con la sentencia SQL 'OFFSET' concatenada con el valor de $offset.
     * Si $offset es menor que 0, se devuelve un array con un mensaje de error.
     * @version 13.13.0
     */
    private function offset_sql(int $offset): string|array
    {
        if($offset<0){
            return $this->error->error(mensaje: 'Error $offset debe ser mayor o igual a 0',data: $offset);

        }
        $offset_sql = '';
        if($offset >0){
            $offset_sql.=' OFFSET '.$offset;
        }
        return $offset_sql;
    }

    /**
     * Obtiene los parametros necesarios para la ejecucion de un SELECT
     * @param bool $aplica_seguridad si aplica seguridad verifica que el usuario tenga acceso
     * @param array $group_by Es un array con la forma array(0=>'tabla.campo', (int)N=>(string)'tabla.campo')
     * @param int $limit Numero de registros a mostrar
     * @param array $modelo_columnas_extra
     * @param int $offset Numero de inicio de registros
     * @param array $order con parametros para generar sentencia
     * @param string $sql_where_previo Sql previo
     * @return array|stdClass
     *          string stdClass->group_by_sql GROUP BY $group_by[tabla.campo] o ''
     *          string stdClass->order_sql ORDER BY $order[tabla.campo] $order[tipo_order] o ''
     *          string stdClass->limit_sql LIMIT $limit o ''
     *          string stdClass->offset_sql OFFSET $offset o ''
     *          string stdClass->seguridad WHERE usuario_permitido_id = $_SESSION[usuario_id] o ''
     */
    final public function params_sql(bool $aplica_seguridad, array $group_by, int $limit, array $modelo_columnas_extra,  int $offset,
                               array $order, string $sql_where_previo): array|stdClass
    {
        if($limit<0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0',data:  $limit);
        }
        if($offset<0){
            return $this->error->error(mensaje: 'Error $offset debe ser mayor o igual a 0',data: $offset);

        }

        $group_by_sql = $this->group_by_sql(group_by: $group_by);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data:$group_by_sql);
        }

        $order_sql = $this->order_sql(order: $order);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar order',data:$order_sql);
        }

        $limit_sql = $this->limit_sql(limit: $limit);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar limit',data:$limit_sql);
        }

        $offset_sql = $this->offset_sql(offset: $offset);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar offset',data:$offset_sql);
        }

        $seguridad = $this->seguridad(aplica_seguridad:$aplica_seguridad, modelo_columnas_extra: $modelo_columnas_extra,
            sql_where_previo:  $sql_where_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql de seguridad', data: $seguridad);
        }

        $params = new stdClass();
        $params->group_by = $group_by_sql;
        $params->order = $order_sql;
        $params->limit = $limit_sql;
        $params->offset = $offset_sql;
        $params->seguridad = $seguridad;

        return $params;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Genera una consulta SQL para ordenar los resultados.
     *
     * @param array $order Un array asociativo donde las claves son los nombres de las columnas a ordenar y
     * los valores son los tipos de orden ('ASC' para ascendente, 'DESC' para descendente).
     * @return array|string Devuelve una consulta SQL que puede ser usada en una cláusula ORDER BY.
     * @version 13.11.0
     */
    private function order_sql(array $order):array|string{
        $order_sql = '';
        foreach ($order as $campo=>$tipo_order){
            if(is_numeric($campo)){
                return $this->error->error(mensaje: 'Error $campo debe ser txt',data: $order);
            }
            if($order_sql === ''){
                $order_sql.=' ORDER BY '.$campo.' '.$tipo_order;
            }
            else {
                $order_sql .= ',' . $campo.' '.$tipo_order;
            }
        }
        return $order_sql;
    }

    /**
     * Genera la seguridad de datos por usuario
     * @param bool $aplica_seguridad si aplica seguridad verifica que el usuario tenga acceso
     * @param array $modelo_columnas_extra
     * @param string $sql_where_previo Sql previo
     * @return array|string
     */
    final public function seguridad(bool $aplica_seguridad, array $modelo_columnas_extra,
                                    string $sql_where_previo): array|string
    {
        $seguridad = '';
        if($aplica_seguridad){

            $valida = $this->valida_seguridad(modelo_columnas_extra: $modelo_columnas_extra);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar $modelo->columnas_extra', data:$valida);
            }

            $seguridad = $this->asigna_seguridad_data(modelo_columnas_extra:$modelo_columnas_extra,
                sql_where_previo: $sql_where_previo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql de seguridad', data: $seguridad);
            }
        }
        return $seguridad;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Valida la seguridad de los datos de entrada comprobando la existencia de ciertas claves en el arreglo
     * proporcionado y en la variable de sesión.
     *
     * Esta función se encuentra en el archivo 'modelado/params_sql.php'.
     * Su propósito es verificar la existencia de las claves 'usuario_permitido_id' en el arreglo proporcionado
     * y 'usuario_id' en la variable de sesión $_SESSION. Si alguna de estas claves no existe, se genera un error.
     *
     * @param array $modelo_columnas_extra La matriz que se comprobará para la existencia de la clave 'usuario_permitido_id'.
     *
     * @return true|array Devuelve true en caso de éxito, de lo contrario, devuelve un arreglo con información del error.
     * @version 15.1.0
     */
    private function valida_seguridad(array $modelo_columnas_extra): true|array
    {
        $keys = array('usuario_permitido_id');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,
            registro: $modelo_columnas_extra,valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $modelo->columnas_extra', data:$valida);
        }
        $keys = array('usuario_id');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $_SESSION);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $_SESSION', data:$valida);
        }
        return true;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función integra la sentencia WHERE de una consulta SQL.
     *
     * @param string $sql_where_previo La condición WHERE previa.
     * @return string Devuelve la sentencia WHERE.
     * @version 15.5.0
     */
    private function where(string $sql_where_previo): string
    {
        $where = '';
        if($sql_where_previo ===''){
            $where = ' WHERE ';
        }
        return $where;
    }

}


<?php
namespace base\orm;
use gamboamartin\errores\errores;
use stdClass;


class params_sql{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    private function asigna_seguridad_data(modelo $modelo, string $sql_where_previo): array|string
    {
        $where = $this->where(sql_where_previo: $sql_where_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar where', data: $where);
        }

        $sq_seg = $modelo->columnas_extra['usuario_permitido_id'];
        return " $where ($sq_seg) = $_SESSION[usuario_id] ";
    }

    /**
     * Genera Group By para sql
     * @version 1.42.14
     * @param array $group_by Es un array con la forma array(0=>'tabla.campo', (int)N=>(string)'tabla.campo')
     * @return string|array
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
     * Genera un limit para sql
     * @version 1.91.19
     * @param int $limit Numero de registros a mostrar
     * @return string|array
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
     * Obtiene un offset para sql
     * @version 1.92.19
     * @param int $offset Numero de inicio de registros
     * @return string|array
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
     * FULL
     * @param bool $aplica_seguridad
     * @param array $group_by
     * @param int $limit Numero de registros a mostrar
     * @param modelo $modelo
     * @param int $offset Numero de inicio de registros
     * @param array $order
     * @param string $sql_where_previo
     * @return array|stdClass
     */
    public function params_sql(bool $aplica_seguridad, array $group_by, int $limit, modelo $modelo,  int $offset,
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

        $seguridad = $this->seguridad(aplica_seguridad:$aplica_seguridad, modelo: $modelo,
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
     *
     * Funcion genera order en forma de sql
     * @param array  $order con parametros para generar sentencia
     * @version 1.0.0
     * @return array|string cadena con order en forma de SQL
     * @throws errores if order[campo] es un numero
     * @example
     * $order_sql = $this->order_sql($order);
     * @uses modelo
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

    private function seguridad(bool $aplica_seguridad, modelo $modelo, string $sql_where_previo): array|string
    {
        $seguridad = '';
        if($aplica_seguridad){
            $seguridad = $this->asigna_seguridad_data(modelo:$modelo, sql_where_previo: $sql_where_previo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql de seguridad', data: $seguridad);
            }
        }
        return $seguridad;
    }

    private function where(string $sql_where_previo): string
    {
        $where = '';
        if($sql_where_previo ===''){
            $where = ' WHERE ';
        }
        return $where;
    }

}


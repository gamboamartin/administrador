<?php
namespace base\orm;
use gamboamartin\administrador\modelado\params_sql;
use gamboamartin\errores\errores;
use stdClass;

class sql{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Genera sql DESCRIBE nombre_table
     * @param string $tabla Nombre de la tabla a verificar
     * @return string|array Sql a ejecutar
     * @version 1.12.8
     */
    final public function describe_table(string $tabla): string|array
    {
        $valida = (new val_sql())->tabla(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tabla', data: $valida);
        }

        return "DESCRIBE $tabla";
    }

    /**
     * Genera el SQL IN
     * @param string $llave Llave o campo
     * @param string $values_sql Valores a integrar
     * @return string|array
     * @version 1.548.51
     */
    public function in(string $llave, string $values_sql): string|array
    {
        $valida = $this->valida_in(llave: $llave, values_sql: $values_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar in', data: $valida);
        }

        $in_sql = '';
        if($values_sql!==''){
            $in_sql.="$llave IN ($values_sql)";
        }
        str_replace('  ', ' ', $in_sql);
        str_replace('  ', ' ', $in_sql);
        str_replace('  ', ' ', $in_sql);
        return $in_sql;
    }

    private function inicializa_param(string $key, stdClass $params_base): array|stdClass
    {
        if(!isset($params_base->$key)){
            $params_base = $this->init_param(key: $key,params_base:  $params_base);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar param', data: $params_base);
            }
        }
        return $params_base;
    }

    private function init_param(string $key, stdClass $params_base): stdClass
    {
        $params_base->$key = '';
        return $params_base;
    }

    private function init_params(stdClass $params_base): array|stdClass
    {
        $params_base_ = $params_base;

        $keys_params[] = 'seguridad';
        $keys_params[] = 'group_by';
        $keys_params[] = 'order';
        $keys_params[] = 'limit';
        $keys_params[] = 'offset';

        foreach ($keys_params as $key){
            $params_base_ = $this->inicializa_param(key: $key, params_base: $params_base_);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar param', data: $params_base_);
            }
        }

        return $params_base_;
    }

    /**
     * Genera el sql para show tables
     * @version 1.160.31
     * @return string
     */
    public function show_tables(): string
    {
        return "SHOW TABLES";
    }

    /**
     * Integra el sql completo para la obtencion de un select
     * @param string $consulta_base Sql base
     * @param stdClass $params_base Parametros de integracion
     * @param string $sql_extra Sql extra
     * @return string|array
     * @version 1.374.41
     */
    final public function sql_select(string $consulta_base, stdClass $params_base, string $sql_extra): string|array
    {
        $consulta_base = trim($consulta_base);
        if($consulta_base === ''){
            return $this->error->error(mensaje: 'Error la consulta no puede venir vacia', data: $consulta_base);
        }

        $params_base_ = $this->init_params(params_base: $params_base);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar param', data: $params_base_);
        }

        $consulta = $consulta_base.' '.$sql_extra.' '.$params_base_->seguridad.' ';
        $consulta.= $params_base_->group_by.' '.$params_base_->order.' '.$params_base_->limit.' '.$params_base_->offset;
        return $consulta;
    }

    /**
     * Funcion que inicializa los elementos de un SQL para sus where
     * @param bool $aplica_seguridad si aplica seguridad verifica que el usuario tenga acceso
     * @param array $columnas Columnas de a obtener en select
     * @param bool $columnas_en_bruto Obtiene las columnas tal como estan en base de datos
     * @param bool $con_sq Integra las columnas extra si true
     * @param array $extension_estructura Extension de estructura para joins
     * @param array $group_by Es un array con la forma array(0=>'tabla.campo', (int)N=>(string)'tabla.campo')
     * @param int $limit Limit en sql
     * @param modelo $modelo Modelo en ejecucion
     * @param int $offset Sql de integracion tipo offset
     * @param array $order con parametros para generar sentencia
     * @param array $renombres Tablas renombradas
     * @param string $sql_where_previo Sql previo a incrustar
     * @return array|stdClass
     * @version 1.373.44
     */
    final public function sql_select_init(bool $aplica_seguridad, array $columnas, bool $columnas_en_bruto,
                                          bool $con_sq, array $extension_estructura, array $group_by, int $limit,
                                          modelo $modelo, int $offset, array $order, array $renombres,
                                          string $sql_where_previo): array|stdClass
    {
        if($limit<0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0 en '.$modelo->tabla,
                data:  $limit);
        }
        if($offset<0){
            return $this->error->error(mensaje: 'Error $offset debe ser mayor o igual a 0 en '.$modelo->tabla,
                data: $offset);

        }

        $params_base = (new params_sql())->params_sql(aplica_seguridad: $aplica_seguridad,group_by: $group_by,
            limit:  $limit,modelo_columnas_extra: $modelo->columnas_extra, offset: $offset, order: $order,
            sql_where_previo: $sql_where_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener parametros base en '.$modelo->tabla,
                data: $params_base);
        }

        $consulta_base = $modelo->genera_consulta_base(columnas: $columnas, columnas_en_bruto: $columnas_en_bruto,
            con_sq: $con_sq, extension_estructura: $extension_estructura, renombradas: $renombres);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar consulta en '.$modelo->tabla, data: $consulta_base);
        }

        $data = new stdClass();
        $data->params = $params_base;
        $data->consulta_base = $consulta_base;
        return $data;
    }

    /**
     * Funcion que genera un UPDATE de tipo SQL
     * @param string $campos_sql Campos en forma sql para update
     * @param int $id Identificador
     * @param string $tabla Tabla en ejecucion
     * @return string|array
     * @version 1.81.17
     */
    public function update(string $campos_sql, int $id, string $tabla): string|array
    {
        $valida = (new val_sql())->tabla(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tabla', data: $valida);
        }
        $campos_sql = trim($campos_sql);
        if($campos_sql === ''){
            return $this->error->error(mensaje: 'Error $campos_sql estan vacios', data: $campos_sql);
        }
        if($id<=0){
            return $this->error->error(mensaje: 'Error $id debe ser mayor a 0', data: $id);
        }


        return 'UPDATE ' . $tabla . ' SET ' . $campos_sql . "  WHERE id = $id";
    }

    /**
     * Valida los datos de entrada para un IN
     * @param string $llave LLave a integrar
     * @param string $values_sql Valores
     * @return bool|array
     * @version 1.548.51
     *
     */
    public function valida_in(string $llave, string $values_sql): bool|array
    {
        $llave = trim($llave);
        $values_sql = trim($values_sql);
        if($llave !== ''){
            if($values_sql ===''){
                return $this->error->error(mensaje: 'Error si llave tiene info values debe tener info', data: $llave);
            }
        }

        if($values_sql !== ''){
            if($llave ===''){
                return $this->error->error(
                    mensaje: 'Error si values_sql tiene info llave debe tener info', data: $values_sql);
            }
        }
        return true;
    }

}

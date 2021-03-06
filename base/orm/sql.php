<?php
namespace base\orm;
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
    public function describe_table(string $tabla): string|array
    {
        $valida = (new val_sql())->tabla(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tabla', data: $valida);
        }

        return "DESCRIBE $tabla";
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

    public function sql_select(string $consulta_base, stdClass $params_base, string $sql_extra): string
    {
        $consulta = $consulta_base.' '.$sql_extra.' '.$params_base->seguridad.' ';
        $consulta.= $params_base->group_by.' '.$params_base->order.' '.$params_base->limit.' '.$params_base->offset;
        return $consulta;
    }

    /**
     * Funcion que inicializa los elementos de un SQL para sus where
     * @param bool $aplica_seguridad si aplica seguridad verifica que el usuario tenga acceso
     * @param array $columnas
     * @param bool $columnas_en_bruto
     * @param array $extension_estructura
     * @param array $group_by Es un array con la forma array(0=>'tabla.campo', (int)N=>(string)'tabla.campo')
     * @param int $limit
     * @param modelo $modelo
     * @param int $offset
     * @param array $order
     * @param array $renombres
     * @param string $sql_where_previo
     * @return array|stdClass
     */
    public function sql_select_init(bool $aplica_seguridad, array $columnas, bool $columnas_en_bruto,
                                    array $extension_estructura, array $group_by, int $limit, modelo $modelo,
                                    int $offset, array $order, array $renombres,
                                    string $sql_where_previo): array|stdClass
    {
        $params_base = (new params_sql())->params_sql(aplica_seguridad: $aplica_seguridad,group_by: $group_by,
            limit:  $limit,modelo: $modelo, offset: $offset, order: $order,sql_where_previo: $sql_where_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener parametros base', data: $params_base);
        }

        $consulta_base = $modelo->genera_consulta_base(columnas: $columnas, columnas_en_bruto: $columnas_en_bruto,
            extension_estructura: $extension_estructura, renombradas: $renombres);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar consulta', data: $consulta_base);
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

}

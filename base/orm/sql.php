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
     * POR DOCUMENTAR EN WIKI
     * Crea una sentencia SQL para agregar una nueva columna a una tabla.
     *
     * @param string $campo El nombre de la nueva columna a agregar.
     * @param string $table El nombre de la tabla a la que se agregará la nueva columna.
     * @param string $tipo_dato El tipo de dato de la nueva columna.
     * @param string $longitud Opcional. La longitud del nuevo campo, si aplicable. Por defecto es una cadena vacía.
     * @return string|array Devuelve la sentencia SQL para agregar la nueva columna a la tabla. O array si existe error
     * @version 13.26.0
     */
    final public function add_column(string $campo, string $table, string $tipo_dato, string $default = '',
                                     string $longitud = ''): string|array
    {
        $campo = trim($campo);
        $table = trim($table);
        $tipo_dato = trim($tipo_dato);
        $tipo_dato = strtoupper($tipo_dato);

        $longitud = trim($longitud);
        if($tipo_dato === 'VARCHAR'){
            $longitud = '255';
        }


        $longitud_sql = '';
        if($longitud !== ''){
            $longitud_sql = "($longitud)";
        }

        $valida = $this->valida_column(campo:$campo,table:  $table, tipo_dato: $tipo_dato, longitud: $longitud);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos',data: $valida);
        }



        $default = $this->default(value: $default);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener default',data: $default);
        }

        return trim("ALTER TABLE $table ADD $campo $tipo_dato $longitud_sql $default;");

    }

    /**
     * Genera una sentencia SQL para alterar una tabla con base en los parámetros proporcionados.
     *
     * @param string $campo El nombre del campo en la tabla que se va a modificar.
     * @param string $statement La declaración SQL a aplicar, puede ser 'ADD', 'DROP', 'RENAME' o 'MODIFY'.
     * @param string $table El nombre de la tabla a la que se va a aplicar la declaración.
     * @param string $longitud Opcional. La longitud del campo en caso de que se agregue o modifique un campo.
     * @param string $new_name Opcional. El nuevo nombre del campo en caso de que se esté renombrando.
     * @param string $tipo_dato Opcional. El tipo de dato del campo en caso de que se agregue o modifique un campo.
     * @return array|string Devuelve una cadena con la sentencia SQL generada.
     */
    final public function alter_table(string $campo, string $statement, string $table, string $longitud = '',
                                      string $new_name = '', string $tipo_dato = ''): array|string
    {
        $sql = '';

        if($statement === 'ADD'){
            $sql = $this->add_column(campo: $campo,table:  $table,tipo_dato:  $tipo_dato,longitud: $longitud);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql',data: $sql);
            }

        }
        if($statement === 'DROP'){
            $sql = $this->drop_column(campo: $campo,table:  $table);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql',data: $sql);
            }
        }
        if($statement === 'RENAME'){
            $sql = $this->rename_column(campo: $campo, new_name: $new_name, table: $table);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql',data: $sql);
            }
        }
        if($statement === 'MODIFY'){
            $sql = $this->modify_column(campo: $campo, table: $table,tipo_dato: $tipo_dato,longitud: $longitud);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql',data: $sql);
            }
        }

        return $sql;

    }

    /**
     * Genera una sentencia SQL para crear una tabla con los campos proporcionados.
     * Si los campos están vacíos o si hay errores al obtener los datos de la tabla o al crear la tabla,
     * retorna un mensaje de error.
     *
     * @param stdClass $campos El objeto que contiene los campos y sus atributos.
     * @param string $table El nombre de la tabla que se creará.
     * @return string|array Devuelve la sentencia SQL para crear la tabla,
     *                      o en caso de error, devuelve un array con el mensaje y los datos del error.
     */
    final public function create_table(stdClass $campos, string $table): array|string
    {
        if(count((array)$campos) === 0){
            return $this->error->error(mensaje: 'Error campos esta vacio',data: $campos);

        }

        $datos_tabla = (new _create())->datos_tabla(campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos_tabla',data: $datos_tabla);
        }

        $sql = (new _create())->table(datos_tabla: $datos_tabla,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener create',data: $sql);
        }

        return $sql;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Genera una declaración SQL para establecer un valor predeterminado en una columna.
     *
     * @param string $value El valor predeterminado a establecer.
     * @return string Devuelve la declaración SQL para establecer un valor predeterminado.
     * @version 13.25.0
     */
    private function default(string $value): string
    {
        $value = trim($value);
        $sql = '';
        if($value !== ''){
            $sql = "DEFAULT $value";
        }
        return trim($sql);
    }

    /**
     * POR DOCUMENTAR WIKI
     * Descripción: Este método genera la consulta SQL para obtener la descripción (estructura) de una tabla en específico.
     *
     * @param string $tabla Nombre de la tabla cuya descripción (estructura) se desea obtener.
     * @return string|array Retorna una cadena con la consulta SQL en caso de éxito.
     *                      Si ocurre un error durante la validación del nombre de la tabla,
     *                      se retorna un array con detalles del error.
     * @version 13.19.0
     *
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
     * Genera una sentencia SQL para eliminar una columna de una tabla.
     *
     * @param string $campo El nombre de la columna a eliminar.
     * @param string $table El nombre de la tabla de la que se eliminará la columna.
     * @return string Retorna la sentencia SQL para eliminar la columna de la tabla.
     */
    final public function drop_column(string $campo, string $table): string
    {
        return "ALTER TABLE $table DROP COLUMN $campo;";

    }

    /**
     * Genera una sentencia SQL para eliminar una tabla.
     *
     * @param string $table El nombre de la tabla a eliminar.
     * @return string Retorna la sentencia SQL para eliminar la tabla.
     */
    final public function drop_table(string $table): string
    {
        return "DROP TABLE $table";

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Genera una sentencia SQL para agregar una clave foránea a una tabla.
     *
     * @param string $table El nombre de la tabla a la que se agregará la clave foránea.
     * @param string $relacion_table El nombre de la tabla referenciada por la clave foránea.
     * @return string|array Devuelve la sentencia SQL que crea la clave foránea en la tabla.
     * @version 13.27.0
     */
    final public function foreign_key(string $table, string $relacion_table): string|array
    {
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }
        $relacion_table = trim($relacion_table);
        if($relacion_table === ''){
            return $this->error->error(mensaje: 'Error relacion_table esta vacia', data: $relacion_table);
        }

        $fk = $relacion_table.'_id';

        $name_indice = $table.'__'.$fk;

        return "ALTER TABLE $table ADD CONSTRAINT $name_indice FOREIGN KEY ($fk) REFERENCES $relacion_table(id);";


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
     * Genera una consulta SQL para modificar una columna en una tabla.
     *
     * @param string $campo El nombre de la columna a modificar.
     * @param string $table El nombre de la tabla que contiene la columna a modificar.
     * @param string $tipo_dato El nuevo tipo de datos para la columna.
     * @param string $longitud Opcional. La nueva longitud para el campo, si aplica. Por defecto es una cadena vacía.
     * @return string Devuelve la consulta SQL para modificar la columna en la tabla.
     */
    final public function modify_column(string $campo, string $table, string $tipo_dato, string $longitud = ''): string
    {
        $longitud_sql = '';
        if($longitud === ''){
            $longitud_sql = "($longitud)";
        }
        return "ALTER TABLE $table MODIFY COLUMN $campo $tipo_dato $longitud_sql;";

    }

    /**
     * Genera una sentencia SQL para renombrar una columna en una tabla.
     *
     * @param string $campo El nombre actual de la columna a renombrar.
     * @param string $new_name El nuevo nombre para la columna.
     * @param string $table El nombre de la tabla que contiene la columna a renombrar.
     * @return string Retorna la sentencia SQL para renombrar la columna en la tabla.
     */
    final public function rename_column(string $campo, string $new_name, string $table): string
    {
        return "ALTER TABLE $table RENAME COLUMN $campo to $new_name;";
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Muestra las tablas de la base de datos de acuerdo con el criterio proporcionado.
     *
     * @param string $entidad Nombre de la tabla que queremos consultar en la base de datos.
     * @return string Consulta SQL construida para mostrar la(o las) tabla(s)
     * que corresponden al criterio proporcionado en `$entidad`.
     * Si $entidad está vacío, el resultado será una consulta SQL para mostrar todas las tablas.
     *
     * Uso:
     * $resultado = show_tables("miTabla");
     * Este ejemplo retornará: "SHOW TABLES LIKE 'miTabla'"
     *
     * $resultado = show_tables("");
     * Este ejemplo retornará: "SHOW TABLES"
     * @version 14.2.0
     */
    final public function show_tables(string $entidad = ''): string
    {
        $entidad = trim($entidad);
        $where = '';
        if($entidad !==''){
            $where = "LIKE '$entidad'";
        }
        $sql = "SHOW TABLES $where";
        return trim($sql);
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
     * POR DOCUMENTAR EN WIKI
     * Valida los valores de los argumentos campo, tabla y tipo de dato. Si alguno de ellos es una cadena vacía, devuelve un error.
     *
     * @param string $campo El nombre de la columna a validar.
     * @param string $table El nombre de la tabla a validar.
     * @param string $tipo_dato El tipo de dato a validar.
     * @return true|array Retorna verdadero si los argumentos son válidos, o un array con un mensaje de error si no lo son.
     * @version 13.28.0
     */
    final function valida_column(string $campo, string $table, string $tipo_dato, string $longitud = ''): true|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio',data: $campo);
        }
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table esta vacia',data: $table);
        }
        $tipo_dato = trim($tipo_dato);
        if($tipo_dato === ''){
            return $this->error->error(mensaje: 'Error tipo_dato esta vacio',data: $tipo_dato);
        }
        $longitud = trim($longitud);

        $tipo_dato = strtoupper($tipo_dato);
        if($tipo_dato === 'VARCHAR'){
            if($longitud === ''){
                return $this->error->error(
                    mensaje: 'Error tipo_dato esta VARCHAR entonces longitud debe ser u numero entero',data: $tipo_dato);
            }

        }

        return true;

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

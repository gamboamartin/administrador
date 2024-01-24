<?php
namespace gamboamartin\administrador\models;

use base\orm\_create;
use base\orm\estructuras;
use base\orm\modelo_base;
use base\orm\sql;
use base\orm\val_sql;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;

class _instalacion
{
    private errores $error;
    private modelo_base $modelo;
    private PDO $link;

    public function __construct(PDO $link)
    {
        $this->link = $link;
        $this->error = new errores();
        $this->modelo = new modelo_base(link: $this->link);


    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Agrega una columna a una tabla dada.
     *
     * @param string $campo El nombre de la columna a agregar.
     * @param string $table El nombre de la tabla a la que se agregará la columna.
     * @param string $tipo_dato El tipo de dato de la nueva columna.
     * @param string $default Valor default en caso de vacio no lo integra
     * @param string $longitud Opcional. La longitud del nuevo campo. Por defecto es una cadena vacía.
     * @param bool $not_null Opcional. Si es true integra el NOT NULL si no lo deja libre.
     * @return stdClass|array Retorna la ejecución de la sentencia SQL para agregar la columna, o en caso de error,
     * devuelve el mensaje de error.
     * @version 13.28.0
     */
    final public function add_colum(string $campo, string $table, string $tipo_dato, string $default = '',
                                    string $longitud = '', bool $not_null = true): stdClass|array
    {
        $campo = trim($campo);
        $table = trim($table);
        $tipo_dato = trim($tipo_dato);
        $tipo_dato = strtoupper($tipo_dato);

        $longitud = trim($longitud);

        if($longitud === '') {
            if ($tipo_dato === 'VARCHAR') {
                $longitud = '255';
            }
            if ($tipo_dato === 'DOUBLE') {
                $longitud = '100,4';
            }
        }

        $valida = (new sql())->valida_column(campo: $campo, table: $table, tipo_dato: $tipo_dato, longitud: $longitud);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $sql = (new sql())->add_column(campo: $campo, table: $table, tipo_dato: $tipo_dato,
            default: $default, longitud: $longitud, not_null: $not_null);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    final public function add_columns(stdClass $campos, string $table)
    {

        $datos = $this->describe_table(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $datos);
        }
        $campos_origen = $datos->registros;

        $adds = array();
        foreach ($campos as $campo=>$atributos){

            $existe_campo = $this->existe_campo_origen(campo_integrar: $campo,campos_origen:  $campos_origen);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar si existe campo', data: $existe_campo);
            }

            if(!$existe_campo){
                $tipo_dato = 'VARCHAR';
                if(isset($atributos->tipo_dato)){
                    $atributos->tipo_dato = strtoupper($atributos->tipo_dato);
                    $tipo_dato = $atributos->tipo_dato;
                }
                $default = '';
                if(isset($atributos->default)){
                    $default = $atributos->default;
                }
                $longitud = '255';

                if($tipo_dato === 'DOUBLE'){
                    $longitud = '100,4';
                }

                if(isset($atributos->longitud)){
                    $longitud = $atributos->longitud;
                }

                $not_null = true;
                if(isset($atributos->not_null)){
                    $not_null = $atributos->not_null;
                }
                $add = $this->add_colum(campo: $campo, table: $table, tipo_dato: $tipo_dato, default: $default,
                    longitud: $longitud, not_null: $not_null);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al agregar columna sql', data: $add);
                }
                $adds[] = $add;
            }

        }

        return $adds;
    }

    private function add_unique_base(string $campo, string $table, string $index_name = '')
    {
        $columnas_unique = array($campo);
        $index_unique = $this->index_unique(columnas: $columnas_unique,table:  $table, index_name: $index_name);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar unique', data: $index_unique);
        }
        return $index_unique;

    }

    private function add_uniques_base(stdClass $campos_por_integrar, string $table)
    {
        $indexs_unique = array();
        foreach ($campos_por_integrar as $campo=>$atributos){
            if(isset($atributos->unique) && $atributos->unique){
                $index_name = '';
                if(isset($atributos->index_name)){
                    $index_name = trim($atributos->index_name);
                }
                $index_unique = $this->add_unique_base(campo: $campo,table:  $table, index_name: $index_name);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al integrar unique', data: $index_unique);
                }
                $indexs_unique[] = $index_unique;
            }

        }
        return $indexs_unique;
    }
    /**
     * POR DOCUMENTAR EN WIKI
     * El método campo_double se encarga de crear un objeto stdClass con la configuración para un campo de tipo double.
     *
     * @param stdClass $campos Es el objeto al que se le añadirá la configuración del nuevo campo.
     * @param string $name_campo Es el nombre que tendrá el nuevo campo.
     * @param string $default Es el valor por defecto que tendrá el campo, si no se establece, el valor por defecto será '0'.
     * @param string $longitud Es la longitud que tendrá el campo, si no se establece, la longitud por defecto será '100,2'.
     *
     * @return array|stdClass Devuelve el objeto $campos con la configuración del nuevo campo agregada.
     * Si el parámetro $name_campo está vacío, devuelve un error.
     *
     * Ejemplo de uso:
     * $campos = new stdClass();
     * $name_campo = "mi_campo_doble";
     * $default = "0";
     * $longitud = "100,2";
     * $resultado = $instalacion->campo_double($campos, $name_campo, $default, $longitud);
     *
     * Tras la ejecución, $resultado contendrá la configuración para un campo 'mi_campo_doble' de tipo double, con valor por defecto '0' y longitud '100,2'.
     * @version 15.26.0
     */

    final public function campo_double(stdClass $campos, string $name_campo, string $default = '0',
                                       string $longitud = '100,2'): array|stdClass
    {
        $name_campo = trim($name_campo);
        if($name_campo === ''){
            return $this->error->error(mensaje: 'Error name_campo esta vacio', data: $name_campo);
        }
        $campos->$name_campo = new stdClass();
        $campos->$name_campo->tipo_dato = 'double';
        $campos->$name_campo->default = $default;
        $campos->$name_campo->longitud = $longitud;

        return $campos;

    }

    final public function campo_status(stdClass $campos, string $name_campo, string $default = 'inactivo'): array|stdClass
    {
        $name_campo = trim($name_campo);
        if($name_campo === ''){
            return $this->error->error(mensaje: 'Error name_campo esta vacio', data: $name_campo);
        }
        $campos->$name_campo = new stdClass();
        $campos->$name_campo->default = $default;

        return $campos;

    }

    final public function campos_status_activo(stdClass $campos, array $name_campos)
    {
        foreach ($name_campos as $name_campo){
            $campos = $this->campo_status(campos: $campos,name_campo:  $name_campo,default: 'activo');
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar campo double', data: $campos);
            }
        }
        return $campos;

    }
    final public function campos_status_inactivo(stdClass $campos, array $name_campos)
    {
        foreach ($name_campos as $name_campo){
            $campos = $this->campo_status(campos: $campos,name_campo:  $name_campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar campo double', data: $campos);
            }
        }
        return $campos;

    }

    final public function campos_double_default(stdClass $campos, array $name_campos)
    {
        foreach ($name_campos as $name_campo){
            $campos = $this->campo_double(campos: $campos,name_campo:  $name_campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar campo double', data: $campos);
            }
        }
        return $campos;


    }

    final public function create_table(stdClass $campos, string $table): array|stdClass
    {
        if(count((array)$campos) === 0){
            $campos = (new _create())->campos_base(campos: $campos);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener campos_base',data: $campos);
            }
        }

        $out = new stdClass();

        $data_sql = (new sql())->create_table(campos: $campos, table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $data_sql);
        }
        $out->data_sql = $data_sql;
        $exe = $this->modelo->ejecuta_sql(consulta: $data_sql->sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        $out->exe = $exe;

        $campos_por_integrar = $data_sql->datos_tabla->campos_por_integrar;
        $indexs_unique = $this->add_uniques_base(campos_por_integrar: $campos_por_integrar,table:  $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar uniques', data: $indexs_unique);
        }
        $out->indexs_unique = $indexs_unique;


        return $out;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Realiza una consulta para describir la estructura de una tabla.
     *
     * @param string $table El nombre de la tabla a describir.
     * @return stdClass|array Retorna el resultado de la consulta de descripción de la tabla o, en caso de error,
     * devuelve un mensaje de error.
     * @version 15.13.0
     */
    private function describe_table(string $table): array|stdClass
    {
        $valida = (new val_sql())->tabla(tabla: $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar table', data: $valida);
        }

        $sql = (new sql())->describe_table(tabla: $table);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al generar sql", data: $sql);
        }
        $exe = $this->modelo->ejecuta_consulta(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;
    }

    final public function drop_index(string $name_index, string $table): array|stdClass
    {
        $sql = (new sql())->drop_index(name_index: $name_index, table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    final public function drop_table(string $table): array|stdClass
    {
        $sql = (new sql())->drop_table(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    /**
     * Verifica si un campo específico existe en un conjunto de campos dado.
     *
     * @param string $campo_integrar El nombre del campo a buscar.
     * @param array $campos_origen Un array de campos en los que buscar el campo.
     * @return bool Retorna true si el campo existe en el conjunto, false en caso contrario.
     */
    private function existe_campo_origen(string $campo_integrar, array $campos_origen): bool
    {
        $existe_campo = false;
        foreach ($campos_origen as $datos_campos){
            $campo_original = trim($datos_campos['Field']);
            if($campo_original === $campo_integrar){
                $existe_campo = true;
                break;
            }
        }
        return $existe_campo;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Verifica si la entidad proporcionada existe en la base de datos.
     *
     * @param string $table El nombre de la tabla a verificar.
     *
     * @return bool|array Devuelve un error si la tabla está vacía o si hay un error al validar.
     *               De lo contrario, devuelve verdadero o falso dependiendo de si la entidad existe.
     *
     * Ejemplo de uso:
     *
     * $instalacion = new _instalacion();
     * if($instalacion->existe_entidad("nombre_tabla")) {
     *    // La tabla existe
     * } else {
     *    // La tabla no existe
     * }
     * @version 15.15.0
     */
    final public function existe_entidad(string $table): bool|array
    {
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table vacia', data: $table);
        }
        $existe = (new estructuras(link: $this->link))->existe_entidad(entidad: $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe entidad',data: $existe);
        }
        return $existe;

    }

    final public function existe_indice_by_name(string $name_index, string $table)
    {
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error al table esta vacia',data: $table);
        }
        $name_index = trim($name_index);
        if($name_index === ''){
            return $this->error->error(mensaje: 'Error al name_index esta vacio',data: $name_index);
        }

        $r_indices = $this->ver_indices(table: $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al buscar indices',data: $r_indices);
        }

        $indices = $r_indices->registros;

        $existe = false;
        foreach ($indices as $index){
            $name_a_val = trim($index['Key_name']);

            if($name_a_val === $name_index){
                $existe = true;
                break;
            }
        }
        return $existe;


    }

    final public function foraneas(array $foraneas, string $table)
    {
        $results = array();
        foreach ($foraneas as $campo=>$atributos){

            $default = '';
            if(isset($atributos->default)){
                $default = trim($atributos->default);
            }
            $valida = (new sql())->valida_column_base(campo: $campo,table:  $table);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar datos de entrada',data: $valida);
            }

            $result = $this->foreign_key_seguro(campo: $campo,table: $table, default: $default);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al ajustar foranea', data:  $result);
            }
            $results[] = $result;
        }
        return $results;

    }

    /**
     * Agrega una columna a una tabla y luego establece una clave foránea en la misma.
     *
     * @param string $campo El nombre de la columna a agregar, que luego se convertirá en la clave foránea.
     * @param string $table El nombre de la tabla a la que se agregará la columna y se establecerá la clave foránea.
     * @return array|stdClass Devuelve el resultado del proceso de generar la clave foránea o, en caso de error, devuelve un mensaje de error.
     */
    final public function foreign_key_completo(string $campo, string $table, string $default = ''): array|stdClass
    {
        $valida = (new sql())->valida_column_base(campo: $campo,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos de entrada',data: $valida);
        }

        $exe = $this->add_colum(campo: $campo, table: $table, tipo_dato: 'bigint', default: $default, longitud: 100);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar add_column', data: $exe);
        }

        $fk = $this->foreign_por_campo(campo: $campo, table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $fk);
        }

        return $fk;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Genera una sentencia SQL para crear una clave foránea y luego la ejecuta.
     *
     * @param string $relacion_table El nombre de la tabla que la clave foránea está referenciando.
     * @param string $table El nombre de la tabla donde se creará la clave foránea.
     * @return array|stdClass Devuelve el resultado de la ejecución de la consulta SQL, o un error si ocurre uno.
     * @version 13.29.0
     */
    final public function foreign_key_existente(string $relacion_table, string $table): array|stdClass
    {
        $table = trim($table);
        if ($table === '') {
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }
        $relacion_table = trim($relacion_table);
        if ($relacion_table === '') {
            return $this->error->error(mensaje: 'Error relacion_table esta vacia', data: $relacion_table);
        }

        $sql = (new sql())->foreign_key(table: $table, relacion_table: $relacion_table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }
    final public function foreign_key_seguro(string $campo, string $table, string $default = '')
    {

        $valida = (new sql())->valida_column_base(campo: $campo,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos de entrada',data: $valida);
        }

        $existe_table = (new estructuras(link: $this->link))->existe_entidad(entidad: $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe entidad',data:  $existe_table);
        }
        if(!$existe_table){
            return $this->error->error(mensaje: 'Error no existe la entidad',data:  $table);
        }

        $datos = $this->describe_table(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $datos);
        }
        $campos_origen = $datos->registros;

        $existe_campo = $this->existe_campo_origen(campo_integrar: $campo,campos_origen:  $campos_origen);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe campo', data: $existe_campo);
        }

        if(!$existe_campo){
            $fk = $this->foreign_key_completo(campo: $campo,table:  $table, default: $default);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar foreign', data: $fk);
            }
        }
        else{
            $fk = $this->foreign_no_conf_integra(campo: $campo, campos_origen: $campos_origen, table: $table);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar foreign no conf', data: $fk);
            }
        }


        return $fk;

    }
    private function foreign_no_conf(string $campo, array $campo_origen, string $table)
    {
        $fk = 'Campo asignado '.$campo;
        if($campo_origen['Key'] !== 'MUL'){
            $fk = $this->foreign_por_campo(campo: $campo,table:  $table);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar foreign', data: $fk);
            }
        }
        return $fk;

    }
    private function foreign_no_conf_integra(string $campo, array $campos_origen, string $table)
    {
        $fk = 'No existe campo '.$campo;
        foreach ($campos_origen as $campo_origen){

            $campo_origen_name = $campo_origen['Field'];

            if($campo_origen_name === $campo) {
                $fk = $this->foreign_no_conf(campo: $campo, campo_origen: $campo_origen, table: $table);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al integrar foreign', data: $fk);
                }
                break;
            }
        }
        return $fk;

    }

    private function foreign_por_campo(string $campo, string $table)
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo);
        }
        $valida = (new validacion())->key_id(txt: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar campo', data: $valida);
        }

        $explode_campo = explode('_id', $campo);
        $relacion_table = $explode_campo[0];

        $fk = $this->foreign_key_existente(relacion_table: $relacion_table, table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $fk);
        }

        return $fk;

    }
    final public function index_unique(array $columnas, $table, string $index_name = '')
    {
        $sql = (new sql())->index_unique(columnas: $columnas,table:  $table, index_name: $index_name);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }

        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;
    }

    /**
     * Integra una clave foránea en una tabla si el campo correspondiente no existe ya en la tabla.
     *
     * @param string $campo_integrar El nombre del campo a integrar como clave foránea.
     * @param array $campos_origen Un array con los campos originales de la tabla.
     * @param array $integraciones Un array con las integraciones ya realizadas.
     * @param string $table El nombre de la tabla donde se integrará la clave foránea.
     * @return array Retorna un array con las integraciones actualizadas después de la integración del nuevo campo.
     */
    private function integra_fk(
        string $campo_integrar, array $campos_origen, array $integraciones, string $table, string $default = ''): array
    {
        $existe_campo = $this->existe_campo_origen(campo_integrar: $campo_integrar,campos_origen:  $campos_origen);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe campo', data: $existe_campo);
        }

        if(!$existe_campo){
            $integra_fk = $this->foreign_key_completo(campo: $campo_integrar,table:  $table, default: $default);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al ejecutar sql', data: $integra_fk);
            }
            $integraciones[] = $integra_fk;
        }

        return $integraciones;
    }

    private function integra_fks(stdClass $campos, array $campos_origen, string $table)
    {
        $integraciones = array();
        foreach ($campos as $campo_integrar=>$estructura){

            if(isset($estructura->foreign_key) && $estructura->foreign_key){

                $default = '';
                if(isset($estructura->default)){
                    $default = trim($estructura->default);
                }
                $integraciones = $this->integra_fk(campo_integrar: $campo_integrar,campos_origen:  $campos_origen,
                    integraciones:  $integraciones,table:  $table,default: $default);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al integrar campo', data: $integraciones);
                }
            }

        }
        return $integraciones;
    }

    final public function integra_foraneas(stdClass $campos, string $table)
    {
        $datos = $this->describe_table(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $datos);
        }
        $campos_origen = $datos->registros;

        $integraciones = $this->integra_fks(campos: $campos,campos_origen:  $campos_origen,table:  $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar campo', data: $integraciones);
        }

        return $integraciones;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Retorna los índices de una tabla especificada.
     *
     * @param string $table El nombre de la tabla.
     * @return array|stdClass Retorna los índices de la tabla especificada o un objeto Error en caso de error.
     * @version 15.22.0
     */
    final public function ver_indices(string $table): array|stdClass
    {
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }

        $sql = (new sql())->ver_indices(table: $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }

        $exe = $this->modelo->ejecuta_consulta(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

}

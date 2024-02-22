<?php
namespace gamboamartin\administrador\models;

use base\orm\_create;
use base\orm\estructuras;
use base\orm\modelo;
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

    private function actualiza_defaults(stdClass $atributos, string $campo, string $default)
    {
        $upds = array();
        if($default !== ''){
            if(isset($atributos->modelo)){
                $upds = $this->actualiza_rows_atributo(campo: $campo,default:  $default,
                    modelo:  $atributos->modelo);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al actualizar rows',data: $upds);
                }
            }
        }
        return $upds;

    }
    private function actualiza_rows_atributo(string $campo, string $default, modelo $modelo)
    {

        $registros = $modelo->registros(columnas_en_bruto: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros',data: $registros);
        }

        $upds = $this->upd_rows_default(campo: $campo,default:  $default,modelo:  $modelo,registros:  $registros);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar rows',data: $upds);
        }
        return $upds;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Método para agregar una columna a una tabla en la base de datos.
     *
     * @param stdClass $atributos Atributos de la columna para agregar.
     * @param string $campo Nombre de la columna a agregar.
     * @param string $table Nombre de la tabla a la cual agregar la columna.
     * @return array|stdClass Respuesta de la función add_colum si la operación fue exitosa, objeto
     * de error si ocurrió un error.
     * @version 16.38.0
     */
    private function add(stdClass $atributos, string $campo, string $table):array|stdClass
    {
        $valida = (new sql())->valida_column_base(campo: $campo,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos de entrada',data: $valida);
        }

        $atributos_fin = $this->ajusta_atributos(atributos: $atributos);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ajustar atributos_fin', data: $atributos_fin);
        }

        $add = $this->add_colum(campo: $campo, table: $table, tipo_dato: $atributos_fin->tipo_dato,
            default: $atributos_fin->default, longitud: $atributos_fin->longitud,
            not_null: $atributos_fin->not_null);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al agregar columna sql', data: $add);
        }
        return $add;

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

        $valida = (new sql())->valida_column_base(campo: $campo,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos de entrada',data: $valida);
        }

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

        $campos_origen = $this->campos_origen(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $campos_origen);
        }

        $adds = $this->adds(campos: $campos,campos_origen:  $campos_origen,table:  $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al agregar columna sql', data: $adds);
        }

        return $adds;
    }

    private function add_existente(array $adds, stdClass $atributos, string $campo, array $campos_origen,
                                   string $table, bool $valida_pep_8)
    {
        $valida = (new sql())->valida_column_base(campo: $campo,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos de entrada',data: $valida);
        }

        $existe_campo = $this->existe_campo_origen(campo_integrar: $campo,campos_origen:  $campos_origen);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe campo', data: $existe_campo);
        }

        if(!$existe_campo){
            $add = $this->add(atributos: $atributos,campo:  $campo,table:  $table);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al agregar columna sql', data: $add);
            }
            $adds[] = $add;
        }
        else{
            $campos_origen_data = $this->campos_origen(table: $table);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al ejecutar sql', data: $campos_origen_data);
            }
            foreach ($campos_origen_data as $campo_origen_data){
                if($campo_origen_data['Field'] === $campo){
                    $type_origen = trim(strtoupper($campo_origen_data['Type']));

                    $desglose = explode('(', $type_origen);
                    $tipo_dato_origen = strtoupper(trim($desglose[0]));

                    $type_new = 'VARCHAR';
                    if(isset($atributos->tipo_dato)) {
                        $type_new = trim(strtoupper($atributos->tipo_dato));
                    }
                    $longitud = '';
                    if(isset($atributos->longitud)) {
                        $longitud = trim($atributos->longitud);
                    }

                    if ($type_new !== $tipo_dato_origen) {
                        $modifica = $this->modifica_columna(
                            campo: $campo, longitud: $longitud, table: $table, tipo_dato: $type_new,
                            valida_pep_8: $valida_pep_8);
                        if (errores::$error) {
                            return $this->error->error(mensaje: 'Error al agregar modificar columnas', data: $modifica);
                        }
                        $adds[] = $modifica;
                    }



                }
            }

        }
        return $adds;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Método para agregar un índice único a una columna en una tabla.
     *
     * @param string $campo Nombre de la columna a la que se agregará el índice.
     * @param string $table Nombre de la tabla a la que pertenece la columna.
     * @param string $index_name Nombre del índice a agregar. Es opcional. Si no se especifica un nombre, el método generará uno.
     * @return array|stdClass La respuesta de la función index_unique si la operación fue exitosa, objeto de error si ocurrió un error.
     * @version 16.44.0
     */
    private function add_unique_base(string $campo, string $table, string $index_name = ''): array|stdClass
    {
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacia', data: $campo);
        }

        $columnas_unique = array($campo);
        $index_unique = $this->index_unique(columnas: $columnas_unique,table:  $table, index_name: $index_name);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar unique', data: $index_unique);
        }
        return $index_unique;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Este método privado 'add_uniques_base' agrega índices únicos a la base en una tabla específica.
     *
     * @param stdClass $campos_por_integrar Un objeto cuyas propiedades son campos para integrar.
     * @param string $table El nombre de la tabla en la que se deben agregar los índices únicos.
     *
     * @return array Retorna una matriz de índices únicos agregados exitosamente.
     *
     * Este método itera sobre cada campo en el parámetro $campos_por_integrar. Si el atributo 'unique' está establecido y es verdadero,
     * se intentará agregar un índice único a la base para el campo.
     * Además, si hay disponible un 'index_name', se utilizará como nombre para el índice único. Si no, se generará un nombre por defecto.
     *
     * Si hay un error durante la adición de un índice único, el método retornará un error utilizando el controlador de errores.
     *
     * De lo contrario, el índice único se agregará a la lista de índices únicos que se ha agregado con éxito y que será devuelto al final del método.
     * @version 16.45.0
     */
    private function add_uniques_base(stdClass $campos_por_integrar, string $table): array
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

    private function adds(stdClass $campos, array $campos_origen, string $table)
    {
        $adds = array();
        foreach ($campos as $campo=>$atributos){
            $valida_pep_8 = true;
            if(isset($atributos->valida_pep_8)){
                $valida_pep_8 = $atributos->valida_pep_8;
            }
            $adds = $this->add_existente(adds: $adds,atributos:  $atributos,campo:  $campo,
                campos_origen:  $campos_origen,table:  $table, valida_pep_8: $valida_pep_8);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al agregar columna sql', data: $adds);
            }
        }
        return $adds;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Ajusta los atributos de un objeto data para su uso en funciones posteriores.
     *
     * @param stdClass $atributos Los atributos a ajustar.
     * @return stdClass $data Un nuevo objeto data con los atributos ajustados.
     * @version 16.20.0
     */
    private function ajusta_atributos(stdClass $atributos): stdClass
    {
        $tipo_dato = $this->tipo_dato(atributos: $atributos);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ajustar tipo dato', data: $tipo_dato);
        }
        $default = $this->default(atributos: $atributos);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ajustar default', data: $default);
        }
        $longitud = $this->longitud(atributos: $atributos, tipo_dato: $tipo_dato);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ajustar default', data: $default);
        }
        $not_null = $this->not_null(atributos: $atributos);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ajustar not_null', data: $not_null);
        }

        $data = new stdClass();
        $data->tipo_dato = $tipo_dato;
        $data->default = $default;
        $data->longitud = $longitud;
        $data->not_null = $not_null;

        return $data;

    }


    /**
     * POR DOCUMENTAR EN WIKI
     * Método privado que ajusta el tipo de dato de los atributos recibidos.
     *
     * Este método se encarga de verificar que exista el atributo 'tipo_dato' y luego convertir este atributo a mayúsculas.
     *
     * @param stdClass $atributos Objeto de tipo stdClass que contiene los atributos a ajustar.
     * @return stdClass|array Retorna el objeto con los atributos ajustados. Si ocurre un error durante la verificación,
     * retorna un arreglo con la información del error.
     * @version 16.9.0
     */
    private function ajusta_tipo_dato(stdClass $atributos): stdClass|array
    {
        $keys = array('tipo_dato');
        $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $atributos,valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar atributos', data: $valida);
        }

        $atributos->tipo_dato = strtoupper($atributos->tipo_dato);
        return $atributos;

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

    final public function campo_status(
        stdClass $campos, string $name_campo, string $default = 'inactivo'): array|stdClass
    {
        $name_campo = trim($name_campo);
        if($name_campo === ''){
            return $this->error->error(mensaje: 'Error name_campo esta vacio', data: $name_campo);
        }
        $campos->$name_campo = new stdClass();
        $campos->$name_campo->default = $default;

        return $campos;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Agregar campos de tipo "double" a una instancia existente de stdClass
     *
     * @param stdClass $campos Instancia a la cual agregar nuevos campos de tipo "double".
     * @param array $campos_new array de strings, donde cada string es el nombre de un nuevo campo a añadir.
     *
     * @return array|stdClass Objeto con los nuevos campos de tipo "double" añadidos, o un mensaje de error si ocurre algún problema.
     *
     * @version 16.85.0
     *
     */
    final public function campos_double(stdClass $campos, array $campos_new): array|stdClass
    {
        foreach ($campos_new as $campo_new){
            $campo_new = trim($campo_new);
            if($campo_new === ''){
                return $this->error->error(mensaje: 'Error campo_new esta vacio', data: $campo_new);
            }
            $campos = $this->campo_double(campos: $campos,name_campo:  $campo_new);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar campo double '.$campo_new, data: $campos);
            }
        }
        return $campos;

    }
    /**
     * POR DOCUMENTAR EN WIKI
     * Metodo campos_origen
     *
     * Este método se encarga de describir los campos de una tabla específica en la base de datos.
     *
     * @param string $table - Nombre de la tabla para describir.
     *
     * @return array - Retorna los registros de los campos de la tabla si la operación es exitosa,
     *                 de lo contrario, devuelve un mensaje de error.
     *
     * @throws errores - Lanza una excepción si ocurre un error al validar la tabla o ejecutar la consulta SQL.
     *
     * @version 15.66.1
     */
    private function campos_origen(string $table): array
    {
        $table = trim($table);
        $valida = (new val_sql())->tabla(tabla: $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar table', data: $valida);
        }
        $datos = $this->describe_table(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $datos);
        }
        return $datos->registros;

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

    /**
     * POR DOCUMENTAR EN WIKI
     * Este método se encarga de procesar los campos double por defecto.
     *
     * @param stdClass $campos Un objeto en el que cada propiedad representa un campo. Al pasar por este método, estos campos podrían ser transformados.
     * @param array $name_campos Un array con los nombres de los campos a procesar.
     *
     * @return stdClass|array Devuelve el objeto de $campos con los campos procesados.
     *
     * @throws errores Si el nombre del campo está vacío o si ocurre un error al procesar campo double.
     *
     * Ejemplo de uso:
     * ```php
     * $campos = new stdClass();
     * $campos->campo1 = 1.1;
     * $campos->campo2 = 2.2;
     *
     * $name_campos = ['campo1', 'campo2'];
     *
     * $instalacion = new _instalacion();
     * $campos_actualizados = $instalacion->campos_double_default($campos, $name_campos);
     *
     * print_r($campos_actualizados);
     * ```
     * @version 15.27.0
     */
    final public function campos_double_default(stdClass $campos, array $name_campos): array|stdClass
    {
        foreach ($name_campos as $name_campo){
            $name_campo = trim($name_campo);
            if($name_campo === ''){
                return $this->error->error(mensaje: 'Error name_campo esta vacio', data: $name_campo);
            }

            $campos = $this->campo_double(campos: $campos,name_campo:  $name_campo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar campo double', data: $campos);
            }
        }
        return $campos;


    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Crea una tabla utilizando la información suministrada.
     *
     * @param stdClass $campos Objeto que contiene la información sobre los campos requeridos para la tabla
     * @param string $table Nombre de la tabla que se va a crear
     * @throws errores Si no se puede obtener campos_base o al generar sql o al ejecutar sql, lanza una excepción
     * @return array|stdClass Si hay un error, la función regresa un array o un objeto stdClass con la estructura de:
     *      'sql' (la consulta sql generada),
     *      'errores' (mensaje de error),
     *      'exe' (resultado de la ejecución de la consulta sql),
     *      'indexs_unique' (resultado de agregar los campos únicos a la tabla)
     * @final
     * @version 16.46.0
     */
    final public function create_table(stdClass $campos, string $table): array|stdClass
    {
        if(count((array)$campos) === 0){
            $campos = (new _create())->campos_base(campos: $campos);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener campos_base',data: $campos);
            }
        }
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error al table esta vacia',data: $table);
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
     * Crea una nueva tabla en la base de datos con el nombre de tabla dado.
     *
     * @param string $table El nombre de la tabla que se va a crear.
     *
     * @return array|string|stdClass Devuelve un mensaje string si ya existe la tabla.
     * Si la tabla no existe, intenta crearla. Si tiene éxito, retorna el resultado del proceso de creación de la tabla.
     * (puede ser un array, string o un stdClass dependiendo del proceso de creación).
     * Si hay un error durante el proceso, devolverá un objeto de error (instancia de la clase 'errores').
     *
     * @throws errores Lanza una excepción si hay un error durante el proceso de verificación o creación de la tabla.
     * @version 16.48.0
     */

    final public function create_table_new(string $table): array|string|stdClass
    {
        $create_table = 'Ya existe tabla '.$table;
        $existe_entidad = $this->existe_entidad(table: $table);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al verificar table', data:  $existe_entidad);
        }

        if(!$existe_entidad) {

            $campos = new stdClass();
            $create_table = $this->create_table(campos: $campos, table: $table);
            if (errores::$error) {
                return (new errores())->error(mensaje: 'Error al crear table', data: $create_table);
            }
        }

        return $create_table;

    }

    final public function data_adm(string $descripcion, modelo $modelo, array $row_ins, array $filtro = array())
    {
        if(count($filtro) === 0) {
            $filtro = array();
            $filtro[$modelo->tabla . '.descripcion'] = $descripcion;
        }

        $existe = $modelo->existe(filtro: $filtro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener '.$modelo->tabla, data:  $existe);
        }
        if(!$existe){

            $alta = $modelo->alta_registro(registro: $row_ins);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al insertar menu', data:  $alta);
            }
            $id = $alta->registro_id;
        }
        else{
            $r_filtro= $modelo->filtro_and(filtro: $filtro);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al obtener datos', data:  $r_filtro);
            }
            $id = $r_filtro->registros[0][$modelo->key_id];
        }
        return $id;

    }
    /**
     * POR DOCUMENTAR EN WIKI
     * Método 'default'
     *
     * Si el objeto $atributos tiene un atributo 'default', este método
     * retornará el valor de dicho atributo. En caso contrario, retornará una
     * cadena de texto vacía.
     *
     * @param stdClass $atributos, un objeto estándar de PHP, que puede contener un
     *                             atributo llamado 'default'.
     *
     * @return string  Retorna el valor del atributo 'default' si está presente en $atributos.
     *                 Se retorna una cadena vacía si 'default' no es un atributo en $atributos.
     *
     * @access private
     * @version 16.11.0
     */
    private function default(stdClass $atributos): string
    {
        $default = '';
        if(isset($atributos->default)){
            $default = $atributos->default;
        }
        return $default;

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
    final public function describe_table(string $table): array|stdClass
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

    final public function drop_table_segura(string $table): array|stdClass|string
    {
        $exe = 'No existe la entidad '.$table;
        $existe = $this->existe_entidad(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error verificar si existe entidad', data: $existe);
        }

        if($existe) {
            $exe = $this->drop_table(table: $table);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
            }
        }
        return $exe;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Verifica si un campo específico existe en un conjunto de campos dado.
     *
     * @param string $campo_integrar El nombre del campo a buscar.
     * @param array $campos_origen Un array de campos en los que buscar el campo.
     * @return bool|array Retorna true si el campo existe en el conjunto, false en caso contrario.
     * @version 15.50.1
     */
    private function existe_campo_origen(string $campo_integrar, array $campos_origen): bool|array
    {
        $campo_integrar = trim($campo_integrar);
        if($campo_integrar === ''){
            return $this->error->error(mensaje: 'Error campo_integrar esta vacio', data: $campo_integrar);
        }

        $existe_campo = false;
        foreach ($campos_origen as $datos_campos){
            if(!is_array($datos_campos)){
                return $this->error->error(mensaje: 'Error datos_campos debe ser  un array', data: $datos_campos);
            }
            $keys = array('Field');
            $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro: $datos_campos);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar datos_campos', data: $valida);
            }

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

    private function existe_foreign(string $name_indice_opt, string $relacion_table, string $table)
    {
        $datas_index = $this->  get_data_indices(name_indice_opt: $name_indice_opt,relacion_table:  $relacion_table, table: $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error obtener datos de indices', data: $datas_index);
        }
        $existe_indice = $this->existe_foreign_base(datas_index: $datas_index);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe indice', data: $existe_indice);
        }
        return $existe_indice;

    }

    private function existe_foreign_base(stdClass $datas_index): bool
    {
        $existe_indice = false;
        foreach ($datas_index->indices as $indice){
            if($datas_index->name_indice === $indice->CONSTRAINT_NAME){
                $existe_indice = true;
                break;
            }
        }
        return $existe_indice;

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

    /**
     * Se ocupa de las operaciones en las tablas foráneas de una tabla dada.
     *
     * @param array $foraneas Array de claves foránea. Cada clave foránea es un conjunto clave-valor, donde la
     * clave es el nombre de la clave foránea y el valor es el valor de la clave foránea.
     * @param string $table
     * @return array Regresa un array con los resultados de la operación.
     *
     * @example
     * $foraneas = array();
     * $foraneas['b'] = '';
     * $tabla = 'a';
     * $resultado = $ins->foraneas($foraneas, $tabla);
     *
     */
    final public function foraneas(array $foraneas, string $table): array
    {
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table esta vacia',data: $table);
        }
        if(is_numeric($table)){
            return $this->error->error(mensaje: 'Error table debe ser un texto',data: $table);
        }
        $results = array();
        foreach ($foraneas as $campo=>$atributos){

            if(is_string($atributos)){
                $atributos = new stdClass();
            }
            $default = '';
            if(isset($atributos->default)){
                $default = trim($atributos->default);
            }
            $name_indice_opt = '';
            if(isset($atributos->name_indice_opt)){
                $name_indice_opt = $atributos->name_indice_opt;
            }
            $valida = (new sql())->valida_column_base(campo: $campo,table:  $table);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar datos de entrada',data: $valida);
            }

            $result = $this->foreign_key_seguro(campo: $campo,table: $table, default: $default,
                name_indice_opt: $name_indice_opt);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al ajustar foranea', data:  $result);
            }

            $upds = $this->actualiza_defaults(atributos: $atributos,campo:  $campo,default:  $default);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al actualizar rows',data: $upds);
            }
            //$result->upds = $upds;

            $results[] = $result;

        }
        return $results;

    }

    /**
     * Esta es la función 'foreign_key_completo'.
     *
     * Retorna una clave extranjera completa a partir de la especificación dada.
     *
     * @param string $campo El nombre del campo que se va a usar como clave extranjera.
     * @param string $table El nombre de la tabla en la que se va a agregar el nuevo campo.
     * @param string $default Un valor predeterminado opcional que se le asignará al campo recién creado.
     * @return array|stdClass Retorna una clave extranjera completa en caso de éxito, y un objeto de errores en caso contrario
     *
     * @example Ejemplo de uso:
     * <?php
     * $instalacion->foreign_key_completo('id_usuario', 'usuarios', '1');
     * ?>
     *
     * Este código intenta crear una nueva clave extranjera 'id_usuario' en la tabla 'usuarios' con un valor predeterminado de '1'.
     * Si los parámetros 'id_usuario' y 'usuarios' son válidos y no existen errores durante la ejecución, se retorna una clave extranjera.
     * Si ocurre un error, se retorna un objeto de error.
     */
    final public function foreign_key_completo(string $campo, string $table, string $default = '',
                                               string $name_indice_opt =''): array|stdClass
    {
        $valida = (new sql())->valida_column_base(campo: $campo,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos de entrada',data: $valida);
        }

        $exe = $this->add_colum(campo: $campo, table: $table, tipo_dato: 'bigint', default: $default, longitud: 100);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar add_column', data: $exe);
        }

        $fk = $this->foreign_por_campo(campo: $campo, table: $table, name_indice_opt: $name_indice_opt);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $fk);
        }

        return $fk;

    }

    /**
     * Verifica si existe el atributo y agrega una clave foránea (FOREIGN KEY) en una tabla dada.
     *
     * @param string $table El nombre de la tabla en la cual se verificará la clave foránea.
     * @param string $relacion_table El nombre de la tabla con la cual se establece la relación.
     * @param string $name_indice_opt Opcional. El nombre personalizado del índice. Si no se proporciona,
     * se genera automáticamente.
     *
     * @return array|stdClass Devuelve un objeto de tipo stdClass con el resultado de la consulta SQL.
     * En caso de error, devuelve un array con información sobre el error.
     * @throws errores En caso de error, se lanza una excepción con información detallada sobre el mismo.
     */
    final public function foreign_key_existente(
        string $relacion_table, string $table, string $name_indice_opt = ''): array|stdClass
    {
        $table = trim($table);
        if ($table === '') {
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }
        $relacion_table = trim($relacion_table);
        if ($relacion_table === '') {
            return $this->error->error(mensaje: 'Error relacion_table esta vacia', data: $relacion_table);
        }


        $existe_foreign = $this->existe_foreign(
            name_indice_opt: $name_indice_opt,relacion_table:  $relacion_table,table:  $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe indice', data: $existe_foreign);
        }


        $exe = new stdClass();
        $exe->mensaje = 'El indice ya existe de la table '.$table.' Relacion '.$relacion_table;

        if(!$existe_foreign) {

            $sql = (new sql())->foreign_key(table: $table, relacion_table: $relacion_table,
                name_indice_opt: $name_indice_opt);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
            }
            $exe = $this->modelo->ejecuta_sql(consulta: $sql);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
            }
        }
        return $exe;

    }

    /**
     * foreign_key_seguro
     *
     * Esta función valida y gestiona keys foráneas en la base de datos de forma segura
     *
     * @param string $campo Campo para el que se genera la key foránea
     * @param string $table Tabla relacionada con la key foránea
     * @param string $default Valor por defecto para el campo
     *
     * @return array|stdClass Retorna un array o un objeto stdClass dependiendo del resultado del proceso
     */
    final public function foreign_key_seguro(
        string $campo, string $table, string $default = '', string $name_indice_opt = ''):array|stdClass
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
            $fk = $this->foreign_key_completo(campo: $campo, table: $table, default: $default, name_indice_opt: $name_indice_opt);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar foreign', data: $fk);
            }
        }
        else{
            $fk = $this->foreign_no_conf_integra(campo: $campo, campos_origen: $campos_origen,
                name_indice_opt: $name_indice_opt, table: $table);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar foreign no conf', data: $fk);
            }
        }


        return $fk;

    }

    /**
     * Este método verifica y maneja la asignación de llaves extranjeras a un campo específico en una tabla.
     *
     * Primero, verifica que tanto el campo como la tabla no estén vacíos. Luego, se asegura de que el parámetro
     * $campo_origen contenga la clave 'Key'. Si el valor de 'Key' no es 'MUL', se llama al método
     * foreign_por_campo para tratar de asignar una llave extranjera al campo. Si este proceso encuentra
     * algún error, se registra y se retorna el error.
     *
     * @param string $campo El nombre del campo al que se va a asignar la llave extranjera.
     * @param array $campo_origen Un arreglo que contiene los detalles del campo original.
     *                              Se espera que tenga una clave 'Key'.
     * @param string $table El nombre de la tabla que contiene el campo.
     * @return string|stdClass|array Retorna un mensaje indicando que el campo fue asignado si el proceso es
     *                              exitoso, o un objeto de error si se encuentra algún problema.
     */
    private function foreign_no_conf(string $campo, array $campo_origen, string $name_indice_opt, string $table):string|stdClass|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo);
        }
        $table = trim($table);
        if ($table === '') {
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }

        if(!isset($campo_origen['Key'])){
            $campo_origen['Key'] = '';
        }
        $fk = 'Campo asignado '.$campo;
        if($campo_origen['Key'] !== 'MUL'){
            $fk = $this->foreign_por_campo(campo: $campo,table:  $table, name_indice_opt: $name_indice_opt);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar foreign', data: $fk);
            }
        }
        return $fk;

    }

    /**
     * Este método integra una relación de clave ajena (Foreign Key) para la cual no haya una configuración definida.
     *
     * @param string $campo Es el campo para el cual se desea establecer la relación de clave ajena.
     * @param array  $campos_origen Es una lista de campos de origen entre los cuales se establecerá la relación.
     * @param string $table Es la tabla en que se desea establecer la relación.
     *
     * @return array Devuelve un arreglo que contiene los detalles de las claves ajenas establecidas.
     *    En caso de error devuelve un mensaje de error con detalles al respecto.
     *
     * @throws errores En caso de que ocurra cualquier error en la ejecución, se lanza una excepción con detalles.
     */
    private function foreign_no_conf_integra(string $campo, array $campos_origen, string $name_indice_opt, string $table):array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo);
        }
        $table = trim($table);
        if ($table === '') {
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }

        $fks = array();
        foreach ($campos_origen as $campo_origen){
            if(!is_array($campo_origen)){
                return $this->error->error(mensaje: 'Error campo_origen no es un array', data: $campo_origen);
            }
            $keys = array('Field');
            $valida = (new validacion())->valida_existencia_keys(keys: $keys,registro:  $campo_origen);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar campo_origen', data: $valida);
            }

            $campo_origen_name = trim($campo_origen['Field']);

            if($campo_origen_name === $campo) {

                $fk = $this->foreign_no_conf(campo: $campo, campo_origen: $campo_origen,
                    name_indice_opt: $name_indice_opt, table: $table);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al integrar foreign', data: $fk);
                }
                $fks[] = $fk;
                break;
            }
        }
        return $fks;

    }

    /**
     * Este método se utiliza para crear la clave foránea de un campo existente de una tabla específica en función de un campo específico.
     * Realiza varias validaciones y devoluciones en caso de errores.
     *
     * @param string $campo El campo del que se quiere obtener la clave foránea.
     * @param string $table La tabla en la que se va a buscar la clave foránea.
     *
     * @return stdClass|array Devuelve la clave foránea si la operación tuvo éxito, de lo contrario devuelve un error.
     *
     * @throws errores en caso de que ocurra un error al generar SQL.
     *
     * ### Ejemplo de uso - Supongamos que tenemos la tabla 'usuarios' con campo 'ciudad_id':
     *
     * ```php
     * $instalacion = new _instalacion();
     * $result = $instalacion->foreign_por_campo('ciudad_id', 'usuarios');
     *
     * if (errores::$error) {
     *     echo 'Error!';
     *     die;
     * }
     * echo $result;
     * ```
     */
    private function foreign_por_campo(string $campo, string $table, string $name_indice_opt): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo);
        }
        $table = trim($table);
        if ($table === '') {
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }
        $valida = (new validacion())->key_id(txt: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar campo', data: $valida);
        }

        $explode_campo = explode('_id', $campo);
        $relacion_table = $explode_campo[0];

        $fk = $this->foreign_key_existente(relacion_table: $relacion_table, table: $table,
            name_indice_opt: $name_indice_opt);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $fk);
        }

        return $fk;

    }

    private function get_data_indices(string $name_indice_opt, string $relacion_table,  string $table)
    {
        $indices = $this->get_foraneas(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener indices', data: $indices);
        }

        $name_indice = (new sql())->name_index_foranea(
            name_indice_opt: $name_indice_opt,relacion_table:  $relacion_table,table:  $table);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error obtener name_indice', data: $name_indice);
        }

        $data = new stdClass();
        $data->indices = $indices;
        $data->name_indice = $name_indice;

        return $data;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Obtiene las claves foráneas de una tabla especificada.
     *
     * @param string $table - Nombre de la tabla a consultar.
     *
     * @return array|stdClass - Retorna un objeto con los resultados en caso de éxito,
     *                 o un error en caso de fallo.
     *
     *
     * Uso:
     *      $result = get_foraneas('users');
     *
     * @version 16.90.0
     */
    private function get_foraneas(string $table): array|stdClass
    {
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table vacia',data:  $table);
        }

        $sql = (new sql())->get_foraneas(table: $table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
        }

        $result = $this->modelo->ejecuta_consulta(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }

        return $result->registros_obj;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * La función index_unique se utiliza para crear un índice único en una tabla específica.
     *
     * @param array $columnas representa las columnas para las cuales se va a crear el índice único.
     * @param string $table es el nombre de la tabla sobre la que se va a establecer el índice.
     * @param string $index_name es el nombre opcional que se puede dar al índice.
     *
     * @return array|stdClass $exe  representa la respuesta obtenida después de la ejecución del sql.
     * 16.40.0
     *
     */
    final public function index_unique(array $columnas,string $table, string $index_name = ''): array|stdClass
    {
        if(count($columnas ) === 0){
            return $this->error->error(mensaje: 'Error columnas esta vacia', data: $columnas);
        }
        $table = trim($table);
        if($table === ''){
            return $this->error->error(mensaje: 'Error table esta vacia', data: $table);
        }
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
     * Método que determina la longitud del atributo basado en el tipo de dato.
     *
     * Este método establece la longitud por defecto de diferentes tipos de datos (por ejemplo, DOUBLE y TIMESTAMP).
     * Si se especifica la longitud en los atributos, se sobreescribirá la longitud predeterminada.
     *
     * @param stdClass $atributos Los atributos del campo para el que se determinará la longitud.
     * Si la longitud es especificada en los atributos, esta será utilizada.
     * @param string $tipo_dato El tipo de dato para el que se determinará la longitud.
     *
     * @return string Retorna la longitud determinada para el atributo.
     * @version 16.12.0
     */
    private function longitud(stdClass $atributos, string $tipo_dato): string
    {
        $longitud = '255';

        if($tipo_dato === 'DOUBLE'){
            $longitud = '100,4';
        }
        if($tipo_dato === 'TIMESTAMP'){
            $longitud = '';
        }
        if($tipo_dato === 'DATE'){
            $longitud = '';
        }
        if($tipo_dato === 'DATETIME'){
            $longitud = '';
        }
        if($tipo_dato === 'LONGBLOB'){
            $longitud = '';
        }
        if($tipo_dato === 'BLOB'){
            $longitud = '';
        }
        if(isset($atributos->longitud)){
            $longitud = $atributos->longitud;
        }
        return $longitud;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Modifica especificaciones de una columna existente en la base de datos.
     *
     * @param string $campo El nombre de la columna a modificar.
     * @param string $longitud La nueva longitud o especificaciones de la columna.
     * @param string $table El nombre de la tabla que contiene la columna a modificar.
     * @param string $tipo_dato El nuevo tipo de dato de la columna.
     * @param bool $valida_pep_8 Indica si se deben seguir las reglas del estándar PEP 8 para el nombre de la columna.
     *
     * @return stdClass|array Retorna resultado de la ejecución SQL si es exitosa, o alternativamente un
     * error si ocurre algún problema.
     * @version 16.127.0
     */
    final public function modifica_columna(
        string $campo, string $longitud, string $table, string $tipo_dato, bool $valida_pep_8 = true):stdClass|array
    {
        $campo = trim($campo);
        $table = trim($table);
        $tipo_dato = trim($tipo_dato);
        $longitud = trim($longitud);

        $valida = (new sql())->valida_datos_modify(
            campo: $campo,table:  $table,tipo_dato:  $tipo_dato, valida_pep_8: $valida_pep_8);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $sql = (new sql())->modify_column(
            campo: $campo,table:  $table,tipo_dato:  $tipo_dato,longitud: $longitud, valida_pep_8: $valida_pep_8 );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sql', data: $sql);
        }
        $exe = $this->modelo->ejecuta_sql(consulta: $sql);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $exe);
        }
        return $exe;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función maneja el parámetro not_null dentro de los atributos de entrada.
     *
     * @param stdClass $atributos El objeto que contiene los atributos de entrada.
     * @return bool Retorna el valor de not_null si está definido dentro de los atributos de entrada,
     * de lo contrario, retorna true.
     * @version 16.16.0
     */
    private function not_null(stdClass $atributos): bool
    {
        $not_null = true;
        if(isset($atributos->not_null)){
            $not_null = $atributos->not_null;
        }
        return $not_null;

    }

    /**
     * POD DOCUMENTAR EN WIKI
     * Esta función se encarga de ajustar el tipo de dato de una propiedad definida en un
     * objeto estándar de PHP (stdClass).
     *
     * - Permite verificar y ajustar el tipo de dato de una propiedad específica,
     *   siempre y cuando esta propiedad esté disponible en el objeto.
     * - Si se produce un error durante el ajuste del tipo de dato, se detiene la
     *   ejecución de la función y retorna un error específico.
     * - Por defecto, si no se especifica cualquier tipo de dato para el atributo,
     *   se inicializa como 'VARCHAR'.
     * - Retorna el tipo de dato ajustado o el valor por defecto 'VARCHAR' en caso de
     *   que el atributo no esté definido en el objeto.
     *
     * @param stdClass $atributos El objeto que contiene los atributos con los respectivos
     *                            tipos de datos que necesitan ser ajustados.
     *
     * @return string|array Retorna una cadena de texto que representa el tipo de dato ajustado,
     *                o 'VARCHAR' como un tipo de dato predeterminado.
     *
     * @version 16.10.0
     */
    private function tipo_dato(stdClass $atributos): string|array
    {
        $tipo_dato = 'VARCHAR';
        if(isset($atributos->tipo_dato)){
            $atributos = $this->ajusta_tipo_dato(atributos: $atributos);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al ajustar tipo dato', data: $atributos);
            }
            $tipo_dato = $atributos->tipo_dato;
        }
        return $tipo_dato;

    }

    private function upd_row_default(string $campo, string $default, modelo $modelo, array $registro)
    {
        $registro_upd = array();
        $registro_upd[$campo] = $default;
        $upd = $modelo->modifica_bd_base(registro: $registro_upd, id: $registro['id'], valida_row_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualiza row',data: $upd);
        }
        return $upd;

    }

    private function upd_rows_default(string $campo, string $default, modelo $modelo, array $registros)
    {
        $upds = array();
        foreach ($registros as $registro){
            if(!isset($registro[$campo])){
                continue;
            }
            if((int)$registro[$campo] === 0){
                $upd = $this->upd_row_default(campo: $campo,default:  $default,modelo:  $modelo,registro:  $registro);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al actualizar row',data: $upd);
                }
                $upds[] = $upd;
            }
        }
        return $upds;

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

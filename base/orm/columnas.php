<?php
namespace base\orm;
use gamboamartin\administrador\modelado\validaciones;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class columnas{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * TOTAL
     * Añade una columna en una consulta SQL.
     *
     * @param string $alias Identificador único para la columna a añadir.
     * @param string $campo Identifica la columna en una tabla de la base de datos.
     *
     * @return string|array Retorna una cadena qué representa la sentencia SQL para sumar y añadir una columna en la consulta.
     *                      Retorna un arreglo en caso de que haya un error con los parámetros de entrada.
     *
     * @throws errores En caso de que $alias o $campo esten vacios.
     * @version 16.30.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.columnas.add_column.21.16.0
     */
    final public function add_column(string $alias, string $campo): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error $campo no puede venir vacio', data: $campo, es_final: true);
        }
        $alias = trim($alias);
        if($alias === ''){
            return $this->error->error(mensaje:'Error $alias no puede venir vacio', data: $alias, es_final: true);
        }
        return 'IFNULL( SUM('. $campo .') ,0)AS ' . $alias;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método privado que permite ajustar las columnas completas para una consulta SQL.
     *
     * @param string $columnas Cadena de caracteres con el nombre de las columnas.
     * @param bool $columnas_en_bruto Bandera para indicar si las columnas están en bruto.
     * @param array $columnas_sql Memoria de las columnas SQL en un array.
     * @param modelo_base $modelo Instancia del modelo base.
     * @param string $tabla Nombre de la tabla.
     * @param string $tabla_renombrada Nombre de la tabla renombrada.
     *
     * @return array|string El resultado puede ser una matriz de columnas o una cadena de error.
     *
     * Un paso del método es verificar que el nombre de la tabla no sea numérico. De serlo, se genera un error.
     *
     * Después se genera las columnas de la consulta, en caso de error en la generación de las columnas, se genera un error.
     *
     * Posteriormente se integran las columnas por data. Si ocurre un error en la integración de columnas, se genera un error.
     *
     * En caso de no haber errores, se devuelve las columnas generadas.
     * @version 15.73.1
     */
    private function ajusta_columnas_completas(string $columnas, bool $columnas_en_bruto, array $columnas_sql,
                                               modelo_base $modelo, string $tabla, string $tabla_renombrada): array|string
    {
        $tabla = str_replace('models\\','',$tabla);
        if(is_numeric($tabla)){
            return $this->error->error(mensaje: 'Error $tabla no puede ser un numero',data:  $tabla, es_final: true);
        }

        $resultado_columnas = $this->genera_columnas_consulta(columnas_en_bruto: $columnas_en_bruto,
            modelo: $modelo, tabla_original: $tabla, tabla_renombrada: $tabla_renombrada, columnas: $columnas_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnas', data: $resultado_columnas);
        }

        $columnas_env = $this->integra_columnas_por_data(columnas: $columnas,resultado_columnas:  $resultado_columnas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas_env);
        }

        return $columnas_env;
    }


    /**
     * TOTAL
     * Esta función verifica si el arreglo de entrada $columnas_by_table contiene algún elemento. Si el conteo
     * del arreglo es más de cero, la función devolverá true, de lo contrario, devolverá false.
     * En otras palabras, verifica si hay alguna columna en el arreglo dado que necesite ser aplicada a la tabla.
     * Si la hay, devolverá true, indicando que la operación de aplicar columnas a la tabla puede realizarse.
     * De lo contrario, devuelve false.
     *
     * @param array $columnas_by_table conjunto de columnas si es vacio aplica la sentencia SQL completa
     * @return bool
     * @version 13.16.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.columnas.aplica_columnas_by_table
     */
    private function aplica_columnas_by_table(array $columnas_by_table): bool
    {
        $aplica_columnas_by_table = false;

        if(count($columnas_by_table)>0){
            $aplica_columnas_by_table = true;
        }
        return $aplica_columnas_by_table;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función asigna_columna_completa
     *
     * Esta función asigna los valores de una columna obtenidos mediante un DESCRIBE
     * para su uso en la creación de una consulta SQL SELECT.
     *
     * @param string $atributo El nombre del atributo a asignar.
     * @param array $columna Un array asociativo con los datos de la columna obtenidos a partir de un DESCRIBE.
     * @param array $columnas_completas Un array asociativo que contiene el estado actual de las columnas completas.
     *
     * @return array Retorna las columnas completas con el nuevo atributo asignado.
     *
     * @throws errores Se lanza esta excepción si el $atributo es una cadena vacía
     * @throws errores Se lanza esta excepción si hay un error al validar la $columna
     *
     * @example
     * ```php
     * $resultado = asigna_columna_completa('nombre', ['Type' => 'int', 'Null' => 'NO', 'Key' => 'PRI'], []);
     * echo $resultado; // ['nombre' => ['campo' => 'nombre', 'Type' => 'int', 'Key' => 'PRI', 'Null' => 'NO']]
     * ```
     *
     * Posibles Resultados:
     * - Un array asociativo con las columnas completas y el nuevo atributo asignado.
     * - Un error si el $atributo es una cadena vacía
     * - Un error si hay problemas al validar la $columna
     * @version 15.38.1
     */
    private function  asigna_columna_completa(string $atributo, array $columna, array $columnas_completas): array
    {
        $atributo = trim($atributo);
        if($atributo === ''){
            return $this->error->error(mensaje: 'Error atributo no puede venir vacio', data: $atributo, es_final: true);
        }
        $keys = array('Type','Null');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $columna);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar $columna', data: $valida);
        }
        if(!isset($columna['Key']) ){
            $columna['Key'] = '';
        }
        $columnas_completas[$atributo]['campo'] = $atributo;
        $columnas_completas[$atributo]['Type'] = $columna['Type'];
        $columnas_completas[$atributo]['Key'] = $columna['Key'];
        $columnas_completas[$atributo]['Null'] = $columna['Null'];

        return $columnas_completas;
    }

    /**
     * TOTAL
     * Asigna las columnas correspondientes a una tabla específica a un modelo y las almacena en la sesión.
     *
     * Esta función se encuentra en el archivo 'base/orm/columnas.php'.
     * Su objetivo es asignar las columnas detalladas en la sesión ($_SESSION) para una tabla específica a un modelo,
     * y si las columnas ya están definidas en la sesión, asigna los datos de columna al modelo.
     *
     * @param modelo_base $modelo El modelo al que se le asignarán las columnas de la tabla.
     * @param string $tabla_bd El nombre de la tabla en la base de datos.
     *
     * @return bool|array Devuelve true en caso de éxito. Si ocurre un error, devuelve un array con la información del error.
     * Si las columnas para la tabla especificada no están definidas en la sesión, devuelve false.
     * @version 13.16.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.columnas.asigna_columnas_en_session
     */
    private function asigna_columnas_en_session(modelo_base $modelo, string $tabla_bd): bool|array
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd===''){
            return $this->error->error(mensaje: 'Error tabla_bd no puede venir vacia', data: $tabla_bd, es_final: true);
        }
        $data = new stdClass();
        if(isset($_SESSION['campos_tabla'][$tabla_bd], $_SESSION['columnas_completas'][$tabla_bd])){
            $data = $this->asigna_data_columnas(data: $data,tabla_bd: $tabla_bd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar columnas', data: $data);
            }
            $modelo->data_columnas = $data;
            return true;
        }
        return false;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función que asigna columnas parseadas, añade nuevos atributos a la colección de columnas parseadas.
     *
     * @param string $atributo Nombre del atributo a añadir
     * @param array $columnas_parseadas Colección existente de columnas parseadas
     *
     * @return array Retorna la colección de columnas parseadas añadidas con el nuevo atributo. En caso de error,
     * devuelve un mensaje de error indicando que el atributo no puede estar vacío.
     * @version 15.30.1
     */
    private function asigna_columnas_parseadas(string $atributo, array $columnas_parseadas): array
    {
        $atributo = trim($atributo);
        if($atributo === ''){
            return $this->error->error(mensaje: 'Error atributo no puede venir vacio',data:  $atributo, es_final: true);
        }
        $columnas_parseadas[] = $atributo;
        return $columnas_parseadas;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Asigna las columnas a la sesión y al modelo dado como parámetro.
     *
     * @param modelo_base $modelo El modelo base para el que se deben asignar las columnas.
     * @param string $tabla_bd Es el nombre de la tabla en la base de datos del modelo.
     *
     * @return array|stdClass Regresa las columnas asignadas al modelo o un error si ocurre algo inesperado.
     *
     * @throws errores Si la tabla pasada está vacía o si es numérica, lanza una excepción.
     *
     * @throws errores Si hay un error al obtener las columnas, lanza una excepción.
     *
     * @example
     * $columnas = asigna_columnas_session_new($modelo, "mi_tabla");
     *
     * La función primero verifica que la tabla no está vacía y no es numérica.
     * Luego, intenta generar las columnas para el modelo llamando a la función `genera_columnas_field`.
     * Si hay un error al generar las columnas, lanza una excepción.
     * A continuación, asigna las columnas generadas a las sesiones y al modelo.
     *
     * @see genera_columnas_field()
     *
     * @version 18.44.0
     */
    private function asigna_columnas_session_new(modelo_base $modelo, string $tabla_bd): array|stdClass
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd === ''){
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia',data:  $tabla_bd, es_final: true);
        }
        if(is_numeric($tabla_bd)){
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero',data:  $tabla_bd,
                es_final: true);
        }

        $columnas_field = $this->genera_columnas_field(modelo:$modelo, tabla_bd: $tabla_bd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas_field);
        }
        $_SESSION['campos_tabla'][$tabla_bd] = $columnas_field->columnas_parseadas;
        $_SESSION['columnas_completas'][$tabla_bd] = $columnas_field->columnas_completas;

        $modelo->data_columnas = $columnas_field;
        return $modelo->data_columnas;
    }

    /**
     * TOTAL
     * Asigna los datos de las columnas de una tabla especificada a la variable de sesión correspondiente y las devuelve.
     *
     * La función se encuentra en el archivo 'base/orm/columnas.php'. El objetivo de esta función es asignar la data
     * de las columnas para una tabla específica almacenada en la variable de sesión a la variable de data pasada como
     * parámetro y retornarla.
     *
     * @param stdClass $data El objeto al que se le asignarán las columnas parseadas y completas.
     * @param string $tabla_bd Nombre de la tabla en la base de datos.
     *
     * @return stdClass|array Devuelve el objeto $data modificado en caso de éxito, de otro modo,
     * devuelve un arreglo con información de error.
     * @version 13.14.0
     * @version https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.columnas.asigna_data_columnas
     */
    private function asigna_data_columnas(stdClass $data, string $tabla_bd): stdClass|array
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd===''){
            return $this->error->error(mensaje: 'Error tabla_bd no puede venir vacia', data: $tabla_bd,
                es_final: true);
        }
        if(!isset($_SESSION['campos_tabla'])){
            return $this->error->error(mensaje: 'Error debe existir SESSION[campos_tabla]',data: $_SESSION,
                es_final: true);
        }
        if(!isset($_SESSION['campos_tabla'][$tabla_bd])){
            return $this->error->error(mensaje: 'Error debe existir SESSION[campos_tabla]['.$tabla_bd.']',
                data: $_SESSION, es_final: true);
        }
        if(!isset($_SESSION['columnas_completas'])){
            return $this->error->error(mensaje: 'Error debe existir SESSION[columnas_completas]',data: $_SESSION,
                es_final: true);
        }
        if(!isset($_SESSION['columnas_completas'][$tabla_bd])){
            return $this->error->error(mensaje: 'Error debe existir SESSION[columnas_completas]['.$tabla_bd.']',
                data:$_SESSION, es_final: true);
        }

        $data->columnas_parseadas = $_SESSION['campos_tabla'][$tabla_bd];
        $data->columnas_completas = $_SESSION['columnas_completas'][$tabla_bd];

        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Este método recibe dos arrays $campos_no_upd y $registro y devuelve un array $registro después de eliminar
     * elementos que existen en ambos arrays.
     * El propósito principal de este método es filtrar ciertos campos de un registro que no se deben actualizar.
     *
     * @param array $campos_no_upd Array que contiene los nombres de los campos que no deben actualizarse.
     * @param array $registro Array que contiene el registro original (por ejemplo, una fila de la base de datos).
     *
     * @return array $registro Devuelve el array original $registro después de eliminar aquellos elementos cuyos
     * nombres de campo estaban presentes en el array $campos_no_upd.
     *
     * @throws errores
     *       Se arroja una excepción con un mensaje de error si el $campo_no_upd está vacío o si es numérico.
     * @version 16.119.0
     */
    final public function campos_no_upd(array $campos_no_upd, array $registro): array
    {
        foreach ($campos_no_upd as $campo_no_upd){
            $campo_no_upd = trim($campo_no_upd);
            if($campo_no_upd === ''){
                $fix = 'Se tiene que mandar un campo del modelo indicado';
                $fix .= ' $campo_no_upd[] debe ser un campo ejemplo $campo_no_upd[] = status';
                return $this->error->error(mensaje: 'Error $campo_no_upd esta vacio', data: $campo_no_upd, fix: $fix);
            }
            if(is_numeric($campo_no_upd)){
                $fix = 'Se tiene que mandar un campo del modelo indicado';
                $fix .= ' $campo_no_upd[] debe ser un campo ejemplo $campo_no_upd[] = status';
                return $this->error->error(mensaje: 'Error $campo_no_upd debe ser un texto', data: $campo_no_upd, fix: $fix);
            }
            if(array_key_exists($campo_no_upd, $registro)){
                unset($registro[$campo_no_upd]);
            }
        }
        return $registro;
    }

    /**
     * Esta función obtiene las columnas (campos) de una tabla dada y las asigna a una instancia de un modelo dado.
     *
     * @param modelo $modelo Es la instancia del modelo a la que se le asignarán los campos de la tabla.
     * @param string $tabla Es el nombre de la tabla de la que se extraerán los campos
     *
     * @return array Retorna un array de campos (columnas) de la tabla asignados al modelo.
     * En caso de error, devuelve un mensaje de error.
     */
    final public function campos_tabla(modelo $modelo, string $tabla): array
    {
        if($tabla !=='') {

            $data = $this->obten_columnas(modelo:$modelo, tabla_original: $tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener columnas de '.$tabla, data: $data);
            }
            $modelo->campos_tabla = $data->columnas_parseadas;
        }
        return $modelo->campos_tabla;
    }


    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * La función responsable para cargar y renombrar las columnas en una tabla.
     *
     * @param string $columnas Cadena que representa las columnas en la tabla.
     * @param array $columnas_sql Array que contiene la información / esquema de las columnas SQL.
     * @param array $data Array de los datos a validar e integrar.
     * @param modelo_base $modelo Un objeto de la clase modelo_base que maneja la interacción con la base de datos.
     * @param string $tabla Nombre de la tabla que se está procesando.
     *
     * @return array|string Devuelve un array con los datos de las columnas ajustadas.
     *                      En caso de error, devuelve una cadena con el mensaje de error.
     *
     * @throws errores Si hay un error durante la validación de los datos o la integración de las columnas, se lanza una excepción.
     *
     * @example cargo_columna_renombre('$columnas', ['$columna1', 'columna2'], ['$data1', '$data2'], $modelo, 'mi_tabla')
     * @version 16.3.0
     */
    private function carga_columna_renombre(string $columnas, array $columnas_sql, array $data, modelo_base $modelo,
                                            string $tabla): array|string
    {

        $valida = $this->validacion->valida_data_columna(data: $data,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }


        $r_columnas = $this->ajusta_columnas_completas(columnas: $columnas, columnas_en_bruto: false,
            columnas_sql: $columnas_sql,  modelo: $modelo, tabla: $data['nombre_original'],
            tabla_renombrada: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $r_columnas);
        }

        return (string)$r_columnas;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función para manejar columnas en el modelo de base.
     *
     * Esta función procesa las columnas pasadas y las configura apropiadamente basándose en la condición de $aplica_columnas_by_table.
     * Si $aplica_columnas_by_table es falsa, entonces las columnas se formatean usando el método 'columnas_base'.
     * En caso contrario, las columnas se formatean utilizando el método 'columnas_by_table'.
     *
     * @param bool $aplica_columnas_by_table Booleano para decidir el formato de las columnas.
     * @param array $columnas_by_table Conjunto de columnas. Si $aplica_columnas_by_table es verdadero, debería contener datos.
     * @param bool $columnas_en_bruto Booleano que indica si las columnas están en formato bruto.
     * @param array $columnas_sql Array de columnas SQL.
     * @param array $extension_estructura Estructura de extensión para las columnas.
     * @param array $extra_join Información adicional para el JOIN en SQL.
     * @param modelo_base $modelo La instancia del modelo base para maniobrar los datos.
     * @param array $renombres Array de renombres para las tablas.
     * @param array $tablas_select Array de tablas seleccionadas.
     *
     * @return array|string Retorna un array o string. Si hay error en el formato de columnas, retorna una cadena de error.
     * @version 16.18.0
     */
    private function columnas(bool $aplica_columnas_by_table, array $columnas_by_table, bool $columnas_en_bruto,
                              array $columnas_sql, array $extension_estructura, array $extra_join, modelo_base $modelo,
                              array $renombres, array $tablas_select): array|string
    {
        if(!$aplica_columnas_by_table) {

            if(count($columnas_by_table) > 0){
                $fix = 'Si !$aplica_columnas_by_table $columnas_by_table debe ser vacio';
                return $this->error->error(mensaje: 'Error columnas_by_table tiene datos en modelo '.$modelo->tabla,
                    data: $columnas_by_table, es_final: true, fix: $fix);
            }

            $columnas = $this->columnas_base(columnas_en_bruto: $columnas_en_bruto, columnas_sql: $columnas_sql,
                extension_estructura: $extension_estructura, extra_join: $extra_join, modelo: $modelo,
                renombres: $renombres, tablas_select: $tablas_select);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas base en '.$modelo->tabla,
                    data: $columnas);
            }

        }
        else{
            if(count($columnas_by_table) === 0){
                $fix = 'Si $aplica_columnas_by_table es true debe haber columnas_by_table con datos';
                $fix .= ' columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = ';
                $fix.= "nombre_tabla";
                return $this->error->error(mensaje: 'Error columnas_by_table esta vacia en '.$modelo->tabla,
                    data: $columnas_by_table, es_final: true, fix: $fix);
            }
            $columnas = $this->columnas_by_table(columnas_by_table: $columnas_by_table,
                columnas_en_bruto: $columnas_en_bruto, modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas by table en '.$modelo->tabla,
                    data: $columnas);
            }
        }

        $columnas = trim($columnas);
        if($columnas === ''){
            $columnas = "$modelo->key_filtro_id as $modelo->key_id";
        }

        return $columnas;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Procesa los detalles de una columna y prepara una lista completa de columnas analizadas y no analizadas.
     *
     * Esta función analiza en profundidad los detalles de una columna proporcionada y los prepara para ser utilizados
     * en consultas de base de datos. Espera un arreglo de columnas y dos arreglos para las columnas completas
     * y las columnas parseadas respectivamente.
     * Durante el procesamiento, esta función puede devolver un mensaje de error si encuentra algún problema.
     *
     * @param array $columna Arreglo con detalles de una columna. Debe tener una estructura clave/valor.
     * @param array $columnas_completas Arreglo con las columnas completas.
     * @param array $columnas_parseadas Arreglo para almacenar columnas que han sido analizadas.
     *
     * @return array|stdClass Un objeto que contiene las columnas parseadas y las columnas completas.
     * @version 15.43.1
     */
    private function columnas_attr(array $columna, array $columnas_completas, array $columnas_parseadas): array|stdClass
    {
        foreach($columna as $campo=>$atributo){
            $columnas_field = $this->columnas_field(atributo: $atributo, campo: $campo, columna: $columna,
                columnas_completas: $columnas_completas, columnas_parseadas:  $columnas_parseadas);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas_field);
            }
            $columnas_parseadas = $columnas_field->columnas_parseadas;
            $columnas_completas = $columnas_field->columnas_completas;
        }

        $data = new stdClass();
        $data->columnas_parseadas = $columnas_parseadas;
        $data->columnas_completas = $columnas_completas;
        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * La función 'columnas_base' es una función privada que se utiliza para integrar columnas de varias partes del modelo de datos.
     *
     * @param bool $columnas_en_bruto Es una variable booleana que determina si se usarán o no las columnas en bruto.
     * @param array $columnas_sql Es una matriz de columnas SQL que deben integrarse en el modelo de datos.
     * @param array $extension_estructura Es una matriz que contiene la estructura de extensión que se utilizará para el modelo de datos.
     * @param array $extra_join  Es una matriz de joins extra que debe aplicarse al modelo de datos.
     * @param modelo_base $modelo Es el modelo base que se utilizará para crear el modelo de datos.
     * @param array $renombres Es una matriz de columnas que deben renombrarse en el modelo de datos.
     * @param array $tablas_select Es una matriz de tablas seleccionadas que deben incluirse en el modelo de datos.
     *
     * @return array|string Devuelve un array de columnas si la operación fue exitosa, en caso de error devuelve un mensaje de error.
     *
     * @throw errores Puede arrojar excepciones si ocurre algún error durante la integración de columnas.
     * @version 16.6.0
     */
    private function columnas_base(bool $columnas_en_bruto, array $columnas_sql, array $extension_estructura,
                                   array $extra_join, modelo_base $modelo, array $renombres,
                                   array $tablas_select): array|string
    {
        $columnas = $this->columnas_tablas_select(columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $columnas_sql,  modelo: $modelo, tablas_select: $tablas_select);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }

        $columnas = $this->columnas_extension(columnas: $columnas, columnas_sql: $columnas_sql,
            extension_estructura: $extension_estructura, modelo: $modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }

        $columnas = $this->columnas_extra(columnas: $columnas, columnas_sql: $columnas_sql, extra_join: $extra_join,
            modelo: $modelo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }

        $columnas = $this->columnas_renombre(columnas: $columnas, columnas_sql: $columnas_sql, modelo: $modelo,
            renombres: $renombres);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }


        return $columnas;
    }

    /**
     * TOTAL
     * Esta función, columnas_bd_native, se utiliza para obtener detalles de las columnas de una tabla de base de
     * datos a partir del nombre de la tabla proporcionado como argumento.
     *
     * @param modelo_base $modelo Una instancia del modelo base, que se usa para ejecutar consultas de base de datos.
     * @param string $tabla_bd El nombre de la tabla de base de datos de la que se obtienen los detalles de las columnas.
     *
     * @return array Regresa una matriz de registros que representa los detalles de las columnas de la tabla de base
     * de datos especificada.
     *
     * @throws errores Si el nombre de la tabla está vacío, se lanza un error.
     * @throws errores Si el nombre de la tabla es numérico, se lanza un error.
     * @throws errores Si ocurre un error al obtener la consulta SQL para describir la tabla, se lanza un error.
     * @throws errores Si ocurre un error al ejecutar la consulta SQL, se lanza un error.
     * @throws errores Si la tabla de base de datos especificada no tiene columnas, se lanza un error.
     * @version 18.32.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.columnas.columnas_bd_native
     */
    final public function columnas_bd_native(modelo_base $modelo, string $tabla_bd): array
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd === ''){
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia',data:  $tabla_bd,es_final: true);
        }
        if(is_numeric($tabla_bd)){
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero',data:  $tabla_bd,
                es_final: true);
        }

        $sql = (new sql())->describe_table(tabla: $tabla_bd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sql', data: $sql);
        }

        $result = $modelo->ejecuta_consulta(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }
        if((int)$result->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existen columnas', data: $result, es_final: true);
        }

        return $result->registros;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Obtiene las columnas en sql de una entidad con sus relaciones
     * @param array $columnas_by_table Array de cadenas con los nombres de las tablas desde las cuales se desean extraer las columnas.
     * @param bool $columnas_en_bruto Dependiendo el valor booleano, se obtienen las columnas en bruto o no.
     * @param modelo_base $modelo Modelo base desde el cual se obtendrán las columnas.
     * @return array|string Dependiendo del proceso, retorna un array con las columnas de salida o un string con un mensaje de error.
     * @version 16.17.0
     */
    private function columnas_by_table(array $columnas_by_table, bool $columnas_en_bruto,
                                       modelo_base $modelo): array|string
    {
        if(count($columnas_by_table) === 0){
            $fix = 'columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = "nombre_tabla"';
            return $this->error->error(mensaje: 'Error debe columnas_by_table esta vacia', data: $columnas_by_table,
                es_final: true, fix: $fix);
        }

        $init = $this->init_columnas_by_table(columnas_by_table: $columnas_by_table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al inicializa datos de columnas by table', data: $init);
        }

        $columnas = $this->columnas_tablas_select(columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $init->columnas_sql,  modelo: $modelo, tablas_select: $init->tablas_select);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }
        return $columnas;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función maneja la combinación de columnas para una consulta SQL basada en las entradas $columnas_extra_sql y $columnas_sql.
     *
     * @param string $columnas_extra_sql Las columnas SQL extra. Pueden contener nombres de columnas adicionales para la consulta SQL.
     * @param string $columnas_sql Las columnas SQL principales. Contienen los nombres de las columnas principales para la consulta.
     *
     * @return string Devuelve un string que contiene las columnas para la consulta SQL. Si $columnas_sql está vacío,
     *                se devuelve $columnas_extra_sql. Si ambas no están vacías, se devolverá una cadena que contiene ambas,
     *                estos estarán separados por una coma.
     *
     * @example
     * // Ejemplo de uso:
     * $columnas_sql = 'id, nombre';
     * $columnas_extra_sql = 'direccion, telefono';
     * $resultado = columnas_envio($columnas_extra_sql, $columnas_sql);
     * // $resultado ahora contiene 'id, nombre, direccion, telefono'
     * @version 15.69.1
     */
    private function columnas_envio(string $columnas_extra_sql, string $columnas_sql): string
    {
        if(trim($columnas_sql) === '' &&  trim($columnas_extra_sql) !==''){
            $columnas_envio = $columnas_extra_sql;
        }
        else{
            $columnas_envio = $columnas_sql;
            if($columnas_extra_sql!==''){
                $columnas_envio.=','.$columnas_extra_sql;
            }
        }
        return $columnas_envio;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Ajusta y extiende las columnas pasadas según la estructura proveída.
     *
     * Esta función recibe los nombres de las columnas, una arreglo asocativo con la estructura de
     * columnas SQL, una estrucura extra para las columnas y un modelo donde se realizarán los cambios.
     * La función ajusta las columnas segun la estructura pasada y las reglas internas.
     *
     * @param string $columnas Nombres de las columnas que serán extendidas.
     * @param array $columnas_sql Proporciona la estructura de las columnas SQL.
     * @param array $extension_estructura Estructura extra a agregar a las columnas.
     * @param modelo_base $modelo Modelo donde se aplicarán las extensiones de columnas.
     *
     * @throws errores Cuando la estructura pasada es inválida.
     *
     * @return string|array Retorna las columnas extendidas ajustadas o un mensaje de error.
     *
     * @example
     * $columnas = 'nombre,apellido';
     * $columnas_sql = ['nombre' => 'VARCHAR', 'apellido' => 'VARCHAR'];
     * $extension_estructura = ['nombre' => ['extension' => 'sortable']];
     * $modelo = new modelo_base();
     * $columnas_extension = columnas_extension($columnas, $columnas_sql, $extension_estructura, $modelo);
     * @version 15.80.1
     *
     */
    private function columnas_extension(string $columnas, array $columnas_sql, array $extension_estructura,
                                        modelo_base $modelo): array|string
    {
        $columnas_env = $columnas;
        foreach($extension_estructura as $tabla=>$data){
            $tabla = str_replace('models\\','',$tabla);
            if(is_numeric($tabla)){
                return $this->error->error(mensaje: 'Error ingrese un array valido '.$tabla,
                    data: $extension_estructura, es_final: true);
            }

            $columnas_env = $this->ajusta_columnas_completas(columnas: $columnas_env, columnas_en_bruto: false,
                columnas_sql: $columnas_sql,  modelo: $modelo, tabla: $tabla, tabla_renombrada: '');
            if(errores::$error){
                return $this->error->error(mensaje:'Error al integrar envio', data:$columnas_env);
            }

        }
        return $columnas_env;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función columnas_extra
     *
     * Esta función se utiliza para procesar las columnas extra en las consultas SQL.
     *
     * @param string $columnas El nombre de las columnas a procesar.
     * @param array $columnas_sql El array de columnas SQL.
     * @param array $extra_join El array de tablas extra para hacer un JOIN.
     * @param modelo_base $modelo El modelo base que se utilizará para el procesamiento.
     *
     * @return array|string Devuelve las columnas procesadas si no hubo errores. En caso de errores, devuelve un mensaje de error.
     * @version 15.83.1
     */
    PUBLIC function columnas_extra(string $columnas, array $columnas_sql,  array $extra_join,
                                    modelo_base $modelo): array|string
    {
        $columnas_env = $columnas;
        foreach($extra_join as $tabla=>$data){
            $tabla = str_replace('models\\','',$tabla);

            if(is_numeric($tabla)){
                return $this->error->error(mensaje: 'Error ingrese un array valido '.$tabla,
                    data: $extra_join, es_final: true);
            }
            if(!is_array($data)){
                return $this->error->error(mensaje: 'Error data debe ser un array ',
                    data: $data, es_final: true);
            }

            $tabla_renombrada = $this->tabla_renombrada_extra(data: $data,tabla:  $tabla);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al integrar tabla_renombrada', data:$tabla_renombrada);
            }

            $columnas_env = $this->ajusta_columnas_completas(columnas: $columnas_env, columnas_en_bruto: false,
                columnas_sql: $columnas_sql,  modelo: $modelo, tabla: $tabla, tabla_renombrada: $tabla_renombrada);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al integrar columnas', data:$columnas_env);
            }

        }
        return $columnas_env;
    }


    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función 'columnas_field' recibe cinco parámetros: $atributo, $campo, $columna, $columnas_completas y
     * $columnas_parseadas.
     *
     * @param string|null $atributo - El primer parámetro que es la entidad de la cual se intenta obtener información.
     * puede ser null.
     * @param string $campo - El nombre del campo de la entidad que se está procesando.
     * @param array $columna - La columna de la entidad.
     * @param array $columnas_completas - Una lista que contiene todas las columnas completas que deben ser procesadas.
     * @param array $columnas_parseadas - Una lista de columnas que ya han sido procesadas.
     *
     * @return array|stdClass - Si el proceso es exitoso, este método retorna un objeto con atributos
     * 'columnas_parseadas' y 'columnas_completas'.
     * Si ocurre un error durante el procesamiento de 'columnas_parseadas' o 'columnas_completas',
     * este método retornará una descripción del error a través del método $this->error->error().
     *
     * @throws errores - Lanza una excepción si ocurre un error durante el procesamiento
     * @version
     */
    private function columnas_field(string|null $atributo, string $campo, array $columna, array $columnas_completas,
                                    array $columnas_parseadas): array|stdClass
    {
        if($campo === 'Field'){
            $columnas_parseadas = $this->asigna_columnas_parseadas( atributo: $atributo,
                columnas_parseadas: $columnas_parseadas);
            if(errores::$error){

                return $this->error->error(mensaje: 'Error al obtener columnas parseadas', data: $columnas_parseadas);
            }

            $columnas_completas = $this->asigna_columna_completa(atributo: $atributo,columna:
                $columna,columnas_completas:  $columnas_completas);
            if(errores::$error){

                return $this->error->error(mensaje: 'Error al obtener columnas completas', data: $columnas_completas);
            }
        }

        $data = new stdClass();
        $data->columnas_parseadas = $columnas_parseadas;
        $data->columnas_completas = $columnas_completas;
        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Obtiene las columnas para un SELECT
     * @param array $columnas_by_table Obtiene solo las columnas de la tabla en ejecucion
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param array $extension_estructura Datos para la extension de una estructura que va fuera de la
     * logica natural de dependencias
     * @param array $extra_join integra joins extra a peticion de funcion no usar en modelo
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @param array $renombres Conjunto de tablas para renombrar
     * @param array $tablas_select Tablas ligadas al modelo en ejecucion
     * @return array|string
     * @version 16.19.0
     */
    private function columnas_full(array $columnas_by_table, bool $columnas_en_bruto, array $columnas_sql,
                                   array $extension_estructura, array $extra_join, modelo_base $modelo,
                                   array $renombres, array $tablas_select): array|string
    {

        $aplica_columnas_by_table = $this->aplica_columnas_by_table(columnas_by_table: $columnas_by_table);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al verificar aplicacion de columnas en modelo '.$modelo->tabla,
                data: $aplica_columnas_by_table);
        }

        $columnas = $this->columnas(aplica_columnas_by_table: $aplica_columnas_by_table,
            columnas_by_table: $columnas_by_table, columnas_en_bruto: $columnas_en_bruto, columnas_sql: $columnas_sql,
            extension_estructura: $extension_estructura, extra_join: $extra_join, modelo: $modelo,
            renombres: $renombres, tablas_select: $tablas_select);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columnas en modelo '.$modelo->tabla,
                data: $columnas);
        }


        return $columnas;


    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Renombra las columnas de la base de datos para su manipulación dentro del código.
     *
     * @param string      $columnas     Las columnas a las que se les aplicará el cambio de nombre.
     * @param array       $columnas_sql Las columnas obtenidas de la consulta SQL.
     * @param modelo_base $modelo       El modelo base del que se obtendrán las columnas.
     * @param array       $renombres    Array asociativo con los nombres de las columnas y los nombres deseados.
     *
     * @return array|string Regresa las columnas con los nombres modificados en formato de string o array según sea el caso.
     * @version 16.4.0
     */
    private function columnas_renombre(string $columnas, array $columnas_sql, modelo_base $modelo,
                                       array $renombres): array|string
    {
        foreach($renombres as $tabla=>$data){
            if(!is_array($data)){
                return $this->error->error(mensaje: 'Error data debe ser array '.$tabla,data:  $data, es_final: true);
            }
            $r_columnas = $this->carga_columna_renombre(columnas: $columnas, columnas_sql: $columnas_sql,
                data: $data, modelo: $modelo, tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar columnas', data: $r_columnas);
            }
            $columnas = (string)$r_columnas;
        }

        return $columnas;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método que genera una cadena SQL para columnas.
     *
     * Este método genera una cadena SQL para columnas dependiendo de varios parámetros de entrada proporcionados.
     * La cadena generada es útil para consultas SQL donde se pueden necesitar alias para las columnas nombradas en SQL.
     *
     * @example
     * Supongamos que tienes una tabla "usuarios" con la columna "nombre"
     * Usar este método así: columnas_sql('nombre_completo', 'nombre', false, '', 'usuarios')
     * Devolverá: "usuarios.nombre AS nombre_completo"
     *
     * @param string $alias_columnas Alias para la columna parseada que se incluirá en la consulta SQL final.
     * @param string $columna_parseada Nombre de la columna extraído que se considerará para generar la consulta SQL.
     * @param bool $columnas_en_bruto Si se establece como verdadero, el alias de la columna será igual a la columna parseada.
     * @param string $columnas_sql Cadena de columnas SQL existente a la que se añadirá el nuevo segmento de columna.
     * @param string $tabla_nombre Nombre de la tabla en la que se encuentra la columna.
     *
     * @return array|string Devuelve la cadena SQL generada con las columnas o un array con información de cualquier error que pueda haber ocurrido.
     * @version 15.61.1
     */
    private function columnas_sql(string $alias_columnas, string $columna_parseada, bool $columnas_en_bruto,
                                  string $columnas_sql, string $tabla_nombre):array|string{


        $valida = $this->valida_columnas_sql(alias_columnas: $alias_columnas,columna_parseada:  $columna_parseada,
            tabla_nombre:  $tabla_nombre);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar datos de entrada',data: $valida);
        }
        $coma = $this->coma(columnas_sql: $columnas_sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al integrar coma',data: $coma);
        }

        if($columnas_en_bruto){
            $alias_columnas = $columna_parseada;
        }

        $columnas_sql.= $coma.$tabla_nombre.'.'.$columna_parseada.' AS '.$alias_columnas;

        return $columnas_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método privado columnas_sql_array realiza el parseo de las columnas proporcionadas.
     *
     * @param array $columnas Las columnas que se van a parsear.
     *
     * @return array|stdClass Retorna un objeto stdClass si se produce un error durante el procesamiento de las columnas.
     *                         En este caso, el objeto contiene información de error.
     *                         Si el procesamiento es exitoso, se retorna un array asociativo con las columnas parseadas y completas.
     *                         El array tiene los siguientes elementos:
     *                           - 'columnas_parseadas': Un array con las columnas parseadas.
     *                           - 'columnas_completas': Un array con todas las columnas procesadas.
     *
     * @throws errores Lanza un error si $columna no es un array o si hay un error al obtener las columnas.
     * @version 15.44.1
     */
    private function columnas_sql_array(array $columnas): array|stdClass
    {
        $columnas_parseadas = array();
        $columnas_completas = array();
        foreach($columnas as $columna ){
            if(!is_array($columna)){
                return $this->error->error(mensaje: 'Error $columna debe ser un array', data: $columnas,
                    es_final: true);
            }
            $columnas_field = $this->columnas_attr(columna: $columna, columnas_completas:  $columnas_completas,
                columnas_parseadas:  $columnas_parseadas);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas_field);
            }
            $columnas_parseadas = $columnas_field->columnas_parseadas;
            $columnas_completas = $columnas_field->columnas_completas;
        }

        $data = new stdClass();
        $data->columnas_parseadas = $columnas_parseadas;
        $data->columnas_completas = $columnas_completas;
        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método columnas_sql_init
     *
     * Inicializa las columnas de SQL de acuerdo a los parámetros suministrados.
     *
     * @param array  $columnas           Las columnas a procesar
     * @param bool   $columnas_en_bruto  Indica si las columnas se encuentran en bruto
     * @param array  $columnas_parseadas Las columnas que ya han sido parseadas
     * @param string $tabla_nombre       El nombre de la tabla
     *
     * @return array|string  Las columnas de SQL inicializadas, o un mensaje de error.
     *
     * @throws errores Si $?tabla_nombre? está vacío.
     * @throws errores Si ocurre un error al obtener las columnas de SQL.
     *
     * @example columnas_sql_init(['id', 'nombre'], false, ['id', 'nombre'], 'mi_tabla') Inicializa columnas de SQL
     *
     * @internal Este método es privado y sólo debe ser utilizado por la clase contenedora.
     * @version 15.62.1
     */
    private function columnas_sql_init(array $columnas, bool $columnas_en_bruto, array $columnas_parseadas,
                                       string $tabla_nombre):array|string{
        if($tabla_nombre === ''){
            return $this->error->error(mensaje: 'Error $tabla_nombre no puede venir vacia',data:  $tabla_nombre,
                es_final: true);
        }
        $columnas_sql = '';
        foreach($columnas_parseadas as $columna_parseada){
            $alias_columnas = $tabla_nombre.'_'.$columna_parseada;
            if((count($columnas) > 0) && !in_array($alias_columnas, $columnas, true)) {
                continue;
            }
            $columnas_sql = $this->columnas_sql(alias_columnas: $alias_columnas, columna_parseada: $columna_parseada,
                columnas_en_bruto: $columnas_en_bruto, columnas_sql: $columnas_sql, tabla_nombre: $tabla_nombre);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener columnas sql',data:  $columnas_sql);
            }
        }


        return $columnas_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * La función columnas_tablas_select elabora las columnas que se seleccionarán en una consulta SQL.
     *
     * @param bool $columnas_en_bruto Indica si las columnas se proporcionan en bruto. Si es true, la función establece
     *                                $tablas_select para ser un arreglo vacío y $modelo->tabla como valor.
     *
     * @param array $columnas_sql Representa las columnas SQL que se usarán en la consulta.
     *
     * @param modelo_base $modelo Instancia del modelo base utilizado para configurar la tabla.
     *                            Se utiliza el valor de $modelo->tabla.
     *
     * @param array $tablas_select Define las tablas que se seleccionarán en la consulta.
     *
     * @return array|string Si hay algún error durante la ejecución, retorna un mensaje de error con los detalles.
     *                      Si todo va bien, retorna las columnas formuladas como una cadena.
     *
     * @throws errores Si $key es un número, se lanza una excepción con un mensaje de error.
     *                   Si hay un error al integrar las columnas, se lanza una excepción con un mensaje de error.
     *
     * La función recorre cada elemento en $tablas_select, por cada tabla llama a la función genera_columna_tabla con
     * los parámetros necesarios. Si encuentra algún error, retorna el mensaje de error con los detalles.
     * Si todo va bien, actualiza el valor de $columnas y continúa hasta que no queden más elementos en $tablas_select.
     * Finalmente, retorna $columnas que ahora son la consulta SQL finalizada.
     * @version 15.79.1
     */
    private function columnas_tablas_select(bool $columnas_en_bruto, array $columnas_sql,  modelo_base $modelo,
                                            array $tablas_select): array|string
    {
        if($columnas_en_bruto){
            $tablas_select = array();
            $tablas_select[$modelo->tabla] = $modelo->tabla;
        }

        $columnas = '';

        foreach ($tablas_select as $key=>$tabla_select){

            if(is_numeric($key)){
                return $this->error->error(mensaje: 'Error $key no puede ser un numero',data:  $key, es_final: true);
            }

            $result = $this->genera_columna_tabla(columnas: $columnas, columnas_en_bruto: $columnas_en_bruto,
                columnas_sql: $columnas_sql, key: $key, modelo: $modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar columnas',data:  $result);
            }
            $columnas = (string)$result;
        }
        return $columnas;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función maneja la adición de una coma en una consulta SQL.
     *
     * El propósito de esta función es agregar una coma al final de una lista de columnas SQL.
     * Solo se agrega la coma si el parámetro de entrada no es un string vacío.
     *
     * @param string $columnas_sql El string que contiene las columnas SQL que ya se han construido en la consulta.
     *                             Este parámetro puede ser un string vacío, en cuyo caso la función no hará nada.
     *
     * @return string Retorna una coma como un string. Si $columnas_sql es un string vacío, se retorna un string vacío.
     * @version 15.60.1
     */
    private function coma(string $columnas_sql): string
    {
        $columnas_sql = trim($columnas_sql);
        $coma = '';
        if($columnas_sql !== ''){
            $coma = ', ';
        }
        return $coma;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Este método privado prepara los datos para las columnas de envío.
     *
     * @param array $columnas Esto es una matriz de columnas.
     * @param bool $columnas_en_bruto Esta es una indicación de si las columnas están en bruto.
     * @param modelo_base $modelo Este es el modelo base utilizado.
     * @param string $tabla_original Este es el nombre original de la tabla.
     * @param string $tabla_renombrada Este es el nombre nuevo de la tabla después de ser renombrada.
     *
     * @return array|stdClass
     *     Retorna un array o un objeto con dos propiedades: 'columnas_sql' y 'columnas_extra_sql'
     *     - 'columnas_sql' contiene la consulta de SQL de las columnas.
     *     - 'columnas_extra_sql' almacena cualquier consulta SQL adicional.
     *     En caso de error, se devolverá un objeto de error con información sobre el mismo.
     * @version 15.67.1
     */
    private function data_for_columnas_envio(array $columnas, bool $columnas_en_bruto, modelo_base $modelo,
                                             string $tabla_original, string $tabla_renombrada): array|stdClass
    {
        $tabla_original = str_replace('models\\','',$tabla_original);

        if($tabla_original === ''){
            return  $this->error->error(mensaje: 'Error tabla original no puede venir vacia',data: $tabla_original,
                es_final: true);
        }
        if(is_numeric($tabla_original)){
            return $this->error->error(mensaje: 'Error $tabla_original no puede ser un numero',data:  $tabla_original,
                es_final: true);
        }

        $columnas_sql = $this->genera_columnas_tabla( columnas_en_bruto: $columnas_en_bruto, modelo: $modelo,
            tabla_original: $tabla_original, tabla_renombrada: $tabla_renombrada, columnas:  $columnas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnas',data:  $columnas_sql);

        }

        $columnas_extra_sql = '';

        $data = new stdClass();
        $data->columnas_sql = $columnas_sql;
        $data->columnas_extra_sql = $columnas_extra_sql;
        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Este método genera una nueva columna para la tabla.
     *
     * @param string $columnas Cadena de caracteres con los nombres de las columnas.
     * @param bool $columnas_en_bruto Indica si las columnas están en bruto (true) o no (false).
     * @param array $columnas_sql Array con las columnas SQL.
     * @param string $key Clave única para la columna.
     * @param modelo_base $modelo Modelo base para generar la columna.
     *
     * @return array|string Si ocurre un error, se devuelve un array con información sobre el error. De lo contrario,
     * se devuelve una cadena de caracteres con la columna generada.
     *
     * @throws errores Si la clave es un número, se lanza un error.
     * @version 15.77.1
     **/
    private function genera_columna_tabla(string $columnas, bool $columnas_en_bruto, array $columnas_sql,
                                          string $key, modelo_base $modelo): array|string
    {
        $key = str_replace('models\\','',$key);
        if(is_numeric($key)){
            return $this->error->error(mensaje: 'Error $key no puede ser un numero',data:  $key, es_final: true);
        }

        $result = $this->ajusta_columnas_completas(columnas: $columnas, columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $columnas_sql,  modelo: $modelo, tabla: $key, tabla_renombrada: '');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $result);
        }
        return (string)$result;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función genera las columnas para una consulta SQL de acuerdo a los parámetros de entrada.
     *
     * @param bool $columnas_en_bruto Determina si las columnas se pasarán en bruto.
     * @param modelo_base $modelo El modelo base que se utilizará para la consulta.
     * @param string $tabla_original El nombre original de la tabla en la que se llevará a cabo la consulta.
     * @param string $tabla_renombrada El nombre nuevo de la tabla en caso de que haya sido renombrada.
     * @param array $columnas Un array con los nombres de las columnas para la consulta.
     *
     * @return array|string Devuelve un array con las columnas para llevar a cabo la consulta o una cadena en caso de error.
     * @version 15.70.1
     */
    private function genera_columnas_consulta(bool $columnas_en_bruto,  modelo_base $modelo, string $tabla_original,
                                              string $tabla_renombrada, array $columnas = array()):array|string{
        $tabla_original = str_replace('models\\','',$tabla_original);

        if(is_numeric($tabla_original)){
            return $this->error->error(mensaje: 'Error $tabla_original no puede ser un numero',data:  $tabla_original,
                es_final: true);
        }

        $data = $this->data_for_columnas_envio(columnas: $columnas, columnas_en_bruto: $columnas_en_bruto,
            modelo: $modelo, tabla_original: $tabla_original, tabla_renombrada: $tabla_renombrada);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al datos para columnas', data: $data);
        }

        $columnas_envio = $this->columnas_envio(columnas_extra_sql: $data->columnas_extra_sql,
            columnas_sql: $data->columnas_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnas', data: $columnas_envio);
        }

        return $columnas_envio;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función es responsable de generar columnas adicionales para una consulta SQL en base a un conjunto de
     * columnas proporcionadas y un modelo específico.
     *
     * @param array $columnas Un array de columnas para las que se generarán columnas adicionales.
     * @param modelo_base $modelo Una instancia del modelo base que se usa para la generación de las columnas adicionales.
     * @return array|string Retorna una cadena SQL de columnas generadas, o un array de mensaje de error y datos
     * correlacionados en caso de error.
     *
     * @throws errores Esta función puede lanzar una excepción si alguna de las condiciones para el nombre de las
     * subqueries no es cumplida:
     * - Si el nombre de la subquery es numérico.
     * - Si el nombre de la subquery está vacío.
     * - Si la sql de la subquery está vacía.
     *
     * @example
     * $model = new modelo_base();
     * $columnas = ['nombre', 'apellido'];
     * echo genera_columnas_extra($columnas, $model);
     *
     * Este código imprimirá una cadena de SQL que contiene las columnas adicionales construidas a partir de las
     * columnas proporcionadas y el modelo dado,
     * o imprimirá un mensaje de error con los datos relacionados, si alguna de las condiciones no se cumple.
     * @version
     */
    final public function genera_columnas_extra(array $columnas, modelo_base $modelo):array|string{//FIN
        $columnas_sql = '';
        $columnas_extra = $modelo->columnas_extra;
        foreach ($columnas_extra as $sub_query => $sql) {
            if((count($columnas) > 0) && !in_array($sub_query, $columnas, true)) {
                continue;
            }
            if(is_numeric($sub_query)){
                return $this->error->error(mensaje: 'Error el key debe ser el nombre de la subquery',
                    data: $columnas_extra, es_final: true);
            }
            if((string)$sub_query === ''){
                return $this->error->error(mensaje:'Error el key no puede venir vacio', data: $columnas_extra,
                    es_final: true);
            }
            if((string)$sql === ''){
                return $this->error->error(mensaje:'Error el sql no puede venir vacio', data: $columnas_extra,
                    es_final: true);
            }
            $columnas_sql .= $columnas_sql === ''?"$sql AS $sub_query":",$sql AS $sub_query";
        }
        return $columnas_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método encargado de generar información de las columnas para los campos del modelo en la base de datos.
     *
     * Este método realiza las siguientes operaciones:
     * 1. Recibe como parámetros un objeto del tipo modelo_base y un string que representa la tabla en la base de datos.
     * 2. Verifica que el nombre de la tabla no esté vacío y no sea un número.
     * 3. Obtiene las columnas nativas de la base de datos para el modelo proporcionado y la tabla especificada.
     * 4. Crea un array con las columnas obtenidas y las retornar.
     *
     * Los errores se manejan devolviendo un objeto de errores si alguna verificación o proceso falla.
     *
     * @param modelo_base $modelo: Modelo con funcionalidad de ORM.
     * @param string $tabla_bd: Nombre de la tabla en la base de datos.
     * @return array|stdClass: Array con información de las columnas si todo sale bien,
     *                         objeto de error si hay un problema.
     * @throws errores: Se lanza una excepción si hay un error en la obtención o gestión de las columnas.
     * @version 18.33.0
     */
    private function genera_columnas_field(modelo_base $modelo, string $tabla_bd): array|stdClass
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd === ''){
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia',data:  $tabla_bd, es_final: true);
        }
        if(is_numeric($tabla_bd)){
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero',data:  $tabla_bd,
                es_final: true);
        }
        $columnas = $this->columnas_bd_native(modelo:$modelo, tabla_bd: $tabla_bd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas);
        }

        $columnas_field = $this->columnas_sql_array(columnas: $columnas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener columnas',data:  $columnas_field);
        }
        return $columnas_field;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función genera una tabla de columnas. Se encarga de validar los parámetros proporcionados,
     * obtener las columnas de la tabla y generar una lista de columnas SQL.
     *
     * @param bool $columnas_en_bruto Define si las columnas se entregan en bruto o no.
     * @param modelo_base $modelo Es la clase modelo que encapsula los datos y la funcionalidad de un registro de la tabla.
     * @param string $tabla_original Es el nombre original de la tabla en la base de datos.
     * @param string $tabla_renombrada Es el nombre con el cual la tabla será renombrada.
     * @param array $columnas Es un array que contiene los nombres de las columnas de la tabla.
     *
     * @return array|string Devuelve un array de columnas SQL si todo va bien. Si surge algún error, devuelve un mensaje de error.
     *
     * @throws errores Si el nombre de la tabla original está vacío o es numérico, lanza una excepción.
     * También hay excepciones para errores al obtener columnas y al obtener el nombre de la tabla.
     * @version 15.63.1
     */

    private function genera_columnas_tabla(bool $columnas_en_bruto,modelo_base $modelo, string $tabla_original,
                                           string $tabla_renombrada, array $columnas = array()):array|string{
        $tabla_original = str_replace('models\\','',$tabla_original);

        if($tabla_original === ''){
            return  $this->error->error(mensaje: 'Error tabla original no puede venir vacia', data: $tabla_original,
                es_final: true);
        }

        if(is_numeric($tabla_original)){
            return $this->error->error(mensaje: 'Error $tabla_original no puede ser un numero',data:  $tabla_original,
                es_final: true);
        }

        $data = $this->obten_columnas( modelo: $modelo, tabla_original: $tabla_original);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener columnas',data:  $data);
        }
        $columnas_parseadas = $data->columnas_parseadas;
        $tabla_nombre = $modelo->obten_nombre_tabla(tabla_original: $tabla_original,
            tabla_renombrada: $tabla_renombrada);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nombre de tabla', data: $tabla_nombre);
        }

        $columnas_sql = $this->columnas_sql_init(columnas: $columnas, columnas_en_bruto:$columnas_en_bruto,
            columnas_parseadas: $columnas_parseadas, tabla_nombre: $tabla_nombre);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener columnas sql',data:  $columnas_sql);
        }
        return $columnas_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Inicializa las columnas por tabla.
     *
     * @param array $columnas_by_table La matriz de nombres de columna por tabla.
     *                                 Debe estar estructurada de la siguiente forma $columnas_by_table[] = "nombre_tabla".
     * @return stdClass|array          Si $columnas_by_table es vacío, retorna un objeto de error.
     *                                 De lo contrario, retorna un objeto que contiene dos propiedades:
     *                                 - columnas_sql: una matriz vacía que posteriormente puede ser llenada con las columnas SQL.
     *                                 - tablas_select: una matriz que asocia cada nombre de tabla con el valor false.
     * @throws errores               Lanza una excepción si $columnas_by_table está vacío.
     * @version 16.14.0
     */
    private function init_columnas_by_table(array $columnas_by_table): stdClass|array
    {
        if(count($columnas_by_table) === 0){
            $fix = 'columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = "nombre_tabla"';
            return $this->error->error(mensaje: 'Error debe columnas_by_table esta vacia', data: $columnas_by_table,
                es_final: true, fix: $fix);
        }
        $columnas_sql = array();
        $tablas_select = array();
        foreach($columnas_by_table as $tabla){
            $tablas_select[$tabla] = false;
        }

        $data = new stdClass();
        $data->columnas_sql = $columnas_sql;
        $data->tablas_select = $tablas_select;
        return $data;
    }

    /**
     * Intega un campo obligatorio para validacion
     * @param string $campo Campo a integrar
     * @param array $campos_obligatorios Campos obligatorios precargados
     * @return array
     * @version 2.114.12
     */
    private function integra_campo_obligatorio(string $campo, array $campos_obligatorios): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo no puede ser vacio', data: $campo);
        }
        $campos_obligatorios[]=$campo;
        return $campos_obligatorios;
    }

    private function integra_campo_obligatorio_existente(string $campo, array $campos_obligatorios, array $campos_tabla): array
    {
        if(in_array($campo, $campos_tabla, true)){

            $campos_obligatorios = $this->integra_campo_obligatorio(campo: $campo,campos_obligatorios:  $campos_obligatorios);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar campos obligatorios ', data: $campos_obligatorios);
            }
        }
        return $campos_obligatorios;
    }

    final public function integra_campos_obligatorios(array $campos_obligatorios, array $campos_tabla): array
    {
        $campos_obligatorios_parciales = array('accion_id','codigo','descripcion','grupo_id','seccion_id');


        foreach($campos_obligatorios_parciales as $campo){

            $campos_obligatorios = $this->integra_campo_obligatorio_existente(
                campo: $campo,campos_obligatorios:  $campos_obligatorios,campos_tabla:  $campos_tabla);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar campos obligatorios ', data: $campos_obligatorios);

            }

        }
        return $campos_obligatorios;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función que integra columnas en una cadena.
     *
     * Esta función tiene la tarea de integrar los nombres de las columnas
     * en formato de cadena que serán utilizados para construir las consultas SQL.
     *
     * @param string $columnas Cadena con los nombres de las columnas actuales.
     * @param string $resultado_columnas Cadena con los nombres de las columnas a añadir.
     *
     * @return stdClass Retorna un objeto que contiene las columnas integradas y una señal de continuación.
     *         - columnas (string): Representa los nombres de las columnas ya integradas.
     *         - continue (boolean): Indica si se debe continuar la operación. Se vuelve verdadero si la entrada $resultado_columnas está vacía.
     *
     * @version 15.71.1
     *
     */
    private function integra_columnas(string $columnas, string $resultado_columnas): stdClass
    {
        $data = new stdClass();
        $continue = false;
        if($columnas === ''){
            $columnas.=$resultado_columnas;
        }
        else{
            if($resultado_columnas === ''){
                $continue = true;
            }
            if(!$continue) {
                $columnas .= ', ' . $resultado_columnas;
            }
        }

        $data->columnas = $columnas;
        $data->continue = $continue;

        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método que integra las columnas por datos.
     *
     * La función se encargará de:
     * - Invocar a la función `integra_columnas` con las columnas y los resultados de columnas dados como parámetros.
     * - Manejar cualquier error que pueda surgir en el proceso anterior.
     * - Si no hay errores, devolverá las columnas integradas.
     *
     * @param string $columnas Columnas para integrar.
     * @param string $resultado_columnas Resultado de las columnas.
     *
     * @return array|string Devuelve las columnas integradas si no hay errores, si surge algún error, devolverá una cadena describiendo el error.
     *
     * @throws errores "Error al integrar columnas" si surge algún error en el proceso.
     *
     * @access private
     * @version 15.72.1
     */
    private function integra_columnas_por_data(string $columnas, string $resultado_columnas):array|string
    {
        $data = $this->integra_columnas(columnas: $columnas, resultado_columnas: $resultado_columnas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $data);
        }
        return $data->columnas;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Esta función se encarga de obtener las columnas de una tabla en la base de datos.
     *
     * @param modelo_base $modelo Modelo base que se utilizará para obtener las columnas.
     * @param string $tabla_original Nombre original de la tabla de la base de datos.
     *
     * @return array|stdClass Devuelve un array que contiene las columnas de la tabla
     * o un objeto stdClass en caso de error.
     *
     * Esta función realiza las siguientes acciones:
     * 1. Verifica que la tabla original no esté vacía.
     * 2. Verifica que la tabla original no sea un número.
     * 3. Intenta asignar las columnas de la tabla a la sesión.
     * 4. Si ocurre un error en el paso anterior, intenta obtener las columnas de una nueva sesión.
     * 5. Devuelve las columnas de la tabla.
     *
     * @throws errores Lanza una excepción en caso de que ocurra un problema al obtener las columnas.
     *
     * @version 19.3.0
     */
    private function obten_columnas(modelo_base $modelo, string $tabla_original):array|stdClass{
        $tabla_original = trim(str_replace('models\\','',$tabla_original));
        $tabla_bd = $tabla_original;

        if($tabla_bd === ''){
            return  $this->error->error(mensaje: 'Error tabla original no puede venir vacia',data: $tabla_bd,
                es_final: true);
        }
        if(is_numeric($tabla_bd)){
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero',data:  $tabla_bd,
                es_final: true);
        }

        $se_asignaron_columnas = $this->asigna_columnas_en_session(modelo: $modelo, tabla_bd: $tabla_bd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar columnas', data: $se_asignaron_columnas);
        }
        if(!$se_asignaron_columnas){
            $columnas_field = $this->asigna_columnas_session_new(modelo:$modelo, tabla_bd: $tabla_bd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas_field);
            }
        }

        return $modelo->data_columnas;
    }

    /**
     * POR DOCUMENTAR WN WIKI FINAL REV
     * Función para obtener las columnas completas de un modelo de base de datos.
     *
     * @param modelo_base $modelo - El modelo base a analizar.
     * @param array $columnas_by_table - Especifica las columnas por tabla.
     * @param bool $columnas_en_bruto - Especifica si se deben obtener las columnas en bruto (sin procesar).
     * @param array $columnas_sql - Permite definir columnas de SQL adicionales.
     * @param array $extension_estructura - Permite definir una estructura de extensión para las columnas.
     * @param array $extra_join - Define uniones adicionales para las consultas.
     * @param array $renombres - Permite cambiar el nombre de las columnas.
     * @return array|string - Devuelve las columnas completas como un array o string en caso de error.
     * @version 16.21.0
     */
    final public function obten_columnas_completas(modelo_base $modelo, array $columnas_by_table = array(),
                                                   bool $columnas_en_bruto = false, array $columnas_sql = array(),
                                                   array $extension_estructura = array(), array $extra_join = array(),
                                                   array $renombres = array()):array|string{


        $tablas_select = (new inicializacion())->tablas_select(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar tablas select en '.$modelo->tabla,
                data:  $tablas_select);
        }

        $columnas = $this->columnas_full(columnas_by_table: $columnas_by_table, columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $columnas_sql, extension_estructura: $extension_estructura, extra_join: $extra_join,
            modelo: $modelo, renombres: $renombres, tablas_select: $tablas_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas en '.$modelo->tabla, data: $columnas);
        }

        return $columnas.' ';
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Crea una consulta subalterna con su correspondiente alias.
     *
     * @param string $alias El alias para la consulta subalterna.
     * @param string $sub_query La consulta subalterna como cadena.
     *
     * @return string|array Devuelve la consulta subalterna con su alias, en caso de que los parámetros sean válidos,
     * de lo contrario arroja un error.
     * @version 16.112.0
     */
    private function sub_query_str(string $alias, string $sub_query): string|array
    {
        $sub_query = trim($sub_query);
        if($sub_query === ''){
            return $this->error->error(mensaje: 'Error sub_query esta vacio ', data: $sub_query, es_final: true);
        }
        $alias = trim($alias);
        if($alias === ''){
            return $this->error->error(mensaje: 'Error alias esta vacio ', data: $alias, es_final: true);
        }
        return $sub_query . ' AS ' . $alias;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función que genera subqueries SQL a partir de un modelo.
     *
     * @param string $columnas Columnas para las que se generarán las subqueries.
     * @param modelo_base $modelo Modelo base que contiene las subqueries a generar.
     * @param array $columnas_seleccionables Opcional. Array de columnas seleccionables que se usarán en las subqueries.
     * Si el array está vacío, se utilizarán todas las columnas.
     *
     * @return array|string Si hay un error, se devuelve un array con información del error. Si no hay errores,
     * se devuelve una cadena con las subqueries SQL generadas.
     *
     * Los errores pueden ser:
     * - Subquery vacía.
     * - Alias vacío.
     * - Alias que es un número.
     * - Error al generar subquery con alias.
     *
     * @version 16.113.0
     */
    final public function sub_querys(string $columnas, modelo_base $modelo,
                               array $columnas_seleccionables = array()):array|string{
        $sub_querys_sql = '';
        foreach($modelo->sub_querys as $alias => $sub_query){
            if($sub_query === ''){
                return $this->error->error(mensaje: "Error el sub query no puede venir vacio",
                    data: $modelo->sub_querys, es_final: true);
            }
            if(trim($alias) === ''){
                return $this->error->error(mensaje:"Error el alias no puede venir vacio", data:$modelo->sub_querys,
                    es_final: true);
            }
            if(is_numeric($alias)){
                return $this->error->error(mensaje:"Error el alias no puede ser un numero", data:$modelo->sub_querys,
                    es_final: true);
            }
            if((count($columnas_seleccionables) > 0) && !in_array($alias, $columnas_seleccionables, true)) {
                continue;
            }
            $sub_query_str = $this->sub_query_str(alias: $alias,sub_query:  $sub_query);
            if(errores::$error){
                return $this->error->error(mensaje:"Error generar subquery con alias", data:$sub_query_str);
            }

            $coma = '';
            if ($sub_querys_sql === '' && $columnas === '') {
                $coma = ' , ';
            }

            $sub_querys_sql .= $coma . $sub_query_str;
        }

        return $sub_querys_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función privada que devuelve el nombre renombrado de una tabla con base en los datos proporcionados.
     *
     * @param array $data Conjunto de datos los cuales pueden contener el renombre asignado a la tabla.
     * @param string $tabla Nombre de la tabla que se necesita renombrar.
     *
     * @return string|array Devuelve el nombre renombrado de la tabla o un mensaje de error.
     *
     * La función comienza por limpiar los espacios en blanco en el nombre de la tabla proporcionado.
     * Si después de este proceso el nombre de la tabla está vacío, la función devuelve un error indicando que
     * la tabla está vacía.
     *
     * A continuación, la función comprueba si en el conjunto de datos proporcionado se incluye un nombre alternativo
     * ('renombre') para la tabla. Si no es así, se devuelve el nombre original de la tabla.
     *
     * Si se proporciona un nombre alternativo y este no está vacío luego de limpiar los espacios en blanco,
     * la función lo devolverá como el nuevo nombre de la tabla.
     *
     * @version 15.82.1
     */
    private function tabla_renombrada_extra(array $data, string $tabla): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje:"Error tabla esta vacia", data:$tabla, es_final: true);
        }
        $tabla_renombrada = $tabla;
        if(isset($data['renombre'])){
            $data['renombre'] = trim($data['renombre']);
            if($data['renombre'] !== ''){
                $tabla_renombrada = $data['renombre'];
            }
        }
        return $tabla_renombrada;

    }


    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Valida las columnas SQL proporcionadas.
     *
     * Esta función privada toma tres parámetros: 'alias_columnas', 'columna_parseada', y 'tabla_nombre', y realiza varias
     * comprobaciones para confirmar que son válidos para el procesamiento adicional.
     * Retorna true si todos los parámetros pasan las validaciones o un error si alguno de los parámetros es vacío.
     *
     * @param string $alias_columnas Representa alias de las columnas
     * @param string $columna_parseada Representa la columna parseada
     * @param string $tabla_nombre Representa el nombre de la tabla
     *
     * @return array|true Retorna true si todos los parámetros son validos. De otra manera, retorna un error.
     *
     * @throws errores
     *
     * @internal
     * @version 15.55.1
     */
    private function valida_columnas_sql(string $alias_columnas, string $columna_parseada,
                                         string $tabla_nombre): true|array
    {
        if($tabla_nombre === ''){
            return $this->error->error(mensaje: 'Error $tabla_nombre no puede venir vacia', data: $tabla_nombre,
                es_final: true);
        }
        if($columna_parseada === ''){
            return $this->error->error(mensaje:'Error $columna_parseada no puede venir vacia',data: $columna_parseada,
                es_final: true);
        }
        if($alias_columnas === ''){
            return $this->error->error(mensaje:'Error $alias_columnas no puede venir vacia',data: $alias_columnas,
                es_final: true);
        }
        return true;

    }

}

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
     * Anexa las columnas para suma
     * @param string $campo Campo a integrar
     * @param string $alias Alias del campo para salida
     * @return string|array
     * @version 1.477.49
     */
    public function add_column(string $alias, string $campo): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error $campo no puede venir vacio', data: $campo);
        }
        $alias = trim($alias);
        if($alias === ''){
            return $this->error->error(mensaje:'Error $alias no puede venir vacio', data: $alias);
        }
        return 'IFNULL( SUM('. $campo .') ,0)AS ' . $alias;
    }

    /**
     * POR DOCUMENTAR EN WIKI
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
            return $this->error->error(mensaje: 'Error $tabla no puede ser un numero',data:  $tabla);
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
     * POR DOCUMENTAR EN WIKI
     * Esta función verifica si el arreglo de entrada $columnas_by_table contiene algún elemento. Si el conteo
     * del arreglo es más de cero, la función devolverá true, de lo contrario, devolverá false.
     * En otras palabras, verifica si hay alguna columna en el arreglo dado que necesite ser aplicada a la tabla.
     * Si la hay, devolverá true, indicando que la operación de aplicar columnas a la tabla puede realizarse.
     * De lo contrario, devuelve false.
     *
     * @param array $columnas_by_table conjunto de columnas si es vacio aplica la sentencia SQL completa
     * @return bool
     * @version 13.16.0
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
     * POR DOCUMENTAR EN WIKI
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
            return $this->error->error(mensaje: 'Error atributo no puede venir vacio', data: $atributo);
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
     * POR DOCUMENTAR EN WIKI
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
     */
    private function asigna_columnas_en_session(modelo_base $modelo, string $tabla_bd): bool|array
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd===''){
            return $this->error->error(mensaje: 'Error tabla_bd no puede venir vacia', data: $tabla_bd);
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
     * POR DOCUMENTAR EN WIKI
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
            return $this->error->error(mensaje: 'Error atributo no puede venir vacio',data:  $atributo);
        }
        $columnas_parseadas[] = $atributo;
        return $columnas_parseadas;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Asigna las columnas de una tabla al objeto de sesión y al modelo base.
     *
     * Esta función toma un modelo y el nombre de una tabla de la base de datos como argumentos. Primero, realiza algunas validaciones en $tabla_bd
     * para verificar que no esté vacío y que no sea un número.
     *
     * Luego, genera las columnas field utilizando el modelo y la tabla de la base de datos y verifica si ocurrió algún error durante la generación.
     *
     * Si todo es correcto, asigna las columnas parseadas y las columnas completas a la sesión y las asigna al objeto modelo de la base. Finalmente,
     * devuelve las columnas de la base de datos del modelo.
     *
     * @param modelo_base $modelo El modelo a verificar.
     * @param string $tabla_bd El nombre de la tabla en la base de datos.
     * @return array|stdClass Las columnas de la base de datos del modelo.
     * @version 15.46.1
     */
    private function asigna_columnas_session_new(modelo_base $modelo, string $tabla_bd): array|stdClass
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd === ''){
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia',data:  $tabla_bd);
        }
        if(is_numeric($tabla_bd)){
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero',data:  $tabla_bd);
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
     * POR DOCUMENTAR EN WIKI
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
     */
    private function asigna_data_columnas(stdClass $data, string $tabla_bd): stdClass|array
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd===''){
            return $this->error->error(mensaje: 'Error tabla_bd no puede venir vacia', data: $tabla_bd);
        }
        if(!isset($_SESSION['campos_tabla'])){
            return $this->error->error(mensaje: 'Error debe existir SESSION[campos_tabla]',data: $_SESSION);
        }
        if(!isset($_SESSION['campos_tabla'][$tabla_bd])){
            return $this->error->error(mensaje: 'Error debe existir SESSION[campos_tabla]['.$tabla_bd.']',
                data: $_SESSION);
        }
        if(!isset($_SESSION['columnas_completas'])){
            return $this->error->error(mensaje: 'Error debe existir SESSION[columnas_completas]',data: $_SESSION);
        }
        if(!isset($_SESSION['columnas_completas'][$tabla_bd])){
            return $this->error->error(mensaje: 'Error debe existir SESSION[columnas_completas]['.$tabla_bd.']',
                data:$_SESSION);
        }

        $data->columnas_parseadas = $_SESSION['campos_tabla'][$tabla_bd];
        $data->columnas_completas = $_SESSION['columnas_completas'][$tabla_bd];

        return $data;
    }

    /**
     * Elimina los campos no actualizables de un modelo
     * @version 1.76.17
     * @param array $campos_no_upd viene de modelo campos_no_upd
     * @param array $registro Arreglo de tipo registro a modificar
     * @return array Registro ajustado
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
     * POR DOCUMENTAR EN WIKI
     * Esta función obtiene las columnas (campos) de una tabla dada y las asigna a una instancia de un modelo dado.
     *
     * @param modelo $modelo Es la instancia del modelo a la que se le asignarán los campos de la tabla.
     * @param string $tabla Es el nombre de la tabla de la que se extraerán los campos
     *
     * @return array Retorna un array de campos (columnas) de la tabla asignados al modelo.
     * En caso de error, devuelve un mensaje de error.
     * @version 15.48.1
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
     * POR DOCUMENTAR EN WIKI
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
     * REFACTORIZAR
     * Obtiene las columnas para un select dependiendo de si aplica o no una tabla o todas
     * @param bool $aplica_columnas_by_table Si aplica columnas by table solo se tra la info de las columnas
     * cargadas en el array
     * @param array $columnas_by_table Conjunto de tablas a obtener campos para un SELECT
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param array $extension_estructura Datos para la extension de una estructura que va fuera de la
     * logica natural de dependencias
     * @param array $extra_join integra joins extra a peticion de funcion no usar en modelo
     * @param modelo_base $modelo Modelo o tabla de aplicacion
     * @param array $renombres Conjunto de tablas para renombrar
     * @param array $tablas_select Tablas ligadas al modelo en ejecucion
     * @return array|string
     * @example Si $aplica_columnas_by_table es true debe haber columnas_by_table con
     * datos columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] =nombre_tabla
     * @example Si !$aplica_columnas_by_table $columnas_by_table deb ser vacio
     */
    private function columnas(bool $aplica_columnas_by_table, array $columnas_by_table, bool $columnas_en_bruto,
                              array $columnas_sql, array $extension_estructura, array $extra_join, modelo_base $modelo,
                              array $renombres, array $tablas_select): array|string
    {
        if(!$aplica_columnas_by_table) {

            if(count($columnas_by_table) > 0){
                $fix = 'Si !$aplica_columnas_by_table $columnas_by_table debe ser vacio';
                return $this->error->error(mensaje: 'Error columnas_by_table tiene datos en modelo '.$modelo->tabla,
                    data: $columnas_by_table, fix: $fix);
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
                    data: $columnas_by_table, fix: $fix);
            }
            $columnas = $this->columnas_by_table(columnas_by_table: $columnas_by_table,
                columnas_en_bruto: $columnas_en_bruto, modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas by table en '.$modelo->tabla,
                    data: $columnas);
            }
        }

        /*$columnas = trim($columnas);
        if($columnas === ''){
            return $this->error->error(mensaje: 'Error ninguna configuracion es aceptable en '.$modelo->tabla,
                data: $columnas);
        }*/
        $columnas = trim($columnas);
        if($columnas === ''){
            $columnas = "$modelo->key_filtro_id as $modelo->key_id";
        }

        return $columnas;
    }

    /**
     * POR DOCUMENTAR EN WIKI
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
     * Genera las columnas en forma de SQL para un select con todas las configuracion nativas de un modelo
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param array $extension_estructura Datos para la extension de una estructura que va fuera de la
     * logica natural de dependencias
     * @param array $extra_join integra joins extra a peticion de funcion no usar en modelo
     * @param modelo_base $modelo Modelo o tabla de aplicacion
     * @param array $renombres Conjunto de tablas para renombrar
     * @param array $tablas_select Tablas ligadas al modelo en ejecucion
     * @return array|string
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
     * POR DOCUMENTAR EN WIKI
     * Devuelve información sobre las columnas de la base de datos nativa
     *
     * Esta función toma una instancia de un modelo base y una tabla de base de datos en formato de cadena como argumentos.
     * Devuelve un array que contiene información sobre las columnas en la tabla de la base de datos proporcionada.
     *
     * @final
     *
     * @param   modelo_base    $modelo    Una instancia del modelo base.
     * @param   string         $tabla_bd  Una cadena que representa la tabla de base datos
     *
     * @return  array          Retorna un array con información de las columnas de la tabla de búsqueda
     *                         Retorna un array vacío y un mensaje de error si la tabla de la base de datos es vacía o es numérica,
     *                         si hubo un error al obtener la consulta SQL o al ejecutarla, o si no existen columnas en la tabla.
     *
     * @throws  errores si la consulta SQL encuentra un error o ejecuta una operación fallida.
     *
     * @example Ejemplo de uso:
     *          columnas_bd_native($modelo, 'tabla_muestra');
     *
     * @version 15.29.1
     */
    final public function columnas_bd_native(modelo_base $modelo, string $tabla_bd): array
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd === ''){
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia',data:  $tabla_bd);
        }
        if(is_numeric($tabla_bd)){
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero',data:  $tabla_bd);
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
            return $this->error->error(mensaje: 'Error no existen columnas', data: $result);
        }

        return $result->registros;
    }

    /**
     * Obtiene un SQL solo con las columnas de una tabla
     * @param array $columnas_by_table Conjunto de tablas a obtener campos para un SELECT
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @param modelo_base $modelo Modelo o tabla de aplicacion
     * @return array|string
     */
    private function columnas_by_table(array $columnas_by_table, bool $columnas_en_bruto,
                                       modelo_base $modelo): array|string
    {
        if(count($columnas_by_table) === 0){
            $fix = 'columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = "nombre_tabla"';
            return $this->error->error(mensaje: 'Error debe columnas_by_table esta vacia', data: $columnas_by_table,
                fix: $fix);
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
     * POR DOCUMENTAR EN WIKI
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
     * POR DOCUMENTAR EN WIKI
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
                    data: $extension_estructura);
            }

            $columnas_env = $this->ajusta_columnas_completas(columnas: $columnas, columnas_en_bruto: false,
                columnas_sql: $columnas_sql,  modelo: $modelo, tabla: $tabla, tabla_renombrada: '');
            if(errores::$error){
                return $this->error->error(mensaje:'Error al integrar columnas', data:$columnas);
            }

        }
        return $columnas_env;
    }

    /**
     * POR DOCUMENTAR EN WIKI
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
    private function columnas_extra(string $columnas, array $columnas_sql,  array $extra_join,
                                    modelo_base $modelo): array|string
    {
        $columnas_env = $columnas;
        foreach($extra_join as $tabla=>$data){
            $tabla = str_replace('models\\','',$tabla);

            if(is_numeric($tabla)){
                return $this->error->error(mensaje: 'Error ingrese un array valido '.$tabla,
                    data: $extra_join);
            }
            if(!is_array($data)){
                return $this->error->error(mensaje: 'Error data debe ser un array ',
                    data: $data);
            }

            $tabla_renombrada = $this->tabla_renombrada_extra(data: $data,tabla:  $tabla);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al integrar tabla_renombrada', data:$tabla_renombrada);
            }

            $columnas_env = $this->ajusta_columnas_completas(columnas: $columnas, columnas_en_bruto: false,
                columnas_sql: $columnas_sql,  modelo: $modelo, tabla: $tabla, tabla_renombrada: $tabla_renombrada);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al integrar columnas', data:$columnas_env);
            }

        }
        return $columnas_env;
    }


    /**
     * POR DOCUMENTAR EN WIKI
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
     * Genera las columnas con renombre para integrarlas en un SELECT
     * @param string $columnas Columnas en forma de SQL para consultas, forma tabla_nombre_campo
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @param array $renombres Conjunto de tablas para renombrar
     * @return array|string
     */
    private function columnas_renombre(string $columnas, array $columnas_sql, modelo_base $modelo,
                                       array $renombres): array|string
    {
        foreach($renombres as $tabla=>$data){
            if(!is_array($data)){
                return $this->error->error(mensaje: 'Error data debe ser array '.$tabla,data:  $data);
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
     * POR DOCUMENTAR EN WIKI
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
     * POR DOCUMENTAR EN WIKI
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
                return $this->error->error(mensaje: 'Error $columna debe ser un array', data: $columnas);
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
     * POR DOCUMENTAR EN WIKI
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
            return $this->error->error(mensaje: 'Error $tabla_nombre no puede venir vacia',data:  $tabla_nombre);
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
     * POR DOCUMENTAR EN WIKI
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
                return $this->error->error(mensaje: 'Error $key no puede ser un numero',data:  $key);
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
     * POR DOCUMENTAR EN WIKI
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
     * POR DOCUMENTAR EN WIKI
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
            return  $this->error->error(mensaje: 'Error tabla original no puede venir vacia',data: $tabla_original);
        }
        if(is_numeric($tabla_original)){
            return $this->error->error(mensaje: 'Error $tabla_original no puede ser un numero',data:  $tabla_original);
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
     * POR DOCUMENTAR EN WIKI
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
            return $this->error->error(mensaje: 'Error $key no puede ser un numero',data:  $key);
        }

        $result = $this->ajusta_columnas_completas(columnas: $columnas, columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $columnas_sql,  modelo: $modelo, tabla: $key, tabla_renombrada: '');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $result);
        }
        return (string)$result;
    }

    /**
     * POR DOCUMENTAR EN WIKI
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
            return $this->error->error(mensaje: 'Error $tabla_original no puede ser un numero',data:  $tabla_original);
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
     *
     * Funcion que genera conjunto de columnas en forma de sql para ser utilizada en un SELECT obtenidas de
     *      this->columnas_extra this->columnas_extra debe ser un conjunto de subquerys
     * @version 1.45.14
     * @param array $columnas columnas a mostrar y obtener en el sql
     * @return array|string string en forma de sql con los datos de las columnas a ejecutar SELECT
     * @throws errores subquerys mal formados
     * @throws errores si key de $this->columnas_extra no es un txt
     * @throws errores si sql de $this->columnas_extra[key] viene vacio
     *@example
     * $columnas_extra_sql = $this->genera_columnas_extra();
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
                    data: $columnas_extra);
            }
            if((string)$sub_query === ''){
                return $this->error->error(mensaje:'Error el key no puede venir vacio', data: $columnas_extra);
            }
            if((string)$sql === ''){
                return $this->error->error(mensaje:'Error el sql no puede venir vacio', data: $columnas_extra);
            }
            $columnas_sql .= $columnas_sql === ''?"$sql AS $sub_query":",$sql AS $sub_query";
        }
        return $columnas_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI
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
     * @version 15.45.1
     */
    private function genera_columnas_field(modelo_base $modelo, string $tabla_bd): array|stdClass
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd === ''){
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia',data:  $tabla_bd);
        }
        if(is_numeric($tabla_bd)){
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero',data:  $tabla_bd);
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
     * POR DOCUMENTAR EN WIKI
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
            return  $this->error->error(mensaje: 'Error tabla original no puede venir vacia', data: $tabla_original);
        }

        if(is_numeric($tabla_original)){
            return $this->error->error(mensaje: 'Error $tabla_original no puede ser un numero',data:  $tabla_original);
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
     * Inicializa los datos necesarios pa integrar las columnas puras de una sola tabla
     * @param array $columnas_by_table Conjunto de tablas a obtener campos para un SELECT
     * @return stdClass|array obj->columnas_sql obj->tablas_select
     * @example $columnas_by_table[] = 'adm_accion'
     * @version 1.53.16
     */
    private function init_columnas_by_table(array $columnas_by_table): stdClass|array
    {
        if(count($columnas_by_table) === 0){
            $fix = 'columnas_by_table debe estar maquetado de la siguiente forma $columnas_by_table[] = "nombre_tabla"';
            return $this->error->error(mensaje: 'Error debe columnas_by_table esta vacia', data: $columnas_by_table,
                fix: $fix);
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
     * POR DOCUMENTAR EN WIKI
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
     * POR DOCUMENTAR EN WIKI
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
     * POR DOCUMENTAR EN WIKI
     * Obtiene columnas de una modelo de base de datos especificada
     *
     * Esta función toma un modelo de base de datos y el nombre de una tabla.
     * Primero, verifica si la tabla proporcionada es válida. Luego, trata de asignar las columnas de la tabla
     * al modelo dado utilizando 'asigna_columnas_en_session'. Si esto no es exitoso, intenta obtener las columnas
     * nuevamente utilizando 'asigna_columnas_session_new'. Finalmente, retorna las columnas del modelo.
     *
     * @param modelo_base $modelo instancia del modelo base desde donde se obtienen las columnas
     * @param string $tabla_original Nombre original de la tabla en la BD
     *
     * @return array|stdClass las columnas del modelo en caso de éxito, en caso contrario, retorna un objeto de error
     *
     * @throws errores si hay un error al asignar o obtener las columnas
     * @version 15.47.1
     */
    private function obten_columnas(modelo_base $modelo, string $tabla_original):array|stdClass{
        $tabla_original = trim(str_replace('models\\','',$tabla_original));
        $tabla_bd = $tabla_original;

        if($tabla_bd === ''){
            return  $this->error->error(mensaje: 'Error tabla original no puede venir vacia',data: $tabla_bd);
        }
        if(is_numeric($tabla_bd)){
            return $this->error->error(mensaje: 'Error $tabla_bd no puede ser un numero',data:  $tabla_bd);
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
     *
     * Genera las columnas en forma de sql para ser utilizado en un SELECT de todas las columnas unidas por el modelo
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @param array $columnas_by_table Obtiene solo las columnas de la tabla en ejecucion
     * @param bool $columnas_en_bruto Envia las columnas tal como estan en la bd
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param array $extension_estructura conjunto de columnas mostradas como extension de datos tablas 1 a 1
     * @param array $extra_join
     * @param array $renombres conjunto de columnas renombradas
     * @return array|string sql con las columnas para un SELECT
     * @example
     *      $columnas = $this->obten_columnas_completas($columnas);
     * @pordoc false
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
     *
     * Devuelve un conjunto de campos obtenidos de this->sub_querys
     * @param string $columnas columnas previamente generadas en SQL
     * @param modelo_base $modelo Modelos en ejecucion
     * @param array $columnas_seleccionables Conjunto de columnas a generar subquerys
     *
     * @return array|string
     * @example
     *      $sub_querys_sql = $this->sub_querys($columnas);
     */
    final public function sub_querys(string $columnas, modelo_base $modelo,
                               array $columnas_seleccionables = array()):array|string{
        $sub_querys_sql = '';
        foreach($modelo->sub_querys as $alias => $sub_query){
            if($sub_query === ''){
                return $this->error->error(mensaje: "Error el sub query no puede venir vacio",
                    data: $modelo->sub_querys);
            }
            if(trim($alias) === ''){
                return $this->error->error(mensaje:"Error el alias no puede venir vacio", data:$modelo->sub_querys);
            }
            if(is_numeric($alias)){
                return $this->error->error(mensaje:"Error el alias no puede ser un numero", data:$modelo->sub_querys);
            }
            if((count($columnas_seleccionables) > 0) && !in_array($alias, $columnas_seleccionables, true)) {
                continue;
            }
            if ($sub_querys_sql === '' && $columnas === '') {
                $sub_querys_sql .= $sub_query . ' AS ' . $alias;
            } else {
                $sub_querys_sql = ' , ' . $sub_query . ' AS ' . $alias;
            }
        }

        return $sub_querys_sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI
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
            return $this->error->error(mensaje:"Error tabla esta vacia", data:$tabla);
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
     * POR DOCUMENTAR EN WIKI
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
            return $this->error->error(mensaje: 'Error $tabla_nombre no puede venir vacia', data: $tabla_nombre);
        }
        if($columna_parseada === ''){
            return $this->error->error(mensaje:'Error $columna_parseada no puede venir vacia',data: $columna_parseada);
        }
        if($alias_columnas === ''){
            return $this->error->error(mensaje:'Error $alias_columnas no puede venir vacia',data: $alias_columnas);
        }
        return true;

    }

}

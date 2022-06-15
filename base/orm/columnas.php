<?php
namespace base\orm;
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
     *
     * @param string $campo
     * @param string $alias
     * @return string|array
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
     * Genera las columnas sql para un select
     * @version 1.47.14
     * @param string $columnas Columnas en forma de SQL para consultas, forma tabla_nombre_campo
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @param string $tabla nombre del modelo debe de coincidir con una estructura de la base de datos
     * @param string $tabla_renombrada Tabla o renombre de como quedara el AS en SQL de la tabla original
     * @return array|string
     */
    private function ajusta_columnas_completas(string $columnas, bool $columnas_en_bruto, array $columnas_sql,
                                               modelo_base $modelo, string $tabla,
                                               string $tabla_renombrada): array|string
    {
        $tabla = str_replace('models\\','',$tabla);
        $class = 'models\\'.$tabla;

        if(!class_exists($class)){
            return  $this->error->error(mensaje: 'Error no existe el modelo '.$tabla,data: $tabla);
        }
        $resultado_columnas = $this->genera_columnas_consulta(columnas_en_bruto:$columnas_en_bruto,modelo: $modelo,
            tabla_original: $tabla, tabla_renombrada: $tabla_renombrada, columnas: $columnas_sql);
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
     * Asigna los valor de una columna obtenida de un DESCRIBE para su uso en la maquetacion de SELECT
     * @version 1.27.14
     * @param string $atributo
     * @param array $columna
     * @param array $columnas_completas
     * @return array
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
            return $this->error->error(mensaje:'Error al validar $columna', data: $valida, params: get_defined_vars());
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
     * Asigna las columnas en forma de SQL en una variable de SESSION en caso de que no exista
     * @version 1.0.0
     * @param string $tabla_bd Tabla de la base de datos de donde se obtendran y asignaran las columnas
     * @param modelo_base $modelo modelo o estructura de la base de datos
     * @return bool|array
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
     * Asigna los atributos de una tabla en un array para su uso en la maquetacion de un select
     * @version 1.27.14
     * @param array $columnas_parseadas Columnas ajustadas paar uso en SELECT
     * @param string $atributo Columna a asignar en columnas parseadas
     * @return array
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
     *
     * @param modelo_base $modelo modelo o estructura de la base de datos con funcionalidades de ORM
     * @version 1.44.14
     * @param string $tabla_bd Tabla o estructura de una base de datos igual al modelo
     * @return array|stdClass
     */
    private function asigna_columnas_session_new(modelo_base $modelo, string $tabla_bd): array|stdClass
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd === ''){
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia',data:  $tabla_bd);
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
     * Obtiene las columnas de una tabla y los asigna a la variable de SESSION[campos_tabla] y
     * SESSION[columnas_completas] Para ser utilizadas en las consultas SELECT
     * @version 1.0.0
     * @param stdClass $data Objeto recursivo con los atributos  columnas_parseadas y columnas_completas
     * @param string $tabla_bd Tabla de la base de datos de donde se obtendran y asignaran las columnas
     * @return stdClass|array stdClass si es exito con atributos columnas_parseadas y columnas_completas
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
     * FULL
     * @param string $columnas Columnas en forma de SQL para consultas, forma tabla_nombre_campo
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param array $data Datos para la maquetacion del JOIN
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @param string $tabla nombre del modelo debe de coincidir con una estructura de la base de datos
     * @return array|string
     */
    private function carga_columna_renombre(string $columnas, array $columnas_sql, array $data, modelo_base $modelo,
                                            string $tabla): array|string
    {

        $valida = $this->validacion->valida_data_columna(data: $data,tabla:  $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }


        $r_columnas = $this->ajusta_columnas_completas(columnas:  $columnas,columnas_en_bruto: false,
            columnas_sql:  $columnas_sql, modelo: $modelo, tabla: $data['nombre_original'], tabla_renombrada: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }

        return (string)$r_columnas;
    }

    /**
     * Genera las columnas de un atributo de la base de datos
     * @version 1.41.14
     * @param array $columna Columna con datos detallados de un DESCRIBE
     * @param array $columnas_parseadas Columnas ajustadas para su salida para SELECT
     * @param array $columnas_completas Todas las columnas ajustadas
     * @return array|stdClass
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
     * Funcion para obtener las columnas de manera nativa
     * @version 1.26.14
     * @param modelo_base $modelo modelo o estructura de la base de datos con funcionalidades de ORM
     * @param string $tabla_bd Tabla o estructura de una base de datos igual al modelo
     * @return array
     */
    public function columnas_bd_native(modelo_base $modelo, string $tabla_bd): array
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd === ''){
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia',data:  $tabla_bd);
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
     * Funcion que genera el sql de columnas en forma de texto para campos SELECT
     * @version 1.47.14
     * @param string $columnas_extra_sql Columnas que vienen de modelo->columnas_extra
     * @param string $columnas_sql Columnas que vienen de modelo->columnas de cada tabla
     * @return string
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
     * FULL
     * @param array $extension_estructura Datos para la extension de una estructura que va fuera de la
     * logica natural de dependencias
     * @param array $columnas_sql
     * @param string $columnas
     * @param modelo_base $modelo
     * @return array|string
     */
    private function columnas_extension(string $columnas, array $columnas_sql, array $extension_estructura,
                                        modelo_base $modelo): array|string
    {
        $columnas_env = $columnas;
        foreach($extension_estructura as $tabla=>$data){
            $tabla = str_replace('models\\','',$tabla);
            $class = 'models\\'.$tabla;
            if(is_numeric($tabla)){
                return $this->error->error(mensaje: 'Error ingrese un array valido '.$tabla,
                    data: $extension_estructura);
            }
            if(!class_exists($class)){
                return $this->error->error(mensaje:'Error no existe el modelo '.$tabla, data:$tabla,
                    params: get_defined_vars());
            }

            $columnas_env = $this->ajusta_columnas_completas(columnas:  $columnas, columnas_en_bruto: false,
                columnas_sql:  $columnas_sql, modelo: $modelo, tabla: $tabla, tabla_renombrada: '');
            if(errores::$error){
                return $this->error->error(mensaje:'Error al integrar columnas', data:$columnas);
            }

        }
        return $columnas_env;
    }

    /**
     * Asigna las columnas para ser utilizadas en la transacciones de SELECT
     * @version 1.27.14
     * @param string $campo Nombre del campo del modelo
     * @param array $columnas_parseadas Columnas ajustadas para su salida para SELECT
     * @param string|null $atributo Atributo de la base de datos
     * @param array $columna Columna con datos detallados de un DESCRIBE
     * @param array $columnas_completas Todas las columnas ajustadas
     * @return array|stdClass
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
     * FULL
     * @param array $columnas_by_table Obtiene solo las columnas de la tabla en ejecucion
     * @param bool $columnas_en_bruto
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param array $extension_estructura Datos para la extension de una estructura que va fuera de la
     * logica natural de dependencias
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @param array $renombres Conjunto de tablas para renombrar
     * @param array $tablas_select Tablas ligadas al modelo en ejecucion
     * @return array|string
     */
    PUBLIC function columnas_full(array $columnas_by_table, bool $columnas_en_bruto, array $columnas_sql,
                                   array $extension_estructura, modelo_base $modelo, array $renombres,
                                   array $tablas_select): array|string
    {
        $aplica_columnas_by_table = false;

        if(count($columnas_by_table)>0){
            $aplica_columnas_by_table = true;
        }

        if(!$aplica_columnas_by_table) {
            $columnas = $this->columnas_tablas_select(columnas_en_bruto:$columnas_en_bruto,
                columnas_sql: $columnas_sql, modelo: $modelo, tablas_select: $tablas_select);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
            }

            $columnas = $this->columnas_extension(columnas: $columnas, columnas_sql: $columnas_sql,
                extension_estructura: $extension_estructura, modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
            }

            $columnas = $this->columnas_renombre(columnas: $columnas, columnas_sql: $columnas_sql, modelo: $modelo,
                renombres: $renombres);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
            }

        }
        else{
            /**
             * REFACTORIZAR
             */
            $columnas_sql = array();
            $tablas_select = array();
            foreach($columnas_by_table as $tabla){
                $tablas_select[$tabla] = false;
            }

            $columnas = $this->columnas_tablas_select(columnas_en_bruto:$columnas_en_bruto, columnas_sql: $columnas_sql,
                modelo: $modelo, tablas_select: $tablas_select);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
            }
        }


        return $columnas;


    }

    /**
     * FULL
     * @param array $renombres Conjunto de tablas para renombrar
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param string $columnas Columnas en forma de SQL para consultas, forma tabla_nombre_campo
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @return array|string
     */
    private function columnas_renombre(string $columnas, array $columnas_sql, modelo_base $modelo, array $renombres): array|string
    {
        foreach($renombres as $tabla=>$data){
            if(!is_array($data)){
                return $this->error->error(mensaje: 'Error data debe ser array '.$tabla,data:  $data);
            }
            $r_columnas = $this->carga_columna_renombre(columnas: $columnas,columnas_sql: $columnas_sql,
                data: $data,modelo: $modelo,tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
            }
            $columnas = (string)$r_columnas;
        }

        return $columnas;
    }

    /**
     *
     * Funcion que genera una columna en forma de sql para ser utilizada en un SELECT
     * @version 1.45.14
     * @param string $columnas_sql columnas en forma de sql
     * @param string $tabla_nombre nombre de la tabla para hacer la union y formar el sql
     * @param string $columna_parseada columna ajustada para ser anexada al sql
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @param string $alias_columnas columna ajustada para ser anexada al sql como un alias
     * @example
    $columnas_sql = $this->columnas_sql($columnas_sql,$tabla_nombre,$columna_parseada,$alias_columnas);
     * @return array|string string en forma de sql con los datos de las columnas a ejecutar SELECT
     * @throws errores $tabla_nombre no puede venir vacia
     * @throws errores $columna_parseada no puede venir vacia
     * @throws errores $alias_columnas no puede venir vacia
     */
    private function columnas_sql(string $alias_columnas, string $columna_parseada, bool $columnas_en_bruto,
                                  string $columnas_sql, string $tabla_nombre):array|string{

        /**
         * REFACTORIZAR
         */
        if($tabla_nombre === ''){
            return $this->error->error(mensaje: 'Error $tabla_nombre no puede venir vacia', data: $tabla_nombre);
        }
        if($columna_parseada === ''){
            return $this->error->error(mensaje:'Error $columna_parseada no puede venir vacia',data: $columna_parseada);
        }
        if($alias_columnas === ''){
            return $this->error->error(mensaje:'Error $alias_columnas no puede venir vacia',data: $alias_columnas);
        }

        if($columnas_en_bruto){
            $alias_columnas = $columna_parseada;
        }
        if($columnas_sql === ''){
            $columnas_sql.= $tabla_nombre.'.'.$columna_parseada.' AS '.$alias_columnas;
        }
        else{
            $columnas_sql.=', '.$tabla_nombre.'.'.$columna_parseada.' AS '.$alias_columnas;
        }

        return $columnas_sql;
    }

    /**
     * Integra las columnas para un select
     * @version 1.43.14
     * @param array $columnas Conjunto de columnas a parsear
     * @return array|stdClass
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
     *
     * Funcion que genera conjunto de columnas en forma de sql para ser utilizada en un SELECT
     * @version 1.45.14
     * @param string $tabla_nombre nombre de la tabla para hacer la union y formar el sql
     * @param array $columnas_parseadas arreglo con datos para la creacion de las columnas en sql
     * @param array $columnas columnas inicializadas a mostrar a peticion
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @example
    $columnas_parseadas = $this->obten_columnas($tabla_original);
    $tabla_nombre = $this->obten_nombre_tabla($tabla_renombrada,$tabla_original);
    $columnas_sql = $this->columnas_sql_init($columnas_parseadas,$tabla_nombre,$columnas);
     * @return array|string string en forma de sql con los datos de las columnas a ejecutar SELECT
     * @throws errores $tabla_nombre no puede venir vacia
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
     * Genera las columnas para un sql con joins
     * @version 1.49.14
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param modelo_base $modelo Modelo o tabla de aplicacion
     * @param array $tablas_select Tablas ligadas al modelo en ejecucion
     * @return array|string
     */
    private function columnas_tablas_select(bool $columnas_en_bruto, array $columnas_sql, modelo_base $modelo,
                                            array $tablas_select): array|string
    {
        $columnas = '';
        foreach ($tablas_select as $key=>$tabla_select){
            $result = $this->genera_columna_tabla(columnas: $columnas, columnas_en_bruto: $columnas_en_bruto,
                columnas_sql:  $columnas_sql,key:  $key, modelo: $modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar columnas',data:  $result);
            }
            $columnas = (string)$result;
        }
        return $columnas;
    }

    /**
     * Obtiene las columnas para la ejecucion de un SELECT
     * @version 1.46.14
     * @param array $columnas columnas inicializadas a mostrar a peticion
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @param string $tabla_original nombre del modelo debe de coincidir con una estructura de la base de datos
     * @param string $tabla_renombrada Tabla o renombre de como quedara el AS en SQL de la tabla original
     * @return array|stdClass
     */
    private function data_for_columnas_envio(array $columnas, bool $columnas_en_bruto, modelo_base $modelo,
                                             string $tabla_original, string $tabla_renombrada): array|stdClass
    {
        $tabla_original = str_replace('models\\','',$tabla_original);
        $class = 'models\\'.$tabla_original;
        if($tabla_original === ''){
            return  $this->error->error(mensaje: 'Error tabla original no puede venir vacia',data: $tabla_original);
        }
        if(!class_exists($class)){
            return $this->error->error(mensaje: 'Error no existe el modelo '.$tabla_original, data: $tabla_original);
        }

        $columnas_sql = $this->genera_columnas_tabla( columnas_en_bruto: $columnas_en_bruto, modelo: $modelo,
            tabla_original: $tabla_original, tabla_renombrada: $tabla_renombrada, columnas:  $columnas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnas',data:  $columnas_sql);

        }

        $columnas_extra_sql = $this->genera_columnas_extra(columnas: $columnas, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar columnas', data: $columnas_extra_sql);
        }

        $data = new stdClass();
        $data->columnas_sql = $columnas_sql;
        $data->columnas_extra_sql = $columnas_extra_sql;
        return $data;
    }

    /**
     * Funcion que genera las columnas de una tabla junto con sus relaciones
     * @version 1.48.14
     * @param string $columnas Columnas en forma de SQL para consultas, forma tabla_nombre_campo
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param string $key Tabla a verificar obtencion de sql
     * @param modelo_base $modelo Modelo o tabla de aplicacion
     * @return array|string
     */
    private function genera_columna_tabla(string $columnas, bool $columnas_en_bruto, array $columnas_sql, string $key,
                                          modelo_base $modelo): array|string
    {
        $key = str_replace('models\\','',$key);


        $result = $this->ajusta_columnas_completas(columnas:  $columnas,
            columnas_en_bruto: $columnas_en_bruto, columnas_sql: $columnas_sql, modelo: $modelo,
            tabla: $key,tabla_renombrada:  '');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $result);
        }
        return (string)$result;
    }

    /**
     *
     * Genera las columnas en forma de sql para ser utilizado en un SELECT
     * @version 1.47.14
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @param string $tabla_original nombre del modelo debe de coincidir con una estructura de la base de datos
     * @param string $tabla_renombrada Tabla o renombre de como quedara el AS en SQL de la tabla original
     * @param array $columnas columnas inicializadas a mostrar a peticion en resultado SQL
     * @return array|string
     * @example
     *      $resultado_columnas = $this->genera_columnas_consulta($key,'',$columnas_sql);
     */
    private function genera_columnas_consulta(bool $columnas_en_bruto, modelo_base $modelo, string $tabla_original,
                                              string $tabla_renombrada, array $columnas = array()):array|string{
        $tabla_original = str_replace('models\\','',$tabla_original);
        $class = 'models\\'.$tabla_original;

        if(!class_exists($class)){
            return  $this->error->error(mensaje: 'Error no existe el modelo '.$tabla_original,data: $tabla_original);
        }

        $data = $this->data_for_columnas_envio(columnas:$columnas, columnas_en_bruto:$columnas_en_bruto,
            modelo: $modelo,tabla_original: $tabla_original, tabla_renombrada: $tabla_renombrada);

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
    private function genera_columnas_extra(array $columnas, modelo_base $modelo):array|string{//FIN
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
     *
     * @param modelo_base $modelo modelo o estructura de la base de datos con funcionalidades de ORM
     * @version 1.44.14
     * @param string $tabla_bd Tabla o estructura de una base de datos igual al modelo
     * @return array|stdClass
     */
    private function genera_columnas_field(modelo_base $modelo, string $tabla_bd): array|stdClass
    {
        $tabla_bd = trim($tabla_bd);
        if($tabla_bd === ''){
            return $this->error->error(mensaje: 'Error $tabla_bd esta vacia',data:  $tabla_bd);
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
     *
     * Funcion que genera conjunto de columnas en forma de sql para ser utilizada en un SELECT
     * @version 1.45.14
     * @param array $columnas columnas inicializadas a mostrar a peticion
     * @param string $tabla_original nombre del modelo debe de coincidir con una estructura de la base de datos
     * @param string $tabla_renombrada nombre para renombre de la tabla
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @param bool $columnas_en_bruto Envia columnas tal como estan en base de datos
     * @example
    $columnas_sql = $this->genera_columnas_tabla($tabla_original,$tabla_renombrada, $columnas);
     * @return array|string string en forma de sql con los datos de las columnas a ejecutar SELECT
     * @throws errores $tabla_original no puede venir vacia
     * @throws errores $tabla_original no es una clase o modelo
     */

    private function genera_columnas_tabla(bool $columnas_en_bruto,modelo_base $modelo, string $tabla_original,
                                           string $tabla_renombrada, array $columnas = array()):array|string{
        $tabla_original = str_replace('models\\','',$tabla_original);
        $class = 'models\\'.$tabla_original;
        if($tabla_original === ''){
            return  $this->error->error(mensaje: 'Error tabla original no puede venir vacia', data: $tabla_original);
        }
        if(!class_exists($class)){
            return $this->error->error(mensaje: 'Error no existe el modelo '.$tabla_original, data: $tabla_original);
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
     * Integra las columnas en forma de SQL de forma recursiva
     * @version 1.0.0
     * @param string $columnas Columnas en forma de SQL para consultas, forma tabla_nombre_campo
     * @param string $resultado_columnas Columnas en forma de SQL para consultas, forma tabla_nombre_campo
     * @return stdClass
     */
    #[Pure] private function integra_columnas(string $columnas, string $resultado_columnas): stdClass
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
     * Integra las columnas de un objeto para su sos en un select
     * @version 1.0.0
     * @param string $columnas Columnas en forma de SQL para consultas, forma tabla_nombre_campo
     * @param string $resultado_columnas Columnas en forma de SQL para consultas, forma tabla_nombre_campo
     * @return array|string String con todas las columnas de una tabla
     */
    private function integra_columnas_por_data(string $columnas, string $resultado_columnas):array|string
    {
        $data = $this->integra_columnas(columnas: $columnas, resultado_columnas: $resultado_columnas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $data, params: get_defined_vars());
        }
        return $data->columnas;
    }

    /**
     *
     * Funcion que obtiene todas las columnas de un modelo para su transaformacion en sql, además asigna a una
     *  variable de session para su reutilizacion futura
     * @version 1.44.14
     * @param string $tabla_original nombre del modelo debe de coincidir con una estructura de la base de datos
     * @return array|stdClass conjunto de columnas para la futura transaformacion de un sql
     * @example
     * $columnas_parseadas = $this->obten_columnas($tabla_original);
     */
    public function obten_columnas(modelo_base $modelo, string $tabla_original):array|stdClass{
        $tabla_original = trim(str_replace('models\\','',$tabla_original));
        $tabla_bd = $tabla_original;

        if($tabla_bd === ''){
            return  $this->error->error(mensaje: 'Error tabla original no puede venir vacia',data: $tabla_bd);
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
     * FULL
     * Genera las columnas en forma de sql para ser utilizado en un SELECT de todas las columnas unidas por el modelo
     * @param array $columnas_sql columnas inicializadas a mostrar a peticion en resultado SQL
     * @param array $extension_estructura conjunto de columnas mostradas como extension de datos tablas 1 a 1
     * @param array $renombres conjunto de columnas renombradas
     * @param modelo_base $modelo Modelo con funcionalidad de ORM
     * @param array $columnas_by_table Obtiene solo las columnas de la tabla en ejecucion
     * @return array|string sql con las columnas para un SELECT
     * @throws errores definidos en la maquetacion de las columnas
     * @throws errores $consulta_base->estructura_bd[$this->tabla]['columnas'] no existe
     *@example
     *      $columnas = $this->obten_columnas_completas($columnas);
     */
    public function obten_columnas_completas(modelo_base $modelo, array $columnas_by_table = array(),
                                             bool $columnas_en_bruto = false, array $columnas_sql = array(),
                                             array $extension_estructura = array(),
                                             array $renombres = array()):array|string{


        $tablas_select = (new inicializacion())->tablas_select(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar tablas select',data:  $tablas_select);
        }

        $columnas = $this->columnas_full(columnas_by_table : $columnas_by_table, columnas_en_bruto:$columnas_en_bruto,
            columnas_sql:  $columnas_sql, extension_estructura: $extension_estructura, modelo: $modelo,
            renombres:  $renombres, tablas_select: $tablas_select);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columnas', data: $columnas);
        }

        return $columnas.' ';
    }

    /**
     * FULL
     * Devuelve un conjunto de campos obtenidos de this->sub_querys
     *
     * @param string $columnas
     * @param modelo_base $modelo
     * @param array $columnas_seleccionables
     *
     * @return array|string
     * @example
     *      $sub_querys_sql = $this->sub_querys($columnas);
     */
    public function sub_querys(string $columnas, modelo_base $modelo, array $columnas_seleccionables = array()):array|string{
        $sub_querys_sql = '';
        foreach($modelo->sub_querys as $alias => $sub_query){
            if($sub_query === ''){
                return $this->error->error(mensaje: "Error el sub query no puede venir vacio",
                    data: $modelo->sub_querys, params: get_defined_vars());
            }
            if(trim($alias) === ''){
                return $this->error->error(mensaje:"Error el alias no puede venir vacio", data:$modelo->sub_querys,
                    params: get_defined_vars());
            }
            if(is_numeric($alias)){
                return $this->error->error(mensaje:"Error el alias no puede ser un numero", data:$modelo->sub_querys,
                    params: get_defined_vars());
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

}

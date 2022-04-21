<?php
namespace base\orm;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;
use stdClass;


class joins{

    public errores $error;
    public validacion $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $tabla
     * @return stdClass|array
     */
    private function ajusta_name_model(string $tabla): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error tabla no puede venir vacia', $tabla);
        }

        $tabla = str_replace('models\\','',$tabla);
        $class = 'models\\'.$tabla;

        $data = new stdClass();
        $data->tabla = $tabla;
        $data->name_model = $class;
        return $data;
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $tabla
     * @param string $tabla_enlace
     * @return array|stdClass
     */
    private function ajusta_name_models(string $tabla, string $tabla_enlace): array|stdClass
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error tabla no puede venir vacia', $tabla);
        }
        $tabla_enlace = trim($tabla_enlace);
        if($tabla_enlace === ''){
            return $this->error->error('Error $tabla_enlace no puede venir vacia', $tabla_enlace);
        }

        $data_model_tabla = $this->ajusta_name_model(tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al ajustar nombre del modelo', $data_model_tabla);
        }

        $data_model_tabla_enl = $this->ajusta_name_model(tabla:$tabla_enlace);
        if(errores::$error){
            return $this->error->error('Error al ajustar nombre del modelo', $data_model_tabla_enl);
        }

        $data = new stdClass();
        $data->tabla = $data_model_tabla;
        $data->tabla_enlace = $data_model_tabla_enl;
        return $data;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $tablas_join
     * @param string $tablas Tablas en forma de SQL
     * @return array|string
     */
    private function ajusta_tablas( string $tablas, array $tablas_join): array|string
    {
        $tablas_env = $tablas;
        foreach ($tablas_join as $key=>$tabla_join){
            $tablas_env = $this->data_tabla_sql(key: $key, tabla_join: $tabla_join,tablas:  $tablas);
            if(errores::$error){
                return $this->error->error('Error al generar data join', $tablas_env);
            }
            $tablas = (string)$tablas_env;
        }
        return $tablas_env;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $tabla_join Datos para hacer join con tablas
     * @return stdClass|array
     */
    private function data_join(array $tabla_join): stdClass|array
    {
        $keys = array('tabla_base','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $tabla_join);
        if(errores::$error){
            return $this->error->error('Error al validar $tabla_join',$valida);
        }

        if(!isset($tabla_join['tabla_renombrada'])){
            $tabla_join['tabla_renombrada'] = '';
        }
        $data = new stdClass();
        $data->tabla_base = $tabla_join['tabla_base'];
        $data->tabla_enlace = $tabla_join['tabla_enlace'];
        $data->tabla_renombre = $tabla_join['tabla_renombrada'];
        $data->campo_renombrado = '';
        $data->campo_tabla_base_id  = '';

        if(isset($tabla_join['campo_tabla_base_id'])) {
            $data->campo_tabla_base_id = $tabla_join['campo_tabla_base_id'];
        }
        if(isset($tabla_join['campo_renombrado'])){
            $data->campo_renombrado = $tabla_join['campo_renombrado'];
        }

        return $data;

    }

    /**
     * P ORDER P INT PROBADO
     * @param string $id_renombrada
     * @param stdClass $init
     * @param string $join
     * @param string $renombrada
     * @return stdClass|array
     */
    private function data_for_rename(string $id_renombrada, stdClass $init, string $join,
                                    string $renombrada): stdClass|array
    {
        $keys = array('tabla','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $init);
        if(errores::$error){
            return $this->error->error('Error al validar $init',$valida);
        }

        $join_tabla = $join.' JOIN '.$init->tabla;
        $on_join = $renombrada.$id_renombrada.' = '.$init->tabla_enlace;
        $asignacion_tabla = $join_tabla.' AS '.$renombrada;

        $data = new stdClass();
        $data->join_tabla = $join_tabla;
        $data->on_join = $on_join;
        $data->asignacion_tabla = $asignacion_tabla;
        return $data;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $tabla_join Datos para hacer join con tablas
     * @return array|string
     */
    private function data_para_join(array $tabla_join): array|string
    {
        $keys = array('tabla_base','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys( keys:$keys, registro: $tabla_join);
        if(errores::$error){
            return $this->error->error('Error al validar $tabla_join',$valida);
        }

        $data_join = $this->data_join(tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error('Error al generar data', $data_join);
        }

        $data = $this->genera_join(tabla: $data_join->tabla_base, tabla_enlace: $data_join->tabla_enlace,
            campo_renombrado: $data_join->campo_renombrado, campo_tabla_base_id: $data_join->campo_tabla_base_id,
            renombrada: $data_join->tabla_renombre);
        if(errores::$error){
            return $this->error->error('Error al generar join', $data);
        }
        return $data;
    }

    /**
     * P INT P ORDER PROBADO
     * @param string $key
     * @param string $tabla_join Datos para hacer join con tablas
     * @return array|string
     */
    private function data_para_join_esp(string $key, string $tabla_join): array|string
    {
        $key = trim($key);
        $tabla_join = trim($tabla_join);

        $valida = (new validaciones())->valida_tabla_join(key: $key, tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error('Error al validar join', $valida);
        }

        $data = $this->genera_join(tabla:$key, tabla_enlace: $tabla_join );
        if(errores::$error){
            return $this->error->error('Error al generar join', $data);
        }
        return $data;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array|string $tabla_join
     * @param string $tablas Tablas en forma de SQL
     * @param string $key
     * @return array|string
     */
    private function data_tabla_sql(string $key, array|string $tabla_join, string $tablas): array|string
    {
        $tablas_env = $tablas;
        if(is_array($tabla_join)){
            $tablas_env = $this->tablas_join_base(tabla_join: $tabla_join, tablas: $tablas);
            if(errores::$error){
                return $this->error->error('Error al generar data join', $tablas_env);
            }
        }
        else if ($tabla_join) {
            $tablas_env = $this->tablas_join_esp(key: $key,tabla_join:  $tabla_join, tablas: $tablas);
            if(errores::$error){
                return $this->error->error('Error al generar join', $tablas_env);
            }
        }
        return $tablas_env;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $extension_estructura
     * @param modelo_base $modelo
     * @param string $tablas
     * @return array|string
     */
    private function extensiones_join(array $extension_estructura, modelo_base $modelo, string $tablas): array|string
    {
        $tablas_env = $tablas;
        foreach($extension_estructura as $tabla=>$data){
            if(!is_array($data)){
                return $this->error->error('Error data debe ser un array', $data);
            }
            $valida = (new validaciones())->valida_keys_sql(data: $data, tabla: $modelo->tabla);
            if(errores::$error){
                return $this->error->error('Error al validar data', $valida);
            }
            if(is_numeric($tabla)){
                return $this->error->error('Error $tabla debe ser un texto', $tabla);
            }

            $tablas_env = $this->join_extension(data: $data, modelo: $modelo,tabla:  $tabla, tablas: $tablas);
            if(errores::$error){
                return $this->error->error('Error al generar join', $tablas);
            }
            $tablas = (string)$tablas_env;
        }
        return $tablas_env;
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $campo_tabla_base_id
     * @return string
     */
    private function id_renombrada(string $campo_tabla_base_id): string
    {
        $campo_tabla_base_id = trim($campo_tabla_base_id);
        $id_renombrada = '.id';
        if($campo_tabla_base_id!==''){
            $id_renombrada = '.'.$campo_tabla_base_id;
        }
        return $id_renombrada;
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion para determinar un JOIN entre dos tablas para SQL
     *
     * @param string $campo_tabla_base_id campo base con el nombre del id a tomar tabla_id
     * @param string $tabla  tabla para la ejecucion del JOIN
     * @param string $renombrada renombre de tabla para su salida en sql
     * @param string $tabla_enlace tabla para la union del join LEFT JOIN tabla ON $tabla_enlace
     * @param string $campo_renombrado campo de renombre a su utilizacion en JOIN
     * @example
     *      $tablas = $tablas . $this->genera_join($tabla_base, $tabla_enlace,$tabla_renombre,$campo_renombrado, $campo_tabla_base_id);
     *
     * @return array|string conjunto de joins en forma de SQL
     * @throws errores $tabla vacia
     * @throws errores $tabla_enlace vacio
     * @throws errores $tabla no es una clase de tipo modelo
     */
    private function genera_join(string $tabla, string $tabla_enlace, string $campo_renombrado = '',
                                 string $campo_tabla_base_id = '', string $renombrada = '' ):array|string{

        $tabla = str_replace('models\\','',$tabla);
        $class = 'models\\'.$tabla;
        $tabla_enlace = str_replace('models\\','',$tabla_enlace);

        if($tabla === ''){
            return $this->error->error('La tabla no puede ir vacia', $tabla);
        }
        if($tabla_enlace === ''){
            return $this->error->error('El $tabla_enlace no puede ir vacio', $tabla_enlace);
        }

        $sql = $this->sql_join(campo_renombrado: $campo_renombrado, campo_tabla_base_id: $campo_tabla_base_id,
            class:  $class, renombrada: $renombrada, tabla: $tabla, tabla_enlace: $tabla_enlace);
        if(errores::$error){
            return $this->error->error('Error al genera sql', $sql);
        }

        return $sql;
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion para determinar un JOIN entre dos tablas para SQL
     *
     * @param string $campo_tabla_base_id campo base con el nombre del id a tomar tabla_id
     * @param string $join string tipo de join INNER O LEFT O ETC
     * @param string $tabla  tabla para la ejecucion del JOIN
     * @param string $renombrada renombre de tabla para su salida en sql
     * @param string $tabla_enlace tabla para la union del join LEFT JOIN tabla ON $tabla_enlace
     * @param string $campo_renombrado campo de renombre a su utilizacion en JOIN
     * @example
     *      $sql = $this->genera_join_renombrado($campo_tabla_base_id,$join,$tabla,$renombrada,$tabla_enlace,$campo_renombrado)
     *
     * @return array|string ' '.$join.' JOIN '.$tabla.' AS '.$renombrada.' ON '.$renombrada.$id_renombrada.' = '.$tabla_enlace.'.'.$campo_renombrado
     * @throws errores $tabla vacia
     * @throws errores $join vacio
     * @throws errores $renombrada vacio
     * @throws errores $tabla_enlace vacio
     * @throws errores $campo_renombrado vacio
     * @uses consultas_base->genera_join
     */
    private function genera_join_renombrado(string $campo_renombrado, string $campo_tabla_base_id, string $join,
                                            string $renombrada, string $tabla, string $tabla_enlace):array|string{


        $init = $this->init_renombre(tabla: $tabla, tabla_enlace:$tabla_enlace);
        if(errores::$error){
            return $this->error->error('Error al inicializar ', $init);
        }

        $valida = (new validaciones())->valida_renombres(campo_renombrado: $campo_renombrado, class: $init->class,
            class_enlace: $init->class_enlace,join:  $join, renombrada: $renombrada,tabla:  $init->tabla,
            tabla_enlace:  $init->tabla_enlace);

        if(errores::$error){
            return $this->error->error('Error al validar ', $valida);
        }

        $id_renombrada = $this->id_renombrada(campo_tabla_base_id: $campo_tabla_base_id);
        if(errores::$error){
            return $this->error->error('El al obtener renombrada ', $id_renombrada);
        }

        $data_rename = $this->data_for_rename(id_renombrada: $id_renombrada,init: $init,join: $join,
            renombrada: $renombrada);
        if(errores::$error){
            return $this->error->error('El al obtener datos ', $data_rename);
        }


        return ' '.$data_rename->asignacion_tabla.' ON '.$data_rename->on_join.'.'.$campo_renombrado;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $data
     * @param modelo_base $modelo
     * @param string $tabla
     * @param string $tablas
     * @return array|string
     */
    private function join_extension(array $data, modelo_base $modelo, string $tabla, string $tablas): array|string
    {

        $valida = (new validaciones())->valida_keys_sql(data: $data, tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error('Error al validar data', $valida);
        }

        $left_join = $this->left_join_str(tablas: $tablas);
        if(errores::$error){
            return $this->error->error('Error al generar join', $left_join);
        }

        $tablas.=$left_join;

        $str_join = $this->string_sql_join(data:  $data, modelo: $modelo, tabla: $tabla,tabla_renombrada:  $tabla);
        if(errores::$error){
            return $this->error->error('Error al generar sql', $str_join);
        }

        $tablas .= ' '.$str_join;
        return $tablas;
    }


    /**
     * P INT P ORDER PROBADO
     * @param array $data
     * @param modelo_base $modelo
     * @param string $tabla_renombrada
     * @param string $tablas
     * @return array|string
     */
    private function join_renombres(array $data, modelo_base $modelo, string $tabla_renombrada, string $tablas): array|string
    {
        $namespace = 'models\\';
        $tabla_renombrada = str_replace($namespace,'',$tabla_renombrada);

        $valida = (new validaciones())->valida_keys_renombre(data:$data,tabla_renombrada:  $tabla_renombrada);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        $data['nombre_original'] = trim($data['nombre_original']);
        $tabla_renombrada = trim($tabla_renombrada);

        $data['enlace'] = str_replace($namespace,'',$data['enlace'] );


        $valida = (new validaciones())->valida_keys_sql(data: $data,tabla:  $modelo->tabla);
        if(errores::$error){
            return $this->error->error('Error al validar data', $valida);
        }


        $left_join = $this->left_join_str(tablas: $tablas);
        if(errores::$error){
            return $this->error->error('Error al generar join', $left_join);
        }

        $tablas.=$left_join;

        $str_join = $this->string_sql_join(data:  $data, modelo: $modelo, tabla: $data['nombre_original'],
            tabla_renombrada:  $tabla_renombrada);
        if(errores::$error){
            return $this->error->error('Error al generar sql', $str_join);
        }

        $tablas .= ' '.$str_join;
        return $tablas;
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $tabla
     * @param string $tabla_enlace
     * @return stdClass|array
     */
    private function init_renombre(string $tabla, string $tabla_enlace): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error tabla no puede venir vacia', $tabla);
        }
        $tabla_enlace = trim($tabla_enlace);
        if($tabla_enlace === ''){
            return $this->error->error('Error $tabla_enlace no puede venir vacia', $tabla_enlace);
        }

        $data_models = $this->ajusta_name_models(tabla: $tabla, tabla_enlace: $tabla_enlace);
        if(errores::$error){
            return $this->error->error('Error al ajustar nombre del modelo', $data_models);
        }

        $data = new stdClass();
        $data->tabla = $data_models->tabla->tabla;
        $data->class = $data_models->tabla->name_model;
        $data->tabla_enlace = $data_models->tabla_enlace->tabla;
        $data->class_enlace = $data_models->tabla_enlace->name_model;
        return $data;
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $tablas
     * @return string
     */
    private function left_join_str(string $tablas): string
    {
        $left_join = '';
        if(trim($tablas) !== '') {
            $left_join =' LEFT JOIN ';
        }
        return $left_join;
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion para determinar un JOINs entre dos p mas tablas para SQL
     *
     * @param string $tabla  tabla para la ejecucion del JOIN
     * @param array $columnas_join  array con conjunto de tablas para join
     * @example
     *      $tablas = $consulta_base->obten_tablas_completas($tabla, $this->columnas);
     *
     * @return array|string conjunto de joins en forma de SQL
     * @throws errores $tabla vacia
     */
    public function obten_tablas_completas(array $columnas_join, string $tabla):array|string{
        $tabla = str_replace('models\\','',$tabla);
        $class = 'models\\'.$tabla;
        if($tabla === ''){
            return $this->error->error('La tabla no puede ir vacia', $tabla);
        }
        if(!class_exists($class)){
            return $this->error->error('Error no existe la clase '.$tabla, $tabla);
        }
        $tablas = $tabla.' AS '.$tabla;
        $tablas_join = $columnas_join;

        $tablas = $this->ajusta_tablas(tablas: $tablas, tablas_join: $tablas_join);
        if(errores::$error){
            return $this->error->error('Error al generar data join', $tablas);
        }
        return $tablas;
    }

    /**
     * P INT P ORDER PROBADO
     * @param modelo_base $modelo
     * @param array $renombradas
     * @param string $tablas
     * @return array|string
     */
    private function renombres_join(modelo_base $modelo, array $renombradas, string $tablas): array|string
    {
        $tablas_env = $tablas;
        foreach($renombradas as $tabla_renombrada=>$data){
            if(!is_array($data)){
                return $this->error->error('Error data debe ser un array', $data);
            }
            $tablas_env = $this->join_renombres(data: $data,modelo: $modelo, tabla_renombrada: $tabla_renombrada,
                tablas:  $tablas);
            if(errores::$error){
                return $this->error->error('Error al generar join', $tablas_env);
            }
            $tablas = (string)$tablas_env;

        }
        return $tablas_env;
    }

    /**
     * P INT P ORDER PROBADO
     * @param string $campo_renombrado
     * @param string $campo_tabla_base_id
     * @param string $class
     * @param string $renombrada
     * @param string $tabla
     * @param string $tabla_enlace
     * @return array|string
     */
    private function sql_join(string $campo_renombrado, string $campo_tabla_base_id, string $class, string $renombrada,
                              string $tabla, string $tabla_enlace): array|string
    {
        $join = 'LEFT';
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error $tabla esta vacia', $tabla);
        }
        $tabla_enlace = trim($tabla_enlace);
        if($tabla_enlace === ''){
            return $this->error->error('Error $tabla_enlace esta vacia', $tabla_enlace);
        }

        if($renombrada !==''){
            $sql = $this->genera_join_renombrado(campo_renombrado: $campo_renombrado,
                campo_tabla_base_id: $campo_tabla_base_id,join: $join, renombrada: $renombrada,tabla: $tabla,
                tabla_enlace: $tabla_enlace);
            if(errores::$error ){
                return $this->error->error('Error al generar sql', $sql);
            }
        }
        else {
            if(!class_exists($class)){
                return $this->error->error('No existe la clase', $class);
            }
            $sql = ' '.$join.' JOIN ' . $tabla . ' AS ' . $tabla . ' ON ' . $tabla . '.id = ' . $tabla_enlace . '.'
                . $tabla . '_id';
        }

        return $sql;
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $data
     * @param modelo_base $modelo
     * @param string $tabla
     * @param string $tabla_renombrada
     * @return string|array
     */
    private function string_sql_join( array $data, modelo_base $modelo, string $tabla, string $tabla_renombrada): string|array
    {
        $valida = (new validaciones())->valida_keys_sql(data:$data, tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error('Error al validar data', $valida);
        }
        $tabla = trim($tabla);
        $tabla_renombrada = trim($tabla_renombrada);

        if($tabla === ''){
            return $this->error->error('Error $tabla no puede venir vacia', $tabla);
        }
        if($tabla_renombrada === ''){
            return $this->error->error('Error $tabla_renombrada no puede venir vacia', $tabla_renombrada);
        }

        if(is_numeric($tabla)){
            return $this->error->error('Error $tabla debe ser un texto', $tabla);
        }
        if(is_numeric($tabla_renombrada)){
            return $this->error->error('Error $tabla debe ser un texto', $tabla);
        }

        return "$tabla AS $tabla_renombrada  ON $tabla_renombrada.$data[key] = $data[enlace].$data[key_enlace]";
    }

    /**
     * P INT P ORDER PROBADO
     * @param array $columnas
     * @param array $extension_estructura
     * @param modelo_base $modelo
     * @param array $renombradas
     * @param string $tabla
     * @return array|string
     */
    public function tablas(array $columnas, array $extension_estructura, modelo_base $modelo, array $renombradas,
                           string $tabla): array|string
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('La tabla no puede ir vacia', $tabla);
        }
        $tablas = $this->obten_tablas_completas(columnas_join:  $columnas, tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener tablas', $tablas);
        }

        $tablas = $this->extensiones_join(extension_estructura: $extension_estructura, modelo: $modelo,
            tablas:  $tablas);
        if(errores::$error){
            return $this->error->error('Error al generar join', $tablas);
        }

        $tablas = $this->renombres_join(modelo:$modelo,renombradas: $renombradas, tablas: $tablas);
        if(errores::$error){
            return $this->error->error('Error al generar join', $tablas);
        }
        return $tablas;
    }


    /**
     * P INT P ORDER PROBADO
     * @param array $tabla_join Datos para hacer join con tablas
     * @param string $tablas Tablas en forma de SQL
     * @return array|string
     */
    private function tablas_join_base(array $tabla_join, string $tablas): array|string
    {
        $keys = array('tabla_base','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $tabla_join);
        if(errores::$error){
            return $this->error->error('Error al validar $tabla_join',$valida);
        }

        $data = $this->data_para_join(tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error('Error al generar data join', $data);
        }
        $tablas .=  $data;
        return $tablas;
    }

    /**
     * P INT P ORDER PROBADO
     * @param string $key
     * @param string $tabla_join
     * @param string $tablas
     * @return array|string
     */
    private function tablas_join_esp(string $key, string $tabla_join, string $tablas): array|string
    {
        $key = trim($key);
        $tabla_join = trim($tabla_join);

        $valida = (new validaciones())->valida_tabla_join(key: $key, tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error('Error al validar join', $valida);
        }
        $data = $this->data_para_join_esp(key: $key, tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error('Error al generar join', $data);
        }
        $tablas .=  $data;
        return $tablas;
    }
}
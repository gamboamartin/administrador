<?php
namespace gamboamartin\administrador\modelado;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

/**
 * PRUEBAS FINALIZADAS FUNCIONES REVISADAS FINAL
 */
class joins{

    public errores $error;
    public validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Ajusta el nombre del modelo.
     *
     * Este método recibe el nombre de una tabla y realiza dos operaciones:
     * primero, elimina el prefijo 'models\\\\' del nombre de la tabla si existe,
     * y segundo, genera el nombre completo de la clase para esa tabla, agregándole el prefijo 'models\\\\'.
     *
     * Retorna un objeto de la clase stdClass con dos propiedades: 'tabla', que contiene el nombre de la tabla ya ajustado,
     * y 'name_model', que contiene el nombre completo de la clase para esa tabla.
     *
     * @param string $tabla El nombre original de la tabla. Debe venir con el prefijo 'models\\\\' para ser ajustado.
     *
     * @return stdClass|array Retorna un objeto de la clase stdClass con las propiedades 'tabla' y 'name_model'.
     * Si la tabla está vacía luego de ser eliminados los espacios en blanco, retorna un array con un mensaje de error.
     *
     * @example
     * // Crear una instancia de la clase en la que se encuentra el método
     * $joins = new Joins();
     *
     * // Llamar al método con un nombre de tabla válido
     * $nombreTabla = "models\\\\Usuario";
     * $resultado = $joins->ajusta_name_model($nombreTabla);
     *
     * // $resultado es ahora un objeto con las propiedades 'tabla' y 'name_model'
     * // $resultado->tabla es 'Usuario'
     * // $resultado->name_model es 'models\\\\Usuario'
     *
     * @throws errores Si la tabla está vacía luego de ser eliminados los espacios en blanco, lanza una excepción con un mensaje de error.
     * @version 15.12.0
     */
    private function ajusta_name_model(string $tabla): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla no puede venir vacia', data: $tabla);
        }

        $tabla = str_replace('models\\','',$tabla);
        $class = 'models\\'.$tabla;

        $data = new stdClass();
        $data->tabla = $tabla;
        $data->name_model = $class;
        return $data;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función ajusta los nombres de los modelos de dos tablas dadas.
     *
     * @param string $tabla El nombre de la primera tabla.
     * @param string $tabla_enlace El nombre de la segunda tabla.
     * @return array|stdClass Devuelve un objeto con los nombres de los modelos ajustados de ambas tablas o un error si ocurre alguna excepción.
     *
     * @throws errores Si alguna de las tablas proporcionadas viene vacía, o si hay un error al ajustar los nombres de los modelos.
     *
     * @example
     * // Ejemplo de uso:
     * $ajuste = $instance->ajusta_name_models('usuarios', 'roles');
     * echo $ajuste->tabla; // Muestra el nombre del modelo ajustado para la tabla 'usuarios'
     * echo $ajuste->tabla_enlace; // Muestra el nombre del modelo ajustado para la tabla 'roles'
     * @version 15.12.0
     *
     */
    private function ajusta_name_models(string $tabla, string $tabla_enlace): array|stdClass
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla no puede venir vacia', data: $tabla);
        }
        $tabla_enlace = trim($tabla_enlace);
        if($tabla_enlace === ''){
            return $this->error->error(mensaje: 'Error $tabla_enlace no puede venir vacia', data: $tabla_enlace);
        }

        $data_model_tabla = $this->ajusta_name_model(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar nombre del modelo', data: $data_model_tabla);
        }

        $data_model_tabla_enl = $this->ajusta_name_model(tabla:$tabla_enlace);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar nombre del modelo', data: $data_model_tabla_enl);
        }

        $data = new stdClass();
        $data->tabla = $data_model_tabla;
        $data->tabla_enlace = $data_model_tabla_enl;
        return $data;
    }

    /**
     * Ajusta las tablas para JOIN
     * @param string $tablas Tablas en forma de SQL
     * @param array $tablas_join Datos para hacer join con tablas
     * @return array|string
     */
    private function ajusta_tablas( string $tablas, array $tablas_join): array|string
    {
        $tablas_env = $tablas;
        foreach ($tablas_join as $key=>$tabla_join){
            $tablas_env = $this->data_tabla_sql(key: $key, tabla_join: $tabla_join,tablas:  $tablas);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar data join',data:  $tablas_env);
            }
            $tablas = (string)$tablas_env;
        }
        return $tablas_env;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Realiza la preparación de los datos para una operación de join (unión) de tablas.
     *
     * @param array $tabla_join Un arreglo que contiene las tablas que se van a unir.
     *
     * Los elementos del arreglo son:
     * 'tabla_base'         - La tabla principal con la que se realizará la unión.
     * 'tabla_enlace'       - La tabla secundaria con la que se unirá la tabla principal.
     * 'tabla_renombrada'   - (Opcional) El nuevo nombre que tendrá 'tabla_enlace' después de la unión.
     * 'campo_tabla_base_id'- (Opcional) Nombre del campo de 'tabla_base' que se usará para la unión.
     * 'campo_renombrado'   - (Opcional) Nuevo nombre que se le asignará al campo de 'tabla_enlace' después de la unión.
     *
     * @return stdClass|array Retorna un objeto con la información para la operación de unión de tablas.
     * En caso de error, devuelve un arreglo con la información del error.
     *
     * @example
     *
     * $datosJoin = [
     *     'tabla_base' => 'usuarios',
     *     'tabla_enlace' => 'pedidos',
     *     'tabla_renombrada' => 'ped',
     *     'campo_tabla_base_id' => 'id',
     *     'campo_renombrado' => 'id_pedido'
     * ];
     *
     * $resultado = joins.data_join($datosJoin);
     *
     * // $resultado será un objeto stdClass con la información para realizar la operación de unión de tablas.
     * // En el caso de que la tabla sea renombrada y los campos sean renombrados, el resultado sería algo como:
     *
     * var_dump($resultado);
     *
     * // object(stdClass)#1 (5) {
     * //   ["tabla_base"]=> string(8) "usuarios"
     * //   ["tabla_enlace"]=> string(7) "pedidos"
     * //   ["tabla_renombre"]=> string(3) "ped"
     * //   ["campo_renombrado"]=> string(9) "id_pedido"
     * //   ["campo_tabla_base_id"]=> string(2) "id"
     * // }
     *
     * @throws errores Si 'tabla_base' y 'tabla_enlace' no están establecidos dentro del array $tabla_join.
     * @version 15.7.0
     *
     */
    private function data_join(array $tabla_join): stdClass|array
    {
        $keys = array('tabla_base','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $tabla_join',data: $valida);
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
     * POR DOCUMENTAR EN WIKI
     * Genera la información requerida para realizar un "JOIN" en una consulta SQL.
     *
     * @param string $id_renombrada El identificador de la tabla renombrada.
     * @param stdClass $init Contiene los datos iniciales necesarios para crear el "JOIN".
     * @param string $join Indica el tipo de "JOIN" a realizar (INNER JOIN, LEFT JOIN, RIGHT JOIN, etc).
     * @param string $renombrada El nombre que se le asignará a la tabla después del renombramiento.
     *
     * @return stdClass|array Retorna un objeto stdClass con la descripción del "JOIN" a realizar,
     * o un arreglo en caso de que se produzca un error.
     *
     * El objeto retornado tiene la siguiente estructura:
     * - 'join_tabla': La declaración del "JOIN" realizada con la tabla y su alias.
     * - 'on_join': La condición bajo la cual se realizará el "JOIN".
     * - 'asignacion_tabla': La asignación de renombramiento de la tabla.
     * @version 15.40.1
     */
    private function data_for_rename(string $id_renombrada, stdClass $init, string $join,
                                    string $renombrada): stdClass|array
    {
        $keys = array('tabla','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $init);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $init',data: $valida);
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
     * Genera los datos para proceder con la configuracion de un JOIN en sql
     * @param array $tabla_join Datos para hacer join con tablas
     * @return array|string
     */
    private function data_para_join(array $tabla_join): array|string
    {
        $keys = array('tabla_base','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys( keys:$keys, registro: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $tabla_join',data: $valida);
        }

        $data_join = $this->data_join(tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data',data:  $data_join);
        }

        $data = $this->genera_join(tabla: $data_join->tabla_base, tabla_enlace: $data_join->tabla_enlace,
            campo_renombrado: $data_join->campo_renombrado, campo_tabla_base_id: $data_join->campo_tabla_base_id,
            renombrada: $data_join->tabla_renombre);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join', data: $data);
        }
        return $data;
    }

    /**
     * Obtiene los parametros necesarios para generar un join
     * @version 1.60.17
     * @param string $key Tabla left
     * @param string $tabla_join Datos para hacer join con tablas
     * @return array|string
     */
    private function data_para_join_esp(string $key, string $tabla_join): array|string
    {
        $key = trim($key);
        $tabla_join = trim($tabla_join);

        $valida = (new validaciones())->valida_tabla_join(key: $key, tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar join', data: $valida);
        }

        $data = $this->genera_join(tabla:$key, tabla_enlace: $tabla_join );
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar join', data:$data);
        }
        return $data;
    }

    /**
     * Obtiene las tablas para hacer un join
     * @param string $key Tabla LEFT
     * @param array|string $tabla_join Datos para hacer join con tablas
     * @param string $tablas Tablas en forma de SQL
     * @return array|string
     */
    private function data_tabla_sql(string $key, array|string $tabla_join, string $tablas): array|string
    {
        $tablas_env = $tablas;
        if(is_array($tabla_join)){
            $tablas_env = $this->tablas_join_base(tabla_join: $tabla_join, tablas: $tablas);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar data join', data: $tablas_env);
            }
        }
        else if ($tabla_join) {
            $tablas_env = $this->tablas_join_esp(key: $key,tabla_join:  $tabla_join, tablas: $tablas);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar join', data: $tablas_env);
            }
        }
        return $tablas_env;
    }

    /**
     * Genera los JOINS de extensiones de tablas 1 a 1
     * @param array $extension_estructura columnas estructura tabla ligada 1 a 1
     * @param string $modelo_tabla
     * @param string $tablas Tablas en JOIN SQL
     * @return array|string
     * @version 1.63.17
     */
    private function extensiones_join(array $extension_estructura, string $modelo_tabla, string $tablas): array|string
    {
        $tablas_env = $tablas;
        foreach($extension_estructura as $tabla=>$data){
            if(!is_array($data)){
                return $this->error->error(mensaje: 'Error data debe ser un array', data: $data);
            }
            $valida = (new validaciones())->valida_keys_sql(data: $data, tabla: $modelo_tabla);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al validar data', data:$valida);
            }
            if(is_numeric($tabla)){
                return $this->error->error(mensaje:'Error $tabla debe ser un texto', data:$tabla);
            }

            $tablas_env = $this->join_base(data: $data, modelo_tabla: $modelo_tabla,tabla:  $tabla, tablas: $tablas);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al generar join',data: $tablas);
            }
            $tablas = (string)$tablas_env;
        }
        return $tablas_env;
    }



    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función renombra un `id` en la tabla base de acuerdo al valor proporcionado.
     *
     * @param string $campo_tabla_base_id Es el nombre que se asignará al `id` de la tabla base.
     * Si este parámetro está vacío, se asignará el valor '.id' por defecto.
     *
     * @return string Retorna la `id` de la tabla base modificada.
     * Si el parámetro de entrada está vacío, retorna '.id', en otro caso concatena '.' al principio
     * del valor de entrada, y lo retorna.
     *
     * Ejemplo:
     *
     * code:
     * // el valor del $campo_tabla_base_id es 'user'
     * $id_renombrada = id_renombrada('user');
     * echo $id_renombrada;
     * // Retorna: '.user'
     *
     * // el valor del $campo_tabla_base_id es ''
     * $id_renombrada2 = id_renombrada('');
     * echo $id_renombrada2;
     * // Retorna: '.id'
     * @version 15.34.1
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
     *
     * Funcion para determinar un JOIN entre dos tablas para SQL
     * @param string $campo_tabla_base_id campo base con el nombre del id a tomar tabla_id
     * @param string $tabla  tabla para la ejecucion del JOIN
     * @param string $renombrada renombre de tabla para su salida en sql
     * @param string $tabla_enlace tabla para la union del join LEFT JOIN tabla ON $tabla_enlace
     * @param string $campo_renombrado campo de renombre a su utilizacion en JOIN
     * @example
     *      $tablas = $tablas . $this->genera_join($tabla_base, $tabla_enlace,$tabla_renombre,$campo_renombrado,
     *          $campo_tabla_base_id);
     *
     * @return array|string conjunto de joins en forma de SQL
     * @throws errores $tabla vacia
     * @throws errores $tabla_enlace vacio
     * @throws errores $tabla no es una clase de tipo modelo
     */
    private function genera_join(string $tabla, string $tabla_enlace, string $campo_renombrado = '',
                                 string $campo_tabla_base_id = '', string $renombrada = '' ):array|string{

        $tabla = str_replace('models\\','',$tabla);
        $tabla_enlace = str_replace('models\\','',$tabla_enlace);

        if($tabla === ''){
            return $this->error->error(mensaje: 'La tabla no puede ir vacia', data: $tabla);
        }
        if($tabla_enlace === ''){
            return $this->error->error(mensaje: 'El $tabla_enlace no puede ir vacio', data: $tabla_enlace);
        }

        $sql = $this->sql_join(campo_renombrado: $campo_renombrado, campo_tabla_base_id: $campo_tabla_base_id,
            renombrada: $renombrada, tabla: $tabla, tabla_enlace: $tabla_enlace);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al genera sql',data:  $sql);
        }

        return $sql;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Genera una cláusula JOIN personalizada en una consulta SQL.
     *
     * @param string $campo_renombrado El nombre del campo que se va a renombrar.
     * @param string $campo_tabla_base_id El nombre del campo en la tabla base.
     * @param string $join La cláusula JOIN que se va a utilizar (por ejemplo, LEFT JOIN, INNER JOIN, etc.).
     * @param string $renombrada El nombre de la tabla donde se encuentra el campo que se va a renombrar.
     * @param string $tabla La tabla base para la cláusula JOIN.
     * @param string $tabla_enlace La tabla que se va a unir a la tabla base.
     *
     * @return array|string Si se genera un error durante el proceso, se devuelve un mensaje de error. Si todo va bien, se devuelve la cláusula JOIN generada.
     * @version 15.41.1
     */
    private function genera_join_renombrado(string $campo_renombrado, string $campo_tabla_base_id, string $join,
                                            string $renombrada, string $tabla, string $tabla_enlace):array|string{


        $init = $this->init_renombre(tabla: $tabla, tabla_enlace:$tabla_enlace);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar ', data: $init);
        }

        $valida = (new validaciones())->valida_renombres(campo_renombrado: $campo_renombrado,join:  $join,
            renombrada: $renombrada,tabla:  $init->tabla,
            tabla_enlace:  $init->tabla_enlace);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar ', data: $valida);
        }

        $id_renombrada = $this->id_renombrada(campo_tabla_base_id: $campo_tabla_base_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'El al obtener renombrada ',data:  $id_renombrada);
        }

        $data_rename = $this->data_for_rename(id_renombrada: $id_renombrada,init: $init,join: $join,
            renombrada: $renombrada);
        if(errores::$error){
            return $this->error->error(mensaje: 'El al obtener datos ', data: $data_rename);
        }


        return ' '.$data_rename->asignacion_tabla.' ON '.$data_rename->on_join.'.'.$campo_renombrado;
    }

    /**
     * Genera los JOINS de una extension 1 a 1
     * @param array $data data[key,enlace,key_enlace] datos para genera JOIN
     * @param string $modelo_tabla
     * @param string $tabla Tabla en LEFT
     * @param string $tablas Tablas en JOIN SQL
     * @return array|string tabla as tabla ON tabla.data[key] = data[enlace].data[key_enlace]
     */
    private function join_base(array $data, string $modelo_tabla, string $tabla, string $tablas): array|string
    {
        $valida = (new validaciones())->valida_keys_sql(data: $data, tabla: $modelo_tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data',data:  $valida);
        }

        if($tabla === ''){
            return $this->error->error(mensaje:'Error $tabla no puede venir vacia', data:$tabla);
        }

        $left_join = $this->left_join_str(tablas: $tablas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join', data:$left_join);
        }

        $tablas.=$left_join;

        $tabla_renombrada = $tabla;
        if(isset($data['renombre'])){
            $data['renombre'] = trim($data['renombre']);
            if($data['renombre'] !== ''){
                $tabla_renombrada = $data['renombre'];
            }

        }

        $str_join = $this->string_sql_join(data:  $data, modelo_tabla: $modelo_tabla, tabla: $tabla,
            tabla_renombrada:  $tabla_renombrada);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql', data:$str_join);
        }

        $tablas .= ' '.$str_join;
        return $tablas;
    }


    /**
     * Genera joins renombrados
     * @param array $data $data[enlace,nombre_original,key_enlace] Datos para JOIN
     * @param string $modelo_tabla
     * @param string $tabla_renombrada nombre nuevo de la tabla
     * @param string $tablas Conjunto de tablas cargadas en SQL
     * @return array|string
     * @version 1.66.17
     */
    private function join_renombres(array $data, string $modelo_tabla, string $tabla_renombrada,
                                    string $tablas): array|string
    {
        $namespace = 'models\\';
        $tabla_renombrada = str_replace($namespace,'',$tabla_renombrada);

        $valida = (new validaciones())->valida_keys_renombre(data:$data,tabla_renombrada:  $tabla_renombrada);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $data['nombre_original'] = trim($data['nombre_original']);
        $tabla_renombrada = trim($tabla_renombrada);

        $data['enlace'] = str_replace($namespace,'',$data['enlace'] );


        $valida = (new validaciones())->valida_keys_sql(data: $data,tabla:  $modelo_tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar data',data: $valida);
        }


        $left_join = $this->left_join_str(tablas: $tablas);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar join', data:$left_join);
        }

        $tablas.=$left_join;

        $str_join = $this->string_sql_join(data:  $data, modelo_tabla: $modelo_tabla, tabla: $data['nombre_original'],
            tabla_renombrada:  $tabla_renombrada);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data: $str_join);
        }

        $tablas .= ' '.$str_join;
        return $tablas;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Función para inicializar y renombrar tablas y modelos en una operación join.
     *
     * Esta función toma como entrada cuatro argumentos los que corresponden a nombres de tablas y devolviéndolos en un
     * objeto estandar de PHP o un array en caso de error.
     *
     * @param string $tabla Un nombre de tabla para renombrar.
     * @param string $tabla_enlace Un nombre de tabla de enlace para renombrar.
     *
     * @return stdClass|array Devuelve un objeto stdClass en caso de éxito con las propiedades 'tabla', 'class', 'tabla_enlace',
     * y 'class_enlace' establecidas a sus nuevos nombres correspondientes, cada uno de tipo string.
     * En caso de error, se devuelve un array con información relacionada con el error.
     *
     * @throws errores se lanza ninguna excepción explícitamente, sin embargo, internamente maneja los errores llamando al
     * método error del objeto error si ocurren durante el proceso de renombrado.
     *
     * @example
     * <code>
     * // Crear un objeto de la clase que contiene init_renombre
     * $obj = new Joins();
     * // Llamar a alguna función pública que a su vez llama a init_renombre
     * $array = ['tabla' => 'nombre_tabla', 'tabla_enlace' => 'nombre_tabla_enlace'];
     * $resultado = $obj->algunMetodoPublico($array);
     * </code>
     *
     * @version 15.28.0
     */
    private function init_renombre(string $tabla, string $tabla_enlace): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla no puede venir vacia', data: $tabla);
        }
        $tabla_enlace = trim($tabla_enlace);
        if($tabla_enlace === ''){
            return $this->error->error(mensaje: 'Error $tabla_enlace no puede venir vacia', data: $tabla_enlace);
        }

        $data_models = $this->ajusta_name_models(tabla: $tabla, tabla_enlace: $tabla_enlace);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ajustar nombre del modelo', data: $data_models);
        }

        $data = new stdClass();
        $data->tabla = $data_models->tabla->tabla;
        $data->class = $data_models->tabla->name_model;
        $data->tabla_enlace = $data_models->tabla_enlace->tabla;
        $data->class_enlace = $data_models->tabla_enlace->name_model;
        return $data;
    }

    /**
     * Integra LEFT JOIN en SQL
     * @param string $tablas Tablas en JOIN SQL
     * @version 1.62.17
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
     *
     * Funcion para determinar un JOINs entre dos p mas tablas para SQL
     * @param string $tabla  tabla para la ejecucion del JOIN
     * @param array $columnas_join  array con conjunto de tablas para join
     * @example
     *      $tablas = $consulta_base->obten_tablas_completas($tabla, $this->columnas);
     *
     * @return array|string conjunto de joins en forma de SQL
     * @throws errores $tabla vacia
     */
    final public function obten_tablas_completas(array $columnas_join, string $tabla):array|string{
        $tabla = str_replace('models\\','',$tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'La tabla no puede ir vacia', data: $tabla);
        }

        $tablas = $tabla.' AS '.$tabla;
        $tablas_join = $columnas_join;

        $tablas = $this->ajusta_tablas(tablas: $tablas, tablas_join: $tablas_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data join', data: $tablas);
        }
        return $tablas;
    }

    /**
     * Genera renombres de tablas en sql
     * @param string $modelo_tabla
     * @param array $renombradas conjunto de tablas renombradas
     * @param string $tablas Tablas en JOIN SQL
     * @return array|string
     * @version 1.66.17
     */
    private function renombres_join(string $modelo_tabla, array $renombradas, string $tablas): array|string
    {
        $tablas_env = $tablas;
        foreach($renombradas as $tabla_renombrada=>$data){
            if(!is_array($data)){
                return $this->error->error(mensaje: 'Error data debe ser un array', data: $data);
            }
            $tablas_env = $this->join_renombres(data: $data,modelo_tabla: $modelo_tabla,
                tabla_renombrada: $tabla_renombrada, tablas:  $tablas);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al generar join', data:$tablas_env);
            }
            $tablas = (string)$tablas_env;

        }
        return $tablas_env;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función se utiliza para generar una declaración de Join SQL.
     *
     * @param string $campo_renombrado  El campo a renombrar.
     * @param string $campo_tabla_base_id El campo ID de la tabla base.
     * @param string $renombrada  La tabla a renombrar.
     * @param string $tabla  La tabla para unirse.
     * @param string $tabla_enlace  La tabla de enlace.
     *
     * @return array|string  Devuelve una cadena SQL de Join o un error si hay problemas.
     *
     * @throws errores Dispara una excepción si la tabla o tabla de enlace están vacías.
     *
     * Estructura del código de error:
     * [
     *    'mensaje' => string, // Descripción del error
     *    'data' => mixed, // Datos relacionados con el error
     * ]
     *
     * Ejemplo de uso:
     *
     * $result = $instance->sql_join("campo1", "id", "tabla1", "tabla2", "tabla3");
     * if (is_array($result)) {
     *     // Manejo de error
     *     var_dump($result);
     * } else {
     *     // Uso del resultado de la consulta
     *     var_dump($result);
     * }
     *
     * Posibles resultados:
     *
     * 1) Devuelve una cadena SQL de Join en caso de éxito. Por ejemplo :
     *    ' LEFT JOIN tabla2 AS tabla2 ON tabla2.id = tabla3.tabla2_id'
     *
     * 2) Devuelve un error si alguno de los nombres de las tablas ($tabla, $tabla_enlace) está vacío. Por ejemplo :
     *    [
     *       'mensaje' => 'Error $tabla esta vacia',
     *       'data' => '   '
     *    ]
     * @version 15.42.1
     */
    private function sql_join(string $campo_renombrado, string $campo_tabla_base_id, string $renombrada, string $tabla,
                              string $tabla_enlace): array|string
    {
        $join = 'LEFT';
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error $tabla esta vacia',data:  $tabla);
        }
        $tabla_enlace = trim($tabla_enlace);
        if($tabla_enlace === ''){
            return $this->error->error(mensaje: 'Error $tabla_enlace esta vacia', data: $tabla_enlace);
        }

        if($renombrada !==''){
            $sql = $this->genera_join_renombrado(campo_renombrado: $campo_renombrado,
                campo_tabla_base_id: $campo_tabla_base_id,join: $join, renombrada: $renombrada,tabla: $tabla,
                tabla_enlace: $tabla_enlace);
            if(errores::$error ){
                return $this->error->error(mensaje: 'Error al generar sql', data: $sql);
            }
        }
        else {

            $sql = ' '.$join.' JOIN ' . $tabla . ' AS ' . $tabla . ' ON ' . $tabla . '.id = ' . $tabla_enlace . '.'
                . $tabla . '_id';
        }

        return $sql;
    }

    /**
     * Genera el SQL PARA joins
     * @param array $data data[key,enlace,key_enlace] datos para genera JOIN
     * @param string $modelo_tabla
     * @param string $tabla Tabla en LEFT
     * @param string $tabla_renombrada Tabla con nuevo nombre se aplica en AS
     * @return string|array
     * @version 1.63.17
     */
    private function string_sql_join( array $data, string $modelo_tabla, string $tabla,
                                      string $tabla_renombrada): string|array
    {
        $valida = (new validaciones())->valida_keys_sql(data:$data, tabla: $modelo_tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }
        $tabla = trim($tabla);
        $tabla_renombrada = trim($tabla_renombrada);

        if($tabla === ''){
            return $this->error->error(mensaje:'Error $tabla no puede venir vacia', data:$tabla);
        }
        if($tabla_renombrada === ''){
            return $this->error->error(mensaje:'Error $tabla_renombrada no puede venir vacia', data:$tabla_renombrada);
        }

        if(is_numeric($tabla)){
            return $this->error->error(mensaje:'Error $tabla debe ser un texto', data:$tabla);
        }
        if(is_numeric($tabla_renombrada)){
            return $this->error->error(mensaje:'Error $tabla debe ser un texto', data:$tabla);
        }

        return "$tabla AS $tabla_renombrada  ON $tabla_renombrada.$data[key] = $data[enlace].$data[key_enlace]";
    }

    /**
     * Obtiene los joins de todas las tablas de un modelo
     * @param array $columnas conjunto de tablas para realizar los joins
     * @param array $extension_estructura columnas estructura tabla ligada 1 a 1
     * @param array $extra_join Join extra a peticion en funciones
     * @param string $modelo_tabla
     * @param array $renombradas conjunto de tablas renombradas
     * @param string $tabla Tabla con el nombre original
     * @return array|string
     */
    final public function tablas(array $columnas, array $extension_estructura, array $extra_join, string $modelo_tabla,
                                 array $renombradas, string $tabla): array|string
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'La tabla no puede ir vacia',data:  $tabla);
        }
        $tablas = $this->obten_tablas_completas(columnas_join:  $columnas, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener tablas',data:  $tablas);
        }

        $tablas = $this->extensiones_join(extension_estructura: $extension_estructura, modelo_tabla: $modelo_tabla,
            tablas:  $tablas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join',data:  $tablas);
        }

        $tablas = $this->extensiones_join(extension_estructura: $extra_join, modelo_tabla: $modelo_tabla,
            tablas:  $tablas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join',data:  $tablas);
        }

        $tablas = $this->renombres_join(modelo_tabla:$modelo_tabla,renombradas: $renombradas, tablas: $tablas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join', data: $tablas);
        }
        return $tablas;
    }


    /**
     * Genera la configuracion base de JOINS
     * @param array $tabla_join Datos para hacer join con tablas
     * @param string $tablas Tablas en forma de SQL
     * @return array|string
     */
    private function tablas_join_base(array $tabla_join, string $tablas): array|string
    {
        $keys = array('tabla_base','tabla_enlace');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $tabla_join',data: $valida);
        }

        $data = $this->data_para_join(tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data join', data: $data);
        }
        $tablas .=  $data;
        return $tablas;
    }

    /**
     *
     * @param string $key Key tabla
     * @version 1.60.17
     * @param string $tabla_join Tabla para join
     * @param string $tablas conjunto de tablas previamente cargadas
     * @return array|string
     */
    private function tablas_join_esp(string $key, string $tabla_join, string $tablas): array|string
    {
        $key = trim($key);
        $tabla_join = trim($tabla_join);

        $valida = (new validaciones())->valida_tabla_join(key: $key, tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar join', data: $valida);
        }
        $data = $this->data_para_join_esp(key: $key, tabla_join: $tabla_join);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar join',data:  $data);
        }
        $tablas .=  $data;
        return $tablas;
    }
}
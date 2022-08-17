<?php
namespace base\orm;

use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use PDO;
use PDOStatement;
use stdClass;
use Throwable;


class modelo_base{ //PRUEBAS EN PROCESO //DOCUMENTACION EN PROCESO
    public string $consulta = '';
    public errores $error ;
    public PDO $link ;
    public string $transaccion = '' ;
    public int $registro_id = -1 ;
    public string  $tabla = '' ;
    public array $registro = array();
    public array $patterns = array();
    public array $hijo = array();
    public array $filtro = array();
    public int $usuario_id = -1;
    public string $campos_sql = '';
    public array $registro_upd = array();
    public array $columnas_extra = array();
    public array $columnas = array();
    public array $sub_querys = array();
    public array $campos_obligatorios=array('status');
    public array $tipo_campos = array();

    public base_modelos     $validacion;
    public string $status_default = 'activo';
    public bool $aplica_bitacora = false;
    public array $filtro_seguridad = array();
    public bool $aplica_seguridad = false;
    public array $registros = array();
    public stdClass $row;
    public int $n_registros;
    public string $sql;
    public stdClass $data_columnas;
    public array $models_dependientes = array();
    public bool $desactiva_dependientes = false;
    public bool $elimina_dependientes = false;
    public array $keys_data_filter;
    public array $no_duplicados = array();

    public string $key_id = '';
    public string $key_filtro_id = '';



    /**
     * @param PDO $link Conexion a la BD
     */
    #[Pure] public function __construct(PDO $link){ //PRUEBAS EN PROCESO
        $this->error = new errores();
        $this->link = $link;
        $this->validacion = new base_modelos();


        $this->patterns['double'] = "/^\\$?[1-9]+,?([0-9]*,?[0,9]*)*.?[0-9]{0,4}$/";
        $this->patterns['double_con_cero'] = "/^[0-9]+[0-9]*.?[0-9]{0,4}$/";
        $this->patterns['telefono'] = "/^[0-9]{10}$/";
        $this->patterns['id'] = "/^[1-9]+[0-9]*$/";

        $this->keys_data_filter = array('sentencia','filtro_especial','filtro_rango','filtro_extra','not_in','sql_extra','filtro_fecha');
    }

    /**
     *
     * Devuelve una cadena que comprueba la existencia del usuario que realiza la modificacion asignando su id a la
     * columna usuario_update_id
     *
     * @example
     *      $this->campos_sql = $campos_sql;
     *      $campos_sql = $this->agrega_usuario_session();
     *
     * @return array|string
     * @throws errores $this->usuario_id en menor o igual a cero
     * @throws errores $this->campos_sql estan vacios
     * @throws errores definidas dentro de las funciones utilization
     * @throws errores si el resultado de la funcion $this->usuario_existente() demuestra que no existe el usuario
     *
     * @uses modelos->modifica_bd();
     * @uses modelos->modifica_por_id();
     * @internal $this->usuario_existente();
     * @version 1.287.41
     */
    protected function agrega_usuario_session(): array|string
    {
        if($this->usuario_id <=0){
            return $this->error->error(mensaje: 'Error usuario invalido no esta logueado',data: $this->usuario_id);
        }

        if($this->campos_sql === ''){
            return $this->error->error(mensaje: 'campos no puede venir vacio',data: $this->campos_sql);
        }
        $existe_user = $this->usuario_existente();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error validar existencia de usuario',data: $existe_user);
        }
        if(!$existe_user){
            return $this->error->error(mensaje: 'Error no existe usuario',
                data: array($existe_user,$this->campos_sql, $this->usuario_id));
        }

        $this->campos_sql .= ',usuario_update_id=' . $this->usuario_id;


        return $this->campos_sql;
    }

    /**
     * PHPUNIT
     * @param string $name_modelo
     * @return string|array
     */
    private function ajusta_modelo_comp(string $name_modelo): string|array
    {
        $name_modelo = trim($name_modelo);
        if($name_modelo === ''){
            return $this->error->error('Error name_modelo no puede venir vacio', $name_modelo);
        }
        $name_modelo = str_replace('models\\','',$name_modelo);
        $name_modelo = 'models\\'.$name_modelo;

        if($name_modelo === 'models\\'){
            return $this->error->error('Error name_modelo no puede venir vacio', $name_modelo);
        }
        return trim($name_modelo);
    }

    /**
     * Ajusta el contenido de un registro asignando valores encriptados y elementos con dependencia basada en modelos
     * hijos
     * @version 1.22.10
     * @param array $campos_encriptados Conjunto de campos a encriptar desencriptar declarados en el modelo en ejecucion
     * @param array $modelos_hijos Conjunto de modelos que dependen del modelo en ejecucion
     * @param array $row Registro a integrar elementos encriptados o con dependientes
     * @return array Registro con los datos ajustados tanto en la encriptacion como de sus dependientes
     */
    private function ajusta_row_select(array $campos_encriptados, array $modelos_hijos, array $row): array
    {
        $row = (new inicializacion())->asigna_valor_desencriptado(campos_encriptados: $campos_encriptados,
            row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al desencriptar', data:$row);
        }


        if(count($modelos_hijos)>0) {
            $row = $this->genera_registros_hijos(modelos_hijos: $modelos_hijos,row:  $row);
            if (errores::$error) {
                return $this->error->error(mensaje: "Error en registro",data: $row);
            }
        }
        return $row;
    }



    /**
     * PHPUNIT
     * @return array
     */
    protected function aplica_desactivacion_dependencias(): array
    {

        $data = array();
        if($this->desactiva_dependientes) {
            $desactiva = $this->desactiva_data_modelos_dependientes();
            if (errores::$error) {
                return $this->error->error('Error al desactivar dependiente', $desactiva);
            }
            $data = $desactiva;
        }
        return $data;
    }

    /**
     * PHPUNIT
     * Ordena un arreglo por un key
     *
     * @param array $array_ini arreglo a ordenar
     * @param string $col columnas a ordenar
     * @param  mixed $order tipo de ordenamiento
     * @example
     *      $movimientos = $this->array_sort_by($movimientos,'fecha');
     *
     * @return array arreglo ordenado
     * @throws errores !isset($row[$col]
     * @uses producto
     */
    protected function array_sort_by(array $array_ini, string $col,  mixed $order = SORT_ASC): array
    {
        $col = trim($col);
        if($col===''){
            return $this->error->error('Error col esta vacio', $col);
        }
        $arr_aux = array();
        foreach ($array_ini as $key=> $row) {
            if(!isset($row[$col])){
                return $this->error->error('Error no existe el $key '.$col, $row);
            }
            if(is_object($row)){
                $arr_aux[$key] = $row->$col;
            }
            else{
                $arr_aux[$key] = $row[$col];
            }

            $arr_aux[$key] = strtolower($arr_aux[$key]);
        }
        array_multisort($arr_aux, $order, $array_ini);
        return $array_ini;
    }

    /**
     * PHPUNIT
     * Devuelve una cadena de ceros con una longitud definida entre la cantidad de digits ingresados y el limite de
     * digitos que requiere en el codigo
     *
     * @param int $longitud es un digito que indica el inicio a partir del cual se concatenaran los ceros faltantes hasta
     * el limite
     * @param int $total_cadena es un digito que indica la cantidad total de caracteres
     * @example
     *      $resultado = asigna_cero_codigo(1,10);
     *      //return $ceros = '000000000';
     *
     * @return array|string
     * @throws errores Si $longitud es menor a 0
     * @example
     *      $resultado = asigna_cero_codigo(-1,10);
     *      //return array errores
     * @throws errores Si $total_cadena es menor a 0
     * @example
     *      $resultado = asigna_cero_codigo(10,-1);
     *      //return array errores
     */
    public function asigna_cero_codigo(int $longitud, int $total_cadena): array|string
    {//FIN Y DOC
        if($longitud<0){
            return $this->error->error('Error $longitud debe ser mayor a 0',$longitud);
        }
        if($total_cadena<0){
            return $this->error->error('Error $total_cadena debe ser mayor a 0',$total_cadena);
        }
        $ceros = '';
        for($i = $longitud; $i<$total_cadena; $i++){
            $ceros.='0';
        }
        return $ceros;
    }





    /**
     *
     * Funcion que asigna un registro encontrado para hijos en las diferentes consultas
     * @version 1.16.9
     *
     * @param string $name_modelo txt con el nombre del modelo para la asignacion del registro
     * @param array $filtro datos para la obtencion de los registros a filtrar
     * @param array $row registro padre al que se le asignaran los hijos
     * @param string  $nombre_estructura nombre del modelo hijo
     * @example
     *     $row = $this->asigna_registros_hijo($name_modelo,$filtro,$row, $data_modelo['nombre_estructura']);
     * @return array conjunto de registros encontrados al registro row


     */
    private function asigna_registros_hijo(array $filtro, string $name_modelo, string $nombre_estructura, array $row):array{
        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar entrada para modelo',data: $valida);
        }
        if($nombre_estructura === ''){
            return  $this->error->error(mensaje: 'Error nombre estructura no puede venir vacia',
                data: $nombre_estructura);
        }

        $modelo = $this->genera_modelo(modelo: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data: $modelo);
        }
        $data = $modelo->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar registro hijos', data: $data);
        }
        $row[$nombre_estructura] = $data->registros;


        return $row;
    }






    private function modelo_dependiente_val(string $modelo_dependiente): array|string
    {
        $modelo_dependiente_ajustado = $this->ajusta_modelo_comp($modelo_dependiente);
        if(errores::$error ){
            return  $this->error->error('Error al ajustar modelo',$modelo_dependiente);
        }

        $valida = $this->valida_data_desactiva($modelo_dependiente_ajustado);
        if(errores::$error){
            return $this->error->error('Error al validar modelos',$valida);
        }

        return $modelo_dependiente_ajustado;
    }

    /**
     *
     * Devuelve el registro ya validado en la posicion de codigo
     *
     * @param array $registros registro a revisar
     * @param string $key cadena de texto que indica la posicion del registro
     * @example
     *      $valida_base = $this->valida_base_ultimo_codigo($registros,$key);
     *
     * @return array
     * @throws errores $registros['registros'] debe existir
     * @throws errores $registros['registros'][0] debe existir
     * @throws errores $key no puede venir vacio
     *
     * @uses modelo_basico->genera_ultimo_codigo_base_numero()
     * @uses modelo_basico->obten_ultimo_codigo_insert()
     *
     */
    private function valida_base_ultimo_codigo(array $registros, string $key):array{
        if(!isset($registros['registros'])){
            return $this->error->error('Error no existe registros en registro',$registros);
        }
        if(!isset($registros['registros'][0])){
            return $this->error->error('Error no existe registros[registro][0]',$registros);
        }
        if($key === ''){
            return $this->error->error('Error no existe key no puede venir vacio',$key);
        }
        return $registros;
    }

    /**
     * PHPUNIT
     * @param array $registros
     * @param string $key
     * @return array|int
     */
    private function obten_ultimo_codigo_insert(array $registros, string $key): array|int
    {

        $valida_base = $this->valida_base_ultimo_codigo($registros,$key);
        if(errores::$error){
            return $this->error->error('Error al validar',$valida_base);
        }

        $registro  = $registros['registros'][0];

        if(!isset($registro[$key])){
            return $this->error->error('Error no existe $registro['.$key.']',$registro);
        }


        $ultimo_codigo = (int)$registro[$key];


        $ultimo_codigo_upd = $this->genera_ultimo_codigo_int($ultimo_codigo);
        if(errores::$error){
            return $this->error->error('Error al generar ultimo codigo',$ultimo_codigo_upd);
        }

        return $ultimo_codigo_upd;
    }

    /**
     * PHPUNIT
     * @param string $pattern
     * @return array|string
     */
    public function pattern_html(string $pattern): array|string
    {
        if($pattern===''){
            return $this->error->error('Error el pattern no puede venir vacio',$this->patterns);
        }

        $buscar = array('/^','$/');

        return str_replace($buscar,'',$pattern);
    }

    /**
     *
     * Devuelve una variable de tipo booleana que indica si el usuario existe o no
     * @version 1.145.31
     * @param array $campos_encriptados Campos a validar desencripctacion encriptacion
     * @return bool|array
     * @example
     *      $existe_user = $this->usuario_existente();
     *
     * @uses modelo_basico->agrega_usuario_session()
     * @internal modelo_basico->$this->ejecuta_consulta();
     */
    private function usuario_existente(array $campos_encriptados = array()): bool|array
    {
        if($this->usuario_id <=0){
            return $this->error->error(mensaje: 'Error usuario invalido o no cargado deberia exitir 
            $modelo->usuario_id mayor  a 0',data: $this->usuario_id);
        }

        $consulta = /** @lang MYSQL */
            'SELECT count(*) AS existe FROM adm_usuario WHERE adm_usuario.id = '.$this->usuario_id;
        $r_usuario_existente = $this->ejecuta_consulta(consulta: $consulta, campos_encriptados: $campos_encriptados);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: $r_usuario_existente);
        }

        $usuario_existente = $r_usuario_existente->registros[0];

        $update_valido = false;
        if((int)$usuario_existente['existe'] === 1){
            $update_valido = true;
        }

        return $update_valido;

    }

    private function valida_data_desactiva(string $modelo_dependiente): bool|array
    {
        $valida = $this->valida_names_model($modelo_dependiente);
        if(errores::$error){
            return $this->error->error('Error al validar modelos',$valida);
        }

        if($this->registro_id<=0){
            return $this->error->error('Error $this->registro_id debe ser mayor a 0',$this->registro_id);
        }
        return true;
    }

    private function valida_names_model(string $modelo_dependiente): bool|array
    {
        $valida = $this->validacion->valida_data_modelo($modelo_dependiente);
        if(errores::$error){
            return  $this->error->error("Error al validar modelo",$valida);
        }

        $valida = $this->validacion->valida_name_clase($this->tabla);
        if(errores::$error){
            return $this->error->error('Error al validar tabla',$valida);
        }

        return true;
    }



    /**
     * ALFABETICO
     */





    private function model_dependiente(string $modelo_dependiente): modelo_base|array
    {
        $modelo_dependiente_ajustado = $this->modelo_dependiente_val($modelo_dependiente);
        if(errores::$error){
            return  $this->error->error('Error al ajustar modelo',$modelo_dependiente);
        }
        $modelo = $this->genera_modelo($modelo_dependiente_ajustado);
        if (errores::$error) {
            return $this->error->error('Error al generar modelo', $modelo);
        }
        return $modelo;
    }

    /**
     * PHPUNIT
     * @param string $modelo_dependiente
     * @return array
     */
    private function desactiva_data_modelo(string $modelo_dependiente): array
    {
        $modelo_dependiente_ajustado = $this->modelo_dependiente_val($modelo_dependiente);
        if(errores::$error){
            return  $this->error->error('Error al ajustar modelo',$modelo_dependiente_ajustado);
        }

        $modelo = $this->model_dependiente($modelo_dependiente_ajustado);
        if (errores::$error) {
            return $this->error->error('Error al generar modelo', $modelo);
        }

        $desactiva = $this->desactiva_dependientes($this->registro_id, $modelo->tabla);
        if (errores::$error) {
            return $this->error->error('Error al desactivar dependiente', $desactiva);
        }
        return $desactiva;
    }

    /**
     * PHPUNIT
     * @return array
     */
    private function desactiva_data_modelos_dependientes(): array
    {
        $data = array();
        foreach ($this->models_dependientes as $dependiente) {
            $desactiva = $this->desactiva_data_modelo($dependiente);
            if (errores::$error) {
                return $this->error->error('Error al desactivar dependiente', $desactiva);
            }
            $data[] = $desactiva;
        }
        return $data;
    }

    /**
     * PHPUNIT
     * @param int $parent_id
     * @param string $tabla_dep
     * @return array
     */
    private function desactiva_dependientes(int $parent_id, string $tabla_dep): array
    {
        $valida = $this->validacion->valida_name_clase($this->tabla);
        if(errores::$error){
            return $this->error->error('Error al validar tabla',$valida);
        }
        if($parent_id<=0){
            return $this->error->error('Error $parent_id debe ser mayor a 0',$parent_id);
        }

        $dependientes = (new dependencias())->data_dependientes(link: $this->link,parent_id: $parent_id,
            tabla: $this->tabla, tabla_children: $tabla_dep);
        if(errores::$error){
            return $this->error->error('Error al obtener dependientes',$dependientes);
        }

        $key_dependiente_id = $tabla_dep.'_id';

        $modelo_dep = $this->genera_modelo($tabla_dep);
        if(errores::$error){
            return $this->error->error('Error al generar modelo',$modelo_dep);
        }


        $result = array();
        foreach($dependientes as $dependiente){

            $modelo_dep->registro_id = $dependiente[$key_dependiente_id];

            $desactiva_bd = $modelo_dep->desactiva_bd();
            if(errores::$error){
                return $this->error->error('Error al desactivar dependiente',$desactiva_bd);
            }
            $result[] = $desactiva_bd;
        }
        return $result;

    }

    /**
     *
     * Funcion que ejecuta un query de tipo select
     * @version 1.24.12
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param string $consulta Consulta en forma de SQL para su ejecucion directa
     * @param array $campos_encriptados Campos encriptados de un modelo
     * @return array|stdClass registros obtenidos de la consulta del modelo con datos o vacio
     * @example
     * $this->consulta = "DESCRIBE $tabla_original";
     * $result = $this->ejecuta_consulta();
     * @uses  modelo_basico
     * @uses  modelo
     * @uses  controlador_reporte
     * @uses  accion
     * @uses  accion_grupo
     */
    public function ejecuta_consulta(string $consulta, array $campos_encriptados = array(),
                                     array $hijo = array()): array|stdClass{
        $this->hijo = $hijo;
        if($consulta === ''){
            return $this->error->error(mensaje: 'La consulta no puede venir vacia', data: array(
                $this->link->errorInfo(),$consulta));
        }
        $this->transaccion = 'SELECT';
        $result = $this->ejecuta_sql(consulta: $consulta);

        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al ejecutar sql',data: $result);
        }

        $r_sql = $result->result;

        $new_array = $this->parsea_registros_envio( r_sql: $r_sql, campos_encriptados: $campos_encriptados);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al parsear registros",data:  $new_array);
        }

        $n_registros = $r_sql->rowCount();
        $r_sql->closeCursor();

        $this->registros = $new_array;
        $this->n_registros = (int)$n_registros;
        $this->sql = $consulta;

        $data = new stdClass();
        $data->registros = $new_array;
        $data->n_registros = (int)$n_registros;
        $data->sql = $consulta;

        $data->registros_obj = array();
        foreach ($data->registros as $row){
            $row_obj = (object)$row;
            $data->registros_obj[] = $row_obj;
        }

        return $data;

    }


    /**
     *
     * Devuelve un objeto que contiene un texto que indica el exito de la sentencia, tambien la consulta inicial de
     * sql y por ultimo un objeto PDOStatement de la consulta sql ingresada
     * @version 1.0.0
     *
     * @param string $consulta Consulta en forma de SQL para su ejecucion directa
     * @return array|stdClass
     * @example
     *      $tabla = 'cliente';
     *      $registro_id = '100';
     *      $this->consulta = "UPDATE . $tabla . SET status = 'inactivo' WHERE id = . $registro_id";
     *      $consulta = $this->consulta;
     *      $resultado = $this->ejecuta_sql();
     *      return array('mensaje'=>'Exito','sql'=>'UPDATE cliente SET status='inactivo' WHERE id='100','result'=>$result);
     *
     * @uses modelo_basico
     * @uses modelo
     * @url http://doc.ciproteo.com:3443/en/home/Sistemas/Manuales/Tecnicos/modelo_basico/ejecuta_sql
     */
    public function ejecuta_sql(string $consulta):array|stdClass{
        if($consulta === ''){
            return $this->error->error(mensaje: "Error consulta vacia", data: $consulta.' tabla: '.$this->tabla);
        }
        try {
            $result = $this->link->query($consulta);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al ejecutar sql '. $e->getMessage(),
                data: array($e->getCode().' '.$this->tabla.' '.$this->consulta.' '.$this->tabla,
                    'registro'=>$this->registro));
        }
        if($this->transaccion ==='INSERT'){
            $this->registro_id = $this->link->lastInsertId();
        }

        $mensaje = 'Exito al ejecutar sql del modelo '.$this->tabla. ' transaccion '.$this->transaccion;

        $data = new stdClass();
        $data->mensaje = $mensaje;
        $data->sql = $consulta;
        $data->result = $result;
        $data->registro = $this->registro;
        $data->registro_id = $this->registro_id;
        $data->salida = 'exito';
        return $data;
    }


    /**
     * PHPUNIT
     * @param string $fecha_inicial
     * @param string $fecha_final
     * @param string $key
     * @return array
     */
    protected function genera_filtro_base_fecha(string $fecha_inicial, string $fecha_final, string $key):array{
        if($fecha_inicial === ''){
            return $this->error->error('Error fecha inicial no puede venir vacia', $fecha_inicial);
        }
        if($fecha_final === ''){
            return $this->error->error( 'Error fecha final no puede venir vacia', $fecha_final);
        }
        $valida = $this->validacion->valida_fecha($fecha_inicial);
        if(errores::$error) {
            return $this->error->error( 'Error al validar fecha inicial', $valida);
        }
        $valida = $this->validacion->valida_fecha($fecha_final);
        if(errores::$error) {
            return $this->error->error( 'Error al validar fecha final', $valida);
        }

        if($fecha_inicial>$fecha_final){
            return $this->error->error( 'Error la fecha inicial no puede ser mayor a la final',
                array($fecha_inicial,$fecha_final));
        }

        $filtro[$key]['valor1'] = $fecha_inicial;
        $filtro[$key]['valor2'] = $fecha_final;
        $filtro[$key]['es_fecha'] = true;

        return $filtro;
    }

    /**
     * PRUEBAS FINALIZADAS/PARAMETROS
     * Devuelve un arreglo que contiene un texto que indica el exito de la sentencia, tambien la consulta inicial de sql y por
     * @param string $filtro_especial_sql sql previo
     * @return array|string
     * @example
     *      $data_filtro_especial_final = $this->filtro_especial_final($filtro_especial_sql,$where);
     *
     * @uses modelo
     */

    public function filtro_especial_final(string $filtro_especial_sql):array|string{
        $filtro_especial_sql_env = $filtro_especial_sql;
        if($filtro_especial_sql !=='') {
            $data_filtro_especial = $this->maqueta_filtro_especial_final($filtro_especial_sql);
            if(errores::$error){
                return  $this->error->error('Error al maquetar sql',$data_filtro_especial);
            }
            $filtro_especial_sql_env = $data_filtro_especial;
        }

        return $filtro_especial_sql_env;
    }





    /**
     * PRUEBAS FINALIZADAS
     * @param string $fecha
     * @param array $filtro
     * @return array
     */
    public function filtro_fecha_rango(string $fecha, array $filtro): array
    {
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return $this->error->error("Error fecha", $valida);
        }
        if($this->tabla === ''){
            return $this->error->error("Error tabla vacia", $this->tabla);
        }
        $namespace = 'models\\';
        $this->tabla = str_replace($namespace,'',$this->tabla);
        $clase = $namespace.$this->tabla;
        if($this->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }

        $filtro_ini = $this->filtro_fecha_inicial($fecha);
        if(errores::$error){
            return $this->error->error('Error al generar filtro fecha', $filtro_ini);
        }

        $filtro_fin = $this->filtro_fecha_final($fecha);
        if(errores::$error){
            return $this->error->error('Error al generar filtro fecha', $filtro_fin);
        }
        $filtro[] = $filtro_ini;
        $filtro[] = $filtro_fin;

        return $filtro;

    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $fecha
     * @return array
     */
    public function filtro_fecha_final(string $fecha): array
    {
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return $this->error->error("Error fecha", $valida);
        }
        if($this->tabla === ''){
            return $this->error->error("Error tabla vacia", $this->tabla);
        }
        $namespace = 'models\\';
        $this->tabla = str_replace($namespace,'',$this->tabla);
        $clase = $namespace.$this->tabla;
        if($this->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }

        $filtro[$fecha]['valor'] = $this->tabla.'.fecha_final';
        $filtro[$fecha]['operador'] = '<=';
        $filtro[$fecha]['comparacion'] = 'AND';
        $filtro[$fecha]['valor_es_campo'] = true;

        return $filtro;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $fecha
     * @return array
     */
    public function filtro_fecha_inicial(string $fecha): array
    {
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return $this->error->error("Error fecha", $valida);
        }
        if($this->tabla === ''){
            return $this->error->error("Error tabla vacia", $this->tabla);
        }
        $namespace = 'models\\';
        $this->tabla = str_replace($namespace,'',$this->tabla);
        $clase = $namespace.$this->tabla;
        if($this->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        $filtro[$fecha]['valor'] = $this->tabla.'.fecha_inicial';
        $filtro[$fecha]['operador'] = '>=';
        $filtro[$fecha]['valor_es_campo'] = true;

        return $filtro;

    }

    /**
     * Asigna el filtro necesario para traer elementos dependiendes de una consulta
     * @version 1.0.0
     * @param string $campo_row Nombre del campo del registro el cual se utiliza para la obtencion de los registros
     * ligados
     * @param string $campo_filtro Nombre del campo del registro el cual se utiliza como valor del filtro
     * @param array $filtro Filtro precargado, es recursivo hace push con el nuevo resultado
     * @param array $row Registro donde se obtendra el valor y el campo para retornar el filtro nuevo
     * @return array
     */
    private function filtro_hijo(string $campo_filtro, string $campo_row, array $filtro, array $row):array{
        if($campo_row===''){
            return $this->error->error(mensaje: "Error campo vacio",data: $campo_row);
        }
        if($campo_filtro===''){
            return $this->error->error(mensaje: "Error filtro",data: $campo_filtro);
        }
        if(!isset($row[$campo_row])){
            $row[$campo_row] = '';
        }
        $filtro[$campo_filtro] = (string)$row[$campo_row];

        return $filtro;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $monto
     * @param string $campo
     * @return array
     */
    public function filtro_monto_ini(string $monto, string $campo): array
    {
        if((float)$monto<0.0){
            return $this->error->error("Error el monto es menor a 0", $monto);
        }
        if($this->tabla === ''){
            return $this->error->error("Error tabla vacia", $this->tabla);
        }
        $namespace = 'models\\';
        $this->tabla = str_replace($namespace,'',$this->tabla);
        $clase = $namespace.$this->tabla;
        if($this->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error("Error campo vacio", $campo);
        }

        $filtro["$monto"]['valor'] = $this->tabla.'.'.$campo;
        $filtro["$monto"]['operador'] = '>=';
        $filtro["$monto"]['comparacion'] = 'AND';
        $filtro["$monto"]['valor_es_campo'] = true;

        return $filtro;
    }

    public function filtro_monto_fin(string $monto, string $campo): array
    {
        if((float)$monto<0.0){
            return $this->error->error("Error el monto es menor a 0", $monto);
        }
        if($this->tabla === ''){
            return $this->error->error("Error tabla vacia", $this->tabla);
        }
        $namespace = 'models\\';
        $this->tabla = str_replace($namespace,'',$this->tabla);
        $clase = $namespace.$this->tabla;
        if($this->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error("Error campo vacio", $campo);
        }

        $filtro["$monto"]['valor'] = $this->tabla.'.'.$campo;
        $filtro["$monto"]['operador'] = '<=';
        $filtro["$monto"]['comparacion'] = 'AND';
        $filtro["$monto"]['valor_es_campo'] = true;

        return $filtro;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $monto
     * @param stdClass $campos
     * @param array $filtro
     * @return array
     */
    public function filtro_monto_rango(string $monto, stdClass $campos, array $filtro): array
    {
        $campos_arr = (array)$campos;
        $keys = array('inf','sup');
        $valida = $this->validacion->valida_existencia_keys($campos_arr, $keys);
        if(errores::$error){
            return $this->error->error("Error validar campos", $valida);
        }

        if($this->tabla === ''){
            return $this->error->error("Error tabla vacia", $this->tabla);
        }
        $namespace = 'models\\';
        $this->tabla = str_replace($namespace,'',$this->tabla);
        $clase = $namespace.$this->tabla;
        if($this->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        if((float)$monto<0.0){
            return $this->error->error("Error el monto es menor a 0", $monto);
        }

        $filtro_monto_ini = $this->filtro_monto_ini($monto, $campos->inf);
        if(errores::$error){
            return $this->error->error('Error al generar filtro monto', $filtro_monto_ini);
        }

        $filtro_monto_fin = $this->filtro_monto_fin($monto, $campos->sup);
        if(errores::$error){
            return $this->error->error('Error al generar filtro monto', $filtro_monto_fin);
        }

        $filtro[] = $filtro_monto_ini;
        $filtro[] = $filtro_monto_fin;

        return $filtro;
    }

    /**
     * PHPUNIT
     * Devuelve un array que contiene un rango de fechas con fecha inicial y final
     *
     * @example
     *      $fechas_in = $this->fechas_in();
     *      //return $resultado = array('fecha_inicial'=>'2020-07-01','fecha_final'=>'2020-07-05');
     * @return array
     * @throws errores si no existen los metodos $_GET y $_POST en su posicion fecha_inicial
     * @throws errores si no existen los metodos $_GET y $_POST en su posicion fecha_final
     * @uses filtro_rango_fechas()
     * @uses obten_datos_con_filtro_especial_rpt()
     */
    protected function fechas_in():array{

        $valida = $this->valida_fechas_in();
        if(errores::$error) {
            return $this->error->error('Error al validar fechas', $valida);
        }

        $fechas = $this->get_fechas_in();
        if(errores::$error) {
            return $this->error->error('Error al obtener fechas', $fechas);
        }

        $valida = $this->verifica_fechas_in($fechas);
        if(errores::$error) {
            return $this->error->error('Error al validar fecha inicial', $valida);
        }

        return array ('fecha_inicial'=>$fechas->fecha_inicial,'fecha_final'=>$fechas->fecha_final);
    }

    /**
     * PHPUNIT
     * @param stdClass $fechas
     * @return bool|array
     */
    private function verifica_fechas_in(stdClass $fechas): bool|array
    {
        if(!isset($fechas->fecha_inicial)){
            return $this->error->error('Error fecha inicial no existe', $fechas);
        }
        if(!isset($fechas->fecha_final)){
            return $this->error->error('Error fecha final no existe', $fechas);
        }
        if($fechas->fecha_inicial === ''){
            return $this->error->error('Error fecha inicial no puede venir vacia', $fechas);
        }
        if($fechas->fecha_final === ''){
            return $this->error->error('Error fecha final no puede venir vacia', $fechas);
        }
        $valida = $this->validacion->valida_fecha($fechas->fecha_inicial);
        if(errores::$error) {
            return $this->error->error('Error al validar fecha inicial', $valida);
        }
        $valida = $this->validacion->valida_fecha($fechas->fecha_final);
        if(errores::$error) {
            return $this->error->error('Error al validar fecha final', $valida);
        }

        if($fechas->fecha_inicial>$fechas->fecha_final){
            return $this->error->error('Error la fecha inicial no puede ser mayor a la final', $fechas);
        }
        return true;
    }

    /**
     * PHPUNIT
     * @return stdClass
     */
    #[Pure] private function get_fechas_in(): stdClass
    {
        $fecha_inicial = $_GET['fecha_inicial'] ?? $_POST['fecha_inicial'];
        $fecha_final = $_GET['fecha_final'] ?? $_POST['fecha_final'];
        $fechas = new stdClass();
        $fechas->fecha_inicial = $fecha_inicial;
        $fechas->fecha_final = $fecha_final;
        return $fechas;
    }

    /**
     * PHPUNIT
     * @return bool|array
     */
    private function valida_fechas_in(): bool|array
    {
        if(!isset($_GET['fecha_inicial']) && !isset($_POST['fecha_inicial'])){
            return $this->error->error('Error debe existir fecha_inicial por POST o GET',array());
        }
        if(!isset($_GET['fecha_final']) && !isset($_POST['fecha_final'])){
            return $this->error->error('Error debe existir fecha_final por POST o GET', array());
        }
        return true;
    }

    /**
     *
     * Devuelve un arreglo con los datos necesarios para obtener un filtro y ser utilizado en las sentencias de consulta
     * para la obtención de los registros esto de todos las columnas que se mandan por el filtro.
     * Genera arreglo
     * @version 1.0.0
     * @param array $filtros arreglo de filtros para la obtencion de registros de hijos
     * @param array $row Registro donde se obtendra el valor y el campo para retornar el filtro nuevo
     * @return array
     */
    private function filtro_para_hijo(array $filtros, array $row):array{
        $filtro = array();
        foreach($filtros as $campo_filtro=>$campo_row){
            if($campo_row===''){
                return $this->error->error(mensaje: "Error campo vacio",data: $campo_filtro);
            }
            $filtro = $this->filtro_hijo(campo_filtro: $campo_filtro, campo_row: $campo_row,filtro: $filtro, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro);
            }
        }
        return $filtro;
    }




    /**
     * P INT P ORDER
     * Genera sql con forma de campos para UPDATE
     * @return array|string con sql de campos para update
     * @example
     *     $campos_sql = $this->genera_campos_update();
     * @uses modelo
     * @internal $consultas_base->obten_campos($this->tabla,'modifica', $this->link);
     * @internal $this->obten_campos_update();
     */
    protected function genera_campos_update(): array|string
    {
        if(count($this->registro_upd) === 0){
            return $this->error->error(mensaje: 'El registro no puede venir vacio',data: $this->registro_upd);
        }

        $elemento_lista = $this->genera_modelo(modelo: 'adm_elemento_lista');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo',data: $elemento_lista);
        }


        $campos = $elemento_lista->obten_campos(estructura_bd:array(), modelo:$this,vista:'modifica');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos',data: $campos);
        }

        $campos = $this->obten_campos_update();

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener campos',data: $campos);
        }

        return $campos;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $name_modelo
     * @param int $registro_id
     * @return array
     */
    public function get_data_img(string $name_modelo, int $registro_id):array{
        $name_modelo = trim($name_modelo);
        $valida = $this->validacion->valida_data_modelo($name_modelo);
        if(errores::$error){
            return  $this->error->error('Error al validar entrada para generacion de modelo en '.$name_modelo,$valida);
        }
        if($registro_id<=0){
            return  $this->error->error('Error registro_id debe ser mayor a 0 ',$registro_id);
        }
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return  $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
        }

        $modelo_foto = $this->genera_modelo($name_modelo);
        if(errores::$error){
            return $this->error->error('Error al generar modelo',$modelo_foto);
        }

        $key_filtro = $this->tabla.'.id';
        $filtro[$key_filtro] = $registro_id;
        $r_foto = $modelo_foto->filtro_and($filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener fotos',$r_foto);
        }
        return $r_foto;
    }


    /**
     * Funcion que genera el SQL para un SELECT
     * @version 1.66.17
     * @param array $columnas columnas inicializadas a mostrar a peticion en resultado SQL
     * @param array $columnas_by_table Obtiene solo las columnas de la tabla en ejecucion
     * @param bool $columnas_en_bruto Envia las columnas tal como estan en la bd
     * @param array $extension_estructura columnas estructura tabla ligada 1 a 1
     * @param array $renombradas columnas estructura tabla ligadas renombradas
     * @return array|string string en forma de sql con los datos para la ejecucion de SELECT
     * @functions $this->obten_columnas_completas($columnas);
     * @functions $consulta_base->obten_tablas_completas($tabla, $this->columnas);
     * @functions $sub_querys_sql = $this->sub_querys($columnas);
     * @example
     * $consulta = $this->genera_consulta_base($columnas);
     * @uses  $this->filtro_and
     * @uses  $this->obten_por_id
     * @uses  $this->obten_registros_activos
     * @uses  modelos->accion_grupo->obten_accion_permitida
     */

    public function genera_consulta_base(array $columnas = array(), $columnas_by_table = array(),
                                            bool $columnas_en_bruto = false, array $extension_estructura = array(),
                                            array $renombradas = array()):array|string{

        $this->tabla = str_replace('models\\','',$this->tabla);

        $columnas_seleccionables = $columnas;
        $columnas_sql = (new columnas())->obten_columnas_completas(modelo: $this, columnas_by_table:$columnas_by_table,
            columnas_en_bruto:$columnas_en_bruto, columnas_sql: $columnas_seleccionables,
            extension_estructura:  $extension_estructura, renombres:  $renombradas);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener columnas',data: $columnas_sql);
        }


        $tablas = (new joins())->tablas(columnas: $this->columnas, extension_estructura:  $extension_estructura,
            modelo: $this, renombradas: $renombradas, tabla: $this->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar joins', data: $tablas);
        }

        $sub_querys_sql = (new columnas())->sub_querys(columnas: $columnas_sql, modelo: $this,
            columnas_seleccionables: $columnas_seleccionables);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sub querys', data: $sub_querys_sql);
        }


        return /** @lang MYSQL */ "SELECT $columnas_sql $sub_querys_sql FROM $tablas";
    }





    /**
     *
     * Funcion que genera un modelo a partir del nombre
     * @version 1.15.9
     *
     * @param string $modelo txt con el nombre del modelo a crear
     * @example
     *     $modelo = $modelo_base->genera_modelo($name_modelo);
     *
     * @throws errores $name_modelo = vacio
     * @throws errores $name_modelo = numero
     * @throws errores $name_modelo no existe una clase con el nombre del modelo

     */
    public function genera_modelo(string $modelo):array|modelo{
        $modelo = str_replace('models\\','',$modelo);
        $modelo = 'models\\'.$modelo;

        $modelo = trim($modelo);
        $valida = $this->validacion->valida_data_modelo(name_modelo: $modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
        }
        return new $modelo($this->link);
    }

    /**
     * Maqueta un arreglo para la generacion de modelos y sus registros asignados a un query para obtener sus
     * dependientes o dependencias
     * de la siguiente forma $registro['tabla']= $reg[0][campos de registro], $reg[n][campos de registro]
     * @version 1.0.0
     *

     *
     * @example
     *      $modelos_hijos = $this->genera_modelos_hijos();
    if(isset($modelos_hijos['error'])){
     *          return $this->error->error('Error al generar $modelos_hijos',
     *          __LINE__,__FILE__,$modelos_hijos);
     *      }
     *
     *
     * @return array
     *      $modelos_hijos[$key]['filtros']= $modelo['filtros'];
     *      $modelos_hijos[$key]['filtros_con_valor']= $modelo['filtros_con_valor'];
     *      $modelos_hijos[$key]['nombre_estructura']= $modelo['nombre_estructura'];
     * @throws errores $this->hijo[$key] key debe ser un txt con nombre del campo a asignar
     * @throws errores $this->hijo[$key][filtros] filtros debe existir
     * @throws errores $this->hijo[$key][filtros_con_valor] filtros_con_valor debe existir
     * @throws errores $this->hijo[$key][filtros] debe ser un array
     * @throws errores $this->hijo[$key][filtros_con_valor] debe ser un array
     * @throws errores $this->hijo[$key][nombre_estructura] debe existir
     * @url http://doc.ciproteo.com:3443/en/home/Sistemas/Manuales/Tecnicos/modelo_basico/genera_modelos_hijos
     */
    private function genera_modelos_hijos(): array{//FIN DEBUG
        $modelos_hijos = array() ;
        foreach($this->hijo as $key=>$modelo){
            if(is_numeric($key)){
                return $this->error->error(mensaje: "Error en key",data: $this->hijo);
            }
            if(!isset($modelo['filtros'])){
                return $this->error->error(mensaje: "Error filtro",data: $this->hijo);
            }
            if(!isset($modelo['filtros_con_valor'])){
                return $this->error->error(mensaje:"Error filtro",data:$this->hijo);
            }
            if(!is_array($modelo['filtros'])){
                return $this->error->error(mensaje:"Error filtro",data:$this->hijo);
            }
            if(!is_array($modelo['filtros_con_valor'])){
                return $this->error->error(mensaje:"Error filtro",data:$this->hijo);
            }
            if(!isset($modelo['nombre_estructura'])){
                return $this->error->error(mensaje:"Error en estructura",data:$this->hijo);
            }

            $modelos_hijos[$key]['filtros']= $modelo['filtros'];
            $modelos_hijos[$key]['filtros_con_valor']= $modelo['filtros_con_valor'];
            $modelos_hijos[$key]['nombre_estructura']= $modelo['nombre_estructura'];
        }
        return $modelos_hijos;
    }

    /**
     *
     * Funcion que asigna los registros encontrados de hijos en un registro
     * @version 1.16.9
     *
     * @param string $name_modelo txt con el nombre del modelo para la asignacion del registro
     * @param array $data_modelo datos de parametrizacion de datos para la ejecucion de obtencion de los registros
     * @param array $row registro padre al que se le asignaran los hijos
     * @example
     *     $row = $this->genera_registro_hijo($data_modelo,$row,$name_modelo);
     * @return array registro del modelo con registros hijos asignados
     * @throws errores $name_modelo = vacio
     * @throws errores $name_modelo = numero
     * @throws errores $name_modelo no existe una clase con el nombre del modelo
     * @throws errores $data_modelo['nombre_estructura'] no existe

     */
    private function genera_registro_hijo(array $data_modelo, string $name_modelo, array $row):array{
        if(!isset($data_modelo['nombre_estructura'])){
            return $this->error->error(mensaje: 'Error debe existir $data_modelo[\'nombre_estructura\'] ',
                data: $data_modelo);
        }
        $filtro = $this->obten_filtro_para_hijo(data_modelo: $data_modelo,row: $row);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error filtro",data: $filtro);
        }
        $row = $this->asigna_registros_hijo(filtro: $filtro, name_modelo: $name_modelo,
            nombre_estructura: $data_modelo['nombre_estructura'],row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar registros de hijo', data: $row);
        }
        return $row;
    }

    /**
     *
     * Funcion que asigna y genera los registros encontrados de hijos en un registro
     * @version 1.16.9
     * @param array $modelos_hijos datos de parametrizacion de datos para la ejecucion de obtencion de los registros
     * @param array $row registro padre al que se le asignaran los hijos
     * @example
     *      $row = (array) $row;
     *      $row = $this->genera_registros_hijos($modelos_hijos,$row);
     * @return array registro del modelo con registros hijos asignados
     * @throws errores $data_modelo['nombre_estructura'] no existe

     */
    private function genera_registros_hijos(array $modelos_hijos, array $row):array{
        foreach($modelos_hijos as $name_modelo=>$data_modelo){
            if(!is_array($data_modelo)){
                $fix = '$modelos_hijos debe ser un array asociativo de la siguiente forma';
                $fix.= ' $modelos_hijos[name_modelo][nombre_estructura] = nombre d ela tabla dependiente';
                $fix.= ' $modelos_hijos[name_modelo][filtros] = array() con configuracion de filtros';
                $fix.= ' $modelos_hijos[name_modelo][filtros_con_valor] = array() con configuracion de filtros';
                return $this->error->error(mensaje: "Error en datos",data: $modelos_hijos, fix: $fix);
            }

            if(!isset($data_modelo['nombre_estructura'])){
                return  $this->error->error(mensaje: 'Error debe existir $data_modelo[\'nombre_estructura\'] ',
                    data: $data_modelo);
            }
            if(!is_string($name_modelo)){
                $fix = '$modelos_hijos debe ser un array asociativo de la siguiente forma';
                $fix.= ' $modelos_hijos[name_modelo][nombre_estructura] = nombre d ela tabla dependiente';
                $fix.= ' $modelos_hijos[name_modelo][filtros] = array() con configuracion de filtros';
                $fix.= ' $modelos_hijos[name_modelo][filtros_con_valor] = array() con configuracion de filtros';
                $this->error->error(mensaje: 'Error $name_modelo debe ser un string ', data: $data_modelo);
            }

            $row = $this->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: $name_modelo,
                row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar registros de hijo', data: $row);
            }

        }

        return $row;
    }





    /**
     *
     * @param array $registros
     * @param string $key
     * @param int $longitud_maxima
     * @return array|string
     */
    public function genera_ultimo_codigo_base_numero(array $registros, string $key,int $longitud_maxima):array|string{

        $valida_base = $this->validacion->valida_base_ultimo_codigo($registros,$key);
        if(errores::$error){
            return $this->error->error('Error al validar',$valida_base);
        }
        if($longitud_maxima < 0){
            return $this->error->error('Error $longitud_maxima debe ser mayor a 0',$longitud_maxima);
        }

        $ultimo_codigo_upd = $this->obten_ultimo_codigo_insert($registros,$key);
        if(errores::$error){
            return $this->error->error('Error al generar ultimo codigo',$ultimo_codigo_upd);
        }

        $longitud_codigo = strlen($ultimo_codigo_upd);

        $ceros = $this->asigna_cero_codigo($longitud_codigo,$longitud_maxima);
        if(errores::$error){
            return $this->error->error('Error al asignar ceros',$ceros);
        }

        return $ceros.$ultimo_codigo_upd;
    }

    /**
     * PHPUNIT
     * @param int $ultimo_codigo
     * @return int|array
     */
    private function genera_ultimo_codigo_int(int $ultimo_codigo): int|array
    {
        if($ultimo_codigo<0){
            return $this->error->error('Error $ultimo_codigo debe ser mayor a 0',$ultimo_codigo);
        }

        $ultimo_codigo_int = $ultimo_codigo;
        return $ultimo_codigo_int+1;
    }

    /**
     *
     * Funcion que asigna y genera los registros encontrados de hijos en un registro
     * @version 1.24.10
     * @param array $modelos_hijos datos de parametrizacion de datos para la ejecucion de obtencion de los registros
     * @param PDOStatement $r_sql registro en forma de retorno de mysql nativo
     * @param array $campos_encriptados Conjunto de campos para desencriptar
     * @example
     *      $modelos_hijos = $this->genera_modelos_hijos();
    if(isset($modelos_hijos['error'])){
    return $this->error->error('Error al generar $modelos_hijos',$modelos_hijos);
    }
    $new_array = $this->maqueta_arreglo_registros($r_sql,$modelos_hijos);
     * @return array registro del modelo con registros hijos asignados
     * @throws errores Errores definidos en las creaciones de hijos
     */
    private function maqueta_arreglo_registros(array $modelos_hijos, PDOStatement $r_sql,
                                              array $campos_encriptados = array()):array{
        $new_array = array();
        while( $row = $r_sql->fetchObject()){
            $row = (array) $row;

            $row_new = $this->ajusta_row_select(campos_encriptados: $campos_encriptados,
                modelos_hijos: $modelos_hijos, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al ajustar rows', data:$row);
            }

            $new_array[] = $row_new;
        }

        return $new_array;
    }





    /**
     * PHPUNIT
     * Devuelve un arreglo con la sentencia de sql que indica si se aplicaran una o dos condiciones
     *
     * @param string $filtro_especial_sql cadena que contiene una sentencia de sql a aplicar el filtro
     * @return array|string
     * @example
     *      $data_filtro_especial = $this->maqueta_filtro_especial_final($filtro_especial_sql);
     *
     * @uses modelo_basico->filtro_especial_final(string $filtro_especial_sql);
     */
    private function maqueta_filtro_especial_final( string $filtro_especial_sql):array|string{//FIN
        if($filtro_especial_sql===''){
            return  $this->error->error('Error el filtro especial no puede venir vacio',$filtro_especial_sql);
        }

        return $filtro_especial_sql;
    }



    /**
     * P INT P ORDER
     * Devuelve la forma de los campos a modifica enb forma de sql
     * @return array|string con sql con maquetacion de una modificacion en sql campo = 'valor'
     * @throws errores $this->registro_upd vacio
     * @throws errores $this->registro_upd[campo] campo es un numero
     * @throws errores $this->registro_upd[campo] campo es vacio
     * @example
     *       $campos = $this->obten_campos_update();
     *
     * @uses modelo_basico
     * @uses modelo
     */
    private function obten_campos_update(): array|string
    {

        if(count($this->registro_upd) === 0){
            return $this->error->error(mensaje: 'El registro no puede venir vacio',data: $this->registro_upd);
        }
        $campos = $this->campos();
        if (errores::$error) {
            return $this->error->error('Error al generar campos', $campos);
        }

        return $campos;
    }

    /**
     * Genera los campos para un update
     * @return array|string
     */
    private function campos(): array|string
    {
        $campos = '';
        foreach ($this->registro_upd as $campo => $value) {
            $campos = $this->maqueta_rows_upd(campo: $campo, campos:  $campos,value:  $value);
            if (errores::$error) {
                return $this->error->error('Error al generar campos', $campos);
            }
        }
        return $campos;
    }

    /**
     * P INT P ORDER
     * @param string $campo Campo a reasignar valor
     * @param string|int|float|null $value
     * @param string $campos
     * @return array|string
     */
    private function maqueta_rows_upd(string $campo, string $campos, string|int|float|null $value): array|string
    {
        if(is_numeric($campo)){
            return $this->error->error(mensaje: 'Error ingrese un campo valido',data: $this->registro_upd);
        }
        if($campo === ''){
            return $this->error->error('Error ingrese un campo valido',$this->registro_upd);
        }

        $params = $this->params_data_update(campo: $campo,value:  $value);
        if (errores::$error) {
            return $this->error->error('Error al generar parametros', $params);
        }

        $campos = $this->rows_update(campos: $campos, params: $params);
        if (errores::$error) {
            return $this->error->error('Error al generar campos', $campos);
        }
        return $campos;
    }

    /**
     * P ORDER P INT
     * @param string $campos
     * @param stdClass $params
     * @return string|array
     */
    private function rows_update(string $campos, stdClass $params): string|array
    {
        if(!isset($params->campo)){
            return $this->error->error('Error no existe params->campo', $params);
        }
        if(!isset($params->value)){
            return $this->error->error('Error no existe params->value', $params);
        }
        $campos .= $campos === "" ? "$params->campo = $params->value" : ", $params->campo = $params->value";
        return $campos;
    }

    /**
     * P ORDER P INT
     * @param string $campo Campo a reasignar valor
     * @param string|float|int|null $value
     * @return array|stdClass
     */
    private function params_data_update(string $campo, string|float|int|null $value): array|stdClass
    {
        $value_ = $value;
        $value_ = (new monedas())->value_moneda(campo: $campo, modelo: $this, value: $value_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar value',data:  $value_);
        }

        $data = $this->slaches_value(campo: $campo,value:  $value_);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar value',data:  $value_);
        }

        $data->value = $this->value_null(value: $data->value);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar value', data:$data);
        }
        return $data;
    }

    /**
     * P ORDER P INT
     * @param string|int|float|null $value
     * @return string
     */
    private function value_null(string|int|float|null $value): string
    {
        if ($value == null) {
            $value = 'NULL';
        }
        else {
            $value = "'" . $value . "'";
        }
        return $value;
    }

    /**
     * P ORDER P INT
     * @param string $campo
     * @param string|int|float|null $value
     * @return stdClass|array
     */
    private function slaches_value(string $campo, string|int|float|null $value): stdClass|array
    {

        if(is_null($value)){
            $value = "";
        }
        if($campo === ''){
            return $this->error->error('Error el campo no puede venir vacio', $campo);
        }
        $campo = addslashes($campo);
        $value = addslashes($value);

        $data = new stdClass();
        $data->campo = $campo;
        $data->value = $value;

        return $data;
    }



    /**
     *
     * Funcion que genera un filtro para ser enviado en forma de array para consultas posteriores
     * @version 1.0.0
     * @param array $data_modelo datos de la configuracion del modelo a procesar los filtros
     * @param array $row registro formado en forma modelo->registro
     * @example
     *     $filtro = $this->obten_filtro_para_hijo($data_modelo,$row);
     *
     * @return array con filtro maquetado para su procesamiento filtro[$campo_filtro] = $value;
     * @throws errores $data_modelo['filtros'] no existe
     * @throws errores $data_modelo['filtros_con_valor'] no existe
     * @throws errores $data_modelo['filtros'] no es un array
     * @throws errores $data_modelo['filtros_con_valor'] no es un array
     * @throws errores $data_modelo['filtros'][$campo] =  ''
     * @throws errores $data_modelo['filtros'][$campo] no existe
     *
     */
    private function obten_filtro_para_hijo(array $data_modelo, array $row):array{
        if(!isset($data_modelo['filtros'])){
            $fix = 'En data_modelo debe existir un key filtros como array data_modelo[filtros] = array()';
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, fix: $fix);
        }
        if(!isset($data_modelo['filtros_con_valor'])){
            $fix = 'En data_modelo debe existir un key filtros como array data_modelo[filtros_con_valor] = array()';
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, fix: $fix);
        }
        if(!is_array($data_modelo['filtros'])){
            $fix = 'En data_modelo debe existir un key filtros como array data_modelo[filtros] = array()';
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, fix: $fix);
        }
        if(!is_array($data_modelo['filtros_con_valor'])){
            $fix = 'En data_modelo debe existir un key filtros_con_valor como array data_modelo[filtros_con_valor] = array()';
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, fix: $fix);
        }

        $filtros = $data_modelo['filtros'];
        $filtros_con_valor = $data_modelo['filtros_con_valor'];

        $filtro = $this->filtro_para_hijo(filtros: $filtros,row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: "Error filtro",data: $filtro);
        }

        foreach($filtros_con_valor as $campo_filtro=>$value){
            $filtro[$campo_filtro] = $value;
        }

        return $filtro;
    }

    /**
     *
     * Funcion que obtiene con base en la tabla renombrada si tabla renombrada no es vacia cambia el nombre a tabla original
     * @version 1.45.14
     * @param string $tabla_original nombre del modelo
     * @param string $tabla_renombrada nombre a renombrar tabla
     * @example
    $tabla_nombre = $this->obten_nombre_tabla($tabla_renombrada,$tabla_original);
    if(isset($tabla_nombre['error'])){
    return $this->error->error('Error al obtener nombre de tabla',
    __LINE__,__FILE__,$tabla_nombre);
    }
     * @return array|string tabla nombre ajustado
     * @throws errores $tabla_renombrada y $tabla_renombrada = vacio
     */
    public function obten_nombre_tabla(string $tabla_original, string $tabla_renombrada):array|string{

        if(trim($tabla_original)==='' && trim($tabla_renombrada) === ''){
            return $this->error->error(mensaje: 'Error no pueden venir vacios todos los parametros',
                data: $tabla_renombrada);
        }
        if($tabla_renombrada!==''){
            $tabla_nombre = $tabla_renombrada;
        }
        else{
            $tabla_nombre = $tabla_original;
        }
        return $tabla_nombre;
    }


    /**
     *
     * Funcion reemplaza el primer dato encontrado en la posicion 0
     * @version 1.0.0
     * @param string $from cadena de busqueda
     * @param string $to cadena de reemplazo
     * @param string $content cadena a ejecutar ajuste
     * @example
    foreach($registro as $key=>$value){
    if(!$value && in_array($key,$keys_int,false) ){
    $value = 0;
    }
    $key_nuevo = $controlador->modelo->str_replace_first($controlador->tabla.'_','',$key);
    $valores[$key_nuevo] = $value;
    }
     * @return array|string cadena con reemplazo aplicado
     * @throws errores $content = vacio
     * @throws errores $from  = vacio
     * @uses clientes
     * @uses controler
     */
    public function str_replace_first(string $content, string $from, string $to):array|string{
        if($content === ''){
            return $this->error->error(mensaje: 'Error al content esta vacio',data: $content);
        }
        if($from === ''){
            return $this->error->error(mensaje: 'Error from esta vacio',data: $from);
        }
        $pos = strpos($content, $from);


        if($pos === 0) {
            $from = '/' . preg_quote($from, '/') . '/';
            return preg_replace($from, $to, $content, 1);
        }

        return $content;
    }



    /**
     *
     * Funcion que asigna y genera los registros encontrados en un query
     * @version 1.23.12
     * @param PDOStatement $r_sql registro en forma de retorno de mysql nativo
     * @param array $campos_encriptados Campos encriptados de un modelo
     * @example
    $this->hijo = $hijo;
    if($this->consulta === ''){
    return $this->error->error('La consulta no puede venir vacia',__LINE__, __FILE__,array($this->link->errorInfo(),$this->consulta));
    }
    $this->transaccion = 'SELECT';
    $result = $this->ejecuta_sql();
    if(isset($result['error'])){
    return $this->error->error('Error al ejecutar sql',$result);
    }
    $r_sql = $result['result'];

    $new_array = $this->parsea_registros_envio( $r_sql);
     * @return array registros del modelo con datos o vacio
     * @throws errores Errores definidos en las creaciones de hijos
     * @throws errores Errores definidos en la maquetacion de informacion
     * @uses modelo_basico->ejecuta_consulta
     * @internal  $this->genera_modelos_hijos()
     * @internal  $this->maqueta_arreglo_registros($r_sql,$modelos_hijos);
     */
    private function parsea_registros_envio(PDOStatement $r_sql, array $campos_encriptados = array()):array{

        $modelos_hijos = $this->genera_modelos_hijos();
        if(errores::$error){
            return $this->error->error(mensaje: "Error al general modelo",data: $modelos_hijos);
        }
        $new_array = $this->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql,
            campos_encriptados: $campos_encriptados);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al generar arreglo con registros',data: $new_array);
        }

        return $new_array;
    }

}


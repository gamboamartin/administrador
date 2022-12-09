<?php
namespace base\orm;

use config\generales;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use gamboamartin\plugins\files;
use JetBrains\PhpStorm\Pure;
use JsonException;
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
    public array $campos_view = array();
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
    public string $NAMESPACE = '';
    public bool $temp = false;

    public array $childrens = array();
    protected array $defaults = array();
    protected array $parents_data = array();


    /**
     * Modelado
     * @param PDO $link Conexion a la BD
     * @param bool $temp Si temp, crea cache de sql del modelo en ejecucion
     * @version 2.12.2.1
     */
    #[Pure] public function __construct(
        PDO $link, array $defaults = array(), array $parents_data = array(), bool $temp = false ){ //PRUEBAS EN PROCESO
        $this->error = new errores();
        $this->link = $link;
        $this->validacion = new base_modelos();
        $this->temp = $temp;


        $this->patterns['double'] = "/^\\$?[1-9]+,?([0-9]*,?[0,9]*)*.?[0-9]{0,4}$/";
        $this->patterns['double_con_cero'] = "/^[0-9]+[0-9]*.?[0-9]{0,4}$/";
        $this->patterns['telefono'] = "/^[0-9]{10}$/";
        $this->patterns['id'] = "/^[1-9]+[0-9]*$/";

        $this->keys_data_filter = array('sentencia','filtro_especial','filtro_rango','filtro_extra','in',
            'not_in', 'diferente_de','sql_extra','filtro_fecha');

        $this->defaults = $defaults;

        $this->parents_data = $parents_data;
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
     *
     * @return array
     * @throws JsonException
     */
    protected function aplica_desactivacion_dependencias(): array
    {

        $data = array();
        if($this->desactiva_dependientes) {
            $desactiva = (new dependencias())->desactiva_data_modelos_dependientes(modelo: $this);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al desactivar dependiente',data:  $desactiva);
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

    protected function asigna_alias(array $registro): array
    {
        if(!isset($registro['alias'])){

            $registro['alias'] = $registro['descripcion'];

        }
        return $registro;
    }

    /**
     * Asigna un codigo automatico si este no existe para alta
     * @param array $keys_registro Key para asignacion de datos base registro
     * @param array $keys_row Keys para asignacion de datos en base row
     * @param modelo $modelo Modelo para obtencion de datos precargados
     * @param array $registro Registro para integracion de codigo
     * @return array
     * @version 1.406.47
     */
    protected function asigna_codigo(array $keys_registro, array $keys_row, modelo $modelo, array $registro): array
    {
        if(!isset($registro['codigo'])){
            $key_id = $modelo->tabla.'_id';
            $keys = array($key_id);
            $valida = $this->validacion->valida_ids(keys: $keys,registro:  $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
            }
            $codigo = (new codigos())->genera_codigo(keys_registro: $keys_registro,keys_row:  $keys_row, modelo: $modelo,
                registro_id:$registro[$modelo->tabla.'_id'] , registro: $registro);

            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener codigo', data: $codigo);
            }
            $registro['codigo'] = $codigo;
        }
        return $registro;
    }

    protected function asigna_codigo_bis(array $registro): array
    {
        if(!isset($registro['codigo_bis'])){

            $registro['codigo_bis'] = $registro['codigo'];
        }
        return $registro;
    }


    /**
     * Asigna una descripcion en caso de no existir
     * @param modelo $modelo Modelo para generacion de descripcion
     * @param array $registro Registro en ejecucion
     * @return array
     * @version 1.446.48
     */
    protected function asigna_descripcion(modelo $modelo, array $registro): array
    {
        $valida = $this->valida_registro_modelo(modelo: $modelo,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }
        if(!isset($registro['descripcion'])){

            $descripcion = $this->genera_descripcion( modelo:$modelo, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener descripcion', data: $descripcion);
            }

            $registro['descripcion'] = $descripcion;

        }
        return $registro;
    }

    protected function asigna_descripcion_select(array $registro): array
    {
        if(!isset($registro['descripcion_select'])){

            $registro['descripcion_select'] = $registro['descripcion'];
        }
        return $registro;
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
    private function asigna_registros_hijo(array $filtro, string $name_modelo, string $namespace_model,
                                           string $nombre_estructura, array $row):array{
        $valida = $this->validacion->valida_data_modelo(name_modelo: $name_modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar entrada para modelo',data: $valida);
        }
        if($nombre_estructura === ''){
            return  $this->error->error(mensaje: 'Error nombre estructura no puede venir vacia',
                data: $nombre_estructura);
        }

        $modelo = $this->genera_modelo(modelo: $name_modelo, namespace_model: $namespace_model);
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

    protected function campos_base(array $data, modelo $modelo, int $id = -1, array $keys_integra_ds = array('codigo','descripcion')): array
    {

        if( !isset($data['codigo'])){
            if(isset($data['descripcion'])){
                $data['codigo'] = $data['descripcion'];
            }
        }

        $data = (new data_base())->init_data_base(data: $data,id: $id, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro previo',data: $data);
        }

        $keys = array('descripcion','codigo');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys,registro:  $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }

        if(!isset($data['codigo_bis'])){
            $data['codigo_bis'] =  $data['codigo'];
        }

        $data = $this->data_base(data: $data, keys_integra_ds: $keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar data base', data: $data);
        }

        return $data;
    }

    protected function data_base(array $data, array $keys_integra_ds = array('codigo','descripcion')): array
    {

        $valida = $this->validacion->valida_existencia_keys(keys:$keys_integra_ds,registro:  $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }

        $data = $this->registro_descripcion_select(data: $data,keys_integra_ds:  $keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integra descripcion select descripcion select', data: $data);
        }

        if(!isset($data['alias'])){
            $data['alias'] = $data['codigo'];
        }
        return $data;
    }

    private function data_result(array $campos_encriptados, string $consulta): array|stdClass
    {
        $result_sql = $this->result_sql(campos_encriptados: $campos_encriptados,consulta:  $consulta);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al ejecutar sql", data: $result_sql);
        }

        $data = $this->maqueta_result(consulta: $consulta,n_registros:  $result_sql->n_registros,
            new_array:  $result_sql->new_array);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear registros", data: $data);
        }
        return $data;
    }


    /**
     * @param modelo $modelo Modelo para generacion de descripcion
     * @param array $registro Registro en ejecucion
     * @return array|string
     * @version 1.416.48
     *
     */
    private function descripcion_alta(modelo $modelo, array $registro): array|string
    {
        $valida = $this->valida_registro_modelo(modelo: $modelo,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $row = $modelo->registro(registro_id: $registro[$modelo->tabla.'_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro', data: $row);
        }

        return $row[$modelo->tabla.'_descripcion'];
    }

    /**
     * Ajusta un registro en su descripcion select
     * @param array $data Datos de registro1
     * @param array $keys_integra_ds Keys para integracion de descripcion
     * @return array|string
     * @version 2.107.12
     */
    private function descripcion_select(array $data, array $keys_integra_ds): array|string
    {
        $ds = '';
        foreach ($keys_integra_ds as $key){
            $key = trim($key);
            if($key === ''){
                return $this->error->error(mensaje: 'Error al key esta vacio', data: $key);
            }

            $keys = array($key);
            $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $data);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar data', data: $valida);
            }
            $ds = $this->integra_ds(data: $data,ds:  $ds,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar descripcion select', data: $ds);
            }
        }
        return trim($ds);
    }

    /**
     *
     * Integra una descripcion select basada en un campo
     * @param array $data Registro en proceso
     * @param string $key Key a integrar
     * @return string|array
     * @version 2.83.6
     */
    private function ds_init(array $data, string $key): array|string
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error al key esta vacio', data: $key);
        }

        $keys = array($key);
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }

        if($key === 'codigo'){
            $ds_init = trim($data[$key]);
        }
        else{
            $ds_init = $this->ds_init_no_codigo(data: $data,key:  $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar descripcion select', data: $ds_init);
            }
        }
        return $ds_init;
    }

    /**
     *
     * Integra una descripcion select basada en un campo
     * @param array $data Registro en proceso
     * @param string $key Key a integrar
     * @return string|array
     * @version 2.83.6
     */
    private function ds_init_no_codigo(array $data, string $key): string|array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error al key esta vacio', data: $key);
        }

        $keys = array($key);
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }

        $ds_init = trim(str_replace("_"," ",$data[$key]));
        return ucwords($ds_init);
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
                                     array $hijo = array(), bool $valida_tabla = true): array|stdClass{
        $this->hijo = $hijo;
        if($consulta === ''){
            return $this->error->error(mensaje: 'La consulta no puede venir vacia', data: array(
                $this->link->errorInfo(),$consulta));
        }
        $this->transaccion = 'SELECT';


        $archivos_sql_tmp = $this->file_tmp_sql(consulta: $consulta, valida_tabla: $valida_tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al obtener archivos_sql_tmp", data: $archivos_sql_tmp);
        }

        $data = $this->result_out(
            archivos_sql_tmp: $archivos_sql_tmp, campos_encriptados: $campos_encriptados, consulta: $consulta,
            valida_tabla: $valida_tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear registros", data: $data);
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
     */
    public function ejecuta_sql(string $consulta):array|stdClass{
        if($consulta === ''){
            return $this->error->error(mensaje: "Error consulta vacia", data: $consulta.' tabla: '.$this->tabla,
                aplica_bitacora: true);
        }
        try {
            $result = $this->link->query( $consulta);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al ejecutar sql '. $e->getMessage(),
                data: array($e->getCode().' '.$this->tabla.' '.$consulta.' '.$this->tabla,
                    'registro'=>$this->registro),aplica_bitacora: true);
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

    private function file_tmp_sql(string $consulta, bool $valida_tabla = true): array|string
    {
        $key_tmp = $this->key_tmp(consulta: $consulta);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al obtener key tmp", data: $key_tmp);
        }

        $archivos_sql_tmp = $this->ruta_file_tmp_sql(key_tmp: $key_tmp, valida_tabla: $valida_tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al obtener archivos_sql_tmp", data: $archivos_sql_tmp);
        }
        return $archivos_sql_tmp;
    }

    /**
     * @param modelo $modelo Modelo para generacion de descripcion
     * @param array $registro Registro en ejecucion
     * @return array|string
     * @version 1.426.48
     */
    private function genera_descripcion(modelo $modelo, array $registro): array|string
    {
        $valida = $this->valida_registro_modelo(modelo: $modelo,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $descripcion = $this->descripcion_alta(modelo: $modelo, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener descripcion', data: $descripcion);
        }
        return $descripcion;
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
        $r_foto = $modelo_foto->filtro_and(filtro:$filtro);
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

    public function genera_consulta_base( array $columnas = array(), array $columnas_by_table = array(),
                                            bool $columnas_en_bruto = false, array $extension_estructura = array(),
                                            array $renombradas = array()):array|string{

        $this->tabla = str_replace('models\\','',$this->tabla);

        $columnas_seleccionables = $columnas;
        $columnas_sql = (new columnas())->obten_columnas_completas(modelo: $this,
            columnas_by_table: $columnas_by_table, columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $columnas_seleccionables, extension_estructura: $extension_estructura, renombres: $renombradas);
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
     * @param string $namespace_model paquete de origen modelo
     * @example
     *     $modelo = $modelo_base->genera_modelo($name_modelo);
     *
     * @throws errores $name_modelo = vacio
     * @throws errores $name_modelo = numero
     * @throws errores $name_modelo no existe una clase con el nombre del modelo

     */
    public function genera_modelo(string $modelo, string $namespace_model = ''):array|modelo{


        /**
         * PRODUCTO NO CONFORME
         */
        $namespaces = array();

        $namespaces[]  = 'gamboamartin\\administrador\\models\\';
        $namespaces[]  = 'gamboamartin\\empleado\\models\\';
        $namespaces[]  = 'gamboamartin\\facturacion\\models\\';
        $namespaces[]  = 'gamboamartin\\organigrama\\models\\';
        $namespaces[]  = 'gamboamartin\\direccion_postal\\models\\';
        $namespaces[]  = 'gamboamartin\\cat_sat\\models\\';
        $namespaces[]  = 'gamboamartin\\comercial\\models\\';
        $namespaces[]  = 'gamboamartin\\boletaje\\models\\';
        $namespaces[]  = 'gamboamartin\\banco\\models\\';
        $namespaces[]  = 'gamboamartin\\gastos\\models\\';

        $es_namespace_especial_como_mis_inges = false;
        foreach ($namespaces as $namespace) {
            $namespaces_explode = explode($namespace, $modelo);

            if (is_array($namespaces_explode) && count($namespaces_explode)>1) {
                $es_namespace_especial_como_mis_inges = true;
                break;
            }

        }

        if(!$es_namespace_especial_como_mis_inges) {
            $modelo = str_replace('models\\', '', $modelo);
            $modelo = 'models\\' . $modelo;
        }

        if($namespace_model !==''){

            $modelo = str_replace($namespace_model, '', $modelo);
            $modelo = str_replace('models\\', '', $modelo);
            $modelo = $namespace_model.'\\'.$modelo;
        }

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
            $modelos_hijos[$key]['namespace_model']= $modelo['namespace_model'];
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

        $keys = array('nombre_estructura','namespace_model');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $data_modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar data_modelo",data: $valida);
        }

        if(!isset($data_modelo['nombre_estructura'])){
            return $this->error->error(mensaje: 'Error debe existir $data_modelo[\'nombre_estructura\'] ',
                data: $data_modelo);
        }
        $filtro = (new rows())->obten_filtro_para_hijo(data_modelo: $data_modelo,row: $row);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error filtro",data: $filtro);
        }
        $row = $this->asigna_registros_hijo(filtro: $filtro, name_modelo: $name_modelo,
            namespace_model: $data_modelo['namespace_model'], nombre_estructura: $data_modelo['nombre_estructura'],
            row: $row);
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
            $keys = array('nombre_estructura','namespace_model');
            $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $data_modelo);
            if(errores::$error){
                return  $this->error->error(mensaje: "Error al validar data_modelo",data: $valida);
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
                $this->error->error(mensaje: 'Error $name_modelo debe ser un string ', data: $data_modelo, fix: $fix);
            }

            $row = $this->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: $name_modelo, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar registros de hijo', data: $row);
            }

        }

        return $row;
    }

    /**
     * Inicializa las carpetas para models temps
     * @param bool $valida_tabla
     * @return string|array
     * @version 2.10.2.4
     */
    protected function init_archivos_tmp_model(bool $valida_tabla = true): string|array
    {
        $tabla = $this->tabla;
        $tabla = trim($tabla);
        if($valida_tabla) {
            if ($tabla === '') {
                return $this->error->error(mensaje: 'Error tabla vacia', data: $tabla);
            }
        }

        $archivos = (new generales())->path_base.'archivos';
        $archivos = str_replace('//', '/', $archivos);
        if(!file_exists($archivos)){
            mkdir($archivos);
        }
        $archivos_tmp = $archivos.'/tmp';

        $archivos_tmp = str_replace('//', '/', $archivos_tmp);
        if(!file_exists($archivos_tmp)){
            mkdir($archivos_tmp);
        }
        $archivos_tmp_model = $archivos_tmp."/$this->tabla";

        $archivos_tmp_model = str_replace('//', '/', $archivos_tmp_model);

        if(!file_exists($archivos_tmp_model)){
            mkdir($archivos_tmp_model);
        }
        return $archivos_tmp_model;
    }


    /**
     * Integra un value para descripcion select
     * @param array $data Registro en proceso
     * @param string $ds Descripcion previa
     * @param string $key Key de value a integrar
     * @return array|string
     * @version 2.92.6
     */
    private function integra_ds(array $data, string $ds, string $key): array|string
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error al key esta vacio', data: $key);
        }

        $keys = array($key);
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar data', data: $valida);
        }
        $ds_init = $this->ds_init(data:$data,key:  $key);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar descripcion select', data: $ds_init);
        }
        $ds.= $ds_init.' ';
        return $ds;
    }


    private function init_result_base(string $consulta, int $n_registros, array $new_array): stdClass
    {
        $this->registros = $new_array;
        $this->n_registros = (int)$n_registros;
        $this->sql = $consulta;
        $data = new stdClass();
        $data->registros = $new_array;
        $data->n_registros = $n_registros;
        $data->sql = $consulta;
        return $data;
    }

    /**
     * Genera el key para temporal en sql
     * @param string $consulta Consulta a ejecutar
     * @return array|string
     * @version 1.624.56
     */
    private function key_tmp(string $consulta): array|string
    {
        $key_tmp = trim($consulta);

        if($key_tmp === ''){
            return $this->error->error(mensaje: 'Error consulta esta vacia', data:$consulta);
        }

        $key = base64_encode($key_tmp);
        return md5($key);
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
                return $this->error->error(mensaje: 'Error al ajustar rows', data:$row_new);
            }

            $new_array[] = $row_new;
        }

        return $new_array;
    }

    private function maqueta_result(string $consulta, int $n_registros, array $new_array ): array|stdClass
    {
        $init = $this->init_result_base(consulta: $consulta,n_registros:  $n_registros,new_array:  $new_array);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear resultado", data: $init);
        }


        $data = $this->result(consulta: $consulta,n_registros:  $n_registros, new_array: $new_array);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear registros", data: $new_array);
        }
        return $data;
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

    private function registro_descripcion_select(array $data, array $keys_integra_ds): array
    {
        if(!isset($data['descripcion_select'])){

            $ds = $this->descripcion_select(data: $data,keys_integra_ds:  $keys_integra_ds);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al inicializar descripcion select', data: $ds);
            }
            $data['descripcion_select'] =  $ds;
        }
        return $data;
    }

    /**
     * Genera los registros por id
     * @param modelo $entidad Modelo o entidad de relacion
     * @param int $id Identificador de registro a obtener
     * @return array|stdClass
     * @version 1.425.48
     */
    public function registro_por_id(modelo $entidad, int $id): array|stdClass
    {
        if($id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $id debe ser mayor a 0', data: $id);
        }
        $data = $entidad->registro(registro_id: $id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los registros', data: $data);
        }
        return $data;
    }

    private function result(string $consulta, int $n_registros, array $new_array): stdClass
    {
        $data = new stdClass();
        $data->registros = $new_array;
        $data->n_registros = (int)$n_registros;
        $data->sql = $consulta;

        $data->registros_obj = array();
        foreach ($data->registros as $row) {
            $row_obj = (object)$row;
            $data->registros_obj[] = $row_obj;
        }
        return $data;
    }

    private function result_out(string $archivos_sql_tmp, array $campos_encriptados, string $consulta,
                                bool $valida_tabla = true): array|stdClass
    {

        if(file_exists($archivos_sql_tmp) && $this->temp ) {
            $data_out = file_get_contents($archivos_sql_tmp);
            $data_out = base64_decode($data_out);
            $data = unserialize($data_out);

        }
        else{

            $init_archivos_tmp_model = $this->init_archivos_tmp_model(valida_tabla: $valida_tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
            }
            if(file_exists($init_archivos_tmp_model)){
                $rmdir = (new files())->rmdir_recursive(dir: $init_archivos_tmp_model);
                if (errores::$error) {
                    return $this->error->error(mensaje:'Error al eliminar '.$this->tabla, data:$rmdir);
                }
            }

            $data = $this->data_result(campos_encriptados: $campos_encriptados,consulta:  $consulta);
            if (errores::$error) {
                return $this->error->error(mensaje: "Error al parsear registros", data: $data);
            }

            if($this->temp) {
                $data_out = serialize($data);
                $data_out = base64_encode($data_out);
                file_put_contents($archivos_sql_tmp, $data_out);
            }

        }
        return $data;
    }

    /**
     * Genera el resultado de un sql de tipo select
     * @param array $campos_encriptados conjunto de campos encriptados del modelo
     * @param string $consulta Sql
     * @return array|stdClass
     * @version 2.117.12
     */
    private function result_sql(array $campos_encriptados, string $consulta): array|stdClass
    {
        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error(mensaje: "Error consulta vacia", data: $consulta.' tabla: '.$this->tabla);
        }
        $result = $this->ejecuta_sql(consulta: $consulta);

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }

        $r_sql = $result->result;

        $new_array = $this->parsea_registros_envio(r_sql: $r_sql, campos_encriptados: $campos_encriptados);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear registros", data: $new_array);
        }

        $n_registros = $r_sql->rowCount();
        $r_sql->closeCursor();

        $data = new stdClass();
        $data->result = $result;
        $data->r_sql = $r_sql;
        $data->new_array = $new_array;
        $data->n_registros = $n_registros;
        return $data;
    }

    private function ruta_file_tmp_sql(string $key_tmp, bool $valida_tabla = true): array|string
    {
        $archivos_tmp_model = $this->init_archivos_tmp_model(valida_tabla: $valida_tabla);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al obtener archivos_tmp_model", data: $archivos_tmp_model);
        }


        return $archivos_tmp_model."/$key_tmp";
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
     * Valida los datos de un modelo para obtener su registro
     * @param modelo $modelo Modelo a validar
     * @param array|stdClass $registro Registro a verificar
     * @return bool|array
     * @version 1.403.45
     */
    protected function valida_registro_modelo(modelo $modelo, array|stdClass $registro): bool|array
    {
        $modelo->tabla = trim($modelo->tabla);
        if($modelo->tabla === ''){
            return $this->error->error(mensaje: 'Error tabla de modelo esta vacia', data: $modelo->tabla);
        }
        $key_id = $modelo->tabla.'_id';
        $keys = array($key_id);
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        return true;
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



}


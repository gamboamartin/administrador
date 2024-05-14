<?php
namespace base\orm;

use gamboamartin\administrador\modelado\joins;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use JsonException;
use PDO;
use stdClass;
use Throwable;


/**
 * @var bool $aplica_bitacora Si es true insertara en una bitacora de control en la base de datos en adm_bitacora
 * @var bool $aplica_bitacora Si es true insertara solicitara y validara login y token por get session_id
 * @var string $campos_sql Campos de la entidad en forma de SQL
 * @var array $campos_view Campos de la entidad ajustados en un array
 * @var string $consulta Es el query en forma de sql para ser ejecutado en el sistema
 * @var errores $error Objeto para manejo de errores
 * @var bool $es_sincronizable Variable que determina si modelo es sincronizable con una base de datos
 */
class modelo_base{ //PRUEBAS EN PROCESO //DOCUMENTACION EN PROCESO


    public bool $aplica_bitacora = false;
    public bool $aplica_seguridad = false;
    public string $campos_sql = '';
    public array $campos_view = array();
    public string $consulta = '';
    public errores $error ;
    public array $filtro = array();
    public array $hijo = array();
    public PDO $link ;
    public array $patterns = array();
    public array $registro = array();
    public int $registro_id = -1 ;
    public string  $tabla = '' ;
    public string $transaccion = '' ;
    public int $usuario_id = -1;

    public array $registro_upd = array();
    public array $columnas_extra = array();
    public array $columnas = array();
    public array $sub_querys = array();
    public array $campos_obligatorios=array('status');

    public array $tipo_campos = array();

    public base_modelos     $validacion;
    public string $status_default = 'activo';

    public array $filtro_seguridad = array();

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
    public array $parents_data = array();
    public stdClass $atributos;
    public array $atributos_criticos = array();

    protected bool $id_code = false;

    public bool $valida_existe_entidad = true;
    public bool $es_sincronizable = false;

    public bool $integra_datos_base = true;
    public string $campo_llave = "";

    public array $mes;
    public array $dia;

    public array $year;

    public array $campos_entidad = array();

    protected bool $aplica_transacciones_base = true;
    public array $letras = array();


    /**
     * Modelado
     * @param PDO $link Conexion a la BD
     * @param bool $temp Si temp, crea cache de sql del modelo en ejecucion
     */
    public function __construct(
        PDO $link, bool $aplica_transacciones_base = true, array $defaults = array(), array $parents_data = array(),
        bool $temp = false ){

        $this->error = new errores();
        $this->link = $link;
        $this->validacion = new base_modelos();
        $this->temp = false;
        $this->atributos = new stdClass();
        $this->aplica_transacciones_base = $aplica_transacciones_base;


        $this->patterns['double'] = "/^\\$?[1-9]+,?([0-9]*,?[0,9]*)*.?[0-9]{0,4}$/";
        $this->patterns['double_con_cero'] = "/^[0-9]+[0-9]*.?[0-9]{0,4}$/";
        $this->patterns['telefono'] = "/^[0-9]{10}$/";
        $this->patterns['id'] = "/^[1-9]+[0-9]*$/";

        $this->keys_data_filter = array('sentencia','filtro_especial','filtro_rango','filtro_extra','in',
            'not_in', 'diferente_de','sql_extra','filtro_fecha');

        $this->defaults = $defaults;

        $this->parents_data = $parents_data;

        $enero = array('numero_texto'=>'01','numero'=>1,'nombre'=>'ENERO','abreviado'=>'ENE');
        $febrero = array('numero_texto'=>'02','numero'=>2,'nombre'=>'FEBRERO','abreviado'=>'FEB');
        $marzo = array('numero_texto'=>'03','numero'=>3,'nombre'=>'MARZO','abreviado'=>'MAR');
        $abril = array('numero_texto'=>'04','numero'=>4,'nombre'=>'ABRIL','abreviado'=>'ABR');
        $mayo = array('numero_texto'=>'05','numero'=>5,'nombre'=>'MAYO','abreviado'=>'MAY');
        $junio = array('numero_texto'=>'06','numero'=>6,'nombre'=>'JUNIO','abreviado'=>'JUN');
        $julio = array('numero_texto'=>'07','numero'=>7,'nombre'=>'JULIO','abreviado'=>'JUL');
        $agosto = array('numero_texto'=>'08','numero'=>8,'nombre'=>'AGOSTO','abreviado'=>'AGO');
        $septiembre = array('numero_texto'=>'09','numero'=>9,'nombre'=>'SEPTIEMBRE','abreviado'=>'SEP');
        $octubre = array('numero_texto'=>'10','numero'=>10,'nombre'=>'OCTUBRE','abreviado'=>'OCT');
        $noviembre = array('numero_texto'=>'11','numero'=>11,'nombre'=>'NOVIEMBRE','abreviado'=>'NOV');
        $diciembre = array('numero_texto'=>'12','numero'=>12,'nombre'=>'DICIEMBRE','abreviado'=>'DIC');

        $this->mes['espaniol'] = array('01'=>$enero,'02'=>$febrero,'03'=>$marzo,'04'=>$abril,
            '05'=>$mayo,'06'=>$junio,'07'=>$julio,'08'=>$agosto,'09'=>$septiembre,'10'=>$octubre,
            '11'=>$noviembre,'12'=>$diciembre);

        $lunes = array('numero_texto'=>'01','numero'=>1,'nombre'=>'LUNES','abreviado'=>'LUN');
        $martes = array('numero_texto'=>'02','numero'=>2,'nombre'=>'MARTES','abreviado'=>'MAR');
        $miercoles = array('numero_texto'=>'03','numero'=>3,'nombre'=>'MIERCOLES','abreviado'=>'MIE');
        $jueves = array('numero_texto'=>'04','numero'=>4,'nombre'=>'JUEVES','abreviado'=>'JUE');
        $viernes = array('numero_texto'=>'05','numero'=>5,'nombre'=>'VIERNES','abreviado'=>'VIE');
        $sabado = array('numero_texto'=>'06','numero'=>6,'nombre'=>'SABADO','abreviado'=>'SAB');
        $domingo = array('numero_texto'=>'07','numero'=>7,'nombre'=>'DOMINGO','abreviado'=>'DOM');

        $this->dia['espaniol'] = array('01'=>$lunes,'02'=>$martes,'03'=>$miercoles,'04'=>$jueves,
            '05'=>$viernes,'06'=>$sabado,'07'=>$domingo);

        $_2019 = array('numero_texto'=>'2019','numero'=>2019,'nombre'=>'DOS MIL DIECINUEVE','abreviado'=>19);
        $_2020 = array('numero_texto'=>'2020','numero'=>2020,'nombre'=>'DOS MIL VEINTE','abreviado'=>20);
        $_2021 = array('numero_texto'=>'2021','numero'=>2021,'nombre'=>'DOS MIL VIENTIUNO','abreviado'=>21);
        $_2022 = array('numero_texto'=>'2022','numero'=>2022,'nombre'=>'DOS MIL VIENTIDOS','abreviado'=>22);
        $_2023 = array('numero_texto'=>'2023','numero'=>2023,'nombre'=>'DOS MIL VIENTITRES','abreviado'=>23);
        $_2024 = array('numero_texto'=>'2024','numero'=>2024,'nombre'=>'DOS MIL VIENTICUATRO','abreviado'=>24);
        $_2025 = array('numero_texto'=>'2025','numero'=>2025,'nombre'=>'DOS MIL VIENTICINCO','abreviado'=>25);
        $_2026 = array('numero_texto'=>'2026','numero'=>2026,'nombre'=>'DOS MIL VIENTISEIS','abreviado'=>26);
        $_2027 = array('numero_texto'=>'2027','numero'=>2027,'nombre'=>'DOS MIL VIENTISIETE','abreviado'=>27);
        $_2028 = array('numero_texto'=>'2028','numero'=>2028,'nombre'=>'DOS MIL VIENTIOCHO','abreviado'=>28);
        $_2029 = array('numero_texto'=>'2029','numero'=>2029,'nombre'=>'DOS MIL VIENTINUEVE','abreviado'=>29);
        $_2030 = array('numero_texto'=>'2030','numero'=>2030,'nombre'=>'DOS MIL TREINTA','abreviado'=>30);
        $this->year['espaniol'] = array(2019=>$_2019,2020=>$_2020,2021=>$_2021,2022=>$_2022,2023=>$_2023,2024=>$_2024,
            2025=>$_2025,2026=>$_2026,2027=>$_2027,2028=>$_2028,2029=>$_2029,2030=>$_2030);


        $this->letras = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S',
            'T','U','V','W','X','Y','Z');


    }



    /**
     *
     * @return array
     * @throws JsonException
     */
    final protected function aplica_desactivacion_dependencias(): array
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
     * Integra los campos base de una entidad
     * @param array $data Datos de transaccion
     * @param modelo $modelo Modelo en ejecucion
     * @param int $id Identificador
     * @param array $keys_integra_ds Campos para generar la descripcion select
     * @return array
     * @final rev
     */
    protected function campos_base(array $data, modelo $modelo, int $id = -1,
                                   array $keys_integra_ds = array('codigo','descripcion')): array
    {

        if( !isset($data['codigo'])){
            if(isset($data['descripcion'])){
                $data['codigo'] = $data['descripcion'];
            }
        }

        if(!isset($data['descripcion']) && $id > 0){
            $registro_previo = $modelo->registro(registro_id: $id, columnas_en_bruto: true, retorno_obj: true);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error obtener registro previo', data: $registro_previo);
            }
            $data['descripcion'] = $registro_previo->descripcion;
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

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función columnas_data
     *
     * Esta función se encarga de crear un objeto con varias cadenas de consultas SQL necesarias
     * para interactuar con la base de datos.
     *
     * @param string $columnas_extra_sql Representa cadenas de SQL extras para las columnas.
     * @param string $columnas_sql Representa las cadenas de SQL para las columnas.
     * @param string $sub_querys_sql Representa las cadenas de SQL para las subconsultas.
     *
     * @return stdClass Un objeto con propiedades que contienen las cadenas SQL.
     *
     * Las propiedades del objeto retornado son:
     * - columnas_sql: Contiene la cadena SQL para las columnas.
     * - sub_querys_sql: Contiene la cadena SQL para las subconsultas.
     * - columnas_extra_sql: Contiene las cadenas SQL extras para las columnas.
     * @version 16.135.0
     */
    private function columnas_data(string $columnas_extra_sql, string $columnas_sql, string $sub_querys_sql): stdClass
    {
        $sub_querys_sql = trim($sub_querys_sql);
        $columnas_extra_sql = trim($columnas_extra_sql);
        $columnas_sql = trim($columnas_sql);

        $columns_data = new stdClass();
        $columns_data->columnas_sql = $columnas_sql;
        $columns_data->sub_querys_sql = $sub_querys_sql;
        $columns_data->columnas_extra_sql = $columnas_extra_sql;


        return $columns_data;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Calcula y aplica la cadena SQL final de columnas al consulta actual en ejecución.
     *
     * @param string $column_data Cadena SQL parcial para una columna
     * @param string $columns_final Cadena SQL actual para todas las columnas
     *
     * @return string Cadena SQL final después de agregar $column_data
     * @version 16.143.0
     */
    private function columns_final(string $column_data, string $columns_final): string
    {
        $columns_final = trim ($columns_final);
        $column_data = trim ($column_data);
        if($columns_final === ''){
            $columns_final.=$column_data;
        }
        else{
            if($column_data !==''){
                $columns_final = $columns_final.','.$column_data;
            }
        }
        return $columns_final;

    }

    /**
     * Inicializa los elementos para una transaccion
     * @param array $data Datos de campos a automatizar
     * @param array $keys_integra_ds Campos de parent a integrar en select
     * @return array
     */
    final protected function data_base(array $data, array $keys_integra_ds = array('codigo','descripcion')): array
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método ejecuta_consulta de la clase modelo_base
     *
     * Este método se encarga de ejecutar una consulta SQL. Recibe como parámetros la consulta,
     * una lista de campos encriptados, una lista de columnas con totales y otra lista relacionada con el "hijo".
     *
     * @param string $consulta La consulta SQL a ejecutar
     * @param array $campos_encriptados Lista de campos que han sido encriptados
     * @param array $columnas_totales Lista de columnas que contienen totales
     * @param array $hijo Lista relacionada con el "hijo"
     *
     * @throws errores Si la consulta está vacía o si hay errores al parsear los registros
     *
     * @return array|stdClass Si no hay errores, retorna los datos de la consulta.
     * Si hay errores, retorna los mensajes de error correspondientes
     *
     * Algoritmo:
     * 1. Se asigna la lista "hijo" a la propiedad $hijo de la clase
     * 2. Se verifica que la consulta no esté vacía
     * 3. Si la consulta está vacía, se devuelve un mensaje de error
     * 4. Se asigna el valor 'SELECT' a la propiedad $transaccion de la clase
     * 5. Se llama al método data_result, pasando la consulta y las listas como parámetros
     * 6. Si hay errores al parsear los registros, se devuelve un mensaje de error
     * 7. Si no hay errores, se retornan los datos de la consulta
     *
     * @version 18.31.0
     */
    final public function ejecuta_consulta(string $consulta, array $campos_encriptados = array(),
                                           array $columnas_totales = array(), array $hijo = array()): array|stdClass{
        $this->hijo = $hijo;
        if(trim($consulta) === ''){
            return $this->error->error(mensaje: 'La consulta no puede venir vacia', data: array(
                $this->link->errorInfo(),$consulta),es_final: true);
        }
        $this->transaccion = 'SELECT';

        $data = (new _result())->data_result(campos_encriptados: $campos_encriptados,
            columnas_totales: $columnas_totales, consulta: $consulta,modelo: $this);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear registros", data: $data);
        }


        return $data;

    }

    /**
     * TOTAL
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.modelo_base.ejecuta_sql.21.5.0
     * Ejecuta una consulta SQL y devuelve un objeto con los resultados de la consulta,
     * el ID del registro recién insertado, y otros detalles sobre la operación realizada.
     *
     * @param string $consulta La consulta SQL que se va a ejecutar.
     * @return array|stdClass Devuelve un objeto con datos como el mensaje, la consulta, el resultado,
     *                        el registro, el ID del registro y la salida.
     *                        En caso de error, devuelve un array con el mensaje y los datos del error.
     *
     * @version 21.5.0
     */
    final public function ejecuta_sql(string $consulta):array|stdClass{
        if($consulta === ''){
            return $this->error->error(mensaje: "Error consulta vacia", data: $consulta.' tabla: '.$this->tabla,
                aplica_bitacora: true, es_final: true);
        }
        try {
            $result = $this->link->query( $consulta);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al ejecutar sql '. $e->getMessage(),
                data: array($e->getCode().' '.$this->tabla.' '.$consulta.' '.$this->tabla,
                    'registro'=>$this->registro),aplica_bitacora: true,es_final: true);
        }
        if($this->transaccion ==='INSERT'){
            $this->campo_llave === "" ? $this->registro_id = $this->link->lastInsertId() :
                $this->registro_id = $this->registro[$this->campo_llave];
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
     * TOTAL
     * Verifica si un modelo cumple con una característica especial de nombres de espacio
     *
     * @param string $modelo Nombre del modelo a verificar
     * @param array $namespaces Lista de nombres de espacio a verificar en el modelo
     *
     * Esta función verifica si el nombre del modelo contiene uno de los nombres de espacio
     * proporcionados.
     *
     * Si el modelo o alguno de los nombres de espacio está vacío, la función retorna un error.
     *
     * Si se encuentra alguna coincidencia, interrumpe la verificación y devuelve 'true'.
     * En caso contrario, devuelve 'false'.
     *
     * @return bool|array Devuelve 'true' si el modelo contiene uno de los nombres de espacio, 'false' en caso contrario
     * @version 18.15.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.modelo_base.es_namespace_especial.21.8.0
     *
     */
    private function es_namespace_especial(string $modelo, array $namespaces): bool|array
    {
        if($modelo === ''){
            return $this->error->error(mensaje: "Error modelo vacio", data: $modelo, es_final: true);
        }

        $es_namespace_especial_como_mis_inges = false;
        foreach ($namespaces as $namespace) {
            $namespace = trim($namespace);
            if($namespace === ''){
                return $this->error->error(mensaje: "Error namespace vacio", data: $namespaces, es_final: true);
            }

            $namespaces_explode = explode($namespace, $modelo);

            if (is_array($namespaces_explode) && count($namespaces_explode)>1) {
                $es_namespace_especial_como_mis_inges = true;
                break;
            }

        }
        return $es_namespace_especial_como_mis_inges;
    }


    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método extra_columns
     *
     * Este método se utiliza para agregar subqueries y columnas adicionales al SQL final.
     * Esta función genera las sentencias SQL para subquerys y columnas extra, las une y las devuelve.
     *
     * @param array $columnas Array de las columnas presentes en el modelo.
     * @param array $columnas_seleccionables Array de columnas que son seleccionables.
     * @param string $columnas_sql String de las columnas separadas por comas para la consulta SQL.
     * @param bool $con_sq Un indicador para decidir si se deben agregar subquerys.
     *
     * @return stdClass|array Objeto que contiene las sentencias SQL finales para subquerys y columnas extras.
     * @version 16.130.0
     *
     */
    private function extra_columns(
        array $columnas, array $columnas_seleccionables, string $columnas_sql, bool $con_sq): stdClass|array
    {
        $sub_querys_sql = '';
        $columnas_extra_sql = '';
        if($con_sq) {
            $sub_querys_sql = (new columnas())->sub_querys(columnas: $columnas_sql, modelo: $this,
                columnas_seleccionables: $columnas_seleccionables);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar sub querys en ' . $this->tabla, data: $sub_querys_sql);
            }

            $columnas_extra_sql = (new columnas())->genera_columnas_extra(columnas: $columnas, modelo: $this);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar columnas', data: $columnas_extra_sql);
            }
        }

        $data = new stdClass();
        $data->sub_querys_sql = $sub_querys_sql;
        $data->columnas_extra_sql = $columnas_extra_sql;

        return $data;


    }

    /**
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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Genera una cadena que contiene las columnas finales basadas en los datos de las columnas proporcionados.
     *
     * @param stdClass $columns_data El objeto que contiene los datos de las columnas que se deben procesar.
     *
     * @return string|array Un string que representa las columnas finales obtenidas de los datos de entrada de las columnas.
     *
     * @version 16.151.0
     */
    private function genera_columns_final(stdClass $columns_data): string|array
    {
        $columns_final = '';
        foreach ($columns_data as $column_data){
            $column_data = trim($column_data);
            $columns_final = trim($columns_final);

            $columns_final = $this->columns_final(column_data: $column_data,columns_final:  $columns_final);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar columns_final', data: $columns_final);
            }
        }
        return $columns_final;

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
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Final public function genera_consulta_base() en la clase modelo_base.
     *
     * Este método facilita la construcción de consultas SQL básicas. Con base en
     * los parámetros de entrada, este método genera una consulta que refleja las
     * necesidades especificadas.
     *
     * @param array  $columnas Un array que indica las columnas a seleccionar.
     * @param array  $columnas_by_table Un array con las columnas agrupadas por tabla.
     * @param bool   $columnas_en_bruto Flag para indicar si las columnas se usarán en su forma bruta.
     * @param bool   $con_sq Flag para indicar si la consulta incluirá sub-consultas.
     * @param bool   $count Flag para indicar si la consulta tendrá una cláusula COUNT.
     * @param array  $extension_estructura Un array para expandir la estructura.
     * @param array  $extra_join Un array para especificar joins adicionales.
     * @param array  $renombradas Un array con columnas renombradas.
     *
     * @return array|string Devuelve una cadena que representa la consulta SQL generada o un array de errores si se
     * produce algún problema.
     * @version 16.167.0
     */

    final public function genera_consulta_base( array $columnas = array(), array $columnas_by_table = array(),
                                                bool $columnas_en_bruto = false, bool $con_sq = true,
                                                bool $count = false, array $extension_estructura = array(),
                                                array $extra_join = array(),
                                                array $renombradas = array()):array|string{


        $this->tabla = str_replace('models\\','',$this->tabla);

        $columnas_seleccionables = $columnas;

        $columnas_sql = (new columnas())->obten_columnas_completas(modelo: $this,
            columnas_by_table: $columnas_by_table, columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $columnas_seleccionables, extension_estructura: $extension_estructura,
            extra_join: $extra_join, renombres: $renombradas);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener columnas en '.$this->tabla,data: $columnas_sql);
        }


        $tablas = (new joins())->tablas(columnas: $this->columnas, extension_estructura:  $extension_estructura,
            extra_join: $extra_join, modelo_tabla: $this->tabla, renombradas: $renombradas, tabla: $this->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar joins e '.$this->tabla, data: $tablas);
        }

        $columns_final = $this->integra_columns_final(columnas: $columnas,
            columnas_seleccionables:  $columnas_seleccionables,columnas_sql:  $columnas_sql,
            con_sq:  $con_sq,count:  $count);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columns_final', data: $columns_final);
        }

        return /** @lang MYSQL */ "SELECT $columns_final FROM $tablas";
    }


    /**
     * TOTAL
     * Genera una instancia del modelo.
     *
     * Esta función se encarga de generar una instancia del modelo.
     *
     * Primero, genera el nombre del modelo utilizando la función `genera_name_modelo`.
     * Si se encuentra un error durante este paso, se lanza un error con el mensaje "Error al maquetar name modelo".
     *
     * Luego, valida el modelo generado utilizando la función `valida_data_modelo`.
     * Si se encuentra un error durante la validación, se lanza un error con el mensaje "Error al validar modelo".
     *
     * Finalmente, si todos los pasos previos son exitosos, se genera y se devuelve una nueva instancia del modelo.
     *
     * @param string $modelo El nombre del modelo para generar.
     * @param string $namespace_model El namespace del modelo. Valor predeterminado es una cadena vacía.
     * @return array|modelo Nueva instancia del modelo o un array con información del error.
     * @throws errores Error al maquetar nombre del modelo.
     * @throws errores Error al validar modelo.
     * @version 18.22.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.modelo_base.genera_modelo.21.17.0
     */
    final public function genera_modelo(string $modelo, string $namespace_model = ''):array|modelo{

        $modelo = $this->genera_name_modelo(modelo: $modelo,namespace_model:  $namespace_model);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al maquetar name modelo",data: $modelo);
        }
        $valida = $this->validacion->valida_data_modelo(name_modelo: $modelo);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida);
        }
        return new $modelo($this->link);
    }

    public static function modelo_new(PDO $link,string $modelo, string $namespace_model): modelo|array
    {
        $modelo_gen = (new modelo_base(link: $link))->genera_modelo(modelo: $modelo,namespace_model: $namespace_model);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al generar modelo',data: $modelo);
        }

        return $modelo_gen;
    }




    /**
     * TOTAL
     * Genera el nombre del modelo.
     *
     * Esta función se encarga de generar el nombre del modelo.
     * Inicialmente, obtiene todos los namespaces disponibles.
     * Luego, verifica si el modelo pertenece a un namespace especial.
     * Finalmente, devuelve el nombre del modelo o lanza un error en caso de que lo haya.
     *
     * @param string $modelo El nombre del modelo a generar.
     * @param string $namespace_model El namespace del modelo.
     * @return array|string El nombre del modelo generado.
     *
     * @version 18.22.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.modelo_base.genera_name_modelo.21.8.0
     */
    private function genera_name_modelo(string $modelo, string $namespace_model): array|string
    {
        $namespaces = $this->namespaces();
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al obtener namespaces",data: $namespaces);
        }

        $es_namespace_especial = $this->es_namespace_especial(
            modelo: $modelo,namespaces:  $namespaces);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar namespaces",data: $es_namespace_especial);
        }

        $modelo = $this->name_modelo(es_namespace_especial: $es_namespace_especial,
            modelo:  $modelo,namespace_model:  $namespace_model);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al maquetar name modelo",data: $modelo);
        }

        return $modelo;

    }






    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Integra la información final de las columnas, tras aplicar varias manipulaciones.
     *
     * @param array $columnas Lista de columnas.
     * @param array $columnas_seleccionables Lista de columnas seleccionables.
     * @param string $columnas_sql Las columnas ya formateadas como cadena SQL.
     * @param bool $con_sq Condición para la ejecución de subconsultas.
     * @param bool $count Determina si se debe contar el número total de registros.
     *
     * @return array|string Devuelve una cadena con las columnas finales para la consulta SQL.
     *                      En caso de activarse $count, se devuelve "COUNT(*) AS total_registros".
     *
     * @throws errores Retorna error si falla alguna de las etapas de generación de columnas.
     * @version 16.165.0
     */
    private function integra_columns_final(array $columnas, array $columnas_seleccionables, string $columnas_sql,
                                           bool $con_sq, bool $count): array|string
    {
        $extra_columns = $this->extra_columns(columnas: $columnas,columnas_seleccionables:  $columnas_seleccionables,
            columnas_sql:  $columnas_sql,con_sq:  $con_sq);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar extra_columns', data: $extra_columns);
        }


        $columns_data = $this->columnas_data(columnas_extra_sql: $extra_columns->columnas_extra_sql,
            columnas_sql:  $columnas_sql,
            sub_querys_sql:  $extra_columns->sub_querys_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar columnas_data', data: $columns_data);
        }

        $columns_final = $this->genera_columns_final(columns_data: $columns_data);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar columns_final', data: $columns_final);
        }

        if($count){
            $columns_final = "COUNT(*) AS total_registros";
        }

        return $columns_final;

    }


    /**
     * Integra un value para descripcion select
     * @param array $data Registro en proceso
     * @param string $ds Descripcion previa
     * @param string $key Key de value a integrar
     * @return array|string
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




    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función genera una clave temporal basada en la consulta proporcionada.
     *
     * @param string $consulta Consulta utilizada para generar la clave.
     * @return array|string Devuelve un string MD5 basado en la consulta proporcionada o un error si la consulta está vacía.
     * @version 13.20.0
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
     * TOTAL
     * Genera el nombre completo del modelo que se utilizará para las operaciones de base de datos.
     *
     * @param bool $es_namespace_especial Indica si el espacio de nombre del modelo es especial. Si es verdadero, el nombre del modelo no se manipulará más.
     * @param string $modelo El nombre del modelo para el que se está generando el nombre.
     * @param string $namespace_model El espacio de nombres del modelo.
     *
     * @return string|array El nombre completo del modelo después de la manipulación, o un objeto Error si ocurrió un error durante el proceso.
     *
     * @throws errores Se lanza una excepción si el nombre del modelo está vacío después de quitar los espacios
     *                en blanco o si ocurrió un error durante la manipulación del nombre del modelo.
     * @version 18.20.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.modelo_base.name_modelo.21.8.0
     */
    private function name_modelo(bool $es_namespace_especial, string $modelo, string $namespace_model): string|array
    {
        $modelo = trim($modelo);
        if($modelo === ''){
            return  $this->error->error(mensaje: "Error modelo esta vacio",data: $modelo, es_final: true);
        }
        if(!$es_namespace_especial) {
            $modelo = $this->name_modelo_base(modelo: $modelo);
            if(errores::$error){
                return  $this->error->error(mensaje: "Error al maquetar name modelo",data: $modelo);
            }
        }
        if($namespace_model !==''){
            $modelo = $this->name_modelo_ajustado(modelo: $modelo, namespace_model: $namespace_model);
            if(errores::$error){
                return  $this->error->error(mensaje: "Error al maquetar name modelo",data: $modelo);
            }
        }
        return trim($modelo);
    }

    /**
     * TOTAL
     * Ajusta el nombre del modelo dado su espacio de nombres.
     *
     * Este método se encarga de tomar el nombre del modelo junto con su espacio de nombres
     * y procede a realizar ajustes para obtener una representación limpia del nombre del modelo.
     * El espacio de nombres y el modelo son validados antes de realizar cualquier operación.
     * En caso de error, se devuelve un mensaje de error con detalle del problema.
     *
     * @param string $modelo El nombre del modelo a ajustar.
     * @param string $namespace_model El espacio de nombres del modelo.
     * @return string|array Retorna el nombre del modelo ajustado si es exitoso, y un error si ocurre una excepción.
     *
     * @throws errores Si los parámetros de entrada están vacíos .
     * @version 18.19.0
     * @ur https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.modelo_base.name_modelo_ajustado.21.8.0
     */
    private function name_modelo_ajustado(string $modelo, string $namespace_model): string|array
    {
        $namespace_model = trim($namespace_model);
        if($namespace_model === ''){
            return  $this->error->error(mensaje: "Error namespace_model esta vacio",data: $namespace_model);
        }
        if($modelo === ''){
            return  $this->error->error(mensaje: "Error modelo esta vacio",data: $modelo);
        }
        $modelo = str_replace($namespace_model, '', $modelo);
        $modelo = str_replace('models\\', '', $modelo);
        return $namespace_model.'\\'.$modelo;


    }

    /**
     * TOTAL
     *  Método Privado name_modelo_base
     *
     * Este método se encarga de procesar el nombre del modelo proporcionado y rechazar cualquier valor vacío.
     * Reemplaza el prefijo 'models\' en el nombre del modelo y devuelve el nombre del modelo con el prefijo 'models\' añadido.
     *
     * @param string $modelo El nombre del modelo a procesar.
     *
     * @return string|array Retorna el nombre del modelo procesado, o un error si el nombre del modelo está vacío.
     *
     * @throws errores Se generará una excepción si el nombre del modelo está vacío.
     * @version 18.18.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.modelo_base.name_modelo_base.21.8.0
     */
    private function name_modelo_base(string $modelo): string|array
    {
        $modelo = trim($modelo);
        if($modelo === ''){
            return  $this->error->error(mensaje: "Error modelo esta vacio",data: $modelo, es_final: true);
        }
        $modelo = str_replace('models\\', '', $modelo);
        return 'models\\' . $modelo;

    }

    /**
     * TOTAL
     * Obtiene los namespaces de paquetes par asu uso y normalizacion en modelos
     *
     * @return array
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.orm.modelo_base.namespaces.21.8.0
     */
    private function namespaces(): array
    {

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
        $namespaces[]  = 'gamboamartin\\nomina\\models\\';
        $namespaces[]  = 'gamboamartin\\im_registro_patronal\\models\\';
        $namespaces[]  = 'gamboamartin\\importador\\models\\';
        $namespaces[]  = 'gamboamartin\\importador_cva\\models\\';
        $namespaces[]  = 'gamboamartin\\proceso\\models\\';
        $namespaces[]  = 'gamboamartin\\notificaciones\\models\\';
        $namespaces[]  = 'gamboamartin\\inmuebles\\models\\';

        return $namespaces;

    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Función para obtener el nombre de una tabla.
     *
     * @param string $tabla_original      El nombre original de la tabla.
     * @param string $tabla_renombrada    El nombre renombrado de la tabla.
     *
     * @return array|string               Retorna el nombre de la tabla si los parámetros son válidos, en otro caso retorna un mensaje de error.
     *
     * @throws errores                      Lanza una excepción si ambos parámetros están vacíos.
     * @version 15.52.1
     */
    final public function obten_nombre_tabla(string $tabla_original, string $tabla_renombrada):array|string{

        if(trim($tabla_original)==='' && trim($tabla_renombrada) === ''){
            return $this->error->error(mensaje: 'Error no pueden venir vacios todos los parametros',
                data: $tabla_renombrada, es_final: true);
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
     * Integra descripcion select in row
     * @param array $data Datos enviados desde modelo
     * @param array $keys_integra_ds Keys a integrar
     * @return array
     */
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


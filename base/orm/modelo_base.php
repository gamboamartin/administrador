<?php
namespace base\orm;

use config\generales;
use gamboamartin\administrador\modelado\joins;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use JsonException;
use PDO;
use PDOStatement;
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
     * POR DOCUMENTAR EN WIKI
     * Ajusta el contenido de un registro asignando valores encriptados y elementos con dependencia basada en modelos
     * hijos
     * @param array $campos_encriptados Conjunto de campos a encriptar desencriptar declarados en el modelo en ejecucion
     * @param array $modelos_hijos Conjunto de modelos que dependen del modelo en ejecucion
     * @param array $row Registro a integrar elementos encriptados o con dependientes
     * @return array Registro con los datos ajustados tanto en la encriptacion como de sus dependientes
     * @version 14.15.0
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
     * POR DOCUMENTAR EN WIKI
     * Asigna registros hijos al modelo dado según el filtro proporcionado.
     *
     * @param array  $filtro El filtro a aplicar al conjunto de registros.
     * @param string $name_modelo Nombre del modelo.
     * @param string $namespace_model Namespace del modelo.
     * @param string $nombre_estructura Nombre de la estructura en la que se asignarán los registros.
     * @param array  $row El array al cual se asignarán los registros del modelo.
     *
     * @return array Retorna un array con los registros asignados, o un error si algo sale mal.
     * @version 14.11.0
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
     * POR DOCUMENTAR EN WIKI
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
     * POR DOCUMENTAR EN WIKI
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
     * POR DOCUMENTAR EN WIKI
     * Método data_result
     *
     * Este método se utiliza para la ejecucion del sql y el retorno del resultado en forma de objeto
     *
     * @param array $campos_encriptados Array de campos encriptados
     * @param string $consulta Consulta SQL
     *
     * @return array|stdClass Devuelve un array o un objeto stdClass dependiendo del resultado de la consulta
     *
     * @version 14.26.0
     */
    private function data_result(array $campos_encriptados, string $consulta): array|stdClass
    {
        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error(mensaje: "Error consulta vacia", data: $consulta.' tabla: '.$this->tabla);
        }
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
     *
     * POR DOCUMENTAR EN WIKI
     * Ejecuta una consulta en la base de datos y devuelve los resultados.
     *
     * @param string $consulta La consulta a ejecutar.
     * @param array  $campos_encriptados Una lista de campos a encriptar en la consulta.
     * @param array  $hijo Una lista de consultas hijo para la consulta principal.
     *
     * @return array|stdClass Retorna un array o un objeto stdClass si la consulta se ejecutó correctamente, o
     * un error si algo salió mal.
     * @version 14.27.0
     */
    final public function ejecuta_consulta(string $consulta, array $campos_encriptados = array(),
                                     array $hijo = array()): array|stdClass{
        $this->hijo = $hijo;
        if(trim($consulta) === ''){
            return $this->error->error(mensaje: 'La consulta no puede venir vacia', data: array(
                $this->link->errorInfo(),$consulta));
        }
        $this->transaccion = 'SELECT';

        $data = $this->data_result(
            campos_encriptados: $campos_encriptados, consulta: $consulta);
        if (errores::$error) {
            return $this->error->error(mensaje: "Error al parsear registros", data: $data);
        }


        return $data;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Ejecuta una consulta SQL y devuelve un objeto con los resultados de la consulta,
     * el ID del registro recién insertado, y otros detalles sobre la operación realizada.
     *
     * @param string $consulta La consulta SQL que se va a ejecutar.
     * @return array|stdClass Devuelve un objeto con datos como el mensaje, la consulta, el resultado,
     *                        el registro, el ID del registro y la salida.
     *                        En caso de error, devuelve un array con el mensaje y los datos del error.
     *
     * @version 13.21.0
     */
    final public function ejecuta_sql(string $consulta):array|stdClass{
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
     * POR DOCUMENTAR EN WIKI
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
     * POR DOCUMENTAR EN WIKI
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
     * POR DOCUMENTAR EN WIKI
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
     * POD DOCUMENTAR EN WIKI
     * Genera un nuevo modelo a partir de las cadenas de nombre del modelo proporcionadas.
     *
     * @param string $modelo Nombre del modelo.
     * @param string $namespace_model Namespace del modelo, opcional.
     * @return array|modelo Retorna una nueva instancia de la clase del modelo o error si algo sale mal.
     * @version 14.9.0
     */
    final public function genera_modelo(string $modelo, string $namespace_model = ''):array|modelo{


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
        $namespaces[]  = 'gamboamartin\\nomina\\models\\';
        $namespaces[]  = 'gamboamartin\\im_registro_patronal\\models\\';
        $namespaces[]  = 'gamboamartin\\importador\\models\\';
        $namespaces[]  = 'gamboamartin\\importador_cva\\models\\';
        $namespaces[]  = 'gamboamartin\\proceso\\models\\';
        $namespaces[]  = 'gamboamartin\\notificaciones\\models\\';
        $namespaces[]  = 'gamboamartin\\inmuebles\\models\\';
        $namespaces[]  = 'tglobally\\tg_nomina\\models\\';
        $namespaces[]  = 'tglobally\\tg_empleado\\models\\';
        $namespaces[]  = 'tglobally\\tg_notificacion\\models\\';

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
     * POR DOCUMENTAR EN WIKI
     * Maqueta un arreglo para la generacion de modelos y sus registros asignados a un query para obtener sus
     * dependientes o dependencias
     * de la siguiente forma $registro['tabla']= $reg[0][campos de registro], $reg[n][campos de registro]
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
     * @version 14.2.0
     */
    private function genera_modelos_hijos(): array{
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
     * POR DOCUMENTAR EN WIKI
     * Genera un registro hijo.
     *
     * Este método se encarga de generar un registro hijo a partir de los datos proporcionados en
     * $data_modelo y $row. De verificarse errores durante este proceso se retorna una anomalía
     * con detalles del error.
     *
     * @param array $data_modelo Datos del modelo.
     * @param string $name_modelo Nombre del modelo.
     * @param array $row Registro actual.
     * @return array Resultado de proceso.
     * @version 14.13.0
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
     * POR DOCUMENTAR EN WIKI
     * Funcion que asigna y genera los registros encontrados de hijos en un registro
     * @param array $modelos_hijos datos de parametrizacion de datos para la ejecucion de obtencion de los registros
     * @param array $row registro padre al que se le asignaran los hijos
     * @example
     *      $row = (array) $row;
     *      $row = $this->genera_registros_hijos($modelos_hijos,$row);
     * @return array registro del modelo con registros hijos asignados
     * @throws errores $data_modelo['nombre_estructura'] no existe
     * @version 14.14.0

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
     * POR DOCUMENTAR EN WIKI
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
     * Inicializa la base del resultado a partir de la consulta, el número de registros y los registros obtenidos.
     *
     * @param string $consulta       La consulta SQL que se ejecutó para obtener los registros
     * @param int $n_registros       El número total de registros devueltos por la consulta
     * @param array $new_array       Los registros obtenidos por la consulta
     *
     * @return stdClass              Retorna un objeto que contiene los registros, el número de registros y la consulta SQL
     *
     * @version 14.21.0
     */
    private function init_result_base(string $consulta, int $n_registros, array $new_array): stdClass
    {

        $this->registros = $new_array;
        $this->n_registros = $n_registros;
        $this->sql = $consulta;
        $data = new stdClass();
        $data->registros = $new_array;
        $data->n_registros = $n_registros;
        $data->sql = $consulta;

        return $data;
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
     * POR DOCUMENTAR EN WIKI
     * Maqueta el arreglo de registros de un modelo de base de datos
     *
     * Este método recorre una lista de registros devueltos de una consulta SQL
     * y los ajusta según los campos específicos de cada modelo. Después,
     * retorna un nuevo arreglo ajustado.
     *
     * @param array $modelos_hijos Los modelos dependientes del modelo principal
     * @param PDOStatement $r_sql Un objeto de consulta SQL que contiene los datos
     * @param array $campos_encriptados Lista de campos a encriptar
     * @return array Retorna un arreglo de registros ajustado
     * @throws errores Si hay un error, produce una excepción con los detalles del error
     * @version 14.16.0
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

    /**
     * POR DOCUMENTAR EN WIKI
     * Función que se encarga de maquetar el resultado de una consulta.
     *
     * @param string $consulta La consulta a realizar.
     * @param int $n_registros El número de registros a devolver.
     * @param array $new_array El array que contiene los nuevos datos.
     * @return array|stdClass Devuelve un array con los resultados o un objeto stdClass en caso de error.
     * @throws errores Lanza una excepción de tipo errores en caso de error al parsear el resultado o los registros.
     * @version 14.23.0
     */
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
     * POR DOCUMENTAR EN WIKI
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
     * POR DOCUMENTAR WIKI
     * Procesa los registros devueltos por una declaración SQL de PDO
     *
     * Esta función toma una declaración SQL de PDO y un arreglo de campos encriptados.
     * Genera modelos hijos y reformatea el arreglo de registros.
     *
     * @param PDOStatement $r_sql Declaración SQL de PDO.
     * @param array $campos_encriptados Un arreglo de campos para encriptar.
     * @return array Un arreglo con los registros procesados.
     * @throws errores Si hay un error al generar modelos hijos o al generar el arreglo de registros.
     * @version 14.17.0
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
     * POR DOCUMENTAR EN WIKI
     * La función "result" ajusta el resultado de una consulta y lo devuelve en un formato específico.
     *
     * @param string $consulta La consulta SQL que se ejecutó.
     * @param int $n_registros El número de registros devueltos por la consulta.
     * @param array $new_array Un array que contiene los registros devueltos por la consulta.
     *
     * @return object $data Un objeto que contiene los resultados de la consulta.
     * $data tiene las siguientes propiedades:
     * registros: array de registros devueltos por la consulta en formato de array asociativo.
     * n_registros: número de registros devueltos por la consulta.
     * sql: la consulta SQL que se ejecutó.
     * campos_entidad: una lista de los campos de la entidad con la que se está trabajando.
     * registros_obj: array de registros devueltos por la consulta en formato de objeto.
     *
     * La función convierte cada fila de $new_array de un array asociativo a un objeto y lo almacena en $data->registros_obj.
     *
     * @version 14.22.0
     */
    private function result(string $consulta, int $n_registros, array $new_array): stdClass
    {

        $campos_entidad = $this->campos_entidad;

        $data = new stdClass();
        $data->registros = $new_array;
        $data->n_registros = (int)$n_registros;
        $data->sql = $consulta;
        $data->campos_entidad = $campos_entidad;


        $data->registros_obj = array();
        foreach ($data->registros as $row) {
            $row_obj = (object)$row;
            $data->registros_obj[] = $row_obj;
        }
        return $data;
    }


    /**
     * POR DOCUMENTAR EN WIKI
     * Ejecuta una consulta SQL y devuelve los registros obtenidos
     *
     * Esta función toma una consulta SQL y un arreglo de campos encriptados.
     * Luego, ejecuta la consulta, procesa los resultados y regresa un objeto con los datos y los registros procesados.
     *
     * @param array $campos_encriptados Un arreglo de campos para encriptar.
     * @param string $consulta La consulta SQL a ejecutar.
     * @return array|stdClass Un objeto con los datos y registros procesados, o un mensaje de error en caso de falla.
     * @throws errores Si la consulta está vacía, hay un error al ejecutar la consulta SQL, o hay un error al procesar los registros.
     * @version 14.19.0
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


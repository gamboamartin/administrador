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


        $letras = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S',
            'T','U','V','W','X','Y','Z');

        foreach ($letras as $letra){
            $this->letras[] = $letra;
        }

        foreach ($letras as $letra){
            $this->letras[] = 'A'.$letra;
        }
        foreach ($letras as $letra){
            $this->letras[] = 'B'.$letra;
        }
        foreach ($letras as $letra){
            $this->letras[] = 'C'.$letra;
        }
        foreach ($letras as $letra){
            $this->letras[] = 'D'.$letra;
        }
        foreach ($letras as $letra){
            $this->letras[] = 'E'.$letra;
        }




    }



    /**
     * REG
     * Desactiva las dependencias de un modelo si está habilitada la opción de desactivación de dependientes.
     *
     * Este método revisa si el modelo tiene la propiedad `$desactiva_dependientes` habilitada y, en caso afirmativo,
     * procede a desactivar las dependencias asociadas a través del método `desactiva_data_modelos_dependientes()` de la clase `dependencias`.
     * Si ocurre un error en la desactivación, el método devuelve un mensaje de error con los detalles del problema.
     *
     * @final
     * @protected
     * @return array Datos sobre las dependencias desactivadas. En caso de error, devuelve un array con la información del error.
     *
     * @example
     * ```php
     * // Supongamos que tenemos un modelo con dependencias que deben ser desactivadas
     * $modelo = new MiModelo();
     * $modelo->desactiva_dependientes = true;
     *
     * $resultado = $modelo->aplica_desactivacion_dependencias();
     * print_r($resultado);
     * ```
     * **Salida esperada (caso exitoso):**
     * ```php
     * Array
     * (
     *     [dependencia_1] => Array
     *     (
     *         [id] => 15
     *         [status] => 'inactivo'
     *     ),
     *     [dependencia_2] => Array
     *     (
     *         [id] => 27
     *         [status] => 'inactivo'
     *     )
     * )
     * ```
     *
     * **Salida esperada (caso de error):**
     * ```php
     * Array
     * (
     *     [error] => true
     *     [mensaje] => 'Error al desactivar dependiente'
     *     [data] => Array()
     * )
     * ```
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
     * REG
     * Genera un objeto que encapsula los datos relacionados con columnas SQL, subconsultas y columnas extra.
     *
     * @param string $columnas_extra_sql Cadena con las columnas adicionales generadas.
     *                                   Se espera que ya estén formateadas para ser utilizadas en una consulta SQL.
     * @param string $columnas_sql Cadena con las columnas principales de la consulta SQL.
     * @param string $sub_querys_sql Cadena con las subconsultas SQL generadas, si las hubiera.
     *
     * @return stdClass Objeto con las siguientes propiedades:
     *                  - `columnas_sql` (string): Contiene las columnas principales de la consulta SQL.
     *                  - `sub_querys_sql` (string): Subconsultas generadas en formato SQL.
     *                  - `columnas_extra_sql` (string): Columnas adicionales generadas en formato SQL.
     *
     * @example Generación exitosa de columnas SQL y subconsultas:
     * ```php
     * $columnas_extra_sql = "(SELECT SUM(precio) FROM ventas) AS ventas_totales";
     * $columnas_sql = "ventas.id, productos.nombre";
     * $sub_querys_sql = " , (SELECT COUNT(*) FROM productos WHERE stock > 0) AS productos_disponibles";
     *
     * $resultado = $this->columnas_data(
     *     columnas_extra_sql: $columnas_extra_sql,
     *     columnas_sql: $columnas_sql,
     *     sub_querys_sql: $sub_querys_sql
     * );
     * // Resultado:
     * // $resultado->columnas_sql = "ventas.id, productos.nombre";
     * // $resultado->sub_querys_sql = " , (SELECT COUNT(*) FROM productos WHERE stock > 0) AS productos_disponibles";
     * // $resultado->columnas_extra_sql = "(SELECT SUM(precio) FROM ventas) AS ventas_totales";
     * ```
     *
     * @example Sin columnas adicionales ni subconsultas:
     * ```php
     * $columnas_extra_sql = "";
     * $columnas_sql = "ventas.id, productos.nombre";
     * $sub_querys_sql = "";
     *
     * $resultado = $this->columnas_data(
     *     columnas_extra_sql: $columnas_extra_sql,
     *     columnas_sql: $columnas_sql,
     *     sub_querys_sql: $sub_querys_sql
     * );
     * // Resultado:
     * // $resultado->columnas_sql = "ventas.id, productos.nombre";
     * // $resultado->sub_querys_sql = "";
     * // $resultado->columnas_extra_sql = "";
     * ```
     */
    private function columnas_data(
        string $columnas_extra_sql,
        string $columnas_sql,
        string $sub_querys_sql
    ): stdClass {
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
     * REG
     * Combina dos cadenas de columnas SQL en una única cadena formateada correctamente.
     *
     * @param string $column_data Cadena con las columnas o datos adicionales a agregar.
     *                            Debe estar correctamente formateada como parte de una sentencia SQL.
     * @param string $columns_final Cadena con las columnas actuales acumuladas. Se actualizará
     *                               con los valores de `$column_data` si corresponde.
     *
     * @return string Una cadena con la combinación de `$columns_final` y `$column_data`, separadas por comas.
     *                Si alguna de las cadenas está vacía, no se incluirán separadores adicionales.
     *
     * @example Agregar columnas a una cadena vacía:
     * ```php
     * $column_data = "SUM(precio) AS total_precio";
     * $columns_final = "";
     *
     * $result = $this->columns_final(column_data: $column_data, columns_final: $columns_final);
     * // Resultado:
     * // $result = "SUM(precio) AS total_precio";
     * ```
     *
     * @example Agregar columnas a una cadena existente:
     * ```php
     * $column_data = "COUNT(*) AS total_items";
     * $columns_final = "SUM(precio) AS total_precio";
     *
     * $result = $this->columns_final(column_data: $column_data, columns_final: $columns_final);
     * // Resultado:
     * // $result = "SUM(precio) AS total_precio,COUNT(*) AS total_items";
     * ```
     *
     * @example Sin agregar columnas cuando `$column_data` está vacío:
     * ```php
     * $column_data = "";
     * $columns_final = "SUM(precio) AS total_precio";
     *
     * $result = $this->columns_final(column_data: $column_data, columns_final: $columns_final);
     * // Resultado:
     * // $result = "SUM(precio) AS total_precio";
     * ```
     */
    private function columns_final(string $column_data, string $columns_final): string
    {
        $columns_final = trim($columns_final);
        $column_data = trim($column_data);

        if ($columns_final === '') {
            $columns_final .= $column_data;
        } else {
            if ($column_data !== '') {
                $columns_final = $columns_final . ',' . $column_data;
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
     * REG
     * Ejecuta una consulta SQL, procesa los resultados y los retorna en un formato estructurado.
     *
     * Este método:
     * 1. Valida que la consulta SQL no esté vacía.
     * 2. Configura los modelos hijos, en caso de ser necesarios, para procesar registros relacionados.
     * 3. Ejecuta la consulta utilizando el método `data_result` de `_result`.
     * 4. Retorna los registros procesados con información adicional como totales, número de registros, y registros en formato objeto.
     *
     * @param string $consulta Consulta SQL a ejecutar.
     * @param array $campos_encriptados Campos que requieren desencriptarse en los registros resultantes.
     * @param array $columnas_totales Columnas para las cuales se deben calcular los totales acumulados.
     * @param array $hijo Modelos hijos para procesar registros relacionados.
     *
     * @return array|stdClass
     *   - Retorna un objeto `stdClass` con los datos procesados si la ejecución es exitosa:
     *     - `registros`: Array con los registros procesados.
     *     - `n_registros`: Número total de registros procesados.
     *     - `sql`: La consulta SQL ejecutada.
     *     - `campos_entidad`: Los campos de la entidad del modelo.
     *     - `totales`: Objeto con los totales acumulados.
     *     - `registros_obj`: Array con los registros procesados como objetos.
     *   - Retorna un arreglo con el error si ocurre algún problema durante el proceso.
     *
     * @example
     *  Ejemplo 1: Ejecución exitosa de una consulta
     *  --------------------------------------------
     *  $consulta = "SELECT id, nombre, precio FROM productos";
     *  $campos_encriptados = ['nombre'];
     *  $columnas_totales = ['precio'];
     *  $hijo = [];
     *
     *  $resultado = $this->ejecuta_consulta(
     *      consulta: $consulta,
     *      campos_encriptados: $campos_encriptados,
     *      columnas_totales: $columnas_totales,
     *      hijo: $hijo
     *  );
     *  // $resultado contendrá un objeto `stdClass` con los datos procesados.
     *
     * @example
     *  Ejemplo 2: Error en consulta vacía
     *  -----------------------------------
     *  $consulta = "";
     *  $resultado = $this->ejecuta_consulta($consulta);
     *  // Retorna un arreglo con el error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'La consulta no puede venir vacia',
     *  //   'data' => [...],
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 3: Procesamiento de modelos hijos
     *  ------------------------------------------
     *  $consulta = "SELECT id, nombre, categoria_id FROM productos";
     *  $campos_encriptados = [];
     *  $columnas_totales = ['precio'];
     *  $hijo = [
     *      'categorias' => [
     *          'nombre_estructura' => 'categoria',
     *          'namespace_model' => 'gamboamartin\\categorias\\models',
     *          'filtros' => ['categoria_id' => 'id'],
     *          'filtros_con_valor' => []
     *      ]
     *  ];
     *
     *  $resultado = $this->ejecuta_consulta(
     *      consulta: $consulta,
     *      campos_encriptados: $campos_encriptados,
     *      columnas_totales: $columnas_totales,
     *      hijo: $hijo
     *  );
     *  // $resultado incluirá los registros con los datos de los modelos hijos relacionados.
     *
     * @throws array Retorna un arreglo con el error en caso de que ocurra un problema durante la ejecución.
     */
    final public function ejecuta_consulta(string $consulta, array $campos_encriptados = array(),
                                           array $columnas_totales = array(), array $hijo = array()): array|stdClass
    {
        $this->hijo = $hijo;

        // Validación inicial de la consulta
        if (trim($consulta) === '') {
            return $this->error->error(
                mensaje: 'La consulta no puede venir vacia',
                data: [$this->link->errorInfo(), $consulta],
                es_final: true
            );
        }

        // Configura la transacción como SELECT
        $this->transaccion = 'SELECT';

        // Ejecuta la consulta y procesa los resultados
        $data = (new _result())->data_result(
            campos_encriptados: $campos_encriptados,
            columnas_totales: $columnas_totales,
            consulta: $consulta,
            modelo: $this
        );

        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al parsear registros",
                data: $data
            );
        }

        return $data;
    }


    /**
     * REG
     * Ejecuta una consulta SQL en la base de datos y maneja el resultado.
     *
     * Este método valida y ejecuta una consulta SQL utilizando el enlace (`link`) a la base de datos.
     * Maneja errores durante la ejecución y puede registrar la transacción dependiendo del tipo (INSERT).
     *
     * @param string $consulta Consulta SQL que se desea ejecutar.
     *
     * @return array|stdClass Retorna:
     *   - Un objeto `stdClass` con los datos del resultado de la consulta si es exitosa.
     *   - Un arreglo de error si la consulta está vacía o si ocurre una excepción al ejecutarla.
     *
     * @throws array Si:
     *   - La consulta está vacía.
     *   - Ocurre una excepción al ejecutar la consulta.
     *
     * @example
     *  Ejemplo 1: Ejecución exitosa de una consulta SELECT
     *  ---------------------------------------------------
     *  $consulta = "SELECT * FROM usuarios";
     *  $resultado = $this->ejecuta_sql($consulta);
     *  // $resultado contiene:
     *  // $resultado->mensaje => "Exito al ejecutar sql del modelo usuarios transaccion SELECT"
     *  // $resultado->sql => "SELECT * FROM usuarios"
     *  // $resultado->result => PDOStatement (resultado del query)
     *  // $resultado->salida => "exito"
     *
     * @example
     *  Ejemplo 2: Ejecución de una consulta INSERT
     *  -------------------------------------------
     *  $this->transaccion = 'INSERT';
     *  $this->link = new PDO(...); // Conexión a la base de datos
     *  $this->tabla = "usuarios";
     *  $this->campo_llave = "id";
     *  $this->registro = ['nombre' => 'Juan', 'email' => 'juan@example.com'];
     *
     *  $consulta = "INSERT INTO usuarios (nombre, email) VALUES ('Juan', 'juan@example.com')";
     *  $resultado = $this->ejecuta_sql($consulta);
     *  // $resultado->registro_id contiene el ID del último registro insertado.
     *
     * @example
     *  Ejemplo 3: Error al pasar una consulta vacía
     *  --------------------------------------------
     *  $consulta = "";
     *  $resultado = $this->ejecuta_sql($consulta);
     *  // $resultado contiene:
     *  // [
     *  //     'error' => 1,
     *  //     'mensaje' => "Error consulta vacia",
     *  //     'data' => " tabla: usuarios",
     *  //     ...
     *  // ]
     */
    final public function ejecuta_sql(string $consulta): array|stdClass
    {
        // Validar que la consulta no esté vacía
        if ($consulta === '') {
            return $this->error->error(
                mensaje: "Error consulta vacia",
                data: $consulta . ' tabla: ' . $this->tabla,
                aplica_bitacora: true,
                es_final: true
            );
        }

        try {
            // Ejecutar la consulta
            $result = $this->link->query($consulta);
        } catch (Throwable $e) {
            // Manejar errores durante la ejecución de la consulta
            return $this->error->error(
                mensaje: 'Error al ejecutar sql ' . $e->getMessage(),
                data: array(
                    $e->getCode() . ' ' . $this->tabla . ' ' . $consulta . ' ' . $this->tabla,
                    'registro' => $this->registro
                ),
                aplica_bitacora: true,
                es_final: true
            );
        }

        // Manejar transacción INSERT
        if ($this->transaccion === 'INSERT') {
            $this->registro_id = $this->campo_llave === ""
                ? $this->link->lastInsertId()
                : $this->registro[$this->campo_llave];
        }

        // Crear mensaje de éxito
        $mensaje = 'Exito al ejecutar sql del modelo ' . $this->tabla . ' transaccion ' . $this->transaccion;

        // Crear objeto de datos de respuesta
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
     * REG
     * Verifica si un modelo pertenece a uno de los namespaces especiales.
     *
     * Este método evalúa si el nombre completo de un modelo contiene alguno de los namespaces
     * especiales proporcionados. Si el modelo pertenece a un namespace especial, devuelve `true`;
     * de lo contrario, devuelve `false`. También maneja errores relacionados con datos vacíos.
     *
     * @param string $modelo Nombre completo del modelo (incluyendo namespace).
     * @param array $namespaces Lista de namespaces especiales a verificar.
     *
     * @return bool|array
     *   - Retorna `true` si el modelo pertenece a uno de los namespaces especiales.
     *   - Retorna `false` si no pertenece a ninguno de los namespaces.
     *   - Retorna un arreglo de error si el modelo o un namespace están vacíos.
     *
     * @example
     *  Ejemplo 1: Modelo pertenece a un namespace especial
     *  ----------------------------------------------------
     *  $modelo = 'gamboamartin\\facturacion\\models\\factura';
     *  $namespaces = [
     *      'gamboamartin\\facturacion\\models\\',
     *      'gamboamartin\\empleado\\models\\'
     *  ];
     *
     *  $resultado = $this->es_namespace_especial($modelo, $namespaces);
     *  // $resultado será `true` porque el modelo contiene el namespace `gamboamartin\\facturacion\\models\\`.
     *
     * @example
     *  Ejemplo 2: Modelo no pertenece a un namespace especial
     *  -------------------------------------------------------
     *  $modelo = 'gamboamartin\\organigrama\\models\\departamento';
     *  $namespaces = [
     *      'gamboamartin\\facturacion\\models\\',
     *      'gamboamartin\\empleado\\models\\'
     *  ];
     *
     *  $resultado = $this->es_namespace_especial($modelo, $namespaces);
     *  // $resultado será `false` porque el modelo no pertenece a los namespaces proporcionados.
     *
     * @example
     *  Ejemplo 3: Error por modelo vacío
     *  ----------------------------------
     *  $modelo = '';
     *  $namespaces = [
     *      'gamboamartin\\facturacion\\models\\',
     *      'gamboamartin\\empleado\\models\\'
     *  ];
     *
     *  $resultado = $this->es_namespace_especial($modelo, $namespaces);
     *  // $resultado será un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error modelo vacio',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 4: Error por namespace vacío
     *  -------------------------------------
     *  $modelo = 'gamboamartin\\facturacion\\models\\factura';
     *  $namespaces = [
     *      'gamboamartin\\facturacion\\models\\',
     *      ''
     *  ];
     *
     *  $resultado = $this->es_namespace_especial($modelo, $namespaces);
     *  // $resultado será un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error namespace vacio',
     *  //   'data' => [...],
     *  //   ...
     *  // ]
     */
    private function es_namespace_especial(string $modelo, array $namespaces): bool|array
    {
        if ($modelo === '') {
            return $this->error->error(
                mensaje: "Error modelo vacio",
                data: $modelo,
                es_final: true
            );
        }

        $es_namespace_especial_como_mis_inges = false;

        foreach ($namespaces as $namespace) {
            $namespace = trim($namespace);
            if ($namespace === '') {
                return $this->error->error(
                    mensaje: "Error namespace vacio",
                    data: $namespaces,
                    es_final: true
                );
            }

            $namespaces_explode = explode($namespace, $modelo);

            if (is_array($namespaces_explode) && count($namespaces_explode) > 1) {
                $es_namespace_especial_como_mis_inges = true;
                break;
            }
        }

        return $es_namespace_especial_como_mis_inges;
    }



    /**
     * REG
     * Genera columnas adicionales y subconsultas SQL para una consulta basada en los parámetros especificados.
     *
     * @param array $columnas Lista de nombres de columnas específicas que se desean incluir en la consulta.
     *                        Si está vacía, se incluirán todas las columnas definidas.
     * @param array $columnas_seleccionables Lista de alias de columnas que son seleccionables para subconsultas.
     *                                       Si está vacía, no se aplican restricciones.
     * @param string $columnas_sql Cadena SQL base que contiene las columnas iniciales de la consulta.
     * @param bool $con_sq Indica si se deben generar subconsultas y columnas adicionales.
     *                     Si es `false`, no se procesan estas partes.
     *
     * @return stdClass|array Retorna un objeto `stdClass` con las siguientes propiedades:
     *                        - `sub_querys_sql` (string): Subconsultas SQL generadas.
     *                        - `columnas_extra_sql` (string): Columnas adicionales generadas.
     *                        En caso de error, retorna un array con detalles del problema encontrado.
     *
     * @throws errores Si ocurre un problema durante la generación de subconsultas o columnas adicionales.
     *
     * @example Generación exitosa de subconsultas y columnas adicionales:
     * ```php
     * $columnas = ['ventas_totales', 'productos_disponibles'];
     * $columnas_seleccionables = ['ventas_totales'];
     * $columnas_sql = 'ventas.id, productos.id';
     * $con_sq = true;
     *
     * $resultado = $this->extra_columns(
     *     columnas: $columnas,
     *     columnas_seleccionables: $columnas_seleccionables,
     *     columnas_sql: $columnas_sql,
     *     con_sq: $con_sq
     * );
     * // Resultado:
     * // $resultado->sub_querys_sql = ' , (SELECT SUM(precio) FROM ventas WHERE estado = "aprobado") AS ventas_totales';
     * // $resultado->columnas_extra_sql = ' , (SELECT COUNT(*) FROM productos WHERE stock > 0) AS productos_disponibles';
     * ```
     *
     * @example Sin subconsultas o columnas adicionales:
     * ```php
     * $columnas = [];
     * $columnas_seleccionables = [];
     * $columnas_sql = 'ventas.id, productos.id';
     * $con_sq = false;
     *
     * $resultado = $this->extra_columns(
     *     columnas: $columnas,
     *     columnas_seleccionables: $columnas_seleccionables,
     *     columnas_sql: $columnas_sql,
     *     con_sq: $con_sq
     * );
     * // Resultado:
     * // $resultado->sub_querys_sql = '';
     * // $resultado->columnas_extra_sql = '';
     * ```
     *
     * @example Error al generar subconsultas:
     * ```php
     * $columnas = ['ventas_totales'];
     * $columnas_seleccionables = ['ventas_totales'];
     * $columnas_sql = 'ventas.id, productos.id';
     * $con_sq = true;
     *
     * // Simula un error en el modelo al generar subconsultas.
     * $this->sub_querys = function() {
     *     return $this->error->error(mensaje: 'Error al generar subquerys', data: []);
     * };
     *
     * $resultado = $this->extra_columns(
     *     columnas: $columnas,
     *     columnas_seleccionables: $columnas_seleccionables,
     *     columnas_sql: $columnas_sql,
     *     con_sq: $con_sq
     * );
     * // Resultado esperado: Array con detalles del error.
     * ```
     */
    private function extra_columns(
        array $columnas,
        array $columnas_seleccionables,
        string $columnas_sql,
        bool $con_sq
    ): stdClass|array {
        $sub_querys_sql = '';
        $columnas_extra_sql = '';
        if ($con_sq) {
            $sub_querys_sql = (new columnas())->sub_querys(
                columnas: $columnas_sql,
                modelo: $this,
                columnas_seleccionables: $columnas_seleccionables
            );
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar sub querys en ' . $this->tabla,
                    data: $sub_querys_sql);
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
     * REG
     * Genera una cadena final de columnas SQL combinando múltiples partes de datos proporcionados en un objeto.
     *
     * @param stdClass $columns_data Objeto que contiene las diferentes cadenas de columnas SQL a combinar.
     *                               Cada propiedad del objeto debe ser una cadena formateada como parte de una consulta SQL.
     *
     * @return string|array Una cadena con todas las columnas SQL combinadas, separadas por comas.
     *                      En caso de error, retorna un array con detalles del error.
     *
     * @throws errores Si ocurre un problema al combinar las columnas, se maneja mediante la clase `errores`.
     *
     * @example Uso con múltiples columnas:
     * ```php
     * $columns_data = new stdClass();
     * $columns_data->col1 = "SUM(precio) AS total_precio";
     * $columns_data->col2 = "COUNT(*) AS total_items";
     *
     * $result = $this->genera_columns_final(columns_data: $columns_data);
     * // Resultado:
     * // $result = "SUM(precio) AS total_precio, COUNT(*) AS total_items";
     * ```
     *
     * @example Uso con una sola columna:
     * ```php
     * $columns_data = new stdClass();
     * $columns_data->col1 = "SUM(precio) AS total_precio";
     *
     * $result = $this->genera_columns_final(columns_data: $columns_data);
     * // Resultado:
     * // $result = "SUM(precio) AS total_precio";
     * ```
     *
     * @example Sin columnas válidas (retorna error):
     * ```php
     * $columns_data = new stdClass();
     * $columns_data->col1 = ""; // Cadena vacía no válida.
     *
     * $result = $this->genera_columns_final(columns_data: $columns_data);
     * // Resultado:
     * // $result es un array con detalles del error.
     * ```
     */
    private function genera_columns_final(stdClass $columns_data): string|array
    {
        $columns_final = '';
        foreach ($columns_data as $column_data) {
            $column_data = trim($column_data);
            $columns_final = trim($columns_final);

            $columns_final = $this->columns_final(column_data: $column_data, columns_final: $columns_final);
            if (errores::$error) {
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
     * REG
     * Genera una consulta SQL base para el modelo actual, incluyendo columnas, joins, y otras estructuras necesarias.
     *
     * @param array $columnas Columnas básicas que se desean incluir en la consulta.
     * @param array $columnas_by_table Especifica columnas agrupadas por tabla para su inclusión en la consulta.
     * @param bool $columnas_en_bruto Si es `true`, las columnas serán generadas directamente sin aplicar alias ni transformaciones.
     * @param bool $con_sq Indica si se deben incluir subqueries en la consulta.
     * @param bool $count Si es `true`, genera una consulta de conteo (`COUNT(*)`) en lugar de listar columnas.
     * @param array $extension_estructura Define estructuras adicionales que se deben unir a la consulta (joins).
     * @param array $extra_join Define joins específicos adicionales para la consulta.
     * @param array $renombradas Especifica tablas con alias para incluir en la consulta.
     *
     * @return array|string Retorna una cadena SQL con la consulta generada. En caso de error, devuelve un array con los detalles del problema.
     *
     * @throws errores Si ocurre algún problema durante la generación de la consulta SQL.
     *
     * @example Generar una consulta básica con columnas seleccionadas:
     * ```php
     * $columnas = ['columna1', 'columna2'];
     * $columnas_by_table = [];
     * $columnas_en_bruto = false;
     * $con_sq = true;
     * $count = false;
     * $extension_estructura = [
     *     'tabla_extra' => ['key' => 'id', 'enlace' => 'tabla_base', 'key_enlace' => 'tabla_extra_id']
     * ];
     * $extra_join = [];
     * $renombradas = [];
     *
     * $consulta = $this->genera_consulta_base(
     *     columnas: $columnas,
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     con_sq: $con_sq,
     *     count: $count,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     renombradas: $renombradas
     * );
     * // Resultado esperado:
     * // "SELECT tabla.columna1, tabla.columna2 FROM tabla_base LEFT JOIN tabla_extra ON tabla_extra.id = tabla_base.tabla_extra_id"
     * ```
     *
     * @example Generar una consulta de conteo:
     * ```php
     * $columnas = [];
     * $columnas_by_table = [];
     * $columnas_en_bruto = false;
     * $con_sq = false;
     * $count = true;
     * $extension_estructura = [];
     * $extra_join = [];
     * $renombradas = [];
     *
     * $consulta = $this->genera_consulta_base(
     *     columnas: $columnas,
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     con_sq: $con_sq,
     *     count: $count,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     renombradas: $renombradas
     * );
     * // Resultado esperado:
     * // "SELECT COUNT(*) AS total_registros FROM tabla_base"
     * ```
     *
     * @example Manejo de error al generar columnas:
     * ```php
     * $columnas = ['columna_invalida'];
     * $columnas_by_table = [];
     * $columnas_en_bruto = false;
     * $con_sq = true;
     * $count = false;
     * $extension_estructura = [];
     * $extra_join = [];
     * $renombradas = [];
     *
     * $consulta = $this->genera_consulta_base(
     *     columnas: $columnas,
     *     columnas_by_table: $columnas_by_table,
     *     columnas_en_bruto: $columnas_en_bruto,
     *     con_sq: $con_sq,
     *     count: $count,
     *     extension_estructura: $extension_estructura,
     *     extra_join: $extra_join,
     *     renombradas: $renombradas
     * );
     * // Resultado: Array con detalles del error al procesar las columnas.
     * ```
     */
    final public function genera_consulta_base(
        array $columnas = array(),
        array $columnas_by_table = array(),
        bool $columnas_en_bruto = false,
        bool $con_sq = true,
        bool $count = false,
        array $extension_estructura = array(),
        array $extra_join = array(),
        array $renombradas = array()
    ): array|string {
        $this->tabla = str_replace('models\\', '', $this->tabla);

        $columnas_seleccionables = $columnas;

        $columnas_sql = (new columnas())->obten_columnas_completas(
            modelo: $this,
            columnas_by_table: $columnas_by_table,
            columnas_en_bruto: $columnas_en_bruto,
            columnas_sql: $columnas_seleccionables,
            extension_estructura: $extension_estructura,
            extra_join: $extra_join,
            renombres: $renombradas
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener columnas en ' . $this->tabla,
                data: $columnas_sql
            );
        }

        $tablas = (new joins())->tablas(
            columnas: $this->columnas,
            extension_estructura: $extension_estructura,
            extra_join: $extra_join,
            modelo_tabla: $this->tabla,
            renombradas: $renombradas,
            tabla: $this->tabla
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al generar joins en ' . $this->tabla,
                data: $tablas
            );
        }

        $columns_final = $this->integra_columns_final(
            columnas: $columnas,
            columnas_seleccionables: $columnas_seleccionables,
            columnas_sql: $columnas_sql,
            con_sq: $con_sq,
            count: $count
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al integrar columns_final',
                data: $columns_final
            );
        }

        return /** @lang MYSQL */ "SELECT $columns_final FROM $tablas";
    }



    /**
     * REG
     * Genera una instancia de un modelo basado en su nombre y namespace proporcionados.
     *
     * Este método:
     * 1. Genera el nombre completo del modelo ajustado utilizando el método `genera_name_modelo`.
     * 2. Valida que el nombre del modelo generado sea válido mediante el método `valida_data_modelo`.
     * 3. Retorna una nueva instancia del modelo si todo es correcto.
     *
     * @param string $modelo Nombre del modelo que se desea generar.
     * @param string $namespace_model Namespace del modelo (opcional). Si está vacío, se utiliza el nombre del modelo tal cual.
     *
     * @return array|modelo
     *   - Retorna una instancia del modelo generado si todo es correcto.
     *   - Retorna un arreglo de error si ocurre algún problema durante la generación o validación del modelo.
     *
     * @example
     *  Ejemplo 1: Generar un modelo con un namespace especificado
     *  -----------------------------------------------------------
     *  $modelo = 'usuario';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->genera_modelo($modelo, $namespace_model);
     *  // Retorna una instancia del modelo: gamboamartin\administrador\usuario
     *
     * @example
     *  Ejemplo 2: Generar un modelo sin namespace
     *  ------------------------------------------
     *  $modelo = 'models\\usuario';
     *  $namespace_model = '';
     *
     *  $resultado = $this->genera_modelo($modelo, $namespace_model);
     *  // Retorna una instancia del modelo: models\usuario
     *
     * @example
     *  Ejemplo 3: Error al generar el nombre del modelo
     *  ------------------------------------------------
     *  $modelo = '';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->genera_modelo($modelo, $namespace_model);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error al maquetar name modelo',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 4: Error al validar el modelo
     *  -------------------------------------
     *  $modelo = 'gamboamartin\\inexistente';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->genera_modelo($modelo, $namespace_model);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error al validar modelo',
     *  //   'data' => 'gamboamartin\\inexistente',
     *  //   ...
     *  // ]
     */
    final public function genera_modelo(string $modelo, string $namespace_model = ''): array|modelo
    {
        // Genera el nombre ajustado del modelo
        $modelo = $this->genera_name_modelo(modelo: $modelo, namespace_model: $namespace_model);
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al maquetar name modelo",
                data: $modelo
            );
        }

        // Valida que el nombre del modelo sea válido
        $valida = $this->validacion->valida_data_modelo(name_modelo: $modelo);
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al validar modelo",
                data: $valida
            );
        }

        // Retorna una nueva instancia del modelo
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
     * REG
     * Genera el nombre completo de un modelo ajustado a su namespace correspondiente.
     *
     * Este método:
     * 1. Obtiene la lista de namespaces válidos mediante el método `namespaces()`.
     * 2. Valida si el modelo pertenece a un namespace especial utilizando el método `es_namespace_especial()`.
     * 3. Ajusta el nombre del modelo según si pertenece a un namespace especial o no, aplicando el namespace proporcionado.
     * 4. Retorna el nombre completo del modelo ajustado.
     *
     * @param string $modelo Nombre del modelo a ajustar.
     * @param string $namespace_model Namespace del modelo que se debe aplicar si corresponde.
     *
     * @return array|string
     *   - Retorna el nombre del modelo ajustado al formato correspondiente.
     *   - Retorna un arreglo de error si ocurre algún problema durante el ajuste.
     *
     * @example
     *  Ejemplo 1: Modelo con namespace especial
     *  ----------------------------------------
     *  $modelo = 'gamboamartin\\administrador\\usuario';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->genera_name_modelo($modelo, $namespace_model);
     *  // $resultado será: 'gamboamartin\\administrador\\usuario'
     *
     * @example
     *  Ejemplo 2: Modelo sin namespace especial
     *  ----------------------------------------
     *  $modelo = 'usuario';
     *  $namespace_model = '';
     *
     *  $resultado = $this->genera_name_modelo($modelo, $namespace_model);
     *  // $resultado será: 'models\\usuario'
     *
     * @example
     *  Ejemplo 3: Modelo con namespace ajustado
     *  ----------------------------------------
     *  $modelo = 'usuario';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->genera_name_modelo($modelo, $namespace_model);
     *  // $resultado será: 'gamboamartin\\administrador\\usuario'
     *
     * @example
     *  Ejemplo 4: Error al obtener namespaces
     *  --------------------------------------
     *  // Simulando un error en `namespaces()`.
     *  $namespaces = null; // Error simulado
     *  $modelo = 'usuario';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->genera_name_modelo($modelo, $namespace_model);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error al obtener namespaces',
     *  //   'data' => null,
     *  //   ...
     *  // ]
     */
    private function genera_name_modelo(string $modelo, string $namespace_model): array|string
    {
        // Obtiene los namespaces disponibles
        $namespaces = $this->namespaces();
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al obtener namespaces",
                data: $namespaces
            );
        }

        // Valida si el modelo pertenece a un namespace especial
        $es_namespace_especial = $this->es_namespace_especial(
            modelo: $modelo,
            namespaces: $namespaces
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al validar namespaces",
                data: $es_namespace_especial
            );
        }

        // Genera el nombre del modelo ajustado
        $modelo = $this->name_modelo(
            es_namespace_especial: $es_namespace_especial,
            modelo: $modelo,
            namespace_model: $namespace_model
        );
        if (errores::$error) {
            return $this->error->error(
                mensaje: "Error al maquetar name modelo",
                data: $modelo
            );
        }

        return $modelo;
    }



    /**
     * REG
     * Genera una cadena de columnas SQL finales combinando las columnas básicas, columnas adicionales y subqueries.
     *
     * @param array $columnas Columnas básicas que se utilizarán en la generación del SQL.
     * @param array $columnas_seleccionables Columnas seleccionables que se deben considerar al integrar las columnas adicionales.
     * @param string $columnas_sql Cadena inicial de columnas SQL que se usará como base para la integración.
     * @param bool $con_sq Indica si se deben incluir subqueries en el resultado.
     * @param bool $count Si es `true`, genera una columna SQL para contar el total de registros (`COUNT(*) AS total_registros`).
     *
     * @return array|string Devuelve una cadena con las columnas SQL finales integradas. En caso de error, retorna un array con detalles del error.
     *
     * @throws errores Si ocurre algún problema en los procesos de validación o integración de columnas.
     *
     * @example Uso básico con columnas adicionales y subqueries:
     * ```php
     * $columnas = ['columna1', 'columna2'];
     * $columnas_seleccionables = ['subquery1', 'subquery2'];
     * $columnas_sql = "tabla.columna_base";
     * $con_sq = true;
     * $count = false;
     *
     * $resultado = $this->integra_columns_final(
     *     columnas: $columnas,
     *     columnas_seleccionables: $columnas_seleccionables,
     *     columnas_sql: $columnas_sql,
     *     con_sq: $con_sq,
     *     count: $count
     * );
     * // Resultado esperado:
     * // "tabla.columna_base, subquery1_sql AS subquery1, subquery2_sql AS subquery2"
     * ```
     *
     * @example Uso con conteo de registros:
     * ```php
     * $columnas = [];
     * $columnas_seleccionables = [];
     * $columnas_sql = "";
     * $con_sq = false;
     * $count = true;
     *
     * $resultado = $this->integra_columns_final(
     *     columnas: $columnas,
     *     columnas_seleccionables: $columnas_seleccionables,
     *     columnas_sql: $columnas_sql,
     *     con_sq: $con_sq,
     *     count: $count
     * );
     * // Resultado esperado:
     * // "COUNT(*) AS total_registros"
     * ```
     *
     * @example Manejo de error al generar columnas adicionales:
     * ```php
     * $columnas = ['columna_invalida'];
     * $columnas_seleccionables = [];
     * $columnas_sql = "tabla.columna_base";
     * $con_sq = true;
     * $count = false;
     *
     * $resultado = $this->integra_columns_final(
     *     columnas: $columnas,
     *     columnas_seleccionables: $columnas_seleccionables,
     *     columnas_sql: $columnas_sql,
     *     con_sq: $con_sq,
     *     count: $count
     * );
     * // Resultado:
     * // Array con detalles del error al generar columnas adicionales.
     * ```
     */
    private function integra_columns_final(array $columnas, array $columnas_seleccionables, string $columnas_sql,
                                           bool $con_sq, bool $count): array|string
    {
        $extra_columns = $this->extra_columns(columnas: $columnas, columnas_seleccionables: $columnas_seleccionables,
            columnas_sql: $columnas_sql, con_sq: $con_sq);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar extra_columns', data: $extra_columns);
        }

        $columns_data = $this->columnas_data(
            columnas_extra_sql: $extra_columns->columnas_extra_sql,
            columnas_sql: $columnas_sql,
            sub_querys_sql: $extra_columns->sub_querys_sql
        );
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al maquetar columnas_data', data: $columns_data);
        }

        $columns_final = $this->genera_columns_final(columns_data: $columns_data);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar columns_final', data: $columns_final);
        }

        if ($count) {
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
     * REG
     * Genera el nombre completo de un modelo, ajustando su formato según el namespace y si es un namespace especial.
     *
     * Este método:
     * 1. Valida que el nombre del modelo no esté vacío.
     * 2. Si el modelo no pertenece a un namespace especial, ajusta el nombre del modelo al formato base.
     * 3. Si se proporciona un namespace, ajusta el nombre del modelo al formato adecuado para ese namespace.
     * 4. Retorna el nombre del modelo ajustado al formato esperado.
     *
     * @param bool $es_namespace_especial Indica si el modelo pertenece a un namespace especial.
     * @param string $modelo Nombre original del modelo.
     * @param string $namespace_model Namespace que se debe aplicar al modelo, si corresponde.
     *
     * @return string|array
     *   - Retorna el nombre del modelo ajustado al formato correspondiente.
     *   - Retorna un arreglo de error si el nombre del modelo está vacío o si ocurre un problema durante el ajuste.
     *
     * @example
     *  Ejemplo 1: Modelo con namespace especial
     *  ----------------------------------------
     *  $es_namespace_especial = true;
     *  $modelo = 'gamboamartin\\administrador\\usuario';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->name_modelo($es_namespace_especial, $modelo, $namespace_model);
     *  // $resultado será: 'gamboamartin\\administrador\\usuario'
     *
     * @example
     *  Ejemplo 2: Modelo sin namespace especial
     *  ----------------------------------------
     *  $es_namespace_especial = false;
     *  $modelo = 'usuario';
     *  $namespace_model = '';
     *
     *  $resultado = $this->name_modelo($es_namespace_especial, $modelo, $namespace_model);
     *  // $resultado será: 'models\\usuario'
     *
     * @example
     *  Ejemplo 3: Modelo con namespace ajustado
     *  ----------------------------------------
     *  $es_namespace_especial = false;
     *  $modelo = 'usuario';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->name_modelo($es_namespace_especial, $modelo, $namespace_model);
     *  // $resultado será: 'gamboamartin\\administrador\\usuario'
     *
     * @example
     *  Ejemplo 4: Error por modelo vacío
     *  ---------------------------------
     *  $es_namespace_especial = false;
     *  $modelo = '';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->name_modelo($es_namespace_especial, $modelo, $namespace_model);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error modelo esta vacio',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     */
    private function name_modelo(bool $es_namespace_especial, string $modelo, string $namespace_model): string|array
    {
        // Valida que el modelo no esté vacío
        $modelo = trim($modelo);
        if ($modelo === '') {
            return $this->error->error(
                mensaje: "Error modelo esta vacio",
                data: $modelo,
                es_final: true
            );
        }

        // Si no es un namespace especial, ajusta al formato base
        if (!$es_namespace_especial) {
            $modelo = $this->name_modelo_base(modelo: $modelo);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: "Error al maquetar name modelo",
                    data: $modelo
                );
            }
        }

        // Si se proporciona un namespace, ajusta el modelo al formato adecuado
        if ($namespace_model !== '') {
            $modelo = $this->name_modelo_ajustado(modelo: $modelo, namespace_model: $namespace_model);
            if (errores::$error) {
                return $this->error->error(
                    mensaje: "Error al maquetar name modelo",
                    data: $modelo
                );
            }
        }

        // Retorna el modelo ajustado
        return trim($modelo);
    }


    /**
     * REG
     * Ajusta el nombre de un modelo al formato adecuado con su namespace correspondiente.
     *
     * Este método:
     * 1. Valida que el namespace y el nombre del modelo no estén vacíos.
     * 2. Elimina el prefijo del namespace proporcionado del nombre del modelo.
     * 3. Elimina el prefijo `models\\` si está presente en el nombre del modelo.
     * 4. Retorna el modelo con el namespace ajustado al formato `{namespace_model}\\{nombre_modelo}`.
     *
     * @param string $modelo Nombre del modelo a ajustar.
     * @param string $namespace_model Namespace que se debe aplicar al modelo.
     *
     * @return string|array
     *   - Retorna el nombre del modelo ajustado al formato `{namespace_model}\\{nombre_modelo}`.
     *   - Retorna un arreglo de error si el namespace o el nombre del modelo están vacíos.
     *
     * @example
     *  Ejemplo 1: Ajuste de un modelo con namespace y prefijo
     *  ------------------------------------------------------
     *  $modelo = 'gamboamartin\\administrador\\models\\usuario';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->name_modelo_ajustado($modelo, $namespace_model);
     *  // $resultado será: 'gamboamartin\\administrador\\usuario'
     *
     * @example
     *  Ejemplo 2: Ajuste de un modelo sin prefijo `models\\`
     *  -----------------------------------------------------
     *  $modelo = 'usuario';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->name_modelo_ajustado($modelo, $namespace_model);
     *  // $resultado será: 'gamboamartin\\administrador\\usuario'
     *
     * @example
     *  Ejemplo 3: Error por namespace vacío
     *  ------------------------------------
     *  $modelo = 'usuario';
     *  $namespace_model = '';
     *
     *  $resultado = $this->name_modelo_ajustado($modelo, $namespace_model);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error namespace_model esta vacio',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     *
     * @example
     *  Ejemplo 4: Error por modelo vacío
     *  ---------------------------------
     *  $modelo = '';
     *  $namespace_model = 'gamboamartin\\administrador';
     *
     *  $resultado = $this->name_modelo_ajustado($modelo, $namespace_model);
     *  // Retorna un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error modelo esta vacio',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     */
    private function name_modelo_ajustado(string $modelo, string $namespace_model): string|array
    {
        // Elimina espacios en blanco al inicio y al final del namespace
        $namespace_model = trim($namespace_model);

        // Valida que el namespace no esté vacío
        if ($namespace_model === '') {
            return $this->error->error(
                mensaje: "Error namespace_model esta vacio",
                data: $namespace_model
            );
        }

        // Valida que el modelo no esté vacío
        $modelo = trim($modelo);
        if ($modelo === '') {
            return $this->error->error(
                mensaje: "Error modelo esta vacio",
                data: $modelo
            );
        }

        // Elimina el namespace del modelo y ajusta al formato adecuado
        $modelo = str_replace($namespace_model, '', $modelo);
        $modelo = str_replace('models\\', '', $modelo);
        return $namespace_model . '\\' . $modelo;
    }


    /**
     * REG
     * Ajusta el nombre de un modelo al formato base esperado.
     *
     * Este método:
     * 1. Elimina cualquier exceso de espacios en el nombre del modelo proporcionado.
     * 2. Verifica que el nombre del modelo no esté vacío.
     * 3. Asegura que el modelo esté en el formato base (`models\\{nombre_modelo}`), agregando el prefijo si es necesario.
     *
     * @param string $modelo El nombre del modelo que se desea ajustar.
     *
     * @return string|array
     *   - Retorna el nombre del modelo ajustado al formato base (`models\\{nombre_modelo}`).
     *   - Retorna un arreglo de error si el nombre del modelo está vacío.
     *
     * @example
     *  Ejemplo 1: Modelo en formato base
     *  ---------------------------------
     *  $modelo = 'models\\factura';
     *
     *  $resultado = $this->name_modelo_base($modelo);
     *  // $resultado será: 'models\\factura'
     *
     * @example
     *  Ejemplo 2: Modelo sin prefijo
     *  ------------------------------
     *  $modelo = 'factura';
     *
     *  $resultado = $this->name_modelo_base($modelo);
     *  // $resultado será: 'models\\factura'
     *
     * @example
     *  Ejemplo 3: Modelo con espacios en blanco
     *  ----------------------------------------
     *  $modelo = '   models\\factura   ';
     *
     *  $resultado = $this->name_modelo_base($modelo);
     *  // $resultado será: 'models\\factura'
     *
     * @example
     *  Ejemplo 4: Error por modelo vacío
     *  ---------------------------------
     *  $modelo = '';
     *
     *  $resultado = $this->name_modelo_base($modelo);
     *  // $resultado será un arreglo de error:
     *  // [
     *  //   'error' => 1,
     *  //   'mensaje' => 'Error modelo esta vacio',
     *  //   'data' => '',
     *  //   ...
     *  // ]
     */
    private function name_modelo_base(string $modelo): string|array
    {
        // Elimina espacios en blanco al inicio y al final del modelo
        $modelo = trim($modelo);

        // Valida que el modelo no esté vacío
        if ($modelo === '') {
            return $this->error->error(
                mensaje: "Error modelo esta vacio",
                data: $modelo,
                es_final: true
            );
        }

        // Asegura que el modelo esté en el formato base esperado
        $modelo = str_replace('models\\', '', $modelo);
        return 'models\\' . $modelo;
    }


    /**
     * REG
     * Obtiene un arreglo de namespaces para los modelos utilizados en la aplicación.
     *
     * Este método define una lista de namespaces que corresponden a diferentes módulos
     * o componentes del sistema. Los namespaces proporcionados son utilizados para
     * buscar y cargar clases de modelos en sus respectivos directorios.
     *
     * @return array Lista de namespaces como strings.
     *
     * @example
     *  Ejemplo: Obtener namespaces disponibles
     *  ----------------------------------------
     *  $namespaces = $this->namespaces();
     *
     *  // $namespaces contendrá:
     *  // [
     *  //   'gamboamartin\\administrador\\models\\',
     *  //   'gamboamartin\\empleado\\models\\',
     *  //   'gamboamartin\\facturacion\\models\\',
     *  //   ...
     *  // ]
     *
     * @example
     *  Uso en carga de modelos dinámicos
     *  -----------------------------------
     *  $namespaces = $this->namespaces();
     *  $modelo = null;
     *  foreach ($namespaces as $namespace) {
     *      $class = $namespace . 'mi_modelo';
     *      if (class_exists($class)) {
     *          $modelo = new $class();
     *          break;
     *      }
     *  }
     *  // Si existe la clase `mi_modelo` en alguno de los namespaces, será instanciada.
     */
    private function namespaces(): array
    {
        $namespaces[] = 'gamboamartin\\administrador\\models\\';
        $namespaces[] = 'gamboamartin\\empleado\\models\\';
        $namespaces[] = 'gamboamartin\\facturacion\\models\\';
        $namespaces[] = 'gamboamartin\\organigrama\\models\\';
        $namespaces[] = 'gamboamartin\\direccion_postal\\models\\';
        $namespaces[] = 'gamboamartin\\cat_sat\\models\\';
        $namespaces[] = 'gamboamartin\\comercial\\models\\';
        $namespaces[] = 'gamboamartin\\boletaje\\models\\';
        $namespaces[] = 'gamboamartin\\banco\\models\\';
        $namespaces[] = 'gamboamartin\\gastos\\models\\';
        $namespaces[] = 'gamboamartin\\nomina\\models\\';
        $namespaces[] = 'gamboamartin\\im_registro_patronal\\models\\';
        $namespaces[] = 'gamboamartin\\importador\\models\\';
        $namespaces[] = 'gamboamartin\\importador_cva\\models\\';
        $namespaces[] = 'gamboamartin\\proceso\\models\\';
        $namespaces[] = 'gamboamartin\\notificaciones\\models\\';
        $namespaces[] = 'gamboamartin\\inmuebles\\models\\';

        return $namespaces;
    }


    /**
     * REG
     * Obtiene el nombre de una tabla a partir de dos posibles valores.
     *
     * Esta función valida y selecciona el nombre de la tabla a utilizar según los parámetros recibidos.
     * Si se proporciona un nombre renombrado, este tendrá prioridad sobre el nombre original.
     * En caso de que ambos estén vacíos, se devuelve un error.
     *
     * @param string $tabla_original Nombre original de la tabla.
     * @param string $tabla_renombrada Nombre renombrado de la tabla.
     *
     * @return array|string Devuelve el nombre de la tabla seleccionada. Si ocurre un error, retorna un array con el detalle.
     *
     * @example
     * // Caso 1: Ambos parámetros son válidos
     * $tabla = $this->obten_nombre_tabla('usuarios', 'clientes');
     * // Resultado: 'clientes'
     *
     * @example
     * // Caso 2: Solo se proporciona tabla original
     * $tabla = $this->obten_nombre_tabla('usuarios', '');
     * // Resultado: 'usuarios'
     *
     * @example
     * // Caso 3: Ambos parámetros están vacíos
     * $tabla = $this->obten_nombre_tabla('', '');
     * // Resultado: [
     * //     'mensaje' => 'Error no pueden venir vacios todos los parametros',
     * //     'data' => '',
     * //     'es_final' => true
     * // ]
     *
     * @example
     * // Caso 4: Solo se proporciona tabla renombrada
     * $tabla = $this->obten_nombre_tabla('', 'clientes');
     * // Resultado: 'clientes'
     */
    final public function obten_nombre_tabla(string $tabla_original, string $tabla_renombrada): array|string
    {
        if (trim($tabla_original) === '' && trim($tabla_renombrada) === '') {
            return $this->error->error(
                mensaje: 'Error no pueden venir vacios todos los parametros',
                data: $tabla_renombrada,
                es_final: true
            );
        }
        if ($tabla_renombrada !== '') {
            $tabla_nombre = $tabla_renombrada;
        } else {
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


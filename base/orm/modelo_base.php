<?php
namespace base\orm;

use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
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
    protected array $keys_data_filter;
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
        $this->patterns['nss'] = "/^[0-9]{11}$/";
        $this->patterns['telefono'] = "/^[0-9]{10}$/";
        $this->patterns['id'] = "/^[1-9]+[0-9]*$/";
        $this->patterns['producto_codigo'] = "/^[0-9]{9}$/";
        $this->patterns['clase_codigo'] = "/^[0-9]{3}$/";
        $this->patterns['sub_clase_codigo'] = "/^[0-9]{6}$/";
        $this->patterns['nomina_antiguedad'] = "/^P[0-9]+W$/";
        $this->keys_data_filter = array('sentencia','filtro_especial','filtro_rango','filtro_extra','not_in','sql_extra','filtro_fecha');
    }

    /**
     * P INT
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
     */
    protected function agrega_usuario_session(): array|string
    {
        if($this->usuario_id <=0){
            return $this->error->error('Error usuario invalido',$this->usuario_id);
        }

        if($this->campos_sql === ''){
            return $this->error->error('campos no puede venir vacio',$this->campos_sql);
        }
        $existe_user = $this->usuario_existente();
        if(errores::$error){
            return $this->error->error('Error validar existencia de usuario',$existe_user);
        }
        if(!$existe_user){
            return $this->error->error('Error no existe usuario',array($existe_user,$this->campos_sql, $this->usuario_id));
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
     * P INT
     * @param string $tabla
     * @param string $funcion
     * @param string $consulta
     * @param int $registro_id
     * @return array
     */
    private function aplica_bitacora(string $tabla, string $funcion, string $consulta, int $registro_id): array
    {
        $model = $this->genera_modelo(modelo: $tabla);
        if(errores::$error){
            return $this->error->error('Error al generar modelo'.$tabla,$model);
        }

        $registro_bitacora = $model->registro(registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error('Error al obtener registro de '.$tabla,$registro_bitacora);
        }

        $bitacora = $this->bitacora(consulta: $consulta, funcion: $funcion, registro: $registro_bitacora);
        if(errores::$error){
            return $this->error->error('Error al insertar bitacora de '.$tabla,$bitacora);
        }
        return $bitacora;
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
     * P INT P ORDER
     * Devuelve un arreglo con las columnas y el valor del ID del usuario
     *
     * @return array
     *
     * @example
     *      $data_asignacion = $this->asigna_data_user_transaccion();
     *
     * @uses modelos->alta_bd();
     */
    protected function asigna_data_user_transaccion(): array
    {//FIN
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error('Error no existe usuario',$_SESSION);
        }
        if($_SESSION['usuario_id'] <= 0){
            return $this->error->error('Error USUARIO INVALIDO',$_SESSION['usuario_id']);
        }

        $usuario_alta_id = $_SESSION['usuario_id'];
        $usuario_upd_id = $_SESSION['usuario_id'];
        $campos = ',usuario_alta_id,usuario_update_id';
        $valores = ','.$usuario_alta_id.','.$usuario_upd_id;

        return array('campos'=>$campos,'valores'=>$valores);
    }

    /**
     * P INT P ORDER
     * Devuelve un arreglo que contiene los campos necesarios para un registro en la bitacora
     *
     * @param array $seccion_menu es un arreglo que indica a que parte del catalogo pertenece la accion
     * @param array $registro es un arreglo que indica cual fue el registro afectado por la accion
     * @param string $funcion es una cadena que indica que funcion o accion se utilizo
     * @param string $consulta es una cadena que indica la peticion en sql, que se realizo a la base de datos que
     * realiza la accion que se utilizo
     * @return array
     * @throws errores Si $seccion_menu es menor o igual a 0
     * @throws errores Si $funcion es una cadena vacia
     * @throws errores Si $consulta es una cadena vacia
     * @throws errores|JsonException Si $registro_id es menor o igual a 0*@throws \JsonException
     * @example
     *      $resultado = asigna_registro_para_bitacora('seccion_menu_id'=>'1'),array('x'),'x','x');
     *      //return $registro_data = array('seccion_menu_id'=>'1','status'=>'activo','registro'=>'json_encode($registro)',
     *      'usuario_id'=>'$_SESSION['usuario_id']','transaccion'=>'x','sql_data'=>'x','valor_id'=>'$this->registro_id');
     *
     * @example
     *      $resultado = asigna_registro_para_bitacora(array('seccion_menu_id'=>'-1'),array('x'),'x','x')
     *      //return array errores
     * @example
     *      $resultado = asigna_registro_para_bitacora(array('seccion_menu_id'=>'1'),array('x'),'','x')
     *      //return array errores
     * @example
     *      $resultado = asigna_registro_para_bitacora(array('seccion_menu_id'=>'1'),array('x'),'x','')
     *      //return array errores
     * @example
     *      $resultado = asigna_registro_para_bitacora(array('seccion_menu_id'=>'1'),$registro_id='-1','x','x')
     *      //return array errores
     */
    private function asigna_registro_para_bitacora(string $consulta,string $funcion, array $registro, array $seccion_menu): array
    {//FIN Y DOC
        if($seccion_menu['seccion_menu_id']<=0){
            return$this->error->error('Error el id de $seccion_menu[\'seccion_menu_id\'] no puede ser menor a 0',$seccion_menu['seccion_menu_id']);
        }
        if($funcion === ''){
            return $this->error->error('Error $funcion no puede venir vacia',$funcion);
        }
        if($consulta === ''){
            return $this->error->error('Error $consulta no puede venir vacia',$consulta);
        }
        if($this->registro_id<=0){
            return $this->error->error('Error el id de $this->registro_id no puede ser menor a 0',$this->registro_id);
        }
        $registro_data['seccion_menu_id'] = $seccion_menu['seccion_menu_id'];
        $registro_data['status'] = 'activo';
        $registro_data['registro'] = json_encode($registro, JSON_THROW_ON_ERROR);
        $registro_data['usuario_id'] = $_SESSION['usuario_id'];
        $registro_data['transaccion'] = $funcion;
        $registro_data['sql_data'] = $consulta;
        $registro_data['valor_id'] = $this->registro_id;

        return $registro_data;
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion que asigna un registro encontrado para hijos en las diferentes consultas
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
            return $this->error->error('Error al validar entrada para modelo',$valida);
        }
        if($nombre_estructura === ''){
            return  $this->error->error('Error nombre estructura no puede venir vacia',$nombre_estructura);
        }

        $modelo = $this->genera_modelo(modelo: $name_modelo);
        if(errores::$error){
            return $this->error->error('Error al generar modelo',$modelo);
        }
        $data = $modelo->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error('Error al generar registro hijos', $data);
        }
        $row[$nombre_estructura] = $data->registros;


        return $row;
    }

    /**
     * P INT P ORDER
     * Inserta una transaccion de bitacora
     * @param array $registro es un arreglo que indica cual fue el registro afectado por la accion
     * @param string $funcion es una cadena que indica que funcion o accion se utilizo
     * @param string $consulta es una cadena que indica la peticion en sql, que se realizo a la base de datos que
     * realiza la accion que se utilizo
     * @return array resultados de inserciones de bitacora
     * @example
     *      $registro_bitacora = $this->obten_data();
     * if(isset($registro_bitacora['error'])){
     * return $this->error->error('Error al obtener registro',
     * __CLASS__,$registro_bitacora);
     * $bitacora = $this->bitacora($registro_bitacora,__FUNCTION__,$consulta );
     * @internal  $this->genera_bitacora($registro,$funcion, $consulta)
     * @uses   modelo
     */
    protected function bitacora(string $consulta, string $funcion, array $registro): array
    {
        $bitacora = array();
        if($this->aplica_bitacora){
            $namespace = 'models\\';
            $this->tabla = str_replace($namespace,'',$this->tabla);
            $clase = $namespace.$this->tabla;
            if($this->tabla === ''){
                return $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
            }
            if(!class_exists($clase)){
                return $this->error->error('Error no existe la clase '.$clase,$clase);
            }
            if($funcion === ''){
                return $this->error->error('Error $funcion no puede venir vacia',$funcion);
            }
            if($consulta === ''){
                return $this->error->error('Error $consulta no puede venir vacia',$consulta);
            }
            if($this->registro_id<=0){
                return $this->error->error('Error el id de $this->registro_id no puede ser menor a 0',$this->registro_id);
            }
            $r_bitacora = $this->genera_bitacora(consulta:  $consulta, funcion: $funcion, registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al generar bitacora en '.$this->tabla,$r_bitacora);
            }
            $bitacora = $r_bitacora;
        }

        return $bitacora;
    }


    /**
     * P INT P ORDER
     * @return string
     */
    private function class_attr(): string
    {
        $namespace = 'models\\';
        $clase_attr = str_replace($namespace,'',$this->tabla);
        return 'models\\attr_'.$clase_attr;
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
     * P INT
     * Devuelve una variable de tipo booleana que indica si el usuario existe o no
     *
     * @return bool|array
     * @example
     *      $existe_user = $this->usuario_existente();
     *
     * @uses modelo_basico->agrega_usuario_session()
     * @internal modelo_basico->$this->ejecuta_consulta();
     */
    private function usuario_existente(): bool|array
    {
        if($this->usuario_id <=0){
            return $this->error->error('Error usuario invalido',$this->usuario_id);
        }

        $consulta = /** @lang MYSQL */
            'SELECT count(*) AS existe FROM usuario WHERE usuario.id = '.$this->usuario_id;
        $r_usuario_existente = $this->ejecuta_consulta(consulta: $consulta);

        if(errores::$error){
            return $this->error->error('Error al ejecutar sql',$r_usuario_existente);
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



    /**
     * P INT P ORDER
     * @param int $parent_id
     * @param string $tabla_children
     * @return array
     */
    protected function data_dependientes(int $parent_id, string $tabla_children): array
    {
        $valida = $this->validacion->valida_name_clase(tabla: $this->tabla);
        if(errores::$error){
            return $this->error->error('Error al validar tabla',$valida);
        }
        if($parent_id<=0){
            return $this->error->error('Error $parent_id debe ser mayor a 0',$parent_id);
        }

        $modelo_children = $this->genera_modelo(modelo: $tabla_children);
        if(errores::$error){
            return $this->error->error('Error al generar modelo',$modelo_children);
        }


        $key_id = $this->tabla.'.id';
        $filtro[$key_id] = $parent_id;

        $result = $modelo_children->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener dependientes',$result);
        }
        return $result['registros'];
    }

    /**
     * P ORDER P INT
     * @param array $atributo
     * @param int $registro_id
     * @return array
     */
    private function data_inst_attr(array $atributo, int $registro_id): array
    {
        $keys = array('atributo_descripcion','atributo_id');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $atributo);
        if(errores::$error){
            return $this->error->error('Error al validar $atributo',$valida);
        }
        $keys = array('atributo_id');
        $valida = $this->validacion->valida_ids(keys:  $keys, registro: $atributo);
        if(errores::$error){
            return $this->error->error('Error al validar $atributo',$valida);
        }
        if($registro_id<=0){
            return $this->error->error('Error registro_id debe ser mayor a 0',$registro_id);
        }
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error('Error $this->tabla esta vacia',$this->tabla);
        }

        $data_ins['descripcion'] = $atributo['atributo_descripcion'];
        $data_ins['status'] = 'activo';
        $data_ins['atributo_id'] = $atributo['atributo_id'];
        $data_ins[$this->tabla.'_id'] = $registro_id;
        $data_ins['valor'] = '';
        return $data_ins;
    }



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

        $dependientes = $this->data_dependientes($parent_id, $tabla_dep);
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
     * P INT P ORDER PROBADO
     * Funcion que ejecuta un query de tipo select
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
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
    public function ejecuta_consulta(string $consulta, array $hijo = array()): array|stdClass{
        $this->hijo = $hijo;
        if($consulta === ''){
            return $this->error->error(mensaje: 'La consulta no puede venir vacia', data: array(
                $this->link->errorInfo(),$consulta), params: get_defined_vars());
        }
        $this->transaccion = 'SELECT';
        $result = $this->ejecuta_sql(consulta: $consulta);

        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al ejecutar sql',data: $result, params: get_defined_vars());
        }

        $r_sql = $result->result;

        $new_array = $this->parsea_registros_envio( r_sql: $r_sql);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al parsear registros",data:  $new_array,
                params: get_defined_vars());
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

        return $data;

    }

    /**
     * P INT
     * @param int $registro_id
     * @return array|string
     */
    protected function ejecuta_insersion_attr(int $registro_id): array|string
    {
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0', data: $registro_id, params: get_defined_vars());
        }

        $clase_attr = $this->class_attr();
        if(errores::$error){
            return $this->error->error('Error al obtener class', $clase_attr);
        }
        if(class_exists($clase_attr)){

            $r_ins = $this->inserta_data_attr(clase_attr: $clase_attr, registro_id: $registro_id);
            if(errores::$error){
                return $this->error->error('Error al insertar atributos', $r_ins);
            }
        }
        return $clase_attr;
    }

    /**
     * PARAMS ORDER P INT PROBADO ERROREV
     * Devuelve un arreglo que contiene un texto que indica el exito de la sentencia, tambien la consulta inicial de
     * sql y por ultimo un objeto PDOStatement de la consulta sql ingresada
     *
     * @param string $consulta
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
    protected function ejecuta_sql(string $consulta):array|stdClass{
        if($consulta === ''){
            return $this->error->error(mensaje: "Error consulta vacia", data: $consulta.' tabla: '.$this->tabla,
                params: get_defined_vars());
        }
        try {
            $result = $this->link->query($consulta);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al ejecutar sql '. $e->getMessage(),
                data: array($e->getCode().' '.$this->tabla.' '.$this->consulta.' '.$this->tabla,
                    'registro'=>$this->registro), params: get_defined_vars());
        }
        if($this->transaccion ==='INSERT'){
            $this->registro_id = $this->link->lastInsertId();
        }

        $mensaje = 'Exito';

        $data = new stdClass();
        $data->mensaje = $mensaje;
        $data->sql = $consulta;
        $data->result = $result;
        $data->registro = $this->registro;
        $data->registro_id = $this->registro_id;
        return $data;
    }

    /**
     * P INT
     * @param string $tabla
     * @param string $funcion
     * @param int $registro_id
     * @return array
     */
    protected function ejecuta_transaccion(string $tabla, string $funcion, int $registro_id = -1):array{
        $consulta = $this->consulta;
        if($this->consulta === ''){
            return $this->error->error('La consulta no puede venir vacia del modelo '.$this->tabla, $this->consulta);
        }
        $resultado = $this->ejecuta_sql(consulta: $consulta);
        if(errores::$error){
            return $this->error->error('Error al ejecutar sql en '.$tabla,$resultado);
        }
        $bitacora = $this->aplica_bitacora(consulta: $consulta, registro_id:  $registro_id,
            funcion: $funcion, tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al insertar bitacora en '.$tabla,$bitacora);
        }

        return $bitacora;
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
     * P ORDER P INT PROBADO ERRORREV
     * @param string $campo_row Nombre del campo del registro el cual se utiliza para la obtencion de los registros ligados
     * @param string $campo_filtro Nombre del campo del registro el cual se utiliza como valor del filtro
     * @param array $filtro Filtro precargado, es recursivo hace push con el nuevo resultado
     * @param array $row Registro donde se obtendra el valor y el campo para retornar el filtro nuevo
     * @return array
     * @url http://doc.ciproteo.com:3443/en/home/Sistemas/Manuales/Tecnicos/modelo_basico/filtro_hijo
     */
    private function filtro_hijo(string $campo_filtro, string $campo_row, array $filtro, array $row):array{ //DEBUG
        if($campo_row===''){
            return $this->error->error(mensaje: "Error campo vacio",data: $campo_row, params: get_defined_vars());
        }
        if($campo_filtro===''){
            return $this->error->error(mensaje: "Error filtro",data: $campo_filtro, params: get_defined_vars());
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
     * P ORDER P INT PROBADO
     * Devuelve un arreglo con los datos necesarios para obtener un filtro y ser utilizado en las sentencias de consulta
     * para la obtención de los registros esto de todos las columnas que se mandan por el filtro.
     * Genera arreglo
     * @param array $filtros arreglo de filtros para la obtencion de registros de hijos
     * @param array $row Registro donde se obtendra el valor y el campo para retornar el filtro nuevo
     * @return array
     */
    private function filtro_para_hijo(array $filtros, array $row):array{
        $filtro = array();
        foreach($filtros as $campo_filtro=>$campo_row){
            if($campo_row===''){
                return $this->error->error(mensaje: "Error campo vacio",data: $campo_filtro,
                    params: get_defined_vars());
            }
            $filtro = $this->filtro_hijo(campo_filtro: $campo_filtro, campo_row: $campo_row,filtro: $filtro, row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro, params: get_defined_vars());
            }
        }
        return $filtro;
    }


    /**
     * P INT P ORDER
     * Inserta un registro de bitacora de la tabla afectada
     * @param array $registro es el registro afectado por la accion del sistema
     * @param string $funcion es la funcion que se aplica sobre el registro
     * @param string $consulta el la sentencia sql de la funcion aplicada
     * @return array con registro de insersion de bitacora
     * @throws errores definidos en internals
     * @example
     *     $r_bitacora = $this->genera_bitacora($registro,$funcion, $consulta);
     * @uses modelo_basico->bitacora
     * @internal $this->maqueta_data_bitacora($registro,$funcion, $consulta);
     * @internal $bitacora_modelo->alta_bd();
     */
    private function genera_bitacora(string $consulta, string $funcion, array $registro): array{
        $namespace = 'models\\';
        $this->tabla = str_replace($namespace,'',$this->tabla);
        $clase = $namespace.$this->tabla;
        if($this->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        if($funcion === ''){
            return $this->error->error('Error $funcion no puede venir vacia',$funcion);
        }
        if($consulta === ''){
            return $this->error->error('Error $consulta no puede venir vacia',$consulta);
        }
        if($this->registro_id<=0){
            return $this->error->error('Error el id de $this->registro_id no puede ser menor a 0',$this->registro_id);
        }

        $bitacora_modelo = $this->genera_modelo(modelo: 'bitacora');
        if(errores::$error){
            return $this->error->error('Error al obtener bitacora',$bitacora_modelo->registro);
        }


        $bitacora_modelo->registro = $this->maqueta_data_bitacora(consulta:  $consulta, funcion: $funcion, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al obtener MAQUETAR REGISTRO PARA BITACORA',$bitacora_modelo->registro);
        }
        $r_bitacora = $bitacora_modelo->alta_bd();
        if(errores::$error){
            return $this->error->error('Error al insertar bitacora',$r_bitacora);
        }
        return $r_bitacora;
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
            return $this->error->error('El registro no puede venir vacio',$this->registro_upd);
        }

        $elemento_lista = $this->genera_modelo(modelo: 'elemento_lista');
        if(errores::$error){
            return $this->error->error('Error al generar modelo',$elemento_lista);
        }


        $campos = $elemento_lista->obten_campos(estructura_bd:array(), modelo:$this,vista:'modifica');
        if(errores::$error){
            return $this->error->error('Error al obtener campos',$campos);
        }

        $campos = $this->obten_campos_update();

        if(errores::$error){
            return $this->error->error('Error al obtener campos',$campos);
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
     * P INT
     * @param array $atributo
     * @param int $registro_id
     * @param string $tabla
     * @return array
     */
    private function inserta_atributo(array $atributo, int $registro_id, string $tabla): array
    {
        $keys = array('atributo_descripcion','atributo_id');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $atributo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar $atributo',data: $valida, params: get_defined_vars());
        }
        $keys = array('atributo_id');
        $valida = $this->validacion->valida_ids( keys:$keys, registro: $atributo);
        if(errores::$error){
            return $this->error->error('Error al validar $atributo',$valida);
        }
        if($registro_id<=0){
            return $this->error->error('Error registro_id debe ser mayor a 0',$registro_id);
        }

        $data_ins = $this->data_inst_attr(atributo: $atributo,registro_id:  $registro_id);
        if(errores::$error){
            return $this->error->error('Error al maquetar atributos', $data_ins);
        }

        $modelo = $this->genera_modelo(modelo: $tabla);
        if(errores::$error){
            return $this->error->error('Error al generar modelo', $modelo);
        }

        $r_ins = $modelo->alta_registro(registro: $data_ins);
        if(errores::$error){
            return $this->error->error('Error al insertar atributos', $r_ins);
        }
        return $r_ins;
    }

    /**
     * P INT
     * @param int $registro_id
     * @param string $tabla_attr
     * @return array
     */
    private function inserta_atributos( int $registro_id, string $tabla_attr): array
    {
        if($this->tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla esta vacia',data:  $this->tabla,
                params: get_defined_vars());
        }
        if($registro_id<=0){
            return $this->error->error('Error registro_id debe ser mayor a 0',$registro_id);
        }

        $modelo_atributo = $this->genera_modelo(modelo: 'atributo');
        if(errores::$error){
            return $this->error->error('Error al generar modelo', $modelo_atributo);
        }


        $atributos = $modelo_atributo->atributos($tabla_attr);
        if(errores::$error){
            return $this->error->error('Error al obtener atributos', $atributos);
        }

        foreach($atributos as $atributo){
            $r_ins = $this->inserta_atributo(atributo: $atributo,registro_id:  $registro_id,tabla:  $tabla_attr);
            if(errores::$error){
                return $this->error->error('Error al insertar atributos', $r_ins);
            }
        }
        return $atributos;
    }



    /**
     * P INT
     * @param string $clase_attr
     * @param int $registro_id
     * @return array
     */
    private function inserta_data_attr(string $clase_attr, int $registro_id): array
    {
        if($registro_id<=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0', data: $registro_id, params: get_defined_vars());
        }

        $model_attr = $this->genera_modelo(modelo: $clase_attr);
        if(errores::$error){
            return $this->error->error('Error al generar modelo', $model_attr);
        }

        $r_ins = $this->inserta_atributos(registro_id:  $registro_id,tabla_attr:  $model_attr->tabla);
        if(errores::$error){
            return $this->error->error('Error al insertar atributos', $r_ins);
        }
        return $r_ins;
    }



    /**
     * P INT P ORDER PROBADO
     * Funcion que genera el SQL para un SELECT
     * @param array $columnas columnas inicializadas a mostrar a peticion
     * @param array $extension_estructura columnas estructura tabla ligada 1 a 1
     * @param array $renombradas columnas estructura tabla ligadas renombradas
     * @return array|string string en forma de sql con los datos para la ejecucion de SELECT
     * @throws errores definidos en la formacion del SQL
     * @functions $this->obten_columnas_completas($columnas);
     * @functions $consulta_base->obten_tablas_completas($tabla, $this->columnas);
     * @functions $sub_querys_sql = $this->sub_querys($columnas);
     * @throws errores $tabla_original no es una clase o modelo
     *@example
     * $consulta = $this->genera_consulta_base($columnas);
     * @uses  $this->filtro_and
     * @uses  $this->obten_por_id
     * @uses  $this->obten_registros_activos
     * @uses  modelos->accion_grupo->obten_accion_permitida
     */

    protected function genera_consulta_base(array $columnas = array(), array $extension_estructura = array(),
                                            array $renombradas = array()):array|string{

        $this->tabla = str_replace('models\\','',$this->tabla);

        $columnas_seleccionables = $columnas;
        $columnas_sql = (new columnas())->obten_columnas_completas(modelo: $this,
            columnas_sql: $columnas_seleccionables, extension_estructura:  $extension_estructura,
            renombres:  $renombradas);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener columnas',data: $columnas_sql,
                params: get_defined_vars());
        }


        $tablas = (new joins())->tablas(columnas: $this->columnas, extension_estructura:  $extension_estructura,
            modelo: $this, renombradas: $renombradas, tabla: $this->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar joins', data: $tablas, params: get_defined_vars());
        }

        $sub_querys_sql = (new columnas())->sub_querys(columnas: $columnas_sql, modelo: $this,
            columnas_seleccionables: $columnas_seleccionables);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sub querys', data: $sub_querys_sql,
                params: get_defined_vars());
        }


        return /** @lang MYSQL */ "SELECT $columnas_sql $sub_querys_sql FROM $tablas";
    }





    /**
     * PROBADO P ORDER P INT
     * Funcion que genera un modelo a partir del nombre
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
            return  $this->error->error("Error al validar modelo",$valida);
        }
        return new $modelo($this->link);
    }

    /**
     * P ORDER P INT PROBADO ERROREV
     * Maqueta un arreglo para la generacion de modelos y sus registros asignados a un query para obtener sus dependientes o dependencias
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
     * @url http://doc.ciproteo.com:3443/en/home/Sistemas/Manuales/Tecnicos/modelo_basico/genera_modelos_hijos
     */
    private function genera_modelos_hijos(): array{//FIN DEBUG
        $modelos_hijos = array() ;
        foreach($this->hijo as $key=>$modelo){
            if(is_numeric($key)){
                return $this->error->error(mensaje: "Error en key",data: $this->hijo, params: get_defined_vars());
            }
            if(!isset($modelo['filtros'])){
                return $this->error->error(mensaje: "Error filtro",data: $this->hijo, params: get_defined_vars());
            }
            if(!isset($modelo['filtros_con_valor'])){
                return $this->error->error(mensaje:"Error filtro",data:$this->hijo, params: get_defined_vars());
            }
            if(!is_array($modelo['filtros'])){
                return $this->error->error(mensaje:"Error filtro",data:$this->hijo, params: get_defined_vars());
            }
            if(!is_array($modelo['filtros_con_valor'])){
                return $this->error->error(mensaje:"Error filtro",data:$this->hijo, params: get_defined_vars());
            }
            if(!isset($modelo['nombre_estructura'])){
                return $this->error->error(mensaje:"Error en estructura",data:$this->hijo, params: get_defined_vars());
            }

            $modelos_hijos[$key]['filtros']= $modelo['filtros'];
            $modelos_hijos[$key]['filtros_con_valor']= $modelo['filtros_con_valor'];
            $modelos_hijos[$key]['nombre_estructura']= $modelo['nombre_estructura'];
        }
        return $modelos_hijos;
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion que asigna los registros encontrados de hijos en un registro
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
                data: $data_modelo, params: get_defined_vars());
        }
        $filtro = $this->obten_filtro_para_hijo(data_modelo: $data_modelo,row: $row);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error filtro",data: $filtro, params: get_defined_vars());
        }
        $row = $this->asigna_registros_hijo(filtro: $filtro, name_modelo: $name_modelo,
            nombre_estructura: $data_modelo['nombre_estructura'],row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar registros de hijo', data: $row,
                params: get_defined_vars());
        }
        return $row;
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion que asigna y genera los registros encontrados de hijos en un registro
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
                return $this->error->error(mensaje: "Error en datos",data: $modelos_hijos, params: get_defined_vars());
            }

            if(!isset($data_modelo['nombre_estructura'])){
                return  $this->error->error(mensaje: 'Error debe existir $data_modelo[\'nombre_estructura\'] ',
                    data: $data_modelo, params: get_defined_vars());
            }

            $row = $this->genera_registro_hijo(data_modelo: $data_modelo, name_modelo: $name_modelo,
                row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar registros de hijo', data: $row,
                    params: get_defined_vars());
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
     * P INT P ORDER PROBADO
     * Funcion que asigna y genera los registros encontrados de hijos en un registro
     * @param array $modelos_hijos datos de parametrizacion de datos para la ejecucion de obtencion de los registros
     * @param PDOStatement $r_sql registro en forma de retorno de mysql nativo
     * @example
     *      $modelos_hijos = $this->genera_modelos_hijos();
    if(isset($modelos_hijos['error'])){
    return $this->error->error('Error al generar $modelos_hijos',$modelos_hijos);
    }
    $new_array = $this->maqueta_arreglo_registros($r_sql,$modelos_hijos);
     * @return array registro del modelo con registros hijos asignados
     * @throws errores Errores definidos en las creaciones de hijos
     */
    private function maqueta_arreglo_registros(array $modelos_hijos, PDOStatement $r_sql):array{
        $new_array = array();
        while( $row = $r_sql->fetchObject()){
            $row = (array) $row;
            if(count($modelos_hijos)>0) {
                $row = $this->genera_registros_hijos(modelos_hijos: $modelos_hijos,row:  $row);
                if (errores::$error) {
                    return $this->error->error(mensaje: "Error en registro",data: $row, params: get_defined_vars());
                }
            }
            $new_array[] = $row;
        }

        return $new_array;
    }

    /**
     * P INT P ORDER
     * Genera un array para insertarlo en la bitacora
     *
     * @param array $registro registro afectado
     * @param string $funcion funcion de modelo
     * @param string $consulta sql ejecutado
     *
     * @example
     *      $this->maqueta_data_bitacora($registro,$funcion, $consulta);
     *
     * @return array registro afectado
     * @throws errores definidos en internal
     * @uses modelo_basico->genera_bitacora
     * @internal $this->obten_seccion_bitacora();
     * @internal $this->asigna_registro_para_bitacora($seccion_menu,$registro,$funcion, $consulta);
     */
    private function maqueta_data_bitacora(string $consulta, string $funcion, array $registro):array{
        $namespace = 'models\\';
        $this->tabla = str_replace($namespace,'',$this->tabla);
        $clase = $namespace.$this->tabla;
        if($this->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }
        if($funcion === ''){
            return $this->error->error('Error $funcion no puede venir vacia',$funcion);
        }
        if($consulta === ''){
            return $this->error->error('Error $consulta no puede venir vacia',$consulta);
        }
        if($this->registro_id<=0){
            return $this->error->error('Error el id de $this->registro_id no puede ser menor a 0',$this->registro_id);
        }
        $seccion_menu = $this->obten_seccion_bitacora();
        if(errores::$error){
            return $this->error->error('Error al obtener seccion', $seccion_menu);
        }

        $registro = $this->asigna_registro_para_bitacora(consulta: $consulta, funcion: $funcion, registro: $registro, seccion_menu: $seccion_menu);
        if(errores::$error){
            return $this->error->error('Error al obtener MAQUETAR REGISTRO PARA BITACORA', $registro);
        }


        return $registro;
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
            return $this->error->error('El registro no puede venir vacio',$this->registro_upd);
        }
        $campos = $this->campos();
        if (errores::$error) {
            return $this->error->error('Error al generar campos', $campos);
        }

        return $campos;
    }

    /**
     * P INT P ORDER
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
     * @param string $campo
     * @param string|int|float|null $value
     * @param string $campos
     * @return array|string
     */
    private function maqueta_rows_upd(string $campo, string $campos, string|int|float|null $value): array|string
    {
        if(is_numeric($campo)){
            return $this->error->error('Error ingrese un campo valido',$this->registro_upd);
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
        $campos .= $campos == "" ? "$params->campo = $params->value" : ", $params->campo = $params->value";
        return $campos;
    }

    /**
     * P ORDER P INT
     * @param string $campo
     * @param string|float|int|null $value
     * @return array|stdClass
     */
    private function params_data_update(string $campo, string|float|int|null $value): array|stdClass
    {
        $value = $this->value_moneda(campo: $campo, value: $value);
        if (errores::$error) {
            return $this->error->error('Error al limpiar value', $value);
        }

        $data = $this->slaches_value(campo: $campo,value:  $value);
        if (errores::$error) {
            return $this->error->error('Error al limpiar value', $value);
        }

        $data->value = $this->value_null(value: $data->value);
        if (errores::$error) {
            return $this->error->error('Error al asignar value', $data);
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
     * P ORDER P INT
     * @param string $campo
     * @param string|float|int|null $value
     * @return float|array|int|string|null
     */
    private function value_moneda(string $campo, string|float|int|null $value): float|array|int|string|null
    {
        $tipos_moneda = array('double','double_con_cero');
        if(array_key_exists($campo, $this->tipo_campos)){
            $value = $this->reasigna_value_moneda(campo: $campo,tipos_moneda:  $tipos_moneda,value:  $value);
            if (errores::$error) {
                return $this->error->error('Error al limpiar value', $value);
            }
        }
        return $value;
    }

    /**
     * P ORDER P INT
     * @param string $campo
     * @param array $tipos_moneda
     * @param string|int|float|null $value
     * @return float|array|int|string|null
     */
    private function reasigna_value_moneda(string $campo, array $tipos_moneda, string|int|float|null $value): float|array|int|string|null
    {
        if($campo === ''){
            return $this->error->error('Error campo no puede venir vacio', $campo);
        }
        if(!isset($this->tipo_campos[$campo])){
            return $value;
        }
        $tipo_dato = $this->tipo_campos[$campo];
        $value = $this->limpia_monedas_values(tipo_dato: $tipo_dato,tipos_moneda:  $tipos_moneda,value:  $value);
        if (errores::$error) {
            return $this->error->error('Error al limpiar value', $value);
        }
        return $value;
    }

    /**
     * P INT P ORDER
     * @param string $tipo_dato
     * @param array $tipos_moneda
     * @param int|string|float|null $value
     * @return float|array|int|string|null
     */
    private function limpia_monedas_values(string $tipo_dato, array $tipos_moneda, int|string|float|null $value): float|array|int|string|null
    {
        if(in_array($tipo_dato, $tipos_moneda, true)) {
            $value = $this->limpia_moneda_value(value: $value);
            if (errores::$error) {
                return $this->error->error('Error al limpiar value', $value);
            }
        }
        return $value;
    }

    /**
     * P ORDER P INT PROBADO
     * @param string|int|float|null $value
     * @return string|int|float|null
     */
    private function limpia_moneda_value(string|int|float|null $value): string|int|float|null
    {
        if($value === null){
            return null;
        }
        $value = trim($value);
        return str_replace(array('$', ','), '', $value);

    }

    /**
     * P INT P ORDER PROBADO
     * Funcion que obtiene todas las columnas de un modelo para su transaformacion en sql, además asigna a una
     *  variable de session para su reutilizacion futura
     * @param string $tabla_original nombre del modelo
     * @return array|stdClass conjunto de columnas para la futura transaformacion de un sql
     * @example
     * $columnas_parseadas = $this->obten_columnas($tabla_original);
     */
    public function obten_columnas(string $tabla_original):array|stdClass{
        $tabla_original = trim(str_replace('models\\','',$tabla_original));
        $tabla_bd = $tabla_original;
        $clase_modelo = 'models\\'.$tabla_original;

        if($tabla_bd === ''){
            return  $this->error->error(mensaje: 'Error tabla original no puede venir vacia',data: $tabla_bd,
                params: get_defined_vars());
        }
        if(!class_exists($clase_modelo)){
            return $this->error->error(mensaje: 'Error no existe el modelo '.$clase_modelo,data:  $clase_modelo,
                params: get_defined_vars());
        }

        $se_asignaron_columnas = (new columnas())->asigna_columnas_en_session(modelo: $this, tabla_bd: $tabla_bd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar columnas', data: $se_asignaron_columnas,
                params: get_defined_vars());
        }
        if(!$se_asignaron_columnas){
            $columnas_field = (new columnas())->asigna_columnas_session_new(modelo:$this, tabla_bd: $tabla_bd);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener columnas', data: $columnas_field,
                    params: get_defined_vars());
            }
        }

        return $this->data_columnas;
    }




    /**
     * P ORDER P INT PROBADO
     * Funcion que genera un filtro para ser enviado en forma de array para consultas posteriores
     *
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
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, params: get_defined_vars());
        }
        if(!isset($data_modelo['filtros_con_valor'])){
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, params: get_defined_vars());
        }
        if(!is_array($data_modelo['filtros'])){
            return $this->error->error(mensaje: "Error filtro",data: $data_modelo, params: get_defined_vars());
        }
        if(!is_array($data_modelo['filtros_con_valor'])){
            return $this->error->error("Error filtro",$data_modelo);
        }

        $filtros = $data_modelo['filtros'];
        $filtros_con_valor = $data_modelo['filtros_con_valor'];

        $filtro = $this->filtro_para_hijo(filtros: $filtros,row: $row);
        if(errores::$error){
            return $this->error->error(mensaje: "Error filtro",data: $filtro, params: get_defined_vars());
        }

        foreach($filtros_con_valor as $campo_filtro=>$value){
            $filtro[$campo_filtro] = $value;
        }

        return $filtro;
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion que obtiene con base en la tabla renombrada si tabla renombrada no es vacia cambia el nombre a tabla original
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
            return $this->error->error('Error no pueden venir vacios todos los parametros', $tabla_renombrada);
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
     * P INT P ORDER
     * Funcion que obtiene el registro de seccion menu para aplicacion de una bitacora
     * @example
    $seccion_menu = $this->obten_seccion_bitacora();
     * @return array registro de seccion menu encontrado
     * @throws errores definidos en filtro and
     * @throws errores si no se encontro registro
     * @internal  $seccion_menu_modelo->filtro_and($filtro);
     * @uses modelo_basico->maqueta_data_bitacora
     * @version Falta de UT
     */
    private function obten_seccion_bitacora(): array
    {
        $namespace = 'models\\';
        $this->tabla = str_replace($namespace,'',$this->tabla);
        $clase = $namespace.$this->tabla;
        if($this->tabla === ''){
            return $this->error->error('Error this->tabla no puede venir vacio',$this->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }

        $seccion_menu_modelo = $this->genera_modelo(modelo: 'seccion_menu');
        if(errores::$error){
            return $this->error->error('Error al generar modelo',$seccion_menu_modelo);
        }


        $filtro['seccion_menu.descripcion'] = $this->tabla;
        $r_seccion_menu = $seccion_menu_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener seccion menu',$r_seccion_menu);
        }
        if((int)$r_seccion_menu['n_registros'] === 0){
            return $this->error->error('Error no existe la seccion menu',$r_seccion_menu);
        }
        return $r_seccion_menu['registros'][0];
    }



    /**
     * P INT P ORDER PROBADO
     * Funcion reemplaza el primer dato encontrado en la posicion 0
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
     * @uses ubicaciones
     * @uses cliente
     * @uses prospecto
     */
    public function str_replace_first(string $content, string $from, string $to):array|string{
        if($content === ''){
            return $this->error->error('Error al content esta vacio',$content);
        }
        if($from === ''){
            return $this->error->error('Error from esta vacio',$from);
        }
        $pos = strpos($content, $from);


        if($pos === 0) {
            $from = '/' . preg_quote($from, '/') . '/';
            return preg_replace($from, $to, $content, 1);
        }

        return $content;
    }

    /**
     * P ORDER P INT PROBADO
     * Funcion genera order en forma de sql
     * @param array  $order con parametros para generar sentencia
     * @return array|string cadena con order en forma de SQL
     * @throws errores if order[campo] es un numero
     * @example
     * $order_sql = $this->order_sql($order);
     * @uses modelo
     */
    protected function order_sql(array $order):array|string{
        $order_sql = '';
        foreach ($order as $campo=>$tipo_order){
            if(is_numeric($campo)){
                return $this->error->error('Error $campo debe ser txt',$order);
            }
            if($order_sql === ''){
                $order_sql.=' ORDER BY '.$campo.' '.$tipo_order;
            }
            else {
                $order_sql .= ',' . $campo.' '.$tipo_order;
            }
        }
        return $order_sql;
    }

    /**
     * P INT P ORDER PROBADO
     * Funcion que asigna y genera los registros encontrados en un query
     * @param PDOStatement $r_sql registro en forma de retorno de mysql nativo
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
    private function parsea_registros_envio(PDOStatement $r_sql):array{

        $modelos_hijos = $this->genera_modelos_hijos();
        if(errores::$error){
            return $this->error->error(mensaje: "Error al general modelo",data: $modelos_hijos,
                params: get_defined_vars());
        }
        $new_array = $this->maqueta_arreglo_registros(modelos_hijos: $modelos_hijos, r_sql: $r_sql);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al generar arreglo con registros',data: $new_array,
                params: get_defined_vars());
        }

        return $new_array;
    }




}


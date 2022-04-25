<?php
namespace base\orm;
use gamboamartin\errores\errores;
use PDO;
use PDOStatement;
use stdClass;
use Throwable;

class modelo extends modelo_base {

    public array $sql_seguridad_por_ubicacion ;
    public array $campos_tabla = array();
    public array $extensiones_imagen = array('jpg','jpeg','png');
    public bool $aplica_transaccion_inactivo;
    public array $order = array();
    public int $limit = 0;
    public int $offset = 0;
    public array $extension_estructura = array();
    public array $renombres = array();
    public bool $validation;


    /**
     *
     * @param PDO $link Conexion a la BD
     * @param string $tabla
     * @param array $columnas_extra
     * @param array $campos_obligatorios
     * @param array $tipo_campos
     * @param array $columnas
     * @param array $sub_querys
     * @param bool $aplica_transaccion_inactivo
     * @param bool $aplica_bitacora
     * @param bool $aplica_seguridad
     * @param array $extension_estructura
     * @param array $renombres
     * @param bool $validation
     */
    public function __construct(PDO $link, string $tabla, bool $aplica_bitacora = false, bool $aplica_seguridad = false,
                                bool $aplica_transaccion_inactivo = true, array $campos_obligatorios= array(),
                                array $columnas = array(), array $columnas_extra = array(),
                                array $extension_estructura = array(), array $no_duplicados = array(),
                                array $renombres = array(), array $sub_querys = array(), array $tipo_campos = array(),
                                bool $validation = false){


        $tabla = str_replace('models\\','',$tabla);
        parent::__construct($link);


        $this->tabla = $tabla;
        $this->columnas_extra = $columnas_extra;
        $this->columnas = $columnas;
        $this->aplica_bitacora = $aplica_bitacora;
        $this->aplica_seguridad = $aplica_seguridad;
        $this->extension_estructura = $extension_estructura;
        $this->renombres = $renombres;
        $this->validation = $validation;
        $this->no_duplicados = $no_duplicados;

        if(isset($_SESSION['usuario_id'])){
            $this->usuario_id = (int)$_SESSION['usuario_id'];
        }
        if($tabla !=='') {

            $data = $this->obten_columnas(tabla_original: $tabla);
            if (errores::$error) {
                $error = $this->error->error(mensaje: 'Error al obtener columnas de '.$tabla, data: $data,
                    params: get_defined_vars());
                print_r($error);
                die('Error');
            }
            $this->campos_tabla = $data->columnas_parseadas;
        }
        $campos_obligatorios_parciales = array('accion_id','codigo','descripcion','grupo_id','seccion_id');

        foreach($campos_obligatorios_parciales as $campo){
            if(in_array($campo, $this->campos_tabla, true)){
                $this->campos_obligatorios[]=$campo;
            }
        }

        $this->sub_querys = $sub_querys;
        $this->sql_seguridad_por_ubicacion = array();
        $this->campos_obligatorios = array_merge($this->campos_obligatorios,$campos_obligatorios);

        if(isset($campos_obligatorios[0]) && trim($campos_obligatorios[0]) === '*'){

            $this->campos_obligatorios = $this->campos_tabla;

            $unsets = array('fecha_alta','fecha_update','id','usuario_alta_id','usuario_update_id');

            foreach($this->campos_obligatorios as $key=>$campo_obligatorio){
                if(in_array($campo_obligatorio, $unsets, true)) {
                    unset($this->campos_obligatorios[$key]);
                }
            }
        }

        $this->tipo_campos = $tipo_campos;


        $this->aplica_transaccion_inactivo = $aplica_transaccion_inactivo;

        if($this->aplica_seguridad) {
            $usuario_modelo = $this->genera_modelo(modelo: 'usuario');
            if (errores::$error) {
                $error = $this->error->error( 'Error al generar modelo', $usuario_modelo);
                print_r($error);
                die('Error');
            }

            $seguridad = $usuario_modelo->filtro_seguridad($this->tabla);
            if (errores::$error) {
                $error = $this->error->error( 'Error al obtener filtro de seguridad', $seguridad);
                print_r($error);
                die('Error');
            }
            $this->filtro_seguridad = $seguridad;
        }

        $this->key_id = $this->tabla.'_id';
        $this->key_filtro_id = $this->tabla.'.id';
    }


    /**
     * P INT ERRROREV
     * @param bool $reactiva
     * @return array
     */
    public function activa_bd(bool $reactiva = false): array{ //FIN
        $namespace = 'models\\';
        $this->tabla = str_replace($namespace,'',$this->tabla);

        if($this->registro_id <= 0){
            return $this->error->error(mensaje: 'Error id debe ser mayor a 0 en '.$this->tabla,data: $this->registro_id,
                params: get_defined_vars());
        }
        if(!$reactiva) {

            $registro = $this->registro(registro_id: $this->registro_id);
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al obtener registro '.$this->tabla,data:$registro,
                    params: get_defined_vars());
            }

            $valida = $this->validacion->valida_transaccion_activa(
                aplica_transaccion_inactivo: $this->aplica_transaccion_inactivo,
                registro_id: $this->registro_id,tabla:  $this->tabla,registro: $registro);
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al validar transaccion activa en '.$this->tabla,data:$valida,
                    params: get_defined_vars() );
            }
        }
        $this->consulta = "UPDATE " . $this->tabla . " SET status = 'activo' WHERE id = " . $this->registro_id;
        $this->transaccion = 'ACTIVA';

        $transaccion = $this->ejecuta_transaccion(tabla: $this->tabla,funcion: __FUNCTION__,
            registro_id: $this->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al EJECUTAR TRANSACCION en '.$this->tabla,data:$transaccion,
                params: get_defined_vars());
        }

        return array('mensaje'=>'Registro activado con éxito en '.$this->tabla, 'registro_id'=>$this->registro_id);
    }

    /**
     * PARAMS ORDER P INT
     * Aplica status = a activo a todos los elementos o registros de un atabla
     * @return array
     */
    public function activa_todo(): array
    {
        $this->transaccion = 'UPDATE';
        $consulta = "UPDATE " . $this->tabla . " SET status = 'activo'  ";

        $resultado = $this->ejecuta_sql(consulta: $consulta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: $resultado, params: get_defined_vars());
        }

        return array('mensaje'=>'Registros activados con éxito','sql'=>$this->consulta);
    }

    public function add_column(string $campo, string $alias): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error $campo no puede venir vacio', $campo);
        }
        $alias = trim($alias);
        if($alias === ''){
            return $this->error->error('Error $alias no puede venir vacio', $alias);
        }
        return 'IFNULL( SUM('. $campo .') ,0)AS ' . $alias;
    }


    /**
     * P INT
     * inserta un registro por registro enviado
     * @return array|stdClass con datos del registro insertado
     * @example
     *      $entrada_modelo->registro = array('tipo_entrada_id'=>1,'almacen_id'=>1,'fecha'=>'2020-01-01',
     *          'proveedor_id'=>1,'tipo_proveedor_id'=>1,'referencia'=>1,'tipo_almacen_id'=>1);
     * $resultado = $entrada_modelo->alta_bd();
     *
     * @internal  $this->valida_campo_obligatorio();
     * @internal  $this->valida_estructura_campos();
     * @internal  $this->asigna_data_user_transaccion();
     * @internal  $this->bitacora($this->registro,__FUNCTION__,$consulta);
     * @uses  todo el sistema
     */
    public function alta_bd(): array|stdClass{
        $this->status_default = 'activo';
        $registro = $this->registro_ins(registro: $this->registro,status_default: $this->status_default);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar registro ', data: $registro, params: get_defined_vars());
        }

        $valida = (new val_sql())->valida_base_alta(campos_obligatorios: $this->campos_obligatorios, modelo: $this,
            no_duplicados: $this->no_duplicados, registro: $registro,tabla:  $this->tabla,
            tipo_campos: $this->tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar alta ', data: $valida, params: get_defined_vars());
        }


        $data_log = $this->genera_data_log();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data log', data: $data_log, params: get_defined_vars());
        }

        $resultado = $this->inserta_sql(data_log: $data_log);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $resultado, params: get_defined_vars());
        }

        $transacciones = $this->transacciones_default(consulta: $resultado->sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transacciones',data:  $transacciones,
                params: get_defined_vars());
        }

        $registro = $this->registro(registro_id: $this->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro', data: $registro, params: get_defined_vars());
        }

        $data = new stdClass();
        $data->mensaje = "Registro insertado con éxito";
        $data->registro_id = $this->registro_id;
        $data->sql = $resultado->sql;
        $data->registro = $registro;

        return $data;
    }

    /**
     * P INT P ORDER ERRORREV
     * @param array $registro
     * @param string $status_default
     * @return array
     */
    private function registro_ins(array $registro, string $status_default): array
    {
        $registro = (new inicializacion())->status(registro: $registro,status_default:  $status_default);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar status ', data: $registro, params: get_defined_vars());
        }

        $registro = (new data_format())->ajusta_campos_moneda(registro: $registro, tipo_campos: $this->tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar campo ', data: $registro, params: get_defined_vars());
        }
        $this->registro = $registro;
        return $registro;
    }

    /**
     * P ORDER P INT
     * @param array $registro Registro con datos para la insersion
     * @return array
     */
    public function alta_registro(array $registro):array{ //FIN
        $this->registro = $registro;

        $r_alta  = $this->alta_bd();
        if(errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta registro', data: $r_alta, params: get_defined_vars());
        }
        return $r_alta;
    }



    /**
     * P INT P ORDER
     * @return array
     */
    private function aplica_eliminacion_dependencias(): array
    {
        $data = array();
        if($this->desactiva_dependientes) {
            $elimina = $this->elimina_data_modelos_dependientes();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al eliminar dependiente', data: $elimina,
                    params: get_defined_vars());
            }
            $data = $elimina;
        }
        return $data;
    }






    /**
     * P INT P ORDER ERROREV
     * @param string $campos
     * @param string $campo
     * @return string|array
     */
    private function campos_alta_sql(string $campo, string $campos): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo esta vacio', data: $campo, params: get_defined_vars());
        }
        $campos .= $campos === '' ? $campo : ",$campo";
        return $campos;
    }

    /**
     * PHPUNIT
     * @param array $campos
     * @return array|string
     */
    private function columnas_suma(array $campos): array|string
    {
        if(count($campos)===0){
            return $this->error->error('Error campos no puede venir vacio',$campos);
        }
        $columnas = '';
        foreach($campos as $alias =>$campo){
            if(is_numeric($alias)){
                return $this->error->error('Error $alias no es txt $campos[alias]=campo',$campos);
            }
            if($campo === ''){
                return $this->error->error('Error $campo esta vacio $campos[alias]=campo',$campos);
            }

            $data = $this->data_campo_suma($campo, $alias, $columnas);
            if(errores::$error){
                return $this->error->error('Error al agregar columna',$data);
            }
            $columnas .= "$data->coma $data->column";

        }
        return $columnas;
    }

    /**
     * PHPUNIT
     * @param string $columnas
     * @return string
     */
    private function coma_sql(string $columnas): string
    {
        $columnas = trim($columnas);
        $coma = '';
        if($columnas !== ''){
            $coma = ' , ';
        }
        return $coma;
    }

    /**
     * P INT P ORDER ERRROEV
     * @param array $group_by
     * @param array $order
     * @param int $limit
     * @param int $offset
     * @param string $tipo_filtro
     * @param array $filtro
     * @param array $filtro_especial
     * @param array $filtro_rango
     * @param array $filtro_extra
     * @param array $not_in
     * @param string $sql_extra
     * @param array $filtro_fecha
     * @return array|stdClass
     */
    private function complemento_sql(array $filtro, array $filtro_especial, array $filtro_extra, array $filtro_rango,
                                     array $group_by, int $limit, array $not_in, int $offset, array $order,
                                     string $sql_extra, string $tipo_filtro, array $filtro_fecha = array()): array|stdClass
    {

        if($limit<0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0',data:  $limit,
                params: get_defined_vars());
        }
        if($offset<0){
            return $this->error->error(mensaje: 'Error $offset debe ser mayor o igual a 0',data: $offset,
                params: get_defined_vars());

        }
        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data:$verifica_tf,
                params: get_defined_vars());
        }

        $params = $this->params_sql(group_by: $group_by,limit:  $limit,offset:  $offset, order:  $order);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar parametros sql',data:$params);
        }

        $filtros = (new where())->data_filtros_full(columnas_extra: $this->columnas_extra, filtro: $filtro,
            filtro_especial:  $filtro_especial, filtro_extra:  $filtro_extra, filtro_fecha:  $filtro_fecha,
            filtro_rango:  $filtro_rango, keys_data_filter: $this->keys_data_filter, not_in: $not_in,
            sql_extra: $sql_extra, tipo_filtro: $tipo_filtro);


        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtros',data:$filtros, params: get_defined_vars());
        }
        $filtros->params = $params;
        return $filtros;
    }

    /**
     * P ORDER P INT ERRORREV
     * @param string $consulta
     * @param stdClass $complemento
     * @return string|array
     */
    private function consulta_full_and(stdClass $complemento, string $consulta): string|array
    {

        $consulta = trim($consulta);
        if($consulta === ''){
            return $this->error->error(mensaje: 'Error $consulta no puede venir vacia',data: $consulta,
                params: get_defined_vars());
        }

        $complemento = (new where())->limpia_filtros(filtros: $complemento,keys_data_filter:  $this->columnas_extra);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al limpiar filtros',data:$complemento,
                params: get_defined_vars());
        }

        $complemento_r = (new where())->init_params_sql(complemento: $complemento,keys_data_filter: $this->keys_data_filter);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al inicializar params',data:$complemento_r,
                params: get_defined_vars());
        }


        $this->consulta = $consulta.$complemento_r->where.$complemento_r->sentencia.' '.$complemento_r->filtro_especial.' ';
        $this->consulta.= $complemento_r->filtro_rango.' '.$complemento_r->filtro_fecha.' ';
        $this->consulta.= $complemento_r->filtro_extra.' '.$complemento_r->sql_extra.' '.$complemento_r->not_in.' ';
        $this->consulta.= $complemento_r->params->group_by.' '.$complemento_r->params->order.' ';
        $this->consulta.= $complemento_r->params->limit.' '.$complemento_r->params->offset;
        return $this->consulta;
    }



    /**
     * P INT P ORDER ERRORREV
     * @param array $filtro
     * @param string $tipo_filtro
     * @param array $filtro_especial
     * @param array $filtro_rango
     * @param array $filtro_fecha
     * @return array|int
     */
    public function cuenta(
        array $filtro = array(), string $tipo_filtro = 'numeros', array $filtro_especial = array(),
        array $filtro_rango = array(), array $filtro_fecha = array()):array|int{

        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf,
                params: get_defined_vars());
        }

        $tablas = (new joins())->obten_tablas_completas(columnas_join:  $this->columnas, tabla: $this->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al obtener tablas", data: $tablas, params: get_defined_vars());
        }

        $filtros = (new where())->data_filtros_full(columnas_extra: $this->columnas_extra, filtro:  $filtro,
            filtro_especial: $filtro_especial, filtro_extra: array(), filtro_fecha: $filtro_fecha,
            filtro_rango: $filtro_rango, keys_data_filter: $this->keys_data_filter, not_in: array(), sql_extra: '',
            tipo_filtro: $tipo_filtro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtros',data: $filtros, params: get_defined_vars());
        }

        $sql = /** @lang MYSQL */
            " SELECT COUNT(*) AS total_registros FROM $tablas $filtros->where $filtros->sentencia 
            $filtros->filtro_especial $filtros->filtro_rango";

        $result = $this->ejecuta_consulta(consulta: $sql);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al ejecutar sql',data: $result, params: get_defined_vars());
        }

        return (int)$result->registros[0]['total_registros'];

    }

    /**
     * PHPUNIT
     * @param string $campo
     * @param string $alias
     * @param string $columnas
     * @return array|stdClass
     */
    private function data_campo_suma(string $campo, string $alias, string $columnas): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error $campo no puede venir vacio', $campo);
        }
        $alias = trim($alias);
        if($alias === ''){
            return $this->error->error('Error $alias no puede venir vacio', $alias);
        }

        $column = (new sql_sum())->add_column($campo, $alias);
        if(errores::$error){
            return $this->error->error('Error al agregar columna',$column);
        }

        $coma = $this->coma_sql($columnas);
        if(errores::$error){
            return $this->error->error('Error al agregar coma',$coma);
        }

        $data = new stdClass();
        $data->column = $column;
        $data->coma = $coma;

        return $data;
    }





    /**
     * P INT P ORDER ERROREV
     * @param bool|PDOStatement $alta_valido
     * @param bool|PDOStatement $update_valido
     * @param string $campos
     * @param string $valores
     * @return array|stdClass
     */
    private function data_log(bool|PDOStatement $alta_valido, string $campos, bool|PDOStatement $update_valido, string $valores): array|stdClass
    {
        if($alta_valido &&  $update_valido ){
            $data_asignacion = $this->asigna_data_user_transaccion();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar datos de transaccion', data: $data_asignacion,
                    params: get_defined_vars());
            }
            $campos .= $data_asignacion['campos'];
            $valores .= $data_asignacion['valores'];
        }

        $data = new stdClass();
        $data->campos = $campos;
        $data->valores = $valores;
        return $data;
    }

    /**
     * P INT P ORDER ERROREV
     * @return stdClass
     */
    private function data_para_log(): stdClass
    {
        $existe_alta_id = /** @lang MYSQL */
            "SELECT count(usuario_alta_id) FROM " . $this->tabla;
        $existe_update_id = /** @lang MYSQL */
            "SELECT count(usuario_alta_id) FROM $this->tabla";

        $alta_valido = $this->link->query($existe_alta_id);
        $update_valido = $this->link->query($existe_update_id);

        $data = new stdClass();
        $data->alta_valido = $alta_valido;
        $data->update_valido = $update_valido;
        return $data;
    }

    /**
     * P INT P ORDER PROBADO ERRORREV
     * @param string $where
     * @param string $sentencia
     * @param string $campo
     * @param string $value
     * @return array|stdClass
     */
    private function data_sentencia(string $campo, string $sentencia, string $value, string $where): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio',data: $campo, params: get_defined_vars());
        }

        if($where === ''){
            $where = ' WHERE ';
        }

        $sentencia_env = $this->sentencia_or(campo:  $campo, sentencia: $sentencia, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql',data:$sentencia_env, params: get_defined_vars());
        }
        $data = new stdClass();
        $data->where = $where;
        $data->sentencia = $sentencia_env;
        return $data;
    }

    /**
     * P INT P ORDER
     * @return  array
     */
    private function data_session_alta(): array
    {
        if($this->tabla === ''){
            return  $this->error->error('Error this->tabla esta vacia',$this->tabla);
        }
        if($this->registro_id <=0){
            return  $this->error->error('Error $this->registro_id debe ser mayor a 0',$this->registro_id);
        }
        $_SESSION['exito'][]['mensaje'] = $this->tabla.' se agrego con el id '.$this->registro_id;
        return $_SESSION['exito'];
    }

    /**
     * PHPUNIT
     * @return array
     */
    public function desactiva_bd(): array{ //FIN
        if($this->registro_id<=0){
            return  $this->error->error('Error $this->registro_id debe ser mayor a 0',$this->registro_id);
        }
        $registro = $this->registro(registro_id: $this->registro_id);
        if(errores::$error){
            return  $this->error->error('Error al obtener registro',$registro);
        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $this->aplica_transaccion_inactivo, registro: $registro,
            registro_id:  $this->registro_id, tabla: $this->tabla);
        if(errores::$error){
            return  $this->error->error('Error al validar transaccion activa',$valida);
        }
        $tabla = $this->tabla;
        $this->consulta = /** @lang MYSQL */
            "UPDATE $tabla SET status = 'inactivo' WHERE id = $this->registro_id";
        $this->transaccion = 'DESACTIVA';
        $transaccion = $this->ejecuta_transaccion(tabla: $this->tabla,funcion: __FUNCTION__,registro_id:  $this->registro_id);
        if(errores::$error){
            return  $this->error->error('Error al EJECUTAR TRANSACCION',$transaccion);
        }

        $desactiva = $this->aplica_desactivacion_dependencias();
        if (errores::$error) {
            return $this->error->error('Error al desactivar dependiente', $desactiva);
        }

        return array('mensaje'=>'Registro desactivado con éxito', 'registro_id'=>$this->registro_id);

    }

    /**
     * PHPUNIT
     * @return array
     */
    public function desactiva_todo(): array
    { //PRUEBA COMPLETA PROTEO

        $consulta = /** @lang MYSQL */
            "UPDATE  $this->tabla SET status='inactivo'";

        $this->link->query($consulta);
        if($this->link->errorInfo()[1]){
            return  $this->error->error($this->link->errorInfo()[0],'');
        }
        else{
            return array('mensaje'=>'Registros desactivados con éxito');
        }
    }

    /**
     * P INT P ORDER
     * Elimina un registro por el id enviado

     * @param int $id id del registro a eliminar
     * @example
     *      $registro = $this->modelo->elimina_bd($this->registro_id);
     *
     * @return array con datos del registro eliminado
     * @throws errores Si $id < 0
     * @throws errores definidas en internals
     * @throws errores si no existe registro
     * @internal  $this->validacion->valida_transaccion_activa($this, $this->aplica_transaccion_inactivo, $this->registro_id, $this->tabla);
     * @internal  $this->obten_data();
     * @internal  $this->ejecuta_sql();
     * @internal  $this->bitacora($registro_bitacora,__FUNCTION__,$consulta);
     * @uses  todo el sistema
     */
    public function elimina_bd(int $id): array{ //PRUEBA COMPLETA PROTEO
        if($id <= 0){
            return  $this->error->error(mensaje: 'El id no puede ser menor a 0 en '.$this->tabla, data: $id
                , params: get_defined_vars());
        }
        $this->registro_id = $id;

        $registro = $this->registro(registro_id: $this->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro' .$this->tabla, data: $registro,
                params: get_defined_vars());

        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $this->aplica_transaccion_inactivo, registro:  $registro,
            registro_id:  $this->registro_id, tabla:  $this->tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar transaccion activa en' .$this->tabla,data: $valida,
                params: get_defined_vars());

        }

        $registro_bitacora = $this->obten_data();
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener registro en '.$this->tabla, data:$registro_bitacora,
                params: get_defined_vars());
        }
        $tabla = $this->tabla;
        $this->consulta = /** @lang MYSQL */
            'DELETE FROM '.$tabla. ' WHERE id = '.$id;
        $consulta = $this->consulta;
        $this->transaccion = 'DELETE';

        $elimina = $this->aplica_eliminacion_dependencias();
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al eliminar dependiente', data:$elimina,
                params: get_defined_vars());
        }

        $resultado = $this->ejecuta_sql(consulta: $this->consulta);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql en '.$this->tabla, data:$resultado,
                params: get_defined_vars());
        }
        $bitacora = $this->bitacora(consulta: $consulta, funcion: __FUNCTION__, registro: $registro_bitacora);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar bitacora de '.$this->tabla, data: $bitacora,
                params: get_defined_vars());
        }

        return array('mensaje'=>'Registro eliminado con éxito', 'registro_id'=>$id);

    }

    /**
     * P INT P ORDER
     * @return string[]
     */
    public function elimina_con_filtro_and(): array{ //PRUEBA COMPLETA PROTEO
        if(count($this->filtro) === 0){
            return $this->error->error('Error no existe filtro', $this->filtro);
        }

        $result = $this->filtro_and(filtro: $this->filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener registros '.$this->tabla, $result);
        }

        foreach ($result['registros'] as $row){

            $del = $this->elimina_bd(id:$row[$this->tabla.'_id']);
            if(errores::$error){
                return $this->error->error('Error al eliminar registros '.$this->tabla, $del);
            }

        }

        return array('mensaje'=>'Registro eliminado con éxito');

    }

    /**
     * P INT P ORDER
     * @param string $modelo_dependiente
     * @return array
     */
    private function elimina_data_modelo(string $modelo_dependiente): array
    {
        $modelo_dependiente = trim($modelo_dependiente);
        $valida = $this->validacion->valida_data_modelo(name_modelo: $modelo_dependiente);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar modelo",data: $valida, params: get_defined_vars());
        }
        if($this->registro_id<=0){
            return $this->error->error(mensaje:'Error $this->registro_id debe ser mayor a 0',data:$this->registro_id,
                params: get_defined_vars());
        }

        $modelo = $this->genera_modelo(modelo: $modelo_dependiente);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar modelo', data:$modelo, params: get_defined_vars());
        }
        $desactiva = $this->elimina_dependientes(model:  $modelo, parent_id: $this->registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al desactivar dependiente',data: $desactiva,
                params: get_defined_vars());
        }
        return $desactiva;
    }

    /**
     * P INT P ORDER
     * @return array
     */
    private function elimina_data_modelos_dependientes(): array
    {
        $data = array();
        foreach ($this->models_dependientes as $dependiente) {
            $dependiente = trim($dependiente);
            $valida = $this->validacion->valida_data_modelo(name_modelo: $dependiente);
            if(errores::$error){
                return  $this->error->error(mensaje: "Error al validar modelo",data: $valida,
                    params: get_defined_vars());
            }
            if($this->registro_id<=0){
                return $this->error->error(mensaje:'Error $this->registro_id debe ser mayor a 0',
                    data:$this->registro_id, params: get_defined_vars());
            }
            $desactiva = $this->elimina_data_modelo(modelo_dependiente: $dependiente);
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al desactivar dependiente', data:$desactiva,
                    params: get_defined_vars());
            }
            $data[] = $desactiva;
        }
        return $data;
    }

    /**
     * P INT P ORDER
     * @param int $parent_id
     * @param modelo $model
     * @return array
     */
    private function elimina_dependientes(modelo $model, int $parent_id): array
    {
        $valida = $this->validacion->valida_name_clase(tabla: $this->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tabla',data: $valida, params: get_defined_vars());
        }
        if($parent_id<=0){
            return $this->error->error(mensaje:'Error $parent_id debe ser mayor a 0',data: $parent_id,
                params: get_defined_vars());
        }

        $dependientes = $this->data_dependientes(parent_id: $parent_id,tabla_children:  $model->tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener dependientes',data:$dependientes,
                params: get_defined_vars());
        }

        $key_dependiente_id = $model->tabla.'_id';

        $result = array();
        foreach($dependientes as $dependiente){
            $elimina_bd = $model->elimina_bd(id: $dependiente[$key_dependiente_id]);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al desactivar dependiente',data:$elimina_bd,
                    params: get_defined_vars());
            }
            $result[] = $elimina_bd;
        }
        return $result;

    }

    /**
     * PHPUNIT
     * @return string[]
     */
    public function elimina_todo(): array
    { //PRUEBA COMPLETA PROTEO
        $tabla = $this->tabla;
        $this->transaccion = 'DELETE';
        $this->consulta = /** @lang MYSQL */
            'DELETE FROM '.$tabla;

        $resultado = $this->ejecuta_sql($this->consulta);

        if(errores::$error){
            return $this->error->error('Error al ejecutar sql',$resultado);
        }

        return array('mensaje'=>'Registros eliminados con éxito');
    }

    /**
     * PHPUNIT
     * @return array
     */
    protected function estado_inicial():array{
        $filtro[$this->tabla.'.inicial'] ='activo';
        $r_estado = $this->filtro_and($filtro);
        if(errores::$error){
            return $this->error->error('Error al filtrar estado',$r_estado);
        }
        if((int)$r_estado['n_registros'] === 0){
            return $this->error->error('Error al no existe estado default',$r_estado);
        }
        if((int)$r_estado['n_registros'] > 1){
            return $this->error->error('Error existe mas de un estado',$r_estado);
        }
        return $r_estado['registros'][0];
    }

    /**
     * PHPUNIT
     * @return int|array
     */
    protected function estado_inicial_id(): int|array
    {
        $estado_inicial = $this->estado_inicial();
        if(errores::$error){
            return $this->error->error('Error al obtener estado',$estado_inicial);
        }
        return (int)$estado_inicial[$this->tabla.'_id'];
    }

    /**
     * P INT P ORDER ERROREV
     * @param array $filtro array('tabla.campo'=>'value'=>valor,'tabla.campo'=>'campo'=>tabla.campo);
     * @return array|bool
     */
    public function existe(array $filtro): array|bool
    {
        $resultado = $this->cuenta(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al contar registros',data: $resultado,
                params: get_defined_vars());
        }
        $existe = false;
        if((int)$resultado>0){
            $existe = true;
        }

        return $existe;

    }

    /**
     * PHPUNIT
     * Funcion para validar si existe un valor de un key de un array dentro de otro array
     * @param array $compare_1
     * @param array $compare_2
     * @param string $key
     * @return bool|array
     */
    private function existe_en_array(array $compare_1, array $compare_2, string $key): bool|array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error('Error $key no puede venir vacio', $key);
        }
        $existe = false;
        if(isset($compare_1[$key]) && isset($compare_2[$key])) {
            if ((string)$compare_1[$key] === (string)$compare_2[$key]) {
                $existe = true;
            }
        }
        return $existe;
    }

    /**
     * PHPUNIT
     * @param array $compare_1
     * @param array $compare_2
     * @param string $key
     * @return bool|array
     */
    protected function existe_registro_array(array $compare_1, array $compare_2, string $key): bool|array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error('Error $key no puede venir vacio', $key);
        }
        $existe = false;
        foreach($compare_1 as $data){
            if(!is_array($data)){
                return $this->error->error("Error data debe ser un array", $data);
            }
            $existe = $this->existe_en_array($data, $compare_2,$key);
            if(errores::$error){
                return $this->error->error("Error al comparar dato", $existe);
            }
            if($existe){
                break;
            }
        }
        return $existe;
    }

    /**
     * P INT P ORDER ERRORREV
     * Devuelve un array de la siguiente con la informacion de registros encontrados
     *
     *
     *
     * @param array $filtro array('tabla.campo'=>'value'=>valor,'tabla.campo'=>'campo'=>tabla.campo);
     * @param string $tipo_filtro string numeros o textos
     * @param array $filtro_especial
     *          arreglo con condiciones especiales $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x','comparacion'=>'AND OR')
     * @param array $order array('tabla.campo'=>'ASC');
     * @param int $limit numero de registros a mostrar, 0 = sin limite
     * @param int $offset numero de registros de comienzo de datos
     * @param array $group_by
     * @param array $columnas columnas a mostrar en la consulta, si columnas = array(), se muestran todas las columnas
     * @param array $filtro_rango
     *                  Opcion1.- $filtro_rango['tabla.campo'] = array('valor1'=>'valor','valor2'=>'valor')
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param array $filtro_extra
     * @param string $sql_extra
     * @param bool $aplica_seguridad
     * @param array $not_in
     * @param array $filtro_fecha
     * @return array
     * @example
     *      Ej 1
     *      $resultado = filtro_and();
     *      $resultado['registros'] = array $registro; //100% de los registros en una tabla
     *              $registro = array('tabla_campo'=>'valor','tabla_campo_n'=> 'valor_n');
     *      $resultado['n_registros'] = int count de todos los registros de una tabla
     *      $resultado['sql'] = string 'SELECT FROM modelo->tabla'
     *
     *      Ej 2
     *      $filtro = array();
     *      $tipo_filtro = 'numeros';
     *      $filtro_especial = array();
     *      $order = array();
     *      $limit = 0;
     *      $offset = 0;
     *      $group_by = array();
     *      $columnas = array();
     *      $filtro_rango['tabla.campo']['valor1'] = 1;
     *      $filtro_rango['tabla.campo']['valor2'] = 2;
     *
     *      $resultado = filtro_and($filtro,$tipo_filtro,$filtro_especial,$order,$limit,$offset,$group_by,$columnas,
     *                                  $filtro_rango);
     *
     *      $resultado['registros'] = array $registro; //registros encontrados como WHERE tabla.campo BETWEEN '1' AND '2'
     *              $registro = array('tabla_campo'=>'valor','tabla_campo_n'=> 'valor_n');
     *      $resultado['n_registros'] = int Total de registros encontrados
     *      $resultado['sql'] = string "SELECT FROM modelo->tabla WHERE tabla.campo BETWEEN '1' AND '2'"
     *
     *
     *      Ej 3
     *      $filtro = array();
     *      $tipo_filtro = 'numeros';
     *      $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x','comparacion'=>'OR')
     *      $order = array();
     *      $limit = 0;
     *      $offset = 0;
     *      $group_by = array();
     *      $columnas = array();
     *      $filtro_rango['tabla.campo']['valor1'] = 1;
     *      $filtro_rango['tabla.campo']['valor2'] = 2;
     *
     *      $resultado = filtro_and($filtro,$tipo_filtro,$filtro_especial,$order,$limit,$offset,$group_by,$columnas,
     *                                  $filtro_rango);
     *
     *      $resultado['registros'] = array $registro; //registros encontrados como WHERE tabla.campo BETWEEN '1' AND '2' OR (tabla.campo < 'x')
     *              $registro = array('tabla_campo'=>'valor','tabla_campo_n'=> 'valor_n');
     *      $resultado['n_registros'] = int Total de registros encontrados
     *      $resultado['sql'] = string "SELECT FROM modelo->tabla WHERE tabla.campo BETWEEN '1' AND '2' OR (tabla.campo < 'x')"
     *
     *
     *      Ej 4
     *      $filtro = array();
     *      $tipo_filtro = 'numeros';
     *      $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x','comparacion'=>'OR')
     *      $order = array();
     *      $limit = 0;
     *      $offset = 0;
     *      $group_by = array();
     *      $columnas = array();
     *      $filtro_rango = array()
     *
     *      $resultado = filtro_and($filtro,$tipo_filtro,$filtro_especial,$order,$limit,$offset,$group_by,$columnas,
     *                                  $filtro_rango);
     *
     *      $resultado['registros'] = array $registro; //registros encontrados como WHERE (tabla.campo < 'x')
     *              $registro = array('tabla_campo'=>'valor','tabla_campo_n'=> 'valor_n');
     *      $resultado['n_registros'] = int Total de registros encontrados
     *      $resultado['sql'] = string "SELECT FROM modelo->tabla WHERE (tabla.campo < 'x')"
     *
     *      Ej 5
     *
     *      $filtro['status_cliente.muestra_ajusta_monto_venta'] = 'activo';
     *      $filtro_especial[0]['cliente.monto_venta']['operador'] = '>';
     *      $filtro_especial[0]['cliente.monto_venta']['valor'] = '0.0';
     *      $r_cliente = $this->filtro_and($filtro,'numeros',$filtro_especial);
     *      $r_cliente['registros] = array con registros de tipo registro
     *      $resultado['sql'] = string "SELECT FROM cliente WHERE status_cliente.muestra_ajusta_monto_venta = 'activo' AND ( cliente.monto_venta>'0.0' )"
     *
     *      Ej 6
     *      $filtro_rango[$fecha]['valor1'] = 'periodo.fecha_inicio';
     *      $filtro_rango[$fecha]['valor2'] = 'periodo.fecha_fin';
     *      $filtro_rango[$fecha]['valor_campo'] = true;
     *      $r_periodo = $this->filtro_and(array(),'numeros',array(),array(),0,0,array(),array(),$filtro_rango);
     *
     * @internal  $this->genera_sentencia_base($tipo_filtro);
     * @internal  $this->filtro_especial_sql($filtro_especial);
     * @internal  $this->filtro_rango_sql($filtro_rango);
     * @internal  $this->filtro_extra_sql($filtro_extra);
     * @internal  $this->genera_consulta_base($columnas);
     * @internal  $this->order_sql($order);
     * @internal  $this->filtro_especial_final($filtro_especial_sql,$where);
     * @internal  $this->ejecuta_consulta($hijo);
     * @uses  todo el sistema
     */
    public function filtro_and(bool $aplica_seguridad = true, array $columnas =array(), array $filtro=array(),
                               array $filtro_especial= array(), array $filtro_extra = array(),
                               array $filtro_fecha = array(), array $filtro_rango = array(), array $group_by=array(),
                               array $hijo = array(), int $limit=0,  array $not_in = array(), int $offset=0,
                               array $order = array(), string $sql_extra = '',
                               string $tipo_filtro='numeros'): array|stdClass{

        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf,
                params: get_defined_vars());
        }

        if($this->aplica_seguridad && $aplica_seguridad) {
            $filtro = array_merge($filtro, $this->filtro_seguridad);
        }

        if($limit < 0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0  con 0 no aplica limit',
                data: $limit, params: get_defined_vars());
        }

        $sql = $this->genera_sql_filtro(columnas: $columnas, filtro:  $filtro, filtro_especial: $filtro_especial,
            filtro_extra:  $filtro_extra,filtro_rango:  $filtro_rango,
            group_by:  $group_by, limit:  $limit, not_in: $not_in, offset:  $offset, order: $order,
            sql_extra:  $sql_extra,tipo_filtro:  $tipo_filtro, filtro_fecha:  $filtro_fecha);

        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al maquetar sql',data:$sql, params: get_defined_vars());
        }

        $result = $this->ejecuta_consulta(consulta:$sql, hijo: $hijo);
        if(errores::$error){
            return  $this->error->error(mensaje:'Error al ejecutar sql',data:$result, params: get_defined_vars());
        }

        return $result;
    }


    /**
     * P INT P ORDER PROBADO ERRORREV
     * @param array $columnas
     * @param array $filtro
     * @param array $hijo
     * @return array|stdClass
     */
    public function filtro_or(array $columnas = array(), array $filtro = array(), array $hijo = array()):array|stdClass{

        $consulta = $this->genera_consulta_base(columnas: $columnas,extension_estructura:  $this->extension_estructura,
            renombradas:  $this->renombres);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $consulta, params: get_defined_vars());
        }
        $where = '';
        $sentencia = '';
        foreach($filtro as $campo=>$value){
            $data_sentencia = $this->data_sentencia(campo:  $campo,sentencia:  $sentencia,value:  $value, where: $where);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar data sentencia',data: $data_sentencia,
                    params: get_defined_vars());
            }
            $where = $data_sentencia->where;
            $sentencia = $data_sentencia->sentencia;
        }
        $consulta .= $where . $sentencia;

        $result = $this->ejecuta_consulta(consulta:$consulta, hijo: $hijo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: $result, params: get_defined_vars());
        }

        return $result;
    }



    /**
     * P INT P ORDER ERRORREV
     * @return array|stdClass
     */
    private function genera_data_log(): array|stdClass
    {
        $sql_data_alta = $this->sql_alta_full();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql ', data: $sql_data_alta,
                params: get_defined_vars());
        }

        $datas = $this->data_para_log();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data log', data: $datas, params: get_defined_vars());
        }

        $data_log = $this->data_log(alta_valido: $datas->alta_valido, campos:  $sql_data_alta->campos,
            update_valido:  $datas->update_valido,valores:  $sql_data_alta->valores);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar data log', data: $data_log,
                params: get_defined_vars());
        }

        return $data_log;
    }



    /**
     * P INT P ORDER ERROREV
     * @param array $columnas
     * @param array $group_by
     * @param array $order
     * @param int $limit
     * @param int $offset
     * @param string $tipo_filtro
     * @param array $filtro
     * @param array $filtro_especial
     * @param array $filtro_rango
     * @param array $filtro_extra
     * @param array $not_in
     * @param string $sql_extra
     * @param array $filtro_fecha
     * @return array|string
     */
    private function genera_sql_filtro(array $columnas, array $filtro, array $filtro_especial, array $filtro_extra,
                                       array $filtro_rango, array $group_by, int $limit, array $not_in, int $offset,
                                       array $order, string $sql_extra, string $tipo_filtro,
                                       array $filtro_fecha = array()): array|string
    {
        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf,
                params: get_defined_vars());
        }
        $consulta = $this->genera_consulta_base(columnas: $columnas,extension_estructura:  $this->extension_estructura,
            renombradas:  $this->renombres);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $consulta, params: get_defined_vars());
        }

        $complemento_sql = $this->complemento_sql(filtro:  $filtro, filtro_especial: $filtro_especial,
            filtro_extra: $filtro_extra, filtro_rango: $filtro_rango, group_by: $group_by, limit: $limit,
            not_in: $not_in, offset:  $offset,order:  $order, sql_extra: $sql_extra, tipo_filtro: $tipo_filtro,
            filtro_fecha:  $filtro_fecha);

        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al maquetar sql',data: $complemento_sql,
                params: get_defined_vars());
        }

        $sql = $this->consulta_full_and(complemento:  $complemento_sql, consulta: $consulta);
        if(errores::$error){
            return  $this->error->error(mensaje:'Error al maquetar sql',data: $sql, params: get_defined_vars());
        }

        $this->consulta = $sql;

        return $sql;
    }




    /**
     * P ORDER P INT PROBADO ERROREV
     * @param array $group_by Es un array con la forma array(0=>'tabla.campo', (int)N=>(string)'tabla.campo')
     * @return string|array
     */
    private function group_by_sql(array $group_by): string|array
    {
        $group_by_sql = '';
        foreach ($group_by as $campo){
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error el campo no puede venir vacio', data: $group_by,
                    params: get_defined_vars());
            }
            if(is_numeric($campo)){
                return $this->error->error(mensaje:'Error el campo debe ser un texto', data: $campo,
                    params: get_defined_vars());
            }
            if($group_by_sql === ''){
                $group_by_sql.=' GROUP BY '.$campo.' ';
            }
            else {
                $group_by_sql .= ',' . $campo.' ';
            }
        }
        return $group_by_sql;
    }




    /**
     * P INT P ORDER ERROREV
     * @param stdClass $data_log
     * @return array|stdClass
     */
    private function inserta_sql(stdClass $data_log): array|stdClass
    {
        $keys = array('campos','valores');
        foreach($keys as $key){
            if(!isset($data_log->$key)){
                return $this->error->error(mensaje: 'Error no existe data_log->'.$key, data: $data_log,
                    params: get_defined_vars());
            }
        }
        foreach($keys as $key){
            if(trim($data_log->$key) === ''){
                return $this->error->error(mensaje:'Error esta vacio data_log->'.$key, data: $data_log,
                    params: get_defined_vars());
            }
        }

        $this->transaccion = 'INSERT';

        $sql = $this->sql_alta(campos: $data_log->campos, valores: $data_log->valores);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar sql',data:  $sql, params: get_defined_vars());
        }

        $resultado = $this->ejecuta_sql(consulta: $sql);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql',data:  $resultado,
                params: get_defined_vars());
        }
        return $resultado;
    }

    /**
     * PHPUNIT
     * @param float $sub_total
     * @return float
     */
    protected function iva(float $sub_total): float
    {
        $iva = $sub_total * .16;
        return  round($iva,2);
    }

    /**
     * P ORDER P INT PROBADO ERRORREV
     * @param int $limit
     * @return string|array
     */
    private function limit_sql(int $limit): string|array
    {
        if($limit<0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0', data: $limit,
                params: get_defined_vars());
        }
        $limit_sql = '';
        if($limit > 0){
            $limit_sql.=' LIMIT '.$limit;
        }
        return $limit_sql;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $registro
     * @param int $id
     * @return array
     */
    public function limpia_campos_registro(array $registro, int $id): array
    {
        $data_upd = array();
        foreach ($registro as $campo){
            $data_upd[$campo] = '';
        }
        $r_modifica = $this->modifica_bd($data_upd, $id);
        if(errores::$error){
            return $this->error->error("Error al modificar", $r_modifica);
        }
        $registro = $this->registro($id);
        if(errores::$error){
            return $this->error->error("Error al obtener registro", $registro);
        }
        return $registro;

    }




    /**
     * PHPUNIT
     * Modifica los datos de un registro de un modelo
     * @param array $registro registro con datos a modificar
     * @param int $id id del registro a modificar
     * @param bool $reactiva para evitar validacion de status inactivos
     * @return array resultado de la insercion
     *@throws errores Si $limit < 0
     * @throws errores $this->registro_upd vacio
     * @example
     *      $r_modifica_bd =  parent::modifica_bd($registro, $id, $reactiva);
     * @internal  $this->validacion->valida_transaccion_activa($this, $this->aplica_transaccion_inactivo, $this->registro_id, $this->tabla);
     * @internal  $this->genera_campos_update();
     * @internal  $this->agrega_usuario_session();
     * @internal  $this->ejecuta_sql();
     * @internal  $this->bitacora($this->registro_upd,__FUNCTION__, $consulta);
     * @uses  todo el sistema
     */
    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array|stdClass
    {
        $this->registro_upd = $registro;
        $this->registro_id = $id;
        if($id <=0){
            return $this->error->error('Error el id debe ser mayor a 0',$id);
        }
        if(count($this->registro_upd) === 0){
            return $this->error->error('El registro no puede venir vacio',$this->registro_upd);
        }
        if(!$reactiva) {
            $valida = $this->validacion->valida_transaccion_activa(
                aplica_transaccion_inactivo: $this->aplica_transaccion_inactivo,
                registro:  $registro, registro_id: $this->registro_id,tabla:  $this->tabla);
            if (errores::$error) {
                return $this->error->error('Error al validar transaccion activa',$valida);
            }
        }
        $campos_sql = $this->genera_campos_update();
        if(errores::$error){
            return $this->error->error('Error al obtener campos',$campos_sql);
        }
        $this->campos_sql = $campos_sql;
        $campos_sql = $this->agrega_usuario_session();
        if(errores::$error){
            return $this->error->error('Error al AGREGAR USER',$campos_sql);
        }
        $this->campos_sql .= ','.$campos_sql;
        $this->consulta = 'UPDATE '. $this->tabla.' SET '.$this->campos_sql."  WHERE id = $id";
        $consulta = $this->consulta;

        $this->transaccion = 'UPDATE';
        $this->registro_id = $id;

        $resultado = $this->ejecuta_sql($this->consulta);

        if(errores::$error){
            return $this->error->error('Error al ejecutar sql',array($resultado,'sql'=>$this->consulta));
        }

        $bitacora = $this->bitacora(consulta: $consulta, funcion: __FUNCTION__, registro: $this->registro_upd);
        if(errores::$error){
            return $this->error->error('Error al insertar bitacora',$bitacora);
        }


        return $resultado;
    }

    /**
     * PHPUNIT
     * @param array $filtro
     * @param array $registro
     * @return string[]
     */
    public function modifica_con_filtro_and(array $filtro, array $registro): array
    {
        $this->registro_upd = $registro;
        if(count($this->registro_upd) === 0){
            return $this->error->error('El registro no puede venir vacio',$this->registro_upd);
        }
        if(count($filtro) === 0){
            return $this->error->error('El filtro no puede venir vacio',$filtro);
        }

        $r_data = $this->filtro_and($filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener registros',$r_data);
        }

        $data = array();
        foreach ($r_data['registros'] as $row){
            $upd = $this->modifica_bd($registro, $row[$this->tabla.'_id']);
            if(errores::$error){
                return $this->error->error('Error al modificar registro',$upd);
            }
            $data[] = $upd;
        }

        return array('mensaje'=>'Registros modificados con exito',$data);

    }

    /**
     * PHPUNIT
     * @param array $registro
     * @param int $id
     * @return array
     */
    public function modifica_por_id(array $registro,int $id): array
    {
        $r_modifica = $this->modifica_bd($registro, $id);
        if(errores::$error){
            return $this->error->error("Error al modificar", $r_modifica);
        }
        return $r_modifica;

    }



    /**
     * P INT P ORDER ERRORREV PROBADO
     * Devuelve un array con el registro buscado por this->registro_id del modelo
     * @param array $columnas columnas a mostrar en la consulta, si columnas = array(), se muestran todas las columnas
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param array $extension_estructura arreglo con la extension de una estructra para obtener datos de foraneas a configuracion
     * @example
     *      $salida_producto_id = $_GET['salida_producto_id'];
    $salida_producto_modelo = new salida_producto($this->link);
    $salida_producto_modelo->registro_id = $salida_producto_id;
    $salida_producto = $salida_producto_modelo->obten_data();
     *
     * @return array con datos del registro encontrado
     * @throws errores $this->registro_id < 0
     * @throws errores no se encontro registro
     * @internal  $this->obten_por_id($hijo, $columnas);
     * @uses  todo el sistema
     */
    public function obten_data(array $columnas = array(), array $extension_estructura = array(), array $hijo= array()): array{
        $this->row = new stdClass();
        if($this->registro_id < 0){
            return  $this->error->error(mensaje: 'Error el id debe ser mayor a 0 en el modelo '.$this->tabla,
                data: $this->registro_id, params: get_defined_vars());
        }
        if(count($extension_estructura) === 0){
            $extension_estructura = $this->extension_estructura;
        }
        $resultado = $this->obten_por_id(columnas:  $columnas, extension_estructura: $extension_estructura, hijo: $hijo);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener por id en '.$this->tabla, data: $resultado,
                params: get_defined_vars());
        }
        if((int)$resultado->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existe registro de '.$this->tabla,data:  $resultado,
                params: get_defined_vars());
        }
        foreach($resultado->registros[0] as $campo=>$value){
            $this->row->$campo = $value;
        }
        return $resultado->registros[0];
    }

    /**
     * PHPUNIT
     * Devuelve un array con los datos del ultimo registro
     * @param array $filtro filtro a aplicar en sql
     * @param bool $aplica_seguridad
     * @return array con datos del registro encontrado o registro vacio
     * @example
     *      $filtro['prospecto.aplica_ruleta'] = 'activo';
     * $resultado = $this->obten_datos_ultimo_registro($filtro);
     *
     * @internal  $this->filtro_and($filtro,'numeros',array(),$this->order,1);
     * @uses  prospecto->obten_ultimo_cerrador_id
     */
    protected function obten_datos_ultimo_registro(array $filtro = array(), bool $aplica_seguridad = true): array
    { //fin
        if($this->tabla === ''){
            return $this->error->error('Error tabla no puede venir vacia',$this->tabla);
        }
        $this->order = array($this->tabla.'.id'=>'DESC');
        $this->limit = 1;

        $resultado = $this->filtro_and(filtro: $filtro,order: $this->order,limit: 1,
            aplica_seguridad: $aplica_seguridad);
        if(errores::$error){
            return $this->error->error('Error al obtener datos',$resultado);
        }
        if((int)$resultado['n_registros'] === 0){
            return array();
        }
        return $resultado['registros'][0];

    }

    /**
     * P INT P ORDER PROBADO ERRORREV
     * Devuelve un array con un elemento declarado por $this->>registro_id
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param array $columnas columnas a mostrar en la consulta, si columnas = array(), se muestran todas las columnas
     * @param array $extension_estructura arreglo con la extension de una estructra para obtener datos de foraneas a configuracion
     * @return array|stdClass con datos del registro encontrado o registro vacio
     * @example
     *      if($this->registro_id < 0){
     * return $this->error->error('Error el id debe ser mayor a 0',
     * __LINE__,__FILE__,$this->registro_id);
     * }
     * $resultado = $this->obten_por_id($hijo, $columnas);
     *
     * @internal  $this->genera_consulta_base($columnas);
     * @internal  $this->ejecuta_consulta($hijo);
     * @uses  modelo
     * @uses  operacion_controladores
     */
    private function obten_por_id(array $columnas = array(), array $extension_estructura= array(),
                                  array $hijo = array()):array|stdClass{
        if($this->registro_id < 0){
            return  $this->error->error(mensaje: 'Error el id debe ser mayor a 0',data: $this->registro_id,
                params: get_defined_vars());
        }
        if(count($extension_estructura)===0){
            $extension_estructura = $this->extension_estructura;
        }
        $tabla = $this->tabla;

        $consulta = $this->genera_consulta_base(columnas: $columnas,extension_estructura: $extension_estructura,
            renombradas:  $this->renombres);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar consulta base',data:  $consulta,
                params: get_defined_vars());
        }

        $where = " WHERE $tabla".".id = $this->registro_id ";
        $consulta .= $where;

        $result = $this->ejecuta_consulta(consulta: $consulta, hijo: $hijo);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result, params: get_defined_vars());
        }
        return $result;
    }

    /**
     * P INT
     * Obtiene todos los registros de un modelo
     * @param string $sql_extra
     * @param string $group_by
     * @param array $columnas
     * @param bool $aplica_seguridad
     * @param int $limit
     * @return array|stdClass conjunto de registros obtenidos
     * @example
     *      $es_referido = $controlador->directiva->checkbox(4,'inactivo','Es Referido',true,'es_referido');
     *
     * @uses  TODO EL SISTEMA
     */
    public function obten_registros(
        string $sql_extra='', string $group_by = '', array $columnas = array(),
        bool $aplica_seguridad = false, int $limit = 0): array|stdClass{

        if($group_by !== ''){
            $group_by =" GROUP BY $group_by ";
        }


        $limit_sql = '';

        if($this->limit > 0){
            $limit_sql =" LIMIT $this->limit ";
        }
        else{
            if($limit > 0){
                $limit_sql =" LIMIT $limit ";
            }
        }

        $offset_sql = '';
        if($this->offset > 0){
            $offset_sql =" OFFSET $this->offset ";
        }

        $order_sql = $this->order_sql(order: $this->order);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar order', data: $order_sql, params: get_defined_vars());
        }


        $seguridad = '';
        if($aplica_seguridad){
            $where = '';
            if($sql_extra ===''){
                $where = ' WHERE ';
            }

            $sq_seg = $this->columnas_extra['usuario_permitido_id'];
            $seguridad = " $where ($sq_seg) = $_SESSION[usuario_id] ";
        }

        $consulta_base = $this->genera_consulta_base(columnas: $columnas,
            extension_estructura: $this->extension_estructura, renombradas: $this->renombres);

        if(errores::$error){
            return $this->error->error('Error al generar consulta', $consulta_base);
        }

        $consulta = $consulta_base.' '.$sql_extra.' '.$seguridad.' '.$group_by.' '.$order_sql.' '.$limit_sql.' '.$offset_sql;

        $this->transaccion = 'SELECT';
        $result = $this->ejecuta_consulta(consulta: $consulta);
        if(errores::$error){
            return $this->error->error('Error al ejecutar consulta', $result);
        }
        $this->transaccion = '';

        return $result;
    }

    /**
     * PHPUNIT
     * Devuelve un conjunto de registros con status igual a activo
     * @param array $order array para ordenar el resultado
     * @param array $filtro filtro para generar AND en el resultado
     * @param array $hijo parametros para la asignacion de registros de tipo hijo del modelo en ejecucion
     * @return array|stdClass conjunto de registros
     * @example
     *      $resultado = $modelo->obten_registros_activos(array(),array());
     * @example
     *      $resultado = $modelo->obten_registros_activos(array(), $filtro);
     * @example
     *      $r_producto = $this->obten_registros_activos();
     *
     * @uses clientes->obten_registros_vista_base
     * @uses directivas->obten_registros_select
     * @uses $directivas->obten_registros_select
     * @uses controlador_grupo->obten_registros_select
     * @uses controlador_grupo->asigna_accion
     * @uses controlador_seccion_menu->alta_bd
     * @uses controlador_session->login
     * @uses controlador_session->login
     * @uses controlador_ubicacion->ve_imagenes
     * @uses producto->obten_productos
     * @uses prospecto->obten_siguiente_cerrador_id
     * @internal $this->genera_consulta_base()
     * @internal $this->genera_and()
     * @internal $this->ejecuta_consulta()
     */
    public function obten_registros_activos(
        array $order = array(), array $filtro= array(), array $hijo = array()):array|stdClass{

        $filtro[$this->tabla.'.status'] = 'activo';
        $r_data = $this->filtro_and(filtro: $filtro,order: $order,hijo: $hijo);
        if(errores::$error){
            return $this->error->error("Error al filtrar", $r_data);
        }

        return $r_data;
    }

    /**
     * P ORDER P INT PORBADO ERRORREV
     * Devuelve un conjunto de registros ordenados con filtro
     * @param array $filtros filtros para generar AND en el resultado
     * @param string $campo campo de orden
     * @param string $orden metodo ordenamiento ASC DESC
     * @return array|stdClass conjunto de registros
     * @example
     *  $filtro = array('elemento_lista.status'=>'activo','seccion_menu.descripcion'=>$seccion,'elemento_lista.encabezado'=>'activo');
     * $resultado = $elemento_lista_modelo->obten_registros_filtro_and_ordenado($filtro,'elemento_lista.orden','ASC');
     *
     * @uses directivas
     * @uses templates
     * @uses consultas_base
     * @internal  $this->genera_and();
     * @internal this->genera_consulta_base();
     * @internal $this->ejecuta_consulta();
     */
    public function obten_registros_filtro_and_ordenado(string $campo, array $filtros, string $orden):array|stdClass{
        $this->filtro = $filtros;
        if(count($this->filtro) === 0){
            return $this->error->error(mensaje: 'Error los filtros no pueden venir vacios',data: $this->filtro,
                params: get_defined_vars());
        }
        if($campo === ''){
            return $this->error->error(mensaje:'Error campo no pueden venir vacios',data:$this->filtro,
                params: get_defined_vars());
        }

        $sentencia = (new where())->genera_and(columnas_extra: $this->columnas_extra, filtro: $filtros);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar and',data:$sentencia,
                params: get_defined_vars());
        }
        $consulta = $this->genera_consulta_base(columnas: array(),extension_estructura: $this->extension_estructura,
            renombradas:  $this->renombres);

        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar consulta',data:$consulta,
                params: get_defined_vars());
        }

        $where = " WHERE $sentencia";
        $order_by = " ORDER BY $campo $orden";
        $consulta .= $where . $order_by;

        $result = $this->ejecuta_consulta(consulta: $consulta);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql',data:$result,
                params: get_defined_vars());
        }

        return $result;
    }

    /**
     * PHPUNIT
     * @return array|int
     */
    public function obten_ultimo_registro(): int|array
    {//PRUEBA COMPLETA PROTEO
        $this->order = array($this->tabla.'.id'=>'DESC');
        $this->limit = 1;
        $resultado = $this->obten_registros();
        if(isset($resultado['error'])){
            return $this->error->error('Error al obtener registros',$resultado);
        }

        if((int)$resultado['n_registros'] === 0){
            return 1;
        }

        return $resultado['registros'][0][$this->tabla.'_id'] + 1;
    }

    /**
     * P ORDER P INT PROBADO ERRORREV
     * @param int $offset
     * @return string|array
     */
    private function offset_sql(int $offset): string|array
    {
        if($offset<0){
            return $this->error->error(mensaje: 'Error $offset debe ser mayor o igual a 0',data: $offset,
                params: get_defined_vars());

        }
        $offset_sql = '';
        if($offset >0){
            $offset_sql.=' OFFSET '.$offset;
        }
        return $offset_sql;
    }

    /**
     * P ORDER P INT PROBADO ERRORREV
     * @param array $group_by
     * @param array $order
     * @param int $limit
     * @param int $offset
     * @return array|stdClass
     */
    private function params_sql(array $group_by, int $limit,  int $offset, array $order): array|stdClass
    {
        if($limit<0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0',data:  $limit,
                params: get_defined_vars());
        }
        if($offset<0){
            return $this->error->error(mensaje: 'Error $offset debe ser mayor o igual a 0',data: $offset,
                params: get_defined_vars());

        }

        $group_by_sql = $this->group_by_sql(group_by: $group_by);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data:$group_by_sql, params: get_defined_vars());
        }

        $order_sql = $this->order_sql(order: $order);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar order',data:$order_sql, params: get_defined_vars());
        }

        $limit_sql = $this->limit_sql(limit: $limit);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar limit',data:$limit_sql, params: get_defined_vars());
        }

        $offset_sql = $this->offset_sql(offset: $offset);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar offset',data:$offset_sql, params: get_defined_vars());
        }

        $params = new stdClass();
        $params->group_by = $group_by_sql;
        $params->order = $order_sql;
        $params->limit = $limit_sql;
        $params->offset = $offset_sql;

        return $params;

    }



    /**
     * P INT P ORDER ERRORREV
     * Funcion que regresa en forma de array un registro de una estructura de datos del registro_id unico de dicha
     * estructura
     * @param int $registro_id $id Identificador del registro
     * @param array $columnas columnas a obtener del registro
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param array $extension_estructura arreglo con la extension de una estructura para obtener datos de foraneas a configuracion
     * @return array
     */
    public function registro(int $registro_id, array $columnas = array(), array $extension_estructura = array(),
                             array $hijo = array()):array{
        if($registro_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $registro_id debe ser mayor a 0',
                data: $registro_id, params: get_defined_vars());
        }
        $this->registro_id = $registro_id;
        $registro = $this->obten_data(columnas: $columnas, extension_estructura: $extension_estructura, hijo: $hijo);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener registro',data: $registro,
                params: get_defined_vars());
        }

        return $registro;
    }

    /**
     * PHPUNIT
     * @param array $columnas
     * @param bool $aplica_seguridad
     * @param int $limit
     * @param array $order
     * @return array
     */
    public function registros(array $columnas = array(), bool $aplica_seguridad = false, int $limit = 0, array $order = array()):array{

        $this->order = $order;
        $resultado =$this->obten_registros('','', $columnas, $aplica_seguridad,$limit);

        if(errores::$error){
            return $this->error->error('Error al obtener registros activos',$resultado);
        }
        $this->registros = $resultado['registros'];
        return $this->registros;
    }

    /**
     * PHPUNIT
     * @param array $columnas
     * @param bool $aplica_seguridad
     * @param int $limit
     * @return array
     */
    public function registros_activos(array $columnas = array(), bool $aplica_seguridad = false, int $limit = 0): array
    {
        $filtro[$this->tabla.'.status'] = 'activo';
        $resultado =$this->filtro_and(filtro: $filtro,limit: $limit,columnas: $columnas,aplica_seguridad: $aplica_seguridad);

        if(errores::$error){
            return $this->error->error('Error al obtener registros',$resultado);
        }
        $this->registros = $resultado['registros'];
        return $this->registros;
    }

    /**
     * PHPUNIT
     * @param array $columnas
     * @return array
     */
    public function registros_permitidos(array $columnas = array()): array
    {
        $registros = $this->registros($columnas, $this->aplica_seguridad);
        if(errores::$error) {
            return $this->error->error('Error al obtener registros', $registros);
        }

        return $registros;
    }

    /**
     * PROBADO P ORDER P INT
     * @param string $seccion
     * @return array|int
     */
    protected function seccion_menu_id(string $seccion):array|int{
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error('Error seccion no puede venir vacio',$seccion);
        }
        $filtro['seccion.descripcion'] = $seccion;
        $modelo_sm = $this->genera_modelo(modelo: 'seccion');
        if(errores::$error){
            return $this->error->error('Error al generar modelo',$modelo_sm);
        }
        $r_seccion_menu = $modelo_sm->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener seccion menu',$r_seccion_menu);
        }
        if((int)$r_seccion_menu->n_registros === 0){
            return $this->error->error('Error al obtener seccion menu no existe',$r_seccion_menu);
        }

        $registros = $r_seccion_menu->registros[0];
        $seccion_menu_id = $registros['seccion_id'];
        return (int)$seccion_menu_id;
    }

    /**
     *  P ORDER P INT PROBADO ERRORREV
     * @param string $sentencia
     * @param string $campo
     * @param string $value
     * @return string|array
     */
    private function sentencia_or(string $campo,  string $sentencia, string $value): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio',data: $campo, params: get_defined_vars());
        }
        $or = '';
        if($sentencia !== ''){
            $or = ' OR ';
        }
        $sentencia.=" $or $campo = '$value'";
        return $sentencia;
    }

    /**
     * P INT P ORDER ERROREV
     * @param string $campo
     * @param mixed $value
     * @return array|stdClass
     */
    public function slaches_campo(string $campo, mixed $value): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo no puede venir vacio',data:  $campo,
                params: get_defined_vars());
        }
        $campo = addslashes($campo);
        try {
            $value = addslashes($value);
        }
        catch (Throwable  $e){
            return $this->error->error(mensaje: 'Error al asignar value de campo '.$campo, data: $e,
                params: get_defined_vars());
        }
        $data = new stdClass();
        $data->campo = $campo;
        $data->value = $value;
        return $data;
    }

    /**
     * P ORDER P INT ERROREV
     * @param string $campos
     * @param string $valores
     * @return string|array
     */
    private function sql_alta(string $campos, string $valores): string|array
    {
        $this->tabla = trim($this->tabla);
        if($this->tabla === ''){
            return $this->error->error(mensaje: 'Error $this tabla no puede venir vacio',data:  $this->tabla ,
                params: get_defined_vars());
        }
        if($campos === ''){
            return $this->error->error(mensaje:'Error campos esta vacio', data:$campos, params: get_defined_vars() );
        }
        if($valores === ''){
            return $this->error->error(mensaje:'Error valores esta vacio',data: $valores, params: get_defined_vars() );
        }
        $this->consulta = /** @lang MYSQL */
            'INSERT INTO '. $this->tabla.' ('.$campos.') VALUES ('.$valores.')';

        return $this->consulta;
    }

    /**
     * P INT P ORDER ERROREV
     * @return array|stdClass
     */
    private function sql_alta_full(): array|stdClass
    {
        $campos = '';
        $valores = '';
        foreach ($this->registro as $campo => $value) {
            $sql_base = $this->sql_base_alta(campo: $campo, campos:  $campos, valores:  $valores, value:  $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar sql ',data:  $sql_base, params: get_defined_vars());
            }
            $campos = $sql_base->campos;
            $valores = $sql_base->valores;
        }

        $datas = new stdClass();
        $datas->campos = $campos;
        $datas->valores = $valores;
        return $datas;
    }

    /**
     * P INT P ORDER ERRORREV
     * @param string $campo
     * @param mixed $value
     * @param string $campos
     * @param string $valores
     * @return array|stdClass
     */
    private function sql_base_alta(string $campo, string $campos, string $valores, mixed $value): array|stdClass
    {
        if(is_numeric($campo)){
            return $this->error->error(mensaje: 'Error el campo no es valido',data:  $campo, params: get_defined_vars());
        }

        $slacheados = $this->slaches_campo(campo: $campo,value:  $value);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ajustar campo ', data:$slacheados, params: get_defined_vars());
        }

        $campos = $this->campos_alta_sql(campo:  $slacheados->campo, campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar campo ', data:$campos, params: get_defined_vars());
        }

        $valores = $this->valores_sql_alta(valores: $valores,value:  $slacheados->value,);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar valor ',data: $valores, params: get_defined_vars());
        }
        $data = new stdClass();
        $data->campos = $campos;
        $data->valores = $valores;
        return $data;
    }



    /**
     * PRUEBAS FINALIZADAS
     * @param array $campos [alias=>campo] alias = string no numerico campo string campo de la base de datos
     * @param array $filtro
     * @return array con la suma de los elementos seleccionados y filtrados
     */
    public function suma(array $campos, array $filtro = array()): array
    {

        $this->filtro = $filtro;
        if(count($campos)===0){
            return $this->error->error('Error campos no puede venir vacio',$campos);
        }

        $columnas = $this->columnas_suma($campos);
        if(errores::$error){
            return $this->error->error('Error al agregar columnas',$columnas);
        }

        $filtro_sql = $this->genera_and($filtro);
        if(errores::$error){
            return $this->error->error('Error al generar filtro',$filtro_sql);
        }

        $where = (new where())->where_suma($filtro_sql);
        if(errores::$error){
            return $this->error->error('Error al generar where',$where);
        }

        $tabla = $this->tabla;
        $tablas = (new joins())->obten_tablas_completas(columnas_join:  $this->columnas, tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener tablas',$tablas);
        }

        $consulta = 'SELECT '.$columnas.' FROM '.$tablas.$where;

        $resultado = $this->ejecuta_consulta(consulta: $consulta);
        if(errores::$error){
            return $this->error->error('Error al ejecutar sql',$resultado);
        }

        return $resultado['registros'][0];
    }

    /**
     * P INT
     * @param string $consulta
     * @return array|stdClass
     */
    private function transacciones_default(string $consulta): array|stdClass
    {
        if($this->registro_id<=0){
            return $this->error->error(mensaje: 'Error this->registro_id debe ser mayor a 0', data: $this->registro_id,
                params: get_defined_vars());
        }

        $bitacora = $this->bitacora(registro: $this->registro,funcion: __FUNCTION__,consulta: $consulta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar bitacora',data:  $bitacora,
                params: get_defined_vars());
        }

        $r_ins = $this->ejecuta_insersion_attr(registro_id: $this->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al insertar atributos', data: $r_ins,
                params: get_defined_vars());
        }

        $data_session = $this->data_session_alta();
        if(errores::$error){
            return $this->error->error(mensaje:'Error al asignar dato de SESSION', data: $data_session,
                params: get_defined_vars());
        }

        $datos = new stdClass();
        $datos->bitacora = $bitacora;
        $datos->attr = $r_ins;
        $datos->session = $data_session;
        return $datos;
    }







    /**
     * PHPUNIT
     * @return array
     */
    public function ultimo_registro(): array
    {
        $this->order = array($this->tabla.'.id'=>'DESC');
        $this->limit = 1;
        $resultado = $this->obten_registros();
        if(errores::$error){
            return $this->error->error('Error al obtener registros',$resultado);
        }

        if((int)$resultado['n_registros'] === 0){
            return array();
        }

        return $resultado['registros'][0];
    }

    /**
     * PHPUNIT
     * @return array|int
     */
    public function ultimo_registro_id(): int|array
    {
        $this->order = array($this->tabla.'.id'=>'DESC');
        $this->limit = 1;
        $resultado = $this->obten_registros();
        if(isset($resultado['error'])){
            return $this->error->error('Error al obtener registros',$resultado);
        }

        if((int)$resultado['n_registros'] === 0){
            return 0;
        }
        return (int)$resultado['registros'][0][$this->tabla.'_id'];
    }

    /**
     * PHPUNIT
     * @param int $n_registros
     * @return array
     */
    protected function ultimos_registros(int $n_registros): array
    {
        $this->order = array($this->tabla.'.id'=>'DESC');
        $this->limit = $n_registros;
        $resultado = $this->obten_registros();
        if(errores::$error){
            return $this->error->error('Error al obtener registros',$resultado);
        }
        if((int)$resultado['n_registros'] === 0){
            $resultado['registros'] = array();
        }
        return $resultado['registros'];
    }







    /**
     * P INT P ORDER ERROREV
     * @param string $valores
     * @param string $value
     * @return string|array
     */
    private function valores_sql_alta(string $valores, string $value): string|array
    {
        $valores .= $valores === '' ? "'$value'" : ",'$value'";
        return $valores;
    }




}

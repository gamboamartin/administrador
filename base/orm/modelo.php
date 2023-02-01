<?php
namespace base\orm;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\plugins\files;
use JsonException;
use PDO;
use stdClass;

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
    protected array $campos_encriptados;
    public array $campos_no_upd = array();
    public array $parents = array();
    public bool $valida_user = true;


    /**
     *
     * @param PDO $link Conexion a la BD
     * @param string $tabla
     * @param bool $aplica_bitacora
     * @param bool $aplica_seguridad
     * @param bool $aplica_transaccion_inactivo
     * @param array $campos_encriptados
     * @param array $campos_obligatorios
     * @param array $columnas
     * @param array $campos_view
     * @param array $columnas_extra
     * @param array $extension_estructura
     * @param array $no_duplicados
     * @param array $renombres
     * @param array $sub_querys
     * @param array $tipo_campos
     * @param bool $validation
     * @param array $campos_no_upd Conjunto de campos no modificables, por default id
     * @param array $parents
     * @param bool $temp
     * @param array $childrens
     * @param array $defaults
     * @param array $parents_data
     * @param array $atributos_criticos
     */
    public function __construct(PDO $link, string $tabla, bool $aplica_bitacora = false, bool $aplica_seguridad = false,
                                bool $aplica_transaccion_inactivo = true, array $campos_encriptados = array(),
                                array $campos_obligatorios= array(), array $columnas = array(),
                                array $campos_view= array(), array $columnas_extra = array(),
                                array $extension_estructura = array(), array $no_duplicados = array(),
                                array $renombres = array(), array $sub_querys = array(), array $tipo_campos = array(),
                                bool $validation = false,array $campos_no_upd = array(), array $parents = array(),
                                bool $temp = false, array $childrens = array(), array $defaults = array(),
                                array $parents_data = array(), array $atributos_criticos = array()){



        /**
         * REFCATORIZAR
         */


        $tabla = str_replace('models\\','',$tabla);
        parent::__construct(link:$link, defaults: $defaults, parents_data: $parents_data, temp: $temp);

        $this->temp = $temp;
        $this->tabla = $tabla;
        $this->columnas_extra = $columnas_extra;
        $this->columnas = $columnas;
        $this->aplica_bitacora = $aplica_bitacora;
        $this->aplica_seguridad = $aplica_seguridad;
        $this->extension_estructura = $extension_estructura;
        $this->renombres = $renombres;
        $this->validation = $validation;
        $this->no_duplicados = $no_duplicados;
        $this->campos_encriptados = $campos_encriptados;
        $this->campos_no_upd = $campos_no_upd;
        $this->childrens = $childrens;
        $this->atributos_criticos = $atributos_criticos;


        $attrs = (new inicializacion())->integra_attrs(modelo: $this);
        if (errores::$error) {
            $error = $this->error->error(mensaje: 'Error al obtener attr '.$tabla, data: $attrs);
            print_r($error);
            die('Error');
        }

        /**
         * REFACTORIZAR
         */
        foreach ($atributos_criticos as $atributo_critico){
            $existe_atributo_critico = false;
            foreach ($this->atributos as $key_attr=>$atributo){

                if($key_attr === $atributo_critico){
                    $existe_atributo_critico = true;
                    break;
                }
            }
            if(!$existe_atributo_critico){
                $error = $this->error->error(mensaje: 'Error no existe en db el  atributo '.$atributo_critico.
                    ' del modelo '.$this->tabla, data: $this->atributos);
                print_r($error);
                die('Error');
            }

        }


        if(!in_array('id', $this->campos_no_upd, true)){
            $this->campos_no_upd[] = 'id';
        }

        if(isset($_SESSION['usuario_id'])){
            $this->usuario_id = (int)$_SESSION['usuario_id'];
        }


        $campos_tabla = (new columnas())->campos_tabla(modelo:$this, tabla: $tabla);
        if (errores::$error) {
            $error = $this->error->error(mensaje: 'Error al obtener campos tabla '.$tabla, data: $campos_tabla);
            print_r($error);
            die('Error');
        }
        $this->campos_tabla = $campos_tabla;


        $campos_obligatorios = (new columnas())->integra_campos_obligatorios(
            campos_obligatorios: $campos_obligatorios, campos_tabla: $this->campos_tabla);
        if (errores::$error) {
            $error = $this->error->error(mensaje: 'Error al integrar campos obligatorios '.$tabla, data: $campos_obligatorios);
            print_r($error);
            die('Error');
        }
        $this->campos_obligatorios = $campos_obligatorios;



        $this->sub_querys = $sub_querys;
        $this->sql_seguridad_por_ubicacion = array();


        $limpia = $this->campos_obligatorios(campos_obligatorios: $campos_obligatorios);
        if (errores::$error) {
            $error = $this->error->error(mensaje: 'Error al asignar campos obligatorios en '.$tabla, data: $limpia);
            print_r($error);
            die('Error');
        }


        $this->campos_view = array_merge($this->campos_view,$campos_view);
        $this->tipo_campos = $tipo_campos;

        $this->aplica_transaccion_inactivo = $aplica_transaccion_inactivo;


        $aplica_seguridad_filter = (new seguridad_dada())->aplica_filtro_seguridad(modelo: $this);
        if (errores::$error) {
            $error = $this->error->error( mensaje: 'Error al obtener filtro de seguridad', data: $aplica_seguridad_filter);
            print_r($error);
            die('Error');
        }


        $this->key_id = $this->tabla.'_id';
        $this->key_filtro_id = $this->tabla.'.id';
    }


    /**
     * Activa un elemento
     * @param bool $reactiva Si reactiva valida si el registro se puede reactivar
     * @param int $registro_id
     * @return array|stdClass
     * @version 1.495.49
     * @final revisada
     */
    public function activa_bd(bool $reactiva = false, int $registro_id = -1): array|stdClass{

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        if($registro_id>0){
            $this->registro_id  = $registro_id;
        }
        if($this->registro_id <= 0){
            return $this->error->error(mensaje: 'Error id debe ser mayor a 0 en '.$this->tabla,data: $this->registro_id);
        }

        $data_activacion = (new activaciones())->init_activa(modelo:$this, reactiva: $reactiva);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar datos de activacion '.$this->tabla,
                data:$data_activacion);
        }

        $transaccion = (new bitacoras())->ejecuta_transaccion(tabla: $this->tabla,funcion: __FUNCTION__,
            modelo: $this, registro_id: $this->registro_id, sql: $data_activacion->consulta);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al EJECUTAR TRANSACCION en '.$this->tabla,data:$transaccion);
        }

        $data = new stdClass();
        $data->mensaje = 'Registro activado con éxito en '.$this->tabla;
        $data->registro_id = $this->registro_id;
        $data->transaccion = $transaccion;

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        return $data;
    }

    /**
     * PARAMS ORDER P INT
     * Aplica status = a activo a todos los elementos o registros de una tabla
     * @return array
     */
    public function activa_todo(): array
    {
        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }
        $this->transaccion = 'UPDATE';
        $consulta = "UPDATE " . $this->tabla . " SET status = 'activo'  ";

        $resultado = $this->ejecuta_sql(consulta: $consulta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: $resultado);
        }

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        return array('mensaje'=>'Registros activados con éxito','sql'=>$this->consulta);
    }

    /**
     *
     * Inserta un registro por registro enviado
     * @return array|stdClass con datos del registro insertado

     * @internal  $this->valida_campo_obligatorio();
     * @internal  $this->valida_estructura_campos();
     * @internal  $this->asigna_data_user_transaccion();
     * @internal  $this->bitacora($this->registro,__FUNCTION__,$consulta);
     * @example
     *      $entrada_modelo->registro = array('tipo_entrada_id'=>1,'almacen_id'=>1,'fecha'=>'2020-01-01',
     *          'proveedor_id'=>1,'tipo_proveedor_id'=>1,'referencia'=>1,'tipo_almacen_id'=>1);
     * $resultado = $entrada_modelo->alta_bd();
     * @version 1.603.54
     * @final revisada
     */
    public function alta_bd(): array|stdClass{

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error SESSION no iniciada',data: array());
        }

        if($_SESSION['usuario_id'] <= 0){
            return $this->error->error(mensaje: 'Error USUARIO INVALIDO',data: $_SESSION['usuario_id']);
        }
        $this->status_default = 'activo';
        $registro = (new inicializacion())->registro_ins(campos_encriptados:$this->campos_encriptados,
            registro: $this->registro,status_default: $this->status_default, tipo_campos: $this->tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar registro ', data: $registro);
        }
        $this->registro = $registro;

        $valida = (new val_sql())->valida_base_alta(campos_obligatorios: $this->campos_obligatorios, modelo: $this,
            no_duplicados: $this->no_duplicados, registro: $registro,tabla:  $this->tabla,
            tipo_campos: $this->tipo_campos, parents: $this->parents);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar alta ', data: $valida);
        }

        $transacciones = (new inserts())->transacciones(modelo: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transacciones',data:  $transacciones);
        }

        $registro = $this->registro(registro_id: $this->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro', data: $registro);
        }

        $data = new stdClass();
        $data->mensaje = "Registro insertado con éxito";
        $data->registro_id = $this->registro_id;
        $data->sql = $transacciones->sql;
        $data->registro = $registro;
        $data->registro_obj = (object)$registro;
        $data->registro_ins = $this->registro;
        $data->campos = $this->campos_tabla;

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        return $data;
    }

    /**
     * Inserta un registro predeterminado
     * @param string|int $codigo Codigo predeterminado default
     * @param string $descripcion Descripcion predeterminado
     * @return array|stdClass
     * @version 6.21.0
     */
    private function alta_predeterminado(
        string|int $codigo = 'PRED', string $descripcion = 'PREDETERMINADO'): array|stdClass
    {
        $pred_ins['predeterminado'] = 'activo';
        $pred_ins['codigo'] = $codigo;
        $pred_ins['descripcion'] = $descripcion;
        $r_alta = $this->alta_registro(registro: $pred_ins);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar prederminado en modelo '.$this->tabla,data:  $r_alta);
        }
        return $r_alta;
    }

    /**
     * Inserta un registro
     * @param array $registro Registro con datos para la insersion
     * @return array|stdClass
     * @version 1.534.51
     */
    public function alta_registro(array $registro):array|stdClass{

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file '.$this->tabla,
                data: $init_archivos_tmp_model);
        }
        if(!isset($_SESSION['usuario_id'])){
            return $this->error->error(mensaje: 'Error SESSION no iniciada',data: array());
        }

        if($_SESSION['usuario_id'] <= 0){
            return $this->error->error(mensaje: 'Error USUARIO INVALIDO en modelo '.$this->tabla,
                data: $_SESSION['usuario_id']);
        }

        $this->registro = $registro;

        $r_alta  = $this->alta_bd();
        if(errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta registro en modelo '.$this->tabla, data: $r_alta);
        }

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file '.$this->tabla,data: $init_archivos_tmp_model);
        }

        return $r_alta;
    }

    private function campos_obligatorios(array $campos_obligatorios){
        $this->campos_obligatorios = array_merge($this->campos_obligatorios,$campos_obligatorios);

        if(isset($campos_obligatorios[0]) && trim($campos_obligatorios[0]) === '*'){

            $limpia = $this->todos_campos_obligatorios();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar campos obligatorios en '.$this->tabla, data: $limpia);
            }
        }
        return $this->campos_obligatorios;
    }

    /**
     * Cuenta los registros de un modelo conforme al filtro en aplicacion
     * @param array $diferente_de Integra el sql para diferente de
     * @param array $filtro Filtro de ejecucion basico
     * @param string $tipo_filtro validos son numeros y textos
     * @param array $filtro_especial arreglo con las condiciones $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x')
     * @param array $filtro_rango
     *                  Opcion1.- Debe ser un array con la siguiente forma array('valor1'=>'valor','valor2'=>'valor')
     *                  Opcion2.-
     *                      Debe ser un array con la siguiente forma
     *                          array('valor1'=>'valor','valor2'=>'valor','valor_campo'=>true)
     * @param array $filtro_fecha Filtros de fecha para sql filtro[campo_1], filtro[campo_2], filtro[fecha]
     * @param array $in Genera IN en sql
     * @param array $not_in Genera NOT IN en SQL
     * @return array|int
     */
    final public function cuenta(array $diferente_de = array(), array $filtro = array(), string $tipo_filtro = 'numeros',
                           array $filtro_especial = array(), array $filtro_rango = array(),
                           array $filtro_fecha = array(), array $in = array(), array $not_in = array()):array|int{

        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }

        $tablas = (new joins())->obten_tablas_completas(columnas_join:  $this->columnas, tabla: $this->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al obtener tablas", data: $tablas);
        }

        $filtros = (new where())->data_filtros_full(columnas_extra: $this->columnas_extra,diferente_de: $diferente_de,
            filtro:  $filtro, filtro_especial: $filtro_especial, filtro_extra: array(), filtro_fecha: $filtro_fecha,
            filtro_rango: $filtro_rango, in:$in, keys_data_filter: $this->keys_data_filter, not_in: $not_in,
            sql_extra: '', tipo_filtro: $tipo_filtro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtros',data: $filtros);
        }

        $sql = /** @lang MYSQL */
            " SELECT COUNT(*) AS total_registros FROM $tablas $filtros->where $filtros->sentencia 
            $filtros->filtro_especial $filtros->filtro_rango";

        $result = $this->ejecuta_consulta(consulta: $sql, campos_encriptados: $this->campos_encriptados);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al ejecutar sql',data: $result);
        }

        return (int)$result->registros[0]['total_registros'];

    }

    /**
     * Genera los datos de una sentencia para WHERE EN SQL
     * @version 1.147.31
     * @param string $where palabra WHERE si vacio lo genera
     * @param string $sentencia Sentencias previamenete cargadas
     * @param string $campo Campo a cargar filtro de or en SQL
     * @param string $value Valor a comparar
     * @return array|stdClass
     */
    private function data_sentencia(string $campo, string $sentencia, string $value, string $where): array|stdClass
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio',data: $campo);
        }

        if($where === ''){
            $where = ' WHERE ';
        }

        $sentencia_env = $this->sentencia_or(campo:  $campo, sentencia: $sentencia, value: $value);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql',data:$sentencia_env);
        }
        $data = new stdClass();
        $data->where = $where;
        $data->sentencia = $sentencia_env;
        return $data;
    }

    /**
     * PHPUNIT
     * @return array|stdClass
     * @throws JsonException
     * @final rev
     */
    public function desactiva_bd(): array|stdClass{
        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }
        if($this->registro_id<=0){
            return  $this->error->error(mensaje: 'Error $this->registro_id debe ser mayor a 0',data: $this->registro_id);
        }
        $registro = $this->registro(registro_id: $this->registro_id);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener registro',data: $registro);
        }


        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $this->aplica_transaccion_inactivo, registro: $registro,
            registro_id:  $this->registro_id, tabla: $this->tabla);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al validar transaccion activa',data: $valida);
        }
        $tabla = $this->tabla;
        $this->consulta = /** @lang MYSQL */
            "UPDATE $tabla SET status = 'inactivo' WHERE id = $this->registro_id";
        $this->transaccion = 'DESACTIVA';
        $transaccion = (new bitacoras())->ejecuta_transaccion(tabla: $this->tabla,funcion: __FUNCTION__, modelo: $this,
            registro_id:  $this->registro_id);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al EJECUTAR TRANSACCION',data: $transaccion);
        }

        $desactiva = $this->aplica_desactivacion_dependencias();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al desactivar dependiente',data:  $desactiva);
        }

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        return array('mensaje'=>'Registro desactivado con éxito', 'registro_id'=>$this->registro_id);

    }

    /**
     * PHPUNIT
     * @return array
     */
    public function desactiva_todo(): array
    {
        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        $consulta = /** @lang MYSQL */
            "UPDATE  $this->tabla SET status='inactivo'";

        $this->link->query($consulta);
        if($this->link->errorInfo()[1]){
            return  $this->error->error($this->link->errorInfo()[0],'');
        }
        else{
            $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
            }
            return array('mensaje'=>'Registros desactivados con éxito');
        }
    }

    /**
     *
     * Elimina un registro por el id enviado
     * @param int $id id del registro a eliminar
     *
     * @return array|stdClass con datos del registro eliminado
     * @example
     *      $registro = $this->modelo->elimina_bd($this->registro_id);
     *
     * @internal  $this->validacion->valida_transaccion_activa($this, $this->aplica_transaccion_inactivo, $this->registro_id, $this->tabla);
     * @internal  $this->obten_data();
     * @internal  $this->ejecuta_sql();
     * @internal  $this->bitacora($registro_bitacora,__FUNCTION__,$consulta);
     * @version 1.563.51
     */
    public function elimina_bd(int $id): array|stdClass{
        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }
        if($id <= 0){
            return  $this->error->error(mensaje: 'El id no puede ser menor a 0 en '.$this->tabla, data: $id);
        }
        $this->registro_id = $id;

        $valida = (new activaciones())->valida_activacion(modelo: $this);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar transaccion activa en ' .$this->tabla,data: $valida);
        }

        $registro_bitacora = $this->obten_data();
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener registro en '.$this->tabla, data:$registro_bitacora);
        }
        $tabla = $this->tabla;
        $this->consulta = /** @lang MYSQL */
            'DELETE FROM '.$tabla. ' WHERE id = '.$id;
        $consulta = $this->consulta;
        $this->transaccion = 'DELETE';

        $elimina = (new dependencias())->aplica_eliminacion_dependencias(
            desactiva_dependientes:$this->desactiva_dependientes,link: $this->link,
            models_dependientes: $this->models_dependientes,registro_id: $this->registro_id,tabla: $this->tabla);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al eliminar dependiente ', data:$elimina);
        }

        $valida = $this->valida_eliminacion_children(id:$id);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al validar children', data:$valida);
        }

        $resultado = $this->ejecuta_sql(consulta: $this->consulta);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql en '.$this->tabla, data:$resultado);
        }
        $bitacora = (new bitacoras())->bitacora(
            consulta: $consulta, funcion: __FUNCTION__,modelo: $this, registro: $registro_bitacora);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar bitacora de '.$this->tabla, data: $bitacora);
        }

        $data = new stdClass();
        $data->registro_id = $id;
        $data->sql = $this->consulta;
        $data->registro = $registro_bitacora;

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        return $data;

    }

    /**
     * Elimina registros con filtro
     * @return string[]
     * @version 1.564.51
     */
    public function elimina_con_filtro_and(array $filtro): array{
        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }
        if(count($filtro) === 0){
            return $this->error->error('Error no existe filtro', $filtro);
        }

        $result = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros '.$this->tabla,data:  $result);
        }
        $dels = array();
        foreach ($result->registros as $row){

            $del = $this->elimina_bd(id:$row[$this->tabla.'_id']);
            if(errores::$error){
                return $this->error->error('Error al eliminar registros '.$this->tabla, $del);
            }
            $dels[] = $del;

        }
        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        return $dels;

    }

    public function elimina_full_childrens(): array
    {
        $dels = array();
        foreach ($this->childrens as $modelo_children=>$namespace){

            $modelo_children_obj = $this->genera_modelo(modelo: $modelo_children,namespace_model: $namespace);
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al generar modelo', data:$modelo_children_obj);
            }
            $elimina_todo_children = $modelo_children_obj->elimina_todo();
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al eliminar children', data:$elimina_todo_children);
            }
            $dels[] = $elimina_todo_children;
        }
        return $dels;
    }

    /**
     * PHPUNIT
     * @return string[]
     */
    public function elimina_todo(): array
    {
        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        $elimina_todo_children = $this->elimina_full_childrens();
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al eliminar childrens', data:$elimina_todo_children);
        }


        $tabla = $this->tabla;
        $this->transaccion = 'DELETE';
        $this->consulta = /** @lang MYSQL */
            'DELETE FROM '.$tabla;

        $resultado = $this->ejecuta_sql($this->consulta);

        if(errores::$error){
            return $this->error->error('Error al ejecutar sql',$resultado);
        }

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        return array('mensaje'=>'Registros eliminados con éxito');
    }

    /**
     * PHPUNIT
     * @return array
     */
    protected function estado_inicial():array{
        $filtro[$this->tabla.'.inicial'] ='activo';
        $r_estado = $this->filtro_and(filtro: $filtro);
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
     * Verifica si existe o no un registro basado en un filtro
     * @param array $filtro array('tabla.campo'=>'value'=>valor,'tabla.campo'=>'campo'=>tabla.campo);
     * @return array|bool
     * @version 1.324.41
     */
    final public function existe(array $filtro): array|bool
    {
        $resultado = $this->cuenta(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al contar registros',data: $resultado);
        }
        $existe = false;
        if((int)$resultado>0){
            $existe = true;
        }

        return $existe;

    }

    /**
     * Verifica si existe un elemento basado en el id
     * @param int $registro_id registro a verificar
     * @return bool|array
     */
    final public function existe_by_id(int $registro_id): bool|array
    {
        $filtro[$this->tabla.'.id'] = $registro_id;
        $existe = $this->existe(filtro: $filtro);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener row', data: $existe);
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
        if(isset($compare_1[$key], $compare_2[$key])) {
            if ((string)$compare_1[$key] === (string)$compare_2[$key]) {
                $existe = true;
            }
        }
        return $existe;
    }

    /**
     * Verifica un elemento predetermindao de la entidad
     * @return bool|array
     * @version 1.485.49
     */
    public function existe_predeterminado(): bool|array
    {
        $key = $this->tabla.'.predeterminado';
        $filtro[$key] = 'activo';
        $existe = $this->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar si existe',data:  $existe);
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
     * Devuelve un array de la siguiente con la informacion de registros encontrados
     *
     * @param bool $aplica_seguridad Si aplica seguridad entonces valida el usuario logueado
     * @param array $columnas columnas a mostrar en la consulta, si columnas = array(), se muestran todas las columnas
     * @param array $columnas_by_table arreglo para obtener los campos especificos de una tabla, si esta seteada,
     * no aplicara las columnas tradicionales
     * @param bool $columnas_en_bruto si true se trae las columnas sion renombrar y solo de la tabla seleccionada
     * @param bool $con_sq
     * @param array $diferente_de Arreglo con los elementos para integrar <> o diferente en el SQL
     * @param array $filtro array('tabla.campo'=>'value'=>valor,'tabla.campo'=>'campo'=>tabla.campo);
     * @param array $filtro_especial arreglo con las condiciones $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x')
     *          arreglo con condiciones especiales $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x','comparacion'=>'AND OR')
     * @param array $filtro_extra arreglo que contiene las condiciones
     * $filtro_extra[0]['tabla.campo']=array('operador'=>'>','valor'=>'x','comparacion'=>'AND');
     * @param array $filtro_fecha Filtros de fecha para sql filtro[campo_1], filtro[campo_2], filtro[fecha]
     * @param array $filtro_rango
     *                  Opcion1.- Debe ser un array con la siguiente forma array('valor1'=>'valor','valor2'=>'valor')
     *                  Opcion2.-
     *                      Debe ser un array con la siguiente forma
     *                          array('valor1'=>'valor','valor2'=>'valor','valor_campo'=>true)
     *                  Opcion1.- $filtro_rango['tabla.campo'] = array('valor1'=>'valor','valor2'=>'valor')
     * @param array $group_by Es un array con la forma array(0=>'tabla.campo', (int)N=>(string)'tabla.campo')
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param array $in Arreglo con los elementos para integrar un IN en SQL in[llave] = tabla.campo, in['values'] = array()
     * @param int $limit numero de registros a mostrar, 0 = sin limite
     * @param array $not_in Conjunto de valores para not_in not_in[llave] = string, not_in['values'] = array()
     * @param int $offset numero de registros de comienzo de datos
     * @param array $order array('tabla.campo'=>'ASC');
     * @param string $sql_extra Sql previo o extra si existe forzara la integracion de un WHERE
     * @param string $tipo_filtro Si es numero es un filtro exacto si es texto es con %%
     * @return array|stdClass
     * @example
     *      $filtro_extra[0][tabla.campo]['operador'] = '<';
     *      $filtro_extra[0][tabla.campo]['valor'] = 'x';
     *
     *      $filtro_extra[0][tabla2.campo]['operador'] = '>';
     *      $filtro_extra[0][tabla2.campo]['valor'] = 'x';
     *      $filtro_extra[0][tabla2.campo]['comparacion'] = 'OR';
     *
     *      $resultado = filtro_extra_sql($filtro_extra);
     *      $resultado =  tabla.campo < 'x' OR tabla2.campo > 'x'
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
     * @author mgamboa
     * @fecha 2022-08-02 16:49
     * @version 1.575.51
     */
    final public function filtro_and(bool $aplica_seguridad = true, array $columnas =array(),
                               array $columnas_by_table = array(), bool $columnas_en_bruto = false, bool $con_sq = true,
                               array $diferente_de = array(), array $filtro=array(), array $filtro_especial= array(),
                               array $filtro_extra = array(), array $filtro_fecha = array(),
                               array $filtro_rango = array(), array $group_by=array(), array $hijo = array(),
                               array $in = array(), int $limit=0,  array $not_in = array(), int $offset=0,
                               array $order = array(), string $sql_extra = '',
                               string $tipo_filtro='numeros'): array|stdClass{

        $params_fn = new stdClass();
        $params_fn->aplica_seguridad = $aplica_seguridad;
        $params_fn->columnas = $columnas;
        $params_fn->columnas_by_table = $columnas_by_table;
        $params_fn->columnas_en_bruto = $columnas_en_bruto;
        $params_fn->diferente_de = $diferente_de;
        $params_fn->filtro = $filtro;
        $params_fn->filtro_especial = $filtro_especial;
        $params_fn->filtro_extra = $filtro_extra;
        $params_fn->filtro_rango = $filtro_rango;
        $params_fn->group_by = $group_by;
        $params_fn->hijo = $hijo;
        $params_fn->in = $in;
        $params_fn->limit = $limit;
        $params_fn->not_in = $not_in;
        $params_fn->offset = $offset;
        $params_fn->order = $order;
        $params_fn->sql_extra = $sql_extra;
        $params_fn->tipo_filtro = $tipo_filtro;
        $params_fn->filtro_fecha = $filtro_fecha;

        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }

        if($this->aplica_seguridad && $aplica_seguridad) {
            $filtro = array_merge($filtro, $this->filtro_seguridad);
        }

        if($limit < 0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0  con 0 no aplica limit',
                data: $params_fn);
        }

        $sql = $this->genera_sql_filtro(columnas: $columnas, columnas_by_table: $columnas_by_table,
            columnas_en_bruto: $columnas_en_bruto, con_sq: $con_sq, diferente_de: $diferente_de, filtro: $filtro,
            filtro_especial: $filtro_especial, filtro_extra: $filtro_extra, filtro_rango: $filtro_rango,
            group_by: $group_by, in: $in, limit: $limit, not_in: $not_in, offset: $offset, order: $order,
            sql_extra: $sql_extra, tipo_filtro: $tipo_filtro, filtro_fecha: $filtro_fecha);

        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al maquetar sql',data:$sql);
        }

        $result = $this->ejecuta_consulta(consulta:$sql,campos_encriptados: $this->campos_encriptados, hijo: $hijo);
        if(errores::$error){
            return  $this->error->error(mensaje:'Error al ejecutar sql',data:$result);
        }

        return $result;
    }


    /**
     * Genera un filtro aplicando OR
     * @param bool $aplica_seguridad
     * @param array $columnas columnas inicializadas a mostrar a peticion en resultado SQL
     * @param array $columnas_by_table Obtiene solo las columnas de la tabla en ejecucion
     * @param bool $columnas_en_bruto Genera las columnas tal y como vienen en la base de datos
     * @param array $filtro Filtro en forma filtro[campo] = 'value filtro'
     * @param array $group_by
     * @param array $hijo Arreglo con los datos para la obtencion de datos dependientes de la estructura o modelo
     * @param int $limit Limit de datos a motrar
     * @param int $offset
     * @param array $order
     * @return array|stdClass
     */
    final public function filtro_or(bool $aplica_seguridad = false, array $columnas = array(),
                              array $columnas_by_table = array(), bool $columnas_en_bruto = false,
                              array $filtro = array(), array $group_by = array(), array $hijo = array(),
                              int $limit = 0, int $offset = 0, array $order = array()):array|stdClass{

        $consulta = $this->genera_consulta_base(columnas: $columnas,
            columnas_by_table: $columnas_by_table, columnas_en_bruto: $columnas_en_bruto,
            extension_estructura: $this->extension_estructura, renombradas: $this->renombres);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $consulta);
        }
        $where = '';
        $sentencia = '';
        foreach($filtro as $campo=>$value){
            $data_sentencia = $this->data_sentencia(campo:  $campo,sentencia:  $sentencia,value:  $value, where: $where);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar data sentencia',data: $data_sentencia);
            }
            $where = $data_sentencia->where;
            $sentencia = $data_sentencia->sentencia;
        }

        $params_sql = (new params_sql())->params_sql(aplica_seguridad: $aplica_seguridad,group_by:  $group_by,
            limit:  $limit, modelo: $this, offset: $offset, order: $order, sql_where_previo: $sentencia);

        $consulta .= $where . $sentencia.$params_sql->limit;

        $result = $this->ejecuta_consulta(consulta:$consulta, campos_encriptados: $this->campos_encriptados, hijo: $hijo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: $result);
        }

        return $result;
    }

    /**
     * Genera los filtros para una sentencia select
     * @param array $columnas Columnas para muestra si vacio muestra todas
     * @param array $columnas_by_table Obtiene solo las columnas de la tabla en ejecucion
     * @param bool $columnas_en_bruto if true obtiene solo los elementos nativos de la tabla o modelo
     * @param bool $con_sq
     * @param array $diferente_de Arreglo con los elementos para integrar un diferente de
     * @param array $filtro Filtro base para ejecucion de WHERE genera ANDS
     * @param array $filtro_especial arreglo con las condiciones $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x')
     * @param array $filtro_extra arreglo que contiene las condiciones
     * $filtro_extra[0]['tabla.campo']=array('operador'=>'>','valor'=>'x','comparacion'=>'AND');
     * @param array $filtro_rango
     *                  Opcion1.- Debe ser un array con la siguiente forma array('valor1'=>'valor','valor2'=>'valor')
     *                  Opcion2.-
     *                      Debe ser un array con la siguiente forma
     *                          array('valor1'=>'valor','valor2'=>'valor','valor_campo'=>true)
     * @param array $group_by Es un array con la forma array(0=>'tabla.campo', (int)N=>(string)'tabla.campo')
     * @param array $in Arreglo con los elementos para integrar un IN en SQL in[llave] = tabla.campo, in['values'] = array()
     * @param int $limit Numero de registros a mostrar
     * @param array $not_in Conjunto de valores para not_in not_in[llave] = string, not_in['values'] = array()
     * @param int $offset Numero de inicio de registros
     * @param array $order con parametros para generar sentencia
     * @param string $sql_extra Sql previo o extra si existe forzara la integracion de un WHERE
     * @param string $tipo_filtro Si es numero es un filtro exacto si es texto es con %%
     * @param array $filtro_fecha Filtros de fecha para sql filtro[campo_1], filtro[campo_2], filtro[fecha]
     * @return array|string
     * @example
     *      $filtro_extra[0][tabla.campo]['operador'] = '<';
     *      $filtro_extra[0][tabla.campo]['valor'] = 'x';
     *
     *      $filtro_extra[0][tabla2.campo]['operador'] = '>';
     *      $filtro_extra[0][tabla2.campo]['valor'] = 'x';
     *      $filtro_extra[0][tabla2.campo]['comparacion'] = 'OR';
     *
     *      $resultado = filtro_extra_sql($filtro_extra);
     *      $resultado =  tabla.campo < 'x' OR tabla2.campo > 'x'
     *
     * @fecha 2022-08-02 16:38
     * @author mgamboa
     * @version 1.575.51
     */
    private function genera_sql_filtro(array $columnas, array $columnas_by_table, bool $columnas_en_bruto, bool $con_sq,
                                       array $diferente_de, array $filtro, array $filtro_especial, array $filtro_extra,
                                       array $filtro_rango, array $group_by, array $in, int $limit, array $not_in,
                                       int $offset, array $order, string $sql_extra, string $tipo_filtro,
                                       array $filtro_fecha = array()): array|string
    {

        $params_fn = new stdClass();
        $params_fn->columnas = $columnas;
        $params_fn->columnas_by_table = $columnas_by_table;
        $params_fn->columnas_en_bruto = $columnas_en_bruto;
        $params_fn->diferente_de = $diferente_de;
        $params_fn->filtro = $filtro;
        $params_fn->filtro_especial = $filtro_especial;
        $params_fn->filtro_extra = $filtro_extra;
        $params_fn->filtro_fecha = $filtro_fecha;
        $params_fn->filtro_rango = $filtro_rango;
        $params_fn->group_by = $group_by;
        $params_fn->in = $in;
        $params_fn->limit = $limit;
        $params_fn->not_in = $not_in;
        $params_fn->offset = $offset;
        $params_fn->order = $order;
        $params_fn->sql_extra = $sql_extra;
        $params_fn->tipo_filtro = $tipo_filtro;


        if($limit<0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0',data:  $params_fn);
        }
        if($offset<0){
            return $this->error->error(mensaje: 'Error $offset debe ser mayor o igual a 0',data: $params_fn);

        }

        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }
        $consulta = $this->genera_consulta_base(columnas: $columnas, columnas_by_table: $columnas_by_table,
            columnas_en_bruto: $columnas_en_bruto, con_sq: $con_sq, extension_estructura: $this->extension_estructura,
            renombradas: $this->renombres);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $consulta);
        }

        $complemento_sql = (new filtros())->complemento_sql(aplica_seguridad:false, diferente_de: $diferente_de,
            filtro:  $filtro, filtro_especial: $filtro_especial, filtro_extra: $filtro_extra,
            filtro_rango: $filtro_rango, group_by: $group_by, in: $in, limit: $limit, modelo: $this, not_in: $not_in,
            offset:  $offset, order:  $order, sql_extra: $sql_extra, tipo_filtro: $tipo_filtro,
            filtro_fecha:  $filtro_fecha);

        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al maquetar sql',data: $complemento_sql);
        }

        $sql = (new filtros())->consulta_full_and(complemento:  $complemento_sql, consulta: $consulta, modelo: $this);
        if(errores::$error){
            return  $this->error->error(mensaje:'Error al maquetar sql',data: $sql);
        }

        $this->consulta = $sql;

        return $sql;
    }

    /**
     * Genera un codigo aleatorio de longitud N
     * @param int $longitud
     * @return string
     */
    public function get_codigo_aleatorio(int $longitud = 6): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random_string = '';

        for($i = 0; $i < $longitud; $i++) {
            $random_character = $chars[mt_rand(0, strlen($chars) - 1)];
            $random_string .= $random_character;
        }

        return $random_string;
    }

    /**
     * Obtiene los datos para datatable
     * @param array $filtro
     * @param array $filtro_especial Filtro para get data
     * @param int $n_rows_for_page N rows
     * @param int $pagina Num pag
     * @return array
     * @version 1.544.51
     */
    public function get_data_lista(array $filtro = array(),array $filtro_especial = array(), int $n_rows_for_page = 10, int $pagina = 1): array
    {

        $limit = $n_rows_for_page;

        $n_rows = $this->cuenta(filtro_especial: $filtro_especial);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros', data: $n_rows);
        }

        $offset = ($pagina - 1) * $n_rows_for_page;

        if($n_rows <= $limit){
            $offset = 0;
        }

        $result = $this->filtro_and(filtro: $filtro,filtro_especial:$filtro_especial,limit: $limit, offset: $offset);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros', data: $result);
        }


        $out = array();
        $out['n_registros'] = $n_rows;
        $out['registros'] = $result->registros;
        $out['data_result'] = $result;

        return $out;
    }

    private function get_predeterminado(): array|stdClass
    {
        $key = $this->tabla.'.predeterminado';
        $filtro[$key] = 'activo';
        $r_modelo = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos',data:  $r_modelo);
        }
        if((int)$r_modelo->n_registros > 1){
            return $this->error->error(mensaje: 'Error existe mas de un predeterminado',data:  $r_modelo);
        }
        return $r_modelo;
    }

    /**
     * Obtiene un identificador predeterminado
     * @return array|int
     * @version 1.486.49
     */
    public function id_predeterminado(): array|int
    {
        $key = $this->tabla.'.predeterminado';

        $filtro[$key] = 'activo';

        $r_modelo = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener predeterminado',data:  $r_modelo);
        }

        if($r_modelo->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existe predeterminado',data:  $r_modelo);
        }
        if($r_modelo->n_registros > 1){
            return $this->error->error(
                mensaje: 'Error existe mas de un predeterminado',data:  $r_modelo);
        }

        return (int) $r_modelo->registros[0][$this->key_id];

    }

    /**
     * Inicializa elementos para cache
     * @return array|string
     * @6.2.0
     */
    private function init_archivos_tmp_model_exe(): array|string
    {
        $init_archivos_tmp_model = $this->init_archivos_tmp_model();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }
        if(file_exists($init_archivos_tmp_model)){
            $rmdir = (new files())->rmdir_recursive(dir: $init_archivos_tmp_model);
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al eliminar '.$this->tabla, data:$rmdir);
            }
        }
        return $init_archivos_tmp_model;
    }


    /**
     * Inserta un registro predeterminado del modelo en ejecucion
     * @param string|int $codigo Codigo predeterminado default
     * @param string $descripcion Descripcion predeterminado
     * @return array|stdClass
     * @version 6.30.0
     */
    public function inserta_predeterminado(
        string|int $codigo = 'PRED', string $descripcion = 'PREDETERMINADO'): array|stdClass
    {
        $r_pred = new stdClass();
        $existe = $this->existe_predeterminado();
        if(errores::$error){
            return $this->error->error(
                mensaje: 'Error al validar si existe predeterminado en modelo '.$this->tabla,data:  $existe);
        }
        if(!$existe){
            $r_pred = $this->alta_predeterminado(codigo: $codigo, descripcion: $descripcion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al insertar prederminado en modelo '.$this->tabla,data:  $r_pred);
            }
        }
        return $r_pred;
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
     * Limpia campos extras de un registro de datos
     * @param array $registro
     * @param array $campos_limpiar
     * @return array
     */
    public function limpia_campos_extras(array $registro, array $campos_limpiar): array
    {
        foreach ($campos_limpiar as $valor) {
            if (isset($registro[$valor])) {
                unset($registro[$valor]);
            }
        }
        return $registro;
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
        $registro = $this->registro(registro_id: $id);
        if(errores::$error){
            return $this->error->error("Error al obtener registro", $registro);
        }
        return $registro;

    }

    private function limpia_campos_obligatorios(array $unsets): array
    {
        foreach($this->campos_obligatorios as $key=>$campo_obligatorio){
            if(in_array($campo_obligatorio, $unsets, true)) {
                unset($this->campos_obligatorios[$key]);
            }
        }
        return $this->campos_obligatorios;
    }


    /**
     *
     * Modifica los datos de un registro de un modelo
     * @param array $registro registro con datos a modificar
     * @param int $id id del registro a modificar
     * @param bool $reactiva para evitar validacion de status inactivos
     * @return array|stdClass resultado de la insercion
     * @example
     *      $r_modifica_bd =  parent::modifica_bd($registro, $id, $reactiva);
     * @internal  $this->validacion->valida_transaccion_activa($this, $this->aplica_transaccion_inactivo, $this->registro_id, $this->tabla);
     * @internal  $this->genera_campos_update();
     * @internal  $this->agrega_usuario_session();
     * @internal  $this->ejecuta_sql();
     * @internal  $this->bitacora($this->registro_upd,__FUNCTION__, $consulta);
     * @final rev
     */
    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array|stdClass
    {
        if($this->usuario_id <=0){
            return $this->error->error(mensaje: 'Error usuario invalido no esta logueado',data: $this->usuario_id);
        }

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }

        $init = (new inicializacion())->init_upd(id:$id, modelo: $this,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar',data: $init);
        }

        $valida = (new validaciones())->valida_upd_base(id:$id, registro_upd: $this->registro_upd,
            tipo_campos: $this->tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos',data: $valida);
        }

        $ajusta = (new inicializacion())->ajusta_campos_upd(id:$id, modelo: $this);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ajustar elemento',data:$ajusta);
        }

        $ejecuta_upd = (new upd())->ejecuta_upd(id:$id,modelo:  $this);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al verificar actualizacion',data:$ejecuta_upd);
        }

        $resultado = (new upd())->aplica_ejecucion(ejecuta_upd: $ejecuta_upd,id:  $id,modelo:  $this,
            reactiva:  $reactiva,registro:  $registro, valida_user: $this->valida_user);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar sql', data:  $resultado);
        }

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
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
        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }
        $this->registro_upd = $registro;
        if(count($this->registro_upd) === 0){
            return $this->error->error('El registro no puede venir vacio',$this->registro_upd);
        }
        if(count($filtro) === 0){
            return $this->error->error('El filtro no puede venir vacio',$filtro);
        }

        $r_data = $this->filtro_and(filtro: $filtro);
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

        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
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
        $init_archivos_tmp_model = $this->init_archivos_tmp_model_exe();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener file'.$this->tabla,data: $init_archivos_tmp_model);
        }
        $r_modifica = $this->modifica_bd($registro, $id);
        if(errores::$error){
            return $this->error->error("Error al modificar", $r_modifica);
        }
        return $r_modifica;

    }

    /**
     *
     * Devuelve un array con el registro buscado por this->registro_id del modelo
     * @version 1.11.8
     * @param array $columnas columnas a mostrar en la consulta, si columnas = array(), se muestran todas las columnas
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param array $extension_estructura arreglo con la extension de una estructura para obtener datos de foraneas a configuracion
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
     */
    final public function obten_data(array $columnas = array(), bool $columnas_en_bruto = false,
                               array $extension_estructura = array(), array $hijo= array()): array{
        $this->row = new stdClass();
        if($this->registro_id < 0){
            return  $this->error->error(mensaje: 'Error el id debe ser mayor a 0 en el modelo '.$this->tabla,
                data: $this->registro_id);
        }
        if(count($extension_estructura) === 0){
            $extension_estructura = $this->extension_estructura;
        }
        $resultado = $this->obten_por_id(columnas: $columnas, columnas_en_bruto: $columnas_en_bruto,
            extension_estructura: $extension_estructura, hijo: $hijo);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener por id en '.$this->tabla, data: $resultado);
        }
        if((int)$resultado->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existe registro de '.$this->tabla,data:  $resultado);
        }
        foreach($resultado->registros[0] as $campo=>$value){
            $this->row->$campo = $value;
        }
        return $resultado->registros[0];
    }

    /**
     *
     * Devuelve un array con los datos del ultimo registro
     * @param array $filtro filtro a aplicar en sql
     * @param bool $aplica_seguridad si aplica seguridad integra usuario_permitido_id
     * @return array con datos del registro encontrado o registro vacio
     * @example
     *      $filtro['prospecto.aplica_ruleta'] = 'activo';
     * $resultado = $this->obten_datos_ultimo_registro($filtro);
     *
     * @internal  $this->filtro_and($filtro,'numeros',array(),$this->order,1);
     * @version 1.451.48
     */
    public function obten_datos_ultimo_registro(bool $aplica_seguridad = true, array $columnas = array(),
                                                bool $columnas_en_bruto = false, array $filtro = array(),
                                                array $filtro_extra = array(), array $order = array()): array
    {
        if($this->tabla === ''){
            return $this->error->error(mensaje: 'Error tabla no puede venir vacia',data: $this->tabla);
        }
        if(count($order)===0){
            $order = array($this->tabla.'.id'=>'DESC');
        }

        $this->limit = 1;

        $resultado = $this->filtro_and(aplica_seguridad: $aplica_seguridad,columnas: $columnas,
            columnas_en_bruto: $columnas_en_bruto, filtro: $filtro,filtro_extra: $filtro_extra, limit: 1,
            order: $order);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener datos',data: $resultado);
        }
        if((int)$resultado->n_registros === 0){
            return array();
        }
        return $resultado->registros[0];

    }

    /**
     * FULL
     * Devuelve un array con un elemento declarado por $this->>registro_id
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param array $columnas columnas a mostrar en la consulta, si columnas = array(), se muestran todas las columnas
     * @param array $extension_estructura arreglo con la extension de una estructura para obtener datos de foraneas a configuracion
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
     */
    private function obten_por_id(array $columnas = array(),array $columnas_by_table = array(),
                                  bool $columnas_en_bruto = false,
                                  array $extension_estructura= array(), array $hijo = array()):array|stdClass{
        if($this->registro_id < 0){
            return  $this->error->error(mensaje: 'Error el id debe ser mayor a 0',data: $this->registro_id);
        }
        if(count($extension_estructura)===0){
            $extension_estructura = $this->extension_estructura;
        }
        $tabla = $this->tabla;

        $consulta = $this->genera_consulta_base(columnas: $columnas,
            columnas_by_table: $columnas_by_table, columnas_en_bruto: $columnas_en_bruto,
            extension_estructura: $extension_estructura, renombradas: $this->renombres);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar consulta base',data:  $consulta);
        }

        $where = " WHERE $tabla".".id = $this->registro_id ";
        $consulta .= $where;

        $result = $this->ejecuta_consulta(consulta: $consulta, campos_encriptados: $this->campos_encriptados,
            hijo: $hijo);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }
        return $result;
    }

    /**
     *
     * Obtiene todos los registros de un modelo
     * @param bool $aplica_seguridad Si aplica seguridad se integra usuario_permitido_id el cual debe existir en los
     * registros
     * @param array $columnas columnas inicializadas a mostrar a peticion en resultado SQL
     * @param bool $columnas_en_bruto Si columnas en bruto obtiene los campos tal cual estan en la bd
     * @param bool $con_sq Integra las columnas extra si true
     * @param array $group_by Es un array con la forma array(0=>'tabla.campo', (int)N=>(string)'tabla.campo')
     * @param int $limit Limit para integrar con sql
     * @param bool $return_objects Retorna el resultado en objetos
     * @param string $sql_extra Sql extra para integrar
     * @return array|stdClass conjunto de registros obtenidos
     * @example
     *      $es_referido = $controlador->directiva->checkbox(4,'inactivo','Es Referido',true,'es_referido');
     *
     * @version 1.376.44
     */
    final public function obten_registros(bool $aplica_seguridad = false, array $columnas = array(),
                                          bool $columnas_en_bruto = false, bool $con_sq = true, array $group_by = array(),
                                          int $limit = 0, bool $return_objects = false,
                                          string $sql_extra=''): array|stdClass{

        if($this->limit > 0){
            $limit = $this->limit;
        }


        $base = (new sql())->sql_select_init(aplica_seguridad: $aplica_seguridad, columnas: $columnas,
            columnas_en_bruto: $columnas_en_bruto, con_sq: $con_sq, extension_estructura: $this->extension_estructura,
            group_by: $group_by, limit: $limit, modelo: $this, offset: $this->offset, order: $this->order,
            renombres: $this->renombres, sql_where_previo: $sql_extra);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar datos en '.$this->tabla, data: $base);
        }

        $consulta = (new sql())->sql_select(consulta_base:$base->consulta_base,params_base:  $base->params,
            sql_extra: $sql_extra);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar consulta en '.$this->tabla, data: $consulta);
        }

        $this->transaccion = 'SELECT';
        $result = $this->ejecuta_consulta(consulta: $consulta, campos_encriptados: $this->campos_encriptados);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar consulta en '.$this->tabla, data: $result);
        }
        $this->transaccion = '';

        return $result;
    }

    /**
     * ERROREV
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
     * @version 1.264.40
     * @verfuncion 1.1.0
     * @fecha 2022-08-02 17:03
     * @author mgamboa
     */
    public function obten_registros_activos(array $filtro= array(), array $hijo = array(),
                                            array $order = array()):array|stdClass{

        $filtro[$this->tabla.'.status'] = 'activo';
        $r_data = $this->filtro_and(filtro: $filtro, hijo: $hijo,order: $order);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al filtrar", data: $r_data);
        }

        return $r_data;
    }

    /**
     *
     * Devuelve un conjunto de registros ordenados con filtro
     * @param string $campo campo de orden
     * @param bool $columnas_en_bruto
     * @param array $filtros filtros para generar AND en el resultado
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
     * @version 1.72.17
     */
    public function obten_registros_filtro_and_ordenado(string $campo, bool $columnas_en_bruto,
                                                        array $filtros, string $orden):array|stdClass{
        $this->filtro = $filtros;
        if(count($this->filtro) === 0){
            return $this->error->error(mensaje: 'Error los filtros no pueden venir vacios',data: $this->filtro);
        }
        if($campo === ''){
            return $this->error->error(mensaje:'Error campo no pueden venir vacios',data:$this->filtro);
        }

        $sentencia = (new where())->genera_and(columnas_extra: $this->columnas_extra, filtro: $filtros);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar and',data:$sentencia);
        }
        $consulta = $this->genera_consulta_base(columnas: array(), columnas_by_table: array(),
            columnas_en_bruto: $columnas_en_bruto,
            extension_estructura: $this->extension_estructura, renombradas:  $this->renombres);

        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar consulta',data:$consulta);
        }

        $where = " WHERE $sentencia";
        $order_by = " ORDER BY $campo $orden";
        $consulta .= $where . $order_by;

        $result = $this->ejecuta_consulta(consulta: $consulta, campos_encriptados: $this->campos_encriptados);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql',data:$result);
        }

        return $result;
    }

    /**
     * PHPUNIT
     * @return array|int
     */
    public function obten_ultimo_registro(): int|array
    {
        $this->order = array($this->tabla.'.id'=>'DESC');
        $this->limit = 1;
        $resultado = $this->obten_registros();
        if(isset($resultado['error'])){
            return $this->error->error(mensaje: 'Error al obtener registros',data: $resultado);
        }

        if((int)$resultado['n_registros'] === 0){
            return 1;
        }

        return $resultado['registros'][0][$this->tabla.'_id'] + 1;
    }


    /**
     *
     * Funcion que regresa en forma de array un registro de una estructura de datos del registro_id unico de dicha
     * estructura
     * @param int $registro_id $id Identificador del registro
     * @param array $columnas columnas a obtener del registro
     * @param bool $columnas_en_bruto
     * @param array $extension_estructura arreglo con la extension de una estructura para obtener datos de foraneas
     * a configuracion
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param bool $retorno_obj
     * @return array|stdClass
     * @version 1.15.9
     */
    final public function registro(int $registro_id, array $columnas = array(), bool $columnas_en_bruto = false,
                             array $extension_estructura = array(), array $hijo = array(),
                             bool $retorno_obj = false):array|stdClass{
        if($registro_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $registro_id debe ser mayor a 0',
                data: $registro_id);
        }
        $this->registro_id = $registro_id;
        $registro = $this->obten_data(columnas: $columnas, columnas_en_bruto: $columnas_en_bruto,
            extension_estructura: $extension_estructura, hijo: $hijo);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener registro',data: $registro);
        }

        if($retorno_obj){
            $registro = (object)$registro;
        }

        return $registro;
    }

    final public function registro_by_descripcion(string $descripcion){

        $key_descripcion = $this->tabla.'.descripcion';
        $filtro[$key_descripcion] = $descripcion;
        $result = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener registros',data: $result);
        }
        return $result;

    }

    /**
     *
     * Obtiene los registros de una tabla
     * @param array $columnas Columnas a mostrar en resultado SQL
     * @param bool $columnas_en_bruto Regresa las columnas en su estado puro de la base de datos
     * @param bool $con_sq Integra las columnas extra si true
     * @param bool $aplica_seguridad Si aplica seguridad buscara usuario permitido
     * @param int $limit Limit de resultado
     * @param array $order Orden de resultado
     * @param bool $return_obj Si retorna obj el resultado se envia en un stdclass
     * @return array|stdClass
     * @version 1.448.48
     */
    public function registros(array $columnas = array(), bool $columnas_en_bruto = false, bool $con_sq = true,                              bool $aplica_seguridad = false, int $limit = 0, array $order = array(),
                              bool $return_obj = false):array|stdClass{

        $this->order = $order;
        $resultado =$this->obten_registros(aplica_seguridad: $aplica_seguridad, columnas: $columnas,
            columnas_en_bruto: $columnas_en_bruto, con_sq: $con_sq, limit: $limit);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros activos en '.$this->tabla,data: $resultado);
        }
        $this->registros = $resultado->registros;
        $registros = $resultado->registros;
        if($return_obj){
            $registros = $resultado->registros_obj;
        }

        return $registros;
    }

    /**
     * Obtiene los registros activos de un modelo de datos
     * @param array $columnas
     * @param bool $aplica_seguridad
     * @param int $limit
     * @return array
     */
    public function registros_activos(array $columnas = array(), bool $aplica_seguridad = false, int $limit = 0): array
    {
        $filtro[$this->tabla.'.status'] = 'activo';
        $resultado =$this->filtro_and(aplica_seguridad: $aplica_seguridad, columnas: $columnas, filtro: $filtro,
            limit: $limit);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registros',data: $resultado);
        }
        $this->registros = $resultado->registros;
        return $this->registros;
    }

    /**
     * Obtiene registros con permisos
     * @param array $columnas
     * @return array
     */
    public function registros_permitidos(array $columnas = array()): array
    {
        $registros = $this->registros(columnas: $columnas,aplica_seguridad:  $this->aplica_seguridad);
        if(errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registros en '.$this->tabla, data: $registros);
        }

        return $registros;
    }

    /**
     * Inicializa un elemento de salida para homolagar resultados
     * @return stdClass
     * @version 7.2.2
     */
    private function result_ini(): stdClass
    {
        $r_modelo = new stdClass();
        $r_modelo->n_registros = 0;
        $r_modelo->registros= array();
        $r_modelo->sql= '';
        $r_modelo->registros_obj= array();
        return $r_modelo;
    }

    public function row_predeterminado(): array|stdClass
    {

        $r_modelo = $this->result_ini();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar result',data:  $r_modelo);
        }


        $tiene_predeterminado = $this->tiene_predeterminado();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener si predeterminado',data:  $tiene_predeterminado);
        }

        if($tiene_predeterminado){
            $r_modelo = $this->get_predeterminado();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener datos',data:  $r_modelo);
            }

        }
        return $r_modelo;
    }

    /**
     * Obtiene el id de una seccion
     * @param string $seccion Seccion a obtener el id
     * @return array|int
     * @version 1.356.41
     */
    protected function seccion_menu_id(string $seccion):array|int{
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion no puede venir vacio',data: $seccion);
        }
        $filtro['adm_seccion.descripcion'] = $seccion;
        $modelo_sm = new adm_seccion($this->link);

        $r_seccion_menu = $modelo_sm->filtro_and(filtro:$filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener seccion menu',data: $r_seccion_menu);
        }
        if((int)$r_seccion_menu->n_registros === 0){
            return $this->error->error(mensaje: 'Error al obtener seccion menu no existe',data: $r_seccion_menu);
        }

        $registros = $r_seccion_menu->registros[0];
        $seccion_menu_id = $registros['adm_seccion_id'];
        return (int)$seccion_menu_id;
    }

    /**
     *
     * @param string $sentencia Sentencias previamenete cargadas
     * @version 1.66.17
     * @param string $campo Campo a cargar filtro de or en SQL
     * @param string $value Valor a comparar
     * @return string|array
     */
    private function sentencia_or(string $campo,  string $sentencia, string $value): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio',data: $campo);
        }
        $or = '';
        if($sentencia !== ''){
            $or = ' OR ';
        }
        $sentencia.=" $or $campo = '$value'";
        return $sentencia;
    }

    /**
     * Suma sql
     * @param array $campos [alias=>campo] alias = string no numerico campo string campo de la base de datos
     * @param array $filtro Filtro para suma
     * @return array con la suma de los elementos seleccionados y filtrados
     * @version 1.535.51
     */
    public function suma(array $campos, array $filtro = array()): array
    {


        $this->filtro = $filtro;
        if(count($campos)===0){
            return $this->error->error(mensaje: 'Error campos no puede venir vacio',data: $campos);
        }

        $columnas = (new sumas())->columnas_suma(campos: $campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al agregar columnas',data: $columnas);
        }

        $filtro_sql = (new where())->genera_and(columnas_extra: $this->columnas_extra, filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro_sql);
        }

        $where = (new where())->where_suma(filtro_sql: $filtro_sql);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar where',data: $where);
        }

        $tabla = $this->tabla;
        $tablas = (new joins())->obten_tablas_completas(columnas_join:  $this->columnas, tabla: $tabla);
        if(errores::$error){
            return $this->error->error('Error al obtener tablas',$tablas);
        }

        $consulta = 'SELECT '.$columnas.' FROM '.$tablas.$where;

        $resultado = $this->ejecuta_consulta(consulta: $consulta, campos_encriptados: $this->campos_encriptados);
        if(errores::$error){
            return $this->error->error('Error al ejecutar sql',$resultado);
        }

        return $resultado->registros[0];
    }


    public function status(string $campo, int $registro_id): array|stdClass
    {
        $registro = $this->registro(registro_id: $registro_id,columnas_en_bruto: true,retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro',data: $registro);
        }

        $status_actual = $registro->$campo;
        $status_nuevo = 'activo';

        if($status_actual === 'activo'){
            $status_nuevo = 'inactivo';
        }

        $registro_upd[$campo] = $status_nuevo;

        $upd = $this->modifica_bd(registro: $registro_upd,id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar registro',data: $upd);
        }

        return $upd;

    }

    public function tiene_predeterminado(): bool
    {
        $tiene_predeterminado = false;
        if(in_array('predeterminado', $this->data_columnas->columnas_parseadas)){
            $tiene_predeterminado = true;
        }
        return $tiene_predeterminado;
    }

    private function todos_campos_obligatorios(){
        $this->campos_obligatorios = $this->campos_tabla;
        $limpia = $this->unset_campos_obligatorios();
        if (errores::$error) {
            return  $this->error->error(mensaje: 'Error al limpiar campos obligatorios en '.$this->tabla, data: $limpia);

        }
        return $limpia;
    }

    public function total_registros(): array|int
    {
        $n_rows = $this->cuenta();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al contar registros',data: $n_rows);
        }
        return $n_rows;
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

    private function unset_campos_obligatorios(){
        $unsets = array('fecha_alta','fecha_update','id','usuario_alta_id','usuario_update_id');

        $limpia = $this->limpia_campos_obligatorios(unsets: $unsets);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar campos obligatorios en '.$this->tabla, data: $limpia);
        }
        return $limpia;
    }

    /**
     * Valida si existe un elemento predeterminado previo a su alta
     * @return bool|array
     * @version 1.532.51
     */
    protected function valida_predetermiando(): bool|array
    {
        if(isset($this->registro['predeterminado']) && $this->registro['predeterminado'] === 'activo'){
            $existe = $this->existe_predeterminado();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al verificar si existe',data:  $existe);
            }
            if($existe){
                return $this->error->error(mensaje: 'Error ya existe elemento predeterminado',data:  $this->registro);
            }
        }
        return true;
    }

    final public function valida_eliminacion_children(int $id): bool|array
    {
        foreach ($this->childrens as $modelo_children=>$namespace){
            $valida = (new validaciones())->verifica_eliminacion_children(
                id:$id,modelo_base:  $this,modelo_children:  $modelo_children,namespace:  $namespace);
            if (errores::$error) {
                return $this->error->error(mensaje:'Error al validar children', data:$valida);
            }
        }
        return true;
    }

}

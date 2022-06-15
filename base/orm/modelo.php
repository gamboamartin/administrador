<?php
namespace base\orm;
use gamboamartin\errores\errores;
use JsonException;
use models\adm_seccion;
use models\seccion;
use models\usuario;
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
     * @param array $columnas_extra
     * @param array $extension_estructura
     * @param array $no_duplicados
     * @param array $renombres
     * @param array $sub_querys
     * @param array $tipo_campos
     * @param bool $validation
     */
    public function __construct(PDO $link, string $tabla, bool $aplica_bitacora = false, bool $aplica_seguridad = false,
                                bool $aplica_transaccion_inactivo = true, array $campos_encriptados = array(),
                                array $campos_obligatorios= array(), array $columnas = array(),
                                array $columnas_extra = array(), array $extension_estructura = array(),
                                array $no_duplicados = array(), array $renombres = array(),
                                array $sub_querys = array(), array $tipo_campos = array(), bool $validation = false){

        /**
         * REFCATORIZAR
         */


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
        $this->campos_encriptados = $campos_encriptados;

        if(isset($_SESSION['usuario_id'])){
            $this->usuario_id = (int)$_SESSION['usuario_id'];
        }
        if($tabla !=='') {

            $data = (new columnas())->obten_columnas(modelo:$this, tabla_original: $tabla);
            if (errores::$error) {
                $error = $this->error->error(mensaje: 'Error al obtener columnas de '.$tabla, data: $data);
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
     * P INT ERRROREV P ORDER
     * @param bool $reactiva
     * @return array
     * @throws JsonException
     */
    public function activa_bd(bool $reactiva = false): array{ //FIN

        if($this->registro_id <= 0){
            return $this->error->error(mensaje: 'Error id debe ser mayor a 0 en '.$this->tabla,data: $this->registro_id);
        }

        $data_activacion = (new activaciones())->init_activa(modelo:$this, reactiva: $reactiva);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al generar datos de activacion '.$this->tabla,
                data:$data_activacion);
        }

        $transaccion = (new bitacoras())->ejecuta_transaccion(tabla: $this->tabla,funcion: __FUNCTION__, modelo: $this,
            registro_id: $this->registro_id,sql: $data_activacion->consulta);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al EJECUTAR TRANSACCION en '.$this->tabla,data:$transaccion);
        }

        return array('mensaje'=>'Registro activado con éxito en '.$this->tabla, 'registro_id'=>$this->registro_id);
    }

    /**
     * PARAMS ORDER P INT
     * Aplica status = a activo a todos los elementos o registros de una tabla
     * @return array
     */
    public function activa_todo(): array
    {
        $this->transaccion = 'UPDATE';
        $consulta = "UPDATE " . $this->tabla . " SET status = 'activo'  ";

        $resultado = $this->ejecuta_sql(consulta: $consulta);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: $resultado);
        }

        return array('mensaje'=>'Registros activados con éxito','sql'=>$this->consulta);
    }

    /**
     * P INT ERRORREV
     * inserta un registro por registro enviado
     * @return array|stdClass con datos del registro insertado
     * @throws JsonException
     * @internal  $this->valida_campo_obligatorio();
     * @internal  $this->valida_estructura_campos();
     * @internal  $this->asigna_data_user_transaccion();
     * @internal  $this->bitacora($this->registro,__FUNCTION__,$consulta);
     * @uses  todo el sistema
     * @example
     *      $entrada_modelo->registro = array('tipo_entrada_id'=>1,'almacen_id'=>1,'fecha'=>'2020-01-01',
     *          'proveedor_id'=>1,'tipo_proveedor_id'=>1,'referencia'=>1,'tipo_almacen_id'=>1);
     * $resultado = $entrada_modelo->alta_bd();
     *
     */
    public function alta_bd(): array|stdClass{
        $this->status_default = 'activo';
        $registro = (new inicializacion())->registro_ins(campos_encriptados:$this->campos_encriptados,
            registro: $this->registro,status_default: $this->status_default, tipo_campos: $this->tipo_campos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar registro ', data: $registro);
        }
        $this->registro = $registro;

        $valida = (new val_sql())->valida_base_alta(campos_obligatorios: $this->campos_obligatorios, modelo: $this,
            no_duplicados: $this->no_duplicados, registro: $registro,tabla:  $this->tabla,
            tipo_campos: $this->tipo_campos);
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

        return $data;
    }

    /**
     * P ORDER P INT
     * @param array $registro Registro con datos para la insersion
     * @return array|stdClass
     * @throws JsonException
     */
    public function alta_registro(array $registro):array|stdClass{ //FIN
        $this->registro = $registro;

        $r_alta  = $this->alta_bd();
        if(errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta registro', data: $r_alta);
        }
        return $r_alta;
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

        $result = $this->ejecuta_consulta(consulta: $sql, campos_encriptados: $this->campos_encriptados);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al ejecutar sql',data: $result);
        }

        return (int)$result->registros[0]['total_registros'];

    }

    /**
     * FULL
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
            return $this->error->error(mensaje:'Error al ejecutar sql',data:$sentencia_env);
        }
        $data = new stdClass();
        $data->where = $where;
        $data->sentencia = $sentencia_env;
        return $data;
    }



    /**
     * PHPUNIT
     * @return array
     * @throws JsonException
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
        $transaccion = (new bitacoras())->ejecuta_transaccion(tabla: $this->tabla,funcion: __FUNCTION__,
            modelo: $this,registro_id:  $this->registro_id);
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
    {

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
     *
     * @return array con datos del registro eliminado
     * @throws errores Si $id < 0
     * @throws errores definidas en internals
     * @throws errores si no existe registro*@throws \JsonException
     * @throws JsonException
     * @example
     *      $registro = $this->modelo->elimina_bd($this->registro_id);
     *
     * @internal  $this->validacion->valida_transaccion_activa($this, $this->aplica_transaccion_inactivo, $this->registro_id, $this->tabla);
     * @internal  $this->obten_data();
     * @internal  $this->ejecuta_sql();
     * @internal  $this->bitacora($registro_bitacora,__FUNCTION__,$consulta);
     * @uses  todo el sistema
     */
    public function elimina_bd(int $id): array{
        if($id <= 0){
            return  $this->error->error(mensaje: 'El id no puede ser menor a 0 en '.$this->tabla, data: $id);
        }
        $this->registro_id = $id;

        $registro = $this->registro(registro_id: $this->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro' .$this->tabla, data: $registro);

        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $this->aplica_transaccion_inactivo, registro:  $registro,
            registro_id:  $this->registro_id, tabla:  $this->tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al validar transaccion activa en' .$this->tabla,data: $valida);

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

        $elimina = (new dependencias())->aplica_eliminacion_dependencias(
            desactiva_dependientes:$this->desactiva_dependientes,link: $this->link,
            models_dependientes: $this->models_dependientes,registro_id: $this->registro_id,tabla: $this->tabla);
        if (errores::$error) {
            return $this->error->error(mensaje:'Error al eliminar dependiente', data:$elimina);
        }

        $resultado = $this->ejecuta_sql(consulta: $this->consulta);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql en '.$this->tabla, data:$resultado);
        }
        $bitacora = (new bitacoras())->bitacora(consulta: $consulta, funcion: __FUNCTION__,modelo: $this, registro: $registro_bitacora);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar bitacora de '.$this->tabla, data: $bitacora);
        }

        return array('mensaje'=>'Registro eliminado con éxito', 'registro_id'=>$id);

    }

    /**
     * P INT P ORDER
     * @return string[]
     * @throws JsonException
     */
    public function elimina_con_filtro_and(): array{
        if(count($this->filtro) === 0){
            return $this->error->error('Error no existe filtro', $this->filtro);
        }

        $result = $this->filtro_and(filtro: $this->filtro);
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

        return $dels;

    }

    /**
     * PHPUNIT
     * @return string[]
     */
    public function elimina_todo(): array
    {
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
            return $this->error->error(mensaje: 'Error al contar registros',data: $resultado);
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
        if(isset($compare_1[$key], $compare_2[$key])) {
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
     * @param bool $aplica_seguridad
     * @param array $columnas columnas a mostrar en la consulta, si columnas = array(), se muestran todas las columnas
     * @param array $columnas_by_table arreglo para obtener los campos especificos de una tabla, si esta seteada,
     * no aplicara las columnas tradicionales
     * @param bool $columnas_en_bruto si true se trae las columnas sion renombrar y solo de la tabla seleccionada
     * @param array $filtro array('tabla.campo'=>'value'=>valor,'tabla.campo'=>'campo'=>tabla.campo);
     * @param array $filtro_especial
     *          arreglo con condiciones especiales $filtro_especial[0][tabla.campo]= array('operador'=>'<','valor'=>'x','comparacion'=>'AND OR')
     * @param array $filtro_extra
     * @param array $filtro_fecha
     * @param array $filtro_rango
     *                  Opcion1.- $filtro_rango['tabla.campo'] = array('valor1'=>'valor','valor2'=>'valor')
     * @param array $group_by
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param int $limit numero de registros a mostrar, 0 = sin limite
     * @param array $not_in
     * @param int $offset numero de registros de comienzo de datos
     * @param array $order array('tabla.campo'=>'ASC');
     * @param string $sql_extra
     * @param string $tipo_filtro string numeros o textos
     * @return array|stdClass
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
    public function filtro_and(bool $aplica_seguridad = true, array $columnas =array(),
                               array $columnas_by_table = array(), bool $columnas_en_bruto = false,
                               array $filtro=array(), array $filtro_especial= array(), array $filtro_extra = array(),
                               array $filtro_fecha = array(), array $filtro_rango = array(), array $group_by=array(),
                               array $hijo = array(), int $limit=0,  array $not_in = array(), int $offset=0,
                               array $order = array(), string $sql_extra = '',
                               string $tipo_filtro='numeros'): array|stdClass{

        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }

        if($this->aplica_seguridad && $aplica_seguridad) {
            $filtro = array_merge($filtro, $this->filtro_seguridad);
        }

        if($limit < 0){
            return $this->error->error(mensaje: 'Error limit debe ser mayor o igual a 0  con 0 no aplica limit',
                data: $limit);
        }

        $sql = $this->genera_sql_filtro(columnas: $columnas, columnas_by_table:$columnas_by_table,
            columnas_en_bruto:$columnas_en_bruto, filtro:  $filtro, filtro_especial: $filtro_especial,
            filtro_extra:  $filtro_extra,filtro_rango:  $filtro_rango, group_by:  $group_by, limit:  $limit,
            not_in: $not_in, offset:  $offset, order: $order, sql_extra:  $sql_extra,tipo_filtro:  $tipo_filtro,
            filtro_fecha:  $filtro_fecha);

        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al maquetar sql',data:$sql);
        }

        $result = $this->ejecuta_consulta(consulta:$sql,campos_encriptados: $this->campos_encriptados, hijo: $hijo);
        if(errores::$error){
            return  $this->error->error(mensaje:'Error al ejecutar sql',data:$result, params: get_defined_vars());
        }

        return $result;
    }


    /**
     * FULL
     * @param array $columnas columnas inicializadas a mostrar a peticion en resultado SQL
     * @param array $columnas_by_table
     * @param array $filtro Filtro en forma filtro[campo] = 'value filtro'
     * @param array $hijo Arreglo con los datos para la obtencion de datos dependientes de la estructura o modelo
     * @return array|stdClass
     */
    public function filtro_or(array $columnas = array(), array $columnas_by_table = array(),
                              bool $columnas_en_bruto = false, array $filtro = array(),
                              array $hijo = array()):array|stdClass{

        $consulta = $this->genera_consulta_base(columnas: $columnas, columnas_by_table: $columnas_by_table,
            columnas_en_bruto: $columnas_en_bruto, extension_estructura:  $this->extension_estructura,
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

        $result = $this->ejecuta_consulta(consulta:$consulta, campos_encriptados: $this->campos_encriptados, hijo: $hijo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql',data: $result, params: get_defined_vars());
        }

        return $result;
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
    private function genera_sql_filtro(array $columnas, array $columnas_by_table, bool $columnas_en_bruto,
                                       array $filtro, array $filtro_especial, array $filtro_extra,
                                       array $filtro_rango, array $group_by, int $limit, array $not_in, int $offset,
                                       array $order, string $sql_extra, string $tipo_filtro,
                                       array $filtro_fecha = array()): array|string
    {
        $verifica_tf = (new where())->verifica_tipo_filtro(tipo_filtro: $tipo_filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar tipo_filtro',data: $verifica_tf);
        }
        $consulta = $this->genera_consulta_base(columnas: $columnas, columnas_by_table:$columnas_by_table,
            columnas_en_bruto:$columnas_en_bruto, extension_estructura:  $this->extension_estructura,
            renombradas:  $this->renombres);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar sql',data: $consulta);
        }

        $complemento_sql = (new filtros())->complemento_sql(filtro:  $filtro, filtro_especial: $filtro_especial,
            filtro_extra: $filtro_extra, filtro_rango: $filtro_rango, group_by: $group_by, limit: $limit,
            modelo: $this, not_in: $not_in, offset:  $offset,order:  $order, sql_extra: $sql_extra,
            tipo_filtro: $tipo_filtro, filtro_fecha:  $filtro_fecha);

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
     * PRUEBAS FINALIZADAS
     * @param array $registro
     * @param int $id
     * @return array
     * @throws JsonException
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
     * @throws errores Si $limit < 0
     * @throws errores $this->registro_upd vacio
     * @throws \JsonException
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

        $bitacora = (new bitacoras())->bitacora(consulta: $consulta, funcion: __FUNCTION__, modelo: $this,
            registro: $this->registro_upd);
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
     * @throws JsonException
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

        return array('mensaje'=>'Registros modificados con exito',$data);

    }

    /**
     * PHPUNIT
     * @param array $registro
     * @param int $id
     * @return array
     * @throws JsonException
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
     *
     * Devuelve un array con el registro buscado por this->registro_id del modelo
     * @version 1.11.8
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
    public function obten_data(array $columnas = array(), array $extension_estructura = array(),
                               array $hijo= array()): array{
        $this->row = new stdClass();
        if($this->registro_id < 0){
            return  $this->error->error(mensaje: 'Error el id debe ser mayor a 0 en el modelo '.$this->tabla,
                data: $this->registro_id);
        }
        if(count($extension_estructura) === 0){
            $extension_estructura = $this->extension_estructura;
        }
        $resultado = $this->obten_por_id(columnas:  $columnas, extension_estructura: $extension_estructura,
            hijo: $hijo);

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

        $resultado = $this->filtro_and(aplica_seguridad: $aplica_seguridad, filtro: $filtro, limit: 1,
            order: $this->order);
        if(errores::$error){
            return $this->error->error('Error al obtener datos',$resultado);
        }
        if((int)$resultado['n_registros'] === 0){
            return array();
        }
        return $resultado['registros'][0];

    }

    /**
     * FULL
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
    private function obten_por_id(array $columnas = array(),array $columnas_by_table = array(),
                                  bool $columnas_en_bruto = false, array $extension_estructura= array(),
                                  array $hijo = array()):array|stdClass{
        if($this->registro_id < 0){
            return  $this->error->error(mensaje: 'Error el id debe ser mayor a 0',data: $this->registro_id);
        }
        if(count($extension_estructura)===0){
            $extension_estructura = $this->extension_estructura;
        }
        $tabla = $this->tabla;

        $consulta = $this->genera_consulta_base(columnas: $columnas, columnas_by_table: $columnas_by_table,
            columnas_en_bruto: $columnas_en_bruto, extension_estructura: $extension_estructura,
            renombradas:  $this->renombres);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar consulta base',data:  $consulta);
        }

        $where = " WHERE $tabla".".id = $this->registro_id ";
        $consulta .= $where;

        $result = $this->ejecuta_consulta(consulta: $consulta, campos_encriptados: $this->campos_encriptados, hijo: $hijo);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar sql', data: $result);
        }
        return $result;
    }

    /**
     * P INT P ORDER ERROREV
     * Obtiene todos los registros de un modelo
     * @param string $sql_extra
     * @param string $group_by
     * @param array $columnas
     * @param bool $aplica_seguridad Si aplica seguridad se integra usuario_permitido_id el cual debe existir en los
     * registros
     * @param int $limit
     * @return array|stdClass conjunto de registros obtenidos
     * @example
     *      $es_referido = $controlador->directiva->checkbox(4,'inactivo','Es Referido',true,'es_referido');
     *
     * @uses  TODO EL SISTEMA
     */
    public function obten_registros(bool $aplica_seguridad = false, array $columnas = array(),
                                    bool $columnas_en_bruto = false, array $group_by = array(), int $limit = 0,
                                    bool $return_objects = false, string $sql_extra=''): array|stdClass{

        /**
         * RAFACTORIZAR
         */


        $group_by_sql = (new params_sql())->group_by_sql(group_by: $group_by);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar group_by', data: $group_by_sql);
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

        $order_sql = (new params_sql())->order_sql(order: $this->order);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar order', data: $order_sql);
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

        $consulta_base = $this->genera_consulta_base(columnas: $columnas, columnas_en_bruto: $columnas_en_bruto,
            extension_estructura: $this->extension_estructura, renombradas: $this->renombres);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar consulta', data: $consulta_base);
        }

        $consulta = $consulta_base.' '.$sql_extra.' '.$seguridad.' '.$group_by_sql.' '.$order_sql.' '.$limit_sql.' '.$offset_sql;

        $this->transaccion = 'SELECT';
        $result = $this->ejecuta_consulta(consulta: $consulta, campos_encriptados: $this->campos_encriptados);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar consulta', data: $result);
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
     */
    public function obten_registros_activos(
        array $order = array(), array $filtro= array(), array $hijo = array()):array|stdClass{

        $filtro[$this->tabla.'.status'] = 'activo';
        $r_data = $this->filtro_and(filtro: $filtro, hijo: $hijo,order: $order);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al filtrar", data: $r_data);
        }

        return $r_data;
    }

    /**
     * FULL
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
     *
     * Funcion que regresa en forma de array un registro de una estructura de datos del registro_id unico de dicha
     * estructura
     * @version 1.15.9
     * @param int $registro_id $id Identificador del registro
     * @param array $columnas columnas a obtener del registro
     * @param array $hijo configuracion para asignacion de un array al resultado de un campo foráneo
     * @param array $extension_estructura arreglo con la extension de una estructura para obtener datos de foraneas
     * a configuracion
     * @return array
     */
    public function registro(int $registro_id, array $columnas = array(), array $extension_estructura = array(),
                             array $hijo = array()):array{
        if($registro_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $registro_id debe ser mayor a 0',
                data: $registro_id);
        }
        $this->registro_id = $registro_id;
        $registro = $this->obten_data(columnas: $columnas, extension_estructura: $extension_estructura, hijo: $hijo);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener registro',data: $registro);
        }

        return $registro;
    }

    /**
     *
     * Obtiene los registros de una tabla
     * @param array $columnas
     * @param bool $aplica_seguridad
     * @param int $limit
     * @param array $order
     * @return array
     */
    public function registros(array $columnas = array(), bool $aplica_seguridad = false, int $limit = 0, array $order = array()):array{

        $this->order = $order;
        $resultado =$this->obten_registros(aplica_seguridad:$aplica_seguridad,  columnas:$columnas, limit: $limit);

        if(errores::$error){
            return $this->error->error('Error al obtener registros activos',$resultado);
        }
        $this->registros = $resultado->registros;
        return $this->registros;
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
     * P ORDER P INT ERROREV
     * @param string $seccion
     * @return array|int
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
     * FULL
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

        $columnas = (new sumas())->columnas_suma($campos);
        if(errores::$error){
            return $this->error->error('Error al agregar columnas',$columnas);
        }

        $filtro_sql = (new where())->genera_and(columnas_extra: $this->columnas_extra, filtro: $filtro);
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

        $resultado = $this->ejecuta_consulta(consulta: $consulta, campos_encriptados: $this->campos_encriptados);
        if(errores::$error){
            return $this->error->error('Error al ejecutar sql',$resultado);
        }

        return $resultado->registros[0];
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

}

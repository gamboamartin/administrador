<?php
namespace base\orm;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;
use Throwable;

class bitacoras{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     *
     * La funcion aplica una bitacora generando un modelo, consultando un registro con referencia al modelo e inserta una transaccion.
     * Retornando los datos de la transaccion
     * @param string $consulta almacena la consulta que se va a realizar a la base de datos
     * @param string $funcion almacena la funcion que se va a utilizar
     * @param modelo $modelo Modelo a generar
     * @param int $registro_id contiene el identificador del registro
     * @param string $tabla almacena el nombre de la tabla con la que se va a interactuar
     * @return array
     * @version 1.495.49
     */
    private function aplica_bitacora(
        string $consulta, string $funcion, modelo $modelo, int $registro_id, string $tabla): array
    {

        if($registro_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $registro_id debe ser mayor a 0',
                data: $registro_id);
        }
        $model = $modelo->genera_modelo(modelo: $tabla, namespace_model: $modelo->NAMESPACE);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo'.$tabla,data: $model);
        }

        $registro_bitacora = $model->registro(registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener registro de '.$tabla,data:$registro_bitacora);
        }

        $bitacora = $this->bitacora(consulta: $consulta, funcion: $funcion, modelo: $modelo,
            registro: $registro_bitacora);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al insertar bitacora de '.$tabla,data:$bitacora);
        }
        return $bitacora;
    }

    /**
     *
     * Devuelve un arreglo que contiene los campos necesarios para un registro en la bitacora
     *
     * @param string $consulta es una cadena que indica la peticion en sql, que se realizo a la base de datos que
     * realiza la accion que se utilizo
     * @param string $funcion es una cadena que indica que funcion o accion se utilizo
     * @param modelo $modelo Modelo en ejecucion
     * @param array $registro es un arreglo que indica cual fue el registro afectado por la accion
     * @param array $seccion
     * @return array
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
     *
     * @version 1.466.49
     */
    private function asigna_registro_para_bitacora(string $consulta,string $funcion, modelo $modelo,
                                                   array $registro, array $seccion): array
    {

        $keys = array('adm_seccion_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar seccion ',data:$valida);
        }

        $keys = array('usuario_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $_SESSION);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar SESSION ',data:$valida);
        }

        if($funcion === ''){
            return $this->error->error(mensaje: 'Error $funcion no puede venir vacia',data:$funcion);
        }
        if($consulta === ''){
            return $this->error->error(mensaje: 'Error $consulta no puede venir vacia',data:$consulta);
        }
        if($modelo->registro_id<=0){
            return $this->error->error(mensaje: 'Error el id de $this->registro_id no puede ser menor a 0',
                data:$modelo->registro_id);
        }
        $registro_data['adm_seccion_id'] = $seccion['adm_seccion_id'];
        $registro_data['status'] = 'activo';
        try {
            $registro_data['registro'] = json_encode($registro, JSON_THROW_ON_ERROR);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al generar json de bitacora', data:$e);
        }
        $registro_data['adm_usuario_id'] = $_SESSION['usuario_id'];
        $registro_data['transaccion'] = $funcion;
        $registro_data['sql_data'] = $consulta;
        $registro_data['valor_id'] = $modelo->registro_id;

        return $registro_data;
    }

    /**
     *
     * Inserta una transaccion de bitacora
     * @param array $registro es un arreglo que indica cual fue el registro afectado por la accion
     * @param string $funcion es una cadena que indica que funcion o accion se utilizo
     * @param string $consulta es una cadena que indica la peticion en sql, que se realizo a la base de datos que
     * realiza la accion que se utilizo
     * @return array resultados de inserciones de bitacora
     * @internal  $this->genera_bitacora($registro,$funcion, $consulta)
     * @uses   modelo
     * @example
     *      $registro_bitacora = $this->obten_data();
     * if(isset($registro_bitacora['error'])){
     * return $this->error->error('Error al obtener registro',
     * __CLASS__,$registro_bitacora);
     * $bitacora = $this->bitacora($registro_bitacora,__FUNCTION__,$consulta );
     * @version 1.495.49
     */
    public function bitacora(string $consulta, string $funcion, modelo $modelo, array $registro): array
    {
        $bitacora = array();
        if($modelo->aplica_bitacora){

            $data_ns = $this->clase_namespace(tabla: $modelo->tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar namespace modelo', data: $data_ns);
            }

            $valida = $this->valida_data_bitacora(
                consulta: $consulta, data_ns: $data_ns, funcion: $funcion,modelo:  $modelo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
            }

            $r_bitacora = $this->genera_bitacora(consulta:  $consulta, funcion: $funcion, modelo: $modelo,
                registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al generar bitacora en '.$data_ns->tabla,data:$r_bitacora);
            }
            $bitacora = $r_bitacora;
        }

        return $bitacora;
    }

    /**
     *
     * agrega el nombre de la tabla y nombre de la clase a var $data
     * @param string $tabla nombre de la tabla
     * @return stdClass|array objeto $data que contiene nombre de la tabla y nombre de la clase
     * @version 1.421.48
     */

    private function clase_namespace(string $tabla): stdClass|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia',data:  $tabla);
        }
        $namespace = 'models\\';
        $tabla = str_replace($namespace,'',$tabla);

        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia o mal escrita',data:  $tabla);
        }

        $data = new stdClass();
        $data->tabla = $tabla;
        $data->clase = $namespace.$tabla;

        return$data;
    }

    /**
     * La funcion registra los datos de la tabla y clase
     * @param string $tabla almacena el nombre correspondiente a la tabla con la que se va a interactuar
     * @return array|stdClass
     * @throws errores si surge un error al componer el namespace modelo
     * @throws errores la variable que almacena el nombre de la tabla tiene contenido vacio
     * @throws  errores la clase consultada para componer el namespace modelo no existe
     * @version 1.422.48
     */

    private function data_ns_val(string $tabla): array|stdClass
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia',data:  $tabla);
        }
        $data_ns = $this->clase_namespace(tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar namespace modelo', data: $data_ns);
        }

        if($data_ns->tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla no puede venir vacio',data: $data_ns->tabla);
        }

        return $data_ns;
    }

    /**
     *
     * La funcion ejecuta una transaccion y realiza una consulta para obtener la bitacora involucrada
     * @param string $tabla contiene el nombre de la tabla con la que se va a interactuar
     * @param string $funcion contiene el nombre el nombre de la funcion que se va a aplicar
     * @param modelo $modelo Modelo en ejecucion
     * @param int $registro_id contiene el identificador del registro a consultar
     * @param string $sql contiene la peticion que se realizara a la base de datos
     * @return array
     * @version 1.495.49
     */
    public function ejecuta_transaccion(
        string $tabla, string $funcion,  modelo $modelo, int $registro_id , string $sql = ''):array{
        $consulta =trim($sql);
        if($sql === '') {
            $consulta = $modelo->consulta;
        }
        if($modelo->consulta === ''){
            return $this->error->error(mensaje: 'La consulta no puede venir vacia del modelo '.$modelo->tabla,
                data: $modelo->consulta);
        }
        $resultado = $modelo->ejecuta_sql(consulta: $consulta);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al ejecutar sql en '.$tabla,data:$resultado);
        }
        $bitacora = $this->aplica_bitacora(consulta: $consulta, funcion: $funcion,modelo: $modelo,
            registro_id:  $registro_id, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al insertar bitacora en '.$tabla,data:$bitacora);
        }

        return $bitacora;
    }

    /**
     *
     * Inserta un registro de bitacora de la tabla afectada
     * @param string $consulta el la sentencia sql de la funcion aplicada
     * @param string $funcion es la funcion que se aplica sobre el registro
     * @param modelo $modelo
     * @param array $registro es el registro afectado por la accion del sistema
     * @return array|stdClass con registro de insersion de bitacora
     * @example
     *     $r_bitacora = $this->genera_bitacora($registro,$funcion, $consulta);
     * @uses modelo_basico->bitacora
     * @internal $this->maqueta_data_bitacora($registro,$funcion, $consulta);
     * @internal $bitacora_modelo->alta_bd();
     * @version 1.491.49
     */
    private function genera_bitacora(string $consulta, string $funcion, modelo $modelo, array $registro): array|stdClass{

        $val = $this->valida_bitacora(consulta:$consulta,funcion:  $funcion, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar valores', data: $val);
        }

        $bitacora_modelo = (new adm_bitacora($modelo ->link));
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener bitacora',data:$bitacora_modelo);
        }

        $bitacora_modelo->registro = $this->maqueta_data_bitacora(consulta:  $consulta, funcion: $funcion,
            modelo: $modelo, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener MAQUETAR REGISTRO PARA BITACORA',
                data:$bitacora_modelo->registro);
        }
        $r_bitacora = $bitacora_modelo->alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje:'Error al insertar bitacora',data:$r_bitacora);
        }
        return $r_bitacora;
    }

    /**
     *
     * Genera un array para insertarlo en la bitacora
     *
     * @param array $registro registro afectado
     * @param string $funcion funcion de modelo
     * @param string $consulta sql ejecutado
     *
     * @return array registro afectado
     * @throws errores definidos en internal*@throws JsonException

     * @example
     *      $this->maqueta_data_bitacora($registro,$funcion, $consulta);
     *
     * @uses modelo_basico->genera_bitacora
     * @internal $this->obten_seccion_bitacora();
     * @internal $this->asigna_registro_para_bitacora($seccion_menu,$registro,$funcion, $consulta);
     * @version 1.466.49
     *
     */
    private function maqueta_data_bitacora(string $consulta, string $funcion, modelo $modelo, array $registro):array{


        $val = $this->valida_bitacora(consulta: $consulta,funcion:  $funcion, modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar valores', data: $val);
        }

        $seccion_menu = $this->obten_seccion_bitacora(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener seccion', data:$seccion_menu);
        }

        $registro = $this->asigna_registro_para_bitacora(consulta: $consulta, funcion: $funcion,
            modelo: $modelo, registro: $registro, seccion: $seccion_menu);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener MAQUETAR REGISTRO PARA BITACORA', data:$registro);
        }


        return $registro;
    }

    /**
     *
     * Funcion que obtiene el registro de seccion menu para aplicacion de una bitacora
     * @example
     *  $seccion_menu = $this->obten_seccion_bitacora();
     * @return array registro de seccion menu encontrado
     * @throws errores definidos en filtro and
     * @throws errores si no se encontro registro
     * @internal  $seccion_menu_modelo->filtro_and($filtro);
     * @uses modelo_basico->maqueta_data_bitacora
     * @version 1.463.49
     */
    private function obten_seccion_bitacora(modelo $modelo): array
    {

        $data_ns = $this->data_ns_val(tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar namespace modelo', data: $data_ns);
        }

        $seccion_menu_modelo = (new adm_seccion(link: $modelo->link));
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar modelo',data:$seccion_menu_modelo);
        }

        $filtro['adm_seccion.descripcion'] = $data_ns->tabla;
        $r_seccion_menu = $seccion_menu_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener seccion menu',data:$r_seccion_menu);
        }
        if((int)$r_seccion_menu->n_registros === 0){
            return $this->error->error(mensaje:'Error no existe la seccion menu',data:$r_seccion_menu);
        }
        return $r_seccion_menu->registros[0];
    }

    /**
     * La funcion Valida los elementos previos a la ejecucion de un SQL
     * @param string $consulta almacena la consulta a la base de datos
     * @param string $funcion almacena la funcion que entrara en interaccion con los datos
     * @param modelo $modelo es el modelo con el que se va a trabajar
     * @return bool|array
     * @throws errores la variable que indicaba la funcion llego vacia
     * @throws  errores la variable que indica la consulta llego vacia
     * @throws  errores el id del registro del modelo es menor o igual a 0
     * @version 1.440.48
     */
    private function val_bitacora(string $consulta, string $funcion, modelo $modelo): bool|array
    {
        if($funcion === ''){
            return $this->error->error(mensaje:'Error $funcion no puede venir vacia',data:$funcion);
        }
        if($consulta === ''){
            return $this->error->error(mensaje:'Error $consulta no puede venir vacia',data:$consulta);
        }
        if($modelo->registro_id<=0){
            return $this->error->error(mensaje:'Error el id de $this->registro_id no puede ser menor a 0',
                data:$modelo->registro_id);
        }
        return true;
    }

    public function valida_bitacora(string $consulta, string $funcion, modelo $modelo): bool|array
    {
        $keys = array('usuario_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $_SESSION);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar SESSION ',data:$valida);
        }

        $data_ns = $this->data_ns_val(tabla: $modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar namespace modelo', data: $data_ns);
        }
        $val = $this->val_bitacora(consulta: $consulta,funcion: $funcion,modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar valores', data: $val);
        }

        return true;
    }

    /**
     * Valida los elementos de una bitacora
     * @param string $consulta Sql a guardar
     * @param stdClass $data_ns Datos de servicio
     * @param string $funcion Funcion de ejecucion
     * @param modelo $modelo Modelo transaccion
     * @return bool|array
     * @version 1.495.49
     */
    private function valida_data_bitacora(string $consulta, stdClass $data_ns, string $funcion, modelo $modelo): bool|array
    {
        $keys = array('tabla');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $data_ns);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al al validar data_ns',data:$valida);
        }
        if($data_ns->tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla no puede venir vacio',data: $data_ns->tabla);
        }
        if($funcion === ''){
            return $this->error->error(mensaje:'Error $funcion no puede venir vacia',data:$funcion);
        }
        if($consulta === ''){
            return $this->error->error(mensaje:'Error $consulta no puede venir vacia',data:$consulta);
        }
        if($modelo->registro_id<=0){
            return $this->error->error(mensaje:'Error el id de $this->registro_id no puede ser menor a 0',
                data: $modelo->registro_id);
        }
        return true;
    }



}

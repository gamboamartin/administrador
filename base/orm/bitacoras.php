<?php
namespace base\orm;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use JsonException;
use models\adm_bitacora;
use models\adm_seccion;
use models\bitacora;
use models\seccion;
use stdClass;

class bitacoras{
    private errores $error;
    private validaciones $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * P INT ERRORREV P ORDER
     * @param string $tabla
     * @param string $funcion
     * @param string $consulta
     * @param int $registro_id
     * @return array
     * @throws JsonException
     */
    private function aplica_bitacora(string $consulta, string $funcion, modelo $modelo, int $registro_id,
                                     string $tabla): array
    {
        $model = $modelo->genera_modelo(modelo: $tabla);
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
     * P INT P ORDER ERROREV
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
    private function asigna_registro_para_bitacora(string $consulta,string $funcion, modelo $modelo,
                                                   array $registro, array $seccion_menu): array
    {//FIN Y DOC
        if($seccion_menu['seccion_menu_id']<=0){
            return$this->error->error(mensaje: 'Error el id de $seccion_menu[\'seccion_menu_id\'] no puede ser menor a 0',
                data: $seccion_menu['seccion_menu_id']);
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
        $registro_data['seccion_menu_id'] = $seccion_menu['seccion_menu_id'];
        $registro_data['status'] = 'activo';
        $registro_data['registro'] = json_encode($registro, JSON_THROW_ON_ERROR);
        $registro_data['usuario_id'] = $_SESSION['usuario_id'];
        $registro_data['transaccion'] = $funcion;
        $registro_data['sql_data'] = $consulta;
        $registro_data['valor_id'] = $modelo->registro_id;

        return $registro_data;
    }

    /**
     * P INT P ORDER ERRORREV
     * Inserta una transaccion de bitacora
     * @param array $registro es un arreglo que indica cual fue el registro afectado por la accion
     * @param string $funcion es una cadena que indica que funcion o accion se utilizo
     * @param string $consulta es una cadena que indica la peticion en sql, que se realizo a la base de datos que
     * realiza la accion que se utilizo
     * @return array resultados de inserciones de bitacora
     * @throws JsonException
     * @internal  $this->genera_bitacora($registro,$funcion, $consulta)
     * @uses   modelo
     * @example
     *      $registro_bitacora = $this->obten_data();
     * if(isset($registro_bitacora['error'])){
     * return $this->error->error('Error al obtener registro',
     * __CLASS__,$registro_bitacora);
     * $bitacora = $this->bitacora($registro_bitacora,__FUNCTION__,$consulta );
     */
    public function bitacora(string $consulta, string $funcion, modelo $modelo, array $registro): array
    {
        $bitacora = array();
        if($modelo->aplica_bitacora){
            $namespace = 'models\\';
            $this->tabla = str_replace($namespace,'',$modelo->tabla);
            $clase = $namespace.$modelo->tabla;
            if($this->tabla === ''){
                return $this->error->error(mensaje: 'Error this->tabla no puede venir vacio',data: $this->tabla);
            }
            if(!class_exists($clase)){
                return $this->error->error(mensaje:'Error no existe la clase '.$clase,data:$clase);
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
            $r_bitacora = $this->genera_bitacora(consulta:  $consulta, funcion: $funcion, modelo: $modelo,
                registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje:'Error al generar bitacora en '.$this->tabla,data:$r_bitacora);
            }
            $bitacora = $r_bitacora;
        }

        return $bitacora;
    }

    /**
     * P INT ERRORREV P ORDER
     * @param string $tabla
     * @param string $funcion
     * @param int $registro_id
     * @param string $sql
     * @return array
     * @throws JsonException
     */
    public function ejecuta_transaccion(string $tabla, string $funcion,  modelo $modelo, int $registro_id = -1, string $sql = ''):array{
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
     * P INT P ORDER ERROREV
     * Inserta un registro de bitacora de la tabla afectada
     * @param array $registro es el registro afectado por la accion del sistema
     * @param string $funcion es la funcion que se aplica sobre el registro
     * @param string $consulta el la sentencia sql de la funcion aplicada
     * @return array con registro de insersion de bitacora
     * @throws errores definidos en internals
     * @throws JsonException
     * @example
     *     $r_bitacora = $this->genera_bitacora($registro,$funcion, $consulta);
     * @uses modelo_basico->bitacora
     * @internal $this->maqueta_data_bitacora($registro,$funcion, $consulta);
     * @internal $bitacora_modelo->alta_bd();
     */
    private function genera_bitacora(string $consulta, string $funcion, modelo $modelo, array $registro): array{
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);
        $clase = $namespace.$modelo->tabla;
        if($modelo->tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla no puede venir vacio', data: $modelo->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error(mensaje:'Error no existe la clase '.$clase,data:$clase);
        }
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
     * P INT P ORDER ERRORREV
     * Genera un array para insertarlo en la bitacora
     *
     * @param array $registro registro afectado
     * @param string $funcion funcion de modelo
     * @param string $consulta sql ejecutado
     *
     * @return array registro afectado
     * @throws errores definidos en internal*@throws JsonException
     * @throws JsonException
     * @example
     *      $this->maqueta_data_bitacora($registro,$funcion, $consulta);
     *
     * @uses modelo_basico->genera_bitacora
     * @internal $this->obten_seccion_bitacora();
     * @internal $this->asigna_registro_para_bitacora($seccion_menu,$registro,$funcion, $consulta);
     */
    private function maqueta_data_bitacora(string $consulta, string $funcion, modelo $modelo, array $registro):array{
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);
        $clase = $namespace.$modelo->tabla;
        if($modelo->tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla no puede venir vacio',data: $modelo->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error(mensaje:'Error no existe la clase '.$clase,data:$clase);
        }
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
        $seccion_menu = $this->obten_seccion_bitacora(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener seccion', data:$seccion_menu);
        }

        $registro = $this->asigna_registro_para_bitacora(consulta: $consulta, funcion: $funcion,
            modelo: $modelo, registro: $registro, seccion_menu: $seccion_menu);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener MAQUETAR REGISTRO PARA BITACORA', data:$registro);
        }


        return $registro;
    }

    /**
     * P INT P ORDER ERROREV
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
    private function obten_seccion_bitacora(modelo $modelo): array
    {
        $namespace = 'models\\';
        $modelo->tabla = str_replace($namespace,'',$modelo->tabla);
        $clase = $namespace.$modelo->tabla;
        if($modelo->tabla === ''){
            return $this->error->error(mensaje: 'Error this->tabla no puede venir vacio',data: $modelo->tabla);
        }
        if(!class_exists($clase)){
            return $this->error->error(mensaje:'Error no existe la clase '.$clase,data:$clase);
        }

        $seccion_menu_modelo = (new adm_seccion($modelo->link));
        if(errores::$error){
            return $this->error->error(mensaje:'Error al generar modelo',data:$seccion_menu_modelo);
        }

        $filtro['seccion_menu.descripcion'] = $modelo->tabla;
        $r_seccion_menu = $seccion_menu_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener seccion menu',data:$r_seccion_menu);
        }
        if((int)$r_seccion_menu['n_registros'] === 0){
            return $this->error->error(mensaje:'Error no existe la seccion menu',data:$r_seccion_menu);
        }
        return $r_seccion_menu->registros[0];
    }



}

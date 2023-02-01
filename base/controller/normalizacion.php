<?php
namespace base\controller;

use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;


class normalizacion{
    private errores $error;
    private valida_controller $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new valida_controller();
    }



    /**
     * Asigna los elementos de un registro previo a procesar
     * @param controler $controler Controlador de ejecucion
     * @param array $registro Registro a limpiar y validar
     * @version 1.223.37
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-07-30 13:05
     * @return array
     */
    final public function asigna_registro_alta(controler $controler, array $registro): array
    {

        $controler->seccion = trim($controler->seccion);
        if($controler->seccion === ''){
            return $this->error->error(
                mensaje: 'Error $controler->seccion no puede venir vacia',data:  $controler->seccion);
        }

        $registro_r = $this->init_registro(controler: $controler,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar registro', data: $registro_r);
        }

        $registro_ins = $this->procesa_registros_alta(controler: $controler, registro: $registro_r);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al procesar registros',data:  $registro_ins);
        }
        $controler->modelo->registro = $registro_ins;

        return $controler->modelo->registro;
    }



    /**
     * Asigna el valor modo namespace controller a controlador en ejecucion
     * @version 1.88.19
     * @param controler $controler Controlador en ejecucion
     * @return string|array
     */
    final public function clase_model(controler $controler): string|array
    {
        if($controler->seccion === ''){
            return $this->error->error(mensaje: 'Error this->seccion esta vacio',data:  $controler->seccion);
        }
        $namespace = 'models\\';
        $controler->seccion = str_replace($namespace,'',$controler->seccion);
        return $namespace.$controler->seccion;
    }



    /**
     *
     * @param array $filtros_brutos
     * @return array
     */
    final public function genera_filtros_envio(array $filtros_brutos): array
    {
        $filtros = array();
        foreach($filtros_brutos as $campo =>$value){
            if(is_numeric($campo)){
                return $this->error->error('Error el key debe ser un texto',$campo);
            }
            $filtros[$campo] = $value;
        }
        return $filtros;
    }


    /**
     * Genera los datos para ejecutar una transaccion
     * @version 1.83.19
     * @param array $registros Conjunto de datos a parsear
     * @param controler $controler Controlador de ejecucion
     * @return array
     */
    private function genera_registros_envio(controler $controler, array $registros):array{
        $registro_envio = array();
        foreach ($registros as $key=>$value){
            if($key === ''){
                return $this->error->error(mensaje: 'Error la $key no puede venir vacia',data: $key);
            }
            if(is_numeric($key)){
                return $this->error->error(mensaje: 'Error la $key debe ser un string valido',data: $key);
            }
            $key_envio = $this->obten_key_envio(controler:  $controler, key: $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error generar  key', data: $key_envio);
            }
            $registro_envio[$key_envio] = $value;
        }

        return $registro_envio;
    }

    /**
     * Inicializa los elementos de un controller
     * @param controler $controler Controlador en ejecucion
     * @return controler
     * @version 2.10.2
     */
    public function init_controler(controler $controler): controler
    {

        if(isset($_GET['seccion'])){
            $controler->tabla = $_GET['seccion'];
            $controler->seccion = $_GET['seccion'];
        }
        if(isset($_GET['accion'])){
            $controler->accion = $_GET['accion'];
        }
        if(isset($_GET['valor_filtro'])){
            $controler->valor_filtro = $_GET['valor_filtro'];
        }
        if(isset($_GET['campo_filtro'])){
            $controler->campo_filtro = $_GET['campo_filtro'];
        }
        if(isset($_GET['selected'])){
            $controler->selected = $_GET['selected'];
        }
        if(isset($_GET['registro_id'])){
            $controler->registro_id = (int)$_GET['registro_id'];
            $controler->modelo->registro_id = (int)$_GET['registro_id'];
        }
        if(isset($_GET['campo'])){
            $controler->campo = $_GET['campo'];
        }
        if(isset($_GET['campo_resultado'])){
            $controler->campo_resultado = $_GET['campo_resultado'];
        }
        return $controler;
    }

    /**
     * Inicializa y valida los datos de un registro para un alta bd
     * @param controler $controler Controlador en ejecucion
     * @param array $registro Registro a limpiar y validar
     * @return array
     * @version 1.219.37
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-07-30 12:15
     */
    private function init_registro( controler $controler, array $registro): array
    {
        $controler->seccion = trim($controler->seccion);
        if($controler->seccion === ''){
            return $this->error->error(
                mensaje: 'Error $controler->seccion no puede venir vacia',data:  $controler->seccion);
        }

        $clase = $this->name_class(seccion: $controler->seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener name clase', data: $clase);
        }
        $controler->seccion = $clase;

        $valida = $this->validacion->valida_in_alta(clase:  $clase,controler: $controler, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar entrada de datos', data: $valida);
        }

        $registro = $this->limpia_btn_post(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar registro', data: $registro);
        }
        return $registro;
    }

    /**
     * Inicializa los elementos de un registro previo a la actualizacion en base de datos
     * @param controler $controler Controlador en ejecucion
     * @param array $registro Registro a inicializar y ajustar
     * @return array
     * @version 1.269.40
     * @verfuncion 1.1.0
     * @fecha 2022-08-04 14:00
     * @author mgamboa
     */
    final public function init_upd_base(controler $controler, array $registro): array
    {
        $valida = $this->validacion->valida_post_modifica();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar POST',data: $valida);
        }
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error el registro no puede venir vacio',data: $registro);
        }
        if($controler->seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion no puede venir vacia', data: $controler->seccion);
        }

        $controler->registros = $this->procesa_registros_alta(controler:  $controler, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al procesar registros',data: $controler->registros);
        }
        $controler->modelo->registro_id = $controler->registro_id;
        return $controler->registros;
    }


    /**
     * Limpia los nombre comunes de los botones no insertables
     * @param array $registro Registro de post alta
     * @version 1.216.37
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-30-07 11:51
     * @return array
     */
    private function limpia_btn_post(array $registro): array
    {
        if(isset($registro['btn_agrega'])){
            unset($registro['btn_agrega']);
        }
        if(isset($registro['btn_guarda'])){
            unset($registro['btn_guarda']);
        }
        if(isset($registro['btn_action_next'])){
            unset($registro['btn_action_next']);
        }
        return $registro;
    }

    /**
     * Limpia los elementos de los botones
     * @version 1.98.25
     * @return array
     */
    final public function limpia_post_alta(): array
    {
        if(!isset($_POST)){
            $_POST = array();
        }
        if(isset($_POST['btn_agrega'])){
            unset($_POST['btn_agrega']);
        }
        if(isset($_POST['btn_guarda'])){
            unset($_POST['btn_guarda']);
        }
        if(isset($_POST['Enviar'])){
            unset($_POST['Enviar']);
        }
        if(isset($_POST['btn_action_next'])){
            unset($_POST['btn_action_next']);
        }
        return $_POST;
    }

    /**
     * Limpia session registro en proceso
     * @return array
     * @version 1.607.55
     */
    final public function limpia_registro_en_proceso(): array
    {
        if(!isset($_SESSION)){
            $_SESSION = array();
        }
        if(isset($_SESSION['registro_en_proceso'])) {
            unset($_SESSION['registro_en_proceso']);
        }
        return $_SESSION;
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $r_fotos
     * @param string $tabla
     * @param controler $controler
     * @return array
     */
    public function maqueta_data_galeria(controler $controler, array $r_fotos, string $tabla):array{
        if(!isset($r_fotos['registros'])){
            return $this->error->error('Error no existe registros en r_fotos',$r_fotos);
        }
        if(!is_array($r_fotos['registros'])){
            return $this->error->error('Error registros en r_fotos debe ser un array',$r_fotos);
        }
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error tabla no puede venir vacia',$tabla);
        }
        $controler->registros['fotos_cargadas'] = $r_fotos['registros'];
        $controler->registros['tabla'] = $tabla;
        return $controler->registros;
    }


    /**
     * Genera un modelo en forma de namespace
     * @version 1.115.28
     * @param string $seccion Seccion en ejecucion
     * @return string|array
     */
    private function name_class(string $seccion): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion no puede venir vacia',data:  $seccion);
        }
        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        return $namespace.$seccion;
    }

    /**
     *
     * Obtiene el ker de envio reemplazando valores de prefijos de tablas
     * @version 1.57.17
     * @param controler $controler Controlador de ejecucion
     * @param string $key Key a ejecutar cambio
     * @return array|string key parseado
     * @example
     *      $key_envio = $this->obten_key_envio($key);
     * @internal $this->modelo->str_replace_first($this->seccion . '_', '', $key);
     * @uses controler->genera_registros_envio
     */
    private function obten_key_envio(controler $controler, string $key):array|string{
        if($controler->seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion no puede venir vacia', data: $controler->seccion);
        }
        if($key === ''){
            return $this->error->error(mensaje: 'Error la $key no puede venir vacia',data: $key);
        }
        if(is_numeric($key)){
            return $this->error->error(mensaje: 'Error la $key debe ser un string valido',data: $key);
        }
        $pos = strpos($key,$controler->seccion.'_');
        $key_envio = $key;
        if((int)$pos === 0) {

            $key_envio = $controler->modelo->str_replace_first(content: $key, from:$controler->seccion . '_', to: '');
            if(errores::$error){
                return $this->error->error(mensaje: 'Error nal obtener key',data: $key_envio);
            }
        }

        return $key_envio;
    }

    /**
     * Procesa ya ajusta un registro previo a la alta en un modelo
     * @param array $registro Registro en ejecucion y a procesar
     * @param controler $controler Controlador de ejecucion
     * @return array
     * @version 1.122.37
     * @verfuncion 1.1.0
     * @fecha 2022-07-30 12:55
     * @author mgamboa
     *
     */
    private function procesa_registros_alta(controler $controler, array $registro): array{
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error el registro no puede venir vacio',data: $registro);
        }
        if($controler->seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion no puede venir vacia', data: $controler->seccion);
        }

        if(isset($registro['btn_modifica'])){
            unset($registro['btn_modifica']);
        }
        $registros = $this->trim_arreglo(arreglo: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar arreglo',data: $registros);
        }
        $registro_envio = $this->genera_registros_envio(controler: $controler, registros: $registros);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar registro envio',data: $registro_envio);
        }

        if(count($registro_envio) === 0){
            return $this->error->error(mensaje: 'Error no se asignaron registros',data: $registro_envio);
        }


        return $registro_envio;
    }


    /**
     * Limpia los elementos de un arreglo
     * @version 1.56.17
     * @param array $arreglo Arreglo a limpiar
     * @return array
     */
    private function trim_arreglo(array $arreglo): array{
        if(count($arreglo) === 0){
            return $this->error->error(mensaje: 'Error el arreglo no puede venir vacio',data: $arreglo);
        }
        $data = array();
        foreach ($arreglo as $key => $value) {
            if(is_array($value)){
                return $this->error->error(mensaje: 'Error $value debe ser un string',data: array($key,$value));
            }
            if ((string)$value !== '') {
                $data[$key] = trim($value);
            }
        }

        return $data;
    }






}

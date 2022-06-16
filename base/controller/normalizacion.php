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
     * FULL
     * @param array $filtro_default_btn
     * @return array
     */
    private function asigna_filtro_btn_get(array $filtro_default_btn):array{
        $keys = array('tabla','valor_default');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $filtro_default_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error validar filtro_default_btn',data: $valida,
                params: get_defined_vars());
        }

        $_GET['filtro_btn'][$filtro_default_btn['tabla'] . '.id']['campo'] = $filtro_default_btn['tabla'] . '.id';
        $_GET['filtro_btn'][$filtro_default_btn['tabla'] . '.id'] = $filtro_default_btn['valor_default'];
        return $_GET;
    }

    /**
     * P INT P ORDER ERROR
     * @param array $filtro_default_btn
     * @param array $filtro_btn
     * @return array
     */
    private function asigna_filtros(array $filtro_btn, array $filtro_default_btn):array{
        $keys = array('tabla','valor_default');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $filtro_default_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro_default_btn', data: $valida,
                params: get_defined_vars());
        }
        $filtro_btn = $this->determina_filtro_btn(filtro_btn: $filtro_btn, filtro_default_btn: $filtro_default_btn);
        if(errores::$error){
            return $this->error->error('Error al determinar filtro',$filtro_btn);
        }
        $asigna_filtro_get = $this->asigna_filtro_btn_get(filtro_default_btn: $filtro_default_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al determinar filtro',data: $asigna_filtro_get,
                params: get_defined_vars());
        }
        return $filtro_btn;
    }

    /**
     * P INT P ORDER  ERROREV
     * @param controler $controler Controlador de ejecucion
     * @param array $registro
     * @return array
     */
    public function asigna_registro_alta(controler $controler, array $registro): array
    {
        $registro_r = $this->init_registro(controler: $controler,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar registro', data: $registro_r,
                params: get_defined_vars());
        }

        $registro_ins = $this->procesa_registros_alta(controler: $controler, registro: $registro_r);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al procesar registros',data:  $registro_ins,
                params: get_defined_vars());
        }
        $controler->modelo->registro = $registro_ins;

        return $controler->modelo->registro;
    }

    /**
     * P ORDER P INT ERROREV
     * @param array $campo
     * @param string $tabla
     * @param array $filtro_modelado
     * @return array
     */
    private function asigna_valor_filtro_modelado(array $campo,array $filtro_modelado, string $tabla):array{
        foreach($campo as $key=>$data){
            if(is_numeric($key)){
                return $this->error->error(mensaje: 'Error filtro invalido',data: $campo, params: get_defined_vars());
            }
            if(isset($data['value']) && (string)$data['value'] === ''){
                continue;
            }
            $es_sq = false;
            if(isset($data['es_sq']) && $data['es_sq'] === 'activo'){
                $es_sq = true;
            }
            if($es_sq) {
                if(!isset($data['value'])){
                    $data['value'] = '';
                }
                $filtro_modelado[$key]['campo'] = $key;
                $filtro_modelado[$key]['value'] = $data['value'];
                $filtro_modelado[$key]['es_sq'] = true;
            }
            else{
                if(!isset($data['value'])){
                    continue;
                }
                $filtro_modelado[$tabla.'.'.$key]['campo']= $tabla.'.'.$key;
                $filtro_modelado[$tabla.'.'.$key]['value']= $data['value'];
            }
        }
        return $filtro_modelado;
    }

    /**
     * P ORDER P INT PROBADO ERRORREV
     * @param controler $controler
     * @return string|array
     */
    public function clase_model(controler $controler): string|array
    {
        if($controler->seccion === ''){
            return $this->error->error(mensaje: 'Error this->seccion esta vacio',data:  $controler->seccion,
                params: get_defined_vars());
        }
        $namespace = 'models\\';
        $controler->seccion = str_replace($namespace,'',$controler->seccion);
        return $namespace.$controler->seccion;
    }

    /**
     * P INT P ORDER ERROREV
     * @param array $filtro_default_btn
     * @param array $filtro_btn
     * @return array
     */
    private function determina_filtro_btn(array $filtro_btn, array $filtro_default_btn):array{
        $keys = array('tabla','valor_default');
        $valida = $this->validacion->valida_existencia_keys( keys:$keys, registro: $filtro_default_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar filtro_default_btn', data: $valida,
                params: get_defined_vars());
        }
        $filtro_btn[$filtro_default_btn['tabla'] . '.id']['campo'] = $filtro_default_btn['tabla'] . '.id';
        $filtro_btn[$filtro_default_btn['tabla'] . '.id'] = $filtro_default_btn['valor_default'];
        return $filtro_btn;
    }

    /**
     * P INT P ORDER ERROREV
     * @param array $filtro
     * @param array $filtro_btn
     * @param controler $controler
     * @return array
     */
    private function determina_filtro_modelado(controler $controler, array $filtro, array $filtro_btn):array{

        $filtro_modelado = $this->genera_filtros_modelados(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro_modelado,
                params: get_defined_vars());
        }

        $filtro_modelado = $this->merge_filtro_modelado(controler:  $controler, filtro_btn: $filtro_btn, filtro_modelado:$filtro_modelado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al aplicar filtro en merge',data: $filtro_modelado,
                params: get_defined_vars());
        }
        return $filtro_modelado;
    }

    /**
     * P INT P ORDER ERRORREV
     * @param controler $controler
     * @return array
     */
    public function filtro_btn(controler $controler):array{
        $filtro_btn = array();
        if(!isset($_SESSION['filtros'][$controler->tabla])) {
            if (isset($_GET['filtro_btn'])) {
                $filtro_btn = $_GET['filtro_btn'];
            }
        }
        else{
            unset($_GET['filtro_btn']);
        }

        if(!is_array($filtro_btn)){
            return $this->error->error(mensaje: 'Error filtro_btn debe ser un array',data: $filtro_btn,
                params: get_defined_vars());
        }
        $filtro_btn = $this->reasigna_filtros(controler:  $controler, filtro_btn: $filtro_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al determinar filtro',data: $filtro_btn,
                params: get_defined_vars());
        }

        return $filtro_btn;
    }

    /**
     *
     * @param array $campos
     * @return array
     */
    public function genera_campos_lista(array $campos): array{
        if(count($campos) === 0){
            return $this->error->error('Error campos no puede venir vacio',$campos);
        }
        $keys = array();
        foreach ($campos as $key => $campo){
            if(is_numeric($key)){
                return $this->error->error('Error key debe ser txt',array($key,$campo));
            }
            if(!isset($campo['tabla_foranea'])){
                return $this->error->error('Error $campo[tabla_foranea] debe existir',array($key,$campo));
            }
            if(!isset($campo['campo_tabla_externa'])){
                return $this->error->error('Error $campo[campo_tabla_externa] debe existir',array($key,$campo));
            }

            if((string)$campo['tabla_foranea'] === ''){
                return $this->error->error('Error $campo[tabla_foranea] no debe venir vacia',array($key,$campo));
            }
            if((string)$campo['campo_tabla_externa'] === ''){
                return $this->error->error('Error $campo[campo_tabla_externa] no debe venir vacia',array($key,$campo));
            }

            $campo_lista = $campo['tabla_foranea'].'_'.$campo['campo_tabla_externa'];
            $keys[] = $campo_lista;
        }

        return $keys;
    }

    /**
     * P INT P ORDER ERROREV
     * @param array $filtro
     * @param controler $controler
     * @param array $filtro_btn
     * @return array
     */
    public function genera_filtro_modelado(controler $controler, array $filtro, array $filtro_btn = array()):array{


        $filtro_modelado = $this->determina_filtro_modelado(controler: $controler, filtro: $filtro, filtro_btn: $filtro_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro_modelado,
                params: get_defined_vars());
        }
        return $filtro_modelado;
    }

    /**
     *
     * @param array $filtros_brutos
     * @return array
     */
    public function genera_filtros_envio(array $filtros_brutos): array
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
     * P ORDER P INT ERROREV
     * @param array $filtro
     * @return array
     */
    private function genera_filtros_modelados(array $filtro):array{
        $filtro_modelado = array();
        foreach($filtro as $tabla =>$campo){
            if(is_numeric($tabla)){
                return $this->error->error(mensaje: 'Error filtro invalido key invalido',data: $filtro,
                    params: get_defined_vars());
            }
            if(!is_array($campo)){
                return $this->error->error(mensaje: 'Error filtro invalido',data: $filtro, params: get_defined_vars());
            }
            $filtro_modelado = $this->asigna_valor_filtro_modelado(campo: $campo,filtro_modelado: $filtro_modelado,
                tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar filtro',data: $filtro_modelado,
                    params: get_defined_vars());
            }
        }
        return $filtro_modelado;
    }

    /**
     * P INT P ORDER ERRORREV
     * @param array $registros
     * @param controler $controler Controlador de ejecucion
     * @return array
     */
    private function genera_registros_envio(controler $controler, array $registros):array{
        $registro_envio = array();
        foreach ($registros as $key=>$value){
            $key_envio = $this->obten_key_envio(controler:  $controler, key: $key);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error generar  key', data: $key_envio);
            }
            $registro_envio[$key_envio] = $value;
        }

        return $registro_envio;
    }

    /**
     * P ORDER P INT PROBADO ERRORREV
     * @param controler $controler
     * @return controler
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
     * P ORDER P INT ERRORREV
     * @param controler $controler
     * @param array $registro
     * @return array
     */
    private function init_registro( controler $controler, array $registro): array
    {
        $clase = $this->name_class(seccion: $controler->seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener name clase', data: $clase, params: get_defined_vars());
        }
        $controler->seccion = $clase;

        $valida = $this->validacion->valida_in_alta(clase:  $clase,controler: $controler, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar entrada de datos', data: $valida,
                params: get_defined_vars());
        }

        $registro = $this->limpia_btn_post(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar registro', data: $registro,
                params: get_defined_vars());
        }
        return $registro;
    }

    /**
     *
     * @param controler $controler
     * @param array $registro
     * @return array
     */
    public function init_upd_base(controler $controler, array $registro): array
    {
        $valida = $this->validacion->valida_post_modifica();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar POST',data: $valida);
        }

        $controler->registros = $this->procesa_registros_alta(registro: $registro,controler:  $controler);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al procesar registros',data: $controler->registros);
        }
        $controler->modelo->registro_id = $controler->registro_id;
        return $controler->registros;
    }


    /**
     * P ORDER P INT ERRORREV
     * @param array $registro
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
        return $registro;
    }

    /**
     * FULL
     * @return array
     */
    public function limpia_post_alta(): array
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
        return $_POST;
    }

    /**
     * ERROREV
     * @return array
     */
    public function limpia_registro_en_proceso(): array
    {
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
     * P INT P ORDER ERROREV
     * @param array $filtro_modelado
     * @param array $filtro_btn
     * @param controler $controler
     * @return array
     */
    #[Pure] private function merge_filtro_modelado( controler $controler, array $filtro_btn, array $filtro_modelado):array{ //FIN
        $filtro_modelado = array_merge($filtro_modelado,$controler->modelo->sql_seguridad_por_ubicacion);
        $filtro_modelado = array_merge($filtro_modelado,$controler->modelo->filtro_seguridad);

        if(count($filtro_btn)>0){
            $filtro_modelado = array_merge($filtro_modelado,$filtro_btn);
        }

        return $filtro_modelado;
    }

    /**
     * FULL
     * @param string $seccion
     * @return string|array
     */
    private function name_class(string $seccion): string|array
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error(mensaje: 'Error seccion no puede venir vacia',data:  $seccion,
                params: get_defined_vars());
        }
        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        return $namespace.$seccion;
    }

    /**
     * P INT P ORDER ERROREV
     * Obtiene el ker de envio reemplazando valores de prefijos de tablas
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
     * P INT P ORDER ERROREV
     * @param array $registro
     * @param controler $controler Controlador de ejecucion
     * @return array
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
     * P INT P ORDER ERROR
     * @param array $filtro_btn
     * @param controler $controler
     * @return array
     */
    private function reasigna_filtros(controler $controler, array $filtro_btn):array{
        foreach($controler->filtro_boton_lista as $filtro_default_btn){
            if(!isset($_SESSION['filtros'][$controler->tabla])) {
                if (!isset($_GET['filtro_btn'])) {
                    if ($filtro_default_btn['valor_default'] > 0) {
                        $filtro_btn = $this->asigna_filtros(filtro_btn: $filtro_btn, filtro_default_btn: $filtro_default_btn);
                        if(errores::$error){
                            return $this->error->error(mensaje: 'Error al determinar filtro',data: $filtro_btn,
                                params: get_defined_vars());
                        }
                    }
                }
            }
        }

        return $filtro_btn;
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

<?php
namespace gamboamartin\administrador\ctl;

use base\controller\controler;
use base\controller\valida_controller;
use gamboamartin\errores\errores;


class normalizacion_ctl{
    private errores $error;
    private valida_controller $validacion;
    public function __construct(){
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
     * TOTAL
     * Asigna el valor del modo del namespace a 'models\\' para controler en ejecución.
     * Esta función transforma el valor de la sección del controler quitando namespaces previos y añadiendo 'models\\'.
     * Regresa el nombre de la clase del modelo con su namespace completo en caso de éxito,
     * o un arreglo con el mensaje y los datos del error en caso de falla.
     *
     * @param controler $controler El controler que se está ejecutando.
     * @return string|array El nombre de la clase del modelo con su namespace completo o un arreglo con el error.
     * @throws errores Cuando la sección del controler está vacía.
     * @version 16.189.0
     * @url https://github.com/gamboamartin/administrador/wiki/administrador.base.ctl.nomalizacion_ctl.clase_model
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
     * Genera los filtros para envio de datos
     * @param array $filtros_brutos Filtro directos de POST
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
     * REG
     * Inicializa las propiedades del controlador con base en los valores de los parámetros recibidos por URL (`$_GET`).
     *
     * Esta función asigna valores de parámetros HTTP GET a las propiedades del controlador (`controler`) y, en algunos casos,
     * también a las propiedades de su modelo asociado. Es útil para configurar dinámicamente el estado del controlador en
     * función de las solicitudes recibidas.
     *
     * @param controler $controler Instancia del controlador a inicializar.
     *                             - Debe ser una clase que contenga propiedades relacionadas con secciones, acciones,
     *                               filtros y registros.
     *                             - Ejemplo de propiedades:
     *                               - `tabla`, `seccion`, `accion`, `valor_filtro`, `campo_filtro`, `selected`,
     *                                 `registro_id`, `campo`, `campo_resultado`.
     *
     * @return controler Devuelve la instancia del controlador con sus propiedades inicializadas según los parámetros `$_GET`.
     *
     * ### Ejemplo de uso exitoso:
     * ```php
     * // Supongamos que la URL es: ?seccion=usuarios&accion=editar&registro_id=1
     *
     * $controler = new controler();
     * $controler = $this->init_controler(controler: $controler);
     *
     * // Resultado esperado:
     * // $controler->tabla = 'usuarios';
     * // $controler->seccion = 'usuarios';
     * // $controler->accion = 'editar';
     * // $controler->registro_id = 1;
     * // $controler->modelo->registro_id = 1;
     * ```
     *
     * ### Ejemplo de errores:
     * ```php
     * // Caso 1: `$_GET` no contiene parámetros esperados.
     * $controler = new controler();
     * $controler = $this->init_controler(controler: $controler);
     *
     * // Resultado esperado:
     * // $controler->tabla = null;
     * // $controler->seccion = null;
     * // $controler->accion = null;
     * // (Y el resto de las propiedades permanecen sin cambios).
     * ```
     *
     * ### Parámetros esperados de `$_GET`:
     * - **`seccion`**: Nombre de la tabla o sección (ejemplo: `usuarios`, `productos`).
     * - **`accion`**: Acción a realizar (ejemplo: `crear`, `editar`).
     * - **`valor_filtro`**: Valor del filtro aplicado.
     * - **`campo_filtro`**: Campo sobre el cual se aplica el filtro.
     * - **`selected`**: Indica el estado seleccionado.
     * - **`registro_id`**: ID del registro actual. Se asigna como entero.
     * - **`campo`**: Nombre del campo a utilizar.
     * - **`campo_resultado`**: Campo donde se almacenará el resultado.
     *
     * ### Proceso de la función:
     * 1. Verifica la existencia de cada parámetro en `$_GET`.
     * 2. Asigna el valor correspondiente al controlador.
     * 3. En el caso de `registro_id`, también lo asigna al modelo del controlador.
     * 4. Retorna la instancia del controlador con las propiedades inicializadas.
     *
     * ### Casos de uso:
     * - **Contexto:** Configuración dinámica del controlador en aplicaciones web basadas en controladores y acciones.
     * - **Ejemplo real:** Una URL como `?seccion=productos&accion=ver&registro_id=5` inicializa un controlador para gestionar
     *   la vista de un producto específico.
     *
     * ### Consideraciones:
     * - Asegúrate de que los parámetros esperados se envíen correctamente mediante `$_GET`.
     * - Esta función no realiza validaciones sobre los valores de los parámetros, más allá de la conversión de `registro_id`
     *   a entero. Considera agregar validaciones adicionales si los parámetros tienen restricciones específicas.
     * - La función asume que el controlador y su modelo contienen las propiedades utilizadas.
     */

    final public function init_controler(controler $controler): controler
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
     * POR DOCUMENTAR EN WIKI
     * Limpia los valores post tras una alta.
     *
     * Esta función elimina ciertos valores del array $_POST que son generados
     * al hacer una solicitud POST cuando se da de alta una instancia en la aplicación.
     * Los valores que se eliminan son: 'btn_agrega', 'btn_guarda', 'Enviar' y 'btn_action_next'.
     *
     * @return array Retorna el array $_POST después de realizar la limpieza.
     * @version 16.273.1
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
    final public function maqueta_data_galeria(controler $controler, array $r_fotos, string $tabla):array{
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

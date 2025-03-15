<?php
namespace base\controller;
use gamboamartin\administrador\ctl\normalizacion_ctl;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;


class valida_controller extends base_modelos{


    final public function valida_alta_bd(controler $controler): bool|array
    {
        $valida = $this->valida_clase(controler: $controler);
        if(errores::$error){

            return $this->error->error(mensaje: 'Error al validar clase', data: $valida);
        }

        if($controler->tabla===''){
            return $this->error->error(mensaje: 'Error seccion por get debe existir',data:  $_GET);
        }

        $limpia = (new normalizacion_ctl())->limpia_post_alta();
        if(errores::$error){

            return $this->error->error(mensaje: 'Error al limpiar POST', data: $limpia);
        }

        $valida = $this->valida_post_alta();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar POST', data: $valida);
        }


        if($controler->seccion === ''){
            return $this->error->error(mensaje: 'Error al seccion no puede venir vacia',data:  $controler->seccion);
        }
        return true;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Valida que los datos de una seccion de un controlador sean validos.
     *
     * @param controler $controler Controlador en ejecución
     * @return bool|array Retorna true si la validación es exitosa, de lo contrario, retorna un array con
     * información sobre el error.
     * @version 16.202.0
     */
    final public function valida_clase(controler $controler): bool|array
    {
        $clase = (new normalizacion_ctl())->clase_model(controler: $controler);
        if(errores::$error){

            return $this->error->error(mensaje: 'Error al obtener clase', data: $clase);
        }

        return true;
    }

    /**
     * P INT P ORDER PROBADO
     * @param string $campo
     * @param string $seccion
     * @param string $tabla_externa
     * @return bool|array
     */
    public function valida_el(string $campo, string $seccion, string $tabla_externa): bool|array
    {
        $tabla_externa = trim($tabla_externa);
        if($tabla_externa === ''){
            return $this->error->error('Error tabla_externa no puede venir vacio',$tabla_externa);
        }
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error $campo no puede venir vacio',$campo);
        }
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->error->error('Error $seccion no puede venir vacio',$seccion);
        }
        return true;
    }

    /**
     * Valida la estructura base de un alta bd
     * @param string $clase Clase en ejecucion
     * @param controler $controler Controlador en ejecucion
     * @param array $registro Registro a insertar
     * @version 1.212.37
     * @verfuncion 1.1.0
     * @author mgamboa
     * @fecha 2022-07-29 13:02
     * @return bool|array
     */
    public function valida_in_alta(string $clase, controler $controler, array $registro): bool|array
    {
        if($controler->tabla === ''){
            return $this->error->error(mensaje: 'Error  tabla no puede venir vacia',data:  $controler->tabla);
        }
        if(count($registro) === 0){
            return $this->error->error(mensaje: 'Error el registro no puede venir vacio',data:  $registro);
        }

        if($controler->seccion === ''){
            return $this->error->error(mensaje:'Error la seccion no puede venir vacia',data: $controler->seccion);

        }


        return true;
    }

    /**
     * Valida los elementos enviado por POST en alta bd
     * @author mgamboa
     * @fecha 2022-07-29 12:20
     * @return bool|array
     */
    final public function valida_post_alta(): bool|array
    {
        if(!isset($_POST)){
            return $this->error->error(mensaje: 'Error no existe POST', data: $_GET);
        }
        if(count($_POST) === 0){
            return $this->error->error(mensaje: 'Error el POST no puede venir vacio', data: $_POST);
        }
        return true;
    }

    /**
     * Valida que la entrada post modifica cumpla con lo minimo necesario
     * @version 1.56.17
     * @return bool|array
     */
    final public function valida_post_modifica(): bool|array
    {
        if(!isset($_POST)){
            return $this->error->error(mensaje: 'POST Debe existir',data: $_GET);
        }
        if(!is_array($_POST)){
            return $this->error->error(mensaje: 'POST Debe ser un array',data: $_POST);
        }
        if(count($_POST)===0){
            return $this->error->error(mensaje: 'POST Debe tener info',data: $_POST);
        }
        return true;
    }

    /**
     * REG
     * Valida el estado de la transacción antes de ejecutarla.
     *
     * Esta función verifica si el `registro_id` del controlador es válido y luego obtiene
     * el registro asociado al modelo. Si existen errores en la obtención del registro o en
     * la validación de la transacción activa, la función devuelve un array con detalles
     * del error. En caso contrario, retorna `true` si la validación es exitosa.
     *
     * @param controler $controler Instancia del controlador con los datos de la transacción.
     *
     * @return array|bool Retorna `true` si la transacción está en un estado válido.
     *                    Retorna un array de error si ocurre un problema durante la validación.
     *
     * @throws errores Si no se puede obtener el registro o si la transacción no es válida.
     *
     * @example
     * ```php
     * $controler = new controler();
     * $controler->registro_id = 10;
     * $controler->modelo = new ModeloEjemplo();
     *
     * $valida = $this->valida_transaccion_status($controler);
     * if (is_array($valida)) {
     *     echo "Error: " . print_r($valida, true);
     * } else {
     *     echo "Transacción válida";
     * }
     * ```
     *
     * **Ejemplo de entrada correcta:**
     * ```php
     * $controler->registro_id = 15;
     * $controler->modelo = new ModeloEjemplo();
     * ```
     *
     * **Ejemplo de salida correcta:**
     * ```php
     * true
     * ```
     *
     * **Ejemplo de entrada incorrecta (registro_id inválido):**
     * ```php
     * $controler->registro_id = 0;
     * ```
     *
     * **Ejemplo de salida incorrecta:**
     * ```php
     * [
     *     'mensaje' => 'Error al registro_id debe ser mayor a 0',
     *     'data' => 0,
     *     'es_final' => true
     * ]
     * ```
     */
    final public function valida_transaccion_status(controler $controler):array|bool{
        if($controler->registro_id<=0){
            return  $this->error->error(mensaje: 'Error al registro_id debe ser mayor a 0',
                data: $controler->registro_id, es_final: true);
        }

        $registro = $controler->modelo->registro(registro_id: $controler->registro_id);
        if(errores::$error){
            return  $this->error->error('Error al obtener registro',$registro);
        }


        $controler->modelo->registro_id = $controler->registro_id;
        $valida = $this->valida_transaccion_activa(
            aplica_transaccion_inactivo: $controler->modelo->aplica_transaccion_inactivo,
            registro: $registro, registro_id: $controler->modelo->registro_id, tabla: $controler->modelo->tabla);
        if(errores::$error){
            return  $this->error->error('Error al validar transaccion activa',$valida);
        }
        return $valida;
    }
}

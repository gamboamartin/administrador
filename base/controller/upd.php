<?php
namespace base\controller;
use gamboamartin\administrador\ctl\normalizacion_ctl;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class upd{
    private errores $error;
    private base_modelos $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new base_modelos();
    }

    /**
     * REG
     * Asigna datos a un controlador para modificar un registro en una sección específica.
     *
     * Esta función valida y asigna los datos necesarios para la modificación de un registro en el modelo relacionado
     * con un controlador. Valida que la sección no esté vacía, que el registro tenga un ID válido, y obtiene los datos
     * del modelo correspondiente para que el controlador los utilice en su lógica de modificación.
     *
     * @param controler $controler Instancia del controlador que contiene el modelo relacionado, la sección, y el ID del
     *                             registro a modificar.
     *
     * @return array Retorna un arreglo con los datos del registro obtenido del modelo o un arreglo con el detalle del
     *               error si ocurre un problema durante el proceso.
     *
     * @example Uso exitoso:
     * ```php
     * $controler = new controler();
     * $controler->seccion = 'usuarios';
     * $controler->registro_id = 123;
     * $controler->modelo = new modelo();
     *
     * $resultado = $controler->asigna_datos_modifica($controler);
     *
     * // Resultado:
     * // [
     * //     'id' => 123,
     * //     'nombre' => 'Juan Pérez',
     * //     'email' => 'juan.perez@ejemplo.com'
     * // ]
     * ```
     *
     * @example Error por sección vacía:
     * ```php
     * $controler = new controler();
     * $controler->seccion = '';
     * $controler->registro_id = 123;
     *
     * $resultado = $controler->asigna_datos_modifica($controler);
     *
     * // Resultado:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error seccion no puede venir vacio',
     * //     'data' => ''
     * // ]
     * ```
     *
     * @example Error por registro ID no válido:
     * ```php
     * $controler = new controler();
     * $controler->seccion = 'usuarios';
     * $controler->registro_id = -1;
     *
     * $resultado = $controler->asigna_datos_modifica($controler);
     *
     * // Resultado:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error registro_id debe sr mayor a 0',
     * //     'data' => -1
     * // ]
     * ```
     *
     * @example Error al obtener datos del modelo:
     * ```php
     * $controler = new controler();
     * $controler->seccion = 'usuarios';
     * $controler->registro_id = 123;
     * $controler->modelo = new modelo();
     *
     * $resultado = $controler->asigna_datos_modifica($controler);
     *
     * // Resultado:
     * // [
     * //     'error' => true,
     * //     'mensaje' => 'Error al obtener datos',
     * //     'data' => [...]
     * // ]
     * ```
     *
     * @throws errores Retorna un error si:
     * - La sección está vacía.
     * - El registro ID es menor o igual a 0.
     * - Ocurre un error al obtener los datos del modelo.
     *
     * @note Esta función depende del método `obten_data` del modelo relacionado para obtener los datos del registro.
     */
    final public function asigna_datos_modifica(controler $controler): array
    {
        $namespace = 'models\\';
        $controler->seccion = str_replace($namespace, '', $controler->seccion);

        if ($controler->seccion === '') {
            return $this->error->error(
                mensaje: 'Error seccion no puede venir vacio',
                data: $controler->seccion,
                es_final: true
            );
        }
        if ($controler->registro_id <= 0) {
            return $this->error->error(
                mensaje: 'Error registro_id debe sr mayor a 0',
                data: $controler->registro_id,
                es_final: true
            );
        }

        $controler->modelo->registro_id = $controler->registro_id;
        $resultado = $controler->modelo->obten_data();
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener datos',
                data: $resultado
            );
        }
        return $resultado;
    }


    /**
     * Modificacion base
     * @param controler $controler Controlador en ejecucion
     * @param array $registro_upd Registro con datos a modificar
     * @return array|stdClass
     * @version 11.31.0
     */
    final public function modifica_bd_base(controler $controler, array $registro_upd): array|stdClass
    {

        if(count($registro_upd) === 0){
            return $this->error->error(mensaje: 'Error el registro no puede venir vacio',data: $registro_upd);
        }
        if($controler->seccion === ''){
            return $this->error->error(mensaje: 'Error la seccion no puede venir vacia', data: $controler->seccion);
        }

        $init = (new normalizacion_ctl())->init_upd_base(controler: $controler, registro: $registro_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar',data: $init);
        }

        $registro = $controler->modelo->registro($controler->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro',data: $registro);
        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $controler->modelo->aplica_transaccion_inactivo, registro: $registro,
            registro_id:  $controler->modelo->registro_id, tabla: $controler->modelo->tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar transaccion activa',data: $valida);
        }

        $resultado = $controler->modelo->modifica_bd(registro: $registro_upd, id:$controler->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar registro',data: $resultado);
        }

        return $resultado;
    }

    /**
     *
     * @param int $registro_id
     * @param controlador_base $controlador
     * @return array|string
     */
    final public function template_modifica(int $registro_id, controler $controlador):array|stdClass{

        if($controlador->seccion === ''){
            return $this->error->error(mensaje: 'Error seccion esta vacia',data: $_GET);
        }
        if($registro_id <=0){
            return $this->error->error(mensaje: 'Error registro_id debe ser mayor a 0',data: $_GET);
        }
        $controlador->registro_id = $registro_id;

        $template_modifica = $controlador->modifica(header: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar $template_modifica',data: $template_modifica);
        }

        return $template_modifica;
    }


}

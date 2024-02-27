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
     * POR DOCUMENTAR EN WIKI
     * Este método asigna datos para modificar utilizando un objeto de controlador proporcionado.
     *
     * Primero, intenta limpiar el espacio de nombres de la sección del controlador objetivo.
     * Si la sección del controlador está vacía, genera un error.
     * Si el registro_id del controlador es igual o menor que 0, también genera un error.
     *
     * Luego, asigna el registro_id al modelo del controlador y intenta obtener la data con ese registro_id.
     *
     * Si se encuentra un error durante la obtención de data, este método generará un error.
     * En caso contrario, devuelve el resultado de la obtención de data.
     *
     * @param controler $controler - El controlador con la sección y el registro_id a manejar.
     * @return array - Un array que contiene el resultado de la obtención de data o un array de error.
     * @throws errores - Si la sección del controlador está vacía, o el registro_id es igual o menor que 0,
     * o si ocurre un error durante la obtención de data.
     * @version 16.194.0
     */

    final public function asigna_datos_modifica(controler $controler):array{
        $namespace = 'models\\';
        $controler->seccion = str_replace($namespace,'', $controler->seccion);

        if($controler->seccion === ''){
            return$this->error->error(mensaje: 'Error seccion no puede venir vacio', data: $controler->seccion);
        }
        if($controler->registro_id<=0){
            return  $this->error->error(mensaje:'Error registro_id debe sr mayor a 0', data:$controler->registro_id);
        }

        $controler->modelo->registro_id = $controler->registro_id;
        $resultado = $controler->modelo->obten_data();
        if(errores::$error){
            return  $this->error->error(mensaje:'Error al obtener datos', data:$resultado);
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

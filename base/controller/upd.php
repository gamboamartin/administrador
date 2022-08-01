<?php
namespace base\controller;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use JsonException;
use stdClass;

class upd{
    private errores $error;
    private base_modelos $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new base_modelos();
    }

    /**
     * Funcion que obtiene los datos de un registro a modificar
     * @version 1.50.14
     * @param controler $controler Controlador en ejecucion
     * @return array
     */

    public function asigna_datos_modifica(controler $controler):array{
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
     *
     * @param controler $controler Controlador en ejecucion
     * @param array $registro_upd
     * @return array|stdClass
     * @throws JsonException
     */
    public function modifica_bd_base(controler $controler, array $registro_upd): array|stdClass
    {
        $init = (new normalizacion())->init_upd_base(controler: $controler, registro: $registro_upd);
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
    public function template_modifica(int $registro_id, controler $controlador):array|string{
        $namespace = 'models\\';
        $controlador->seccion = str_replace($namespace,'',$controlador->seccion);
        $clase = $namespace.$controlador->seccion;
        if($registro_id <=0){
            return $this->error->error(mensaje: 'Error no existe registro_id debe ser mayor a 0',data: $_GET,
                params: get_defined_vars());
        }
        if((string)$controlador->seccion === ''){
            return $this->error->error('Error seccion esta vacia',$_GET);

        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase '.$clase,$clase);
        }

        $controlador->registro_id = $registro_id;

        $template_modifica = $controlador->modifica(false,' ',true,false,
            false,array('status'));
        if(errores::$error){
            return $this->error->error('Error al generar $template_modifica',$template_modifica);
        }

        return $template_modifica;
    }


}

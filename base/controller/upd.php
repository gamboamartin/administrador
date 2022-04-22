<?php
namespace base\controller;
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
     *
     * @param controler $controler
     * @return array
     */

    public function asigna_datos_modifica(controler $controler):array{
        $namespace = 'models\\';
        $controler->seccion = str_replace($namespace,'', $controler->seccion);
        $clase = $namespace.$controler->seccion;
        if($controler->seccion === ''){
            return$this->error->error('Error seccion no puede venir vacio', $controler->seccion);
        }
        if($controler->registro_id<=0){
            return  $this->error->error('Error registro_id debe sr mayor a 0',$controler->registro_id);
        }
        if(!class_exists($clase)){
            return  $this->error->error('Error no existe la clase',$clase);
        }

        $controler->modelo->registro_id = $controler->registro_id;
        $resultado = $controler->modelo->obten_data();
        if(errores::$error){
            return  $this->error->error('Error al obtener datos',$resultado);
        }
        return $resultado;
    }

    /**
     *
     * @param controler $controler
     * @param array $registro_upd
     * @return array|stdClass
     */
    public function modifica_bd_base(controler $controler, array $registro_upd): array|stdClass
    {
        $init = (new normalizacion())->init_upd_base(controler: $controler, registro: $registro_upd);
        if(errores::$error){
            return $this->error->error('Error al inicializar',$init);
        }

        $registro = $controler->modelo->registro($controler->registro_id);
        if(errores::$error){
            return $this->error->error('Error al obtener registro',$registro);
        }

        $valida = $this->validacion->valida_transaccion_activa(
            aplica_transaccion_inactivo: $controler->modelo->aplica_transaccion_inactivo, registro: $registro,
            registro_id:  $controler->modelo->registro_id, tabla: $controler->modelo->tabla);
        if(errores::$error){
            return $this->error->error('Error al validar transaccion activa',$valida);
        }

        $resultado = $controler->modelo->modifica_bd(registro: $registro_upd, id:$controler->registro_id);
        if(errores::$error){
            return $this->error->error('Error al modificar registro',$resultado);
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
            return $this->error->error('Error no existe registro_id debe ser mayor a 0',$_GET);
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

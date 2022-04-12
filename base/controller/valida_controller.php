<?php
namespace base\controller;
use gamboamartin\base_modelos\base_modelos;
use gamboamartin\errores\errores;


class valida_controller extends base_modelos{

    /**
     * P ORDER P INT
     * @param controler $controler
     * @return bool|array
     */
    public function valida_clase(controler $controler): bool|array
    {
        $clase = (new normalizacion())->clase_model(controler: $controler);
        if(errores::$error){

            return $this->error->error('Error al obtener clase', $clase);
        }

        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase', $clase);

        }
        return true;
    }

    /**
     * P ORDER P INT
     * @param string $clase
     * @param controler $controler
     * @param array $registro
     * @return bool|array
     */
    public function valida_in_alta(string $clase, controler $controler, array $registro): bool|array
    {
        if($controler->tabla === ''){
            return $this->error->error('Error  tabla no puede venir vacia', $controler->tabla);
        }
        if(count($registro) === 0){
            return $this->error->error('Error el registro no puede venir vacio', $registro);
        }

        if($controler->seccion === ''){
            return $this->error->error('Error la seccion no puede venir vacia', $controler->seccion);

        }
        if(!class_exists($clase)){
            return $this->error->error('Error no existe la clase', $clase);
        }

        return true;
    }

    /**
     * P ORDER P INT
     * @return bool|array
     */
    public function valida_post_alta(): bool|array
    {
        if(!isset($_POST)){
            return $this->error->error('Error no existe POST', $_GET);
        }
        if(count($_POST) === 0){
            return $this->error->error('Error el POST no puede venir vacio', $_POST);
        }
        return true;
    }

    /**
     * P ORDER P INT PROBADO
     * @return bool|array
     */
    public function valida_post_modifica(): bool|array
    {
        if(!isset($_POST)){
            return $this->error->error('POST Debe existir',$_GET);
        }
        if(!is_array($_POST)){
            return $this->error->error('POST Debe ser un array',$_POST);
        }
        if(count($_POST)===0){
            return $this->error->error('POST Debe tener info',$_POST);
        }
        return true;
    }

    /**
     *
     * @param controler $controler
     * @return array
     */
    public function valida_transaccion_status(controler $controler):array|bool{
        if($controler->registro_id<=0){
            return  $this->error->error('Error al registro_id debe ser mayor a 0',$controler->registro_id);
        }

        $registro = $controler->modelo->registro(registro_id: $controler->registro_id);
        if(errores::$error){
            return  $this->error->error('Error al obtener registro',$registro);
        }


        $controler->modelo->registro_id = $controler->registro_id;
        $valida = $this->valida_transaccion_activa(
            aplica_transaccion_inactivo: $controler->modelo->aplica_transaccion_inactivo,
            registro_id: $controler->modelo->registro_id, tabla: $controler->modelo->tabla,registro: $registro);
        if(errores::$error){
            return  $this->error->error('Error al validar transaccion activa',$valida);
        }
        return $valida;
    }
}

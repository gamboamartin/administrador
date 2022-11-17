<?php
namespace base\controller;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;
use stdClass;


class inputs{
    private errores $error;
    private validacion $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    public function inputs_view(controler $controler, array $inputs, string $key, array $value): array|stdClass
    {
        $keys = array('type');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar value filtro',data: $valida);
        }
        $key = trim($key);
        if($key === ''){
            return $this->error->error(mensaje: 'Error key esta vacio',data: $key);
        }
        $type = $this->type_validado(inputs: $inputs,value:  $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener type',data: $type);
        }

        if(!is_object($controler->inputs)){
            return $this->error->error(
                mensaje: 'Error controlador->inputs debe se run objeto',data: $controler->inputs);
        }

        $controler->inputs->$key = $inputs[$type]->$key;
        return $controler->inputs;
    }

    /**
     * Obtiene el tipo de input para templates de alta
     * @param array $value Value de modelo->campos_view
     * @return array|string
     * @version 2.14.2.1
     */
    private function type(array $value): array|string
    {
        $keys = array('type');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys,registro:  $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar value',data: $valida);
        }
        $type = $value['type'];

        $type = trim($type);
        if($type === ''){
            return $this->error->error(mensaje: 'Error type esta vacio',data: $type);
        }
        return $type;
    }

    /**
     * Obtiene el type para templates alta validado
     * @param array|stdClass $inputs Inputs precargados
     * @param array $value Valor de modelo campos views
     * @return array|string
     * @version 2.14.2.2
     */
    private function type_validado(array|stdClass $inputs, array $value): array|string
    {
        $type = $this->type(value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener type',data: $type);
        }

        $keys = array($type);
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar value',data: $valida);
        }
        return $type;
    }

}

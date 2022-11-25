<?php
namespace gamboamartin\administrador\models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class _base_accion{
    private errores $error;
    private validacion $validacion;
    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    private function css_registro(int $id, modelo $modelo, array $registro): array
    {
        $registro_previo = $modelo->registro(registro_id: $id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener accion',data: $registro_previo);
        }

        $registro = $this->init_css(registro: $registro, registro_previo: $registro_previo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar css',data: $registro);
        }
        return $registro;
    }

    private function init_css(array $registro, stdClass $registro_previo): array
    {
        if(!isset($registro['css'])){
            $registro['css'] = $registro_previo['css'];
        }
        return $registro;
    }

    public function registro_validado_css(int $id, modelo $modelo, array $registro): array
    {
        $registro = $this->css_registro(id: $id, modelo: $modelo,registro: $registro );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar css',data: $registro);
        }

        $keys = array('css');
        $valida = $this->validacion->valida_estilos_css(keys: $keys,row: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data: $valida);
        }

        return $registro;
    }
}

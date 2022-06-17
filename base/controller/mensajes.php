<?php
namespace base\controller;
use gamboamartin\errores\errores;
use stdClass;

class mensajes{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }
    public function data(bool $con_html = true): array|stdClass
    {
        $errores_transaccion = (new errores_html())->errores_transaccion();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar errores', data: $errores_transaccion);

        }

        $exito_transaccion = (new exito_html())->mensajes_full(html: $con_html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar mensajes de exito', data: $exito_transaccion);
        }

        $data = new stdClass();
        $data->error_msj = $errores_transaccion;
        $data->exito_msj = $exito_transaccion;
        return $data;
    }
}

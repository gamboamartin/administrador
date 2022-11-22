<?php
namespace base\controller;
use base\orm\validaciones;
use gamboamartin\administrador\ctl\base_html;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;

class warning_html extends base_html {
    private errores $error;
    private validacion $validacion;
    public function __construct(){
        parent::__construct();
        $this->error = new errores();
        $this->validacion = new validaciones();
    }

    /**
     * Carga un mensaje de tipo warning
     * @version 1.84.19
     * @param array $mensaje Mensaje con datos de SESSION[warning]
     * @param string $warning_transaccion Mensajes previos cargados
     * @return array|string
     */
    private function carga_mensaje(array $mensaje, string $warning_transaccion): array|string
    {
        $keys = array('mensaje');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $mensaje);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar mensaje', data: $valida);
        }
        $warning_transaccion .= ' '.$mensaje['mensaje'];
        return $warning_transaccion;
    }

    /**
     * Genera las mensajes en texto para envio
     * @version 1.84.19
     * @param array $mensajes_warning Conjunto de mensajes en SESSION[warning]
     * @param string $warning_transaccion Mensajes previos cargados
     * @return array|string
     */
    private function carga_mensajes(array $mensajes_warning, string $warning_transaccion): array|string
    {
        $warning_transaccion_ = $warning_transaccion;
        foreach ($mensajes_warning as $mensaje){
            if(!is_array($mensaje)){
                return $this->error->error(mensaje: 'Error $mensajes_warning[] debe ser un array',
                    data: $mensajes_warning);
            }
            $keys = array('mensaje');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $mensaje);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar mensaje', data: $valida);
            }
            $warning_transaccion_ = $this->carga_mensaje(mensaje: $mensaje,warning_transaccion: $warning_transaccion_);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al cargar mensaje', data: $warning_transaccion_);
            }
        }
        return $warning_transaccion_;
    }

    /**
     * Genera los mensajes warning en ejecucion
     * @version 1.84.19
     * @return array|string
     */
    public function mensajes(): array|string
    {
        $mensajes_warning = $_SESSION['warning'] ?? array();

        if(!is_array($mensajes_warning)){
            return $this->error->error(mensaje: 'Error $_SESSION[warning] debe ser un array', data: $mensajes_warning);
        }

        $warning_transaccion = $this->warning_transaccion(mensajes_warning: $mensajes_warning);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al cargar mensaje', data: $warning_transaccion);
        }

        if (isset($_SESSION['warning'])) {
            unset($_SESSION['warning']);
        }

        return $warning_transaccion;
    }

    /**
     * Genera la salida de mensajes de tipo warning basados en SESSION[warning]
     * @version 1.84.19
     * @param array $mensajes_warning Conjunto de mensajes en SESSION[warning]
     * @return array|string
     */
    private function warning_transaccion(array $mensajes_warning): array|string
    {
        $warning_transaccion = '';
        if(count($mensajes_warning)>0) {

            $warning_transaccion = $this->carga_mensajes(mensajes_warning: $mensajes_warning,
                warning_transaccion: $warning_transaccion);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al cargar mensaje', data: $warning_transaccion);
            }

        }
        return $warning_transaccion;
    }


}

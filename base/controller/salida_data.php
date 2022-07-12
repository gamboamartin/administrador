<?php
namespace base\controller;
use base\seguridad;
use gamboamartin\errores\errores;
use JsonException;
use stdClass;
use Throwable;

class salida_data{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Genera la salida general de una funcion de tipo controller para ejecucion de cliente
     * @param bool $header
     * @param array|stdClass $result
     * @param bool $ws
     * @return array|stdClass|void
     */
    public function salida(bool $header, array|stdClass $result, bool $ws){
        if($header){
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }
        if($ws){
            header('Content-Type: application/json');
            try {
                echo json_encode($result, JSON_THROW_ON_ERROR);
            }
            catch (Throwable $e){
                return $this->error->error(mensaje: 'Error al maquetar estados',data:  $e);
            }
            exit;
        }
        return $result;
    }


    public function salida_ws(controler $controlador, string $include_action, seguridad $seguridad): bool|string
    {
        $out = true;

        $params = (new init())->params_controler();
        if(errores::$error){
            $error = $this->error->error('Error al generar parametros', $params);
            $out = $error;

        }
        $accion = $seguridad->accion;
        $data = $controlador->$accion(header:$params->header,ws: $params->ws);

        try {
            if ($params->ws && ($seguridad->accion === 'denegado')) {
                $out = json_encode(array('mensaje' => $_GET['mensaje'], 'error' => True), JSON_THROW_ON_ERROR);
            }
            if ($params->ws) {
                $out = json_encode($data, JSON_THROW_ON_ERROR);

            }

            if ($params->ws) {
                header('Content-Type: application/json');
                ob_clean();
                echo $out;
            }
        }
        catch (Throwable $e){
           $error = $this->error->error('Error al cargar json', $e);
           $out = $error;
        }

        if($params->view){
            ob_clean();
            include($include_action);
            exit;
        }

        return $out;
    }

}

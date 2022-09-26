<?php
namespace base\controller;
use base\seguridad;
use gamboamartin\errores\errores;
use stdClass;
use Throwable;

class salida_data{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Genera la salida general de una funcion de tipo controller para ejecucion de cliente
     * 
     * @param bool $header Variable utilizada que, en caso de ser solicitada, almacenará la dirección de la
     * pagina que emplea el usuario para la pagina actual en $_SERVER para ser almacenado en la variable
     * $retorno, finaliza almacenando en un header el valor de $retorno con la etiqueta 'Location'
     *
     * @param array|stdClass $result Se encarga de almacenar los valores y devuelve el valor de $result en formato JSON
     * y/o los resultados en formato JSON o un error en caso de haber errores.
     *
     * @param bool $ws variable utilizada para verificar si se ha realizado la maquetacion de estados en formato JSON.
     * En caso de haber error lanzará un mensaje
     *
     * @return array|stdClass|void
     * @version 1.499.49
     */
    private function salida(bool $header, array|stdClass $result, bool $ws){
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

    /**
     * Genera salida para funciones de controller de tipo get
     *
     * @param controler $controler Objeto con la finalidad de generar la integracion entre el objeto y la vista. Usando
     * los valores de "$controler". En caso de error, generará un mensaje
     *
     * @param array $filtro Genera un filtro en base a los registros encontrados. En caso de error, lanzará un mensaje.
     *
     * @param bool $header Variable utilizada que, en caso de ser solicitada, almacenará la dirección de la
     * pagina que emplea el usuario para la pagina actual en $_SERVER para ser almacenado en la variable
     * $retorno, finaliza almacenando en un header el valor de $retorno con la etiqueta 'Location'
     *
     * @param bool $ws variable utilizada para verificar si se ha realizado la maquetacion de estados en formato JSON.
     * En caso de haber error lanzará un mensaje
     *
     * @return array|stdClass
     * @version 1.501.50
     */
    public function salida_get(controler $controler, array $filtro, bool $header, bool $ws): array|stdClass
    {
        $r_modelo = $controler->modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $controler->retorno_error(mensaje: 'Error al obtener datos',data:  $r_modelo,header: $header,ws: $ws);

        }

        $salida = $this->salida(header: $header,result:  $r_modelo,ws:  $ws);
        if(errores::$error){
            return $controler->retorno_error(mensaje: 'Error al generar salida',data:  $salida,header: $header,ws: $ws);

        }
        return $r_modelo;
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
                $out = json_encode(array('mensaje' => 'Denegado', 'error' => True), JSON_THROW_ON_ERROR);
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

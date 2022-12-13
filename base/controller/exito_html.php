<?php
namespace base\controller;

use base\orm\validaciones;
use gamboamartin\administrador\ctl\base_html;
use gamboamartin\errores\errores;

class exito_html extends base_html {
    private errores $error;
    public function __construct(){
        parent::__construct();
        $this->error = new errores();
    }

    /**
     * Genera un boton para un alert
     * @return string
     * @version 1.68.17
     */
    private function boton_exito(): string
    {
        return '<button type="button" class="btn btn-success" data-toggle="collapse" data-target="#msj_exito">Detalle</button>';
    }

    private function exito_html(bool $html): string
    {
        $exito_html = '';
        if($html) {
            $exito_html = '<div class="alert alert-success no-margin-bottom alert-dismissible fade show no-print" role="alert">';
        }
        return $exito_html;
    }

    private function exito_html_string(string $close_btn, string $exito_html): string
    {
        $exito_html .= $close_btn;
        $exito_html .= '</div>';
        return $exito_html;
    }

    private function exito_transaccion(string $close_btn, string $exito_html, string $exito_transaccion, bool $html, string $mensaje_html){

        if($html) {
            $exito_transaccion = $this->exito_html_string(close_btn: $close_btn,exito_html:  $exito_html);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar mensaje',data:  $exito_transaccion);
            }
        }
        if (isset($_SESSION['exito'])) {
            unset($_SESSION['exito']);
        }
        if(!$html){
            $exito_transaccion.=$mensaje_html;
        }

        return $exito_transaccion;
    }

    private function genera_exito_html(bool $html){
        $exito_html = $this->exito_html(html: $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar mensaje',data:  $exito_html);
        }

        $head_html = $this->head(titulo: 'Exito');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar head',data:  $head_html);
        }
        if($html) {
            $exito_html .= $head_html;
        }

        $exito_html = $this->integra_boton(exito_html: $exito_html, html: $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar boton',data:  $exito_html);
        }
        return $exito_html;
    }

    private function integra_boton(string $exito_html, bool $html){
        $boton = $this->boton_exito();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar boton',data:  $boton);
        }

        if($html) {
            $exito_html .= $boton;
        }
        return $exito_html;
    }

    /**
     * Asigna el mensaje de exito a un p
     * @param array $mensaje_exito Datos con mensaje
     * @param bool $html Si html retorna con html sino puro texto
     * @return string|array
     * @version 1.67.17
     */
    private function mensaje(array $mensaje_exito, bool $html = true): string|array
    {
        $keys = array('mensaje');
        $valida = (new validaciones())->valida_existencia_keys(keys: $keys, registro: $mensaje_exito);
        if(errores::$error){
            $fix = 'Debe existir mensaje_exito[mensaje]';
            return $this->error->error(mensaje: 'Error al integrar mensaje', data: $valida, fix: $fix);
        }
        $mensaje = '<p class="mb-0">'.$mensaje_exito['mensaje'] . '</p>';
        if(!$html){
            $mensaje = $mensaje_exito['mensaje'];
        }

        return $mensaje;
    }

    /**
     * Integra el trazado de todos los mensajes de exito
     * @param array $mensajes_exito Conjunto de mensajes cargados en un SESSION
     * @param bool $con_html Si con html retorna con html si no puro texto
     * @return array|string
     * @version 1.67.17
     */
    private function mensajes(array $mensajes_exito, bool $con_html = true): array|string
    {
        $html = '';
        foreach ($mensajes_exito as $mensaje_exito) {
            if(!is_array($mensaje_exito)){
                $fix = 'mensajes_exito debe tener la siguiente forma $mensaje_exito[][mensaje] = mensaje';
                return $this->error->error(mensaje: 'Error $mensaje_exito debe ser un array', data: $mensaje_exito,
                    fix: $fix);
            }
            $keys = array('mensaje');
            $valida = (new validaciones())->valida_existencia_keys(keys: $keys, registro: $mensaje_exito);
            if(errores::$error){
                $fix = 'mensajes_exito debe tener la siguiente forma $mensaje_exito[][mensaje] = mensaje ';
                $fix .= 'Debe existir mensaje_exito[mensaje]';
                return $this->error->error(mensaje: 'Error al integrar mensaje', data: $valida, fix: $fix);
            }
            $mensaje_html = $this->mensaje(mensaje_exito: $mensaje_exito, html: $con_html);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar mensaje', data: $mensaje_html);
            }
            $html .=      $mensaje_html;
        }
        return $html;
    }

    public function mensajes_full(bool $html = true): array|string
    {
        $mensajes_exito = $_SESSION['exito'] ?? array();

        if(!is_array($mensajes_exito)){
            return $this->error->error(mensaje: 'Error $mensajes_exito debe ser un array',data:  $mensajes_exito);
        }

        $exito_transaccion = '';
        if(count($mensajes_exito)>0) {

            $exito_html = $this->genera_exito_html(html: $html);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al integrar mensaje',data:  $exito_html);
            }


            $mensaje_html = $this->mensajes_collapse(mensajes_exito: $mensajes_exito, html: $html);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar mensaje',data:  $mensaje_html);

            }

            $close_btn = $this->close_btn();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar boton', data: $close_btn);

            }

            $exito_html.= $mensaje_html;

            $exito_transaccion = $this->exito_transaccion(close_btn: $close_btn,exito_html:  $exito_html,
                exito_transaccion:  $exito_transaccion,html:  $html,mensaje_html:  $mensaje_html);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar mensaje',data:  $exito_transaccion);
            }


        }

        return $exito_transaccion;
    }

    /**
     * Genera los mensajes para se mostrados en html
     * @version 1.67.17
     * @param array $mensajes_exito Conjunto de mensajes obtenidos se SESSION
     * @return array|string Salida html de mensajes en success
     */
    private function mensajes_collapse(array $mensajes_exito, bool $html = true): array|string
    {

        $mensajes = $this->mensajes(mensajes_exito: $mensajes_exito, con_html: $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar mensajes', data: $mensajes);
        }
        $msjs = '<div class="collapse" id="msj_exito">'.$mensajes.'</div>';
        if(!$html){
            $msjs = $mensajes;
        }
        return  $msjs;

    }


}

<?php
namespace base\controller;
use gamboamartin\errores\errores;

class errores_html extends base_html {
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }



    private function contenido_modal(array $errores_previos): array|string
    {
        $errores_previos_html = $this->errores_previos(errores_previos: $errores_previos);
        if(errores::$error){
            return $this->error->error('Error al maquetar errores', $errores_previos_html);
        }

        $modal_btns = $this->modal_btns();
        if(errores::$error){
            return $this->error->error('Error al generar botones', $modal_btns);
        }

        return $errores_previos_html.$modal_btns;
    }

    private function data_modal_error(array $errores_previos): array|string
    {
        $head_error = $this->head(titulo: 'Error');
        if(errores::$error){
            return $this->error->error('Error al generar head', $head_error);
        }


        $contenido_modal = $this->contenido_modal(errores_previos: $errores_previos);
        if(errores::$error){
            return $this->error->error('Error al generar botones', $contenido_modal);
        }


        $mensaje_error_detalle = $this->mensaje_error_detalle(errores_previos:$errores_previos);
        if(errores::$error){
            return $this->error->error('Error al generar errores', $mensaje_error_detalle);
        }

        return $head_error.$contenido_modal.$mensaje_error_detalle;
    }

    private function detalle_btn(): string
    {
        return '<button type="button" class="btn btn-danger" data-toggle="collapse" data-target="#msj_error">Detalle</button>';
    }



    private function error_previo(array $error_previo): string
    {
        $html = $error_previo['mensaje'] ;
        $html .= ' Line '.$error_previo['line'] ;
        $html .= ' Funcion  '.$error_previo['function'] ;
        $html .= ' Class '.$error_previo['class'];
        return $html;
    }

    private function error_previo_detalle(array $error_previo): string
    {
        $html =print_r($error_previo,true);
        $html.='<br><br>';
        return $html;
    }

    private function errores_previos(array $errores_previos): array|string
    {
        $errores_html = '';
        foreach ($errores_previos as $error_previo) {
            $html = $this->error_previo(error_previo: $error_previo);
            if(errores::$error){
                return $this->error->error('Error al maquetar error', $html);
            }
            $errores_html.=$html."<br><br>";

        }
        return $errores_html;
    }

    private function errores_previos_detalle(array $errores_previos): array|string
    {
        $html = '';
        foreach ($errores_previos as $error_previo) {

            $error_previo_detalle = $this->error_previo_detalle(error_previo:  $error_previo);
            if(errores::$error){
                return $this->error->error('Error al generar error', $error_previo_detalle);
            }
            $html.=$error_previo_detalle;

        }
        return $html;
    }

    public function errores_transaccion(): array|string
    {
        $errores_previos = $_SESSION['error_resultado'] ?? array();

        $errores_transaccion = '';

        if(count($errores_previos)>0) {
            $errores_html = '<div class="alert alert-danger no-margin-bottom alert-dismissible fade show" role="alert">';

            $data_modal_error = (new errores_html())->data_modal_error(errores_previos: $errores_previos);
            if(errores::$error){
                return $this->error->error('Error al generar errores', $data_modal_error);
            }
            $errores_html.=$data_modal_error;

            $errores_html.='</div>';

            $errores_transaccion = $errores_html;
            if (isset($_SESSION['error_resultado'])) {
                unset($_SESSION['error_resultado']);
            }
        }
        return $errores_transaccion;
    }

    private function mensaje_error_detalle(array $errores_previos): array|string
    {

        $errores_previos_detalle = $this->errores_previos_detalle(errores_previos: $errores_previos);
        if(errores::$error){
            return $this->error->error('Error al generar errores', $errores_previos_detalle);
        }

        return '<div class="collapse" id="msj_error">'.$errores_previos_detalle."</div>";
    }

    private function modal_btns(): array|string
    {

        $close_btn = $this->close_btn();
        if(errores::$error){
            return $this->error->error('Error al generar boton close', $close_btn);
        }

        $detalle_btn = (new errores_html())->detalle_btn();
        if(errores::$error){
            return $this->error->error('Error al generar boton detalle', $detalle_btn);

        }

        return $close_btn.$detalle_btn;
    }
}

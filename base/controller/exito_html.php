<?php
namespace base\controller;
use gamboamartin\errores\errores;

class exito_html extends base_html {
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    public function boton_exito(): string
    {
        return '<button type="button" class="btn btn-success" data-toggle="collapse" data-target="#msj_exito">Detalle</button>';
    }

    public function mensaje(array $mensaje_exito): string
    {
        return '<p class="mb-0">'.$mensaje_exito['mensaje'] . '</p>';
    }

    private function mensajes(array $mensajes_exito): array|string
    {
        $html = '';
        foreach ($mensajes_exito as $mensaje_exito) {
            $mensaje_html = $this->mensaje(mensaje_exito: $mensaje_exito);
            if(errores::$error){
                return $this->error->error('Error al generar mensaje', $mensaje_html);
            }
            $html .=      $mensaje_html;
        }
        return $html;
    }

    public function mensajes_full(): array|string
    {
        $mensajes_exito = $_SESSION['exito'] ?? array();

        $exito_transaccion = '';
        if(count($mensajes_exito)>0) {

            $exito_html =   '<div class="alert alert-success no-margin-bottom alert-dismissible fade show no-print" role="alert">';

            $head_html = (new exito_html())->head(titulo: 'Exito');
            if(errores::$error){
                return $this->error->error('Error al generar head', $head_html);
            }
            $exito_html  .=    $head_html;

            $boton = (new exito_html())->boton_exito();
            if(errores::$error){
                return $this->error->error('Error al generar boton', $boton);
            }

            $exito_html.=  $boton;

            $mensaje_html = (new exito_html())->mensajes_collapse(mensajes_exito: $mensajes_exito);
            if(errores::$error){
                return $this->error->error('Error al generar mensaje', $mensaje_html);

            }

            $close_btn = (new base_html())->close_btn();
            if(errores::$error){
                return $this->error->error('Error al generar boton', $close_btn);

            }

            $exito_html.= $mensaje_html;
            $exito_html.= $close_btn;
            $exito_html .=      '</div>';
            $exito_transaccion = $exito_html;
            if (isset($_SESSION['exito'])) {
                unset($_SESSION['exito']);
            }
        }

        return $exito_transaccion;
    }

    public function mensajes_collapse(array $mensajes_exito): array|string
    {

        $mensajes = $this->mensajes(mensajes_exito: $mensajes_exito);
        if(errores::$error){
            return $this->error->error('Error al generar mensajes', $mensajes);
        }
        return  '<div class="collapse" id="msj_exito">'.$mensajes.'</div>';

    }


}

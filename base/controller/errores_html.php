<?php
namespace base\controller;
use base\orm\validaciones;
use gamboamartin\errores\errores;

class errores_html extends base_html {
    private errores $error;
    public function __construct(){
        parent::__construct();
        $this->error = new errores();
    }


    /**
     * @param array $errores_previos Conjunto de errores
     * @version 1.118.29
     * @return array|string
     */
    private function contenido_modal(array $errores_previos): array|string
    {
        $errores_previos_html = $this->errores_previos(errores_previos: $errores_previos);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar errores',data:  $errores_previos_html);
        }

        $modal_btns = $this->modal_btns();
        if(errores::$error){
            return $this->error->error('Error al generar botones', $modal_btns);
        }

        return $errores_previos_html.$modal_btns;
    }

    /**
     * @param array $errores_previos Conjunto de errores
     * @return array|string
     */
    private function data_modal_error(array $errores_previos): array|string
    {
        $head_error = $this->head(titulo: 'Error');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar head', data: $head_error);
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

    /**
     * Genera un boton de detalle collapse
     * @version 1.85.19
     * @return string
     */
    private function detalle_btn(): string
    {
        return '<button type="button" class="btn btn-danger" data-toggle="collapse" data-target="#msj_error">Detalle</button>';
    }

    /**
     * Obtiene el html basado en un array de errores de la clase errores
     * @param array $error_previo Error de la clase errores
     * @return string|array
     * @version 1.70.17
     */
    private function error_previo(array $error_previo): string|array
    {
        $keys = array('mensaje','line','function','class');
        $valida = (new validaciones())->valida_existencia_keys(keys: $keys,registro:  $error_previo,valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }
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

    /**
     * Genera las errores basado en el conjunto de errores de la clase errores
     * @version 1.84.19
     * @param array $errores_previos Conjunto de errores
     * @return array|string
     */
    private function errores_previos(array $errores_previos): array|string
    {
        $errores_html = '';
        foreach ($errores_previos as $error_previo) {
            if(!is_array($error_previo)){
                return $this->error->error(mensaje: 'Error $errores_previos[] debe ser un array',
                    data: $errores_previos);
            }
            $keys = array('mensaje','line','function','class');
            $valida = (new validaciones())->valida_existencia_keys(keys: $keys,registro:  $error_previo,
                valida_vacio: false);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
            }
            $html = $this->error_previo(error_previo: $error_previo);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar error', data: $html);
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
                return $this->error->error(mensaje: 'Error al generar errores', data: $data_modal_error);
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

    /**
     * Genera los botones de un modal para alerts de errores
     * @version 1.118.28
     * @return array|string
     */
    private function modal_btns(): array|string
    {

        $close_btn = $this->close_btn();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar boton close', data: $close_btn);
        }

        $detalle_btn = (new errores_html())->detalle_btn();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar boton detalle',data:  $detalle_btn);

        }

        return $close_btn.$detalle_btn;
    }
}

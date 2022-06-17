<?php
namespace base\controller;
use gamboamartin\errores\errores;

class base_html{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }
    public function close_btn(): string
    {
        return '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>';
    }

    /**
     * Genera el head de un mensaje
     * @param string $titulo Titulo a mostrar en el encabezado
     * @return string|array
     * @version 1.68.1
     */
    public function head(string $titulo): string|array
    {
        $titulo = trim($titulo);
        if($titulo === ''){
            return $this->error->error(mensaje: 'Error el titulo esta vacio',data:  $titulo);
        }

        $errores_html = '<h4 class="alert-heading">';
        $errores_html .= $titulo;
        $errores_html .= '</h4>';
        return $errores_html;
    }
}

<?php
namespace gamboamartin\administrador\ctl;
use gamboamartin\errores\errores;

class base_html{
    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Genera boton close para alert
     * @return string
     */
    final protected function close_btn(): string
    {
        return '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>';
    }

    /**
     * POR DOCUMENTAR EN WIKI FINAL REV
     * Método para generar encabezado HTML
     *
     * @param string $titulo - El título del encabezado
     *
     * @return string|array - Retorna el elemento del encabezado HTML si todo se procesa correctamente, de lo contrario se retorna un error.
     *
     * @throws errores si el título está vacío.
     *
     * @throws errores si hay algún otro problema durante la generación del encabezado
     * @version 18.7.0
     */
    final protected function head(string $titulo): string|array
    {
        $titulo = trim($titulo);
        if($titulo === ''){
            return $this->error->error(mensaje: 'Error el titulo esta vacio',data:  $titulo, es_final: true);
        }

        $errores_html = '<h4 class="alert-heading">';
        $errores_html .= $titulo;
        $errores_html .= '</h4>';
        return $errores_html;
    }
}

<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;

/**
 * PROBADO PARAMS ORDER PARAMS INT
 */
class class_css{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }
    /**
     * Genera las clases de un css en forma html
     * @param array $clases_css Clases para generar css html
     * @return string
     * @version 1.309.41
     */
    public function class_css_html(array $clases_css): string
    {
        $class_css_html = '';
        foreach($clases_css as $clase_css){
            $class_css_html.=' '.$clase_css;
        }
        return $class_css_html;
    }

    /**
     * Aplica Inline
     * @param string $size tamaño input css
     * @param bool $inline si inline deja el input inline
     * @return string|array
     * @version 1.455.49
     */
    public function inline_html(bool $inline, string $size): string|array
    {
        $size = trim ($size);
        if($size === ''){
            return $this->error->error(mensaje: 'Error size no puede venir vacio',data: $size);
        }
        $inline_html = "col-$size-10";
        if(!$inline){
            $inline_html = "col-$size-12";
        }
        return $inline_html;
    }

    /**
     * Maqueta la salida inline si aplica
     * @param bool $inline Genera el input inline
     * @param string $size tamaño del input css
     * @return string|array
     * @version 1.455.49
     */
    public function inline_html_lb(bool $inline, string $size): string|array
    {
        $size =  trim($size);
        if($size === ''){
            return $this->error->error(mensaje: 'Error size no puede venir vacio',data: $size);
        }

        $inline_html_lb = "col-$size-2";
        if(!$inline){
            $inline_html_lb = "label-select";
        }
        return $inline_html_lb;
    }
}

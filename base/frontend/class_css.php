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
     * PROBADO-PARAMS ORDER P INT ERRORREV
     * @param array $clases_css
     * @return string
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
     * PROBADO-PARAMS ORDER P INT
     * @param string $size
     * @param bool $inline
     * @return string|array
     */
    public function inline_html(bool $inline, string $size): string|array
    {
        $size = trim ($size);
        if($size === ''){
            return $this->error->error('Error size no puede venir vacio',$size);
        }
        $inline_html = "col-$size-10";
        if(!$inline){
            $inline_html = "col-$size-12";
        }
        return $inline_html;
    }

    /**
     * PROBADO - PARAMS ORDER P INT
     * @param bool $inline
     * @param string $size
     * @return string|array
     */
    public function inline_html_lb(bool $inline, string $size): string|array
    {
        $size =  trim($size);
        if($size === ''){
            return $this->error->error('Error size no puede venir vacio',$size);
        }

        $inline_html_lb = "col-$size-2";
        if(!$inline){
            $inline_html_lb = "label-select";
        }
        return $inline_html_lb;
    }
}

<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

/**
 * PARAMS ORDER, PARAMS INT PROBADO
 */
class botones{
    private errores $error;

    #[Pure] public function __construct(){
        $this->error = new errores();

    }





    /**
     * Genera los parametros para la integracion de un boton
     * @param array $class_css clases para integrar en html
     * @param string $icon Icono html
     * @param array $datas Conjunto de extra params para se convertido en html
     * @return array|stdClass
     * @version 1.309.41
     */
    public function data_btn(array $class_css, array $datas, string $icon): array|stdClass
    {
        $class_html = (new class_css())->class_css_html(clases_css:$class_css);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar clases',data:  $class_html);
        }

        $icon_html = $this->icon_html(icon:$icon);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar icons', data: $icon_html);
        }


        $params = new stdClass();
        $params->class = $class_html;
        $params->icon = $icon_html;

        return $params;
    }

    /**
     * Genera un icono svg
     * @param string $icon Icono
     * @return string
     * @version 1.309.41
     */
    private function icon_html(string $icon): string
    {
        $icon_html = '';
        if($icon !==''){
            $icon_html = '<i class="'.$icon.'"></i>';
        }
        return $icon_html;
    }




}

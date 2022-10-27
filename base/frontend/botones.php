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
     * @param string $icon Icono html
     * @return array|stdClass
     * @version 1.309.41
     */
    public function data_btn( string $icon): array|stdClass
    {

        $icon_html = $this->icon_html(icon:$icon);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar icons', data: $icon_html);
        }


        $params = new stdClass();
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

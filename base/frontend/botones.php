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
     * FULL
     * @param string $id_css
     * @param string $label
     * @param string $name
     * @param string $stilo
     * @param string $type
     * @param string $value
     * @return string|array
     */
    protected function btn_html(string $id_css, string $label, string $name,string $stilo,
                              string $type, string $value): string|array
    {
        $id_css = trim($id_css);
        $label = trim($label);
        $name = trim($name);
        $value = trim($value);


        $type = trim ($type);
        if($type === ''){
            return $this->error->error(mensaje: 'Error type esta vacio', data: $type);
        }

        $stilo = trim ($stilo);
        if($stilo === ''){
            return $this->error->error(mensaje: 'Error $stilo esta vacio', data: $stilo);
        }

        return "<button type='$type' name='$name' value='$value' id='$id_css'  
                    class='btn btn-$stilo col-md-12 ' > $label</button>";

    }

    /**
     * Genera un boton html
     * @param int $cols Columnas para css
     * @param string $id_css identificador css
     * @param string $label Etiqueta del boton
     * @param string $name Nombre del input
     * @param string $stilo Estilo o contento danger , success etc
     * @param string $type Tipo de input
     * @param string $value Valor del input
     * @return array|string
     */
    public function button(int $cols,string $id_css, string $label, string $name,string $stilo,
                           string $type, string $value): array|string
    {
        $type = trim ($type);
        if($type === ''){
            return $this->error->error(mensaje: 'Error type esta vacio',data:  $type);
        }
        $stilo = trim ($stilo);
        if($stilo === ''){
            return $this->error->error(mensaje: 'Error $stilo esta vacio', data: $stilo);
        }

        $btn = $this->btn_html(id_css:  $id_css, label: $label,name:  $name, stilo: $stilo,
            type: $type, value:  $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar boton', data: $btn);
        }

        $button = $this->container_html(cols: $cols, contenido:  $btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar container',data:  $btn);
        }
        return $button;

    }

    /**
     * Genera un contenedor base html
     * @param int $cols Columnas para css
     * @param string $contenido Contenido del div
     * @return string|array
     */
    private function container_html(int $cols, string $contenido): string|array
    {
        $contenido = trim($contenido);

        $valida =  (new validaciones_directivas())->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols',data: $valida);
        }

        $html = "<div class='col-md-$cols'>";
        $html .= $contenido;
        $html .= '</div>';
        return $html;
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

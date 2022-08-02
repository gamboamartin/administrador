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
     * PROBADO-PARAMS ORDER-PARAMS INT
     * @return string
     */
    public function boton_acciones_list(): string
    {
        return"<button class='btn btn-outline-info btn-sm'><i class='bi bi-chevron-down'></i> Acciones </button>";
    }

    /**
     * PROBADO-PARAMS ORDER-PARAMS INT
     * @param string $class_btn
     * @param string $target
     * @return array|string
     */
    public function boton_pestana(string $class_btn, string $target): array|string
    {
        $class_btn = trim($class_btn);
        if($class_btn === ''){
            return $this->error->error('Error class_btn vacio', $class_btn);
        }

        $target = trim($target);
        if($target === '') {
            return $this->error->error('Error target vacio', $target);
        }

        $etiqueta = str_replace('_', ' ', $target);
        $etiqueta = ucwords($etiqueta);
        $btn = '<button class="nav-link active  btn-'.$class_btn.'"';
        $btn.='data-toggle="collapse"  data-target="#'.$target.'" aria-expanded="true"';
        $btn.='aria-controls="'.$target.'" >'.$etiqueta.'</button>';
        return $btn;
    }

    /**
     * FULL
     * @param string $type
     * @param string $name
     * @param string $value
     * @param string $id_css
     * @param string $label
     * @param string $stilo
     * @param stdClass $params
     * @return string|array
     */
    private function btn_html(string $id_css, string $label, string $name, stdClass $params,string $stilo,
                              string $type, string $value): string|array
    {
        $id_css = trim($id_css);
        $label = trim($label);
        $name = trim($name);
        $value = trim($value);

        $params = (new params_inputs())->limpia_obj_btn(params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar params',data:  $params, params: get_defined_vars());
        }

        $type = trim ($type);
        if($type === ''){
            return $this->error->error(mensaje: 'Error type esta vacio', data: $type, params: get_defined_vars());
        }

        $stilo = trim ($stilo);
        if($stilo === ''){
            return $this->error->error(mensaje: 'Error $stilo esta vacio', data: $stilo, params: get_defined_vars());
        }

        return "<button type='$type' name='$name' value='$value' id='$id_css'  
                    class='btn btn-$stilo col-md-12 $params->class' $params->data_extra>$params->icon $label</button>";

    }

    /**
     * FULL
     * @param string $type
     * @param string $name
     * @param string $value
     * @param string $id_css
     * @param string $label
     * @param string $stilo
     * @param stdClass $params
     * @param int $cols Columnas para css
     * @return array|string
     */
    public function button(int $cols,string $id_css, string $label, string $name, stdClass $params,string $stilo,
                           string $type, string $value): array|string
    {
        $type = trim ($type);
        if($type === ''){
            return $this->error->error(mensaje: 'Error type esta vacio',data:  $type, params: get_defined_vars());
        }
        $stilo = trim ($stilo);
        if($stilo === ''){
            return $this->error->error(mensaje: 'Error $stilo esta vacio', data: $stilo, params: get_defined_vars());
        }

        $btn = $this->btn_html(id_css:  $id_css, label: $label,name:  $name,params:  $params, stilo: $stilo,
            type: $type, value:  $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar boton', data: $btn, params: get_defined_vars());
        }

        $button = $this->container_html(cols: $cols, contenido:  $btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar container',data:  $btn);
        }
        return $button;

    }

    /**
     * FULL
     * @param int $cols Columnas para css
     * @param string $contenido
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
     * FULL
     * @param array $class_css
     * @param string $icon
     * @param array $datas Conjunto de extra params para se convertido en html
     * @return array|stdClass
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

        $data_extra_html = (new extra_params())->data_extra_html(data_extra: $datas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar datas', data: $data_extra_html);
        }
        $params = new stdClass();
        $params->class = $class_html;
        $params->icon = $icon_html;
        $params->data_extra = $data_extra_html;
        return $params;
    }

    /**
     * FULL
     * @param string $icon
     * @return string
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

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
     * PROBADO-PARAMS ORDER-PARAMS INT
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
            return $this->error->error('Error al limpiar params', $params);
        }

        $type = trim ($type);
        if($type === ''){
            return $this->error->error('Error type esta vacio', $type);
        }

        $stilo = trim ($stilo);
        if($stilo === ''){
            return $this->error->error('Error $stilo esta vacio', $stilo);
        }

        return "<button type='$type' name='$name' value='$value' id='$id_css'  
                    class='btn btn-$stilo col-md-12 $params->class' $params->data_extra>$params->icon $label</button>";

    }

    /**
     * PROBADO-PARAMS ORDER-PARAMS INT
     * @param string $type
     * @param string $name
     * @param string $value
     * @param string $id_css
     * @param string $label
     * @param string $stilo
     * @param stdClass $params
     * @param int $cols
     * @return array|string
     */
    public function button(int $cols,string $id_css, string $label, string $name, stdClass $params,string $stilo,
                           string $type, string $value): array|string
    {
        $type = trim ($type);
        if($type === ''){
            return $this->error->error('Error type esta vacio', $type);
        }
        $stilo = trim ($stilo);
        if($stilo === ''){
            return $this->error->error('Error $stilo esta vacio', $stilo);
        }

        $btn = $this->btn_html(id_css:  $id_css, label: $label,name:  $name,params:  $params, stilo: $stilo,
            type: $type, value:  $value);
        if(errores::$error){
            return $this->error->error('Error al generar boton', $btn);
        }

        $button = $this->container_html(cols: $cols, contenido:  $btn);
        if(errores::$error){
            return $this->error->error('Error al generar container', $btn);
        }
        return $button;

    }

    /**
     * PROBADO-PARAMS ORDER-PARAMS INT
     * @param int $cols
     * @param string $contenido
     * @return string|array
     */
    private function container_html(int $cols, string $contenido): string|array
    {
        $contenido = trim($contenido);

        $valida =  (new validaciones_directivas())->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error('Error al validar cols',$valida);
        }

        $html = "<div class='col-md-$cols'>";
        $html .= $contenido;
        $html .= '</div>';
        return $html;
    }

    /**
     * PROBADO - PARAMS-ORDER-PARAMS INT
     * @param array $class_css
     * @param string $icon
     * @param array $datas
     * @return array|stdClass
     */
    public function data_btn(array $class_css, array $datas, string $icon): array|stdClass
    {
        $class_html = (new class_css())->class_css_html(clases_css:$class_css);
        if(errores::$error){
            return $this->error->error('Error al generar clases', $class_html);
        }

        $icon_html = $this->icon_html(icon:$icon);
        if(errores::$error){
            return $this->error->error('Error al generar icons', $icon_html);
        }

        $data_extra_html = (new extra_params())->data_extra_html(data_extra: $datas);
        if(errores::$error){
            return $this->error->error('Error al generar datas', $data_extra_html);
        }
        $params = new stdClass();
        $params->class = $class_html;
        $params->icon = $icon_html;
        $params->data_extra = $data_extra_html;
        return $params;
    }

    /**
     * PROBADO - PARAMS ORDER-PARAMS INT
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

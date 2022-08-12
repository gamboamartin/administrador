<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class inputs_files{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $class_css_html
     * @param string $ids_html id css
     * @param string $campo Campo de input
     * @param string $disable_html atributo disabled
     * @param string $required_html
     * @param stdClass $labels
     * @return array|string
     */
    private function contains_input_file(string $campo, string $class_css_html,  string $disable_html, string $ids_html,
                                         stdClass $labels, string $required_html): array|string
    {
        if(!isset($labels->label_input_upload)){
            $labels->label_input_upload = '';
        }

        $input_upload_multiple = $this->input_upload_multiple(campo: $campo, class_css_html: $class_css_html,
            disable_html: $disable_html, ids_html: $ids_html, required_html: $required_html);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener input',data: $input_upload_multiple);
        }

        $content_input = $this->content_input_multiple(input_upload_multiple: $input_upload_multiple,
            label_input_upload: $labels->label_input_upload);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener input',data: $input_upload_multiple);
        }

        return $content_input;
    }

    /**
     * Integra el contenido de un input
     * @param string $input_upload_multiple Input en forma html
     * @param string $label_input_upload Etiqueta de input
     * @return string
     */
    private function content_input_multiple(string $input_upload_multiple, string $label_input_upload): string
    {
        $html = '<div class="custom-file">';
        $html.= $input_upload_multiple;
        $html.= $label_input_upload;
        $html.= '</div>';
        return $html;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $codigo Codigo para ser mostrado en label
     * @param string $etiqueta Etiqueta a mostrar
     * @param string $class_css_html
     * @param string $ids_html id css
     * @param string $campo Campo de input
     * @param string $disable_html atributo disabled
     * @param string $required_html
     * @return array|stdClass
     */

    private function data_contains_input_file(string $campo, string $class_css_html, string $codigo,
                                              string $disable_html, string $etiqueta, string $ids_html,
                                              string $required_html): array|stdClass {
        $labels = (new etiquetas())->labels_multiple(codigo: $codigo, etiqueta: $etiqueta);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener labels',data: $labels);
        }

        $content_input = $this->contains_input_file(campo: $campo, class_css_html: $class_css_html,
            disable_html: $disable_html, ids_html: $ids_html, labels:  $labels, required_html: $required_html);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener input',data: $content_input);
        }

        $labels->content_input = $content_input;

        return $labels;
    }


    /**
     *
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $codigo Codigo para ser mostrado en label
     * @param string $etiqueta Etiqueta a mostrar
     * @param string $class_css_html
     * @param string $ids_html id css
     * @param string $campo Campo de input
     * @param string $disable_html atributo disabled
     * @param string $required_html
     * @return array|string
     */
    public function input_file_multiple(string $campo, string $class_css_html, string $codigo, string $disable_html,
                                        string $etiqueta, string $ids_html, string $required_html): array|string
    {
        $data_contains = $this->data_contains_input_file(campo: $campo, class_css_html:  $class_css_html,
            codigo:  $codigo, disable_html:  $disable_html, etiqueta:  $etiqueta, ids_html: $ids_html,
            required_html:  $required_html);
        if(errores::$error){
            return  $this->error->error('Error al obtener input',$data_contains);
        }

        $input_file = $this->input_multiple_file(content_input: $data_contains->content_input,
            label_upload: $data_contains->label_upload);
        if(errores::$error){
            return  $this->error->error('Error al obtener input',$input_file);
        }

        return $input_file;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $label_upload
     * @param string $content_input
     * @return string
     */
    private function input_multiple_file(string $content_input, string $label_upload): string
    {
        $html ='<div class="input-group mb-3">';
        $html.=     $label_upload;
        $html.=     $content_input;
        $html.='</div>';
        return $html;
    }

    /**
     * Genera un input file multiple
     * @param string $campo Campo de input
     * @param string $class_css_html Class css html
     * @param string $disable_html atributo disabled
     * @param string $ids_html id css
     * @param string $required_html atributo required
     * @return string|array
     * @version 1.318.41
     *
     */
    private function input_upload_multiple(string $campo, string $class_css_html, string $disable_html,
                                           string $ids_html, string $required_html): string|array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio', data: $campo);
        }

        $html = '<input type="file" class="custom-file-input '.$class_css_html.'"';
        $html .= ' id="'.$ids_html.'" name="'.$campo.'" multiple '.$disable_html.' '.$required_html.'>';
        return str_replace(array('  ', ' "'), array('', '"'), $html);
    }

}

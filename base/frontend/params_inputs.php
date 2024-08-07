<?php
namespace base\frontend;



use gamboamartin\errores\errores;
use stdClass;

class params_inputs{
    private errores $error;

    public function __construct()
    {
        $this->error = new errores();
    }

    /**
     * Integra clases css de manera dinamica
     * @param array $class_css
     * @return string|array
     * @version 11.10.0
     */
    final public function class_html(array $class_css): string|array
    {
        $class_html = '';
        foreach ($class_css as $class){
            $class = trim($class);
            if($class === ''){
                return $this->error->error(mensaje: 'Error class vacio',data:  $class);
            }
            $class_html.=" $class ";
        }
        $class_html = trim($class_html);
        if($class_html!==''){
            $class_html = "class='$class_html'";
        }
        return $class_html;
    }
    
    /**
     * Si disabled retorna attr disabled  en string
     * @param bool $disabled Si disabled retorna attr disabled
     * @return string
     *
     */
    final public function disabled_html(bool $disabled): string
    {
        $disabled_html = '';
        if($disabled){
            $disabled_html = 'disabled';
        }
        return $disabled_html;
    }

    /**
     * Integra los id para elementos de frontend
     * @param array $ids_css Identificadores css
     * @return string|array
     */
    final public function ids_html(array $ids_css): string|array
    {
        $id_html = '';
        foreach ($ids_css as $id_css){
            $id_css = trim($id_css);
            if($id_css === ''){
                return $this->error->error(mensaje: 'Error id_css vacio',data:  $id_css);
            }
            $id_html.=" $id_css ";
        }
        $id_html = trim($id_html);
        if($id_html!==''){
            $id_html = "id='$id_html'";
        }
        return $id_html;
    }

    /**
     * Obtiene los parametros base para un input de tipo radio
     * @param string $campo Campo a integrar
     * @param string $tag Tag de input
     * @return stdClass|array
     * @version 11.9.0
     */
    final public function params_base_chk(string $campo, string $tag): stdClass|array{

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio',data:  $campo);
        }

        $tag = trim($tag);
        if($tag === ''){
            $tag = $campo;
            $tag = str_replace('_', ' ', $tag);
            $tag = ucwords($tag);
        }
        $tag = trim($tag);

        if($tag === '' ){
            return $this->error->error(mensaje: 'Error tag vacio',data:  $tag);
        }

        $class_label[] = 'form-check-label';
        $class_label[] = 'chk';

        $class_radio[] = 'form-check-input';
        $class_radio[] = $campo;

        $for = $tag;
        
        $ids_css[] = $campo;

        $label_html = $tag;
        $title = $tag;

        $data = new stdClass();

        $data->class_label = $class_label;
        $data->class_radio = $class_radio;
        $data->for = $for;
        $data->ids_css = $ids_css;
        $data->label_html = $label_html;
        $data->title = $title;
        $data->name = $campo;

        return $data;
    }


    /**
     * Integra un regex a un pattern input
     * @param string $regex
     * @return string
     */
    public function regex_html(string $regex): string
    {
        $regex_html = '';
        if($regex){
            $regex_html = "pattern='$regex'";
        }
        return $regex_html;
    }


    /**
     * Genera required en forma html para ser integrado en un input
     * @version 1.87.19
     * @stable true
     * @param bool $required indica si es requerido o no
     * @return string required en caso true o vacio en false
     */
    final public function required_html(bool $required): string
    {
        $required_html = '';
        if($required){
            $required_html = 'required';
        }
        return $required_html;
    }

    public function multiple_html(bool $multiple): string
    {
        $multiple_html = '';
        if($multiple){
            $multiple_html = 'multiple';
        }
        return $multiple_html;
    }

    /**
     * @param string $place_holder
     * @param string $title
     * @return string
     */
    public function title_html(string $place_holder, string $title): string
    {
        $title = trim($title);
        if($title === ''){
            $title = $place_holder;
        }

        $title_html = '';
        if($title !== ''){
            $title_html = "title='$title'";
        }
        return $title_html;
    }

}

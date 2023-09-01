<?php
namespace base\frontend;



use stdClass;

class params_inputs{

    /**
     * Integra clases css de manera dinamica
     * @param array $class_css
     * @return string
     */
    final public function class_html(array $class_css): string
    {
        $class_html = '';
        foreach ($class_css as $class){
            $class_html.=" $class ";
        }
        if($class_html!==''){
            $class_html = "class='$class_html'";
        }
        return $class_html;
    }
    
    /**
     * Si disabled retorna attr disabled  en string
     * @stable true
     * @version 1.588.52
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
     * Obtiene los parametros base para un input de tipo radio
     * @param string $campo Campo a integrar
     * @param string $tag Tag de input
     * @return stdClass
     */
    final public function params_base_chk(string $campo, string $tag): stdClass{
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

<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use PDO;
use stdClass;

class links{
    private errores $error;
    private validaciones_directivas $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
    }

    /**
     * P INT
     * @param PDO|bool $link
     * @param bool $valida_accion
     * @param string $seccion
     * @param string $accion
     * @param array $accion_registro
     * @return bool|array|stdClass
     */
    public function aplica_data_link_validado(PDO|bool $link, bool $valida_accion, string $seccion, string $accion, array $accion_registro): bool|array|stdClass
    {
        $data_link = false;
        if($link && $valida_accion) {
            $valida = $this->validacion->seccion_accion(seccion: $seccion,accion:  $accion);
            if(errores::$error){
                return  $this->error->error(mensaje: 'Error al validar seccion', data: $valida, params: get_defined_vars());
            }

            $data_link = $this->data_link_conectado(seccion: $seccion,accion:  $accion,accion_registro:  $accion_registro);
            if(errores::$error){
                return   $this->error->error('Error al generar datos ', $data_link);
            }
        }
        return $data_link;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $icon
     * @param array $class_css
     * @param string $size
     * @param string $tarjet
     * @param string $data_toggle
     * @param string $label
     * @param array $datas
     * @return string
     */
    private function boton_icon(string $icon, array $class_css = array(), string $data_toggle = '',
                                array $datas = array(), string $label = '', string $size = '',
                                string $tarjet = ''): string
    {
        $class_css_html = '';
        foreach ($class_css as $css){
            $class_css_html.=" $css ";
        }

        if($class_css_html === ''){
            $class_css_html = 'btn-primary '.$size;
        }

        if($icon !==''){
            $icon = '<i class="'.$icon.'"></i>';
        }

        $datas_html = '';
        foreach ($datas as $key=>$valor){
            $datas_html.=" data-$key='$valor' ";
        }

        $btn = '<button type="button" class="btn '.$class_css_html.'" data-target="'.$tarjet.'"
        data-toggle = "'.$data_toggle.'" '.$datas_html.' >'.$label.' '.$icon.'</button>';

        return $btn;
    }

    /**
     * P INT
     * @param array $accion_registro
     * @return bool|array|stdClass
     */
    private function data_link_bd(array $accion_registro): bool|array|stdClass
    {
        $data_link = false;
        if( count($accion_registro) > 0 ){
            $data_link = $this->data_link_br(accion_registro: $accion_registro);
            if(errores::$error){
                return   $this->error->error('Error al generar datos ', $data_link);
            }

        }
        return $data_link;
    }

    /**
     * P INT
     * @param string $seccion
     * @param string $accion
     * @param array $accion_registro
     * @return bool|array|stdClass
     */
    private function data_link_conectado(string $seccion, string $accion, array $accion_registro): bool|array|stdClass
    {
        $valida = $this->validacion->seccion_accion(seccion: $seccion,accion:  $accion);
        if(errores::$error){
            return  $this->error->error('Error al validar seccion',$valida);
        }


        $data_link = $this->data_link_bd(accion_registro: $accion_registro);
        if(errores::$error){
            return   $this->error->error('Error al generar datos ', $data_link);
        }
        return $data_link;
    }

    /**
     * P INT
     * @param array $accion_registro
     * @return stdClass
     */
    private function data_link_br(array $accion_registro): stdClass
    {


        $seccion_br = '';
        $accion_br = '';
        if((string)$accion_registro['seccion_menu_etiqueta_label'] !== '') {
            $seccion_br = $accion_registro['seccion_menu_etiqueta_label'];
        }
        if((string)$accion_registro['accion_etiqueta_label'] !=='') {
            $accion_br = $accion_registro['accion_etiqueta_label'];
        }

        $data = new stdClass();
        $data->seccion = $seccion_br;
        $data->accion = $accion_br;
        return $data;
    }





    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $seccion
     * @param string $accion
     * @param int $registro_id
     * @param string $icon
     * @param array $styles
     * @param string $session_id
     * @return array|string
     */
    public function link_accion(string $accion,  string $icon,  int $registro_id, string $seccion, string $session_id,
                                array $styles): array|string
    {
        $href = "index.php?seccion=$seccion&accion=$accion&registro_id=$registro_id&session_id=".$session_id;
        $btn = $this->boton_icon(icon: $icon, class_css: $styles);
        if(errores::$error){
            return $this->error->error("Error al generar boton", $btn);
        }
        return "<a href=$href>$btn</a>";
    }


    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $seccion_menu_descripcion
     * @param array $menus
     * @param string $session_id
     * @return array|string
     */
    private function link_menu(array $menus, string $seccion_menu_descripcion, string $session_id): array|string
    {
        if(!isset($_SESSION['grupo_id'])){
            return $this->error->error('Error debe existir grupo_id',$_SESSION);
        }
        if((int)$_SESSION['grupo_id']<=0){
            return $this->error->error('Error grupo_id debe ser mayor o igual a 0',$_SESSION);
        }

        $link_seccion_menu_descripcion = strtolower($seccion_menu_descripcion);

        $html = "";
        foreach ($menus as $key => $menu) {

            $link_accion = strtolower($menu['accion_descripcion']);
            $etiqueta_accion = ucfirst($link_accion);
            if($menu['accion_etiqueta_label'] !== ''){
                $etiqueta_accion=$menu['accion_etiqueta_label'];
            }
            $etiqueta_accion_muestra = str_replace('_', ' ', $etiqueta_accion);
            $html .= "<li><a href='index.php?seccion=$link_seccion_menu_descripcion&accion=$link_accion&session_id=" . $session_id . "'>$etiqueta_accion_muestra</a></li>";

        }
        return $html;
    }


}

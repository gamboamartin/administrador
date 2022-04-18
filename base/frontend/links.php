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
                return  $this->error->error('Error al validar seccion',$valida);
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
        $accion_registro = $this->limpia_keys_accion(row: $accion_registro);
        if(errores::$error){
            return   $this->error->error('Error al limpiar datos ', $accion_registro);
        }

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
     * P ORDER P INT
     * @param array $accion
     * @param bool $aplica_etiqueta
     * @param string $id
     * @param string $session_id
     * @param string $st_btn
     * @return array|stdClass
     */
    public function data_para_link(array $accion, bool $aplica_etiqueta, string $id, string $session_id, string $st_btn): array|stdClass
    {
        $init = $this->init_link(accion: $accion);
        if(errores::$error){
            return $this->error->error('Error al inicializar link', $init);
        }

        $data_accion = $this->link_accion_base(accion: $init->accion,accion_envio:  $init->accion_descripcion_envio,
            id:$id, seccion: $init->seccion_envio, session_id: $session_id);

        $etiqueta = $init->accion_descripcion_envio;
        if(!$aplica_etiqueta){
            $etiqueta = '';
        }

        $texto_link = $this->txt_link(etiqueta: $etiqueta,init:  $init,st_btn:  $st_btn);
        if(errores::$error){
            return $this->error->error('Error al inicializar texto link', $texto_link);
        }

        $data = new stdClass();
        $data->texto_link = $texto_link;
        $data->href = $data_accion->href;
        $data->title = $init->title;
        $data->accion_descripcion_envio = $init->accion_descripcion_envio;
        $data->btn_modal = $data_accion->btn_modal;
        $data->modal = $data_accion->modal;

        return $data;
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $accion
     * @param string $id
     * @param string $seccion
     * @param string $session_id
     * @return string|array
     */
    private function href_base(string $accion, string $id, string $seccion, string $session_id): string|array
    {
        $valida = (new validaciones_directivas())->valida_href(accion: $accion,id:  $id,seccion:  $seccion,
            session_id:  $session_id);
        if(errores::$error){
            return $this->error->error('Error al validar datos', $valida);
        }

        return "./index.php?seccion=$seccion&accion=$accion&session_id=".$session_id."&registro_id=$id";
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $accion
     * @return array|stdClass
     */
    private function init_link(array $accion): array|stdClass
    {
        $keys = array('seccion_descripcion','accion_descripcion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $accion);
        if(errores::$error){
            return $this->error->error('Error al validar $accion', $valida);
        }


        if(!isset($accion['accion_icono'])){
            $accion['accion_icono'] = '';
        }
        $seccion_envio = $accion['seccion_descripcion'];
        $accion_descripcion_envio = $accion['accion_descripcion'];
        $title = (new etiquetas())->title(txt: $accion_descripcion_envio);
        if(errores::$error){
            return $this->error->error('Error al generar titulo', $title);
        }
        $icono = $accion['accion_icono'];

        $data = new stdClass();

        $data->accion = $accion;
        $data->seccion_envio = $seccion_envio;
        $data->accion_descripcion_envio = $accion_descripcion_envio;
        $data->title = $title;
        $data->icono = $icono;

        return $data;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param array $row
     * @return array
     */
    private function limpia_keys_accion(array $row): array
    {
        $keys = array('seccion_menu_etiqueta_label','accion_etiqueta_label');
        foreach ($keys as $key){
            if(!isset($row[$key])){
                $row[$key] = '';
            }
        }
        return $row;
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
     * PROBADO - P ORDER P INT
     * @param array $accion
     * @param string $accion_envio
     * @param string $id
     * @param string $seccion
     * @param string $session_id
     * @return array|stdClass
     */
    private function link_accion_base(array $accion, string $accion_envio, string $id, string $seccion, string $session_id): array|stdClass
    {
        $href = $this->href_base(accion: $accion_envio, id: $id,seccion:  $seccion, session_id: $session_id);
        if(errores::$error){
            return $this->error->error('Error al generar href', $href);
        }


        $data_accion = new stdClass();
        $data_accion->href = $href;
        $data_accion->modal = '';
        $data_accion->btn_modal = '';

        if(isset($accion['accion_es_modal'])&&$accion['accion_es_modal'] === 'activo'){

            $data_accion = (new listas())->data_accion_limpia();
            if(errores::$error){
                return $this->error->error('Error al generar data accion modal', $data_accion);
            }

        }
        return $data_accion;
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

    /**
     * PROBADO - P ORDER P INT
     * @param string $etiqueta
     * @param stdClass $init
     * @param string $st_btn
     * @return string
     */
    private function txt_link(string $etiqueta, stdClass $init, string $st_btn): string
    {
        $texto_link = "<button class='btn btn-sm btn-$st_btn'><i class='$init->icono'></i> $etiqueta</button>";

        if ($init->icono === '') {
            $texto_link = $init->title;
        }

        return $texto_link;
    }
}

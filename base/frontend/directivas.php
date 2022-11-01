<?php //DEBUG FIN
namespace base\frontend;


use gamboamartin\errores\errores;

use JetBrains\PhpStorm\Pure;

use PDO;

class directivas  {
    private errores $error;
    public errores $errores;
    private validaciones_directivas $validacion;

    public array $input = array();
    public string $html;


    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->errores = new errores();
        $this->validacion = new validaciones_directivas();

    }


    /**
     * NO SE MEUEVE
     * @param string $session_id
     * @return string
     */
    public function hidden_session_id(string $session_id):string{
        return "<input type='hidden'   name='session_id' value='" . $session_id . "' >";
    }

    /**
     * NO SE MUEVE
     * @param int $cols
     * @param string $campo
     * @param string $tipo_letra
     * @param bool $con_label
     * @param bool $ln
     * @param bool $required
     * @param string $etiqueta
     * @param string $value
     * @param bool $disabled
     * @param array $data_extra
     * @param bool $value_vacio
     * @return array|string
     */
    public function hora(int $cols, string $campo,string $tipo_letra='capitalize', bool $con_label=true,
                         bool $ln=false, bool $required=false, string $etiqueta = '', string $value="",
                         bool $disabled=false, array $data_extra = array(), bool $value_vacio = false){ //FIN PROT

        if($etiqueta === ''){
            $etiqueta = ucwords($campo);
        }
        if($value === '' && !$value_vacio){
            $value = date('Y-m-d');
        }

        if($etiqueta === ''){
            return $this->errores->error('Error $etiqueta no puede venir vacio',$etiqueta);
        }
        if($tipo_letra === ''){
            return $this->errores->error('Envie un tipo de letra valido capitalize normal',$tipo_letra);
        }
        $valida = $this->validacion->valida_elementos_base_input($campo,$cols);
        if(isset($valida['error'])){
            return $this->errores->error('Error al validar',$valida);
        }


        $html = '';
        if($ln){
            $html = $html."<div class='col-md-12'></div>";
        }
        $disabled_html = '';
        if($disabled){
            $disabled_html = 'disabled';
        }

        $required_html = '';
        if($required){
            $required_html = 'required';
        }
        $html = $html."
		<div class='form-group col-md-$cols'>";
        if($con_label) {
            $html = $html . "<label for='$campo'></label>";
        }

        $data_extra_html = '';

        foreach($data_extra as $key=>$dato){
            $data_txt = "data-$key = '$dato'";
            $data_extra_html.= ' '.$data_txt;
        }


        $html = $html."
			<input 
				type='time' class='form-control input-md' name='$campo' id='$campo' placeholder='Ingresa ' 
				$required_html title='Ingrese una $campo' value='$value' $disabled_html $data_extra_html>
		</div>";
        return $html;
    }

    /**
     * NO SE MUEVE
     * @param string $src
     * @param int $css_id
     * @param array $class_css
     * @param array $data_extra
     * @return string|array
     */
    public function img_btn_modal(string $src, int $css_id, array $class_css = array(), array $data_extra = array()): string|array
    {
        if($css_id<=0){
            return $this->errores->error('Error $css_id debe ser mayor a 0',$css_id);
        }

        $class_html = '';
        foreach ($class_css as $class){
            $class_html.=' '.$class;
        }

        $img = '<img class="img-thumbnail '.$class_html.'" src="'.$src.'" data-foto_previa_id = "'.$css_id.'"';
        $img.= ' role="button" data-toggle="modal" data-target="#_'.$css_id.'">';
        return $img;
    }




    /**
     * NO SE MUEVE
     * @param string $lector
     * @return string
     */
    public function lector_qr(string $lector): string
    {
        $html = "<div id='$lector'></div>";
        $html .= "<script>
                    var html5QrcodeScanner = new Html5QrcodeScanner('$lector', { fps: 10, qrbox: 550 });
                        function onScanSuccess(qrCodeMessage) {
                            // handle on success condition with the decoded message
                            alert(qrCodeMessage);
                            html5QrcodeScanner.clear();
                            // ^ this will stop the scanner (video feed) and clear the scan area.
                        }
                    
                    html5QrcodeScanner.render(onScanSuccess);
                    
                   </script>";
        return $html;
    }

    /**
     * NO SE MUEVE
     * @param string $seccion
     * @param string $accion
     * @param string $label
     * @param int $registro_id
     * @param bool $exe_header
     * @param string $id_css
     * @return string
     */
    public function link_btn(string $seccion, string $accion, string $label, int $registro_id, string $session_id, bool $exe_header=true,
                             string $id_css = ''): string
    {
        $href = "index.php?seccion=$seccion";
        $href .= "&accion=$accion&session_id=" . $session_id . "&registro_id=$registro_id";

        if(!$exe_header){
            $href = '#';
        }

        return "<a class='btn btn-success col-md-12' id='$id_css' href='$href'>$label</a>";
    }

    /**
     * NO SE MEUEVE
     * @param string $seccion
     * @param string $accion
     * @param int $registro_id
     * @param string $session_id
     * @return array|string
     */
    public function link_desvalida(string $seccion, string $accion, int $registro_id, string $session_id): array|string
    {
        $link = (new links())->link_accion(accion: $accion, icon: 'bi bi-arrow-counterclockwise',
            registro_id: $registro_id, seccion: $seccion, session_id: $session_id, styles: array('btn-success'));
        if(errores::$error){
            return $this->errores->error("Error al generar boton", $link);
        }

        return $link;
    }

    /**
     * NO SE MUEVE
     * @param string $seccion
     * @param string $accion
     * @param int $registro_id
     * @return array|string
     */
    public function link_elimina(string $seccion, string $accion, int $registro_id, string $session_id): array|string
    {

        $link = (new links())->link_accion(accion: $accion, icon: 'bi bi-eraser',
            registro_id: $registro_id, seccion: $seccion, session_id: $session_id, styles: array('btn-danger'));
        if(errores::$error){
            return $this->errores->error("Error al generar boton", $link);
        }

        return $link;
    }

    /**
     * NO SE MUEVE
     * @param string $url
     * @param string $etiqueta
     * @return string
     */
    public function link_externo(string $url, string $etiqueta): string
    {
        $link = '<div class="col-md-12">';
        $link .= '<a href="'.$url.'" class="btn btn-primary btn-sm col-md-12" role="button" aria-disabled="true" target="_blank">'.$etiqueta.'</a>';
        $link .= '</div>';
        return $link;
    }

    /**
     * NO SE MEUEVE
     * @param string $seccion
     * @param string $accion
     * @param int $registro_id
     * @return array|string
     */
    public function link_pdf(string $seccion, string $accion, int $registro_id, string $session_id): array|string
    {

        $link = (new links())->link_accion(accion: $accion, icon: 'bi bi-cloud-check-fill',
            registro_id: $registro_id, seccion: $seccion, session_id: $session_id, styles: array('btn-success'));
        if(errores::$error){
            return $this->errores->error("Error al generar boton", $link);
        }

        return $link;
    }

    /**
     * NO SE MUEVE
     * @param string $seccion
     * @param string $accion
     * @param int $registro_id
     * @return array|string
     */
    public function link_timbra(string $seccion, string $accion, int $registro_id, string $session_id): array|string
    {

        $link = (new links())->link_accion(accion: $accion, icon: 'bi bi-code-slash',
            registro_id: $registro_id, seccion: $seccion, session_id: $session_id, styles: array('btn-warning'));
        if(errores::$error){
            return $this->errores->error("Error al generar boton", $link);
        }

        return $link;
    }

    /**
     * NO SE MEUEVE
     * @param string $seccion
     * @param string $accion
     * @param int $registro_id
     * @return array|string
     */
    public function link_xls(string $seccion, string $accion, int $registro_id, string $session_id): array|string
    {

        $link = (new links())->link_accion(accion: $accion, icon: 'bi bi-arrow-counterclockwise',
            registro_id: $registro_id, seccion: $seccion, session_id: $session_id, styles: array('btn-success'));
        if(errores::$error){
            return $this->errores->error("Error al generar boton", $link);
        }

        return $link;
    }

    /**
     * NO SE MEUEV
     * @param string $seccion
     * @param string $accion
     * @param int $registro_id
     * @return array|string
     */
    public function link_xml(string $seccion, string $accion, int $registro_id, string $session_id): array|string
    {

        $link = (new links())->link_accion(accion: $accion, icon: 'bi bi-cloud-download-fill',
            registro_id: $registro_id, seccion: $seccion, session_id: $session_id, styles: array('btn-info'));
        if(errores::$error){
            return $this->errores->error("Error al generar boton", $link);
        }

        return $link;
    }

    /**
     * P ORDER P INT
     * @return string
     */
    public function modal_ejecuta_accion():string{
        $html = '<div class="modal fade modal-accion" id="modalAccion" tabindex="-1" aria-labelledby="accionLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="accionLabel">Accion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="accion_modal">
                    Accion
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>';
        return $html;
    }

    /**
     *
     *  NO SE MEUEV
     * @param string $url_tb_image
     * @param string $css_id
     * @param array $class_tb_html
     * @return array|string
     */
    public function modal_foto(string $url_tb_image, string $css_id, array $class_tb_html = array()): array|string
    {
        $img_tb = $this->img_btn_modal($url_tb_image, $css_id, $class_tb_html);
        if(errores::$error){
            return $this->errores->error('Error al obtener tb',$img_tb);
        }

        return $img_tb;
    }


    /**
     * NO SE MUEVE
     * @param int $cols
     * @param string $valor
     * @param string $etiqueta
     * @param bool $ln
     * @param string $campo
     * @param array $data_extra
     * @param $size
     * @param array $class_css
     * @return array|string
     */
    public function switch(int $cols = 4, string $valor = 'inactivo', string $etiqueta = '', bool $ln = true,
                           string $campo  = '', array $data_extra = array(),$size='md', array $class_css = array()):array|string{ //PROTFIN

        if($valor!=='activo' && $valor !=='inactivo'){
            return $this->error->error('Error $checked debe ser activo o inactivo',array($campo,$valor));
        }

        $checked_html = '';
        if($valor==='activo'){
            $checked_html = 'checked';
        }
        $salto = '';
        if($ln){
            $salto = "<div class='col-$size-12'></div>";
        }

        $name_id = "id_$campo";
        $id_html = 'id = "'.$name_id.'"';

        $data_extra_html = '';

        foreach ($data_extra as $key=>$value){
            $data_extra_html .= "data-$key = '$value'";
        }

        $class_css_html = '';
        foreach ($class_css as $c_css){
            $class_css_html.=" $c_css ";
        }

        $class ='class="checkboxes custom-control-input '.$class_css_html.' "';


        $data_etiqueta = "<label class='custom-control-label col-form-label-$size' for='$name_id' >".$etiqueta."</label>";

        $data_input = "<input type='checkbox' name='".$campo."' value=".$valor." $class $id_html $data_extra_html  $checked_html>";

        $html = "
                $salto
                <div class='form-group col-$size-".$cols."'>
                    <div class='custom-control custom-switch'>
                    $data_input
                        $data_etiqueta
                        
		            </div>
		        </div>
		        ";

        if($etiqueta === ''){
            $html = $data_input;
        }


        return $html;
    }


    /**
     * P INT
     * Genera un input de tipo textarea
     *
     * @param string $campo_name
     * @param int $cols numero de columnas entre 1 y 12
     * @param string $value
     * @param string $etiqueta
     * @return string|array html con info del input a mostrar
     * @example
     *      $data_html = $directiva->textarea($this->valor, $this->cols, $this->campo,$con_label);
     *
     * @uses templates
     * @internal $this->genera_texto_etiqueta($campo,$tipo_letra);
     */
    public function textarea(string $campo_name,int $cols, string $value, string $etiqueta):string|array{ //FIN PROT
        if($campo_name === ''){
            return  $this->error->error('Error $campo no puede venir vacio',$campo_name);
        }
        if($cols <0){
            return  $this->error->error('Error cols debe ser mayor a 0',$cols);
        }
        if($cols >12){
            return  $this->error->error('Error cols debe ser menor a 12',$cols);
        }

        $html = "<div class='form-group col-md-$cols'>";
        if(trim($etiqueta)!=='') {

            $html = $html . "
			<label for='$campo_name'>$etiqueta</label>";
        }
        $html = $html."
			<textarea 
				class='form-control noresize' name='$campo_name' placeholder='Ingresa $etiqueta' 
				title='Ingrese $etiqueta'>$value</textarea>
		</div>";

        return $html;
    }


}


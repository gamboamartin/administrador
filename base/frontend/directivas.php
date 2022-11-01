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
     * NO SE MUEVE
     * Genera y asigna los breadcrumbs de una vista
     *
     * @param string $seccion Seccion de un controlador o modelo
     * @param string $accion
     * @param array $etiquetas conjunto de etiquetas a generar
     * @param string $session_id
     * @return array|string html con info de los breadcrumbs
     * @example
     *      $breadcrumbs = $this->genera_breadcrumbs(array('lista'));
     *
     * @internal  $this->breadcrumbs_con_label($etiquetas);
     * @internal  $this->directiva->nav_breadcumbs($breadcrumbs,$this->seccion,$this->accion, $this, $valida_accion);
     * @uses  controlador_entrada
     * @uses  controlador_reporte
     * @uses  controlador_salida
     */
    public function genera_breadcrumbs(string $seccion, string $accion, array $etiquetas, string $session_id):array|string{
        if($seccion === ''){
            return  $this->errores->error("Error la seccion esta vacia",$seccion);
        }
        if($accion === ''){
            return  $this->errores->error('$accion no puede venir vacia',$accion);
        }
        $breadcrumbs = (new menus())->breadcrumbs_con_label_html(etiquetas_accion: $etiquetas,seccion:  $seccion);
        if(errores::$error){
            return  $this->errores->error('Error al generar breads',$breadcrumbs);
        }

        $data_breadcrumbs = $this->nav_breadcumbs($breadcrumbs,$seccion,$accion,$session_id);
        if(errores::$error){
            return  $this->errores->error(mensaje: 'Error al generar nav breads',data: $data_breadcrumbs,
                params: get_defined_vars());
        }


        return $data_breadcrumbs;
    }

    /**
     * NO SE MUEVE
     * @param array $registro
     * @param string $seccion
     * @param array $encabezados
     * @param array $registro_renombrado
     * @return array|string
     */
    public function genera_encabezados(array $registro,string $seccion, array $encabezados, array $registro_renombrado=array()):array|string{ //PROTFIN

        $namespase = 'models\\';
        $seccion = str_replace($namespase,'',$seccion);
        $clase = $namespase.$seccion;
        if($seccion === ''){
            return $this->error->error('Error seccion no puede venir vacio',$seccion);
        }
        if(!class_exists($clase )){
            return $this->error->error('Error no existe clase '.$clase,$clase);
        }

        if(count($registro_renombrado)>0){
            $encabezados = array();
            foreach($registro_renombrado as $key=>$valor){
                $encabezados[] = array('elemento_lista_etiqueta'=>$key,'elemento_lista_descripcion'=>$key);
                $registro[$key] = $valor;
            }
        }

        $encabezado_html = '';
        $encabezado_html_print='';

        foreach ($encabezados as $encabezado){
            if((string)trim($encabezado['elemento_lista_etiqueta']) === ''){
                return $this->error->error('Error $encabezado[elemento_lista_etiqueta] viene vacio',$encabezado);

            }
            if((string)trim($encabezado['elemento_lista_descripcion']) === ''){
                return $this->error->error('Error $encabezado[elemento_lista_descripcion] viene vacio',$encabezado);
            }
            $campo = strtolower(trim($encabezado['elemento_lista_descripcion']));
            if(!isset($registro[$campo])){
                $registro[$campo] = '';
            }

            $etiqueta = strtoupper($encabezado['elemento_lista_etiqueta']);
            $valor = $registro[$campo];


            $encabezado_html_print = $encabezado_html_print."<label> $etiqueta: </label> <span> $valor | </span>";
        }

        $html = "<div class='row generales_pago_cliente sombra-borde encabezado'>$encabezado_html</div>";
        $html  = $html."<div class='encabezado_print'>$encabezado_html_print</div>";

        return $html;

    }


    /**
     * ERRORREV
     * Genera los inputs de un conjunto de selects
     *
     * @param string $tabla Tabla o estructura
     * @param array $columnas Conjunto de columnas a mostrar en select
     * @param array $data_extra Extra params
     * @param array $valores Valores a integrar
     * @param PDO $link Conexion a la base de datos
     * @param array $campos_permitidos Campos permitidos para options info
     * @param array $inputs Inputs previos cargados
     * @param array $campos_invisibles Campos que nose mostraran en template
     * @param string $campo Campo a integrar
     * @param bool $required Si required el input queda requerido
     * @param array $registros
     * @param bool $todos Si todos genera todos los registros completos
     * @param bool $select_vacio_alta
     * @param bool $disabled aplica o no disabled a input
     * @return array definidos en internals
     * @example
     *      $data_html = $controlador->genera_input_select_columnas_template($tabla, $columnas, $data_extra,$valores,$required, $parametros['registros'],$parametros['todos'],$select_vacio_alta);
     * @internal  $this->validacion->valida_estructura_input_base($columnas,$tabla);
     * @internal  $directiva->input_select_columnas($tabla,(string)$valores[$campo], 4,false,$columnas, $this->link,$required, 'capitalize', false,$select_vacio_alta,$registros,$data_extra, array(),true,'','',$todos);

     */
    public function genera_input_select_columnas_template(
        array $columnas, array $data_extra, string $tabla, array $valores, PDO $link, array $campos_permitidos,
        array $inputs, array $campos_invisibles, string $campo, bool $disabled = false, bool $required = true,
        array $registros=array(), bool $todos = false, bool $select_vacio_alta=false): array{

        $valida = $this->validacion->valida_estructura_input_base(columnas: $columnas,tabla: $tabla);
        if(errores::$error) {
            return $this->errores->error(mensaje: 'Error al validar estructura de input',data: $valida);
        }


        if(!isset($valores[$campo])){
            $valores[$campo] = '';
        }



        if(count($campos_permitidos) === 0){
            $inputs[$campo] = '';
        }
        if(in_array($campo, $campos_invisibles, true)){
            $inputs[$campo] = '';
        }

        return $inputs;
    }


    /**
     * NO SE MUEVE
     * Genera un input con un pattern para telefono
     *
     * @param string $campo Nombre del campo
     * @param array $valores valores default de los inputs a mostrar
     * @param array $inputs
     * @param int $cols columnas css
     * @param string $pattern
     * @return mixed html con info del input a mostrar dependiendo el tipo de input
     * @example
     *      $data_html = $controlador->genera_input_text_template($input,$controlador->valores,array(),true);
     *
     * @internal  $this->obten_data_input_template($campo,$valores);
     * @internal  $this->asigna_campo_template($campo,$data_input_template['valores'],$data_input_template['etiqueta'],$clases_css,'text',$required);
     */
    public function genera_input_text_template(string $campo, array $valores, array $inputs, int $cols = 4, string $pattern = ''): array{
        if($campo === ''){
            return $this->errores->error('Error $campo no puede venir vacio',$campo);
        }


        $data_input_template = (new values())->obten_data_input_template($campo,$valores);
        if(errores::$error){
            return $this->errores->error('Error al obtener datos ',$data_input_template);
        }


        return $inputs;
    }


    /**
     * PARAMS ORDER P INT
     * Genera un contenedor div con un select
     * @param string $campo_name
     * @param string $llaves_valores
     * @param string $css_id
     * @param int $cols
     * @param string $etiqueta
     * @param mixed $valor
     * @return array|string informacion de select en forma html
     * @example
     *     $controlador->inputs['estado_civil'] =  $directiva->genera_select_estatico('Selecciona una opción:,Soltero:soltero,Casado:casado', 4,'estado_civil', 'Estado civil', false, false, $valores['estado_civil']);
     * @uses  clientes
     * @uses  templates
     * @internal $this->valida_elementos_base_input($input_name,$cols);
     */
    public function genera_select_estatico(string $campo_name, string $llaves_valores,string $css_id = '',
                                           int $cols = 4, string $etiqueta = '', mixed $valor =''):array|string{
        if(trim($llaves_valores) === ''){
            return $this->error->error('Error $llaves_valores debe venir en formato json string',$llaves_valores);
        }
        $valida = $this->validacion->valida_elementos_base_input(cols:$cols, tabla: $campo_name);
        if(errores::$error){
            return $this->error->error('Error en validacion entrada',$valida);
        }

        $select_input_name = 'select_'.$campo_name;

        if($css_id!==''){
            $select_input_name = $css_id;
        }


        $elementos_select = (new selects())->elementos_for_select_fijo(llaves_valores: $llaves_valores);
        if(errores::$error){
            return $this->error->error('Error al data para option', $elementos_select);
        }



        $html = "<div class='form-group col-md-$cols' id='contenedor_select_$campo_name'>";

        $html .= "<label for='$etiqueta'>$etiqueta</label>";
        $html .= "<select name='" . $campo_name . "'  class='form-control input-md' 
                   title='Seleccione un  ' id='$select_input_name'>";


        $options = (new selects())->options_for_select(elementos_select: $elementos_select,valor:  $valor);
        if(errores::$error){
            return $this->error->error('Error al generar options', $options);
        }

        $html.=$options;

        $html .= "</select></div>";



        return $html;


    }


    /**
     * NO SE MEUVE
     * @param string $name
     * @param string $value
     * @return string|array
     */
    public function hidden(string $name, string $value):string|array{
        $name = trim($name);
        if($name === ''){
            return $this->error->error('Error name no puede venir vacio',$name);
        }
        return "<input type='hidden'   name='" . $name . "' value='" . $value . "' >";
    }

    /**
     * NO SE MEUEVE
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
     * Genera el html de la barra de navegacion
     *
     * @param array $breadcrumbs Arreglo con parametro para generacion
     * @param string $seccion Seccion para su aplicacion
     * @param string $accion para definir accion active
     * @param string $session_id
     * @return array|string html para incrustarlo y mostrarlo
     * @example
     *      $breadcrumbs = $controlador->breadcrumbs_con_label(array('alta', 'lista'));
     * $data_breadcrumbs = $controlador->directiva->nav_breadcumbs($breadcrumbs,$controlador->tabla,'modifica', $controlador);
     *
     * @uses clientes
     * @uses controlador_cliente
     * @uses controlador_grupo
     * @uses controlador_seccion_menu
     * @internal $this->breadcrumbs_con_label($link, $seccion, $accion,$valida_accion);
     * @internal $this->breadcrumbs($breadcrumbs, $accion, $seccion);
     */
    public function nav_breadcumbs(array $breadcrumbs, string $seccion, string $accion, string $session_id):array|string{

        if($seccion === ''){
            return $this->error->error(mensaje: "Error la seccion esta vacia",data: $seccion, params: get_defined_vars());
        }
        if($accion === ''){
            return $this->error->error('$accion no puede venir vacia',$accion);
        }

        $breads = (new menus())->breadcrumbs_con_label( $seccion, $accion);
        if(errores::$error){
            return  $this->error->error('Error al generar breads', $breads);
        }

        $seccion = $breads->seccion;
        $accion = $breads->accion;
        $breadcrumbs_html = (new menus())->breadcrumbs($breadcrumbs, $accion, $seccion, $session_id);

        if(errores::$error){
            return  $this->error->error('Error al generar breads', $breadcrumbs_html);
        }

        $html ='<div class="container-fluid text-left titulo">
          <h4>'.$breads->seccion_br.' '.$breads->accion_br.'</h4>
            '.$breadcrumbs_html.'
      </div>';


        return $html;
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


<?php //DEBUG FIN
namespace base\frontend;

use gamboamartin\errores\errores;
use gamboamartin\orm\modelo_base;
use JetBrains\PhpStorm\Pure;

use PDO;
use stdClass;

class directivas extends html {
    private errores $error;
    public errores $errores;

    public array $input = array();
    public string $html;


    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->errores = new errores();

        parent::__construct();
    }


    /**
     * P INT
     * Genera un input con un pattern para telefono
     *
     * @param array $inputs
     * @param string $campo Nombre del campo
     * @param array $valores valores default de los inputs a mostrar
     * @param string $etiqueta Etiqueta a mostrar en input es un label
     * @param array $clases_css base de tipo css que se asignaran al input
     * @param string $tipo_campo text o telefono
     * @param bool $required si el elemento es requerido asigna required al html
     * @param bool $disabled aplica o no disabled
     * @param int $cols columnas css
     * @param string $pattern Regex de ejecucion en html5
     * @param bool $ln
     * @return array|string html con info del input a mostrar dependiendo el tipo de input
     * @example
     *      $this->inputs[$campo] = $this->asigna_campo_template($campo,$data_input_template['valores'],
     *          $data_input_template['etiqueta'],$clases_css,'telefono',$required);
     */
    private function asigna_campo_template(array $inputs, string $campo, array $valores, string $etiqueta,
                                           array $clases_css, string $tipo_campo, bool $required = false,
                                           bool $disabled = false, int $cols = 4, string $pattern = '',
                                           bool $ln = false):array|string{

        if(count($valores) === 0){
            return $this->errores->error('Valores no puede venir vacio',$valores);
        }
        if($tipo_campo === ''){
            return $this->errores->error('El tipo campo no puede venir vacio ' . $campo,$tipo_campo);
        }
        if($tipo_campo !=='text' && $tipo_campo !=='telefono'){
            return $this->errores->error('El tipo campo debe ser text o telefono ' . $campo,$tipo_campo);
        }
        if(!isset($valores[$campo])){
            $valores[$campo] = '';
        }
        if($etiqueta === ''){
            return $this->errores->error('Etiqueta no puede venir vacio ' . $campo,$etiqueta);
        }



        if($tipo_campo === 'text') {

            $inputs[$campo] = $this->genera_input_text(campo: $campo,cols:  $cols,value:  $valores[$campo],
                required: $required,disabled: $disabled,ln:  $ln,etiqueta: $etiqueta, pattern: $pattern,
                data_extra:  array(), clases_css: $clases_css, ids_css: array());
            if (errores::$error) {
                return $this->errores->error('Error al generar ' . $campo,$inputs[$campo]);
            }
        }
        if($tipo_campo === 'telefono'){
            $inputs[$campo] =  $this->telefono(campo: $campo,cols: $cols, value: $valores[$campo],required:  $required,
                disabled: $disabled,ln:  $ln,etiqueta: $etiqueta, css_id: '', data_extra: array(),
                 tipo_letra: 'capitalize',clases_css: $clases_css,ids_css: array(),size: 'md');
            if(errores::$error){
                return $this->errores->error('Error al generar '.$campo,$inputs[$campo]);
            }
        }

        return $inputs[$campo];
    }

    /**
     * NO SE MUEVE
     * @param string $seccion
     * @param string $accion
     * @param int $id
     * @param string $etiqueta
     * @param string $session_id
     * @param array $class_css
     * @param string $icon
     * @return string
     */
    public function boton_link(string $seccion, string $accion, int $id, string $etiqueta, string $session_id, array $class_css = array(),
                               string $icon = ''): string
    {

        $class_css_html = '';
        foreach ($class_css as $css){
            $class_css_html.=" $css ";
        }

        if($class_css_html === ''){
            $class_css_html = 'btn-primary';
        }

        if($icon !==''){
            $icon = "<i class='$icon'></i>";
        }

        $link = "./index.php?seccion=$seccion&accion=$accion&session_id=".$session_id;
        $link .= "&registro_id=$id";
        return "<a class='btn $class_css_html' href='$link' role='button'>$icon $etiqueta </a>";
    }

    /**
     *
     * @return array|string
     */
    private function btn_busca():array|string{

        $btn_busca = $this->btn_enviar(cols: 1,label: 'Busca',name: 'busca',value: 'busca');
        if(errores::$error){
            return $this->errores->error('Error al genera btn',$btn_busca);
        }
        return $btn_busca;
    }

    /**
     * P INT
     * Genera el html de un boton
     *
     * @param int $cols Tipo de letra para realizar ajuste de texto capitalize, mayusculas, minusculas
     * @param string $label Texto a mostrar en el boton
     * @param string $name name del input
     * @param string $value $value del input
     * @param string $type submit o button
     * @param string $stilo forma del boton success warning etc
     * @param array $class_css Clases css a incrustar en div
     * @param string $icon
     * @param string $id_css
     * @param array $datas
     * @return array|string string palabra ajustada
     * @example
     *      $this->btn = $this->directiva->btn_enviar(4,'Modifica','btn_modifica','modifica');
     *
     * @uses  templates
     * @uses  almacenes
     * @uses  clientes

     */
    public function btn_enviar(int $cols = 12, string $label = 'Enviar', string $name= 'Enviar',
                               string $type = 'submit', string $value = 'Enviar', string $stilo = 'primary',
                               array $class_css = array(), string $icon = '', string $id_css = '',
                               array $datas = array()):array|string{

        $valida = $this->validacion->valida_data_btn(cols: $cols,label:  $label,name:  $name,value:  $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar boton', data: $valida, params: get_defined_vars());
        }

        $params = (new botones())->data_btn(class_css: $class_css, datas: $datas, icon: $icon);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar parametros', data: $params,params: get_defined_vars());
        }

        $button = (new botones())->button(cols:  $cols, id_css: $id_css,label: $label, name:  $name,  params: $params,
            stilo: $stilo,type: $type,value:  $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar container', data: $button);
        }

        return $button;
    }

    /**
     * NO SE MUEVE
     * @param string $etiqueta_campo_busca
     * @param string $campo_busca
     * @param string $valor_busca_fault
     * @return array
     */
    private function busqueda_base(string $etiqueta_campo_busca, string $campo_busca, string $valor_busca_fault): array
    {
        $btn_busca = $this->btn_busca();

        if(errores::$error){
            return $this->errores->error('Error al genera btn',$btn_busca);
        }

        $campo_busca_r = $this->campo_busca($etiqueta_campo_busca, $campo_busca, $valor_busca_fault);
        if(errores::$error){
            return $this->errores->error('Error al campo de busqueda',$campo_busca_r);

        }

        return array('campo_busca'=>$campo_busca_r,'btn_busca'=>$btn_busca);
    }

    /**
     * NO SE MUEVE
     * @param string $etiqueta_campo_busca
     * @param string $campo_busca
     * @param string $valor_busca_fault
     * @return array|string
     */
    private function campo_busca(string $etiqueta_campo_busca, string $campo_busca, string $valor_busca_fault): array|string
    {
        $campo_busca_r = $this->genera_input_text($campo_busca,1,$valor_busca_fault,
            true,false, false,$etiqueta_campo_busca,'','', array(),'capitalize',array(),array());
        if(errores::$error){
            return $this->errores->error('Error al campo de busqueda',$campo_busca_r);
        }
        return $campo_busca_r;
    }

    /**
     * PROBADO-PARAMS ORDER P INT
     * Genera un input checkbox
     * @param string $campo Nombre del campo input
     * @param int $cols
     * @param string $valor
     * @param bool $disabled Si disabled retorna text disabled
     * @param bool $ln
     * @param string $etiqueta
     * @param string $css_id
     * @param array $data_extra
     * @return array|string informacion de input en forma html
     * @example
     *      $es_referido = $controlador->directiva->checkbox(4,'inactivo','Es Referido',true,'es_referido');
     *
     * @uses  clientes
     * @uses  templates
     * @uses  controlador_cliente
     * @uses  controlador_contrarecibo
     */
    public function checkbox(string $campo,string $css_id = '', int $cols = 4, array $data_extra = array(),
                             bool $disabled = false, string $etiqueta = '', bool $ln = true,
                             string $valor = 'inactivo'):array|string{

        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error campo vacio', $campo);
        }

        $class ="class='checkboxes'";

        $params_base = (new params_inputs())->params_chk(css_id:  $css_id,ln: $ln,valor:$valor);
        if(errores::$error){
            return $this->error->error('Error al generar parametros',$params_base);
        }

        $data_extra_html = (new extra_params())->data_extra_html(data_extra: $data_extra);
        if(errores::$error){
            return $this->error->error('Error al generar data-extra',$data_extra_html);
        }

        $data_etiqueta = (new etiquetas())->etiqueta_chk(etiqueta: $etiqueta);
        if(errores::$error){
            return $this->error->error('Error al generar etiqueta',$data_etiqueta);
        }

        $disabled_html = (new params_inputs())->disabled_html(disabled: $disabled);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar disabled',data: $disabled_html);
        }

        $data_chk = (new checkboxes())->data_chk(campo: $campo, checked_html: $params_base->checked_html,
            class: $class,cols:  $cols,data_etiqueta: $data_etiqueta,data_extra_html:  $data_extra_html,
            disabled_html: $disabled_html, id_html: $params_base->id_html, valor: $valor);
        if(errores::$error){
            return $this->error->error('Error al generar datos chk',$data_chk);
        }

        $checkbox = (new checkboxes())->checkbox(data_input:  $data_chk->data_input, div_chk: $data_chk->div_chk,
            etiqueta:  $etiqueta,salto: $params_base->salto);
        if(errores::$error){
            return $this->error->error('Error al generar checkbox',$checkbox);
        }


        return $checkbox;
    }

    /**
     * NO SE MUEVE
     * @param string $seccion Seccion del controlador a ejecutar
     * @param string $accion Accion del controlador a ejecutar
     * @param int $registro_id Identificador del registro
     * @param array $var_get_extra Variables por get que se naden extra forma es array(name_variable=>value)
     * @return array|string
     */
    public function data_form_id(string $seccion, string $accion, int $registro_id, array $var_get_extra): array|string
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->errores->error('Error $seccion no puede venir vacio',$seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->errores->error('Error $accion no puede venir vacio',$accion);
        }

        if($registro_id <=0){
            return $this->errores->error('Error $registro_id debe ser mayor a 0',$registro_id);
        }

        $action_form = (new forms())->action_form_id($seccion, $accion, $registro_id,$var_get_extra);
        if(errores::$error){
            return $this->errores->error("Error al generar accion_form", $action_form);
        }
        $data_form_base = (new forms())->data_form_base();
        if(errores::$error){
            return $this->errores->error("Error al generar data_form_base", $data_form_base);
        }

        return 'action="'.$action_form.'" '.$data_form_base;
    }

    /**
     * NO SE MUEVE
     * @param string $seccion
     * @param string $accion
     * @param array $var_get_extra
     * @return array|string
     */
    public function data_form_sin_id(string $seccion, string $accion, array $var_get_extra): array|string
    {
        $seccion = trim($seccion);
        if($seccion === ''){
            return $this->errores->error('Error $seccion no puede venir vacio',$seccion);
        }
        $accion = trim($accion);
        if($accion === ''){
            return $this->errores->error('Error $accion no puede venir vacio',$accion);
        }

        $action_form = (new forms())->action_form($seccion, $accion,$var_get_extra);
        if(errores::$error){
            return $this->errores->error("Error al generar accion_form", $action_form);
        }
        $data_form_base = (new forms())->data_form_base();
        if(errores::$error){
            return $this->errores->error("Error al generar data_form_base", $data_form_base);
        }

        return 'action="'.$action_form.'" '.$data_form_base;
    }

    /**
     * NO SE MUEVE
     * @param string $target
     * @param string $include
     * @return void
     */
    public function data_pestana(string $target, string $include){
        $header_section = '';
        echo  '<div id="'.$target.'" class="collapse" data-parent=".view">';
        require $include;
        echo '</div>';
    }



    /**
     * P INT
     * Genera un input fecha
     * @param string $campo Nombre o identificador del campo del input
     * @param int $cols Columnas para asignacion de html entre 1 y 12
     * @param string $value
     * @param bool $required
     * @param bool $disabled
     * @param bool $ln
     * @param string $etiqueta
     * @param string $pattern
     * @param string $css_id
     * @param array $data_extra
     * @param string $tipo_letra
     * @param bool $value_vacio
     * @param array $css
     * @param string $tipo
     * @param string $size
     * @param array $ids
     * @return array|string informacion de input en forma html
     * @example
     *     $controlador->inputs['fecha'] = $controlador->directiva->fecha(4, 'fecha','capitalize',true, false, true, 'Fecha',$controlador->valores['fecha']);
     *
     * @uses  TODO EL SISTEMA
     * @internal $this->valida_elementos_base_input($campo,$cols);
     * @internal $this->genera_texto_etiqueta($etiqueta, $tipo_letra);
     */
    public function fecha(string $campo, int $cols = 4, bool $required = true, string $value = '',
                          bool $disabled = false, bool $ln = false, string $etiqueta = '', string $pattern = '',
                          string $css_id = '', array $data_extra = array(), string $tipo_letra='capitalize',
                          bool $value_vacio = false, array $css = array(), string $tipo = 'date', string $size = 'md',
                          array $ids = array()):array|string{ //FIN PROT

        if($etiqueta === ''){
            $etiqueta = ucwords($campo);
        }

        if(trim($etiqueta) === ''){
            return $this->error->error("Etiqueta vacia",$etiqueta);
        }
        if($tipo_letra === ''){
            return $this->error->error('Envie un tipo de letra valido capitalize normal',$tipo_letra);
        }

        $valida = $this->validacion->valida_elementos_base_input(cols:$cols, tabla: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar",data: $valida, params: get_defined_vars());
        }

        $con_label = false;
        if($etiqueta!==''){
            $con_label = true;
        }
        $campo_capitalize = (new etiquetas())->genera_texto_etiqueta(texto: $etiqueta,tipo_letra:  $tipo_letra);
        if (errores::$error) {
            return $this->error->error('Error al generar etiqueta',$campo_capitalize);
        }

        $value = (new values())->value_fecha(tipo: $tipo, value: $value, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error('Error al asignar valor',$value);
        }

        $html = '';


        $ln_html = (new params_inputs())->ln(ln: $ln,size:  $size);
        if(errores::$error){
            return $this->error->error('Error al generar ln',$ln_html);
        }

        $html.=$ln_html;


        $params = (new params_inputs())->params_fecha(campo: $campo, css: $css,data_extra: $data_extra,
            disabled: $disabled, ids: $ids, required: $required);
        if (errores::$error) {
            return $this->error->error('Error al generar params',$params);
        }


        $html = $html."
		<div class='form-group col-$size-$cols'>";


        $con_label_html = (new etiquetas())->con_label(campo: $campo, campo_capitalize: $campo_capitalize,
            con_label: $con_label, size:  $size);
        if (errores::$error) {
            return $this->error->error('Error al generar label',$con_label_html);
        }

        $html .= $con_label_html;

        $container_html = $this->html_fecha(campo:  $campo, campo_capitalize:  $campo_capitalize, params: $params,
            size: $size, tipo: $tipo, value: $value);
        if (errores::$error) {
            return $this->error->error('Error al integrar params',$container_html);
        }

        $html.=$container_html;

        $html.="  </div> ";


        return $html;
    }

    /**
     * NO SE MUEVE
     * @param PDO $link
     * @param string $fecha
     * @param array $dia
     * @param array $hora
     * @param array $mes
     * @param array $year
     * @return array|string
     */
    public function fecha_detalle(PDO $link, string $fecha, array $dia, array $hora, array $mes, array $year): array|string
    {
        $select = $this->sl_year($link, $year, $fecha);
        if(errores::$error){
            return $this->errores->error('Error al generar input', $select);
        }
        $input = $select;

        $select = $this->sl_mes($link, $mes, $fecha);
        if(errores::$error){
            return $this->errores->error('Error al generar input', $select);
        }
        $input .= $select;


        $select = $this->sl_dia($link, $dia, $fecha);
        if(errores::$error){
            return $this->errores->error('Error al generar input', $select);
        }
        $input .= $select;
        $select = $this->sl_hora($link, $hora, $fecha);
        if(errores::$error){
            return $this->errores->error('Error al generar input', $select);
        }
        $input .= $select;
        $select = $this->sl_minuto($link, $fecha);
        if(errores::$error){
            return $this->errores->error('Error al generar input', $select);
        }
        $input .= $select;

        return $input;
    }

    /**
     * P INT P ORDER ERORREV
     * @param array $filtro
     * @return array|string
     */
    public function format_filtro_base_html(array $filtro):array|string{
        $filtro_html = '';
        foreach($filtro as $key=>$data){
            if($key === ''){
                return $this->errores->error(mensaje: 'Error el key no puede venir vacio',data:  $key,
                    params: get_defined_vars());
            }
            $data_key = str_replace(array('.', '_'), ' ', $key);
            $data_key = ucwords($data_key);

            $value = $data['value'] ?? $data;
            $filtro_html.='<b>'.$data_key.'</b> contiene : <b>'.$value.' </b>';
        }

        return $filtro_html;

    }

    /**
     * NO SE MUEVE
     * @param array $filtro
     * @return array|string
     */
    public function format_filtro_rango_html(array $filtro):array|string{ //FIN
        $filtro_html = '';
        foreach($filtro as $key=>$value){
            if(is_numeric($key)){
                return $this->errores->error('Error el key debe ser un tabla.valor',$filtro);
            }
            if(!isset($value['valor1'])){
                return $this->errores->error('Error el valor del filtro debe tener valor1',$filtro);
            }
            if(!isset($value['valor2'])){
                return $this->errores->error('Error el valor del filtro debe tener valor2',$filtro);
            }
            $data_key = str_replace(array('.', '_'), ' ', $key);

            $filtro_html.='<b>'.$data_key.'</b> es mayor o igual a: <b>'.$value['valor1'].'</b> y es menor o igual a: <b>'.$value['valor2'].'</b>';
        }

        return $filtro_html;

    }

    /**
     * NO SE MUEVE
     * Genera y asigna los breadcrumbs de una vista
     *
     * @param string $seccion Seccion de un controlador o modelo
     * @param string $accion
     * @param array $etiquetas conjunto de etiquetas a generar
     * @param PDO $link
     * @param array $accion_registro
     * @param string $session_id
     * @param bool $valida_accion valida si la accion existe en el modelo accion
     *
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
    public function genera_breadcrumbs(string $seccion, string $accion, array $etiquetas, PDO $link,
                                       array $accion_registro, string $session_id, bool $valida_accion = true):array|string{
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

        $data_breadcrumbs = $this->nav_breadcumbs(
            $breadcrumbs,$seccion,$accion, $link, $accion_registro,$session_id, $valida_accion);
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

            $data_encabezado = $this->crea_elemento_encabezado(contenido: $valor, label: $etiqueta);
            if(errores::$error){
                return $this->error->error('Error al generar $encabezado_html',$data_encabezado);
            }
            $encabezado_html.=$data_encabezado;
            $encabezado_html_print = $encabezado_html_print."<label> $etiqueta: </label> <span> $valor | </span>";
        }

        $html = "<div class='row generales_pago_cliente sombra-borde encabezado'>$encabezado_html</div>";
        $html  = $html."<div class='encabezado_print'>$encabezado_html_print</div>";

        return $html;

    }

    /**
     * P INT
     * Genera un input para ser mostrado en html del front
     *
     * @param string $campo Nombre del campo
     * @param string $value Valor default a mostrar en el input
     * @param string $tipo_letra tipo de letra
     * @param bool $required si el elemento es requerido asigna required al html
     * @param bool $ln aplica salto de linea
     * @param int $cols columnas a mostrar en md-cols
     *
     * @example
     *      $data_html = $directiva->genera_input_numero($this->campo, $this->cols, $this->valor, $required,'mayusculas',$this->ln);
     *
     * @return array|string html con info del input a mostrar
     * @throws errores definidos en internals
     * @uses templates
     * */
    public function genera_input_numero(string $campo, int $cols, string $value,bool $required, bool $disabled,
                                        bool $ln, string $etiqueta, string $pattern, string $css_id, array $data_extra, string $tipo_letra):array|string{  //FIN PROT

        $valida = $this->validacion->valida_elementos_base_input(cols: $cols, tabla: $campo);
        if(errores::$error){
            return  $this->error->error('Error al validar',$valida);
        }

        $campo_capitalize = (new etiquetas())->genera_texto_etiqueta(texto: $campo,tipo_letra:  $tipo_letra);

        if(errores::$error){
            return  $this->error->error('Error al generar $campo_capitalize',$campo_capitalize);
        }

        $html = "";
        if($ln){
            $html = $html."<div class='col-md-12'></div>";
        }

        $html = $html."<div class=' form-group col-md-$cols'>";

        $html = $html . "<label for='$campo'>$campo_capitalize</label>";

        $required_html = '';
        if($required){
            $required_html = 'required';
        }

        $html = $html."
        <div class='input-group number-spinner'>
				<span class='input-group-btn data-dwn'>
					<div class='btn btn-default menos' data-dir='dwn'>
					    <span class='glyphicon glyphicon-minus'></span>
					   </div>
				</span>
				<input type='text' name='$campo' class='form-control text-center numero_input $campo' value='$value' $required_html>
				<span class='input-group-btn data-up'>
					<div class='btn btn-default mas' data-dir='up'><span class='glyphicon glyphicon-plus'></span></div>
				</span>
				</div></div>";

        return $html;
    }

    /**
     * ERRORREV
     * Genera los inputs de un conjunto de selects
     *
     * @param string $tabla
     * @param array $columnas
     * @param array $data_extra
     * @param array $valores
     * @param PDO $link
     * @param array $campos_permitidos
     * @param array $inputs
     * @param array $campos_invisibles
     * @param string $campo
     * @param bool $required
     * @param array $registros
     * @param bool $todos
     * @param bool $select_vacio_alta
     * @param bool $disabled aplica o no disabled a input
     * @return array definidos en internals
     * @example
     *      $data_html = $controlador->genera_input_select_columnas_template($tabla, $columnas, $data_extra,$valores,$required, $parametros['registros'],$parametros['todos'],$select_vacio_alta);
     * @internal  $this->validacion->valida_estructura_input_base($columnas,$tabla);
     * @internal  $directiva->input_select_columnas($tabla,(string)$valores[$campo], 4,false,$columnas, $this->link,$required, 'capitalize', false,$select_vacio_alta,$registros,$data_extra, array(),true,'','',$todos);
     * @uses  clientes
     */
    public function genera_input_select_columnas_template(string $tabla, array $columnas, array $data_extra,
                                                          array $valores, PDO $link, array $campos_permitidos,
                                                          array $inputs, array $campos_invisibles, string $campo,
                                                          bool $required = true, array $registros=array(),
                                                          bool $todos = false, bool $select_vacio_alta=false,
                                                          bool $disabled = false): array{

        $valida = $this->validacion->valida_estructura_input_base(columnas: $columnas,tabla: $tabla);
        if(errores::$error) {
            return $this->errores->error(mensaje: 'Error al validar estructura de input',data: $valida,
                params: get_defined_vars());
        }


        if(!isset($valores[$campo])){
            $valores[$campo] = '';
        }


        $inputs[$campo] = $this->input_select_columnas(campo_name:$tabla.'_id',cols: 4,valor:  (string)$valores[$campo],
            required: $required, disabled: $disabled,ln: false, etiqueta: $tabla, css_id: '', tabla: $tabla,
            link:$link,select_vacio_alta: $select_vacio_alta, registros: $registros,todos: $todos,
            data_extra:$data_extra);


        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar '.$campo,data: $inputs[$campo],
                params: get_defined_vars());
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
     * P INT
     * Genera un input con un pattern para telefono los asigna a this->inputs
     *
     * @param string $campo Nombre del campo
     * @param array $valores valores default de los inputs a mostrar
     * @param array $inputs Conjunto de inputs precargados para ser mostrados en front
     * @param array $clases_css base de tipo css que se asignaran al input
     * @param bool $required si el elemento es requerido asigna required al html
     * @param int $cols columnas css
     *
     * @return array con this->inputs
     * @example
     *      $data_html = $controlador->genera_input_telefono_template($input,$controlador->valores, $clases_css = array('data_conyugue'));
     *
     * @internal  $this->obten_data_input_template($campo,$valores);
     * @internal  $this->asigna_campo_template()
     * @uses  clientes
     */
    public function genera_input_telefono_template(string $campo, array $valores, array $inputs, array $clases_css= array(),
                                                   bool $required = false,int $cols = 4): array{
        if($campo === ''){
            return $this->errores->error('Error $campo no puede venir vacio',$campo);
        }
        $data_input_template = (new values())->obten_data_input_template(campo: $campo,valores: $valores);
        if(errores::$error){
            return $this->errores->error('Error al obtener datos ', $data_input_template);
        }
        $inputs[$campo] = $this->asigna_campo_template(inputs: $inputs,campo: $campo,valores: $data_input_template['valores'],
            etiqueta: $data_input_template['etiqueta'],clases_css: $clases_css,tipo_campo: 'telefono',required:
            $required,disabled: false,cols: $cols);
        if(errores::$error){
            return $this->errores->error('Error al generar '.$campo, $inputs[$campo]);
        }

        return $inputs;

    }

    /**
     * P INT
     * Genera un input para ser mostrado en html del front
     *
     * @param string $campo Nombre del campo
     * @param int $cols numero de columnas entre 1 y 12
     * @param string $value Valor default a mostrar en el input
     * @param bool $required si el elemento es requerido asigna required al html
     * @param bool $disabled si el elemento es true asigna disabled al html
     * @param bool $ln inserta <div class="col-md-12"></div> antes del input
     * @param string $etiqueta Etiqueta a mostrar en input es un label
     * @param string $pattern expresion regular a validar en el evento submit
     * @param string $css_id Identificador css
     * @param array $data_extra conjunto de data_extra para asignar al input text
     * @param string $tipo_letra ajustes del label de salida para mostrar en html
     * @param array $clases_css base de tipo css que se asignaran al input
     * @param array $ids_css ids de tipo css que se asignaran al input
     * @param bool $aplica_etiqueta si aplica etiqueta mostrar el label del campo si no no integra label
     * @param string $size
     * @return array|string html con info del input a mostrar
     * @example
     *      $input = $directiva->genera_input_text($campo,12,'',true,'',false,'capitalize',false,'',array(),array(),false);
     */
    public function genera_input_text(string $campo, int $cols = 12 , string $value = '', bool $required = true,
                                      bool $disabled = false,bool $ln = false,string $etiqueta = '',
                                      string $pattern = '', string $css_id = '', array $data_extra = array(),
                                      string $tipo_letra = 'capitalize', array $clases_css = array(),
                                      array $ids_css= array(), bool $aplica_etiqueta = true, string $size = 'md'):array|string{ //FIN PROT

        if($etiqueta === ''){
            $etiqueta = $campo;
        }

        $valida = $this->validacion->valida_elementos_base_input(cols: $cols, tabla: $campo);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al validar", data: $valida);
        }
        if($aplica_etiqueta) {
            $etiqueta = str_replace('_',' ',$etiqueta);
            $etiqueta_valida = $this->validacion->letra_numero_espacio(txt: $etiqueta);
            if (!$etiqueta_valida) {
                return $this->error->error(mensaje: "Error etiqueta invalida", data: $etiqueta);
            }
        }

        $html = '';

        if($ln){
            $html .= "<div class='col-md-12'></div>";
        }

        $form  = (new forms())->genera_form_group(campo: $campo, cols: $cols);
        if(errores::$error){
            return $this->error->error('Error al generar $form de '.$campo, $form);
        }

        $html.=$form;

        $label = (new etiquetas())->genera_label(aplica_etiqueta: $aplica_etiqueta, campo: $etiqueta,
            tipo_letra: $tipo_letra, size: $size);

        if(errores::$error){
            return $this->error->error('Error al generar label', $label);
        }

        $tipo = 'text';
        $input = $this->genera_input(campo: $campo,value:  $value,required:  $required, pattern: $pattern,
            disabled: $disabled,tipo: $tipo,etiqueta: $etiqueta,clases_css: $clases_css, ids_css: $ids_css,
            aplica_etiqueta: false,data_extra: $data_extra,size: $size);

        if(errores::$error){
            return $this->error->error('Error al generar input', $input);
        }

        $html = str_replace(array('|label|', '|input|'), array($label, $input), $html);
        $this->html = $html;
        return $html;
    }

    /**
     * NO SE MUEVE
     * Genera un input con un pattern para telefono
     *
     * @param string $campo Nombre del campo
     * @param array $valores valores default de los inputs a mostrar
     * @param array $inputs
     * @param array $clases_css base de tipo css que se asignaran al input
     * @param bool $required si el elemento es requerido asigna required al html
     * @param bool $disabled indica si el elemento es disabled o no
     * @param int $cols columnas css
     * @param string $pattern
     * @return mixed html con info del input a mostrar dependiendo el tipo de input
     * @example
     *      $data_html = $controlador->genera_input_text_template($input,$controlador->valores,array(),true);
     *
     * @internal  $this->obten_data_input_template($campo,$valores);
     * @internal  $this->asigna_campo_template($campo,$data_input_template['valores'],$data_input_template['etiqueta'],$clases_css,'text',$required);
     * @uses  clientes
     * @uses  ubicaciones
     */
    public function genera_input_text_template(string $campo, array $valores, array $inputs,
                                               array $clases_css= array(), bool $required=false,
                                               bool $disabled = false, int $cols = 4, string $pattern = ''): array{
        if($campo === ''){
            return $this->errores->error('Error $campo no puede venir vacio',$campo);
        }


        $data_input_template = (new values())->obten_data_input_template($campo,$valores);
        if(errores::$error){
            return $this->errores->error('Error al obtener datos ',$data_input_template);
        }
        $inputs[$campo] = $this->asigna_campo_template($inputs,$campo,$data_input_template['valores'],
            $data_input_template['etiqueta'],$clases_css,'text',$required, $disabled,$cols, $pattern);

        if(errores::$error){
            return $this->errores->error('Error al generar '.$campo,$inputs[$campo]);
        }

        return $inputs;
    }

    /**
     * P INT ERRORREV
     * @param array $accion
     * @param string $id
     * @param string $session_id
     * @param string $class_link
     * @param string $st_btn
     * @param bool $aplica_etiqueta
     * @return array|string
     */
    public function genera_link_accion(array $accion, string $id, string $session_id, string $class_link='',
                                       string $st_btn = 'info', bool $aplica_etiqueta = true ):array|string{ //FIN PROT


        $valida = (new validaciones_directivas())->valida_link(accion: $accion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar accion',data:  $valida, params: get_defined_vars());
        }


        $datos = (new links())->data_para_link(accion:$accion,aplica_etiqueta:  $aplica_etiqueta,id:  $id,
            session_id:  $session_id,st_btn:  $st_btn);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa datos',data: $datos, params: get_defined_vars());
        }

        $html = "<a href='$datos->href' title='$datos->title' ";
        $html.= "class='$datos->accion_descripcion_envio $class_link $datos->btn_modal' ";
        $html.= "data-name_accion='$datos->accion_descripcion_envio' $datos->modal>$datos->texto_link</a>";


        return $html;

    }

    /**
     * PARAMS ORDER P INT
     * Genera un contenedor div con un select
     * @param string $campo_name
     * @param string $llaves_valores
     * @param int $cols
     * @param mixed $valor
     * @param bool $required indica si es requerido o no
     * @param bool $disabled
     * @param string $etiqueta
     * @param string $css_id
     * @return array|string informacion de select en forma html
     * @example
     *     $controlador->inputs['estado_civil'] =  $directiva->genera_select_estatico('Selecciona una opción:,Soltero:soltero,Casado:casado', 4,'estado_civil', 'Estado civil', false, false, $valores['estado_civil']);
     * @uses  clientes
     * @uses  templates
     * @internal $this->valida_elementos_base_input($input_name,$cols);
     */
    public function genera_select_estatico(string $campo_name, string $llaves_valores,string $css_id = '',
                                           int $cols = 4, bool $disabled = false, string $etiqueta = '',
                                           bool $required = true, mixed $valor =''):array|string{
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

        $required_html = (new params_inputs())->required_html(required: $required);
        if(errores::$error){
            return $this->error->error('Error al generar required', $required_html);
        }

        $disabled_html = (new params_inputs())->disabled_html(disabled: $disabled);
        if(errores::$error){
            return $this->error->error('Error al generar disabled', $disabled_html);
        }


        $elementos_select = (new selects())->elementos_for_select_fijo(llaves_valores: $llaves_valores);
        if(errores::$error){
            return $this->error->error('Error al data para option', $elementos_select);
        }



        $html = "<div class='form-group col-md-$cols' id='contenedor_select_$campo_name'>";

        $html .= "<label for='$etiqueta'>$etiqueta</label>";
        $html .= "<select name='" . $campo_name . "' $disabled_html class='form-control input-md' 
                   title='Seleccione un  ' id='$select_input_name' $required_html>";


        $options = (new selects())->options_for_select(elementos_select: $elementos_select,valor:  $valor);
        if(errores::$error){
            return $this->error->error('Error al generar options', $options);
        }

        $html.=$options;

        $html .= "</select></div>";



        return $html;


    }

    /**
     * NO SE MUEVE
     * @param int $valor
     * @param PDO $link
     * @return array|string
     */
    public function grupo_comercial_id(int $valor, PDO $link):array|string{
        $columnas = array('grupo_comercial_codigo','grupo_comercial_descripcion');
        $input = $this->input_select_columnas(campo_name:'grupo_comercial_id', cols:4,valor: $valor, required: true,
            disabled: false, ln:false,etiqueta: "grupo_comercial",css_id: '', data_extra: array(),
            tabla: "grupo_comercial", columnas: $columnas, link: $link);
        if(errores::$error){
            return $this->errores->error("Error al generar input grupo comercial", $input);
        }
        return $input;
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
        return $input = "<input type='hidden'   name='".$name."' value='".$value."' >";
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

        $campo_capitalize = (new etiquetas())->genera_texto_etiqueta($etiqueta, $tipo_letra);
        if (isset($campo_capitalize['error'])) {
            return $this->errores->error('Error al generar etiqueta', $campo_capitalize);
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
            $html = $html . "<label for='$campo'>$campo_capitalize</label>";
        }

        $data_extra_html = '';

        foreach($data_extra as $key=>$dato){
            $data_txt = "data-$key = '$dato'";
            $data_extra_html.= ' '.$data_txt;
        }


        $html = $html."
			<input 
				type='time' class='form-control input-md' name='$campo' id='$campo' placeholder='Ingresa $campo_capitalize' 
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
     * P INT ERRORREV
     * Genera un contenedor div con un select
     * @param string $campo_name
     * @param int $cols Columnas para asignacion de html entre 1 y 12
     * @param string $valor Valor de Identificador de registro
     * @param bool $required si required el input es obligatorio en su captura
     * @param bool $disabled si disabled el input queda deshabilitado
     * @param bool $ln
     * @param string $etiqueta
     * @param string $css_id
     * @param array $data_extra
     * @param string $tabla Tabla - estructura modelo sistema
     * @param PDO $link Conexion a la BD
     * @param array $columnas Columnas a mostrar en select
     * @param string $tipo_letra
     * @param bool $select_vacio_alta
     * @param array $registros
     * @param array $filtro
     * @param bool $todos
     * @param bool $multiple
     * @param array $data_con_valor igual con data extra pero con valores fijos
     * @param string $size
     * @param bool $inline
     * @return array|string informacion de select en forma html
     * @example
     *     $input = $directiva->input_select_columnas($tabla,$value,12,false,$columnas,$link, true,'capitalize',false,false, $registros,$data_extra , array(),false);
     * @uses  TODO EL SISTEMA
     * @internal $this->validacion->valida_estructura_input_base($columnas,$tabla);
     * @internal $modelo_base->genera_modelo($tabla);
     * @internal $this->obten_registros_select($select_vacio_alta,$modelo, $filtro,$todos);
     * @internal $this->genera_contenedor_select($tabla,$cols,$disabled,$required,$tipo_letra, $aplica_etiqueta,$name_input,$etiqueta);
     * @internal $this->valida_selected($value,$tabla,$valor_envio);
     */
    public function input_select_columnas(string $campo_name, string $tabla, PDO $link, int $cols = 4,
                                          mixed $valor = '', bool $required = true, bool $disabled = false,
                                          bool $ln = false,string $etiqueta = '', array $columnas = array(),
                                          string $tipo_letra = 'capitalize', bool $select_vacio_alta = false,
                                          array $registros = array(), array $filtro = array(), bool $todos = false,
                                          bool $multiple = false, array $data_con_valor = array(), string $size = 'md',
                                          bool $inline = false, array $data_extra = array(),
                                          string $css_id = ''):array|string{


        $aplica_etiqueta = false;
        if($etiqueta!==''){
            $aplica_etiqueta = true;
        }

        $datos = (new selects())->init_datos_select(valor: $valor,tabla:  $tabla,data_extra:  $data_extra,
            data_con_valor:  $data_con_valor, columnas: $columnas,link:  $link, registros: $registros,
            select_vacio_alta:  $select_vacio_alta,filtro:  $filtro,todos:  $todos, ln: $ln, size: $size,cols:  $cols,
            disabled: $disabled,required:  $required,tipo_letra:  $tipo_letra, aplica_etiqueta: $aplica_etiqueta,
            name_input:  $campo_name,etiqueta:  $etiqueta,multiple:  $multiple,inline:  $inline);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar options', data: $datos);

        }

        return $datos->ln_html.$datos->header_fg.$datos->contenedor.$datos->options_html.'</select></div>';


    }

    /**
     * PARAMS ORDER
     * @param array $items
     * @return array|string
     */
    public function items_pestanas(array $items): array|string
    {

        $items_html = '';
        foreach ($items as $target=>$class_btn){
            $item_html = (new menus())->item_pestana($class_btn,$target);
            if(errores::$error){
                return $this->errores->error('Error al generar item', $item_html);
            }
            $items_html.=$item_html;
        }
        return $items_html;
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
     * @param string $url_image_min
     * @param string $url_tb_image
     * @param string $css_id
     * @param array $class_tb_html
     * @return array|string
     */
    public function modal_foto(string $url_image_min, string $url_tb_image, string $css_id, array $class_tb_html = array()): array|string
    {
        $img_tb = $this->img_btn_modal($url_tb_image, $css_id, $class_tb_html);
        if(errores::$error){
            return $this->errores->error('Error al obtener tb',$img_tb);
        }
        $modal = (new modals())->modal_base_img($css_id, $url_image_min);
        if(errores::$error){
            return $this->errores->error('Error al obtener modal',$modal);
        }
        return $img_tb.$modal;
    }

    /**
     * P ORDER P INT
     * @param string $acciones_autorizadas_base
     * @return string
     */
    public function modal_menu_acciones(string $acciones_autorizadas_base): string
    {

        return "
<div class='menu_acciones_lista modal fade' role='dialog'  aria-labelledby='menu_acciones_lista' aria-hidden='true'>
    <div class='modal-dialog' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h4 class='modal-title' id=''>Acciones</h4>
                <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
            <div class='modal-body'>
                $acciones_autorizadas_base
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
            </div>
        </div>
    </div>
</div>";

    }

    /**
     * NO SE MUEVE
     * Genera el html de la barra de navegacion
     *
     * @param array $breadcrumbs Arreglo con parametro para generacion
     * @param string $seccion Seccion para su aplicacion
     * @param string $accion para definir accion active
     * @param PDO $link
     * @param array $accion_registro
     * @param string $session_id
     * @param bool $valida_accion valida si la accion existe
     *
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
    public function nav_breadcumbs(array $breadcrumbs, string $seccion, string $accion, PDO $link,
                                   array $accion_registro, string $session_id, bool $valida_accion = true):array|string{

        if($seccion === ''){
            return $this->error->error(mensaje: "Error la seccion esta vacia",data: $seccion, params: get_defined_vars());
        }
        if($accion === ''){
            return $this->error->error('$accion no puede venir vacia',$accion);
        }

        $breads = (new menus())->breadcrumbs_con_label($link, $seccion, $accion, $accion_registro,$valida_accion);
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
     * @param string $campo_busca
     * @param string $valor_busca_fault
     * @return array
     */
    public function panel_busca(string $campo_busca, string $valor_busca_fault): array
    {
        $etiqueta_campo_busca = (new etiquetas())->etiqueta_campo_vista(campo_busca: $campo_busca);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar etiqueta',data: $etiqueta_campo_busca);
        }

        $inputs_busca = $this->busqueda_base(campo_busca: $campo_busca, etiqueta_campo_busca: $etiqueta_campo_busca,
            valor_busca_fault: $valor_busca_fault);
        if(errores::$error){
            return $this->errores->error('Error al generar datos de busqueda',$inputs_busca);
        }


        return $inputs_busca;
    }

    /**
     * P INT
     * Genera el html de la barra de navegacion
     *
     * @param string $value
     * @param int $cols
     * @param string $campo
     * @example
     *      $data_html = $directiva->password($this->valor, $this->cols, $this->campo);
     *
     * @return string|array html para incrustarlo y mostrarlo
     * @throws errores definidos en internals
     * @uses templates
     * @internal $this->valida_elementos_base_input($campo,$cols);
     * @internal $this->genera_texto_etiqueta($campo,'capitalize');
     */
    public function password( string $campo, int $cols, string $value, bool $required, string $etiqueta,
                              string $pattern, string $css_id, array $data_extra):string|array{ //FIN PROT
        $valida = $this->validacion->valida_elementos_base_input(cols: $cols, tabla: $campo);
        if(errores::$error){
            return  $this->error->error('Error al validar',$valida);
        }
        $campo_capitalize = (new etiquetas())->genera_texto_etiqueta(texto:$campo,tipo_letra: 'capitalize');

        if(errores::$error){
            return  $this->error->error('Error al generar etiqueta',$campo_capitalize);
        }

        $html = "<div class='form-group col-md-$cols'>";

        $html .=   "<label for='$campo'>$campo_capitalize</label>";

        $html .= "<input 
				type='password' class='form-control input-md' name='$campo' placeholder='$campo_capitalize' 
				required title='Ingrese un $campo' value='$value'>
		</div>";

        return $html;
    }


    /**
     * NO SE MUEVE
     * @param string $valor
     * @param PDO $link
     * @param array $empleados
     * @param int $cols
     * @return array|string
     */
    private function select_empleado_id(string $valor, PDO $link, array $empleados=array(),int $cols = 6):array|string{
        $columnas = array('empleado_codigo','empleado_descripcion');
        $input = $this->input_select_columnas(campo_name:'empleado_id',cols: $cols,valor: $valor, required: true,
            disabled: false, ln:false,etiqueta: "empleado",css_id: '', data_extra: array(),tabla: "empleado",
            columnas: $columnas, link: $link,registros: $empleados);
        if(errores::$error){
            return $this->errores->error(mensaje: "Error al generar input empleado", data: $input);
        }
        return $input;
    }

    /**
     * NO SE MUEVE
     * @param string $valor
     * @param PDO $link
     * @param array $empleados
     * @param int $cols
     * @return array|string
     */
    private function select_empleado_id_tipo(string $valor, PDO $link, array $empleados, int $cols = 6):array|string{

        $input = $this->select_empleado_id($valor,$link, $empleados,$cols);
        if(errores::$error){
            return $this->errores->error("Error al generar input empleado", $input);
        }
        return $input;
    }

    /**
     * NO SE MUEVE
     * @param PDO $link
     * @param array $dia
     * @param string $value
     * @return array|string
     */
    private function sl_dia(PDO $link, array $dia, string $value = ''): array|string
    {
        if($value === ''){

            $value = $dia['dia_id'];
        }
        $select = $this->input_select_columnas('dia_id',1,$value,true, false, false,
            'dia','', array(),'dia',array('dia_codigo'),$link);
        if(errores::$error){
            return $this->errores->error('Error al generar input', $select);
        }
        return $select;
    }

    /**
     * NO SE MUEVE
     * @param PDO $link
     * @param array $hora
     * @param string $value
     * @return array|string
     */
    private function sl_hora(PDO $link, array $hora, string $value = ''): array|string
    {
        if($value === ''){

            $value = $hora['hora_id'];
        }
        $select = $this->input_select_columnas('hora_id',1,$value,true, false,
            false,'hora','', array(),'hora', array('hora_codigo'), $link);
        if(errores::$error){
            return $this->errores->error('Error al generar input', $select);
        }
        return $select;
    }

    /**
     * NO SE MUEVE
     * @param PDO $link
     * @param string $value
     * @return array|string
     */
    private function sl_mes(PDO $link, array $mes, string $value = ''): array|string
    {
        if($value === ''){

            $value = $mes['mes_id'];
        }
        $select = $this->input_select_columnas('mes_id',1,$value, true, false,
            false,'mes','', array(),'tabla', array('mes_codigo','mes_descripcion'), $link);
        if(errores::$error){
            return $this->errores->error('Error al generar input', $select);
        }
        return $select;
    }

    /**
     * NO SE MUEVE
     * @param PDO $link
     * @param string $value
     * @return array|string
     */
    private function sl_minuto(PDO $link, string $value = ''): array|string
    {
        if($value === ''){
            $modelo = (new modelo_base($link))->genera_modelo('minuto');
            if(errores::$error){
                return $this->errores->error('Error al generar modelo', $modelo);
            }

            $minuto = $modelo->hoy();
            if(errores::$error){
                return $this->errores->error('Error al obtener minuto', $minuto);
            }
            $value = $minuto['minuto_id'];
        }
        $select = $this->input_select_columnas('minuto_id',1,$value,true, false, false,
            'minuto','', array(),'minuto', array('minuto_codigo'), $link);
        if(errores::$error){
            return $this->errores->error('Error al generar input', $select);
        }
        return $select;
    }

    /**
     * NO SE MUEVE
     * @param PDO $link
     * @param array $year
     * @param string $value
     * @return array|string
     */
    private function sl_year(PDO $link, array $year, string $value = ''): array|string
    {
        if($value === ''){

            $value = $year['year_id'];
        }
        $select = $this->input_select_columnas('year_id',1,$value,true, false, false,
            'year','', array(),'year', array('year_descripcion'), $link);
        if(errores::$error){
            return $this->errores->error('Error al generar input', $select);
        }
        return $select;
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
     * Genera un input con un pattern para telefono
     *
     * @param string $campo Nombre del campo
     * @param int $cols numero de columnas entre 1 y 12
     * @param string $value Valor default a mostrar en el input
     * @param bool $required si el elemento es requerido asigna required al html
     * @param bool $disabled si el elemento es true asigna disabled al html
     * @param string $tipo_letra ajustes del label de salida para mostrar en html
     * @param bool $ln inserta <div class="col-md-12"></div> antes del input
     *
     * @param string $etiqueta Etiqueta a mostrar en input es un label
     * @param array $clases_css base de tipo css que se asignaran al input
     * @param array $ids_css ids de tipo css que se asignaran al input
     * @param string $size
     * @return array|string html con info del input a mostrar con pattern de telefono
     * @example
     *      $this->inputs[$campo] =  $this->directiva->telefono($campo,4, $valores[$campo], $required,'', 'capitalize',
     *          false,$etiqueta,$clases_css);
     */
    public function telefono(string $campo, int $cols, string $value,bool $required, bool $disabled, bool $ln,
                             string $etiqueta, string $css_id, array $data_extra, string $tipo_letra,array $clases_css = array(),
                             array $ids_css = array(),string $size = 'sm'):array|string{//FIN PROT

        $valida = $this->validacion->valida_elementos_base_input(cols: $cols, tabla: $campo);
        if(errores::$error){
            return $this->error->error('Error al validar '.$campo,$valida);
        }

        $html = $this->genera_input_text(campo: $campo,cols: $cols,value: $value,required: $required,
            disabled: $disabled,ln:  $ln,etiqueta: $etiqueta,pattern: '[0-9]{10}',css_id: $css_id, data_extra: $data_extra,
            tipo_letra: $tipo_letra,clases_css: $clases_css,ids_css: $ids_css,size: $size);

        if(errores::$error){
            return $this->error->error('Error al generar text '.$campo,$html);
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
     * @param bool $required
     * @param bool $disabled
     * @param bool $ln
     * @param string $etiqueta
     * @param string $pattern
     * @param string $css_id
     * @param array $data_extra
     * @param string $tipo_letra
     * @return string|array html con info del input a mostrar
     * @example
     *      $data_html = $directiva->textarea($this->valor, $this->cols, $this->campo,$con_label);
     *
     * @uses templates
     * @internal $this->genera_texto_etiqueta($campo,$tipo_letra);
     */
    public function textarea(string $campo_name,int $cols, string $value, bool $required, bool $disabled, bool $ln,
                             string $etiqueta, string $pattern, string $css_id, array $data_extra, string $tipo_letra='capitalize'):string|array{ //FIN PROT
        if($campo_name === ''){
            return  $this->error->error('Error $campo no puede venir vacio',$campo_name);
        }
        if($cols <0){
            return  $this->error->error('Error cols debe ser mayor a 0',$cols);
        }
        if($cols >12){
            return  $this->error->error('Error cols debe ser menor a 12',$cols);
        }
        $campo_capitalize = (new etiquetas())->genera_texto_etiqueta(texto: $campo_name,tipo_letra: $tipo_letra);
        if(errores::$error){
            return  $this->error->error('Error al $campo_capitalize',$campo_capitalize);
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

    /**
     * NO SE MUEVE
     * @param PDO $link
     * @param string $valor
     * @param int $cols
     * @param array $registros
     * @return array|string
     */
    public function ubicacion_id(PDO $link, string $valor  = '-1', int $cols = 4, array $registros = array()): array|string
    {
        $input = $this->input_select_columnas(campo_name:'ubicacion_id',cols: $cols,valor: $valor,required:true,
            disabled: false, ln:false,etiqueta: 'ubicacion',css_id: '', data_extra: array(),tabla: 'ubicacion', columnas: array('ubicacion_descripcion_completa'),link: $link,
            registros: $registros);

        if(errores::$error){
            return $this->errores->error('Error al generar input',$input);
        }
        return $input;
    }

    /**
     * P INT
     * Genera un input para subida de archivos
     *
     * @param string $campo Nombre del campo
     * @param int $cols numero de columnas entre 1 y 12
     * @param bool $required
     * @param bool $disabled
     * @param bool $ln inserta <div class="col-md-12"></div> antes del input
     *
     * @param string $etiqueta Etiqueta a mostrar en input es un label
     * @param string $css_id
     * @param array $data_extra
     * @param string $codigo Codigo del tipo de documento
     * @param bool $multiple
     * @param array $ids
     * @param array $class_css
     * @return array|string html con info del input a mostrar
     * @example
     *      $input = $directiva->upload_file(12,'tipo_documento_id['.$tipo_documento['tipo_documento_id'].']',false,$tipo_documento['tipo_documento_descripcion']);
     *
     * @uses template_documentos
     * @uses templates
     * @uses alta views
     * @uses modifica views
     */
    public function upload_file(string $campo,int $cols, bool $required, bool $disabled, bool $ln=false,
                                string $etiqueta = '', string $css_id = '', array $data_extra = array(),
                                string $codigo='', bool $multiple = false, array $ids = array(),
                                array $class_css = array()):array|string{ //FIN PROT

        /**
         * REFACTORIZAR
         */

        $valida = $this->validacion->valida_base_input(campo: $campo,cols:  $cols);
        if(errores::$error){
            return  $this->error->error('Error al validar input',$valida);
        }

        if($etiqueta===''){
            $etiqueta = $campo;
        }
        if($codigo===''){
            $codigo = $campo;
        }
        $required_html = '';
        if($required){
            $required_html = 'required';
        }

        $ids_html = (new params_inputs())->ids_html(campo:  $campo, ids_css: $ids);
        if(errores::$error){
            return  $this->error->error('Error al obtener ids',$ids_html);
        }

        $class_css_html = (new class_css())->class_css_html(clases_css: $class_css);
        if(errores::$error){
            return  $this->error->error('Error al obtener ids',$ids_html);
        }

        $disable_html = (new params_inputs())->disabled_html(disabled: $disabled);
        if(errores::$error){
            return  $this->error->error('Error al obtener $disable_html',$disable_html);
        }


        $html = '';

        $ln_html = (new params_inputs())->ln(ln: $ln,size:  'md');
        if(errores::$error){
            return  $this->error->error('Error al obtener ln',$ln_html);
        }

        $html.=$ln_html;

        if($multiple){

            $input_file = (new inputs_files())->input_file_multiple(campo: $campo, class_css_html: $class_css_html,
                codigo: $codigo, disable_html: $disable_html, etiqueta:  $etiqueta, ids_html:  $ids_html,
                 required_html: $required_html);
            if(errores::$error){
                return  $this->error->error('Error al obtener input',$input_file);
            }
            $html.=$input_file;

        }
        else {
            $html.="<div class='col-md-12'>";
            $html .=  "
<div class='input-group mb-3'>
    <div class='input-group-prepend'>
        <span class='input-group-text' >$codigo</span>
    </div>
    <div class='custom-file'>
        <input type='file' class='custom-file-input input-file $class_css_html' name='$campo' $required_html $disable_html id='$ids_html'>
        <label class='custom-file-label' for='$etiqueta'>$etiqueta</label>
    </div>
</div>
</div>";

        }

        return $html;
    }


    /**
     * NO SE MUEVE
     * @param PDO $link
     * @param string $valor
     * @param int $cols
     * @param array $registros
     * @return array|string
     */
    public function valuador_id(PDO $link, string $valor  = '-1', int $cols = 4, array $registros = array()): array|string
    {
        $input = $this->input_select_columnas(campo_name:'valuador_id',cols: $cols,valor: $valor,required: true,
            disabled: false, ln:false,etiqueta: 'valuador',css_id: '', data_extra: array(),tabla: 'valuador', columnas: array('valuador_codigo','proveedor_razon_social'),
            link: $link,registros: $registros);

        if(errores::$error){
            return $this->errores->error('Error al generar input',$input);
        }
        return $input;
    }



}


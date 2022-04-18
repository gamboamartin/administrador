<?php
namespace base\frontend;

use gamboamartin\errores\errores;

use JetBrains\PhpStorm\Pure;
use PDO;
use stdClass;


class templates{
    public string $accion = '';
    public array $campos_filtro;
    public array $campos_invisibles = array();


    public PDO $link;
    public errores $error;
    public validaciones_directivas $validacion;

    public array $registro = array();
    public array $valores = array();
    public array $botones_filtros = array();

    /**
     * DEBUG INI
     * templates constructor.
     * @param PDO $link
     */
    #[Pure] public function __construct(PDO $link){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
        $this->link = $link;

    }

    /**
     *
     * @param array $directivas_extra
     * @param bool $muestra_btn_guardar
     * @param bool $aplica_form
     * @param array $valores_filtrados
     * @param array $campos
     * @param string $seccion
     * @param string $session_id
     * @param string $path_base
     * @param array $campos_disabled
     * @param array $valores_default
     * @param array $campos_invisibles
     * @return array|string
     */
    public function alta(array $directivas_extra , bool $muestra_btn_guardar , bool $aplica_form ,
                         array $valores_filtrados, array $campos, string $seccion,string $session_id,
                         string $path_base, array $campos_disabled, array $valores_default,
                         array $campos_invisibles):array|string{
        $directiva = new directivas();
        $valida_metodo = $directiva->validacion->valida_metodos(accion: 'alta', tabla: $seccion);
        if(errores::$error){
            return  $this->error->error("Error al validar metodo",$valida_metodo);
        }
        $html = '';
        if($aplica_form) {
            $header_form = (new forms())->header_form(accion:  'alta', accion_request: 'alta_bd', seccion: $seccion,
                session_id:  $session_id);
            if(errores::$error){
                return  $this->error->error("Error al generar header form",$header_form);
            }
            $html.=$header_form;

        }
        $header_section = 'Alta';
        $html .= file_get_contents($path_base.'views/_templates/__header_section.php');
        $html .= "<div class='form-row  alta'>";
        $data_html = $this->genera_campos_alta(valores_filtrados: $valores_filtrados,campos:  $campos,
            campos_disabled: $campos_disabled,valores_default: $valores_default,
            campos_invisibles: $campos_invisibles);
        if(errores::$error){
            return  $this->error->error('Error al generar campos alta',$data_html);
        }

        $html .= $data_html;

        foreach ($directivas_extra as $directiva_extra){
            $html.=$directiva_extra;
        }

        $html .= '<div class="col-md-12"></div>';
        if($muestra_btn_guardar) {
            $btn = $directiva->btn_enviar(cols: 12,label:  'Guardar',name:  'btn_guarda',value: 'activo',
                type: 'submit', stilo: 'success');
            if(errores::$error){
                return  $this->error->error('Error al generar boton',$btn);
            }
            $html.=$btn;
        }

        $html .= '</div>';
        if($aplica_form) {
            $html .= '</form></div>';
        }
        $html.= file_get_contents($path_base.'views/_templates/__footer_section.php');

        return $html;
    }
    /**
     *
     * Asigna valores bool a inputs
     * @example
     *      $this->input = $this->asigna_valores_booleanos_input();
     *
     * @return array informacion de input
     * @throws errores !isset($this->input['pattern'])
     * @throws errores !isset($this->input['pattern']
     * @throws errores !isset($this->input['con_label'])
     * @throws errores !is_bool($this->input['con_label'])
     * @uses  templates
     */
    private function asigna_valores_booleanos_input(string $campo_name, array $input, bool $disabled,
                                                    array $campos_disabled): array{

        if (!isset($input['pattern'])) {
            $input['pattern'] = '';
        }
        if (!isset($input['pattern'])) {
            $input['select_vacio_alta'] = false;
        }

        $con_label = $this->con_label($input);
        if(errores::$error){
            return $this->error->error('Error al asignar con label',$con_label);
        }

        $ln = $this->input_ln($input);
        if(errores::$error){
            return $this->error->error('Error al asignar ln',$ln);
        }

        $input['disabled'] = $disabled;
        if(in_array($campo_name, $campos_disabled, true)){
            $input['disabled'] = true;
        }

        return $input;
    }

    /**
     * PROBADO P ORDER P INT
     * @param array $elementos_lista
     * @return array|stdClass
     */
    public function campos_lista(array $elementos_lista): array|stdClass
    {
        if(count($elementos_lista) === 0){
            return $this->error->error("Error elemento_lista no puede venir vacio", $elementos_lista);
        }
        $campos_lista = (new inicializacion())->campos_lista(elementos_lista: $elementos_lista);
        if(errores::$error){
            return $this->error->error('Error al generar campos lista',$campos_lista);
        }
        return $campos_lista;

    }

    /**
     *
     * @param array $input
     * @return array
     */
    private function con_label(array $input): array
    {
        if (!isset($input['con_label'])) {
            $input['con_label'] = false;
        }
        else if(!is_bool($input['con_label'])){
            return $this->error->error('Error al input[con_label] deb ser un bool',$input);
        }
        return $input;
    }

    /**
     * PHPUNIT
     * @param array $input
     * @return bool|array
     */
    private function input_ln(array $input): bool|array
    {
        if (!isset($input['ln'])) {
            $input['ln'] = false;
        }
        else if(!is_bool($input['ln'])){
            return $this->error->error('Error al input[ln] deb ser un bool',$input);
        }
        return $input['ln'];
    }


    /**
     *
     * @param string $tipo
     * @param string $campo_name
     * @param int $cols
     * @param bool $required
     * @param bool $disabled
     * @param bool $ln
     * @param string $etiqueta
     * @param string $pattern
     * @param string $css_id
     * @param array $data_extra
     * @param bool $select_vacio_alta
     * @param array $valores_filtrados
     * @param string $columnas
     * @param array $input
     * @param string $llaves_foraneas
     * @param array $campos_invisibles
     * @param array $vistas
     * @param array $registro
     * @return array|string
     */
    public function carga_html_form(string $tipo, string $campo_name, int $cols, bool $required, bool $disabled,
                                    bool $ln,string $etiqueta, string $pattern, string $css_id, array $data_extra,
                                    bool $select_vacio_alta, array $valores_filtrados, string $columnas, array $input,
                                    string $llaves_foraneas, array $campos_invisibles, array $vistas, array $registro):array|string{ //fin
        $keys = array('etiqueta','vista','cols','tabla_foranea','campo_tabla_externa');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $input);
        if(errores::$error){
            return $this->error->error('Error al validar $input',$valida);
        }

        $tabla_foranea = $input['tabla_foranea'];
        if(isset($input['tabla_externa_renombrada']) && trim($input['tabla_externa_renombrada'])!==''){
            $tabla_foranea = trim($input['tabla_externa_renombrada']);
        }
        $key_validar = $tabla_foranea.'_'.$input['campo_tabla_externa'];
        if(!isset($registro[$key_validar])){
            $registro[$key_validar] = '';
        }

        $html = $this->genera_html_form(tipo: $tipo,campo_name:  $campo_name,cols:  $cols,required:  $required,
            disabled:  $disabled, ln: $ln, etiqueta: $etiqueta, pattern: $pattern, css_id: $css_id,
            data_extra:  $data_extra,tabla_foranea:  $tabla_foranea,select_vacio_alta:  $select_vacio_alta,
            valores_filtrados:  $valores_filtrados, columnas: $columnas, llaves_foraneas: $llaves_foraneas,
            vistas: $vistas, campos_invisibles: $campos_invisibles,key_validar:  $key_validar, html: '',
            registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al generar $html',$html);
        }

        return $html;
    }



    /**
     *
     * @param string $tipo
     * @param string $campo_name
     * @param int $cols
     * @param mixed $valor
     * @param bool $required
     * @param bool $disabled
     * @param bool $ln
     * @param string $etiqueta
     * @param string $pattern
     * @param string $css_id
     * @param array $data_extra
     * @param string $tabla_foranea
     * @param bool $select_vacio_alta
     * @param array $valores_filtrados
     * @param string $columnas
     * @param string $llaves_foraneas
     * @param array $vistas
     * @param string $accion
     * @param array $campos_invisibles
     * @return array|string
     */
    public function genera_campos(string $tipo, string $campo_name, int $cols, mixed $valor, bool $required,
                                  bool $disabled, bool $ln, string $etiqueta, string $pattern, string $css_id,
                                  array $data_extra, string $tabla_foranea, bool $select_vacio_alta,
                                  array $valores_filtrados, string $columnas, string $llaves_foraneas,
                                  array $vistas, string $accion, array $campos_invisibles):array|string{

        if($accion === ''){
            return $this->error->error('Error accion debe existir',$accion);
        }

        if(in_array($campo_name, $campos_invisibles,false)){
            return '';
        }
        $etiqueta_valida = $this->validacion->letra_numero_espacio(txt: $etiqueta);
        if(!$etiqueta_valida){
            return $this->error->error('Error etiqueta no es valida',$etiqueta);
        }
        $filtro = $valores_filtrados[$campo_name] ?? array();


        $html = $this->genera_html_input(tipo: $tipo,campo_name: $campo_name,cols: $cols,valor: $valor,
            required: $required,disabled: $disabled,ln: $ln,etiqueta: $etiqueta,pattern: $pattern, css_id: $css_id,
            data_extra: $data_extra,tabla_foranea: $tabla_foranea,select_vacio_alta: $select_vacio_alta,
            columnas: $columnas,llaves_valores: $llaves_foraneas,filtro: $filtro,vistas: $vistas,accion: $accion);

        if(errores::$error){
            return $this->error->error('Error al generar html',$html);
        }

        return $html;
    }

    /**
     *
     * @param array $valores_filtrados
     * @param array $campos
     * @param array $campos_disabled
     * @param array $valores_default
     * @param array $campos_invisibles
     * @return array|string
     */
    public function genera_campos_alta(array $valores_filtrados, array $campos, array $campos_disabled,
                                       array $valores_default, array $campos_invisibles):array|string{ //FIN
        $html = '';

        $campos_alta = $campos['campos'];
        foreach ($campos_alta as $campo_name=>$input){
            $valor = $valores[$campo_name]??false;
            $disabled = false;
            if(in_array($campo_name,$campos_disabled,false)){
                $disabled = true;
            }
            $key = in_array($input['elemento_lista_id'], $valores_default, false);
            if(false !== $key){
                $valor = $valores_default['default'];
            }

            $keys = array('data_extra','required');
            $valida = $this->validacion->valida_existencia_keys(keys:  $keys, registro: $input);
            if(errores::$error){
                return  $this->error->error('Error al validar input',$valida);
            }

            if(!isset($input['llaves_foraneas'])){
                $input['llaves_foraneas'] = '';
            }

            $accion = 'alta';
            $data_html = $this->genera_campos(tipo: $input['tipo'],campo_name: $campo_name,cols: $input['cols'],
                valor: $valor,required: $input['required'], disabled: $disabled,ln: $input['ln'],
                etiqueta: $input['etiqueta'],pattern: $input['pattern'],css_id: $input['css_id'],
                data_extra: $input['data_extra'], tabla_foranea: $input['tabla_foranea'],
                select_vacio_alta: $input['select_vacio_alta'],valores_filtrados: $valores_filtrados,
                columnas: $input['columnas'], llaves_foraneas: $input['llaves_foraneas'],vistas: array('alta'),
                accion: $accion,campos_invisibles: $campos_invisibles);
            if(errores::$error){
                return  $this->error->error('Error al generar campos',$data_html);
            }
            $html .= $data_html;
        }

        return $html;
    }



    /**
     *
     * @param array $valores_filtrados
     * @param array $campos_alta
     * @param array $registro
     * @return array|string
     */
    public function genera_campos_modificables(array $valores_filtrados, array $campos_alta, array $registro):array|string{ //fin

        $html = $this->genera_html_campos(campos_alta: $campos_alta,valores_filtrados:  $valores_filtrados,
            registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al generar $html',$html);
        }
        return $html;
    }

    /**
     *
     * Genera un input de tipo HTML
     *
     * @example
     *      $$data_html = $this->genera_dato_html();
     *
     * @return array|string html con info del input a mostrar
     * @throws errores $campo vacio
     * @throws errores $cols <0
     * @throws errores $cols >12
     * @uses templates
     * @internal $this->validacion->valida_existencia_keys($this->input, $keys);
     * @internal $directiva->checkbox($this->cols, $this->valor, $etiqueta, $this->ln,$this->campo);
     * @internal $directiva->upload_file($this->cols,$this->campo,$this->ln);
     * @internal $directiva->fecha($this->cols, $this->campo,'capitalize',$con_label,$this->ln,$required, $etiqueta,$this->valor, $disabled);
     * @internal $directiva->genera_input_numero($this->campo, $this->cols, $this->valor, $required,'mayusculas',$this->ln);
     * @internal $this->validacion->valida_existencia_keys($this->input, $keys);
     * @internal $directiva->input_select_columnas($tabla_foranea,$this->valor,$this->cols,$disabled,$columnas,$this->link,$required,'capitalize',$this->ln,$select_vacio_alta,$registros = array(),$valor_extra,$this->filtro);
     * @internal $directiva->genera_select_estatico($llaves_valores, $this->cols, $this->campo,$etiqueta,false,false,$this->valor,$css_id);
     */
    public function genera_dato_html(string $tipo, string $campo_name, int $cols,mixed $valor, bool $required, bool $disabled,
                                     bool $ln, string $etiqueta, string $pattern,string $css_id, array $data_extra,
                                     array $filtro, string $tabla_foranea, bool $select_vacio_alta, string $columnas,
                                     string $llaves_valores):array|string{ //FIN

        if($cols <0){
            return $this->error->error('Error cols debe ser mayor a 0',$cols);
        }
        if($cols >12){
            return $this->error->error('Error cols debe ser menor a 12',$cols);
        }

        $data_html = '';

        $directiva = new directivas();

        if( $tipo === 'checkbox') {
            if($valor === ''){
                $valor = 'inactivo';
            }
            $data_html = $directiva->checkbox(campo: $campo_name, css_id: $css_id, cols:  $cols,
                data_extra: $data_extra, disabled: $disabled,etiqueta:  $etiqueta, ln: $ln, valor: $valor);
            if(errores::$error){
                return $this->error->error('Error al crear checkbox',$data_html);
            }
        }
        if( $tipo === 'documento'){
            $data_html = $directiva->upload_file(campo: $campo_name,cols: $cols,required:  $required,
                disabled: $disabled,ln: $ln,etiqueta:  $etiqueta,css_id:  $css_id,data_extra: $data_extra);
            if(errores::$error){
                return $this->error->error('Error al crear upload_file',$data_html);
            }
        }

        if( $tipo === 'fecha') {
            $data_html =  $directiva->fecha( campo: $campo_name,cols: $cols,value: $valor, required: $required,
                disabled: $disabled,ln: $ln,etiqueta: $etiqueta,pattern:  $pattern, css_id: $css_id,
                data_extra: $data_extra,tipo_letra: 'capitalize');

            if(errores::$error){
                return $this->error->error('Error al generar input fecha',$data_html);
            }
        }
        if($tipo === 'numero') {
            $data_html = $directiva->genera_input_numero($campo_name, $cols, $valor, $required, $disabled,$ln,
                $etiqueta, $pattern, $css_id,  $data_extra ,'mayusculas');
            if(errores::$error){
                return $this->error->error('Error al generar input numero',$data_html);
            }
        }
        if($tipo === 'password') {
            $data_html = $directiva->password($campo_name,$cols, $valor, $required, $etiqueta, $pattern, $css_id,$data_extra );
            if(errores::$error){
                return $this->error->error('Error al generar input password',$data_html);
            }
        }


        if($tipo === 'select_columnas' || $tipo === 'select'){
            $columnas_sl = array();
            if(($tipo === 'select_columnas') && $columnas !== '') {
                $columnas_sl = explode(',', $columnas);
            }

            if( $tipo === 'select') {
                $columnas_sl = array($tabla_foranea.'_id',$tabla_foranea.'_descripcion');
            }

            $data_html = $directiva->input_select_columnas(campo_name:$campo_name,tabla: $tabla_foranea,
                link: $this->link,cols:$cols, valor: $valor, required:  $required,disabled: $disabled,ln: $ln,
                etiqueta: $etiqueta, columnas: $columnas_sl,select_vacio_alta: $select_vacio_alta,filtro: $filtro,
                data_extra: $data_extra,css_id:  $css_id);

            if(errores::$error){
                return  $this->error->error('Error al obtener select columnas del modelo '.$tabla_foranea,$data_html);
            }

        }

        if( $tipo === 'select_estatico') {


            $data_html =  $directiva->genera_select_estatico($campo_name,$cols,$valor,$required,$disabled, $ln,
                $etiqueta,$css_id );

            if(errores::$error){
                return  $this->error->error('Error al obtener genera_select_estatico',$data_html);
            }

        }
        if( $tipo === 'text') {


            $data_html = $directiva->genera_input_text($campo_name, $cols, $valor, $required,$disabled, $ln,$etiqueta,
                $pattern, $css_id, $data_extra,'capitalize', array(),array(),true,'md');

            if(errores::$error){
                return  $this->error->error('Error al generar text',$data_html);
            }
        }
        if( $tipo === 'telefono') {
            $data_html = $directiva->telefono($campo_name, $cols, $valor, $required, $disabled,$ln,$etiqueta, $css_id, $data_extra,
                'capitalize');
            if(errores::$error){
                return  $this->error->error('Error al generar telefono',$data_html);
            }
        }
        if( $tipo === 'textarea') {
            $data_html = $directiva->textarea($campo_name,$cols, $valor, $required, $disabled, $ln, $etiqueta, $pattern, $css_id, $data_extra);
            if(errores::$error){
                return  $this->error->error('Error al generar textarea',$data_html);
            }
        }

        return $data_html;
    }



    /**
     *
     * @param array $campos_alta
     * @param array $valores_filtrados
     * @param array $registro
     * @return array|string
     */
    public function genera_html_campos(array $campos_alta, array $valores_filtrados, array $registro):array|string{
        $html = '';
        foreach ($campos_alta as $campo_name=>$data){

            if(!is_array($data)){
                return $this->error->error('Error al data debe ser un array',$data);
            }

            if(in_array($campo_name, $this->campos_invisibles, true)){
                continue;
            }
            $html_data = $this->carga_html_form(tipo: $data['tipo'],campo_name:  $campo_name, cols: $data['cols'],
                required:  $data['required'], disabled: $data['disabled'],ln:  $data['ln'],etiqueta: $data['etiqueta'],
                pattern: $data['pattern'],css_id:   $data['css_id'], data_extra: $data['data_extra'],
                select_vacio_alta:  $data['select_vacio_alta'],valores_filtrados: $valores_filtrados,
                columnas:  $data['columnas'],input:  $data, llaves_foraneas: $data['llaves_foraneas'],
                campos_invisibles: $this->campos_invisibles, vistas: array('modifica'), registro:  $registro);
            if(errores::$error){
                return $this->error->error('Error al generar $html',$html_data);
            }
            $html.=$html_data;
        }

        return $html;
    }


    /**
     *
     * @param string $tipo
     * @param string $campo_name
     * @param int $cols
     * @param bool $required
     * @param bool $disabled
     * @param bool $ln
     * @param string $etiqueta
     * @param string $pattern
     * @param string $css_id
     * @param array $data_extra
     * @param string $tabla_foranea
     * @param bool $select_vacio_alta
     * @param array $valores_filtrados
     * @param string $columnas
     * @param string $llaves_foraneas
     * @param array $vistas
     * @param array $campos_invisibles
     * @param string $key_validar
     * @param string $html
     * @param array $registro
     * @return array|string
     */
    public function genera_html_form(string $tipo,string $campo_name, int $cols, bool $required, bool $disabled,
                                     bool $ln, string $etiqueta, string $pattern, string $css_id, array $data_extra,
                                     string $tabla_foranea, bool $select_vacio_alta, array $valores_filtrados,
                                     string $columnas, string $llaves_foraneas, array $vistas,array $campos_invisibles,
                                     string $key_validar, string $html, array $registro):array|string{


        if(!isset($registro[trim($key_validar)])){
            $registro[trim($key_validar)] ='';
        }

        $valor = (string)$registro[trim($key_validar)];

        $accion ='modifica';

        $campos_html = $this->genera_campos($tipo, $campo_name, $cols, $valor, $required, $disabled, $ln, $etiqueta,
            $pattern, $css_id, $data_extra, $tabla_foranea, $select_vacio_alta, $valores_filtrados, $columnas,
            $llaves_foraneas, $vistas, $accion, $campos_invisibles);
        if(errores::$error){
            return $this->error->error('Error al generar campos',$campos_html);
        }
        $html .=$campos_html;



        return $html;
    }

    /**
     *
     * Genera un input de tipo HTML
     * @param string $tipo
     * @param string $campo_name
     * @param int $cols
     * @param mixed $valor
     * @param bool $required
     * @param bool $disabled
     * @param bool $ln
     * @param string $etiqueta
     * @param string $pattern
     * @param string $css_id
     * @param array $data_extra
     * @param string $tabla_foranea
     * @param bool $select_vacio_alta
     * @param string $columnas
     * @param string $llaves_valores
     * @param array $filtro
     * @param array $vistas
     * @param string $accion
     * @return array|string array con errores o string con html
     * @example
     *      $html = $this->genera_html_input($this->filtro);
     *
     * @uses templates
     * @internal $this->validacion->valida_existencia_keys($this->input, $keys);
     * @internal $this->genera_dato_html();
     */
    public function genera_html_input(string $tipo, string $campo_name, int $cols, mixed $valor, bool $required,
                                      bool $disabled, bool $ln, string $etiqueta, string $pattern, string $css_id,
                                      array $data_extra, string $tabla_foranea, bool $select_vacio_alta,
                                      string $columnas, string $llaves_valores, array $filtro, array $vistas,
                                      string $accion):array|string{

        $html = '';

        if(in_array($accion,$vistas,false)) {

            if($cols<=0){
                return $this->error->error('Error cols debe ser mayor a 0',$cols);

            }
            if($cols>12){
                return $this->error->error('Error cols debe ser menor a 13',$cols);
            }
            $data_html = $this->genera_dato_html(tipo: $tipo, campo_name: $campo_name,cols:  $cols, valor: $valor,
                required:  $required,disabled:  $disabled,ln:  $ln, etiqueta: $etiqueta,pattern:  $pattern,
                css_id:  $css_id, data_extra:  $data_extra,filtro:  $filtro,tabla_foranea:  $tabla_foranea,
                select_vacio_alta: $select_vacio_alta,columnas:  $columnas, llaves_valores: $llaves_valores);

            if(errores::$error){
                return $this->error->error('Error al generar html',$data_html);
            }

            $html .= $data_html;
        }


        return $html;
    }


    /**
     *
     * @param array $registros
     * @param string $campo_id
     * @param int $n_paginas
     * @param int $pagina_seleccionada
     * @param string $seccion
     * @param array $acciones_asignadas
     * @param string $seccion_link
     * @param string $accion_link
     * @param string $session_id
     * @param array $botones_filtros
     * @param array $filtro_boton_seleccionado
     * @param array $etiqueta_campos
     * @param array $campos
     * @return array|string
     */
    public function lista_completa(array $registros, string $campo_id, int $n_paginas, int $pagina_seleccionada,
                                   string $seccion, array $acciones_asignadas, string $seccion_link,
                                   string $accion_link, string $session_id, array $campos, array $etiqueta_campos,
                                   array $botones_filtros = array(), array $filtro_boton_seleccionado = array()): array|string
    {

        if(count($campos) === 0){
            return $this->error->error('Error los campos de lista no pueden venir vacios',$campos);
        }
        if(count($etiqueta_campos) === 0){
            return $this->error->error('Error $etiqueta_campos esta vacio',$etiqueta_campos);
        }

        $filtro_boton_seleccionado_html = '';
        if(count($filtro_boton_seleccionado)>0){
            $key_filtro = key($filtro_boton_seleccionado);
            $valor = $filtro_boton_seleccionado[$key_filtro];
            $filtro_boton_seleccionado_html .= "&filtro_btn[$key_filtro]=$valor";
        }


        $this->botones_filtros = $botones_filtros;

        $ths = (new listas())->genera_th(etiqueta_campos:  $etiqueta_campos, seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al generar ths',$ths);
        }

        $filtros_lista = (new listas())->genera_filtros_lista(seccion: $seccion,
            botones_filtros:  $this->botones_filtros,campos_filtro: $this->campos_filtro,session_id: $session_id);
        if(errores::$error){
            return $this->error->error('Error al obtener filtros', $filtros_lista);
        }

        $filtros_html = (new listas())->obten_html_filtros($filtros_lista, $filtro_boton_seleccionado_html, $seccion,$session_id);
        if(errores::$error){
            return $this->error->error('Error al obtener filtros', $filtros_html);
        }

        $acciones_completas = (new inicializacion())->acciones( $acciones_asignadas);
        if(errores::$error){
            return $this->error->error('Error al obtener acciones',$acciones_completas);
        }

        $acciones_autorizadas_base = (new listas())->obten_acciones('{registro_id}', '{registro_status}',
            $acciones_completas, $seccion,'icono_menu_lista',$this->link, $session_id);
        if(errores::$error){
            return $this->error->error('Error al obtener acciones autorizadas',$acciones_autorizadas_base);
        }

        $html = $filtros_html;

        $modal = (new directivas())->modal_menu_acciones($acciones_autorizadas_base);
        if(errores::$error){
            return $this->error->error('Error al generar modal',$modal);
        }
        $html.=$modal;

        $modal = (new directivas())->modal_ejecuta_accion();
        if(errores::$error){
            return $this->error->error('Error al generar modal',$modal);
        }
        $html.=$modal;

        $html.= "<div class='table-responsive'>";
        $html .= "<table class='table table-striped table-bordered table-hover letra-mediana text-truncate table-lista'>";

        $html .= "<thead class='thead-azul-light'><tr>$ths</tr></thead><tbody class='listado'>";

        $lista_html = (new listas())->lista(campo_id:  $campo_id, campos:  $campos, registros: $registros,
            seccion: $seccion);
        if(errores::$error){
            return $this->error->error('Error al generar lista',$lista_html);
        }


        $html .= $lista_html;
        $html .= '</tbody></table>';
        $html.= "<div>";

        $paginas_numeradas = '';
        $paginas_previas_mostrables = 4;

        $pagina_inicial = $pagina_seleccionada - $paginas_previas_mostrables;

        if($pagina_inicial <= 0){
            $pagina_inicial = 1;
        }

        if($pagina_inicial > 1) {
            if ($pagina_seleccionada === 1) {
                $paginas_numeradas .= '<li class="page-item active">
          <a class="page-link" href="#">1 <span class="sr-only">(current)</span></a>
        </li>';
            } else {
                $link_pagina = './index.php?seccion='.$seccion_link.'&accion='.$accion_link.'&session_id='.$session_id.'&pag_seleccionada=1'.$filtro_boton_seleccionado_html;
                $paginas_numeradas .= '<li class="page-item"><a class="page-link" href="' . $link_pagina . '">1</a></li>';

            }
        }
        $paginas_vistas = 0;
        for($i = $pagina_inicial; $i<=$n_paginas; $i++){
            if($paginas_vistas >=10){
                if($i < $n_paginas) {
                    continue;
                }
            }
            $link_pagina = './index.php?seccion='.$seccion_link.'&accion='.$accion_link.'&session_id='.$session_id.'&pag_seleccionada='.$i.$filtro_boton_seleccionado_html;
            if($i === $pagina_seleccionada){
                $paginas_numeradas.='<li class="page-item active">
      <a class="page-link" href="#">'.$i.' <span class="sr-only">(current)</span></a>
    </li>';
            }
            else {
                $paginas_numeradas .= '<li class="page-item"><a class="page-link" href="'.$link_pagina.'">' . $i . '</a></li>';
            }
            $paginas_vistas ++;
        }

        $n_pagina_previa = $pagina_seleccionada -1;
        if($n_pagina_previa <= 0){
            $pagina_previa = '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>';
        }
        else{
            $link_pagina = './index.php?seccion='.$seccion_link.'&accion='.$accion_link.'&session_id='.$session_id.'&pag_seleccionada='.$n_pagina_previa.$filtro_boton_seleccionado_html;
            $pagina_previa = '<li class="page-item"><a class="page-link" href="'.$link_pagina.'" tabindex="-1">Previous</a></li>';
        }

        $n_pagina_siguiente = $pagina_seleccionada + 1;

        if($n_pagina_siguiente > $n_paginas){
            $pagina_siguiente = '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Next</a></li>';
        }
        else{
            $link_pagina = './index.php?seccion='.$seccion_link.'&accion='.$accion_link.'&session_id='.$session_id.'&pag_seleccionada='.$n_pagina_siguiente.$filtro_boton_seleccionado_html;
            $pagina_siguiente = '<li class="page-item"><a class="page-link" href="'.$link_pagina.'">Next</a></li>';
        }

        $paginador = '<nav aria-label="..." class="no-print">
<ul class="pagination">
    '.$pagina_previa.'
    '.$paginas_numeradas.'
    '.$pagina_siguiente.'
  </ul>
</nav>';

        $html.=$paginador;

        return $html;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param array $registro
     * @param string $seccion
     * @param string $breadcrumbs
     * @param array $valores_filtrados
     * @param array $campos_alta
     * @param string $session_id
     * @param string $path_base
     * @param bool $aplica_form
     * @param bool $muestra_btn
     * @return array|string
     */
    public function modifica(array $registro, string $seccion, string $breadcrumbs, array $valores_filtrados,
                             array $campos_alta, string $session_id, string $path_base, bool $aplica_form,
                             bool $muestra_btn):array|string{


        $namespace = 'models\\';
        $seccion = str_replace($namespace,'',$seccion);
        $clase = $namespace.$seccion;

        if(trim($seccion) === ''){
            return $this->error->error('Error seccion no puede venir vacia',$seccion);
        }
        if(!class_exists(trim($clase))){
            return $this->error->error('Error no existe la clase',$clase);
        }

        $seccion = trim($seccion);


        $existe_key = $this->validacion->existe_key_data(arreglo: $registro, key: $seccion.'_id');
        if(!$existe_key){
            return $this->error->error('Error no existe $registro['.$seccion.'_id] ',$registro);
        }

        $id_valido = $this->validacion->id(txt:$registro[$seccion.'_id']);
        if(!$id_valido){
            return $this->error->error('Error no es id valido',$registro);
        }

        $directiva = new directivas();

        $html = '';
        if($aplica_form) {
            $html .= "<form
            id='form-" . $seccion . "-modifica' name='form-" . $seccion . "-modifica' 
            method='post' 
            action='./index.php?seccion=" . $seccion . '&accion=modifica_bd&session_id=' . $session_id . '&registro_id='
                . $registro[$seccion . '_id'] . "'
            enctype='multipart/form-data'>
                ";
        }



        $html .=$breadcrumbs;

        $html.= file_get_contents($path_base.'views/_templates/__header_section.php');
        $html .= "<div class='form-row modifica'>";
        $campos_modificables = $this->genera_campos_modificables(valores_filtrados: $valores_filtrados,
            campos_alta: $campos_alta,registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al generar campos',$campos_modificables);

        }

        $html .= $campos_modificables;

        $html .= '<div class="col-md-12"></div>';

        if($muestra_btn) {
            $btn_modifica = $directiva->btn_enviar(cols:2,label:  'Modificar',name:  'btn_modifica', value: 'activo');
            if (errores::$error) {
                return $this->error->error('Error al generar boton',$btn_modifica);
            }
            $html .= $btn_modifica;
        }
        $html .= '</div>';
        if($aplica_form) {
            $html .= '</form>';
        }

        $html.= file_get_contents($path_base.'views/_templates/__footer_section.php');

        return $html;
    }



    /**
     * P ORDER P INT
     * @param array $elementos_lista
     * @return array
     */
    public function obten_campos_filtro(array $elementos_lista): array
    {

        $this->campos_filtro = array();
        foreach ($elementos_lista as $elemento_lista) {

            $campo_filtro = (new inicializacion())->campo_filtro($elemento_lista);
            if(errores::$error){
                return $this->error->error('Error al limpiar datos', $campo_filtro);
            }

            $elemento = array(
                'campo_filtro'=>$campo_filtro,
                'tabla_externa'=>$elemento_lista['elemento_lista_tabla_externa'],
                'campo'=>$elemento_lista['elemento_lista_campo'],
                'etiqueta'=>$elemento_lista['elemento_lista_etiqueta'],
                'columnas'=> '' ,
                'tipo' => $elemento_lista['elemento_lista_tipo']);
            $this->campos_filtro[] = $elemento;
        }
        return $this->campos_filtro;

    }

}

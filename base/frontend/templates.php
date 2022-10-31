<?php
namespace base\frontend;

use config\generales;
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
     * P INT
     * @param array $directivas_extra Directivas no automatizadas a integrar en view
     * @param bool $muestra_btn_guardar si false no muestra el boton
     * @param bool $aplica_form Si aplica form integra eh head de un form
     * @param array $valores_filtrados Valores a aplicar un filtro para form alta
     * @param array $campos Conjunto de campos pa ejecucion del html
     * @param string $seccion
     * @param string $session_id
     * @param string $path_base
     * @param array $campos_disabled Conjunto de campos para dejar como deshabilitados en el frontend
     * @param array $valores_default Valores definidos como default del controller
     * @param array $campos_invisibles Campos que omitira la generacion
     * @return array|string
     */
    public function alta(bool $aplica_form, array $directivas_extra   , array $valores_filtrados, array $campos,
                         string $seccion,string $session_id, string $path_base, array $campos_disabled,
                         array $valores_default, array $campos_invisibles):array|string{
        $directiva = new directivas();
        $valida_metodo = (new validaciones_directivas())->valida_metodos(accion: 'alta', tabla: $seccion);
        if(errores::$error){
            return  $this->error->error(mensaje: "Error al validar metodo",data:$valida_metodo);
        }
        $html = '';


        $html .= file_get_contents($path_base.'views/_templates/__header_section.php');
        $html .= "<div class='form-row  alta'>";
        $data_html = $this->genera_campos_alta(campos:  $campos, valores_filtrados: $valores_filtrados,
            campos_disabled: $campos_disabled,valores_default: $valores_default,
            campos_invisibles: $campos_invisibles);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al generar campos alta',data: $data_html);
        }

        $html .= $data_html;

        foreach ($directivas_extra as $directiva_extra){
            $html.=$directiva_extra;
        }

        $html .= '<div class="col-md-12"></div>';


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
     * Genera los campos para una lista basado en elementos de lista
     * @version 1.30.14
     * @param array $elementos_lista Registros de tipo elementos de lista
     * @return array|stdClass
     */
    public function campos_lista(array $elementos_lista): array|stdClass
    {

        $campos_lista = (new inicializacion())->campos_lista(elementos_lista: $elementos_lista);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar campos lista',data: $campos_lista);
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
                                    bool $ln,string $etiqueta, string $css_id, array $data_extra,
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
            disabled:  $disabled, ln: $ln, etiqueta: $etiqueta, css_id: $css_id,
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
     * P INT
     * @param string $tipo tipo de input
     * @param string $campo_name Name del input
     * @param int $cols Columnas para css
     * @param string $valor Valor del input
     * @param bool $required indica si es requerido o no
     * @param bool $disabled Si disabled genera un atributo de tipo disabled para input
     * @param bool $ln Si true aplica div 12
     * @param string $etiqueta Etiqueta de input
     * @param string $pattern Regex para validacion html
     * @param string $css_id Identificador css para java
     * @param array $data_extra Elementos para integrar extraparams
     * @param string $tabla_foranea Tabla de ligue
     * @param bool $select_vacio_alta si true no mostrar datos en el select
     * @param array $valores_filtrados Valores a mostrar en select filtrados
     * @param string $columnas columnas para select
     * @param string $llaves_foraneas llaves para integrar con select
     * @param array $vistas Conjunto de views permitidas
     * @param string $accion Accion para view
     * @param array $campos_invisibles
     * @return array|string
     */
    public function genera_campos(string $accion, string $campo_name, array $campos_invisibles, string $css_id,
                                  int $cols, string $columnas, array $data_extra, bool $disabled, string $etiqueta,
                                  string $llaves_foraneas, bool $ln, bool $required,
                                  bool $select_vacio_alta, string $tipo, mixed $valor, string $tabla_foranea,
                                  array $valores_filtrados, array $vistas):array|string{

        if($accion === ''){
            return $this->error->error(mensaje: 'Error accion debe existir',data: $accion);
        }

        if(in_array($campo_name, $campos_invisibles,false)){
            return '';
        }
        $etiqueta_valida = $this->validacion->letra_numero_espacio(txt: $etiqueta);
        if(!$etiqueta_valida){
            return $this->error->error(mensaje: 'Error etiqueta no es valida',data: $etiqueta);
        }
        $filtro = $valores_filtrados[$campo_name] ?? array();


        $html = $this->genera_html_input(accion: $accion, campo_name: $campo_name, css_id: $css_id, cols: $cols,
            columnas: $columnas, data_extra: $data_extra, disabled: $disabled, etiqueta: $etiqueta, filtro: $filtro,
            llaves_valores: $llaves_foraneas, ln: $ln, required: $required,
            select_vacio_alta: $select_vacio_alta, tabla_foranea: $tabla_foranea, tipo: $tipo, valor: $valor,
            vistas: $vistas);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar html',data: $html);
        }

        return $html;
    }

    /**
     * Genera el html de un form alta base
     * @param array $valores_filtrados Valores a aplicar un filtro para form alta
     * @param array $campos Conjunto de campos pa ejecucion del html
     * @param array $campos_disabled Conjunto de campos para dejar como deshabilitados en el frontend
     * @param array $valores_default Valores definidos como default del controller
     * @param array $campos_invisibles Campos que omitira la generacion
     * @return array|string
     */
    public function genera_campos_alta(array $campos, array $valores_filtrados, array $campos_disabled,
                                       array $valores_default, array $campos_invisibles):array|string{ //FIN
        $html = '';

        $campos_alta = $campos['campos'];
        foreach ($campos_alta as $campo_name=>$input){
            $valor = $valores[$campo_name]??false;
            $disabled = false;
            if(in_array($campo_name,$campos_disabled,false)){
                $disabled = true;
            }
            $key = in_array($input['adm_elemento_lista_id'], $valores_default, false);
            if(false !== $key){
                $valor = $valores_default['default'];
            }

            $keys = array('data_extra','required');
            $valida = $this->validacion->valida_existencia_keys(keys:  $keys, registro: $input);
            if(errores::$error){
                return  $this->error->error(mensaje: 'Error al validar input',data: $valida);
            }

            if(!isset($input['llaves_foraneas'])){
                $input['llaves_foraneas'] = '';
            }

            $accion = 'alta';
            $data_html = $this->genera_campos(accion: $accion, campo_name: $campo_name,
                campos_invisibles: $campos_invisibles, css_id: $input['css_id'], cols: $input['cols'],
                columnas: $input['columnas'], data_extra: $input['data_extra'], disabled: $disabled,
                etiqueta: $input['etiqueta'], llaves_foraneas: $input['llaves_foraneas'], ln: $input['ln'],
                required: $input['required'],
                select_vacio_alta: $input['select_vacio_alta'], tipo: $input['tipo'], valor: $valor,
                tabla_foranea: $input['tabla_foranea'], valores_filtrados: $valores_filtrados, vistas: array('alta'));
            if(errores::$error){
                return  $this->error->error(mensaje: 'Error al generar campos',data: $data_html);
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
     * @param string $campo_name Name del input
     * @param string $css_id Identificador css para java
     * @param int $cols Columnas para css
     * @param string $columnas Columnas para select
     * @param array $data_extra Elementos para integrar extraparams
     * @param bool $disabled Si disabled genera un atributo de tipo disabled para input
     * @param string $etiqueta Etiqueta de input
     * @param array $filtro Filtro para selects
     * @param string $llaves_valores LLaves para select
     * @param bool $ln Si true aplica div 12
     * @param bool $required Si required genera un atributo de tipo required para input
     * @param bool $select_vacio_alta si true deja select vacio
     * @param string $tabla_foranea Tabla de enlace para select
     * @param string $tipo tipo de input
     * @param string $valor Valor del input
     * @return array|string html con info del input a mostrar
     * @example
     *      $$data_html = $this->genera_dato_html();
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
    public function genera_dato_html(string $campo_name, string $css_id, int $cols, string $columnas,
                                     array $data_extra, bool $disabled, string $etiqueta, array $filtro,
                                     string $llaves_valores, bool $ln, bool $required,
                                     bool $select_vacio_alta, string $tabla_foranea, string $tipo,
                                     mixed $valor):array|string{

        if($cols <0){
            return $this->error->error(mensaje: 'Error cols debe ser mayor a 0',data: $cols);
        }
        if($cols >12){
            return $this->error->error(mensaje: 'Error cols debe ser menor a 12',data: $cols);
        }

        $data_html = '';

        $directiva = new directivas();



        if($tipo === 'select_columnas' || $tipo === 'select'){
            $columnas_sl = array();
            if(($tipo === 'select_columnas') && $columnas !== '') {
                $columnas_sl = explode(',', $columnas);
            }

            if( $tipo === 'select') {
                $columnas_sl = array($tabla_foranea.'_id',$tabla_foranea.'_descripcion');
            }

            $data_html = $directiva->input_select_columnas(campo_name: $campo_name, link: $this->link,
                tabla: $tabla_foranea, cols: $cols, columnas: $columnas_sl, data_extra: $data_extra, disabled: $disabled,
                etiqueta: $etiqueta, filtro: $filtro, ln: $ln, select_vacio_alta: $select_vacio_alta,
                required: $required, valor: $valor);

            if(errores::$error){
                return  $this->error->error(mensaje: 'Error al obtener select columnas del modelo '.$tabla_foranea,
                    data: $data_html);
            }

        }

        if( $tipo === 'select_estatico') {


            $data_html =  $directiva->genera_select_estatico(campo_name: $campo_name, llaves_valores: $llaves_valores,
                css_id: $css_id, cols: $cols, etiqueta: $etiqueta, valor: $valor );

            if(errores::$error){
                return  $this->error->error('Error al obtener genera_select_estatico',$data_html);
            }

        }

        if( $tipo === 'textarea') {
            $data_html = $directiva->textarea(campo_name: $campo_name,cols: $cols,value:  $valor,etiqueta:  $etiqueta);
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
                css_id:   $data['css_id'], data_extra: $data['data_extra'],
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
                                     bool $ln, string $etiqueta, string $css_id, array $data_extra,
                                     string $tabla_foranea, bool $select_vacio_alta, array $valores_filtrados,
                                     string $columnas, string $llaves_foraneas, array $vistas,array $campos_invisibles,
                                     string $key_validar, string $html, array $registro):array|string{


        if(!isset($registro[trim($key_validar)])){
            $registro[trim($key_validar)] ='';
        }

        $valor = (string)$registro[trim($key_validar)];

        $accion ='modifica';

        $campos_html = $this->genera_campos(accion: $accion, campo_name: $campo_name,
            campos_invisibles: $campos_invisibles, css_id: $css_id, cols: $cols, columnas: $columnas,
            data_extra: $data_extra, disabled: $disabled, etiqueta: $etiqueta, llaves_foraneas: $llaves_foraneas,
            ln: $ln, required: $required, select_vacio_alta: $select_vacio_alta, tipo: $tipo,
            valor: $valor, tabla_foranea: $tabla_foranea, valores_filtrados: $valores_filtrados, vistas: $vistas);
        if(errores::$error){
            return $this->error->error('Error al generar campos',$campos_html);
        }
        $html .=$campos_html;



        return $html;
    }

    /**
     * P INT
     * Genera un input de tipo HTML
     * @param string $accion Accion para view
     * @param string $campo_name Name del input
     * @param string $css_id Identificador css para java
     * @param int $cols Columnas para css
     * @param string $columnas columnas para select
     * @param array $data_extra Elementos para integrar extraparams
     * @param bool $disabled Si disabled genera un atributo de tipo disabled para input
     * @param string $etiqueta Etiqueta de input
     * @param array $filtro Filtro para selects
     * @param string $llaves_valores llaves en forma json
     * @param bool $ln Si true aplica div 12
     * @param bool $required indica si es requerido o no
     * @param bool $select_vacio_alta si true no mostrara datos el select
     * @param string $tabla_foranea Tabla de ligue
     * @param string $tipo tipo de input
     * @param string $valor Valor del input
     * @param array $vistas Conjunto de views permitidas
     * @return array|string array con errores o string con html
     * @example
     *      $html = $this->genera_html_input($this->filtro);
     *
     * @uses templates
     * @internal $this->validacion->valida_existencia_keys($this->input, $keys);
     * @internal $this->genera_dato_html();
     */
    public function genera_html_input(string $accion, string $campo_name, string $css_id, int $cols, string $columnas,
                                      array $data_extra, bool $disabled, string $etiqueta, array $filtro,
                                      string $llaves_valores, bool $ln, bool $required,
                                      bool $select_vacio_alta, string $tabla_foranea, string $tipo, mixed $valor,
                                      array $vistas):array|string{

        $html = '';

        if(in_array($accion,$vistas,false)) {

            if($cols<=0){
                return $this->error->error(mensaje: 'Error cols debe ser mayor a 0',data: $cols);

            }
            if($cols>12){
                return $this->error->error(mensaje: 'Error cols debe ser menor a 13',data: $cols);
            }
            $data_html = $this->genera_dato_html(campo_name: $campo_name, css_id: $css_id, cols: $cols,
                columnas: $columnas, data_extra: $data_extra, disabled: $disabled, etiqueta: $etiqueta,
                filtro: $filtro, llaves_valores: $llaves_valores, ln: $ln, required: $required,
                select_vacio_alta: $select_vacio_alta, tabla_foranea: $tabla_foranea, tipo: $tipo, valor: $valor);

            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar html',data: $data_html);
            }

            $html .= $data_html;
        }


        return $html;
    }


    /**
     * P INT
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
    public function lista_completa(string $campo_id, array $registros, int $n_paginas, int $pagina_seleccionada,
                                   string $seccion, array $acciones_asignadas, string $seccion_link,
                                   string $accion_link, string $session_id, array $campos, array $etiqueta_campos,
                                   array $botones_filtros = array(), array $filtro_boton_seleccionado = array()): array|string
    {


        $filtro_boton_seleccionado_html = '';
        if(count($filtro_boton_seleccionado)>0){
            $key_filtro = key($filtro_boton_seleccionado);
            $valor = $filtro_boton_seleccionado[$key_filtro];
            $filtro_boton_seleccionado_html .= "&filtro_btn[$key_filtro]=$valor";
        }


        $this->botones_filtros = $botones_filtros;

        $ths = (new listas())->genera_th(etiqueta_campos:  $etiqueta_campos, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar ths',data: $ths);
        }


        $filtros_lista = (new listas())->genera_filtros_lista(botones_filtros:  $this->botones_filtros,
            seccion: $seccion,session_id: $session_id);
        if(errores::$error){
            return $this->error->error('Error al obtener filtros', $filtros_lista);
        }

        $filtros_html = (new listas())->obten_html_filtros(filtros_lista: $filtros_lista,
            filtro_boton_seleccionado_html:  $filtro_boton_seleccionado_html,seccion:  $seccion,session_id: $session_id);
        if(errores::$error){
            return $this->error->error('Error al obtener filtros', $filtros_html);
        }



        $acciones_autorizadas_base = (new listas())->obten_acciones(id:'{registro_id}', seccion:  $seccion,
            class_link: 'icono_menu_lista', link: $this->link, session_id: $session_id);
        if(errores::$error){
            return $this->error->error('Error al obtener acciones autorizadas',$acciones_autorizadas_base);
        }

        $html = $filtros_html;

        $modal = (new directivas())->modal_menu_acciones(acciones_autorizadas_base:$acciones_autorizadas_base);
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
            return $this->error->error(mensaje: 'Error al generar lista',data: $lista_html);
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
     * @return array|string
     */
    public function modifica(array $registro, string $seccion, string $breadcrumbs, array $valores_filtrados,
                             array $campos_alta, string $session_id, string $path_base, bool $aplica_form):array|string{


        if(trim($seccion) === ''){
            return $this->error->error('Error seccion no puede venir vacia',$seccion);
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
        if((new generales())->sistema === 'administrador'){
            $html.= file_get_contents($path_base.'views/_templates/__header_section.php');
        }

        $html .= "<div class='form-row modifica'>";
        $campos_modificables = $this->genera_campos_modificables(valores_filtrados: $valores_filtrados,
            campos_alta: $campos_alta,registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al generar campos',$campos_modificables);

        }

        $html .= $campos_modificables;

        $html .= '<div class="col-md-12"></div>';


        $html .= '</div>';
        if($aplica_form) {
            $html .= '</form>';
        }

        if((new generales())->sistema === 'administrador') {
            $html .= file_get_contents($path_base . 'views/_templates/__footer_section.php');
        }

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
                'tabla_externa'=>$elemento_lista['adm_elemento_lista_tabla_externa'],
                'campo'=>$elemento_lista['adm_elemento_lista_campo'],
                'etiqueta'=>$elemento_lista['adm_elemento_lista_etiqueta'],
                'columnas'=> '' ,
                'tipo' => $elemento_lista['adm_elemento_lista_tipo']);
            $this->campos_filtro[] = $elemento;
        }
        return $this->campos_filtro;

    }

}

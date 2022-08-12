<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

/**
 * PARAMS ORDER, PARAMS INT
 */
class etiquetas{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }
    /**
     *
     * Ajusta el texto enviado basado en el tip de letra capitalize, mayúsculos, minusculas
     *
     * @param string $tipo_letra Tipo de letra para realizar ajuste de texto capitalize, mayúsculas, minusculas
     * @param string $texto Texto a ejecutar funcion de ajuste
     *
     * @example
     *      $tipo_letra = 'mayusculas;
     *      $texto = 'nombre'
     *      $campo_capitalize = $this->ajusta_texto($tipo_letra,$texto);
     *
     * @return array|string string palabra ajustada
     * @throws errores texto vacio
     * @throws errores tipo_letra no sea una de las tres validas
     * @throws errores tipo_letra vacia
     * @uses  $directivas->genera_texto_etiqueta
     * @version 1.310.41
     */
    private function ajusta_texto(string $texto, string $tipo_letra ):array|string{
        $texto = trim($texto);
        $tipo_letra = trim($tipo_letra);

        $campo_capitalize = $texto;
        if($tipo_letra === 'capitalize'){
            $campo_capitalize = strtolower($texto);
            $campo_capitalize = ucwords($campo_capitalize);
        }
        if($tipo_letra === 'mayusculas'){
            $campo_capitalize = strtoupper($texto);
        }
        if($tipo_letra === 'minusculas'){
            $campo_capitalize = strtolower($texto);
        }

        return $campo_capitalize;
    }


    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param bool $con_label
     * @param string $size
     * @param string $campo
     * @param string $campo_capitalize
     * @return string|array
     */
    public function con_label(string $campo, string $campo_capitalize, bool $con_label, string $size): string|array
    {
        $html = '';
        if($con_label) {
            $size = trim($size);
            if($size === ''){
                return $this->error->error('Error size no puede venir vacio',$size);
            }
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error('Error $campo no puede venir vacio',$campo);
            }
            $campo_capitalize = trim($campo_capitalize);
            if($campo_capitalize === ''){
                return $this->error->error('Error $campo_capitalize no puede venir vacio',$campo_capitalize);
            }

            $html = "<label class='col-form-label-$size' for='$campo'>$campo_capitalize</label>";
        }
        return $html;
    }

    /**
     * PROBADO PARAMS INT PARAMS ORDER ERRORREV
     * @param string $campo_busca
     * @return string|string[]
     */
    public function etiqueta_campo_vista(string $campo_busca): array|string
    {
        return str_replace(array('_', '.', '[', ']'), ' ', $campo_busca);
    }

    /**
     * Genera una etiqueta para checkbox
     * @param string $etiqueta Txt con etiqueta a mostrar
     * @return string
     * @version 1.257.40
     * @version 1.1.0
     * @author mgamboa
     * @fecha 2022-08-02 11:39
     *
     */
    public function etiqueta_chk(string $etiqueta): string
    {
        $etiqueta = trim($etiqueta);
        return "<button class='btn btn-default btn-checkbox' type='button'>".$etiqueta."</button>";
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $tabla
     * @param string $tipo_letra
     * @param string $etiqueta
     * @return array|string
     */
    public function etiqueta_label(string $etiqueta, string $tabla, string $tipo_letra): array|string
    {
        $etiqueta_label = strtoupper($tabla);
        $etiqueta_label = $this->genera_texto_etiqueta(texto: $etiqueta_label, tipo_letra: $tipo_letra);
        if (errores::$error) {
            return $this->error->error('Error al generar etiqueta',$etiqueta_label);
        }
        $etiqueta_label_mostrable = $this->etiqueta_label_mostrable(etiqueta:  $etiqueta, etiqueta_label: $etiqueta_label);
        if (errores::$error) {
            return $this->error->error('Error al generar etiqueta',$etiqueta_label_mostrable);
        }
        return $etiqueta_label_mostrable;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param string $etiqueta_label
     * @param string $etiqueta
     * @return string
     */
    private function etiqueta_label_mostrable(string $etiqueta, string $etiqueta_label): string
    {
        $etiqueta = trim($etiqueta);
        $etiqueta_label = trim($etiqueta_label);
        $etiqueta_label_mostrable = $etiqueta_label;
        if($etiqueta!==''){
            $etiqueta_label_mostrable = $etiqueta;
        }
        return $etiqueta_label_mostrable;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * Genera un label para mostrarlo en html ajusta el texto del campo para que sea mas aceptable para el usuario
     *
     * @param string $tipo_letra Tipo de letra para realizar ajuste de texto capitalize, mayusculas, minusculas
     * @param string $campo Texto a ejecutar funcion de ajuste regularmente un campo de bd
     * @param bool $aplica_etiqueta si aplica etiqueta genera label, sino la envia vacia
     *
     * @example
     *      $label = $this->genera_label($campo,$tipo_letra,true);
     *
     * @return array|string html ajustando en label
     * @throws errores $campo vacio
     * @throws errores tipo_letra no sea una de las tres validas
     * @throws errores tipo_letra vacia
     */
    public function genera_label(bool $aplica_etiqueta, string $campo,string $tipo_letra, string $size = 'sm'):array|string{ //FIN PROT
        $campo = trim($campo);
        $tipo_letra = trim($tipo_letra);
        if(!$aplica_etiqueta){
            return '';
        }

        if($campo === ''){
            return $this->error->error("Error campo vacio", $campo);
        }

        $campo_mostrable = $this->genera_texto_etiqueta(texto: $campo,tipo_letra: $tipo_letra);
        if(errores::$error){
            return $this->error->error("Error al generar texto", $campo_mostrable);
        }

        return "<label for='$campo' class='col-form-label-$size'>$campo_mostrable</label>";
    }

    /**
     *
     * Ajusta el texto enviado basado en el tip de letra capitalize, mayusculas, minusculas y sustituye guiones
     * bajos por espacios
     *
     * @param string $tipo_letra Tipo de letra para realizar ajuste de texto capitalize, mayusculas, minusculas
     * @param string $texto Texto a ejecutar funcion de ajuste
     *
     * @example
     *      $etiqueta = 'nombre'
     *      $r_etiqueta = $this->genera_texto_etiqueta($etiqueta,'capitalize');
     *
     * @return array|string string palabra ajustada
     * @throws errores texto vacio
     * @throws errores tipo_letra no sea una de las tres validas
     * @throws errores tipo_letra vacia
     * @version 1.310.41
     */
    public function genera_texto_etiqueta(string $texto, string $tipo_letra):array|string{
        if($texto === ''){
            return $this->error->error(mensaje: "Error texto vacio",data: $texto);
        }
        $campo_capitalize = $this->ajusta_texto(texto: $texto, tipo_letra: $tipo_letra);
        if(errores::$error){
            return $this->error->error(mensaje: "Error al ajustar texto",data: $campo_capitalize);
        }
        $campo_capitalize = str_replace('_', ' ', $campo_capitalize);

        return trim($campo_capitalize);
    }

    /**
     * Genera un label de un input de tipo file
     * @param string $etiqueta Etiqueta a mostrar
     * @return string|array
     * @version 1.312.41
     */
    private function label_input_upload(string $etiqueta): string|array
    {
        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return  $this->error->error('Error etiqueta esta vacio',$etiqueta);
        }
        return '<label class="custom-file-label" for="'.$etiqueta.'">'.$etiqueta.'</label>';
    }

    /**
     * Genera la etiqueta de un input file
     * @param string $codigo Codigo de input mostrado en file
     * @return string|array
     * @version 1.311.41
     */
    private function label_upload(string $codigo): string|array
    {
        $codigo = trim($codigo);
        if($codigo === ''){
            return  $this->error->error(mensaje: 'Error codigo esta vacio',data: $codigo);
        }

        $html =     '<div class="input-group-prepend">';
        $html.=         '<span class="input-group-text" >'.$codigo.'</span>';
        $html.=     '</div>';

        return $html;
    }

    /**
     * Genera label de files multiple
     * @param string $codigo Codigo para ser mostrado en label
     * @param string $etiqueta Etiqueta a mostrar
     * @return array|stdClass
     * @version 1.314.41
     */
    public function labels_multiple(string $codigo, string $etiqueta): array|stdClass
    {
        $codigo = trim($codigo);
        if($codigo === ''){
            return  $this->error->error(mensaje: 'Error codigo esta vacio',data: $codigo);
        }

        $etiqueta = trim($etiqueta);
        if($etiqueta === ''){
            return  $this->error->error(mensaje: 'Error etiqueta esta vacio',data: $etiqueta);
        }

        $label_upload = $this->label_upload(codigo: $codigo);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener label',data: $label_upload);
        }

        $label_input_upload = $this->label_input_upload(etiqueta: $etiqueta);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener label',data: $label_input_upload);
        }

        $data = new stdClass();
        $data->label_upload = $label_upload;
        $data->label_input_upload = $label_input_upload;

        return $data;
    }

    /**
     * Genera un span para un checkbox
     * @param string $data_etiqueta Datos de la etiqueta del chk
     * @param string $span_chk Texto del chk
     * @return string
     * @version 1.305.41
     */
    public function span_btn_chk(string $data_etiqueta, string $span_chk): string
    {
        return "<span class='input-group-btn'>$data_etiqueta $span_chk</span>";
    }

    /**
     * Genera un un span para un checkbox
     * @param string $data_input Data de input de checkbox en html
     * @return string
     * @version 1.296.41
     */
    public function span_chk(string $data_input): string
    {
        return "<span class='input-group-addon checkbox_directiva'>$data_input</span>";
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT ERROREV
     * @param string $txt
     * @return string|array
     */
    public function title(string $txt): string|array
    {
        $title = trim(str_replace('_',' ',$txt));
        if($title === ''){
            return  $this->error->error(mensaje: 'Error title esta vacio',data: $title, params: get_defined_vars());
        }
        return ucwords(trim($title));
    }
}

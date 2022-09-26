<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use NumberFormatter;
use Throwable;

class values{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }
    /**
     * PROBADO P ORDER P INT
     * @param array $campos
     * @param string $key
     * @param array $registro
     * @return array
     */
    private function adapta_valor_campo(array $campos,string $key, array $registro): array{
        $key = trim($key);
        if(count($campos) === 0){
            return $this->error->error('Error campos no puede venir vacio',$campos);
        }
        if(trim($key) === ''){
            return $this->error->error('Error $key no puede venir vacio',$key);
        }

        if(count($registro) === 0){
            return $this->error->error('Error $registro no puede venir vacio',$registro);
        }
        if(!isset($campos[$key])){
            return $this->error->error('Error $campos['.$key.'] no existe',$campos);
        }

        if(!isset($campos[$key]['representacion'])){
            return $this->error->error('Error $campos['.$key.'][representacion] no existe',$campos);

        }
        if(!isset($registro[$key])){
            $registro[$key] = '';
        }
        if($campos[$key]['representacion'] === 'moneda'){
            if(!is_numeric($registro[$key])){
                return $this->error->error('Error $registro['.$key.'] debe ser un numero',$registro);
            }
            $registro[$key] = '$'.number_format($registro[$key],2);
        }

        return $registro;
    }

    /**
     * P ORDER P INT
     * @param array $registro
     * @param array $campos
     * @return array
     */
    private function adapta_valor_registro(array $campos, array $registro): array{

        if(count($registro) === 0){
            return $this->error->error('Error $registro no puede venir vacio',$registro);
        }
        if(count($campos) === 0){
            return $this->error->error('Error $campos no puede venir vacio',$campos);
        }

        $registro = $this->valores_registro(campos: $campos, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al adaptar valor',$registro);
        }

        return $registro;
    }

    /**
     * P ORDER P INT
     * @param array $campos
     * @param string $key
     * @param array $registro
     * @return array
     */
    private function adapta_valor_campo_val(array$campos, string $key, array $registro): array
    {
        $key = trim($key);
        if($key === ''){
            return $this->error->error('Error key no puede venir vacio',$key);
        }
        if(!isset($campos[$key]['representacion'])){
            return $this->error->error('Error no existe representacion',$campos);
        }

        $registro = $this->adapta_valor_campo(campos: $campos,key: $key, registro: $registro);
        if(errores::$error){
            return $this->error->error('Error al adaptar valor',$registro);
        }

        return $registro;
    }

    /**
     * P ORDER P INT
     * @param array $campos
     * @param array $registros
     * @return array
     */
    public function ajusta_formato_salida_registros(array $campos, array $registros): array{
        if(count($campos) === 0){
            $this->error->error('Error $campos no puede venir vacio',$campos);
        }

        $registros_ajustados = array();

        foreach($registros as $registro){
            if(!is_array($registro)){
                return $this->error->error('Error $registro tiene que ser un array',$registro);
            }
            if(count($registro) === 0){
                return $this->error->error('Error $registro no puede venir vacio',$registro);
            }
            $registro = $this->adapta_valor_registro(campos: $campos, registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al adaptar valor',$registro);
            }
            $registros_ajustados[] = $registro;
        }

        return $registros_ajustados;
    }

    /**
     *
     * @param array $data
     * @param array $keys_moneda
     * @return array
     */
    public function aplica_valores_moneda(array $data, array $keys_moneda):array{
        foreach($data as $key=>$value){
            if(is_array($value)){
                continue;
            }
            if(is_null($value)){
                $value = '';
            }
            $data = $this->valores_moneda($key,$keys_moneda,(string)$value,$data);
            if(errores::$error){
                return $this->error->error("Error asignar valores de moneda", $data);
            }
        }
        return $data;
    }

    /**
     * P INT
     * Devuelve los valores ingresados en valores, su funcion principal es la asignacion de valores si son nulos
     *  carga vacio sino cargados
     *
     * @param string $campo nombre de un campo definido en la BD
     * @param array $valores Valores para la asignacion default de un campo
     * @example
     *      $controlador = new controler();
     *      $campo = 'name_campo';
     *      $valores = array('name_campo'=>'1')
     *      print_r($valores);
     *      //array('name_campo'=>'1');
     *
     * @example
     *      $controlador = new controler();
     *      $campo = 'name_campo';
     *      $valores = array()
     *      print_r($valores);
     *      //array('name_campo'=>'');
     *
     * @return array Valores inicializados
     * @throws errores $campo = vacio
     */
    private function asigna_valor_campo_template(string $campo, array $valores): array{
        if($campo === ''){
            return $this->error->error('Error $campo no puede venir vacio',$campo);
        }
        if(!isset($valores[$campo])||(string)$valores[$campo] === ''){
            $valores[$campo] = '';
        }

        return $valores;
    }

    /**
     * P INT
     * Genera la etiqueta en txt para mostrarse en html Con mayusculas por cada letra limpia caracteres
     *
     * @param string $campo variable para parsear elemento con la salida deseada

     * @example
     *      $campo = 'xxxx';
     *      $etiqueta = $this->asigna_valores_etiqueta_template($campo);
     *      $etiqueta = Xxxx
     *
     * @return array|string string con codigo html del input
     * @throws errores campo vacio
     * @throws errores si resultado de ajuste es vacio
     */
    private function asigna_valores_etiqueta_template(string $campo):array|string{
        if($campo === ''){
            return $this->error->error('Error $campo no puede venir vacio',$campo);
        }

        $etiqueta = str_replace('_',' ',$campo);
        $etiqueta = ucwords($etiqueta);

        if(trim($etiqueta) === ''){
            return $this->error->error('Error $etiqueta la etiqueta quedo vacia',$campo);
        }


        return $etiqueta;
    }

    /**
     * P ORDER P INT
     * @param string $value_html
     * @param string $data_extra_html
     * @param string $selected
     * @return string|array
     */
    public function content_option(string $data_extra_html, string $selected, string $value_html): string|array
    {
        $value_html = trim($value_html);
        if($value_html === ''){
            return $this->error->error('Error $value_html esta vacio ',$value_html);
        }
        return "$value_html $data_extra_html $selected";
    }

    /**
     * P ORDER P INT PROBADO
     * @param array $value
     * @param string $tabla
     * @return string|array
     */
    public function content_option_value(string $tabla, array $value): string|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error('Error tabla esta vacia ',$tabla);
        }
        $key = $tabla.'_id';
        if(!isset($value[$key])){
            $value[$key] = '';
        }
        return "value='" . $value[$key] . "'";
    }

    /**
     * Integra un extra param en un option
     * @param string $data Dato a integrar
     * @param string $value Valor del extra param
     * @return string|array
     * @version 1.507.50
     */
    public function data_extra_html_base(string $data, string $value): string|array
    {
        $data = trim($data);
        if($data === ''){
            return $this->error->error(mensaje: 'Error al data esta vacio',data: $data);
        }
        $data_extra_html = 'data-'.$data;
        $data_extra_html .= '  =  ';
        $data_extra_html .= "'".$value."'";
        $data_extra_html .= ' ';
        return $data_extra_html;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param array $data_con_valor
     * @return array|string
     */
    public function datas_con_valor(array $data_con_valor): array|string
    {
        $data_extra_html = '';
        foreach($data_con_valor as $key_value=>$valor){
            $data_ex = $this->data_extra_html_base(data: $key_value, value: $valor);
            if(errores::$error){
                return $this->error->error('Error al generar data extra',$data_ex);
            }
            $data_extra_html.=$data_ex;

        }
        return $data_extra_html;
    }

    /**
     * P INT
     * Asigna los valores parseados para su procesamiento, hace lo mismo para las etiquetas de ese campo
     *
     * @param string $campo variable para parsear elemento con la salida deseada
     * @param array $valores variables donde estan guardados los valores de un campo de tipo registro

     * @example
     *      $campo = 'xxxx';
     *      $valores = array();
     *      $data_input_template = $this->obten_data_input_template($campo,$valores);
     *      $data_input_template = array(valores=>array(xxxx=>''),etiqueta=>'Xxxx')
     *
     * @return array array(valores=>array(),etiqueta=>string)
     * @throws errores campo vacio
     */
    public function obten_data_input_template(string $campo, array $valores):array{
        if($campo === ''){
            return $this->error->error('Error $campo no puede venir vacio',$campo);
        }
        $valores_campo = $this->asigna_valor_campo_template(campo: $campo,valores:  $valores);
        if(errores::$error){
            return $this->error->error('Error al asignar valores del campo '.$campo,$valores_campo);
        }
        $etiqueta = $this->asigna_valores_etiqueta_template(campo: $campo);
        if(errores::$error){
            return $this->error->error('Error al asignar etiqueta '.$campo,$etiqueta);
        }

        return array('valores'=>$valores_campo,'etiqueta'=>$etiqueta);
    }

    /**
     * P ORDER P INT PROBADO
     * @param string $valor
     * @return int|string
     */
    public function valor_envio(string $valor): int|string
    {
        $valor_envio = $valor;
        if($valor === ''){
            $valor_envio = -1;
        }
        return $valor_envio;
    }

    /**
     * P ORDER P INT
     * @param string $key
     * @param array $campos
     * @param array $registro
     * @return array
     */
    private function valor_registro_row(array $campos, string $key, array $registro): array
    {
        if(is_numeric($key)){
            return $this->error->error('$registro['.$key.'] key invalido tiene que ser un txt',$registro);
        }
        if(isset($campos[$key])){
            $registro = $this->adapta_valor_campo_val(campos: $campos, key:  $key, registro:  $registro);
            if(errores::$error){
                return $this->error->error('Error al adaptar valor',$registro);
            }
        }

        return $registro;
    }

    /**
     * P ORDER P INT
     * @param array $campos
     * @param array $registro
     * @return array
     */
    private function valores_registro(array $campos, array $registro): array
    {
        foreach($registro as $key=>$valor){
            $registro = $this->valor_registro_row(campos:  $campos, key: $key, registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al adaptar valor',$registro);
            }
        }
        return $registro;
    }

    /**
     * Genera un valor fecha default
     * @param string $value valor init
     * @param bool $value_vacio is vacio deja vacio
     * @param string $tipo Tipo de fecha date, datetime local
     * @return string
     * @version 1
     * 1.310.41
     */
    public function value_fecha(string $tipo, string $value, bool $value_vacio): string
    {
        if($value === '' && !$value_vacio && $tipo ==='date'){
            $value = date('Y-m-d');
        }
        if($value==='' && !$value_vacio && $tipo === 'datetime-local'){
            $value = date("Y-m-d\Th:i");
        }
        return $value;
    }

    /**
     * PROBADO P ORDER P INT
     * @param string|float|int $valor
     * @return string|array
     */
    public function valor_moneda(string|float|int $valor):string|array{
        $valor_r = $valor;
        if((string)$valor_r === ''){
            $valor_r = 0;
        }
        $valor_r = str_replace(array('$', ','), '', $valor_r);

        $valor_r = (float)$valor_r;

        $number_formatter = new NumberFormatter("es_MX", NumberFormatter::CURRENCY);
        try {
            $valor_r = $number_formatter->format($valor_r);
        }
        catch (Throwable $e){
            return $this->error->error("Error al maquetar moneda", $e);
        }
        return $valor_r;
    }

    /**
     *
     * @param string $campo
     * @param array $keys_moneda
     * @param float|string|int $valor
     * @param array $data
     * @return array
     */
    private function valores_moneda(string $campo, array $keys_moneda, float|string|int $valor, array $data):array{
        if(in_array($campo, $keys_moneda)){
            $valor = $this->valor_moneda($valor);
            if(errores::$error){
                return $this->error->error("Error al maquetar moneda", $valor);
            }
            $data[$campo] = $valor;
        }
        return $data;
    }

}

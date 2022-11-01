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
            return $this->error->error(mensaje: 'Error $campo no puede venir vacio',data: $campo);
        }

        $etiqueta = str_replace('_',' ',$campo);
        $etiqueta = ucwords($etiqueta);

        if(trim($etiqueta) === ''){
            return $this->error->error(mensaje: 'Error $etiqueta la etiqueta quedo vacia',data: $campo);
        }


        return $etiqueta;
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
     * Genera un valor de nevio para select
     * @param string $valor Valor a integrar
     * @return int|string
     * @version 1.509.51
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

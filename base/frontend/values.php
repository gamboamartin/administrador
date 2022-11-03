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

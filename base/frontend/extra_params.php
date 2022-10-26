<?php
namespace base\frontend;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;

/**
 * PARAMS ORDER, PARAMS INT PROBADO
 */
class extra_params{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }
    /**
     * Genera un extra param basico
     * @param array $value Valor a integrar
     * @param string $data dato
     * @return array|string
     * @version 1.507.50
     */
    private function data_extra_base(string $data, array $value): array|string
    {
        $data = trim($data);
        if($data === ''){
            return $this->error->error(mensaje: 'Error al data esta vacio',data: $data);
        }

        if(!isset($value[$data])){
            $value[$data] = '';
        }

        $data_ex = (new values())->data_extra_html_base(data: $data, value: $value[$data]);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data extra',data: $data_ex);
        }
        return $data_ex;
    }



    /**
     * Integra extra params a options
     * @param array $data_extra Datos extra params
     * @param array $data_con_valor Datos extra params
     * @param array $value Valores a integrar
     * @return string|array
     * @version 1.509.51
     */
    public function datas_extra(array $data_con_valor, array $data_extra, array $value): string|array
    {
        $datas_extras = $this->datas_extras(data_extra: $data_extra,value: $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data extra',data: $datas_extras);
        }

        $datas_con_valor_html = (new values())->datas_con_valor(data_con_valor: $data_con_valor);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar data extra',data: $datas_con_valor_html);
        }

        return $datas_extras.' '.$datas_con_valor_html;
    }

    /**
     * Genera un conjunto de extra params
     * @param array $data_extra Dato a integrar
     * @param array $value valor a integrar
     * @return array|string
     * @version 1.507.50
     */
    private function datas_extras(array $data_extra, array $value): array|string
    {
        $data_extra_html = '';
        foreach($data_extra as $data){
            $data_ex = $this->data_extra_base(data:$data, value: $value);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar data extra',data: $data_ex);
            }
            $data_extra_html.=$data_ex;
        }
        return $data_extra_html;
    }


}

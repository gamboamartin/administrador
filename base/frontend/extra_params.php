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
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param array $value
     * @param string $data
     * @return array|string
     */
    private function data_extra_base(string $data, array $value): array|string
    {
        $data = trim($data);
        if($data === ''){
            return $this->error->error('Error al data esta vacio',$data);
        }

        if(!isset($value[$data])){
            $value[$data] = '';
        }

        $data_ex = (new values())->data_extra_html_base(data: $data, value: $value[$data]);
        if(errores::$error){
            return $this->error->error('Error al generar data extra',$data_ex);
        }
        return $data_ex;
    }

    /**
     * Genera los extra params via html
     * @param array $data_extra Conjunto de extra params para se asignados a un input
     * @return string|array
     * @version 1.254.39
     * @verfuncion 1.1.0
     * @fecha 2022-08-02 10:41
     * @author mgamboa
     */
    public function data_extra_html(array $data_extra): string|array
    {
        $data_extra_html = '';
        foreach($data_extra as $key =>$valor){
            if(is_numeric($key)){
                return $this->error->error(mensaje: 'Error el data_extra[] key debe ser texto',data: $data_extra);
            }
            $valor = trim($valor);
            if($valor === ''){
                return $this->error->error(mensaje:'Error el $valor de data extra no puede venir vacio',
                    data:$data_extra);
            }
            $data_extra_html.= 'data-'.$key." = '$valor'";
        }
        return $data_extra_html;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param array $data_extra
     * @param array $data_con_valor
     * @param array $value
     * @return string|array
     */
    public function datas_extra(array $data_con_valor, array $data_extra, array $value): string|array
    {
        $datas_extras = $this->datas_extras(data_extra: $data_extra,value: $value);
        if(errores::$error){
            return $this->error->error('Error al generar data extra',$datas_extras);
        }

        $datas_con_valor_html = (new values())->datas_con_valor(data_con_valor: $data_con_valor);
        if(errores::$error){
            return $this->error->error('Error al generar data extra',$datas_con_valor_html);
        }

        return $datas_extras.' '.$datas_con_valor_html;
    }

    /**
     * PROBADO - PARAMS ORDER PARAMS INT
     * @param array $data_extra
     * @param array $value
     * @return array|string
     */
    private function datas_extras(array $data_extra, array $value): array|string
    {
        $data_extra_html = '';
        foreach($data_extra as $data){
            $data_ex = $this->data_extra_base(data:$data, value: $value);
            if(errores::$error){
                return $this->error->error('Error al generar data extra',$data_ex);
            }
            $data_extra_html.=$data_ex;
        }
        return $data_extra_html;
    }


}

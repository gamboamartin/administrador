<?php
namespace base\orm;
use gamboamartin\errores\errores;

class monedas{

    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Elimina los carcateres para convertir el valor en un double
     * @param string|int|float|null $value Valor moneda
     * @return string|int|float|null
     */
    private function limpia_moneda_value(string|int|float|null $value): string|int|float|null
    {
        if($value === null){
            return null;
        }
        $value = trim($value);
        return str_replace(array('$', ','), '', $value);

    }

    /**
     * P INT P ORDER
     * @param string $tipo_dato
     * @param array $tipos_moneda
     * @param int|string|float|null $value
     * @return float|array|int|string|null
     */
    private function limpia_monedas_values(string $tipo_dato, array $tipos_moneda, int|string|float|null $value): float|array|int|string|null
    {
        if(in_array($tipo_dato, $tipos_moneda, true)) {
            $value = $this->limpia_moneda_value(value: $value);
            if (errores::$error) {
                return $this->error->error('Error al limpiar value', $value);
            }
        }
        return $value;
    }

    /**
     * P ORDER P INT
     * @param string $campo
     * @param modelo $modelo
     * @param array $tipos_moneda
     * @param string|int|float|null $value
     * @return float|array|int|string|null
     */
    private function reasigna_value_moneda(string $campo, modelo $modelo, array $tipos_moneda, string|int|float|null $value): float|array|int|string|null
    {
        $value_ = $value;
        if($campo === ''){
            return $this->error->error('Error campo no puede venir vacio', $campo);
        }
        if(!isset($modelo->tipo_campos[$campo])){
            return $value_;
        }
        $tipo_dato = $modelo->tipo_campos[$campo];
        $value_ = $this->limpia_monedas_values(tipo_dato: $tipo_dato,tipos_moneda:  $tipos_moneda,value:  $value_);
        if (errores::$error) {
            return $this->error->error('Error al limpiar value', $value_);
        }
        return $value_;
    }

    /**
     * P ORDER P INT
     * @param string $campo
     * @param modelo $modelo
     * @param string|float|int|null $value
     * @return float|array|int|string|null
     */
    public function value_moneda(string $campo, modelo_base $modelo, string|float|int|null $value): float|array|int|string|null
    {
        $value_= $value;
        $tipos_moneda = array('double','double_con_cero');
        if(array_key_exists($campo, $modelo->tipo_campos)){
            $value_ = $this->reasigna_value_moneda(campo: $campo, modelo: $modelo,tipos_moneda:  $tipos_moneda,value:  $value_);
            if (errores::$error) {
                return $this->error->error('Error al limpiar value', $value);
            }
        }
        return $value_;
    }

}

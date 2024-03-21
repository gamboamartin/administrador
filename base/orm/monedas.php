<?php
namespace base\orm;
use gamboamartin\errores\errores;

class monedas{

    private errores $error;
    public function __construct(){
        $this->error = new errores();
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Limpia el valor de una cantidad de moneda que puede ser de tipo string, int, float o null.
     *
     * @param string|int|float|null $value El valor de la moneda a limpiar.
     * @return string|int|float|null Valor de la moneda después de haber sido limpiado.
     * Si el valor ingresado en la función es null, también retorna null.
     *
     * Esta función está principalmente diseñada para limpiar los signos de dólar y las comas,
     * que suelen estar presentes en las cantidades de dinero.
     * La función trim se utiliza para eliminar los espacios en blanco del principio y del final.
     *
     * @version 14.42.0
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
     * POR DOCUMENTAR EN WIKI
     * Limpia los valores de diversas monedas dado un tipo de dato y un array de tipos de moneda.
     *
     * @param string $tipo_dato El tipo de dato a procesar.
     * @param array $tipos_moneda Los tipos de moneda disponibles en un array.
     * @param int|string|float|null $value El valor de la moneda a limpiar.
     * @return float|array|int|string|null Retorna el valor de la moneda después de ser limpiado.
     * Si el tipo de dato es una cadena vacía, se devuelve un error.
     * Si hay un error durante la limpieza, también se devuelve un error.
     * Esta función utiliza la función limpia_moneda_value para realizar la limpieza.
     * @version 17.43.0
     */
    private function limpia_monedas_values(string $tipo_dato, array $tipos_moneda,
                                           int|string|float|null $value): float|array|int|string|null
    {
        $tipo_dato = trim($tipo_dato);
        if($tipo_dato === ''){
            return $this->error->error(mensaje: 'Error tipo dato vacio', data: $tipo_dato);
        }
        if(in_array($tipo_dato, $tipos_moneda, true)) {
            $value = $this->limpia_moneda_value(value: $value);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar value', data: $value);
            }
        }
        return $value;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Resigna el valor de la moneda.
     *
     * @param string $campo          El nombre del campo.
     * @param modelo_base $modelo         El objeto del modelo.
     * @param array  $tipos_moneda   La lista de tipos de moneda.
     * @param mixed  $value          El valor original.
     *
     * @return float|array|int|string|null  $value_ El valor reasignado o original. Puede ser null, flotante, entero, array, o string.
     *
     * @throws errores si el campo viene vacío o si el tipo del campo no está establecido en el modelo.
     * @version 17.45.0
     */
    private function reasigna_value_moneda(string $campo, modelo_base $modelo, array $tipos_moneda,
                                           string|int|float|null $value): float|array|int|string|null
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
            return $this->error->error(mensaje: 'Error al limpiar value',data:  $value_);
        }
        return $value_;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Obtiene el valor de la moneda.
     *
     * @param string $campo           El nombre del campo.
     * @param modelo_base $modelo     El objeto del modelo.
     * @param mixed  $value           El valor original.
     *
     * @return float|array|int|string|null  $value_    El valor procesado o original. Puede ser null, float, int, string o array.
     *
     * @throws errores si hay un error al limpiar el valor.
     * @version 17.47.0
     */
    final public function value_moneda(
        string $campo, modelo_base $modelo, string|float|int|null $value): float|array|int|string|null
    {
        $value_= $value;
        $tipos_moneda = array('double','double_con_cero');
        if(array_key_exists($campo, $modelo->tipo_campos)){
            $value_ = $this->reasigna_value_moneda(
                campo: $campo, modelo: $modelo,tipos_moneda:  $tipos_moneda,value:  $value_);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al limpiar value', data: $value_);
            }
        }
        return $value_;
    }

}

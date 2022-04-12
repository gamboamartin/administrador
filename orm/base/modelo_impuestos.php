<?php
namespace orm;

use gamboamartin\errores\errores;

use gamboamartin\orm\modelo;
use stdClass;

class modelo_impuestos extends modelo {

    /**
     * PRUEBAS FINALIZADAS
     * @param string $fecha
     * @param string $monto
     * @return array
     */
    public function filtros_rango_impuestos(string $fecha, string $monto): array
    {
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return $this->error->error(MENSAJES['fecha_valida'], $valida);
        }
        if($this->tabla === ''){
            return $this->error->error("Error tabla vacia", $this->tabla);
        }
        $valida = $this->validacion->valida_name_clase($this->tabla);
        if(errores::$error){
            return $this->error->error("Error la tabla es invalida", $valida);
        }

        if((float)$monto<0.0){
            return $this->error->error("Error el monto es menor a 0", $monto);
        }

        $filtro = array();
        $filtro = $this->filtro_fecha_rango($fecha, $filtro);
        if(errores::$error){
            return $this->error->error('Error al generar filtro fecha', $filtro);
        }

        $campos = new stdClass();
        $campos->inf = 'limite_inferior';
        $campos->sup = 'limite_superior';
        $filtro = $this->filtro_monto_rango($monto, $campos, $filtro);
        if(errores::$error){
            return $this->error->error('Error al generar filtro monto', $filtro);
        }
        return $filtro;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param int $sat_nomina_periodicidad_pago_id
     * @param array $filtro
     * @return array
     */
    public function filtra_data(int $sat_nomina_periodicidad_pago_id, array $filtro): array
    {

        $valida = $this->validacion->valida_name_clase($this->tabla);
        if(errores::$error){
            return $this->error->error("Error la tabla es invalida", $valida);
        }
        if($sat_nomina_periodicidad_pago_id<=0){
            return $this->error->error("Error en ID", $sat_nomina_periodicidad_pago_id);
        }

        $filtro_base['sat_nomina_periodicidad_pago.id'] = $sat_nomina_periodicidad_pago_id;
        $resultado = $this->filtro_and($filtro_base,'numeros',$filtro);
        if(errores::$error){
            return (new errores())->error('Error al obtener datos', array($resultado, $filtro));
        }
        if((int)$resultado['n_registros'] === 0){
            return (new errores())->error('Error no existe registro con las condiciones', array($resultado, $filtro));
        }
        if((int)$resultado['n_registros'] > 1){
            return (new errores())->error('Error  existe mas de un  registro con las condiciones', $resultado);
        }
        return $resultado['registros'][0];
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param string $fecha
     * @param float $monto
     * @param int $sat_nomina_periodicidad_pago_id
     * @return array
     */
    public function obten_data_impuesto(string $fecha, float $monto, int $sat_nomina_periodicidad_pago_id): array
    {
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return (new errores())->error('Error al validar fecha', $valida);
        }
        $valida = $this->validacion->valida_name_clase($this->tabla);
        if(errores::$error){
            return $this->error->error("Error la tabla es invalida", $valida);
        }
        if((float)$monto<0.0){
            return $this->error->error("Error el monto es menor a 0", $monto);
        }
        if($sat_nomina_periodicidad_pago_id<=0){
            return $this->error->error("Error en ID", $sat_nomina_periodicidad_pago_id);
        }

        $monto = round($monto,2);

        $filtro = $this->filtros_rango_impuestos($fecha, $monto);
        if(errores::$error){
            return $this->error->error('Error al generar filtro monto', $filtro);
        }

        $data = $this->filtra_data($sat_nomina_periodicidad_pago_id, $filtro);
        if(errores::$error){
            return $this->error->error('Error al obtener datos', $data);
        }
        return $data;
    }

}

<?php
namespace base\orm;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;



class data_format{

    public errores $error;
    public validacion $validacion;

    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     *  P INT P ORDER
     * @param array $registro
     * @param array $tipo_campos
     * @return array
     */
    public function ajusta_campos_moneda(array $registro, array $tipo_campos): array
    {
        foreach($tipo_campos as $campo =>$tipo_dato){
            $registro = $this->asignacion_campo_moneda(campo: $campo, registro: $registro,tipo_dato:  $tipo_dato);
            if(errores::$error){
                return $this->error->error('Error al asignar campo ', $registro);
            }
        }
        return $registro;
    }

    /**
     * P INT P ORDER PROBADO
     * @param string $campo
     * @param array $registro
     * @return array
     */
    private function asigna_campo_moneda(string $campo, array $registro): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error el campo esta vacio', $campo);
        }
        if(!isset($registro[$campo])){
            return $this->error->error('Error $registro['.$campo.'] no existe', $registro);
        }
        $registro[$campo] = str_replace('$', '', $registro[$campo]);
        $registro[$campo] = str_replace(',', '', $registro[$campo]);
        return $registro;
    }

    /**
     * P INT P ORDER PROBADO
     * @param string $campo
     * @param array $registro
     * @param string $tipo_dato
     * @return array
     */
    private function asignacion_campo_moneda(string $campo, array $registro, string $tipo_dato): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error('Error el campo esta vacio', $campo);
        }
        $tipo_dato = trim($tipo_dato);
        if($tipo_dato === ''){
            return $this->error->error('Error el tipo_dato esta vacio', $tipo_dato);
        }
        if(isset($registro[$campo]) && ($tipo_dato === 'double' || $tipo_dato === 'moneda')){
            $registro = $this->asigna_campo_moneda(campo: $campo, registro: $registro);
            if(errores::$error){
                return $this->error->error('Error al asignar campo ', $registro);
            }
        }
        return $registro;
    }


}
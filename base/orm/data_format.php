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
     * Funcion ajusta los campos en formato moneda previo a la insersion, los limpia y los integra como double
     * @param array $registro Registro donde se ajustaran los valores
     * @param array $tipo_campos Son los tipos de campos declarados en el modelo
     * @fecha 2022-07-30 13:26
     * @return array
     */
    final public function ajusta_campos_moneda(array $registro, array $tipo_campos): array
    {
        foreach($tipo_campos as $campo =>$tipo_dato){
            $campo = trim($campo);
            if($campo === ''){
                return $this->error->error(mensaje: 'Error el campo esta vacio',data:  $campo);
            }
            if(!is_string($tipo_dato)){
                $fix = 'modelo->tipo_campos debe llevar esta forma $modelo->tipo_campos[campo] = regex 
                donde el regex debe existir en el paquete de validaciones en validacion->patterns';
                return $this->error->error(mensaje: 'Error el tipo_dato debe ser un string', data: $tipo_dato, fix: $fix);
            }

            $tipo_dato = trim($tipo_dato);
            if($tipo_dato === ''){
                $fix = 'modelo->tipo_campos debe llevar esta forma $modelo->tipo_campos[campo] = regex 
                donde el regex debe existir en el paquete de validaciones en validacion->patterns';
                return $this->error->error(mensaje: 'Error el tipo_dato esta vacio', data: $tipo_dato, fix: $fix);
            }

            $registro = $this->asignacion_campo_moneda(campo: $campo, registro: $registro,tipo_dato:  $tipo_dato);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar campo ',data:  $registro);
            }
        }
        return $registro;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función asigna un formato a un campo de moneda en un registro.
     * Remueve el caracter de moneda($) y las comas que son comúnmente usadas
     * en formatos de moneda.
     *
     * @param string $campo El nombre del campo que se va a formatear.
     * @param array $registro El registro que contiene el campo a formatear.
     *
     * @return array Retorna el registro con el campo de moneda formateado.
     * @throws errores Se lanza si el campo está vacío o si el campo no existe en el registro.
     * @version 15.9.0
     */
    private function asigna_campo_moneda(string $campo, array $registro): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio', data: $campo);
        }
        if(!isset($registro[$campo])){
            return $this->error->error(mensaje: 'Error $registro['.$campo.'] no existe',data:  $registro);
        }
        $registro[$campo] = str_replace('$', '', $registro[$campo]);
        $registro[$campo] = str_replace(',', '', $registro[$campo]);
        return $registro;
    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Este método se utiliza para asignar un valor de tipo moneda a un campo especificado en un registro.
     *
     * @param string $campo Es el nombre del campo al que se le asignará el valor.
     * @param array $registro Es el registro donde se encuentra el campo a asignar.
     * @param string $tipo_dato Es el tipo de dato que se asignará. Debe ser 'double' o 'moneda'.
     *
     * @return array Retorna el registro con el campo asignado. Si se encuentra un error, se retorna información detallada del error.
     *
     * @throws errores Si el campo o el tipo de dato están vacíos o no existen, se lanza una excepción.
     * @version 16.222.0
     */
    private function asignacion_campo_moneda(string $campo, array $registro, string $tipo_dato): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error el campo esta vacio',data:  $campo);
        }


        $tipo_dato = trim($tipo_dato);
        if($tipo_dato === ''){
            $fix = 'modelo->tipo_campos debe llevar esta forma $modelo->tipo_campos[campo] = regex 
                donde el regex debe existir en el paquete de validaciones en validacion->patterns';
            return $this->error->error(mensaje: 'Error el tipo_dato esta vacio', data: $tipo_dato, fix: $fix);
        }
        if(isset($registro[$campo]) && ($tipo_dato === 'double' || $tipo_dato === 'moneda')){
            $registro = $this->asigna_campo_moneda(campo: $campo, registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al asignar campo ',data:  $registro);
            }
        }
        return $registro;
    }


}
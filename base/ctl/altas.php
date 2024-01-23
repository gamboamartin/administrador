<?php
namespace gamboamartin\administrador\ctl;
use base\controller\controler;
use gamboamartin\errores\errores;
use JetBrains\PhpStorm\Pure;
use stdClass;

class altas{
    private errores $error;
    #[Pure] public function __construct(){
        $this->error = new errores();
    }

    /**
     *
     * Función que sube los registros a base de datos después de que los registros
     * fueron asignados y validados.
     * @param array $registro Registro que se insertara
     * @param controler $controler Controlador de ejecucion
     * @return array|stdClass
     */
    final public function alta_base(array $registro, controler $controler): array|stdClass{

        $controler->seccion = trim($controler->seccion);
        if($controler->seccion === ''){
            return $this->error->error(
                mensaje: 'Error $controler->seccion no puede venir vacia',data:  $controler->seccion);
        }

        $registro_r = (new normalizacion_ctl())->asigna_registro_alta(controler: $controler,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar registro',data:  $registro_r);
        }

        $resultado = $controler->modelo->alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta registros', data: $resultado);
        }

        return $resultado;
    }




}

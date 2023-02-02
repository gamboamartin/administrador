<?php
namespace base\controller;

use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JetBrains\PhpStorm\Pure;


class not_in{
    private errores $error;
    private validacion $validacion;
    #[Pure] public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    final public function integra_not_in_post(): array
    {
        $not_in = array();
        if(isset($_POST['not_in'])){
            /**
             * llave = string tabla.campo
             * values = array(n1,n2,n3,nn)
             * @example $_POST[llave] = 'adm_seccion.id'
             * @example $_POST[values] = array(1,2,3);
             */

            $keys = array('not_in');
            $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $_POST);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar not in',data:  $valida);
            }

            $not_in = $this->init_not_in(not_in: $not_in);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar not in', data: $not_in);
            }

        }
        return $not_in;
    }

    private function init_not_in(array $not_in): array
    {
        if(isset ($_POST['not_in']['values'])) {
            if (count($_POST['not_in']['values']) > 0) {
                $not_in = $this->not_in_post();
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al integrar not in', data: $not_in);
                }
            }
        }
        return $not_in;
    }



    /**
     * Maqueta un not in obtenido por POST
     * @return array
     * @version 1.600.54
     *
     * llave = string tabla.campo
     * values = array(n1,n2,n3,nn)
     * @example $_POST[llave] = 'adm_seccion.id'
     * @example $_POST[values] = array(1,2,3);
     */
    private function not_in_post(): array
    {

        $keys = array('not_in');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $_POST);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar not in',data:  $valida);
        }

        $keys = array('llave');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $_POST['not_in']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar not in',data:  $valida);
        }

        if(!is_array($_POST['not_in']['values'])){
            return $this->error->error(mensaje: 'Error POST[not_in][values] debe ser un array',data:  $_POST);
        }
        if(count($_POST['not_in']['values']) === 0){
            return $this->error->error(mensaje: 'Error POST[not_in][values] esta vacio',data:  $_POST);
        }

        $not_in['llave'] = $_POST['not_in']['llave'];
        $not_in['values'] = $_POST['not_in']['values'];

        return $not_in;
    }



}

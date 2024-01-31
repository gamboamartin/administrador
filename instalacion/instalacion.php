<?php
namespace gamboamartin\administrador\instalacion;

use gamboamartin\errores\errores;
use PDO;
use stdClass;

class instalacion
{
    private function adm_accion_basica(PDO $link): array|stdClass
    {
        $out = new stdClass();



        //$out->altas = $altas;

        return $out;



    }
    final public function instala(PDO $link): array|stdClass
    {

        $out = new stdClass();


        $adm_accion_basica = $this->adm_accion_basica(link: $link);
        if (errores::$error) {
            return (new errores())->error(mensaje: 'Error al init adm_accion_basica', data: $adm_accion_basica);
        }
        $out->adm_accion_basica = $adm_accion_basica;


        return $out;

    }

}

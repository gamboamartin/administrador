<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\administrador\models\adm_accion;


use gamboamartin\errores\errores;
use PDO;
use stdClass;

class controlador_adm_accion extends controlador_base
{

    /**
     * @param PDO $link Conexion a la base de datos
     */
    public function __construct(PDO $link)
    {
        $modelo = new adm_accion($link);
        parent::__construct($link, $modelo);
        // $this->directiva = new html_accion();
    }

    final public function get_adm_accion(bool $header, bool $ws = true): array|stdClass
    {

        $keys['adm_menu'] = array('id','descripcion','codigo','codigo_bis');
        $keys['adm_seccion'] = array('id','descripcion','codigo','codigo_bis');
        $keys['adm_accion'] = array('id','descripcion','codigo','codigo_bis');

        $salida = $this->get_out(header: $header,keys: $keys, ws: $ws);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar salida',data:  $salida,header: $header,ws: $ws);

        }


        return $salida;


    }
}
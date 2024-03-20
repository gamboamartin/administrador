<?php
namespace gamboamartin\administrador\instalacion;

use gamboamartin\administrador\models\_instalacion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class instalacion
{

    PUBLIC function _add_adm_reporte(PDO $link): array|stdClass
    {
        $out = new stdClass();
        $create = (new _instalacion(link: $link))->create_table_new(table: 'adm_reporte');
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al create table', data:  $create);
        }
        $out->create = $create;


        return $out;

    }

    private function adm_accion(PDO $link): array|stdClass
    {

        $adm_acciones = (new adm_accion(link: $link))->registros(columnas_en_bruto: true);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener acciones', data:  $adm_acciones);
        }
        $upds = array();
        foreach ($adm_acciones as $adm_accion){
            $upd = array();
            if($adm_accion['es_view'] === 'false'){
                $upd['es_view'] = 'inactivo';
            }
            if($adm_accion['descripcion'] === 'lista'){
                if($adm_accion['visible'] === 'inactivo') {
                    $upd['visible'] = 'activo';
                }
            }

            if(count($upd) >0) {
                $r_upd = (new adm_accion(link: $link))->modifica_bd_base(registro: $upd, id: $adm_accion['id']);
                if (errores::$error) {
                    return (new errores())->error(mensaje: 'Error al actualizar accion', data: $r_upd);
                }
                $upds[] = $r_upd;
            }
        }

        return $upds;

    }
    private function adm_accion_basica(PDO $link): array|stdClass
    {
        $out = new stdClass();

        $adm_acciones_basicas = array();
        $adm_acciones_basicas[0]['descripcion'] = 'get_data_descripcion';
        $adm_acciones_basicas[0]['visible'] = 'inactivo';
        $adm_acciones_basicas[0]['seguridad'] = 'activo';
        $adm_acciones_basicas[0]['inicio'] = 'inactivo';
        $adm_acciones_basicas[0]['lista'] = 'inactivo';
        $adm_acciones_basicas[0]['status'] = 'activo';
        $adm_acciones_basicas[0]['es_view'] = 'inactivo';
        $adm_acciones_basicas[0]['codigo'] = 'get_data_descripcion';
        $adm_acciones_basicas[0]['codigo_bis'] = 'get_data_descripcion';
        $adm_acciones_basicas[0]['descripcion_select'] = 'get_data_descripcion';
        $adm_acciones_basicas[0]['etiqueta_label'] = 'get_data_descripcion';
        $adm_acciones_basicas[0]['es_modal'] = 'inactivo';
        $adm_acciones_basicas[0]['titulo'] = 'get_data_descripcion';
        $adm_acciones_basicas[0]['css'] = 'info';
        $adm_acciones_basicas[0]['es_status'] = 'inactivo';
        $adm_acciones_basicas[0]['alias'] = 'get_data_descripcion';
        $adm_acciones_basicas[0]['es_lista'] = 'inactivo';
        $adm_acciones_basicas[0]['muestra_icono_btn'] = 'inactivo';
        $adm_acciones_basicas[0]['muestra_titulo_btn'] = 'inactivo';

        $adm_acciones_basicas[1]['descripcion'] = 'importa';
        $adm_acciones_basicas[1]['visible'] = 'inactivo';
        $adm_acciones_basicas[1]['seguridad'] = 'activo';
        $adm_acciones_basicas[1]['inicio'] = 'inactivo';
        $adm_acciones_basicas[1]['lista'] = 'inactivo';
        $adm_acciones_basicas[1]['status'] = 'activo';
        $adm_acciones_basicas[1]['es_view'] = 'activo';
        $adm_acciones_basicas[1]['codigo'] = 'importa';
        $adm_acciones_basicas[1]['codigo_bis'] = 'importa';
        $adm_acciones_basicas[1]['descripcion_select'] = 'importa';
        $adm_acciones_basicas[1]['etiqueta_label'] = 'Importa';
        $adm_acciones_basicas[1]['es_modal'] = 'inactivo';
        $adm_acciones_basicas[1]['titulo'] = 'Importa';
        $adm_acciones_basicas[1]['css'] = 'info';
        $adm_acciones_basicas[1]['es_status'] = 'inactivo';
        $adm_acciones_basicas[1]['alias'] = 'importa';
        $adm_acciones_basicas[1]['es_lista'] = 'inactivo';
        $adm_acciones_basicas[1]['muestra_icono_btn'] = 'inactivo';
        $adm_acciones_basicas[1]['muestra_titulo_btn'] = 'inactivo';

        $altas = array();
        foreach ($adm_acciones_basicas as $adm_accion_basica){
            $con_descripcion['adm_accion_basica.descripcion'] = $adm_accion_basica['descripcion'];
            $alta = (new adm_accion_basica(link: $link))->inserta_registro_si_no_existe(registro: $adm_accion_basica,
                con_descripcion: $con_descripcion);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al insertar accion_basica',data:  $alta);
            }
            $altas[] = $alta;
        }

        $out->altas = $altas;

        return $out;



    }


    private function adm_reporte(PDO $link): array|stdClass
    {
        $create = $this->_add_adm_reporte(link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar create', data:  $create);
        }

        return $create;

    }
    final public function instala(PDO $link): array|stdClass
    {

        $out = new stdClass();


        $adm_accion_basica = $this->adm_accion_basica(link: $link);
        if (errores::$error) {
            return (new errores())->error(mensaje: 'Error al init adm_accion_basica', data: $adm_accion_basica);
        }
        $out->adm_accion_basica = $adm_accion_basica;

        $adm_accion = $this->adm_accion(link: $link);
        if (errores::$error) {
            return (new errores())->error(mensaje: 'Error al init adm_accion', data: $adm_accion);
        }
        $out->adm_accion = $adm_accion;

        $adm_reporte = $this->adm_reporte(link: $link);
        if (errores::$error) {
            return (new errores())->error(mensaje: 'Error al init adm_reporte', data: $adm_reporte);
        }
        $out->adm_reporte = $adm_reporte;


        return $out;

    }

}

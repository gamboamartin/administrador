<?php
namespace gamboamartin\administrador\instalacion;

use gamboamartin\administrador\models\_instalacion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_tipo_dato;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class instalacion
{

    private function _add_adm_campo(PDO $link): array|stdClass
    {
        $out = new stdClass();
        $create = (new _instalacion(link: $link))->create_table_new(table: 'adm_campo');
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al create table', data:  $create);
        }
        $out->create = $create;

        $foraneas = array();
        $foraneas['adm_tipo_dato_id'] = new stdClass();
        $foraneas['adm_seccion_id'] = new stdClass();

        $result = (new _instalacion(link: $link))->foraneas(foraneas: $foraneas,table:  'adm_campo');

        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar foranea', data:  $result);
        }

        $campos = new stdClass();
        $campos->codigo = new stdClass();
        $campos->sub_consulta = new stdClass();
        $campos->sub_consulta->tipo_dato = 'TEXT';

        $campos->descripcion_select = new stdClass();
        $campos->descripcion_select->default = 'SIN DS';

        $campos->codigo_bis = new stdClass();
        $campos->predeterminado = new stdClass();
        $campos->predeterminado->default = 'inactivo';

        $campos->es_foranea = new stdClass();
        $campos->es_foranea->default = 'inactivo';

        $result = (new _instalacion(link: $link))->add_columns(campos: $campos,table:  'adm_campo');

        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar campos', data:  $result);
        }


        return $out;

    }

    private function _add_adm_menu(PDO $link): array|stdClass
    {
        $out = new stdClass();
        $create = (new _instalacion(link: $link))->create_table_new(table: 'adm_menu');
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al create table', data:  $create);
        }
        $out->create = $create;


        $campos = new stdClass();
        $campos->codigo = new stdClass();


        $campos->descripcion_select = new stdClass();
        $campos->descripcion_select->default = 'SIN DS';

        $campos->codigo_bis = new stdClass();
        $campos->predeterminado = new stdClass();
        $campos->predeterminado->default = 'inactivo';

        $campos->etiqueta_label = new stdClass();
        $campos->etiqueta_label->default = 'SE';

        $campos->icono = new stdClass();
        $campos->icono->default = 'SI';

        $campos->titulo = new stdClass();
        $campos->titulo->default = 'ST';



        $result = (new _instalacion(link: $link))->add_columns(campos: $campos,table:  'adm_menu');

        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar campos', data:  $result);
        }


        return $out;

    }

    private function _add_adm_namespace(PDO $link): array|stdClass
    {
        $out = new stdClass();
        $create = (new _instalacion(link: $link))->create_table_new(table: 'adm_namespace');
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al create table', data:  $create);
        }
        $out->create = $create;


        $campos = new stdClass();
        $campos->codigo = new stdClass();


        $campos->descripcion_select = new stdClass();
        $campos->descripcion_select->default = 'SIN DS';

        $campos->codigo_bis = new stdClass();
        $campos->predeterminado = new stdClass();
        $campos->predeterminado->default = 'inactivo';

        $campos->etiqueta_label = new stdClass();
        $campos->etiqueta_label->default = 'SE';

        $campos->name = new stdClass();
        $campos->name->default = 'SN';


        $result = (new _instalacion(link: $link))->add_columns(campos: $campos,table:  'adm_namespace');

        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar campos', data:  $result);
        }


        return $out;

    }

    private function _add_adm_reporte(PDO $link): array|stdClass
    {
        $out = new stdClass();
        $create = (new _instalacion(link: $link))->create_table_new(table: 'adm_reporte');
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al create table', data:  $create);
        }
        $out->create = $create;


        return $out;

    }

    private function _add_adm_seccion(PDO $link): array|stdClass
    {
        $out = new stdClass();
        $create = (new _instalacion(link: $link))->create_table_new(table: 'adm_seccion');
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al create table', data:  $create);
        }
        $out->create = $create;

        $foraneas = array();
        $foraneas['adm_menu_id'] = new stdClass();
        $foraneas['adm_namespace_id'] = new stdClass();

        $foraneas_r = (new _instalacion(link:$link))->foraneas(foraneas: $foraneas,table:  'adm_seccion');

        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar foranea', data:  $foraneas_r);
        }
        $out->foraneas_r = $foraneas_r;

        $campos = new stdClass();
        $campos->etiqueta_label = new stdClass();
        $campos->etiqueta_label->default = 'SIN ETIQUETA';

        $campos->icono = new stdClass();
        $campos->icono->default = 'SIN ICONO';

        $result = (new _instalacion(link: $link))->add_columns(campos: $campos,table:  'adm_seccion');

        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar campos', data:  $result);
        }
        $out->columnas = $result;


        return $out;

    }

    /**
     * POR DOCUMENTAR EN WIKI
     * Esta función agrega la entidad tipo de dato en el administrador.
     *
     * @param PDO $link Conexión a la base de datos.
     *
     * @return stdClass|array Devuelve un objeto con los resultados de la operación.
     * Si hay un error, devuelve un array con los detalles del error.
     *
     * @version 17.39.0
     */
    private function _add_adm_tipo_dato(PDO $link): array|stdClass
    {
        $out = new stdClass();
        $create = (new _instalacion(link: $link))->create_table_new(table: 'adm_tipo_dato');
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al create table', data:  $create);
        }
        $out->create = $create;

        $campos = new stdClass();
        $campos->codigo = new stdClass();
        $campos->descripcion_select = new stdClass();
        $campos->descripcion_select->default = 'SIN DS';

        $campos->codigo_bis = new stdClass();
        $campos->predeterminado = new stdClass();
        $campos->predeterminado->default = 'inactivo';

        $result = (new _instalacion(link: $link))->add_columns(campos: $campos,table:  'adm_tipo_dato');

        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar campos', data:  $result);
        }


        return $out;

    }

    private function accion_basica_importa(string $accion_basica_descripcion, PDO $link)
    {
        $accion_basica_importa = (new adm_accion_basica(link: $link))->accion_basica(descripcion:$accion_basica_descripcion);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener accion_basica_importa',
                data:  $accion_basica_importa);
        }
        unset($accion_basica_importa['id']);
        unset($accion_basica_importa['usuario_alta_id']);
        unset($accion_basica_importa['usuario_update_id']);

        return $accion_basica_importa;

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

        $adm_acciones_basicas[2]['descripcion'] = 'importa_previo';
        $adm_acciones_basicas[2]['visible'] = 'inactivo';
        $adm_acciones_basicas[2]['seguridad'] = 'activo';
        $adm_acciones_basicas[2]['inicio'] = 'inactivo';
        $adm_acciones_basicas[2]['lista'] = 'inactivo';
        $adm_acciones_basicas[2]['status'] = 'activo';
        $adm_acciones_basicas[2]['es_view'] = 'activo';
        $adm_acciones_basicas[2]['codigo'] = 'importa_previo';
        $adm_acciones_basicas[2]['codigo_bis'] = 'importa_previo';
        $adm_acciones_basicas[2]['descripcion_select'] = 'importa_previo';
        $adm_acciones_basicas[2]['etiqueta_label'] = 'importa_previo';
        $adm_acciones_basicas[2]['es_modal'] = 'inactivo';
        $adm_acciones_basicas[2]['titulo'] = 'importa_previo';
        $adm_acciones_basicas[2]['css'] = 'info';
        $adm_acciones_basicas[2]['es_status'] = 'inactivo';
        $adm_acciones_basicas[2]['alias'] = 'importa_previo';
        $adm_acciones_basicas[2]['es_lista'] = 'inactivo';
        $adm_acciones_basicas[2]['muestra_icono_btn'] = 'inactivo';
        $adm_acciones_basicas[2]['muestra_titulo_btn'] = 'inactivo';


        $adm_acciones_basicas[3]['descripcion'] = 'importa_previo_muestra';
        $adm_acciones_basicas[3]['visible'] = 'inactivo';
        $adm_acciones_basicas[3]['seguridad'] = 'activo';
        $adm_acciones_basicas[3]['inicio'] = 'inactivo';
        $adm_acciones_basicas[3]['lista'] = 'inactivo';
        $adm_acciones_basicas[3]['status'] = 'activo';
        $adm_acciones_basicas[3]['es_view'] = 'activo';
        $adm_acciones_basicas[3]['codigo'] = 'importa_previo_muestra';
        $adm_acciones_basicas[3]['codigo_bis'] = 'importa_previo_muestra';
        $adm_acciones_basicas[3]['descripcion_select'] = 'importa_previo_muestra';
        $adm_acciones_basicas[3]['etiqueta_label'] = 'importa_previo_muestra';
        $adm_acciones_basicas[3]['es_modal'] = 'inactivo';
        $adm_acciones_basicas[3]['titulo'] = 'importa_previo_muestra';
        $adm_acciones_basicas[3]['css'] = 'info';
        $adm_acciones_basicas[3]['es_status'] = 'inactivo';
        $adm_acciones_basicas[3]['alias'] = 'importa_previo_muestra';
        $adm_acciones_basicas[3]['es_lista'] = 'inactivo';
        $adm_acciones_basicas[3]['muestra_icono_btn'] = 'inactivo';
        $adm_acciones_basicas[3]['muestra_titulo_btn'] = 'inactivo';


        $adm_acciones_basicas[4]['descripcion'] = 'importa_previo_muestra_bd';
        $adm_acciones_basicas[4]['visible'] = 'inactivo';
        $adm_acciones_basicas[4]['seguridad'] = 'activo';
        $adm_acciones_basicas[4]['inicio'] = 'inactivo';
        $adm_acciones_basicas[4]['lista'] = 'inactivo';
        $adm_acciones_basicas[4]['status'] = 'activo';
        $adm_acciones_basicas[4]['es_view'] = 'inactivo';
        $adm_acciones_basicas[4]['codigo'] = 'importa_previo_muestra_bd';
        $adm_acciones_basicas[4]['codigo_bis'] = 'importa_previo_muestra_bd';
        $adm_acciones_basicas[4]['descripcion_select'] = 'importa_previo_muestra_bd';
        $adm_acciones_basicas[4]['etiqueta_label'] = 'importa_previo_muestra_bd';
        $adm_acciones_basicas[4]['es_modal'] = 'inactivo';
        $adm_acciones_basicas[4]['titulo'] = 'importa_previo_muestra_bd';
        $adm_acciones_basicas[4]['css'] = 'info';
        $adm_acciones_basicas[4]['es_status'] = 'inactivo';
        $adm_acciones_basicas[4]['alias'] = 'importa_previo_muestra_bd';
        $adm_acciones_basicas[4]['es_lista'] = 'inactivo';
        $adm_acciones_basicas[4]['muestra_icono_btn'] = 'inactivo';
        $adm_acciones_basicas[4]['muestra_titulo_btn'] = 'inactivo';


        $adm_acciones_basicas[5]['descripcion'] = 'importa_result';
        $adm_acciones_basicas[5]['visible'] = 'inactivo';
        $adm_acciones_basicas[5]['seguridad'] = 'activo';
        $adm_acciones_basicas[5]['inicio'] = 'inactivo';
        $adm_acciones_basicas[5]['lista'] = 'inactivo';
        $adm_acciones_basicas[5]['status'] = 'activo';
        $adm_acciones_basicas[5]['es_view'] = 'activo';
        $adm_acciones_basicas[5]['codigo'] = 'importa_result';
        $adm_acciones_basicas[5]['codigo_bis'] = 'importa_result';
        $adm_acciones_basicas[5]['descripcion_select'] = 'importa_result';
        $adm_acciones_basicas[5]['etiqueta_label'] = 'importa_result';
        $adm_acciones_basicas[5]['es_modal'] = 'inactivo';
        $adm_acciones_basicas[5]['titulo'] = 'importa_result';
        $adm_acciones_basicas[5]['css'] = 'info';
        $adm_acciones_basicas[5]['es_status'] = 'inactivo';
        $adm_acciones_basicas[5]['alias'] = 'importa_result';
        $adm_acciones_basicas[5]['es_lista'] = 'inactivo';
        $adm_acciones_basicas[5]['muestra_icono_btn'] = 'inactivo';
        $adm_acciones_basicas[5]['muestra_titulo_btn'] = 'inactivo';


        $adm_acciones_basicas[6]['descripcion'] = 'descarga_layout';
        $adm_acciones_basicas[6]['visible'] = 'inactivo';
        $adm_acciones_basicas[6]['seguridad'] = 'activo';
        $adm_acciones_basicas[6]['inicio'] = 'inactivo';
        $adm_acciones_basicas[6]['lista'] = 'inactivo';
        $adm_acciones_basicas[6]['status'] = 'activo';
        $adm_acciones_basicas[6]['es_view'] = 'inactivo';
        $adm_acciones_basicas[6]['codigo'] = 'descarga_layout';
        $adm_acciones_basicas[6]['codigo_bis'] = 'descarga_layout';
        $adm_acciones_basicas[6]['descripcion_select'] = 'descarga_layout';
        $adm_acciones_basicas[6]['etiqueta_label'] = 'descarga_layout';
        $adm_acciones_basicas[6]['es_modal'] = 'inactivo';
        $adm_acciones_basicas[6]['titulo'] = 'descarga_layout';
        $adm_acciones_basicas[6]['css'] = 'info';
        $adm_acciones_basicas[6]['es_status'] = 'inactivo';
        $adm_acciones_basicas[6]['alias'] = 'descarga_layout';
        $adm_acciones_basicas[6]['es_lista'] = 'inactivo';
        $adm_acciones_basicas[6]['muestra_icono_btn'] = 'inactivo';
        $adm_acciones_basicas[6]['muestra_titulo_btn'] = 'inactivo';

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

    private function adm_campo(PDO $link): array|stdClass
    {
        $create = $this->_add_adm_campo(link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar create', data:  $create);
        }


        return $create;

    }

    private function adm_menu(PDO $link): array|stdClass
    {
        $create = $this->_add_adm_menu(link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar create', data:  $create);
        }


        $adm_menu_descripcion = 'ACL';
        $adm_sistema_descripcion = 'administrador';
        $etiqueta_label = 'Menus';
        $adm_seccion_pertenece_descripcion = 'administrador';
        $adm_namespace_descripcion = 'gamboa.martin/administrador';
        $adm_namespace_name = 'gamboamartin/administrador';

        $acl = (new _adm())->integra_acl(adm_menu_descripcion: $adm_menu_descripcion,
            adm_namespace_name: $adm_namespace_name, adm_namespace_descripcion: $adm_namespace_descripcion,
            adm_seccion_descripcion: __FUNCTION__, adm_seccion_pertenece_descripcion: $adm_seccion_pertenece_descripcion,
            adm_sistema_descripcion: $adm_sistema_descripcion, etiqueta_label: $etiqueta_label, link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener acl', data:  $acl);
        }


        return $create;

    }

    private function adm_namespace(PDO $link): array|stdClass
    {
        $create = $this->_add_adm_namespace(link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar create', data:  $create);
        }


        $adm_menu_descripcion = 'ACL';
        $adm_sistema_descripcion = 'administrador';
        $etiqueta_label = 'Namespaces';
        $adm_seccion_pertenece_descripcion = 'administrador';
        $adm_namespace_descripcion = 'gamboa.martin/administrador';
        $adm_namespace_name = 'gamboamartin/administrador';

        $acl = (new _adm())->integra_acl(adm_menu_descripcion: $adm_menu_descripcion,
            adm_namespace_name: $adm_namespace_name, adm_namespace_descripcion: $adm_namespace_descripcion,
            adm_seccion_descripcion: __FUNCTION__, adm_seccion_pertenece_descripcion: $adm_seccion_pertenece_descripcion,
            adm_sistema_descripcion: $adm_sistema_descripcion, etiqueta_label: $etiqueta_label, link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener acl', data:  $acl);
        }


        return $create;

    }


    private function adm_reporte(PDO $link): array|stdClass
    {
        $create = $this->_add_adm_reporte(link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar create', data:  $create);
        }

        return $create;

    }

    private function adm_seccion(PDO $link): array|stdClass
    {
        $create = $this->_add_adm_seccion(link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar create', data:  $create);
        }

        $adm_secciones = (new adm_seccion(link: $link))->registros();
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener adm_secciones', data:  $adm_secciones);
        }


        $r_acciones = $this->integra_accion_basica(accion_basica_descripcion: 'importa', adm_secciones: $adm_secciones,link:  $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar accion', data:  $r_acciones);
        }
        $r_acciones = $this->integra_accion_basica(accion_basica_descripcion: 'importa_previo', adm_secciones: $adm_secciones,link:  $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar accion', data:  $r_acciones);
        }
        $r_acciones = $this->integra_accion_basica(accion_basica_descripcion: 'importa_previo_muestra', adm_secciones: $adm_secciones,link:  $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar accion', data:  $r_acciones);
        }
        $r_acciones = $this->integra_accion_basica(accion_basica_descripcion: 'importa_previo_muestra_bd', adm_secciones: $adm_secciones,link:  $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar accion', data:  $r_acciones);
        }
        $r_acciones = $this->integra_accion_basica(accion_basica_descripcion: 'importa_result', adm_secciones: $adm_secciones,link:  $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar accion', data:  $r_acciones);
        }

        $r_acciones = $this->integra_accion_basica(accion_basica_descripcion: 'descarga_layout', adm_secciones: $adm_secciones,link:  $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar accion', data:  $r_acciones);
        }


        $adm_menu_descripcion = 'ACL';
        $adm_sistema_descripcion = 'administrador';
        $etiqueta_label = 'Secciones';
        $adm_seccion_pertenece_descripcion = 'administrador';
        $adm_namespace_descripcion = 'gamboa.martin/administrador';
        $adm_namespace_name = 'gamboamartin/administrador';

        $acl = (new _adm())->integra_acl(adm_menu_descripcion: $adm_menu_descripcion,
            adm_namespace_name: $adm_namespace_name, adm_namespace_descripcion: $adm_namespace_descripcion,
            adm_seccion_descripcion: __FUNCTION__, adm_seccion_pertenece_descripcion: $adm_seccion_pertenece_descripcion,
            adm_sistema_descripcion: $adm_sistema_descripcion, etiqueta_label: $etiqueta_label, link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener acl', data:  $acl);
        }


        $inserta_campos = (new _instalacion(link: $link))->inserta_adm_campos(
            modelo_integracion: (new adm_seccion(link: $link)));
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar adm campos', data:  $inserta_campos);
        }



        return $create;

    }

    private function adm_tipo_dato(PDO $link): array|stdClass
    {
        $create = $this->_add_adm_tipo_dato(link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al ajustar create', data:  $create);
        }

        $adm_tipo_dato_modelo = new adm_tipo_dato(link: $link);

        $adm_tipos_datos = array();

        $adm_tipo_dato['id'] = 1;
        $adm_tipo_dato['descripcion'] = 'INT';
        $adm_tipo_dato['codigo'] = 'INT';

        $adm_tipos_datos[] = $adm_tipo_dato;

        $adm_tipo_dato['id'] = 2;
        $adm_tipo_dato['descripcion'] = 'BIGINT';
        $adm_tipo_dato['codigo'] = 'BIGINT';

        $adm_tipos_datos[] = $adm_tipo_dato;

        $adm_tipo_dato['id'] = 3;
        $adm_tipo_dato['descripcion'] = 'VARCHAR';
        $adm_tipo_dato['codigo'] = 'VARCHAR';

        $adm_tipos_datos[] = $adm_tipo_dato;

        $adm_tipo_dato['id'] = 4;
        $adm_tipo_dato['descripcion'] = 'TEXT';
        $adm_tipo_dato['codigo'] = 'TEXT';

        $adm_tipos_datos[] = $adm_tipo_dato;

        $adm_tipo_dato['id'] = 5;
        $adm_tipo_dato['descripcion'] = 'TIMESTAMP';
        $adm_tipo_dato['codigo'] = 'TIMESTAMP';


        $adm_tipos_datos[] = $adm_tipo_dato;

        $adm_tipo_dato['id'] = 6;
        $adm_tipo_dato['descripcion'] = 'DOUBLE';
        $adm_tipo_dato['codigo'] = 'DOUBLE';


        $adm_tipos_datos[] = $adm_tipo_dato;

        $adm_tipo_dato['id'] = 7;
        $adm_tipo_dato['descripcion'] = 'FLOAT';
        $adm_tipo_dato['codigo'] = 'FLOAT';


        $adm_tipos_datos[] = $adm_tipo_dato;

        $adm_tipo_dato['id'] = 8;
        $adm_tipo_dato['descripcion'] = 'DATE';
        $adm_tipo_dato['codigo'] = 'DATE';


        $adm_tipos_datos[] = $adm_tipo_dato;

        $adm_tipo_dato['id'] = 9;
        $adm_tipo_dato['descripcion'] = 'DATETIME';
        $adm_tipo_dato['codigo'] = 'DATETIME';


        $adm_tipos_datos[] = $adm_tipo_dato;


        foreach ($adm_tipos_datos as $adm_tipo_dato){
            $inserta = $adm_tipo_dato_modelo->inserta_registro_si_no_existe(registro: $adm_tipo_dato);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al insertar adm_tipo_dato', data:  $inserta);
            }
        }

        foreach ($adm_tipos_datos as $adm_tipo_dato){
            $existe = $adm_tipo_dato_modelo->existe_by_id(registro_id: $adm_tipo_dato['id']);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al verificar si existe', data:  $existe);
            }
            if($existe){
                $filtro = array();
                $filtro['adm_tipo_dato.id'] = $adm_tipo_dato['id'];
                $filtro['adm_tipo_dato.descripcion'] = $adm_tipo_dato['descripcion'];
                $filtro['adm_tipo_dato.codigo'] = $adm_tipo_dato['codigo'];

                $existe_fil = $adm_tipo_dato_modelo->existe(filtro: $filtro);
                if(errores::$error){
                    return (new errores())->error(mensaje: 'Error al verificar si existe', data:  $existe_fil);
                }

                if(!$existe_fil){
                    $upd = $adm_tipo_dato_modelo->modifica_bd_base(registro: $adm_tipo_dato, id: $adm_tipo_dato['id']);
                    if(errores::$error){
                        return (new errores())->error(mensaje: 'Error al actualizar', data:  $upd);
                    }
                }

            }
        }

        return $create;

    }

    private function existe_accion(string $accion, array $adm_seccion, PDO $link)
    {
        $seccion = $adm_seccion['adm_seccion_descripcion'];
        $existe_accion = (new adm_accion(link: $link))->existe_accion(adm_accion: $accion,adm_seccion:  $seccion);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al validar accion', data:  $existe_accion);
        }
        return $existe_accion;

    }

    private function inserta_accion(array $accion_basica_importa, array $adm_seccion, PDO $link): array|stdClass
    {
        $accion_ins = $accion_basica_importa;
        $accion_ins['adm_seccion_id'] = $adm_seccion['adm_seccion_id'];
        $r_accion = (new adm_accion(link: $link))->alta_registro(registro: $accion_ins);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar accion', data:  $r_accion);
        }
        return $r_accion;

    }

    private function inserta_accion_base(string $accion, array $accion_basica_importa, array $adm_seccion, PDO $link): array|stdClass
    {
        $r_accion = new stdClass();
        $existe_accion = $this->existe_accion(accion: $accion,adm_seccion:  $adm_seccion, link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al validar accion', data:  $existe_accion);
        }
        if(!$existe_accion){
            $r_accion = $this->inserta_accion(accion_basica_importa: $accion_basica_importa,adm_seccion:  $adm_seccion,link:  $link);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al insertar accion', data:  $r_accion);
            }
        }
        return $r_accion;

    }
    final public function instala(PDO $link): array|stdClass
    {

        $out = new stdClass();

        $adm_menu = $this->adm_menu(link: $link);
        if (errores::$error) {
            return (new errores())->error(mensaje: 'Error al init adm_menu', data: $adm_menu);
        }
        $out->adm_menu = $adm_menu;

        $adm_namespace = $this->adm_namespace(link: $link);
        if (errores::$error) {
            return (new errores())->error(mensaje: 'Error al init adm_namespace', data: $adm_namespace);
        }
        $out->adm_namespace = $adm_namespace;

        $adm_tipo_dato = $this->adm_tipo_dato(link: $link);
        if (errores::$error) {
            return (new errores())->error(mensaje: 'Error al init adm_tipo_dato', data: $adm_tipo_dato);
        }
        $out->adm_tipo_dato = $adm_tipo_dato;

        $adm_campo = $this->adm_campo(link: $link);
        if (errores::$error) {
            return (new errores())->error(mensaje: 'Error al init adm_campo', data: $adm_campo);
        }
        $out->adm_campo = $adm_campo;


        $adm_accion_basica = $this->adm_accion_basica(link: $link);
        if (errores::$error) {
            return (new errores())->error(mensaje: 'Error al init adm_accion_basica', data: $adm_accion_basica);
        }
        $out->adm_accion_basica = $adm_accion_basica;

        $adm_seccion = $this->adm_seccion(link: $link);
        if (errores::$error) {
            return (new errores())->error(mensaje: 'Error al init adm_seccion', data: $adm_seccion);
        }
        $out->adm_seccion = $adm_seccion;


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

    private function integra_accion_basica(string $accion_basica_descripcion, array $adm_secciones, PDO $link): array
    {

        $acciones = array();
        $accion_basica_importa = $this->accion_basica_importa(accion_basica_descripcion: $accion_basica_descripcion,link:  $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener accion_basica_importa',
                data:  $accion_basica_importa);
        }

        foreach ($adm_secciones as $adm_seccion){

            $r_accion = $this->inserta_accion_base(accion: $accion_basica_descripcion,
                accion_basica_importa:  $accion_basica_importa,adm_seccion:  $adm_seccion,link:  $link);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al insertar accion', data:  $r_accion);
            }
            $acciones[] = $r_accion;

        }

        return $acciones;

    }

}

<?php
namespace gamboamartin\administrador\instalacion;

use gamboamartin\administrador\models\_instalacion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\administrador\models\adm_namespace;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class _adm
{
    private errores $error;

    public function __construct()
    {
        $this->error = new errores();

    }

    private function adm_accion_ins(int $adm_seccion_id, string $descripcion, string $es_view,
                                    string $icono, string $lista, string $titulo): array
    {
        $adm_accion_ins['descripcion'] = $descripcion;
        $adm_accion_ins['adm_seccion_id'] = $adm_seccion_id;
        $adm_accion_ins['icono'] = $icono;
        $adm_accion_ins['visible'] = 'inactivo';
        $adm_accion_ins['inicio'] = 'inactivo';
        $adm_accion_ins['lista'] = $lista;
        $adm_accion_ins['seguridad'] = 'activo';
        $adm_accion_ins['es_modal'] = 'inactivo';
        $adm_accion_ins['es_view'] = $es_view;
        $adm_accion_ins['titulo'] = $titulo;
        $adm_accion_ins['css'] = 'warning';
        $adm_accion_ins['es_status'] = 'inactivo';
        $adm_accion_ins['es_lista'] = $lista;
        $adm_accion_ins['muestra_icono_btn'] = 'activo';
        $adm_accion_ins['muestra_titulo_btn'] = 'inactivo';

        return $adm_accion_ins;

    }

    private function adm_childrens(string $adm_seccion_descripcion, string $adm_seccion_pertenece_descripcion,
                                        string $etiqueta_label, PDO $link, stdClass $parents): array|stdClass
    {
        $out = new stdClass();
        $adm_seccion_id = $this->adm_seccion_id(adm_menu_id: $parents->adm_menu_id,adm_namespace_id:  $parents->adm_namespace_id,
            adm_seccion_descripcion:  $adm_seccion_descripcion, etiqueta_label: $etiqueta_label, link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener adm_seccion_id', data:  $adm_seccion_id);
        }
        $out->adm_seccion_id = $adm_seccion_id;


        $adm_seccion_pertenece_id = $this->adm_seccion_pertenece_id(
            adm_seccion_pertenece_descripcion: $adm_seccion_pertenece_descripcion,adm_seccion_id:  $adm_seccion_id,
            adm_sistema_id:  $parents->adm_sistema_id,link:  $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener adm_seccion_pertenece_id', data:  $adm_seccion_pertenece_id);
        }

        $out->adm_seccion_pertenece_id = $adm_seccion_pertenece_id;
        return $out;

    }

    private function adm_menu_id(string $adm_menu_descripcion, PDO $link)
    {

        $adm_menu_modelo = new adm_menu(link: $link);

        $row_ins = array();
        $row_ins['descripcion'] = $adm_menu_descripcion;
        $row_ins['etiqueta_label'] = $adm_menu_descripcion;
        $row_ins['icono'] = 'SI';
        $row_ins['titulo'] = $adm_menu_descripcion;

        $adm_menu_id = (new _instalacion(link: $link))->data_adm(
            descripcion: $adm_menu_descripcion,modelo:  $adm_menu_modelo, row_ins: $row_ins);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_menu_id', data:  $adm_menu_id);
        }

        return $adm_menu_id;
    }

    private function adm_namespace_id(string $adm_namespace_name, string $adm_namespace_descripcion, PDO $link)
    {
        $adm_namespace_modelo = new adm_namespace(link: $link);

        $row_ins = array();
        $row_ins['descripcion'] = $adm_namespace_descripcion;
        $row_ins['name'] = $adm_namespace_name;


        $adm_namespace_id = (new _instalacion(link: $link))->data_adm(
            descripcion: $adm_namespace_descripcion,modelo:  $adm_namespace_modelo, row_ins: $row_ins);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener adm_namespace_id', data:  $adm_namespace_id);
        }

        return $adm_namespace_id;

    }

    private function adm_parents(string $adm_menu_descripcion, string $adm_namespace_name,
                                 string $adm_namespace_descripcion, string $adm_sistema_descripcion, PDO $link): array|stdClass
    {
        $out = new stdClass();

        $adm_menu_id = $this->adm_menu_id(adm_menu_descripcion: $adm_menu_descripcion, link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener adm_menu_id', data:  $adm_menu_id);
        }

        $out->adm_menu_id = $adm_menu_id;


        $adm_namespace_id = $this->adm_namespace_id(adm_namespace_name: $adm_namespace_name, adm_namespace_descripcion: $adm_namespace_descripcion, link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener adm_namespace_id', data:  $adm_namespace_id);
        }
        $out->adm_namespace_id = $adm_namespace_id;

        $adm_sistema_id = $this->adm_sistema_id(adm_sistema_descripcion: $adm_sistema_descripcion,link:  $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener adm_sistema_id', data:  $adm_sistema_id);
        }

        $out->adm_sistema_id = $adm_sistema_id;

        return $out;

    }

    private function adm_seccion_id(int $adm_menu_id, int $adm_namespace_id, string $adm_seccion_descripcion,
                                    string $etiqueta_label, PDO $link)
    {
        $adm_seccion_modelo = new adm_seccion(link: $link);

        $row_ins = array();
        $row_ins['descripcion'] = $adm_seccion_descripcion;
        $row_ins['etiqueta_label'] = $etiqueta_label;
        $row_ins['adm_menu_id'] = $adm_menu_id;
        $row_ins['adm_namespace_id'] = $adm_namespace_id;

        $adm_seccion_id = (new _instalacion(link: $link))->data_adm(
            descripcion: $adm_seccion_descripcion,modelo:  $adm_seccion_modelo, row_ins: $row_ins);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener adm_seccion_id', data:  $adm_seccion_id);
        }

        return $adm_seccion_id;

    }

    private function adm_seccion_pertenece_id(string $adm_seccion_pertenece_descripcion, int $adm_seccion_id, int $adm_sistema_id, PDO $link)
    {

        $adm_seccion_pertenece_modelo = new adm_seccion_pertenece(link: $link);

        $row_ins = array();
        $row_ins['adm_sistema_id'] = $adm_sistema_id;
        $row_ins['adm_seccion_id'] = $adm_seccion_id;

        $filtro['adm_seccion.id'] = $adm_seccion_id;
        $filtro['adm_sistema.id'] = $adm_sistema_id;

        $adm_seccion_pertenece_id = (new _instalacion(link: $link))->data_adm(descripcion: $adm_seccion_pertenece_descripcion,
            modelo:  $adm_seccion_pertenece_modelo, row_ins: $row_ins, filtro: $filtro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener adm_seccion_pertenece_id', data:  $adm_seccion_pertenece_id);
        }

        return $adm_seccion_pertenece_id;

    }

    private function adm_sistema_id(string $adm_sistema_descripcion, PDO $link)
    {

        $adm_sistema_modelo = new adm_sistema(link: $link);

        $row_ins = array();
        $row_ins['descripcion'] = $adm_sistema_descripcion;

        $adm_sistema_id = (new _instalacion(link: $link))->data_adm(descripcion: $adm_sistema_descripcion,
            modelo:  $adm_sistema_modelo, row_ins: $row_ins);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener adm_sistema_id', data:  $adm_sistema_id);
        }

        return $adm_sistema_id;

    }

    private function inserta_accion(string $adm_accion_descripcion, array $adm_accion_ins,
                                    string $adm_seccion_descripcion, PDO $link): array|stdClass
    {
        $alta = new stdClass();
        $filtro['adm_accion.descripcion'] = $adm_accion_descripcion;
        $filtro['adm_seccion.descripcion'] = $adm_seccion_descripcion;

        $existe  = (new adm_accion(link: $link))->existe(filtro: $filtro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener accion',data:  $existe);
        }
        if(!$existe){
            $alta = (new adm_accion(link: $link))->alta_registro(registro: $adm_accion_ins);
            if(errores::$error){
                return (new errores())->error(mensaje: 'Error al insertar accion',data:  $alta);
            }
        }

        return $alta;


    }

    final public function inserta_accion_base(string $adm_accion_descripcion,string $adm_seccion_descripcion,
                                         string $es_view, string $icono, PDO $link, string $lista, string $titulo)
    {
        $adm_seccion_id = (new adm_seccion(link: $link))->adm_seccion_id(descripcion: $adm_seccion_descripcion);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener adm_seccion_id', data:  $adm_seccion_id);
        }

        $adm_accion_ins = $this->adm_accion_ins(adm_seccion_id: $adm_seccion_id,descripcion:  $adm_accion_descripcion,
            es_view:  $es_view,icono:  $icono,lista:  $lista,titulo:  $titulo);

        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener accion ins',data:  $adm_accion_ins);
        }

        $alta_accion = $this->inserta_accion(adm_accion_descripcion: $adm_accion_descripcion,adm_accion_ins:  $adm_accion_ins,
            adm_seccion_descripcion:  $adm_seccion_descripcion,link:  $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar accion',data:  $alta_accion);
        }

    }

    final public function integra_acl(string $adm_menu_descripcion, string $adm_namespace_name,string $adm_namespace_descripcion, string $adm_seccion_descripcion ,string $adm_seccion_pertenece_descripcion,
                                      string $adm_sistema_descripcion, string $etiqueta_label, PDO $link): array|stdClass
    {
        $parents = $this->adm_parents(adm_menu_descripcion: $adm_menu_descripcion,
            adm_namespace_name: $adm_namespace_name, adm_namespace_descripcion: $adm_namespace_descripcion, adm_sistema_descripcion: $adm_sistema_descripcion, link: $link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener parents', data:  $parents);
        }

        $childrens = $this->adm_childrens(adm_seccion_descripcion: $adm_seccion_descripcion,
            adm_seccion_pertenece_descripcion: $adm_seccion_pertenece_descripcion, etiqueta_label: $etiqueta_label,
            link: $link, parents: $parents);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al obtener childrens', data:  $childrens);
        }

        $data = new stdClass();
        $data->parents = $parents;
        $data->childrens = $childrens;

        return $data;

    }

}

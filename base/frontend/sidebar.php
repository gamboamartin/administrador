<?php

namespace base\frontend;

use gamboamartin\errores\errores;

class sidebar
{

    private function transformar_datos(array $registros, string $titulo_categoria): array
    {

        $categorias_secciones = array();

        foreach ($registros as $index => $registro) {

            if (!array_key_exists(key: $titulo_categoria, array: $registro)) {
                return (new errores())->error("No existe la key: $titulo_categoria", $registro);
            }

            if (!array_key_exists(key: $registro['adm_categoria_categoria'], array: $categorias_secciones)) {
                $categorias_secciones[$registro['adm_categoria_categoria']] = array();
            }

            $secciones = array();
            $secciones['seccion'] = $registro['adm_seccion_descripcion'];
            $secciones['titulo'] = $registro['adm_categoria_secciones_titulo'];
            $secciones['accion'] = 'lista';

            $categorias_secciones[$registro['adm_categoria_categoria']]['icono'] = "bi-envelope";
            $categorias_secciones[$registro['adm_categoria_categoria']]['secciones'][] = $secciones;

        }

        return $categorias_secciones;
    }

    private function transformar_datos2(array $registros, string $titulo_categoria): array
    {
        $categorias_secciones = array();

        foreach ($registros as $index => $registro) {
            if (!array_key_exists(key: $titulo_categoria, array: $registro)) {
                return (new errores())->error("No existe la key: $titulo_categoria", $registro);
            }

            if (!array_key_exists(key: $registro[$titulo_categoria], array: $categorias_secciones)) {
                $categorias_secciones[$registro[$titulo_categoria]] = array();
            }

            $categorias_secciones[$registro[$titulo_categoria]]['icono'] = "bi-envelope";

            foreach ($registro['adm_secciones'] as $seccion){
                $secciones = array();
                $secciones['seccion'] = $seccion['adm_seccion_descripcion'];
                $secciones['titulo'] = $seccion['adm_seccion_etiqueta_label'];
                $secciones['accion'] = 'lista';

                $categorias_secciones[$registro[$titulo_categoria]]['secciones'][] = $secciones;
            }
        }

        return $categorias_secciones;
    }

    public function print_categorias(array $registros, string $titulo_categoria, string $session_id): string|array
    {
        $data_object = $this->transformar_datos(registros: $registros, titulo_categoria: $titulo_categoria);
        if (errores::$error) {
            return (new errores())->error("No se pudo transformar los datos", $data_object);
        }

        $html = '';

        foreach ($data_object as $key => $registro) {

            $html_li = $this->html_li_item(categoria: $key, items: $registro['secciones'], session_id: $session_id,
                icono: $registro['icono']);
            if (errores::$error) {
                return (new errores())->error("No se pudo maquetar html li item", $html_li);
            }
            $html .= $html_li;
        }
        return $html;
    }

    public function print_categorias2(array $registros, string $titulo_categoria, string $session_id): string|array
    {
        $data_object = $this->transformar_datos2(registros: $registros, titulo_categoria: $titulo_categoria);
        if (errores::$error) {
            return (new errores())->error("No se pudo transformar los datos", $data_object);
        }

        $html = '';

        foreach ($data_object as $key => $registro) {

            $html_li = $this->html_li_item(categoria: $key, items: $registro['secciones'], session_id: $session_id,
                icono: $registro['icono']);
            if (errores::$error) {
                return (new errores())->error("No se pudo maquetar html li item", $html_li);
            }
            $html .= $html_li;
        }
        return $html;
    }

    public function html_li_item(string $categoria, array $items, string $session_id, string $icono = ""): string|array
    {
        $html = '<li class="nav-group" aria-expanded="false"><a class="nav-link nav-group-toggle" href="#">';

        if ($icono !== "") {
            $html .= "<i class='bi $icono nav-icon'></i>";
        }

        $html .= $categoria . '</a>';

        $html_li_list = $this->html_li_list_items(items: $items, session_id: $session_id);
        if (errores::$error) {
            return (new errores())->error("No se pudo maquetar html li list items", $html_li_list);
        }

        $html .= $html_li_list;
        $html .= '</li>';

        return $html;
    }

    public function html_li_list_items(array $items, string $session_id): string
    {
        $html = '<ul class="nav-group-items" style="height: 0px;">';

        foreach ($items as $item) {
            $link = './index.php?seccion=' . $item['seccion'];
            $link .= '&accion=' . $item['accion'] . '&session_id=' . $session_id;

            $html .= '<li class="nav-item">';
            $html .= "<a class='nav-link' href='$link'><span class='nav-icon'></span>";

            $html .= $item['titulo'];
            $html .= '</a></li>';
        }

        $html .= '</ul>';

        return $html;
    }

}
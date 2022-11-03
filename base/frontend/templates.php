<?php
namespace base\frontend;

use config\generales;
use gamboamartin\errores\errores;

use JetBrains\PhpStorm\Pure;
use PDO;
use stdClass;


class templates{
    public string $accion = '';
    public array $campos_filtro;
    public array $campos_invisibles = array();


    public PDO $link;
    public errores $error;
    public validaciones_directivas $validacion;

    public array $registro = array();
    public array $valores = array();
    public array $botones_filtros = array();

    /**
     * DEBUG INI
     * templates constructor.
     * @param PDO $link
     */
    #[Pure] public function __construct(PDO $link){
        $this->error = new errores();
        $this->validacion = new validaciones_directivas();
        $this->link = $link;

    }


    /**
     *
     * Asigna valores bool a inputs
     * @example
     *      $this->input = $this->asigna_valores_booleanos_input();
     *
     * @return array informacion de input
     * @throws errores !isset($this->input['pattern'])
     * @throws errores !isset($this->input['pattern']
     * @throws errores !isset($this->input['con_label'])
     * @throws errores !is_bool($this->input['con_label'])
     * @uses  templates
     */
    private function asigna_valores_booleanos_input(string $campo_name, array $input, bool $disabled,
                                                    array $campos_disabled): array{

        if (!isset($input['pattern'])) {
            $input['pattern'] = '';
        }
        if (!isset($input['pattern'])) {
            $input['select_vacio_alta'] = false;
        }

        $con_label = $this->con_label($input);
        if(errores::$error){
            return $this->error->error('Error al asignar con label',$con_label);
        }

        $ln = $this->input_ln($input);
        if(errores::$error){
            return $this->error->error('Error al asignar ln',$ln);
        }

        $input['disabled'] = $disabled;
        if(in_array($campo_name, $campos_disabled, true)){
            $input['disabled'] = true;
        }

        return $input;
    }

    /**
     * Genera los campos para una lista basado en elementos de lista
     * @version 1.30.14
     * @param array $elementos_lista Registros de tipo elementos de lista
     * @return array|stdClass
     */
    public function campos_lista(array $elementos_lista): array|stdClass
    {

        $campos_lista = (new inicializacion())->campos_lista(elementos_lista: $elementos_lista);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar campos lista',data: $campos_lista);
        }
        return $campos_lista;

    }

    /**
     *
     * @param array $input
     * @return array
     */
    private function con_label(array $input): array
    {
        if (!isset($input['con_label'])) {
            $input['con_label'] = false;
        }
        else if(!is_bool($input['con_label'])){
            return $this->error->error('Error al input[con_label] deb ser un bool',$input);
        }
        return $input;
    }

    /**
     * PHPUNIT
     * @param array $input
     * @return bool|array
     */
    private function input_ln(array $input): bool|array
    {
        if (!isset($input['ln'])) {
            $input['ln'] = false;
        }
        else if(!is_bool($input['ln'])){
            return $this->error->error('Error al input[ln] deb ser un bool',$input);
        }
        return $input['ln'];
    }


    /**
     * P INT
     * @param array $registros
     * @param string $campo_id
     * @param int $n_paginas
     * @param int $pagina_seleccionada
     * @param string $seccion
     * @param array $acciones_asignadas
     * @param string $seccion_link
     * @param string $accion_link
     * @param string $session_id
     * @param array $botones_filtros
     * @param array $filtro_boton_seleccionado
     * @param array $etiqueta_campos
     * @param array $campos
     * @return array|string
     */
    public function lista_completa(string $campo_id, array $registros, int $n_paginas, int $pagina_seleccionada,
                                   string $seccion, array $acciones_asignadas, string $seccion_link,
                                   string $accion_link, string $session_id, array $campos, array $etiqueta_campos,
                                   array $botones_filtros = array(), array $filtro_boton_seleccionado = array()): array|string
    {


        $filtro_boton_seleccionado_html = '';
        if(count($filtro_boton_seleccionado)>0){
            $key_filtro = key($filtro_boton_seleccionado);
            $valor = $filtro_boton_seleccionado[$key_filtro];
            $filtro_boton_seleccionado_html .= "&filtro_btn[$key_filtro]=$valor";
        }


        $this->botones_filtros = $botones_filtros;

        $ths = (new listas())->genera_th(etiqueta_campos:  $etiqueta_campos, seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar ths',data: $ths);
        }


        $filtros_lista = (new listas())->genera_filtros_lista(botones_filtros:  $this->botones_filtros,
            seccion: $seccion,session_id: $session_id);
        if(errores::$error){
            return $this->error->error('Error al obtener filtros', $filtros_lista);
        }

        $filtros_html = (new listas())->obten_html_filtros(filtros_lista: $filtros_lista,
            filtro_boton_seleccionado_html:  $filtro_boton_seleccionado_html,seccion:  $seccion,session_id: $session_id);
        if(errores::$error){
            return $this->error->error('Error al obtener filtros', $filtros_html);
        }



        $html = $filtros_html;



        $html.= "<div class='table-responsive'>";
        $html .= "<table class='table table-striped table-bordered table-hover letra-mediana text-truncate table-lista'>";

        $html .= "<thead class='thead-azul-light'><tr>$ths</tr></thead><tbody class='listado'>";

        $lista_html = (new listas())->lista(campo_id:  $campo_id, campos:  $campos, registros: $registros,
            seccion: $seccion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar lista',data: $lista_html);
        }


        $html .= $lista_html;
        $html .= '</tbody></table>';
        $html.= "<div>";

        $paginas_numeradas = '';
        $paginas_previas_mostrables = 4;

        $pagina_inicial = $pagina_seleccionada - $paginas_previas_mostrables;

        if($pagina_inicial <= 0){
            $pagina_inicial = 1;
        }

        if($pagina_inicial > 1) {
            if ($pagina_seleccionada === 1) {
                $paginas_numeradas .= '<li class="page-item active">
          <a class="page-link" href="#">1 <span class="sr-only">(current)</span></a>
        </li>';
            } else {
                $link_pagina = './index.php?seccion='.$seccion_link.'&accion='.$accion_link.'&session_id='.$session_id.'&pag_seleccionada=1'.$filtro_boton_seleccionado_html;
                $paginas_numeradas .= '<li class="page-item"><a class="page-link" href="' . $link_pagina . '">1</a></li>';

            }
        }
        $paginas_vistas = 0;
        for($i = $pagina_inicial; $i<=$n_paginas; $i++){
            if($paginas_vistas >=10){
                if($i < $n_paginas) {
                    continue;
                }
            }
            $link_pagina = './index.php?seccion='.$seccion_link.'&accion='.$accion_link.'&session_id='.$session_id.'&pag_seleccionada='.$i.$filtro_boton_seleccionado_html;
            if($i === $pagina_seleccionada){
                $paginas_numeradas.='<li class="page-item active">
      <a class="page-link" href="#">'.$i.' <span class="sr-only">(current)</span></a>
    </li>';
            }
            else {
                $paginas_numeradas .= '<li class="page-item"><a class="page-link" href="'.$link_pagina.'">' . $i . '</a></li>';
            }
            $paginas_vistas ++;
        }

        $n_pagina_previa = $pagina_seleccionada -1;
        if($n_pagina_previa <= 0){
            $pagina_previa = '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>';
        }
        else{
            $link_pagina = './index.php?seccion='.$seccion_link.'&accion='.$accion_link.'&session_id='.$session_id.'&pag_seleccionada='.$n_pagina_previa.$filtro_boton_seleccionado_html;
            $pagina_previa = '<li class="page-item"><a class="page-link" href="'.$link_pagina.'" tabindex="-1">Previous</a></li>';
        }

        $n_pagina_siguiente = $pagina_seleccionada + 1;

        if($n_pagina_siguiente > $n_paginas){
            $pagina_siguiente = '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Next</a></li>';
        }
        else{
            $link_pagina = './index.php?seccion='.$seccion_link.'&accion='.$accion_link.'&session_id='.$session_id.'&pag_seleccionada='.$n_pagina_siguiente.$filtro_boton_seleccionado_html;
            $pagina_siguiente = '<li class="page-item"><a class="page-link" href="'.$link_pagina.'">Next</a></li>';
        }

        $paginador = '<nav aria-label="..." class="no-print">
<ul class="pagination">
    '.$pagina_previa.'
    '.$paginas_numeradas.'
    '.$pagina_siguiente.'
  </ul>
</nav>';

        $html.=$paginador;

        return $html;
    }




    /**
     * P ORDER P INT
     * @param array $elementos_lista
     * @return array
     */
    public function obten_campos_filtro(array $elementos_lista): array
    {

        $this->campos_filtro = array();
        foreach ($elementos_lista as $elemento_lista) {

            $campo_filtro = (new inicializacion())->campo_filtro($elemento_lista);
            if(errores::$error){
                return $this->error->error('Error al limpiar datos', $campo_filtro);
            }

            $elemento = array(
                'campo_filtro'=>$campo_filtro,
                'tabla_externa'=>$elemento_lista['adm_elemento_lista_tabla_externa'],
                'campo'=>$elemento_lista['adm_elemento_lista_campo'],
                'etiqueta'=>$elemento_lista['adm_elemento_lista_etiqueta'],
                'columnas'=> '' ,
                'tipo' => $elemento_lista['adm_elemento_lista_tipo']);
            $this->campos_filtro[] = $elemento;
        }
        return $this->campos_filtro;

    }

}

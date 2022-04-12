<?php
namespace controllers;

use base\controller\controlador_base;
use models\accion;
use models\accion_grupo;
use models\grupo;
use models\menu;
use models\seccion;


class controlador_grupo extends controlador_base{
    public string $grupo_descripcion;
    public $menus;
    public $secciones;
    public $acciones_vista;
    public $acciones;
    private $acciones_grupos;
    public $grupo_id;

    private $accion_grupo;
    public $grupos;
    private $grupo_modelo;
    private $menu_modelo;
    public $encabezado_grupo;
    private $operaciones_controlador;
    private accion $accion_modelo;
    public array $filtro = array();
    public $resultado;

    public function __construct($link){
        $modelo = new grupo($link);

        parent::__construct($link, $modelo);

        $this->grupo_modelo = new grupo($link);
        $this->menu_modelo = new menu($link);

        $this->seccion_menu_modelo = new seccion($link);
        $this->accion_modelo = new accion($link);
    }

    public function acl_tipo_documento(){
        $acl = (new grupo($this->link))->acl_tipo_documento($this->registro_id);
        if(isset($acl['error'])){
            $error = $this->errores->error('Error al obtner acl',$acl);
            print_r($error);
            die('Error');
        }

        foreach($acl as $key=>$data){
            $link_asigna = '<a href="index.php?seccion=tipo_documento&accion=asigna_permiso&registro_id=';
            $link_asigna .= $data['tipo_documento_id']."&grupo_id=$this->registro_id&session_id=".SESSION_ID.'">';
            $link_asigna .= "Asigna";
            $link_asigna .= '</a>';

            $link_elimina = '<a href="index.php?seccion=tipo_documento&accion=elimina_permiso&registro_id=';
            $link_elimina .= $data['tipo_documento_id']."&grupo_id=$this->registro_id&session_id=".SESSION_ID.'">';
            $link_elimina .= "Elimina";
            $link_elimina .= '</a>';

            $acl[$key]['link_asigna'] = '';
            $acl[$key]['link_elimina'] = '';
            if($data['existe'] === 'inactivo'){
                $acl[$key]['link_asigna'] = $link_asigna;
            }
            if($data['existe'] === 'activo'){
                $acl[$key]['link_elimina'] = $link_elimina;
            }
        }

        $this->registros['acl'] = $acl;
    }

    public function asigna_accion($header, $ws){
        if(!isset($_GET['registro_id'])){
            $error = $this->errores->error('Error al no existe registro_id',$_GET);
            if(!$header){
                return $error;
            }
            if($ws){
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode($error);
                exit;
            }
            print_r($error);
            die('Error');
        }

        $this->grupo_id = $_GET['registro_id'];
        $menu_modelo = new menu($this->link);
        $grupo_modelo = new grupo($this->link);
        $seccion_menu_modelo = new seccion_($this->link);
        $accion_modelo = new accion($this->link);
        $accion_grupo_modelo = new accion_grupo($this->link);


        $this->filtro['grupo_id']['campo'] = 'grupo_id';
        $this->filtro['grupo_id']['value'] = $this->grupo_id;

        $this->resultado = $accion_grupo_modelo->filtro_and($this->filtro,'numeros',array(),array(),
            0,0,array());

        if(isset($this->resultado['error'])){
            $error = $this->errores->error('Error al obtener datos',$this->resultado);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $this->acciones_grupos = $this->resultado['registros'];

        $grupo_modelo->registro_id = $this->grupo_id;

        $grupo = $grupo_modelo->obten_data();

        if(isset($grupo['error'])){
            $error = $this->errores->error('Error al obtener grupo',$grupo);
            if(!$header){
                return $error;
            }
            if($ws){
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode($error);
                exit;
            }
            print_r($error);
            die('Error');
        }

        $this->grupo_descripcion = $grupo['grupo_descripcion'];
        $resultado = $menu_modelo->obten_registros_activos(array(),array());
        if(isset($resultado['error'])){
            $error = $this->errores->error('Error al obtener menus',$resultado);
            if(!$header){
                return $error;
            }
            if($ws){
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode($error);
                exit;
            }
            print_r($error);
            die('Error');
        }

        $this->menus = $resultado['registros'];
        $resultado = $seccion_menu_modelo->obten_registros_activos(array(),array());

        if(isset($resultado['error'])){
            $error = $this->errores->error('Error al obtener menus',$resultado);
            if(!$header){
                return $error;
            }
            if($ws){
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode($error);
                exit;
            }
            print_r($error);
            die('Error');
        }

        $this->secciones = $resultado['registros'];

        $resultado = $accion_modelo->obten_registros_activos(array(),array());
        if(isset($resultado['error'])){
            $error = $this->errores->error('Error al obtener menus',$resultado);
            if(!$header){
                return $error;
            }
            if($ws){
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode($error);
                exit;
            }
            print_r($error);
            die('Error');
        }

        $this->acciones = $resultado['registros'];

        $this->acciones_vista = array();
        foreach($this->acciones as $accion){
            $aplicado = 0;
            $accion_grupo_id = -1;
            foreach ($this->acciones_grupos as $this->accion_grupo){
                if($accion['accion_id'] == $this->accion_grupo['accion_id']) {
                    $aplicado = 1;
                    $accion_grupo_id = $this->accion_grupo['accion_grupo_id'];
                    break;
                }
            }
            $accion['aplicado'] = $aplicado;
            $accion['accion_grupo_id'] = $accion_grupo_id;
            $this->acciones_vista[] = $accion;
        }
        if(!$header){
            return $this->acciones;
        }
        if($ws){
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($this->acciones);
            exit;
        }
    }

    public function guarda_grupo_id(){
        setcookie('grupo_permiso_id',$_POST['grupo_id']);
        header('Location: index.php?seccion=grupo&accion=selecciona_permiso&session_id='.SESSION_ID);
        exit;
    }

    public function elimina_accion_bd(bool $header){
        $modelo = new accion_grupo($this->link);
        $tabla = 'accion_grupo';
        $grupo_id = $_POST['grupo_id'];
        $accion_id = $_POST['accion_id'];

        $filtros['grupo_id']['campo'] = 'grupo_id';
        $filtros['grupo_id']['value'] = $grupo_id;

        $filtros['accion_id']['campo'] = 'accion_id';
        $filtros['accion_id']['value'] = $accion_id;

        $resultado = $modelo->filtro_and($filtros);

        if(isset($resultado['error'])){
            $error = $this->errores->error('Error al obtener datos',$this->breadcrumbs);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $registro = $resultado['registros'];

        $registro_id = $registro[0]['accion_grupo_id'];

        $r_elimina = $modelo->elimina_bd($registro_id);
        if(isset($r_elimina['error'])){
            $error = $this->errores->error('Error al eliminar',$r_elimina);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

    }

    public function agrega_accion_bd(){
        $tabla = 'accion_grupo';
        $modelo = new modelo($this->link,$tabla);
        $modelo->registro = $_POST;
        $resultado = $modelo->alta_bd();
    }

}

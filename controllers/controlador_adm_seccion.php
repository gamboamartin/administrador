<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use base\frontend\templates;
use config\generales;
use gamboamartin\errores\errores;
use JsonException;
use models\adm_accion;
use models\adm_accion_basica;
use models\adm_seccion;


class controlador_adm_seccion extends controlador_base{
    public $operaciones_controlador;
    public $accion_modelo;
    public $accion_basica_modelo;
    public $encabezado_seccion_menu;
    public $seccion_menu_id = false;
    public $template_accion;


    public function __construct($link){

        $modelo = new adm_seccion($link);

        parent::__construct($link, $modelo);

        $this->accion_modelo = new adm_accion($link);
        $this->accion_basica_modelo = new adm_accion_basica($link);
        $this->seccion_menu_modelo = new adm_seccion($link);
        $this->template_accion = new templates($link,'accion');

    }

    public function asigna_accion(){
        $breadcrumbs = array('lista', 'alta');

        $accion_modelo = new adm_accion($this->link);
        if(errores::$error){
            return  $this->errores->error(mensaje: 'Error al generar modelo',data: $accion_modelo);
        }

        $accion_registro = $accion_modelo->accion_registro($this->seccion, $this->accion);
        if(errores::$error){
            return  $this->errores->error('Error al obtener acciones',$accion_registro);
        }

        $this->breadcrumbs = $this->directiva->nav_breadcumbs($breadcrumbs,$this->seccion,$this->accion, $this->link, $accion_registro);
        $this->seccion_menu_id = $_GET['registro_id'];
        $this->operaciones_controlador->encabezados($this);
        setcookie('seccion_menu_id' , $this->seccion_menu_id);

    }

    public function accion_alta_bd(){

    }

    /**
     * @param bool $header Si header muestra resultado en front
     * @param bool $ws
     * @return array
     * @throws JsonException
     */
    public function alta_bd(bool $header, bool $ws): array{
        $this->link->beginTransaction();
        $r_alta_bd = parent::alta_bd(false, false);
        if(errores::$error){
            $this->link->rollBack();
            $error =   $this->errores->error(mensaje: 'Error al dar de alta registro',data: $r_alta_bd);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $seccion_menu_id = $this->registro_id;
        $r_accion_basica = $this->accion_basica_modelo->obten_registros_activos();
        if (errores::$error){
            $this->link->rollBack();
            $error =   $this->errores->error(mensaje: 'Error al obtener datos del registro',data: $r_accion_basica);

            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $acciones_basicas = $r_accion_basica->registros;
        $accion = array();
        foreach ($acciones_basicas as $accion_basica) {
            $accion['descripcion'] = $accion_basica['adm_accion_basica_descripcion'];
            $accion['icono'] = $accion_basica['adm_accion_basica_icono'];
            $accion['visible'] = $accion_basica['adm_accion_basica_visible'];
            $accion['seguridad'] = $accion_basica['adm_accion_basica_seguridad'];
            $accion['inicio'] = $accion_basica['adm_accion_basica_inicio'];
            $accion['lista'] = $accion_basica['adm_accion_basica_lista'];
            $accion['status'] = $accion_basica['adm_accion_basica_status'];
            $accion['adm_seccion_id'] = $seccion_menu_id;
            $this->accion_modelo->registro = $accion;
            $r_alta_accion = $this->accion_modelo->alta_bd();

            if (errores::$error){
                $this->link->rollBack();
                $error =   $this->errores->error(mensaje: 'Error al dar de alta acciones basicas',data: $r_alta_accion);

                if(!$header){
                    return $error;
                }

                print_r($error);
                die('Error');
            }
        }
        $this->link->commit();

        if($header){
            header('Location: index.php?seccion=seccion_menu&accion=lista&mensaje=Agreado con éxito&tipo_mensaje=exito&session_id=' . (new generales())->session_id);
            exit;
        }
        return $r_alta_bd;
    }

}

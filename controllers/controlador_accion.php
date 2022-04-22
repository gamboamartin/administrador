<?php
namespace controllers;
use base\controller\controlador_base;
use gamboamartin\errores\errores;
use JsonException;
use models\accion;

class controlador_accion extends controlador_base{
    public string $busca_accion = '';
    public string $btn_envia = '';
    public string $form_ini = '';
    public string $form_fin = '';
    public array $acciones = array();

    public function __construct($link){
        $modelo = new accion($link);
        parent::__construct($link, $modelo);
        $this->directiva = new html_accion();
    }


    /**
     *
     * @param bool $header
     * @return array|$this
     * @throws JsonException
     */
    public function encuentra_accion(bool $header):array|controlador_accion{
        $template = parent::alta(header: false);
        if(errores::$error){
            $error = $this->errores->error(mensaje: "Error al cargar template",data:  $template,
                params: get_defined_vars());
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $input = $this->directiva->busca_accion();
        if(errores::$error){
            $error = $this->errores->error(mensaje: "Error al generar input", data: $input, params: get_defined_vars());
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->busca_accion = $input;

        $input = $this->directiva->btn_envia();
        if(errores::$error){
            $error = $this->errores->error(mensaje: "Error al generar input",data:  $input, params: get_defined_vars());
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->btn_envia = $input;

        $this->form_ini = $this->directiva->form_ini('resultado_accion');
        if(errores::$error){
            $error = $this->errores->error(mensaje: "Error al generar form", data: $input, params: get_defined_vars());
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->form_fin = $this->directiva->form_fin();
        if(errores::$error){
            $error = $this->errores->error(mensaje: "Error al generar form",data:  $input, params: get_defined_vars());
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        return $this;
    }

    /**
     * PRUEBAS FINALIZADAS
     * @param bool $header
     * @return array
     */
    public function resultado_accion(bool $header):array{
        /**
         * REFCATRORIZAR
         */
        if(!isset($_POST)){
            $error = $this->errores->error(mensaje: "Error no existe POST", data: $_GET, params: get_defined_vars());
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        if(!is_array($_POST)){
            $error = $this->errores->error(mensaje: "Error POST debe ser un array",  data: $_POST,
                params: get_defined_vars());
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $keys = array('busca_accion');
        $valida = $this->validacion->valida_existencia_keys($_POST,$keys);
        if(errores::$error){
            $error = $this->errores->error("Error al validar POST", $valida);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $filtro['accion.descripcion'] = $_POST['busca_accion'];
        $filtro['seccion_menu.descripcion'] = $_POST['busca_accion'];
        $filtro['menu.descripcion'] = $_POST['busca_accion'];


        $accion_modelo = new accion($this->link);
        $resultado = $accion_modelo->filtro_or($filtro);
        if(errores::$error){
            $error = $this->errores->error("Error al obtener acciones", $resultado);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $acciones = $accion_modelo->registros;
        foreach ($acciones as $accion){
            $data = $accion;
            $link = $this->directiva->link($accion['seccion_menu_descripcion'], $accion['accion_descripcion']);
            if(errores::$error){
                $error = $this->errores->error("Error al obtener link", $link);
                if(!$header){
                    return $error;
                }
                print_r($error);
                die('Error');
            }
            $data['ejecuta'] = $link;
            $this->acciones[] = $data;
        }
        return $this->acciones;
    }

}
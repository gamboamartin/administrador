<?php
namespace gamboamartin\controllers;
use base\controller\controlador_base;
use gamboamartin\errores\errores;
use models\usuario;

class controlador_usuario extends controlador_base{
    public function __construct($link){
        $modelo = new usuario($link);
        parent::__construct($link, $modelo);
    }

    public function alta_cerrador(bool $header){
        $template = parent::alta(false);
        if(isset($template['error'])){
            $error = $this->errores->error('Error al generar data template alta',$template);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $input = $this->directiva->genera_input_text('usuario',2,'',true);
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['usuario'] = $input;

        $input = $this->directiva->password('',2,'password');
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['password'] = $input;

        $input = $this->directiva->genera_input_text('nombre',2,'',true);
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['nombre'] = $input;

        $input = $this->directiva->genera_input_text('apellido_paterno',2,'',true);
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['apellido_paterno'] = $input;

        $input = $this->directiva->genera_input_text('apellido_materno',2,'',true);
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['apellido_materno'] = $input;

        $input = $this->directiva->input_select_columnas('empleado',-1,4,false,
            array('empleado_codigo','empleado_descripcion'),$this->link,false,'capitalize',
            false,false,array(),array(),array(),true,'empleado_id','Jefe');

        $this->inputs['jefe_id'] = $input;

        $input = $this->directiva->genera_input_text('email',2,'',true);
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['email'] = $input;
    }

    public function alta_cerrador_bd(bool $header){
        $this->link->beginTransaction();
        $usuario_modelo = new usuario($this->link);
        $data = $usuario_modelo->alta_cerrador_bd();
        if(errores::$error){
            $this->link->rollBack();
            $error = $this->errores->error('Error al insertar datos',$data);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->link->commit();
        header('Location: index.php?seccion=usuario&accion=lista&session_id='.SESSION_ID);
        exit;

    }

    public function alta_prospectador(bool $header){
        $template = parent::alta(false);
        if(isset($template['error'])){
            $error = $this->errores->error('Error al generar data template alta',$template);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $input = $this->directiva->genera_input_text('usuario',2,'',true);
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['usuario'] = $input;

        $input = $this->directiva->password('',2,'password');
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['password'] = $input;

        $input = $this->directiva->genera_input_text('nombre',2,'',true);
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['nombre'] = $input;

        $input = $this->directiva->genera_input_text('apellido_paterno',2,'',true);
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['apellido_paterno'] = $input;

        $input = $this->directiva->genera_input_text('apellido_materno',2,'',true);
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['apellido_materno'] = $input;

        $input = $this->directiva->input_select_columnas('empleado',-1,4,false,
            array('empleado_codigo','empleado_descripcion'),$this->link,false,'capitalize',
            false,false,array(),array(),array(),true,'empleado_id','Jefe');

        $this->inputs['jefe_id'] = $input;

        $input = $this->directiva->genera_input_text('email',2,'',true);
        if(isset($input['error'])){
            $error = $this->errores->error('Error al generar input',$input);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->inputs['email'] = $input;
    }

    public function alta_prospectador_bd(bool $header){
        $this->link->beginTransaction();
        $usuario_modelo = new usuario($this->link);
        $data = $usuario_modelo->alta_prospectador_bd();
        if(isset($data['error'])){
            $this->link->rollBack();
            $error = $this->errores->error('Error al insertar datos',$data);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $this->link->commit();
        header('Location: index.php?seccion=usuario&accion=lista&session_id='.SESSION_ID);
        exit;

    }

}
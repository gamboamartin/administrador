<?php
namespace tests\base\controller;

use base\controller\controler;
use base\controller\init;
use base\seguridad;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\controllers\controlador_adm_seccion;
use gamboamartin\controllers\controlador_adm_session;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\seccion;
use stdClass;


class initTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();

        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/administrador/config/generales.php';
        $this->paths_conf->database = '/var/www/html/administrador/config/database.php';
        $this->paths_conf->views = '/var/www/html/administrador/config/views.php';
    }

    public function test_aplica_view()
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $init = new init();
        $init = new liberator($init);

        $filtro['adm_accion_basica.descripcion'] = 'a';
        $del = (new adm_accion_basica($this->link))->elimina_con_filtro_and($filtro);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_campo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_elemento_lista($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion_grupo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_accion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_bitacora($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion_pertenece($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new adm_seccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $adm_seccion_ins['id'] = 2;
        $adm_seccion_ins['descripcion'] = 'adm_session';
        $adm_seccion_ins['adm_menu_id'] = '1';
        $adm_seccion_ins['adm_namespace_id'] = '1';
        $alta = (new adm_seccion($this->link))->alta_registro($adm_seccion_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $adm_accion_ins['descripcion'] = 'login';
        $adm_accion_ins['titulo'] = 'login';
        $adm_accion_ins['adm_seccion_id'] = 2;
        $adm_accion_ins['icono'] = '';
        $adm_accion_ins['muestra_icono_btn'] = 'inactivo';
        $alta = (new adm_accion($this->link))->alta_registro($adm_accion_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $seguridad = new seguridad();
        $resultado = $init->aplica_view($this->link, $seguridad);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_asigna_session_get(){

        errores::$error = false;

        $init = new init();
        //$init = new liberator($init);

        $resultado = $init->asigna_session_get();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric($resultado['session_id']);
        errores::$error = false;
    }

    public function test_controller(): void
    {

        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $init = new init();
        //$init = new liberator($init);

        $seccion = 'adm_seccion';
        $paths_conf = new stdClass();
        $paths_conf->generales = '/var/www/html/administrador/config/generales.php';
        $paths_conf->database = '/var/www/html/administrador/config/database.php';
        $paths_conf->views = '/var/www/html/administrador/config/views.php';
        $resultado = $init->controller($this->link, $seccion, paths_conf: $paths_conf);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_data_include_base()
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $init = new init();
        $init = new liberator($init);

        $accion = 'lista';
        $seccion = 'b';
        $ctl = (new controlador_adm_seccion(link: $this->link, paths_conf: $this->paths_conf));

        $resultado = $init->data_include_base($accion, $ctl, $seccion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado->existe);
        $this->assertStringContainsStringIgnoringCase("vista_base/lista.php",$resultado->include_action);
        errores::$error = false;
    }

    public function test_existe_include(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $include_action = '';
        $resultado = $init->existe_include($include_action);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error include_action esta vacio',$resultado['mensaje_limpio']);

        errores::$error = false;

        $include_action = 'a';
        $resultado = $init->existe_include($include_action);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;

        $include_action = '/var';
        $resultado = $init->existe_include($include_action);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_genera_salida(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $ctl = (new controlador_adm_seccion(link: $this->link, paths_conf: $this->paths_conf));

        $include_action = 'a';
        $resultado = $init->genera_salida($ctl, $include_action);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_include_action(){

        errores::$error = false;
        unset($_SESSION);
        $init = new init();
        $init = new liberator($init);
        $ctl = new controlador_adm_session(link:$this->link, paths_conf: $this->paths_conf);
        $seguridad = (new seguridad());
        $seguridad->seccion = 'xxx';
        $resultado = $init->include_action(true, $ctl, $seguridad);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener include local',$resultado['mensaje']);
        errores::$error = false;
    }

    public function test_include_action_local(){

        errores::$error = false;
        unset($_SESSION);
        $init = new init();
        $init = new liberator($init);

        $accion = 'a';
        $seccion = 'a';
        $resultado = $init->include_action_local($accion, $seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('./views/a/a.php',$resultado);
        errores::$error = false;
    }

    public function test_include_action_local_base(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $accion = 'a';
        $resultado = $init->include_action_local_base($accion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('./views/vista_base/a.php',$resultado);
        errores::$error = false;
    }

    public function test_include_action_local_base_data(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $ctl = new controlador_adm_session(link:$this->link, paths_conf: $this->paths_conf);

        $accion = '';
        $resultado = $init->include_action_local_base_data($accion, $ctl);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error la $accion esta vacia',$resultado['mensaje_limpio']);

        errores::$error = false;


        $accion = 'a';
        $resultado = $init->include_action_local_base_data($accion, $ctl);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_include_action_local_data()
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $init = new init();
        $init = new liberator($init);
        $ctl = new controlador_adm_session(link:$this->link, paths_conf: $this->paths_conf);

        $accion = 'f';
        $seccion = 'a';
        $resultado = $init->include_action_local_data($accion, $ctl, $seccion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('./views/a/f.php',$resultado->include_action);
        errores::$error = false;
    }

    public function test_include_action_template(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $accion = 'v';
        $seccion = 'a';
        $resultado = $init->include_action_template($accion, $seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('/var/www/html/administrador/views/a/v.php',$resultado);

    }

    public function test_include_action_template_base(){

        errores::$error = false;
        unset($_SESSION);
        $init = new init();
        $init = new liberator($init);

        $accion = 'a';
        $resultado = $init->include_action_template_base($accion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('views/vista_base/a.php',$resultado);
        errores::$error = false;
    }

    public function test_include_action_template_base_data(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $accion = 'a';

        $ctl = new controlador_adm_session(link:$this->link, paths_conf: $this->paths_conf);

        $resultado = $init->include_action_template_base_data($accion, $ctl);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_include_action_template_data(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $accion = 's';
        $seccion = 'a';
        $ctl = new controlador_adm_session(link:$this->link, paths_conf: $this->paths_conf);
        $resultado = $init->include_action_template_data($accion, $ctl, $seccion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_init_data_controler(){

        errores::$error = false;

        $init = new init();
        //$init = new liberator($init);
        $controler = new controler($this->link);
        $resultado = $init->init_data_controler($controler);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_init_for_ws(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);


        $resultado = $init->init_for_ws();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertFalse($resultado->header);
        $this->assertTrue($resultado->ws);
        $this->assertFalse($resultado->view);
        errores::$error = false;
    }

    public function test_include_template(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $accion = 'alta';
        $seccion = 'a';
        $ctl = new controlador_adm_session(link:$this->link, paths_conf: $this->paths_conf);
        $resultado = $init->include_template($accion, $ctl, $seccion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_include_template_base(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);
        $ctl = new controlador_adm_session(link:$this->link, paths_conf: $this->paths_conf);
        $accion = 'a';

        $resultado = $init->include_template_base($accion, $ctl);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error no existe la view',$resultado['mensaje_limpio']);

        errores::$error = false;


        $accion = 'lista';

        $resultado = $init->include_template_base($accion, $ctl);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('/var/www/html/administrador/views/vista_base/lista.php',$resultado->include_action);
        errores::$error = false;
    }

    public function test_init_params(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $resultado = $init->init_params();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado->ws);
        $this->assertTrue($resultado->header);
        $this->assertNotTrue($resultado->view);
        errores::$error = false;
    }

    public function test_key_select_txt(){

        errores::$error = false;

        $init = new init();
        //$init = new liberator($init);

        $cols = -1;
        $key = 'a';
        $keys_selects = array();
        $place_holder = '';
        $resultado = $init->key_select_txt($cols, $key, $keys_selects, $place_holder);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(-1,$resultado['a']->cols);
        errores::$error = false;
    }

    public function test_maqueta_key_select_input(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $selects = array();
        $name_model = 'a';
        $namespace_paquete = 'c';
        $resultado = $init->maqueta_key_select_input($selects, $name_model, $namespace_paquete);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado['a_id']->name_model);
        $this->assertEquals('c\models',$resultado['a_id']->namespace_model);
        errores::$error = false;
    }

    public function test_model_init_campos(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);
        $campos_view = array();
        $key = 'a';
        $type = 'v';
        $resultado = $init->model_init_campos($campos_view, $key, $type);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('v',$resultado['a']['type']);
        errores::$error = false;
    }

    public function test_model_init_campos_input(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $campos_view = array();
        $key = 'a';
        $campos_view[] = '';
        $resultado = $init->model_init_campos_input($campos_view, $key, 'inputs');
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('inputs',$resultado['a']['type']);
        errores::$error = false;
    }

    public function test_model_init_campos_inputs(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $campos_view = array();
        $keys = array();
        $type = 'a';
        $keys[] = 'a';
        $resultado = $init->model_init_campos_inputs($campos_view, $keys, $type);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado['a']['type']);
        errores::$error = false;
    }

    public function test_model_init_campos_select(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $campos_view = array();
        $key = 'a';
        $link = $this->link;
        $name_model = 'adm_seccion';
        $namespace_model = 'gamboamartin\\administrador\\models';
        $resultado = $init->model_init_campos_select($campos_view, $key, $link, $name_model, $namespace_model);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_model_init_campos_selects(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $campos_view = array();
        $keys = array();
        $link = $this->link;

        $keys['a']= new stdClass();
        $keys['a']->name_model= 'adm_accion';
        $keys['a']->namespace_model= 'gamboamartin\\administrador\\models';
        $resultado = $init->model_init_campos_selects($campos_view, $keys, $link);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_model_init_campos_template(){

        errores::$error = false;

        $init = new init();
        //$init = new liberator($init);
        $campos_view = array();
        $keys = new stdClass();
        $link = $this->link;
        $campos_view[]['namespace_model'] = '';
        $keys->selects['a'] = new stdClass();
        $keys->selects['a']->namespace_model = 'gamboamartin\administrador\models';
        $keys->selects['a']->name_model = 'adm_session';
        $resultado = $init->model_init_campos_template($campos_view, $keys, $link);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_modela_input()
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $init = new init();
        $init = new liberator($init);

        $atributo = 'select';
        $campos_view = array();
        $keys = new stdClass();
        $resultado = $init->modela_input($atributo, $campos_view, $keys);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }

    public function test_modela_inputs_attr()
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $init = new init();
        $init = new liberator($init);

        $atributos = array();
        $campos_view = array();
        $keys = new stdClass();
        $atributos[] = 'a';
        $resultado = $init->modela_inputs_attr($atributos, $campos_view, $keys);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;

    }

    public function test_modela_inputs_campos()
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $init = new init();
        $init = new liberator($init);


        $campos_view = array();
        $keys = new stdClass();
        $keys->inputs[] = 'z';
        $keys->telefonos[] = 'a';
        $resultado = $init->modela_inputs_campos($campos_view, $keys);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('inputs',$resultado['z']['type']);
        $this->assertEquals('telefonos',$resultado['a']['type']);
        errores::$error = false;

    }


    public function test_name_controler(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $seccion = 'adm_seccion';
        $resultado = $init->name_controler($seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('gamboamartin\controllers\controlador_adm_seccion',$resultado);
        errores::$error = false;
    }

    public function test_output_include(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $existe = false;
        $include_action = '';
        $resultado = $init->output_include($existe, $include_action);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_select_key_input()
    {

        errores::$error = false;

        $_SESSION['usuario_id'] = 2;
        $init = new init();
        //$init = new liberator($init);

        $init_data = array();
        $init_data['x'] = 'y';
        $selects = array();

        $resultado = $init->select_key_input($init_data, $selects);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado['x_id']->name_model);
        $this->assertEquals('y\models',$resultado['x_id']->namespace_model);

        errores::$error = false;

    }

    /**
     * @throws JsonException
     */
    public function test_session_id(){

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

        $resultado = $init->session_id();

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsNumeric($resultado);

        errores::$error = false;
    }






}
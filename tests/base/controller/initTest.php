<?php
namespace tests\base\controller;

use base\controller\controler;
use base\controller\init;
use base\controller\normalizacion;
use base\seguridad;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\seccion;
use stdClass;


class initTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_aplica_view()
    {

        errores::$error = false;

        $init = new init();
        $init = new liberator($init);

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

        $include_action = 'a';
        $resultado = $init->genera_salida($include_action);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }



    public function test_include_action(){

        errores::$error = false;
        unset($_SESSION);
        $init = new init();
        $init = new liberator($init);
        $seguridad = (new seguridad());
        $seguridad->seccion = 'xxx';
        $resultado = $init->include_action(true, $seguridad);
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

        $accion = '';
        $resultado = $init->include_action_local_base_data($accion);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertEquals('Error la $accion esta vacia',$resultado['mensaje_limpio']);

        errores::$error = false;


        $accion = 'a';
        $resultado = $init->include_action_local_base_data($accion);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
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
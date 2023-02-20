<?php
namespace gamboamartin\administrador\tests\base\ctl;

use base\controller\controlador_base;
use base\controller\controler;
use gamboamartin\administrador\ctl\normalizacion_ctl;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class
normalizacion_ctlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }





    public function test_asigna_registro_alta(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        //$nm = new liberator($nm);

        $controler = new controler($this->link);
        $registro = array();
        $resultado = $nm->asigna_registro_alta(controler: $controler,registro:  $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $controler->seccion no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;
        $controler = new controler($this->link);
        $controler->seccion = 'z';
        $controler->tabla = 'z';
        $registro = array();
        $registro['a'] = 1;

        $controler->modelo = new adm_seccion($this->link);

        $resultado = $nm->asigna_registro_alta(controler: $controler,registro:  $registro);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['a']);

        errores::$error = false;
        $controler = new controler($this->link);
        $controler->modelo = new adm_seccion($this->link);
        $controler->seccion = 'adm_seccion';
        $controler->tabla = 'adm_seccion';
        $registro = array();
        $registro['a'] = 1;

        $resultado = $nm->asigna_registro_alta(controler: $controler,registro:  $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['a']);
        errores::$error = false;
    }

    public function test_clase_model(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        //$nm = new liberator($nm);

        $controler = new controler($this->link, '', '');
        $resultado = $nm->clase_model($controler);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error this->seccion esta vacio", $resultado['mensaje']);

        errores::$error = false;
        $nm = new normalizacion_ctl();
        //$nm = new liberator($nm);

        $controler = new controler($this->link, '', '');
        $controler->seccion = 'a';
        $resultado = $nm->clase_model($controler);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("models\\a", $resultado);
        errores::$error = false;
    }






    public function test_init_upd_base(): void
    {
        errores::$error = false;
        $nm = new normalizacion_ctl();
        //$nm = new liberator($nm);
        $controler = new controler($this->link, '', '');

        $registro = array();
        //$registro[] = '';
        $resultado = $nm->init_upd_base($controler, $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);

        errores::$error = false;

        $registro = array();
        $registro[] = '';
        $_POST = array();
        $_POST[] = '';
        $resultado = $nm->init_upd_base($controler, $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error la seccion no puede venir vacia", $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $registro[] = '';
        $_POST = array();
        $_POST[] = '';
        $controler->seccion = 'a';
        $resultado = $nm->init_upd_base($controler, $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al procesar registros", $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $registro[] = 'a';
        $_POST = array();
        $_POST['a'] = 'a';
        $controler->seccion = 'a';
        $resultado = $nm->init_upd_base($controler, $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al procesar registros", $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $registro['a'] = 'a';
        $_POST = array();
        $_POST['a'] = 'a';
        $controler->seccion = 'a';
        $controler->modelo = new adm_seccion($this->link);
        $resultado = $nm->init_upd_base($controler, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado['a']);
        errores::$error = false;
    }

    public function test_genera_registros_envio(): void
    {
        errores::$error = false;
        $nm = new normalizacion_ctl();
        $nm = new liberator($nm);
        $controler = new controler($this->link, '', '');

        $registros = array();
        $resultado = $nm->genera_registros_envio($controler, $registros);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $controler = new controler($this->link, '', '');
        $controler->seccion = 'x';
        $controler->modelo = new adm_seccion($this->link);
        $registros = array();
        $registros['a'] = '';
        $resultado = $nm->genera_registros_envio($controler, $registros);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);
        errores::$error = false;
    }

    public function test_init_controler(){
        errores::$error = false;
        $_SESSION['usuario_id'] = 2;
        $nm = new normalizacion_ctl();
        //$nm = new liberator($nm);

        $mod = new adm_seccion($this->link);
        $paths_conf = new stdClass();
        $paths_conf->generales = '/var/www/html/administrador/config/generales.php';
        $paths_conf->database = '/var/www/html/administrador/config/database.php';
        $paths_conf->views = '/var/www/html/administrador/config/views.php';
        $controler = new controlador_base(link: $this->link,modelo: $mod, paths_conf: $paths_conf);
        $resultado = $nm->init_controler(controler: $controler);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("adm_session", $resultado->tabla);
        errores::$error = false;
    }

    public function test_init_registro(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        $nm = new liberator($nm);

        $controler = new controler($this->link, '', '');
        $registro = array();
        $controler->seccion = 'adm_accion';
        $controler->tabla = 'a';
        $registro[] = '';
        $resultado = $nm->init_registro($controler, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado[0]);
        errores::$error = false;
    }

    public function test_limpia_btn_post(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        $nm = new liberator($nm);

        $registro = array();
        $resultado = $nm->limpia_btn_post($registro);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $registro = array('btn_action_next'=>'a');
        $resultado = $nm->limpia_btn_post($registro);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }

    public function test_limpia_post_alta(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        //$nm = new liberator($nm);
        unset($_POST);

        $resultado = $nm->limpia_post_alta();

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

    }

    public function test_limpia_registro_en_proceso(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        //$nm = new liberator($nm);

        $resultado = $nm->limpia_registro_en_proceso();

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotContains('registro_en_proceso',$resultado);

        errores::$error = false;
        $_SESSION['registro_en_proceso'] = 'X';
        $resultado = $nm->limpia_registro_en_proceso();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotContains('registro_en_proceso',$resultado);

        errores::$error = false;
    }

    public function test_maqueta_data_galeria(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        //$nm = new liberator($nm);

        $controler = new controler($this->link, '', '');
        $r_fotos = array();
        $tabla = '';
        $resultado = $nm->maqueta_data_galeria(controler: $controler,r_fotos:  $r_fotos,tabla:  $tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error no existe registros en r_fotos", $resultado['mensaje']);

        errores::$error = false;

        $controler = new controler($this->link, '', '');
        $r_fotos = array();
        $tabla = '';
        $r_fotos['registros'] = '';
        $resultado = $nm->maqueta_data_galeria(controler: $controler,r_fotos:  $r_fotos,tabla:  $tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error registros en r_fotos debe ser un array", $resultado['mensaje']);

        errores::$error = false;

        $controler = new controler($this->link, '', '');
        $r_fotos = array();
        $tabla = '';
        $r_fotos['registros'] = array();
        $resultado = $nm->maqueta_data_galeria(controler: $controler,r_fotos:  $r_fotos,tabla:  $tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error tabla no puede venir vacia", $resultado['mensaje']);

        errores::$error = false;

        $controler = new controler($this->link, '', '');
        $r_fotos = array();
        $tabla = 'a';
        $r_fotos['registros'] = array();
        $resultado = $nm->maqueta_data_galeria(controler: $controler,r_fotos:  $r_fotos,tabla:  $tabla);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_name_class(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        $nm = new liberator($nm);

        $seccion = '';
        $resultado = $nm->name_class(seccion: $seccion);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error seccion no puede venir vacia", $resultado['mensaje']);

        errores::$error = false;

        $seccion = 'a';
        $resultado = $nm->name_class(seccion: $seccion);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("models\\a", $resultado);


        errores::$error = false;
    }

    public function test_obten_key_envio(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        $nm = new liberator($nm);

        $controler = new controler($this->link);
        $key = '';
        $resultado = $nm->obten_key_envio($controler, $key);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error la seccion no puede venir vacia", $resultado['mensaje']);

        errores::$error = false;

        $controler = new controler($this->link);
        $controler->seccion = 'a';
        $key = '';
        $resultado = $nm->obten_key_envio($controler, $key);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la $key no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;

        $controler = new controler($this->link);

        $controler->seccion = 'a';
        $key = 'c';
        $controler->modelo = new adm_seccion($this->link);
        $resultado = $nm->obten_key_envio($controler, $key);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('c',$resultado);
        errores::$error = false;
    }

    public function test_procesa_registros_alta(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        $nm = new liberator($nm);

        $controler = new controler($this->link);
        $registro = array();
        $registro['a'] = 'a';
        $controler->seccion = 'a';
        $controler->modelo = new adm_seccion($this->link);
        $resultado = $nm->procesa_registros_alta($controler, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado['a']);
        errores::$error = false;
    }


    public function test_trim_arreglo(){
        errores::$error = false;
        $nm = new normalizacion_ctl();
        $nm = new liberator($nm);

        $arreglo = array();
        $resultado = $nm->trim_arreglo($arreglo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error el arreglo no puede venir vacio", $resultado['mensaje']);

        errores::$error = false;

        $arreglo = array();
        $arreglo[] = '';
        $resultado = $nm->trim_arreglo($arreglo);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }


}
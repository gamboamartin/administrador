<?php
namespace tests\base\controller;

use base\controller\controlador_base;
use base\controller\controler;
use base\controller\normalizacion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_seccion;
use stdClass;


class normalizacionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_asigna_filtro_btn_get(){
        errores::$error = false;
        $nm = new normalizacion();
        $nm = new liberator($nm);

        $filtro_default_btn = array();
        $resultado = $nm->asigna_filtro_btn_get(filtro_default_btn: $filtro_default_btn);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error validar filtro_default_btn", $resultado['mensaje']);

        errores::$error = false;
        $filtro_default_btn = array();
        $filtro_default_btn[] = '';
        $resultado = $nm->asigna_filtro_btn_get(filtro_default_btn: $filtro_default_btn);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error validar filtro_default_btn", $resultado['mensaje']);

        errores::$error = false;
        $filtro_default_btn = array();
        $filtro_default_btn['tabla'] = '';
        $resultado = $nm->asigna_filtro_btn_get(filtro_default_btn: $filtro_default_btn);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error validar filtro_default_btn", $resultado['mensaje']);

        errores::$error = false;
        $filtro_default_btn = array();
        $filtro_default_btn['tabla'] = 'a';
        $resultado = $nm->asigna_filtro_btn_get(filtro_default_btn: $filtro_default_btn);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error validar filtro_default_btn", $resultado['mensaje']);

        errores::$error = false;
        $filtro_default_btn = array();
        $filtro_default_btn['tabla'] = 'a';
        $filtro_default_btn['valor_default'] = 'b';
        $resultado = $nm->asigna_filtro_btn_get(filtro_default_btn: $filtro_default_btn);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("b", $resultado['filtro_btn']['a.id']);

        errores::$error = false;
    }

    public function test_asigna_filtros(){
        errores::$error = false;
        $nm = new normalizacion();
        $nm = new liberator($nm);
;
        unset($_GET);
        $filtro_btn = array();
        $filtro_default_btn = array();
        $filtro_default_btn['tabla'] = 'a';
        $filtro_default_btn['valor_default'] = 'a';
        $resultado = $nm->asigna_filtros($filtro_btn, $filtro_default_btn);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("a", $resultado['a.id']);
        errores::$error = false;
    }

    public function test_asigna_registro_alta(){
        errores::$error = false;
        $nm = new normalizacion();
        //$nm = new liberator($nm);

        $controler = new controler();
        $registro = array();
        $resultado = $nm->asigna_registro_alta(controler: $controler,registro:  $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $controler->seccion no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;
        $controler = new controler();
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
        $controler = new controler();
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
        $nm = new normalizacion();
        //$nm = new liberator($nm);

        $controler = new controler('', '');
        $resultado = $nm->clase_model($controler);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error this->seccion esta vacio", $resultado['mensaje']);

        errores::$error = false;
        $nm = new normalizacion();
        //$nm = new liberator($nm);

        $controler = new controler('', '');
        $controler->seccion = 'a';
        $resultado = $nm->clase_model($controler);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("models\\a", $resultado);
        errores::$error = false;
    }

    public function test_determina_filtro_btn(){
        errores::$error = false;
        $nm = new normalizacion();
        $nm = new liberator($nm);

        $filtro_default_btn = array();
        $filtro_btn = array();
        $filtro_default_btn['tabla'] = 'x';
        $filtro_default_btn['valor_default'] = '1';
        $resultado = $nm->determina_filtro_btn($filtro_btn, $filtro_default_btn);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['x.id']);
        errores::$error = false;
    }

    public function test_ejecuta_filtro(){
        errores::$error = false;
        $nm = new normalizacion();
        $nm = new liberator($nm);

        $filtro_default_btn = array();
        $controler = new controler('', '');
        $controler->tabla = 'x';
        $resultado = $nm->ejecuta_filtro($controler, $filtro_default_btn);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertFalse($resultado);

        errores::$error = false;

        $filtro_default_btn = array();
        $controler = new controler('', '');
        $controler->tabla = 'x';

        unset($_SESSION['filtros'][$controler->tabla]);
        unset($_GET['filtro_btn']);
        $filtro_default_btn['valor_default'] = 1;

        $resultado = $nm->ejecuta_filtro($controler, $filtro_default_btn);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }

    public function test_filtro_btn(){
        errores::$error = false;
        $nm = new normalizacion();
        //$nm = new liberator($nm);

        $controler = new controler('', '');
        $resultado = $nm->filtro_btn($controler);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_init_upd_base(): void
    {
        errores::$error = false;
        $nm = new normalizacion();
        //$nm = new liberator($nm);
        $controler = new controler('', '');

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
        $nm = new normalizacion();
        $nm = new liberator($nm);
        $controler = new controler('', '');

        $registros = array();
        $resultado = $nm->genera_registros_envio($controler, $registros);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $controler = new controler('', '');
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
        $nm = new normalizacion();
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
        $nm = new normalizacion();
        $nm = new liberator($nm);

        $controler = new controler('', '');
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
        $nm = new normalizacion();
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
        $nm = new normalizacion();
        //$nm = new liberator($nm);
        unset($_POST);

        $resultado = $nm->limpia_post_alta();

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

    }

    public function test_limpia_registro_en_proceso(){
        errores::$error = false;
        $nm = new normalizacion();
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
        $nm = new normalizacion();
        //$nm = new liberator($nm);

        $controler = new controler('', '');
        $r_fotos = array();
        $tabla = '';
        $resultado = $nm->maqueta_data_galeria(controler: $controler,r_fotos:  $r_fotos,tabla:  $tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error no existe registros en r_fotos", $resultado['mensaje']);

        errores::$error = false;

        $controler = new controler('', '');
        $r_fotos = array();
        $tabla = '';
        $r_fotos['registros'] = '';
        $resultado = $nm->maqueta_data_galeria(controler: $controler,r_fotos:  $r_fotos,tabla:  $tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error registros en r_fotos debe ser un array", $resultado['mensaje']);

        errores::$error = false;

        $controler = new controler('', '');
        $r_fotos = array();
        $tabla = '';
        $r_fotos['registros'] = array();
        $resultado = $nm->maqueta_data_galeria(controler: $controler,r_fotos:  $r_fotos,tabla:  $tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error tabla no puede venir vacia", $resultado['mensaje']);

        errores::$error = false;

        $controler = new controler('', '');
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
        $nm = new normalizacion();
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
        $nm = new normalizacion();
        $nm = new liberator($nm);

        $controler = new controler();
        $key = '';
        $resultado = $nm->obten_key_envio($controler, $key);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error la seccion no puede venir vacia", $resultado['mensaje']);

        errores::$error = false;

        $controler = new controler();
        $controler->seccion = 'a';
        $key = '';
        $resultado = $nm->obten_key_envio($controler, $key);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error la $key no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;

        $controler = new controler();

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
        $nm = new normalizacion();
        $nm = new liberator($nm);

        $controler = new controler();
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
        $nm = new normalizacion();
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
<?php
namespace tests\base\controller;

use base\controller\controler;
use base\controller\normalizacion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_seccion;
use models\seccion;


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

    public function test_asigna_registro_alta(){
        errores::$error = false;
        $nm = new normalizacion();
        //$nm = new liberator($nm);

        $controler = new controler();
        $registro = array();
        $resultado = $nm->asigna_registro_alta(controler: $controler,registro:  $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al limpiar registro", $resultado['mensaje']);

        errores::$error = false;
        $controler = new controler();
        $controler->seccion = 'z';
        $controler->tabla = 'z';
        $registro = array();
        $registro['a'] = 1;

        $resultado = $nm->asigna_registro_alta(controler: $controler,registro:  $registro);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("Error al limpiar registro", $resultado['mensaje']);

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

    public function test_init_controler(){
        errores::$error = false;
        $nm = new normalizacion();
        //$nm = new liberator($nm);

        $controler = new controler('', '');
        $resultado = $nm->init_controler(controler: $controler);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado->tabla);
        errores::$error = false;
    }

    public function test_limpia_post_alta(){
        errores::$error = false;
        $nm = new normalizacion();
        //$nm = new liberator($nm);

        $filtro_default_btn = array();
        $resultado = $nm->limpia_post_alta();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

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
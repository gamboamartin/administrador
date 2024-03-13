<?php
namespace tests\orm;

use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class adm_accion_grupoTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_filtro_and(){

        errores::$error = false;
        $modelo = new adm_accion_grupo($this->link);
        //$modelo = new liberator($modelo);

        $extra_join = array();
        $extra_join['adm_grupo']['key'] = 'id';
        $extra_join['adm_grupo']['enlace'] = 'adm_grupo';
        $extra_join['adm_grupo']['key_enlace'] = 'id';
        $extra_join['adm_grupo']['renombre'] = 'a';

        $extra_join['adm_accion']['key'] = 'id';
        $extra_join['adm_accion']['enlace'] = 'adm_accion';
        $extra_join['adm_accion']['key_enlace'] = 'id';
        $extra_join['adm_accion']['renombre'] = 'b';

        $resultado = $modelo->filtro_and(extra_join: $extra_join);
        //print_r($resultado);exit;

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_grupos_por_accion(): void
    {

        errores::$error = false;
        $modelo = new adm_accion_grupo($this->link);
        //$modelo = new liberator($modelo);


        $adm_accion_id= 1;
        $resultado = $modelo->grupos_por_accion($adm_accion_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $adm_accion_id= 4;
        $resultado = $modelo->grupos_por_accion($adm_accion_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);

        errores::$error = false;
    }

    public function test_id_preferido_detalle(): void
    {

        errores::$error = false;
        $modelo = new adm_accion_grupo($this->link);
        //$modelo = new liberator($modelo);


        $entidad_preferida= 'adm_grupo';
        $resultado = $modelo->id_preferido_detalle($entidad_preferida);
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2,$resultado);
        errores::$error = false;
    }

    public function test_obten_accion_permitida(): void
    {

        errores::$error = false;
        $modelo = new adm_accion_grupo($this->link);
        //$modelo = new liberator($modelo);

        unset($_SESSION['grupo_id']);
        $seccion_menu_id= -1;
        $resultado = $modelo->obten_accion_permitida($seccion_menu_id);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar session', $resultado['mensaje']);

        errores::$error = false;

        $_SESSION['grupo_id'] = 2;

        $seccion_menu_id= -1;
        $resultado = $modelo->obten_accion_permitida($seccion_menu_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

    }


}


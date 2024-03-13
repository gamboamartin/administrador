<?php
namespace tests\base;


use base\orm\_defaults;
use gamboamartin\administrador\models\adm_grupo;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;




class _defaultsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_filtro_default()
    {

        errores::$error = false;
        $def = new _defaults();
        $def = new liberator($def);
        $entidad = new adm_grupo(link: $this->link);
        $row = array();
        $row['codigo'] = 'a';

        $resultado = $def->filtro_default($entidad, $row);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('a',$resultado['adm_grupo.codigo']);

        errores::$error = false;
    }



}
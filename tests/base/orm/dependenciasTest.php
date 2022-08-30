<?php
namespace tests\base\orm;

use base\orm\dependencias;
use base\orm\estructuras;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class dependenciasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_data_dependientes(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        //$st = new liberator($st);
        $link = $this->link;
        $parent_id = 1;
        $tabla = 'adm_menu';
        $tabla_children = 'adm_seccion';
        $resultado = $dep->data_dependientes($link, $parent_id, $tabla, $tabla_children);

        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }




}
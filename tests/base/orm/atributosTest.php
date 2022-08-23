<?php
namespace tests\base;

use base\controller\normalizacion;
use base\orm\activaciones;
use base\orm\atributos;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\adm_accion_grupo;
use models\adm_campo;
use models\adm_dia;
use models\atributo;


class atributosTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_atributos()
    {
        errores::$error = false;
        $attr = new atributos();
        //$act = new liberator($act);

        $link = $this->link;
        $tabla = '';
        $resultado = $attr->atributos($link, $tabla);
       
        errores::$error = false;
    }


}
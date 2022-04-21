<?php
namespace tests\base;

use base\conexion;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class conexionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }


}
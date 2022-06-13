<?php
namespace tests\base\orm;

use base\orm\sql;
use base\orm\sql_bass;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;



class sqlTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_describe_table(): void
    {
        errores::$error = false;
        $sql = new sql();
        //$sql = new liberator($sql);

        $tabla = '';
        $resultado = $sql->describe_table($tabla);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla esta vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'a';
        $resultado = $sql->describe_table($tabla);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('DESCRIBE a',$resultado);
        errores::$error = false;
    }



}
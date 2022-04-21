<?php
namespace tests\base;

use base\conexion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class conexionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    /**
     * @throws JsonException
     */
    public function test_valida_conf_file(){
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';

        $cnx = new conexion($paths);
        $cnx = new liberator($cnx);
        $tipo_conf = '';
        $resultado = $cnx->valida_conf_file($paths, $tipo_conf);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tipo_conf esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $tipo_conf = 'a';
        $resultado = $cnx->valida_conf_file($paths, $tipo_conf);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe el archivo config/a.php',$resultado['mensaje']);

        errores::$error = false;

        $tipo_conf = 'generales';
        $resultado = $cnx->valida_conf_file($paths, $tipo_conf);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }


}
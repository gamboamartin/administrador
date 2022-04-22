<?php
namespace tests\validacion\confs;

use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;
use validacion\confs\configuraciones;


class configuracionesTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    /**
     * @throws JsonException
     */
    public function test_valida_conf(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';

        $val = new configuraciones();
        $val = new liberator($val);
        $tipo_conf = '';
        $resultado = $val->valida_conf($paths, $tipo_conf);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tipo_conf esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $tipo_conf = 'z';
        $resultado = $val->valida_conf($paths, $tipo_conf);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar z.php',$resultado['mensaje']);

        errores::$error = false;

        $tipo_conf = 'generales';
        $resultado = $val->valida_conf($paths, $tipo_conf);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }

    /**
     * @throws JsonException
     */
    public function test_valida_conf_composer(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';

        $val = new configuraciones();
        $val = new liberator($val);
        $tipo_conf = '';
        $resultado = $val->valida_conf_composer($tipo_conf);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tipo_conf esta vacio',$resultado['mensaje']);

        errores::$error = false;
        $tipo_conf = 'a';
        $resultado = $val->valida_conf_composer($tipo_conf);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Agrega el registro',$resultado['mensaje']);

        errores::$error = false;
        $tipo_conf = 'generales';
        $resultado = $val->valida_conf_composer($tipo_conf);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);


        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_valida_conf_file(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';

        $val = new configuraciones();
        $val = new liberator($val);
        $tipo_conf = '';
        $resultado = $val->valida_conf_file($paths, $tipo_conf);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tipo_conf esta vacio',$resultado['mensaje']);

        errores::$error = false;

        $tipo_conf = 'a';
        $resultado = $val->valida_conf_file($paths, $tipo_conf);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe el archivo config/a.php',$resultado['mensaje']);

        errores::$error = false;

        $tipo_conf = 'generales';
        $resultado = $val->valida_conf_file($paths, $tipo_conf);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_valida_confs(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $val = new configuraciones();
        //$cnx = new liberator($cnx);
        $paths->generales = '/var/www/html/administrador/config/generale.php';
        $paths->database = '/var/www/html/administrador/config/database.php';
        $resultado = $val->valida_confs($paths);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar generales.php',$resultado['mensaje']);

        errores::$error = false;

        $paths = new stdClass();

        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';
        $resultado = $val->valida_confs($paths);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

    }
}
<?php
namespace tests\base;

use base\conexion;
use config\database;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use stdClass;


class conexionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_asigna_parametros_query(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';
        $paths->views = '/var/www/html/administrador/config/views.php';

        $cnx = new conexion($paths);
        $cnx = new liberator($cnx);

        $set_name = 'utf8';
        $sql_mode = '';
        $time_out = '1';
        $resultado = $cnx->asigna_parametros_query(link: conexion::$link, set_name: $set_name,sql_mode:  $sql_mode,time_out:  $time_out);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_asigna_set_names(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';
        $paths->views = '/var/www/html/administrador/config/views.php';

        $cnx = new conexion($paths);
        $cnx = new liberator($cnx);

        $set_name = 'utf8';
        $resultado = $cnx->asigna_set_names(conexion::$link, $set_name);

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);


    }

    /**
     */
    public function test_asigna_sql_mode(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';
        $paths->views = '/var/www/html/administrador/config/views.php';

        $cnx = new conexion($paths);
        $cnx = new liberator($cnx);

        $sql_mode = '';
        $resultado = $cnx->asigna_sql_mode($this->link, $sql_mode);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    /**
     */
    public function test_asigna_timeout(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';
        $paths->views = '/var/www/html/administrador/config/views.php';

        $cnx = new conexion($paths);
        $cnx = new liberator($cnx);

        $time_out = -1;
        $resultado = $cnx->asigna_timeout($this->link, $time_out);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    /**
     * @throws JsonException
     */
    public function test_conecta(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';
        $paths->views = '/var/www/html/administrador/config/views.php';

        $cnx = new conexion($paths);
        $cnx = new liberator($cnx);
        $conf_database = new database();
        $conf_database->db_user = '';
        $resultado = $cnx->conecta($conf_database, 'MYSQL');

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf_database',$resultado['mensaje']);

        errores::$error = false;

        $conf_database->db_user = 'x';
        $conf_database = new database();
        $conf_database->db_user = '';
        $resultado = $cnx->conecta($conf_database, 'MYSQL');
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar conf_database',$resultado['mensaje']);

        errores::$error = false;

        $conf_database = new database();

        $resultado = $cnx->conecta($conf_database, 'MYSQL');
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);


        errores::$error = false;
    }

    public function test_conexion(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';
        $paths->views = '/var/www/html/administrador/config/views.php';

        $cnx = new conexion($paths);
        $cnx = new liberator($cnx);

        $conf_database = new stdClass();
        $motor = 'MYSQL';
        $conf_database->db_user = (new database())->db_user;
        $conf_database->db_host = (new database())->db_host;
        $conf_database->db_name = (new database())->db_name;
        $conf_database->db_password = (new database())->db_password;
        $conf_database->set_name = (new database())->set_name;
        $conf_database->time_out = (new database())->time_out;
        $conf_database->sql_mode = (new database())->sql_mode;
        $resultado = $cnx->conexion($conf_database, $motor);


        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }


    public function test_usa_base_datos(): void
    {
        errores::$error = false;

        $paths = new stdClass();


        $paths->generales = '/var/www/html/administrador/config/generales.php';
        $paths->database = '/var/www/html/administrador/config/database.php';
        $paths->views = '/var/www/html/administrador/config/views.php';

        $cnx = new conexion($paths);
        $cnx = new liberator($cnx);

        $db_name = 'administrador';
        $resultado = $cnx->usa_base_datos($this->link, $db_name);


        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }










}
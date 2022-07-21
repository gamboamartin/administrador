<?php
namespace tests\base\orm;

use base\orm\sql_bass;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;

class sql_bassTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_asigna_booleanos(): void
    {
        errores::$error = false;
        $sql = new sql_bass();
        $sql = new liberator($sql);

        $campo = array();
        $bools_asignar = array();
        $bools = array();
        $resultado = $sql->asigna_booleanos($bools, $bools_asignar, $campo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $campo = array();
        $bools_asignar = array();
        $bools = array();
        $bools_asignar[] = '';
        $resultado = $sql->asigna_booleanos($bools, $bools_asignar, $campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $bool no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;

        $campo = array();
        $bools_asignar = array();
        $bools = array();
        $bools_asignar[] = 'z';
        $campo['adm_elemento_lista_z'] = 'b';

        $resultado = $sql->asigna_booleanos($bools, $bools_asignar, $campo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_asigna_data_bool(): void
    {
        errores::$error = false;
        $sql = new sql_bass();
        $sql = new liberator($sql);

        $campo = array();
        $bool = '';
        $bools = array();
        $resultado = $sql->asigna_data_bool($bool, $bools, $campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $bool no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;

        $campo = array();
        $bool = 'a';
        $bools = array();
        $resultado = $sql->asigna_data_bool($bool, $bools, $campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo[adm_elemento_lista_a] debe existir', $resultado['mensaje']);

        errores::$error = false;

        $campo = array();
        $bool = 'a';
        $bools = array();
        $campo['adm_elemento_lista_a'] = 'z';
        $resultado = $sql->asigna_data_bool($bool, $bools, $campo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_coma_sql(){
        errores::$error = false;

        $sql = new sql_bass($this->link);
        //$modelo = new liberator($modelo);


        $columnas = '';
        $resultado = $sql->coma_sql(columnas: $columnas);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $columnas = 'x';
        $resultado = $sql->coma_sql(columnas: $columnas);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(' , ', $resultado);

        errores::$error = false;
    }

    public function test_genera_bools(): void
    {
        errores::$error = false;

        $sql = new sql_bass($this->link);
        $sql = new liberator($sql);


        $campo = array();
        $campos_obligatorios = array();
        $campo['adm_elemento_lista_campo'] = 'a';
        $campo['adm_elemento_lista_ln'] = 'a';
        $campo['adm_elemento_lista_con_label'] = 'a';
        $campo['adm_elemento_lista_select_vacio_alta'] = 'a';
        $resultado = $sql->genera_bools($campo, $campos_obligatorios);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_genera_estructura(): void
    {
        errores::$error = false;
        $sql = new sql_bass();
        $sql = new liberator($sql);

        $campo = array();
        $campo_envio = array();
        $estructura_bd = array();
        $tabla = '';

        $campo['adm_elemento_lista_campo'] = 'a';
        $campo['adm_elemento_lista_tabla_externa'] = 'a';

        $resultado = $sql->genera_estructura($campo, $campo_envio, $estructura_bd, $tabla);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_true_false(): void
    {
        errores::$error = false;
        $sql = new sql_bass();
        $sql = new liberator($sql);

        $campo = array();
        $key = '';
        $resultado = $sql->true_false($campo, $key);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error key no puede venir vacio', $resultado['mensaje']);


        errores::$error = false;


        $campo = array();
        $key = 'a';
        $resultado = $sql->true_false($campo, $key);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo[adm_elemento_lista_a]', $resultado['mensaje']);

        errores::$error = false;


        $campo = array();
        $key = 'a';
        $campo['adm_elemento_lista_a'] = 'activo';
        $resultado = $sql->true_false($campo, $key);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;


        $campo = array();
        $key = 'a';
        $campo['adm_elemento_lista_a'] = 'inactivo';
        $resultado = $sql->true_false($campo, $key);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;
    }

    public function test_valida_estructura(): void
    {
        errores::$error = false;
        $sql = new sql_bass();
        $sql = new liberator($sql);

        $campo = array();

        $resultado = $sql->valida_estructura($campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe $campo[adm_elemento_lista_campo]', $resultado['mensaje']);

        errores::$error = false;

        $campo = array();
        $campo['adm_elemento_lista_campo'] = 'a';
        $campo['adm_elemento_lista_tabla_externa'] = 'a';

        $resultado = $sql->valida_estructura($campo);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valor_extra(): void
    {
        errores::$error = false;

        $sql = new sql_bass($this->link);
        $sql = new liberator($sql);


        $campo = array();

        $campo['adm_elemento_lista_campo'] = 'a';

        $resultado = $sql->valor_extra($campo);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }







}
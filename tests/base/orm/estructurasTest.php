<?php
namespace tests\base\orm;

use base\orm\estructuras;
use base\orm\modelo_base;
use gamboamartin\administrador\models\adm_menu;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class estructurasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }



    public function test_asigna_dato_estructura(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $campo = array();
        $campo['Field'] = 'b';
        $campo['Null'] = 'b';
        $campo['Key'] = 'b';
        $campo['Type'] = 'b';
        $campo['Default'] = 'b';
        $campo['Extra'] = 'b';
        $keys_no_foraneas = array();
        $name_modelo = 'a';

        $resultado = $st->asigna_dato_estructura($campo,$keys_no_foraneas, $name_modelo);


        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

    }

    public function test_asigna_dato_foranea(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);

        $data = new stdClass();
        $estructura_bd = new stdClass();
        $foraneas = new stdClass();
        $modelo = 'a';
        $data->tabla_foranea = 'x';
        $resultado = $st->asigna_dato_foranea($data,$estructura_bd,$foraneas,$modelo);


        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

    }

    public function test_asigna_datos_estructura(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        //$st = new liberator($st);
        $resultado = $st->asigna_datos_estructura('administrador');


        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertTrue($resultado->adm_accion->data_campos->id->es_primaria);
        $this->assertTrue($resultado->adm_accion->data_campos->adm_seccion_id->es_foranea);
        $this->assertEquals('adm_seccion',$resultado->adm_accion->data_campos->adm_seccion_id->tabla_foranea);

        errores::$error = false;


        $resultado = $st->asigna_datos_estructura('administrador');
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertTrue($resultado->adm_accion_grupo->tiene_foraneas);
        $this->assertNotTrue($resultado->adm_dia->tiene_foraneas);

        errores::$error = false;


    }

    public function test_asigna_data_modelo(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $modelos = array();
        $name_db = 'a';
        $row = array();
        $row['Tables_in_a'] = 'a';
        $resultado = $st->asigna_data_modelo($modelos, $name_db, $row);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }

    public function test_asigna_datos_modelo(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $data_table = array();
        $keys_no_foraneas = array();
        $name_modelo = 'a';
        $data_table[0]['Field'] = 'x';
        $data_table[0]['Null'] = 'x';
        $data_table[0]['Key'] = 'x';
        $data_table[0]['Type'] = 'x';
        $data_table[0]['Default'] = 'x';
        $data_table[0]['Extra'] = 'x';
        $resultado = $st->asigna_datos_modelo($data_table,$keys_no_foraneas, $name_modelo);


        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

    }

    public function test_es_auto_increment(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $campo = array();
        $campo['Extra'] = 'auto_increment';
        $resultado = $st->es_auto_increment($campo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_es_foranea(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);

        $campo = array();
        $keys_no_foraneas = array();
        $campo['Field'] = '';
        $resultado = $st->es_foranea($campo, $keys_no_foraneas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        $this->assertNotTrue($resultado);
        errores::$error = false;
    }

    public function test_es_primaria(){
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $campo = array();
        $campo['Key'] = '';
        $resultado = $st->es_primaria($campo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        $this->assertNotTrue($resultado);

    }

    public function test_existe_entidad(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        //$st = new liberator($st);
        $entidad = 'adm_accion';
        $resultado = $st->existe_entidad($entidad);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        $this->assertTrue($resultado);

        errores::$error = false;
        $entidad = 'adm_accionxxxx';
        $resultado = $st->existe_entidad($entidad);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        $this->assertNotTrue($resultado);


        errores::$error = false;
    }

    public function test_genera_estructura(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);

        $keys_no_foraneas = array();
        $modelos = array();
        $modelos[] = 'adm_menu';
        $resultado = $st->genera_estructura($keys_no_foraneas,$modelos);


        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

    }

    public function test_genera_foranea(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);

        $data_campos = new stdClass();
        $estructura_bd = new stdClass();
        $foraneas = new stdClass();
        $modelo = 'a';
        $data_campos->a = new stdClass();
        $data_campos->a->es_foranea = true;
        $data_campos->a->tabla_foranea = true;
        $resultado = $st->genera_foranea($data_campos,$estructura_bd,$foraneas,$modelo);


        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

    }

    public function test_get_tables_sql(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $resultado = $st->get_tables_sql();
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }

    public function test_inicializa_campo(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $campo = array();
        $campo['Null'] = '';
        $campo['Key'] = '';
        $campo['Field'] = '';
        $keys_no_foraneas = array();
        $resultado = $st->inicializa_campo($campo,$keys_no_foraneas);

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

    }

    public function test_init_estructura_campo(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $campo = array();
        $name_modelo = 'a';
        $campo['Field'] = 'z';
        $resultado = $st->init_estructura_campo($campo, $name_modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

    }



    public function test_init_dato_estructura(){
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);

        $modelo_base = new modelo_base(link: $this->link);
        $modelo_base->tabla = 'adm_seccion';
        $name_modelo = 'adm_seccion';

        $resultado = $st->init_dato_estructura($modelo_base, $name_modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('id',$resultado[0]['Field']);
        $this->assertEquals('varchar(200)',$resultado[1]['Type']);
        $this->assertEquals('NO',$resultado[2]['Null']);
        $this->assertEquals('',$resultado[3]['Key']);
        $this->assertEquals('',$resultado[4]['Default']);
        $this->assertEquals('',$resultado[5]['Extra']);
        errores::$error = false;
    }

    public function test_init_estructura_modelo(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);

        $name_modelo = 'a';
        $resultado = $st->init_estructura_modelo($name_modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

    }

    public function test_key_table(){
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $resultado = $st->key_table('a');
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('Tables_in_a',$resultado);
        errores::$error = false;
    }

    public function test_maqueta_estructura(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $campo = array();
        $campo['Field'] = 'x';
        $campo['Type'] = 'x';
        $campo['Default'] = 'x';
        $campo['Extra'] = 'x';
        $campo['Key'] = 'x';
        $campo_init = new stdClass();
        $campo_init->tabla_foranea = '';
        $campo_init->es_foranea = '';
        $campo_init->permite_null = '';
        $campo_init->es_primaria = '';
        $campo_init->es_auto_increment = '';
        $name_modelo = 'a';

        $resultado = $st->maqueta_estructura($campo,$campo_init,$name_modelo);

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);

        errores::$error = false;

    }

    public function test_maqueta_modelos(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);

        $name_db = 'a';
        $rows = array();

        $resultado = $st->maqueta_modelos($name_db, $rows);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_modelos(){
        //$_GET['seccion'] = 'adm_campo';
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $resultado = $st->modelos('administrador');

        //print_r($resultado);exit;

        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('adm_campo',$resultado[5]);

        errores::$error = false;

    }

    public function test_permite_null(){
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $campo = array();
        $campo['Null'] = 'NO';
        $resultado = $st->permite_null($campo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        $this->assertNotTrue($resultado);

        errores::$error = false;

        $campo = array();
        $campo['Null'] = '';
        $resultado = $st->permite_null($campo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_tabla_foranea(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $campo = array();
        $keys_no_foraneas = array();
        $campo['Field'] = 'a_id';

        $resultado = $st->tabla_foranea($campo, $keys_no_foraneas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('a',$resultado);
        errores::$error = false;
    }



}
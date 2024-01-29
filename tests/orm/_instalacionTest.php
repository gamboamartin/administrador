<?php
namespace gamboamartin\administrador\tests\orm;

use base\orm\_base;
use gamboamartin\administrador\models\_base_accion;
use gamboamartin\administrador\models\_instalacion;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class _instalacionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_add_existente(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $adds = array();
        $atributos = new stdClass();
        $campo = 'a';
        $campos_origen[0]['Field'] = 'a';
        $table = 'z';


        $resultado = $ins->add_existente($adds, $atributos, $campo, $campos_origen, $table);
        //print_r($resultado);exit;
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

        $adds = array();
        $atributos = new stdClass();
        $atributos->tipo_dato = 'BIGINT';
        $campo = 'a';
        $campos_origen[0]['Field'] = 'a';
        $table = 'z';


        $resultado = $ins->add_existente($adds, $atributos, $campo, $campos_origen, $table);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        //$this->assertNotTrue("ALTER TABLE z MODIFY COLUMN a BIGINT (100);",$resultado->sql);
        errores::$error = false;

    }
    public function test_ajusta_tipo_dato(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $atributos = new stdClass();
        $atributos->tipo_dato = 'a';
        $resultado = $ins->ajusta_tipo_dato($atributos);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('A',$resultado->tipo_dato);

        errores::$error = false;
    }
    public function test_campo_double(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $campos = new stdClass();
        $name_campo = 'a';
        $resultado = $ins->campo_double($campos, $name_campo);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('double',$resultado->a->tipo_dato);
        $this->assertEquals('0',$resultado->a->default);
        $this->assertEquals('100,2',$resultado->a->longitud);

        errores::$error = false;
    }
    public function test_campos_double_default(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $campos = new stdClass();
        $name_campos = array();
        $name_campos[] = 'a';
        $resultado = $ins->campos_double_default($campos, $name_campos);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('double',$resultado->a->tipo_dato);
        $this->assertEquals('0',$resultado->a->default);
        $this->assertEquals('100,2',$resultado->a->longitud);

        errores::$error = false;
    }

    public function test_campos_origen(): void
    {

        errores::$error = false;

        $create = (new _instalacion(link: $this->link))->create_table_new('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }

        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $table = 'a';
        $resultado = $ins->campos_origen($table);
       // print_r($resultado);exit;

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('bigint',$resultado[0]['Type']);


        errores::$error = false;
    }
    public function test_describe_table(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $table = 'a';
        $resultado = $ins->describe_table($table);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }
    public function test_existe_campo_origen(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $campo_integrar = 'a';
        $campos_origen = array();
        $resultado = $ins->existe_campo_origen($campo_integrar, $campos_origen);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);
        errores::$error = false;

        $campo_integrar = 'a';
        $campos_origen[]['Field'] = 'a';
        $resultado = $ins->existe_campo_origen($campo_integrar, $campos_origen);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;

    }
    public function test_existe_indice_by_name(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $table = 'adm_seccion';
        $name_index = 'PRIMARY';
        $resultado = $ins->existe_indice_by_name(name_index: $name_index,table: $table);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);


        errores::$error = false;
    }
    public function test_foreign_key_completo(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $drop = $ins->drop_table_segura('test');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }

        $table = 'b';

        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $create = $ins->create_table($campos, $table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }
        $campo = 'a_id';
        $add_campo = $ins->add_colum($campo, $table, 'bigint');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear add_campo',data:  $add_campo);
            print_r($error);
            exit;
        }


        $campo = 'a';
        $table = 'b';
        $resultado = $ins->foreign_key_completo($campo, $table);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("ALTER TABLE b ADD CONSTRAINT b__a_id FOREIGN KEY (a_id) REFERENCES a(id);", $resultado->sql);

        errores::$error = false;
    }
    public function test_foreign_por_campo(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);



        $drop = $ins->drop_table_segura('test');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }

        $table = 'b';

        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }

        $campos = new stdClass();

        $create = $ins->create_table($campos, $table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }
        $campo = 'a_id';
        $add_campo = $ins->add_colum($campo, $table, 'bigint');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear add_campo',data:  $add_campo);
            print_r($error);
            exit;
        }


        $resultado = $ins->foreign_por_campo($campo, $table);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("ALTER TABLE b ADD CONSTRAINT b__a_id FOREIGN KEY (a_id) REFERENCES a(id);", $resultado->sql);

        errores::$error = false;
    }
    public function test_ver_indices(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $table = 'adm_seccion';
        $resultado = $ins->ver_indices($table);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('PRIMARY', $resultado->registros[0]['Key_name']);

        errores::$error = false;
    }





}


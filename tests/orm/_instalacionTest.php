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

        $drop = $ins->drop_table_segura('z');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data:  $drop);
            print_r($error);
            exit;
        }
        $create = $ins->create_table_new('z');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create',data:  $create);
            print_r($error);
            exit;
        }

        $ins = new liberator($ins);

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

        $drop = $ins->drop_table_segura('z');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data:  $drop);
            print_r($error);
            exit;
        }

    }
    public function test_add_unique_base(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $drop = $ins->drop_table_segura('z');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data:  $drop);
            print_r($error);
            exit;
        }
        $create = $ins->create_table_new('z');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create',data:  $create);
            print_r($error);
            exit;
        }
        $add = $ins->add_colum('a', 'z','varchar');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al add',data:  $add);
            print_r($error);
            exit;
        }
        //$ins = new liberator($ins);

        $campo = 'a';
        $table = 'z';

        $resultado = $ins->add_unique_base($campo, $table);
        $this->assertEquals('CREATE UNIQUE INDEX z_unique_a  ON z (a);',$resultado->sql);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

        $drop = $ins->drop_table_segura('z');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data:  $drop);
            print_r($error);
            exit;
        }


    }
    public function test_add_uniques_base(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $drop = $ins->drop_table_segura('z');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data:  $drop);
            print_r($error);
            exit;
        }
        $create = $ins->create_table_new('z');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create',data:  $create);
            print_r($error);
            exit;
        }
        $add = $ins->add_colum('a', 'z','varchar');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al add',data:  $add);
            print_r($error);
            exit;
        }
        //$ins = new liberator($ins);

        $table = 'z';
        $campos_por_integrar = new stdClass();
        $campos_por_integrar->a = new stdClass();
        $campos_por_integrar->a->unique = true;
        $resultado = $ins->add_uniques_base($campos_por_integrar, $table);
        //print_r($resultado);exit;
        $this->assertEquals('CREATE UNIQUE INDEX z_unique_a  ON z (a);',$resultado[0]->sql);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;

        $drop = $ins->drop_table_segura('z');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data:  $drop);
            print_r($error);
            exit;
        }


    }
    public function test_ajusta_atributos(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $atributos = new stdClass();
        $resultado = $ins->ajusta_atributos($atributos);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('VARCHAR',$resultado->tipo_dato);
        $this->assertEmpty($resultado->default);
        $this->assertEquals('255',$resultado->longitud);
        $this->assertTrue($resultado->not_null);


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
    public function test_create_table(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $drop = (new _instalacion(link: $this->link))->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop entidad',data:  $drop);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $table = 'a';
        $resultado = $ins->create_table($campos, $table);
        //print_r($resultado);exit;
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsString("codigo VARCHAR (255) NOT NULL , descripcion VARCHAR (255) NOT NULL , status VARCHAR (255) NOT NU",$resultado->data_sql->sql);


        errores::$error = false;
        $drop = (new _instalacion(link: $this->link))->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop entidad',data:  $drop);
            print_r($error);
            exit;
        }

    }
    public function test_create_table_new(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);

        $drop = (new _instalacion(link: $this->link))->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop entidad',data:  $drop);
            print_r($error);
            exit;
        }


        $table = 'a';
        $resultado = $ins->create_table_new($table);
        //print_r($resultado);exit;
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsString("codigo VARCHAR (255) NOT NULL , descripcion VARCHAR (255) NOT NULL , status VARCHAR (255) NOT NU",$resultado->data_sql->sql);


        errores::$error = false;
        $drop = (new _instalacion(link: $this->link))->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop entidad',data:  $drop);
            print_r($error);
            exit;
        }

    }
    public function test_default(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $atributos = new stdClass();
        $resultado = $ins->default($atributos);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;

        $atributos = new stdClass();
        $atributos->default = 'a';
        $resultado = $ins->default($atributos);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado);
    }
    public function test_describe_table(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);

        $drop = (new _instalacion(link: $this->link))->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop entidad',data:  $drop);
            print_r($error);
            exit;
        }
        $create = (new _instalacion(link: $this->link))->create_table_new('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create entidad',data:  $create);
            print_r($error);
            exit;
        }

        $ins = new liberator($ins);

        $table = 'a';
        $resultado = $ins->describe_table($table);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $drop = (new _instalacion(link: $this->link))->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop entidad',data:  $drop);
            print_r($error);
            exit;
        }
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
    public function test_foraneas(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        //$ins = new liberator($ins);



        $drop = $ins->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear drop',data:  $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear drop',data:  $drop);
            print_r($error);
            exit;
        }

        $create = $ins->create_table_new('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }

        $create = $ins->create_table_new('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }
        $campo = 'b_id';
        $add_campo = $ins->add_colum($campo, 'a', 'bigint');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear add_campo',data:  $add_campo);
            print_r($error);
            exit;
        }

        $foraneas = array();
        $foraneas['b'] = '';
        $table = 'a';
        $resultado = $ins->foraneas($foraneas, $table);
        //print_r($resultado);exit;

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("ALTER TABLE a ADD CONSTRAINT a__b_id FOREIGN KEY (b_id) REFERENCES b(id);", $resultado[0]->sql);

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

        $drop = $ins->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura('a');
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
        $create = $ins->create_table_new('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }
        $create = $ins->create_table_new('b');
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

        $drop = $ins->drop_table_segura('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }
    }
    public function test_foreign_no_conf(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $drop = $ins->drop_table_segura('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }
        $create = $ins->create_table_new('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }

        $create = $ins->create_table_new('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }

        $add = $ins->add_colum('a_id','b', 'bigint');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear campo',data:  $add);
            print_r($error);
            exit;
        }

        $campo = 'a';
        $campo_origen = array();
        $table = 'b';
        $resultado = $ins->foreign_no_conf($campo, $campo_origen, $table);
        //print_r($resultado);exit;

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("ALTER TABLE b ADD CONSTRAINT b__a_id FOREIGN KEY (a_id) REFERENCES a(id);", $resultado->sql);

        errores::$error = false;

        $drop = $ins->drop_table_segura('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }
    }
    public function test_foreign_no_conf_integra()
    {
        $ins = new _instalacion(link: $this->link);
        $drop = $ins->drop_table_segura('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }
        $create = $ins->create_table_new('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }

        $create = $ins->create_table_new('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }

        $add = $ins->add_colum('a_id','b', 'bigint');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear campo',data:  $add);
            print_r($error);
            exit;
        }

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $campo = 'a';
        $campos_origen = array();
        $table = 'b';
        $resultado = $ins->foreign_no_conf_integra($campo, $campos_origen, $table);
        //print_r($resultado);exit;
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $campo = 'a';
        $campos_origen = array();
        $table = 'b';
        $campos_origen[]['Field'] = 'b';
        $resultado = $ins->foreign_no_conf_integra($campo, $campos_origen, $table);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $campo = 'a';
        $campos_origen = array();
        $table = 'b';
        $campos_origen[]['Field'] = 'a';
        $resultado = $ins->foreign_no_conf_integra($campo, $campos_origen, $table);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("ALTER TABLE b ADD CONSTRAINT b__a_id FOREIGN KEY (a_id) REFERENCES a(id);", $resultado[0]->sql);

        errores::$error = false;

        $drop = $ins->drop_table_segura('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }

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

        $create = $ins->create_table_new('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al crear entidad',data:  $create);
            print_r($error);
            exit;
        }

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

        $drop = $ins->drop_table_segura('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }
        $drop = $ins->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar entidad',data:  $drop);
            print_r($error);
            exit;
        }
    }
    public function test_longitud(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);


        $atributos = new stdClass();
        $tipo_dato = '';
        $resultado = $ins->longitud($atributos, $tipo_dato);
        //($resultado);exit;

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("255", $resultado);

        errores::$error = false;

        $atributos = new stdClass();
        $tipo_dato = 'DOUBLE';
        $resultado = $ins->longitud($atributos, $tipo_dato);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("100,4", $resultado);

        errores::$error = false;

        $atributos = new stdClass();
        $tipo_dato = 'TIMESTAMP';
        $resultado = $ins->longitud($atributos, $tipo_dato);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);
        errores::$error = false;
    }

    public function test_modifica_columna(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
       // $ins = new liberator($ins);
        $table = 'b';

        $drop = $ins->drop_table_segura(table: $table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data:  $drop);
            print_r($error);
            exit;
        }

        $create = $ins->create_table_new(table: $table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create',data:  $create);
            print_r($error);
            exit;
        }

        $campo = 'a';
        $add = $ins->add_colum($campo, $table, 'varchar');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al add',data:  $add);
            print_r($error);
            exit;
        }


        $longitud = '';

        $tipo_dato = 'int';
        $resultado = $ins->modifica_columna($campo, $longitud, $table, $tipo_dato);

        //print_r($resultado);exit;

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("ALTER TABLE b MODIFY COLUMN a int ;", $resultado->sql);

        errores::$error = false;

        $drop = $ins->drop_table_segura(table: $table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data:  $drop);
            print_r($error);
            exit;
        }

    }
    public function test_not_null(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);


        $atributos = new stdClass();
        $resultado = $ins->not_null($atributos);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue( $resultado);

        errores::$error = false;
    }
    public function test_tipo_dato(): void
    {

        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $atributos = new stdClass();
        $resultado = $ins->tipo_dato($atributos);
       // print_r($resultado);exit;

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('VARCHAR', $resultado);

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


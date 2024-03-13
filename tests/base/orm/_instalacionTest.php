<?php

use base\orm\estructuras;
use gamboamartin\administrador\models\_instalacion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

class _instalacionTest extends test
{
    public errores $errores;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_add(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $table = 'z';
        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar',data: $drop);
            print_r($error);
            exit;
        }
        $create = $ins->create_table_new($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create',data: $create);
            print_r($error);
            exit;
        }

        $campo = 'a';

        $atributos = new stdClass();
        $resultado = $ins->add($atributos, $campo, $table);

        //print_r($resultado);exit;

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE z ADD a VARCHAR (255)  NOT NULL;', $resultado->sql);

        errores::$error = false;

        $campo = 'b';
        $atributos = new stdClass();
        $atributos->tipo_dato = 'bigint';
        $resultado = $ins->add($atributos, $campo, $table);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE z ADD b BIGINT (255)  NOT NULL;', $resultado->sql);

        errores::$error = false;

        $campo = 'c';
        $atributos = new stdClass();
        $atributos->tipo_dato = 'timestamp';
        $resultado = $ins->add($atributos, $campo, $table);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE z ADD c TIMESTAMP   NOT NULL;', $resultado->sql);

        errores::$error = false;

        $campo = 'd_id';
        $atributos = new stdClass();
        $atributos->foreign_key = true;
        $resultado = $ins->add($atributos, $campo, $table);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE z ADD d_id VARCHAR (255)  NOT NULL;', $resultado->sql);
        errores::$error = false;

        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar',data: $drop);
            print_r($error);
            exit;
        }

    }

    public function test_add_campo(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $table = 'c';

        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data: $drop);
            print_r($error);
            exit;
        }

        $create = $ins->create_table_new($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create',data: $create);
            print_r($error);
            exit;
        }
        $campo = 'b';
        $add = $ins->add_colum(campo: $campo,table: 'c',tipo_dato: 'VARCHAR');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al add',data: $add);
            print_r($error);
            exit;
        }

        $adds = array();
        $atributos = new stdClass();

        $campo_origen_data = array();
        $campo_origen_data['Type'] = 'a';
        $valida_pep_8 = true;
        $resultado = $ins->add_campo($adds, $atributos, $campo, $campo_origen_data, $table, $valida_pep_8);
        //print_r($resultado);exit;
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE c MODIFY COLUMN b VARCHAR (255);', $resultado[0]->sql);

        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar',data: $drop);
            print_r($error);
            exit;
        }

        errores::$error = false;

    }

    public function test_add_campo_final(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $table = 'b';

        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data: $drop);
            print_r($error);
            exit;
        }

        $create = $ins->create_table_new($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create',data: $create);
            print_r($error);
            exit;
        }

        $adds = array();
        $atributos = new stdClass();
        $campo = 'a';
        $existe_campo = false;

        $valida_pep_8 = true;
        $resultado = $ins->add_campo_final($adds, $atributos, $campo, $existe_campo, $table, $valida_pep_8);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE b ADD a VARCHAR (255)  NOT NULL;', $resultado[0]->sql);

        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar',data: $drop);
            print_r($error);
            exit;
        }

        errores::$error = false;

    }
    public function test_add_colum(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);


        $table = 'test';

        $existe_table = (new estructuras(link: $this->link))->existe_entidad($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al validar si existe entidad',data:  $existe_table);
            print_r($error);
            exit;
        }

        if($existe_table) {
            $drop = $ins->drop_table(table: $table);
            if (errores::$error) {
                $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
                print_r($error);
                exit;
            }
        }

        $campos = new stdClass();
        $campos->a = new stdClass();
        $table_create = $ins->create_table($campos, 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $campo = 'campo';
        $table = 'test';
        $tipo_dato = 'varchar';
        $longitud = '';
        $default = '';
        $resultado = $ins->add_colum(campo: $campo, table: $table, tipo_dato: $tipo_dato, default: $default,
            longitud: $longitud);


        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD campo VARCHAR (255)  NOT NULL;', $resultado->sql);

        errores::$error = false;

        $campo = 'campo2';
        $table = 'test';
        $tipo_dato = 'bigint';
        $longitud = '';
        $default = '';
        $resultado = $ins->add_colum(campo: $campo, table: $table, tipo_dato: $tipo_dato, default: $default,
            longitud: $longitud);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD campo2 BIGINT   NOT NULL;', $resultado->sql);

        errores::$error = false;

        $campo = 'campo3';
        $table = 'test';
        $tipo_dato = 'bigint';
        $longitud = '100';
        $default = '';
        $resultado = $ins->add_colum(campo: $campo, table: $table, tipo_dato: $tipo_dato, default: $default,
            longitud: $longitud);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD campo3 BIGINT (100)  NOT NULL;', $resultado->sql);

        errores::$error = false;

        $campo = 'campo4';
        $table = 'test';
        $tipo_dato = 'bigint';
        $longitud = '100';
        $default = '11';
        $resultado = $ins->add_colum(campo: $campo, table: $table, tipo_dato: $tipo_dato, default: $default,
            longitud: $longitud);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("ALTER TABLE test ADD campo4 BIGINT (100) DEFAULT '11' NOT NULL;", $resultado->sql);

        errores::$error = false;

        $campo = 'total_descuento';
        $table = 'test';
        $tipo_dato = 'double';
        $longitud = '';
        $default = '';
        $resultado = $ins->add_colum(campo: $campo, table: $table, tipo_dato: $tipo_dato, default: $default,
            longitud: $longitud);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD total_descuento DOUBLE (100,4)  NOT NULL;', $resultado->sql);

        errores::$error = false;

        $campo = 'total_descuento2';
        $table = 'test';
        $tipo_dato = 'double';
        $longitud = '10,2';
        $default = '';
        $resultado = $ins->add_colum(campo: $campo, table: $table, tipo_dato: $tipo_dato, default: $default,
            longitud: $longitud);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD total_descuento2 DOUBLE (10,2)  NOT NULL;', $resultado->sql);

        errores::$error = false;

        $campo = 'total_descuento3';
        $table = 'test';
        $tipo_dato = 'double';
        $longitud = '10,2';
        $default = '15';
        $resultado = $ins->add_colum(campo: $campo, table: $table, tipo_dato: $tipo_dato, default: $default,
            longitud: $longitud);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("ALTER TABLE test ADD total_descuento3 DOUBLE (10,2) DEFAULT '15' NOT NULL;", $resultado->sql);

        errores::$error = false;

    }
    public function test_add_columns(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);


        $table = 'test';

        $existe_table = (new estructuras(link: $this->link))->existe_entidad($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al validar si existe entidad',data:  $existe_table);
            print_r($error);
            exit;
        }

        if($existe_table) {
            $drop = $ins->drop_table(table: $table);
            if (errores::$error) {
                $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
                print_r($error);
                exit;
            }
        }

        $campos = new stdClass();
        $campos->a = new stdClass();
        $table_create = $ins->create_table($campos, 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->b = new stdClass();
        $resultado = $ins->add_columns(campos: $campos,table:  $table);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD b VARCHAR (255)  NOT NULL;', $resultado[0]->sql);

        errores::$error = false;

        $drop = $ins->drop_table(table: $table);
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->a = new stdClass();
        $table_create = $ins->create_table($campos, 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->b = new stdClass();
        $campos->b->tipo_dato = 'double';
        $resultado = $ins->add_columns(campos: $campos,table:  $table);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD b DOUBLE (100,4)  NOT NULL;', $resultado[0]->sql);

        errores::$error = false;

        $drop = $ins->drop_table(table: $table);
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->a = new stdClass();
        $table_create = $ins->create_table($campos, 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->b = new stdClass();
        $campos->b->tipo_dato = 'double';
        $campos->b->longitud = '100,2';
        $resultado = $ins->add_columns(campos: $campos,table:  $table);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD b DOUBLE (100,2)  NOT NULL;', $resultado[0]->sql);

        errores::$error = false;

        $drop = $ins->drop_table(table: $table);
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->a = new stdClass();
        $table_create = $ins->create_table($campos, 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->b = new stdClass();
        $campos->b->tipo_dato = 'double';
        $campos->b->longitud = '100,2';
        $campos->b->not_null = false;

        $resultado = $ins->add_columns(campos: $campos,table:  $table);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD b DOUBLE (100,2)  ;', $resultado[0]->sql);

        errores::$error = false;

        $drop = $ins->drop_table(table: $table);
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->a = new stdClass();
        $table_create = $ins->create_table($campos, 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->b = new stdClass();
        $campos->b->tipo_dato = 'double';
        $campos->b->longitud = '100,2';
        $campos->b->not_null = false;
        $campos->c = new stdClass();
        $campos->d = new stdClass();
        $campos->d->tipo_dato = 'bigint';


        $resultado = $ins->add_columns(campos: $campos,table:  $table);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD b DOUBLE (100,2)  ;', $resultado[0]->sql);
        $this->assertEquals('ALTER TABLE test ADD c VARCHAR (255)  NOT NULL;', $resultado[1]->sql);
        $this->assertEquals('ALTER TABLE test ADD d BIGINT (255)  NOT NULL;', $resultado[2]->sql);

        errores::$error = false;
    }

    public function test_add_existente(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $table = 'b';
        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar',data: $drop);
            print_r($error);
            exit;
        }
        $create = $ins->create_table_new($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create',data: $create);
            print_r($error);
            exit;
        }

        $adds = array();
        $atributos = new stdClass();
        $campo = 'a';
        $campos_origen = array();
        $valida_pep_8 = true;
        $resultado = $ins->add_existente($adds, $atributos, $campo, $campos_origen, $table, $valida_pep_8);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE b ADD a VARCHAR (255)  NOT NULL;', $resultado[0]->sql);

        errores::$error = false;

        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar',data: $drop);
            print_r($error);
            exit;
        }

    }
    public function test_add_unique_base(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $drop = $ins->drop_table_segura('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data: $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data: $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura('b');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data: $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al drop',data: $drop);
            print_r($error);
            exit;
        }

        $create = $ins->create_table_new('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create',data: $create);
            print_r($error);
            exit;
        }
        $add = $ins->add_colum('v', 'a', 'varchar');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al add',data: $add);
            print_r($error);
            exit;
        }

        $campo = 'v';
        $table = 'a';

        $resultado = $ins->add_unique_base($campo, $table);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('CREATE UNIQUE INDEX a_unique_v  ON a (v);', $resultado->sql);

        errores::$error = false;


        $drop = $ins->drop_table_segura('a');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar',data: $drop);
            print_r($error);
            exit;
        }

    }

    public function test_campo_origen_data_add(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $table = 'a';
        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar',data: $drop);
            print_r($error);
            exit;
        }
        $create = $ins->create_table_new($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al create',data: $create);
            print_r($error);
            exit;
        }

        $adds = array();
        $atributos =new stdClass();
        $campo = '';
        $table = 'a';
        $valida_pep_8 = true;
        $resultado = $ins->campo_origen_data_add($adds, $atributos, $campo, $table, $valida_pep_8);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        $drop = $ins->drop_table_segura($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar',data: $drop);
            print_r($error);
            exit;
        }

        errores::$error = false;

    }

    public function test_create_table(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);


        $table = 'test';

        $drop = $ins->drop_table_segura(table: $table);
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }

        //exit;

        $campos = new stdClass();
        $campos->a = new stdClass();
        $resultado = $ins->create_table(campos: $campos,  table: $table);

        //print_r($resultado);exit;
        //exit;
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('CREATE TABLE test (
                    id bigint NOT NULL AUTO_INCREMENT,
                    a VARCHAR (255) NOT NULL , 
                    PRIMARY KEY (id) 
                   
                    );', $resultado->exe->sql);

        errores::$error = false;

        $table = 'b';


        $existe_table = (new estructuras(link: $this->link))->existe_entidad($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al validar si existe entidad',data:  $existe_table);
            print_r($error);
            exit;
        }

        if($existe_table) {

            $drop = $ins->drop_table(table: $table);
            if (errores::$error) {
                $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
                print_r($error);
                exit;
            }
        }

        $table = 'a';

        $drop = $ins->drop_table_segura(table: $table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla',data:  $drop);
            print_r($error);
            exit;
        }
        $table = 'a';
        $campos = new stdClass();
        $campos->a = new stdClass();
        $resultado = $ins->create_table(campos: $campos, table: $table);
        //print_r($resultado);exit;
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('CREATE TABLE a (
                    id bigint NOT NULL AUTO_INCREMENT,
                    a VARCHAR (255) NOT NULL , 
                    PRIMARY KEY (id) 
                   
                    );', $resultado->exe->sql);


        $table = 'b';
        $campos = new stdClass();
        $campos->a = new stdClass();
        $campos->a_id = new stdClass();
        $campos->a_id->foreign_key = true;
        $campos->a_id->tipo_dato = 'bigint';
        $resultado = $ins->create_table(campos: $campos, table: $table);
        //print_r($resultado);exit;
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('CREATE TABLE b (
                    id bigint NOT NULL AUTO_INCREMENT,
                    a VARCHAR (255) NOT NULL , a_id BIGINT (255) NOT NULL , 
                    PRIMARY KEY (id) , 
                   FOREIGN KEY (a_id) REFERENCES a(id) ON UPDATE RESTRICT ON DELETE RESTRICT
                    );', $resultado->exe->sql);

        errores::$error = false;


        $table = 'z';
        $existe_table = (new estructuras(link: $this->link))->existe_entidad($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al validar si existe entidad',data:  $existe_table);
            print_r($error);
            exit;
        }

        if($existe_table) {
            $drop = $ins->drop_table(table: $table);
            if (errores::$error) {
                $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
                print_r($error);
                exit;
            }
        }
        //exit;

        $campos = new stdClass();
        $resultado = $ins->create_table(campos: $campos, table: $table);
        //print_r($resultado);exit;
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("CREATE TABLE z (
                    id bigint NOT NULL AUTO_INCREMENT,
                    codigo VARCHAR (255) NOT NULL , descripcion VARCHAR (255) NOT NULL , status VARCHAR (255) NOT NULL DEFAULT 'activo', usuario_alta_id INT (255) NOT NULL , usuario_update_id INT (255) NOT NULL , fecha_alta TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP, fecha_update TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, descripcion_select VARCHAR (255) NOT NULL , alias VARCHAR (255) NOT NULL , codigo_bis VARCHAR (255) NOT NULL , predeterminado VARCHAR (255) NOT NULL DEFAULT 'inactivo', 
                    PRIMARY KEY (id) 
                   
                    );", $resultado->exe->sql);

        errores::$error = false;

    }

    public function test_data_column(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $atributos = new stdClass();
        $campo_origen_data = array();
        $campo_origen_data['Type'] = 'a';
        $resultado = $ins->data_column($atributos, $campo_origen_data);


        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("A", $resultado->type_origen);
        $this->assertEquals("A", $resultado->tipo_dato_origen);
        $this->assertEquals("VARCHAR", $resultado->type_new);
        $this->assertEquals("", $resultado->longitud);

        errores::$error = false;

    }

    public function test_existen_entidad(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);


        $table = 'a';

        $resultado = $ins->existe_entidad($table);
        $this->assertTrue($resultado);

        errores::$error = false;

        $table = 'zzzz';

        $resultado = $ins->existe_entidad($table);
        $this->assertNotTrue($resultado);


        errores::$error = false;
    }

    public function test_foreign_key_completo(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);


        $table = 'test';

        $existe_table = (new estructuras(link: $this->link))->existe_entidad($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al validar si existe entidad',data:  $existe_table);
            print_r($error);
            exit;
        }

        if($existe_table) {
            $drop = $ins->drop_table(table: $table);
            if (errores::$error) {
                $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
                print_r($error);
                exit;
            }
        }

        $campos = new stdClass();
        $campos->a = new stdClass();
        $table_create = $ins->create_table($campos, 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $campo = 'b_id';
        $table = 'test';
        $resultado = $ins->foreign_key_completo(campo: $campo,table:  $table);
        //print_r($resultado);exit;
        $this->assertEquals('ALTER TABLE test ADD CONSTRAINT test_b_id FOREIGN KEY (b_id) REFERENCES b(id);', $resultado->sql);

        errores::$error = false;
    }

    public function test_foreign_key_existente(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);



        $table = 'test';
        $drop = $ins->drop_table_segura(table: $table);
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }
        $drop = $ins->drop_table_segura(table: 'b');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }



        $campos = new stdClass();
        $campos->a = new stdClass();
        $campos->b_id = new stdClass();
        $campos->b_id->tipo_dato = 'bigint';
        $table_create = $ins->create_table($campos, 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $table_create = $ins->create_table_new( 'b');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $relacion_table = 'b';
        $table = 'test';
        $resultado = $ins->foreign_key_existente($relacion_table, $table);
        //print_r($resultado);exit;
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD CONSTRAINT test_b_id FOREIGN KEY (b_id) REFERENCES b(id);', $resultado->sql);

        errores::$error = false;
        $table = 'test';
        $drop = $ins->drop_table_segura(table: $table);
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }
        $drop = $ins->drop_table_segura(table: 'b');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }
        errores::$error = false;




    }

    public function test_foreign_key_seguro(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);

        $drop = $ins->drop_table_segura(table: 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al drop', data: $drop);
            print_r($error);
            exit;
        }

        $table = 'b';
        $drop = $ins->drop_table_segura(table: $table);
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al drop', data: $drop);
            print_r($error);
            exit;
        }
        $create = $ins->create_table_new($table);
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al create', data: $create);
            print_r($error);
            exit;
        }

        $add = $ins->add_colum('a_id',$table, 'bigint');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al add', data: $add);
            print_r($error);
            exit;
        }

        $campo = 'a';
        $resultado = $ins->foreign_key_seguro($campo, $table);
        $this->assertEquals('ALTER TABLE b ADD CONSTRAINT b_a_id FOREIGN KEY (a_id) REFERENCES a(id);', $resultado->sql);

        errores::$error = false;
    }

    public function test_get_data_indices(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);
        $ins = new liberator($ins);

        $name_indice_opt = '';
        $relacion_table = 'b';
        $table = 'a';
        $resultado = $ins->get_data_indices($name_indice_opt, $relacion_table, $table);
        //print_r($resultado);exit;
        $this->assertIsObject($resultado);

        errores::$error = false;

        $name_indice_opt = '';
        $relacion_table = 'adm_seccion';
        $table = 'adm_accion';
        $resultado = $ins->get_data_indices($name_indice_opt, $relacion_table, $table);
        $this->assertNotTrue($resultado);
        $this->assertIsObject($resultado);
        $this->assertEquals('adm_accion',$resultado->indices[0]->TABLE_NAME);
        errores::$error = false;

    }

    public function test_index_unique(): void
    {

        $ins = new _instalacion(link: $this->link);

        $drop = $ins->drop_table_segura(table: 'b');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al drop', data: $drop);
            print_r($error);
            exit;
        }

        $create = $ins->create_table_new(table: 'b');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al create', data: $create);
            print_r($error);
            exit;
        }

        $add = $ins->add_colum(campo: 'z', table: 'b', tipo_dato: 'bigint');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al add', data: $add);
            print_r($error);
            exit;
        }
        errores::$error = false;

        $table = 'b';
        $columnas = array();
        $columnas[] = 'z';
        $resultado = $ins->index_unique($columnas, $table);
        $this->assertNotTrue($resultado);
        $this->assertEquals('CREATE UNIQUE INDEX b_unique_z  ON b (z);',$resultado->sql);


        $drop = $ins->drop_table_segura(table: 'b');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al drop', data: $drop);
            print_r($error);
            exit;
        }

        errores::$error = false;
    }

    public function test_integra_foraneas(): void
    {
        errores::$error = false;
        $ins = new _instalacion(link: $this->link);

        $table = 'test';

        $drop = $ins->drop_table_segura(table: $table);
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura(table: 'b');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }


        $table_create = $ins->create_table_new( 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $table_create = $ins->create_table_new( 'b');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->b_id = new stdClass();
        $campos->b_id->foreign_key = true;
        $resultado = $ins->integra_foraneas(campos: $campos,table: 'test');

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD CONSTRAINT test_b_id FOREIGN KEY (b_id) REFERENCES b(id);', $resultado[0]->sql);

        errores::$error = false;

        $table = 'c';
        $existe_table = (new estructuras(link: $this->link))->existe_entidad($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al validar si existe entidad',data:  $existe_table);
            print_r($error);
            exit;
        }

        if($existe_table) {
            $drop = $ins->drop_table(table: $table);
            if (errores::$error) {
                $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
                print_r($error);
                exit;
            }
        }

        $campos = new stdClass();
        $campos->a = new stdClass();
        $table_create = $ins->create_table($campos, 'c');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $campos = new stdClass();
        $campos->c_id = new stdClass();
        $campos->c_id->foreign_key = true;
        $resultado = $ins->integra_foraneas(campos: $campos,table: 'test');
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD CONSTRAINT test_c_id FOREIGN KEY (c_id) REFERENCES c(id);', $resultado[0]->sql);

        errores::$error = false;

        $drop = $ins->drop_table_segura(table: 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }

        $drop = $ins->drop_table_segura(table: 'b');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
            print_r($error);
            exit;
        }
    }
}

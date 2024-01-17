<?php

use base\orm\estructuras;
use gamboamartin\administrador\models\_instalacion;
use gamboamartin\errores\errores;
use gamboamartin\test\test;

class _instalacionTest extends test
{
    public errores $errores;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
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
        $this->assertEquals('ALTER TABLE test ADD campo4 BIGINT (100) DEFAULT 11 NOT NULL;', $resultado->sql);

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
        $this->assertEquals('ALTER TABLE test ADD total_descuento3 DOUBLE (10,2) DEFAULT 15 NOT NULL;', $resultado->sql);

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

    public function test_create_table(): void
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
        $resultado = $ins->create_table(campos: $campos,  table: $table);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('CREATE TABLE test (
                    id bigint NOT NULL AUTO_INCREMENT,
                    a VARCHAR (255) NOT NULL, 
                    PRIMARY KEY (id) 
                   
                    );', $resultado->sql);

        errores::$error = false;

        $table = 'b';

        $drop = $ins->drop_table( table: $table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla',data:  $drop);
            print_r($error);
            exit;
        }

        $table = 'a';

        $drop = $ins->drop_table(table: $table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla',data:  $drop);
            print_r($error);
            exit;
        }
        $table = 'a';
        $campos = new stdClass();
        $campos->a = new stdClass();
        $resultado = $ins->create_table(campos: $campos, table: $table);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('CREATE TABLE a (
                    id bigint NOT NULL AUTO_INCREMENT,
                    a VARCHAR (255) NOT NULL, 
                    PRIMARY KEY (id) 
                   
                    );', $resultado->sql);


        $table = 'b';
        $campos = new stdClass();
        $campos->a = new stdClass();
        $campos->a_id = new stdClass();
        $campos->a_id->foreign_key = true;
        $campos->a_id->tipo_dato = 'bigint';
        $resultado = $ins->create_table(campos: $campos, table: $table);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('CREATE TABLE b (
                    id bigint NOT NULL AUTO_INCREMENT,
                    a VARCHAR (255) NOT NULL, a_id bigint (255) NOT NULL, 
                    PRIMARY KEY (id) , 
                   FOREIGN KEY (a_id) REFERENCES a(id) ON UPDATE RESTRICT ON DELETE RESTRICT
                    );', $resultado->sql);

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
        $this->assertEquals('ALTER TABLE test ADD CONSTRAINT test__b_id FOREIGN KEY (b_id) REFERENCES b(id);', $resultado->sql);

        errores::$error = false;
    }

    public function test_foreign_key_existente(): void
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
        $campos->b_id = new stdClass();
        $campos->b_id->tipo_dato = 'bigint';
        $table_create = $ins->create_table($campos, 'test');
        if (errores::$error) {
            $error = (new errores())->error(mensaje: 'Error al crear tabla', data: $table_create);
            print_r($error);
            exit;
        }

        $relacion_table = 'b';
        $table = 'test';
        $resultado = $ins->foreign_key_existente($relacion_table, $table);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD CONSTRAINT test__b_id FOREIGN KEY (b_id) REFERENCES b(id);', $resultado->sql);

        errores::$error = false;



    }

    public function test_integra_foraneas(): void
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
        $campos->b_id = new stdClass();
        $campos->b_id->foreign_key = true;
        $resultado = $ins->integra_foraneas(campos: $campos,table: 'test');
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ALTER TABLE test ADD CONSTRAINT test__b_id FOREIGN KEY (b_id) REFERENCES b(id);', $resultado[0]->sql);

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
        $this->assertEquals('ALTER TABLE test ADD CONSTRAINT test__c_id FOREIGN KEY (c_id) REFERENCES c(id);', $resultado[0]->sql);

        errores::$error = false;
    }
}

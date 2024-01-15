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

    public function test_create_table(): void
    {
        errores::$error = false;
        $ins = new _instalacion();


        $table = 'test';

        $existe_table = (new estructuras(link: $this->link))->existe_entidad($table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al validar si existe entidad',data:  $existe_table);
            print_r($error);
            exit;
        }

        if($existe_table) {
            $drop = $ins->drop_table(link: $this->link, table: $table);
            if (errores::$error) {
                $error = (new errores())->error(mensaje: 'Error al eliminar tabla', data: $drop);
                print_r($error);
                exit;
            }
        }

        $campos = new stdClass();
        $campos->a = new stdClass();
        $resultado = $ins->create_table(campos: $campos, link: $this->link, table: $table);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('CREATE TABLE test (
                    id bigint NOT NULL AUTO_INCREMENT,
                    a VARCHAR (255) NOT NULL, 
                    PRIMARY KEY (id) 
                   
                    );', $resultado->sql);

        errores::$error = false;

        $table = 'b';

        $drop = $ins->drop_table(link: $this->link, table: $table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla',data:  $drop);
            print_r($error);
            exit;
        }

        $table = 'a';

        $drop = $ins->drop_table(link: $this->link, table: $table);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar tabla',data:  $drop);
            print_r($error);
            exit;
        }
        $table = 'a';
        $campos = new stdClass();
        $campos->a = new stdClass();
        $resultado = $ins->create_table(campos: $campos, link: $this->link, table: $table);
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
        $resultado = $ins->create_table(campos: $campos, link: $this->link, table: $table);
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
}

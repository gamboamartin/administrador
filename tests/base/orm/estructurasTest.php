<?php
namespace tests\base\orm;

use base\orm\estructuras;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class estructurasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_asigna_datos_estructura(): void
    {
        errores::$error = false;
        $st = new estructuras($this->link);
        //$st = new liberator($st);
        $resultado = $st->asigna_datos_estructura();

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertTrue($resultado->accion->data_campos->id->es_primaria);
        $this->assertTrue($resultado->accion->data_campos->seccion_id->es_foranea);
        $this->assertEquals('seccion',$resultado->accion->data_campos->seccion_id->tabla_foranea);

        errores::$error = false;


        $resultado = $st->asigna_datos_estructura();
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertTrue($resultado->accion_grupo->tiene_foraneas);
        $this->assertNotTrue($resultado->dia->tiene_foraneas);

        errores::$error = false;




    }

    public function test_modelos(){
        errores::$error = false;
        $st = new estructuras($this->link);
        $st = new liberator($st);
        $resultado = $st->modelos();
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('campo',$resultado[5]);

        errores::$error = false;

    }



}
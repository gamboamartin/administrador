<?php
namespace tests\base\orm;

use base\orm\sql;
use base\orm\sql_bass;
use base\orm\upd;
use gamboamartin\errores\errores;

use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_accion;


class updTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_ejecuta_upd(): void
    {
        errores::$error = false;
        $upd = new upd();
        //$sql = new liberator($sql);

        $id = 1;
        $modelo = new adm_accion($this->link);
        $resultado = $upd->ejecuta_upd($id, $modelo);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Info no hay elementos a modificar',$resultado->resultado->mensaje);

        errores::$error = false;
        $upd = new upd();
        //$sql = new liberator($sql);

        $id = 1;

        $modelo = new adm_accion($this->link);
        $modelo->registro_upd['status'] = 'activo';
        $resultado = $upd->ejecuta_upd($id, $modelo);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado->ejecuta_upd);
        $this->assertEquals('activo',$modelo->registro_upd['status']);

        errores::$error = false;
        $upd = new upd();
        //$sql = new liberator($sql);

        $id = 1;

        $modelo = new adm_accion($this->link);
        $modelo->registro_upd = array();
        $resultado = $upd->ejecuta_upd($id, $modelo);



        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado->ejecuta_upd);


        errores::$error = false;
    }



}
<?php
namespace tests\orm;

use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_accion_basica;
use gamboamartin\administrador\models\adm_accion_grupo;
use gamboamartin\administrador\models\adm_bitacora;
use gamboamartin\administrador\models\adm_campo;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_seccion_pertenece;
use gamboamartin\administrador\models\adm_sistema;
use gamboamartin\errores\errores;
use gamboamartin\test\test;


class adm_sistemaTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_secciones_pertenece(){

        errores::$error = false;
        $modelo = new adm_sistema($this->link);
        //$modelo = new liberator($modelo);

        $_SESSION['usuario_id']= 2;

        $adm_sistema_id = 1;
        $resultado = $modelo->secciones_pertenece($adm_sistema_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado[0]['adm_seccion_pertenece_id']);
        errores::$error = false;
    }





}


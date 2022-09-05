<?php
namespace tests\base\orm;

use base\orm\dependencias;
use base\orm\estructuras;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use models\adm_mes;


class dependenciasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_aplica_eliminacion_dependencias(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        //$dep = new liberator($dep);
        $link = $this->link;
        $tabla = '';
        $registro_id = 1;
        $models_dependientes = array();
        $desactiva_dependientes = true;
        $resultado = $dep->aplica_eliminacion_dependencias($desactiva_dependientes, $link, $models_dependientes,
            $registro_id, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }

    public function test_data_dependientes(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);
        $link = $this->link;
        $parent_id = 1;
        $tabla = 'adm_menu';
        $tabla_children = 'adm_seccion';
        $resultado = $dep->data_dependientes($link, $parent_id, $tabla, $tabla_children);

        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }

    public function test_elimina_data_modelo(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);
        $link = $this->link;
        $tabla = 'adm_accion';
        $modelo_dependiente = 'adm_accion_grupo';
        $registro_id = 1;
        $resultado = $dep->elimina_data_modelo($modelo_dependiente, $link, $registro_id, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }

    public function test_elimina_data_modelos_dependientes(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);
        $link = $this->link;
        $tabla = 'adm_accion_grupo';
        $registro_id = 1;
        $models_dependientes = array('adm_accion_grupo');
        $resultado = $dep->elimina_data_modelos_dependientes($models_dependientes, $link, $registro_id, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }

    public function test_elimina_dependientes(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);
        $link = $this->link;
        $parent_id = 1;
        $tabla = 'adm_mes';

        $model = new adm_mes($this->link);
        $resultado = $dep->elimina_dependientes($model, $parent_id, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }




}
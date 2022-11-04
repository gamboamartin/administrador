<?php
namespace tests\base\orm;

use base\orm\dependencias;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class dependenciasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_ajusta_modelo_comp(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);

        $name_modelo = 'a';
        $resultado = $dep->ajusta_modelo_comp($name_modelo);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        errores::$error = false;
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
        $resultado = $dep->data_dependientes(link: $link, namespace_model: 'gamboamartin\\administrador\\models',
            parent_id: $parent_id, tabla: $tabla, tabla_children: $tabla_children);

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
        $resultado = $dep->elimina_data_modelo($modelo_dependiente,'gamboamartin\\administrador\\models', $link, $registro_id, $tabla);

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
        $models_dependientes[0]['dependiente'] = 'adm_accion_grupo';
        $models_dependientes[0]['namespace_model'] = 'gamboamartin\\administrador\\models';
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
<?php
namespace tests\base\orm;

use base\orm\dependencias;
use gamboamartin\administrador\models\adm_elemento_lista;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_year;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class dependenciasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
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

    public function test_desactiva_data_modelo(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);
        $modelo = new adm_seccion($this->link);
        $modelo->registro_id = 1;
        $namespace_model = '\\gamboamartin\\administrador\\models';

        $modelo_dependiente = 'adm_accion';

        $resultado = $dep->desactiva_data_modelo($modelo,$modelo_dependiente,$namespace_model);

        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }

    public function test_desactiva_data_modelos_dependientes(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        //$dep = new liberator($dep);
        $modelo = new adm_seccion($this->link);
        $modelo->registro_id = 1;

        $resultado = $dep->desactiva_data_modelos_dependientes($modelo);


        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        errores::$error = false;
    }


    public function test_desactiva_dependientes(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);
        $modelo = new adm_seccion($this->link);
        $namespace_model = '\\gamboamartin\\administrador\\models';
        $parent_id = 1;
        $tabla_dep = 'adm_accion';

        $resultado = $dep->desactiva_dependientes($modelo,$namespace_model,$parent_id,$tabla_dep);

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

    public function test_model_dependiente(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);

        $modelo_dependiente = 'adm_elemento_lista';
        $modelo = new adm_mes($this->link);

        $modelo->registro_id = 1;
        $resultado = $dep->model_dependiente($modelo,$modelo_dependiente,'gamboamartin\administrador\models');

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('adm_elemento_lista',$resultado->tabla);
        errores::$error = false;
    }

    public function test_modelo_dependiente_val(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);
        $modelo_dependiente = 'a';

        $modelo = new adm_mes($this->link);
        $modelo->registro_id = 1;
        $resultado = $dep->modelo_dependiente_val($modelo, $modelo_dependiente);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("models\a",$resultado);
        errores::$error = false;
    }

    public function test_valida_data_desactiva(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);

        $modelo = new adm_mes(link: $this->link);
        $modelo_dependiente = 'a';
        $modelo->registro_id = 1;

        $resultado = $dep->valida_data_desactiva($modelo, $modelo_dependiente);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_valida_names_model(): void
    {
        errores::$error = false;
        $dep = new dependencias();
        $dep = new liberator($dep);

        $modelo_dependiente = 'a';
        $tabla = 'gamboamartin\administrador\adm_seccion';
        $resultado = $dep->valida_names_model($modelo_dependiente, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);
        errores::$error = false;

    }




}
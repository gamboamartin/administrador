<?php
namespace tests\base\orm;

use base\orm\joins;
use gamboamartin\administrador\models\adm_accion;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class joinsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_ajusta_name_models(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);
        $tabla = '';
        $tabla_enlace = '';
        $resultado = $joins->ajusta_name_models($tabla, $tabla_enlace);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error tabla no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;
        $tabla = 'a';
        $tabla_enlace = '';
        $resultado = $joins->ajusta_name_models($tabla, $tabla_enlace);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error $tabla_enlace no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;
        $tabla = 'a';
        $tabla_enlace = 'b';
        $resultado = $joins->ajusta_name_models($tabla, $tabla_enlace);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('models\a',$resultado->tabla->name_model);
        $this->assertEquals('models\b',$resultado->tabla_enlace->name_model);
        errores::$error = false;
    }

    public function test_ajusta_name_model(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);
        errores::$error = false;
        $tabla = '';
        $resultado = $joins->ajusta_name_model($tabla);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error tabla no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;
        $tabla = 'a';
        $resultado = $joins->ajusta_name_model($tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('a',$resultado->tabla);
        $this->assertEquals('models\a',$resultado->name_model);
        errores::$error = false;
    }

    public function test_ajusta_tablas(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);

        $tablas = '';
        $tablas_join = array();
        $resultado = $joins->ajusta_tablas($tablas, $tablas_join);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('',$resultado);

        errores::$error = false;
        $tablas = 'a';
        $tablas_join = array();
        $resultado = $joins->ajusta_tablas($tablas, $tablas_join);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('a',$resultado);

        errores::$error = false;
        $tablas = 'a';
        $tablas_join = array();
        $tablas_join['adm_seccion'] = 'b';
        $tablas_join['adm_accion'] = 'c';
        $resultado = $joins->ajusta_tablas($tablas, $tablas_join);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('a LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = b.adm_seccion_id LEFT JOIN adm_accion AS adm_accion ON adm_accion.id = c.adm_accion_id',$resultado);

        errores::$error = false;
    }

    public function test_data_for_rename(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);

        $renombrada = 'd';
        $join = 'a';
        $init = new stdClass();
        $id_renombrada = 'c';
        $resultado = $joins->data_for_rename($id_renombrada, $init, $join, $renombrada);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar $init',$resultado['mensaje']);

        errores::$error = false;


        $renombrada = 'd';
        $join = 'a';
        $init = new stdClass();
        $init->tabla = '';
        $id_renombrada = 'c';
        $resultado = $joins->data_for_rename($id_renombrada, $init, $join, $renombrada);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar $init',$resultado['mensaje']);

        errores::$error = false;


        $renombrada = 'd';
        $join = 'a';
        $init = new stdClass();
        $init->tabla = 'a';
        $init->tabla_enlace = 'a';
        $id_renombrada = 'c';
        $resultado = $joins->data_for_rename($id_renombrada, $init, $join, $renombrada);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);


        errores::$error = false;
    }

    public function test_data_join(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);
        errores::$error = false;
        $tabla_join = array();
        $resultado = $joins->data_join($tabla_join);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar $tabla_join',$resultado['mensaje']);
        $this->assertStringContainsStringIgnoringCase('Error tabla_base no existe en el registro',$resultado['data']['mensaje']);

        errores::$error = false;
        $tabla_join = array();
        $tabla_join['tabla_base'] = 'x';
        $resultado = $joins->data_join($tabla_join);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar $tabla_join',$resultado['mensaje']);
        $this->assertStringContainsStringIgnoringCase('Error tabla_enlace no',$resultado['data']['mensaje']);

        errores::$error = false;
        $tabla_join = array();
        $tabla_join['tabla_base'] = 'x';
        $tabla_join['tabla_enlace'] = 'y';
        $resultado = $joins->data_join($tabla_join);

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('x',$resultado->tabla_base);
        $this->assertEquals('y',$resultado->tabla_enlace);
        $this->assertEquals('',$resultado->tabla_renombre);
        $this->assertEquals('',$resultado->campo_renombrado);
        $this->assertEquals('',$resultado->campo_tabla_base_id);

        errores::$error = false;
        $tabla_join = array();
        $tabla_join['tabla_base'] = 'a';
        $tabla_join['tabla_enlace'] = 'b';
        $tabla_join['tabla_renombrada'] = 'c';
        $tabla_join['campo_renombrado'] = 'd';
        $tabla_join['campo_tabla_base_id'] = 'e';
        $resultado = $joins->data_join($tabla_join);

        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('a',$resultado->tabla_base);
        $this->assertEquals('b',$resultado->tabla_enlace);
        $this->assertEquals('c',$resultado->tabla_renombre);
        $this->assertEquals('d',$resultado->campo_renombrado);
        $this->assertEquals('e',$resultado->campo_tabla_base_id);


        errores::$error = false;
    }

    public function test_data_para_join(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);


        $tabla_join = array();
        $resultado = $joins->data_para_join($tabla_join);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar $tabla_join',$resultado['mensaje']);

        errores::$error = false;
        $tabla_join = array();
        $tabla_join['tabla_base'] = '';
        $resultado = $joins->data_para_join($tabla_join);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar $tabla_join',$resultado['mensaje']);

        errores::$error = false;
        $tabla_join = array();
        $tabla_join['tabla_base'] = 'adm_seccion';
        $tabla_join['tabla_enlace'] = 'adm_seccion';
        $resultado = $joins->data_para_join($tabla_join);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals(' LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = adm_seccion.adm_seccion_id',$resultado);
        errores::$error = false;
    }

    public function test_data_para_join_esp(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);


        $tabla_join = '';
        $key = '';
        $resultado = $joins->data_para_join_esp($key, $tabla_join);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar join',$resultado['mensaje']);

        errores::$error = false;

        $tabla_join = '';
        $key = 'x';
        $resultado = $joins->data_para_join_esp($key, $tabla_join);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar join',$resultado['mensaje']);

        errores::$error = false;

        $tabla_join = 'z';
        $key = 'adm_seccion';
        $resultado = $joins->data_para_join_esp($key, $tabla_join);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals(' LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = z.adm_seccion_id',$resultado);

        errores::$error = false;
    }

    public function test_data_tabla_sql(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);


        $tabla_join = 'x';
        $key = 'adm_seccion';
        $tablas = '';
        $resultado = $joins->data_tabla_sql($key, $tabla_join, $tablas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals(' LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = x.adm_seccion_id',$resultado);
        errores::$error = false;
    }

    public function test_extensiones_join(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);


        $modelo = new adm_accion($this->link);
        $extension_estructura = array();
        $tablas = '';
        $resultado = $joins->extensiones_join($extension_estructura, $modelo, $tablas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('',$resultado);
        errores::$error = false;
    }

    public function test_genera_join(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);

        $tabla = 'adm_seccion';
        $tabla_enlace = 'adm_seccion';
        $resultado = $joins->genera_join($tabla, $tabla_enlace);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase(' LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = adm_seccion.adm_seccion_id',$resultado);
        errores::$error = false;
    }

    public function test_genera_join_renombrado(){

        errores::$error = false;
        $join = new joins();
        $join = new liberator($join);
        $resultado = $join->genera_join_renombrado('','','','','','');
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar',$resultado['mensaje']);

        errores::$error = false;

        $resultado = $join->genera_join_renombrado('','','x','','','');
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar',$resultado['mensaje']);

        errores::$error = false;

        $resultado = $join->genera_join_renombrado('','x','x','','','');
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar',$resultado['mensaje']);

        errores::$error = false;

        $resultado = $join->genera_join_renombrado('','x','x','x','','');
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar',$resultado['mensaje']);

        errores::$error = false;

        $resultado = $join->genera_join_renombrado('','x','x','x','x','');
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al inicializar',$resultado['mensaje']);

        errores::$error = false;
        $resultado = $join->genera_join_renombrado('','x','x','x','x','x');
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar',$resultado['mensaje']);

        errores::$error = false;
        $resultado = $join->genera_join_renombrado('','x','estado','x','x','x');
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar',$resultado['mensaje']);

        errores::$error = false;
        $resultado = $join->genera_join_renombrado('','LEFT','estado','x','x','x');
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar',$resultado['mensaje']);
        errores::$error = false;

        $resultado = $join->genera_join_renombrado('x','id','LEFT','x','adm_seccion','adm_seccion');

        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase(' LEFT JOIN adm_seccion AS x ON x.id = adm_seccion.x',$resultado);
        errores::$error = false;


    }

    public function test_id_renombrada(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);

        $campo_tabla_base_id = '';
        $resultado = $joins->id_renombrada($campo_tabla_base_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('.id',$resultado);
        errores::$error = false;
    }

    public function test_init_renombre(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);
        $tabla = '';
        $tabla_enlace = '';
        $resultado = $joins->init_renombre($tabla, $tabla_enlace);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error tabla no puede venir vacia',$resultado['mensaje']);

        errores::$error = false;

        $tabla = 'a';
        $tabla_enlace = 'b';
        $resultado = $joins->init_renombre($tabla, $tabla_enlace);
        $this->assertNotTrue(errores::$error);
        $this->assertIsObject($resultado);
        $this->assertEquals('models\a',$resultado->class);
        $this->assertEquals('models\b',$resultado->class_enlace);
        errores::$error = false;
    }

    public function test_join_extension(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);


        $data = array();
        $modelo = new adm_accion($this->link);
        $tabla = '';
        $tablas = '';
        $resultado = $joins->join_extension($data, $modelo, $tabla, $tablas);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar data',$resultado['mensaje']);

        errores::$error = false;

        $data = array();
        $modelo = new adm_accion($this->link);
        $tabla = '';
        $tablas = '';
        $data['key'] = 'a';
        $data['enlace'] = 'b';
        $data['key_enlace'] = 'c';
        $resultado = $joins->join_extension($data, $modelo, $tabla, $tablas);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al generar sql',$resultado['mensaje']);

        errores::$error = false;

        $data = array();
        $modelo = new adm_accion($this->link);
        $tabla = 'd';
        $tablas = '';
        $data['key'] = 'a';
        $data['enlace'] = 'b';
        $data['key_enlace'] = 'c';
        $resultado = $joins->join_extension($data, $modelo, $tabla, $tablas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals(' d AS d  ON d.a = b.c',$resultado);
        errores::$error = false;
    }

    public function test_join_renombres(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);


        $modelo = new adm_accion($this->link);
        $data = array();
        $tabla_renombrada = '';
        $tablas = '';
        $resultado = $joins->join_renombres($data, $modelo, $tabla_renombrada, $tablas);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos',$resultado['mensaje']);

        errores::$error = false;
        $modelo = new adm_accion($this->link);
        $data = array();
        $tabla_renombrada = 'a';
        $tablas = '';
        $data['enlace'] = 'a';
        $data['nombre_original'] = 'a';
        $data['key'] = 'a';
        $data['key_enlace'] = 'a';
        $resultado = $joins->join_renombres($data, $modelo, $tabla_renombrada, $tablas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals(' a AS a  ON a.a = a.a',$resultado);
        errores::$error = false;
    }

    public function test_left_join_str(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);


        $tablas = '';
        $resultado = $joins->left_join_str($tablas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('',$resultado);

        errores::$error = false;
        $tablas = 'a';
        $resultado = $joins->left_join_str($tablas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals(' LEFT JOIN ',$resultado);

        errores::$error = false;
    }

    public function test_obten_tablas_completas(){
        errores::$error = false;
        $joins = new joins();
        //$joins = new liberator($joins);

        $columnas_join = array();
        $tabla = 'a';
        $resultado = $joins->obten_tablas_completas($columnas_join, $tabla);

        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);


        errores::$error = false;

        $columnas_join = array();
        $tabla = 'adm_accion';
        $resultado = $joins->obten_tablas_completas($columnas_join, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('adm_accion AS adm_accion',$resultado);
        errores::$error = false;
    }

    public function test_renombres_join(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);


        $modelo = new adm_accion($this->link);
        $renombradas = array();
        $tablas = '';
        $resultado = $joins->renombres_join($modelo, $renombradas, $tablas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('',$resultado);

        errores::$error = false;


    }

    public function test_sql_join(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);
        errores::$error = false;
        $campo_renombrado = '';
        $campo_tabla_base_id = '';
        $class = '';
        $renombrada = '';
        $tabla = '';
        $tabla_enlace = '';
        $resultado = $joins->sql_join(campo_renombrado: $campo_renombrado,campo_tabla_base_id:  $campo_tabla_base_id,
            class: $class, renombrada: $renombrada, tabla: $tabla, tabla_enlace: $tabla_enlace);

        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al llamar datos',$resultado['mensaje']);

        errores::$error = false;

        $campo_renombrado = '';
        $campo_tabla_base_id = '';
        $class = 'models\\seccion';
        $renombrada = '';
        $tabla = '';
        $tabla_enlace = '';
        $resultado = $joins->sql_join(campo_renombrado: $campo_renombrado,campo_tabla_base_id:  $campo_tabla_base_id,
            class: $class, renombrada: $renombrada, tabla: $tabla, tabla_enlace: $tabla_enlace);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al llamar datos',$resultado['mensaje']);

        errores::$error = false;

        $campo_renombrado = '';
        $campo_tabla_base_id = '';
        $class = 'models\\seccion';
        $renombrada = '';
        $tabla = 'a';
        $tabla_enlace = '';
        $resultado = $joins->sql_join(campo_renombrado: $campo_renombrado,campo_tabla_base_id:  $campo_tabla_base_id,
            class: $class, renombrada: $renombrada, tabla: $tabla, tabla_enlace: $tabla_enlace);

        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al llamar datos',$resultado['mensaje']);

        errores::$error = false;

        $campo_renombrado = '';
        $campo_tabla_base_id = '';

        $renombrada = '';
        $tabla = 'a';
        $tabla_enlace = 'c';
        $resultado = $joins->sql_join(campo_renombrado: $campo_renombrado,campo_tabla_base_id:  $campo_tabla_base_id,
             renombrada: $renombrada, tabla: $tabla, tabla_enlace: $tabla_enlace);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals(' LEFT JOIN a AS a ON a.id = c.a_id',$resultado);

        errores::$error = false;

        $campo_renombrado = 'x';
        $campo_tabla_base_id = '';

        $renombrada = 'z';
        $tabla = 'adm_seccion';
        $tabla_enlace = 'adm_seccion';
        $resultado = $joins->sql_join(campo_renombrado: $campo_renombrado,campo_tabla_base_id:  $campo_tabla_base_id,
             renombrada: $renombrada, tabla: $tabla, tabla_enlace: $tabla_enlace);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals(' LEFT JOIN adm_seccion AS z ON z.id = adm_seccion.x',$resultado);

        errores::$error = false;
    }

    public function test_string_sql_join(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);
        $data = array();
        $modelo = new adm_seccion($this->link);
        $tabla = 'd';
        $tabla_renombrada = 'd';
        $data['key'] = 'a';
        $data['enlace'] = 'b';
        $data['key_enlace'] = 'c';
        $resultado = $joins->string_sql_join($data, $modelo, $tabla, $tabla_renombrada);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('d AS d  ON d.a = b.c',$resultado);

        errores::$error = false;
    }

    public function test_tablas(){
        errores::$error = false;
        $joins = new joins();
        //$joins = new liberator($joins);


        $modelo = new adm_accion($this->link);
        $renombradas = array();
        $columnas = array();
        $extension_estructura = array();
        $tabla = '';
        $resultado = $joins->tablas($columnas, $extension_estructura, $modelo, $renombradas, $tabla);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('La tabla no puede ir vacia',$resultado['mensaje']);

        errores::$error = false;

        $modelo = new adm_accion($this->link);
        $renombradas = array();
        $columnas = array();
        $extension_estructura = array();
        $tabla = 'adm_seccion';
        $resultado = $joins->tablas($columnas, $extension_estructura, $modelo, $renombradas, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals('adm_seccion AS adm_seccion',$resultado);
        errores::$error = false;
    }

    public function test_tablas_join_base(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);


        $tabla_join = array();
        $tablas = '';
        $resultado = $joins->tablas_join_base($tabla_join, $tablas);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar $tabla_join',$resultado['mensaje']);

        errores::$error = false;

        $tabla_join = array();
        $tablas = '';
        $tabla_join['tabla_base'] = 'adm_seccion';
        $tabla_join['tabla_enlace'] = 'adm_seccion';
        $resultado = $joins->tablas_join_base($tabla_join, $tablas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertEquals(' LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = adm_seccion.adm_seccion_id',$resultado);
        errores::$error = false;
    }

    public function test_tablas_join_esp(){
        errores::$error = false;
        $joins = new joins();
        $joins = new liberator($joins);

        $tabla_join = '';
        $key = '';
        $tablas = '';
        $resultado = $joins->tablas_join_esp($key, $tabla_join, $tablas);
        $this->assertTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertStringContainsStringIgnoringCase('Error al validar join',$resultado['mensaje']);

        errores::$error = false;

        $tabla_join = 'b';
        $key = 'adm_seccion';
        $tablas = '';
        $resultado = $joins->tablas_join_esp($key, $tabla_join, $tablas);
        $this->assertNotTrue(errores::$error);
        $this->assertIsString($resultado);
        $this->assertStringContainsStringIgnoringCase(' LEFT JOIN adm_seccion AS adm_seccion ON adm_seccion.id = b.adm_seccion_id',$resultado);
        errores::$error = false;
    }

}
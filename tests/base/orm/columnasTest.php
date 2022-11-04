<?php
namespace tests\base\orm;

use base\orm\columnas;
use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\administrador\models\adm_usuario;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use stdClass;


class columnasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_add_column(){
        errores::$error = false;

        $modelo = new columnas();
        //$modelo = new liberator($modelo);

        $alias = '';
        $campo = '';
        $resultado = $modelo->add_column(alias: $alias, campo: $campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $alias = '';
        $campo = 'x';
        $resultado = $modelo->add_column(alias: $alias, campo: $campo);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $alias no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $alias = 'x';
        $campo = 'x';
        $resultado = $modelo->add_column(alias: $alias, campo: $campo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('IFNULL( SUM(x) ,0)AS x', $resultado);

        errores::$error = false;
    }

    public function test_ajusta_columnas_completas(){
        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);

        $tabla = '';
        $tabla_renombrada = '';
        $columnas_sql = array();
        $columnas = '';
        $modelo = new adm_seccion($this->link);
        $resultado = $col->ajusta_columnas_completas(columnas: $columnas, columnas_en_bruto:false,
            columnas_sql:  $columnas_sql, modelo: $modelo, tabla: $tabla,tabla_renombrada:  $tabla_renombrada);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar columnas', $resultado['mensaje']);

        errores::$error = false;
        $tabla = 'adm_seccion';
        $tabla_renombrada = '';
        $columnas_sql = array();
        $columnas = '';
        $resultado = $col->ajusta_columnas_completas(columnas: $columnas, columnas_en_bruto:false,
            columnas_sql:  $columnas_sql, modelo: $modelo, tabla: $tabla,tabla_renombrada:  $tabla_renombrada);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion.descripcion AS adm_seccion_descripcion', $resultado);

        errores::$error = false;
        $tabla = 'adm_seccion';
        $tabla_renombrada = 'zeta';
        $columnas_sql = array();
        $columnas = '';
        $resultado = $col->ajusta_columnas_completas(columnas: $columnas, columnas_en_bruto:false,
            columnas_sql:  $columnas_sql, modelo: $modelo, tabla: $tabla,tabla_renombrada:  $tabla_renombrada);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('zeta_etiqueta_label, zeta.status', $resultado);
        errores::$error = false;
    }

    public function test_aplica_columnas_by_table(){
        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);

        $columnas_by_table = array();
        $resultado = $col->aplica_columnas_by_table($columnas_by_table);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;

        $columnas_by_table = array();
        $columnas_by_table[] = '';
        $resultado = $col->aplica_columnas_by_table($columnas_by_table);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_asigna_columna_completa(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $atributo = '';
        $columna = array();
        $columnas_completas = array();
        $resultado = $col->asigna_columna_completa($atributo, $columna, $columnas_completas);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error atributo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;
        $atributo = 'x';
        $columna = array();
        $columnas_completas = array();
        $resultado = $col->asigna_columna_completa($atributo, $columna, $columnas_completas);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar $columna', $resultado['mensaje']);
        $this->assertStringContainsStringIgnoringCase('Error Type no existe en el registro', $resultado['data']['mensaje']);
        errores::$error = false;


        $atributo = 'x';
        $columna = array();
        $columna['Type'] = 'x';
        $columna['Key'] = 'x';
        $columna['Null'] = 'x';
        $columnas_completas = array();
        $resultado = $col->asigna_columna_completa($atributo, $columna, $columnas_completas);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado['x']['campo']);
        errores::$error = false;

    }

    public function test_asigna_columnas_en_session(): void
    {
        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $modelo = new adm_seccion($this->link);
        $tabla_bd = '';
        $resultado = $col->asigna_columnas_en_session(modelo:$modelo, tabla_bd: $tabla_bd);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla_bd no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;

        $tabla_bd = 'a';
        $resultado = $col->asigna_columnas_en_session(modelo:$modelo, tabla_bd: $tabla_bd);
        $this->assertIsBool( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);
        errores::$error = false;
    }

    public function test_asigna_columnas_parseadas(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $columnas_parseadas = array();
        $atributo = '';
        $resultado = $col->asigna_columnas_parseadas(atributo:  $atributo, columnas_parseadas: $columnas_parseadas);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error atributo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;
        $columnas_parseadas = array();
        $atributo = 'x';
        $resultado = $col->asigna_columnas_parseadas(atributo:  $atributo, columnas_parseadas: $columnas_parseadas);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado[0]);
        errores::$error = false;

    }

    public function test_asigna_columnas_session_new(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $tabla_bd = '';
        $modelo = new adm_seccion($this->link);
        $resultado = $col->asigna_columnas_session_new($modelo, $tabla_bd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tabla_bd esta vacia', $resultado['mensaje']);

        errores::$error = false;
        $tabla_bd = 'x';
        $resultado = $col->asigna_columnas_session_new($modelo, $tabla_bd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener columnas', $resultado['mensaje']);

        errores::$error = false;
        $tabla_bd = 'adm_seccion';
        $resultado = $col->asigna_columnas_session_new($modelo, $tabla_bd);


        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('MUL', $resultado->columnas_completas['adm_menu_id']['Key']);
        $this->assertEquals('bigint', $resultado->columnas_completas['adm_menu_id']['Type']);
        errores::$error = false;


    }

    public function test_asigna_data_columnas()
    {
        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);

        $data = new stdClass();
        $tabla_bd = '';
        $resultado = $col->asigna_data_columnas($data, $tabla_bd);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla_bd no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;

        $data = new stdClass();
        unset($_SESSION['campos_tabla']);
        $tabla_bd = 'a';
        $resultado = $col->asigna_data_columnas($data, $tabla_bd);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error debe existir SESSION[campos_tabla]', $resultado['mensaje']);

        errores::$error = false;

        $data = new stdClass();
        $_SESSION['campos_tabla'] = 'a';
        $tabla_bd = 'a';
        $resultado = $col->asigna_data_columnas($data, $tabla_bd);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error debe existir SESSION[campos_tabla][a]', $resultado['mensaje']);

        errores::$error = false;

        $data = new stdClass();
        $_SESSION = array();
        $_SESSION['campos_tabla']['a'] = 'a';
        $_SESSION['columnas_completas']['a'] = 'a';


        $tabla_bd = 'a';
        $resultado = $col->asigna_data_columnas($data, $tabla_bd);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->columnas_parseadas);
        $this->assertEquals('a', $resultado->columnas_completas);
        errores::$error = false;
    }

    public function test_campos_tabla(){
        errores::$error = false;

        $col = new columnas();
        //$modelo = new liberator($modelo);

        $modelo = new adm_usuario($this->link);

        $tabla = '';
        $resultado = $col->campos_tabla($modelo, $tabla);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('id', $resultado[0]);
        $this->assertEquals('user', $resultado[1]);
        $this->assertEquals('password', $resultado[2]);
        $this->assertEquals('email', $resultado[3]);
        $this->assertEquals('adm_grupo_id', $resultado[4]);
        $this->assertEquals('status', $resultado[5]);
        $this->assertEquals('usuario_alta_id', $resultado[6]);
        $this->assertEquals('usuario_update_id', $resultado[7]);
        $this->assertEquals('fecha_alta', $resultado[8]);
        $this->assertEquals('fecha_update', $resultado[9]);
        $this->assertEquals('session', $resultado[10]);
        $this->assertEquals('telefono', $resultado[11]);
        errores::$error = false;
    }

    public function test_carga_columna_renombre(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $modelo = new adm_seccion($this->link);
        //$modelo_base = new liberator($modelo_base);
        $columnas_sql = array();
        $data = array();
        $columnas = '';
        $tabla = '';
        $resultado = $col->carga_columna_renombre($columnas, $columnas_sql, $data, $modelo, $tabla);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar data', $resultado['mensaje']);

        errores::$error = false;
        $columnas_sql = array();
        $data = array();
        $columnas = '';
        $tabla = '';
        $data['nombre_original'] = 'adm_seccion';
        $resultado = $col->carga_columna_renombre($columnas, $columnas_sql, $data, $modelo, $tabla);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion_descripcion, adm_seccion.etiqueta_label', $resultado);
        errores::$error = false;
    }

    public function test_columnas(): void
    {
        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $modelo = new adm_seccion($this->link);
        $columnas_sql = array();
        $aplica_columnas_by_table = false;
        $columnas_by_table = array();
        $columnas_en_bruto = true;
        $extension_estructura = array();
        $renombres = array();
        $tablas_select = array('adm_seccion'=>false,'adm_accion'=>'adm_seccion');

        $resultado = $col->columnas($aplica_columnas_by_table, $columnas_by_table, $columnas_en_bruto, $columnas_sql,
            $extension_estructura, $modelo, $renombres, $tablas_select);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion.etiqueta_label AS etiqueta_label,', $resultado);

        errores::$error = false;
    }

    public function test_columnas_attr(){

        errores::$error = false;
        $modelo = new columnas();
        $modelo = new liberator($modelo);
        $columna = array();
        $columnas_parseadas = array();
        $columnas_completas = array();
        $resultado = $modelo->columnas_attr($columna, $columnas_parseadas, $columnas_completas);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado->columnas_parseadas);

        errores::$error = false;
        $columna = array();
        $columnas_parseadas = array();
        $columnas_completas = array();
        $columna['Field'] = 'x';
        $columna['Type'] = 'x';
        $columna['Null'] = 'x';
        $resultado = $modelo->columnas_attr($columna, $columnas_parseadas, $columnas_completas);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $resultado->columnas_parseadas[0]);

        errores::$error = false;

    }

    public function test_columnas_base(){
        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);

        $columnas_en_bruto = true;
        $columnas_sql = array();
        $extension_estructura = array();
        $modelo = new adm_seccion($this->link);
        $renombres = array();
        $tablas_select = array();
        $resultado = $col->columnas_base($columnas_en_bruto, $columnas_sql, $extension_estructura,
            $modelo, $renombres, $tablas_select);


        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_seccion.id AS id, adm_seccion.descripcion AS descripcion, adm_seccion.etiqueta_label AS etiqueta_label, adm_seccion.status AS status, adm_seccion.adm_menu_id AS adm_menu_id, adm_seccion.icono AS icono, adm_seccion.fecha_alta AS fecha_alta, adm_seccion.fecha_update AS fecha_update, adm_seccion.usuario_alta_id AS usuario_alta_id, adm_seccion.usuario_update_id AS usuario_update_id, adm_seccion.codigo AS codigo, adm_seccion.codigo_bis AS codigo_bis, adm_seccion.descripcion_select AS descripcion_select, adm_seccion.alias AS alias', $resultado);

        errores::$error = false;

        $columnas_en_bruto = true;
        $columnas_sql = array();
        $extension_estructura = array();
        $modelo = new adm_seccion($this->link);
        $renombres = array();
        $tablas_select = array();
        $tablas_select['adm_accion'] = 'x';
        $resultado = $col->columnas_base($columnas_en_bruto, $columnas_sql, $extension_estructura,
            $modelo, $renombres, $tablas_select);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion.id AS id', $resultado);
        $this->assertStringContainsStringIgnoringCase(' adm_seccion.etiqueta_label', $resultado);
        errores::$error = false;
    }

    public function test_campos_no_upd(){

        errores::$error = false;
        $col = new columnas();
        //$col = new liberator($col);
        $registro = array();
        $campos_no_upd = array();
        $registro['a'] = 'x';
        $campos_no_upd[] = 'a';
        $resultado = $col->campos_no_upd($campos_no_upd, $registro);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;
    }



    public function test_columnas_bd_native(){

        errores::$error = false;
        $col = new columnas();
        //$col = new liberator($col);
        $tabla_bd = '';
        $modelo = new adm_seccion($this->link);
        $resultado = $col->columnas_bd_native($modelo,$tabla_bd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tabla_bd esta vacia', $resultado['mensaje']);

        errores::$error = false;
        $tabla_bd = 'x';
        $resultado = $col->columnas_bd_native($modelo,$tabla_bd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al ejecutar sql', $resultado['mensaje']);

        errores::$error = false;
        $tabla_bd = 'adm_seccion';
        $resultado = $col->columnas_bd_native($modelo,$tabla_bd);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('id', $resultado[0]['Field']);
        errores::$error = false;
    }

    public function test_columnas_by_table(): void
    {
        errores::$error = false;

        $mb = new columnas();
        $mb = new liberator($mb);

        $columnas_by_table =  array();
        $columnas_by_table[] = 'adm_usuario';
        $columnas_en_bruto = true;
        $modelo = new adm_usuario($this->link);

        $resultado = $mb->columnas_by_table($columnas_by_table, $columnas_en_bruto, $modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_usuario.id AS id, adm_usuario.user AS user', $resultado);

        errores::$error = false;


        $columnas_by_table =  array();
        $columnas_by_table[] = 'adm_usuario';
        $columnas_en_bruto = false;
        $modelo = new adm_usuario($this->link);

        $resultado = $mb->columnas_by_table($columnas_by_table, $columnas_en_bruto, $modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_usuario.id AS adm_usuario_id, adm_usuario.user AS adm_usuario_user', $resultado);
        errores::$error = false;
    }

    public function test_columnas_envio(){

        errores::$error = false;
        $modelo_base = new columnas();
        $modelo_base = new liberator($modelo_base);
        $columnas_extra_sql = '';
        $columnas_sql = '';
        $resultado = $modelo_base->columnas_envio($columnas_extra_sql, $columnas_sql);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;
    }

    public function test_columnas_extension(){
        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $modelo = new adm_seccion($this->link);
        $extension_estructura = array();
        $columnas_sql = array();
        $columnas = '';
        $resultado = $col->columnas_extension( $columnas, $columnas_sql, $extension_estructura,$modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;
        $extension_estructura = array();
        $columnas_sql = array();
        $columnas = 'a';
        $resultado = $col->columnas_extension($columnas, $columnas_sql, $extension_estructura,$modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado);

        errores::$error = false;
        $extension_estructura = array();
        $columnas_sql = array();
        $columnas = 'a';
        $extension_estructura[] = '';
        $resultado = $col->columnas_extension($columnas, $columnas_sql, $extension_estructura,$modelo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error ingrese un array valido', $resultado['mensaje']);

        errores::$error = false;
        $extension_estructura = array();
        $columnas_sql = array();
        $columnas = 'a';
        $extension_estructura['a'] = '';
        $resultado = $col->columnas_extension($columnas, $columnas_sql, $extension_estructura,$modelo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al llamar datos', $resultado['mensaje']);

        errores::$error = false;
        $extension_estructura = array();
        $columnas_sql = array();
        $columnas = 'a';
        $extension_estructura['adm_seccion'] = '';
        $resultado = $col->columnas_extension($columnas, $columnas_sql, $extension_estructura,$modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion.adm_menu_id', $resultado);
        errores::$error = false;
    }

    public function test_columnas_filed(){
        errores::$error = false;

        $mb = new columnas();
        $mb = new liberator($mb);
        $atributo = '';
        $campo = '';
        $columna = array();
        $columnas_completas =  array();
        $columnas_parseadas =  array();
        $resultado = $mb->columnas_field($atributo, $campo, $columna, $columnas_completas, $columnas_parseadas);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_columnas_full(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);


        $extension_estructura = array();
        $tablas_select = array();
        $columnas_sql = array();
        $modelo = new adm_seccion($this->link);
        $renombres = array();
        $resultado = $col->columnas_full( array(), false,$columnas_sql, $extension_estructura, $modelo, $renombres, $tablas_select);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);


        errores::$error = false;
        $extension_estructura = array();
        $tablas_select = array('adm_accion'=>'seccion');
        $columnas_sql = array();
        $modelo = new adm_seccion($this->link);
        $renombres = array();
        $columas_by_table = array();
        $resultado = $col->columnas_full($columas_by_table, false, $columnas_sql, $extension_estructura, $modelo,
            $renombres, $tablas_select);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_accion.descripcion AS adm_accion_descripcion',$resultado);

        errores::$error = false;
        $extension_estructura = array();
        $tablas_select = array('adm_accion'=>false);
        $columnas_sql = array();
        $modelo = new adm_seccion($this->link);
        $renombres = array();
        $columas_by_table = array('adm_menu');
        $resultado = $col->columnas_full($columas_by_table, false, $columnas_sql, $extension_estructura, $modelo,
            $renombres, $tablas_select);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_menu.id AS adm_menu_id, adm_menu.descripcion',$resultado);


        errores::$error = false;
    }

    public function test_columnas_renombre(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $columnas = '';
        $columnas_sql = array();
        $modelo = new adm_seccion($this->link);
        $renombres = array();
        $resultado = $col->columnas_renombre($columnas, $columnas_sql, $modelo, $renombres);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);
        errores::$error = false;

    }

    public function test_columnas_sql(){

        errores::$error = false;
        $mb = new columnas();
        $mb = new liberator($mb);
        $columnas_sql = '';
        $tabla_nombre = '';
        $columna_parseada = '';
        $alias_columnas = '';
        $resultado = $mb->columnas_sql(alias_columnas: $alias_columnas, columna_parseada: $columna_parseada,
            columnas_en_bruto: false, columnas_sql: $columnas_sql, tabla_nombre: $tabla_nombre);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tabla_nombre no puede venir vacia', $resultado['mensaje']);
        errores::$error = false;

        $columnas_sql = '';
        $tabla_nombre = 'x';
        $columna_parseada = '';
        $alias_columnas = '';
        $resultado = $mb->columnas_sql(alias_columnas: $alias_columnas, columna_parseada: $columna_parseada,
            columnas_en_bruto: false, columnas_sql: $columnas_sql, tabla_nombre: $tabla_nombre);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $columna_parseada no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;
        $columnas_sql = '';
        $tabla_nombre = 'x';
        $columna_parseada = 'x';
        $alias_columnas = '';
        $resultado = $mb->columnas_sql(alias_columnas: $alias_columnas, columna_parseada: $columna_parseada,
            columnas_en_bruto: false, columnas_sql: $columnas_sql, tabla_nombre: $tabla_nombre);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $alias_columnas no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;
        $columnas_sql = '';
        $tabla_nombre = 'x';
        $columna_parseada = 'x';
        $alias_columnas = 'x';
        $resultado = $mb->columnas_sql(alias_columnas: $alias_columnas, columna_parseada: $columna_parseada,
            columnas_en_bruto: false, columnas_sql: $columnas_sql, tabla_nombre: $tabla_nombre);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('x.x AS x', $resultado);

        errores::$error = false;
        $columnas_sql = 'x';
        $tabla_nombre = 'x';
        $columna_parseada = 'x';
        $alias_columnas = 'x';
        $resultado = $mb->columnas_sql(alias_columnas: $alias_columnas, columna_parseada: $columna_parseada,
            columnas_en_bruto: false, columnas_sql: $columnas_sql, tabla_nombre: $tabla_nombre);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('x.x AS x', $resultado);


        errores::$error = false;
        $columnas_sql = '';
        $tabla_nombre = 'x';
        $columna_parseada = 'x';
        $alias_columnas = 'x';
        $columnas_en_bruto = true;
        $resultado = $mb->columnas_sql(alias_columnas: $alias_columnas, columna_parseada: $columna_parseada,
            columnas_en_bruto: $columnas_en_bruto, columnas_sql: $columnas_sql, tabla_nombre: $tabla_nombre);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('x.x AS x', $resultado);

        errores::$error = false;

    }

    public function test_columnas_sql_array(){

        errores::$error = false;
        $mb = new columnas();
        $mb = new liberator($mb);
        $columnas = array();
        $resultado = $mb->columnas_sql_array($columnas);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty( $resultado->columnas_completas);

        errores::$error = false;
        $columnas = array();
        $columnas[] = '';
        $resultado = $mb->columnas_sql_array($columnas);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);

        errores::$error = false;
        $columnas = array();
        $columnas[] = array();
        $resultado = $mb->columnas_sql_array($columnas);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty( $resultado->columnas_completas);

        errores::$error = false;
        $columnas = array();
        $columnas[] = array();
        $columnas[][] = '';
        $resultado = $mb->columnas_sql_array($columnas);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty( $resultado->columnas_completas);
        errores::$error = false;

    }

    public function test_columnas_sql_init(){

        errores::$error = false;
        $mb = new columnas($this->link);
        $mb = new liberator($mb);

        $columnas_parseadas = array();
        $columnas = array();
        $resultado = $mb->columnas_sql_init($columnas,false, $columnas_parseadas,'');
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tabla_nombre no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;
        $resultado = $mb->columnas_sql_init($columnas,false, $columnas_parseadas,'x');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
        $columnas_parseadas[] = 'z';
        $resultado = $mb->columnas_sql_init($columnas,true, $columnas_parseadas,'x');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x.z AS z', $resultado);

        errores::$error = false;
        $columnas_parseadas = array();
        $columnas_parseadas[] = 'z';
        $resultado = $mb->columnas_sql_init($columnas,false, $columnas_parseadas,'x');
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x.z AS x_z', $resultado);
        errores::$error = false;

    }

    public function test_columnas_tablas_select(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $modelo = new adm_seccion($this->link);
        $tablas_select = array();
        $columnas_sql = array();

        $resultado = $col->columnas_tablas_select(false,$columnas_sql, $modelo, $tablas_select);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;
        $tablas_select = array();
        $columnas_sql = array();

        $resultado = $col->columnas_tablas_select(false,$columnas_sql, $modelo, $tablas_select);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);

        errores::$error = false;
        $tablas_select = array();
        $columnas_sql = array();

        $tablas_select[] = '';
        $resultado = $col->columnas_tablas_select(false,$columnas_sql, $modelo, $tablas_select);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $key no puede ser un numero', $resultado['mensaje']);

        errores::$error = false;
        $tablas_select = array();
        $columnas_sql = array();

        $tablas_select['adm_seccion'] = '';
        $resultado = $col->columnas_tablas_select(false,$columnas_sql, $modelo, $tablas_select);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion.etiqueta_label',$resultado);

        errores::$error = false;

    }

    public function test_data_for_columnas_envio(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $columnas = array();
        $modelo = new adm_seccion($this->link);
        $tabla_original = '';
        $tabla_renombrada = '';
        $resultado = $col->data_for_columnas_envio($columnas,false,$modelo, $tabla_original, $tabla_renombrada);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla original no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;
        $columnas = array();
        $tabla_original = 'a';
        $tabla_renombrada = '';
        $resultado = $col->data_for_columnas_envio($columnas,false,$modelo, $tabla_original, $tabla_renombrada);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al llamar datos', $resultado['mensaje']);

        errores::$error = false;
        $columnas = array();
        $tabla_original = 'adm_seccion';
        $tabla_renombrada = '';
        $resultado = $col->data_for_columnas_envio($columnas,false,$modelo, $tabla_original, $tabla_renombrada);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion.id AS adm_seccion_id', $resultado->columnas_sql);

        errores::$error = false;
        $columnas = array();
        $tabla_original = 'adm_seccion';
        $tabla_renombrada = 'z';
        $resultado = $col->data_for_columnas_envio($columnas,false,$modelo, $tabla_original, $tabla_renombrada);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('z.id AS z_id', $resultado->columnas_sql);
        errores::$error = false;
    }

    public function test_genera_columnas_consulta(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $modelo = new adm_seccion($this->link);
        $tabla_original = '';
        $tabla_renombrada = '';
        $resultado = $col->genera_columnas_consulta(false,$modelo,$tabla_original, $tabla_renombrada);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al datos para columnas', $resultado['mensaje']);

        errores::$error = false;
        $tabla_original = 'x';
        $tabla_renombrada = '';
        $resultado = $col->genera_columnas_consulta(false,$modelo,$tabla_original, $tabla_renombrada);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al datos para columnas', $resultado['mensaje']);

        errores::$error = false;
        $tabla_original = 'adm_seccion';
        $tabla_renombrada = '';
        $resultado = $col->genera_columnas_consulta(false,$modelo,$tabla_original, $tabla_renombrada);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion.id AS adm_seccion_id', $resultado);

        errores::$error = false;
        $tabla_original = 'adm_seccion';
        $tabla_renombrada = 'abc';
        $resultado = $col->genera_columnas_consulta(false,$modelo,$tabla_original, $tabla_renombrada);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('abc.id AS abc_id', $resultado);

        errores::$error = false;

    }

    public function test_genera_columnas_extra(){

        errores::$error = false;
        $col = new columnas();
        $modelo = new adm_seccion($this->link);
        $col = new liberator($col);
        $columnas = array();
        $resultado = $col->genera_columnas_extra($columnas,$modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);

        errores::$error = false;
        $columnas = array();
        $columnas[] = '';
        $resultado = $col->genera_columnas_extra($columnas, $modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);

        errores::$error = false;
        $columnas = array();
        $columnas[] = 'x';
        $resultado = $col->genera_columnas_extra($columnas, $modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("", $resultado);
        errores::$error = false;

    }

    public function test_genera_columnas_filed(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $modelo = new adm_seccion($this->link);
        $tabla_bd = '';
        $resultado = $col->genera_columnas_field($modelo,$tabla_bd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error $tabla_bd esta vacia', $resultado['mensaje']);

        errores::$error = false;
        $tabla_bd = 'x';
        $resultado = $col->genera_columnas_field($modelo,$tabla_bd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener columnas', $resultado['mensaje']);

        errores::$error = false;
        $tabla_bd = 'adm_seccion';
        $resultado = $col->genera_columnas_field($modelo,$tabla_bd);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('adm_menu_id', $resultado->columnas_parseadas[4]);
        errores::$error = false;
    }

    public function test_genera_columna_tabla(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $modelo = new adm_seccion($this->link);
        $columnas = '';
        $columnas_sql = array();
        $key = '';
        $resultado = $col->genera_columna_tabla($columnas, false, $columnas_sql, $key, $modelo);

        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al integrar columnas', $resultado['mensaje']);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);
        $columnas = '';
        $columnas_sql = array();
        $key = 'a';
        $resultado = $col->genera_columna_tabla($columnas, false, $columnas_sql, $key, $modelo);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al llamar datos', $resultado['mensaje']);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);
        $columnas = '';
        $columnas_sql = array();
        $key = 'adm_seccion';
        $resultado = $col->genera_columna_tabla($columnas, false, $columnas_sql, $key, $modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion.descripcion AS adm_seccion_descripcion', $resultado);
        errores::$error = false;
    }

    public function test_genera_columnas_tabla(){

        errores::$error = false;
        $col = new columnas();
        $col = new liberator($col);
        $tabla_original = '';
        $modelo = new adm_seccion($this->link);
        $tabla_renombrada = '';
        $resultado = $col->genera_columnas_tabla(false,$modelo, $tabla_original, $tabla_renombrada);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error tabla original no puede venir vacia', $resultado['mensaje']);

        errores::$error = false;
        $tabla_original = 'x';
        $tabla_renombrada = '';
        $resultado = $col->genera_columnas_tabla(false,$modelo,$tabla_original, $tabla_renombrada);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al obtener columnas', $resultado['mensaje']);

        errores::$error = false;
        $tabla_original = 'adm_seccion';
        $tabla_renombrada = '';
        $resultado = $col->genera_columnas_tabla(false,$modelo,$tabla_original, $tabla_renombrada);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion.id AS adm_seccion_id', $resultado);

        errores::$error = false;
        $tabla_original = 'adm_seccion';
        $tabla_renombrada = 'adm_seccion_x';
        $resultado = $col->genera_columnas_tabla(false,$modelo,$tabla_original, $tabla_renombrada);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion_x.id AS adm_seccion_x_id', $resultado);
        errores::$error = false;


    }

    public function test_init_columnas_by_table(): void
    {
        errores::$error = false;

        $mb = new columnas();
        $mb = new liberator($mb);

        $columnas_by_table =  array();
        $columnas_by_table[] = 'a';
        $columnas_by_table[] = 'b';
        $resultado = $mb->init_columnas_by_table($columnas_by_table);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado->columnas_sql);
        $this->assertEquals(false,$resultado->tablas_select['a']);
        $this->assertEquals(false,$resultado->tablas_select['b']);
        errores::$error = false;
    }

    public function test_integra_columnas(){
        errores::$error = false;
        $mb = new columnas();
        $mb = new liberator($mb);

        $columnas = '';
        $resultado_columnas = '';
        $resultado = $mb->integra_columnas(columnas: $columnas,resultado_columnas:  $resultado_columnas);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
        $columnas = 'a';
        $resultado_columnas = '';
        $resultado = $mb->integra_columnas(columnas: $columnas,resultado_columnas:  $resultado_columnas);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado->columnas);
        $this->assertIsBool($resultado->continue);
        $this->assertTrue($resultado->continue);
        errores::$error = false;
    }

    public function test_integra_columnas_por_data(){
        errores::$error = false;
        $mb = new columnas();
        $mb = new liberator($mb);

        $columnas = '';
        $resultado_columnas = '';
        $resultado = $mb->integra_columnas_por_data(columnas: $columnas, resultado_columnas: $resultado_columnas);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $columnas = 'a';
        $resultado_columnas = '';
        $resultado = $mb->integra_columnas_por_data(columnas: $columnas, resultado_columnas: $resultado_columnas);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado);

        errores::$error = false;

        $columnas = 'a';
        $resultado_columnas = 'b';
        $resultado = $mb->integra_columnas_por_data(columnas: $columnas, resultado_columnas: $resultado_columnas);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a, b', $resultado);
        errores::$error = false;
    }

    public function test_obten_columnas(){

        errores::$error = false;
        $col = new columnas();
        $col = (new liberator($col));
        $modelo = new adm_seccion($this->link);
        $resultado = $col->obten_columnas($modelo,'adm_seccion');

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('descripcion',$resultado->columnas_parseadas[1]);
        $this->assertCount(14, $resultado->columnas_parseadas);
        errores::$error = false;

    }

    public function test_obten_columnas_completas(){

        errores::$error = false;
        $col = new columnas();
        $modelo = new adm_seccion($this->link);
        $resultado = $col->obten_columnas_completas($modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('adm_seccion.etiqueta_label AS adm_seccion_etiqueta_label',$resultado);

        errores::$error = false;

    }

    public function test_sub_querys(){

        errores::$error = false;
        $col = new columnas();
        $modelo = new adm_seccion($this->link);
        $columnas = '';
        $resultado = $col->sub_querys($columnas, $modelo);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado);
        errores::$error = false;
    }


}
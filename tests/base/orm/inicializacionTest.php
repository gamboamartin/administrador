<?php
namespace tests\base\orm;

use gamboamartin\administrador\models\adm_seccion;
use gamboamartin\encripta\encriptador;
use gamboamartin\errores\errores;

use base\orm\inicializacion;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use stdClass;



class inicializacionTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_ajusta_campos_upd(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);

        $modelo = new adm_seccion($this->link);
        $id = 1;
        $resultado = $inicializacion->ajusta_campos_upd($id, $modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);
        $modelo->registro_upd['status'] = '';
        $id = 1;
        $resultado = $inicializacion->ajusta_campos_upd($id, $modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado['status']);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);
        $modelo->registro_upd['status'] = 'z';
        $id = 1;
        $resultado = $inicializacion->ajusta_campos_upd($id, $modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('z',$resultado['status']);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);
        $modelo->registro_upd['status'] = 'activo';
        $id = 1;
        $resultado = $inicializacion->ajusta_campos_upd($id, $modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;

        $modelo = new adm_seccion($this->link);
        $modelo->registro_upd['status'] = 'inactivo';
        $id = 1;
        $resultado = $inicializacion->ajusta_campos_upd($id, $modelo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('inactivo',$resultado['status']);

        errores::$error = false;
    }

    public function test_ajusta_params(): void
    {
        errores::$error = false;
        $modelo = new inicializacion();
        //$modelo = new liberator($modelo);


        $complemento = new stdClass();
        $resultado = $modelo->ajusta_params($complemento);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado->params->offset);
        $this->assertEquals('',$resultado->params->group_by);
        $this->assertEquals('',$resultado->params->order);
        $this->assertEquals('',$resultado->params->limit);
        errores::$error = false;
    }

    public function test_ajusta_registro_upd(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);


        $registro_previo = new stdClass();
        $registro_previo->z = 'z';

        $modelo = new adm_seccion($this->link);
        $campo = 'z';
        $value_upd = 'z';
        $modelo->registro_upd['z'] = 'z';
        $resultado = $inicializacion->ajusta_registro_upd($campo, $modelo, $registro_previo, $value_upd);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEmpty($resultado);

        errores::$error = false;



        $registro_previo = new stdClass();
        $registro_previo->z = 'z';

        $modelo = new adm_seccion($this->link);
        $campo = 'z';
        $value_upd = 'd';
        $modelo->registro_upd['z'] = 'z';
        $resultado = $inicializacion->ajusta_registro_upd($campo, $modelo, $registro_previo, $value_upd);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotEmpty($resultado);
        errores::$error = false;

    }

    public function test_asigna_data_campo(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);


        $bools = array();
        $campo = array();
        $datos = new stdClass();

        $resultado = $inicializacion->asigna_data_campo($bools, $campo, $datos);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

    }

    public function test_asigna_valor_desencriptado(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);


        $campos_encriptados = array();
        $row = array();
        $row['a'] = 'PHDA/NloYgF1lc+UHzxaUw==';
        $campos_encriptados[] = 'a';
        $resultado = $inicializacion->asigna_valor_desencriptado($campos_encriptados, $row);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado['a']);
        errores::$error = false;
    }

    public function test_asigna_valor_encriptado(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $campo_limpio = new stdClass();
        $registro = array();

        $resultado = $inicializacion->asigna_valor_encriptado($campo_limpio, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campo_limpio', $resultado['mensaje']);

        errores::$error = false;

        $campo_limpio = new stdClass();
        $campo_limpio->valor = '';
        $campo_limpio->campo = '';
        $registro = array();

        $resultado = $inicializacion->asigna_valor_encriptado($campo_limpio, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campo_limpio', $resultado['mensaje']);

        errores::$error = false;

        $campo_limpio = new stdClass();
        $campo_limpio->valor = '';
        $campo_limpio->campo = 'a';
        $registro = array();

        $resultado = $inicializacion->asigna_valor_encriptado($campo_limpio, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);

        errores::$error = false;

        $campo_limpio = new stdClass();
        $campo_limpio->valor = '';
        $campo_limpio->campo = 'a';
        $registro = array();
        $registro['a'] = 'z';

        $resultado = $inicializacion->asigna_valor_encriptado($campo_limpio, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('PHDA/NloYgF1lc+UHzxaUw==', $resultado['a']);

        errores::$error = false;

        $campo_limpio = new stdClass();
        $campo_limpio->valor = 'z';
        $campo_limpio->campo = 'a';
        $registro = array();
        $registro['a'] = 'z';

        $resultado = $inicializacion->asigna_valor_encriptado($campo_limpio, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('65RRm7OkwNx4LtwV7rJRnA==', $resultado['a']);
        errores::$error = false;
    }

    public function test_data_in_sql(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $llave = 'a';
        $values_in = array(1);
        $resultado = $inicializacion->data_in_sql($llave, $values_in);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_encripta_valor_registro(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $campo = '';
        $campos_encriptados = array();
        $registro = array();
        $valor = '';

        $resultado = $inicializacion->encripta_valor_registro($campo, $campos_encriptados, $registro, $valor);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;


        $campo = 'a';
        $campos_encriptados = array();
        $registro = array();
        $valor = '';

        $resultado = $inicializacion->encripta_valor_registro($campo, $campos_encriptados, $registro, $valor);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar registro', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $campos_encriptados = array();
        $registro = array();
        $valor = '';
        $registro['a'] = 'prueba';

        $resultado = $inicializacion->encripta_valor_registro($campo, $campos_encriptados, $registro, $valor);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('prueba', $resultado['a']);

        errores::$error = false;

        $campo = 'a';
        $campos_encriptados = array();
        $registro = array();
        $valor = '';
        $registro['a'] = 'prueba';
        $campos_encriptados = array('z','a');

        $resultado = $inicializacion->encripta_valor_registro($campo, $campos_encriptados, $registro, $valor);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('PHDA/NloYgF1lc+UHzxaUw==', $resultado['a']);
        errores::$error = false;
    }

    public function test_encripta_valores_registro(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $campos_encriptados = array();
        $registro = array();

        $resultado = $inicializacion->encripta_valores_registro($campos_encriptados, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el registro no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $campos_encriptados = array();
        $registro = array();
        $registro[] = '';

        $resultado = $inicializacion->encripta_valores_registro($campos_encriptados, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

        $campos_encriptados = array();
        $registro = array();
        $registro['z'] = '';

        $resultado = $inicializacion->encripta_valores_registro($campos_encriptados, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado['z']);

        errores::$error = false;

        $campos_encriptados = array();
        $registro = array();
        $registro['z'] = '';
        $campos_encriptados = array('z','p');

        $resultado = $inicializacion->encripta_valores_registro($campos_encriptados, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('PHDA/NloYgF1lc+UHzxaUw==',$resultado['z']);

        errores::$error = false;

        $campos_encriptados = array();
        $registro = array();
        $registro['z'] = '';
        $registro['p'] = 'hola . x';
        $campos_encriptados = array('z','p');

        $resultado = $inicializacion->encripta_valores_registro($campos_encriptados, $registro);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('PHDA/NloYgF1lc+UHzxaUw==',$resultado['z']);
        $this->assertEquals('m0iIjKzl1DrfZ17LaSD5Zg==',$resultado['p']);
        errores::$error = false;
    }

    public function test_init_bools(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $bools = array();
        $bools[] = '';
        $resultado = $inicializacion->init_bools($bools);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertCount(6, $resultado);
        $this->assertEquals('', $resultado['con_label']);
        $this->assertEquals('', $resultado['required']);
        $this->assertEquals('', $resultado['ln']);

        errores::$error = false;

        $bools = array();
        $bools['con_label'] = 'x';
        $resultado = $inicializacion->init_bools($bools);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertCount(5, $resultado);
        $this->assertEquals('x', $resultado['con_label']);
        $this->assertEquals('', $resultado['required']);
        $this->assertEquals('', $resultado['ln']);

        errores::$error = false;
    }

    public function test_init_campo(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);
        $campo = array();
        $resultado = $inicializacion->init_campo($campo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertCount(7, $resultado);
        $this->assertEquals('', $resultado['elemento_lista_cols']);
        $this->assertEquals('', $resultado['elemento_lista_tipo']);
        $this->assertEquals('', $resultado['elemento_lista_tabla_externa']);
        $this->assertEquals('', $resultado['elemento_lista_etiqueta']);
        $this->assertEquals('', $resultado['elemento_lista_campo']);
        $this->assertEquals('', $resultado['elemento_lista_descripcion']);
        $this->assertEquals('', $resultado['elemento_lista_id']);

        errores::$error = false;
        $campo = array();
        $campo['elemento_lista_cols'] = 7;
        $resultado = $inicializacion->init_campo($campo);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertCount(7, $resultado);
        $this->assertEquals('7', $resultado['elemento_lista_cols']);
        $this->assertEquals('', $resultado['elemento_lista_tipo']);
        $this->assertEquals('', $resultado['elemento_lista_tabla_externa']);
        $this->assertEquals('', $resultado['elemento_lista_etiqueta']);
        $this->assertEquals('', $resultado['elemento_lista_campo']);
        $this->assertEquals('', $resultado['elemento_lista_descripcion']);
        $this->assertEquals('', $resultado['elemento_lista_id']);


        errores::$error = false;

    }

    public function test_init_data(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);


        $bools = array();
        $campo = array();
        $datos = new stdClass();

        $resultado = $inicializacion->init_data($bools, $campo, $datos);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_init_datos(): void
    {
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);
        $datos = new stdClass();
        $resultado = $inicializacion->init_datos($datos);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
        $datos = new stdClass();
        $datos->columnas = 'x';
        $resultado = $inicializacion->init_datos($datos);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x', $datos->columnas);
        errores::$error = false;
    }

    public function test_init_params(): void
    {
        errores::$error = false;
        $modelo = new inicializacion();
        $modelo = new liberator($modelo);


        $complemento = new stdClass();
        $resultado = $modelo->init_params($complemento);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('',$resultado->params->offset);
        $this->assertEquals('',$resultado->params->group_by);
        $this->assertEquals('',$resultado->params->order);
        $this->assertEquals('',$resultado->params->limit);
        errores::$error = false;
    }

    public function test_init_upd(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);


        $registro = array();
        $registro[] = '';
        $modelo = new adm_seccion($this->link);
        $id = 1;
        $resultado = $inicializacion->init_upd($id, $modelo, $registro);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_integra_value_in(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $values_in = array();
        $value = 1;
        $resultado = $inicializacion->integra_value_in($value, $values_in);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado[0]);

    }

    public function test_limpia_valores(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $campo = '';
        $valor = '';
        $resultado = $inicializacion->limpia_valores($campo, $valor);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;


        $campo = 'a';
        $valor = ' z ';
        $resultado = $inicializacion->limpia_valores($campo, $valor);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a',$resultado->campo);
        $this->assertEquals('z',$resultado->valor);
        errores::$error = false;
    }

    public function test_maqueta_campo_envio(): void
    {
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);


        $bools = array();
        $campo = array();
        $representacion = '';
        $valor_extra = array();
        $vista = '';

        $campo['adm_elemento_lista_campo'] = 'a';
        $campo['adm_elemento_lista_cols'] = '1';
        $campo['adm_elemento_lista_tipo'] = '1';
        $campo['adm_elemento_lista_tabla_externa'] = '1';
        $campo['adm_elemento_lista_etiqueta'] = '1';
        $campo['adm_elemento_lista_descripcion'] = '1';
        $campo['adm_elemento_lista_id'] = '1';
        $bools['con_label'] = '1';
        $bools['required'] = '1';
        $bools['ln'] = '1';
        $bools['select_vacio_alta'] = '1';

        $resultado = $inicializacion->maqueta_campo_envio($bools, $campo, $representacion, $valor_extra, $vista);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_registro_ins(): void
    {
        errores::$error = false;
        $modelo = new inicializacion();
        //$modelo = new liberator($modelo);

        $status_default = '';
        $registro = array();
        $resultado = $modelo->registro_ins(array(),$registro, $status_default, array());
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error status_default no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $status_default = 'a';
        $registro = array();
        $resultado = $modelo->registro_ins(array(),$registro, $status_default, array());
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado['status']);

        errores::$error = false;
        $campos_encriptados = array('status');
        $status_default = 'a';
        $registro = array();
        $resultado = $modelo->registro_ins($campos_encriptados,$registro, $status_default, array());
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('SE7WlTQsa7AceVeNzanVCg==', $resultado['status']);

        errores::$error = false;
        $campos_encriptados = array('status');
        $status_default = 'p';
        $registro = array();
        $resultado = $modelo->registro_ins($campos_encriptados,$registro, $status_default, array());
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('OM+frc65i8dAfMJhbnMd9A==', $resultado['status']);

        errores::$error = false;
    }

    public function test_result_warning_upd(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);

        $registro_upd = array();
        $id = 1;
        $resultado = new stdClass();
        $resultado = $inicializacion->result_warning_upd($id, $registro_upd, $resultado);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('Info no hay elementos a modificar', $resultado->mensaje);
        $this->assertEquals('', $resultado->sql);
        $this->assertEquals('', $resultado->result);
        $this->assertEquals(1, $resultado->registro_id);
        $this->assertEquals('warning', $resultado->salida);
        $this->assertEmpty( $resultado->registro);
        errores::$error = false;
    }

    public function test_status(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $registro = array();
        $status_default = '';
        $resultado = $inicializacion->status($registro, $status_default);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error status_default no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;


        $registro = array();
        $status_default = 'a';
        $resultado = $inicializacion->status($registro, $status_default);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('a', $resultado['status']);

        errores::$error = false;


        $registro = array();
        $status_default = 'a';
        $registro['status'] = 'cv';
        $resultado = $inicializacion->status($registro, $status_default);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('cv', $resultado['status']);
        errores::$error = false;
    }

    public function test_tablas_select(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        //$inicializacion = new liberator($inicializacion);

        $modelo = new adm_seccion($this->link);
        $resultado = $inicializacion->tablas_select($modelo);

        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado['adm_seccion']);
        errores::$error = false;
    }

    public function test_value_desencriptado(){
        errores::$error = false;
        $inicializacion = new inicializacion();
        $inicializacion = new liberator($inicializacion);

        $campo = '';
        $campos_encriptados = array();
        $value = '';

        $resultado = $inicializacion->value_desencriptado($campo, $campos_encriptados, $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $campo = '';
        $campos_encriptados = array();
        $campos_encriptados[] = '';
        $value = '';

        $resultado = $inicializacion->value_desencriptado($campo, $campos_encriptados, $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al desencriptar', $resultado['mensaje']);

        errores::$error = false;

        $campo = '';
        $campos_encriptados = array();
        $campos_encriptados[] = 'a';
        $value = '';

        $resultado = $inicializacion->value_desencriptado($campo, $campos_encriptados, $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $campo = 'a';
        $campos_encriptados = array();
        $campos_encriptados[] = 'a';
        $value = '';

        $resultado = $inicializacion->value_desencriptado($campo, $campos_encriptados, $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al desencriptar', $resultado['mensaje']);

        errores::$error = false;

        $campo = 'a';
        $campos_encriptados = array();
        $campos_encriptados[] = 'a';
        $value = 'z';

        $resultado = $inicializacion->value_desencriptado($campo, $campos_encriptados, $value);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al desencriptar', $resultado['mensaje']);

        errores::$error = false;

        $value_enc = (new encriptador())->encripta('z');
        $campo = 'a';
        $campos_encriptados = array();
        $campos_encriptados[] = 'a';
        $value = $value_enc;

        $resultado = $inicializacion->value_desencriptado($campo, $campos_encriptados, $value);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('z', $resultado);
        errores::$error = false;
    }

}
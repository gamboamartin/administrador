<?php
namespace tests\base\frontend;

use base\frontend\listas;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


class listasTest extends test {
    public errores $errores;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
    }

    public function test_accion_activa_desactiva(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);
        $accion = '';
        $status = '';
        $resultado = $ls->accion_activa_desactiva($accion, $status);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error accion esta vacia', $resultado['mensaje']);

        errores::$error = false;
        $accion = 'activa_bd';
        $status = '';
        $resultado = $ls->accion_activa_desactiva($accion, $status);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('activa_bd', $resultado->accion);

        errores::$error = false;
        $accion = 'activa_bd';
        $status = 'activo';
        $resultado = $ls->accion_activa_desactiva($accion, $status);
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('desactiva_bd', $resultado->accion);

        errores::$error = false;
    }

    public function test_activa_desactiva(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);
        $status = '';
        $resultado = $ls->activa_desactiva($status);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('activa_bd', $resultado);

        errores::$error = false;
        $status = 'activo';
        $resultado = $ls->activa_desactiva($status);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('desactiva_bd', $resultado);
        errores::$error = false;
    }

    public function test_campos_lista_html(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);
        $etiqueta_campos = array();
        $seccion = 'a';
        $resultado = $ls->campos_lista_html($etiqueta_campos, $seccion);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('', $resultado);

        errores::$error = false;

        $etiqueta_campos = array();
        $seccion = 'a';
        $etiqueta_campos[] = '';
        $resultado = $ls->campos_lista_html($etiqueta_campos, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $etiqueta_campos = array();
        $seccion = 'A';
        $etiqueta_campos[] = 'a';
        $resultado = $ls->campos_lista_html($etiqueta_campos, $seccion);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<th class='text-uppercase text-truncate td-90'>A</th>", $resultado);



        errores::$error = false;



    }

    public function test_data_accion_limpia(): void
    {
        errores::$error = false;
        $ls = new listas();
        //$ls = new liberator($ls);

        $resultado = $ls->data_accion_limpia();
        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("#", $resultado->href);
        $this->assertEquals("data-toggle='modal' data-target='#modalAccion'", $resultado->modal);
        $this->assertEquals("btn_modal", $resultado->btn_modal);
        errores::$error = false;
    }

    public function test_data_html(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $campo = array();
        $registro = array();
        $resultado = $ls->data_html($campo, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al asignar valor campo', $resultado['mensaje']);

        errores::$error = false;


        $campo = array();
        $registro = array();
        $campo['representacion'] = 'moneda';
        $campo['nombre_campo'] = 'x';
        $resultado = $ls->data_html($campo, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("title='$0.00'>$0.00", $resultado);
        errores::$error = false;
    }

    public function test_dato_campo(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $campo = array();
        $dato = '';
        $resultado = $ls->dato_campo($campo, $dato);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al asignar valor campo', $resultado['mensaje']);

        errores::$error = false;

        $campo = array();
        $dato = '';
        $campo['representacion'] = 'a';
        $resultado = $ls->dato_campo($campo, $dato);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $campo = array();
        $dato = '';
        $campo['representacion'] = 'telefono';
        $resultado = $ls->dato_campo($campo, $dato);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<a href='tel:'></a>", $resultado);
        errores::$error = false;
    }

    public function test_data_row_html(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);
        $registro = array();
        $campo = array();
        $resultado = $ls->data_row_html($campo, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al asignar valor campo', $resultado['mensaje']);

        errores::$error = false;
        $registro = array();
        $campo = array();
        $campo['representacion'] = 'a';
        $campo['nombre_campo'] = 'a';
        $resultado = $ls->data_row_html($campo, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("title=''></td>", $resultado);
        errores::$error = false;
    }

    public function test_dato_moneda(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $campo = array();
        $dato = '';
        $resultado = $ls->dato_moneda($campo, $dato);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al asignar valor campo', $resultado['mensaje']);

        errores::$error = false;
        $campo = array();
        $dato = '';
        $campo['representacion'] = 'a';
        $resultado = $ls->dato_moneda($campo, $dato);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;
        $campo = array();
        $dato = '1,1,1';
        $campo['representacion'] = 'moneda';
        $resultado = $ls->dato_moneda($campo, $dato);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('$111.00', $resultado);
        errores::$error = false;
    }

    public function test_dato_telefono(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $campo = array();
        $dato = '';
        $resultado = $ls->dato_telefono($campo, $dato);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al asignar valor campo', $resultado['mensaje']);

        errores::$error = false;

        $campo = array();
        $dato = '';
        $campo['representacion'] = 'telefono';
        $resultado = $ls->dato_telefono($campo, $dato);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<a href='tel:'></a>", $resultado);
        errores::$error = false;
    }

    public function test_footer_registro(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $registro = array();
        $seccion = '';
        $resultado = $ls->footer_registro($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $registro = array();
        $seccion = '';
        $registro[] = '';
        $resultado = $ls->footer_registro($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $registro = array();
        $seccion = 'a';
        $registro[] = '';
        $resultado = $ls->footer_registro($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $registro = array();
        $seccion = 'adm_seccion';
        $registro[] = '';
        $resultado = $ls->footer_registro($registro, $seccion);

        $this->assertIsObject( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('inactivo', $resultado->registro['adm_seccion_status']);

        errores::$error = false;
    }

    public function test_genera_campos_elementos_lista(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);
        $etiqueta_campos = array();
        $seccion = 'a';
        $etiqueta_campos[] = 'a';
        $resultado = $ls->campos_lista_html($etiqueta_campos, $seccion);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<th class='text-uppercase text-truncate td-90'> </th>", $resultado);
        errores::$error = false;
    }

    public function test_genera_html_dato(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $dato = '';
        $resultado = $ls->genera_html_dato($dato);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('data-placement', $resultado);

        errores::$error = false;

        $dato = 'x';
        $resultado = $ls->genera_html_dato($dato);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("title='x'>x</td>", $resultado);


        errores::$error = false;


    }

    public function test_init_dato_campo(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $campo = array();
        $registro = array();

        $resultado = $ls->init_dato_campo($campo, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar campo', $resultado['mensaje']);

        errores::$error = false;

        $campo = array();
        $registro = array();
        $campo['nombre_campo'] = 'a';
        $resultado = $ls->init_dato_campo($campo, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;

        $campo = array();
        $registro = array();
        $campo['nombre_campo'] = 'a';
        $registro['a'] = 'z';
        $resultado = $ls->init_dato_campo($campo, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('z', $resultado);
        errores::$error = false;
    }


    public function test_obten_panel(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $status = '';

        $resultado = $ls->obten_panel($status);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error status debe tener datos', $resultado['mensaje']);

        errores::$error = false;
        $status = 'a';

        $resultado = $ls->obten_panel($status);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('bg-danger', $resultado);

        errores::$error = false;
        $status = 'inactivo';

        $resultado = $ls->obten_panel($status);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('bg-danger', $resultado);

        errores::$error = false;
        $status = 'activo';

        $resultado = $ls->obten_panel($status);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);
        errores::$error = false;
    }

    public function test_parsea_ths_html(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $seccion = '';
        $campo = '';
        $resultado = $ls->parsea_ths_html(campo:$campo, seccion:$seccion);

        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error campo no puede venir vacio', $resultado['mensaje']);

        errores::$error = false;

        $seccion = '';
        $campo = 'a';
        $resultado = $ls->parsea_ths_html(campo:$campo, seccion:$seccion);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("<th class='text-uppercase text-truncate td-90'>A</th>", $resultado);



        errores::$error = false;


    }

    public function test_registro_status(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $registro = array();
        $seccion = '';
        $resultado = $ls->registro_status($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error seccion esta vacia', $resultado['mensaje']);

        errores::$error = false;

        $registro = array();
        $seccion = 'a';
        $resultado = $ls->registro_status($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('inactivo', $resultado['a_status']);

        errores::$error = false;

        $registro = array();
        $registro['a_status'] = 'activo';
        $seccion = 'a';
        $resultado = $ls->registro_status($registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('activo', $resultado['a_status']);
        errores::$error = false;
    }

    public function test_row_html(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);
        $registro = array();
        $campos = array();
        $resultado = $ls->row_html($campos, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('', $resultado);

        errores::$error = false;
        $registro = array();
        $campos = array();
        $campos[] = '';
        $resultado = $ls->row_html($campos, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error el campo debe ser un array', $resultado['mensaje']);

        errores::$error = false;
        $registro = array();
        $campos = array();
        $campos[] = array();
        $resultado = $ls->row_html($campos, $registro);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al generar html', $resultado['mensaje']);

        errores::$error = false;
        $registro = array();
        $campos = array();
        $campos[0] = array();
        $campos[0]['nombre_campo'] = 'a';
        $resultado = $ls->row_html($campos, $registro);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase(" title=''></td>", $resultado);
        errores::$error = false;
    }

    public function test_td_acciones(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $resultado = $ls->td_acciones();
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;


    }

    public function test_td_acciones_html(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);

        $resultado = $ls->td_acciones_html();
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('<td class="no-print">ACCIONES</td>', $resultado);


        errores::$error = false;


    }

    public function test_tr_data(): void
    {
        errores::$error = false;
        $ls = new listas();
        $ls = new liberator($ls);
        $campos = array();
        $registro = array();
        $seccion = '';
        $resultado = $ls->tr_data($campos, $registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $registro = array();
        $seccion = '';
        $registro[] = '';
        $resultado = $ls->tr_data($campos, $registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $registro = array();
        $seccion = 'a';
        $registro[] = '';
        $resultado = $ls->tr_data($campos, $registro, $seccion);
        $this->assertIsArray( $resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar datos', $resultado['mensaje']);

        errores::$error = false;
        $campos = array();
        $registro = array();
        $seccion = 'adm_seccion';
        $registro[] = '';
        $resultado = $ls->tr_data($campos, $registro, $seccion);
        $this->assertIsString( $resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('menu_acciones_lista', $resultado);
        errores::$error = false;
    }



}
<?php
namespace tests\base;


use base\orm\_defaults;
use gamboamartin\administrador\models\adm_grupo;
use gamboamartin\administrador\models\adm_mes;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;




class _defaultsTest extends test {
    public errores $errores;
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->errores = new errores();
    }

    public function test_alta_defaults()
    {

        errores::$error = false;
        $def = new _defaults();
        //$def = new liberator($def);
        $entidad = new adm_mes(link: $this->link);
        $catalogo = array();
        $filtro = array();
        $resultado = $def->alta_defaults($catalogo,$entidad,$filtro);

        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }

    public function test_ajusta_data_catalogo()
    {

        errores::$error = false;
        $def = new _defaults();
        $def = new liberator($def);
        $modelo = new adm_mes(link: $this->link);
        $catalogo = array();

        $resultado = $def->ajusta_data_catalogo($catalogo,$modelo);

        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }

    public function test_ajusta_datas_catalogo()
    {

        errores::$error = false;
        $def = new _defaults();
        $def = new liberator($def);
        $modelo = new adm_mes(link: $this->link);
        $catalogo = array();
        $campo = 'x';
        $resultado = $def->ajusta_datas_catalogo($catalogo,$campo,$modelo);


        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }

    public function test_ajusta_row()
    {

        errores::$error = false;
        $def = new _defaults();
        $def = new liberator($def);
        $modelo = new adm_mes(link: $this->link);
        $catalogo = array();
        $row = array();
        $indice = -1;
        $campo = 'a';
        $resultado = $def->ajusta_row($campo,$catalogo,$indice,$modelo, $row);


        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }

    public function test_existe_cod_default()
    {

        errores::$error = false;
        $def = new _defaults();
        $def = new liberator($def);
        $entidad = new adm_grupo(link: $this->link);
        $row = array();
        $filtro = array();

        $resultado = $def->existe_cod_default($entidad,$row,$filtro);

        $this->assertNotTrue(errores::$error);
        $this->assertIsBool($resultado);

        errores::$error = false;
    }

    public function test_filtro()
    {

        errores::$error = false;
        $def = new _defaults();
        $def = new liberator($def);
        $modelo = new adm_mes(link: $this->link);
        $campo = 'a';
        $row = array();

        $resultado = $def->filtro($campo,$modelo,$row);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('',$resultado['adm_mes.a']);

        errores::$error = false;
    }

    public function test_filtro_default()
    {

        errores::$error = false;
        $def = new _defaults();
        $def = new liberator($def);
        $entidad = new adm_grupo(link: $this->link);
        $row = array();
        $row['codigo'] = 'a';

        $resultado = $def->filtro_default($entidad, $row);
        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);
        $this->assertEquals('a',$resultado['adm_grupo.codigo']);

        errores::$error = false;
    }

    public function test_inserta_default()
    {

        $_SESSION['usuario_id'] = 2;
        errores::$error = false;
        $def = new _defaults();
        $def = new liberator($def);

        $del = (new adm_mes($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar mes', $del);
            print_r($error);exit;
        }

        $entidad = new adm_mes(link: $this->link);
        $row = array();
        $filtro = array();

        $row['descripcion'] = 'A';

        $resultado = $def->inserta_default($entidad,$row,$filtro);

        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }


    public function test_limpia_si_existe()
    {

        errores::$error = false;
        $def = new _defaults();
        $def = new liberator($def);
        $modelo = new adm_mes(link: $this->link);
        $catalogo = array();
        $filtro = array();
        $indice = -1;
        $resultado = $def->limpia_si_existe($catalogo,$filtro,$indice,$modelo);

        $this->assertNotTrue(errores::$error);
        $this->assertIsArray($resultado);

        errores::$error = false;
    }



}
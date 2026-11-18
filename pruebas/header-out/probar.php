<?php
namespace gamboamartin\errores {
    class errores {
        public static bool $error = false;
    }
}
namespace base\controller {
    require __DIR__ . '/../../base/controller/controler.php';
    class Captura extends \RuntimeException {
    }

    function header($cabecera) {
        if (($GLOBALS['argv'][1] ?? '') === 'ws') {
            echo $cabecera . "\n";
            return;
        }
        throw new Captura($cabecera);
    }

    class controlador_base extends controler {
        public static int $updates = 0;
        public function modifica_bd(bool $header, bool $ws): array|\stdClass {
            self::$updates++;
            return (object)[
                'salida' => 'exito',
                'mensaje' => 'Actualizado',
                'registro' => $_POST
            ];
        }
    }
}
namespace {
    require __DIR__ . '/system-activo.php';
    class ConexionFicticia extends PDO {
        public int $begins = 0, $commits = 0, $rollbacks = 0;
        public bool $active = false;
        public function __construct() {
        }

        public function inTransaction(): bool {
            return $this->active;
        }

        public function beginTransaction(): bool {
            $this->begins++;
            return $this->active = true;
        }

        public function commit(): bool {
            $this->commits++;
            $this->active = false;
            return true;
        }

        public function rollBack(): bool {
            $this->rollbacks++;
            $this->active = false;
            return true;
        }

        public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false {
            throw new RuntimeException('SQL prohibido');
        }

        public function exec(string $statement): int|false {
            throw new RuntimeException('SQL prohibido');
        }

        public function prepare(string $query, array $options = []): PDOStatement|false {
            throw new RuntimeException('SQL prohibido');
        }
    }
    set_error_handler(function ($nivel, $mensajeError) {
        throw new RuntimeException($mensajeError);
    });
    $controlador = (new ReflectionClass(base\controller\controler::class))->newInstanceWithoutConstructor();
    $controlador->seccion = 'fc_factura';
    $controlador->registro_id = 17;
    $controlador->session_id = 'SESION-FICTICIA';
    if (($argv[1] ?? '') === 'ws') {
        $_SERVER = [];
        $controlador->header_out(['ok' => true], false, true);
        throw new RuntimeException('No salio');
    }
    $comprobaciones = 0;
    function igual($actual, $esperado, $mensaje) {
        global $comprobaciones;
        $comprobaciones++;
        if ($actual !== $esperado) {
            throw new RuntimeException($mensaje . ' ' . var_export([$actual, $esperado], true));
        }
    }

    function destino($controlador, $retornoExplicito = '') {
        try {
            $controlador->header_out([], true, false, $retornoExplicito);
        }
        catch (base\controller\Captura $e) {
            return $e->getMessage();
        }
        throw new RuntimeException('No redirigio');
    }
    $url = 'Location:./index.php?seccion=fc_factura&accion=modifica&registro_id=17&session_id=SESION-FICTICIA';
    foreach ([null, '', ' ', [], false, "https://interno.test/a\r\nX:1"] as $referer) {
        $_SERVER = ['HTTP_REFERER' => $referer];
        igual(destino($controlador), $url, 'Fallback invalido');
    }
    $_SERVER = [];
    igual(destino($controlador), $url, 'Fallback ausente');
    $_SERVER = ['HTTP_REFERER' => []];
    igual(destino($controlador, ' ./index.php?accion=especial '), 'Location:./index.php?accion=especial', 'Explicito precede Referer');
    foreach (['https://interno.test/index.php?accion=especial', './index.php?accion=lista', 'https://externo.test/ruta'] as $referer) {
        $_SERVER = ['HTTP_REFERER' => $referer];
        igual(destino($controlador), 'Location:' . $referer, 'Compatibilidad historica Referer valido');
    }
    $_SERVER = [];
    $controlador->registro_id = 0;
    igual(destino($controlador), 'Location:./index.php?seccion=fc_factura&accion=lista&session_id=SESION-FICTICIA', 'Sin ID lista');
    $controlador->seccion = "fc_factura&host=otro";
    $controlador->session_id = "s\r\nX:1";
    igual(destino($controlador), 'Location:./index.php?seccion=fc_factura%26host%3Dotro&accion=lista&session_id=s%0D%0AX%3A1', 'Codificacion');
    igual($controlador->header_out(['ok' => true], false, false), null, 'Sin cabecera conserva retorno void');
    $ws = shell_exec(PHP_BINARY . ' ' . escapeshellarg(__FILE__) . ' ws');
    igual($ws, "Content-Type: application/json\n{\"ok\":true}", 'WS JSON sin Referer');
    foreach (['fc_factura', 'fc_nota_credito', 'fc_complemento_pago'] as $modulo) {
        $sistema = (new ReflectionClass(gamboamartin\system\system::class))->newInstanceWithoutConstructor();
        $sistema->link = new ConexionFicticia();
        $sistema->seccion = $modulo;
        $sistema->registro_id = 17;
        $sistema->session_id = 'SESION-FICTICIA';
        $_POST = ['observaciones' => 'FICTICIO', 'guarda' => '1'];
        $_SERVER = [];
        $_SESSION = [];
        $antes = base\controller\controlador_base::$updates;
        try {
            $sistema->modifica_bd(true, false);
            throw new RuntimeException('No redirigio');
        }
        catch (base\controller\Captura $e) {
            igual($e->getMessage(), 'Location:./index.php?seccion=' . $modulo . '&accion=modifica&registro_id=17&session_id=SESION-FICTICIA', 'Cadena real system');
        }
        igual(base\controller\controlador_base::$updates, $antes + 1, 'Una actualizacion');
        igual($sistema->link->begins, 1, 'Una transaccion');
        igual($sistema->link->commits, 1, 'Commit antes retorno');
        igual($sistema->link->rollbacks, 0, 'Sin rollback');
        igual(isset($_POST['guarda']), false, 'Guarda removido');
        igual($_SESSION['exito'][0]['mensaje'], 'Actualizado del id 17', 'Mensaje conservado');
    }
    echo "PASS: $comprobaciones comprobaciones header_out real + system::modifica_bd real, persistencia padre simulada, SQL prohibido.\n";
}

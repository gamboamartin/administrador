<?php
declare(strict_types=1);

// Código real y su versión anterior contra el mismo catálogo ficticio, sin BD/red.
namespace gamboamartin\errores {
    final class errores {
        public static bool $error = false;
        public function error(string $mensaje, mixed $data): array {
            self::$error = true;
            return ['error' => true, 'mensaje' => $mensaje];
        }
    }
}
namespace gamboamartin\validacion {
    final class validacion {
        public function valida_ids(array $keys, array $registro): bool|array {
            foreach ($keys as $key) {
                if (!isset($registro[$key]) || (int)$registro[$key] <= 0) {
                    return (new \gamboamartin\errores\errores())->error('ID inválido', []);
                }
            }
            return true;
        }
    }
}
namespace config {
    final class generales {
        public static string $sistema_qa = 'principal';
        public string $sistema;
        public function __construct() { $this->sistema = self::$sistema_qa; }
    }
}
namespace base\orm {
    class modelo {}
    class _modelo_parent_sin_codigo {
        public \PDO $link;
        public \gamboamartin\errores\errores $error;
        public string $tabla;
        public string $NAMESPACE;
        public string $etiqueta;
        public function __construct(\PDO $link, string $tabla, mixed ...$args) {
            $this->link = $link; $this->tabla = $tabla;
            $this->error = new \gamboamartin\errores\errores();
        }
    }
}
namespace gamboamartin\administrador\models {
    final class Catalogo {
        public static array $menus = [];
        public static array $secciones = [];
        public static array $acciones = [];
        public static array $permisos = [];
        public static array $usuarios = [];
        public static array $pertenencias = [];
        public static int $consultas = 0;
        public static ?string $fallar_tabla = null;
        public static function filas(string $tabla): array { return match ($tabla) {
            'adm_menu' => self::$menus, 'adm_seccion' => self::$secciones,
            'adm_accion' => self::$acciones,
        }; }
        public static function permitidos(array $filtro, string $tabla): array {
            $seleccion = [];
            foreach (self::$permisos as $permiso) {
                $accion = self::$acciones[$permiso['accion']];
                $seccion = self::$secciones[$accion['adm_accion_adm_seccion_id']];
                $menu = self::$menus[$seccion['adm_seccion_adm_menu_id']];
                $registro = [
                    'adm_grupo.id' => $permiso['grupo'],
                    'adm_menu.id' => $menu['adm_menu_id'],
                    'adm_seccion.id' => $seccion['adm_seccion_id'],
                    'adm_accion.es_lista' => $accion['adm_accion_es_lista'],
                    'adm_accion.es_status' => $accion['adm_accion_es_status'],
                    'adm_accion.visible' => $accion['adm_accion_visible'],
                ];
                foreach ($filtro as $key => $value) {
                    if (!isset($registro[$key]) || $registro[$key] != $value) continue 2;
                }
                $row = match ($tabla) {
                    'adm_menu' => $menu, 'adm_seccion' => $seccion, 'adm_accion' => $accion,
                };
                $seleccion[$row[$tabla . '_id']] = $row;
            }
            ksort($seleccion, SORT_NUMERIC);
            return array_values($seleccion);
        }
    }
    final class adm_usuario {
        public function __construct(\PDO $link) {}
        public function usuario_activo(): array {
            Catalogo::$consultas++;
            $id = $_SESSION['usuario_id'] ?? -1;
            if (!isset(Catalogo::$usuarios[$id])) {
                return (new \gamboamartin\errores\errores())->error('Usuario inexistente', []);
            }
            return Catalogo::$usuarios[$id];
        }
    }
    final class adm_accion_grupo {
        public function __construct(\PDO $link) {}
        public function filtro_and(array $columnas_by_table, array $filtro, array $group_by): \stdClass {
            Catalogo::$consultas++;
            $tabla = $columnas_by_table[0];
            if (Catalogo::$fallar_tabla === $tabla) {
                \gamboamartin\errores\errores::$error = true;
                return (object)['registros' => []];
            }
            if ($group_by !== [$tabla . '.id']) throw new \RuntimeException('Agrupación alterada');
            return (object)['registros' => Catalogo::permitidos($filtro, $tabla)];
        }
    }
    final class adm_seccion_pertenece {
        public function __construct(\PDO $link) {}
        public function filtro_and(array $columnas, array $filtro): \stdClass {
            Catalogo::$consultas++;
            if (Catalogo::$fallar_tabla === 'pertenencias') {
                \gamboamartin\errores\errores::$error = true;
                return (object)['registros' => []];
            }
            if ($columnas !== ['adm_seccion_id']) throw new \RuntimeException('Proyección incorrecta');
            $rows = [];
            foreach (Catalogo::$pertenencias as [$id, $sistema]) {
                if ($sistema === $filtro['adm_sistema.descripcion']) $rows[] = ['adm_seccion_id' => $id];
            }
            return (object)['registros' => $rows];
        }
        public function existe(array $filtro): bool {
            Catalogo::$consultas++;
            if (Catalogo::$fallar_tabla === 'pertenencias') {
                \gamboamartin\errores\errores::$error = true; return false;
            }
            foreach (Catalogo::$pertenencias as [$id, $sistema]) {
                if ($id == $filtro['adm_seccion.id'] && $sistema === $filtro['adm_sistema.descripcion']) return true;
            }
            return false;
        }
    }
}
namespace {
    use gamboamartin\administrador\models\Catalogo;
    use gamboamartin\administrador\models\adm_menu;
    use gamboamartin\administrador\models\adm_menu_legacy;
    use gamboamartin\errores\errores;

    require __DIR__ . '/../../orm/_base_accion.php';
    require __DIR__ . '/../../orm/adm_menu.php';
    // Fixtures inmutables del origen 9f09c92: no dependen de Git al instalar el paquete.
    $fixtures = [
        'base_accion_legacy.php.txt' => 'bc3cc421f8c5e5350b6e709bccd8f42263dd71385d60e07a22791854238b4f53',
        'adm_menu_legacy.php.txt' => 'e52b01b4d0c6df097e02c7d1ae3395666a945b6c74fdf994d178018ccee74a91',
    ];
    foreach ($fixtures as $fixture => $hash) {
        if (hash_file('sha256', __DIR__ . '/' . $fixture) !== $hash) {
            throw new RuntimeException('Fixture histórico alterado: ' . $fixture);
        }
        $source = file_get_contents(__DIR__ . '/' . $fixture);
        $source = str_replace(['class _base_accion{', 'class adm_menu extends', 'new _base_accion()'],
            ['class _base_accion_legacy{', 'class adm_menu_legacy extends', 'new _base_accion_legacy()'], $source);
        eval(substr($source, 5));
    }
    final class ConexionFicticia extends PDO { public function __construct() {} }
    $pdo = new ConexionFicticia(); $casos = 0; $aserciones = 0;
    function verifica(bool $ok, string $mensaje): void {
        global $aserciones; $aserciones++; if (!$ok) throw new RuntimeException($mensaje);
    }
    function prepara(): void {
        Catalogo::$menus = [];
        Catalogo::$secciones = [];
        Catalogo::$acciones = [];
        Catalogo::$permisos = [];
        for ($m = 1; $m <= 4; $m++) {
            Catalogo::$menus[$m] = ['adm_menu_id' => (string)$m, 'adm_menu_titulo' => 'Menú ' . $m, 'adm_menu_status' => 'inactivo'];
            for ($s = 1; $s <= 3; $s++) {
                $id = $m * 10 + $s;
                Catalogo::$secciones[$id] = ['adm_seccion_id' => (string)$id, 'adm_seccion_adm_menu_id' => (string)$m,
                    'adm_seccion_descripcion' => 'Sección ' . $id, 'adm_seccion_status' => 'inactivo'];
                for ($a = 1; $a <= 4; $a++) {
                    $aid = $id * 10 + $a;
                    Catalogo::$acciones[$aid] = ['adm_accion_id' => (string)$aid, 'adm_accion_adm_seccion_id' => (string)$id,
                        'adm_accion_visible' => $a === 2 ? 'inactivo' : 'activo',
                        'adm_accion_es_lista' => $a === 3 ? 'activo' : 'inactivo',
                        'adm_accion_es_status' => $a === 4 ? 'activo' : 'inactivo', 'adm_accion_status' => 'inactivo'];
                    Catalogo::$permisos[] = ['grupo' => 1, 'accion' => $aid];
                }
            }
        }
        // Otro grupo tiene acceso solamente a la primera sección; duplicado histórico.
        Catalogo::$permisos[] = ['grupo' => 2, 'accion' => 111];
        Catalogo::$permisos[] = ['grupo' => 2, 'accion' => 111];
        Catalogo::$usuarios = [1 => ['adm_grupo_id' => 1], 2 => ['adm_grupo_id' => 2], 3 => ['adm_grupo_id' => 3]];
        Catalogo::$pertenencias = [[11, 'principal'], [11, 'principal'], [12, 'principal'], [21, 'principal'], [31, 'otro']];
        Catalogo::$fallar_tabla = null; Catalogo::$consultas = 0;
        errores::$error = false; $_SESSION = ['usuario_id' => 1, 'grupo_id' => 999];
        \config\generales::$sistema_qa = 'principal';
    }
    function compara(string $nombre): array {
        global $pdo, $casos;
        errores::$error = false; Catalogo::$consultas = 0;
        $anterior = (new adm_menu_legacy($pdo))->menus_visibles_permitidos_full();
        $error_anterior = errores::$error; $consultas_anteriores = Catalogo::$consultas;
        errores::$error = false; Catalogo::$consultas = 0;
        $actual = (new adm_menu($pdo))->menus_visibles_permitidos_full();
        verifica(errores::$error === $error_anterior, $nombre . ': cambió propagación de error');
        if (!$error_anterior) verifica($actual === $anterior, $nombre . ': cambió salida/orden/campos');
        verifica(Catalogo::$consultas <= 5, $nombre . ': consultas no acotadas');
        $casos++;
        return [$actual, $consultas_anteriores, Catalogo::$consultas];
    }
    prepara(); [$arbol, $antes, $despues] = compara('árbol, proyección y orden completos');
    verifica(array_column($arbol, 'adm_menu_id') === ['1', '2'], 'Menús vacíos deben omitirse');
    verifica(array_column($arbol[0]['adm_secciones'], 'adm_seccion_id') === ['11', '12'], 'Sistema incorrecto');
    verifica(count($arbol[0]['adm_secciones'][0]['adm_acciones']) === 1, 'Filtros de visibilidad/acción alterados');
    verifica($antes > $despues, 'No eliminó consultas por sección');
    prepara(); $_SESSION['usuario_id'] = 2; compara('usuario restringido y permisos duplicados');
    prepara(); $_SESSION['usuario_id'] = 3; compara('grupo sin permisos');
    prepara(); \config\generales::$sistema_qa = 'otro'; compara('aislamiento de sistema');
    prepara(); \config\generales::$sistema_qa = 'sin-secciones'; compara('sistema sin pertenencias');
    prepara(); unset($_SESSION['usuario_id']); compara('sesión sin usuario');
    prepara(); unset($_SESSION); compara('sesión ausente');
    prepara(); $_SESSION['usuario_id'] = -1; compara('usuario negativo');
    prepara(); $_SESSION['usuario_id'] = 999; compara('usuario inexistente');
    prepara(); Catalogo::$menus[1]['adm_menu_id'] = '0'; compara('menú con ID cero');
    prepara(); Catalogo::$menus[1]['adm_menu_id'] = '-2'; compara('menú con ID negativo');
    prepara(); Catalogo::$secciones[11]['adm_seccion_id'] = '0'; compara('sección con ID cero');
    prepara(); Catalogo::$secciones[11]['adm_seccion_id'] = '-3'; compara('sección con ID negativo');
    foreach (['adm_menu', 'adm_seccion', 'adm_accion', 'pertenencias'] as $tabla) {
        prepara(); Catalogo::$fallar_tabla = $tabla; compara('error consulta ' . $tabla);
    }
    prepara(); $m = new adm_menu($pdo); $inicial = $m->menus_visibles_permitidos_full();
    Catalogo::$permisos = []; errores::$error = false;
    verifica($m->menus_visibles_permitidos_full() === [], 'Permiso revocado retenido en el mismo objeto'); $casos++;
    prepara(); $m = new adm_menu($pdo); $m->menus_visibles_permitidos_full(); $_SESSION['usuario_id'] = 2;
    verifica(count($m->menus_visibles_permitidos_full()[0]['adm_secciones']) === 1, 'Contexto de otro usuario retenido'); $casos++;
    prepara(); $m = new adm_menu($pdo); $m->menus_visibles_permitidos_full(); \config\generales::$sistema_qa = 'otro';
    verifica($m->menus_visibles_permitidos_full()[0]['adm_menu_id'] === '3', 'Sistema anterior retenido'); $casos++;
    echo json_encode(['estado' => 'PASS_MENU_EQUIVALENCIA', 'casos' => $casos, 'aserciones' => $aserciones,
        'consultas_fixture_antes' => $antes, 'consultas_fixture_despues' => $despues, 'sin_bd_red' => true], JSON_PRETTY_PRINT), PHP_EOL;
}

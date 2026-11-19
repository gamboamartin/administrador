<?php
declare(strict_types=1);

/** Método real extraído por tokens; sin autoload, PDO, SQL, red ni aplicación. */
namespace gamboamartin\errores {
    final class errores { public static bool $error = false; }
}
namespace {
    use gamboamartin\errores\errores;

    function extraerMetodo(string $archivo): string
    {
        $tokens = token_get_all(file_get_contents($archivo), TOKEN_PARSE);
        for ($i = 0, $total = count($tokens); $i < $total; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) continue;
            $j = $i + 1;
            while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
            if (!is_array($tokens[$j] ?? null) || $tokens[$j][0] !== T_STRING
                || $tokens[$j][1] !== 'get_data_lista') continue;
            $metodo = '';
            $profundidad = 0;
            $abierto = false;
            for ($k = $i; $k < $total; $k++) {
                $token = $tokens[$k];
                $metodo .= is_array($token) ? $token[1] : $token;
                if ($token === '{') { $profundidad++; $abierto = true; }
                if ($token === '}' && --$profundidad === 0 && $abierto) {
                    return 'public ' . $metodo;
                }
            }
            throw new RuntimeException('Método real incompleto');
        }
        throw new RuntimeException('Método real ausente');
    }

    final class ErroresDobles {
        public function error(string $mensaje, mixed $data): array {
            errores::$error = true;
            return ['error' => 1, 'mensaje' => $mensaje, 'data' => $data];
        }
    }

    class DoblesConsulta {
        public string $tabla = 'registro_qa';
        public ErroresDobles $error;
        public array $conteoArgumentos = [];
        public array $consultaArgumentos = [];
        public array $filas;
        public string $fallo;
        public function __construct(int $cantidad, string $fallo = '') {
            $this->error = new ErroresDobles();
            $this->filas = array_map(static fn(int $id): array => ['registro_qa_id' => $id],
                $cantidad === 0 ? [] : range(1, $cantidad));
            $this->fallo = $fallo;
        }
        public function cuenta_bis(...$argumentos): int|array {
            $this->conteoArgumentos[] = $argumentos;
            if ($this->fallo === 'conteo') {
                errores::$error = true;
                return ['causa' => 'conteo_ficticio'];
            }
            return count($this->filas);
        }
        public function filtro_and(...$argumentos): object|array {
            $this->consultaArgumentos[] = $argumentos;
            if ($this->fallo === 'consulta') {
                errores::$error = true;
                return ['causa' => 'consulta_ficticia'];
            }
            return (object)['registros' => array_slice($this->filas, $argumentos['offset'], $argumentos['limit'])];
        }
    }

    $fuente = $argv[1] ?? dirname(__DIR__) . '/base/orm/modelo.php';
    $metodo = extraerMetodo($fuente);
    // Solo colaboradores dobles; el código bajo prueba se obtiene íntegro del archivo recibido.
    eval('namespace { use gamboamartin\\errores\\errores; final class ModeloBajoPrueba extends DoblesConsulta {' . $metodo . '} }');

    $aserciones = 0;
    $casos = [];
    function igual(mixed $esperado, mixed $actual, string $mensaje): void {
        global $aserciones;
        $aserciones++;
        if ($esperado !== $actual) throw new RuntimeException($mensaje);
    }
    function caso(string $nombre, Closure $prueba): void {
        global $casos;
        errores::$error = false;
        try {
            $prueba();
            $casos[] = ['caso' => $nombre, 'estado' => 'PASS'];
        } catch (Throwable $error) {
            $casos[] = ['caso' => $nombre, 'estado' => 'FAIL', 'motivo' => $error->getMessage()];
        }
    }

    caso('una_fila_pagina_dos_respeta_offset', function (): void {
        $modelo = new ModeloBajoPrueba(1);
        $r = $modelo->get_data_lista(n_rows_for_page: 10, pagina: 2);
        igual(10, $modelo->consultaArgumentos[0]['offset'], 'Offset solicitado se perdió');
        igual([], $r['registros'], 'La página fuera del rango debe estar vacía');
        igual(1, $r['n_registros'], 'Conteo total alterado');
    });
    caso('once_filas_pagina_dos', function (): void {
        $modelo = new ModeloBajoPrueba(11);
        $r = $modelo->get_data_lista(n_rows_for_page: 10, pagina: 2);
        igual(10, $modelo->consultaArgumentos[0]['offset'], 'Offset de última página incorrecto');
        igual([['registro_qa_id' => 11]], $r['registros'], 'Última página incorrecta');
        igual(11, $r['n_registros'], 'Conteo de varias páginas alterado');
    });
    caso('primera_pagina_conserva_filas_y_orden_default', function (): void {
        $modelo = new ModeloBajoPrueba(1);
        $r = $modelo->get_data_lista(n_rows_for_page: 10, pagina: 1);
        igual([['registro_qa_id' => 1]], $r['registros'], 'Primera página alterada');
        igual(0, $modelo->consultaArgumentos[0]['offset'], 'Primera página debe iniciar en cero');
        igual(['registro_qa.id' => 'DESC'], $modelo->consultaArgumentos[0]['order'], 'Orden default alterado');
        igual(10, $modelo->consultaArgumentos[0]['limit'], 'Límite alterado');
    });
    caso('consulta_vacia_conserva_offset_y_conteo', function (): void {
        $modelo = new ModeloBajoPrueba(0);
        $r = $modelo->get_data_lista(n_rows_for_page: 10, pagina: 2);
        igual([], $r['registros'], 'Lista vacía incorrecta');
        igual(0, $r['n_registros'], 'Conteo vacío incorrecto');
        igual(10, $modelo->consultaArgumentos[0]['offset'], 'Incluso una lista vacía debe respetar el offset');
    });
    caso('error_conteo_detiene_consulta', function (): void {
        $modelo = new ModeloBajoPrueba(1, 'conteo');
        $r = $modelo->get_data_lista();
        igual(1, $r['error'], 'Error de conteo no propagado');
        igual(['causa' => 'conteo_ficticio'], $r['data'], 'Causa de conteo perdida');
        igual([], $modelo->consultaArgumentos, 'No consultar filas tras fallo de conteo');
        igual(true, errores::$error, 'Bandera de error de conteo perdida');
    });
    caso('error_consulta_conserva_causa', function (): void {
        $modelo = new ModeloBajoPrueba(1, 'consulta');
        $r = $modelo->get_data_lista();
        igual(1, $r['error'], 'Error de consulta no propagado');
        igual(['causa' => 'consulta_ficticia'], $r['data'], 'Causa de consulta perdida');
        igual(true, errores::$error, 'Bandera de error de consulta perdida');
    });
    caso('filtros_y_orden_se_transmiten_sin_cambios', function (): void {
        $modelo = new ModeloBajoPrueba(30);
        $args = [
            'filtro' => ['registro_qa.status' => 'activo'],
            'columnas' => ['registro_qa_id'],
            'filtro_especial' => [['registro_qa.codigo' => ['operador' => 'LIKE', 'valor' => '%QA%']]],
            'filtro_extra' => [['registro_qa.codigo' => ['operador' => '=', 'valor' => 'QA']]],
            'filtro_rango' => ['registro_qa.fecha' => ['valor1' => '2026-01-01', 'valor2' => '2026-12-31']],
            'n_rows_for_page' => 5, 'pagina' => 3,
            'in' => ['registro_qa.id' => [1, 2]],
            'extra_join' => ['qa' => 'registro_qa'],
            'order' => ['registro_qa.codigo' => 'ASC']
        ];
        $r = $modelo->get_data_lista(...$args);
        foreach (['extra_join', 'filtro', 'filtro_especial', 'filtro_extra', 'filtro_rango', 'in'] as $clave) {
            igual($args[$clave], $modelo->conteoArgumentos[0][$clave], 'Argumento de conteo alterado: ' . $clave);
            igual($args[$clave], $modelo->consultaArgumentos[0][$clave], 'Argumento de consulta alterado: ' . $clave);
        }
        igual($args['columnas'], $modelo->consultaArgumentos[0]['columnas'], 'Columnas alteradas');
        igual($args['order'], $modelo->consultaArgumentos[0]['order'], 'Orden explícito alterado');
        igual(5, $modelo->consultaArgumentos[0]['limit'], 'Límite explícito alterado');
        igual(10, $modelo->consultaArgumentos[0]['offset'], 'Offset de tamaño explícito alterado');
        igual(30, $r['n_registros'], 'Conteo final alterado');
    });
    $exito = count($casos) === 7 && count(array_filter($casos, static fn(array $c): bool => $c['estado'] !== 'PASS')) === 0;
    echo json_encode(['estado' => $exito ? 'PASS' : 'FAIL', 'casos_ejecutados' => count($casos),
        'aserciones_ejecutadas' => $aserciones, 'fuente_sha256' => hash_file('sha256', $fuente),
        'metodo_sha256' => hash('sha256', $metodo), 'casos' => $casos], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit($exito ? 0 : 1);
}

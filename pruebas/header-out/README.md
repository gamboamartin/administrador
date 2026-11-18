# Retorno focal header_out

Base main/v22.44.1: 21fb890eb42f2dd81c629ff4799d834db56ce9ca. Único cambio runtime: base/controller/controler.php. Sin cambios a controlador_base, ACL, Referrer-Policy ni reglas de negocio.

## Causa y alcance
System::modifica_bd guarda y confirma la transacción antes de llamar header_out. La lectura incondicional de HTTP_REFERER generaba warning y retorno vacío bajo no-referrer. El candidato anterior de controlador_base no cubría este camino.

El retorno explícito ahora tiene precedencia sin leer Referer. Una cabecera string no vacía sin CR/LF conserva exactamente su navegación histórica. Esto incluye autoridades externas históricamente aceptadas: no se corrige aquí esa política ni se amplía. Ausente/vacía/tipo inválido/CRLF usa ruta relativa con sección, ID y sesión del controlador codificados; Modifica si ID positivo, Lista en otro caso. Header false y WS conservan comportamiento. No se ocultan errores de persistencia.

## Pruebas reproducibles
php pruebas/header-out/probar.php: 36 checks, controler real y system::modifica_bd íntegro; persistencia padre simulada y PDO prohíbe SQL. Tres módulos, una actualización/commit, retornos explícitos, ausentes, vacíos, tipos, CRLF, codificación, JSON y sin header. No sustituye dispatcher/login/ORM/UI reales.

Snapshot system-activo.php SHA256 48bb3f7185763f073bacb998574f7ff8c4f01dff013bd66ef30a159c9cdfc9ba (system 24.21.1); código sin datos ni secretos.

python3 -B pruebas/regresion_partida/ejecutar.py: 8 casos vigentes. Históricos 77 intactos conservados en candidato anterior administrador-retorno-seguro-Ft6GAX, no acreditan camino nuevo. Suite tests/ heredada NO ejecutada: requiere DB aislada, contiene altas/borrados; no afirmar cobertura completa. Mantener distinción y coordinar antes de commit.

Para despliegue: respaldo recuperable, hashes, FPM exclusivo, batería vigente e inspección visual ficticia en factura/NC/CP con limpieza propia autorizada. Restaurar ante fallo. Ningún resultado unitario reemplaza esa validación.

## Validación UI en entorno de pruebas: 18 de noviembre de 2026

Se aplicó únicamente controler.php candidato SHA256 04b1e5a0977e8ba942f31ee4c08a266af8570f3b615935dc481a8ccc897ef0d3, con respaldo recuperable del archivo y recarga FPM exclusiva. No se instaló el antiguo parche de controlador_base.

Factura 4787, NC 61 y CP 15 ficticios: un único Modificar por documento regresó automáticamente al formulario; una recarga GET conservó observaciones modificadas. Verificación DB independiente confirmó texto esperado y total 0 en los tres. No nuevos warnings HTTP_REFERER en ventana de prueba. Capturas reales emitidas inline por coordinador; no hay archivos de capturas entregables acreditados.

Alta CP creó automáticamente partida 105, pago 105 y total 105 en cero; no se ejecutaron pagos efectivos ni relaciones. Sin certificado/llave, PAC, timbrado ni comunicaciones. Grafo final 16 objetos propios, 64 FK, respaldo tipado preservado; limpieza explícitamente autorizada completada a 15:09:41 UTC,81 referencias y conteos de 241 tablas y hashes de compartidos en 16 tablas controlados. Factura/NC/CP/layout vacíos. Recuperación aislada de datos y ausencia de triggers no acreditadas, límite informado y descarte autorizado; no confundir con respaldo recuperable del código desplegado.

Regresión de copia de Facturación reportada por coordinador: 108 pruebas / 421 aserciones y 21 frontend PASS con candidato final. Antes de cualquier publicación se deben repetir los gates vigentes del candidato exacto; este informe no sustituye ese paso.

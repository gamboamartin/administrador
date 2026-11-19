# v22.44.3: desplazamiento de listados

Problema: get_data_lista reiniciaba offset a cero si el total era menor o igual al tamaño de página. Una petición start=10,length=10 con un registro repetía la primera fila. Se conserva el desplazamiento solicitado, los filtros, el conteo y los errores reales.

Validación focal: `php pruebas/paginacion_get_data_lista.php` extrae por tokens el método íntegro del archivo real. Dobles de conteo/consulta permiten verificar offsets y errores sin PDO, autoload, red ni datos de aplicación. Siete casos y 37 aserciones; la versión anterior falla dos casos y la corregida pasa todos. No certifica SQL real ni toda la suite heredada.

También deben pasar `php pruebas/header-out/probar.php` y `python3 -B pruebas/regresion_partida/ejecutar.py`. La suite tests/ heredada requiere laboratorio con BD aislada; no ejecutar sobre datos reales ni darla por aprobada.

Consumidor Facturación local: la verificación HTTP separada debe cubrir factura, nota de crédito, complemento y layout de nómina, incluyendo página fuera de rango y búsqueda vacía. No hace operaciones fiscales. La publicación de esta versión fue autorizada expresamente por el usuario en el chat del 19 de noviembre de 2026.

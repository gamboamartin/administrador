# Equivalencia y consultas del menú autorizado

Ejecutar desde cualquier directorio:

```sh
php pruebas/rendimiento_menu/probar.php
```

La prueba carga las clases reales `orm/_base_accion.php` y `orm/adm_menu.php` con dobles de conexión/modelos. No carga Composer, configuración de aplicación, PDO real, sesiones persistidas ni servicios externos.

Las versiones anteriores de esas dos clases se conservan como fixtures inmutables del commit `9f09c92`; sus SHA256 se comprueban antes de renombrarlas y ejecutarlas contra el mismo catálogo ficticio. Se comparan estrictamente campos, valores, claves y orden del árbol; además se verifican escenarios de permisos, sistema, revocación, usuario/grupo, errores e identificadores inválidos. No se añaden filtros de estado que no existían en la versión anterior.

Resultado esperado: `PASS_MENU_EQUIVALENCIA`, 20 casos y 46 aserciones. En este catálogo la construcción anterior hace 46 lecturas y la nueva 5. Son conteos del doble, no una estimación del tiempo de producción. El perfil de BD/FPM real y las pruebas de integración se ejecutan separadamente antes de desplegar.

El cambio no conserva permisos ni pertenencias entre llamadas. La nueva construcción vuelve a consultar el usuario de la sesión en memoria y el catálogo autorizado en cada llamada.

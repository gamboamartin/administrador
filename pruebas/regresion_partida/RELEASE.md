# v22.44.1 — consultas de acciones por sección

Agrupa consulta de acciones lista para IDs canónicos. Conserva claves, orden, duplicados y comparación literal. IDs no canónicos conservan ruta histórica íntegra. ACL/grupo/permisos posteriores no cambian.

Ocho casos comparativos prueban método real extraído con consulta simulada. Ensayos independientes ORM MariaDB10.6.11 con usuarios/grupos ficticios verificaron misma salida y datos intactos; no sustituyen suite histórica completa. Batería Facturación104 pruebas415 aserciones aprobada antes de commit. La suite heredada requiere laboratorio de BD por escrituras y no se ejecutó contra servicios reales.

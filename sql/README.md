# Scripts SQL - Anulación de Períodos

Este directorio contiene los scripts SQL necesarios para implementar y gestionar la funcionalidad de anulación de períodos.

## 📁 Archivos Disponibles

### 1. `add_soft_deletes_to_facturas.sql` ⭐ EJECUTAR PRIMERO
**Descripción:** Script principal que agrega las columnas necesarias para soft deletes en la tabla `facturas`.

**Qué hace:**
- Agrega columna `deleted_at` (TIMESTAMP)
- Agrega columna `motivo_anulacion` (VARCHAR 500)
- Agrega columna `anulado_por` (INT, FK a users)
- Agrega columna `fecha_anulacion` (DATETIME)
- Crea índices para mejorar performance

**Cuándo ejecutar:** Una sola vez, antes de usar la funcionalidad de anulación.

**Cómo ejecutar:**
```bash
mysql -u usuario -p base_datos < add_soft_deletes_to_facturas.sql
```

O desde MySQL Workbench/phpMyAdmin: copiar y pegar el contenido.

**IMPORTANTE:** 
- ✅ Hacer backup de la base de datos ANTES de ejecutar
- ✅ Incluye script de rollback al final (comentado)

---

### 2. `verify_anulacion_setup.sql` 🔍 VERIFICACIÓN
**Descripción:** Script completo de verificación del sistema.

**Qué verifica:**
- ✓ Estructura de tabla facturas (columnas agregadas)
- ✓ Índices creados correctamente
- ✓ Talonarios disponibles y activos
- ✓ Configuración AFIP (facturas con CAE)
- ✓ Notas de crédito existentes
- ✓ Períodos con facturas activas
- ✓ Usuarios administradores
- ✓ Estado general del sistema
- ✓ Checklist completo de requisitos

**Cuándo ejecutar:** 
- Después de ejecutar `add_soft_deletes_to_facturas.sql`
- Periódicamente para verificar estado del sistema

**Resultado esperado:**
```
✓ Columnas soft delete en facturas: ✓ OK
✓ Talonarios activos: ✓ OK (X talonarios)
✓ Facturas con CAE (AFIP funcional): ✓ OK (X facturas)
✓ Tabla notas_credito existe: ✓ OK
```

---

### 3. `test_anulacion_periodo.sql` 🧪 PRUEBAS
**Descripción:** Script para probar la anulación en ambiente de desarrollo/testing.

**Qué hace:**
1. Lista períodos con pocas facturas (ideal para pruebas)
2. Muestra detalle de un período específico
3. Simula qué se va a anular (sin ejecutar)
4. Verifica prerequisitos
5. Incluye queries de verificación post-anulación
6. Incluye script de limpieza de pruebas

**Cuándo ejecutar:** Antes de anular períodos en producción.

**Flujo recomendado:**
1. Ejecutar este script para seleccionar un período de prueba
2. Modificar variable `@periodo_prueba` (ej: '01/2026')
3. Ver simulación de qué se anulará
4. Ir a la interfaz web y anular ese período
5. Descomentar queries de verificación para validar resultado
6. Si fue exitoso, proceder con períodos reales

---

### 4. `verificacion_periodos_anulados.sql` 📊 AUDITORÍA
**Descripción:** Scripts para consultar y auditar períodos ya anulados.

**Qué incluye:**
- Ver todos los períodos anulados
- Detalles de facturas anuladas por período
- NC generadas por anulación con CAE
- Verificar integridad (facturas sin NC)
- Resumen de períodos con anulaciones
- Estadísticas comparativas (anuladas vs activas)
- Scripts de recuperación (uso con precaución)

**Cuándo ejecutar:** 
- Para auditoría y control
- Para verificar períodos anulados
- Para generar reportes

---

## 🚀 Guía de Instalación Paso a Paso

### Paso 1: Backup
```bash
mysqldump -u usuario -p base_datos > backup_antes_anulacion_$(date +%Y%m%d).sql
```

### Paso 2: Ejecutar script principal
```bash
mysql -u usuario -p base_datos < add_soft_deletes_to_facturas.sql
```

### Paso 3: Verificar instalación
```bash
mysql -u usuario -p base_datos < verify_anulacion_setup.sql
```

Revisa que todos los checks estén en ✓ OK.

### Paso 4: Probar con período de testing
```bash
mysql -u usuario -p base_datos < test_anulacion_periodo.sql
```

Modifica `@periodo_prueba` y ejecuta paso a paso.

### Paso 5: Usar en producción
Una vez verificado en testing, ya puedes usar la funcionalidad en producción desde la interfaz web.

---

## ⚠️ Advertencias Importantes

### NO Ejecutar en Producción sin:
1. ✅ Backup completo de la base de datos
2. ✅ Verificación exitosa con `verify_anulacion_setup.sql`
3. ✅ Prueba exitosa en ambiente de testing
4. ✅ Configuración AFIP validada y funcional
5. ✅ Aprobación de contador/responsable

### Consideraciones AFIP:
- Las NC se emiten oficialmente con CAE
- No se pueden "deshacer" fácilmente
- Tienen implicancias legales y contables
- Verificar siempre que AFIP esté respondiendo antes de anular períodos

---

## 🔧 Solución de Problemas

### Error: "Table 'facturas' doesn't exist"
**Causa:** Nombre de base de datos incorrecto  
**Solución:** Modificar `USE redin;` en el script por el nombre correcto

### Error: "Duplicate column name 'deleted_at'"
**Causa:** El script ya fue ejecutado anteriormente  
**Solución:** Las columnas ya existen, no es necesario ejecutarlo de nuevo

### Verificación muestra "✗ FALTA"
**Causa:** Algún requisito no se cumple  
**Solución:** Revisar el detalle específico y corregir:
- Talonarios: crear talonarios activos
- AFIP: configurar certificados y conexión
- Tabla NC: ejecutar migración de notas_credito

### Los índices no se crearon
**Causa:** Error durante la ejecución  
**Solución:** Ejecutar manualmente:
```sql
ALTER TABLE facturas ADD INDEX idx_deleted_at (deleted_at);
ALTER TABLE facturas ADD INDEX idx_periodo (periodo);
```

---

## 📞 Soporte

Si tienes problemas:
1. Revisa los logs de MySQL
2. Ejecuta `verify_anulacion_setup.sql` para diagnóstico
3. Verifica permisos del usuario MySQL
4. Consulta la documentación completa en `/docs/ANULACION_PERIODOS.md`

---

## 🔄 Rollback (Revertir Cambios)

Si necesitas revertir la instalación:

```sql
-- Ejecutar el script de rollback incluido en add_soft_deletes_to_facturas.sql
-- (está comentado al final del archivo)

-- O manualmente:
ALTER TABLE facturas DROP INDEX idx_deleted_at;
ALTER TABLE facturas DROP INDEX idx_periodo;
ALTER TABLE facturas DROP COLUMN deleted_at;
ALTER TABLE facturas DROP COLUMN motivo_anulacion;
ALTER TABLE facturas DROP COLUMN anulado_por;
ALTER TABLE facturas DROP COLUMN fecha_anulacion;
```

**ADVERTENCIA:** Solo revertir si NO se han anulado períodos aún.

---

## 📝 Orden de Ejecución Recomendado

```
1. add_soft_deletes_to_facturas.sql    (instalación)
2. verify_anulacion_setup.sql          (verificación)
3. test_anulacion_periodo.sql          (pruebas)
4. [Usar interfaz web]                 (anular período real)
5. verificacion_periodos_anulados.sql  (auditoría)
```

---

**Última actualización:** 2026-01-09  
**Versión:** 1.0

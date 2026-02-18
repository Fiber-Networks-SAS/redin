# Completar Facturas Faltantes - Acceso para Sistemas

## 🔒 Acceso Directo (Sin menú)

Esta funcionalidad está disponible únicamente para personal de sistemas mediante acceso directo por URL.

### URL de Acceso

```
http://localhost/admin/period/complete-missing
```

O en producción:

```
https://tu-dominio.com/admin/period/complete-missing
```

## 📋 Guía Rápida de Uso

### 1. Acceder a la Vista

Ingresar directamente a la URL: `/admin/period/complete-missing`

### 2. Verificar Clientes Sin Factura

1. Ingresar el **Período** (formato: `MM/YYYY`, ejemplo: `01/2026`)
2. Ingresar la **Fecha de Emisión** (formato: `DD/MM/YYYY`, ejemplo: `04/02/2026`)
3. Hacer clic en **"Verificar Faltantes"**
4. El sistema mostrará una tabla con todos los clientes que no tienen factura en ese periodo

### 3. Completar las Facturas

1. Después de verificar, el botón **"Completar Facturas"** se habilitará
2. Hacer clic en el botón
3. Confirmar la acción en el diálogo de confirmación
4. Esperar a que el proceso complete (puede tomar varios minutos)
5. Revisar el resultado con el detalle de facturas creadas

## ⚙️ Características de Seguridad

- ✅ **No duplica facturas**: Si un cliente ya tiene factura, lo omite automáticamente
- ✅ **Continúa con errores**: Si una factura falla, continúa con las demás
- ✅ **Log completo**: Todos los procesos se registran en `storage/logs/laravel.log`
- ✅ **Emite en AFIP**: Genera CAE oficial para cada factura
- ✅ **Notifica por email**: Envía automáticamente el email al cliente

## 🚨 Cuándo Usar

### Escenario 1: Proceso Interrumpido
```
Problema: La creación de facturas se detuvo en la factura 126
Solución: Usar esta herramienta para completar las faltantes
```

### Escenario 2: Error Específico en Algunos Clientes
```
Problema: 5 clientes fallaron por error de MercadoPago
Solución: Corregir el problema y ejecutar esta herramienta
```

### Escenario 3: Cliente Agregado Retroactivamente
```
Problema: Se agregó un cliente con servicios que debían facturarse
Solución: Usar esta herramienta para generar su factura del periodo
```

## 📊 Monitoreo

### Ver Log en Tiempo Real

**Linux/Mac:**
```bash
tail -f storage/logs/laravel.log
```

**Windows (PowerShell):**
```powershell
Get-Content -Path storage\logs\laravel.log -Tail 50 -Wait
```

### Consulta SQL: Verificar Clientes Sin Factura

```sql
SELECT 
    u.id,
    u.nro_cliente,
    u.nombre,
    u.apellido,
    u.email,
    COUNT(su.id) as servicios_activos
FROM users u
INNER JOIN role_user ru ON u.id = ru.user_id
INNER JOIN roles r ON ru.role_id = r.id
LEFT JOIN servicio_usuarios su ON u.id = su.user_id AND su.status = 1
WHERE r.name = 'client' 
  AND u.status = 1
  AND u.id NOT IN (
    SELECT user_id FROM facturas WHERE periodo = '01/2026'
  )
GROUP BY u.id
HAVING servicios_activos > 0;
```

## 🔧 Troubleshooting

### Error: "No se encontraron clientes activos"
- Verificar que existan usuarios con rol 'client' y status = 1

### Error: "AfipService no está disponible"
- Verificar certificados AFIP en `storage/app/afip.crt` y `storage/app/afip.pem`
- Revisar configuración en `config/afip.php`

### Error: "Error al crear la preferencia de pago: amount:0"
- Ya corregido: El sistema ahora valida antes de generar QR
- Si aparece, verificar que la validación esté en el código

### Proceso muy lento
- Normal si hay muchas facturas (cada una consulta AFIP)
- Monitorear el log para ver progreso
- El timeout está configurado en 10 minutos

## 📝 Notas Importantes

1. **Acceso restringido**: Esta URL solo funciona con sesión de administrador activa
2. **No aparece en menú**: Funcionalidad oculta para evitar uso indebido
3. **Use con precaución**: Genera facturas reales con CAE de AFIP
4. **Backup recomendado**: Hacer backup de BD antes de procesos masivos
5. **Horario sugerido**: Usar fuera del horario de atención al cliente

## 🔗 URLs Relacionadas

- **Vista principal**: `/admin/period/complete-missing`
- **API verificar**: `POST /admin/period/verify-missing`
- **API completar**: `POST /bill/complete-missing`
- **Lista periodos**: `/admin/period`
- **Buscar facturas**: `/admin/bills`

## 📞 Soporte

En caso de problemas:

1. Revisar `storage/logs/laravel.log`
2. Verificar configuración AFIP
3. Comprobar servicios de MercadoPago
4. Contactar al equipo de desarrollo

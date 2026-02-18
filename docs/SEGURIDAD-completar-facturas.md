# SEGURIDAD - Completar Facturas Faltantes

## 🔐 Configuración de Seguridad

### Middleware de Autenticación

La ruta `/admin/period/complete-missing` está protegida por:

1. **Middleware de autenticación**: Requiere usuario autenticado
2. **Middleware de admin**: Solo usuarios con permisos de administrador
3. **CSRF Protection**: Token CSRF requerido en todos los POST

### Configuración en routes/web.php

```php
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {
    // ... otras rutas admin
    
    // Completar facturas faltantes (oculto del menú)
    Route::get('/period/complete-missing', 'BillController@showCompleteMissingView');
    Route::post('/period/verify-missing', 'BillController@verifyMissingBills');
});

// API pública (también requiere autenticación)
Route::post('/bill/complete-missing', 'BillController@completeMissingBills')
    ->middleware(['auth', 'admin']);
```

## 🚫 Restricciones Adicionales (Opcional)

### Opción 1: Restringir por IP

Si deseas restringir aún más el acceso solo a IPs específicas:

**En `.htaccess`:**
```apache
<Location /admin/period/complete-missing>
    Order Deny,Allow
    Deny from all
    Allow from 192.168.1.100  # IP de sistemas
    Allow from 10.0.0.50      # IP de oficina
</Location>
```

**En `nginx.conf`:**
```nginx
location /admin/period/complete-missing {
    allow 192.168.1.100;  # IP de sistemas
    allow 10.0.0.50;      # IP de oficina
    deny all;
}
```

### Opción 2: Verificación de Usuario Específico

Agregar en el controlador `BillController.php`:

```php
public function showCompleteMissingView()
{
    // Solo permitir a usuarios específicos
    $allowedUsers = ['admin', 'sistemas', 'soporte'];
    
    if (!in_array(Auth::user()->username, $allowedUsers)) {
        abort(403, 'Acceso denegado. Esta función es solo para sistemas.');
    }
    
    return view('period.complete_missing');
}
```

### Opción 3: Variable de Entorno

Agregar en `.env`:

```env
ENABLE_COMPLETE_MISSING_BILLS=true
```

Y en el controlador:

```php
public function showCompleteMissingView()
{
    if (!env('ENABLE_COMPLETE_MISSING_BILLS', false)) {
        abort(404);
    }
    
    return view('period.complete_missing');
}
```

## 📝 Registro de Accesos

Todos los accesos y operaciones se registran en:

- `storage/logs/laravel.log` - Log general de Laravel
- Base de datos tabla `audits` - Si está habilitada la auditoría

### Log de Ejemplo

```
[2026-02-04 14:30:20] INFO: === COMPLETANDO FACTURAS FALTANTES DEL PERIODO ===
[2026-02-04 14:30:20] INFO: Usuario: admin@example.com
[2026-02-04 14:30:20] INFO: IP: 192.168.1.100
[2026-02-04 14:30:20] INFO: Periodo: 01/2026
[2026-02-04 14:30:25] INFO: Facturas creadas: 45
```

## ⚠️ Advertencias de Seguridad

1. **No compartir URL**: La URL `/admin/period/complete-missing` debe ser conocida solo por sistemas
2. **Revisar logs**: Monitorear regularmente quién accede a esta funcionalidad
3. **Backup antes de usar**: Siempre hacer backup antes de operaciones masivas
4. **Horario restringido**: Usar preferiblemente fuera de horario de atención
5. **Validar resultados**: Siempre revisar el resultado antes de dar por finalizado

## 🔍 Monitoreo Recomendado

### Query para verificar accesos (si hay tabla de auditoría)

```sql
SELECT 
    user_id,
    event,
    url,
    ip_address,
    created_at
FROM audits
WHERE url LIKE '%complete-missing%'
ORDER BY created_at DESC
LIMIT 50;
```

### Alertas Sugeridas

Configurar alertas cuando:
- Se ejecuta `completeMissingBills` fuera de horario laboral
- Se crean más de 100 facturas en una sola ejecución
- El proceso falla con muchos errores (>10%)
- Acceso desde IP no autorizada

## 📋 Checklist de Seguridad

Antes de poner en producción, verificar:

- [ ] Middleware de autenticación activo
- [ ] Solo usuarios admin tienen acceso
- [ ] URL no aparece en ningún menú público
- [ ] CSRF protection habilitado
- [ ] Logs de auditoría funcionando
- [ ] Backup automático configurado
- [ ] Límite de timeout apropiado (10 min)
- [ ] Documentación actualizada en `/docs`
- [ ] Personal de sistemas capacitado
- [ ] Proceso de rollback definido

---

**Importante**: Esta funcionalidad es crítica y debe ser usada solo por personal técnico capacitado.

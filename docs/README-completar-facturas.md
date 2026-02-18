# Completar Facturas Faltantes - Guía Rápida

## ¿Qué hace?

Completa las facturas de un periodo que no se hayan generado. Útil cuando el proceso se interrumpe por algún error.

## Uso Rápido

### Con cURL (Linux/Mac/Windows)

```bash
curl -X POST http://localhost/bill/complete-missing \
  -H "Content-Type: application/json" \
  -d '{"periodo":"01/2026","fecha_emision":"04/02/2026"}'
```

### Con PowerShell (Windows)

```powershell
.\docs\completar-facturas.ps1 -Periodo "01/2026" -FechaEmision "04/02/2026"
```

### Con Bash (Linux/Mac)

```bash
chmod +x docs/completar-facturas.sh
./docs/completar-facturas.sh "01/2026" "04/02/2026"
```

## Parámetros

- **periodo**: Formato `m/Y` (ejemplo: `"01/2026"`)
- **fecha_emision**: Formato `d/m/Y` (ejemplo: `"04/02/2026"`)

## Ejemplo de Respuesta

```json
{
  "success": true,
  "message": "Proceso completado: 45 facturas creadas.",
  "summary": {
    "periodo": "01/2026",
    "total_clientes_sin_factura": 45,
    "facturas_creadas": 45,
    "errores": 0,
    "processed_at": "2026-02-04 14:30:25"
  },
  "facturas": [
    {
      "id": 1275,
      "user_id": 123,
      "nro_factura": "00001275",
      "importe_total": 5000.00,
      "cae": "72081234567890"
    }
  ],
  "errores": []
}
```

## Verificar Clientes Sin Factura

Antes de ejecutar, puedes verificar cuántos clientes no tienen factura:

```sql
SELECT COUNT(*) as clientes_sin_factura
FROM users u
INNER JOIN role_user ru ON u.id = ru.user_id
INNER JOIN roles r ON ru.role_id = r.id
WHERE r.name = 'client' 
  AND u.status = 1
  AND u.id NOT IN (
    SELECT user_id FROM facturas WHERE periodo = '01/2026'
  );
```

## Ver Logs

```bash
# Linux/Mac
tail -f storage/logs/laravel.log

# Windows (PowerShell)
Get-Content -Path storage\logs\laravel.log -Tail 50 -Wait
```

## Importante

- ✅ **No duplica**: Si un cliente ya tiene factura, lo omite
- ✅ **Continúa con errores**: Si falla una factura, sigue con las demás
- ✅ **Mismo proceso**: Usa la misma lógica que la facturación normal
- ✅ **Genera CAE en AFIP**: Emite las facturas oficialmente
- ✅ **Envía emails**: Notifica automáticamente a los clientes

## Documentación Completa

Ver [completar-facturas-faltantes.md](./completar-facturas-faltantes.md) para más detalles.

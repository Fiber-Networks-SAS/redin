# Completar Facturas Faltantes de un Periodo

## Descripción

Este endpoint permite completar las facturas de un periodo que no se hayan generado. Identifica automáticamente los clientes que deberían tener factura pero no la tienen en el periodo especificado y genera las facturas faltantes.

Es útil cuando:
- El proceso de facturación se interrumpió (por ejemplo, si se cortó en la factura 126)
- Algunos clientes quedaron sin factura por errores específicos
- Se necesita reanudar la facturación desde donde se detuvo

## Endpoint

```
POST /bill/complete-missing
```

## Parámetros Requeridos

| Parámetro | Tipo | Formato | Descripción |
|-----------|------|---------|-------------|
| `periodo` | string | `m/Y` | Periodo de facturación (ej: "01/2026") |
| `fecha_emision` | string | `d/m/Y` | Fecha de emisión de las facturas (ej: "04/02/2026") |

## Ejemplo de Uso

### Request

```bash
curl -X POST http://localhost/bill/complete-missing \
  -H "Content-Type: application/json" \
  -d '{
    "periodo": "01/2026",
    "fecha_emision": "04/02/2026"
  }'
```

### Response Exitosa

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
    },
    // ... más facturas
  ],
  "errores": []
}
```

### Response Sin Facturas Faltantes

```json
{
  "success": true,
  "message": "No hay facturas faltantes para completar.",
  "facturas_creadas": 0
}
```

### Response con Errores Parciales

```json
{
  "success": true,
  "message": "Proceso completado: 43 facturas creadas.",
  "summary": {
    "periodo": "01/2026",
    "total_clientes_sin_factura": 45,
    "facturas_creadas": 43,
    "errores": 2,
    "processed_at": "2026-02-04 14:30:25"
  },
  "facturas": [
    // ... facturas creadas exitosamente
  ],
  "errores": [
    {
      "user_id": 456,
      "nro_cliente": "00456",
      "error": "Error al crear la preferencia de pago: amount:0"
    },
    {
      "user_id": 789,
      "nro_cliente": "00789",
      "error": "AfipService no está disponible"
    }
  ]
}
```

## Funcionalidades

El endpoint realiza las mismas operaciones que el proceso normal de facturación:

1. **Identifica clientes sin factura**: Compara todos los clientes activos con las facturas ya generadas en el periodo
2. **Valida servicios facturables**: Solo procesa servicios activos que deban facturarse según:
   - Fecha de alta del servicio
   - Estado del servicio
   - Planes de pago vigentes
3. **Calcula importes**: Aplica la misma lógica que la facturación normal:
   - Abonos mensuales
   - Proporcionales por alta de servicio
   - Cuotas de instalación
   - Bonificaciones por servicio
4. **Aplica saldo a favor**: Si el cliente tiene saldo disponible, se aplica automáticamente
5. **Emite en AFIP**: Genera el CAE correspondiente (solo si el importe es > 0)
6. **Genera QR de pago**: Crea códigos QR de MercadoPago para facturas con saldo pendiente
7. **Genera PDFs**: Crea los archivos PDF de las facturas generadas
8. **Envía emails**: Notifica automáticamente a los clientes

## Validaciones

- **Periodo válido**: Debe estar en formato `m/Y`
- **Fecha emisión válida**: Debe estar en formato `d/m/Y`
- **Solo clientes activos**: Solo procesa usuarios con `status = 1`
- **Solo servicios activos**: Solo procesa servicios con `status = 1`
- **No duplica facturas**: Verifica que el cliente no tenga ya una factura en el periodo

## Logs

El proceso genera logs detallados en `storage/logs/laravel.log`:

```
[2026-02-04 14:30:20] INFO: === COMPLETANDO FACTURAS FALTANTES DEL PERIODO ===
[2026-02-04 14:30:20] INFO: Clientes con factura existente {"cantidad":126,"user_ids":[1,2,3...]}
[2026-02-04 14:30:21] INFO: Clientes sin factura que requieren facturación {"cantidad":45,"user_ids":[127,128...]}
[2026-02-04 14:30:25] INFO: Factura creada exitosamente {"factura_id":1275,"user_id":127,...}
```

## Notas Importantes

1. **No sobrescribe facturas existentes**: Si un cliente ya tiene factura en el periodo, se omite
2. **Proceso idempotente**: Se puede ejecutar varias veces sin crear duplicados
3. **Manejo de errores**: Si falla una factura, continúa con las siguientes
4. **Mismo orden**: Las facturas se generan en orden alfabético por domicilio (igual que el proceso normal)
5. **AFIP**: Requiere que el servicio AFIP esté disponible y configurado correctamente

## Comparación con Regenerar PDF

| Característica | `complete-missing` | `regenerate-pdf` |
|----------------|-------------------|------------------|
| **Propósito** | Crear facturas nuevas que faltan | Regenerar PDF de facturas existentes |
| **Consulta AFIP** | Sí, genera nuevo CAE | No, usa CAE existente |
| **Crea registros DB** | Sí, nuevas facturas y detalles | No, solo regenera archivo |
| **Aplica saldo a favor** | Sí | No (ya aplicado) |
| **Envía emails** | Sí | No |
| **Idempotente** | Sí (no duplica) | Sí (sobrescribe PDF) |

## Casos de Uso

### Caso 1: Proceso interrumpido por error
```
Problema: Creación de facturas se detuvo en factura 126
Solución: Ejecutar complete-missing para generar las 200 facturas restantes
```

### Caso 2: Cliente agregado después de la facturación
```
Problema: Se agregó un cliente nuevo con servicios retroactivos
Solución: Ejecutar complete-missing para generar solo su factura
```

### Caso 3: Error específico en algunos clientes
```
Problema: 5 clientes fallaron por error de MercadoPago
Solución: Corregir el problema y ejecutar complete-missing para solo esos 5
```

## Monitoreo

Para verificar el estado después de ejecutar:

```sql
-- Ver facturas creadas en el periodo
SELECT COUNT(*) as total, MIN(id) as primera, MAX(id) as ultima
FROM facturas 
WHERE periodo = '01/2026';

-- Ver clientes sin factura en el periodo
SELECT u.id, u.nro_cliente, u.nombre, u.apellido
FROM users u
INNER JOIN role_user ru ON u.id = ru.user_id
INNER JOIN roles r ON ru.role_id = r.id
WHERE r.name = 'client' 
  AND u.status = 1
  AND u.id NOT IN (
    SELECT user_id FROM facturas WHERE periodo = '01/2026'
  );
```

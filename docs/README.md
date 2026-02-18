# Documentación - Sistema de Facturación REDIN

## 📚 Índice de Documentación

### 🔧 Herramientas de Sistemas (Acceso Restringido)

#### Completar Facturas Faltantes
- **Archivo**: [`SISTEMAS-completar-facturas.md`](./SISTEMAS-completar-facturas.md)
- **URL**: `/admin/period/complete-missing`
- **Descripción**: Herramienta para identificar y generar facturas que faltaron en un periodo
- **Uso**: Solo personal de sistemas - Acceso directo por URL (sin menú)
- **Cuándo usar**: Cuando el proceso de facturación se interrumpe o algunos clientes quedan sin factura

### 📖 Documentación Técnica

#### API Completar Facturas
- **Archivo**: [`completar-facturas-faltantes.md`](./completar-facturas-faltantes.md)
- **Endpoint**: `POST /bill/complete-missing`
- **Descripción**: Documentación técnica completa del endpoint
- **Incluye**: Parámetros, ejemplos de request/response, validaciones, logs

#### Guía Rápida
- **Archivo**: [`README-completar-facturas.md`](./README-completar-facturas.md)
- **Descripción**: Guía de uso rápido para desarrolladores
- **Incluye**: Ejemplos con cURL, PowerShell, Bash

### 🔨 Scripts de Automatización

#### Bash Script (Linux/Mac)
- **Archivo**: [`completar-facturas.sh`](./completar-facturas.sh)
- **Uso**: `./completar-facturas.sh "01/2026" "04/02/2026"`
- **Descripción**: Script para ejecutar desde terminal

#### PowerShell Script (Windows)
- **Archivo**: [`completar-facturas.ps1`](./completar-facturas.ps1)
- **Uso**: `.\completar-facturas.ps1 -Periodo "01/2026" -FechaEmision "04/02/2026"`
- **Descripción**: Script para ejecutar desde PowerShell

### 🔌 Integraciones

#### Colección Postman/Insomnia
- **Archivo**: [`completar-facturas-postman.json`](./completar-facturas-postman.json)
- **Descripción**: Colección de requests para testing
- **Incluye**: Casos de éxito, validaciones, errores

## 🚀 Inicio Rápido

### Para Sistemas/Soporte

Si necesitas completar facturas faltantes:

1. **Acceder a la herramienta web**:
   ```
   https://tu-dominio.com/admin/period/complete-missing
   ```

2. **Verificar qué falta**:
   - Ingresar periodo (ej: `01/2026`)
   - Hacer clic en "Verificar Faltantes"

3. **Completar facturas**:
   - Hacer clic en "Completar Facturas"
   - Esperar resultado

### Para Desarrolladores

Si necesitas integrar el endpoint en scripts:

```bash
# Con cURL
curl -X POST http://localhost/bill/complete-missing \
  -H "Content-Type: application/json" \
  -d '{"periodo":"01/2026","fecha_emision":"04/02/2026"}'

# Con PowerShell
.\docs\completar-facturas.ps1 -Periodo "01/2026" -FechaEmision "04/02/2026"

# Con Bash
./docs/completar-facturas.sh "01/2026" "04/02/2026"
```

## 📋 Casos de Uso Comunes

### 1. Proceso de Facturación Interrumpido
```
Problema: Se detuvo en la factura 126 de 300
Solución: Acceder a /admin/period/complete-missing y completar
Resultado: Genera las 174 facturas faltantes automáticamente
```

### 2. Error en Clientes Específicos
```
Problema: 5 clientes fallaron por error de MercadoPago (amount:0)
Solución: 
  1. Corregir el bug (ya implementado)
  2. Ejecutar completar facturas
Resultado: Solo genera las 5 facturas que faltaron
```

### 3. Cliente Agregado Después
```
Problema: Se agregó cliente con servicios retroactivos
Solución: Ejecutar completar facturas para ese periodo
Resultado: Genera solo la factura del nuevo cliente
```

## ⚠️ Notas Importantes

- **No duplica**: El sistema verifica automáticamente qué facturas ya existen
- **Idempotente**: Se puede ejecutar múltiples veces sin problemas
- **AFIP**: Genera CAE oficial para cada factura
- **Logs**: Todo se registra en `storage/logs/laravel.log`
- **Emails**: Se envían automáticamente a los clientes

## 🔒 Seguridad

- Acceso solo con sesión de administrador
- Herramienta oculta (no aparece en menú)
- URL directa: `/admin/period/complete-missing`
- Requiere confirmación antes de ejecutar

## 📞 Soporte

Para problemas o consultas:
1. Revisar `storage/logs/laravel.log`
2. Consultar documentación técnica en esta carpeta
3. Contactar al equipo de desarrollo

---

**Última actualización**: Febrero 2026

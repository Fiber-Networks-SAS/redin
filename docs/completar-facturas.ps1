# Script PowerShell para completar facturas faltantes de un periodo
# Uso: .\completar-facturas.ps1 -Periodo "01/2026" -FechaEmision "04/02/2026"

param(
    [Parameter(Mandatory=$true, HelpMessage="Periodo en formato m/Y (ej: 01/2026)")]
    [string]$Periodo,
    
    [Parameter(Mandatory=$true, HelpMessage="Fecha de emisión en formato d/m/Y (ej: 04/02/2026)")]
    [string]$FechaEmision,
    
    [Parameter(Mandatory=$false, HelpMessage="URL del endpoint")]
    [string]$Url = "http://localhost/bill/complete-missing"
)

Write-Host "===================================================" -ForegroundColor Cyan
Write-Host "COMPLETAR FACTURAS FALTANTES" -ForegroundColor Cyan
Write-Host "===================================================" -ForegroundColor Cyan
Write-Host "Periodo:        $Periodo"
Write-Host "Fecha Emisión:  $FechaEmision"
Write-Host "URL:            $Url"
Write-Host "===================================================" -ForegroundColor Cyan
Write-Host ""

# Preparar payload
$payload = @{
    periodo = $Periodo
    fecha_emision = $FechaEmision
} | ConvertTo-Json

# Ejecutar request
Write-Host "Enviando request..." -ForegroundColor Yellow
Write-Host ""

try {
    $response = Invoke-WebRequest -Uri $Url -Method POST -Body $payload -ContentType "application/json" -UseBasicParsing
    
    $statusCode = $response.StatusCode
    $body = $response.Content | ConvertFrom-Json
    
    Write-Host "Status Code: $statusCode" -ForegroundColor Green
    Write-Host ""
    Write-Host "Respuesta:"
    $body | ConvertTo-Json -Depth 10
    Write-Host ""
    
    if ($statusCode -eq 200) {
        $facturasCreadas = $body.summary.facturas_creadas
        $errores = $body.summary.errores
        
        Write-Host "===================================================" -ForegroundColor Cyan
        Write-Host "RESULTADO" -ForegroundColor Cyan
        Write-Host "===================================================" -ForegroundColor Cyan
        Write-Host "✓ Facturas creadas: $facturasCreadas" -ForegroundColor Green
        Write-Host "✗ Errores: $errores" -ForegroundColor $(if ($errores -gt 0) { "Red" } else { "Green" })
        Write-Host "===================================================" -ForegroundColor Cyan
        
        if ($errores -gt 0) {
            Write-Host ""
            Write-Host "⚠ Revisar el log para detalles de los errores:" -ForegroundColor Yellow
            Write-Host "  Get-Content -Path storage\logs\laravel.log -Tail 50 -Wait" -ForegroundColor Yellow
            
            Write-Host ""
            Write-Host "Detalles de errores:" -ForegroundColor Yellow
            $body.errores | ForEach-Object {
                Write-Host "  - Cliente $($_.nro_cliente): $($_.error)" -ForegroundColor Red
            }
        }
    }
    
} catch {
    $statusCode = $_.Exception.Response.StatusCode.value__
    $errorBody = $_.ErrorDetails.Message
    
    Write-Host "===================================================" -ForegroundColor Red
    Write-Host "ERROR" -ForegroundColor Red
    Write-Host "===================================================" -ForegroundColor Red
    Write-Host "El request falló con código: $statusCode" -ForegroundColor Red
    Write-Host ""
    Write-Host "Detalle del error:" -ForegroundColor Red
    
    if ($errorBody) {
        try {
            $errorBody | ConvertFrom-Json | ConvertTo-Json -Depth 10
        } catch {
            Write-Host $errorBody
        }
    } else {
        Write-Host $_.Exception.Message
    }
    
    Write-Host "===================================================" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Proceso completado." -ForegroundColor Green

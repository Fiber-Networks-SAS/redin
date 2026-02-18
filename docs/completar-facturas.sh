#!/bin/bash

# Script para completar facturas faltantes de un periodo
# Uso: ./completar-facturas.sh <periodo> <fecha_emision>
# Ejemplo: ./completar-facturas.sh "01/2026" "04/02/2026"

# Verificar argumentos
if [ $# -ne 2 ]; then
    echo "Uso: $0 <periodo> <fecha_emision>"
    echo "Ejemplo: $0 \"01/2026\" \"04/02/2026\""
    exit 1
fi

PERIODO=$1
FECHA_EMISION=$2

# URL del endpoint (ajustar según entorno)
URL="http://localhost/bill/complete-missing"

# Headers
CONTENT_TYPE="Content-Type: application/json"

# Payload
PAYLOAD=$(cat <<EOF
{
  "periodo": "$PERIODO",
  "fecha_emision": "$FECHA_EMISION"
}
EOF
)

echo "==================================================="
echo "COMPLETAR FACTURAS FALTANTES"
echo "==================================================="
echo "Periodo: $PERIODO"
echo "Fecha Emisión: $FECHA_EMISION"
echo "URL: $URL"
echo "==================================================="
echo ""

# Ejecutar request
echo "Enviando request..."
echo ""

RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" -X POST "$URL" \
  -H "$CONTENT_TYPE" \
  -d "$PAYLOAD")

# Separar body y status code
HTTP_BODY=$(echo "$RESPONSE" | sed -e 's/HTTP_STATUS\:.*//g')
HTTP_STATUS=$(echo "$RESPONSE" | tr -d '\n' | sed -e 's/.*HTTP_STATUS://')

# Mostrar respuesta formateada
echo "Status Code: $HTTP_STATUS"
echo ""
echo "Respuesta:"
echo "$HTTP_BODY" | python3 -m json.tool 2>/dev/null || echo "$HTTP_BODY"
echo ""

# Evaluar resultado
if [ "$HTTP_STATUS" -eq 200 ]; then
    FACTURAS_CREADAS=$(echo "$HTTP_BODY" | grep -o '"facturas_creadas":[0-9]*' | grep -o '[0-9]*')
    ERRORES=$(echo "$HTTP_BODY" | grep -o '"errores":[0-9]*' | grep -o '[0-9]*' | tail -1)
    
    echo "==================================================="
    echo "RESULTADO"
    echo "==================================================="
    echo "✓ Facturas creadas: $FACTURAS_CREADAS"
    echo "✗ Errores: $ERRORES"
    echo "==================================================="
    
    if [ "$ERRORES" -gt 0 ]; then
        echo ""
        echo "⚠ Revisar el log para detalles de los errores:"
        echo "  tail -f storage/logs/laravel.log"
    fi
else
    echo "==================================================="
    echo "ERROR"
    echo "==================================================="
    echo "El request falló con código: $HTTP_STATUS"
    echo "==================================================="
    exit 1
fi

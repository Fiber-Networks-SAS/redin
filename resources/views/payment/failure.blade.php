<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('constants.title') }} - Pago Rechazado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px;
            background-color: #f5f5f5;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .error-icon {
            color: #dc3545;
            font-size: 64px;
            margin-bottom: 20px;
        }
        .title {
            color: #dc3545;
            font-size: 28px;
            margin-bottom: 20px;
        }
        .message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .payment-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
        .btn {
            display: inline-block;
            background: #0000FF;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
        }
        .btn:hover {
            background: #0275a8;
        }
        .btn-retry {
            background: #ffc107;
            color: #000;
        }
        .btn-retry:hover {
            background: #e0a800;
        }
        .reasons {
            text-align: left;
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">❌</div>
        <h1 class="title">Pago Rechazado</h1>
        <p class="message">Tu pago no pudo ser procesado. Por favor, verifica los datos e intenta nuevamente.</p>
        
        @if(isset($collection_id) || isset($collection_status) || isset($external_reference))
            <div class="payment-details">
                <h3>Detalles del Intento:</h3>
                @if(isset($collection_id))
                    <p><strong>ID de Transacción:</strong> {{ $collection_id }}</p>
                @endif
                @if(isset($collection_status))
                    <p><strong>Estado:</strong> {{ $collection_status }}</p>
                @endif
                @if(isset($external_reference))
                    <p><strong>Referencia:</strong> {{ $external_reference }}</p>
                @endif
            </div>
        @endif

        <div class="reasons">
            <h4>Posibles causas del rechazo:</h4>
            <ul>
                <li>Datos incorrectos de la tarjeta</li>
                <li>Fondos insuficientes</li>
                <li>Límite de compra excedido</li>
                <li>Tarjeta vencida o bloqueada</li>
                <li>Problemas con el banco emisor</li>
            </ul>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="javascript:history.back()" class="btn btn-retry">Intentar Nuevamente</a>
            <a href="{{ config('app.url') }}" class="btn">Volver al Inicio</a>
            @auth
                <a href="/dashboard" class="btn">Mi Panel</a>
            @endauth
        </div>

        <div style="margin-top: 20px; color: #666; font-size: 14px;">
            <p>Si el problema persiste, contacta con nuestro servicio de atención al cliente:</p>
            <p>{{ config('constants.account_info') }} | {{ config('constants.company_tel') }}</p>
        </div>
    </div>

    <script>
        // Auto-cerrar ventana después de 15 segundos si es un popup
        if (window.opener && window.opener !== window) {
            setTimeout(function() {
                window.close();
            }, 15000);
        }
    </script>
</body>
</html>
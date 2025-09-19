<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('constants.title') }} - Pago Exitoso</title>
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
        .success-icon {
            color: #28a745;
            font-size: 64px;
            margin-bottom: 20px;
        }
        .title {
            color: #28a745;
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
        .error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        @if(isset($error))
            <div class="success-icon error">⚠️</div>
            <h1 class="title error">Error en el Pago</h1>
            <p class="message">{{ $error }}</p>
        @else
            <div class="success-icon">✅</div>
            <h1 class="title">¡Pago Exitoso!</h1>
            <p class="message">Tu pago ha sido procesado correctamente. Recibirás un comprobante por email.</p>
            
            @if(isset($payment_id))
                <div class="payment-details">
                    <h3>Detalles del Pago:</h3>
                    <p><strong>ID de Pago:</strong> {{ $payment_id }}</p>
                    @if(isset($status))
                        <p><strong>Estado:</strong> {{ $status }}</p>
                    @endif
                    @if(isset($collection_status))
                        <p><strong>Estado de Cobro:</strong> {{ $collection_status }}</p>
                    @endif
                </div>
            @endif
        @endif
        
        <div style="margin-top: 30px;">
            <a href="{{ config('app.url') }}" class="btn">Volver al Inicio</a>
        </div>
    </div>

    <script>
        // Auto-cerrar ventana después de 10 segundos si es un popup
        if (window.opener && window.opener !== window) {
            setTimeout(function() {
                window.close();
            }, 10000);
        }
    </script>
</body>
</html>
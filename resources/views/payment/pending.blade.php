<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('constants.title') }} - Pago Pendiente</title>
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
        .pending-icon {
            color: #ffc107;
            font-size: 64px;
            margin-bottom: 20px;
        }
        .title {
            color: #ffc107;
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
        .status-check {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
            text-align: left;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #ffc107;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .refresh-btn {
            background: #17a2b8;
        }
        .refresh-btn:hover {
            background: #138496;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="pending-icon">⏳</div>
        <h1 class="title">Pago Pendiente de Aprobación</h1>
        <p class="message">Tu pago está siendo procesado. Esto puede tomar unos minutos dependiendo del método de pago utilizado.</p>
        
        @if(isset($collection_id) || isset($payment_id) || isset($external_reference))
            <div class="payment-details">
                <h3>Detalles del Pago:</h3>
                @if(isset($collection_id))
                    <p><strong>ID de Pago:</strong> {{ $collection_id }}</p>
                @endif
                @if(isset($payment_id))
                    <p><strong>ID de Transacción:</strong> {{ $payment_id }}</p>
                @endif
                @if(isset($external_reference))
                    <p><strong>Referencia:</strong> {{ $external_reference }}</p>
                @endif
                @if(isset($payment_type))
                    <p><strong>Método de Pago:</strong> {{ $payment_type }}</p>
                @endif
            </div>
        @endif

        <div class="status-check">
            <h4>Estado del Pago:</h4>
            <div id="status-container">
                <div class="loading"></div>
                <span>Verificando estado...</span>
            </div>
            <p style="margin-top: 10px; font-size: 14px; color: #666;">
                Se actualizará automáticamente cada 30 segundos.
            </p>
        </div>

        <div style="margin-top: 30px;">
            <button onclick="checkPaymentStatus()" class="btn refresh-btn" id="refresh-btn">
                Verificar Estado
            </button>
            <a href="{{ config('app.url') }}" class="btn">Volver al Inicio</a>
            @auth
                <a href="/dashboard" class="btn">Mi Panel</a>
            @endauth
        </div>

        <div style="margin-top: 30px; color: #666; font-size: 14px; text-align: left;">
            <h4>Información sobre pagos pendientes:</h4>
            <ul style="text-align: left; display: inline-block;">
                <li><strong>Tarjeta de débito:</strong> Generalmente se aprueban al instante</li>
                <li><strong>Transferencia bancaria:</strong> Puede tomar 1-3 días hábiles</li>
                <li><strong>Efectivo:</strong> Se acredita al presentar el comprobante</li>
                <li><strong>Rapipago/Pago Fácil:</strong> Hasta 2 horas después del pago</li>
            </ul>
        </div>

        <div style="margin-top: 20px; color: #666; font-size: 14px;">
            <p>¿Necesitas ayuda? Contacta con nosotros:</p>
            <p>{{ config('constants.account_info') }} | {{ config('constants.company_tel') }}</p>
        </div>
    </div>

    <script>
        let checkInterval;
        let attempts = 0;
        const maxAttempts = 20; // 10 minutos máximo de verificación automática

        function checkPaymentStatus() {
            @if(isset($preference_id))
            const preferenceId = '{{ $preference_id }}';
            @else
            const preferenceId = null;
            @endif
            
            if (!preferenceId) {
                document.getElementById('status-container').innerHTML = 
                    '<span style="color: #dc3545;">No se pudo verificar el estado del pago</span>';
                return;
            }

            document.getElementById('refresh-btn').disabled = true;
            document.getElementById('status-container').innerHTML = 
                '<div class="loading"></div><span>Verificando estado...</span>';

            fetch('/api/payment/preference/' + preferenceId + '/status')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.status === 'approved') {
                        document.getElementById('status-container').innerHTML = 
                            '<span style="color: #28a745;">✅ Pago aprobado - Redirigiendo...</span>';
                        setTimeout(function() {
                            window.location.href = '/payment/success?collection_id=' + data.payment_id;
                        }, 2000);
                    } else if (data.success && data.status === 'rejected') {
                        document.getElementById('status-container').innerHTML = 
                            '<span style="color: #dc3545;">❌ Pago rechazado - Redirigiendo...</span>';
                        setTimeout(function() {
                            window.location.href = '/payment/failure?collection_status=rejected';
                        }, 2000);
                    } else {
                        document.getElementById('status-container').innerHTML = 
                            '<span style="color: #ffc107;">⏳ Aún pendiente de aprobación</span>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('status-container').innerHTML = 
                        '<span style="color: #dc3545;">Error al verificar el estado</span>';
                })
                .finally(() => {
                    document.getElementById('refresh-btn').disabled = false;
                });
        }

        // Verificación automática cada 30 segundos
        function startAutoCheck() {
            checkInterval = setInterval(function() {
                attempts++;
                if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    document.getElementById('status-container').innerHTML += 
                        '<br><small style="color: #666;">Verificación automática detenida. Usa el botón para verificar manualmente.</small>';
                    return;
                }
                checkPaymentStatus();
            }, 30000);
        }

        // Iniciar verificación automática cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            // Primera verificación después de 5 segundos
            setTimeout(checkPaymentStatus, 5000);
            // Luego cada 30 segundos
            startAutoCheck();
        });

        // Auto-cerrar ventana después de 20 minutos si es un popup
        if (window.opener && window.opener !== window) {
            setTimeout(function() {
                window.close();
            }, 1200000); // 20 minutos
        }

        // Limpiar intervalo al salir de la página
        window.addEventListener('beforeunload', function() {
            if (checkInterval) {
                clearInterval(checkInterval);
            }
        });
    </script>
</body>
</html>
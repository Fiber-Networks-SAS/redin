<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Aprobado - Factura Pagada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #d4edda;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .alert-success {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        .highlight {
            background-color: #d4edda;
            padding: 2px 5px;
            border-radius: 3px;
            color: #155724;
            font-weight: bold;
        }
        strong {
            color: #2c3e50;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="success-icon">✅</div>
        <h1 style="color: #155724; margin: 0;">¡PAGO APROBADO!</h1>
        <p style="margin: 5px 0 0 0; color: #155724;">Su factura ha sido marcada como pagada</p>
    </div>

    <div class="content">
        <h2 style="color: #2c3e50;">Estimado/a {{$pagoInformado->usuario->firstname}} {{$pagoInformado->usuario->lastname}},</h2>
        
        <p>¡Excelentes noticias! Hemos validado exitosamente el pago que informó y su factura ha sido marcada como <span class="highlight">PAGADA</span>.</p>

        <div class="alert-success">
            <strong>✅ Pago Validado y Aprobado</strong><br>
            Su pago ha sido verificado y aprobado por nuestro equipo de administración el {{$pagoInformado->fecha_validacion->format('d/m/Y')}} a las {{$pagoInformado->fecha_validacion->format('H:i')}} hs.
        </div>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #2c3e50;">📄 Información de la Factura</h3>
            <p><strong>Número:</strong> {{$pagoInformado->factura->talonario->letra}} {{str_pad($pagoInformado->factura->talonario->nro_punto_vta, 4, '0', STR_PAD_LEFT)}} - {{str_pad($pagoInformado->factura->nro_factura, 8, '0', STR_PAD_LEFT)}}</p>
            <p><strong>Período:</strong> {{$pagoInformado->factura->periodo}}</p>
            <p><strong>Cliente:</strong> {{$pagoInformado->factura->nro_cliente}}</p>
            <p><strong>Estado:</strong> <span class="highlight">PAGADA</span></p>
        </div>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #2c3e50;">💰 Detalles del Pago Aprobado</h3>
            <p><strong>Importe Pagado:</strong> <span class="highlight">${{number_format($pagoInformado->importe_informado, 2, ',', '.')}}</span></p>
            <p><strong>Fecha del Pago:</strong> {{$pagoInformado->fecha_pago_informado->format('d/m/Y')}}</p>
            <p><strong>Tipo:</strong> {{$pagoInformado->tipo_transferencia_texto}}</p>
            <p><strong>Banco:</strong> {{$pagoInformado->banco_origen}}</p>
            <p><strong>Nro. Operación:</strong> {{$pagoInformado->numero_operacion}}</p>
            @if($pagoInformado->validadoPor)
            <p><strong>Validado por:</strong> {{$pagoInformado->validadoPor->firstname}} {{$pagoInformado->validadoPor->lastname}}</p>
            @endif
        </div>

        @if($pagoInformado->observaciones)
        <div class="info-box">
            <h4 style="margin-top: 0; color: #2c3e50;">📝 Observaciones del Administrador:</h4>
            <p style="font-style: italic;">"{{$pagoInformado->observaciones}}"</p>
        </div>
        @endif

        <h3 style="color: #2c3e50;">🎉 ¡Su cuenta está al día!</h3>
        <ul>
            <li>Su factura ha sido marcada como pagada en nuestro sistema</li>
            <li>No necesita realizar ninguna acción adicional</li>
            <li>Este correo sirve como comprobante de pago aprobado</li>
            <li>Puede verificar el estado en su panel de cliente</li>
        </ul>

        <div class="info-box">
            <h4 style="margin-top: 0; color: #2c3e50;">🏠 Acceda a su panel de cliente</h4>
            <p>Puede verificar el estado actualizado de sus facturas ingresando a:</p>
            <p><strong>{{url('/login')}}</strong></p>
        </div>

        <div class="info-box">
            <h4 style="margin-top: 0; color: #2c3e50;">📞 ¿Necesita ayuda?</h4>
            <p>Si tiene alguna consulta, puede contactarnos:</p>
            <ul>
                <li>📧 Email: administracion@redin.com.ar</li>
                <li>📞 Teléfono: [NÚMERO DE CONTACTO]</li>
            </ul>
        </div>

        <p>Gracias por su confianza y por mantenerse al día con sus pagos.</p>
        
        <p>Saludos cordiales,<br>
        <strong>Equipo REDIN</strong></p>
    </div>

    <div class="footer">
        <p>Este es un correo automático, por favor no responda a esta dirección.</p>
        <p>© {{date('Y')}} REDIN - Todos los derechos reservados</p>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Informado - Pendiente de Validación</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
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
            background-color: #e7f3ff;
            padding: 2px 5px;
            border-radius: 3px;
        }
        strong {
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="color: #2c3e50; margin: 0;">REDIN - Pago Informado</h1>
        <p style="margin: 5px 0 0 0; color: #666;">Su pago ha sido registrado correctamente</p>
    </div>

    <div class="content">
        <h2 style="color: #2c3e50;">Estimado/a {{$pagoInformado->usuario->firstname}} {{$pagoInformado->usuario->lastname}},</h2>
        
        <p>Hemos recibido correctamente la información del pago que realizó para su factura. Le confirmamos los siguientes datos:</p>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #2c3e50;">📄 Información de la Factura</h3>
            <p><strong>Número:</strong> {{$pagoInformado->factura->talonario->letra}} {{str_pad($pagoInformado->factura->talonario->nro_punto_vta, 4, '0', STR_PAD_LEFT)}} - {{str_pad($pagoInformado->factura->nro_factura, 8, '0', STR_PAD_LEFT)}}</p>
            <p><strong>Período:</strong> {{$pagoInformado->factura->periodo}}</p>
            <p><strong>Importe Original:</strong> ${{number_format($pagoInformado->factura->importe_total, 2, ',', '.')}}</p>
        </div>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #2c3e50;">💰 Información del Pago Informado</h3>
            <p><strong>Importe Pagado:</strong> <span class="highlight">${{number_format($pagoInformado->importe_informado, 2, ',', '.')}}</span></p>
            <p><strong>Fecha del Pago:</strong> {{$pagoInformado->fecha_pago_informado->format('d/m/Y')}}</p>
            <p><strong>Tipo:</strong> {{$pagoInformado->tipo_transferencia_texto}}</p>
            <p><strong>Banco:</strong> {{$pagoInformado->banco_origen}}</p>
            <p><strong>Nro. Operación:</strong> {{$pagoInformado->numero_operacion}}</p>
            @if($pagoInformado->cbu_origen)
            <p><strong>CBU Origen:</strong> {{$pagoInformado->cbu_origen}}</p>
            @endif
            <p><strong>Titular:</strong> {{$pagoInformado->titular_cuenta}}</p>
        </div>

        <div class="alert">
            <strong>⏳ Estado Actual: PENDIENTE DE VALIDACIÓN</strong><br>
            Su factura permanecerá pendiente hasta que nuestro equipo de administración valide la información proporcionada.
        </div>

        <h3 style="color: #2c3e50;">📋 Próximos Pasos:</h3>
        <ul>
            <li>Nuestro equipo revisará la información en las próximas <strong>24-48 horas</strong></li>
            <li>Verificaremos que los datos coincidan con nuestros registros</li>
            <li>Le enviaremos un correo de confirmación con el resultado</li>
            <li>Si el pago es aprobado, su factura se marcará como pagada automáticamente</li>
        </ul>

        <div class="info-box">
            <h4 style="margin-top: 0; color: #2c3e50;">❓ ¿Necesita ayuda?</h4>
            <p>Si tiene alguna consulta sobre su pago, puede contactarnos:</p>
            <ul>
                <li>📧 Email: administracion@redin.com.ar</li>
                <li>📞 Teléfono: [NÚMERO DE CONTACTO]</li>
            </ul>
        </div>

        <p><strong>Importante:</strong> Conserve este correo como comprobante de que ha informado su pago correctamente.</p>
        
        <p>Gracias por elegirnos.</p>
        
        <p>Saludos cordiales,<br>
        <strong>Equipo REDIN</strong></p>
    </div>

    <div class="footer">
        <p>Este es un correo automático, por favor no responda a esta dirección.</p>
        <p>© {{date('Y')}} REDIN - Todos los derechos reservados</p>
    </div>
</body>
</html>
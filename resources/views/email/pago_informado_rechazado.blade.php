<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Rechazado - Información Importante</title>
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
            background-color: #f8d7da;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .alert-danger {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .alert-warning {
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
            background-color: #f8d7da;
            padding: 2px 5px;
            border-radius: 3px;
            color: #721c24;
            font-weight: bold;
        }
        strong {
            color: #2c3e50;
        }
        .warning-icon {
            font-size: 48px;
            color: #dc3545;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="warning-icon">❌</div>
        <h1 style="color: #721c24; margin: 0;">Pago Rechazado</h1>
        <p style="margin: 5px 0 0 0; color: #721c24;">Información importante sobre su pago informado</p>
    </div>

    <div class="content">
        <h2 style="color: #2c3e50;">Estimado/a {{$pagoInformado->usuario->firstname}} {{$pagoInformado->usuario->lastname}},</h2>
        
        <p>Le informamos que el pago que reportó para su factura ha sido <span class="highlight">RECHAZADO</span> por nuestro equipo de administración tras la revisión correspondiente.</p>

        <div class="alert-danger">
            <strong>❌ Pago Rechazado</strong><br>
            Su pago fue rechazado el {{$pagoInformado->fecha_validacion->format('d/m/Y')}} a las {{$pagoInformado->fecha_validacion->format('H:i')}} hs.
        </div>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #2c3e50;">📄 Información de la Factura</h3>
            <p><strong>Número:</strong> {{$pagoInformado->factura->talonario->letra}} {{str_pad($pagoInformado->factura->talonario->nro_punto_vta, 4, '0', STR_PAD_LEFT)}} - {{str_pad($pagoInformado->factura->nro_factura, 8, '0', STR_PAD_LEFT)}}</p>
            <p><strong>Período:</strong> {{$pagoInformado->factura->periodo}}</p>
            <p><strong>Cliente:</strong> {{$pagoInformado->factura->nro_cliente}}</p>
            <p><strong>Estado:</strong> <span class="highlight">PENDIENTE</span> (sin cambios)</p>
        </div>

        <div class="info-box">
            <h3 style="margin-top: 0; color: #2c3e50;">💰 Datos del Pago Rechazado</h3>
            <p><strong>Importe Informado:</strong> ${{number_format($pagoInformado->importe_informado, 2, ',', '.')}}</p>
            <p><strong>Fecha del Pago:</strong> {{$pagoInformado->fecha_pago_informado->format('d/m/Y')}}</p>
            <p><strong>Tipo:</strong> {{$pagoInformado->tipo_transferencia_texto}}</p>
            <p><strong>Banco:</strong> {{$pagoInformado->banco_origen}}</p>
            <p><strong>Nro. Operación:</strong> {{$pagoInformado->numero_operacion}}</p>
            @if($pagoInformado->validadoPor)
            <p><strong>Rechazado por:</strong> {{$pagoInformado->validadoPor->firstname}} {{$pagoInformado->validadoPor->lastname}}</p>
            @endif
        </div>

        <div class="alert-danger">
            <h4 style="margin-top: 0; color: #721c24;">📝 Motivo del Rechazo:</h4>
            <p style="font-weight: bold; font-size: 16px;">"{{$pagoInformado->observaciones}}"</p>
        </div>

        <div class="alert-warning">
            <h4 style="margin-top: 0; color: #856404;">⚠️ Estado Actual de su Factura:</h4>
            <ul style="margin: 10px 0;">
                <li>Su factura <strong>permanece PENDIENTE</strong> de pago</li>
                <li>No se ha registrado ningún pago válido</li>
                <li>Debe realizar el pago nuevamente</li>
            </ul>
        </div>

        <h3 style="color: #2c3e50;">🔄 Próximos Pasos:</h3>
        <ol>
            <li><strong>Revise el motivo del rechazo</strong> indicado arriba</li>
            <li><strong>Verifique sus datos bancarios</strong> y el comprobante de pago</li>
            <li><strong>Si el pago fue realizado correctamente:</strong>
                <ul>
                    <li>Contacte con nuestro equipo para aclarar la situación</li>
                    <li>Proporcione documentación adicional si es necesaria</li>
                </ul>
            </li>
            <li><strong>Si el pago no fue realizado o hay errores:</strong>
                <ul>
                    <li>Realice el pago correctamente</li>
                    <li>Informe el nuevo pago con los datos correctos</li>
                </ul>
            </li>
        </ol>

        <div class="info-box">
            <h4 style="margin-top: 0; color: #2c3e50;">💳 Opciones de Pago Disponibles</h4>
            <p>Puede realizar el pago de su factura a través de:</p>
            <ul>
                <li><strong>MercadoPago:</strong> Pago inmediato con tarjeta de crédito/débito</li>
                <li><strong>Transferencia/CBU:</strong> Informar nuevo pago con datos correctos</li>
            </ul>
            <p><strong>Acceda a su panel:</strong> {{url('/login')}}</p>
        </div>

        <div class="info-box">
            <h4 style="margin-top: 0; color: #2c3e50;">📞 Contacto Inmediato</h4>
            <p>Si tiene dudas sobre este rechazo, contáctenos de inmediato:</p>
            <ul>
                <li>📧 Email: administracion@redin.com.ar</li>
                <li>📞 Teléfono: [NÚMERO DE CONTACTO]</li>
                <li>🕒 Horario: Lunes a Viernes de 9:00 a 18:00 hs</li>
            </ul>
        </div>

        <p><strong>Importante:</strong> Para evitar inconvenientes, le recomendamos resolver esta situación a la brevedad.</p>
        
        <p>Quedamos a su disposición para cualquier consulta.</p>
        
        <p>Saludos cordiales,<br>
        <strong>Equipo REDIN</strong></p>
    </div>

    <div class="footer">
        <p>Este es un correo automático, por favor no responda a esta dirección.</p>
        <p>© {{date('Y')}} REDIN - Todos los derechos reservados</p>
    </div>
</body>
</html>
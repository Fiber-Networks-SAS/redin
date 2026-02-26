<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ config('constants.title') }} | Nota de Crédito</title>

    <style>
        @page {
            margin: 5px 10px !important;
        }

        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            margin: 5px;
            font-size: 12px;
        }

        .title {
            background-color: #0000FF;
            height: 90px;
        }

        .title img {
            width: 200px;
            border-radius: 5px;
            margin: 10px;
            display: inline-block;
        }

        .header {
            padding: 10px 10px;
            width: 100%;
            height: 165px;
            border: 1px solid #0000FF;
        }

        .header .row {
            display: block;
            clear: both;
            height: 85px;
        }

        .header .row .info_empresa {
            display: inline-block;
            width: 350px;
            border: 1px solid white;
            height: auto;
            float: left;
        }

        .header .row .tipo_comprobante {
            display: inline-block;
            width: 50px;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 50px;
            text-align: center;
            float: left;
        }

        .header .row .tipo_comprobante .subtipo {
            font-size: 11px;
            font-weight: normal;
            display: block;
            border-top: 1px solid #000;
            padding-top: 2px;
            margin-top: 2px;
        }

        .header .row .info_comprobante {
            display: inline-block;
            width: 330px;
            border: 1px solid white;
            padding-left: 30px;
            float: left;
        }

        .header .row .darkness {
            float: left;
            display: inline-block;
            background-color: #cdcdcd;
            font-weight: bold;
            width: 100%;
        }

        .header .row .darkness p {
            padding: 2px 5px;
            font-size: 15px;
        }

        .content {
            margin: 10px 0;
        }

        p {
            font-size: 12px;
            margin: 2px 0;
            padding: 0;
        }

        table {
            width: 100%;
            border: 0;
            border-spacing: 0;
        }

        thead {
            background-color: #eee;
        }

        tfoot {
            background-color: #eee;
        }

        thead tr th {
            padding: 10px;
            border-bottom: 1px solid #cdcdcd;
            font-size: 14px;
        }

        tfoot tr th {
            padding: 5px 10px;
            border-bottom: 1px solid #cdcdcd;
            font-size: 14px;
        }

        tr {
            margin: 2px 0;
        }

        td {
            border: 0;
            padding: 3px 10px;
            width: 50%;
            font-size: 14px;
        }

        .left {
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
            padding-right: 10px;
        }

        .detalle_row {
            border-top: 1px solid #cdcdcd;
        }

        .footer {
            bottom: 0;
            position: absolute;
        }

        .footer .info {
            clear: both;
            text-align: center;
        }

        .footer .info .mensaje {
            margin: 5px 0;
            text-align: left;
            padding: 5px 10px;
            border: 1px solid #0000FF;
            clear: both;
            display: block;
        }

        .referencia_box {
            border: 1px solid #cdcdcd;
            padding: 8px 12px;
            margin: 10px 0;
            background-color: #f9f9f9;
            font-size: 13px;
        }

        .referencia_box p {
            font-size: 13px;
        }
    </style>
</head>

<body>

    <?php
    if (!function_exists('nc_clean_number')) {
        function nc_clean_number($value)
        {
            if (is_null($value) || $value === '') {
                return 0;
            }
            if (is_numeric($value)) {
                return floatval($value);
            }
            $cleaned = str_replace('.', '', trim($value));
            $cleaned = str_replace(',', '.', $cleaned);
            return floatval($cleaned);
        }
    }

    if (!function_exists('nc_number_format')) {
        function nc_number_format($value, $decimals = 2)
        {
            $num = nc_clean_number($value);
            return number_format($num, $decimals, ',', '.');
        }
    }
    ?>

    <?php $i = 1; ?>
    <?php foreach ($notasCredito as $nc): ?>

    <div class="title">
        <img src="{{ config('constants.logo_pdf') }}" alt="Logo">
    </div>

    <div class="header">

        <div class="row">

            <div class="info_empresa">
                <p>Fiber Networks SAS</p>
                <p>{{ config('constants.company_dir') }}</p>
                <p>CUIT: {{ config('constants.company_cuit') }}</p>
                <p>IIBB: {{ config('constants.company_iibb') }}</p>
                <p>{{ config('constants.company_iva') }}</p>
                <p>CAE: {{ $nc->cae }}</p>
                <p>Vto. CAE: {{ $nc->cae_vto instanceof \Carbon\Carbon ? $nc->cae_vto->format('d/m/Y') : $nc->cae_vto }}</p>
            </div>

            <div class="tipo_comprobante">
                {{ $nc->talonario->letra }}
                <span class="subtipo">N.C.</span>
            </div>

            <div class="info_comprobante">
                <p><strong>NOTA DE CRÉDITO</strong></p>
                <p>Nro.: {{ str_pad($nc->talonario->nro_punto_vta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($nc->nro_nota_credito, 8, '0', STR_PAD_LEFT) }}</p>
                <p><strong>{{ strtoupper($nc->factura->cliente->firstname . ' ' . $nc->factura->cliente->lastname) }}</strong></p>
                <p>Nro. Cliente: {{ $nc->nro_cliente }}</p>
                <p>DNI/CUIT: {{ $nc->factura->cliente->dni }}</p>
                <p>Domicilio: Calle {{ $nc->factura->cliente->calle . ' ' . $nc->factura->cliente->altura . ' - Mz.' . $nc->factura->cliente->manzana }}</p>
                <p>Fecha de emisión: {{ $nc->fecha_emision instanceof \Carbon\Carbon ? $nc->fecha_emision->format('d/m/Y') : $nc->fecha_emision }}</p>
            </div>

        </div>

        <div class="row" style="height: auto; margin-top: 5px;">
            <div class="darkness">
                <p>Período: {{ $nc->periodo }}</p>
            </div>
        </div>

    </div>

    <div class="content">

        <div class="referencia_box">
            <p>
                <strong>Factura de referencia:</strong>
                {{ $nc->talonario->letra }}-{{ str_pad($nc->talonario->nro_punto_vta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($nc->factura->nro_factura, 8, '0', STR_PAD_LEFT) }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Motivo:</strong> {{ $nc->motivo }}
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 60%;" class="left">Concepto</th>
                    <th style="width: 10%;" class="right">Neto</th>
                    <th style="width: 10%;" class="right">Tasa</th>
                    <th style="width: 10%;" class="right">IVA</th>
                    <th style="width: 10%;" class="right">Total</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td class="detalle_row left">
                        Nota de Crédito — Período {{ $nc->periodo }}
                        @if($nc->motivo)
                            <br><small style="color: #555;">{{ $nc->motivo }}</small>
                        @endif
                    </td>
                    <td class="right">-${{ nc_number_format($nc->importe_bonificacion) }}</td>
                    <td class="right">21%</td>
                    <td class="right">-${{ nc_number_format($nc->importe_iva) }}</td>
                    <td class="right">-${{ nc_number_format($nc->importe_total) }}</td>
                </tr>
            </tbody>

            <tfoot>
                <tr>
                    <th style="width: 60%;" class="left">Total Nota de Crédito</th>
                    <th style="width: 10%;" class="right">-${{ nc_number_format($nc->importe_bonificacion) }}</th>
                    <th style="width: 10%;" class="right"></th>
                    <th style="width: 10%;" class="right">-${{ nc_number_format($nc->importe_iva) }}</th>
                    <th style="width: 10%;" class="right">-${{ nc_number_format($nc->importe_total) }}</th>
                </tr>
            </tfoot>

        </table>

    </div>

    <div class="footer">
        <div class="info">
            <div class="mensaje">
                <h3>Régimen de Transparencia Fiscal al Consumidor Ley 27.743</h3>
                <ul>
                    <li>IVA contenido: ${{ nc_number_format($nc->importe_iva) }}</li>
                    <li>De cada item en la presente nota de crédito se ha discriminado el IVA para preservar la transparencia fiscal.</li>
                </ul>
            </div>
            <h4>Consulte su estado de cuenta online</h4>
            <p>{{ config('constants.company_web') }}</p>
            <p>Tel.: {{ config('constants.company_tel') }}</p>
            <p>{{ config('constants.account_info') }}</p>
        </div>
    </div>

    <?php if ($i++ < count($notasCredito)): ?>
    <div style="page-break-after:always;"></div>
    <?php endif; ?>

    <?php endforeach; ?>

</body>

</html>

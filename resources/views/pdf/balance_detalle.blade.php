<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>{{config('constants.title')}} | Balance</title>

    <style>
        @page { margin: 10px 10px !important; }
        body{
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            margin: 5px;
            font-size: 12px;
        }
        h1{
            margin: 0;
            font-size: 16px;
            padding: 2px 0;
            text-align: center;
        }
        h2{
            margin: 0;
            font-size: 14px;
            padding: 0 0 5px 0;
            text-align: center;
        }  
        h3{
            margin: 15px 0 3px 0;
            background-color: #dddddd;
            width: 100%;
            padding: 5px;
            font-size: 13px;
        }
        p{
            font-size: 10px;
            margin: 2px 0;
            padding: 0;
        }
        .header{
            width: 100%;
            height: 90px;
        }
        .logo{
            float: left;
            width: 150px;
        }
        .logo img{
            width: 60px !important;
        }
        .titulo{
            float: left;
            width: 400px;
        }
      
        .ntramite{
            border: 1px solid #cdcdcd;
            float: right;
            padding: 5px;
            width: 192px;
            margin: 0;
        }
        .bar-code-container{
            float: right;
            padding: 5px 0;
            width: 202px;
            margin: 30px 0 0 0;
            clear: both;
        }
        .header_fecha{
            float: right;
            padding: 5px 0;
            width: 202px;
            margin: 0;
            clear: both;
        }

        .left{
            text-align: left;
        }
        .center{
            text-align: center;
        }
        .right {
            text-align: right;
            padding-right: 10px;
        }
        table{
            width: 100%; 
            border: 0;
            border-spacing: 0;
        }
        thead{
            background-color:#eee;
        }
        tfoot{
            background-color:#eee;
        }
        tr{
            margin: 2px 0;
        }
        td{
            border: 0;
            padding: 3px 0;
            width: 50%;
            font-size: 13px;
        }

        .page-break {
            page-break-after: always;
        }
        .footer{
            /*bottom: 0;*/
            /*position: absolute;*/
            /*display: none;*/
            float: right;
            margin: 0 5px;

        }
  

        .box{
            height: 151px;
            width: 151px;
        }
        .firma{
            /*float: left;*/
            width: 150px;
            text-align: center;
            /*margin-left: 0;*/
            display: inline-block;
        }
            .firma .box{
                border-bottom: 1px dotted #000;
            }

       

        .sello{
            /*float: left;*/
            width: 150px;
            text-align: center;
            margin-left: 100px;
            /*clear: both;*/
            display: inline-block;
        }
            .sello .box{
                border: 1px solid #000;
            }
        .paragraph{
            /*margin: 40px 0;*/
            display: block;
            padding: 20px 0 50px;
            font-size: 10px;
        }
        h4{
            margin: 40px 0 5px 0;
            font-size: 14px;
        }
        h4 small{
            font-size: 12px;
            font-weight: normal;
        }
        tbody tr td{
            border-bottom: 1px solid #cdcdcd;
            padding: 5px;
        }
        thead tr th,
        tfoot tr th{
            padding: 5px;
        }
        .page-number:before  {
          content: counter(page);
        }
        .detail tr td{
            border-bottom: none;
        }
        table tr td.subheader{
            background-color: #e4e8ea;
            text-align: left!important;
        }
        /*@font-face {
          font-family: '3of9';
          src: url(http://www.registro.beg.misiones.gov.ar/public/frontend/fonts/3of9.ttf) format('truetype');
        }

        .bar-code{
            font-family:"3of9";
            font-weight:normal;
            font-style:normal;
            font-size: 30px;
        }*/
        .header_table{
            background-color: #FFF;
        }
        table tbody tr.debe,
        table tbody tr.debe a{
            color: red;
        }        
    </style>
</head>
<body>

    <div class="reportContainer">
                        
        <div class="header">
            <div class="logo">
                <img src="{{ config('constants.logo_pdf') }}" alt="logo">
            </div>
            <div class="titulo">
                <h1>{{ config('constants.title') }} - {{ config('constants.company_razon_social') }}</h1>
                <h2>{{ config('constants.company_web') }}</h2>        
            </div>
           <!--  <div class="ntramite">
                <b>Comprobante Nro.:</b> <?php /* echo $movimiento->id; */?>
            </div> -->
            <div class="header_fecha">
                <b>Fecha:</b> <?php echo date('d/m/Y');?>  <!-- H:i:s -->
                <br><b>Tel:</b> {{config('constants.company_tel')}}
                <br>{{config('constants.company_dir')}}

            </div>        
        </div>

        <h2>Balance Detalle</h2>                        

        <?php foreach ($response as $key => $users): 

                $result = '';
                $total_importe_facturado = 0;
                $total_importe_pagado = 0;
                $total_importe_adeudado = 0;

                $user = $users[0]; 
                ?>

                <h3><?php echo $user['cliente']['nombre_apellido'] . ' <small>(Cliente Nro. ' . $user['nro_cliente'] .')</small>'; ?></h3>
                        
                <table>
                    <thead>

                        <tr>
                            <th style="width: 12%;" class="left">Periodo</th>
                            <th style="width: 18%;" class="left">Factura</th>
                            <th style="width: 10%;" class="center">Fecha de Emisión</th>
                            <th style="width: 12%;" class="right">Importe Facturado</th>
                            <th style="width: 10%;" class="center">Fecha de Pago</th>
                            <th style="width: 12%;" class="right">Importe Pagado</th>
                            <th style="width: 13%;" class="right">Medio de Pago</th>
                            <th style="width: 13%;" class="right">Importe Adeudado</th>
                        </tr>
                    </thead>

                    <tbody>
                        
                        <?php foreach ($users as $key => $factura): 

                                // Skip invalid entries (same as JS)
                                if (!isset($factura['talonario'])) continue;

                                $importe_pago_val = 0;
                                $importe_pago_display = '';
                                $importe_adeudado_display = '';

                                // Si es una Nota de Crédito, mostrarla como reducción
                                if (isset($factura['is_nota_credito']) && $factura['is_nota_credito']) {
                                    $class_tr = '';
                                    // Las notas de crédito restan (ya vienen con importe_total_numeric negativo)
                                    $total_importe_adeudado = (float)$total_importe_adeudado + (float)$factura['importe_total_numeric'];
                                    $importe_adeudado_display = $factura['importe_total_formatted'];
                                } elseif (isset($factura['is_anulada']) && $factura['is_anulada']) {
                                    // Facturas anuladas: no cuentan para totales
                                    $class_tr = '';
                                } elseif (isset($factura['importe_pago_numeric']) && $factura['importe_pago_numeric'] != '' && $factura['importe_pago_numeric'] != null && $factura['importe_pago_numeric'] != 0) {
                                    $class_tr = '';
                                    $importe_pago_display = $factura['importe_pago_formatted'];
                                    $importe_pago_val = (float)$factura['importe_pago_numeric'];
                                } else {
                                    $class_tr = 'debe';
                                    $total_importe_adeudado = (float)$total_importe_adeudado + (float)$factura['importe_total_numeric'];
                                    $importe_adeudado_display = $factura['importe_total_formatted'];
                                }

                                // Totalizar: solo facturas válidas (no anuladas)
                                if (!isset($factura['is_anulada']) || !$factura['is_anulada']) {
                                    $total_importe_facturado = (float)$total_importe_facturado + (float)$factura['importe_total_numeric'];
                                    $total_importe_pagado = (float)$total_importe_pagado + $importe_pago_val;
                                }

                                ?>                        

                                <tr class="<?php echo $class_tr; ?>">
                                    <td style="width: 12%;" class="left"><?php echo $factura['periodo']; ?> </td>
                                    <td style="width: 18%;" class="left">
                                        <?php if (isset($factura['is_nota_credito']) && $factura['is_nota_credito']): ?>
                                            <?php echo 'Nota de Crédito: ' . $factura['talonario']['letra'] . ' ' . $factura['talonario']['nro_punto_vta'] . ' - '. $factura['nro_factura']; ?>
                                        <?php else: ?>
                                            <?php 
                                                echo $factura['talonario']['letra'] . ' ' . $factura['talonario']['nro_punto_vta'] . ' - '. $factura['nro_factura'];
                                                if (isset($factura['is_anulada']) && $factura['is_anulada']) {
                                                    echo ' (ANULADA)';
                                                }
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                    <td style="width: 10%;" class="center"><?php echo $factura['fecha_emision']; ?> </td>
                                    <td style="width: 12%;" class="right"><?php echo isset($factura['importe_total_formatted']) ? $factura['importe_total_formatted'] : number_format((float)$factura['importe_total_numeric'], 2, ',', '.'); ?> </td>
                                    <td style="width: 10%;" class="center"><?php echo isset($factura['fecha_pago']) ? $factura['fecha_pago'] : ''; ?> </td>
                                    <td style="width: 12%;" class="right"><?php echo $importe_pago_display; ?> </td>
                                    <td style="width: 13%;" class="right"><?php echo isset($factura['forma_pago']) ? $factura['forma_pago'] : ''; ?> </td>
                                    <td style="width: 13%;" class="right"><?php echo $importe_adeudado_display; ?> </td>
                                </tr>

                        <?php endforeach; ?>
                    
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="2"><b>Total</b></td>
                            <th colspan="2" class="right"><?php echo number_format((float)$total_importe_facturado, 2, ',', '.'); ?></td>
                            <th colspan="2" class="right"><?php echo number_format((float)$total_importe_pagado, 2, ',', '.'); ?></td>
                            <th colspan="2" class="right"><?php echo number_format((float)$total_importe_adeudado, 2, ',', '.'); ?></td>                            
                        </tr>
                    </tfoot>       
                            
                </table>

        <?php endforeach; ?>

    </div>   

    <div class="footer">        
        <!-- <div class="firma"><div class="box"></div>Firma</div> -->
        <!-- <div class="sello"><div class="box"></div>Sello</div> -->
    </div>

</body>
</html>
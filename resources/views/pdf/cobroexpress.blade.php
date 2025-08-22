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
            width: 99.5%; 
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

        <h2>Pago de clientes mediante Cobro Express</h2>                        
        
        <?php $total_general = 0; ?>
        <?php //ksort($response); ?>
        <?php foreach ($response as $key => $registro): ?>

                <h3><?php echo 'Cliente: <small>' . $registro['cod_cliente'] . ' - '. $registro['detalle_cliente']['firstname'] .' '. $registro['detalle_cliente']['lastname'] .'</small>'; ?></h3>
                        
                <table>
                    <thead>

                        <tr>
                            <th style="width: 30%;" class="left">Fecha de pago</th>
                            <th style="width: 50%;" class="left">Factura</th>
                            <th style="width: 20%;" class="right">Importe</th>
                        </tr>
                    </thead>

                    <?php foreach ($registro['detalle_pagos'] as $key => $fechaPago): ?>
                    
                        <?php foreach ($fechaPago as $key => $pago): ?>
                        
                                <tbody>

                                    <td style="width: 20%;" class="left"><?php echo $pago['fecha']; ?></td>
                                    
                                    <?php if ($pago['nro_factura'] > 0 && $pago['nro_sucursal'] > 0): ?>
                                            <td style="width: 50%;" class="left"><?php echo $pago['nro_sucursal'] . ' - ' . $pago['nro_factura']; ?></td>
                                    <?php else: ?>
                                            <td style="width: 50%;" class="left">(SIN ESPECIFICAR)</td>
                                    <?php endif; ?>                            
                                    
                                    <td style="width: 20%;"  class="right"><?php echo number_format($pago['importe'], 2); ?></td>                           
                                 
                                </tbody>
                        
                        <?php endforeach; ?>

                    <?php endforeach; ?>

                    <tfoot>
                        <tr>
                            <th style="width: 20%;" colspan="2" class="left">Total</th>
                            <th style="width: 80%;"  class="right"><?php echo number_format($registro['total_pagos'], 2); ?></th>
                        </tr>
                    </tfoot>       
                            
                </table>

                <?php $total_general = $total_general + $registro['total_pagos']; ?>

        <?php endforeach; ?>
                        
        <br>
        <br>
        <table>
            <tfoot>
                <tr>
                    <th style="width: 20%;" class="left">Total General</th>
                    <th style="width: 80%;"  class="right"><?php echo number_format($total_general, 2); ?></th>
                </tr>
            </tfoot>       
                    
        </table>

    </div>   

    <div class="footer">        
        <!-- <div class="firma"><div class="box"></div>Firma</div> -->
        <!-- <div class="sello"><div class="box"></div>Sello</div> -->
    </div>

</body>
</html>
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

    <?php 
        
        $result = '';
        $footer_facturas_total = 0;
        $footer_facturas_pagadas = 0;
        $footer_facturas_adeudadas = 0;
        $footer_importe_facturado = 0;
        $footer_importe_pagado = 0;

    ?>


    <div class="reportContainer">

        <table>
            <thead>
                <tr>
                    <td class="header_table" colspan="6">
                        <!-- <p>Current Page: <span class="page-number"></span></p> -->
                        
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

                        <h4><?php 
                        
                        $firstKey = key($response);

                        echo 'Cliente: <small>' . $response[$firstKey]['cliente'] . '</small>'; ?></h4>
                        <h3>Balance</h3>                        
                    
                    </td>
                </tr>                        
                <tr>
                    <th style="width: 5%;" class="left">Período</th>
                    <th style="width: 5%;" class="center">Total Facturas</th>
                    <th style="width: 10%;" class="center">Facturas Pagadas</th>
                    <th style="width: 10%;" class="center">Facturas Adeudadas</th>
                    <th style="width: 10%;" class="right">Importe Facturado</th>
                    <th style="width: 10%;" class="right">Importe Pagado</th>
                </tr>
            </thead>
            
         
                <?php foreach ($response as $key => $periodo): ?>
                    
                        <?php 

                            // totales
                            $footer_facturas_total  = $footer_facturas_total + $periodo['facturas_total'];
                            $footer_facturas_pagadas  = $footer_facturas_pagadas + $periodo['facturas_pagadas'];
                            $footer_facturas_adeudadas   = $footer_facturas_adeudadas  + $periodo['facturas_adeudadas'];
                            $footer_importe_facturado  = $footer_importe_facturado  + $periodo['importe_facturado'];
                            $footer_importe_pagado = $footer_importe_pagado + $periodo['importe_pagado'];

                         ?>

                    <tbody>

                        <td style="width: 5%;" class="left"><?php echo $periodo['periodo']; ?></td>
                        <td style="width: 5%;" class="center"><?php echo $periodo['facturas_total']; ?></td>
                        <td style="width: 10%;" class="center"><?php echo $periodo['facturas_pagadas']; ?></td>
                        <td style="width: 10%;" class="center"><?php echo $periodo['facturas_adeudadas']; ?></td>
                        <td style="width: 10%;"  class="right"><?php echo number_format($periodo['importe_facturado'], 2); ?></td>
                        <td style="width: 10%;"  class="right"><?php echo number_format($periodo['importe_pagado'], 2); ?></td>                           
                     
                    </tbody>
                    
                <?php endforeach; ?>
                        
            <tfoot>
                <tr>
                    <th style="width: 5%;" class="left">Totales</th>
                    <th style="width: 5%;" class="center"><?php echo $footer_facturas_total; ?></th>
                    <th style="width: 10%;" class="center"><?php echo $footer_facturas_pagadas; ?></th>
                    <th style="width: 10%;" class="center"><?php echo $footer_facturas_adeudadas; ?></th>
                    <th style="width: 10%;"  class="right"><?php echo number_format($footer_importe_facturado, 2); ?></th>
                    <th style="width: 10%;"  class="right"><?php echo number_format($footer_importe_pagado, 2); ?></th>
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
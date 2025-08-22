<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>{{config('constants.title')}} | Facturas</title>

    <style>
        @page { margin: 5px 10px !important; }
        body{
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            margin: 5px;
            font-size: 12px;
        }
        
        .title{
                background-color: #028fcc;
                height: 90px;
        }
        .title img{
            width: 100px;
            background-color: #fff;
            border-radius: 5px;
            margin: 10px;
            display: inline-block;
        }        
        .title h1{
            margin: 0 10px;
            color: #fff;
            font-size: 40px;
            line-height: 15px;
            display: inline-block;
            /*border:1px solid orange;*/
            text-align: right;
            width: 640px;
        }
        
        .header{
            padding: 10px 10px;
            width: 100%;
            height: 190px;
            border: 1px solid #028fcc;
        }
        .header .row{
            display: block;
            clear: both;
            height: 110px;
            /*border: 2px dotted orange;*/
            /*background-color: #cdcdcd;*/
        }
        .header .row .info_empresa{
            display: inline-block;
            width: 350px;
            border: 1px solid white;
            height: auto;
            float: left;
        }
        .header .row .tipo_factura{
            display: inline-block;
            width: 50px;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 50px;
            text-align: center;
            /*border: 1px solid red;*/
            float: left;
        }
        .header .row .info_factura{
            display: inline-block;
            width: 330px;
            border: 1px solid white;
            padding-left: 30px; 
            float: left;
        }
        
        .header .row .cod_barras{
            float: left;
            width: 435px;
            height: 100px;
        }
        .header .row .cod_barras p{
            text-align: left;
            /*margin-left: 30px;*/
            font-size: 12px;

        }
        .header .row .darkness{
            float: left;
            display: inline-block;
            background-color: #cdcdcd; 
            font-weight: bold;
            width: 325px;
        }
        .header .row .darkness p{
            padding: 2px 5px;
            font-size: 15px;

        }

        .content{
            margin: 10px 0;
            /*padding: 10px;*/
        }

        p{
            font-size: 12px;
            margin: 2px 0;
            padding: 0;
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
        thead tr th{
            padding: 10px;
            border-bottom: 1px solid #cdcdcd;
            font-size: 14px;
        } 
        tfoot tr th{
            padding: 5px 10px;
            border-bottom: 1px solid #cdcdcd;
            font-size: 14px;
        }       
        tr{
            margin: 2px 0;
        }
        td{
            border: 0;
            padding: 3px 10px;
            width: 50%;
            font-size: 14px;
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
        .detalle_row{
            border-top: 1px solid #cdcdcd;
        }
        .footer{
            bottom: 0;
            position: absolute;
        }
        .footer .vencimiento{
            text-align: left;
            margin: 20px 0;
            border: 1px solid #cdcdcd;
            height: 100px;
            width: 510px;
        }
        .footer .vencimiento .vto_title{
            background-color:#eee;
            padding: 10px;
            margin: 0 0 5px 0; 
            font-weight: bold;
        }
        .footer .vencimiento .cod_barras{
            margin-top: 10px;
            text-align: center;
            float: left;
            width: 370px;
        }
        .footer .vencimiento .darkness{
            text-align: left;
            float: left;
            width: 300px;
        }
        .footer .info{
            clear: both;    
            text-align: center;
        }   
        .footer .info .mensaje{
            margin: 5px 0;
            text-align: left;
            padding: 5px 10px;
            border: 1px solid #f5a83c;
            clear: both;
            display: block;
        }     
    </style>
</head>
<body>

    <?php 
        
        /*
        $generator = new Picqer\Barcode\BarcodeGeneratorHTML();
        echo $generator->getBarcode('307085301460001705620171215000047', $generator::TYPE_CODE_128, 2, 50);
        echo '<br>';
        */

        $i = 1;
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    
    ?>

    <?php foreach ($facturas as $factura): ?>


        <div class="title">
            <img src="{{ config('constants.logo_pdf') }}" alt="Logo">
            <h1>{{ config('constants.title') }}</h1>
        </div>

        <div class="header">
            
            <div class="row">
                
                <div class="info_empresa">
                    <p>{{ config('constants.title') }} - {{ config('constants.company_razon_social') }}</p>
                    <p>{{ config('constants.company_dir') }}</p>
                    <p>CUIT: {{ config('constants.company_cuit') }}</p>
                    <p>IIBB: {{ config('constants.company_iibb') }}</p>
                    <p>{{ config('constants.company_iva') }}</p>
                </div>

                <div class="tipo_factura">
                    {{$factura->talonario->letra}}
                </div>

                <div class="info_factura">
                    <p>Recibo: {{$factura->talonario->nro_punto_vta}} - {{$factura->nro_factura}}</p>
                    <p><strong>{{ strtoupper($factura->cliente->firstname.' '.$factura->cliente->lastname) }}</strong></p>
                    <p>Nro. Cliente: {{$factura->nro_cliente}}</p>
                    <p>DNI/CUIT: {{$factura->cliente->dni}}</p>
                    <p>Domicilio: Calle {{$factura->cliente->calle.' '.$factura->cliente->altura.' - Mz.'.$factura->cliente->manzana}}</p>
                    <p>Fecha de emisión: {{$factura->fecha_emision}}</p>
                </div>
            </div>
            
            <div class="row">
                <div class="cod_barras">
                    <!-- <img src='http://barcode.tec-it.com/barcode.ashx?translate-esc=off&data={{$factura->primer_vto_codigo}}&code=Code128&unit=Fit&dpi=96&imagetype=Gif&rotation=0&color=000000&bgcolor=FFFFFF&qunit=Mm&quiet=0' alt='Barcode Generator TEC-IT'/> -->
                    <?php echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($factura->primer_vto_codigo, $generator::TYPE_CODE_128, 1, 55)) . '">'; ?>
                    <p>{{$factura->primer_vto_codigo}}</p>
                </div>
        
                <div class="darkness">
                    <p>Total a Pagar: ${{$factura->importe_total}}</p>
                    <p>Vencimiento: {{$factura->primer_vto_fecha}}</p>
                    <p>Período: {{$factura->periodo}}</p>
                </div>

            </div>
        </div> 


        <div class="content">
            
            <table>
                    <thead>
                        <tr>
                            <th style="width: 90%;" class="left">Servicio</th>
                            <th style="width: 10%;" class="right">Importe</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                 
                        <?php foreach ($factura->detalle as $detalle): ?>

                                <?php if ($detalle->pp_flag == 1 ) : ?>
                                
                                    <?php if ($detalle->instalacion_cuota != null && $detalle->instalacion_cuota <= $detalle->instalacion_plan_pago): ?>
                                            <tr>
                                                <td class="detalle_row" colspan="2">                                            
                                                    <tr>
                                                        <td class="left">Convenio de Pago - Servicio {{$detalle->servicio->nombre}}  (Cuota {{$detalle->instalacion_cuota.'/'.$detalle->instalacion_plan_pago}})</td>   
                                                        <td class="right">{{number_format($detalle->costo_instalacion, 2)}}</td>  
                                                    </tr>
                                                </td> 
                                            </tr>                                            
                                    <?php endif; ?>  

                                <?php else: ?>

                                    <tr>
                                        <td class="detalle_row" colspan="2">
                                            
                                            <?php if(!is_null($detalle->abono_proporcional)){

                                                        $label_dia = $detalle->dias_proporcional == 1 ? ' día' : ' días';

                                                        $fila_detalle = $detalle->servicio->nombre . ' (Abono proporcional correspondiente a '. $detalle->dias_proporcional . $label_dia .' de servicio)';
                                                        $fila_importe = $detalle->abono_proporcional;
                                                  }else{
                                                        $fila_detalle = $detalle->servicio->nombre;
                                                        $fila_importe = $detalle->abono_mensual;
                                                  }
                                            ?>

                                            <tr>
                                                <td class="left">{{$fila_detalle}}</td>   
                                                <td class="right">{{number_format($fila_importe, 2)}}</td>   
                                            </tr>

                                            <?php if ($detalle->instalacion_cuota != null && $detalle->instalacion_cuota <= $detalle->instalacion_plan_pago): ?>
                                                    <tr>
                                                        <td class="left"> *Costo de Instalación (Cuota {{$detalle->instalacion_cuota.'/'.$detalle->instalacion_plan_pago}})</td>   
                                                        <td class="right">{{number_format($detalle->costo_instalacion, 2)}}</td>  
                                                    </tr>
                                            <?php endif; ?>  

                                        </td> 
                                    </tr>
                            
                            <?php endif; ?>
                                 
                        <?php endforeach; ?>

                    </tbody>
                    
                    <tfoot>
                        <tr>
                            <th style="width: 90%;" class="left">Subtotal</th>
                            <th style="width: 10%;" class="right">{{$factura->importe_subtotal}}</th>
                        </tr>
                        <tr>
                            <th style="width: 90%;" class="left">Bonificación</th>
                            <th style="width: 10%;" class="right">{{$factura->importe_bonificacion}}</th>
                        </tr>
                        <tr>
                            <th style="width: 90%;" class="left">Total</th>
                            <th style="width: 10%;" class="right">${{$factura->importe_total}}</th>
                        </tr>
                    </tfoot>

                </table>

        </div>

    
        <div class="footer">

            <div class="vencimiento">
                <p class="vto_title">Segundo Vencimiento</p>
                
                <div class="cod_barras">
                    <?php echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($factura->segundo_vto_codigo, $generator::TYPE_CODE_128, 1, 40)) . '">'; ?>
                    <p>{{$factura->segundo_vto_codigo}}</p>
                </div>
        
                <div class="darkness">
                    <p>Importe: ${{$factura->segundo_vto_importe}}</p>
                    <p>Vencimiento: {{$factura->segundo_vto_fecha}}</p>
                </div>
            </div>

            <?php if($factura->tercer_vto_codigo): ?>
                <div class="vencimiento">
                    <p class="vto_title">Tercer Vencimiento</p>
                    
                    <div class="cod_barras">
                        <?php echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($factura->tercer_vto_codigo, $generator::TYPE_CODE_128, 1, 40)) . '">'; ?>
                        <p>{{$factura->tercer_vto_codigo}}</p>
                    </div>
            
                    <div class="darkness">
                        <p>Importe: ${{$factura->tercer_vto_importe}}</p>
                        <p>Vencimiento: {{$factura->tercer_vto_fecha}}</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="info">
                <div class="mensaje">
                    <h4>Sr. Cliente, a partir de la próxima factura se modifican los días de vencimiento de la siguiente manera:</h4>
                    <ul>
                        <li>1er vencimiento, día 01</li>
                        <li>2do vencimiento, día 10</li>
                    </ul>
                </div>
                
                <h4>Descargue y controle su recibo online</h4>
                <p>{{ config('constants.company_web') }}</p>        
                <p>Tel.: {{ config('constants.company_tel') }}</p>
                <p>{{ config('constants.account_info') }}</p>
                <p><strong>*Condiciones de servicios exclusivos de CobroExpress IPLYC - SE. y Pago Mis Cuentas</strong></p>        
            </div>
        </div>

        
        <!-- Salto de Pagina -->
        <?php if ($i++ < count($facturas)): ?>
        
                <div style="page-break-after:always;"></div>
        
        <?php endif; ?>

        
    <?php endforeach; ?>

</body>
</html>
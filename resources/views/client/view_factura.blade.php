@include('layout.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Facturas</h3>
      </div>
    </div>

	<div class="clearfix"></div>

    @if (!empty($factura))
      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>Factura {{$factura->talonario->nro_punto_vta}} - {{$factura->nro_factura}} </h2> 
            <span class="nav navbar-right">
              <a href="/my-invoice" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"></i> Volver</a>
              <!-- <a href="/admin/period/create" class="btn btn-info btn-xs"><i class="fa fa-plus-square-o"></i> Nuevo</a> -->
            
            </span>              
              <div class="clearfix"></div>
            </div>
                    
            @if (session('status'))

                <div class="panel panel-{{session('status')}}">
                    <div class="panel-heading">
                        <i class="fa {{session('icon')}}"></i> {{session('message')}}
                    </div>     
                </div>   

            @endif




            <div class="x_content">
                <div class="col-md-12 col-lg-12 col-sm-12">
                  <div class="col-md-12 col-lg-12 col-sm-12">
                    <?php echo $factura->mail_to ? '<h3><span class="label label-success pull-left">Recibido en: ' . $factura->mail_to . '</span></h3><br>' : ''; ?> 
                    <h3> <?php echo $factura->fecha_pago ? '<span class="label label-success pull-left">Pagada: ' . $factura->fecha_pago . '</span>' : '<span class="label label-danger pull-left">Pendiente</span>'; ?> </h3>
                    
                    @php
                        $pagoInformadoPendiente = $factura->pagosInformados()->where('estado', 'pendiente')->first();
                    @endphp
                    
                    @if($pagoInformadoPendiente)
                      <div class="alert alert-warning" role="alert">
                        <i class="fa fa-clock-o"></i> <strong>Pago Pendiente de Validación:</strong><br>
                        Usted informó un pago por <strong>${{number_format($pagoInformadoPendiente->importe_informado, 2, ',', '.')}}</strong> 
                        realizado el {{$pagoInformadoPendiente->fecha_pago_informado_formatted}}.<br>
                        <small><em>Su factura permanecerá pendiente hasta que nuestro equipo valide la información. Recibirá una notificación del resultado.</em></small>
                      </div>
                    @endif
                    @if(!$factura->fecha_pago && !$pagoInformadoPendiente)
                      <br>
                      <div class="btn-group" role="group">
                        <a href="/my-invoice/pay/{{$factura->id}}" class="btn btn-success btn-lg">
                          <i class="fa fa-credit-card"></i> Pagar con MercadoPago
                        </a>
                        <a href="/my-invoice/inform-payment/{{$factura->id}}" class="btn btn-info btn-lg">
                          <i class="fa fa-bank"></i> Informar Pago por CBU/Transferencia
                        </a>
                      </div>
                    @elseif(!$factura->fecha_pago && $pagoInformadoPendiente)
                      <br>
                      <div class="alert alert-info text-center">
                        <i class="fa fa-info-circle"></i> 
                        <strong>No puede realizar otro pago mientras hay uno pendiente de validación.</strong>
                      </div>
                    @endif
                  </div>
                </div>

                <br>
                <br>
              
              <div class="col-md-12 col-lg-12 col-sm-12">
              
                <div class="col-md-6 col-lg-6 col-sm-12">
                  <h5>Nombre y Apellido</h5>
                  <h4>{{ strtoupper($factura->cliente->firstname.' '.$factura->cliente->lastname) }}</h4>
                  <br>
                  <h5>Domicilio</h5>
                  <h4>Calle {{$factura->cliente->calle.' '.$factura->cliente->altura.' - Mz.'.$factura->cliente->manzana}}</h4>
                </div>
              
                <div class="col-md-3 col-lg-3 col-sm-12">
                  <h5>Nro. Cliente </h5>
                  <h4>{{$factura->nro_cliente}}</h4>
                  <br>
                  <h5>Fecha de emisión</h5>
                  <h4>{{$factura->fecha_emision}}</h4>
                  <br>
                </div>
                
                <div class="col-md-3 col-lg-3 col-sm-12">
                  <h5>DNI/CUIT</h5>
                  <h4>{{$factura->cliente->dni}}</h4>
                  <br>
                  <h5>Período</h5>
                  <h4>{{$factura->periodo}}</h4>
                </div>
              </div>

              <div class="clearfix"></div>
              <div class="ln_solid"></div>

               <div class="col-md-12 col-lg-12 col-sm-12">
              
                <div class="col-md-3 col-lg-3 col-sm-12">
                  <h5>Primer Vencimiento </h5>
                  <h4>{{ $factura->primer_vto_fecha instanceof \Carbon\Carbon ? $factura->primer_vto_fecha->format('d/m/Y') : $factura->primer_vto_fecha }}</h4>
                  
                  <br>
                  <h5>Segundo Vencimiento </h5>
                  <h4>{{ $factura->segundo_vto_fecha instanceof \Carbon\Carbon ? $factura->segundo_vto_fecha->format('d/m/Y') : $factura->segundo_vto_fecha }}</h4>

                  @if($factura->tercer_vto_importe > 0)
                    <br>
                    <h5>Tercer Vencimiento </h5>
                    <h4>{{ $factura->tercer_vto_fecha instanceof \Carbon\Carbon ? $factura->tercer_vto_fecha->format('d/m/Y') : $factura->tercer_vto_fecha }}</h4>
                  @endif

                </div>
              
                <div class="col-md-6 col-lg-6 col-sm-12">
                  <h5>Importe</h5>
                  <h4>{{$factura->importe_total}}</h4>
                  
                  <br>
                  <h5>Importe <small>(Interés {{$factura->segundo_vto_tasa}}%)</small></h5>
                  <h4>{{$factura->segundo_vto_importe}}</h4>
                
                  @if($factura->tercer_vto_importe > 0)
                    <br>
                    <h5>Importe <small>(Interés {{$factura->tercer_vto_tasa}}% + Interés Segundo Vencimiento)</small></h5>
                    <h4>{{$factura->tercer_vto_importe}}</h4>
                  @endif


                </div>


              </div>


            </div>
          </div>


        </div>
      </div>

      <div class="clearfix"></div>

      @if(isset($notasCredito) && $notasCredito->count() > 0)
      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>Historial de Bonificaciones</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Nota de Crédito</th>
                    <th>Detalle</th>
                    <th class="text-right">Importe</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($notasCredito as $nota)
                  <tr>
                    <td>{{ \Carbon\Carbon::parse($nota->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $nota->talonario->letra }} {{ $nota->talonario->nro_punto_vta }}-{{ str_pad($nota->nro_nota_credito, 8, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $nota->motivo }}</td>
                    <td class="text-right">${{ number_format($nota->importe_bonificacion, 2, ',', '.') }}</td>
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="3" class="text-right"><strong>Total Bonificaciones:</strong></th>
                    <th class="text-right"><strong>${{ number_format($notasCredito->sum('importe_bonificacion'), 2, ',', '.') }}</strong></th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
      @endif

      @if(isset($notasDebito) && $notasDebito->count() > 0)
      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>Historial de Ajustes por Intereses</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Nota de Débito</th>
                    <th>Detalle</th>
                    <th class="text-right">Importe</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($notasDebito as $nota)
                  <tr>
                    <td>{{ \Carbon\Carbon::parse($nota->fecha_emision)->format('d/m/Y') }}</td>
                    <td>{{ $nota->talonario->letra }} {{ $nota->talonario->nro_punto_vta }}-{{ str_pad($nota->nro_nota_debito, 8, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $nota->motivo }}</td>
                    <td class="text-right">${{ number_format($nota->importe_ampliacion, 2, ',', '.') }}</td>
                  </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="3" class="text-right"><strong>Total Ajustes:</strong></th>
                    <th class="text-right"><strong>${{ number_format($notasDebito->sum('importe_ampliacion'), 2, ',', '.') }}</strong></th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
      @endif

      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">




          <div class="x_panel">
            <!-- <div class="x_title">
              <h2>Detalle</h2>
              <div class="clearfix"></div>
            </div> -->
            <div class="x_content">
                     
          <table id="dataTableFact" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th>Servicio</th>
                <th class="right">Importe</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($factura->detalle as $detalle): ?>

                      <?php if($detalle->abono_proporcional != ''){
                                  $fila_detalle = $detalle->servicio->nombre . ' (Abono proporcional)';
                                  $fila_importe = $detalle->abono_proporcional;
                            }else{
                                  $fila_detalle = $detalle->servicio->nombre;
                                  $fila_importe = $detalle->abono_mensual;
                            }
                      ?>

                      <tr>
                          <td scope="row">{{$fila_detalle}}</td>   
                          <td class="right">{{number_format($fila_importe, 2)}}</td>   
                      </tr>

                      <?php if ($detalle->instalacion_cuota != null && $detalle->instalacion_cuota <= $detalle->instalacion_plan_pago): ?>
                              <tr>
                                  <td scope="row"> *Costo de Instalación (Cuota {{$detalle->instalacion_cuota.'/'.$detalle->instalacion_plan_pago}})</td>   
                                  <td class="right">{{number_format($detalle->costo_instalacion, 2)}}</td>  
                              </tr>
                      <?php endif; ?> 

              <?php endforeach; ?>
            </tbody>

            <tfoot>
                <tr>
                    <th scope="row">Subtotal</th>
                    <th class="right">{{$factura->importe_subtotal}}</th>
                </tr>
                <tr>
                    <th scope="row">Bonificación</th>
                    <th class="right">{{$factura->importe_bonificacion}}</th>
                </tr>
                <tr>
                    <th scope="row">Total</th>
                    <th class="right">${{$factura->importe_total}}</th>
                </tr>
            </tfoot>

          </table>


            </div>
          </div>



 



        </div>
      </div>

    @else

      <div class="panel panel-danger">
          <div class="panel-heading">
              <i class="fa fa-frown-o"></i> An error occurred.
          </div>     
      </div> 

    @endif  
  </div>
</div>
<!-- /page content -->

@include('layout.footer')
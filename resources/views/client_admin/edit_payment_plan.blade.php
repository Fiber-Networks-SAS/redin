@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Plan de Pagos</h3>
      </div>
    </div>

  <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Modificar</h2>
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
            <br />

            @if (!empty($servicio))
            
              <form action="/admin/clients/payment_plan/edit/{{$servicio->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

                {{ csrf_field() }}

                <input type="hidden" id="current_prices" value="{{$servicio->servicio->id}}">
                <input type="hidden" id="form_edit_service" value="{{$servicio->servicio->id}}">                
                
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nro_cliente">Nro. Cliente <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="nro_cliente" class="form-control col-md-7 col-xs-12 @if ($errors->has('nro_cliente')) parsley-error @endif" value="{{ old('nro_cliente') ? old('nro_cliente') : $user->nro_cliente }}"  required readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Nombre del Cliente <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12 container-name">
                    <input type="text" id="autocomplete-client-name" name="name" class="form-control col-md-7 col-xs-12 @if ($errors->has('name')) parsley-error @endif" value="{{ old('name') ? old('name') : $servicio->usuario->firstname . ' '. $servicio->usuario->lastname }}"  required readonly>
                    <input type="hidden" id="user_id" name="user_id" value="{{ old('user_id') ? old('user_id') : $servicio->user_id }}">
                    @if ($errors->has('name')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('name') }}</li></ul> @endif
                    @if ($errors->has('user_id')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('user_id') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="service_name">Nombre del Servicio <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="autocomplete-service-name" name="service_name" class="form-control col-md-7 col-xs-12 @if ($errors->has('service_name')) parsley-error @endif" value="{{ old('service_name') ? old('service_name') : $servicio->nombre }}" required autofocus>
                    <input type="hidden" id="servicio_id" name="servicio_id" value="{{ old('servicio_id') ? old('servicio_id') : $servicio->servicio->id }}">
                    @if ($errors->has('service_name')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('service_name') }}</li></ul> @endif
                    @if ($errors->has('servicio_id')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('servicio_id') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="abono_mensual">Abono Mensual <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="abono_mensual" name="abono_mensual" class="form-control col-md-7 col-xs-12 @if ($errors->has('abono_mensual')) parsley-error @endif" value="{{ old('abono_mensual') ? old('abono_mensual') : str_replace(',', '', $servicio->pp_abono_mensual_inicial) }}" required>
                    @if ($errors->has('abono_mensual')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('abono_mensual') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="pp_cuotas_adeudadas">Cuotas adeudadas <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="pp_cuotas_adeudadas" name="pp_cuotas_adeudadas" class="form-control col-md-7 col-xs-12 @if ($errors->has('pp_cuotas_adeudadas')) parsley-error @endif" value="{{ old('pp_cuotas_adeudadas') ? old('pp_cuotas_adeudadas') : $servicio->pp_cuotas_adeudadas }}" required>
                    @if ($errors->has('pp_cuotas_adeudadas')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('pp_cuotas_adeudadas') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="deuda_instalacion">Instalación adeudado<span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                      <select class="form-control" id="deuda_instalacion" name="deuda_instalacion">
                        @for ($i = 0; $i <= 100; $i = $i + 25)
                          <option value="{{$i}}" {{ old('deuda_instalacion') == $i || $servicio->pp_instalacion_adeudado == $i ? 'selected' : '' }}>{{$i}}%</option>
                        @endfor
                      </select>

                    @if ($errors->has('deuda_instalacion')) <p class="help-block">{{ $errors->first('deuda_instalacion') }}</p> @endif

                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="total_deuda">Total Adeudado<span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="hidden" id="hidden_total_deuda" name="hidden_total_deuda" value="{{ old('hidden_total_deuda') ? old('hidden_total_deuda') : $servicio->pp_importe_total_adeudado  }}">
                    <input type="text" id="total_deuda" name="total_deuda" class="form-control col-md-7 col-xs-12 @if ($errors->has('total_deuda')) parsley-error @endif" value="{{ old('total_deuda') ? old('total_deuda') : number_format($servicio->pp_importe_total_adeudado, 2, ',' , '.') }}" required readonly>
                    @if ($errors->has('total_deuda')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('total_deuda') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="pp_plan_pago">Plan de Pago <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                      <select class="form-control" id="pp_plan_pago" name="pp_plan_pago">

                        @if ($pagosConfig && $pagosConfig->max_cuotas)
                          @for ($i = 1; $i <= $pagosConfig->max_cuotas; $i++)

                            <?php 
                              $label_text = $i == 1 ? $i . ' Cuota' : $i . ' Cuotas';
                            ?>
                            
                            <option value="{{$i}}" {{ old('pp_plan_pago') == $i || $servicio->plan_pago == $i ? 'selected' : '' }}>{{$label_text}}</option>

                          @endfor
                        @else
                          <option value="">No hay configuración de pagos disponible</option>
                        @endif

                      </select>

                    @if ($errors->has('pp_plan_pago')) <p class="help-block">{{ $errors->first('pp_plan_pago') }}</p> @endif

                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="abono_mensual_pagar">Importe Mensual a pagar <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="input-group">
                      <input type="hidden" id="pp_tasa" name="pp_tasa" value="{{$pagosConfig->tasa }}">
                      <input type="text" id="abono_mensual_pagar" name="abono_mensual_pagar" class="form-control col-md-7 col-xs-12 @if ($errors->has('abono_mensual_pagar')) parsley-error @endif" value="{{ old('abono_mensual_pagar') ? old('abono_mensual_pagar') : $servicio->abono_mensual}}" requireds readonly>
                      <span class="input-group-addon" id="current-price" title="Tasa Efectiva Mensual">TEM.: {{$pagosConfig->tasa }}%</span>
                    </div>                  
                    @if ($errors->has('abono_mensual_pagar')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('abono_mensual_pagar') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="comentario">Comentarios</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea id="comentario" name="comentario" class="form-control @if ($errors->has('comentario')) parsley-error @endif" rows="3">{{ old('comentario') ? old('comentario') : $servicio->comentario }}</textarea>
                    @if ($errors->has('comentario')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('comentario') }}</li></ul> @endif
                  </div>                
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/clients/payment_plan/{{$user->id}}" class="btn btn-primary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar</button>
                  </div>
                </div>

              </form>

            @else

              <div class="panel panel-danger">
                  <div class="panel-heading">
                      <i class="fa fa-frown-o"></i> An error occurred.
                  </div>     
              </div> 

            @endif

          </div>
        </div>


      </div>
    </div>

  </div>
</div>
<!-- /page content -->

@include('layout_admin.footer')
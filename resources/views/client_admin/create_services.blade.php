@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Asignación de Servicios</h3>
      </div>
    </div>

	<div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Nuevo</h2>
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

              <form action="/admin/clients/services/create" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}
                
                <input type="hidden" id="mode" name="mode" value="create">

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Cliente (DNI/CUIT) <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12 container-name">
                    <input type="text" id="autocomplete-client-name" name="name" class="form-control col-md-7 col-xs-12 @if ($errors->has('name')) parsley-error @endif" value="{{ old('name') ? old('name') : '' }}"  required autofocus placeholder="Ingrese DNI, CUIT o nombre...">
                    <input type="hidden" id="user_id" name="user_id" value="{{ old('user_id') ? old('user_id') : '' }}">
                    @if ($errors->has('name')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('name') }}</li></ul> @endif
                    @if ($errors->has('user_id')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('user_id') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="service_name">Nombre del Servicio <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="autocomplete-service-name" name="service_name" class="form-control col-md-7 col-xs-12 @if ($errors->has('service_name')) parsley-error @endif" value="{{ old('service_name') ? old('service_name') : '' }}" required>
                    <input type="hidden" id="servicio_id" name="servicio_id" value="{{ old('servicio_id') ? old('servicio_id') : '' }}">
                    @if ($errors->has('service_name')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('service_name') }}</li></ul> @endif
                    @if ($errors->has('servicio_id')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('servicio_id') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="contrato_nro">Nro. de Contrato <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="contrato_nro" name="contrato_nro" class="form-control col-md-7 col-xs-12 @if ($errors->has('contrato_nro')) parsley-error @endif" value="{{ old('contrato_nro') ? old('contrato_nro') : '' }}" required>
                    @if ($errors->has('contrato_nro')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('contrato_nro') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="contrato_fecha">Fecha del Contrato <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="contrato_fecha" name="contrato_fecha" class="form-control col-md-7 col-xs-12 @if ($errors->has('contrato_fecha')) parsley-error @endif" value="{{ old('contrato_fecha') ? old('contrato_fecha') : '' }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'" required>
                    @if ($errors->has('contrato_fecha')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('contrato_fecha') }}</li></ul> @endif
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="alta_servicio">Fecha de Alta</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="alta_servicio" name="alta_servicio" class="form-control col-md-7 col-xs-12 @if ($errors->has('alta_servicio')) parsley-error @endif" value="{{ old('alta_servicio') ? old('alta_servicio') : '' }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'">
                    @if ($errors->has('alta_servicio')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('alta_servicio') }}</li></ul> @endif
                  </div>
                </div>

                <!-- <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="mes_alta">Mes Alta</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                      <select class="form-control" name="mes_alta" required>
                        <option value="0" {{ old('mes_alta') == 0 ? 'selected' : '' }}>-- En proceso --</option>
                        
                        @for ($i = 1; $i <= 12; $i++)
                          <option value="{{$i}}" {{ old('mes_alta') == $i ? 'selected' : '' }}>{{$i < 10 ? "0" . $i : $i}}</option>
                        @endfor
                      </select>
                    @if ($errors->has('mes_alta')) <p class="help-block">{{ $errors->first('mes_alta') }}</p> @endif

                  </div>
                </div> -->


                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="abono_mensual">Abono Mensual <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="abono_mensual" name="abono_mensual" class="form-control col-md-7 col-xs-12 @if ($errors->has('abono_mensual')) parsley-error @endif" value="{{ old('abono_mensual') ? old('abono_mensual') : '' }}" readonly required>
                    @if ($errors->has('abono_mensual')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('abono_mensual') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="abono_proporcional">Abono Proporcional</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="input-group">
                      <input type="text" id="abono_proporcional" name="abono_proporcional" class="form-control col-md-7 col-xs-12 @if ($errors->has('abono_proporcional')) parsley-error @endif" value="{{ old('abono_proporcional') ? old('abono_proporcional') : '' }}" readonly>
                      <span class="input-group-addon" id="abono_proporcional_sugerido">Importe Sugerido</span>
                    </div>
                    @if ($errors->has('abono_proporcional')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('abono_proporcional') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="costo_instalacion">Costo de Instalación <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="costo_instalacion" name="costo_instalacion" costo_instalacion_base="" class="form-control col-md-7 col-xs-12 @if ($errors->has('costo_instalacion')) parsley-error @endif" value="{{ old('costo_instalacion') ? old('costo_instalacion') : '' }}" readonly required>
                    @if ($errors->has('costo_instalacion')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('costo_instalacion') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="plan_pago">Plan de Pago</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                      <select class="form-control" name="plan_pago">
                        
                        @foreach ($cuotas as $cuota)

                          <?php 
                            $label_text = $cuota->numero == 1 ? 'Cuota' : 'Cuotas';
                            $label_tasa = $cuota->interes > 0 ? ' ('.$cuota->interes.'% Interés)' : '';
                            $label_opt  = $cuota->numero . ' ' .$label_text . $label_tasa;
                          ?>
                          
                          <option value="{{$cuota->id}}" tasa="{{$cuota->interes}}" {{ old('plan_pago') == $cuota->id ? 'selected' : '' }}>{{$label_opt}}</option>

                        @endforeach

                      </select>
                      
                    @if ($errors->has('plan_pago')) <p class="help-block">{{ $errors->first('plan_pago') }}</p> @endif

                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="comentario">Comentarios</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea id="comentario" name="comentario" class="form-control @if ($errors->has('comentario')) parsley-error @endif" rows="3">{{ old('comentario') ? old('comentario') : '' }}</textarea>
                    @if ($errors->has('comentario')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('comentario') }}</li></ul> @endif
                  </div>                
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/services" class="btn btn-primary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar</button>
                  </div>
                </div>

              </form>

          </div>
        </div>


      </div>
    </div>

    <!-- <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Servicios Contratados</h2>
            
            <!- - <ul class="nav navbar-right panel_toolbox">
              <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
            </ul> - ->

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
            
            <table id="dataTableClientServicesAdd" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Contrato</th>
                  <th>Nombre</th>
                  <th>Tipo</th>
                  <th>Abono Mensual</th>
                  <th>Costo Instalación</th>
                  <th>Plan de Pago</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>

              <tbody>
                
              </tbody>
            </table>


          </div>
        </div>
      </div>
    </div> -->

  </div>
</div>
<!-- /page content -->

@include('layout_admin.footer')
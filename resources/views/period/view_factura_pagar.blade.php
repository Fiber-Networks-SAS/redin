@include('layout_admin.header')

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
              <h2>Imputar Pago de Factura {{$factura->talonario->nro_punto_vta}} - {{$factura->nro_factura}} |  <small>{{$factura->cliente->firstname.' '.$factura->cliente->lastname}}</small> </h2> 
            <span class="nav navbar-right">
              <a href="{{ url()->previous() }}" class="btn btn-primary btn-xs"><i class="fa fa-arrow-left"></i> Volver</a>
              <!-- <a href="/admin/period/view/{{$factura->periodo}}" class="btn btn-primary btn-xs"><i class="fa fa-arrow-left"></i> Volver</a> -->
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
              
                <div class="col-md-3 col-lg-3 col-sm-12">
                  <h5>Primer Vencimiento </h5>
                  <h4>{{$factura->primer_vto_fecha}}</h4>
                  
                  <br>
                  <h5>Segundo Vencimiento </h5>
                  <h4>{{$factura->segundo_vto_fecha}}</h4>

                  @if($factura->tercer_vto_importe > 0)
                    <br>
                    <h5>Tercer Vencimiento </h5>
                    <h4>{{$factura->tercer_vto_fecha}}</h4>
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


              <div class="clearfix"></div>
              <div class="ln_solid"></div>


              <form action="/admin/period/bill-pay/{{$factura->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

                {{ csrf_field() }}

                <input type="hidden" name="previousUrl" value="{{ url()->previous() }}">

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="fecha_pago">Fecha de Pago <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="fecha_pago" name="fecha_pago" class="form-control col-md-7 col-xs-12 @if ($errors->has('fecha_pago')) parsley-error @endif" value="{{ old('fecha_pago') ? old('fecha_pago') : date('d/m/Y') }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'" required autofocus>
                    @if ($errors->has('fecha_pago')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('fecha_pago') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="importe_pago">Importe <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="importe_pago" name="importe_pago" class="form-control col-md-7 col-xs-12 @if ($errors->has('importe_pago')) parsley-error @endif" value="{{ old('importe_pago') ? old('importe_pago') : '' }}" required>
                    @if ($errors->has('importe_pago')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('importe_pago') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="forma_pago">Medio de Pago <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                    @if(count($forma_pago))
                        <select class="form-control" name="forma_pago" required>
                            @foreach($forma_pago as $key => $value)
                                <option value="{{$key}}" {{ old('forma_pago') == $key ? 'selected' : '' }}>{{$value}}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="alert alert-danger">
                            No existen Medios de Pagos.
                        </div>                                            
                    @endif
                    @if ($errors->has('forma_pago')) <p class="help-block">{{ $errors->first('forma_pago') }}</p> @endif

                  </div>
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="{{ url()->previous() }}" class="btn btn-primary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar</button>
                  </div>
                </div>

              </form>


            </div>
          </div>


        </div>
      </div>

      <div class="clearfix"></div>


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

@include('layout_admin.footer')
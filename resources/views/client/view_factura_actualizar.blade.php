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
              <h2>Actualizar Factura {{$factura->talonario->nro_punto_vta}} - {{$factura->nro_factura}}</h2> 
            <span class="nav navbar-right">
              <a href="/my-invoice" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"></i> Volver</a>
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

              <form action="/my-invoice/update/{{$factura->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

                {{ csrf_field() }}

                <input type="hidden" name="previousUrl" value="{{ url()->previous() }}">

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tercer_vto_fecha">Fecha de Pago <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="tercer_vto_fecha" name="tercer_vto_fecha" class="form-control col-md-7 col-xs-12 @if ($errors->has('tercer_vto_fecha')) parsley-error @endif" value="{{ old('tercer_vto_fecha') ? old('tercer_vto_fecha') : date('d/m/Y') }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'" required autofocus>
                    @if ($errors->has('tercer_vto_fecha')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('tercer_vto_fecha') }}</li></ul> @endif
                  </div>
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/my-invoice" class="btn btn-primary">Cancelar</a>
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

@include('layout.footer')
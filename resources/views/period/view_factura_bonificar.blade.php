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
              <h2>Bonificar Factura {{$factura->talonario->nro_punto_vta}} - {{$factura->nro_factura}} |  <small>{{$factura->cliente->firstname.' '.$factura->cliente->lastname}}</small> </h2> 
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

              <form action="/admin/period/bill-improve/{{$factura->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

                {{ csrf_field() }}

                <input type="hidden" name="previousUrl" value="{{ url()->previous() }}">

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="importe_bonificacion">Subtotal</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                  <h4>{{$factura->importe_subtotal}}</h4>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="importe_bonificacion">Importe a Bonificar<span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="importe_bonificacion" name="importe_bonificacion" class="form-control col-md-7 col-xs-12 @if ($errors->has('importe_bonificacion')) parsley-error @endif" value="{{ old('importe_bonificacion') ? old('importe_bonificacion') : '' }}" required autofocus>
                    @if ($errors->has('importe_bonificacion')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('importe_bonificacion') }}</li></ul> @endif
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
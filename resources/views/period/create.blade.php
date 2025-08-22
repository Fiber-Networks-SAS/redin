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

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Nuevo Período</h2>

            <span class="nav navbar-right">

              <a href="/admin/period" class="btn btn-primary btn-xs"><i class="fa fa-arrow-left"></i> Volver</a>
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
            <br />

            @if (count($interes))

                @if ($clients)
                    
                    @if (count($talonarios))

                        @if (count($servicios))
                          
                          <div class="alert alert-warning alert-dismissible fade in" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                            </button>
                            <strong>Atención!</strong> Esta acción puede demorar varios minutos dependiendo de la cantidad de facturas que se generen.
                          </div>
                          
                          <form action="/admin/period/create" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

                          	{{ csrf_field() }}

                            <div class="form-group">
                              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="fecha_emision">Fecha de Emisión <span class="required">*</span></label>
                              <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" id="fecha_emision" name="fecha_emision" class="form-control col-md-7 col-xs-12 @if ($errors->has('fecha_emision')) parsley-error @endif" value="{{ old('fecha_emision') ? old('fecha_emision') : date('d/m/Y') }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'" autofocus required>
                                @if ($errors->has('fecha_emision')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('fecha_emision') }}</li></ul> @endif
                              </div>
                            </div>

                            <div class="form-group">
                              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="periodo">Período <span class="required">*</span></label>
                              <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" id="periodo" name="periodo" class="form-control col-md-7 col-xs-12 @if ($errors->has('periodo')) parsley-error @endif" value="{{ old('periodo') ? old('periodo') : $periodo_siguiente }}"  placeholder="mm/aaaa" data-inputmask="'mask': '99/9999'" required>
                                @if ($errors->has('periodo')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('periodo') }}</li></ul> @endif
                              </div>
                            </div>

                            <div class="ln_solid"></div>
                            <div class="form-group">
                              <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                                <a href="/admin/period" class="btn btn-primary">Cancelar</a>
                                <button type="submit" class="btn btn-success">Guardar</button>
                              </div>
                            </div>

                          </form>

                        @else

                          <div class="panel panel-danger">
                              <div class="panel-heading">
                                  <i class="fa fa-frown-o"></i> Aún no se han asignado Servicios a los clientes.
                              </div>     
                          </div> 

                        @endif 

                    @else

                      <div class="panel panel-danger">
                          <div class="panel-heading">
                              <i class="fa fa-frown-o"></i> Aún no se han configurado los Talonarios.
                          </div>     
                      </div> 

                    @endif 

                @else

                  <div class="panel panel-danger">
                      <div class="panel-heading">
                          <i class="fa fa-frown-o"></i> No existen Clientes activos.
                      </div>     
                  </div> 

                @endif 

            @else

              <div class="panel panel-danger">
                  <div class="panel-heading">
                      <i class="fa fa-frown-o"></i> Aún no se han configurado los Intereses.
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
@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Factura Individual</h3>
      </div>
    </div>

	<div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Nueva</h2>
            <div class="clearfix"></div>
          </div>
                  
		  @if (session('status'))

              <div class="panel panel-{{session('status')}}">
                  <div class="panel-heading">
                      <i class="fa {{session('icon')}}"></i> {{session('message')}} @if (session('filename')) Puede descargar el PDF <b><a href="{{session('filename')}}" target="_blank">Aquí</a></b> @endif
                  </div>     
              </div>   

          @endif

          <div class="x_content">
            <br />

              <form action="/admin/bills/single" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}
                
                <div class="alert alert-warning alert-dismissible fade in" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                  </button>
                  <strong>Atención!</strong> Esta factura será generada al ultimo período facturado y será asignada al cliente seleccionado.
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="role">Período</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                    @if(is_array($periodo) || $periodo instanceof Countable)
                        <select name="periodo" id="periodo" class="form-control col-md-7 col-xs-12 @if ($errors->has('periodo')) parsley-error @endif" required>
                            @foreach($periodo as $p)
                                <option value="{{ $p }}" @if (old('periodo') == $p) selected @endif>{{ $p }}</option>
                            @endforeach
                        </select>
                    {{-- <input type="text" id="periodo" name="periodo" class="form-control col-md-7 col-xs-12 @if ($errors->has('periodo')) parsley-error @endif" value="{{ old('periodo') ? old('periodo') : $periodo }}"  required readonly> --}}
                    @else
                        <div class="alert alert-danger">
                            No existen Periodos Facturados. Los puede crear desde <a href="/admin/period/create" class="alert-link">Aqu&iacute;</a>.
                        </div>                                            
                    @endif
                    @if ($errors->has('periodo')) <p class="help-block">{{ $errors->first('periodo') }}</p> @endif

                  </div>                  
                </div>
                
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="fecha_emision">Fecha de Emisión <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="fecha_emision" name="fecha_emision" class="form-control col-md-7 col-xs-12 @if ($errors->has('fecha_emision')) parsley-error @endif" value="{{ old('fecha_emision') ? old('fecha_emision') : date('d/m/Y') }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'" autofocus required>
                    @if ($errors->has('fecha_emision')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('fecha_emision') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Cliente</label>
                  <div class="col-md-6 col-sm-6 col-xs-12 container-name">
                    <input type="text" id="autocomplete-client-name-not-bill" name="name" class="form-control col-md-7 col-xs-12 @if ($errors->has('name')) parsley-error @endif" value="{{ old('name') ? old('name') : '' }}"  required>
                    <input type="hidden" id="user_id" name="user_id" value="{{ old('user_id') ? old('user_id') : '' }}">
                    @if ($errors->has('name')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('name') }}</li></ul> @endif
                    @if ($errors->has('user_id')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('user_id') }}</li></ul> @endif
                  </div>
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/bills/single" class="btn btn-primary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar</button>
                  </div>
                </div>

              </form>

          </div>
        </div>


      </div>
    </div>

    <div class="clearfix"></div>

    <!-- <div class="row filterResult hidden">
        <div class="col-md-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>Resultados</h2>

              <div class="clearfix"></div>
            </div>
            <div class="x_content">

              <section class="content invoice">

                <div class="row">
                  <div class="col-xs-12 table balanceContainerGeneral"></div>
                </div>

                <div class="row no-print hidden">
                    <button class="btn btn-primary pull-right" style="margin-right: 5px;"><i class="fa fa-download"></i> Descargar PDF</button>
                </div>

              </section>
            </div>
          </div>
        </div>
    </div> -->

  </div>
</div>
<!-- /page content -->

@include('layout_admin.footer')
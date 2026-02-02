@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Balance General</h3>
      </div>
    </div>

	<div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Buscador</h2>
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

              <form action="/admin/balance/general/search" id="balanceSearchForm" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="role">Período</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                    @if(count($periodos))
                        <select class="form-control" name="periodo">
                            @foreach($periodos as $periodo)
                                <option value="{{$periodo}}">{{$periodo}}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="alert alert-danger">
                            No existen Periodos Facturados. Los puede crear desde <a href="/admin/period/create" class="alert-link">Aqu&iacute;</a>.
                        </div>                                            
                    @endif
                    @if ($errors->has('periodo')) <p class="help-block">{{ $errors->first('periodo') }}</p> @endif

                  </div>

                  <div class="col-md-2 col-sm-2 col-xs-12">
                      <label>
                        <input type="checkbox" class="js-switch" id="periodo_all" name="periodo_all" /> Todos
                      </label>
                  </div>                  
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Cliente (DNI/CUIT)</label>
                  <div class="col-md-6 col-sm-6 col-xs-12 container-name">
                    <input type="text" id="autocomplete-client-name" name="name" class="form-control col-md-7 col-xs-12 @if ($errors->has('name')) parsley-error @endif" value="{{ old('name') ? old('name') : '' }}"  required autofocus placeholder="Ingrese DNI, CUIT o nombre...">
                    <input type="hidden" id="user_id" name="user_id" value="{{ old('user_id') ? old('user_id') : '' }}">
                    @if ($errors->has('name')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('name') }}</li></ul> @endif
                    @if ($errors->has('user_id')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('user_id') }}</li></ul> @endif
                  </div>
                
                  <div class="col-md-2 col-sm-2 col-xs-12">
                      <label>
                        <input type="checkbox" class="js-switch" id="client_all" name="client_all" /> Todos
                      </label>
                  </div>
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/balance/general" class="btn btn-primary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Buscar</button>
                  </div>
                </div>

              </form>

          </div>
        </div>


      </div>
    </div>

    <div class="clearfix"></div>

    <div class="row filterResult hidden">
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
    </div>
  </div>
</div>
<!-- /page content -->

@include('layout_admin.footer')
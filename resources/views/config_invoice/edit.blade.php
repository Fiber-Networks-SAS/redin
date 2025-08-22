@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Servicios</h3>
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
            
            @if (!empty($talonario))

              <form action="/admin/config/invoice/edit/{{$talonario->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="letra">Letra <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="letra" name="letra" class="form-control col-md-7 col-xs-12 @if ($errors->has('letra')) parsley-error @endif" value="{{ old('letra') ? old('letra') : $talonario->letra }}" placeholder="B" data-inputmask="'mask': 'a'" autofocus required>
                    @if ($errors->has('letra')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('letra') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nombre">Nombre <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="nombre" name="nombre" class="form-control col-md-7 col-xs-12 @if ($errors->has('nombre')) parsley-error @endif" value="{{ old('nombre') ? old('nombre') : $talonario->nombre }}" autofocus required >
                    @if ($errors->has('nombre')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('nombre') }}</li></ul> @endif
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nro_punto_vta">Punto de Venta <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="nro_punto_vta" name="nro_punto_vta" class="form-control col-md-7 col-xs-12 @if ($errors->has('nro_punto_vta')) parsley-error @endif" value="{{ old('nro_punto_vta') ? old('nro_punto_vta') : $talonario->nro_punto_vta }}" placeholder="0001" data-inputmask="'mask': '9999'" required>
                    @if ($errors->has('nro_punto_vta')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('nro_punto_vta') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nro_inicial">Número Inicial <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="nro_inicial" name="nro_inicial" class="form-control col-md-7 col-xs-12 @if ($errors->has('nro_inicial')) parsley-error @endif" value="{{ old('nro_inicial') ? old('nro_inicial') : $talonario->nro_inicial }}" placeholder="00000001" data-inputmask="'mask': '99999999'" required>
                    @if ($errors->has('nro_inicial')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('nro_inicial') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nro_cai">Número CAI <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="nro_cai" name="nro_cai" class="form-control col-md-7 col-xs-12 @if ($errors->has('nro_cai')) parsley-error @endif" value="{{ old('nro_cai') ? old('nro_cai') : $talonario->nro_cai }}" placeholder="00000000000001" data-inputmask="'mask': '99999999999999'" required>
                    @if ($errors->has('nro_cai')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('nro_cai') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nro_cai_fecha_vto">Fecha de Vto. <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="nro_cai_fecha_vto" name="nro_cai_fecha_vto" class="form-control col-md-7 col-xs-12 @if ($errors->has('nro_cai_fecha_vto')) parsley-error @endif" value="{{ old('nro_cai_fecha_vto') ? old('nro_cai_fecha_vto') : $talonario->nro_cai_fecha_vto }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'" required>
                    @if ($errors->has('nro_cai_fecha_vto')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('nro_cai_fecha_vto') }}</li></ul> @endif
                  </div>
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/config/invoice" class="btn btn-primary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Modificar</button>
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
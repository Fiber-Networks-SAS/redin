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

              <form action="/admin/services/create" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nombre">Nombre <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="nombre" name="nombre" class="form-control col-md-7 col-xs-12 @if ($errors->has('nombre')) parsley-error @endif" value="{{ old('nombre') ? old('nombre') : '' }}" autofocus required>
                    @if ($errors->has('nombre')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('nombre') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tipo">Tipo <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                      <select class="form-control" name="tipo" required>
                        <option value="0" {{ old('tipo') == 0 ? 'selected' : '' }}>Internet</option>
                        <option value="1" {{ old('tipo') == 1 ? 'selected' : '' }}>Telefonía</option>
                        <option value="2" {{ old('tipo') == 2 ? 'selected' : '' }}>Televisión</option>
                      </select>
                    @if ($errors->has('tipo')) <p class="help-block">{{ $errors->first('tipo') }}</p> @endif

                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="abono_mensual">Abono Mensual <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="abono_mensual" name="abono_mensual" class="form-control col-md-7 col-xs-12 @if ($errors->has('abono_mensual')) parsley-error @endif" value="{{ old('abono_mensual') ? old('abono_mensual') : '' }}" required>
                    @if ($errors->has('abono_mensual')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('abono_mensual') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="abono_proporcional">Abono Proporcional</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="input-group">
                      <input type="text" id="abono_proporcional" name="abono_proporcional" class="form-control col-md-7 col-xs-12 @if ($errors->has('abono_proporcional')) parsley-error @endif" value="{{ old('abono_proporcional') ? old('abono_proporcional') : '' }}" >
                      <span class="input-group-addon" id="abono_proporcional_sugerido">Importe Sugerido</span>
                    </div>
                    @if ($errors->has('abono_proporcional')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('abono_proporcional') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="costo_instalacion">Costo de Instalación</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="costo_instalacion" name="costo_instalacion" class="form-control col-md-7 col-xs-12 @if ($errors->has('costo_instalacion')) parsley-error @endif" value="{{ old('costo_instalacion') ? old('costo_instalacion') : '' }}">
                    @if ($errors->has('costo_instalacion')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('costo_instalacion') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="detalle">Detalle</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea id="detalle" name="detalle" class="form-control @if ($errors->has('detalle')) parsley-error @endif" rows="3">{{ old('detalle') ? old('detalle') : '' }}</textarea>
                    @if ($errors->has('detalle')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('detalle') }}</li></ul> @endif
                  </div>                
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="status">Estado</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">

                    <?php 
                      // $status = old('status') ? old('status') : '';
                      $status = old('status') ? 'checked' : '';
                    ?>

                    <input type="checkbox" class="js-switch" name="status" {{ $status }} />
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

  </div>
</div>
<!-- /page content -->

@include('layout_admin.footer')
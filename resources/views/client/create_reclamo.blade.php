@include('layout.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Mis Reclamos</h3>
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

              <form action="/my-claims/create" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="titulo">Asunto <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="titulo" name="titulo" class="form-control col-md-7 col-xs-12 @if ($errors->has('titulo')) parsley-error @endif" value="{{ old('titulo') ? old('titulo') : '' }}" autofocus required>
                    @if ($errors->has('titulo')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('titulo') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="servicio_id">Servicio Relacionado<span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                    @if(count($user->servicios))
                        <select class="form-control" name="servicio_id" required>
                            <option value="0" {{ old('servicio_id') == 0 ? 'selected' : '' }}>Ninguno</option>
                            @foreach($user->servicios as $servicio)
                                <option value="{{$servicio->servicio->id}}" {{ old('servicio_id') == $servicio->servicio->id ? 'selected' : '' }}>{{$servicio->servicio->nombre}}</option>
                            @endforeach
                        </select>
                    @else
                        <select class="form-control" name="servicio_id" required>
                            <option value="0" {{ old('servicio_id') == 0 ? 'selected' : '' }}>Ninguno</option>
                        </select>                                           
                    @endif
                    @if ($errors->has('servicio_id')) <p class="help-block">{{ $errors->first('servicio_id') }}</p> @endif

                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="mensaje">Mensaje<span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea id="mensaje" name="mensaje" class="form-control @if ($errors->has('mensaje')) parsley-error @endif" rows="3" required>{{ old('mensaje') ? old('mensaje') : '' }}</textarea>
                    @if ($errors->has('mensaje')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('mensaje') }}</li></ul> @endif
                  </div>                
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/my-claims" class="btn btn-primary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Enviar</button>
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

@include('layout.footer')
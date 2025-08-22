@include('layout.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Perfil</h3>
      </div>
    </div>

	<div class="clearfix"></div>

    <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Modificar Datos</h2>
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
                    
                    <form action="/profile" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

                    	{{ csrf_field() }}

                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nro_cliente">Nro. Cliente</label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="nro_cliente" name="nro_cliente" class="form-control col-md-7 col-xs-12 @if ($errors->has('nro_cliente')) parsley-error @endif" value="{{ old('nro_cliente') ? old('nro_cliente') : $user->nro_cliente }}" disabled="disabled">
                          </div>
                        </div>
                        
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dni">DNI / CUIT </label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="dni" name="dni" class="form-control col-md-7 col-xs-12 @if ($errors->has('dni')) parsley-error @endif" value="{{ $user->dni }}" disabled="disabled">
                          </div>
                        </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="firstname">Nombre</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="firstname" name="firstname" class="form-control col-md-7 col-xs-12 @if ($errors->has('firstname')) parsley-error @endif" value="{{ $user->firstname }}" disabled="disabled">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="lastname">Apellido</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="lastname" name="lastname" class="form-control col-md-7 col-xs-12 @if ($errors->has('lastname')) parsley-error @endif" value="{{ $user->lastname }}" disabled="disabled">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="calle">Dirección</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="calle" name="calle" class="form-control col-md-7 col-xs-12 @if ($errors->has('calle')) parsley-error @endif" value="{{ 'Barrio ' . $user->barrio . ' | Calle ' . $user->calle . ' | Nro.' . $user->altura . ' | Mz ' .  $user->manzana}}" disabled="disabled">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="calle">Localidad / Provincia</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="calle" name="calle" class="form-control col-md-7 col-xs-12 @if ($errors->has('calle')) parsley-error @endif" value="{{ $user->localidad . ' / ' . $user->provincia}}" disabled="disabled">
                        </div>
                      </div>
                      
                      <h4>Datos Personales</h4>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">{{ trans('words.email') }}<span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="email" id="email" name="email" class="form-control col-md-7 col-xs-12 @if ($errors->has('email')) parsley-error @endif" value="{{ old('email') ? old('email') : $user->email }}" autofocus required>
                          @if ($errors->has('email')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('email') }}</li></ul> @endif

                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="password">{{ trans('words.password') }}</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="password" id="password" name="password" class="form-control col-md-7 col-xs-12 @if ($errors->has('password')) parsley-error @endif" value="">
                          @if ($errors->has('password')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('password') }}</li></ul> @endif
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="password_confirm">{{ trans('words.password-confirm') }}</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="password" id="password_confirm" name="password_confirm" class="form-control col-md-7 col-xs-12 @if ($errors->has('password_confirm')) parsley-error @endif" value="">
                          @if ($errors->has('password_confirm')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('password_confirm') }}</li></ul> @endif
                        </div>
                      </div>


                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tel1">Teléfono</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="tel1" name="tel1" class="form-control col-md-7 col-xs-12 @if ($errors->has('tel1')) parsley-error @endif" value="{{ old('tel1') ? old('tel1') : $user->tel1 }}">
                          @if ($errors->has('tel1')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('tel1') }}</li></ul> @endif
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tel2">Teléfono Adicional</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="tel2" name="tel2" class="form-control col-md-7 col-xs-12 @if ($errors->has('tel2')) parsley-error @endif" value="{{ old('tel2') ? old('tel2') : $user->tel2 }}">
                          @if ($errors->has('tel2')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('tel2') }}</li></ul> @endif
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="autorizado_nombre">Persona Autorizada <small>(Nombre)</small></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="autorizado_nombre" name="autorizado_nombre" class="form-control col-md-7 col-xs-12 @if ($errors->has('autorizado_nombre')) parsley-error @endif" value="{{ old('autorizado_nombre') ? old('autorizado_nombre') : $user->autorizado_nombre }}">
                          @if ($errors->has('autorizado_nombre')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('autorizado_nombre') }}</li></ul> @endif
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="autorizado_tel">Persona Autorizada <small>(Teléfono)</small></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="autorizado_tel" name="autorizado_tel" class="form-control col-md-7 col-xs-12 @if ($errors->has('autorizado_tel')) parsley-error @endif" value="{{ old('autorizado_tel') ? old('autorizado_tel') : $user->autorizado_tel }}">
                          @if ($errors->has('autorizado_tel')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('autorizado_tel') }}</li></ul> @endif
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="picture">Foto</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="file" id="picture" name="picture" class="form-control col-md-7 col-xs-12 @if ($errors->has('picture')) parsley-error @endif" value="">
                          @if ($errors->has('picture')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('picture') }}</li></ul> @endif
                        </div>
                      </div>

                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                          <a href="/dashboard" class="btn btn-primary">Cancelar</a>
                          <button type="submit" class="btn btn-success">Modificar</button>
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
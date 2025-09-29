@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Clientes</h3>
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

              <form action="/admin/clients/create" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}

                <h4>Datos Personales</h4>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nro_cliente">Nro. Cliente<span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="nro_cliente" name="nro_cliente" class="form-control col-md-7 col-xs-12 @if ($errors->has('nro_cliente')) parsley-error @endif" value="{{ old('nro_cliente') ? old('nro_cliente') : $nro_cliente }}" required>
                    @if ($errors->has('nro_cliente')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('nro_cliente') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dni">DNI / CUIT <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="dni" name="dni" class="form-control col-md-7 col-xs-12 @if ($errors->has('dni')) parsley-error @endif" value="{{ old('dni') ? old('dni') : '' }}" autofocus required>
                    @if ($errors->has('dni')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('dni') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="firstname">Nombre <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="firstname" name="firstname" class="form-control col-md-7 col-xs-12 @if ($errors->has('firstname')) parsley-error @endif" value="{{ old('firstname') ? old('firstname') : '' }}" cssrequired>
                    @if ($errors->has('firstname')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('firstname') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="lastname">Apellido <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="lastname" name="lastname" class="form-control col-md-7 col-xs-12 @if ($errors->has('lastname')) parsley-error @endif" value="{{ old('lastname') ? old('lastname') : '' }}" required>
                    @if ($errors->has('lastname')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('lastname') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">{{ trans('words.email') }}<!--  <span class="required">*</span> --></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="email" id="email" name="email" class="form-control col-md-7 col-xs-12 @if ($errors->has('email')) parsley-error @endif" value="{{ old('email') ? old('email') : '' }}">
                    @if ($errors->has('email')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('email') }}</li></ul> @endif

                  </div>
                </div>

<!--                 <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="password">{{ trans('words.password') }} <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="password" id="password" name="password" class="form-control col-md-7 col-xs-12 @if ($errors->has('password')) parsley-error @endif" value="" required >
                    @if ($errors->has('password')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('password') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="password_confirm">{{ trans('words.password-confirm') }} <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control col-md-7 col-xs-12 @if ($errors->has('password_confirm')) parsley-error @endif" value="" required >
                    @if ($errors->has('password_confirm')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('password_confirm') }}</li></ul> @endif
                  </div>
                </div> -->

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="barrio">Barrio</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="barrio" name="barrio" class="form-control col-md-7 col-xs-12 @if ($errors->has('barrio')) parsley-error @endif" value="{{ old('barrio') ? old('barrio') : '' }}">
                    @if ($errors->has('barrio')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('barrio') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="calle">Calle</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="calle" name="calle" class="form-control col-md-7 col-xs-12 @if ($errors->has('calle')) parsley-error @endif" value="{{ old('calle') ? old('calle') : '' }}">
                    @if ($errors->has('calle')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('calle') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="altura">Altura</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="altura" name="altura" class="form-control col-md-7 col-xs-12 @if ($errors->has('altura')) parsley-error @endif" value="{{ old('altura') ? old('altura') : '' }}">
                    @if ($errors->has('altura')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('altura') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="manzana">Manzana</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="manzana" name="manzana" class="form-control col-md-7 col-xs-12 @if ($errors->has('manzana')) parsley-error @endif" value="{{ old('manzana') ? old('manzana') : '' }}">
                    @if ($errors->has('manzana')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('manzana') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="provincia">Provincia</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                    @if(count($provincias))
                        <select class="form-control" name="provincia" required>
                            @foreach($provincias as $provincia)
                                <option value="{{$provincia->id}}" {{ old('provincia') == $provincia->id ? 'selected' : '' }}>{{$provincia->nombre}}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="alert alert-danger">
                            No existen Provincias.
                        </div>                                            
                    @endif
                    @if ($errors->has('provincia')) <p class="help-block">{{ $errors->first('provincia') }}</p> @endif

                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="localidad">Localidad</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                    @if(count($localidades))
                        <select class="form-control" name="localidad" required>
                            @foreach($localidades as $localidad)
                                <option value="{{$localidad->id}}" {{ old('localidad') == $localidad->id ? 'selected' : '' }}>{{$localidad->nombre}}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="alert alert-danger">
                            No existen Localidades.
                        </div>                                            
                    @endif
                    @if ($errors->has('localidad')) <p class="help-block">{{ $errors->first('localidad') }}</p> @endif

                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="cp">Código Postal</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="cp" name="cp" class="form-control col-md-7 col-xs-12 @if ($errors->has('cp')) parsley-error @endif" value="{{ old('cp') ? old('cp') : '' }}">
                    @if ($errors->has('cp')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('cp') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tel1">Teléfono</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="tel1" name="tel1" class="form-control col-md-7 col-xs-12 @if ($errors->has('tel1')) parsley-error @endif" value="{{ old('tel1') ? old('tel1') : '' }}">
                    @if ($errors->has('tel1')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('tel1') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tel2">Teléfono <small>(Adicional)</small></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="tel2" name="tel2" class="form-control col-md-7 col-xs-12 @if ($errors->has('tel2')) parsley-error @endif" value="{{ old('tel2') ? old('tel2') : '' }}">
                    @if ($errors->has('tel2')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('tel2') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="autorizado_nombre">Persona Autorizada <small>(Nombre)</small></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="autorizado_nombre" name="autorizado_nombre" class="form-control col-md-7 col-xs-12 @if ($errors->has('autorizado_nombre')) parsley-error @endif" value="{{ old('autorizado_nombre') ? old('autorizado_nombre') : '' }}">
                    @if ($errors->has('autorizado_nombre')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('autorizado_nombre') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="autorizado_tel">Persona Autorizada <small>(Teléfono)</small></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="autorizado_tel" name="autorizado_tel" class="form-control col-md-7 col-xs-12 @if ($errors->has('autorizado_tel')) parsley-error @endif" value="{{ old('autorizado_tel') ? old('autorizado_tel') : '' }}">
                    @if ($errors->has('autorizado_tel')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('autorizado_tel') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="talonario_id">Comprobante</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                    @if(count($talonarios))
                        <select class="form-control" name="talonario_id" required>
                            @foreach($talonarios as $talonario)
                                <option value="{{$talonario->id}}" data-letra="{{$talonario->letra}}" {{ old('talonario_id') == $talonario->id ? 'selected' : '' }}>{{$talonario->nombre}}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="alert alert-danger">
                            No existen Talonarios.
                        </div>                                            
                    @endif
                    @if ($errors->has('talonario_id')) <p class="help-block">{{ $errors->first('talonario_id') }}</p> @endif

                  </div>
                </div>


                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="comentario">Comentarios</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea id="comentario" name="comentario" class="form-control @if ($errors->has('comentario')) parsley-error @endif" rows="3">{{ old('comentario') ? old('comentario') : '' }}</textarea>
                    @if ($errors->has('comentario')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('comentario') }}</li></ul> @endif
                  </div>                
                </div>
                
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="picture">Foto</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="file" id="picture" name="picture" class="form-control col-md-7 col-xs-12 @if ($errors->has('picture')) parsley-error @endif" value="">
                    @if ($errors->has('picture')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('picture') }}</li></ul> @endif
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

                <h4>Datos Técnicos</h4>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="drop">Drop</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    
                      <select class="form-control" name="drop" required>
                        <option value="0" {{ old('drop') == 0 ? 'selected' : '' }}>En Pilar</option>
                        <option value="1" {{ old('drop') == 1 ? 'selected' : '' }}>En Domicilio</option>
                        <option value="2" {{ old('drop') == 2 ? 'selected' : '' }}>Sin Drop</option>
                      </select>
                    @if ($errors->has('drop')) <p class="help-block">{{ $errors->first('drop') }}</p> @endif

                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="firma_contrato">Firma del Contrato</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="firma_contrato" name="firma_contrato" class="form-control col-md-7 col-xs-12 @if ($errors->has('firma_contrato')) parsley-error @endif" value="{{ old('firma_contrato') ? old('firma_contrato') : '' }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'">
                    @if ($errors->has('firma_contrato')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('firma_contrato') }}</li></ul> @endif
                  </div>
                </div>

                <!-- <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ont_instalado">ONT Instalado</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">  
                    <div class="radio">
                      <label>
                        <input type="radio" class="flat" value="1" checked name="ont_instalado"> Si
                      </label>
                      <label>
                        <input type="radio" class="flat" value="0" name="ont_instalado"> No
                      </label>
                    </div>
                  </div>
                </div> -->



                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ont_instalado">ONT Instalado</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="ont_instalado" name="ont_instalado" class="form-control col-md-7 col-xs-12 @if ($errors->has('ont_instalado')) parsley-error @endif" value="{{ old('ont_instalado') ? old('ont_instalado') : '' }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'">
                    @if ($errors->has('ont_instalado')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('ont_instalado') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ont_funcionando">ONT Funcionando</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="ont_funcionando" name="ont_funcionando" class="form-control col-md-7 col-xs-12 @if ($errors->has('ont_funcionando')) parsley-error @endif" value="{{ old('ont_funcionando') ? old('ont_funcionando') : '' }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'">
                    @if ($errors->has('ont_funcionando')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('ont_funcionando') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ont_serie1">ONT Serie <small>(1)</small></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="ont_serie1" name="ont_serie1" class="form-control col-md-7 col-xs-12 @if ($errors->has('ont_serie1')) parsley-error @endif" value="{{ old('ont_serie1') ? old('ont_serie1') : '' }}">
                    @if ($errors->has('ont_serie1')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('ont_serie1') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ont_serie2">ONT Serie <small>(2)</small></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="ont_serie2" name="ont_serie2" class="form-control col-md-7 col-xs-12 @if ($errors->has('ont_serie2')) parsley-error @endif" value="{{ old('ont_serie2') ? old('ont_serie2') : '' }}">
                    @if ($errors->has('ont_serie2')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('ont_serie2') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="spliter_serie">Spliter</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="spliter_serie" name="spliter_serie" class="form-control col-md-7 col-xs-12 @if ($errors->has('spliter_serie')) parsley-error @endif" value="{{ old('spliter_serie') ? old('spliter_serie') : '' }}">
                    @if ($errors->has('spliter_serie')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('spliter_serie') }}</li></ul> @endif
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Instalador <small>(Personal)</small></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="autocomplete-user-name" name="name" class="form-control col-md-7 col-xs-12 @if ($errors->has('name')) parsley-error @endif" value="{{ old('name') ? old('name') : '' }}">
                    <input type="hidden" id="instalador_id" name="instalador_id" value="{{ old('instalador_id') ? old('instalador_id') : '' }}">
                    @if ($errors->has('name')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('name') }}</li></ul> @endif
                    @if ($errors->has('instalador_id')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('instalador_id') }}</li></ul> @endif
                  </div>
                </div>
                
                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/clients" class="btn btn-primary">Cancelar</a>
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

<script>
$(document).ready(function() {
    function updateDniField() {
        var selectedOption = $('select[name="talonario_id"] option:selected');
        var letra = selectedOption.data('letra');
        if (letra == 'A') {
            $('#dni').attr('placeholder', 'CUIT sin guiones (11 dígitos)');
            $('#dni').attr('maxlength', '11');
            $('#dni').attr('minlength', '11');
            $('label[for="dni"]').html('CUIT <span class="required">*</span>');
        } else {
            $('#dni').attr('placeholder', '');
            $('#dni').removeAttr('maxlength');
            $('#dni').removeAttr('minlength');
            $('label[for="dni"]').html('DNI / CUIT <span class="required">*</span>');
        }
    }
    updateDniField();
    $('select[name="talonario_id"]').change(function() {
        updateDniField();
    });
});
</script>

@include('layout_admin.footer')
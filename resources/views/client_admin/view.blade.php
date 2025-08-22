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
            <h2>Ver</h2>
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
            @if (!empty($user))

              <form action="/admin/clients/edit/{{$user->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

                  {{ csrf_field() }}


                  @if($user->fecha_registro != '')
                    <div class="col-md-12 col-sm-12 col-xs-12 alert alert-success" role="alert">
                      El Cliente activó su cuenta el día <strong>{{ $user->fecha_registro }}</strong>.
                    </div>
                  @else
                    <div class="col-md-12 col-sm-12 col-xs-12 alert alert-danger" role="alert">
                      El Cliente aún no activó su cuenta.
                    </div>                  
                  @endif
                  
                  <h4>Datos Personales</h4>
                  
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nro_cliente">Nro. Cliente</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="nro_cliente" name="nro_cliente" class="form-control col-md-7 col-xs-12 @if ($errors->has('nro_cliente')) parsley-error @endif" value="{{ old('nro_cliente') ? old('nro_cliente') : $user->nro_cliente }}" readonly>
                      @if ($errors->has('nro_cliente')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('nro_cliente') }}</li></ul> @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dni">DNI / CUIT </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="dni" name="dni" class="form-control col-md-7 col-xs-12 @if ($errors->has('dni')) parsley-error @endif" value="{{ old('dni') ? old('dni') : $user->dni }}" readonly>
                      @if ($errors->has('dni')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('dni') }}</li></ul> @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="firstname">Nombre</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="firstname" name="firstname" class="form-control col-md-7 col-xs-12 @if ($errors->has('firstname')) parsley-error @endif" value="{{ old('firstname') ? old('firstname') : $user->firstname }}" readonly>
                      @if ($errors->has('firstname')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('firstname') }}</li></ul> @endif
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="lastname">Apellido</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="lastname" name="lastname" class="form-control col-md-7 col-xs-12 @if ($errors->has('lastname')) parsley-error @endif" value="{{ old('lastname') ? old('lastname') : $user->lastname }}" readonly>
                      @if ($errors->has('lastname')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('lastname') }}</li></ul> @endif
                    </div>
                  </div>                

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">{{ trans('words.email') }}</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="email" id="email" name="email" class="form-control col-md-7 col-xs-12 @if ($errors->has('email')) parsley-error @endif" value="{{ old('email') ? old('email') : $user->email }}" readonly>
                      @if ($errors->has('email')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('email') }}</li></ul> @endif

                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="barrio">Barrio</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="barrio" name="barrio" class="form-control col-md-7 col-xs-12 @if ($errors->has('barrio')) parsley-error @endif" value="{{ old('barrio') ? old('barrio') :  $user->barrio }}" readonly>
                      @if ($errors->has('barrio')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('barrio') }}</li></ul> @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="calle">Calle</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="calle" name="calle" class="form-control col-md-7 col-xs-12 @if ($errors->has('calle')) parsley-error @endif" value="{{ old('calle') ? old('calle') :  $user->calle }}" readonly>
                      @if ($errors->has('calle')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('calle') }}</li></ul> @endif
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="altura">Altura</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="altura" name="altura" class="form-control col-md-7 col-xs-12 @if ($errors->has('altura')) parsley-error @endif" value="{{ old('altura') ? old('altura') :  $user->altura }}" readonly>
                      @if ($errors->has('altura')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('altura') }}</li></ul> @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="manzana">Manzana</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="manzana" name="manzana" class="form-control col-md-7 col-xs-12 @if ($errors->has('manzana')) parsley-error @endif" value="{{ old('manzana') ? old('manzana') :  $user->manzana }}" readonly>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="provincia">Provincia</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="provincia" name="provincia" class="form-control col-md-7 col-xs-12 @if ($errors->has('provincia')) parsley-error @endif" value="{{ old('provincia') ? old('provincia') :  $user->provincia }}" readonly>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="localidad">Localidad</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="localidad" name="localidad" class="form-control col-md-7 col-xs-12 @if ($errors->has('localidad')) parsley-error @endif" value="{{ old('localidad') ? old('localidad') :  $user->localidad }}" readonly>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="cp">Código Postal</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="cp" name="cp" class="form-control col-md-7 col-xs-12 @if ($errors->has('cp')) parsley-error @endif" value="{{ old('cp') ? old('cp') : $user->cp }}" readonly>
                      @if ($errors->has('cp')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('cp') }}</li></ul> @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tel1">Teléfono</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="tel1" name="tel1" class="form-control col-md-7 col-xs-12 @if ($errors->has('tel1')) parsley-error @endif" value="{{ old('tel1') ? old('tel1') : $user->tel1 }}" readonly>
                      @if ($errors->has('tel1')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('tel1') }}</li></ul> @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tel2">Teléfono <small>(Adicional)</small></label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="tel2" name="tel2" class="form-control col-md-7 col-xs-12 @if ($errors->has('tel2')) parsley-error @endif" value="{{ old('tel2') ? old('tel2') : $user->tel2 }}" readonly>
                      @if ($errors->has('tel2')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('tel2') }}</li></ul> @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="autorizado_nombre">Persona Autorizada <small>(Nombre)</small></label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="autorizado_nombre" name="autorizado_nombre" class="form-control col-md-7 col-xs-12 @if ($errors->has('autorizado_nombre')) parsley-error @endif" value="{{ old('autorizado_nombre') ? old('autorizado_nombre') : $user->autorizado_nombre }}" readonly>
                      @if ($errors->has('autorizado_nombre')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('autorizado_nombre') }}</li></ul> @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="autorizado_tel">Persona Autorizada <small>(Teléfono)</small></label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="autorizado_tel" name="autorizado_tel" class="form-control col-md-7 col-xs-12 @if ($errors->has('autorizado_tel')) parsley-error @endif" value="{{ old('autorizado_tel') ? old('autorizado_tel') : $user->autorizado_tel }}" readonly>
                      @if ($errors->has('autorizado_tel')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('autorizado_tel') }}</li></ul> @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="talonario">Comprobante</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="talonario" name="talonario" class="form-control col-md-7 col-xs-12 @if ($errors->has('talonario')) parsley-error @endif" value="{{ old('talonario') ? old('talonario') : $user->talonario }}" readonly>
                      @if ($errors->has('talonario')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('talonario') }}</li></ul> @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="comentario">Comentarios</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <textarea id="comentario" name="comentario" class="form-control @if ($errors->has('comentario')) parsley-error @endif" rows="3" readonly>{{ old('comentario') ? old('comentario') : $user->comentario }}</textarea>
                      @if ($errors->has('comentario')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('comentario') }}</li></ul> @endif
                    </div>
                  </div> 

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="status">Estado</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <?php 
                        $cliente_status = $user->status ? 'Activo' : 'Inactivo';
                        $cliente_fecha_baja = !$user->status && $user->fecha_baja != null && $user->fecha_baja != '' ? ' (Fecha de Baja: ' . $user->fecha_baja . ')' : '';
                        $view_status = $cliente_status . $cliente_fecha_baja;
                       ?>
                      <input type="text" id="estado" name="estado" class="form-control col-md-7 col-xs-12 @if ($errors->has('estado')) parsley-error @endif" value="{{ $view_status }}" readonly>
                    </div>
                  </div>


                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="picture">Foto</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <img src="{{ $user->picture ? '/pictures/' . $user->picture : '/_admin/images/user_default.png' }}" alt="Picture" class="img-circle img-responsive avatar-view" />
                    </div>
                  </div>



                  <h4>Datos Técnicos</h4>
                

                  <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="drop">Drop</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="drop" name="drop" class="form-control col-md-7 col-xs-12 @if ($errors->has('drop')) parsley-error @endif" value="{{ $user->drop_detalle }}" readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="firma_contrato">Firma del Contrato</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="firma_contrato" name="firma_contrato" class="form-control col-md-7 col-xs-12 @if ($errors->has('firma_contrato')) parsley-error @endif" value="{{ old('firma_contrato') ? old('firma_contrato') : $user->firma_contrato }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'" readonly>
                  </div>
                </div>

                <!-- <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ont_instalado">ONT Instalado</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">  
                      <input type="text" id="ont_instalado" name="ont_instalado" class="form-control col-md-7 col-xs-12 @if ($errors->has('ont_instalado')) parsley-error @endif" value="{{ $user->ont_instalado == '1' ? 'Si' : 'No' }}" readonly>
                  </div>
                </div> -->

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ont_instalado">ONT Instalado</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="ont_instalado" name="ont_instalado" class="form-control col-md-7 col-xs-12 @if ($errors->has('ont_instalado')) parsley-error @endif" value="{{ old('ont_instalado') ? old('ont_instalado') : $user->ont_instalado }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'" readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ont_funcionando">ONT Funcionando</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="ont_funcionando" name="ont_funcionando" class="form-control col-md-7 col-xs-12 @if ($errors->has('ont_funcionando')) parsley-error @endif" value="{{ old('ont_funcionando') ? old('ont_funcionando') : $user->ont_funcionando }}"  placeholder="dd/mm/aaaa" data-inputmask="'mask': '99/99/9999'" readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ont_serie1">ONT Serie <small>(1)</small></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="ont_serie1" name="ont_serie1" class="form-control col-md-7 col-xs-12 @if ($errors->has('ont_serie1')) parsley-error @endif" value="{{ old('ont_serie1') ? old('ont_serie1') : $user->ont_serie1 }}" readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="ont_serie2">ONT Serie <small>(2)</small></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="ont_serie2" name="ont_serie2" class="form-control col-md-7 col-xs-12 @if ($errors->has('ont_serie2')) parsley-error @endif" value="{{ old('ont_serie2') ? old('ont_serie2') : $user->ont_serie2 }}" readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="spliter_serie">Spliter</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="spliter_serie" name="spliter_serie" class="form-control col-md-7 col-xs-12 @if ($errors->has('spliter_serie')) parsley-error @endif" value="{{ old('spliter_serie') ? old('spliter_serie') : $user->spliter_serie }}" readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Instalador <small>(Personal)</small></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="autocomplete-user-name" name="name" class="form-control col-md-7 col-xs-12 @if ($errors->has('name')) parsley-error @endif" value="{{ $user->instalador ? $user->instalador->firstname . ' ' . $user->instalador->lastname : ''}}" readonly>
                  </div>
                </div>


                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/clients" class="btn btn-primary">Volver</a>
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
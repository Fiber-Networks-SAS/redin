@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Personal</h3>
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
            <br />
            
            @if (!empty($user))

              <form action="/admin/staff/edit/{{$user->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

                {{ csrf_field() }}

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
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="lastname">Apellido</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="lastname" name="lastname" class="form-control col-md-7 col-xs-12 @if ($errors->has('lastname')) parsley-error @endif" value="{{ old('lastname') ? old('lastname') : $user->lastname }}" readonly>
                  </div>
                </div>                

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">{{ trans('words.email') }}</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="email" id="email" name="email" class="form-control col-md-7 col-xs-12 @if ($errors->has('email')) parsley-error @endif" value="{{ old('email') ? old('email') : $user->email }}" readonly >
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="calle">Dirección</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="calle" name="calle" class="form-control col-md-7 col-xs-12 @if ($errors->has('calle')) parsley-error @endif" value="{{ old('calle') ? old('calle') :  $user->calle }}" readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tel1">Teléfono</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="tel1" name="tel1" class="form-control col-md-7 col-xs-12 @if ($errors->has('tel1')) parsley-error @endif" value="{{ old('tel1') ? old('tel1') :  $user->tel1 }}" readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tel2">Teléfono <small>(Adicional)</small></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="tel2" name="tel2" class="form-control col-md-7 col-xs-12 @if ($errors->has('tel2')) parsley-error @endif" value="{{ old('tel2') ? old('tel2') :  $user->tel2 }}" readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="comentario">Comentarios</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea id="comentario" name="comentario" class="form-control @if ($errors->has('comentario')) parsley-error @endif" rows="3" readonly>{{ old('comentario') ? old('comentario') : $user->comentario }}</textarea>
                    @if ($errors->has('comentario')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('comentario') }}</li></ul> @endif
                  </div>
                </div> 

                @if($user->id != Auth::user()->id)
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="status">Estado</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="estado" name="estado" class="form-control col-md-7 col-xs-12 @if ($errors->has('estado')) parsley-error @endif" value="{{ $user->status ? 'Activo' : 'Inactivo' }}" readonly>
                    </div>
                  </div>
                @endif  
                



                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/staff" class="btn btn-primary">Volver</a>
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
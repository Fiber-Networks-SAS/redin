@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Administradores</h3>
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

              <form action="/admin/users/edit/{{$user->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

                {{ csrf_field() }}

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

                @if($user->id != Auth::user()->id)
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="status">Estado</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="estado" name="estado" class="form-control col-md-7 col-xs-12 @if ($errors->has('estado')) parsley-error @endif" value="{{ $user->status ? 'Activo' : 'Inactivo' }}" readonly>
                    </div>
                  </div>
                @endif  
                
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="picture">Foto</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <img src="{{ $user->picture ? '/pictures/' . $user->picture : '/_admin/images/user_default.png' }}" alt="Picture" class="img-circle img-responsive avatar-view" />
                  </div>

                </div>


                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/users" class="btn btn-primary">Volver</a>
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
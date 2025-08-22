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
            
            @if (!empty($user))

              <form action="/admin/users/edit/{{$user->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="firstname">Nombre <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="firstname" name="firstname" class="form-control col-md-7 col-xs-12 @if ($errors->has('firstname')) parsley-error @endif" value="{{ old('firstname') ? old('firstname') : $user->firstname }}" autofocus required >
                    @if ($errors->has('firstname')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('firstname') }}</li></ul> @endif
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="lastname">Apellido <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="lastname" name="lastname" class="form-control col-md-7 col-xs-12 @if ($errors->has('lastname')) parsley-error @endif" value="{{ old('lastname') ? old('lastname') : $user->lastname }}" required>
                    @if ($errors->has('lastname')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('lastname') }}</li></ul> @endif
                  </div>
                </div>                

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">{{ trans('words.email') }} <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="email" id="email" name="email" class="form-control col-md-7 col-xs-12 @if ($errors->has('email')) parsley-error @endif" value="{{ old('email') ? old('email') : $user->email }}" required>
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
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="calle">Dirección</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="calle" name="calle" class="form-control col-md-7 col-xs-12 @if ($errors->has('calle')) parsley-error @endif" value="{{ old('calle') ? old('calle') :  $user->calle }}">
                    @if ($errors->has('calle')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('calle') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tel1">Teléfono</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="tel1" name="tel1" class="form-control col-md-7 col-xs-12 @if ($errors->has('tel1')) parsley-error @endif" value="{{ old('tel1') ? old('tel1') :  $user->tel1 }}">
                    @if ($errors->has('tel1')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('tel1') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="picture">Foto</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="file" id="picture" name="picture" class="form-control col-md-7 col-xs-12 @if ($errors->has('picture')) parsley-error @endif" value="">
                    @if ($errors->has('picture')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('picture') }}</li></ul> @endif
                  </div>
                </div>

                @if($user->id != Auth::user()->id)
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="status">Estado</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">

                      <?php 
                        $status = old('status') ? old('status') : $user->status;
                        $status = $status ? 'checked' : '';
                      ?>

                      <input type="checkbox" class="js-switch" name="status" {{ $status }} />
                    </div>
                  </div>
                @endif  

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/users" class="btn btn-primary">Cancelar</a>
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
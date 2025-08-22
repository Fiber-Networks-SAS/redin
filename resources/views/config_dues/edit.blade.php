@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Cuotas</h3>
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
            
            @if (!empty($cuota))

              <form action="/admin/config/dues/edit/{{$cuota->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="numero">Número de Cuota <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="numero" name="numero" class="form-control col-md-7 col-xs-12 @if ($errors->has('numero')) parsley-error @endif" value="{{ old('numero') ? old('numero') : $cuota->numero }}" placeholder="1" autofocus required>
                    @if ($errors->has('numero')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('numero') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="interes">Interés <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="interes" name="interes" class="form-control col-md-7 col-xs-12 @if ($errors->has('interes')) parsley-error @endif" value="{{ old('interes') ? old('interes') : $cuota->interes }}" autofocus required >
                    @if ($errors->has('interes')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('interes') }}</li></ul> @endif
                  </div>
                </div>


                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/config/dues" class="btn btn-primary">Cancelar</a>
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
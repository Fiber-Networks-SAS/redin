@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Plan de pagos</h3>
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
            
              <form action="/admin/config/payments" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="max_cuotas">Nro. máximo de cuotas <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="max_cuotas" name="max_cuotas" class="form-control col-md-7 col-xs-12 @if ($errors->has('max_cuotas')) parsley-error @endif" value="{{ old('max_cuotas') ? old('max_cuotas') : ($pagosConfig ? $pagosConfig->max_cuotas : '') }}" placeholder="12" required>
                    @if ($errors->has('max_cuotas')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('max_cuotas') }}</li></ul> @endif
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tasa">Tasa Efectiva Mensual <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="tasa" name="tasa" class="form-control col-md-7 col-xs-12 @if ($errors->has('tasa')) parsley-error @endif" value="{{ old('tasa') ? old('tasa') : ($pagosConfig ? $pagosConfig->tasa : '') }}" placeholder="4">
                    @if ($errors->has('tasa')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('tasa') }}</li></ul> @endif
                  </div>
                </div>
                
                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <!-- <button type="submit" class="btn btn-primary">Cancel</button> -->
                    <a href="/admin/dashboard" class="btn btn-primary">Cancelar</a>
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

@include('layout_admin.footer')
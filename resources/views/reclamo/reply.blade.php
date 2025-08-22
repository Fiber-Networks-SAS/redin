@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Reclamos</h3>
      </div>
    </div>

	<div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2><strong>{{$reclamo->titulo}}</strong> <small>{{$reclamo->servicio != null ? '(Servicio ' . $reclamo->servicio->nombre . ')' : ''}}</small></h2>
            <span class="nav navbar-right">
              <!-- <a href="#" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"></i> Volver</a> -->

              <h4><?php echo $reclamo->status == 0 ? '<span class="label label-success pull-left">Abierto</span>' : '<span class="label label-default pull-left">Cerrado</span>'; ?></h4>


            </span>  
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

            <ul class="messages">

              <li>
                <div class="message_date">
                  <p class="month">{{$reclamo->fecha}}</p>
                </div>

                <div class="message_wrapper">
                  <h4 class="heading"><a href="/admin/clients/view/{{ $reclamo->usuario->id }}">{{ $reclamo->usuario->firstname.' '.$reclamo->usuario->lastname }}</a></h4>
                  <blockquote class="message">{{$reclamo->mensaje}}</blockquote>
                </div>
                <br>
              </li>

              @foreach($reclamo->replys as $reply)
                <li>
                  <div class="message_date">
                    <p class="month">{{$reply->fecha}}</p>
                  </div>

                  <div class="message_wrapper">
                    <h4 class="heading">{{ $reply->usuario->firstname.' '.$reply->usuario->lastname }}</h4>
                    <blockquote class="message">{{$reply->mensaje}}</blockquote>
                  </div>
                  <br>
                </li>
              @endforeach                             

            </ul>

            @if (!empty($reclamo))

              <form action="/admin/claims/reply/{{$reclamo->id}}" method="POST" class="form-horizontal form-label-left" enctype="multipart/form-data">

              	{{ csrf_field() }}

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="mensaje">Respuesta <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea id="mensaje" name="mensaje" class="form-control @if ($errors->has('mensaje')) parsley-error @endif" rows="3" required>{{ old('mensaje') ? old('mensaje') : '' }}</textarea>
                    @if ($errors->has('mensaje')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('mensaje') }}</li></ul> @endif
                  </div>
                </div> 

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <a href="/admin/claims" class="btn btn-primary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Responder</button>
  
                    @if($reclamo->status == 0)
                      <a href="/admin/claims/close/{{$reclamo->id}}" class="btn btn-dark">Cerrar</a>
                    @endif

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
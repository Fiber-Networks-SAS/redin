@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Facturas</h3>
      </div>
    </div>

  <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Listado | <strong>{{$user->firstname . ' '. $user->lastname}}</strong> <small>(Cliente Nro. {{$user->nro_cliente }})</small></h2>
            <span class="nav navbar-right">

              <a href="/admin/clients" class="btn btn-primary btn-xs"><i class="fa fa-arrow-left"></i> Volver</a>
            </span>
            <div class="clearfix"></div>
          </div>

          @if (session('status'))

              <div class="panel panel-{{session('status')}}">
                  <div class="panel-heading">
                      <i class="fa {{session('icon')}}"></i> {{session('message')}} @if (session('filename')) Puede ver el PDF <b><a href="{{session('filename')}}" target="_blank">Aquí</a></b> @endif
                  </div>     
              </div>   

          @endif

          <div class="x_content">
      
            {{ csrf_field() }}

            <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
            
            <table id="dataTableClientFacturas" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
              <thead>
                <tr>
                  <th></th>
                  <!-- <th>Tipo</th> -->
                  <!-- <th>Talonario</th> -->
                  <th>Período</th>
                  <th>Factura</th>
                  <!-- <th>Nro. Ciente</th> -->
                  <!-- <th>Nombre y Apellido</th> -->
                  <th>Emisión</th>
                  <th>Fecha Vto.</th>
                  <th>Imp. Facturado</th>
                  <th>Imp. Pagado</th>
                  <!-- <th>Enviada</th> -->
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>

              <tbody>
                
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
  
</div>
<!-- /page content -->





@include('layout_admin.footer')
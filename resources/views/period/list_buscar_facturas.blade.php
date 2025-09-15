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
            <h2>Buscar</h2>
            <span class="nav navbar-right">
              <!-- <a href="/admin/period" class="btn btn-primary btn-xs"><i class="fa fa-arrow-left"></i> Volver</a> -->
            	<!-- <a href="/admin/period/create" class="btn btn-info btn-xs"><i class="fa fa-plus-square-o"></i> Nuevo</a> -->
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

      			
            <table id="dataTableFacturasBuscar" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
              <thead>
                <tr>
                  <th></th>
                  <!-- <th>Tipo</th> -->
                  <!-- <th>Talonario</th> -->
                  <th>Factura</th>
                  <th>Nro. Ciente</th>
                  <th>DNI / CUIT</th>
                  <th>Nombre y Apellido</th>
                  <th>Fecha de Emisión</th>
                  <th>Vencimiento</th>
                  <th>Importe</th>
                  <th>Enviado</th>
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
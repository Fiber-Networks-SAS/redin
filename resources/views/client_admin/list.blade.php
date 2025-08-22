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
            <h2>Listado</h2>
            <span class="nav navbar-right">
              <!-- <a href="/clients/admin/list" class="btn btn-success btn-xs"><i class="fa fa-print"></i> Listado de Cohermanos</a> -->
            	<a href="/admin/clients/create" class="btn btn-info btn-xs"><i class="fa fa-plus-square-o"></i> Nuevo</a>
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
			
      			{{ csrf_field() }}

      			<table id="dataTableClients" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
              <thead>
                <tr>
                  <th>Nro. Cliente</th>
                  <th>Estado</th>
                  <th>DNI</th>
                  <th>Nombre</th>
                  <th>Apellido</th>
                  <th>Correo Electrónico</th>
                  <th>Tel.</th>
                  <th>ONT Serie</th>
                  <th>ONT Funcionando</th>
                  <th>Drop</th>
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
@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Auditor&iacute;a</h3>
      </div>
    </div>

	<div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Listado</h2>
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

			<table id="dataTableAuditoria" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Usuario</th>
                  <th>Acción</th>
                  <th>Elemento</th>
                  <th>Valor Anterior</th>
                  <th>Valor Actual</th>
                  <th>Url</th>
                  <th>Fecha</th>
                  <!-- <th>Dirección IP</th> -->
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
@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Plan de Pagos</h3>
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
              <a href="/admin/clients/payment_plan/create/{{$user->id}}" class="btn btn-info btn-xs"><i class="fa fa-plus-square-o"></i> Nuevo</a>
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

            <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
            
            <table id="dataTableClientPaymentPlan" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
              <thead>
                <tr>
                  <!-- <th>ID</th> -->
                  <th>Servicio</th>
                  <th>Abono Mensual</th>
                  <th>Cuotas Adeudadas</th>
                  <th>Total Adeudado</th>
                  <th>Plan de Pago</th>
                  <th>Importe Mensual a pagar</th>
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
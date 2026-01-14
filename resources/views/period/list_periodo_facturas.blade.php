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
            <h2>Facturas del Período <strong>{{$periodo}}</strong></h2>
            <span class="nav navbar-right">
              <a href="/admin/period" class="btn btn-primary btn-xs"><i class="fa fa-arrow-left"></i> Volver</a>
              @php
                list($mes, $ano) = explode('/', $periodo);
              @endphp
              <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#modalAnularPeriodo">
                <i class="fa fa-exclamation-triangle"></i> Anular Período
              </button>
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

            <input type="hidden" name="periodo" id="periodo" value="{{$periodo}}">
      			
            <table id="dataTableFacturasPeriodo" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
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
                  <th>Enviada</th>
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

<!-- Modal de Advertencia CRÍTICA de Anulación -->
<div class="modal fade" id="modalAnularPeriodo" tabindex="-1" role="dialog" aria-labelledby="modalAnularPeriodoLabel" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border: 5px solid #d9534f;">
      
      <!-- Header -->
      <div class="modal-header" style="background-color: #d9534f; color: white;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
          <span aria-hidden="true" style="font-size: 28px;">&times;</span>
        </button>
        <h3 class="modal-title text-center" style="font-weight: bold;">
          <i class="fa fa-exclamation-triangle fa-2x" style="display: block; margin-bottom: 10px;"></i>
          ADVERTENCIA CRÍTICA
        </h3>
      </div>
      
      <!-- Body -->
      <div class="modal-body" style="padding: 25px;">
        
        <!-- Mensaje principal -->
        <div class="alert alert-danger" style="border: 2px solid #d9534f; font-size: 15px;">
          <h4 style="margin-top: 0; font-weight: bold;">
            <i class="fa fa-bomb"></i> ESTÁ POR ANULAR TODO EL PERÍODO {{$periodo}}
          </h4>
          <p style="margin-bottom: 0;">
            Esta es una operación <strong>IRREVERSIBLE</strong> que afectará TODAS las facturas de este período.
          </p>
        </div>

        <!-- Qué va a suceder -->
        <div class="panel panel-danger">
          <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-warning"></i> ¿Qué va a suceder?</h4>
          </div>
          <div class="panel-body">
            <ul style="margin-bottom: 0; line-height: 1.8;">
              <li><strong>TODAS las facturas del período {{$periodo}} serán anuladas definitivamente</strong></li>
              <li><strong>Se generarán automáticamente Notas de Crédito en AFIP</strong></li>
              <li><strong>Las facturas anuladas no aparecerán en los reportes del sistema</strong></li>
              <li><strong>Los clientes no verán estas facturas en sus estados de cuenta</strong></li>
              <li><strong>Esta acción NO se puede deshacer</strong></li>
            </ul>
          </div>
        </div>

        <!-- Cuándo usar -->
        <div class="panel panel-warning">
          <div class="panel-heading">
            <h4 class="panel-title"><i class="fa fa-question-circle"></i> ¿Cuándo usar esta función?</h4>
          </div>
          <div class="panel-body">
            <ul style="margin-bottom: 0; line-height: 1.8;">
              <li>Cuando se generó un período completo con datos erróneos</li>
              <li>Cuando se facturó con tarifas incorrectas</li>
              <li>Cuando hay un error masivo que afecta a todos los clientes del período</li>
            </ul>
          </div>
        </div>

        <!-- Nota informativa -->
        <div class="alert alert-info" style="margin-bottom: 0;">
          <i class="fa fa-info-circle"></i> <strong>Nota:</strong> Si tiene dudas, cancele esta operación y consulte con un supervisor o técnico senior.
        </div>

      </div>
      
      <!-- Footer -->
      <div class="modal-footer" style="padding: 15px 25px;">
        <button type="button" class="btn btn-success btn-lg" data-dismiss="modal">
          <i class="fa fa-times-circle"></i> CANCELAR
        </button>
        <form id="formAnularPeriodo" action="/admin/period/cancel/{{$mes}}/{{$ano}}" method="POST" style="display: inline;">
          {{ csrf_field() }}
          <input type="hidden" name="confirmacion" id="inputConfirmacion" value="0">
          <button type="submit" id="btnConfirmarAnulacion" class="btn btn-danger btn-lg" style="font-weight: bold;">
            <i class="fa fa-trash"></i> SÍ, ANULAR PERÍODO {{$periodo}}
          </button>
        </form>
      </div>
      
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  console.log('Modal script loaded');
  

  // Validar antes de enviar
  $('#formAnularPeriodo').on('submit', function(e) {
    if (!$('#confirmarResponsabilidad').is(':checked')) {
      e.preventDefault();
      alert('Debe confirmar la operación marcando el checkbox.');
      return false;
    }
  });

  // Resetear al cerrar el modal
  $('#modalAnularPeriodo').on('hidden.bs.modal', function () {
    $('#confirmarResponsabilidad').prop('checked', false);
    $('#inputConfirmacion').val('0');
    $('#btnConfirmarAnulacion').prop('disabled', true).css('opacity', '0.5');
  });
});
</script>



@include('layout_admin.footer')
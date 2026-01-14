@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Anular Período</h3>
      </div>
    </div>

	<div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Confirmar Anulación del Período <strong class="text-danger">{{$periodo}}</strong></h2>
            <span class="nav navbar-right">
              <a href="/admin/period/view/{{$mes}}/{{$ano}}" class="btn btn-primary btn-xs">
                <i class="fa fa-arrow-left"></i> Volver
              </a>
            </span>
            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            
            <div class="alert alert-warning">
              <h4><i class="fa fa-exclamation-triangle"></i> ¡ATENCIÓN!</h4>
              <p><strong>Esta acción es irreversible y tendrá las siguientes consecuencias:</strong></p>
              <ul>
                <li>Se emitirán <strong>Notas de Crédito en AFIP</strong> automáticamente por cada factura del período</li>
                <li>Las NC obtendrán CAE de AFIP y quedarán registradas oficialmente</li>
                <li>Las facturas NO se eliminarán físicamente, quedarán registradas con su motivo de anulación</li>
                <li>Esta operación se registrará en el sistema de auditoría</li>
                <li><strong class="text-danger">No se requiere talonario de NC</strong> - Las NC se emiten directamente en AFIP</li>
              </ul>
            </div>

            <div class="panel panel-info">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-info-circle"></i> Estadísticas del Período {{$periodo}}</h3>
              </div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-md-3 col-sm-6">
                    <div class="well well-sm">
                      <h4 class="text-center">{{$stats['total_facturas']}}</h4>
                      <p class="text-center text-muted">Total de Facturas</p>
                    </div>
                  </div>
                  <div class="col-md-3 col-sm-6">
                    <div class="well well-sm">
                      <h4 class="text-center">{{$stats['facturas_pagadas']}}</h4>
                      <p class="text-center text-muted">Facturas Pagadas</p>
                    </div>
                  </div>
                  <div class="col-md-3 col-sm-6">
                    <div class="well well-sm">
                      <h4 class="text-center">{{$stats['facturas_con_cae']}}</h4>
                      <p class="text-center text-muted">Emitidas en AFIP</p>
                    </div>
                  </div>
                  <div class="col-md-3 col-sm-6">
                    <div class="well well-sm">
                      <h4 class="text-center">${{number_format($stats['importe_total'], 2)}}</h4>
                      <p class="text-center text-muted">Importe Total</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul>
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form action="/admin/period/cancel/{{$mes}}/{{$ano}}" method="POST" class="form-horizontal form-label-left" onsubmit="return confirmarAnulacion();">
              {{ csrf_field() }}

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="motivo_anulacion">
                  Motivo de Anulación <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <textarea 
                    name="motivo_anulacion" 
                    id="motivo_anulacion" 
                    class="form-control" 
                    rows="4" 
                    required
                    placeholder="Explique detalladamente el motivo de la anulación del período (mínimo 10 caracteres)"
                  >{{old('motivo_anulacion')}}</textarea>
                  <p class="help-block">Este motivo quedará registrado en todas las facturas y notas de crédito del período.</p>
                </div>
              </div>

              <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="confirmacion" id="confirmacion" value="1" required> 
                      <strong>Confirmo que entiendo las consecuencias y deseo anular el período {{$periodo}}</strong>
                    </label>
                  </div>
                </div>
              </div>

              <div class="ln_solid"></div>
              
              <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                  <a href="/admin/period/view/{{$mes}}/{{$ano}}" class="btn btn-default">
                    <i class="fa fa-times"></i> Cancelar
                  </a>
                  <button type="submit" class="btn btn-danger">
                    <i class="fa fa-trash"></i> Anular Período
                  </button>
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

<script>
function confirmarAnulacion() {
  var motivo = document.getElementById('motivo_anulacion').value;
  var confirmacion = document.getElementById('confirmacion').checked;
  
  if (!confirmacion) {
    alert('Debe marcar la casilla de confirmación para continuar.');
    return false;
  }
  
  if (motivo.length < 10) {
    alert('El motivo debe tener al menos 10 caracteres.');
    return false;
  }
  
  return confirm('¿Está completamente seguro de que desea anular el período {{$periodo}}?\n\nEsta acción NO se puede deshacer.');
}
</script>

@include('layout_admin.footer')

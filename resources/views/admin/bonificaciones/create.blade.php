@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Bonificaciones de Servicios</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Nueva Bonificación</h2>
            <span class="nav navbar-right">
              <a href="/admin/bonificaciones" class="btn btn-default btn-xs">
                <i class="fa fa-arrow-left"></i> Volver al Listado
              </a>
            </span>
            <div class="clearfix"></div>
          </div>

          @if($errors->any())
              <div class="panel panel-danger">
                  <div class="panel-heading">
                      <i class="fa fa-exclamation-triangle"></i> Por favor corrija los siguientes errores:
                  </div>
                  <div class="panel-body">
                      <ul>
                          @foreach($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              </div>
          @endif

          <div class="x_content">
            <form method="POST" action="/admin/bonificaciones/create" class="form-horizontal form-label-left">
              {{ csrf_field() }}

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="service_id">
                  Servicio <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <select name="service_id" id="service_id" class="form-control" required>
                    <option value="">Seleccione un servicio...</option>
                    @foreach($servicios as $servicio)
                      <option value="{{ $servicio->id }}" {{ old('service_id') == $servicio->id ? 'selected' : '' }}>
                        {{ $servicio->nombre }} - ${{ number_format($servicio->abono_mensual, 2) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="porcentaje_bonificacion">
                  Porcentaje de Bonificación (%) <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="number" name="porcentaje_bonificacion" id="porcentaje_bonificacion" 
                         class="form-control" min="0" max="100" step="0.01" 
                         value="{{ old('porcentaje_bonificacion') }}" required
                         placeholder="Ej: 15.50">
                  <small class="text-muted">Ingrese el porcentaje de descuento (0-100)</small>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="periodos_bonificacion">
                  Períodos de Bonificación (meses) <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="number" name="periodos_bonificacion" id="periodos_bonificacion" 
                         class="form-control" min="1" max="120" 
                         value="{{ old('periodos_bonificacion') }}" required
                         placeholder="Ej: 6">
                  <small class="text-muted">Número de meses que durará la bonificación (máximo 120 meses)</small>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="fecha_inicio">
                  Fecha de Inicio <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="date" name="fecha_inicio" id="fecha_inicio" 
                         class="form-control" value="{{ old('fecha_inicio', date('Y-m-d')) }}" required>
                  <small class="text-muted">Fecha desde la cual comenzará a aplicarse la bonificación</small>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="descripcion">
                  Descripción
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <textarea name="descripcion" id="descripcion" class="form-control" 
                            rows="3" placeholder="Descripción opcional de la bonificación">{{ old('descripcion') }}</textarea>
                </div>
              </div>

              <div class="ln_solid"></div>

              <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-save"></i> Crear Bonificación
                  </button>
                  <a href="/admin/bonificaciones" class="btn btn-default">
                    <i class="fa fa-times"></i> Cancelar
                  </a>
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

<script>
$(document).ready(function() {
    // Calculadora de bonificación en tiempo real
    $('#porcentaje_bonificacion, #service_id').change(function() {
        calculateBonification();
    });
    
    function calculateBonification() {
        var serviceSelect = $('#service_id');
        var porcentaje = parseFloat($('#porcentaje_bonificacion').val());
        
        if (serviceSelect.val() && porcentaje) {
            var selectedOption = serviceSelect.find('option:selected');
            var serviceText = selectedOption.text();
            var priceMatch = serviceText.match(/\$([0-9,\.]+)/);
            
            if (priceMatch) {
                var precio = parseFloat(priceMatch[1].replace(',', ''));
                var descuento = (precio * porcentaje) / 100;
                var precioFinal = precio - descuento;
                
                var infoHtml = '<div class="alert alert-info">' +
                    '<strong>Vista previa:</strong><br>' +
                    'Precio original: $' + precio.toFixed(2) + '<br>' +
                    'Descuento (' + porcentaje + '%): -$' + descuento.toFixed(2) + '<br>' +
                    'Precio final: $' + precioFinal.toFixed(2) +
                    '</div>';
                
                $('#preview-container').remove();
                $(infoHtml).attr('id', 'preview-container').insertAfter('#porcentaje_bonificacion').parent().parent();
            }
        } else {
            $('#preview-container').remove();
        }
    }
});
</script>
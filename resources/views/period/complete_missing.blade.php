@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3><i class="fa fa-check-circle"></i> Completar Facturas Faltantes</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Completar Facturas de un Período</h2>
            
            <span class="nav navbar-right">
              <a href="/admin/period" class="btn btn-primary btn-xs"><i class="fa fa-arrow-left"></i> Volver</a>
            </span>

            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            
            <div class="alert alert-info">
              <strong><i class="fa fa-info-circle"></i> ¿Qué hace esta herramienta?</strong>
              <p>Identifica clientes que deberían tener factura en el período especificado pero no la tienen, y las genera automáticamente.</p>
              <p><strong>Útil cuando:</strong></p>
              <ul>
                <li>El proceso de facturación se interrumpió por un error</li>
                <li>Algunos clientes quedaron sin factura</li>
                <li>Necesitas reanudar la facturación desde donde se detuvo</li>
              </ul>
              <p class="text-muted"><small><i class="fa fa-shield"></i> Esta acción <strong>NO duplica</strong> facturas. Si un cliente ya tiene factura en el periodo, se omite.</small></p>
            </div>

            <form id="formCompletarFacturas" class="form-horizontal form-label-left">
              {{ csrf_field() }}

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="periodo">
                  Período <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" id="periodo" name="periodo" 
                         class="form-control" 
                         placeholder="MM/YYYY (ej: 01/2026)"
                         data-inputmask="'mask': '99/9999'"
                         required autofocus>
                  <small class="form-text text-muted">
                    Ingrese el período en formato MM/YYYY
                  </small>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="fecha_emision">
                  Fecha de Emisión <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" id="fecha_emision" name="fecha_emision" 
                         class="form-control" 
                         value="{{ date('d/m/Y') }}"
                         placeholder="DD/MM/YYYY (ej: 04/02/2026)"
                         data-inputmask="'mask': '99/99/9999'"
                         required>
                  <small class="form-text text-muted">
                    Fecha que aparecerá en las facturas generadas
                  </small>
                </div>
              </div>

              <div class="ln_solid"></div>
              
              <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                  <button type="button" id="btnVerificar" class="btn btn-info">
                    <i class="fa fa-search"></i> Verificar Faltantes
                  </button>
                  <button type="submit" id="btnCompletar" class="btn btn-success" disabled>
                    <i class="fa fa-check-circle"></i> Completar Facturas
                  </button>
                  <button type="reset" class="btn btn-default">
                    <i class="fa fa-refresh"></i> Limpiar
                  </button>
                </div>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>

    <!-- Panel de verificación -->
    <div class="row" id="verificacionContainer" style="display: none;">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2><i class="fa fa-search"></i> Clientes Sin Factura</h2>
            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            <div id="verificacionContent"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel de resultados -->
    <div class="row" id="resultadoContainer" style="display: none;">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2><i class="fa fa-check-square"></i> Resultado del Proceso</h2>
            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            <div id="resultContent"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel de progreso -->
    <div class="row" id="progressContainer" style="display: none;">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_content text-center">
            <div style="padding: 40px;">
              <i class="fa fa-spinner fa-spin fa-3x fa-fw text-primary"></i>
              <h3 id="progressMessage">Procesando...</h3>
              <p class="text-muted">Este proceso puede tomar varios minutos. Por favor, no cierre esta ventana.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<!-- /page content -->

@include('layout_admin.footer')

@push('scripts')
<!-- SweetAlert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

<script>
(function($) {
$(document).ready(function() {
    // Aplicar máscaras de entrada
    $('#periodo').inputmask();
    $('#fecha_emision').inputmask();

    // Verificar clientes sin factura
    $('#btnVerificar').click(function() {
        var periodo = $('#periodo').val();
        
        if (!periodo) {
            swal("Error", "Por favor ingrese un período", "error");
            return;
        }

        // Validar formato
        if (!/^\d{2}\/\d{4}$/.test(periodo)) {
            swal("Error", "El formato del período debe ser MM/YYYY", "error");
            return;
        }

        $('#verificacionContainer').hide();
        $('#progressContainer').show();
        $('#progressMessage').text('Verificando clientes sin factura...');

        $.ajax({
            url: '/admin/period/verify-missing',
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                periodo: periodo
            },
            success: function(response) {
                $('#progressContainer').hide();
                $('#verificacionContainer').show();

                if (response.success) {
                    if (response.clientes_sin_factura === 0) {
                        $('#verificacionContent').html(`
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i> <strong>No hay facturas faltantes</strong>
                                <p>Todos los clientes activos ya tienen factura para el período ${periodo}.</p>
                            </div>
                        `);
                        $('#btnCompletar').prop('disabled', true);
                    } else {
                        var html = `
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i> <strong>Se encontraron ${response.clientes_sin_factura} cliente(s) sin factura</strong>
                            </div>
                            
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nro. Cliente</th>
                                        <th>Nombre</th>
                                        <th>DNI</th>
                                        <th>Servicios Activos</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        response.clientes.forEach(function(cliente) {
                            html += `
                                <tr>
                                    <td>${cliente.nro_cliente}</td>
                                    <td>${cliente.nombre} ${cliente.apellido}</td>
                                    <td>${cliente.dni}</td>
                                    <td>${cliente.servicios_count}</td>
                                </tr>
                            `;
                        });

                        html += `
                                </tbody>
                            </table>
                            
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Haga clic en <strong>"Completar Facturas"</strong> para generar las ${response.clientes_sin_factura} factura(s) faltante(s).
                            </div>
                        `;

                        $('#verificacionContent').html(html);
                        $('#btnCompletar').prop('disabled', false);
                    }
                }
            },
            error: function(xhr) {
                $('#progressContainer').hide();
                var errorMsg = 'Error al verificar';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                swal("Error", errorMsg, "error");
            }
        });
    });

    // Completar facturas faltantes
    $('#formCompletarFacturas').submit(function(e) {
        e.preventDefault();

        var periodo = $('#periodo').val();
        var fecha_emision = $('#fecha_emision').val();

        // Validaciones
        if (!periodo || !fecha_emision) {
            swal("Error", "Por favor complete todos los campos", "error");
            return;
        }

        if (!/^\d{2}\/\d{4}$/.test(periodo)) {
            swal("Error", "El formato del período debe ser MM/YYYY", "error");
            return;
        }

        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(fecha_emision)) {
            swal("Error", "El formato de la fecha debe ser DD/MM/YYYY", "error");
            return;
        }

        // Confirmar acción
        swal({
            title: "¿Está seguro?",
            text: "Se generarán todas las facturas faltantes del período " + periodo,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#26B99A",
            confirmButtonText: "Sí, continuar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: false,
            showLoaderOnConfirm: true
        }, function() {
            procesarFacturas(periodo, fecha_emision);
        });
    });

    function procesarFacturas(periodo, fecha_emision) {
        $('#resultadoContainer').hide();
        $('#progressContainer').show();
        $('#progressMessage').text('Generando facturas faltantes... Este proceso puede tomar varios minutos.');

        $.ajax({
            url: '/admin/bill/complete-missing',
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                periodo: periodo,
                fecha_emision: fecha_emision
            },
            timeout: 600000, // 10 minutos
            success: function(response) {
                $('#progressContainer').hide();
                
                if (response.success) {
                    mostrarResultado(response);
                    
                    swal({
                        title: "¡Proceso Completado!",
                        text: response.message,
                        type: "success",
                        confirmButtonColor: "#26B99A"
                    });
                } else {
                    swal("Error", response.message || "Error al completar facturas", "error");
                }
            },
            error: function(xhr) {
                $('#progressContainer').hide();
                
                var errorMsg = 'Error al procesar las facturas';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.statusText === 'timeout') {
                    errorMsg = 'El proceso tomó demasiado tiempo. Verifique el log del servidor.';
                }
                
                swal("Error", errorMsg, "error");
            }
        });
    }

    function mostrarResultado(response) {
        $('#resultadoContainer').show();

        var summary = response.summary;
        var facturas = response.facturas || [];
        var errores = response.errores || [];

        var html = `
            <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Facturas Creadas</span>
                            <span class="info-box-number">${summary.facturas_creadas}</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total sin Factura</span>
                            <span class="info-box-number">${summary.total_clientes_sin_factura}</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box ${errores.length > 0 ? 'bg-red' : 'bg-gray'}">
                        <span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Errores</span>
                            <span class="info-box-number">${summary.errores}</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-blue">
                        <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Período</span>
                            <span class="info-box-number">${summary.periodo}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Tabla de facturas creadas
        if (facturas.length > 0) {
            html += `
                <h4><i class="fa fa-list"></i> Facturas Generadas (${facturas.length})</h4>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nro. Factura</th>
                                <th>Cliente ID</th>
                                <th>Importe Total</th>
                                <th>CAE</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            facturas.forEach(function(factura) {
                html += `
                    <tr>
                        <td>${factura.id}</td>
                        <td><strong>${factura.nro_factura}</strong></td>
                        <td>${factura.user_id}</td>
                        <td>$${parseFloat(factura.importe_total).toFixed(2)}</td>
                        <td><small>${factura.cae || 'N/A'}</small></td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        // Tabla de errores
        if (errores.length > 0) {
            html += `
                <h4 class="text-danger"><i class="fa fa-exclamation-circle"></i> Errores Encontrados (${errores.length})</h4>
                <div class="alert alert-danger">
                    <p><strong>Algunos clientes no pudieron ser facturados:</strong></p>
                </div>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Cliente ID</th>
                            <th>Nro. Cliente</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            errores.forEach(function(error) {
                html += `
                    <tr>
                        <td>${error.user_id}</td>
                        <td>${error.nro_cliente}</td>
                        <td class="text-danger"><small>${error.error}</small></td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Revise el log del servidor para más detalles: <code>storage/logs/laravel.log</code>
                </div>
            `;
        }

        html += `
            <div class="text-muted" style="margin-top: 20px;">
                <small><i class="fa fa-clock-o"></i> Procesado: ${summary.processed_at}</small>
            </div>
        `;

        $('#resultContent').html(html);
    }

    // Limpiar al resetear
    $('button[type="reset"]').click(function() {
        $('#verificacionContainer').hide();
        $('#resultadoContainer').hide();
        $('#btnCompletar').prop('disabled', true);
    });
});
})(jQuery);
</script>
@endpush

<style>
.info-box {
    display: block;
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}

.info-box-icon {
    border-radius: 2px 0 0 2px;
    display: block;
    float: left;
    height: 90px;
    width: 90px;
    text-align: center;
    font-size: 45px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 90px;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 28px;
}

.info-box-text {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.bg-green { background-color: #26B99A !important; color: white; }
.bg-yellow { background-color: #F39C12 !important; color: white; }
.bg-red { background-color: #E74C3C !important; color: white; }
.bg-blue { background-color: #3498DB !important; color: white; }
.bg-gray { background-color: #95A5A6 !important; color: white; }
</style>

@include('layout_admin.footer')

@push('scripts')
<!-- SweetAlert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

<script>

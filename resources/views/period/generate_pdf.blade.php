@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3><i class="fa fa-file-pdf-o"></i> Generar PDFs de Período</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-8 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Generar PDFs de un Período</h2>
            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            
            <form id="formGenerarPDF" class="form-horizontal form-label-left">
              {{ csrf_field() }}

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="periodo">
                  Período <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" id="periodo" name="periodo" 
                         class="form-control col-md-7 col-xs-12" 
                         placeholder="MM/YYYY (ej: 03/2024)"
                         required>
                  <small class="form-text text-muted">
                    Ingrese el período en formato MM/YYYY
                  </small>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">
                  Opciones
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" id="force" name="force" value="1"> 
                      Forzar regeneración si el PDF ya existe
                    </label>
                  </div>
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" id="verbose" name="verbose" value="1"> 
                      Mostrar información detallada
                    </label>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                  <button type="submit" class="btn btn-success btn-lg">
                    <i class="fa fa-file-pdf-o"></i> Generar PDF
                  </button>
                  <button type="reset" class="btn btn-default btn-lg">
                    <i class="fa fa-refresh"></i> Limpiar
                  </button>
                </div>
              </div>
            </form>

          </div>
        </div>
      </div>

      <!-- Panel de resultados -->
      <div class="col-md-4 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Últimos Períodos</h2>
            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            <div id="periodosList">
              <p class="text-muted">Cargando períodos...</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel de resultado de la generación -->
    <div class="row" id="resultadoContainer" style="display: none;">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel" id="resultPanel">
          <div class="x_title">
            <h2>Resultado de la Generación</h2>
            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            <div id="resultContent"></div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<!-- /page content -->

<style>
  .alert-info-custom {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid #d1d3e3;
    border-radius: 3px;
  }

  .alert-success-custom {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
  }

  .alert-error-custom {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
  }

  .alert-warning-custom {
    background-color: #fff3cd;
    border-color: #ffeeba;
    color: #856404;
  }

  .info-item {
    padding: 5px 0;
    border-bottom: 1px solid #e3e3e3;
  }

  .info-item:last-child {
    border-bottom: none;
  }

  .info-label {
    font-weight: bold;
    color: #666;
  }

  .info-value {
    color: #333;
    word-break: break-all;
  }

  .loading {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 100%;
    background-color: #333;
    animation: sk-bounce 1.4s infinite ease-in-out both;
  }

  .loading.one {
    animation-delay: -0.32s;
  }

  .loading.two {
    animation-delay: -0.16s;
  }

  @keyframes sk-bounce {
    0%, 80%, 100% {
      transform: scale(0);
    }
    40% {
      transform: scale(1);
    }
  }
</style>

@include('layout_admin.footer')

<script>
$(document).ready(function() {
  
  // Cargar últimos períodos
  cargarUltimosPeriodos();

  // Manejar envío del formulario
  $('#formGenerarPDF').on('submit', function(e) {
    e.preventDefault();
    generarPDF();
  });

  function cargarUltimosPeriodos() {
    $.ajax({
      url: '/admin/period/list',
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.data && response.data.length > 0) {
          let html = '<div class="list-group">';
          
          // Tomar los últimos 5 períodos
          response.data.slice(0, 5).forEach(function(periodo) {
            html += `
              <a href="javascript:void(0)" 
                 class="list-group-item list-group-item-action periodo-item"
                 data-periodo="${periodo.periodo}">
                <strong>${periodo.periodo}</strong>
                <small class="text-muted d-block">
                  ${periodo.total} facturas | ${periodo.pagas} pagas
                </small>
              </a>
            `;
          });

          html += '</div>';
          $('#periodosList').html(html);

          // Evento click en períodos
          $('.periodo-item').on('click', function(e) {
            e.preventDefault();
            $('#periodo').val($(this).data('periodo'));
            $('#formGenerarPDF').focus();
          });
        }
      }
    });
  }

  function generarPDF() {
    const periodo = $('#periodo').val();
    const force = $('#force').is(':checked') ? 1 : 0;
    const verbose = $('#verbose').is(':checked') ? 1 : 0;

    if (!periodo) {
      mostrarResultado('error', 'Por favor ingrese un período');
      return;
    }

    // Mostrar loading
    mostrarLoading();

    // Hacer la petición
    $.ajax({
      url: '/admin/period/generate-pdf',
      type: 'GET',
      data: {
        periodo: periodo,
        force: force,
        verbose: verbose
      },
      dataType: 'json',
      timeout: 300000, // 5 minutos de timeout
      success: function(response) {
        mostrarResultado(response.status, response);
      },
      error: function(xhr) {
        let mensaje = 'Error desconocido';
        let datos = {};

        try {
          datos = JSON.parse(xhr.responseText);
          mensaje = datos.message || mensaje;
        } catch (e) {
          mensaje = 'Error en la solicitud (HTTP ' + xhr.status + ')';
        }

        mostrarResultado('error', {
          status: 'error',
          message: mensaje,
          ...datos
        });
      }
    });
  }

  function mostrarLoading() {
    $('#resultadoContainer').show();
    $('#resultContent').html(`
      <div class="text-center" style="padding: 40px;">
        <p>Generando PDFs...</p>
        <div style="display: flex; justify-content: center; gap: 5px;">
          <div class="loading one"></div>
          <div class="loading two"></div>
          <div class="loading"></div>
        </div>
      </div>
    `);
  }

  function mostrarResultado(status, data) {
    $('#resultadoContainer').show();

    let html = '';
    const clase = status === 'success' ? 'alert-success-custom' : 'alert-error-custom';

    html += `<div class="alert-info-custom ${clase}">`;
    html += `<strong>${status === 'success' ? '✓ Éxito' : '✗ Error'}:</strong> ${data.message}`;
    html += `</div>`;

    // Información del período
    if (data.periodo) {
      html += `
        <div class="info-item">
          <span class="info-label">Período:</span>
          <span class="info-value">${data.periodo}</span>
        </div>
      `;
    }

    // Cantidad de facturas
    if (data.facturas_count) {
      html += `
        <div class="info-item">
          <span class="info-label">Facturas:</span>
          <span class="info-value">${data.facturas_count}</span>
        </div>
      `;
    }

    // Acción realizada
    if (data.action) {
      const acciones = {
        'generated': 'Generado',
        'regenerated': 'Regenerado',
        'no_generated': 'No se generó (ya existe)'
      };
      html += `
        <div class="info-item">
          <span class="info-label">Acción:</span>
          <span class="info-value">${acciones[data.action] || data.action}</span>
        </div>
      `;
    }

    // Tiempo de ejecución
    if (data.execution_time_seconds) {
      html += `
        <div class="info-item">
          <span class="info-label">Tiempo:</span>
          <span class="info-value">${data.execution_time_seconds}s</span>
        </div>
      `;
    }

    // Tamaño del archivo
    if (data.file_size_kb) {
      html += `
        <div class="info-item">
          <span class="info-label">Tamaño del archivo:</span>
          <span class="info-value">${data.file_size_kb} KB</span>
        </div>
      `;
    }

    // Link al PDF
    if (data.pdf_path && status === 'success') {
      html += `
        <div class="info-item" style="margin-top: 15px;">
          <a href="${data.pdf_path}" target="_blank" class="btn btn-info btn-sm">
            <i class="fa fa-download"></i> Descargar PDF
          </a>
        </div>
      `;
    }

    // Información verbose
    if (data.verbose) {
      html += `
        <div class="info-item" style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #e3e3e3;">
          <strong>Información técnica:</strong>
          <div class="info-item">
            <span class="info-label">Carpeta:</span>
            <span class="info-value" style="font-size: 12px;">${data.verbose.folder_periodos}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Disco:</span>
            <span class="info-value">${data.verbose.disk}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Ruta:</span>
            <span class="info-value" style="font-size: 11px; word-break: break-all;">${data.verbose.storage_path || 'N/A'}</span>
          </div>
        </div>
      `;
    }

    // Error trace
    if (data.error_trace) {
      html += `
        <div class="info-item" style="margin-top: 15px; padding: 10px; background-color: #f5f5f5; border-radius: 3px;">
          <strong>Stack trace:</strong>
          <pre style="font-size: 11px; margin: 5px 0 0 0;">${data.error_trace}</pre>
        </div>
      `;
    }

    $('#resultContent').html(html);

    // Scroll al resultado
    $('html, body').animate({
      scrollTop: $('#resultadoContainer').offset().top - 100
    }, 300);
  }

  // Validar formato del período en tiempo real
  $('#periodo').on('blur', function() {
    const valor = $(this).val();
    if (valor && !/^\d{2}\/\d{4}$/.test(valor)) {
      $(this).addClass('parsley-error');
      $(this).after('<small class="help-block">Formato: MM/YYYY (ej: 03/2024)</small>');
    } else {
      $(this).removeClass('parsley-error');
      $(this).next('.help-block').remove();
    }
  });

});
</script>

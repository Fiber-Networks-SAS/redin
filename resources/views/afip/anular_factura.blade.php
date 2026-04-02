@include('layout_admin.header')

<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3><i class="fa fa-ban"></i> Anular Factura y Emitir Nota de Cr&eacute;dito</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="alert alert-warning">
      <strong><i class="fa fa-exclamation-triangle"></i> Atenci&oacute;n:</strong>
      Esta herramienta emite una <strong>Nota de Cr&eacute;dito por el total</strong> de la factura seleccionada,
      <strong>anula la factura</strong> en el sistema y permite su refacturaci&oacute;n.
      Si la factura estaba pagada, se generar&aacute; un <strong>saldo a favor</strong> para el cliente.
    </div>

    {{-- Mensajes flash --}}
    @if (session('status'))
    <div class="alert alert-{{ session('status') == 'success' ? 'success' : 'danger' }}" id="flash-msg">
      <i class="fa {{ session('icon', 'fa-info-circle') }}"></i> {!! session('message') !!}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0" style="margin:0;">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    {{-- ===== BUSCADOR ===== --}}
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="x_panel">
          <div class="x_title">
            <h2><i class="fa fa-search"></i> Buscar factura activa</h2>
            <div class="clearfix"></div>
          </div>
          <div class="x_content" style="padding-bottom:10px;">
            <div class="input-group input-group-lg">
              <input type="text" id="buscador" class="form-control"
                     placeholder="Nombre del cliente, DNI/CUIT, n&uacute;mero de factura..."
                     autocomplete="off">
              <span class="input-group-addon" style="cursor:pointer;" id="btn-limpiar" title="Limpiar selecci&oacute;n">
                <i class="fa fa-times"></i>
              </span>
            </div>
            <div id="dropdown-resultados" style="display:none; position:absolute; z-index:9999; width:calc(100% - 30px); background:#fff; border:1px solid #ddd; border-top:none; border-radius:0 0 4px 4px; max-height:320px; overflow-y:auto; box-shadow:0 4px 8px rgba(0,0,0,.15);">
            </div>
            <small class="text-muted" id="hint-buscar">Escrib&iacute; al menos 2 caracteres para buscar. Solo se muestran facturas activas (no anuladas).</small>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== PANEL DE DETALLE (oculto hasta que se seleccione una factura) ===== --}}
    <div id="panel-detalle" style="display:none;">

      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <div class="x_panel">
            <div class="x_title">
              <h2 id="titulo-factura"></h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">

              <div class="row">
                {{-- Datos generales --}}
                <div class="col-md-5">
                  <table class="table table-condensed table-striped" id="tabla-datos">
                  </table>
                </div>
                {{-- Servicios --}}
                <div class="col-md-7">
                  <h4>Servicios facturados</h4>
                  <table class="table table-condensed table-bordered">
                    <thead><tr><th>Servicio</th><th class="text-right">Importe</th></tr></thead>
                    <tbody id="tabla-detalles"></tbody>
                  </table>
                </div>
              </div>

              {{-- NC ya emitidas --}}
              <div id="bloque-historial" style="display:none;">
                <hr>
                <h4><i class="fa fa-history"></i> Notas de cr&eacute;dito ya emitidas para esta factura</h4>
                <table class="table table-striped table-condensed">
                  <thead>
                    <tr>
                      <th>Tipo</th><th>N&uacute;mero</th><th>Fecha</th>
                      <th class="text-right">Importe</th><th>CAE</th>
                      <th>Vto. CAE</th><th>Motivo</th>
                    </tr>
                  </thead>
                  <tbody id="tabla-historial"></tbody>
                </table>
              </div>

              <hr>

              {{-- Resumen de anulaci&oacute;n --}}
              <div class="alert alert-danger" id="resumen-anulacion">
                <h4><i class="fa fa-ban"></i> Resumen de la operaci&oacute;n</h4>
                <ul style="margin-top:10px; font-size:14px;">
                  <li>Se emitir&aacute; una <strong>Nota de Cr&eacute;dito en AFIP</strong> por el total: <strong id="resumen-total" class="text-danger"></strong></li>
                  <li>La factura quedar&aacute; <strong>anulada</strong> en el sistema</li>
                  <li id="resumen-saldo" style="display:none;">Se crear&aacute; un <strong>saldo a favor</strong> para el cliente por el importe pagado: <strong id="resumen-saldo-monto"></strong></li>
                  <li>El cliente podr&aacute; ser <strong>refacturado</strong> en el pr&oacute;ximo per&iacute;odo o factura individual</li>
                </ul>
              </div>

              {{-- Formulario de motivo + confirm --}}
              <form id="form-anular" method="POST" action="" class="form-horizontal form-label-left">
                {{ csrf_field() }}

                <div class="form-group">
                  <label class="control-label col-md-3">Motivo de anulaci&oacute;n <span class="required">*</span></label>
                  <div class="col-md-6">
                    <textarea name="motivo" id="motivo" class="form-control" rows="3"
                              placeholder="Ej: Error en los servicios facturados, cliente dado de baja, etc."
                              required maxlength="500">{{ old('motivo') }}</textarea>
                  </div>
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-md-offset-3">
                    <button type="button" class="btn btn-danger btn-lg" id="btn-confirm-anular">
                      <i class="fa fa-ban"></i> Anular Factura y Emitir NC
                    </button>
                  </div>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>

    </div>

    {{-- Estado vac&iacute;o --}}
    <div id="panel-vacio" class="row">
      <div class="col-md-8 col-md-offset-2 text-center" style="padding: 40px 0; color: #aaa;">
        <i class="fa fa-search fa-4x"></i>
        <p style="margin-top:15px; font-size:16px;">Busc&aacute; una factura para anular</p>
      </div>
    </div>

  </div>
</div>

@include('layout_admin.footer')

<script>
$(document).ready(function () {

  var facturaActual = null;
  var searchTimeout = null;

  // -- B&uacute;squeda --

  $('#buscador').on('input', function () {
    clearTimeout(searchTimeout);
    var q = $(this).val().trim();
    if (q.length < 2) {
      ocultarDropdown();
      return;
    }
    $('#hint-buscar').text('Buscando...');
    searchTimeout = setTimeout(function () {
      $.getJSON('/admin/anular-factura/buscar', { q: q })
        .done(function (data) {
          mostrarDropdown(data);
          $('#hint-buscar').text(data.length ? '' : 'Sin resultados.');
        })
        .fail(function () {
          $('#hint-buscar').text('Error al buscar.');
        });
    }, 300);
  });

  function mostrarDropdown(data) {
    var $d = $('#dropdown-resultados').empty();
    if (data.length === 0) {
      $d.append('<div style="padding:10px 14px; color:#999;">Sin resultados</div>');
    } else {
      $.each(data, function (i, item) {
        var icon = '<i class="fa fa-file-text-o" style="margin-right:6px; color:#888;"></i>';
        $('<div>')
          .css({ padding: '9px 14px', cursor: 'pointer', borderBottom: '1px solid #f0f0f0', background: '#fff' })
          .html(icon + item.label)
          .on('mouseenter', function () { $(this).css('background', '#f5f5f5'); })
          .on('mouseleave', function () { $(this).css('background', '#fff'); })
          .on('click', function () {
            ocultarDropdown();
            $('#buscador').val(item.label);
            cargarDetalle(item.id);
          })
          .appendTo($d);
      });
    }
    $d.show();
  }

  function ocultarDropdown() {
    $('#dropdown-resultados').hide().empty();
  }

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#buscador, #dropdown-resultados').length) {
      ocultarDropdown();
    }
  });

  $('#btn-limpiar').on('click', function () {
    $('#buscador').val('').trigger('focus');
    ocultarDropdown();
    limpiarDetalle();
  });

  // -- Cargar detalle --

  function cargarDetalle(id) {
    $('#panel-detalle').hide();
    $('#panel-vacio').hide();

    $.getJSON('/admin/anular-factura/' + id + '/detalle')
      .done(function (f) {
        facturaActual = f;
        renderDetalle(f);
        $('#panel-detalle').show();
        $('#form-anular').attr('action', '/admin/anular-factura/' + f.id);
      })
      .fail(function (xhr) {
        var msg = xhr.responseJSON && xhr.responseJSON.error
          ? xhr.responseJSON.error
          : 'No se pudo cargar la factura.';
        alert(msg);
        limpiarDetalle();
      });
  }

  function limpiarDetalle() {
    facturaActual = null;
    $('#panel-detalle').hide();
    $('#panel-vacio').show();
    $('#form-anular').attr('action', '');
  }

  function renderDetalle(f) {
    // T&iacute;tulo
    $('#titulo-factura').html(
      '<i class="fa fa-file-text-o"></i> Factura ' + f.letra + ' ' + f.nro_punto_vta + '-' + f.nro_factura +
      ' &mdash; <small>' + f.cliente_nombre + '</small>'
    );

    // Tabla datos
    var pagadoHtml = f.fecha_pago
      ? '<tr><th>Fecha pago:</th><td><span class="label label-success">' + f.fecha_pago + '</span></td></tr>' +
        '<tr><th>Importe pagado:</th><td><strong class="text-success">$' + f.importe_pago + '</strong></td></tr>'
      : '<tr><th>Estado pago:</th><td><span class="label label-default">No pagada</span></td></tr>';
    var caeHtml = f.cae
      ? '<tr><th>CAE:</th><td><code>' + f.cae + '</code></td></tr>'
      : '';
    $('#tabla-datos').html(
      '<tr><th>Tipo:</th><td>Factura ' + f.letra + '</td></tr>' +
      '<tr><th>N&uacute;mero:</th><td>' + f.letra + ' ' + f.nro_punto_vta + '-' + f.nro_factura + '</td></tr>' +
      '<tr><th>Per&iacute;odo:</th><td>' + (f.periodo || '-') + '</td></tr>' +
      '<tr><th>Emisi&oacute;n:</th><td>' + f.fecha_emision + '</td></tr>' +
      '<tr><th>Cliente:</th><td>' + f.cliente_nombre + '</td></tr>' +
      '<tr><th>DNI/CUIT:</th><td><code>' + (f.cliente_dni || '-') + '</code></td></tr>' +
      '<tr><th>Subtotal:</th><td>$' + f.importe_subtotal + '</td></tr>' +
      '<tr><th>Total:</th><td><strong class="text-primary">$' + f.importe_total + '</strong></td></tr>' +
      caeHtml + pagadoHtml
    );

    // Servicios
    var detallesHtml = '';
    if (f.detalles && f.detalles.length) {
      $.each(f.detalles, function (i, d) {
        detallesHtml += '<tr><td>' + d.servicio + '</td><td class="text-right">$' + d.importe + '</td></tr>';
      });
    } else {
      detallesHtml = '<tr><td colspan="2" class="text-center text-muted">Sin detalle</td></tr>';
    }
    $('#tabla-detalles').html(detallesHtml);

    // Historial NC
    if (f.historial && f.historial.length) {
      var histHtml = '';
      $.each(f.historial, function (i, h) {
        var badge = '<span class="label label-warning">' + h.tipo + '</span>';
        histHtml +=
          '<tr>' +
          '<td>' + badge + '</td>' +
          '<td><code>' + h.nro + '</code></td>' +
          '<td>' + h.fecha + '</td>' +
          '<td class="text-right">$' + h.importe_total + '</td>' +
          '<td><code>' + (h.cae || '-') + '</code></td>' +
          '<td>' + h.cae_vto + '</td>' +
          '<td>' + (h.motivo || '-') + '</td>' +
          '</tr>';
      });
      $('#tabla-historial').html(histHtml);
      $('#bloque-historial').show();
    } else {
      $('#tabla-historial').empty();
      $('#bloque-historial').hide();
    }

    // Resumen
    $('#resumen-total').text('$' + f.importe_total);
    if (f.fecha_pago && f.importe_pago) {
      $('#resumen-saldo').show();
      $('#resumen-saldo-monto').text('$' + f.importe_pago);
    } else {
      $('#resumen-saldo').hide();
    }
  }

  // -- Confirmaci&oacute;n --

  $('#btn-confirm-anular').on('click', function () {
    if (!facturaActual) { alert('Seleccion&aacute; una factura primero.'); return; }
    var motivo = $('#motivo').val().trim();
    if (!motivo) { alert('Ingres&aacute; el motivo de anulaci&oacute;n.'); return; }

    var pagadaMsg = facturaActual.fecha_pago
      ? '\nLa factura estaba PAGADA. Se crear&aacute; un saldo a favor por $' + facturaActual.importe_pago + '.'
      : '';

    if (confirm(
      '\u26A0\uFE0F CONFIRMACI\u00D3N DE ANULACI\u00D3N\n\n' +
      'Factura: ' + facturaActual.letra + ' ' + facturaActual.nro_punto_vta + '-' + facturaActual.nro_factura + '\n' +
      'Cliente: ' + facturaActual.cliente_nombre + '\n' +
      'Total: $' + facturaActual.importe_total + '\n\n' +
      'Se emitir\u00E1 una NOTA DE CR\u00C9DITO en AFIP por el total.\n' +
      'La factura quedar\u00E1 ANULADA en el sistema.' +
      pagadaMsg +
      '\n\n\u00BFContinuar?'
    )) {
      $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando anulaci&oacute;n...');
      $('#form-anular').submit();
    }
  });

});
</script>

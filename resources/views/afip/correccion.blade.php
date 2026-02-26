@include('layout_admin.header')

<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3><i class="fa fa-wrench"></i> Herramienta de Corrección AFIP</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="alert alert-danger">
      <strong><i class="fa fa-exclamation-circle"></i> USO EXCLUSIVO PARA CORRECCIÓN DE ERRORES EN PRODUCCIÓN.</strong>
      Las acciones de esta pantalla emiten comprobantes en AFIP pero <strong>NO modifican</strong> ningún registro de facturación del sistema.
    </div>

    {{-- Mensajes flash del servidor --}}
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

    {{-- ===== NC MANUAL (sin factura en sistema) ===== --}}
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="x_panel">
          <div class="x_title" style="cursor:pointer;" id="toggle-nc-manual">
            <h2>
              <i class="fa fa-exclamation-triangle text-danger"></i>
              Emitir NC para factura <strong>no registrada</strong> en el sistema
              <small class="text-muted"> — ingresá el CAE y número de la factura original</small>
            </h2>
            <div class="nav navbar-right panel_toolbox">
              <a id="icon-toggle-manual"><i class="fa fa-chevron-down"></i></a>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="x_content" id="panel-nc-manual" style="display:{{ session('tab_manual') ? 'block' : 'none' }};">
            <div class="alert alert-danger" style="margin-bottom:15px;">
              <i class="fa fa-warning"></i>
              Usá este formulario <strong>solo si la factura fue emitida en AFIP pero no figura en la base de datos</strong>.
              La NC se guardará sin referencia a ninguna factura del sistema.
            </div>
            <form method="POST" action="/admin/afip-correccion/nc-manual" class="form-horizontal form-label-left" id="form-nc-manual">
              {{ csrf_field() }}

              <div class="form-group">
                <label class="control-label col-md-3">Punto de venta / Letra <span class="required">*</span></label>
                <div class="col-md-3">
                  <select name="talonario_id" id="manual-talonario" class="form-control" required>
                    <option value="">— Seleccionar —</option>
                    @foreach($talonarios as $tal)
                      <option value="{{ $tal->id }}" {{ old('talonario_id') == $tal->id ? 'selected' : '' }}>
                        Factura {{ $tal->letra }} — Pto. Vta. {{ str_pad($tal->nro_punto_vta, 4, '0', STR_PAD_LEFT) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">N° de factura original (en AFIP) <span class="required">*</span></label>
                <div class="col-md-3">
                  <input type="number" name="nro_factura_orig" class="form-control"
                         value="{{ old('nro_factura_orig') }}" placeholder="Ej: 142" min="1" required>
                  <p class="help-block">Número del comprobante que se quiere revertir.</p>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">DNI / CUIT del receptor</label>
                <div class="col-md-3">
                  <input type="text" name="dni" class="form-control" id="manual-dni"
                         value="{{ old('dni') }}" placeholder="Ej: 28123456" maxlength="11" autocomplete="off">
                  <p class="help-block">Requerido para NC tipo A (CUIT 11 dígitos) o para montos ≥ $10.000.000.</p>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">Importe total (con IVA 21%) <span class="required">*</span></label>
                <div class="col-md-3">
                  <div class="input-group">
                    <span class="input-group-addon">$</span>
                    <input type="text" name="importe" id="manual-importe" class="form-control"
                           value="{{ old('importe') }}" placeholder="Ej: 1500.00" required>
                  </div>
                  <p class="help-block">Neto: <strong id="manual-neto">—</strong> &nbsp; IVA: <strong id="manual-iva">—</strong></p>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">Motivo <span class="required">*</span></label>
                <div class="col-md-6">
                  <textarea name="motivo" class="form-control" rows="3"
                            placeholder="Describí por qué se emite esta NC (ej: factura emitida con DNI y monto invertidos, no guardada en el sistema)"
                            required maxlength="500">{{ old('motivo') }}</textarea>
                </div>
              </div>

              <div class="ln_solid"></div>
              <div class="form-group">
                <div class="col-md-6 col-md-offset-3">
                  <button type="button" class="btn btn-danger btn-lg" id="btn-confirm-nc-manual">
                    <i class="fa fa-paper-plane"></i> Emitir NC Manual en AFIP
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== FACTURA MANUAL (sin registrar) ===== --}}
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="x_panel">
          <div class="x_title" style="cursor:pointer;" id="toggle-factura-manual">
            <h2>
              <i class="fa fa-exclamation-triangle text-warning"></i>
              Emitir Factura <strong>sin registrar</strong> en el sistema
              <small class="text-muted"> — para cierre de balances</small>
            </h2>
            <div class="nav navbar-right panel_toolbox">
              <a id="icon-toggle-factura-manual"><i class="fa fa-chevron-down"></i></a>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="x_content" id="panel-factura-manual" style="display:{{ session('tab_factura_manual') ? 'block' : 'none' }};">
            <div class="alert alert-warning" style="margin-bottom:15px;">
              <i class="fa fa-warning"></i>
              Emite una factura en AFIP <strong>sin crear ningún registro en la base de datos</strong>.
              Solo usá esto para ajustes de balance o comprobantes de cierre.
            </div>
            <form method="POST" action="/admin/afip-correccion/factura-manual" class="form-horizontal form-label-left" id="form-factura-manual">
              {{ csrf_field() }}

              <div class="form-group">
                <label class="control-label col-md-3">Punto de venta / Letra <span class="required">*</span></label>
                <div class="col-md-3">
                  <select name="talonario_id" id="fmanual-talonario" class="form-control" required>
                    <option value="">— Seleccionar —</option>
                    @foreach($talonarios as $tal)
                      <option value="{{ $tal->id }}" {{ old('talonario_id') == $tal->id && session('tab_factura_manual') ? 'selected' : '' }}>
                        Factura {{ $tal->letra }} — Pto. Vta. {{ str_pad($tal->nro_punto_vta, 4, '0', STR_PAD_LEFT) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">DNI / CUIT del receptor</label>
                <div class="col-md-3">
                  <input type="text" name="dni" class="form-control" id="fmanual-dni"
                         value="{{ session('tab_factura_manual') ? old('dni') : '' }}"
                         placeholder="Ej: 28123456" maxlength="11" autocomplete="off">
                  <p class="help-block">Requerido para Factura A (CUIT 11 dígitos) o montos ≥ $10.000.000.</p>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">Importe total (con IVA 21%) <span class="required">*</span></label>
                <div class="col-md-3">
                  <div class="input-group">
                    <span class="input-group-addon">$</span>
                    <input type="text" name="importe" id="fmanual-importe" class="form-control"
                           value="{{ session('tab_factura_manual') ? old('importe') : '' }}"
                           placeholder="Ej: 1500.00" required>
                  </div>
                  <p class="help-block">Neto: <strong id="fmanual-neto">—</strong> &nbsp; IVA: <strong id="fmanual-iva">—</strong></p>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3">Motivo / descripción <span class="required">*</span></label>
                <div class="col-md-6">
                  <textarea name="motivo" class="form-control" rows="3"
                            placeholder="Ej: Factura de cierre de balance — ajuste periodo X"
                            required maxlength="500">{{ session('tab_factura_manual') ? old('motivo') : '' }}</textarea>
                </div>
              </div>

              <div class="ln_solid"></div>
              <div class="form-group">
                <div class="col-md-6 col-md-offset-3">
                  <button type="button" class="btn btn-warning btn-lg" id="btn-confirm-factura-manual">
                    <i class="fa fa-paper-plane"></i> Emitir Factura Manual en AFIP
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== BUSCADOR ===== --}}
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="x_panel">
          <div class="x_title">
            <h2><i class="fa fa-search"></i> Buscar factura</h2>
            <div class="clearfix"></div>
          </div>
          <div class="x_content" style="padding-bottom:10px;">
            <div class="input-group input-group-lg">
              <input type="text" id="buscador" class="form-control"
                     placeholder="Nombre del cliente, DNI/CUIT, número de factura o CAE..."
                     autocomplete="off">
              <span class="input-group-addon" style="cursor:pointer;" id="btn-limpiar" title="Limpiar selección">
                <i class="fa fa-times"></i>
              </span>
            </div>
            <div id="dropdown-resultados" style="display:none; position:absolute; z-index:9999; width:calc(100% - 30px); background:#fff; border:1px solid #ddd; border-top:none; border-radius:0 0 4px 4px; max-height:320px; overflow-y:auto; box-shadow:0 4px 8px rgba(0,0,0,.15);">
            </div>
            <small class="text-muted" id="hint-buscar">Escribí al menos 2 caracteres para buscar.</small>
          </div>
        </div>
      </div>
    </div>

    {{-- ===== PANEL DE DETALLE (oculto hasta que se seleccione una factura) ===== --}}
    <div id="panel-detalle" style="display:none;">

      {{-- Encabezado con info de la factura seleccionada --}}
      <div class="row">
        <div class="col-md-12">
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

              {{-- Historial de correcciones --}}
              <div id="bloque-historial" style="display:none;">
                <hr>
                <h4><i class="fa fa-history"></i> Correcciones ya emitidas para esta factura</h4>
                <table class="table table-striped table-condensed">
                  <thead>
                    <tr>
                      <th>Tipo</th><th>Número</th><th>Fecha</th>
                      <th class="text-right">Importe</th><th>CAE</th>
                      <th>Vto. CAE</th><th>Motivo</th>
                    </tr>
                  </thead>
                  <tbody id="tabla-historial"></tbody>
                </table>
              </div>

              {{-- ===== TABS DE ACCIÓN ===== --}}
              <hr>
              <h4><i class="fa fa-bolt"></i> Acciones en AFIP</h4>

              <ul class="nav nav-tabs" id="tabs-accion">
                <li id="tab-li-nc" class="{{ session('tab') != 'factura' ? 'active' : '' }}">
                  <a href="#tab-nc" data-toggle="tab">
                    <i class="fa fa-minus-circle text-warning"></i> Emitir Nota de Crédito
                  </a>
                </li>
                <li id="tab-li-factura" class="{{ session('tab') == 'factura' ? 'active' : '' }}">
                  <a href="#tab-factura" data-toggle="tab">
                    <i class="fa fa-plus-circle text-info"></i> Emitir Factura Correctiva
                  </a>
                </li>
              </ul>

              <div class="tab-content" style="padding-top:20px;">

                {{-- TAB NC --}}
                <div class="tab-pane {{ session('tab') != 'factura' ? 'active' : '' }}" id="tab-nc">
                  <div class="alert alert-warning">
                    <i class="fa fa-info-circle"></i>
                    Emite una <strong>Nota de Crédito</strong> asociada a la factura seleccionada en AFIP.
                    <strong>No modifica la factura ni el monto a cobrar en el sistema.</strong>
                  </div>
                  <form id="form-nc" method="POST" class="form-horizontal form-label-left" action="">
                    {{ csrf_field() }}
                    <div class="form-group">
                      <label class="control-label col-md-3">DNI del receptor <small class="text-muted">(requerido si monto ≥ $10.000.000)</small></label>
                      <div class="col-md-3">
                        <input type="text" name="dni" id="nc-dni" class="form-control"
                               value="{{ session('tab') != 'factura' ? old('dni') : '' }}"
                               placeholder="Ej: 28123456" maxlength="11" autocomplete="off">
                        <p class="help-block">Dejá vacío para emitir como Consumidor Final.</p>
                      </div>
                    </div>
                    <div class="form-group {{ $errors->has('importe') && session('tab') != 'factura' ? 'has-error' : '' }}">
                      <label class="control-label col-md-3">Importe total (con IVA 21%) <span class="required">*</span></label>
                      <div class="col-md-3">
                        <div class="input-group">
                          <span class="input-group-addon">$</span>
                          <input type="text" name="importe" id="nc-importe" class="form-control"
                                 value="{{ session('tab') != 'factura' ? old('importe') : '' }}"
                                 placeholder="Ej: 1500.00" required>
                        </div>
                        <p class="help-block">Neto: <strong id="nc-neto">—</strong> &nbsp; IVA: <strong id="nc-iva">—</strong></p>
                        @if($errors->has('importe') && session('tab') != 'factura')
                          <span class="text-danger">{{ $errors->first('importe') }}</span>
                        @endif
                      </div>
                    </div>
                    <div class="form-group {{ $errors->has('motivo') && session('tab') != 'factura' ? 'has-error' : '' }}">
                      <label class="control-label col-md-3">Motivo <span class="required">*</span></label>
                      <div class="col-md-6">
                        <textarea name="motivo" class="form-control" rows="3"
                                  placeholder="Describí el error a corregir"
                                  required maxlength="500">{{ session('tab') != 'factura' ? old('motivo') : '' }}</textarea>
                        @if($errors->has('motivo') && session('tab') != 'factura')
                          <span class="text-danger">{{ $errors->first('motivo') }}</span>
                        @endif
                      </div>
                    </div>
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                        <button type="button" class="btn btn-warning btn-lg" id="btn-confirm-nc">
                          <i class="fa fa-paper-plane"></i> Emitir Nota de Crédito en AFIP
                        </button>
                      </div>
                    </div>
                  </form>
                </div>

                {{-- TAB FACTURA --}}
                <div class="tab-pane {{ session('tab') == 'factura' ? 'active' : '' }}" id="tab-factura">
                  <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    Emite una <strong>Factura</strong> en AFIP para el mismo cliente y punto de venta,
                    <strong>sin crear ningún registro de facturación en el sistema</strong>. Solo se guarda el CAE para trazabilidad.
                  </div>
                  <form id="form-factura" method="POST" class="form-horizontal form-label-left" action="">
                    {{ csrf_field() }}
                    <div class="form-group">
                      <label class="control-label col-md-3">DNI del receptor <small class="text-muted">(sobreescribe el del cliente; requerido si monto ≥ $10.000.000)</small></label>
                      <div class="col-md-3">
                        <input type="text" name="dni" id="fact-dni" class="form-control"
                               value="{{ session('tab') == 'factura' ? old('dni') : '' }}"
                               placeholder="Ej: 28123456" maxlength="11" autocomplete="off">
                        <p class="help-block" id="fact-dni-hint">Dejá vacío para usar el DNI del cliente.</p>
                      </div>
                    </div>
                    <div class="form-group {{ $errors->has('importe') && session('tab') == 'factura' ? 'has-error' : '' }}">
                      <label class="control-label col-md-3">Importe total (con IVA 21%) <span class="required">*</span></label>
                      <div class="col-md-3">
                        <div class="input-group">
                          <span class="input-group-addon">$</span>
                          <input type="text" name="importe" id="fact-importe" class="form-control"
                                 value="{{ session('tab') == 'factura' ? old('importe') : '' }}"
                                 placeholder="Ej: 1500.00" required>
                        </div>
                        <p class="help-block">Neto: <strong id="fact-neto">—</strong> &nbsp; IVA: <strong id="fact-iva">—</strong></p>
                        @if($errors->has('importe') && session('tab') == 'factura')
                          <span class="text-danger">{{ $errors->first('importe') }}</span>
                        @endif
                      </div>
                    </div>
                    <div class="form-group {{ $errors->has('motivo') && session('tab') == 'factura' ? 'has-error' : '' }}">
                      <label class="control-label col-md-3">Motivo / descripción <span class="required">*</span></label>
                      <div class="col-md-6">
                        <textarea name="motivo" class="form-control" rows="3"
                                  placeholder="Ej: Reemisión correctiva de factura con monto erróneo"
                                  required maxlength="500">{{ session('tab') == 'factura' ? old('motivo') : '' }}</textarea>
                        @if($errors->has('motivo') && session('tab') == 'factura')
                          <span class="text-danger">{{ $errors->first('motivo') }}</span>
                        @endif
                      </div>
                    </div>
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                        <button type="button" class="btn btn-info btn-lg" id="btn-confirm-factura">
                          <i class="fa fa-paper-plane"></i> Emitir Factura Correctiva en AFIP
                        </button>
                      </div>
                    </div>
                  </form>
                </div>

              </div>{{-- /tab-content --}}
            </div>{{-- /x_content --}}
          </div>{{-- /x_panel --}}
        </div>
      </div>

    </div>{{-- /panel-detalle --}}

    {{-- Estado vacío --}}
    <div id="panel-vacio" class="row">
      <div class="col-md-8 col-md-offset-2 text-center" style="padding: 40px 0; color: #aaa;">
        <i class="fa fa-search fa-4x"></i>
        <p style="margin-top:15px; font-size:16px;">Buscá una factura para comenzar</p>
      </div>
    </div>

  </div>
</div>

@include('layout_admin.footer')

<script>
$(document).ready(function () {

  var facturaActual = null;
  var searchTimeout = null;
  var factura_id_inicial = {{ $factura_id_inicial ?? 'null' }};

  // ── Búsqueda con autocomplete ────────────────────────────────────────────

  $('#buscador').on('input', function () {
    clearTimeout(searchTimeout);
    var q = $(this).val().trim();
    if (q.length < 2) {
      ocultarDropdown();
      return;
    }
    $('#hint-buscar').text('Buscando...');
    searchTimeout = setTimeout(function () {
      $.getJSON('/admin/afip-correccion/buscar', { q: q })
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
        var bgBase  = item.anulada ? '#fff8f8' : '#fff';
        var bgHover = item.anulada ? '#ffe8e8' : '#f5f5f5';
        var icon    = item.anulada
          ? '<i class="fa fa-ban" style="margin-right:6px; color:#c0392b;"></i>'
          : '<i class="fa fa-file-text-o" style="margin-right:6px; color:#888;"></i>';
        var labelHtml = item.anulada
          ? '<span style="text-decoration:line-through; color:#c0392b;">' + item.label + '</span>'
          : item.label;
        $('<div>')
          .addClass('resultado-item')
          .css({ padding: '9px 14px', cursor: 'pointer', borderBottom: '1px solid #f0f0f0', background: bgBase })
          .html(icon + labelHtml)
          .on('mouseenter', function () { $(this).css('background', bgHover); })
          .on('mouseleave', function () { $(this).css('background', bgBase); })
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

  // ── Cargar detalle de factura vía AJAX ───────────────────────────────────

  function cargarDetalle(id) {
    $('#panel-detalle').hide();
    $('#panel-vacio').hide();

    $.getJSON('/admin/afip-correccion/' + id + '/detalle')
      .done(function (f) {
        facturaActual = f;
        renderDetalle(f);
        $('#panel-detalle').show();

        // Actualizar URLs de los formularios
        $('#form-nc').attr('action', '/admin/afip-correccion/' + f.id + '/nc?factura_id=' + f.id);
        $('#form-factura').attr('action', '/admin/afip-correccion/' + f.id + '/factura?factura_id=' + f.id);
      })
      .fail(function () {
        alert('No se pudo cargar la factura. Intente de nuevo.');
        limpiarDetalle();
      });
  }

  function limpiarDetalle() {
    facturaActual = null;
    $('#panel-detalle').hide();
    $('#panel-vacio').show();
    $('#form-nc').attr('action', '');
    $('#form-factura').attr('action', '');
  }

  function renderDetalle(f) {
    // Título
    $('#titulo-factura').html(
      '<i class="fa fa-file-text-o"></i> Factura ' + f.letra + ' ' + f.nro_punto_vta + '-' + f.nro_factura +
      ' &mdash; <small>' + f.cliente_nombre + '</small>'
    );

    // Tabla datos
    var pagadoHtml = f.fecha_pago
      ? '<tr><th>Fecha pago:</th><td><span class="label label-success">' + f.fecha_pago + '</span></td></tr>'
      : '';
    var caeHtml = f.cae
      ? '<tr><th>CAE original:</th><td><code>' + f.cae + '</code></td></tr>'
      : '';
    $('#tabla-datos').html(
      '<tr><th>Tipo:</th><td>Factura ' + f.letra + '</td></tr>' +
      '<tr><th>Número:</th><td>' + f.letra + ' ' + f.nro_punto_vta + '-' + f.nro_factura + '</td></tr>' +
      '<tr><th>Período:</th><td>' + (f.periodo || '-') + '</td></tr>' +
      '<tr><th>Emisión:</th><td>' + f.fecha_emision + '</td></tr>' +
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

    // Historial
    if (f.historial && f.historial.length) {
      var histHtml = '';
      $.each(f.historial, function (i, h) {
        var badge = h.tipo === 'correccion'
          ? '<span class="label label-warning">Nota de Crédito</span>'
          : '<span class="label label-info">Factura Correctiva</span>';
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
  }

  // ── Carga automática si hay factura_id_inicial (post redirect) ───────────

  if (factura_id_inicial) {
    cargarDetalle(factura_id_inicial);
    $('#panel-vacio').hide();
  }

  // ── Cálculo automático neto + IVA ────────────────────────────────────────

  function bindCalculo(inputId, netoId, ivaId) {
    $('#' + inputId).on('input', function () {
      var total = parseFloat($(this).val().replace(',', '.'));
      if (!isNaN(total) && total > 0) {
        var neto = (total / 1.21).toFixed(2);
        var iva  = (total - parseFloat(neto)).toFixed(2);
        $('#' + netoId).text('$' + neto);
        $('#' + ivaId).text('$' + iva);
      } else {
        $('#' + netoId).text('—');
        $('#' + ivaId).text('—');
      }
    }).trigger('input');
  }
  bindCalculo('nc-importe',   'nc-neto',   'nc-iva');
  bindCalculo('fact-importe', 'fact-neto', 'fact-iva');

  // ── Confirmaciones antes de submit ──────────────────────────────────────

  $('#btn-confirm-nc').on('click', function () {
    if (!facturaActual) { alert('Seleccioná una factura primero.'); return; }
    var importe = $('#nc-importe').val().trim();
    if (!importe) { alert('Ingresá el importe.'); return; }
    if (confirm(
      '⚠️ CONFIRMACIÓN\n\n' +
      'Factura: ' + facturaActual.letra + ' ' + facturaActual.nro_punto_vta + '-' + facturaActual.nro_factura + '\n' +
      'Cliente: ' + facturaActual.cliente_nombre + '\n' +
      'Se emitirá una NOTA DE CRÉDITO en AFIP por $' + importe + '.\n' +
      'La factura original NO será modificada.\n\n¿Continuar?'
    )) {
      $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Emitiendo...');
      $('#form-nc').submit();
    }
  });

  // ── Toggle panel NC manual ───────────────────────────────────────────────

  $('#toggle-nc-manual').on('click', function () {
    var $panel = $('#panel-nc-manual');
    var $icon  = $('#icon-toggle-manual i');
    $panel.slideToggle(200, function () {
      $icon.toggleClass('fa-chevron-down fa-chevron-up');
    });
  });

  // Cálculo neto/IVA para NC manual
  bindCalculo('manual-importe', 'manual-neto', 'manual-iva');

  // Confirmación NC manual
  $('#btn-confirm-nc-manual').on('click', function () {
    var talonario = $('#manual-talonario option:selected').text().trim();
    var nroOrig   = $('[name="nro_factura_orig"]').val().trim();
    var importe   = $('#manual-importe').val().trim();
    if (!talonario || !nroOrig || !importe) { alert('Completá todos los campos obligatorios.'); return; }
    if (confirm(
      '⚠️ CONFIRMACIÓN\n\n' +
      'Comprobante: ' + talonario + '\n' +
      'N° factura original: ' + nroOrig + '\n' +
      'Se emitirá una NOTA DE CRÉDITO en AFIP por $' + importe + '.\n' +
      'NO se guardará ningún registro en el sistema.\n\n¿Continuar?'
    )) {
      $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Emitiendo...');
      $('#form-nc-manual').submit();
    }
  });

  // ── Toggle + confirmación Factura manual ─────────────────────────────────

  $('#toggle-factura-manual').on('click', function () {
    var $panel = $('#panel-factura-manual');
    var $icon  = $('#icon-toggle-factura-manual i');
    $panel.slideToggle(200, function () {
      $icon.toggleClass('fa-chevron-down fa-chevron-up');
    });
  });

  bindCalculo('fmanual-importe', 'fmanual-neto', 'fmanual-iva');

  $('#btn-confirm-factura-manual').on('click', function () {
    var talonario = $('#fmanual-talonario option:selected').text().trim();
    var importe   = $('#fmanual-importe').val().trim();
    if (!talonario || !importe) { alert('Completá todos los campos obligatorios.'); return; }
    if (confirm(
      '⚠️ CONFIRMACIÓN\n\n' +
      'Comprobante: ' + talonario + '\n' +
      'Se emitirá una FACTURA en AFIP por $' + importe + '.\n' +
      'NO se guardará ningún registro en el sistema.\n\n¿Continuar?'
    )) {
      $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Emitiendo...');
      $('#form-factura-manual').submit();
    }
  });

  $('#btn-confirm-factura').on('click', function () {
    if (!facturaActual) { alert('Seleccioná una factura primero.'); return; }
    var importe = $('#fact-importe').val().trim();
    if (!importe) { alert('Ingresá el importe.'); return; }
    if (confirm(
      '⚠️ CONFIRMACIÓN\n\n' +
      'Factura base: ' + facturaActual.letra + ' ' + facturaActual.nro_punto_vta + '-' + facturaActual.nro_factura + '\n' +
      'Cliente: ' + facturaActual.cliente_nombre + '\n' +
      'Se emitirá una FACTURA CORRECTIVA en AFIP por $' + importe + '.\n' +
      'No se creará ningún registro de facturación en el sistema.\n\n¿Continuar?'
    )) {
      $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Emitiendo...');
      $('#form-factura').submit();
    }
  });

});
</script>

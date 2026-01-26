@include('layout_admin.header')

<div class="right_col" role="main">
    <div class="page-title">
        <div class="title_left">
            <h3>Facturas sin PDF - Control de Período</h3>
        </div>
    </div>
    <div class="clearfix"></div>

    <!-- Selector de Período -->
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Seleccionar Período</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form method="GET" action="{{ url('/admin/period/missing-pdfs') }}" class="form-inline">
                        <div class="form-group">
                            <label for="periodo">Período:</label>
                            <select name="periodo" id="periodo" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                @foreach($periodos as $per)
                                    <option value="{{ $per }}" {{ request('periodo') == $per ? 'selected' : '' }}>
                                        {{ $per }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($resumen && isset($resumen['total_facturas']))
    <!-- Resumen -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="x_panel tile">
                <div class="x_content">
                    <h4>Total Facturas</h4>
                    <h2>{{ $resumen['total_facturas'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="x_panel tile" style="background-color: #f0ad4e;">
                <div class="x_content">
                    <h4 style="color: white;">Sin PDF</h4>
                    <h2 style="color: white;">{{ $resumen['sin_pdf'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="x_panel tile" style="background-color: #5cb85c;">
                <div class="x_content">
                    <h4 style="color: white;">Con PDF</h4>
                    <h2 style="color: white;">{{ $resumen['con_pdf'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="x_panel tile">
                <div class="x_content">
                    <h4>Porcentaje Completo</h4>
                    <h2>{{ number_format($resumen['porcentaje_con_pdf'], 1) }}%</h2>
                </div>
            </div>
        </div>
    </div>

    @if(count($facturasSinPDF) > 0)
    <!-- Facturas SIN PDF -->
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Facturas SIN PDF ({{ count($facturasSinPDF) }})</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        <li>
                            <button type="button" class="btn btn-danger btn-sm" id="regenerarSoloSinPDF" style="margin-right: 5px;">
                                <i class="fa fa-file-pdf-o"></i> Regenerar Solo Sin PDF
                            </button>
                        </li>
                        <li>
                            <button type="button" class="btn btn-warning btn-sm" id="regenerarTodosSinPDF">
                                <i class="fa fa-refresh"></i> Regenerar Todas del Período
                            </button>
                        </li>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Nro Factura</th>
                                <th>Importe</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($facturasSinPDF as $factura)
                            <tr data-factura-id="{{ $factura['id'] }}">
                                <td>{{ $factura['id'] }}</td>
                                <td>
                                    {{ $factura['cliente'] }}
                                    <br>
                                    <small class="text-muted">Cliente #{{ $factura['nro_cliente'] }}</small>
                                </td>
                                <td>{{ $factura['numero_factura'] }}</td>
                                <td>${{ $factura['importe_total'] }}</td>
                                <td>
                                    @if($factura['estado_pago'] == 'Pagado')
                                        <span class="label label-success">Pagada</span>
                                    @else
                                        <span class="label label-warning">Pendiente</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-xs regenerar-pdf" data-id="{{ $factura['id'] }}">
                                        <i class="fa fa-file-pdf-o"></i> Regenerar PDF
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(count($facturasConPDF) > 0)
    <!-- Facturas CON PDF -->
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Facturas CON PDF ({{ count($facturasConPDF) }})</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Nro Factura</th>
                                <th>Importe</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($facturasConPDF as $factura)
                            <tr data-factura-id="{{ $factura['id'] }}">
                                <td>{{ $factura['id'] }}</td>
                                <td>
                                    {{ $factura['cliente'] }}
                                    <br>
                                    <small class="text-muted">Cliente #{{ $factura['nro_cliente'] }}</small>
                                </td>
                                <td>{{ $factura['numero_factura'] }}</td>
                                <td>${{ $factura['importe_total'] }}</td>
                                <td>
                                    @if($factura['estado_pago'] == 'Pagado')
                                        <span class="label label-success">Pagada</span>
                                    @else
                                        <span class="label label-warning">Pendiente</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-xs regenerar-pdf" data-id="{{ $factura['id'] }}">
                                        <i class="fa fa-refresh"></i> Re-Regenerar
                                    </button>
                                    <a href="{{ url(config('constants.folder_facturas') . 'factura-' . str_replace(['A ', 'B ', 'C '], '', $factura['numero_factura']) . '.pdf') }}" 
                                       target="_blank" 
                                       class="btn btn-info btn-xs">
                                        <i class="fa fa-eye"></i> Ver PDF
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @endif
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
$(document).ready(function() {
    
    // Configurar toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
    };

    // Regenerar PDF individual
    $('.regenerar-pdf').on('click', function(e) {
        e.preventDefault();
        
        var facturaId = $(this).data('id');
        var $btn = $(this);
        var $row = $btn.closest('tr');
        
        // Deshabilitar botón
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
        
        $.ajax({
            url: '/api/bill/regenerate-pdf',
            method: 'POST',
            data: {
                factura_id: facturaId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('PDF regenerado correctamente para factura ID: ' + facturaId);
                    
                    // Mover fila de SIN PDF a CON PDF
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    toastr.error(response.message || 'Error al regenerar PDF');
                    $btn.prop('disabled', false).html('<i class="fa fa-file-pdf-o"></i> Regenerar PDF');
                }
            },
            error: function(xhr) {
                var errorMsg = 'Error al regenerar PDF';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
                $btn.prop('disabled', false).html('<i class="fa fa-file-pdf-o"></i> Regenerar PDF');
            }
        });
    });

    // Regenerar SOLO las facturas SIN PDF
    $('#regenerarSoloSinPDF').on('click', function(e) {
        e.preventDefault();
        
        var facturaIds = [];
        $('#regenerarSoloSinPDF').closest('.x_panel').find('tbody tr[data-factura-id]').each(function() {
            var id = $(this).data('factura-id');
            if (id) {
                facturaIds.push(id);
            }
        });
        
        if (facturaIds.length === 0) {
            toastr.warning('No hay facturas sin PDF para regenerar');
            return;
        }
        
        if (!confirm('¿Está seguro de regenerar ' + facturaIds.length + ' PDFs faltantes?')) {
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
        
        $.ajax({
            url: '/api/bill/regenerate-pdf',
            method: 'POST',
            data: {
                factura_ids: facturaIds,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    var exitosos = response.summary?.successful || response.resultados?.exitosos || 0;
                    var fallidos = response.summary?.failed || response.resultados?.fallidos || 0;
                    
                    toastr.success('Proceso completado: ' + exitosos + ' exitosos, ' + fallidos + ' fallidos');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    toastr.error(response.message || 'Error al regenerar PDFs');
                    $btn.prop('disabled', false).html('<i class="fa fa-file-pdf-o"></i> Regenerar Solo Sin PDF');
                }
            },
            error: function(xhr) {
                var errorMsg = 'Error al regenerar PDFs';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
                $btn.prop('disabled', false).html('<i class="fa fa-file-pdf-o"></i> Regenerar Solo Sin PDF');
            }
        });
    });

    // Regenerar todos los PDFs del período (con y sin PDF)
    $('#regenerarTodosSinPDF').on('click', function(e) {
        e.preventDefault();
        
        var facturaIds = [];
        $('tr[data-factura-id]').each(function() {
            var id = $(this).data('factura-id');
            if (id) {
                facturaIds.push(id);
            }
        });
        
        if (facturaIds.length === 0) {
            toastr.warning('No hay facturas para regenerar');
            return;
        }
        
        if (!confirm('¿Está seguro de regenerar TODAS las ' + facturaIds.length + ' facturas del período (incluidas las que ya tienen PDF)?')) {
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
        
        $.ajax({
            url: '/api/bill/regenerate-pdf',
            method: 'POST',
            data: {
                factura_ids: facturaIds,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    var exitosos = response.summary?.successful || response.resultados?.exitosos || 0;
                    var fallidos = response.summary?.failed || response.resultados?.fallidos || 0;
                    
                    toastr.success('Proceso completado: ' + exitosos + ' exitosos, ' + fallidos + ' fallidos');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    toastr.error(response.message || 'Error al regenerar PDFs');
                    $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Regenerar Todas del Período');
                }
            },
            error: function(xhr) {
                var errorMsg = 'Error al regenerar PDFs';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
                $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Regenerar Todas del Período');
            }
        });
    });
});
</script>

@include('layout_admin.footer')

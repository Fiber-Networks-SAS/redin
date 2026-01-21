@extends('layouts.admin')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="page-title-box">
                <h4 class="page-title">Verificar PDFs del Período</h4>
                <ol class="breadcrumb">
                    <li><a href="/admin/period">Períodos</a></li>
                    <li class="active">Verificar PDFs</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if($resumen)
                <!-- Card de Resumen -->
                <div class="card">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fa fa-file-pdf-o"></i> Período: {{ $periodo }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-blue"><i class="fa fa-file"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Facturas</span>
                                        <span class="info-box-number">{{ $resumen['total_facturas'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Con PDF</span>
                                        <span class="info-box-number">{{ $resumen['con_pdf'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-red"><i class="fa fa-times-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Sin PDF</span>
                                        <span class="info-box-number">{{ $resumen['sin_pdf'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-orange"><i class="fa fa-percentage"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Cobertura</span>
                                        <span class="info-box-number">{{ $resumen['porcentaje_con_pdf'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de progreso -->
                        <div class="progress" style="height: 25px; margin-top: 20px;">
                            <div class="progress-bar bg-green" role="progressbar" 
                                 style="width: {{ $resumen['porcentaje_con_pdf'] }}%;"
                                 aria-valuenow="{{ $resumen['porcentaje_con_pdf'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span style="color: black;">{{ $resumen['porcentaje_con_pdf'] }}% con PDF</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón para regenerar todos los PDFs faltantes -->
                @if($resumen['sin_pdf'] > 0)
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12">
                            <button class="btn btn-primary" id="btn-regenerar-todos">
                                <i class="fa fa-refresh"></i> Regenerar {{ $resumen['sin_pdf'] }} PDF(s)
                            </button>
                            <div id="regenerar-resultado" style="margin-top: 15px; display: none;"></div>
                        </div>
                    </div>
                @endif

                <!-- Tabla de Facturas SIN PDF -->
                @if(count($facturasSinPDF) > 0)
                    <div class="card" style="margin-top: 20px; border-left: 4px solid #dd4b39;">
                        <div class="card-header with-border bg-red">
                            <h3 class="card-title">
                                <i class="fa fa-warning"></i> Facturas SIN PDF ({{ count($facturasSinPDF) }})
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="tabla-sin-pdf">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Factura</th>
                                            <th>Cliente</th>
                                            <th>Importe</th>
                                            <th>Emisión</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($facturasSinPDF as $factura)
                                            <tr>
                                                <td>{{ $factura['id'] }}</td>
                                                <td>
                                                    <strong>{{ $factura['numero_factura'] }}</strong>
                                                    <br>
                                                    <small class="text-muted">Nro Cliente: {{ $factura['nro_cliente'] }}</small>
                                                </td>
                                                <td>{{ $factura['cliente'] }}</td>
                                                <td class="text-right">${{ $factura['importe_total'] }}</td>
                                                <td>{{ $factura['fecha_emision'] }}</td>
                                                <td>
                                                    <span class="label {{ $factura['estado_pago'] === 'Pagado' ? 'label-success' : 'label-warning' }}">
                                                        {{ $factura['estado_pago'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-xs btn-info btn-regenerar-individual" 
                                                            data-factura-id="{{ $factura['id'] }}"
                                                            title="Regenerar PDF">
                                                        <i class="fa fa-refresh"></i> Regenerar
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tabla de Facturas CON PDF -->
                @if(count($facturasConPDF) > 0 && session('mostrar_con_pdf'))
                    <div class="card" style="margin-top: 20px; border-left: 4px solid #00a65a;">
                        <div class="card-header with-border bg-green">
                            <h3 class="card-title">
                                <i class="fa fa-check"></i> Facturas CON PDF ({{ count($facturasConPDF) }})
                                <a href="javascript:void(0)" class="toggle-con-pdf" style="margin-left: 15px; font-size: 12px;">
                                    [Ocultar]
                                </a>
                            </h3>
                        </div>
                        <div class="card-body tabla-con-pdf">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="tabla-con-pdf">
                                    <thead>
                                        <tr>
                                            <th>Factura</th>
                                            <th>Cliente</th>
                                            <th>Importe</th>
                                            <th>Emisión</th>
                                            <th>Tamaño PDF</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($facturasConPDF as $factura)
                                            <tr class="text-muted">
                                                <td>{{ $factura['numero_factura'] }}</td>
                                                <td>{{ $factura['cliente'] }}</td>
                                                <td class="text-right">${{ $factura['importe_total'] }}</td>
                                                <td>{{ $factura['fecha_emision'] }}</td>
                                                <td>{{ $factura['pdf_size'] }} KB</td>
                                                <td>
                                                    <span class="label label-success">
                                                        <i class="fa fa-check"></i> OK
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @elseif(count($facturasConPDF) > 0)
                    <div style="margin-top: 20px; text-align: center;">
                        <a href="javascript:void(0)" class="toggle-con-pdf" style="color: #3c8dbc; font-weight: bold;">
                            Mostrar {{ count($facturasConPDF) }} facturas con PDF
                        </a>
                    </div>
                @endif

            @else
                <!-- Formulario de selección de período -->
                <div class="card">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fa fa-calendar"></i> Seleccionar Período
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form method="GET" action="/admin/period/missing-pdfs">
                                    <div class="form-group">
                                        <label>Período <span class="text-danger">*</span></label>
                                        <select name="periodo" class="form-control" required>
                                            <option value="">-- Seleccionar período --</option>
                                            @foreach($periodos as $p)
                                                <option value="{{ $p }}">{{ $p }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> Verificar PDFs
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @if(count($periodos) === 0)
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>No hay períodos disponibles.</strong>
                        Primero debe crear facturas para un período.
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Script para regenerar PDFs -->
<script>
$(document).ready(function() {
    // Regenerar un PDF individual
    $('.btn-regenerar-individual').click(function() {
        var facturaId = $(this).data('factura-id');
        var btn = $(this);
        
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Regenerando...');
        
        $.ajax({
            url: '/api/bill/regenerate-pdf/' + facturaId,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('PDF regenerado exitosamente', 'Éxito');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Error al regenerar PDF', 'Error');
                    btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Regenerar');
                }
            },
            error: function(xhr) {
                var response = xhr.responseJSON;
                toastr.error(response.error || 'Error al regenerar PDF', 'Error');
                btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Regenerar');
            }
        });
    });

    // Regenerar todos los PDFs faltantes
    $('#btn-regenerar-todos').click(function() {
        var facturaIds = [];
        $('#tabla-sin-pdf tbody tr').each(function() {
            facturaIds.push($(this).find('td:first').text());
        });

        if (facturaIds.length === 0) {
            toastr.warning('No hay PDFs para regenerar', 'Aviso');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Regenerando...');
        
        $.ajax({
            url: '/api/bill/regenerate-pdf',
            method: 'POST',
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify({
                factura_ids: facturaIds
            }),
            success: function(response) {
                if (response.success) {
                    var html = '<div class="alert alert-success">' +
                        '<i class="fa fa-check-circle"></i> ' +
                        '<strong>¡Regeneración completada!</strong><br>' +
                        'Total: ' + response.summary.total_requested + ' facturas<br>' +
                        'Exitosas: ' + response.summary.successful + '<br>' +
                        'Fallidas: ' + response.summary.failed + '<br>' +
                        'Tasa de éxito: ' + response.summary.success_rate + '%<br>' +
                        'Tiempo: ' + response.summary.processing_time +
                        '</div>';
                    $('#regenerar-resultado').html(html).show();
                    
                    toastr.success('PDFs regenerados exitosamente', 'Éxito');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    var html = '<div class="alert alert-warning">' +
                        '<i class="fa fa-warning"></i> ' +
                        '<strong>Regeneración parcial completada</strong><br>' +
                        'Exitosas: ' + response.summary.successful + '<br>' +
                        'Fallidas: ' + response.summary.failed + '<br>' +
                        'Tasa de éxito: ' + response.summary.success_rate + '%' +
                        '</div>';
                    $('#regenerar-resultado').html(html).show();
                    
                    toastr.warning(response.message, 'Advertencia');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            },
            error: function(xhr) {
                var response = xhr.responseJSON;
                toastr.error(response.error || 'Error al regenerar PDFs', 'Error');
                btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Regenerar todos');
            }
        });
    });

    // Toggle tabla de facturas con PDF
    $('.toggle-con-pdf').click(function(e) {
        e.preventDefault();
        $('.tabla-con-pdf').toggle();
        
        if ($(this).text().includes('Ocultar')) {
            $(this).text('[Mostrar]');
        } else {
            $(this).text('[Ocultar]');
        }
    });

    // Si es la primera carga sin filtro, ocultar tabla de con PDF
    @if(isset($periodo) && count($facturasConPDF) > 0)
        $('.tabla-con-pdf').hide();
    @endif
});
</script>

<style>
.info-box-number {
    font-size: 2.5rem !important;
    font-weight: bold;
}

.table-responsive {
    overflow-x: auto;
}

.btn-regenerar-individual:hover {
    background-color: #0c5460 !important;
}
</style>

@endsection

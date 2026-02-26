@include('layout_admin.header')

<div class="right_col" role="main">
    <div class="page-title">
        <div class="title_left">
            <h3>Notas de Crédito — Generar PDF</h3>
        </div>
    </div>
    <div class="clearfix"></div>

    <!-- Filtros -->
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Buscar Notas de Crédito</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form method="GET" action="{{ url('/admin/notas-credito/pdf-view') }}" class="form-inline">
                        <div class="form-group" style="margin-right: 10px;">
                            <label for="periodo">Período:</label>
                            <select name="periodo" id="periodo" class="form-control">
                                <option value="">-- Todos --</option>
                                @foreach($periodos as $per)
                                    <option value="{{ $per }}" {{ $periodoSeleccionado == $per ? 'selected' : '' }}>
                                        {{ $per }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-right: 10px;">
                            <label for="cliente">Nro. Cliente:</label>
                            <input type="text" name="cliente" id="cliente" class="form-control"
                                value="{{ $clienteSearch }}" placeholder="Ej: 00123" style="width: 130px;">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($periodoSeleccionado || $clienteSearch)

    @if(count($notasCredito) === 0)
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                No se encontraron Notas de Crédito para los filtros seleccionados.
            </div>
        </div>
    </div>
    @else

    <!-- Resumen -->
    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="x_panel tile">
                <div class="x_content">
                    <h4>Total NC</h4>
                    <h2>{{ count($notasCredito) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="x_panel tile" style="background-color: #5cb85c;">
                <div class="x_content">
                    <h4 style="color: white;">Con PDF</h4>
                    <h2 style="color: white;">{{ count(array_filter($notasCredito, function($nc){ return $nc['has_pdf']; })) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="x_panel tile" style="background-color: #f0ad4e;">
                <div class="x_content">
                    <h4 style="color: white;">Sin PDF</h4>
                    <h2 style="color: white;">{{ count(array_filter($notasCredito, function($nc){ return !$nc['has_pdf']; })) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Notas de Crédito</h2>
                    <ul class="nav navbar-right panel_toolbox">
                        <li>
                            <button type="button" class="btn btn-danger btn-sm" id="generarTodas">
                                <i class="fa fa-file-pdf-o"></i> Generar todas sin PDF
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
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Período</th>
                                <th>Importe</th>
                                <th>CAE</th>
                                <th>Fecha emisión</th>
                                <th>Motivo</th>
                                <th>PDF</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notasCredito as $nc)
                            <tr data-nc-id="{{ $nc['id'] }}" data-has-pdf="{{ $nc['has_pdf'] ? '1' : '0' }}">
                                <td>{{ $nc['id'] }}</td>
                                <td><strong>{{ $nc['numero'] }}</strong></td>
                                <td>
                                    {{ $nc['cliente'] }}
                                    <br>
                                    <small class="text-muted">Cliente #{{ $nc['nro_cliente'] }}</small>
                                </td>
                                <td>{{ $nc['periodo'] }}</td>
                                <td>${{ $nc['importe_total'] }}</td>
                                <td><small>{{ $nc['cae'] }}</small></td>
                                <td>{{ $nc['fecha_emision'] }}</td>
                                <td><small>{{ $nc['motivo'] }}</small></td>
                                <td>
                                    @if($nc['has_pdf'])
                                        <span class="label label-success"><i class="fa fa-check"></i> Sí</span>
                                    @else
                                        <span class="label label-warning"><i class="fa fa-times"></i> No</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    <button class="btn btn-primary btn-xs generar-pdf" data-id="{{ $nc['id'] }}">
                                        @if($nc['has_pdf'])
                                            <i class="fa fa-refresh"></i> Regenerar
                                        @else
                                            <i class="fa fa-file-pdf-o"></i> Generar PDF
                                        @endif
                                    </button>
                                    @if($nc['has_pdf'])
                                    <a href="{{ url(config('constants.folder_notas_credito_pdf') . 'nc-' . $nc['filename'] . '.pdf') }}"
                                       target="_blank"
                                       class="btn btn-info btn-xs">
                                        <i class="fa fa-eye"></i> Ver
                                    </a>
                                    @endif
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
$(document).ready(function () {

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: "5000"
    };

    // Generar PDF individual
    $(document).on('click', '.generar-pdf', function () {
        var ncId = $(this).data('id');
        var $btn = $(this);
        var $row = $btn.closest('tr');

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generando...');

        $.ajax({
            url: '/api/nota-credito/generate-pdf',
            method: 'POST',
            data: {
                nc_id: ncId,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.success) {
                    toastr.success('PDF generado: ' + response.data.numero);

                    // Actualizar estado de la fila sin recargar
                    $row.attr('data-has-pdf', '1');
                    $row.find('td:nth-child(9)').html('<span class="label label-success"><i class="fa fa-check"></i> Sí</span>');

                    var url = response.data.public_url;
                    var filename = response.data.filename;
                    var verBtn = '<a href="' + url + '" target="_blank" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Ver</a>';

                    $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Regenerar');
                    $btn.after(' ' + verBtn);
                } else {
                    toastr.error(response.message || 'Error al generar PDF');
                    $btn.prop('disabled', false).html('<i class="fa fa-file-pdf-o"></i> Generar PDF');
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error al generar PDF';
                toastr.error(msg);
                $btn.prop('disabled', false).html('<i class="fa fa-file-pdf-o"></i> Generar PDF');
            }
        });
    });

    // Generar todas sin PDF
    $('#generarTodas').on('click', function () {
        var ids = [];
        $('tr[data-has-pdf="0"]').each(function () {
            ids.push($(this).data('nc-id'));
        });

        if (ids.length === 0) {
            toastr.info('Todas las NC ya tienen PDF.');
            return;
        }

        if (!confirm('¿Generar PDF para ' + ids.length + ' nota(s) de crédito sin PDF?')) {
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');

        var exitosos = 0, fallidos = 0, total = ids.length;

        function procesarSiguiente(index) {
            if (index >= ids.length) {
                toastr.success('Proceso completado: ' + exitosos + ' exitosos, ' + fallidos + ' fallidos de ' + total);
                $btn.prop('disabled', false).html('<i class="fa fa-file-pdf-o"></i> Generar todas sin PDF');
                setTimeout(function () { location.reload(); }, 2000);
                return;
            }

            $.ajax({
                url: '/api/nota-credito/generate-pdf',
                method: 'POST',
                data: { nc_id: ids[index], _token: '{{ csrf_token() }}' },
                success: function (response) {
                    if (response.success) { exitosos++; } else { fallidos++; }
                    procesarSiguiente(index + 1);
                },
                error: function () {
                    fallidos++;
                    procesarSiguiente(index + 1);
                }
            });
        }

        procesarSiguiente(0);
    });

});
</script>

@include('layout_admin.footer')

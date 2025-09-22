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
            <h2>Listado de Bonificaciones</h2>
            <span class="nav navbar-right">
            	<a href="/admin/bonificaciones/create" class="btn btn-info btn-xs"><i class="fa fa-plus-square-o"></i> Nueva Bonificación</a>
            </span>
            <div class="clearfix"></div>
          </div>

          @if (session('success'))
              <div class="panel panel-success">
                  <div class="panel-heading">
                      <i class="fa fa-check"></i> {{ session('success') }}
                  </div>
              </div>
          @endif

          @if (session('error'))
              <div class="panel panel-danger">
                  <div class="panel-heading">
                      <i class="fa fa-times"></i> {{ session('error') }}
                  </div>
              </div>
          @endif

          <div class="x_content">
            <table id="datatable" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Servicio</th>
                  <th>% Bonificación</th>
                  <th>Períodos</th>
                  <th>Fecha Inicio</th>
                  <th>Fecha Fin</th>
                  <th>Estado</th>
                  <th>Vigente</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                @foreach($bonificaciones as $bonificacion)
                <tr>
                  <td>{{ $bonificacion->id }}</td>
                  <td>{{ $bonificacion->servicio->nombre }}</td>
                  <td>{{ number_format($bonificacion->porcentaje_bonificacion, 2) }}%</td>
                  <td>{{ $bonificacion->periodos_bonificacion }} meses</td>
                  <td>{{ \Carbon\Carbon::parse($bonificacion->fecha_inicio)->format('d/m/Y') }}</td>
                  <td>{{ \Carbon\Carbon::parse($bonificacion->fecha_inicio)->addMonths($bonificacion->periodos_bonificacion)->format('d/m/Y') }}</td>
                  <td>
                    @if($bonificacion->activo)
                      <span class="label label-success">Activo</span>
                    @else
                      <span class="label label-danger">Inactivo</span>
                    @endif
                  </td>
                  <td>
                    @if($bonificacion->esVigente())
                      <span class="label label-info">Vigente</span>
                    @else
                      <span class="label label-warning">Expirado</span>
                    @endif
                  </td>
                  <td>
                    <div class="btn-group">
                      <a href="/admin/bonificaciones/view/{{ $bonificacion->id }}" class="btn btn-info btn-xs" title="Ver">
                        <i class="fa fa-eye"></i>
                      </a>
                      <a href="/admin/bonificaciones/edit/{{ $bonificacion->id }}" class="btn btn-warning btn-xs" title="Editar">
                        <i class="fa fa-edit"></i>
                      </a>
                      <form method="POST" action="/admin/bonificaciones/toggle/{{ $bonificacion->id }}" style="display: inline;">
                        {{ csrf_field() }}

                        <button type="submit" class="btn btn-{{ $bonificacion->activo ? 'danger' : 'success' }} btn-xs" 
                                title="{{ $bonificacion->activo ? 'Desactivar' : 'Activar' }}"
                                onclick="return confirm('¿Está seguro de {{ $bonificacion->activo ? 'desactivar' : 'activar' }} esta bonificación?')">
                          <i class="fa fa-{{ $bonificacion->activo ? 'times' : 'check' }}"></i>
                        </button>
                      </form>
                      <form method="POST" action="/admin/bonificaciones/delete/{{ $bonificacion->id }}" style="display: inline;">
                        {{ csrf_field() }}

                        <button type="submit" class="btn btn-danger btn-xs" title="Eliminar"
                                onclick="return confirm('¿Está seguro de eliminar esta bonificación? Esta acción no se puede deshacer.')">
                          <i class="fa fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>

            <!-- Paginación -->
            <div class="row">
              <div class="col-md-12">
                {{ $bonificaciones->links() }}
              </div>
            </div>
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
    // Initialize datatable without pagination since we're using Laravel pagination
    $('#datatable').DataTable({
      "paging": false,
      "searching": true,
      "ordering": true,
      "info": false
    });
  });
</script>
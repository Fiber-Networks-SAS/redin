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
            <h2>Detalle de Bonificación #{{ $bonificacion->id }}</h2>
            <span class="nav navbar-right">
              <a href="/admin/bonificaciones/edit/{{ $bonificacion->id }}" class="btn btn-warning btn-xs">
                <i class="fa fa-edit"></i> Editar
              </a>
              <a href="/admin/bonificaciones" class="btn btn-default btn-xs">
                <i class="fa fa-arrow-left"></i> Volver al Listado
              </a>
            </span>
            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            <div class="row">
              <div class="col-md-6">
                <div class="panel panel-info">
                  <div class="panel-heading">
                    <h3 class="panel-title">Información General</h3>
                  </div>
                  <div class="panel-body">
                    <table class="table table-condensed">
                      <tbody>
                        <tr>
                          <td><strong>ID:</strong></td>
                          <td>{{ $bonificacion->id }}</td>
                        </tr>
                        <tr>
                          <td><strong>Servicio:</strong></td>
                          <td>{{ $bonificacion->servicio->nombre }}</td>
                        </tr>
                        <tr>
                          <td><strong>Precio del Servicio:</strong></td>
                          <td>${{ number_format($bonificacion->servicio->abono_mensual, 2) }}</td>
                        </tr>
                        <tr>
                          <td><strong>Porcentaje de Bonificación:</strong></td>
                          <td>{{ number_format($bonificacion->porcentaje_bonificacion, 2) }}%</td>
                        </tr>
                        <tr>
                          <td><strong>Períodos:</strong></td>
                          <td>{{ $bonificacion->periodos_bonificacion }} meses</td>
                        </tr>
                        <tr>
                          <td><strong>Estado:</strong></td>
                          <td>
                            @if($bonificacion->activo)
                              <span class="label label-success">Activo</span>
                            @else
                              <span class="label label-danger">Inactivo</span>
                            @endif
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Vigencia:</strong></td>
                          <td>
                            @if($bonificacion->esVigente())
                              <span class="label label-info">Vigente</span>
                            @else
                              <span class="label label-warning">Expirado</span>
                            @endif
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="panel panel-success">
                  <div class="panel-heading">
                    <h3 class="panel-title">Información de Fechas</h3>
                  </div>
                  <div class="panel-body">
                    <table class="table table-condensed">
                      <tbody>
                        <tr>
                          <td><strong>Fecha de Inicio:</strong></td>
                          <td>{{ $bonificacion->fecha_inicio->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                          <td><strong>Fecha de Fin:</strong></td>
                          <td>{{ $bonificacion->fecha_inicio->copy()->addMonths($bonificacion->periodos_bonificacion)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                          <td><strong>Días Restantes:</strong></td>
                          <td>
                            @php
                              $fechaFin = $bonificacion->fecha_inicio->copy()->addMonths($bonificacion->periodos_bonificacion);
                              $diasRestantes = \Carbon\Carbon::now()->diffInDays($fechaFin, false);
                            @endphp
                            @if($diasRestantes > 0)
                              <span class="text-success">{{ $diasRestantes }} días</span>
                            @else
                              <span class="text-danger">Expirado hace {{ abs($diasRestantes) }} días</span>
                            @endif
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Creado:</strong></td>
                          <td>{{ $bonificacion->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                          <td><strong>Última Actualización:</strong></td>
                          <td>{{ $bonificacion->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-warning">
                  <div class="panel-heading">
                    <h3 class="panel-title">Cálculo de Bonificación</h3>
                  </div>
                  <div class="panel-body">
                    <div class="row">
                      <div class="col-md-3">
                        <div class="well text-center">
                          <h4>Precio Original</h4>
                          <h3 class="text-info">${{ number_format($bonificacion->servicio->abono_mensual, 2) }}</h3>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="well text-center">
                          <h4>Descuento</h4>
                          <h3 class="text-warning">
                            -${{ number_format($bonificacion->calcularBonificacion($bonificacion->servicio->abono_mensual), 2) }}
                          </h3>
                          <small>({{ $bonificacion->porcentaje_bonificacion }}%)</small>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="well text-center">
                          <h4>Precio Final</h4>
                          <h3 class="text-success">
                            ${{ number_format($bonificacion->aplicarBonificacion($bonificacion->servicio->abono_mensual), 2) }}
                          </h3>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="well text-center">
                          <h4>Ahorro Total</h4>
                          <h3 class="text-danger">
                            ${{ number_format($bonificacion->calcularBonificacion($bonificacion->servicio->abono_mensual) * $bonificacion->periodos_bonificacion, 2) }}
                          </h3>
                          <small>({{ $bonificacion->periodos_bonificacion }} meses)</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            @if($bonificacion->descripcion)
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3 class="panel-title">Descripción</h3>
                  </div>
                  <div class="panel-body">
                    {{ $bonificacion->descripcion }}
                  </div>
                </div>
              </div>
            </div>
            @endif

            <div class="ln_solid"></div>

            <div class="form-group">
              <div class="col-md-12">
                <a href="/admin/bonificaciones/edit/{{ $bonificacion->id }}" class="btn btn-warning">
                  <i class="fa fa-edit"></i> Editar Bonificación
                </a>
                <form method="POST" action="/admin/bonificaciones/toggle/{{ $bonificacion->id }}" style="display: inline;">
                  {{ csrf_field() }}

                  <button type="submit" class="btn btn-{{ $bonificacion->activo ? 'danger' : 'success' }}" 
                          onclick="return confirm('¿Está seguro de {{ $bonificacion->activo ? 'desactivar' : 'activar' }} esta bonificación?')">
                    <i class="fa fa-{{ $bonificacion->activo ? 'times' : 'check' }}"></i> 
                    {{ $bonificacion->activo ? 'Desactivar' : 'Activar' }}
                  </button>
                </form>
                <form method="POST" action="/admin/bonificaciones/delete/{{ $bonificacion->id }}" style="display: inline;">
                  {{ csrf_field() }}

                  <button type="submit" class="btn btn-danger" 
                          onclick="return confirm('¿Está seguro de eliminar esta bonificación? Esta acción no se puede deshacer.')">
                    <i class="fa fa-trash"></i> Eliminar
                  </button>
                </form>
                <a href="/admin/bonificaciones" class="btn btn-default">
                  <i class="fa fa-arrow-left"></i> Volver al Listado
                </a>
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
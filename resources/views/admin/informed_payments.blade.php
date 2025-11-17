@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Pagos Informados</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    @if (session('status'))
        <div class="panel panel-{{session('status')}}">
            <div class="panel-heading">
                <i class="fa {{session('icon')}}"></i> {{session('message')}}
            </div>
        </div>
    @endif

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Gestión de Pagos Informados por CBU/Transferencia</h2>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">
            
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Factura</th>
                    <th>Cliente</th>
                    <th>Importe</th>
                    <th>Fecha Pago</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Fecha Informe</th>
                    <th>Acciones</th>
                  </tr>
{{-- Eliminar string JS suelto, todo el JS está dentro del bloque <script> y @push('scripts') --}}
                </thead>
                <tbody>
                  @forelse($pagos_informados as $pago)
                  <tr class="
                    @if($pago->estado == 'pendiente') warning
                    @elseif($pago->estado == 'aprobado') success
                    @else danger
                    @endif
                  ">
                    <td>
                      <strong>{{$pago->factura->talonario->letra}} {{$pago->factura->talonario->nro_punto_vta}} - {{$pago->factura->nro_factura}}</strong><br>
                      <small>Período: {{$pago->factura->periodo}}</small>
                    </td>
                    <td>
                      <strong>{{$pago->factura->cliente->firstname}} {{$pago->factura->cliente->lastname}}</strong><br>
                      <small>Cliente: {{$pago->factura->nro_cliente}}</small>
                    </td>
                    <td>
                      <strong>${{number_format($pago->importe_informado, 2, ',', '.')}}</strong>
                    </td>
                    <td>
                      {{$pago->fecha_pago_informado_formatted}}
                    </td>
                    <td>
                      <span class="label label-info">{{$pago->tipo_transferencia_texto}}</span>
                    </td>
                    <td>
                      @if($pago->estado == 'pendiente')
                        <span class="label label-warning">
                          <i class="fa fa-clock-o"></i> {{$pago->estado_texto}}
                        </span>
                      @elseif($pago->estado == 'aprobado')
                        <span class="label label-success">
                          <i class="fa fa-check"></i> {{$pago->estado_texto}}
                        </span>
                      @else
                        <span class="label label-danger">
                          <i class="fa fa-times"></i> {{$pago->estado_texto}}
                        </span>
                      @endif
                    </td>
                    <td>
                      {{$pago->created_at->format('d/m/Y H:i')}}
                    </td>
                    <td>
                      <a href="{{ url('/admin/payments/informed/' . $pago->id) }}" class="btn btn-info btn-xs" title="Ver Detalle">
                        <i class="fa fa-eye"></i> Ver
                      </a>
                      
                      @if($pago->estado == 'pendiente')
                        <button class="btn btn-success btn-xs btn-aprobar-pago" data-pago-id="{{$pago->id}}" title="Aprobar Pago">
                          <i class="fa fa-check"></i> Aprobar
                        </button>
                        <button class="btn btn-danger btn-xs btn-rechazar-pago" data-pago-id="{{$pago->id}}" title="Rechazar Pago">
                          <i class="fa fa-times"></i> Rechazar
                        </button>
                      @endif
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="8" class="text-center">No hay pagos informados</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal para aprobar pago -->
<div class="modal fade" id="modalAprobar" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Aprobar Pago Informado</h4>
      </div>
      <div class="modal-body">
        <p>¿Está seguro que desea aprobar este pago? Esta acción marcará la factura como pagada.</p>
        <div class="form-group">
          <label>Observaciones (opcional):</label>
          <textarea class="form-control" id="observaciones_aprobar" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnConfirmarAprobar">
          <i class="fa fa-check"></i> Aprobar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para rechazar pago -->
<div class="modal fade" id="modalRechazar" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Rechazar Pago Informado</h4>
      </div>
      <div class="modal-body">
        <p>¿Está seguro que desea rechazar este pago?</p>
        <div class="form-group">
          <label>Motivo del rechazo <span class="text-danger">*</span>:</label>
          <textarea class="form-control" id="observaciones_rechazar" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarRechazar">
          <i class="fa fa-times"></i> Rechazar
        </button>
      </div>
    </div>
  </div>
</div>

@include('layout_admin.footer')

{{-- Script personalizado justo antes de </body> --}}
@push('scripts')
<script src="{{ asset('_admin/js/admin.payments.informed.js') }}" defer onerror="console.error('failed to load admin.payments.informed.js');"></script>
@endpush
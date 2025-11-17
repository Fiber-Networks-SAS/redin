@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Pago Informado - Detalle</h3>
      </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">

              <!-- Información del Pago Informado -->
              <div class="col-md-6">
                <div class="well">
                  <h4><i class="fa fa-bank"></i> Información del Pago</h4>
                  <table class="table table-condensed">
                    <tr>
                      <td><strong>Importe Informado:</strong></td>
                      <td class="text-success"><strong>${{number_format($pago_informado->importe_informado, 2, ',', '.')}}</strong></td>
                    </tr>
                    <tr>
                      <td><strong>Fecha del Pago:</strong></td>
                      <td>{{$pago_informado->fecha_pago_informado_formatted}}</td>
                    </tr>
                    <tr>
                      <td><strong>Tipo:</strong></td>
                      <td>{{$pago_informado->tipo_transferencia_texto}}</td>
                    </tr>
                    <tr>
                      <td><strong>Banco:</strong></td>
                      <td>{{$pago_informado->banco_origen}}</td>
                    </tr>
                    <tr>
                      <td><strong>Nro. Operación:</strong></td>
                      <td><code>{{$pago_informado->numero_operacion}}</code></td>
                    </tr>
                    <tr>
                      <td><strong>CBU Origen:</strong></td>
                      <td>{{$pago_informado->cbu_origen ?: 'No informado'}}</td>
                    </tr>
                    <tr>
                      <td><strong>Titular:</strong></td>
                      <td>{{$pago_informado->titular_cuenta}}</td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
    </div>

            <div class="row">
              <!-- Estado y Validación -->
              <div class="col-md-6">
                <div class="well">
                  <h4><i class="fa fa-check-circle"></i> Estado y Validación</h4>
                  <table class="table table-condensed">
                    <tr>
                      <td><strong>Estado:</strong></td>
                      <td>
                        @if($pago_informado->estado == 'pendiente')
                          <span class="label label-warning">
                            <i class="fa fa-clock-o"></i> {{$pago_informado->estado_texto}}
                          </span>
                        @elseif($pago_informado->estado == 'aprobado')
                          <span class="label label-success">
                            <i class="fa fa-check"></i> {{$pago_informado->estado_texto}}
                          </span>
                        @else
                          <span class="label label-danger">
                            <i class="fa fa-times"></i> {{$pago_informado->estado_texto}}
                          </span>
                        @endif
                      </td>
                    </tr>
                    <tr>
                      <td><strong>Informado por:</strong></td>
                      <td>{{$pago_informado->usuario->firstname}} {{$pago_informado->usuario->lastname}}</td>
                    </tr>
                    <tr>
                      <td><strong>Fecha Informe:</strong></td>
                      <td>{{$pago_informado->created_at->format('d/m/Y H:i')}}</td>
                    </tr>
                    @if($pago_informado->validado_por)
                    <tr>
                      <td><strong>Validado por:</strong></td>
                      <td>{{$pago_informado->validadoPor->firstname}} {{$pago_informado->validadoPor->lastname}}</td>
                    </tr>
                    <tr>
                      <td><strong>Fecha Validación:</strong></td>
                      <td>{{$pago_informado->fecha_validacion_formatted}}</td>
                    </tr>
                    @endif
                    @if($pago_informado->observaciones)
                    <tr>
                      <td><strong>Observaciones:</strong></td>
                      <td>{{$pago_informado->observaciones}}</td>
                    </tr>
                    @endif
                  </table>
                </div>
              </div>

              <!-- Comprobante -->
              <div class="col-md-6">
                <div class="well">
                  <h4><i class="fa fa-file"></i> Comprobante</h4>
                  @if($pago_informado->comprobante_path)
                    <div class="text-center">
                      @php
                        $extension = pathinfo($pago_informado->comprobante_path, PATHINFO_EXTENSION);
                      @endphp
                      
                      @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                        <img src="{{ asset('storage/' . $pago_informado->comprobante_path) }}" 
                             class="img-responsive" style="max-width: 100%; max-height: 300px;">
                      @else
                        <div class="alert alert-info">
                          <i class="fa fa-file-pdf-o fa-3x"></i><br>
                          Archivo PDF adjunto
                        </div>
                      @endif
                      
                      <br>
                      <a href="{{ asset('storage/' . $pago_informado->comprobante_path) }}" 
                         target="_blank" class="btn btn-info">
                        <i class="fa fa-download"></i> Descargar Comprobante
                      </a>
                    </div>
                  @else
                    <div class="alert alert-warning text-center">
                      <i class="fa fa-warning"></i><br>
                      No se adjuntó comprobante
                    </div>
                  @endif
                </div>
              </div>
            </div>

            @if($pago_informado->estado == 'pendiente')
            <div class="row">
              <div class="col-md-12">
                <div class="text-center">
                  <button class="btn btn-success btn-lg btn-aprobar-pago" data-pago-id="{{$pago_informado->id}}">
                    <i class="fa fa-check"></i> Aprobar Pago
                  </button>
                  <button class="btn btn-danger btn-lg btn-rechazar-pago" data-pago-id="{{$pago_informado->id}}">
                    <i class="fa fa-times"></i> Rechazar Pago
                  </button>
                </div>
              </div>
            </div>
            @endif
            
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
@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Facturas</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    @if (!empty($factura))
      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>
                Nota de Crédito por Corrección &mdash;
                Factura {{ $factura->talonario->letra }} {{ $factura->talonario->nro_punto_vta }} - {{ $factura->nro_factura }}
                <small>{{ $factura->cliente->firstname . ' ' . $factura->cliente->lastname }}</small>
              </h2>
              <span class="nav navbar-right">
                <a href="{{ url()->previous() }}" class="btn btn-primary btn-xs"><i class="fa fa-arrow-left"></i> Volver</a>
              </span>
              <div class="clearfix"></div>
            </div>

            @if (session('status'))
              <div class="panel panel-{{ session('status') }}">
                <div class="panel-heading">
                  <i class="fa {{ session('icon') }}"></i> {{ session('message') }}
                </div>
              </div>
            @endif

            <div class="x_content">

              <div class="alert alert-warning">
                <strong><i class="fa fa-exclamation-triangle"></i> Aviso:</strong>
                Esta acción emite una Nota de Crédito en AFIP <strong>únicamente para corregir un error de facturación</strong>.
                <br>La factura original <strong>NO será modificada</strong> y el monto a pagar por el cliente <strong>NO cambia</strong>.
              </div>

              <!-- Información de la factura -->
              <div class="row">
                <div class="col-md-6">
                  <h4>Información de la Factura</h4>
                  <table class="table table-striped">
                    <tr>
                      <th>Número:</th>
                      <td>{{ $factura->talonario->letra }} {{ $factura->talonario->nro_punto_vta }} - {{ $factura->nro_factura }}</td>
                    </tr>
                    <tr>
                      <th>Cliente:</th>
                      <td>{{ $factura->cliente->firstname . ' ' . $factura->cliente->lastname }}</td>
                    </tr>
                    <tr>
                      <th>Fecha de Emisión:</th>
                      <td>{{ $factura->fecha_emision }}</td>
                    </tr>
                    <tr>
                      <th>Período:</th>
                      <td>{{ $factura->periodo }}</td>
                    </tr>
                    <tr>
                      <th>Subtotal:</th>
                      <td>${{ number_format($factura->importe_subtotal, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                      <th>Total:</th>
                      <td><strong>${{ number_format($factura->importe_total, 2, ',', '.') }}</strong></td>
                    </tr>
                    @if ($factura->fecha_pago)
                    <tr>
                      <th>Fecha de Pago:</th>
                      <td>{{ $factura->fecha_pago }}</td>
                    </tr>
                    @endif
                  </table>
                </div>
                <div class="col-md-6">
                  <h4>Detalles de Servicios</h4>
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Servicio</th>
                        <th>Importe</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($detalles as $detalle)
                      <tr>
                        <td>{{ $detalle->servicio->nombre ?? 'N/A' }}</td>
                        <td>${{ number_format($detalle->importe, 2, ',', '.') }}</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- NC de corrección ya emitidas para esta factura -->
              @if ($notasCredito->count() > 0)
              <div class="row">
                <div class="col-md-12">
                  <h4>Notas de Crédito de Corrección ya Emitidas</h4>
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Número NC</th>
                        <th>Fecha</th>
                        <th>Importe Total</th>
                        <th>CAE</th>
                        <th>Vencimiento CAE</th>
                        <th>Motivo</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($notasCredito as $nc)
                      <tr>
                        <td>{{ $nc->talonario->letra }} {{ $nc->talonario->nro_punto_vta }}-{{ str_pad($nc->nro_nota_credito, 8, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ \Carbon\Carbon::parse($nc->fecha_emision)->format('d/m/Y') }}</td>
                        <td>${{ number_format($nc->importe_total, 2, ',', '.') }}</td>
                        <td><code>{{ $nc->cae }}</code></td>
                        <td>{{ $nc->cae_vto ? \Carbon\Carbon::parse($nc->cae_vto)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $nc->motivo }}</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
              @endif

              <!-- Formulario para emitir nueva NC de corrección -->
              <form action="/admin/period/bill-corregir/{{ $factura->id }}" method="POST" class="form-horizontal form-label-left">
                {{ csrf_field() }}

                <h4>Emitir Nueva Nota de Crédito de Corrección</h4>

                <div class="form-group {{ $errors->has('importe') ? 'has-error' : '' }}">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="importe">
                    Importe (total con IVA) <span class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-12">
                    <input type="text" id="importe" name="importe"
                           class="form-control col-md-7 col-xs-12"
                           value="{{ old('importe') }}"
                           placeholder="Ej: 1500.00"
                           required autofocus>
                    @if ($errors->has('importe'))
                      <ul class="parsley-errors-list filled">
                        <li class="parsley">{{ $errors->first('importe') }}</li>
                      </ul>
                    @endif
                    <p class="help-block">Ingresá el importe total incluyendo IVA (21%).</p>
                  </div>
                </div>

                <div class="form-group {{ $errors->has('motivo') ? 'has-error' : '' }}">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="motivo">
                    Motivo de la corrección <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea id="motivo" name="motivo" class="form-control" rows="3"
                              placeholder="Describí el error de facturación a corregir"
                              required maxlength="500">{{ old('motivo') }}</textarea>
                    @if ($errors->has('motivo'))
                      <ul class="parsley-errors-list filled">
                        <li class="parsley">{{ $errors->first('motivo') }}</li>
                      </ul>
                    @endif
                  </div>
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <a href="{{ url()->previous() }}" class="btn btn-primary">Cancelar</a>
                    <button type="submit" class="btn btn-warning"
                            onclick="return confirm('¿Confirmar emisión de Nota de Crédito de corrección en AFIP?\n\nEsta acción NO modifica la factura ni el monto a pagar.')">
                      <i class="fa fa-paper-plane"></i> Emitir NC en AFIP
                    </button>
                  </div>
                </div>

              </form>

            </div>
          </div>
        </div>
      </div>

      <div class="clearfix"></div>

    @else

      <div class="panel panel-danger">
        <div class="panel-heading">
          <i class="fa fa-frown-o"></i> Factura no encontrada.
        </div>
      </div>

    @endif
  </div>
</div>
<!-- /page content -->

@include('layout_admin.footer')

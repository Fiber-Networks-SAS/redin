@include('layout.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Informar Pago por CBU/Transferencia</h3>
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
            <h2>Informar Pago de Factura {{$factura->talonario->letra}} {{$factura->talonario->nro_punto_vta}} - {{$factura->nro_factura}}</h2>
            <span class="nav navbar-right">
              <a href="{{ url('/my-invoice/detail/' . $factura->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"></i> Volver</a>
            </span>
            <div class="clearfix"></div>
          </div>
          
          <div class="x_content">
            
            <div class="row">
              <div class="col-md-6">
                <div class="well">
                  <h4><i class="fa fa-file-text"></i> Datos de la Factura</h4>
                  <p><strong>Cliente:</strong> {{$factura->cliente->firstname}} {{$factura->cliente->lastname}}</p>
                  <p><strong>Número:</strong> {{$factura->talonario->letra}} {{$factura->talonario->nro_punto_vta}} - {{$factura->nro_factura}}</p>
                  <p><strong>Período:</strong> {{$factura->periodo}}</p>
                  <p><strong>Importe a Pagar:</strong> <span class="text-success"><strong>${{number_format($importe_correspondiente, 2, ',', '.')}}</strong></span></p>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="well">
                  <h4><i class="fa fa-info-circle"></i> Instrucciones</h4>
                  <ul>
                    <li>Complete todos los datos del pago realizado</li>
                    <li><strong>Adjunte el comprobante de transferencia (obligatorio)</strong></li>
                    <li>Su pago será validado en 24-48 horas</li>
                    <li>Recibirá una notificación del resultado</li>
                  </ul>
                </div>
              </div>
            </div>

            <form method="POST" action="{{ url('/my-invoice/inform-payment/' . $factura->id) }}" enctype="multipart/form-data" class="form-horizontal form-label-left">
              {{ csrf_field() }}

              <div class="form-group {{ $errors->has('importe_informado') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="importe_informado">Importe Pagado <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="importe_informado" id="importe_informado" 
                         value="{{ old('importe_informado', number_format($importe_correspondiente, 2, ',', '.')) }}" 
                         placeholder="Ejemplo: 1.234,56" required>
                  @if ($errors->has('importe_informado')) <p class="help-block">{{ $errors->first('importe_informado') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('fecha_pago_informado') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="fecha_pago_informado">Fecha del Pago <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="fecha_pago_informado" id="fecha_pago_informado" 
                         value="{{ old('fecha_pago_informado') }}" 
                         placeholder="dd/mm/aaaa" required>
                  @if ($errors->has('fecha_pago_informado')) <p class="help-block">{{ $errors->first('fecha_pago_informado') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('tipo_transferencia') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tipo_transferencia">Tipo de Operación <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <select class="form-control" name="tipo_transferencia" id="tipo_transferencia" required>
                    <option value="">Seleccione una opción</option>
                    <option value="CBU" {{ old('tipo_transferencia') == 'CBU' ? 'selected' : '' }}>Transferencia CBU</option>
                    <option value="TRANSFERENCIA" {{ old('tipo_transferencia') == 'TRANSFERENCIA' ? 'selected' : '' }}>Transferencia Bancaria</option>
                    <option value="DEPOSITO" {{ old('tipo_transferencia') == 'DEPOSITO' ? 'selected' : '' }}>Depósito Bancario</option>
                  </select>
                  @if ($errors->has('tipo_transferencia')) <p class="help-block">{{ $errors->first('tipo_transferencia') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('banco_origen') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="banco_origen">Banco de Origen <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="banco_origen" id="banco_origen" 
                         value="{{ old('banco_origen') }}" 
                         placeholder="Ejemplo: Banco Nación, Banco Santander, etc." required>
                  @if ($errors->has('banco_origen')) <p class="help-block">{{ $errors->first('banco_origen') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('numero_operacion') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="numero_operacion">Número de Operación <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="numero_operacion" id="numero_operacion" 
                         value="{{ old('numero_operacion') }}" 
                         placeholder="Número de transacción/operación" required>
                  @if ($errors->has('numero_operacion')) <p class="help-block">{{ $errors->first('numero_operacion') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('cbu_origen') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="cbu_origen">CBU de Origen</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="cbu_origen" id="cbu_origen" 
                         value="{{ old('cbu_origen') }}" 
                         placeholder="CBU de la cuenta desde donde se realizó la transferencia">
                  @if ($errors->has('cbu_origen')) <p class="help-block">{{ $errors->first('cbu_origen') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('titular_cuenta') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="titular_cuenta">Titular de la Cuenta <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="titular_cuenta" id="titular_cuenta" 
                         value="{{ old('titular_cuenta') }}" 
                         placeholder="Nombre del titular de la cuenta origen" required>
                  @if ($errors->has('titular_cuenta')) <p class="help-block">{{ $errors->first('titular_cuenta') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('comprobante') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="comprobante">Comprobante <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="file" class="form-control" name="comprobante" id="comprobante" 
                        accept="image/*,.pdf" required>
                      <p class="help-block">Obligatorio. Formatos: JPG, PNG, PDF. Máximo 2MB.</p>
                  @if ($errors->has('comprobante')) <p class="help-block">{{ $errors->first('comprobante') }}</p> @endif
                </div>
              </div>

              <div class="ln_solid"></div>
              <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-send"></i> Informar Pago
                  </button>
                  <a href="{{ url('/my-invoice/detail/' . $factura->id) }}" class="btn btn-warning">
                    <i class="fa fa-times"></i> Cancelar
                  </a>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Date picker -->
<script>
$(document).ready(function() {
    // Configurar datepicker para la fecha de pago
    $('#fecha_pago_informado').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true,
        endDate: new Date(),
        language: 'es'
    });
});
</script>

@include('layout.footer')
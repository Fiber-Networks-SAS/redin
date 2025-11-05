@include('layout.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Confirmar Pago de Factura</h3>
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
            <h2>Confirmar Pago de Factura</h2>
            <span class="nav navbar-right">
              <a href="{{ url('/my-invoice/detail/' . $factura->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-arrow-left"></i> Volver</a>
            </span>
            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            @if($tipoVencimiento == 'segundo' || $tipoVencimiento == 'tercer')
            <div class="alert alert-warning">
              <strong>¡Atención!</strong> Esta factura tiene intereses por pago fuera de término.
            </div>
            @endif

            <div class="row">
              <div class="col-md-6">
                <h4>Factura N° {{ $factura->talonario->nro_punto_vta }}-{{ str_pad($factura->nro_factura, 8, '0', STR_PAD_LEFT) }}</h4>
                <p><strong>Cliente:</strong> {{ $factura->cliente->firstname }} {{ $factura->cliente->lastname }}</p>
                <p><strong>Fecha de Emisión:</strong> {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}</p>
                <p><strong>Primer Vencimiento:</strong> {{ \Carbon\Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y') }}</p>
                <p><strong>Segundo Vencimiento:</strong> {{ \Carbon\Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y') }}</p>
              </div>
              <div class="col-md-6">
                <h4>Importes</h4>
                <p><strong>Importe Original:</strong> ${{ number_format($factura->importe_total, 2, ',', '.') }}</p>
                @if($tipoVencimiento == 'segundo')
                  <p><strong>Intereses ({{ $tasaInteres }}% diario):</strong> ${{ number_format($intereses, 2, ',', '.') }}</p>
                  <p><strong>Días de Mora:</strong> {{ $diasMora }}</p>
                @elseif($tipoVencimiento == 'tercer')
                  <p><strong>Intereses ({{ $tasaInteres }}% diario):</strong> ${{ number_format($intereses, 2, ',', '.') }}</p>
                  <p><strong>Días de Mora:</strong> {{ $diasMora }}</p>
                @endif
                <hr>
                <h4><strong>Total a Pagar: ${{ number_format($importeTotal, 2, ',', '.') }}</strong></h4>
              </div>
            </div>

            @if($tipoVencimiento == 'segundo' || $tipoVencimiento == 'tercer')
              <div class="alert alert-info">
                <p><strong>Nota:</strong> Los intereses se calculan diariamente desde la fecha de vencimiento hasta la fecha de pago.</p>
                <p>Si paga antes del tercer vencimiento, se aplicará la tasa del segundo vencimiento.</p>
              </div>
            @endif

            <div class="text-center">
              <form action="/my-invoice/process-payment/{{$factura->id}}" method="POST" style="display: inline;">
                {{ csrf_field() }}
                <button type="submit" class="btn btn-success btn-lg">
                  <i class="fa fa-credit-card"></i> Pagar con MercadoPago
                </button>
              </form>
              <a href="{{ url('/my-invoice/detail/' . $factura->id) }}" class="btn btn-default btn-lg">
                <i class="fa fa-arrow-left"></i> Volver
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- /page content -->

@include('layout.footer')
<?php

use TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\PaymentController;
use App\Contracts\PaymentServiceInterface;
use App\Factura;
use App\PaymentPreference;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Stub para PaymentServiceInterface (nombre único para evitar colisión)
 */
class NullFacturaPaymentStub implements PaymentServiceInterface
{
    public function createPaymentPreference(array $data) { return []; }
    public function getPaymentStatus($paymentId) { return []; }
    public function cancelPaymentPreference($preferenceId) { return true; }
    public function processWebhook(array $data) { return []; }
    public function getPaymentInfo($paymentId) { return []; }
    public function generarNotaDebitoPorIntereses($factura, $diferencia) { return true; }
}

/**
 * Tests: Fix de acceso a $factura->id cuando factura es null en processPaymentApproval
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/PaymentControllerNullFacturaTest.php
 */
class PaymentControllerNullFacturaTest extends TestCase
{
    use DatabaseTransactions;

    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new PaymentController(new NullFacturaPaymentStub());
    }

    /**
     * Test: Pago aprobado con factura asociada funciona correctamente
     */
    public function testPagoAprobadoConFacturaAsociada()
    {
        $factura = $this->crearFacturaPrueba();
        $preference = $this->crearPreferenciaPrueba($factura->id);

        $stub = new class($preference->external_reference) extends NullFacturaPaymentStub {
            private $ref;
            public function __construct($ref) { $this->ref = $ref; }
            public function processWebhook(array $data) {
                return [
                    'success' => true,
                    'type' => 'payment',
                    'payment_info' => [
                        'success' => true,
                        'payment_id' => '999',
                        'status' => 'approved',
                        'external_reference' => $this->ref,
                        'transaction_amount' => 1000.00
                    ]
                ];
            }
        };

        $controller = new PaymentController($stub);
        $request = $this->crearRequestWebhook([
            'type' => 'payment',
            'data' => ['id' => '999']
        ]);

        $response = $controller->mercadoPagoWebhook($request);

        $this->assertEquals(200, $response->getStatusCode());

        // markAsPaid setea status = 'approved'
        $updatedPref = PaymentPreference::find($preference->id);
        $this->assertEquals('approved', $updatedPref->status);
    }

    /**
     * Test: Pago aprobado SIN factura asociada no explota
     * (Antes tiraba: "Trying to get property 'id' of non-object")
     */
    public function testPagoAprobadoSinFacturaNoExplota()
    {
        // Crear factura real, luego preferencia, luego borrar la factura
        $factura = $this->crearFacturaPrueba();
        $preference = $this->crearPreferenciaPrueba($factura->id);

        // Borrar la factura (soft delete) para simular factura inexistente
        $factura->delete();

        $stub = new class($preference->external_reference) extends NullFacturaPaymentStub {
            private $ref;
            public function __construct($ref) { $this->ref = $ref; }
            public function processWebhook(array $data) {
                return [
                    'success' => true,
                    'type' => 'payment',
                    'payment_info' => [
                        'success' => true,
                        'payment_id' => '999',
                        'status' => 'approved',
                        'external_reference' => $this->ref,
                        'transaction_amount' => 1000.00
                    ]
                ];
            }
        };

        $controller = new PaymentController($stub);
        $request = $this->crearRequestWebhook([
            'type' => 'payment',
            'data' => ['id' => '999']
        ]);

        // Esto NO debe lanzar excepción
        $response = $controller->mercadoPagoWebhook($request);

        $this->assertEquals(200, $response->getStatusCode());

        // La preferencia igual se marca como pagada
        $updatedPref = PaymentPreference::find($preference->id);
        $this->assertEquals('approved', $updatedPref->status);
    }

    /**
     * Test: Pago con external_reference vacío lanza excepción controlada
     */
    public function testPagoSinExternalReferenceLanzaError()
    {
        $stub = new class extends NullFacturaPaymentStub {
            public function processWebhook(array $data) {
                return [
                    'success' => true,
                    'type' => 'payment',
                    'payment_info' => [
                        'success' => true,
                        'payment_id' => '999',
                        'status' => 'approved',
                        'external_reference' => '',
                        'transaction_amount' => 1000.00
                    ]
                ];
            }
        };

        $controller = new PaymentController($stub);
        $request = $this->crearRequestWebhook([
            'type' => 'payment',
            'data' => ['id' => '999']
        ]);

        $response = $controller->mercadoPagoWebhook($request);
        $this->assertEquals(500, $response->getStatusCode());
    }

    // ==================== HELPERS ====================

    protected function crearFacturaPrueba()
    {
        $talonario = \App\Talonario::first();
        if (!$talonario) {
            $talonario = new \App\Talonario();
            $talonario->letra = 'B';
            $talonario->nro_punto_vta = 1;
            $talonario->save();
        }

        $user = new \App\User();
        $user->firstname = 'Test';
        $user->lastname = 'NullFactura';
        $user->email = 'nullfactura' . uniqid() . '@test.com';
        $user->dni = '20304050607';
        $user->nro_cliente = rand(1000, 9999);
        $user->password = bcrypt('password');
        $user->save();

        $factura = new Factura();
        $factura->user_id = $user->id;
        $factura->nro_cliente = $user->nro_cliente;
        $factura->talonario_id = $talonario->id;
        $factura->nro_factura = rand(1000, 9999);
        $factura->periodo = date('m/Y');
        $factura->fecha_emision = date('Y-m-d H:i:s');
        $factura->importe_subtotal = 826.45;
        $factura->importe_subtotal_iva = 173.55;
        $factura->importe_bonificacion = 0;
        $factura->importe_bonificacion_iva = 0;
        $factura->importe_iva = 173.55;
        $factura->importe_total = 1000.00;
        $factura->primer_vto_fecha = Carbon::now()->addDays(10)->format('Y-m-d H:i:s');
        $factura->primer_vto_codigo = '001';
        $factura->segundo_vto_fecha = Carbon::now()->addDays(20)->format('Y-m-d H:i:s');
        $factura->segundo_vto_tasa = 3.0;
        $factura->segundo_vto_importe = 1030.00;
        $factura->segundo_vto_codigo = '002';
        $factura->save();

        return $factura;
    }

    protected function crearPreferenciaPrueba($facturaId)
    {
        $preference = new PaymentPreference();
        $preference->factura_id = $facturaId;
        $preference->vencimiento_tipo = 'primer';
        $preference->external_reference = 'test_nullfact_' . uniqid();
        $preference->status = 'pending';
        $preference->amount = 1000.00;
        $preference->save();

        return $preference;
    }

    protected function crearRequestWebhook($data)
    {
        $request = new \Illuminate\Http\Request();
        $request->merge($data);
        $request->setMethod('POST');
        $request->headers->set('User-Agent', 'MercadoPago Webhook/1.0');
        return $request;
    }
}

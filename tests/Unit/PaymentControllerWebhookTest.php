<?php

use TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\PaymentController;
use App\Contracts\PaymentServiceInterface;
use App\Factura;
use App\PaymentPreference;
use Carbon\Carbon;

/**
 * Stub simple para PaymentServiceInterface
 */
class PaymentServiceStub implements PaymentServiceInterface
{
    public function createPaymentPreference(array $data) { return []; }
    public function getPaymentStatus($paymentId) { return []; }
    public function cancelPaymentPreference($preferenceId) { return true; }
    public function processWebhook(array $data) { return []; }
    public function getPaymentInfo($paymentId) { return []; }
    public function generarNotaDebitoPorIntereses($factura, $diferencia) { return true; }
}

/**
 * Tests unitarios para PaymentController - Webhooks de MercadoPago
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/PaymentControllerWebhookTest.php
 */
class PaymentControllerWebhookTest extends TestCase
{
    use DatabaseTransactions;

    protected $controller;
    protected $paymentServiceStub;

    public function setUp()
    {
        parent::setUp();

        // Usar stub simple en lugar de mock
        $this->paymentServiceStub = new PaymentServiceStub();
        $this->controller = new PaymentController($this->paymentServiceStub);
    }

    /**
     * Test: Webhook con pago aprobado - estructura correcta
     */
    public function testWebhookPagoAprobado()
    {
        // Crear factura de prueba
        $factura = $this->crearFacturaPrueba();

        // Crear preferencia de pago de prueba
        $paymentPreference = $this->crearPaymentPreferencePrueba($factura->id);

        // Stub que simula respuesta correcta del servicio de pago
        $stubAprobado = new class($paymentPreference->external_reference) extends PaymentServiceStub {
            private $expectedRef;

            public function __construct($expectedRef) {
                $this->expectedRef = $expectedRef;
            }

            public function processWebhook(array $data) {
                return [
                    'success' => true,
                    'type' => 'payment',
                    'payment_info' => [
                        'success' => true,
                        'payment_id' => '123456789',
                        'status' => 'approved',
                        'external_reference' => $this->expectedRef,
                        'transaction_amount' => 1000.00
                    ]
                ];
            }
        };

        $controller = new PaymentController($stubAprobado);

        // Datos del webhook con estructura correcta (type en lugar de action)
        $webhookData = [
            'type' => 'payment',
            'data' => [
                'id' => '123456789'
            ]
        ];

        // Crear request del webhook con User-Agent correcto
        $request = $this->crearRequestWebhook($webhookData);

        // Ejecutar el webhook
        $response = $controller->mercadoPagoWebhook($request);

        // Verificar respuesta exitosa
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['status' => 'ok'], json_decode($response->getContent(), true));

        // Verificar que la preferencia de pago se actualizó en BD
        $preferenceActualizada = PaymentPreference::find($paymentPreference->id);
        $this->assertEquals('approved', $preferenceActualizada->status);
        $this->assertEquals('approved', $preferenceActualizada->payment_status);
        $this->assertNotNull($preferenceActualizada->paid_at);

        // Verificar que la factura se marcó como pagada
        $facturaActualizada = Factura::find($factura->id);
        $this->assertNotNull($facturaActualizada->fecha_pago);
        $this->assertEquals(1000.00, $facturaActualizada->importe_pago);
        $this->assertEquals(4, $facturaActualizada->forma_pago); // MercadoPago
    }

    /**
     * Test: Webhook con pago rechazado
     */
    public function testWebhookPagoRechazado()
    {
        // Crear factura de prueba
        $factura = $this->crearFacturaPrueba();

        // Crear preferencia de pago de prueba
        $paymentPreference = $this->crearPaymentPreferencePrueba($factura->id);

        // Stub que simula respuesta de pago rechazado
        $stubRechazado = new class($paymentPreference->external_reference) extends PaymentServiceStub {
            private $expectedRef;

            public function __construct($expectedRef) {
                $this->expectedRef = $expectedRef;
            }

            public function processWebhook(array $data) {
                return [
                    'success' => true,
                    'type' => 'payment',
                    'payment_info' => [
                        'success' => true,
                        'payment_id' => '987654321',
                        'status' => 'rejected',
                        'external_reference' => $this->expectedRef,
                        'status_detail' => 'cc_rejected_insufficient_amount'
                    ]
                ];
            }
        };

        $controller = new PaymentController($stubRechazado);

        // Datos del webhook
        $webhookData = [
            'type' => 'payment',
            'data' => [
                'id' => '987654321'
            ]
        ];

        $request = $this->crearRequestWebhook($webhookData);
        $response = $controller->mercadoPagoWebhook($request);

        // Verificar respuesta exitosa
        $this->assertEquals(200, $response->getStatusCode());

        // Verificar que la preferencia de pago se marcó como rechazada
        $preferenceActualizada = PaymentPreference::find($paymentPreference->id);
        $this->assertEquals('rejected', $preferenceActualizada->status);

        // Verificar que la factura sigue pendiente
        $facturaActualizada = Factura::find($factura->id);
        $this->assertNull($facturaActualizada->fecha_pago);
    }

    /**
     * Test: Webhook con datos inválidos
     */
    public function testWebhookDatosInvalidos()
    {
        // Crear controller con stub básico
        $controller = new PaymentController($this->paymentServiceStub);

        // Datos del webhook inválidos (sin type) pero con User-Agent válido
        $webhookData = [
            'data' => [
                'id' => '123' // Agregar ID para pasar validación de User-Agent
            ]
            // Sin 'type' - esto debería causar error 400
        ];

        $request = $this->crearRequestWebhook($webhookData);
        $response = $controller->mercadoPagoWebhook($request);

        // Debe retornar error 400 por estructura inválida
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test: Webhook sin User-Agent de MercadoPago (debe ser rechazado)
     */
    public function testWebhookSinUserAgentMercadoPago()
    {
        // Datos del webhook válidos pero sin User-Agent correcto
        $webhookData = [
            'type' => 'payment',
            'data' => [
                'id' => '123456789'
            ]
        ];

        $request = new \Illuminate\Http\Request();
        $request->merge($webhookData);
        $request->setMethod('POST');
        // Sin User-Agent de MercadoPago

        $response = $this->controller->mercadoPagoWebhook($request);

        // Debe retornar 401 por no provenir de MercadoPago
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test: Procesamiento de pago con intereses (genera nota de débito)
     */
    public function testProcesamientoPagoConIntereses()
    {
        // Asegurar que existe configuración de intereses
        $interes = \App\Interes::first();
        if (!$interes) {
            $interes = new \App\Interes();
            $interes->id = 1;
            $interes->tercer_vto_tasa = 0.1; // 10% diario
            $interes->save();
        }

        // Crear factura vencida (pago después del segundo vencimiento)
        $factura = $this->crearFacturaPrueba();
        $factura->primer_vto_fecha = Carbon::now()->subDays(30)->format('Y-m-d H:i:s');
        $factura->segundo_vto_fecha = Carbon::now()->subDays(20)->format('Y-m-d H:i:s');
        $factura->save();

        // Crear preferencia de pago
        $paymentPreference = $this->crearPaymentPreferencePrueba($factura->id);

        // Calcular importe esperado para verificar
        $controller = new PaymentController($this->paymentServiceStub);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('calcularImporteCorrespondiente');
        $method->setAccessible(true);
        $importeEsperado = $method->invoke($controller, $factura, date('Y-m-d'));

        echo "Importe esperado: {$importeEsperado}, Importe factura: {$factura->importe_total}\n";
        $diferencia = round($importeEsperado - $factura->importe_total, 2);
        echo "Diferencia calculada: {$diferencia}\n";

        // Stub que simula pago aprobado con intereses
        $stubConIntereses = new class($paymentPreference->external_reference) extends PaymentServiceStub {
            private $expectedRef;

            public function __construct($expectedRef) {
                $this->expectedRef = $expectedRef;
            }

            public function processWebhook(array $data) {
                return [
                    'success' => true,
                    'type' => 'payment',
                    'payment_info' => [
                        'success' => true,
                        'payment_id' => '123456789',
                        'status' => 'approved',
                        'external_reference' => $this->expectedRef,
                        'transaction_amount' => 1050.00 // Monto con intereses
                    ]
                ];
            }
        };

        $controller = new PaymentController($stubConIntereses);

        // Ejecutar webhook
        $webhookData = [
            'type' => 'payment',
            'data' => [
                'id' => '123456789'
            ]
        ];

        $request = $this->crearRequestWebhook($webhookData);
        $response = $controller->mercadoPagoWebhook($request);

        // Verificar respuesta exitosa
        $this->assertEquals(200, $response->getStatusCode());

        // Verificar que la factura se marcó como pagada
        $facturaActualizada = Factura::find($factura->id);
        $this->assertNotNull($facturaActualizada->fecha_pago);
        $this->assertEquals(1050.00, $facturaActualizada->importe_pago);

        // Verificar que se generó una nota de débito (diferencia > 1)
        $notasDebito = \App\NotaDebito::where('factura_id', $factura->id)->get();
        $this->assertCount(1, $notasDebito);

        $nota = $notasDebito->first();
        $this->assertGreaterThan(0, $nota->importe_total);
    }

    /**
     * Test: Procesamiento de pago sin intereses (no genera nota de débito)
     */
    public function testProcesamientoPagoSinIntereses()
    {
        // Crear factura al día
        $factura = $this->crearFacturaPrueba();
        $factura->primer_vto_fecha = Carbon::now()->addDays(5)->format('Y-m-d H:i:s');
        $factura->save();

        // Crear preferencia de pago
        $paymentPreference = $this->crearPaymentPreferencePrueba($factura->id);

        // Stub que simula pago aprobado sin intereses
        $stubSinIntereses = new class($paymentPreference->external_reference) extends PaymentServiceStub {
            private $expectedRef;

            public function __construct($expectedRef) {
                $this->expectedRef = $expectedRef;
            }

            public function processWebhook(array $data) {
                return [
                    'success' => true,
                    'type' => 'payment',
                    'payment_info' => [
                        'success' => true,
                        'payment_id' => '123456789',
                        'status' => 'approved',
                        'external_reference' => $this->expectedRef,
                        'transaction_amount' => 1000.00 // Monto exacto
                    ]
                ];
            }
        };

        $controller = new PaymentController($stubSinIntereses);

        // Ejecutar webhook
        $webhookData = [
            'type' => 'payment',
            'data' => [
                'id' => '123456789'
            ]
        ];

        $request = $this->crearRequestWebhook($webhookData);
        $response = $controller->mercadoPagoWebhook($request);

        // Verificar respuesta exitosa
        $this->assertEquals(200, $response->getStatusCode());

        // Verificar que la factura se marcó como pagada
        $facturaActualizada = Factura::find($factura->id);
        $this->assertNotNull($facturaActualizada->fecha_pago);
        $this->assertEquals(1000.00, $facturaActualizada->importe_pago);

        // Verificar que NO se generó nota de débito
        $notasDebito = \App\NotaDebito::where('factura_id', $factura->id)->get();
        $this->assertCount(0, $notasDebito);
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Crear factura de prueba
     */
    protected function crearFacturaPrueba()
    {
        // Crear talonario de prueba si no existe
        $talonario = \App\Talonario::find(1);
        if (!$talonario) {
            $talonario = new \App\Talonario();
            $talonario->id = 1;
            $talonario->letra = 'B'; // Talonario B para evitar problemas con AFIP
            $talonario->nro_punto_vta = 1;
            $talonario->save();
        }

        // Crear usuario/cliente de prueba con CUIT válido
        $user = new \App\User();
        $user->id = rand(100000, 999999);
        $user->firstname = 'Cliente';
        $user->lastname = 'Test';
        $user->email = 'test' . rand(100, 999) . '@example.com';
        $user->dni = '20304050607'; // CUIT válido de 11 dígitos
        $user->nro_cliente = rand(1000, 9999);
        $user->password = bcrypt('password');
        $user->save();

        $factura = new Factura();
        $factura->id = rand(100000, 999999);
        $factura->user_id = $user->id; // Usar el ID del usuario creado
        $factura->nro_cliente = $user->nro_cliente;
        $factura->talonario_id = $talonario->id;
        $factura->nro_factura = rand(1000, 9999);
        $factura->periodo = date('m/Y');
        $factura->fecha_emision = date('Y-m-d H:i:s');
        $factura->importe_subtotal = 826.45;
        $factura->importe_subtotal_iva = 173.55; // 21% IVA
        $factura->importe_bonificacion = 0.00;
        $factura->importe_bonificacion_iva = 0.00;
        $factura->importe_iva = 173.55;
        $factura->importe_total = 1000.00;
        $factura->primer_vto_fecha = Carbon::now()->addDays(10)->format('Y-m-d H:i:s');
        $factura->primer_vto_codigo = '001';
        $factura->segundo_vto_fecha = Carbon::now()->addDays(20)->format('Y-m-d H:i:s');
        $factura->segundo_vto_tasa = 3.0;
        $factura->segundo_vto_importe = 1030.00;
        $factura->segundo_vto_codigo = '002';
        $factura->tercer_vto_fecha = Carbon::now()->addDays(30)->format('Y-m-d H:i:s');
        $factura->tercer_vto_tasa = 0.1;
        $factura->tercer_vto_importe = 1030.00;
        $factura->tercer_vto_codigo = '003';
        $factura->save();

        return $factura;
    }

    /**
     * Crear preferencia de pago de prueba
     */
    protected function crearPaymentPreferencePrueba($facturaId)
    {
        $preference = new PaymentPreference();
        $preference->id = rand(100000, 999999);
        $preference->factura_id = $facturaId;
        $preference->vencimiento_tipo = 'primer';
        $preference->external_reference = 'test_ref_' . rand(100, 999);
        $preference->status = 'pending';
        $preference->amount = 1000.00;
        $preference->save();

        return $preference;
    }

    /**
     * Crear request de webhook simulado
     */
    protected function crearRequestWebhook($data)
    {
        // Crear un request simulado con User-Agent de MercadoPago
        $request = new \Illuminate\Http\Request();
        $request->merge($data);
        $request->setMethod('POST');
        $request->headers->set('User-Agent', 'MercadoPago Webhook/1.0');

        return $request;
    }
}
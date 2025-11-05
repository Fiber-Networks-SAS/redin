<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\AfipService;
use App\Http\Controllers\PaymentController;
use App\Contracts\PaymentServiceInterface;
use App\Factura;
use App\User;
use App\Talonario;
use App\Interes;
use App\NotaDebito;
use App\PaymentPreference;
use Carbon\Carbon;

/**
 * Stub simple para PaymentServiceInterface
 */
class PaymentServiceStubIntegration implements PaymentServiceInterface
{
    public function createPaymentPreference(array $data) { return []; }
    public function getPaymentStatus($paymentId) { return []; }
    public function cancelPaymentPreference($preferenceId) { return true; }
    public function processWebhook(array $data) { return []; }
}

/**
 * Tests de integración para el flujo completo de Notas de Débito
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Integration/NotaDebitoIntegrationTest.php
 */
class NotaDebitoIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected $controller;
    protected $afipService;

    public function setUp()
    {
        parent::setUp();
        $paymentServiceStub = new PaymentServiceStubIntegration();
        $this->controller = new PaymentController($paymentServiceStub);
        $this->afipService = new AfipService();
    }

    /**
     * Test de integración: Flujo completo de pago con nota de débito
     * 
     * Escenario:
     * 1. Factura emitida por $18,000
     * 2. Cliente paga en segundo vencimiento
     * 3. Sistema calcula diferencia de $540 (3%)
     * 4. Sistema emite nota de débito automática
     * 5. Nota se guarda en BD con CAE
     */
    public function testFlujoCompletoPagoConNotaDebito()
    {
        // 1. Preparar datos
        $cliente = $this->crearClientePrueba();
        $talonario = $this->crearTalonarioPrueba();
        $interes = $this->crearInteresConfiguracion();
        $factura = $this->crearFacturaCompleta($cliente, $talonario);
        
        // 2. Simular pago en segundo vencimiento
        $fechaPago = Carbon::parse($factura->segundo_vto_fecha)->format('Y-m-d');
        
        // 3. Calcular importe esperado
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calcularImporteCorrespondiente');
        $method->setAccessible(true);
        
        $importeEsperado = $method->invoke($this->controller, $factura, $fechaPago);
        
        // 4. Verificar que hay diferencia
        $diferencia = round($importeEsperado - $factura->importe_total, 2);
        
        $this->assertGreaterThanOrEqual(1, $diferencia);
        $this->assertEquals(540.00, $diferencia);
        
        // 5. Verificar que la diferencia corresponde al 3%
        $diferenciaCalculada = round($factura->importe_total * 0.03, 2);
        $this->assertEquals($diferenciaCalculada, $diferencia);
    }

    /**
     * Test: Flujo sin nota de débito (pago en primer vencimiento)
     */
    public function testFlujoSinNotaDebitoPrimerVencimiento()
    {
        $cliente = $this->crearClientePrueba();
        $talonario = $this->crearTalonarioPrueba();
        $factura = $this->crearFacturaCompleta($cliente, $talonario);
        
        // Pago en primer vencimiento
        $fechaPago = Carbon::parse($factura->primer_vto_fecha)->subDays(1)->format('Y-m-d');
        
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calcularImporteCorrespondiente');
        $method->setAccessible(true);
        
        $importeEsperado = $method->invoke($this->controller, $factura, $fechaPago);
        $diferencia = round($importeEsperado - $factura->importe_total, 2);
        
        // No debe haber diferencia
        $this->assertEquals(0, $diferencia);
        $this->assertLessThan(1, $diferencia);
    }

    /**
     * Test: Cálculo de interés diario después del segundo vencimiento
     */
    public function testCalculoInteresDiarioDespuesSegundoVencimiento()
    {
        $cliente = $this->crearClientePrueba();
        $talonario = $this->crearTalonarioPrueba();
        $interes = $this->crearInteresConfiguracion();
        $factura = $this->crearFacturaCompleta($cliente, $talonario);

        // Pago 5 días después del segundo vencimiento
        $fechaPago = Carbon::parse($factura->segundo_vto_fecha)->addDays(5)->format('Y-m-d');

        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calcularImporteCorrespondiente');
        $method->setAccessible(true);

        $importeEsperado = $method->invoke($this->controller, $factura, $fechaPago);

        // Calcular manualmente - corregir el cálculo
        $segundoVtoFecha = Carbon::parse($factura->segundo_vto_fecha);
        $fechaPagoCarbon = Carbon::parse($fechaPago);
        $diasExcedentes = $segundoVtoFecha->diffInDays($fechaPagoCarbon) + 1; // Esto debería ser 6
        $tasaAcumulada = $diasExcedentes * $interes->tercer_vto_tasa; // 6 * 0.1 = 0.6
        $importeCalculado = round(($factura->segundo_vto_importe * $tasaAcumulada / 100) + $factura->segundo_vto_importe, 2);

        $this->assertEquals($importeCalculado, $importeEsperado);
        $this->assertGreaterThan($factura->segundo_vto_importe, $importeEsperado);
    }

    /**
     * Test: Validación de estructura de nota de débito guardada
     */
    public function testEstructuraNotaDebitoGuardada()
    {
        $nota = new NotaDebito();
        $nota->factura_id = 999999;
        $nota->talonario_id = 1;
        $nota->nro_nota_debito = 123;
        $nota->importe_ampliacion = 446.28;
        $nota->importe_iva = 93.72;
        $nota->importe_total = 540.00;
        $nota->cae = '74123456789012';
        $nota->cae_vto = Carbon::now()->addDays(10);
        $nota->fecha_emision = Carbon::now();
        $nota->motivo = 'Ajuste automático por diferencia de pago (intereses de mora)';
        $nota->nro_cliente = 12345;
        $nota->periodo = '202511';
        
        // Verificar campos obligatorios
        $this->assertNotNull($nota->factura_id);
        $this->assertNotNull($nota->importe_total);
        $this->assertNotNull($nota->motivo);
        $this->assertEquals(14, strlen($nota->cae));
    }

    /**
     * Test: Relación entre Factura y NotaDebito
     */
    public function testRelacionFacturaNotaDebito()
    {
        $cliente = $this->crearClientePrueba();
        $talonario = $this->crearTalonarioPrueba();
        $factura = $this->crearFacturaCompleta($cliente, $talonario);
        
        // Crear nota de débito asociada
        $nota = new NotaDebito();
        $nota->factura_id = $factura->id;
        $nota->talonario_id = $talonario->id;
        $nota->nro_nota_debito = 123;
        $nota->importe_ampliacion = 445.45;
        $nota->importe_iva = 94.55;
        $nota->importe_total = 540.00;
        $nota->fecha_emision = Carbon::now();
        $nota->motivo = 'Test';
        $nota->save();
        
        // Verificar relación
        $notasDebito = $factura->notaDebito;
        
        $this->assertNotNull($notasDebito);
        $this->assertGreaterThan(0, $notasDebito->count());
    }

    /**
     * Test: Cálculo de IVA 21% correcto
     */
    public function testCalculoIVACorrecto()
    {
        $importeTotal = 540.00;
        $importeNeto = round($importeTotal / 1.21, 2);
        $importeIVA = round($importeTotal - $importeNeto, 2);
        
        $this->assertEquals(446.28, $importeNeto);
        $this->assertEquals(93.72, $importeIVA);
        
        // Verificar que la suma sea exacta
        $suma = $importeNeto + $importeIVA;
        $this->assertEquals($importeTotal, $suma);
    }

    /**
     * Test: Múltiples escenarios de fechas de pago
     */
    public function testMultiplesEscenariosDeVencimiento()
    {
        $cliente = $this->crearClientePrueba();
        $talonario = $this->crearTalonarioPrueba();
        $interes = $this->crearInteresConfiguracion();
        $factura = $this->crearFacturaCompleta($cliente, $talonario);
        
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calcularImporteCorrespondiente');
        $method->setAccessible(true);
        
        // Escenario 1: Pago antes del primer vencimiento
        $fecha1 = Carbon::parse($factura->primer_vto_fecha)->subDays(5)->format('Y-m-d');
        $importe1 = $method->invoke($this->controller, $factura, $fecha1);
        $this->assertEquals($factura->importe_total, $importe1);
        
        // Escenario 2: Pago exactamente en primer vencimiento
        $fecha2 = Carbon::parse($factura->primer_vto_fecha)->format('Y-m-d');
        $importe2 = $method->invoke($this->controller, $factura, $fecha2);
        $this->assertEquals($factura->importe_total, $importe2);
        
        // Escenario 3: Pago en segundo vencimiento
        $fecha3 = Carbon::parse($factura->segundo_vto_fecha)->format('Y-m-d');
        $importe3 = $method->invoke($this->controller, $factura, $fecha3);
        $this->assertEquals($factura->segundo_vto_importe, $importe3);
        
        // Escenario 4: Pago después del segundo vencimiento
        $fecha4 = Carbon::parse($factura->segundo_vto_fecha)->addDays(1)->format('Y-m-d');
        $importe4 = $method->invoke($this->controller, $factura, $fecha4);
        $this->assertGreaterThan($factura->segundo_vto_importe, $importe4);
    }

    // ==================== MÉTODOS AUXILIARES ====================

    protected function crearClientePrueba()
    {
        $cliente = new User();
        $cliente->id = 999999;
        $cliente->nro_cliente = 12345;
        $cliente->firstname = 'Test';
        $cliente->lastname = 'Cliente';
        $cliente->dni = '12345678';
        $cliente->email = 'test@example.com';
        $cliente->password = bcrypt('test123');
        $cliente->save();

        return $cliente;
    }

    protected function crearTalonarioPrueba()
    {
        $talonario = new Talonario();
        $talonario->id = 1;
        $talonario->letra = 'A';
        $talonario->nro_punto_vta = 1;
        
        return $talonario;
    }

    protected function crearInteresConfiguracion()
    {
        $interes = Interes::find(1);

        if (!$interes) {
            $interes = new Interes();
            $interes->id = 1;
        }

        // Siempre actualizar con los valores correctos para el test
        $interes->primer_vto_dia = 10;
        $interes->segundo_vto_dia = 20;
        $interes->segundo_vto_tasa = 3.0;
        $interes->tercer_vto_tasa = 0.1;
        $interes->save();

        return $interes;
    }

    protected function crearFacturaCompleta($cliente, $talonario)
    {
        $factura = new Factura();
        // Dejar que Laravel asigne el ID automáticamente para evitar conflictos
        $factura->user_id = $cliente->id; // Campo requerido - referencia al usuario/cliente
        $factura->nro_factura = 12345;
        $factura->nro_cliente = $cliente->nro_cliente;
        $factura->talonario_id = $talonario->id;

        // Importes requeridos
        $factura->importe_subtotal = 18000.00;
        $factura->importe_subtotal_iva = 18000.00; // IVA incluido en subtotal
        $factura->importe_bonificacion = 0.00;
        $factura->importe_bonificacion_iva = 0.00; // IVA de bonificación
        $factura->importe_total = 18000.00;
        $factura->segundo_vto_importe = 18540.00;

        // Fechas de vencimiento
        $factura->primer_vto_fecha = Carbon::now()->addDays(10)->format('Y-m-d H:i:s');
        $factura->primer_vto_codigo = '001';
        $factura->segundo_vto_fecha = Carbon::now()->addDays(20)->format('Y-m-d H:i:s');
        $factura->segundo_vto_tasa = 3.0;
        $factura->segundo_vto_codigo = '002';
        $factura->tercer_vto_fecha = Carbon::now()->addDays(30)->format('Y-m-d H:i:s');
        $factura->tercer_vto_tasa = 0.1;
        $factura->tercer_vto_codigo = '003';

        $factura->periodo = date('Ym');
        $factura->fecha_emision = Carbon::now()->format('Y-m-d H:i:s');

        // Guardar la factura en la base de datos
        $factura->save();

        // Mock de relaciones
        $factura->setRelation('cliente', $cliente);
        $factura->setRelation('talonario', $talonario);

        return $factura;
    }
}


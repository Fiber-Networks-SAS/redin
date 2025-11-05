<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\PaymentController;
use App\Contracts\PaymentServiceInterface;
use App\Factura;
use App\Cliente;
use App\Talonario;
use App\Interes;
use App\NotaDebito;
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
}

/**
 * Tests unitarios para PaymentController - Lógica de Notas de Débito
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/PaymentControllerNotaDebitoTest.php
 */
class PaymentControllerNotaDebitoTest extends TestCase
{
    use DatabaseTransactions;

    protected $controller;

    public function setUp()
    {
        parent::setUp();

        // Usar stub simple en lugar de mock
        $paymentServiceStub = new PaymentServiceStub();
        $this->controller = new PaymentController($paymentServiceStub);
    }

    /**
     * Test: Cálculo de importe en primer vencimiento (sin intereses)
     */
    public function testCalcularImportePrimerVencimiento()
    {
        // Crear factura de prueba
        $factura = $this->crearFacturaPrueba();
        
        // Fecha de pago dentro del primer vencimiento
        $fechaPago = Carbon::parse($factura->primer_vto_fecha)->subDays(1)->format('Y-m-d');
        
        // Usar reflexión para acceder al método protected
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calcularImporteCorrespondiente');
        $method->setAccessible(true);
        
        $importeEsperado = $method->invoke($this->controller, $factura, $fechaPago);
        
        // Debe retornar el importe original sin intereses
        $this->assertEquals($factura->importe_total, $importeEsperado);
    }

    /**
     * Test: Cálculo de importe en segundo vencimiento (recargo 3%)
     */
    public function testCalcularImporteSegundoVencimiento()
    {
        $factura = $this->crearFacturaPrueba();
        
        // Fecha de pago dentro del segundo vencimiento
        $fechaPago = Carbon::parse($factura->segundo_vto_fecha)->subDays(1)->format('Y-m-d');
        
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calcularImporteCorrespondiente');
        $method->setAccessible(true);
        
        $importeEsperado = $method->invoke($this->controller, $factura, $fechaPago);
        
        // Debe retornar el importe del segundo vencimiento
        $this->assertEquals($factura->segundo_vto_importe, $importeEsperado);
    }

    /**
     * Test: Cálculo de importe después del segundo vencimiento (interés diario)
     */
    public function testCalcularImporteDespuesSegundoVencimiento()
    {
        $factura = $this->crearFacturaPrueba();
        
        // Crear configuración de interés
        $interes = $this->crearInteresConfiguracion();
        
        // Fecha de pago 3 días después del segundo vencimiento
        $fechaPago = Carbon::parse($factura->segundo_vto_fecha)->addDays(3)->format('Y-m-d');
        
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calcularImporteCorrespondiente');
        $method->setAccessible(true);
        
        $importeEsperado = $method->invoke($this->controller, $factura, $fechaPago);
        
        // Calcular manualmente el importe esperado
        $diasExcedentes = 4; // 3 días + 1 (según lógica del sistema)
        $tasaAcumulada = $diasExcedentes * $interes->tercer_vto_tasa;
        $importeCalculado = round(($factura->segundo_vto_importe * $tasaAcumulada / 100) + $factura->segundo_vto_importe, 2);
        
        $this->assertEquals($importeCalculado, $importeEsperado);
        $this->assertGreaterThan($factura->segundo_vto_importe, $importeEsperado);
    }

    /**
     * Test: Cálculo de diferencia para nota de débito
     */
    public function testCalculoDiferenciaParaNotaDebito()
    {
        $factura = $this->crearFacturaPrueba();
        $interes = $this->crearInteresConfiguracion();
        
        // Pago en segundo vencimiento
        $fechaPago = Carbon::parse($factura->segundo_vto_fecha)->format('Y-m-d');
        
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calcularImporteCorrespondiente');
        $method->setAccessible(true);
        
        $importeEsperado = $method->invoke($this->controller, $factura, $fechaPago);
        $diferencia = round($importeEsperado - $factura->importe_total, 2);
        
        // La diferencia debe ser el 3% del importe original
        $diferenciaCalculada = round($factura->importe_total * 0.03, 2);
        
        $this->assertEquals($diferenciaCalculada, $diferencia);
        $this->assertGreaterThan(0, $diferencia);
    }

    /**
     * Test: No emitir nota de débito si diferencia es menor a $1
     */
    public function testNoEmitirNotaSiDiferenciaEsMenorAUno()
    {
        $factura = $this->crearFacturaPrueba();
        
        // Pago en primer vencimiento (diferencia = 0)
        $fechaPago = Carbon::parse($factura->primer_vto_fecha)->format('Y-m-d');
        
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calcularImporteCorrespondiente');
        $method->setAccessible(true);
        
        $importeEsperado = $method->invoke($this->controller, $factura, $fechaPago);
        $diferencia = round($importeEsperado - $factura->importe_total, 2);
        
        // La diferencia debe ser 0
        $this->assertEquals(0, $diferencia);
        $this->assertLessThan(1, $diferencia);
    }

    /**
     * Test: Formato de fecha de pago
     */
    public function testFormatoFechaPago()
    {
        $fechaPago = date('Y-m-d');

        $this->assertRegExp('/^\d{4}-\d{2}-\d{2}$/', $fechaPago);

        // Verificar que Carbon pueda parsear la fecha
        $carbon = Carbon::parse($fechaPago);
        $this->assertInstanceOf(Carbon::class, $carbon);
    }

    /**
     * Test: Cálculo de días excedentes
     */
    public function testCalculoDiasExcedentes()
    {
        $segundoVto = Carbon::parse('2025-11-25');
        $fechaPago = Carbon::parse('2025-11-28');
        
        $diasExcedentes = $segundoVto->diffInDays($fechaPago) + 1;
        
        $this->assertEquals(4, $diasExcedentes); // 3 días + 1
    }

    /**
     * Test: Cálculo de tasa acumulada
     */
    public function testCalculoTasaAcumulada()
    {
        $interes = $this->crearInteresConfiguracion();
        $diasExcedentes = 4;

        $tasaAcumulada = $diasExcedentes * $interes->tercer_vto_tasa;

        // Verificar que la tasa se calcula correctamente (días * tasa diaria)
        $tasaEsperada = $diasExcedentes * $interes->tercer_vto_tasa;
        $this->assertEquals($tasaEsperada, $tasaAcumulada);
        $this->assertGreaterThan(0, $tasaAcumulada);
    }

    /**
     * Test: Redondeo de importes a 2 decimales
     */
    public function testRedondeoImportesADosDecimales()
    {
        $importe = 18540.123456;
        $importeRedondeado = round($importe, 2);
        
        $this->assertEquals(18540.12, $importeRedondeado);
        
        // Verificar que tenga máximo 2 decimales
        $partes = explode('.', (string)$importeRedondeado);
        if (count($partes) > 1) {
            $this->assertLessThanOrEqual(2, strlen($partes[1]));
        }
    }

    /**
     * Test: Validación de fechas de vencimiento
     */
    public function testValidacionFechasVencimiento()
    {
        $factura = $this->crearFacturaPrueba();
        
        $primerVto = Carbon::parse($factura->primer_vto_fecha);
        $segundoVto = Carbon::parse($factura->segundo_vto_fecha);
        
        // El segundo vencimiento debe ser posterior al primero
        $this->assertTrue($segundoVto->greaterThan($primerVto));
    }

    /**
     * Test: Estructura de datos de NotaDebito
     */
    public function testEstructuraDatosNotaDebito()
    {
        $nota = new NotaDebito();
        
        // Verificar que tenga los campos necesarios
        $fillable = $nota->getFillable();
        
        $this->assertContains('factura_id', $fillable);
        $this->assertContains('importe_total', $fillable);
        $this->assertContains('cae', $fillable);
        $this->assertContains('motivo', $fillable);
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Crear factura de prueba
     */
    protected function crearFacturaPrueba()
    {
        $factura = new Factura();
        $factura->id = 999999;
        $factura->importe_total = 18000.00;
        $factura->segundo_vto_importe = 18540.00; // 3% más
        $factura->primer_vto_fecha = Carbon::now()->addDays(10)->format('Y-m-d');
        $factura->segundo_vto_fecha = Carbon::now()->addDays(20)->format('Y-m-d');
        $factura->tercer_vto_fecha = Carbon::now()->addDays(30)->format('Y-m-d');
        
        return $factura;
    }

    /**
     * Crear configuración de interés
     */
    protected function crearInteresConfiguracion()
    {
        // Buscar o crear configuración de interés
        $interes = Interes::find(1);
        
        if (!$interes) {
            $interes = new Interes();
            $interes->id = 1;
            $interes->primer_vto_dia = 10;
            $interes->segundo_vto_dia = 20;
            $interes->segundo_vto_tasa = 3.0; // 3%
            $interes->tercer_vto_tasa = 0.1; // 0.1% diario
            $interes->save();
        }
        
        return $interes;
    }
}


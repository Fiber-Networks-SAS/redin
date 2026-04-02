<?php

use TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\BillController;
use App\Factura;
use App\User;
use App\Talonario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Tests: Fix de parseo de fechas en BillController
 * Verifica que fechas en formato d/m/Y, Y-m-d, Y-m-d H:i:s y objetos Carbon
 * se manejen correctamente sin lanzar "Failed to parse time string"
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/BillDateParsingTest.php
 */
class BillDateParsingTest extends TestCase
{
    use DatabaseTransactions;

    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = app()->make(BillController::class);
    }

    // =========================================
    // TESTS: Formateo de fechas (lógica directa)
    // getBillDetail retorna una View, no el objeto factura.
    // Testeamos la lógica de formateo directamente.
    // =========================================

    /**
     * Test: Fecha en formato Y-m-d (estándar DB) se formatea a d/m/Y
     */
    public function testFechaFormatoDbSeFormatea()
    {
        $factura = new Factura();
        $factura->fecha_emision = '2026-03-15';
        $factura->primer_vto_fecha = '2026-04-10';
        $factura->segundo_vto_fecha = '2026-04-20';
        $factura->tercer_vto_fecha = '2026-05-10';

        $result = $this->aplicarFormateoFechas($factura);

        $this->assertEquals('15/03/2026', $result->fecha_emision);
        $this->assertEquals('10/04/2026', $result->primer_vto_fecha);
        $this->assertEquals('20/04/2026', $result->segundo_vto_fecha);
        $this->assertEquals('10/05/2026', $result->tercer_vto_fecha);
    }

    /**
     * Test: Fecha en formato Y-m-d H:i:s (datetime DB) se formatea a d/m/Y
     */
    public function testFechaFormatoDatetimeSeFormatea()
    {
        $factura = new Factura();
        $factura->fecha_emision = '2026-03-15 10:30:00';
        $factura->primer_vto_fecha = '2026-04-10 00:00:00';
        $factura->segundo_vto_fecha = '2026-04-20 00:00:00';
        $factura->tercer_vto_fecha = '2026-05-10 00:00:00';

        $result = $this->aplicarFormateoFechas($factura);

        $this->assertEquals('15/03/2026', $result->fecha_emision);
        $this->assertEquals('10/04/2026', $result->primer_vto_fecha);
        $this->assertEquals('20/04/2026', $result->segundo_vto_fecha);
        $this->assertEquals('10/05/2026', $result->tercer_vto_fecha);
    }

    /**
     * Test: Fecha ya en formato d/m/Y se deja como está (el fix principal)
     * Antes tiraba: "Failed to parse time string (13/02/2026)"
     */
    public function testFechaYaFormateadaNoExplota()
    {
        $factura = new Factura();
        $factura->fecha_emision = '13/02/2026';
        $factura->primer_vto_fecha = '10/03/2026';
        $factura->segundo_vto_fecha = '20/03/2026';
        $factura->tercer_vto_fecha = '30/03/2026';

        $result = $this->aplicarFormateoFechas($factura);

        $this->assertEquals('13/02/2026', $result->fecha_emision);
        $this->assertEquals('10/03/2026', $result->primer_vto_fecha);
        $this->assertEquals('20/03/2026', $result->segundo_vto_fecha);
        $this->assertEquals('30/03/2026', $result->tercer_vto_fecha);
    }

    /**
     * Test: Fechas como objetos Carbon se parsean correctamente
     * (Caso de generarFacturaIndividual que crea Carbon instances)
     */
    public function testFechasComoObjetosCarbonSeParsean()
    {
        $factura = new Factura();
        $factura->fecha_emision = Carbon::createFromFormat('d/m/Y', '15/03/2026');
        $factura->primer_vto_fecha = Carbon::createFromFormat('d/m/Y', '10/04/2026');
        $factura->segundo_vto_fecha = Carbon::createFromFormat('d/m/Y', '20/04/2026');
        $factura->tercer_vto_fecha = Carbon::createFromFormat('d/m/Y', '10/05/2026');

        $result = $this->aplicarFormateoFechas($factura);

        $this->assertEquals('15/03/2026', $result->fecha_emision);
        $this->assertEquals('10/04/2026', $result->primer_vto_fecha);
        $this->assertEquals('20/04/2026', $result->segundo_vto_fecha);
        $this->assertEquals('10/05/2026', $result->tercer_vto_fecha);
    }

    /**
     * Test: Mezcla de formatos (fecha_emision ya formateada, vtos desde DB)
     */
    public function testMezclaDeFormatosFunciona()
    {
        $factura = new Factura();
        $factura->fecha_emision = '15/03/2026';           // ya formateada
        $factura->primer_vto_fecha = '2026-04-10';         // desde DB
        $factura->segundo_vto_fecha = '2026-04-20 00:00:00'; // datetime DB
        $factura->tercer_vto_fecha = Carbon::create(2026, 5, 10); // Carbon object

        $result = $this->aplicarFormateoFechas($factura);

        $this->assertEquals('15/03/2026', $result->fecha_emision);
        $this->assertEquals('10/04/2026', $result->primer_vto_fecha);
        $this->assertEquals('20/04/2026', $result->segundo_vto_fecha);
        $this->assertEquals('10/05/2026', $result->tercer_vto_fecha);
    }

    /**
     * Test: tercer_vto_fecha NULL no explota (comportamiento preexistente)
     */
    public function testTercerVtoNullNoExplota()
    {
        $factura = new Factura();
        $factura->fecha_emision = '2026-03-15';
        $factura->primer_vto_fecha = '2026-04-10';
        $factura->segundo_vto_fecha = '2026-04-20';
        $factura->tercer_vto_fecha = null;

        // No debe lanzar excepción
        $result = $this->aplicarFormateoFechas($factura);

        $this->assertEquals('15/03/2026', $result->fecha_emision);
        // tercer_vto_fecha null se parsea como fecha actual (Carbon::parse(null) = now)
        $this->assertNotNull($result->tercer_vto_fecha);
    }

    // =========================================
    // TESTS: getPeriodRange - rango cross-year
    // =========================================

    /**
     * Test: Rango de periodos dentro del mismo año
     */
    public function testGetPeriodRangeMismoAno()
    {
        $method = new ReflectionMethod(BillController::class, 'getPeriodRange');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, '01/2026', '03/2026');

        $this->assertEquals(['01/2026', '02/2026', '03/2026'], $result);
    }

    /**
     * Test: Rango cruzando años (el bug original: "09/2025" > "02/2026" como string)
     */
    public function testGetPeriodRangeCrossYear()
    {
        $method = new ReflectionMethod(BillController::class, 'getPeriodRange');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, '09/2025', '02/2026');

        $this->assertEquals([
            '09/2025', '10/2025', '11/2025', '12/2025',
            '01/2026', '02/2026'
        ], $result);
    }

    /**
     * Test: Rango de un solo periodo
     */
    public function testGetPeriodRangeUnSoloPeriodo()
    {
        $method = new ReflectionMethod(BillController::class, 'getPeriodRange');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, '03/2026', '03/2026');

        $this->assertEquals(['03/2026'], $result);
    }

    /**
     * Test: Parámetros vacíos devuelven null
     */
    public function testGetPeriodRangeVacioDevuelveNull()
    {
        $method = new ReflectionMethod(BillController::class, 'getPeriodRange');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($this->controller, '', '03/2026'));
        $this->assertNull($method->invoke($this->controller, '01/2026', ''));
        $this->assertNull($method->invoke($this->controller, '', ''));
    }

    // =========================================
    // HELPER
    // =========================================

    /**
     * Replica la lógica exacta del fix aplicado en getBillDetail/sendEmailFactura
     */
    protected function aplicarFormateoFechas($factura)
    {
        $factura->fecha_emision = (is_string($factura->fecha_emision) && strpos($factura->fecha_emision, '/') !== false)
            ? $factura->fecha_emision
            : Carbon::parse($factura->fecha_emision)->format('d/m/Y');

        $factura->primer_vto_fecha = (is_string($factura->primer_vto_fecha) && strpos($factura->primer_vto_fecha, '/') !== false)
            ? $factura->primer_vto_fecha
            : Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');

        $factura->segundo_vto_fecha = (is_string($factura->segundo_vto_fecha) && strpos($factura->segundo_vto_fecha, '/') !== false)
            ? $factura->segundo_vto_fecha
            : Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');

        $factura->tercer_vto_fecha = (is_string($factura->tercer_vto_fecha) && strpos($factura->tercer_vto_fecha, '/') !== false)
            ? $factura->tercer_vto_fecha
            : Carbon::parse($factura->tercer_vto_fecha)->format('d/m/Y');

        return $factura;
    }
}

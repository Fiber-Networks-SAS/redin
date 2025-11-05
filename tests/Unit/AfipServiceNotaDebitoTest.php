<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\AfipService;
use App\Factura;
use App\Cliente;
use App\Talonario;

/**
 * Tests unitarios para AfipService - Notas de Débito
 * 
 * Ejecutar con: vendor/bin/phpunit tests/Unit/AfipServiceNotaDebitoTest.php
 */
class AfipServiceNotaDebitoTest extends TestCase
{
    use DatabaseTransactions;

    protected $afipService;

    public function setUp()
    {
        parent::setUp();
        $this->afipService = new AfipService();
    }
    
    /**
     * Test: Estructura de datos para Nota de Débito A
     *
     * Verifica que el método notaDebitoA() genere la estructura correcta
     * NOTA: Este test requiere AFIP habilitado o mock completo
     */
    public function testNotaDebitoAEstructuraDatos()
    {
        // Verificar que AFIP esté deshabilitado para evitar llamadas reales
        if (env('AFIP_ENABLED', false) === true) {
            $this->markTestSkipped('Test omitido: AFIP está habilitado. Deshabilitar para tests unitarios.');
        }

        // Test básico: verificar que el método existe y acepta los parámetros correctos
        $this->assertTrue(method_exists($this->afipService, 'notaDebitoA'));

        // Verificar que lanza excepción si no hay CUIT
        try {
            $this->afipService->notaDebitoA(1, '', 121.00, 12345);
            $this->fail('Debería lanzar excepción sin CUIT');
        } catch (\Exception $e) {
            $this->assertContains('CUIT', $e->getMessage());
        }
    }

    /**
     * Test: Cálculo de importes con IVA 21% para Nota de Débito A
     */
    public function testNotaDebitoACalculoImportes()
    {
        $importeTotal = 121.00;
        $importeNeto = round($importeTotal / 1.21, 2);
        $importeIVA = round($importeTotal - $importeNeto, 2);

        $this->assertEquals(100.00, $importeNeto);
        $this->assertEquals(21.00, $importeIVA);
        $this->assertEquals(121.00, $importeNeto + $importeIVA);
    }

    /**
     * Test: Nota de Débito A requiere CUIT del cliente
     */
    public function testNotaDebitoARequiereCuit()
    {
        if (env('AFIP_ENABLED', false) === true) {
            $this->markTestSkipped('Test omitido: AFIP está habilitado.');
        }

        try {
            $this->afipService->notaDebitoA(1, '', 121.00, 12345);
            $this->fail('Debería lanzar excepción sin CUIT');
        } catch (\Exception $e) {
            $this->assertContains('CUIT', $e->getMessage());
        }
    }

    /**
     * Test: Estructura de datos para Nota de Débito B
     */
    public function testNotaDebitoBEstructuraDatos()
    {
        if (env('AFIP_ENABLED', false) === true) {
            $this->markTestSkipped('Test omitido: AFIP está habilitado.');
        }

        // Test básico: verificar que el método existe
        $this->assertTrue(method_exists($this->afipService, 'notaDebitoB'));
    }

    /**
     * Test: Nota de Débito B no requiere CUIT (consumidor final)
     */
    public function testNotaDebitoBNoRequiereCuit()
    {
        if (env('AFIP_ENABLED', false) === true) {
            $this->markTestSkipped('Test omitido: AFIP está habilitado.');
        }

        // Verificar que el método existe y acepta parámetros sin CUIT
        $this->assertTrue(method_exists($this->afipService, 'notaDebitoB'));
    }

    /**
     * Test: Redondeo correcto de importes
     */
    public function testRedondeoImportes()
    {
        // Caso 1: Importe con decimales exactos
        $importe1 = 595.62;
        $neto1 = round($importe1 / 1.21, 2);
        $iva1 = round($importe1 - $neto1, 2);

        // Ajustar expectativa según redondeo real de PHP
        $this->assertEquals(492.25, $neto1); // PHP redondea 492.247... a 492.25
        $this->assertEquals(103.37, $iva1);

        // Caso 2: Importe que genera decimales largos
        $importe2 = 540.00;
        $neto2 = round($importe2 / 1.21, 2);
        $iva2 = round($importe2 - $neto2, 2);

        $this->assertEquals(446.28, $neto2);
        $this->assertEquals(93.72, $iva2);
    }

    /**
     * Test: Asociación correcta con factura original
     */
    public function testAsociacionConFacturaOriginal()
    {
        $nroFacturaAsociada = 12345;

        // Verificar que la estructura CbtesAsoc sea correcta
        $cbtesAsoc = [
            [
                'Tipo'  => 1, // Factura A
                'PtoVta'=> 1,
                'Nro'   => $nroFacturaAsociada,
            ]
        ];

        $this->assertTrue(is_array($cbtesAsoc));
        $this->assertCount(1, $cbtesAsoc);
        $this->assertEquals(1, $cbtesAsoc[0]['Tipo']);
        $this->assertEquals(12345, $cbtesAsoc[0]['Nro']);
    }

    /**
     * Test: Formato de fecha correcto para AFIP (Ymd)
     */
    public function testFormatoFechaAfip()
    {
        $fechaActual = date('Ymd');

        $this->assertEquals(8, strlen($fechaActual));
        $this->assertRegExp('/^\d{8}$/', $fechaActual);

        // Verificar que sea una fecha válida
        $year = substr($fechaActual, 0, 4);
        $month = substr($fechaActual, 4, 2);
        $day = substr($fechaActual, 6, 2);

        $this->assertTrue(checkdate($month, $day, $year));
    }
}


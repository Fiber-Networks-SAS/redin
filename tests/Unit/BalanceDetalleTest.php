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
 * Tests: Balance Detalle - Notas de Débito, rango cross-year, campo nombre_cliente
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/BalanceDetalleTest.php
 */
class BalanceDetalleTest extends TestCase
{
    use DatabaseTransactions;

    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = app()->make(BillController::class);
    }

    /**
     * Test: Balance detalle incluye Notas de Crédito con label de factura original
     */
    public function testBalanceDetalleIncluyeNotasCreditoConLabel()
    {
        $factura = $this->crearFacturaConNotaCredito('03/2026');

        $request = new Request([
            'date_from' => '03/2026',
            'date_to' => '03/2026',
            'user_id' => ''
        ]);

        $method = new ReflectionMethod(BillController::class, 'getBalanceDetalle');
        $method->setAccessible(true);
        $result = $method->invoke($this->controller, $request);

        $this->assertNotNull($result);

        $ncEncontrada = false;
        foreach ($result as $nroCliente => $items) {
            foreach ($items as $item) {
                if (isset($item['is_nota_credito']) && $item['is_nota_credito']) {
                    $ncEncontrada = true;
                    $this->assertArrayHasKey('factura_original_label', $item);
                    $this->assertNotEmpty($item['factura_original_label']);
                    $this->assertArrayHasKey('nombre_cliente', $item);
                }
            }
        }

        $this->assertTrue($ncEncontrada, 'Debería encontrar al menos una Nota de Crédito');
    }

    /**
     * Test: Balance detalle incluye Notas de Débito
     */
    public function testBalanceDetalleIncluyeNotasDebito()
    {
        $factura = $this->crearFacturaConNotaDebito('03/2026');

        $request = new Request([
            'date_from' => '03/2026',
            'date_to' => '03/2026',
            'user_id' => ''
        ]);

        $method = new ReflectionMethod(BillController::class, 'getBalanceDetalle');
        $method->setAccessible(true);
        $result = $method->invoke($this->controller, $request);

        $this->assertNotNull($result);

        $ndEncontrada = false;
        foreach ($result as $nroCliente => $items) {
            foreach ($items as $item) {
                if (isset($item['is_nota_debito']) && $item['is_nota_debito']) {
                    $ndEncontrada = true;
                    $this->assertArrayHasKey('factura_original_label', $item);
                    $this->assertArrayHasKey('nombre_cliente', $item);
                    $this->assertGreaterThan(0, $item['importe_total_numeric']);
                    $this->assertFalse($item['is_anulada']);
                }
            }
        }

        $this->assertTrue($ndEncontrada, 'Debería encontrar al menos una Nota de Débito');
    }

    /**
     * Test: Balance detalle cross-year devuelve facturas de todos los meses
     */
    public function testBalanceDetalleCrossYear()
    {
        $user = $this->crearUsuarioPrueba();
        $this->crearFacturaDirecta($user, '11/2025');
        $this->crearFacturaDirecta($user, '12/2025');
        $this->crearFacturaDirecta($user, '01/2026');
        $this->crearFacturaDirecta($user, '02/2026');

        $request = new Request([
            'date_from' => '11/2025',
            'date_to' => '02/2026',
            'user_id' => ''
        ]);

        $method = new ReflectionMethod(BillController::class, 'getBalanceDetalle');
        $method->setAccessible(true);
        $result = $method->invoke($this->controller, $request);

        $this->assertNotNull($result);

        $nroCliente = str_pad($user->nro_cliente, 5, '0', STR_PAD_LEFT);
        $this->assertArrayHasKey($nroCliente, $result);
        $this->assertEquals(4, count($result[$nroCliente]), 'Deberían aparecer las 4 facturas cross-year');
    }

    /**
     * Test: Campo nombre_cliente se propaga correctamente
     */
    public function testNombreClienteSePropaga()
    {
        $user = $this->crearUsuarioPrueba();
        $this->crearFacturaDirecta($user, '03/2026');

        $request = new Request([
            'date_from' => '03/2026',
            'date_to' => '03/2026',
            'user_id' => ''
        ]);

        $method = new ReflectionMethod(BillController::class, 'getBalanceDetalle');
        $method->setAccessible(true);
        $result = $method->invoke($this->controller, $request);

        $this->assertNotNull($result);

        $nroCliente = str_pad($user->nro_cliente, 5, '0', STR_PAD_LEFT);
        $this->assertArrayHasKey($nroCliente, $result);

        $factura = $result[$nroCliente][0];
        $this->assertArrayHasKey('nombre_cliente', $factura);
        $this->assertContains('BalanceTest', $factura['nombre_cliente']);
    }

    // =========================================
    // HELPERS
    // =========================================

    protected function crearUsuarioPrueba()
    {
        $talonario = Talonario::first();
        if (!$talonario) {
            Talonario::unguard();
            $talonario = Talonario::create([
                'letra' => 'B',
                'nro_punto_vta' => 1,
                'nro_factura' => 1
            ]);
            Talonario::reguard();
        }

        $user = new User();
        $user->firstname = 'Test';
        $user->lastname = 'BalanceTest';
        $user->email = 'balance' . uniqid() . '@test.com';
        $user->dni = rand(10000000, 99999999);
        $user->nro_cliente = rand(10000, 99999);
        $user->password = bcrypt('password');
        $user->status = 1;
        $user->talonario_id = $talonario->id;
        $user->save();

        return $user;
    }

    protected function crearFacturaDirecta($user, $periodo)
    {
        $talonario = Talonario::first();

        DB::table('facturas')->insert([
            'user_id' => $user->id,
            'nro_cliente' => $user->nro_cliente,
            'talonario_id' => $talonario->id,
            'nro_factura' => rand(1000, 9999),
            'periodo' => $periodo,
            'fecha_emision' => Carbon::now()->format('Y-m-d'),
            'importe_subtotal' => 5000,
            'importe_subtotal_iva' => 1050,
            'importe_total' => 5000,
            'importe_iva' => 1050,
            'importe_bonificacion' => 0,
            'importe_bonificacion_iva' => 0,
            'primer_vto_fecha' => Carbon::now()->addDays(15)->format('Y-m-d'),
            'primer_vto_codigo' => '123456',
            'segundo_vto_fecha' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'segundo_vto_tasa' => 5,
            'segundo_vto_importe' => 5250,
            'segundo_vto_codigo' => '654321',
            'tercer_vto_tasa' => 1,
        ]);

        return Factura::where('user_id', $user->id)->where('periodo', $periodo)->first();
    }

    protected function crearFacturaConNotaCredito($periodo)
    {
        $user = $this->crearUsuarioPrueba();
        $factura = $this->crearFacturaDirecta($user, $periodo);

        $talonario = Talonario::first();
        DB::table('notas_credito')->insert([
            'factura_id' => $factura->id,
            'talonario_id' => $talonario->id,
            'nro_nota_credito' => rand(100, 999),
            'importe_bonificacion' => 1000,
            'importe_iva' => 210,
            'importe_total' => 1210,
            'cae' => '12345678901234',
            'cae_vto' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'fecha_emision' => Carbon::now()->format('Y-m-d'),
            'motivo' => 'Bonificacion',
            'nro_cliente' => $user->id,
            'periodo' => $periodo,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $factura;
    }

    protected function crearFacturaConNotaDebito($periodo)
    {
        $user = $this->crearUsuarioPrueba();
        $factura = $this->crearFacturaDirecta($user, $periodo);

        $talonario = Talonario::first();
        DB::table('notas_debito')->insert([
            'factura_id' => $factura->id,
            'talonario_id' => $talonario->id,
            'nro_nota_debito' => rand(100, 999),
            'importe_ampliacion' => 0,
            'importe_iva' => 105,
            'importe_total' => 500,
            'cae' => '12345678901234',
            'cae_vto' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'fecha_emision' => Carbon::now()->format('Y-m-d'),
            'motivo' => 'Intereses',
            'nro_cliente' => $user->id,
            'periodo' => $periodo,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $factura;
    }
}

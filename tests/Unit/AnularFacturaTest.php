<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\BillController;
use App\Services\AfipService;
use App\Services\PaymentQRService;
use App\Factura;
use App\NotaCredito;
use App\SaldoFavor;
use App\User;
use App\Role;
use App\Talonario;
use App\Servicio;
use App\ServicioUsuario;
use App\FacturaDetalle;
use App\Interes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Tests: Anulación de factura individual + NC por el total
 *
 * Verifica el flujo completo de anulación:
 * - Búsqueda solo de facturas activas
 * - Detalle correcto (incluye info de pago)
 * - Emisión de NC por el total en AFIP
 * - Soft delete de la factura
 * - Creación de saldo a favor si estaba pagada
 * - Validaciones (factura no encontrada, ya anulada, sin motivo)
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/AnularFacturaTest.php
 */
class AnularFacturaTest extends TestCase
{
    use DatabaseTransactions;

    protected $controller;
    protected $periodo = '02/2026';

    public function setUp()
    {
        parent::setUp();
        $this->asegurarConfiguracionInteres();
        $this->loginComoAdmin();
    }

    /**
     * Crear controller con AfipService mockeado
     */
    protected function crearControllerConAfipMock($afipMock = null)
    {
        if ($afipMock === null) {
            $afipMock = $this->crearAfipMockExitoso();
        }
        $paymentQRService = app()->make(PaymentQRService::class);
        return new BillController($paymentQRService, $afipMock);
    }

    /**
     * Crea un mock de AfipService que responde exitosamente
     */
    protected function crearAfipMockExitoso($nroNc = 999)
    {
        $mock = $this->getMockBuilder(AfipService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getLastVoucher')->willReturn($nroNc - 1);

        $mock->method('notaCreditoA')->willReturn([
            'CbteDesde' => $nroNc,
            'CbteHasta' => $nroNc,
            'CAE' => '71234567890123',
            'CAEFchVto' => '20260430',
        ]);

        $mock->method('notaCreditoB')->willReturn([
            'CbteDesde' => $nroNc,
            'CbteHasta' => $nroNc,
            'CAE' => '71234567890123',
            'CAEFchVto' => '20260430',
        ]);

        return $mock;
    }

    // =========================================
    // TESTS: BÚSQUEDA (getAnularFacturaBuscar)
    // =========================================

    /**
     * Test 1: La búsqueda solo retorna facturas activas (no anuladas)
     */
    public function testBusquedaSoloRetornaFacturasActivas()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaActiva();
        $clienteAnulada = $this->crearClienteConFacturaAnulada();

        $request = new Request(['q' => 'Test']);
        $response = $controller->getAnularFacturaBuscar($request);
        $data = json_decode($response->getContent(), true);

        $idsEncontrados = array_column($data, 'id');

        $facturaActiva = Factura::where('user_id', $cliente->id)->first();
        $facturaAnulada = Factura::withTrashed()->where('user_id', $clienteAnulada->id)->first();

        $this->assertContains($facturaActiva->id, $idsEncontrados,
            'Factura activa debe aparecer en resultados');
        $this->assertNotContains($facturaAnulada->id, $idsEncontrados,
            'Factura anulada NO debe aparecer en resultados');
    }

    /**
     * Test 2: La búsqueda con menos de 2 caracteres retorna vacío
     */
    public function testBusquedaCorta()
    {
        $controller = $this->crearControllerConAfipMock();

        $request = new Request(['q' => 'T']);
        $response = $controller->getAnularFacturaBuscar($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEmpty($data, 'Búsqueda con 1 carácter debe retornar vacío');
    }

    /**
     * Test 3: La búsqueda por número de factura funciona
     */
    public function testBusquedaPorNumeroFactura()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteBase();
        $factura = $this->crearFacturaParaCliente($cliente, false, 54321);

        $request = new Request(['q' => '54321']);
        $response = $controller->getAnularFacturaBuscar($request);
        $data = json_decode($response->getContent(), true);

        $idsEncontrados = array_column($data, 'id');
        $this->assertContains($factura->id, $idsEncontrados,
            'Búsqueda por nro_factura debe encontrar la factura');
    }

    // =========================================
    // TESTS: DETALLE (getAnularFacturaDetalle)
    // =========================================

    /**
     * Test 4: El detalle retorna datos correctos incluyendo info de pago
     */
    public function testDetalleRetornaDatosCorrectos()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaPagada();
        $factura = Factura::where('user_id', $cliente->id)->first();

        $request = new Request();
        $response = $controller->getAnularFacturaDetalle($request, $factura->id);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals($factura->id, $data['id']);
        $this->assertArrayHasKey('importe_total', $data);
        $this->assertArrayHasKey('importe_pago', $data);
        $this->assertArrayHasKey('fecha_pago', $data);
        $this->assertArrayHasKey('detalles', $data);
        $this->assertArrayHasKey('historial', $data);
        $this->assertNotNull($data['importe_pago'], 'Factura pagada debe tener importe_pago');
        $this->assertNotNull($data['fecha_pago'], 'Factura pagada debe tener fecha_pago');
    }

    /**
     * Test 5: El detalle de factura no existente retorna 404
     */
    public function testDetalleFacturaNoExistenteRetorna404()
    {
        $controller = $this->crearControllerConAfipMock();

        $request = new Request();
        $response = $controller->getAnularFacturaDetalle($request, 999999);

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test 6: El detalle retorna importe_pago null para factura no pagada
     */
    public function testDetalleFacturaNoPagadaRetornaImportePagoNull()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaActiva();
        $factura = Factura::where('user_id', $cliente->id)->first();

        $request = new Request();
        $response = $controller->getAnularFacturaDetalle($request, $factura->id);
        $data = json_decode($response->getContent(), true);

        $this->assertNull($data['importe_pago'], 'Factura no pagada debe tener importe_pago = null');
        $this->assertNull($data['fecha_pago'], 'Factura no pagada debe tener fecha_pago = null');
    }

    // =========================================
    // TESTS: ANULACIÓN (postAnularFactura)
    // =========================================

    /**
     * Test 7: Anulación exitosa crea NC y soft-deletes factura
     */
    public function testAnulacionExitosaCreaNCYAnulaFactura()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaActiva();
        $factura = Factura::where('user_id', $cliente->id)->first();
        $facturaId = $factura->id;

        $request = new Request(['motivo' => 'Error en servicios facturados']);
        $response = $controller->postAnularFactura($request, $facturaId);

        // Verificar que la factura fue soft-deleted
        $facturaAnulada = Factura::find($facturaId);
        $this->assertNull($facturaAnulada, 'Factura debe estar soft-deleted (find retorna null)');

        $facturaConTrashed = Factura::withTrashed()->find($facturaId);
        $this->assertNotNull($facturaConTrashed, 'Factura debe seguir existiendo con withTrashed');
        $this->assertNotNull($facturaConTrashed->deleted_at, 'deleted_at debe estar seteado');
        $this->assertNotNull($facturaConTrashed->motivo_anulacion, 'motivo_anulacion debe estar seteado');
        $this->assertNotNull($facturaConTrashed->anulado_por, 'anulado_por debe estar seteado');
        $this->assertNotNull($facturaConTrashed->fecha_anulacion, 'fecha_anulacion debe estar seteado');

        // Verificar que se creó la NC
        $nc = NotaCredito::where('factura_id', $facturaId)->where('tipo', 'anulacion')->first();
        $this->assertNotNull($nc, 'Debe haberse creado una NotaCredito de tipo anulacion');
        $this->assertEquals($facturaConTrashed->importe_total, $nc->importe_total, 'NC debe ser por el total');
        $this->assertEquals('71234567890123', $nc->cae, 'NC debe tener el CAE de AFIP');
        $this->assertContains('Error en servicios facturados', $nc->motivo, 'NC debe contener el motivo');
    }

    /**
     * Test 8: Anulación de factura pagada crea saldo a favor
     */
    public function testAnulacionFacturaPagadaCreaSaldoAFavor()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaPagada();
        $factura = Factura::where('user_id', $cliente->id)->first();
        $facturaId = $factura->id;
        $importePago = $factura->importe_pago;

        // Contar saldos antes
        $saldosAntes = SaldoFavor::where('user_id', $cliente->id)->count();

        $request = new Request(['motivo' => 'Refacturación por error']);
        $controller->postAnularFactura($request, $facturaId);

        // Verificar saldo a favor
        $saldosDespues = SaldoFavor::where('user_id', $cliente->id)->count();
        $this->assertEquals($saldosAntes + 1, $saldosDespues, 'Debe crearse un saldo a favor');

        $saldo = SaldoFavor::where('factura_anulada_id', $facturaId)->first();
        $this->assertNotNull($saldo, 'Saldo a favor debe existir');
        $this->assertEquals($importePago, $saldo->importe_pagado, 'Saldo debe ser por el importe pagado');
        $this->assertEquals($importePago, $saldo->importe_disponible, 'Saldo disponible debe ser el total pagado');
        $this->assertEquals(0, $saldo->importe_utilizado, 'Saldo utilizado debe ser 0');
        $this->assertEquals('pendiente', $saldo->estado, 'Estado debe ser pendiente');
    }

    /**
     * Test 9: Anulación de factura NO pagada NO crea saldo a favor
     */
    public function testAnulacionFacturaNoPagadaNOCreaSaldoAFavor()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaActiva();
        $factura = Factura::where('user_id', $cliente->id)->first();
        $facturaId = $factura->id;

        $saldosAntes = SaldoFavor::where('user_id', $cliente->id)->count();

        $request = new Request(['motivo' => 'Error de facturación']);
        $controller->postAnularFactura($request, $facturaId);

        $saldosDespues = SaldoFavor::where('user_id', $cliente->id)->count();
        $this->assertEquals($saldosAntes, $saldosDespues, 'NO debe crearse saldo a favor para factura no pagada');
    }

    /**
     * Test 10: Anulación falla si la factura no existe
     */
    public function testAnulacionFallaSiFacturaNoExiste()
    {
        $controller = $this->crearControllerConAfipMock();

        $request = new Request(['motivo' => 'Test']);
        $response = $controller->postAnularFactura($request, 999999);

        // Debe redirigir con error
        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('danger', $session->get('status'));
    }

    /**
     * Test 11: Anulación falla si la factura ya está anulada
     */
    public function testAnulacionFallaSiFacturaYaAnulada()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaAnulada();
        $factura = Factura::withTrashed()->where('user_id', $cliente->id)->first();

        $request = new Request(['motivo' => 'Test']);
        $response = $controller->postAnularFactura($request, $factura->id);

        // Debe redirigir con error
        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('danger', $session->get('status'));
    }

    /**
     * Test 12: Anulación falla sin motivo
     */
    public function testAnulacionFallaSinMotivo()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaActiva();
        $factura = Factura::where('user_id', $cliente->id)->first();

        $request = new Request([]); // Sin motivo
        $response = $controller->postAnularFactura($request, $factura->id);

        // Debe redirigir con errores de validación
        $this->assertEquals(302, $response->getStatusCode());

        // Factura NO debe haber sido anulada
        $facturaCheck = Factura::find($factura->id);
        $this->assertNotNull($facturaCheck, 'Factura NO debe anularse sin motivo');
    }

    /**
     * Test 13: Anulación falla si AFIP no responde con CAE
     */
    public function testAnulacionFallaSiAfipNoRespondeCae()
    {
        // Mock que retorna respuesta sin CAE
        $afipMock = $this->getMockBuilder(AfipService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $afipMock->method('getLastVoucher')->willReturn(100);
        $afipMock->method('notaCreditoB')->willReturn([
            'CbteDesde' => null,
            'CAE' => null,
        ]);

        $controller = $this->crearControllerConAfipMock($afipMock);
        $cliente = $this->crearClienteConFacturaActiva();
        $factura = Factura::where('user_id', $cliente->id)->first();

        $request = new Request(['motivo' => 'Test AFIP falla']);
        $response = $controller->postAnularFactura($request, $factura->id);

        // Debe redirigir con error
        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('danger', $session->get('status'));

        // Factura NO debe haber sido anulada
        $facturaCheck = Factura::find($factura->id);
        $this->assertNotNull($facturaCheck, 'Factura NO debe anularse si AFIP falla');
    }

    /**
     * Test 14: NC creada tiene tipo 'anulacion'
     */
    public function testNCCreadaTieneTipoAnulacion()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaActiva();
        $factura = Factura::where('user_id', $cliente->id)->first();

        $request = new Request(['motivo' => 'Test tipo NC']);
        $controller->postAnularFactura($request, $factura->id);

        $nc = NotaCredito::where('factura_id', $factura->id)->first();
        $this->assertNotNull($nc);
        $this->assertEquals('anulacion', $nc->tipo, 'Tipo de NC debe ser anulacion');
    }

    /**
     * Test 15: Motivo de anulación se guarda correctamente en la factura
     */
    public function testMotivoAnulacionSeGuardaEnFactura()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaActiva();
        $factura = Factura::where('user_id', $cliente->id)->first();

        $request = new Request(['motivo' => 'Cliente se mudó']);
        $controller->postAnularFactura($request, $factura->id);

        $facturaAnulada = Factura::withTrashed()->find($factura->id);
        $this->assertContains('Cliente se mudó', $facturaAnulada->motivo_anulacion,
            'motivo_anulacion debe contener el motivo ingresado');
        $this->assertContains('ANULACIÓN INDIVIDUAL', $facturaAnulada->motivo_anulacion,
            'motivo_anulacion debe indicar que es anulación individual');
    }

    /**
     * Test 16: Cliente puede ser refacturado después de anulación
     * (la factura anulada no bloquea una nueva factura en el mismo periodo)
     */
    public function testClientePuedeSerRefacturadoDespuesDeAnulacion()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaActiva();
        $factura = Factura::where('user_id', $cliente->id)->first();

        // Anular
        $request = new Request(['motivo' => 'Para refacturar']);
        $controller->postAnularFactura($request, $factura->id);

        // Verificar que no hay factura activa
        $facturaActiva = Factura::where('user_id', $cliente->id)
            ->where('periodo', $this->periodo)
            ->first();
        $this->assertNull($facturaActiva, 'No debe haber factura activa después de anular');

        // Crear nueva factura (simular refacturación)
        $nuevaFactura = $this->crearFacturaParaCliente($cliente, false);
        $this->assertNotNull($nuevaFactura, 'Debe poder crearse nueva factura');

        // Verificar que ahora hay 1 activa y 1 anulada
        $activas = Factura::where('user_id', $cliente->id)
            ->where('periodo', $this->periodo)
            ->count();
        $totales = Factura::withTrashed()
            ->where('user_id', $cliente->id)
            ->where('periodo', $this->periodo)
            ->count();

        $this->assertEquals(1, $activas, 'Debe haber exactamente 1 factura activa');
        $this->assertEquals(2, $totales, 'Debe haber 2 facturas en total (1 activa + 1 anulada)');
    }

    /**
     * Test 17: NC tiene los importes correctos (neto, IVA, total)
     */
    public function testNCTieneImportesCorrectos()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteBase();
        $this->crearFacturaParaCliente($cliente, false, null, 12100.00); // total = 12100
        $factura = Factura::where('user_id', $cliente->id)->first();

        $request = new Request(['motivo' => 'Test importes']);
        $controller->postAnularFactura($request, $factura->id);

        $nc = NotaCredito::where('factura_id', $factura->id)->first();
        $this->assertNotNull($nc);

        $expectedNeto = round(12100.00 / 1.21, 2); // 10000.00
        $expectedIva = round(12100.00 - $expectedNeto, 2); // 2100.00

        $this->assertEquals(12100.00, $nc->importe_total, 'Total NC debe ser 12100.00');
        $this->assertEquals($expectedNeto, $nc->importe_bonificacion, 'Neto NC debe ser 10000.00');
        $this->assertEquals($expectedIva, $nc->importe_iva, 'IVA NC debe ser 2100.00');
    }

    /**
     * Test 18: El saldo a favor vincula la NC correctamente
     */
    public function testSaldoAFavorVinculaNC()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteConFacturaPagada();
        $factura = Factura::where('user_id', $cliente->id)->first();

        $request = new Request(['motivo' => 'Test vinculación']);
        $controller->postAnularFactura($request, $factura->id);

        $nc = NotaCredito::where('factura_id', $factura->id)->where('tipo', 'anulacion')->first();
        $saldo = SaldoFavor::where('factura_anulada_id', $factura->id)->first();

        $this->assertNotNull($saldo);
        $this->assertEquals($nc->id, $saldo->nota_credito_id,
            'Saldo a favor debe estar vinculado a la NC');
        $this->assertEquals($factura->id, $saldo->factura_anulada_id,
            'Saldo a favor debe estar vinculado a la factura anulada');
    }

    // =========================================
    // HELPERS
    // =========================================

    protected function crearClienteConFacturaActiva()
    {
        $cliente = $this->crearClienteBase();
        $this->crearFacturaParaCliente($cliente, false);
        return $cliente;
    }

    protected function crearClienteConFacturaAnulada()
    {
        $cliente = $this->crearClienteBase();
        $this->crearFacturaParaCliente($cliente, true);
        return $cliente;
    }

    protected function crearClienteConFacturaPagada()
    {
        $cliente = $this->crearClienteBase();
        $talonario = Talonario::first();

        DB::table('facturas')->insert([
            'user_id' => $cliente->id,
            'nro_cliente' => $cliente->nro_cliente,
            'talonario_id' => $talonario->id,
            'nro_factura' => rand(1000, 9999),
            'periodo' => $this->periodo,
            'fecha_emision' => Carbon::now()->subDays(30),
            'importe_subtotal' => 5000,
            'importe_subtotal_iva' => 5000,
            'importe_total' => 5000,
            'importe_iva' => 1050,
            'importe_bonificacion' => 0,
            'importe_bonificacion_iva' => 0,
            'primer_vto_fecha' => Carbon::now()->subDays(15),
            'primer_vto_codigo' => '123456',
            'segundo_vto_fecha' => Carbon::now(),
            'segundo_vto_tasa' => 5,
            'segundo_vto_importe' => 5250,
            'segundo_vto_codigo' => '654321',
            'tercer_vto_tasa' => 1,
            'cae' => '71234567890123',
            'importe_pago' => 5000,
            'fecha_pago' => Carbon::now()->subDays(10),
            'forma_pago' => 1,
            'deleted_at' => null
        ]);

        return $cliente;
    }

    protected function crearClienteBase()
    {
        $role = Role::firstOrCreate(['name' => 'client']);
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

        $uniqueId = uniqid();
        User::unguard();
        $user = User::create([
            'nro_cliente' => rand(10000, 99999),
            'firstname' => 'Test',
            'lastname' => 'Anular ' . $uniqueId,
            'email' => "testanular{$uniqueId}@test.com",
            'password' => bcrypt('password'),
            'dni' => rand(10000000, 99999999),
            'status' => 1,
            'calle' => 'Calle Test',
            'altura' => rand(100, 9999),
            'talonario_id' => $talonario->id
        ]);
        User::reguard();

        $user->roles()->attach($role->id);
        return $user;
    }

    protected function crearFacturaParaCliente($cliente, $anulada = false, $nroFactura = null, $importeTotal = null)
    {
        $talonario = Talonario::first();
        $nroFactura = $nroFactura ?: rand(1000, 9999);
        $importeTotal = $importeTotal ?: 5000;

        DB::table('facturas')->insert([
            'user_id' => $cliente->id,
            'nro_cliente' => $cliente->nro_cliente,
            'talonario_id' => $talonario->id,
            'nro_factura' => $nroFactura,
            'periodo' => $this->periodo,
            'fecha_emision' => Carbon::now()->subDays(30),
            'importe_subtotal' => round($importeTotal / 1.21, 2),
            'importe_subtotal_iva' => round($importeTotal / 1.21, 2),
            'importe_total' => $importeTotal,
            'importe_iva' => round($importeTotal - ($importeTotal / 1.21), 2),
            'importe_bonificacion' => 0,
            'importe_bonificacion_iva' => 0,
            'primer_vto_fecha' => Carbon::now()->addDays(15),
            'primer_vto_codigo' => '123456',
            'segundo_vto_fecha' => Carbon::now()->addDays(30),
            'segundo_vto_tasa' => 5,
            'segundo_vto_importe' => $importeTotal * 1.05,
            'segundo_vto_codigo' => '654321',
            'tercer_vto_tasa' => 1,
            'cae' => '71234567890123',
            'deleted_at' => $anulada ? Carbon::now() : null,
            'motivo_anulacion' => $anulada ? 'Test anulación previa' : null,
            'anulado_por' => $anulada ? 1 : null,
            'fecha_anulacion' => $anulada ? Carbon::now() : null,
        ]);

        return Factura::withTrashed()
            ->where('user_id', $cliente->id)
            ->where('periodo', $this->periodo)
            ->where('nro_factura', $nroFactura)
            ->first();
    }

    protected function loginComoAdmin()
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
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

        $uniqueId = uniqid();
        User::unguard();
        $admin = User::create([
            'nro_cliente' => rand(10000, 99999),
            'firstname' => 'Admin',
            'lastname' => 'Test ' . $uniqueId,
            'email' => "admin{$uniqueId}@test.com",
            'password' => bcrypt('password'),
            'dni' => rand(10000000, 99999999),
            'status' => 1,
            'calle' => 'Calle Test',
            'altura' => rand(100, 9999),
            'talonario_id' => $talonario->id
        ]);
        User::reguard();

        $admin->roles()->attach($role->id);
        Auth::login($admin);
        return $admin;
    }

    protected function asegurarConfiguracionInteres()
    {
        $interes = Interes::find(1);
        if (!$interes) {
            Interes::create([
                'id' => 1,
                'primer_vto_dia' => 10,
                'segundo_vto_dia' => 20,
                'segundo_vto_tasa' => 5,
                'tercer_vto_tasa' => 1
            ]);
        }
    }
}

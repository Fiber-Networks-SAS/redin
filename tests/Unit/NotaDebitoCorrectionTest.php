<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\BillController;
use App\Services\AfipService;
use App\Services\PaymentQRService;
use App\Factura;
use App\NotaDebito;
use App\User;
use App\Role;
use App\Talonario;
use App\Interes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Tests: Emisión de Notas de Débito (corrección desde vista de factura y desde herramienta AFIP)
 *
 * Cubre los métodos:
 *  - BillController::getBillCorregirNDPost   → /admin/period/bill-corregir/{id}/nd
 *  - BillController::postAfipCorreccionND    → /admin/afip-correccion/{id}/nd
 *  - BillController::postAfipCorreccionNDManual → /admin/afip-correccion/nd-manual
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/NotaDebitoCorrectionTest.php
 */
class NotaDebitoCorrectionTest extends TestCase
{
    use DatabaseTransactions;

    protected $periodo = '03/2026';

    public function setUp()
    {
        parent::setUp();
        $this->asegurarConfiguracionInteres();
        $this->loginComoAdmin();
    }

    // =========================================================
    // HELPERS: mocks y creación de datos
    // =========================================================

    protected function crearControllerConAfipMock($afipMock = null)
    {
        if ($afipMock === null) {
            $afipMock = $this->crearAfipMockExitoso();
        }
        $paymentQRService = app()->make(PaymentQRService::class);
        return new BillController($paymentQRService, $afipMock);
    }

    protected function crearAfipMockExitoso($nroNd = 888)
    {
        $mock = $this->getMockBuilder(AfipService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getLastVoucher')->willReturn($nroNd - 1);

        $respuesta = [
            'CbteDesde'  => $nroNd,
            'CbteHasta'  => $nroNd,
            'CAE'        => '72345678901234',
            'CAEFchVto'  => '20260530',
        ];

        $mock->method('notaDebitoA')->willReturn($respuesta);
        $mock->method('notaDebitoB')->willReturn($respuesta);

        return $mock;
    }

    protected function crearAfipMockSinCae()
    {
        $mock = $this->getMockBuilder(AfipService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getLastVoucher')->willReturn(100);
        $mock->method('notaDebitoA')->willReturn(['CbteDesde' => null, 'CAE' => null]);
        $mock->method('notaDebitoB')->willReturn(['CbteDesde' => null, 'CAE' => null]);

        return $mock;
    }

    protected function crearClienteBase($dni = null, $letra = 'B')
    {
        $role = Role::firstOrCreate(['name' => 'client']);
        $talonario = $this->obtenerOCrearTalonario($letra);

        $uniqueId = uniqid();
        User::unguard();
        $user = User::create([
            'nro_cliente' => rand(10000, 99999),
            'firstname'   => 'Test',
            'lastname'    => 'ND ' . $uniqueId,
            'email'       => "testnd{$uniqueId}@test.com",
            'password'    => bcrypt('password'),
            'dni'         => $dni ?: rand(10000000, 99999999),
            'status'      => 1,
            'calle'       => 'Calle Test',
            'altura'      => rand(100, 9999),
            'talonario_id' => $talonario->id,
        ]);
        User::reguard();

        $user->roles()->attach($role->id);
        return $user;
    }

    protected function obtenerOCrearTalonario($letra = 'B')
    {
        $talonario = Talonario::where('letra', $letra)->first();
        if (!$talonario) {
            Talonario::unguard();
            $talonario = Talonario::create([
                'letra'         => $letra,
                'nro_punto_vta' => 1,
                'nro_factura'   => 1,
            ]);
            Talonario::reguard();
        }
        return $talonario;
    }

    protected function crearFacturaParaCliente($cliente, $importeTotal = 12100.00)
    {
        $talonario = Talonario::where('id', $cliente->talonario_id)->first()
                  ?: $this->obtenerOCrearTalonario();

        DB::table('facturas')->insert([
            'user_id'               => $cliente->id,
            'nro_cliente'           => $cliente->nro_cliente,
            'talonario_id'          => $talonario->id,
            'nro_factura'           => rand(1000, 99999),
            'periodo'               => $this->periodo,
            'fecha_emision'         => Carbon::now()->subDays(30),
            'importe_subtotal'      => round($importeTotal / 1.21, 2),
            'importe_subtotal_iva'  => round($importeTotal / 1.21, 2),
            'importe_total'         => $importeTotal,
            'importe_iva'           => round($importeTotal - ($importeTotal / 1.21), 2),
            'importe_bonificacion'  => 0,
            'importe_bonificacion_iva' => 0,
            'primer_vto_fecha'      => Carbon::now()->addDays(15),
            'primer_vto_codigo'     => '123456',
            'segundo_vto_fecha'     => Carbon::now()->addDays(30),
            'segundo_vto_tasa'      => 5,
            'segundo_vto_importe'   => $importeTotal * 1.05,
            'segundo_vto_codigo'    => '654321',
            'tercer_vto_tasa'       => 1,
            'cae'                   => '71234567890123',
            'deleted_at'            => null,
        ]);

        return Factura::where('user_id', $cliente->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    protected function loginComoAdmin()
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $talonario = $this->obtenerOCrearTalonario();
        $uniqueId = uniqid();

        User::unguard();
        $admin = User::create([
            'nro_cliente'  => rand(10000, 99999),
            'firstname'    => 'Admin',
            'lastname'     => 'ND ' . $uniqueId,
            'email'        => "adminnd{$uniqueId}@test.com",
            'password'     => bcrypt('password'),
            'dni'          => rand(10000000, 99999999),
            'status'       => 1,
            'calle'        => 'Calle Test',
            'altura'       => rand(100, 9999),
            'talonario_id' => $talonario->id,
        ]);
        User::reguard();

        $admin->roles()->attach($role->id);
        Auth::login($admin);
        return $admin;
    }

    protected function asegurarConfiguracionInteres()
    {
        if (!Interes::find(1)) {
            Interes::create([
                'id'               => 1,
                'primer_vto_dia'   => 10,
                'segundo_vto_dia'  => 20,
                'segundo_vto_tasa' => 5,
                'tercer_vto_tasa'  => 1,
            ]);
        }
    }

    // =========================================================
    // TESTS: getBillCorregirNDPost
    // =========================================================

    /**
     * Test 1: Validación falla si falta importe_nd → redirect back con tab_nd
     */
    public function testBillCorregirNDFallaSinImporte()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente = $this->crearClienteBase();
        $factura = $this->crearFacturaParaCliente($cliente);

        $request  = new Request(['motivo_nd' => 'Prueba sin importe']);
        $response = $controller->getBillCorregirNDPost($request, $factura->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue((bool) $response->getSession()->get('tab_nd'),
            'tab_nd debe ser true en redirect de validación fallida');

        // No debe haberse creado ninguna ND
        $this->assertEquals(0, NotaDebito::where('factura_id', $factura->id)->count());
    }

    /**
     * Test 2: Validación falla si falta motivo_nd → redirect back con tab_nd
     */
    public function testBillCorregirNDFallaSinMotivo()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente    = $this->crearClienteBase();
        $factura    = $this->crearFacturaParaCliente($cliente);

        $request  = new Request(['importe_nd' => '1500.00']);
        $response = $controller->getBillCorregirNDPost($request, $factura->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue((bool) $response->getSession()->get('tab_nd'));
        $this->assertEquals(0, NotaDebito::where('factura_id', $factura->id)->count());
    }

    /**
     * Test 3: Factura no encontrada → redirect back con error + tab_nd
     */
    public function testBillCorregirNDFacturaNoEncontrada()
    {
        $controller = $this->crearControllerConAfipMock();

        $request  = new Request(['importe_nd' => '1500.00', 'motivo_nd' => 'Test']);
        $response = $controller->getBillCorregirNDPost($request, 999999);

        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('danger', $session->get('status'));
        $this->assertTrue((bool) $session->get('tab_nd'));
    }

    /**
     * Test 4: AFIP no devuelve CAE → redirect con error + tab_nd, no guarda ND
     */
    public function testBillCorregirNDAfipSinCaeNoGuarda()
    {
        $controller = $this->crearControllerConAfipMock($this->crearAfipMockSinCae());
        $cliente    = $this->crearClienteBase();
        $factura    = $this->crearFacturaParaCliente($cliente);

        $request  = new Request(['importe_nd' => '1500.00', 'motivo_nd' => 'Test AFIP sin CAE']);
        $response = $controller->getBillCorregirNDPost($request, $factura->id);

        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('danger', $session->get('status'));
        $this->assertTrue((bool) $session->get('tab_nd'));
        $this->assertEquals(0, NotaDebito::where('factura_id', $factura->id)->count(),
            'No debe guardarse ND si AFIP no devuelve CAE');
    }

    /**
     * Test 5: Emisión exitosa tipo B → crea NotaDebito en DB con datos correctos
     */
    public function testBillCorregirNDExitosaCreaNDEnDB()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente    = $this->crearClienteBase();
        $factura    = $this->crearFacturaParaCliente($cliente, 12100.00);

        $request  = new Request(['importe_nd' => '12100.00', 'motivo_nd' => 'Cargo adicional por servicios']);
        $response = $controller->getBillCorregirNDPost($request, $factura->id);

        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('success', $session->get('status'),
            'Emisión exitosa debe devolver status success');
        $this->assertTrue((bool) $session->get('tab_nd'),
            'tab_nd debe ser true incluso en respuesta exitosa');

        $nd = NotaDebito::where('factura_id', $factura->id)->first();
        $this->assertNotNull($nd, 'Debe haberse creado una NotaDebito');
        $this->assertEquals($factura->talonario_id, $nd->talonario_id);
        $this->assertEquals('72345678901234', $nd->cae);
        $this->assertEquals(12100.00, $nd->importe_total);
        $this->assertEquals('Cargo adicional por servicios', $nd->motivo);
        $this->assertEquals($factura->nro_cliente, $nd->nro_cliente);
        $this->assertEquals($factura->periodo, $nd->periodo);
    }

    /**
     * Test 6: Importes de ND calculados correctamente (neto + IVA + total)
     */
    public function testBillCorregirNDImportesCalculadosCorrectamente()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente    = $this->crearClienteBase();
        $factura    = $this->crearFacturaParaCliente($cliente);

        $importeConIva = 2420.00; // neto esperado: 2000.00, IVA: 420.00
        $request  = new Request(['importe_nd' => (string) $importeConIva, 'motivo_nd' => 'Test importes']);
        $controller->getBillCorregirNDPost($request, $factura->id);

        $nd = NotaDebito::where('factura_id', $factura->id)->first();
        $this->assertNotNull($nd);

        $expectedNeto = round(2420.00 / 1.21, 2); // 2000.00
        $expectedIva  = round(2420.00 - $expectedNeto, 2); // 420.00

        $this->assertEquals($expectedNeto, $nd->importe_ampliacion,
            'importe_ampliacion debe ser el neto sin IVA');
        $this->assertEquals($expectedIva, $nd->importe_iva,
            'importe_iva debe ser el 21% del neto');
        $this->assertEquals(2420.00, $nd->importe_total,
            'importe_total debe ser el valor ingresado con IVA');
    }

    /**
     * Test 7: Letra A con CUIT inválido → redirect con error + tab_nd
     */
    public function testBillCorregirNDLetraACuitInvalidoFalla()
    {
        // Crear talonario tipo A
        $talonarioA = $this->obtenerOCrearTalonario('A');

        // Cliente con DNI de 8 dígitos (no es CUIT válido de 11)
        $cliente = $this->crearClienteBase('12345678', 'A');
        // Asegurarse de que el cliente use el talonario A
        $cliente->talonario_id = $talonarioA->id;
        $cliente->save();

        $factura = $this->crearFacturaParaCliente($cliente);

        $controller = $this->crearControllerConAfipMock();
        $request    = new Request(['importe_nd' => '1500.00', 'motivo_nd' => 'Test CUIT A']);
        $response   = $controller->getBillCorregirNDPost($request, $factura->id);

        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('danger', $session->get('status'));
        $this->assertTrue((bool) $session->get('tab_nd'));
        $this->assertEquals(0, NotaDebito::where('factura_id', $factura->id)->count(),
            'No debe emitirse ND tipo A con CUIT inválido');
    }

    /**
     * Test 8: tab_nd está presente en TODOS los caminos de redirect (error + éxito)
     */
    public function testBillCorregirNDTabNdSiemprePresente()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente    = $this->crearClienteBase();
        $factura    = $this->crearFacturaParaCliente($cliente);

        // Validación falla
        $r1 = $controller->getBillCorregirNDPost(new Request([]), $factura->id);
        $this->assertTrue((bool) $r1->getSession()->get('tab_nd'), 'tab_nd en validación fallida');

        // Factura no existe
        $r2 = $controller->getBillCorregirNDPost(
            new Request(['importe_nd' => '100', 'motivo_nd' => 'x']),
            999999
        );
        $this->assertTrue((bool) $r2->getSession()->get('tab_nd'), 'tab_nd en factura no encontrada');

        // Éxito
        $r3 = $controller->getBillCorregirNDPost(
            new Request(['importe_nd' => '1210.00', 'motivo_nd' => 'Test tab_nd éxito']),
            $factura->id
        );
        $this->assertTrue((bool) $r3->getSession()->get('tab_nd'), 'tab_nd en respuesta exitosa');
    }

    // =========================================================
    // TESTS: postAfipCorreccionND
    // =========================================================

    /**
     * Test 9: Validación falla → redirect con tab=nd
     */
    public function testAfipCorreccionNDValidacionFalla()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente    = $this->crearClienteBase();
        $factura    = $this->crearFacturaParaCliente($cliente);

        // Sin motivo
        $request  = new Request(['importe' => '1500.00']);
        $response = $controller->postAfipCorreccionND($request, $factura->id);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('nd', $response->getSession()->get('tab'),
            'tab debe ser "nd" cuando la validación falla');
        $this->assertEquals(0, NotaDebito::where('factura_id', $factura->id)->count());
    }

    /**
     * Test 10: Factura no encontrada → redirect con error
     */
    public function testAfipCorreccionNDFacturaNoExiste()
    {
        $controller = $this->crearControllerConAfipMock();

        $request  = new Request(['importe' => '1500.00', 'motivo' => 'Test']);
        $response = $controller->postAfipCorreccionND($request, 999999);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('danger', $response->getSession()->get('status'));
    }

    /**
     * Test 11: AFIP no devuelve CAE → redirect con error + tab=nd
     */
    public function testAfipCorreccionNDSinCaeNoGuarda()
    {
        $controller = $this->crearControllerConAfipMock($this->crearAfipMockSinCae());
        $cliente    = $this->crearClienteBase();
        $factura    = $this->crearFacturaParaCliente($cliente);

        $request  = new Request(['importe' => '1500.00', 'motivo' => 'Test sin CAE']);
        $response = $controller->postAfipCorreccionND($request, $factura->id);

        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('danger', $session->get('status'));
        $this->assertEquals('nd', $session->get('tab'));
        $this->assertEquals(0, NotaDebito::where('factura_id', $factura->id)->count());
    }

    /**
     * Test 12: Emisión exitosa tipo B → crea NotaDebito y redirect con tab=nd + status=success
     */
    public function testAfipCorreccionNDExitosaCreaNDEnDB()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente    = $this->crearClienteBase();
        $factura    = $this->crearFacturaParaCliente($cliente, 6050.00);

        $request  = new Request(['importe' => '6050.00', 'motivo' => 'Ajuste por consumo adicional']);
        $response = $controller->postAfipCorreccionND($request, $factura->id);

        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('success', $session->get('status'));
        $this->assertEquals('nd', $session->get('tab'));

        $nd = NotaDebito::where('factura_id', $factura->id)->first();
        $this->assertNotNull($nd, 'Debe haberse creado una NotaDebito');
        $this->assertEquals(888, $nd->nro_nota_debito);
        $this->assertEquals('72345678901234', $nd->cae);
        $this->assertEquals(6050.00, $nd->importe_total);
        $this->assertEquals('Ajuste por consumo adicional', $nd->motivo);
        $this->assertEquals($factura->periodo, $nd->periodo);
    }

    /**
     * Test 13: Importes de ND calculados correctamente en postAfipCorreccionND
     */
    public function testAfipCorreccionNDImportesCorrectos()
    {
        $controller = $this->crearControllerConAfipMock();
        $cliente    = $this->crearClienteBase();
        $factura    = $this->crearFacturaParaCliente($cliente);

        $importeConIva = 3630.00; // neto: 3000.00, IVA: 630.00
        $request  = new Request(['importe' => (string) $importeConIva, 'motivo' => 'Verificar importes']);
        $controller->postAfipCorreccionND($request, $factura->id);

        $nd = NotaDebito::where('factura_id', $factura->id)->first();
        $this->assertNotNull($nd);

        $expectedNeto = round(3630.00 / 1.21, 2);
        $expectedIva  = round(3630.00 - $expectedNeto, 2);

        $this->assertEquals($expectedNeto, $nd->importe_ampliacion);
        $this->assertEquals($expectedIva, $nd->importe_iva);
        $this->assertEquals(3630.00, $nd->importe_total);
    }

    /**
     * Test 14: Letra A con CUIT inválido → redirect con error + tab=nd
     */
    public function testAfipCorreccionNDLetraACuitInvalidoFalla()
    {
        $talonarioA = $this->obtenerOCrearTalonario('A');
        $cliente    = $this->crearClienteBase('12345678', 'A');
        $cliente->talonario_id = $talonarioA->id;
        $cliente->save();
        $factura = $this->crearFacturaParaCliente($cliente);

        $controller = $this->crearControllerConAfipMock();
        $request    = new Request(['importe' => '1500.00', 'motivo' => 'Test CUIT A']);
        $response   = $controller->postAfipCorreccionND($request, $factura->id);

        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('danger', $session->get('status'));
        $this->assertEquals('nd', $session->get('tab'));
        $this->assertEquals(0, NotaDebito::where('factura_id', $factura->id)->count());
    }

    // =========================================================
    // TESTS: postAfipCorreccionNDManual
    // =========================================================

    /**
     * Test 15: Validación falla (sin talonario_id) → redirect con tab_nd_manual
     */
    public function testAfipCorreccionNDManualValidacionFalla()
    {
        $controller = $this->crearControllerConAfipMock();

        $request  = new Request([
            'nro_factura_orig' => '12345',
            'importe'          => '1500.00',
            'motivo'           => 'Test sin talonario',
            // sin talonario_id
        ]);
        $response = $controller->postAfipCorreccionNDManual($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue((bool) $response->getSession()->get('tab_nd_manual'),
            'tab_nd_manual debe ser true cuando la validación falla');
    }

    /**
     * Test 16: Tipo B exitoso → redirect con success (NO guarda en DB — es manual sin factura asociada)
     */
    public function testAfipCorreccionNDManualExitosaTipoBNoGuardaEnDB()
    {
        $controller = $this->crearControllerConAfipMock();
        $talonario  = $this->obtenerOCrearTalonario('B');

        $ndAntes = NotaDebito::count();

        $request  = new Request([
            'talonario_id'     => $talonario->id,
            'nro_factura_orig' => '9999',
            'importe'          => '2420.00',
            'motivo'           => 'ND manual por ajuste de servicio',
        ]);
        $response = $controller->postAfipCorreccionNDManual($request);

        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('success', $session->get('status'),
            'Emisión manual exitosa debe retornar status success');
        $this->assertContains('72345678901234', $session->get('message'),
            'El mensaje debe incluir el CAE retornado por AFIP');

        // No debe haberse creado ningún registro en notas_debito
        $this->assertEquals($ndAntes, NotaDebito::count(),
            'ND manual no debe guardar registro en la DB (es solo AFIP)');
    }

    /**
     * Test 17: Tipo A con CUIT inválido → redirect con error + tab_nd_manual
     */
    public function testAfipCorreccionNDManualLetraACuitInvalidoFalla()
    {
        $controller = $this->crearControllerConAfipMock();
        $talonarioA = $this->obtenerOCrearTalonario('A');

        $ndAntes = NotaDebito::count();

        $request  = new Request([
            'talonario_id'     => $talonarioA->id,
            'nro_factura_orig' => '1234',
            'importe'          => '1000.00',
            'motivo'           => 'Test CUIT A manual',
            'dni'              => '12345678', // 8 dígitos, no válido para A
        ]);
        $response = $controller->postAfipCorreccionNDManual($request);

        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('danger', $session->get('status'));
        $this->assertTrue((bool) $session->get('tab_nd_manual'));
        $this->assertEquals($ndAntes, NotaDebito::count(),
            'No debe crearse ningún registro si el CUIT es inválido');
    }

    /**
     * Test 18: AFIP no autoriza en ND manual → redirect con error + tab_nd_manual
     */
    public function testAfipCorreccionNDManualAfipNoAutorizaFalla()
    {
        $controller = $this->crearControllerConAfipMock($this->crearAfipMockSinCae());
        $talonario  = $this->obtenerOCrearTalonario('B');

        $request  = new Request([
            'talonario_id'     => $talonario->id,
            'nro_factura_orig' => '5555',
            'importe'          => '1500.00',
            'motivo'           => 'Test AFIP no autoriza',
        ]);
        $response = $controller->postAfipCorreccionNDManual($request);

        $this->assertEquals(302, $response->getStatusCode());
        $session = $response->getSession();
        $this->assertEquals('danger', $session->get('status'));
        $this->assertTrue((bool) $session->get('tab_nd_manual'));
    }

    /**
     * Test 19: Tipo A con CUIT válido (11 dígitos) → redirect exitoso
     */
    public function testAfipCorreccionNDManualLetraACuitValidoExitoso()
    {
        $controller = $this->crearControllerConAfipMock();
        $talonarioA = $this->obtenerOCrearTalonario('A');

        $request  = new Request([
            'talonario_id'     => $talonarioA->id,
            'nro_factura_orig' => '6789',
            'importe'          => '3630.00',
            'motivo'           => 'ND tipo A manual',
            'dni'              => '20123456789', // CUIT válido de 11 dígitos
        ]);
        $response = $controller->postAfipCorreccionNDManual($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('success', $response->getSession()->get('status'),
            'ND manual tipo A con CUIT válido debe ser exitosa');
    }
}

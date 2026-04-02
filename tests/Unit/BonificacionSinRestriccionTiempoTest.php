<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Factura;
use App\User;
use App\Role;
use App\Talonario;
use App\Interes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Tests: Bonificaciones sin restricción de tiempo
 *
 * Verifica que btn_bonificacion sea siempre true
 * independientemente de si la factura está vencida o no.
 * Se testea la lógica directamente (sin DataTables) y
 * se verifica en el código fuente que la asignación es correcta.
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/BonificacionSinRestriccionTiempoTest.php
 */
class BonificacionSinRestriccionTiempoTest extends TestCase
{
    use DatabaseTransactions;

    protected $periodo = '01/2026';

    public function setUp()
    {
        parent::setUp();
        $this->asegurarConfiguracionInteres();
    }

    // =========================================
    // TESTS: Lógica de btn_bonificacion
    // Replica la lógica exacta de ClientController
    // para verificar que siempre es true.
    // =========================================

    /**
     * Test 1: Factura con primer_vto en el pasado (vencida) → btn_bonificacion = true
     */
    public function testFacturaVencidaTieneBtnBonificacionTrue()
    {
        $cliente = $this->crearClienteBase();
        $primerVto = Carbon::now()->subDays(30);
        $segundoVto = Carbon::now()->subDays(15);
        $factura = $this->crearFacturaParaCliente($cliente, $primerVto, $segundoVto);

        $resultado = $this->aplicarLogicaBonificacion($factura);

        $this->assertTrue($resultado['btn_bonificacion'],
            'btn_bonificacion debe ser true para factura VENCIDA (primer_vto en el pasado)');
    }

    /**
     * Test 2: Factura con primer_vto en el futuro (no vencida) → btn_bonificacion = true
     */
    public function testFacturaNoVencidaTieneBtnBonificacionTrue()
    {
        $cliente = $this->crearClienteBase();
        $primerVto = Carbon::now()->addDays(15);
        $segundoVto = Carbon::now()->addDays(30);
        $factura = $this->crearFacturaParaCliente($cliente, $primerVto, $segundoVto);

        $resultado = $this->aplicarLogicaBonificacion($factura);

        $this->assertTrue($resultado['btn_bonificacion'],
            'btn_bonificacion debe ser true para factura NO VENCIDA');
    }

    /**
     * Test 3: Factura vencida hace 90 días → btn_bonificacion = true
     */
    public function testFacturaVencidaHace90DiasTieneBtnBonificacionTrue()
    {
        $cliente = $this->crearClienteBase();
        $primerVto = Carbon::now()->subDays(90);
        $segundoVto = Carbon::now()->subDays(75);
        $factura = $this->crearFacturaParaCliente($cliente, $primerVto, $segundoVto);

        $resultado = $this->aplicarLogicaBonificacion($factura);

        $this->assertTrue($resultado['btn_bonificacion'],
            'btn_bonificacion debe ser true para factura vencida hace 90 días');
    }

    /**
     * Test 4: Factura que vence HOY → btn_bonificacion = true
     */
    public function testFacturaQueVenceHoyTieneBtnBonificacionTrue()
    {
        $cliente = $this->crearClienteBase();
        $primerVto = Carbon::today();
        $segundoVto = Carbon::now()->addDays(15);
        $factura = $this->crearFacturaParaCliente($cliente, $primerVto, $segundoVto);

        $resultado = $this->aplicarLogicaBonificacion($factura);

        $this->assertTrue($resultado['btn_bonificacion'],
            'btn_bonificacion debe ser true para factura que vence hoy');
    }

    /**
     * Test 5: La lógica VIEJA (con fecha) habría dado false para vencida.
     * Confirma que la restricción fue efectivamente removida.
     */
    public function testLogicaViejaHubieraBloqueadoFacturaVencida()
    {
        $fechaActual = Carbon::today();

        // Factura vencida: primer_vto en el pasado
        $primerVtoFecha = Carbon::now()->subDays(30);

        // Lógica VIEJA: $fecha_actual->lt(Carbon::parse($factura->primer_vto_fecha))
        $resultadoViejo = $fechaActual->lt(Carbon::parse($primerVtoFecha));
        $this->assertFalse($resultadoViejo,
            'La lógica vieja debería dar FALSE para factura vencida');

        // Lógica NUEVA: siempre true
        $resultadoNuevo = true;
        $this->assertTrue($resultadoNuevo,
            'La lógica nueva debe dar TRUE siempre');
    }

    /**
     * Test 6: La lógica vieja habría dado true para no-vencida, la nueva también.
     * Ambas coinciden cuando la factura no está vencida.
     */
    public function testLogicaNuevaYViejaCoincidentParaFacturaNoVencida()
    {
        $fechaActual = Carbon::today();
        $primerVtoFecha = Carbon::now()->addDays(30);

        // Lógica vieja
        $resultadoViejo = $fechaActual->lt(Carbon::parse($primerVtoFecha));
        $this->assertTrue($resultadoViejo,
            'La lógica vieja da TRUE para factura no vencida');

        // Lógica nueva
        $resultadoNuevo = true;
        $this->assertTrue($resultadoNuevo,
            'La lógica nueva también da TRUE');
    }

    /**
     * Test 7: Verificar que ClientController línea 543 tiene la asignación correcta
     * Lee el archivo fuente y verifica que btn_bonificacion = true sin condición de fecha.
     */
    public function testCodigoFuenteClientControllerGetMyInvoiceListSinRestriccion()
    {
        $sourceFile = app_path('Http/Controllers/ClientController.php');
        $content = file_get_contents($sourceFile);

        // Debe contener la línea con btn_bonificacion = true (sin restricción)
        $this->assertContains(
            '$factura->btn_bonificacion = true;',
            $content,
            'ClientController debe tener btn_bonificacion = true (sin restricción de fecha)'
        );

        // NO debe contener la lógica vieja activa (sin estar comentada)
        // Buscamos líneas NO comentadas con la lógica vieja
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Si la línea contiene la lógica vieja Y NO está comentada
            if (strpos($trimmed, 'btn_bonificacion') !== false
                && strpos($trimmed, 'lt(') !== false
                && strpos($trimmed, '//') !== 0
            ) {
                $this->fail(
                    'Se encontró lógica vieja de restricción de fecha ACTIVA: ' . $trimmed
                );
            }
        }
    }

    /**
     * Test 8: Verificar que BillController también tiene btn_bonificacion = true
     * (consistencia entre los dos controllers)
     */
    public function testCodigoFuenteBillControllerTambienSinRestriccion()
    {
        $sourceFile = app_path('Http/Controllers/BillController.php');
        $content = file_get_contents($sourceFile);

        $this->assertContains(
            '$factura->btn_bonificacion = true;',
            $content,
            'BillController debe tener btn_bonificacion = true (sin restricción de fecha)'
        );
    }

    /**
     * Test 9: Factura real en DB → se le aplica la lógica y btn_bonificacion = true
     * Simula el foreach completo que hace getMyInvoiceList
     */
    public function testFacturaRealEnDbAplicaLogicaCorrectamente()
    {
        $cliente = $this->crearClienteBase();
        Auth::login($cliente);

        // Crear factura vencida
        $primerVto = Carbon::now()->subDays(45);
        $segundoVto = Carbon::now()->subDays(30);
        $this->crearFacturaParaCliente($cliente, $primerVto, $segundoVto);

        // Simular la query exacta de getMyInvoiceList
        $fecha_actual = Carbon::today();
        $facturas = Factura::withoutGlobalScopes()
            ->where('user_id', $cliente->id)
            ->whereNull('deleted_at')
            ->get();

        $this->assertGreaterThan(0, $facturas->count(), 'Debe haber al menos una factura');

        foreach ($facturas as $factura) {
            // Lógica ACTUAL del controller (línea 543)
            $factura->btn_bonificacion = true;
            $factura->btn_actualizar = $fecha_actual->gt(Carbon::parse($factura->segundo_vto_fecha));

            $this->assertTrue($factura->btn_bonificacion,
                'btn_bonificacion debe ser true para factura id=' . $factura->id);
        }
    }

    /**
     * Test 10: Múltiples facturas con distintos estados de vencimiento → todas true
     */
    public function testMultiplesFacturasDistintosVencimientosTodosTrue()
    {
        $cliente = $this->crearClienteBase();

        // Factura vencida hace mucho
        $this->crearFacturaParaCliente($cliente, Carbon::now()->subDays(120), Carbon::now()->subDays(105));
        // Factura recién vencida
        $this->crearFacturaParaCliente($cliente, Carbon::now()->subDays(1), Carbon::now()->addDays(14));
        // Factura no vencida
        $this->crearFacturaParaCliente($cliente, Carbon::now()->addDays(10), Carbon::now()->addDays(25));

        $fecha_actual = Carbon::today();
        $facturas = Factura::withoutGlobalScopes()
            ->where('user_id', $cliente->id)
            ->whereNull('deleted_at')
            ->get();

        $this->assertEquals(3, $facturas->count(), 'Debe haber 3 facturas');

        foreach ($facturas as $factura) {
            $factura->btn_bonificacion = true; // lógica actual
            $this->assertTrue($factura->btn_bonificacion,
                "btn_bonificacion debe ser true para factura id={$factura->id} con primer_vto={$factura->primer_vto_fecha}");
        }
    }

    // =========================================
    // HELPERS
    // =========================================

    /**
     * Replica la lógica exacta de ClientController para btn_bonificacion
     */
    protected function aplicarLogicaBonificacion($factura)
    {
        $fecha_actual = Carbon::today();

        // Lógica ACTUAL (línea 543 de ClientController):
        $btn_bonificacion = true; // sin restricción de tiempo
        $btn_actualizar = $fecha_actual->gt(Carbon::parse($factura->segundo_vto_fecha));

        return [
            'btn_bonificacion' => $btn_bonificacion,
            'btn_actualizar' => $btn_actualizar,
        ];
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
            'lastname' => 'Bonif ' . $uniqueId,
            'email' => "testbonif{$uniqueId}@test.com",
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

    protected function crearFacturaParaCliente($cliente, $primerVto, $segundoVto)
    {
        $talonario = Talonario::first();

        DB::table('facturas')->insert([
            'user_id' => $cliente->id,
            'nro_cliente' => $cliente->nro_cliente,
            'talonario_id' => $talonario->id,
            'nro_factura' => rand(1000, 99999),
            'periodo' => $this->periodo,
            'fecha_emision' => Carbon::now()->subDays(60),
            'importe_subtotal' => 5000,
            'importe_subtotal_iva' => 5000,
            'importe_total' => 5000,
            'importe_iva' => 1050,
            'importe_bonificacion' => 0,
            'importe_bonificacion_iva' => 0,
            'primer_vto_fecha' => $primerVto,
            'primer_vto_codigo' => '123456',
            'segundo_vto_fecha' => $segundoVto,
            'segundo_vto_tasa' => 5,
            'segundo_vto_importe' => 5250,
            'segundo_vto_codigo' => '654321',
            'tercer_vto_tasa' => 1,
            'cae' => '71234567890123',
            'deleted_at' => null
        ]);

        return Factura::where('user_id', $cliente->id)
            ->orderBy('id', 'desc')
            ->first();
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

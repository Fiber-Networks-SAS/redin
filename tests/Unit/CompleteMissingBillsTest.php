<?php

use TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\BillController;
use App\Services\PaymentQRService;
use App\Services\AfipService;
use App\Factura;
use App\FacturaDetalle;
use App\User;
use App\Role;
use App\Servicio;
use App\ServicioUsuario;
use App\Talonario;
use App\Interes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Tests unitarios para BillController - Completar Facturas Faltantes
 *
 * Casos de uso probados:
 * 1. Identificar clientes sin factura en un periodo
 * 2. No duplicar facturas para clientes ya facturados
 * 3. Generar facturas solo para clientes faltantes
 * 4. Ignorar facturas anuladas (deleted_at) y permitir refacturación
 * 5. Validar parámetros requeridos
 * 6. Manejar errores individuales sin afectar otras facturas
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/CompleteMissingBillsTest.php
 */
class CompleteMissingBillsTest extends TestCase
{
    use DatabaseTransactions;

    protected $controller;
    protected $periodo = '01/2026';
    protected $fechaEmision = '04/02/2026';

    public function setUp()
    {
        parent::setUp();
        
        // Usar el contenedor de Laravel para resolver el controller con sus dependencias
        $this->controller = app()->make(BillController::class);
        
        // Asegurar que existe configuración de intereses
        $this->asegurarConfiguracionInteres();
    }

    // =========================================
    // TESTS DE VERIFICACIÓN (verifyMissingBills)
    // =========================================

    /**
     * Test 1: Verificar que se identifican correctamente los clientes sin factura
     */
    public function testVerifyIdentificaClientesSinFactura()
    {
        // Arrange: Crear 3 clientes activos
        $clientesConFactura = $this->crearClientesConFactura(2);
        $clientesSinFactura = $this->crearClientesSinFactura(3);

        // Act: Llamar a verifyMissingBills
        $request = new Request(['periodo' => $this->periodo]);
        $response = $this->controller->verifyMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert
        $this->assertTrue($data['success']);
        $this->assertEquals($this->periodo, $data['periodo']);
        
        // Verificar que los clientes sin factura están en la lista
        $idsEncontrados = array_column($data['clientes'], 'id');
        foreach ($clientesSinFactura as $cliente) {
            $this->assertContains($cliente->id, $idsEncontrados, 
                "Cliente {$cliente->id} sin factura debería estar en la lista");
        }
        
        // Verificar que los clientes con factura NO están en la lista
        foreach ($clientesConFactura as $cliente) {
            $this->assertNotContains($cliente->id, $idsEncontrados,
                "Cliente {$cliente->id} con factura NO debería estar en la lista");
        }
    }

    /**
     * Test 2: Verificar que clientes con factura activa NO aparecen como faltantes
     */
    public function testVerifyNoIncluyeClientesConFacturaActiva()
    {
        // Arrange: Crear cliente con factura activa
        $clienteConFactura = $this->crearClienteConFacturaActiva();

        // Act
        $request = new Request(['periodo' => $this->periodo]);
        $response = $this->controller->verifyMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert
        $idsEncontrados = array_column($data['clientes'], 'id');
        $this->assertNotContains($clienteConFactura->id, $idsEncontrados,
            "Cliente con factura activa NO debe aparecer como faltante");
    }

    /**
     * Test 3: Verificar que clientes con factura ANULADA SÍ aparecen como faltantes
     */
    public function testVerifyIncluyeClientesConFacturaAnulada()
    {
        // Arrange: Crear cliente con factura anulada (soft deleted)
        $clienteConFacturaAnulada = $this->crearClienteConFacturaAnulada();

        // Act
        $request = new Request(['periodo' => $this->periodo]);
        $response = $this->controller->verifyMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert
        $idsEncontrados = array_column($data['clientes'], 'id');
        $this->assertContains($clienteConFacturaAnulada->id, $idsEncontrados,
            "Cliente con factura ANULADA SÍ debe aparecer como faltante para refacturar");
    }

    /**
     * Test 4: Verificar que requiere parámetro periodo
     */
    public function testVerifyRequierePeriodo()
    {
        // Act: Llamar sin periodo
        $request = new Request([]);
        $response = $this->controller->verifyMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert
        $this->assertFalse($data['success']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test 5: Verificar que solo incluye clientes con servicios FACTURABLES
     */
    public function testVerifySoloIncluyeClientesConServiciosFacturables()
    {
        // Arrange: Cliente sin servicios facturables para el periodo
        $clienteSinServiciosFacturables = $this->crearClienteSinServiciosFacturables();
        
        // Cliente con servicios facturables
        $clienteConServiciosFacturables = $this->crearClienteConServiciosFacturables();

        // Act
        $request = new Request(['periodo' => $this->periodo]);
        $response = $this->controller->verifyMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert
        $idsEncontrados = array_column($data['clientes'], 'id');
        
        $this->assertNotContains($clienteSinServiciosFacturables->id, $idsEncontrados,
            "Cliente SIN servicios facturables NO debe aparecer");
        $this->assertContains($clienteConServiciosFacturables->id, $idsEncontrados,
            "Cliente CON servicios facturables SÍ debe aparecer");
    }

    // =========================================
    // TESTS DE COMPLETAR (completeMissingBills)
    // =========================================

    /**
     * Test 6: Completar facturas - No genera duplicados para clientes ya facturados
     */
    public function testCompleteNoGeneraDuplicados()
    {
        // Arrange: Crear cliente con factura existente
        $clienteConFactura = $this->crearClienteConFacturaActiva();
        $facturaExistenteId = Factura::where('user_id', $clienteConFactura->id)
            ->where('periodo', $this->periodo)
            ->first()->id;

        // Contar facturas antes
        $facturasAntes = Factura::where('user_id', $clienteConFactura->id)
            ->where('periodo', $this->periodo)
            ->count();

        // Act
        $request = new Request([
            'periodo' => $this->periodo,
            'fecha_emision' => $this->fechaEmision
        ]);
        $response = $this->controller->completeMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert: No se crearon facturas adicionales para este cliente
        $facturasDespues = Factura::where('user_id', $clienteConFactura->id)
            ->where('periodo', $this->periodo)
            ->count();

        $this->assertEquals($facturasAntes, $facturasDespues,
            "No debe crear facturas duplicadas para cliente ya facturado");
    }

    /**
     * Test 7: Completar facturas - Genera factura para cliente faltante
     */
    public function testCompleteGeneraFacturaParaClienteFaltante()
    {
        // Arrange: Crear cliente sin factura pero con servicios facturables
        $clienteSinFactura = $this->crearClienteConServiciosFacturables();

        // Verificar que no tiene factura
        $facturaAntes = Factura::where('user_id', $clienteSinFactura->id)
            ->where('periodo', $this->periodo)
            ->first();
        $this->assertNull($facturaAntes, "Pre-condición: cliente no debe tener factura");

        // Act
        $request = new Request([
            'periodo' => $this->periodo,
            'fecha_emision' => $this->fechaEmision
        ]);
        $response = $this->controller->completeMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert: Se creó la factura
        $facturaDespues = Factura::where('user_id', $clienteSinFactura->id)
            ->where('periodo', $this->periodo)
            ->first();

        $this->assertNotNull($facturaDespues, "Debe crear factura para cliente faltante");
        $this->assertEquals($clienteSinFactura->id, $facturaDespues->user_id);
        $this->assertEquals($this->periodo, $facturaDespues->periodo);
    }

    /**
     * Test 8: Completar facturas - Genera nueva factura para cliente con factura ANULADA
     */
    public function testCompleteGeneraFacturaParaClienteConFacturaAnulada()
    {
        // Arrange: Crear cliente con factura anulada
        $cliente = $this->crearClienteConFacturaAnulada();

        // Act
        $request = new Request([
            'periodo' => $this->periodo,
            'fecha_emision' => $this->fechaEmision
        ]);
        $response = $this->controller->completeMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert: Se creó nueva factura (la anulada sigue existiendo con deleted_at)
        $facturaActiva = Factura::where('user_id', $cliente->id)
            ->where('periodo', $this->periodo)
            ->whereNull('deleted_at')
            ->first();

        $this->assertNotNull($facturaActiva, 
            "Debe crear nueva factura para cliente con factura anulada");
    }

    /**
     * Test 9: Validar parámetros requeridos - periodo
     */
    public function testCompleteRequierePeriodo()
    {
        // Act
        $request = new Request([
            'fecha_emision' => $this->fechaEmision
        ]);
        $response = $this->controller->completeMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert
        $this->assertFalse($data['success']);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
    }

    /**
     * Test 10: Validar parámetros requeridos - fecha_emision
     */
    public function testCompleteRequiereFechaEmision()
    {
        // Act
        $request = new Request([
            'periodo' => $this->periodo
        ]);
        $response = $this->controller->completeMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert
        $this->assertFalse($data['success']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test 11: Validar formato de periodo
     */
    public function testCompleteValidaFormatoPeriodo()
    {
        // Act
        $request = new Request([
            'periodo' => '2026-01', // Formato incorrecto
            'fecha_emision' => $this->fechaEmision
        ]);
        $response = $this->controller->completeMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert
        $this->assertFalse($data['success']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Test 12: Retorna mensaje correcto cuando no hay facturas faltantes
     */
    public function testCompleteRetornaMensajeCuandoNoHayFaltantes()
    {
        // Arrange: Crear clientes y facturarlos a todos
        $clientes = $this->crearClientesConFactura(3);

        // Act
        $request = new Request([
            'periodo' => $this->periodo,
            'fecha_emision' => $this->fechaEmision
        ]);
        $response = $this->controller->completeMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert
        $this->assertTrue($data['success']);
        $this->assertEquals(0, $data['facturas_creadas'] ?? $data['summary']['facturas_creadas'] ?? 0);
        $this->assertContains('No hay facturas faltantes', $data['message']);
    }

    /**
     * Test 13: El proceso es idempotente - ejecutar dos veces no duplica
     */
    public function testCompleteEsIdempotente()
    {
        // Arrange: Crear cliente sin factura
        $cliente = $this->crearClienteConServiciosFacturables();

        $request = new Request([
            'periodo' => $this->periodo,
            'fecha_emision' => $this->fechaEmision
        ]);

        // Act: Ejecutar dos veces
        $response1 = $this->controller->completeMissingBills($request);
        $data1 = json_decode($response1->getContent(), true);

        $response2 = $this->controller->completeMissingBills($request);
        $data2 = json_decode($response2->getContent(), true);

        // Assert: Primera ejecución crea, segunda no
        $facturasCreadas1 = $data1['summary']['facturas_creadas'] ?? $data1['facturas_creadas'] ?? 0;
        $facturasCreadas2 = $data2['summary']['facturas_creadas'] ?? $data2['facturas_creadas'] ?? 0;

        $this->assertGreaterThan(0, $facturasCreadas1, "Primera ejecución debe crear facturas");
        $this->assertEquals(0, $facturasCreadas2, "Segunda ejecución NO debe crear facturas");

        // Verificar que solo hay una factura
        $totalFacturas = Factura::where('user_id', $cliente->id)
            ->where('periodo', $this->periodo)
            ->count();
        $this->assertEquals(1, $totalFacturas, "Debe haber exactamente 1 factura");
    }

    /**
     * Test 14: Genera detalle de factura correctamente
     */
    public function testCompleteGeneraDetalleFactura()
    {
        // Arrange
        $cliente = $this->crearClienteConServiciosFacturables();

        // Act
        $request = new Request([
            'periodo' => $this->periodo,
            'fecha_emision' => $this->fechaEmision
        ]);
        $this->controller->completeMissingBills($request);

        // Assert
        $factura = Factura::where('user_id', $cliente->id)
            ->where('periodo', $this->periodo)
            ->first();

        $this->assertNotNull($factura, "Debe existir la factura");

        $detalles = FacturaDetalle::where('factura_id', $factura->id)->get();
        $this->assertGreaterThan(0, $detalles->count(), 
            "Debe tener detalle de factura");
    }

    /**
     * Test 15: Resumen incluye información correcta
     */
    public function testCompleteRetornaResumenCorrecto()
    {
        // Arrange
        $this->crearClienteConServiciosFacturables();
        $this->crearClienteConServiciosFacturables();

        // Act
        $request = new Request([
            'periodo' => $this->periodo,
            'fecha_emision' => $this->fechaEmision
        ]);
        $response = $this->controller->completeMissingBills($request);
        $data = json_decode($response->getContent(), true);

        // Assert
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('summary', $data);
        $this->assertArrayHasKey('periodo', $data['summary']);
        $this->assertArrayHasKey('facturas_creadas', $data['summary']);
        $this->assertArrayHasKey('errores', $data['summary']);
        $this->assertArrayHasKey('processed_at', $data['summary']);
    }

    // =========================================
    // HELPERS - Métodos para crear datos de prueba
    // =========================================

    /**
     * Crear clientes con factura existente en el periodo
     */
    protected function crearClientesConFactura($cantidad)
    {
        $clientes = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $cliente = $this->crearClienteConFacturaActiva();
            $clientes[] = $cliente;
        }
        return $clientes;
    }

    /**
     * Crear clientes sin factura en el periodo
     */
    protected function crearClientesSinFactura($cantidad)
    {
        $clientes = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $cliente = $this->crearClienteConServiciosFacturables();
            $clientes[] = $cliente;
        }
        return $clientes;
    }

    /**
     * Crear un cliente activo con factura activa en el periodo
     */
    protected function crearClienteConFacturaActiva()
    {
        $cliente = $this->crearClienteBase();
        $this->crearServicioParaCliente($cliente);
        $this->crearFacturaParaCliente($cliente, false); // No anulada
        return $cliente;
    }

    /**
     * Crear un cliente con factura ANULADA (soft deleted)
     */
    protected function crearClienteConFacturaAnulada()
    {
        $cliente = $this->crearClienteBase();
        $this->crearServicioParaCliente($cliente);
        $this->crearFacturaParaCliente($cliente, true); // Anulada
        return $cliente;
    }

    /**
     * Crear un cliente sin servicios facturables para el periodo
     */
    protected function crearClienteSinServiciosFacturables()
    {
        $cliente = $this->crearClienteBase();
        // No crear servicios o crear con fecha de alta futura
        $this->crearServicioParaCliente($cliente, '12/2026'); // Alta futura
        return $cliente;
    }

    /**
     * Crear un cliente con servicios facturables
     */
    protected function crearClienteConServiciosFacturables()
    {
        $cliente = $this->crearClienteBase();
        $this->crearServicioParaCliente($cliente, '01/2025'); // Alta pasada
        return $cliente;
    }

    /**
     * Crear usuario base con rol client
     */
    protected function crearClienteBase()
    {
        // Buscar o crear rol client
        $role = Role::firstOrCreate(['name' => 'client']);

        // Buscar talonario existente o crear uno
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

        // Crear usuario - usar unguard para permitir mass assignment en tests
        $uniqueId = uniqid();
        User::unguard();
        $user = User::create([
            'nro_cliente' => rand(10000, 99999),
            'firstname' => 'Test',
            'lastname' => 'User ' . $uniqueId,
            'email' => "test{$uniqueId}@test.com",
            'password' => bcrypt('password'),
            'dni' => rand(10000000, 99999999),
            'status' => 1,
            'calle' => 'Calle Test',
            'altura' => rand(100, 9999),
            'talonario_id' => $talonario->id
        ]);
        User::reguard();

        // Asignar rol
        $user->roles()->attach($role->id);

        return $user;
    }

    /**
     * Crear servicio para un cliente
     */
    protected function crearServicioParaCliente($cliente, $altaServicio = '01/2025')
    {
        // Buscar servicio existente o crear uno
        $servicio = Servicio::first();
        if (!$servicio) {
            Servicio::unguard();
            $servicio = Servicio::create([
                'nombre' => 'Internet Test',
                'abono_mensual' => 5000,
                'costo_instalacion' => 10000,
                'status' => 1
            ]);
            Servicio::reguard();
        }

        // Crear relación usuario-servicio
        $altaDate = Carbon::createFromFormat('m/Y', $altaServicio)->startOfMonth();
        
        ServicioUsuario::unguard();
        ServicioUsuario::create([
            'user_id' => $cliente->id,
            'servicio_id' => $servicio->id,
            'alta_servicio' => $altaDate,
            'abono_mensual' => $servicio->abono_mensual,
            'costo_instalacion' => $servicio->costo_instalacion,
            'plan_pago' => 1,
            'abono_proporcional' => 0,
            'status' => 1,
            'pp_flag' => 0
        ]);
        ServicioUsuario::reguard();

        return $servicio;
    }

    /**
     * Crear factura para un cliente usando DB directamente
     */
    protected function crearFacturaParaCliente($cliente, $anulada = false)
    {
        $talonario = Talonario::first();
        $nroFactura = rand(1000, 9999);

        // Insertar factura directamente con DB para evitar problemas de fillable
        DB::table('facturas')->insert([
            'user_id' => $cliente->id,
            'nro_cliente' => $cliente->nro_cliente,
            'talonario_id' => $talonario->id,
            'nro_factura' => $nroFactura,
            'periodo' => $this->periodo,
            'fecha_emision' => Carbon::createFromFormat('d/m/Y', $this->fechaEmision),
            'importe_subtotal' => 5000,
            'importe_subtotal_iva' => 5000,
            'importe_total' => 5000,
            'importe_iva' => 1050,
            'importe_bonificacion' => 0,
            'importe_bonificacion_iva' => 0,
            'primer_vto_fecha' => Carbon::now()->addDays(15),
            'primer_vto_codigo' => '123456',
            'segundo_vto_fecha' => Carbon::now()->addDays(30),
            'segundo_vto_tasa' => 5,
            'segundo_vto_importe' => 5250,
            'segundo_vto_codigo' => '654321',
            'tercer_vto_tasa' => 1,
            'deleted_at' => $anulada ? Carbon::now() : null
        ]);

        $factura = Factura::withTrashed()
            ->where('user_id', $cliente->id)
            ->where('periodo', $this->periodo)
            ->first();

        return $factura;
    }

    /**
     * Asegurar que existe configuración de intereses
     */
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

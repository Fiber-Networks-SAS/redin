<?php

use TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\ClientController;
use App\User;
use App\Role;
use App\Talonario;

/**
 * Tests: Autocompletado de clientes muestra DNI
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/ClientAutocompleteDniTest.php
 */
class ClientAutocompleteDniTest extends TestCase
{
    use DatabaseTransactions;

    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = app()->make(ClientController::class);
    }

    /**
     * Test: Cliente con DNI muestra "Nombre Apellido - DNI"
     */
    public function testClienteConDniMuestraDni()
    {
        $user = $this->crearClienteActivo('Juan', 'Perez', 30123456);

        $response = $this->controller->getClientList();

        // getClientList retorna un array PHP, no JSON
        $data = is_array($response) ? $response : json_decode($response, true);

        $encontrado = $this->buscarClienteEnLista($data, $user->id);

        $this->assertNotNull($encontrado, 'Cliente debería estar en la lista');
        $this->assertContains('30123456', $encontrado['value']);
        $this->assertContains(' - ', $encontrado['value']);
        $this->assertEquals('Juan Perez - 30123456', $encontrado['value']);
    }

    /**
     * Test: Cliente sin DNI no muestra guión extra
     */
    public function testClienteSinDniNoMuestraGuion()
    {
        // dni = null en la DB (campo integer nullable)
        $user = $this->crearClienteActivo('Maria', 'Lopez', null);

        $response = $this->controller->getClientList();
        $data = is_array($response) ? $response : json_decode($response, true);

        $encontrado = $this->buscarClienteEnLista($data, $user->id);

        $this->assertNotNull($encontrado, 'Cliente debería estar en la lista');
        $this->assertEquals('Maria Lopez', $encontrado['value']);
    }

    // ==================== HELPERS ====================

    protected function crearClienteActivo($firstname, $lastname, $dni)
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

        User::unguard();
        $user = User::create([
            'nro_cliente' => rand(10000, 99999),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => strtolower($firstname) . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'dni' => $dni,
            'status' => 1,
            'talonario_id' => $talonario->id
        ]);
        User::reguard();

        $user->roles()->attach($role->id);

        return $user;
    }

    protected function buscarClienteEnLista($data, $userId)
    {
        if (!is_array($data)) return null;

        foreach ($data as $item) {
            if (isset($item['data']) && $item['data'] == $userId) {
                return $item;
            }
        }

        return null;
    }
}

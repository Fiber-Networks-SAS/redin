<?php

use TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\HomeSetting;
use App\Servicio;

/**
 * Tests: Override de precios web en HomeController
 * Verifica que los precios de la landing se sobreescriban cuando existe un HomeSetting
 *
 * Ejecutar con: php74 vendor/phpunit/phpunit/phpunit tests/Unit/HomePreciosWebTest.php
 */
class HomePreciosWebTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test: Servicio sin override usa precio del sistema
     */
    public function testServicioSinOverrideUsaPrecioSistema()
    {
        $servicio = $this->crearServicioPrueba(5000, 10000);

        $homeSettings = HomeSetting::all()->keyBy('key');

        $this->aplicarOverrides($servicio, $homeSettings);

        $this->assertEquals(5000, $servicio->abono_mensual);
        $this->assertEquals(10000, $servicio->costo_instalacion);
    }

    /**
     * Test: Servicio con override de abono usa precio web
     */
    public function testServicioConOverrideAbonoUsaPrecioWeb()
    {
        $servicio = $this->crearServicioPrueba(5000, 10000);
        $this->crearHomeSetting('web_price_abono_' . $servicio->id, '3500.50');

        $homeSettings = HomeSetting::all()->keyBy('key');
        $this->aplicarOverrides($servicio, $homeSettings);

        $this->assertEquals(3500.50, $servicio->abono_mensual);
        $this->assertEquals(10000, $servicio->costo_instalacion);
    }

    /**
     * Test: Servicio con override de instalación usa precio web
     */
    public function testServicioConOverrideInstalacionUsaPrecioWeb()
    {
        $servicio = $this->crearServicioPrueba(5000, 10000);
        $this->crearHomeSetting('web_price_inst_' . $servicio->id, '7500');

        $homeSettings = HomeSetting::all()->keyBy('key');
        $this->aplicarOverrides($servicio, $homeSettings);

        $this->assertEquals(5000, $servicio->abono_mensual);
        $this->assertEquals(7500, $servicio->costo_instalacion);
    }

    /**
     * Test: Override con valor vacío no sobreescribe
     */
    public function testOverrideVacioNoSobreescribe()
    {
        $servicio = $this->crearServicioPrueba(5000, 10000);
        $this->crearHomeSetting('web_price_abono_' . $servicio->id, '');

        $homeSettings = HomeSetting::all()->keyBy('key');
        $this->aplicarOverrides($servicio, $homeSettings);

        $this->assertEquals(5000, $servicio->abono_mensual);
    }

    /**
     * Test: Override con ambos precios funciona
     */
    public function testOverrideAmbosPreciosFunciona()
    {
        $servicio = $this->crearServicioPrueba(5000, 10000);
        $this->crearHomeSetting('web_price_abono_' . $servicio->id, '2999.99');
        $this->crearHomeSetting('web_price_inst_' . $servicio->id, '0');

        $homeSettings = HomeSetting::all()->keyBy('key');
        $this->aplicarOverrides($servicio, $homeSettings);

        $this->assertEquals(2999.99, $servicio->abono_mensual);
        // '0' !== '' por lo que sí sobreescribe
        $this->assertEquals(0, $servicio->costo_instalacion);
    }

    // ==================== HELPERS ====================

    /**
     * Replica la lógica exacta del HomeController
     */
    protected function aplicarOverrides($servicio, $homeSettings)
    {
        $keyAbono = 'web_price_abono_' . $servicio->id;
        $keyInst  = 'web_price_inst_'  . $servicio->id;
        if (isset($homeSettings[$keyAbono]) && $homeSettings[$keyAbono]->value !== '') {
            $servicio->abono_mensual = (float) $homeSettings[$keyAbono]->value;
        }
        if (isset($homeSettings[$keyInst]) && $homeSettings[$keyInst]->value !== '') {
            $servicio->costo_instalacion = (float) $homeSettings[$keyInst]->value;
        }
    }

    protected function crearServicioPrueba($abono, $instalacion)
    {
        Servicio::unguard();
        $servicio = Servicio::create([
            'nombre' => 'Test Plan ' . uniqid(),
            'tipo' => '0',
            'abono_mensual' => $abono,
            'costo_instalacion' => $instalacion,
            'status' => 1
        ]);
        Servicio::reguard();

        return $servicio;
    }

    protected function crearHomeSetting($key, $value)
    {
        HomeSetting::unguard();
        HomeSetting::create([
            'key' => $key,
            'value' => $value,
        ]);
        HomeSetting::reguard();
    }
}

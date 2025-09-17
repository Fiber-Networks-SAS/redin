<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PaymentServiceInterface;
use App\Contracts\QRCodeServiceInterface;
use App\Services\MercadoPagoService;
use App\Services\QRCodeService;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PaymentServiceInterface::class, MercadoPagoService::class);
        $this->app->bind(QRCodeServiceInterface::class, QRCodeService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
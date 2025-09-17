<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\PaymentPreference;

class CheckQRCodes extends Command
{
    protected $signature = 'check:qr {factura_id}';
    protected $description = 'Verificar códigos QR de una factura';

    public function handle()
    {
        $facturaId = $this->argument('factura_id');
        
        $preferences = PaymentPreference::where('factura_id', $facturaId)->get();
        
        if ($preferences->count() == 0) {
            $this->error("No se encontraron códigos QR para la factura $facturaId");
            return;
        }
        
        foreach ($preferences as $pref) {
            $this->info("Vencimiento: {$pref->vencimiento_tipo}");
            $this->info("MercadoPago ID: {$pref->mercadopago_preference_id}");
            $this->info("QR Code: " . (strlen($pref->qr_code_base64) > 100 ? 'YES (' . strlen($pref->qr_code_base64) . ' chars)' : 'NO'));
            $this->info("---");
        }
    }
}
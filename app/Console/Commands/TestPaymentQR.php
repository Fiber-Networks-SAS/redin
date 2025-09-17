<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentQRService;
use App\Factura;

class TestPaymentQR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:test-qr {factura_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test QR code generation for a specific invoice';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $facturaId = $this->argument('factura_id');
        
        $this->info("Testing QR generation for factura ID: {$facturaId}");
        
        // Buscar la factura
        $factura = Factura::find($facturaId);
        
        if (!$factura) {
            $this->error("Factura with ID {$facturaId} not found");
            return 1;
        }
        
        $this->info("Found factura: {$factura->nro_factura}");
        
        // Verificar si existe relación con cliente
        if ($factura->cliente) {
            $this->info("Client: {$factura->cliente->firstname} {$factura->cliente->lastname}");
        } else {
            $this->info("No client relation found for this factura");
        }
        
        // Inicializar el servicio de QR
        $paymentQRService = app(PaymentQRService::class);
        
        // Generar QR codes para cada vencimiento
        $vencimientos = ['primer', 'segundo', 'tercer'];
        
        foreach ($vencimientos as $vencimiento) {
            $this->info("Generating QR for {$vencimiento} vencimiento...");
            
            try {
                $result = $paymentQRService->createPaymentQR($factura, $vencimiento);
                
                if ($result && is_object($result) && get_class($result) === 'App\\PaymentPreference') {
                    $this->info("✓ QR generated successfully for {$vencimiento}");
                    $this->info("  Preference ID: {$result->preference_id}");
                    $this->info("  Amount: \${$result->amount}");
                } elseif (is_array($result) && isset($result['success']) && !$result['success']) {
                    $this->error("✗ Failed to generate QR for {$vencimiento}");
                    $this->error("  Error: {$result['error']}");
                    if (isset($result['trace'])) {
                        $this->error("  Trace: " . substr($result['trace'], 0, 200) . "...");
                    }
                } else {
                    $this->error("✗ Failed to generate QR for {$vencimiento} - Unknown result");
                }
                
            } catch (\Exception $e) {
                $this->error("Exception generating QR for {$vencimiento}: " . $e->getMessage());
            }
        }
        
        return 0;
    }
}
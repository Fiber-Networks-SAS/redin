<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BillController;
use App\Factura;
use App\User;
use App\Talonario;
use Exception;

class GenerateSingleInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:generate-single 
                            {user_id : ID del cliente}
                            {--period= : Período (formato YYYY-MM, ej: 2024-09)}
                            {--amount= : Importe total de la factura}
                            {--service= : Descripción del servicio}
                            {--pdf : Generar PDF inmediatamente}
                            {--qr : Generar códigos QR de MercadoPago}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a single invoice with optional PDF and QR codes';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $userId = $this->argument('user_id');
            $period = $this->option('period') ?: date('Y-m');
            $amount = $this->option('amount') ?: $this->ask('Ingrese el importe total de la factura');
            $service = $this->option('service') ?: $this->ask('Descripción del servicio', 'Servicio de Internet');
            $generatePdf = $this->option('pdf');
            $generateQr = $this->option('qr');
            
            // Validar usuario
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuario con ID {$userId} no encontrado");
                return 1;
            }
            
            $this->info("Generando factura para: {$user->firstname} {$user->lastname}");
            $this->info("Email: {$user->email}");
            $this->info("Período: {$period}");
            $this->info("Importe: \${$amount}");
            
            if (!$this->confirm('¿Continuar con la generación de la factura?')) {
                $this->info('Operación cancelada');
                return 0;
            }
            
            // Obtener talonario (usar el primero disponible)
            $talonario = Talonario::first();
            if (!$talonario) {
                $this->error('No se encontró ningún talonario configurado');
                return 1;
            }
            
            // Generar número de factura (siguiente disponible)
            $lastFactura = Factura::where('talonario_id', $talonario->id)
                                 ->orderBy('nro_factura', 'desc')
                                 ->first();
            
            $nextNumber = $lastFactura ? ($lastFactura->nro_factura + 1) : 1;
            
            // Calcular vencimientos
            $baseAmount = floatval($amount);
            $secondAmount = $baseAmount * 1.03; // 3% de recargo
            $thirdAmount = $baseAmount * 1.05;  // 5% de recargo
            
            $firstDue = date('Y-m-d', strtotime('+10 days'));
            $secondDue = date('Y-m-d', strtotime('+20 days'));
            $thirdDue = date('Y-m-d', strtotime('+30 days'));
            
            // Crear la factura
            $factura = new Factura();
            $factura->talonario_id = $talonario->id;
            $factura->nro_factura = $nextNumber;
            $factura->user_id = $userId;
            $factura->nro_cliente = $userId; // Campo requerido
            $factura->periodo = $period;
            $factura->fecha_emision = date('Y-m-d H:i:s');
            $factura->importe_subtotal = $baseAmount;
            $factura->importe_bonificacion = 0;
            $factura->importe_total = $baseAmount;
            $factura->primer_vto_fecha = date('Y-m-d H:i:s', strtotime('+10 days'));
            $factura->primer_vto_codigo = $this->generateBarcodeNumber($nextNumber, 1);
            $factura->segundo_vto_fecha = date('Y-m-d H:i:s', strtotime('+20 days'));
            $factura->segundo_vto_tasa = 3.00;
            $factura->segundo_vto_importe = $secondAmount;
            $factura->segundo_vto_codigo = $this->generateBarcodeNumber($nextNumber, 2);
            $factura->tercer_vto_fecha = date('Y-m-d H:i:s', strtotime('+30 days'));
            $factura->tercer_vto_tasa = 5.00;
            $factura->tercer_vto_importe = $thirdAmount;
            $factura->tercer_vto_codigo = $this->generateBarcodeNumber($nextNumber, 3);
            // Los campos observaciones y estado_pago no existen en la migración original
            $factura->save();
            
            $this->info("✓ Factura creada: {$talonario->nro_punto_vta}-{$nextNumber}");
            $this->info("  ID: {$factura->id}");
            $this->info("  Primer vencimiento: " . date('Y-m-d', strtotime('+10 days')) . " - \${$baseAmount}");
            $this->info("  Segundo vencimiento: " . date('Y-m-d', strtotime('+20 days')) . " - \${$secondAmount}");
            $this->info("  Tercer vencimiento: " . date('Y-m-d', strtotime('+30 days')) . " - \${$thirdAmount}");
            
            // Crear detalle de factura (usar servicio existente o crear uno genérico)
            try {
                $servicio = \App\Servicio::first(); // Usar el primer servicio disponible
                if ($servicio) {
                    $facturaDetalle = new \App\FacturaDetalle();
                    $facturaDetalle->factura_id = $factura->id;
                    $facturaDetalle->servicio_id = $servicio->id;
                    $facturaDetalle->abono_mensual = $baseAmount;
                    $facturaDetalle->save();
                    
                    $this->info("✓ Detalle de factura agregado (Servicio: {$servicio->descripcion})");
                } else {
                    $this->warn("⚠ No se encontraron servicios para agregar al detalle");
                }
            } catch (Exception $e) {
                $this->warn("⚠ Error agregando detalle: " . $e->getMessage());
            }
            
                    // Generar PDF si se solicitó
        if ($this->option('pdf')) {
            $this->info('Generando PDF...');
            $paymentQRService = app('App\Services\PaymentQRService');
            $billController = new BillController($paymentQRService);
            $billController->setFacturasPeriodoPDF($period, $factura->id);
            $this->info('✓ PDF generado exitosamente');
        }
            
            // Generar códigos QR si se solicita
            if ($generateQr || $this->confirm('¿Generar códigos QR de MercadoPago?', true)) {
                $this->info('Generando códigos QR...');
                
                try {
                    $paymentQRService = app(\App\Services\PaymentQRService::class);
                    
                    $vencimientos = ['primer', 'segundo', 'tercer'];
                    foreach ($vencimientos as $vencimiento) {
                        $result = $paymentQRService->createPaymentQR($factura, $vencimiento);
                        
                        if ($result && is_object($result)) {
                            $this->info("  ✓ QR {$vencimiento}: {$result->preference_id}");
                        } else {
                            $this->warn("  ⚠ QR {$vencimiento}: No se pudo generar");
                        }
                    }
                    
                } catch (Exception $e) {
                    $this->error("Error generando QR: " . $e->getMessage());
                }
            }
            
            // Mostrar enlaces útiles
            $this->info("\n" . str_repeat('=', 50));
            $this->info("FACTURA GENERADA EXITOSAMENTE");
            $this->info(str_repeat('=', 50));
            $this->info("ID de Factura: {$factura->id}");
            $this->info("Número: {$talonario->nro_punto_vta}-{$nextNumber}");
            $this->info("Cliente: {$user->firstname} {$user->lastname}");
            $this->info("Período: {$period}");
            $this->info("Total: \${$baseAmount}");
            
            // Sugerir próximos pasos
            $this->info("\nPróximos pasos:");
            $this->info("• Ver PDF: Acceder al sistema web y buscar factura {$factura->id}");
            $this->info("• Probar QR: php74 artisan payment:test-qr {$factura->id}");
            $this->info("• Ver logs: tail -f storage/logs/laravel.log");
            
            return 0;
            
        } catch (Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
    }
    
    /**
     * Generar número de código de barras para vencimiento
     *
     * @param int $facturaNumber
     * @param int $vencimiento (1, 2, 3)
     * @return string
     */
    protected function generateBarcodeNumber($facturaNumber, $vencimiento)
    {
        // Generar código de barras simple basado en número de factura y vencimiento
        // Formato: YYYYMMDDNNNNNV (Año, mes, día, número factura, vencimiento)
        $date = date('Ymd');
        $paddedNumber = str_pad($facturaNumber, 5, '0', STR_PAD_LEFT);
        
        return $date . $paddedNumber . $vencimiento;
    }
}
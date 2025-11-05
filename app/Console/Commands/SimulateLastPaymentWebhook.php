<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PaymentController;
use App\Contracts\PaymentServiceInterface;
use App\Factura;
use App\PaymentPreference;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SimulateLastPaymentWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:simulate-webhook {--factura_id= : ID específico de factura} {--force : Forzar simulación incluso si ya está pagada} {--details : Mostrar detalles adicionales del proceso}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula webhook de pago aprobado para la última factura con payment preference';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $facturaId = $this->option('factura_id');
        $force = $this->option('force');
        $details = $this->option('details');

        $this->info('🔍 Buscando factura con payment preference...');

        // Si se especificó un ID de factura, usarlo
        if ($facturaId) {
            $factura = Factura::find($facturaId);

            if (!$factura) {
                $this->error("❌ Factura con ID {$facturaId} no encontrada");
                return 1;
            }

            $paymentPreference = $factura->paymentPreferences()
                ->where('status', '!=', 'cancelled')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$paymentPreference) {
                $this->error("❌ La factura {$facturaId} no tiene payment preferences");
                return 1;
            }

            if ($details) {
                $this->info("Factura especificada:");
                $this->line("  ID: {$factura->id}");
                $this->line("  Número: {$factura->nro_factura}");
                $this->line("  Importe Original: $" . number_format($factura->importe_original, 2));
                $this->line("  Importe Total (con bonificaciones): $" . number_format($factura->importe_total, 2));
                $this->line("  Importe Payment Preference: $" . number_format($paymentPreference->amount, 2));
                $this->line("  Vencimiento: {$paymentPreference->vencimiento_tipo}");
                $this->line("  Estado: " . ($factura->fecha_pago ? 'Pagada' : 'Pendiente'));
                if ($factura->cliente) {
                    $this->line("  Cliente: {$factura->cliente->firstname} {$factura->cliente->lastname}");
                }
            }
        } else {
            // Buscar la última factura que tenga payment preferences
            $paymentPreference = PaymentPreference::where('status', '!=', 'cancelled')
                ->orderBy('id', 'desc')
                ->first();

            if (!$paymentPreference) {
                $this->error('❌ No se encontraron payment preferences en el sistema');
                return 1;
            }

            $factura = $paymentPreference->factura;
            if (!$factura) {
                $this->error("❌ La payment preference {$paymentPreference->id} no tiene factura asociada");
                return 1;
            }

            if ($details) {
                $this->info("Última preferencia de pago encontrada (por ID):");
                $this->line("  ID Preferencia: {$paymentPreference->id}");
                $this->line("  ID Factura: {$factura->id}");
                $this->line("  Número Factura: {$factura->nro_factura}");
                $this->line("  Importe Original: $" . number_format($factura->importe_original, 2));
                $this->line("  Importe Total (con bonificaciones): $" . number_format($factura->importe_total, 2));
                $this->line("  Importe Payment Preference: $" . number_format($paymentPreference->amount, 2));
                $this->line("  Vencimiento: {$paymentPreference->vencimiento_tipo}");
                $this->line("  Estado: " . ($factura->fecha_pago ? 'Pagada' : 'Pendiente'));
                if ($factura->cliente) {
                    $this->line("  Cliente: {$factura->cliente->firstname} {$factura->cliente->lastname}");
                }
                $this->line("  External Reference: {$paymentPreference->external_reference}");
                $this->line("  Creada: {$paymentPreference->created_at}");
            }
        }

        // Verificar si la factura ya está pagada
        if ($factura->fecha_pago && !$this->option('force')) {
            $this->warn("⚠️  La factura {$factura->id} ya está pagada (fecha: {$factura->fecha_pago})");
            $this->warn('💡 Usa --force para simular el webhook de todas formas');
            return 1;
        }

        $this->info("✅ Encontrada factura ID: {$factura->id}");
        $this->info("   📄 Número: {$factura->nro_factura}");
        $this->info("   💰 Importe a simular: $" . number_format($paymentPreference->amount, 2) . " ({$paymentPreference->vencimiento_tipo})");
        $this->info("   🔗 External Reference: {$paymentPreference->external_reference}");
        $this->info("   📅 Creada: {$paymentPreference->created_at}");

        // Confirmar antes de proceder (solo en modo interactivo)
        if ($this->input->isInteractive() && !$this->confirm('¿Deseas simular el webhook de pago aprobado para esta factura?', true)) {
            $this->info('❌ Operación cancelada');
            return 0;
        }

        $this->info('🔄 Simulando webhook de pago aprobado...');

        try {
            // Simular directamente el procesamiento del pago sin usar el webhook completo
            $paymentService = app(PaymentServiceInterface::class);
            $controller = new PaymentController($paymentService);

            // Simular los datos del pago aprobado
            $paymentStatus = [
                'success' => true,
                'payment_id' => 'sim_' . time(),
                'status' => 'approved',
                'external_reference' => $paymentPreference->external_reference,
                'transaction_amount' => $paymentPreference->amount,
                'simulated' => true  // Marcar como simulado para diferenciar de webhooks reales
            ];

            // Marcar la preferencia como pagada
            $paymentPreference->markAsPaid($paymentStatus['payment_id']);

            // Actualizar la factura directamente
            $controller->updateFacturaPaymentStatus($factura, $paymentPreference, $paymentStatus, $force);

            $this->info('✅ Webhook simulado exitosamente!');

            // Verificar cambios en la base de datos
            $facturaActualizada = Factura::find($factura->id);
            $preferenceActualizada = PaymentPreference::find($paymentPreference->id);

            $this->info('📊 Estado actualizado:');
            $this->info("   💳 Payment Preference: {$preferenceActualizada->status} ({$preferenceActualizada->payment_status})");
            $this->info("   📄 Factura pagada: " . ($facturaActualizada->fecha_pago ? 'Sí' : 'No'));
            if ($facturaActualizada->fecha_pago) {
                $this->info("   📅 Fecha de pago: {$facturaActualizada->fecha_pago}");
                $this->info("   💰 Importe pagado: $" . number_format($facturaActualizada->importe_pago, 2));
            }

            // Verificar si se generó nota de débito
            $notasDebito = \App\NotaDebito::where('factura_id', $factura->id)->get();
            if ($notasDebito->count() > 0) {
                $this->info("   📝 Nota(s) de débito generada(s): {$notasDebito->count()}");
                foreach ($notasDebito as $nota) {
                    $this->info("      - Nota #{$nota->nro_nota_debito}: $" . number_format($nota->importe_total, 2));
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error durante la simulación: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
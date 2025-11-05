<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BillController;
use App\Factura;
use Illuminate\Http\Request;
use Exception;

class RegenerateInvoicePDF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:regenerate-pdf {factura_id : ID de la factura a regenerar} {--force : Forzar regeneración sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenera el PDF de una factura específica por ID. Útil para regenerar PDFs que fallaron durante el proceso de facturación.';

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
        $facturaId = $this->argument('factura_id');
        $force = $this->option('force');
        
        // Validar ID de factura
        if (!is_numeric($facturaId)) {
            $this->error('El ID de factura debe ser un número válido.');
            return 1;
        }

        // Verificar que la factura existe
        $factura = Factura::with(['talonario', 'cliente'])->find($facturaId);
        if (!$factura) {
            $this->error("No se encontró una factura con ID: {$facturaId}");
            return 1;
        }

        // Mostrar información de la factura
        $facturaInfo = sprintf(
            'ID: %d | %s %s-%s | Cliente: %s %s | Período: %s | Importe: $%s',
            $factura->id,
            $factura->talonario->letra,
            str_pad($factura->talonario->nro_punto_vta, 4, '0', STR_PAD_LEFT),
            str_pad($factura->nro_factura, 8, '0', STR_PAD_LEFT),
            $factura->cliente->firstname,
            $factura->cliente->lastname,
            $factura->periodo,
            number_format($factura->importe_total, 2)
        );

        $this->info('Factura encontrada:');
        $this->line($facturaInfo);

        // Confirmación si no se usa --force
        if (!$force && !$this->confirm('¿Desea regenerar el PDF de esta factura?')) {
            $this->info('Operación cancelada.');
            return 0;
        }

        $this->info('Regenerando PDF de factura...');

        try {
            // Crear instancia del controlador
            $billController = new BillController(
                app(\App\Services\PaymentQRService::class),
                app(\App\Services\AfipService::class)
            );

            // Crear un request falso para el método
            $request = Request::create('/', 'POST');
            $request->setUserResolver(function () {
                // Retornar un usuario admin por defecto para el contexto del comando
                return \App\User::where('email', 'admin@redin.com')->first();
            });

            // Llamar al método de regeneración
            $response = $billController->regenerateBillPDF($request, $facturaId);
            $responseData = $response->getData(true);

            if ($responseData['success']) {
                $this->info('✓ PDF regenerado exitosamente!');
                $this->line('');
                $this->line('Detalles:');
                $this->line('- Archivo: ' . $responseData['data']['filename']);
                $this->line('- Ruta: ' . $responseData['data']['file_path']);
                $this->line('- Tamaño: ' . number_format($responseData['data']['file_size']) . ' bytes');
                $this->line('- URL pública: ' . $responseData['data']['public_url']);
                $this->line('- Generado: ' . $responseData['data']['generated_at']);
                
                return 0;
            } else {
                $this->error('✗ Error al regenerar PDF: ' . $responseData['message']);
                if (isset($responseData['error'])) {
                    $this->line('Error técnico: ' . $responseData['error']);
                }
                return 1;
            }

        } catch (Exception $e) {
            $this->error('✗ Error inesperado: ' . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->line($e->getTraceAsString());
            }
            return 1;
        }
    }
}
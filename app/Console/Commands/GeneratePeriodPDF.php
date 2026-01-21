<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BillController;
use App\Factura;
use App\Services\PaymentQRService;
use App\Services\AfipService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Exception;

class GeneratePeriodPDF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'period:generate-pdf 
                            {periodo : Período en formato MM/YYYY (ej: 03/2024)}
                            {--force : Forzar regeneración si el PDF ya existe}
                            {--verbose : Mostrar información detallada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera los PDFs de un período específico. Verifica si existe antes de generar, puede regenerar con --force.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $periodo = $this->argument('periodo');
        $force = $this->option('force');
        $verbose = $this->option('verbose');

        // Validar formato del período
        if (!$this->validatePeriodoFormat($periodo)) {
            $this->error("Formato de período inválido. Use MM/YYYY (ej: 03/2024)");
            return 1;
        }

        // Verificar que existan facturas para el período
        $facturasCount = Factura::where('periodo', $periodo)
            ->whereNull('deleted_at')
            ->count();

        if ($facturasCount === 0) {
            $this->error("No se encontraron facturas para el período: {$periodo}");
            return 1;
        }

        $this->info("Período: {$periodo}");
        $this->info("Facturas encontradas: {$facturasCount}");

        // Verificar si el PDF del período ya existe
        $pdfPath = $this->getPeriodoPDFPath($periodo);
        $pdfExists = Storage::disk('public')->exists($pdfPath);

        if ($verbose) {
            $this->line("Ruta PDF: {$pdfPath}");
            $this->line("PDF existente: " . ($pdfExists ? 'Sí' : 'No'));
        }

        // Si el PDF existe y no se fuerza la regeneración
        if ($pdfExists && !$force) {
            $this->info("El PDF del período ya existe. Use --force para regenerar.");
            return 0;
        }

        // Si se fuerza regeneración y existe
        if ($pdfExists && $force) {
            $this->line("Eliminando PDF existente...");
            Storage::disk('public')->delete($pdfPath);
            $this->info("PDF eliminado.");
        }

        $this->line("Generando PDFs del período...");

        try {
            // Crear instancia del controlador
            $billController = new BillController(
                app(PaymentQRService::class),
                app(AfipService::class)
            );

            // Generar PDFs de facturas individuales y del período
            $result = $billController->setFacturasPeriodoPDF($periodo);

            if ($result) {
                $this->info("✓ PDFs generados exitosamente para el período {$periodo}");
                
                if ($verbose) {
                    $fullPath = storage_path('app/public/' . $pdfPath);
                    if (file_exists($fullPath)) {
                        $fileSize = File::size($fullPath) / 1024; // Tamaño en KB
                        $this->line("Tamaño del archivo: " . number_format($fileSize, 2) . " KB");
                    }
                }
                
                return 0;
            } else {
                $this->error("No se pudieron generar los PDFs del período {$periodo}");
                return 1;
            }

        } catch (Exception $e) {
            $this->error("Error durante la generación de PDFs: " . $e->getMessage());
            if ($verbose) {
                $this->error("Stack trace: " . $e->getTraceAsString());
            }
            return 1;
        }
    }

    /**
     * Valida que el período tenga el formato MM/YYYY
     *
     * @param string $periodo
     * @return bool
     */
    private function validatePeriodoFormat($periodo)
    {
        // Validar formato MM/YYYY
        if (!preg_match('/^\d{2}\/\d{4}$/', $periodo)) {
            return false;
        }

        list($mes, $ano) = explode('/', $periodo);
        
        // Validar mes (01-12)
        if ((int)$mes < 1 || (int)$mes > 12) {
            return false;
        }

        // Validar año (razonable, ej: 1900-2100)
        if ((int)$ano < 1900 || (int)$ano > 2100) {
            return false;
        }

        return true;
    }

    /**
     * Obtiene la ruta del PDF del período en el disco public
     *
     * @param string $periodo
     * @return string
     */
    private function getPeriodoPDFPath($periodo)
    {
        $filename = str_replace('/', '-', $periodo);
        return config('constants.folder_periodos') . 'periodo-' . $filename . '.pdf';
    }
}

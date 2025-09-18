<?php

namespace App\Services;

use App\Contracts\PaymentServiceInterface;
use App\Contracts\QRCodeServiceInterface;
use App\PaymentPreference;
use App\Factura;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentQRService
{
    protected $paymentService;
    protected $qrCodeService;

    public function __construct(
        PaymentServiceInterface $paymentService,
        QRCodeServiceInterface $qrCodeService
    ) {
        $this->paymentService = $paymentService;
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Crear preferencia de pago y generar código QR para una factura
     *
     * @param Factura $factura
     * @param string $vencimientoTipo 'primer', 'segundo', 'tercer'
     * @return PaymentPreference|null
     */
    public function createPaymentQR(Factura $factura, $vencimientoTipo)
    {
        try {
            // Obtener datos del vencimiento según el tipo
            $paymentData = $this->getPaymentDataForVencimiento($factura, $vencimientoTipo);
            
            if (!$paymentData) {
                throw new Exception("Tipo de vencimiento inválido: {$vencimientoTipo}");
            }

            // Crear preferencia en MercadoPago
            $preferenceResult = $this->paymentService->createPaymentPreference($paymentData);

            if (!$preferenceResult['success']) {
                throw new Exception('Error al crear preferencia de MercadoPago: ' . $preferenceResult['error']);
            }
            
            // Log para confirmar qué URL se está utilizando
            $isSandbox = isset($preferenceResult['is_sandbox']) ? $preferenceResult['is_sandbox'] : false;
            Log::info('Generando QR con URL de ' . ($isSandbox ? 'SANDBOX' : 'PRODUCCIÓN'), [
                'factura_id' => $factura->id,
                'vencimiento_tipo' => $vencimientoTipo,
                'init_point' => $preferenceResult['init_point'],
                'is_sandbox' => $isSandbox
            ]);
            
            // Generar código QR con la URL de pago (parámetros optimizados para mejor legibilidad)
            $qrCodeBase64 = $this->qrCodeService->generateQRCode($preferenceResult['init_point'], [
                'size' => 200,        // Aumentado de 150 a 200
                'margin' => 0         // Sin margen para maximizar el QR
            ]);

            // Guardar en base de datos
            $paymentPreference = PaymentPreference::create([
                'factura_id' => $factura->id,
                'vencimiento_tipo' => $vencimientoTipo,
                'preference_id' => $preferenceResult['preference_id'],
                'init_point' => $preferenceResult['init_point'],
                'qr_code_base64' => $qrCodeBase64,
                'amount' => $paymentData['amount'],
                'external_reference' => $paymentData['external_reference'],
                'status' => 'pending'
            ]);

            return $paymentPreference;

        } catch (Exception $e) {
            Log::error('Error creando QR de pago: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return null;
        }
    }

    /**
     * Obtener email por defecto de forma segura
     */
    protected function getDefaultEmail()
    {
        try {
            return config('constants.account_no_reply', 'administracion@redin.com.ar');
        } catch (Exception $e) {
            return 'administracion@redin.com.ar';
        }
    }

    /**
     * Obtener datos de pago según el tipo de vencimiento
     *
     * @param Factura $factura
     * @param string $vencimientoTipo
     * @return array|null
     */
    protected function getPaymentDataForVencimiento(Factura $factura, $vencimientoTipo)
    {
        // Cargar relaciones si no están cargadas
        if (!$factura->relationLoaded('cliente')) {
            $factura->load('cliente');
        }
        if (!$factura->relationLoaded('talonario')) {
            $factura->load('talonario');
        }
        
        $puntoVenta = $factura->talonario ? $factura->talonario->nro_punto_vta : '0001';
        $clienteName = $factura->cliente ? ($factura->cliente->firstname . ' ' . $factura->cliente->lastname) : 'Cliente';
        
        $baseTitle = "Factura {$puntoVenta}-{$factura->nro_factura}";
        $baseDescription = "Pago de factura periodo {$factura->periodo} - Cliente: {$clienteName}";
        
        switch ($vencimientoTipo) {
            case 'primer':
                return [
                    'title' => $baseTitle . ' - Primer Vencimiento',
                    'description' => $baseDescription . ' - Primer vencimiento',
                    'amount' => $this->parseAmount($factura->importe_total),
                    'external_reference' => $factura->id . '_primer_' . time(),
                    'payer' => [
                        'name' => $factura->cliente ? $factura->cliente->firstname : 'Cliente',
                        'surname' => $factura->cliente ? $factura->cliente->lastname : 'Cliente',
                        'email' => ($factura->cliente && $factura->cliente->email) ? $factura->cliente->email : $this->getDefaultEmail(),
                        'identification' => [
                            'type' => 'DNI',
                            'number' => ($factura->cliente && $factura->cliente->dni) ? $factura->cliente->dni : '00000000'
                        ]
                    ]
                ];

            case 'segundo':
                return [
                    'title' => $baseTitle . ' - Segundo Vencimiento',
                    'description' => $baseDescription . ' - Segundo vencimiento con recargo',
                    'amount' => $this->parseAmount($factura->segundo_vto_importe),
                    'external_reference' => $factura->id . '_segundo_' . time(),
                    'payer' => [
                        'name' => $factura->cliente ? $factura->cliente->firstname : 'Cliente',
                        'surname' => $factura->cliente ? $factura->cliente->lastname : 'Cliente',
                        'email' => ($factura->cliente && $factura->cliente->email) ? $factura->cliente->email : $this->getDefaultEmail(),
                        'identification' => [
                            'type' => 'DNI',
                            'number' => ($factura->cliente && $factura->cliente->dni) ? $factura->cliente->dni : '00000000'
                        ]
                    ]
                ];

            case 'tercer':
                if (empty($factura->tercer_vto_importe) || $factura->tercer_vto_importe <= 0) {
                    return null; // No hay tercer vencimiento
                }
                
                return [
                    'title' => $baseTitle . ' - Tercer Vencimiento',
                    'description' => $baseDescription . ' - Tercer vencimiento con recargo máximo',
                    'amount' => $this->parseAmount($factura->tercer_vto_importe),
                    'external_reference' => $factura->id . '_tercer_' . time(),
                    'payer' => [
                        'name' => $factura->cliente ? $factura->cliente->firstname : 'Cliente',
                        'surname' => $factura->cliente ? $factura->cliente->lastname : 'Cliente',
                        'email' => ($factura->cliente && $factura->cliente->email) ? $factura->cliente->email : $this->getDefaultEmail(),
                        'identification' => [
                            'type' => 'DNI',
                            'number' => ($factura->cliente && $factura->cliente->dni) ? $factura->cliente->dni : '00000000'
                        ]
                    ]
                ];

            default:
                return null;
        }
    }

    /**
     * Regenerar código QR para una preferencia existente
     *
     * @param PaymentPreference $paymentPreference
     * @return bool
     */
    public function regenerateQRCode(PaymentPreference $paymentPreference)
    {
        try {
            if (empty($paymentPreference->init_point)) {
                return false;
            }

            $qrCodeBase64 = $this->qrCodeService->generateQRCode($paymentPreference->init_point, [
                'size' => 200,        // Aumentado de 150 a 200
                'margin' => 0         // Sin margen para maximizar el QR
            ]);

            $paymentPreference->update([
                'qr_code_base64' => $qrCodeBase64
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error regenerando código QR: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancelar preferencia de pago
     *
     * @param PaymentPreference $paymentPreference
     * @return bool
     */
    public function cancelPaymentPreference(PaymentPreference $paymentPreference)
    {
        try {
            if (!empty($paymentPreference->preference_id)) {
                $this->paymentService->cancelPaymentPreference($paymentPreference->preference_id);
            }

            $paymentPreference->markAsCancelled();
            
            return true;

        } catch (Exception $e) {
            Log::error('Error cancelando preferencia: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las preferencias de pago para una factura
     *
     * @param Factura $factura
     * @return array
     */
    public function getFacturaPaymentPreferences(Factura $factura)
    {
        $preferences = [];
        
        $preferences['primer'] = PaymentPreference::where('factura_id', $factura->id)
            ->where('vencimiento_tipo', 'primer')
            ->where('status', '!=', 'cancelled')
            ->first();
            
        $preferences['segundo'] = PaymentPreference::where('factura_id', $factura->id)
            ->where('vencimiento_tipo', 'segundo')
            ->where('status', '!=', 'cancelled')
            ->first();
            
        $preferences['tercer'] = PaymentPreference::where('factura_id', $factura->id)
            ->where('vencimiento_tipo', 'tercer')
            ->where('status', '!=', 'cancelled')
            ->first();

        return $preferences;
    }

    /**
     * Convierte un valor que puede estar formateado con comas a un float válido
     * 
     * @param mixed $amount
     * @return float
     */
    private function parseAmount($amount)
    {
        if (is_numeric($amount)) {
            return (float) $amount;
        }
        
        // Si es string, remover comas y convertir a float
        if (is_string($amount)) {
            $cleaned = str_replace(',', '', $amount);
            return is_numeric($cleaned) ? (float) $cleaned : 0.0;
        }
        
        return 0.0;
    }
}
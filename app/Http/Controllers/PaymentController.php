<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentServiceInterface;
use App\PaymentPreference;
use App\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Página de éxito del pago
     */
    public function paymentSuccess(Request $request)
    {
        $collection_id = $request->get('collection_id');
        $collection_status = $request->get('collection_status');
        $payment_id = $request->get('payment_id');
        $status = $request->get('status');
        $external_reference = $request->get('external_reference');
        $payment_type = $request->get('payment_type');
        $merchant_order_id = $request->get('merchant_order_id');
        $preference_id = $request->get('preference_id');
        $site_id = $request->get('site_id');
        $processing_mode = $request->get('processing_mode');
        $merchant_account_id = $request->get('merchant_account_id');

        try {
            if ($payment_id) {
                $this->processPaymentApproval($payment_id);
            }

            return view('payment.success', [
                'payment_id' => $payment_id,
                'status' => $status,
                'collection_status' => $collection_status
            ]);

        } catch (Exception $e) {
            Log::error('Error en página de éxito: ' . $e->getMessage());
            return view('payment.success', [
                'error' => 'Hubo un problema al procesar el pago. Contacte con atención al cliente.'
            ]);
        }
    }

    /**
     * Página de fallo del pago
     */
    public function paymentFailure(Request $request)
    {
        return view('payment.failure', [
            'collection_id' => $request->get('collection_id'),
            'collection_status' => $request->get('collection_status'),
            'external_reference' => $request->get('external_reference')
        ]);
    }

    /**
     * Página de pago pendiente
     */
    public function paymentPending(Request $request)
    {
        return view('payment.pending', [
            'collection_id' => $request->get('collection_id'),
            'collection_status' => $request->get('collection_status'),
            'external_reference' => $request->get('external_reference')
        ]);
    }

    /**
     * Webhook de MercadoPago
     */
    public function mercadoPagoWebhook(Request $request)
    {
        try {
            $webhookData = $request->all();
            $headers = $request->headers->all();
            
            Log::info('Webhook MercadoPago recibido:', [
                'data' => $webhookData,
                'user_agent' => $request->header('User-Agent'),
                'ip' => $request->ip()
            ]);

            // Validar que el webhook venga de MercadoPago
            if (!$this->validateMercadoPagoWebhook($request)) {
                Log::warning('Webhook rechazado: no proviene de MercadoPago', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent')
                ]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Validar estructura básica del webhook
            if (!isset($webhookData['type']) || !isset($webhookData['data'])) {
                Log::warning('Webhook con estructura inválida:', $webhookData);
                return response()->json(['error' => 'Invalid webhook structure'], 400);
            }

            // Procesar el webhook
            $result = $this->paymentService->processWebhook($webhookData);

            if ($result['success'] && $result['type'] === 'payment') {
                $paymentInfo = $result['payment_info'];
                
                if ($paymentInfo['success']) {
                    $this->processPaymentApproval($paymentInfo);
                    Log::info('Pago procesado exitosamente desde webhook:', [
                        'payment_id' => $paymentInfo['payment_id'],
                        'status' => $paymentInfo['status']
                    ]);
                }
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (Exception $e) {
            Log::error('Error procesando webhook MercadoPago: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    /**
     * Validar que el webhook provenga de MercadoPago
     */
    protected function validateMercadoPagoWebhook(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        
        // MercadoPago usa un User-Agent específico para sus webhooks
        if (empty($userAgent) || strpos($userAgent, 'MercadoPago') === false) {
            return false;
        }

        // Validar que tenga estructura de webhook válida
        $data = $request->all();
        if (!isset($data['type']) || !isset($data['data']['id'])) {
            return false;
        }

        // Validar tipos de webhook permitidos
        $allowedTypes = ['payment', 'plan', 'subscription', 'invoice'];
        if (!in_array($data['type'], $allowedTypes)) {
            return false;
        }

        // Validar rangos de IP de MercadoPago (opcional pero recomendado)
        $clientIp = $request->ip();
        if (!$this->isValidMercadoPagoIP($clientIp)) {
            Log::warning('Webhook desde IP no autorizada', ['ip' => $clientIp]);
            // En desarrollo permitir localhost, en producción comentar esta línea
            if (!in_array($clientIp, ['127.0.0.1', '::1']) && config('app.env') !== 'local') {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validar si la IP pertenece a MercadoPago
     */
    protected function isValidMercadoPagoIP($ip)
    {
        // Rangos de IP conocidos de MercadoPago (actualizar según documentación)
        $mercadoPagoRanges = [
            '209.225.49.0/24',
            '216.33.197.0/24',
            '216.33.196.0/24'
        ];

        foreach ($mercadoPagoRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si una IP está dentro de un rango CIDR
     */
    protected function ipInRange($ip, $range)
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        
        return ($ip & $mask) === $subnet;
    }

    /**
     * Procesar aprobación de pago
     */
    protected function processPaymentApproval($paymentInfo)
    {
        try {
            // Si recibimos solo el ID, obtener la información completa
            if (is_string($paymentInfo)) {
                $paymentStatus = $this->paymentService->getPaymentStatus($paymentInfo);
                
                if (!$paymentStatus['success']) {
                    throw new Exception('No se pudo obtener información del pago: ' . $paymentStatus['error']);
                }
            } else {
                // Ya tenemos la información completa
                $paymentStatus = $paymentInfo;
            }

            // Buscar la preferencia de pago por external_reference
            $externalReference = $paymentStatus['external_reference'];
            
            if (empty($externalReference)) {
                throw new Exception('External reference no encontrada en el pago');
            }

            $paymentPreference = PaymentPreference::where('external_reference', $externalReference)->first();
            
            if (!$paymentPreference) {
                throw new Exception('Preferencia de pago no encontrada para external_reference: ' . $externalReference);
            }

            // Solo procesar si el pago está aprobado
            if ($paymentStatus['status'] === 'approved') {
                // Marcar la preferencia como pagada
                $paymentPreference->markAsPaid($paymentStatus['payment_id']);

                // Actualizar la factura si es necesario
                $factura = $paymentPreference->factura;
                if ($factura) {
                    $this->updateFacturaPaymentStatus($factura, $paymentPreference, $paymentStatus);
                }

                Log::info("Pago procesado exitosamente para factura {$factura->id}, vencimiento {$paymentPreference->vencimiento_tipo}");
            } else {
                // Marcar como rechazada si no está aprobada
                if ($paymentStatus['status'] === 'rejected') {
                    $paymentPreference->markAsRejected();
                    Log::info("Pago rechazado para external_reference: {$externalReference}");
                }
            }

        } catch (Exception $e) {
            Log::error('Error procesando aprobación de pago: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualizar estado de pago de la factura
     */
    protected function updateFacturaPaymentStatus(Factura $factura, PaymentPreference $paymentPreference, $paymentStatus)
    {
        try {
            // Verificar si la factura no tiene pago registrado aún
            if (empty($factura->fecha_pago)) {
                $factura->fecha_pago = date('Y-m-d');
                $factura->importe_pago = $paymentStatus['transaction_amount'];
                $factura->forma_pago = 4; // Asignar código para MercadoPago
                $factura->save();

                Log::info("Factura {$factura->id} marcada como pagada vía MercadoPago");
            }

        } catch (Exception $e) {
            Log::error('Error actualizando estado de factura: ' . $e->getMessage());
        }
    }

    /**
     * API para obtener estado de una preferencia de pago
     */
    public function getPaymentPreferenceStatus(Request $request, $preferenceId)
    {
        try {
            $paymentPreference = PaymentPreference::where('preference_id', $preferenceId)->first();
            
            if (!$paymentPreference) {
                return response()->json(['error' => 'Preferencia no encontrada'], 404);
            }

            return response()->json([
                'success' => true,
                'preference' => [
                    'id' => $paymentPreference->id,
                    'status' => $paymentPreference->status,
                    'payment_status' => $paymentPreference->payment_status,
                    'amount' => $paymentPreference->amount,
                    'vencimiento_tipo' => $paymentPreference->vencimiento_tipo,
                    'paid_at' => $paymentPreference->paid_at
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error obteniendo estado de preferencia: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }
}
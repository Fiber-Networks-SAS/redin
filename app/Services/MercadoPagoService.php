<?php

namespace App\Services;

use App\Contracts\PaymentServiceInterface;
use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;
use MercadoPago\Payer;
use MercadoPago\Payment;
use Exception;
use Illuminate\Support\Facades\Log;

class MercadoPagoService implements PaymentServiceInterface
{
    protected $accessToken;
    protected $publicKey;

    public function __construct()
    {
        $this->accessToken = config('services.mercadopago.access_token');
        $this->publicKey = config('services.mercadopago.public_key');
        
        SDK::setAccessToken($this->accessToken);
    }

    /**
     * Crear una preferencia de pago para MercadoPago
     *
     * @param array $paymentData
     * @return array
     */
    public function createPaymentPreference(array $paymentData)
    {
        try {
            $preference = new Preference();

            // Crear el item
            $item = new Item();
            $item->title = $paymentData['title'];
            $item->quantity = 1;
            $item->unit_price = (float) $paymentData['amount'];
            $item->description = $paymentData['description'];

            $preference->items = array($item);

            // Información del pagador
            if (isset($paymentData['payer'])) {
                $payer = new Payer();
                $payer->name = $paymentData['payer']['name'];
                $payer->surname = $paymentData['payer']['surname'];
                $payer->email = $paymentData['payer']['email'];
                
                if (isset($paymentData['payer']['phone'])) {
                    $payer->phone = array(
                        "area_code" => "",
                        "number" => $paymentData['payer']['phone']
                    );
                }
                
                if (isset($paymentData['payer']['identification'])) {
                    $payer->identification = array(
                        "type" => $paymentData['payer']['identification']['type'],
                        "number" => $paymentData['payer']['identification']['number']
                    );
                }

                $preference->payer = $payer;
            }

            // URLs de retorno y notificación
            $baseUrl = config('app.url');
            
            // Asegurar que la URL base es válida para MercadoPago
            if ($baseUrl === 'http://localhost' || empty($baseUrl) || strpos($baseUrl, 'localhost') !== false) {
                // Para development, usar una URL válida pero que redirija a localhost
                // En producción, cambiar por la URL real del sitio
                $baseUrl = env('MERCADOPAGO_BASE_URL', 'https://example.com');
                Log::warning('Usando URL de desarrollo para MercadoPago callbacks: ' . $baseUrl);
            }
            
            $preference->back_urls = array(
                "success" => $baseUrl . "/payment/success",
                "failure" => $baseUrl . "/payment/failure", 
                "pending" => $baseUrl . "/payment/pending"
            );

            // Solo configurar webhook si tenemos una URL accesible públicamente
            if (strpos($baseUrl, 'localhost') === false && strpos($baseUrl, '127.0.0.1') === false) {
                $preference->notification_url = $baseUrl . "/webhooks/mercadopago";
            }

            // Configuraciones adicionales
            // Solo habilitar auto_return si tenemos URLs públicas válidas
            if (strpos($baseUrl, 'localhost') === false && strpos($baseUrl, '127.0.0.1') === false) {
                $preference->auto_return = "approved";
            }
            
            // ID de referencia externa (ID de la factura + vencimiento)
            $preference->external_reference = $paymentData['external_reference'];

            // Guardar la preferencia
            $preference->save();

            if ($preference->id) {
                return [
                    'success' => true,
                    'preference_id' => $preference->id,
                    'init_point' => $preference->init_point,
                    'sandbox_init_point' => $preference->sandbox_init_point,
                    'public_key' => $this->publicKey
                ];
            } else {
                throw new Exception('Error al crear la preferencia de pago');
            }

        } catch (Exception $e) {
            // Obtener detalles adicionales del error de MercadoPago
            $errorDetails = $e->getMessage();
            
            // Si es un error de MercadoPago, intentar obtener más información
            if (method_exists($e, 'getCode') && $e->getCode()) {
                $errorDetails .= ' (Code: ' . $e->getCode() . ')';
            }
            
            // Log detallado del error
            Log::error('Error en MercadoPago Service: ' . $errorDetails);
            Log::error('Datos enviados: ' . json_encode($paymentData));
            
            return [
                'success' => false,
                'error' => $errorDetails
            ];
        }
    }

    /**
     * Obtener el estado de un pago
     *
     * @param string $paymentId
     * @return array
     */
    public function getPaymentStatus($paymentId)
    {
        try {
            $payment = Payment::find_by_id($paymentId);
            
            if ($payment) {
                return [
                    'success' => true,
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
                    'status_detail' => $payment->status_detail,
                    'external_reference' => $payment->external_reference,
                    'transaction_amount' => $payment->transaction_amount,
                    'date_approved' => $payment->date_approved
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Pago no encontrado'
                ];
            }

        } catch (Exception $e) {
            Log::error('Error al obtener estado de pago: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancelar una preferencia de pago
     *
     * @param string $preferenceId
     * @return bool
     */
    public function cancelPaymentPreference($preferenceId)
    {
        try {
            $preference = Preference::find_by_id($preferenceId);
            
            if ($preference) {
                // MercadoPago no permite cancelar preferencias directamente
                // pero se puede actualizar con fecha de expiración pasada
                $preference->expires = true;
                $preference->expiration_date_from = date('c');
                $preference->expiration_date_to = date('c');
                
                $preference->update();
                
                return true;
            }
            
            return false;

        } catch (Exception $e) {
            Log::error('Error al cancelar preferencia: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Procesar webhook de MercadoPago
     *
     * @param array $webhookData
     * @return array
     */
    public function processWebhook(array $webhookData)
    {
        try {
            $type = isset($webhookData['type']) ? $webhookData['type'] : null;
            $dataId = isset($webhookData['data']['id']) ? $webhookData['data']['id'] : null;

            if ($type === 'payment' && $dataId) {
                $paymentInfo = $this->getPaymentStatus($dataId);
                
                return [
                    'success' => true,
                    'type' => 'payment',
                    'payment_info' => $paymentInfo
                ];
            }

            return [
                'success' => false,
                'error' => 'Tipo de webhook no soportado'
            ];

        } catch (Exception $e) {
            Log::error('Error procesando webhook: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
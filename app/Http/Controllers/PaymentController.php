<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentServiceInterface;
use App\PaymentPreference;
use App\Factura;
use App\Interes;
use App\NotaDebito;
use App\Services\AfipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
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

        // Si tiene 'type', validar que sea un tipo permitido
        $data = $request->all();
        if (isset($data['type'])) {
            $allowedTypes = ['payment', 'plan', 'subscription', 'invoice'];
            if (!in_array($data['type'], $allowedTypes)) {
                return false;
            }
        }

        // Validar rangos de IP de MercadoPago (opcional pero recomendado)
        $clientIp = $request->ip();
        // if (!$this->isValidMercadoPagoIP($clientIp)) {
        //     Log::warning('Webhook desde IP no autorizada', ['ip' => $clientIp]);
        //     // En desarrollo permitir localhost, en producción comentar esta línea
        //     if (!in_array($clientIp, ['127.0.0.1', '::1']) && config('app.env') !== 'local') {
        //         return false;
        //     }
        // }

        return true;
    }    /**
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
            }            // Solo procesar si el pago está aprobado
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
    public function updateFacturaPaymentStatus(Factura $factura, PaymentPreference $paymentPreference, $paymentStatus, $force = false)
    {
        try {
            // DEBUG: Mostrar todos los parámetros recibidos
            Log::info("DEBUG updateFacturaPaymentStatus called", [
                'factura_id' => $factura->id,
                'factura_fecha_pago' => $factura->fecha_pago,
                'factura_importe_pago' => $factura->importe_pago,
                'payment_preference_amount' => $paymentPreference->amount,
                'payment_status' => $paymentStatus,
                'force' => $force
            ]);

            // Verificar si la factura no tiene pago registrado aún, o si se fuerza el procesamiento
            if (empty($factura->fecha_pago) || $force) {
                Log::info("Factura procesando pago (force: " . ($force ? 'SÍ' : 'NO') . ")");

                $fechaPago = date('Y-m-d');
                $importePagado = $paymentStatus['transaction_amount'];

                // Para webhooks simulados (como nuestro comando), usar directamente el importe pagado
                // Para webhooks reales, validar que coincida con la payment preference
                $esSimulacion = isset($paymentStatus['simulated']) && $paymentStatus['simulated'];

                Log::info("Es simulación: " . ($esSimulacion ? 'SÍ' : 'NO'));

                if ($esSimulacion) {
                    // Simulación: usar directamente el importe pagado
                    Log::info("Procesando pago SIMULADO de factura {$factura->id}", [
                        'importe_factura_original' => $factura->importe_total,
                        'importe_pagado' => $importePagado,
                        'fecha_pago' => $fechaPago
                    ]);
                } else {
                    // Webhook real: validar que el importe pagado coincida con la payment preference
                    $importeEsperado = $paymentPreference->amount;

                    if (abs($importePagado - $importeEsperado) > 0.01) {
                        Log::warning("Importe pagado no coincide con payment preference", [
                            'factura_id' => $factura->id,
                            'importe_pagado' => $importePagado,
                            'importe_esperado' => $importeEsperado,
                            'diferencia' => abs($importePagado - $importeEsperado)
                        ]);
                        // En webhooks reales, podríamos rechazar o manejar de otra forma
                        // Por ahora, continuamos con el importe pagado
                    }

                    Log::info("Procesando pago REAL de factura {$factura->id}", [
                        'importe_factura_original' => $factura->importe_total,
                        'importe_pagado' => $importePagado,
                        'importe_esperado' => $importeEsperado,
                        'fecha_pago' => $fechaPago
                    ]);
                }

                // Calcular diferencia entre importe pagado y importe original de la factura
                // Si se pagó más (por intereses), emitir nota de débito
                $diferencia = round($importePagado - $factura->importe_total, 2);
                if ($diferencia >= 1) {
                    Log::info("Diferencia detectada, emitiendo nota de débito", [
                        'factura_id' => $factura->id,
                        'diferencia' => $diferencia
                    ]);
                    $this->emitirNotaDebitoAutomatica($factura, $diferencia, $fechaPago);
                }

                $factura->fecha_pago = $fechaPago;
                $factura->importe_pago = $importePagado;
                $factura->forma_pago = 4; // Asignar código para MercadoPago

                Log::info("Guardando factura con importe_pago: {$importePagado}");
                $factura->save();

                Log::info("Factura {$factura->id} marcada como pagada vía MercadoPago");
            } else {
                Log::info("Factura ya tiene fecha_pago y no se fuerza procesamiento");
            }

        } catch (Exception $e) {
            Log::error('Error actualizando estado de factura: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Calcular el importe correspondiente según la fecha de pago y los vencimientos
     *
     * @param Factura $factura
     * @param string $fechaPago Fecha en formato Y-m-d
     * @return float Importe que corresponde pagar según la fecha
     */
    protected function calcularImporteCorrespondiente(Factura $factura, $fechaPago)
    {
        $fechaPagoCarbon = Carbon::parse($fechaPago);

        // Obtener fechas de vencimiento
        $primerVtoFecha = Carbon::parse($factura->primer_vto_fecha);
        $segundoVtoFecha = Carbon::parse($factura->segundo_vto_fecha);

        Log::info("Calculando importe correspondiente", [
            'factura_id' => $factura->id,
            'fecha_pago' => $fechaPagoCarbon->format('Y-m-d'),
            'primer_vto' => $primerVtoFecha->format('Y-m-d'),
            'segundo_vto' => $segundoVtoFecha->format('Y-m-d'),
            'importe_total' => $factura->importe_total,
            'segundo_vto_importe' => $factura->segundo_vto_importe
        ]);

        // Si paga en o antes del primer vencimiento → importe original
        if ($fechaPagoCarbon->lte($primerVtoFecha)) {
            Log::info("Pago en primer vencimiento - sin intereses");
            return $factura->importe_total;
        }

        // Si paga en o antes del segundo vencimiento → importe con recargo del segundo vencimiento
        if ($fechaPagoCarbon->lte($segundoVtoFecha)) {
            Log::info("Pago en segundo vencimiento - con recargo fijo", [
                'importe_con_recargo' => $factura->segundo_vto_importe
            ]);
            return $factura->segundo_vto_importe;
        }

        // Si paga después del segundo vencimiento → calcular interés diario
        $interes = Interes::find(1);
        if (!$interes || !$interes->tercer_vto_tasa) {
            Log::warning("No se encontró configuración de interés diario, usando importe segundo vencimiento");
            return $factura->segundo_vto_importe;
        }

        // Calcular días transcurridos desde el segundo vencimiento
        $diasExcedentes = $segundoVtoFecha->diffInDays($fechaPagoCarbon) + 1;

        // Calcular tasa acumulada (tasa diaria * días)
        $tasaAcumulada = $diasExcedentes * $interes->tercer_vto_tasa;

        // Calcular importe final con interés diario sobre el importe del segundo vencimiento
        $importeConInteres = round(($factura->segundo_vto_importe * $tasaAcumulada / 100) + $factura->segundo_vto_importe, 2);

        Log::info("Pago después del segundo vencimiento - con interés diario", [
            'dias_excedentes' => $diasExcedentes,
            'tasa_diaria' => $interes->tercer_vto_tasa,
            'tasa_acumulada' => $tasaAcumulada,
            'importe_base' => $factura->segundo_vto_importe,
            'importe_con_interes' => $importeConInteres
        ]);

        return $importeConInteres;
    }

    /**
     * Emitir nota de débito automática en AFIP cuando hay diferencia por intereses
     *
     * @param Factura $factura
     * @param float $diferencia Diferencia a facturar (ya incluye IVA)
     * @param string $fechaPago Fecha del pago
     */
    protected function emitirNotaDebitoAutomatica(Factura $factura, $diferencia, $fechaPago)
    {
        try {
            Log::info("Iniciando emisión de nota de débito automática", [
                'factura_id' => $factura->id,
                'diferencia' => $diferencia,
                'fecha_pago' => $fechaPago
            ]);

            // En entorno de testing, simular respuesta exitosa sin llamar a AFIP
            if (app()->environment('testing')) {
                Log::info('Modo testing: simulando emisión de nota de débito AFIP');

                // Simular respuesta AFIP exitosa
                $afipResponse = [
                    'CbteDesde' => rand(100000, 999999),
                    'CAE' => '12345678901234',
                    'CAEFchVto' => date('Ymd', strtotime('+30 days'))
                ];
            } else {
                // Determinar tipo de nota según talonario
                $cbteTipo = $factura->talonario->letra == 'A' ? 2 : 7;

                // Emitir en AFIP
                $afipService = app(AfipService::class);

                if ($factura->talonario->letra == 'A') {
                    $afipResponse = $afipService->notaDebitoA(
                        $factura->talonario->nro_punto_vta,
                        $factura->cliente->dni,
                        $diferencia,
                        $factura->nro_factura
                    );
                } else {
                    $afipResponse = $afipService->notaDebitoB(
                        $factura->talonario->nro_punto_vta,
                        $diferencia,
                        $factura->nro_factura
                    );
                }
            }

            Log::info('Respuesta AFIP nota de débito automática', $afipResponse);

            // Verificar si la respuesta indica éxito
            if (isset($afipResponse['CbteDesde']) && !empty($afipResponse['CbteDesde'])) {
                // Calcular importes (la diferencia ya incluye IVA)
                $importeTotal = round($diferencia, 2);
                $importeNeto = round($diferencia / 1.21, 2);
                $importeIVA = round($importeTotal - $importeNeto, 2);

                // Guardar registro en BD
                $nota = new NotaDebito();
                $nota->factura_id = $factura->id;
                $nota->talonario_id = $factura->talonario_id;
                $nota->nro_nota_debito = $afipResponse['CbteDesde'];
                $nota->importe_ampliacion = $importeNeto;
                $nota->importe_iva = $importeIVA;
                $nota->importe_total = $importeTotal;
                $nota->cae = isset($afipResponse['CAE']) ? $afipResponse['CAE'] : null;

                try {
                    $nota->cae_vto = isset($afipResponse['CAEFchVto'])
                        ? Carbon::createFromFormat('Ymd', $afipResponse['CAEFchVto'])
                        : null;
                } catch (Exception $e) {
                    Log::warning('Error parseando fecha CAE vencimiento: ' . $e->getMessage());
                    $nota->cae_vto = null;
                }

                $nota->fecha_emision = Carbon::parse($fechaPago);
                $nota->motivo = 'Ajuste automático por diferencia de pago (intereses de mora)';
                $nota->nro_cliente = $factura->cliente->nro_cliente;
                $nota->periodo = $factura->periodo;

                Log::info('Datos de nota de débito a guardar', $nota->toArray());

                if ($nota->save()) {
                    Log::info("Nota de débito automática creada exitosamente", [
                        'nota_id' => $nota->id,
                        'factura_id' => $factura->id,
                        'importe_total' => $importeTotal,
                        'cae' => $nota->cae
                    ]);
                } else {
                    Log::error('Error al guardar nota de débito: save() retornó false');
                }
            } else {
                Log::error('Respuesta AFIP inválida para nota de débito', [
                    'response' => $afipResponse
                ]);
            }

        } catch (Exception $e) {
            Log::error('Error emitiendo nota de débito automática: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            // No lanzar excepción para no interrumpir el flujo de pago
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
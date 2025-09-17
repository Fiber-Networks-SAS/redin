<?php

namespace App\Contracts;

interface PaymentServiceInterface
{
    /**
     * Crear una preferencia de pago
     *
     * @param array $paymentData
     * @return array
     */
    public function createPaymentPreference(array $paymentData);

    /**
     * Obtener el estado de un pago
     *
     * @param string $paymentId
     * @return array
     */
    public function getPaymentStatus($paymentId);

    /**
     * Cancelar una preferencia de pago
     *
     * @param string $preferenceId
     * @return bool
     */
    public function cancelPaymentPreference($preferenceId);

    /**
     * Procesar webhook de pago
     *
     * @param array $webhookData
     * @return array
     */
    public function processWebhook(array $webhookData);
}
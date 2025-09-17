<?php

namespace App\Contracts;

interface QRCodeServiceInterface
{
    /**
     * Generar código QR para un enlace de pago
     *
     * @param string $paymentUrl
     * @param array $options
     * @return string Base64 encoded QR code
     */
    public function generateQRCode($paymentUrl, array $options = []);

    /**
     * Generar código QR y guardarlo como archivo
     *
     * @param string $paymentUrl
     * @param string $filePath
     * @param array $options
     * @return bool
     */
    public function saveQRCodeToFile($paymentUrl, $filePath, array $options = []);

    /**
     * Validar URL de pago
     *
     * @param string $paymentUrl
     * @return bool
     */
    public function validatePaymentUrl($paymentUrl);
}
<?php

namespace App\Services;

use App\Contracts\QRCodeServiceInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Exception;
use Illuminate\Support\Facades\Log;

class QRCodeService implements QRCodeServiceInterface
{
    protected $defaultOptions = [
        'size' => 250,        // Aumentado de 200 a 250 para mejor legibilidad
        'margin' => 0,        // Eliminado el margen para maximizar el QR
        'foreground_color' => [0, 0, 0],
    ];

    /**
     * Generar código QR para un enlace de pago
     *
     * @param string $paymentUrl
     * @param array $options
     * @return string Base64 encoded QR code
     */
    public function generateQRCode($paymentUrl, array $options = [])
    {
        try {
            if (!$this->validatePaymentUrl($paymentUrl)) {
                throw new Exception('URL de pago inválida');
            }

            $options = array_merge($this->defaultOptions, $options);

            $qrCode = new QrCode($paymentUrl);
            $qrCode->setSize($options['size']);
            $qrCode->setMargin($options['margin']);
            $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::LOW()); // Cambiado de HIGH a LOW para menos complejidad
            
            // Para Endroid QR Code v3.x, los colores se configuran de forma diferente
            if (isset($options['foreground_color']) && is_array($options['foreground_color'])) {
                $qrCode->setForegroundColor([
                    'r' => $options['foreground_color'][0],
                    'g' => $options['foreground_color'][1], 
                    'b' => $options['foreground_color'][2]
                ]);
            }
            
            // Configurar fondo transparente explícitamente
            $qrCode->setBackgroundColor([
                'r' => 255,
                'g' => 255,
                'b' => 255,
                'a' => 127  // Alpha para transparencia (0-127, donde 127 es completamente transparente)
            ]);

            $writer = new PngWriter();
            $result = $writer->writeString($qrCode);

            return base64_encode($result);

        } catch (Exception $e) {
            Log::error('Error generando código QR: ' . $e->getMessage());
            throw new Exception('Error al generar código QR: ' . $e->getMessage());
        }
    }

    /**
     * Generar código QR y guardarlo como archivo
     *
     * @param string $paymentUrl
     * @param string $filePath
     * @param array $options
     * @return bool
     */
    public function saveQRCodeToFile($paymentUrl, $filePath, array $options = [])
    {
        try {
            if (!$this->validatePaymentUrl($paymentUrl)) {
                throw new Exception('URL de pago inválida');
            }

            $options = array_merge($this->defaultOptions, $options);

            $qrCode = new QrCode($paymentUrl);
            $qrCode->setSize($options['size']);
            $qrCode->setMargin($options['margin']);
            $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
            
            if (isset($options['foreground_color'])) {
                $qrCode->setForegroundColor($options['foreground_color']);
            }
            
            if (isset($options['background_color'])) {
                $qrCode->setBackgroundColor($options['background_color']);
            }

            // Crear directorio si no existe
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $writer = new PngWriter();
            $writer->writeFile($qrCode, $filePath);
            
            return true;

        } catch (Exception $e) {
            Log::error('Error guardando código QR: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar URL de pago
     *
     * @param string $paymentUrl
     * @return bool
     */
    public function validatePaymentUrl($paymentUrl)
    {
        if (empty($paymentUrl)) {
            return false;
        }

        // Validar que sea una URL válida
        if (!filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Validar que sea de MercadoPago (opcional, para mayor seguridad)
        $mercadoPagoDomains = [
            'mercadopago.com',
            'mercadopago.com.ar',
            'mercadopago.com.br',
            'mercadopago.com.mx',
            'mercadopago.com.co',
            'mercadopago.cl',
            'mercadopago.com.pe',
            'mercadopago.com.uy'
        ];

        $parsedUrl = parse_url($paymentUrl);
        $domain = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        
        foreach ($mercadoPagoDomains as $allowedDomain) {
            if (strpos($domain, $allowedDomain) !== false) {
                return true;
            }
        }

        // También permitir URLs de sandbox
        if (strpos($domain, 'sandbox') !== false && strpos($domain, 'mercadopago') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Generar código QR con texto adicional (para mostrar debajo del QR)
     *
     * @param string $paymentUrl
     * @param string $text
     * @param array $options
     * @return string
     */
    public function generateQRCodeWithText($paymentUrl, $text, array $options = [])
    {
        try {
            if (!$this->validatePaymentUrl($paymentUrl)) {
                throw new Exception('URL de pago inválida');
            }

            $options = array_merge($this->defaultOptions, $options);

            $qrCode = new QrCode($paymentUrl);
            $qrCode->setSize($options['size']);
            $qrCode->setMargin($options['margin']);
            $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
            
            if (isset($options['foreground_color'])) {
                $qrCode->setForegroundColor($options['foreground_color']);
            }
            
            if (isset($options['background_color'])) {
                $qrCode->setBackgroundColor($options['background_color']);
            }

            $writer = new PngWriter();
            $result = $writer->writeString($qrCode);

            return base64_encode($result);

        } catch (Exception $e) {
            Log::error('Error generando código QR con texto: ' . $e->getMessage());
            throw new Exception('Error al generar código QR: ' . $e->getMessage());
        }
    }
}
<?php

namespace App\Services;

use Afip;

class AfipService
{
    protected $afip;

    public function __construct()
    {
        $certPath = storage_path('app/afip.crt');
        $keyPath = storage_path('app/afip.pem');
        
        // Verificar que los archivos existan
        if (!file_exists($certPath)) {
            throw new \Exception('Certificado AFIP no encontrado en: ' . $certPath);
        }
        
        if (!file_exists($keyPath)) {
            throw new \Exception('Clave privada AFIP no encontrada en: ' . $keyPath);
        }
        
        $cert = file_get_contents($certPath);
        $key = file_get_contents($keyPath);
        
        // Verificar que el contenido no esté vacío
        if (empty($cert)) {
            throw new \Exception('El certificado AFIP está vacío');
        }
        
        if (empty($key)) {
            throw new \Exception('La clave privada AFIP está vacía');
        }
        
        // Verificar formato PEM básico
        if (!str_contains($cert, '-----BEGIN CERTIFICATE-----') || !str_contains($cert, '-----END CERTIFICATE-----')) {
            throw new \Exception('El certificado AFIP no tiene formato PEM válido');
        }
        
        if (!str_contains($key, '-----BEGIN PRIVATE KEY-----') || !str_contains($key, '-----END PRIVATE KEY-----')) {
            throw new \Exception('La clave privada AFIP no tiene formato PEM válido');
        }
        
        // Limpiar cualquier espacio en blanco extra
        $cert = trim($cert);
        $key = trim($key);
        
        try {
            $cuit = env('AFIP_CUIT', 30716353334); // Usar CUIT del .env o el actualizado
            $this->afip = new Afip([
                'CUIT' => $cuit,
                'production' => env('AFIP_PRODUCTION', false),
                'cert' => $cert,
                'key'  => $key,
                'access_token' => env('AFIP_ACCESS_TOKEN', 'default_access_token')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al inicializar AFIP: ' . $e->getMessage());
            throw new \Exception('Error al inicializar servicio AFIP: ' . $e->getMessage());
        }
    }

    /**
     * Crear comprobante AFIP (genérico)
     */
    private function createVoucher(array $data)
    {
        \Log::info('AFIP - Enviando datos para crear voucher', $data);
        try {
            $result = $this->afip->ElectronicBilling->CreateVoucher($data);
            \Log::info('AFIP - Respuesta completa del voucher', $result);
            return $result;
        } catch (\Exception $e) {
            \Log::error('AFIP - Error al crear voucher: ' . $e->getMessage());
            \Log::error('AFIP - Datos enviados que causaron el error', $data);
            throw $e;
        }
    }

    /**
     * Obtener el último número de comprobante emitido
     */
    public function getLastVoucher($ptoVta, $cbteTipo)
    {
        return $this->afip->ElectronicBilling->GetLastVoucher($ptoVta, $cbteTipo);
    }

    /**
     * Factura A
     */
    public function facturaA($ptoVta, $cuitCliente, $importe)
    {
        if (empty($cuitCliente)) {
            throw new \Exception('El CUIT del cliente es requerido para emitir Factura A');
        }

        \Log::info('AFIP - Iniciando creación de Factura A', [
            'ptoVta' => $ptoVta,
            'cuitCliente' => $cuitCliente,
            'importe' => $importe
        ]);

        $lastVoucher = $this->getLastVoucher($ptoVta, 1);
        \Log::info('AFIP - Último voucher obtenido para Factura A', ['lastVoucher' => $lastVoucher]);

        $importeTotal = round($importe, 2);
        $importeNeto = round($importe / 1.21, 2);
        $importeIVA = round($importeTotal - $importeNeto, 2);
        $baseImponibleIVA = round($importeTotal, 2);

        \Log::info('AFIP - Cálculos de importes para Factura A', [
            'importeTotal' => $importeTotal,
            'importeNeto' => $importeNeto,
            'importeIVA' => $importeIVA
        ]);

        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $ptoVta,
            'CbteTipo'  => 1, // Factura A
            'Concepto'  => 2, // Servicios
            'DocTipo'   => 80, // CUIT
            'DocNro'    => $cuitCliente,
            'CbteDesde' => $lastVoucher + 1,
            'CbteHasta' => $lastVoucher + 1,
            'CbteFch'   => intval(date('Ymd')),
            'FchServDesde'  => intval(date('Ymd')),
            'FchServHasta'  => intval(date('Ymd')),
            'FchVtoPago'    => intval(date('Ymd')),
            'ImpTotal'  => $importeTotal,
            'ImpTotConc'=> 0,
            'ImpNeto'   => $importeNeto,
            'ImpOpEx'   => 0,
            'ImpIVA'    => $importeIVA,
            'ImpTrib'   => 0,
            'MonId'     => 'PES',
            'MonCotiz'  => 1,
            'CondicionIVAReceptorId' => 1, // Responsable Inscripto
            'Iva'       => [
                [
                    'Id'      => 5, // 21%
                    'BaseImp' => $importeNeto,
                    'Importe' => $importeIVA,
                ]
            ],
        ];

        \Log::info('AFIP - Datos preparados para Factura A', $data);

        $result = $this->createVoucher($data);

        \Log::info('AFIP - Resultado de creación de Factura A', $result);

        return $result;
    }

    /**
     * Factura B
     */
    public function facturaB($ptoVta, $importe)
    {
        $lastVoucher = $this->getLastVoucher($ptoVta, 6);
        $importeTotal = round($importe, 2);
        $importeNeto = round($importe / 1.21, 2);
        $importeIVA = round($importeTotal - $importeNeto, 2);
        $baseImponibleIVA = round($importeTotal, 2);
        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $ptoVta,
            'CbteTipo'  => 6, // Factura B
            'Concepto'  => 2,
            'DocTipo'   => 99, // Consumidor Final
            'DocNro'    => 0,
            'CbteDesde' => $lastVoucher + 1,
            'CbteHasta' => $lastVoucher + 1,
            'CbteFch'   => intval(date('Ymd')),
            'FchServDesde'  => intval(date('Ymd')),
            'FchServHasta'  => intval(date('Ymd')),
            'FchVtoPago'    => intval(date('Ymd')),
            'ImpTotal'  => $importeTotal,
            'ImpTotConc'=> 0,
            'ImpNeto'   => $importeNeto,
            'ImpOpEx'   => 0,
            'ImpIVA'    => $importeIVA,
            'ImpTrib'   => 0,
            'MonId'     => 'PES',
            'MonCotiz'  => 1,
            'Iva'       => [
                [
                    'Id'      => 5,
                    'BaseImp' => $importeNeto,
                    'Importe' => $importeIVA,
                ]
            ],
            'CondicionIVAReceptorId' => 4,
        ];
        try {
            return $this->createVoucher($data);
        } catch (\Exception $e) {
            \Log::error('Error al crear factura B: ' . $e->getMessage());
            dd($e);
        }
    }

    /**
     * Nota de Crédito A
     */
    public function notaCreditoA($ptoVta, $cuitCliente, $importe, $nroFacturaAsociada)
    {
        $lastVoucher = $this->getLastVoucher($ptoVta, 3);
        $importeTotal = round($importe, 2);
        $importeNeto = round($importe / 1.21, 2);
        $importeIVA = round($importeTotal - $importeNeto, 2);
        $baseImponibleIVA = round($importeTotal, 2);
        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $ptoVta,
            'CbteTipo'  => 3, // Nota de Crédito A
            'Concepto'  => 2, // Servicios
            'DocTipo'   => 80,
            'DocNro'    => $cuitCliente,
            'CbteDesde' => $lastVoucher + 1,
            'CbteHasta' => $lastVoucher + 1,
            'CbteFch'   => intval(date('Ymd')),
            'FchServDesde'  => intval(date('Ymd')),
            'FchServHasta'  => intval(date('Ymd')),
            'FchVtoPago'    => intval(date('Ymd')),
            'ImpTotal'  => $importeTotal,
            'ImpTotConc'=> 0,
            'ImpNeto'   => $importeNeto,
            'ImpOpEx'   => 0,
            'ImpIVA'    => $importeIVA,
            'ImpTrib'   => 0,
            'MonId'     => 'PES',
            'MonCotiz'  => 1,
            'CondicionIVAReceptorId' => 1, // Responsable Inscripto
            'Iva'       => [
                [
                    'Id'      => 5,
                    'BaseImp' => $importeNeto,
                    'Importe' => $importeIVA,
                ]
            ],
            'CbtesAsoc' => [
                [
                    'Tipo'  => 1, // Factura A
                    'PtoVta'=> $ptoVta,
                    'Nro'   => $nroFacturaAsociada,
                ]
            ],
        ];

        return $this->createVoucher($data);
    }

    /**
     * Nota de Crédito B
     */
    public function notaCreditoB($ptoVta, $importe, $nroFacturaAsociada)
    {
        $lastVoucher = $this->getLastVoucher($ptoVta, 8);
        $importeTotal = round($importe, 2);
        $importeNeto = round($importe / 1.21, 2);
        $importeIVA = round($importeTotal - $importeNeto, 2);
        $baseImponibleIVA = round($importeTotal, 2);

        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $ptoVta,
            'CbteTipo'  => 8, // Nota de Crédito B
            'Concepto'  => 2,
            'DocTipo'   => 99, // Consumidor Final
            'DocNro'    => 0,
            'CbteDesde' => $lastVoucher + 1,
            'CbteHasta' => $lastVoucher + 1,
            'CbteFch'   => intval(date('Ymd')),
            'FchServDesde'  => intval(date('Ymd')),
            'FchServHasta'  => intval(date('Ymd')),
            'FchVtoPago'    => intval(date('Ymd')),
            'ImpTotal'  => $importeTotal,
            'ImpTotConc'=> 0,
            'ImpNeto'   => $importeNeto,
            'ImpOpEx'   => 0,
            'ImpIVA'    => $importeIVA,
            'ImpTrib'   => 0,
            'MonId'     => 'PES',
            'MonCotiz'  => 1,
            'CondicionIVAReceptorId' => 4, // Consumidor Final
            'Iva'       => [
                [
                    'Id'      => 5,
                    'BaseImp' => $importeNeto,
                    'Importe' => $importeIVA,
                ]
            ],
            'CbtesAsoc' => [
                [
                    'Tipo'  => 6, // Factura B
                    'PtoVta'=> $ptoVta,
                    'Nro'   => $nroFacturaAsociada,
                ]
            ],
        ];

        return $this->createVoucher($data);
    }

    /**
     * Métodos de prueba y consulta para testing
     */
    
    /**
     * Obtener estado del servidor AFIP
     */
    public function getServerStatus()
    {
        return $this->afip->ElectronicBilling->GetServerStatus();
    }
    
    /**
     * Obtener tipos de comprobantes disponibles
     */
    public function getVoucherTypes()
    {
        return $this->afip->ElectronicBilling->GetVoucherTypes();
    }
    
    /**
     * Obtener tipos de documentos disponibles
     */
    public function getDocumentTypes()
    {
        return $this->afip->ElectronicBilling->GetDocumentTypes();
    }
    
    /**
     * Obtener tipos de alícuotas de IVA disponibles
     */
    public function getAliquotTypes()
    {
        return $this->afip->ElectronicBilling->GetAliquotTypes();
    }
    
    /**
     * Obtener tipos de monedas disponibles
     */
    public function getCurrencyTypes()
    {
        return $this->afip->ElectronicBilling->GetCurrenciesTypes();
    }
    
    /**
     * Obtener tipos de conceptos disponibles
     */
    public function getConceptTypes()
    {
        return $this->afip->ElectronicBilling->GetConceptTypes();
    }
    
    /**
     * Obtener tipos de tributos disponibles
     */
    public function getTaxTypes()
    {
        return $this->afip->ElectronicBilling->GetTaxTypes();
    }
    
    /**
     * Obtener información detallada de un comprobante
     */
    public function getVoucherInfo($number, $salesPoint, $type)
    {
        return $this->afip->ElectronicBilling->GetVoucherInfo($number, $salesPoint, $type);
    }
    
    /**
     * Obtener puntos de venta habilitados
     */
    public function getSalesPoints()
    {
        return $this->afip->ElectronicBilling->GetSalesPoints();
    }
    
    /**
     * Obtener instancia AFIP para acceso directo (solo para testing)
     */
    public function getAfipInstance()
    {
        return $this->afip;
    }
}

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
        
        // Verificar formato PEM básico (compatible con PHP < 8.0)
        if (strpos($cert, '-----BEGIN CERTIFICATE-----') === false || strpos($cert, '-----END CERTIFICATE-----') === false) {
            throw new \Exception('El certificado AFIP no tiene formato PEM válido');
        }
        
        if (strpos($key, '-----BEGIN PRIVATE KEY-----') === false || strpos($key, '-----END PRIVATE KEY-----') === false) {
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
        // Si AFIP está deshabilitado, simular respuesta exitosa
        if ($this->afip === null) {
            \Log::info('AFIP deshabilitado - Simulando creación de voucher', $data);
            return [
                'CAE' => 'DISABLED_' . date('YmdHis'),
                'CAEFchVto' => date('Ymd', strtotime('+10 days')),
                'CbteDesde' => 1,
                'CbteHasta' => 1,
                'Resultado' => 'A',
                'Observaciones' => 'AFIP deshabilitado por configuración'
            ];
        }

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
        // Si AFIP está deshabilitado, retornar número simulado
        if ($this->afip === null) {
            \Log::info('AFIP deshabilitado - Simulando último voucher', ['ptoVta' => $ptoVta, 'cbteTipo' => $cbteTipo]);
            return 0; // Empezar desde 1
        }

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

        \Log::info('AFIP - Iniciando creaci�n de Factura A', [
            'ptoVta' => $ptoVta,
            'cuitCliente' => $cuitCliente,
            'importe' => $importe
        ]);

        $lastVoucher = $this->getLastVoucher($ptoVta, 1);
        \Log::info('AFIP - �ltimo voucher obtenido para Factura A', ['lastVoucher' => $lastVoucher]);

        $importeTotal = round($importe, 2);
        $importeNeto = round($importe / 1.21, 2);
        $importeIVA = round($importeTotal - $importeNeto, 2);
        $baseImponibleIVA = round($importeTotal, 2);

        \Log::info('AFIP - C�lculos de importes para Factura A', [
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

        \Log::info('AFIP - Resultado de creaci�n de Factura A', $result);

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
     * Nota de Cr�dito A
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
            'CbteTipo'  => 3, // Nota de Cr�dito A
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
     * Nota de Cr�dito B
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
            'CbteTipo'  => 8, // Nota de Cr�dito B
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
     * Nota de Débito A
     * Se utiliza para ampliar el monto de una factura A (por ejemplo, por intereses de mora)
     *
     * @param int $ptoVta Punto de venta
     * @param string $cuitCliente CUIT del cliente (11 dígitos)
     * @param float $importe Importe total de la nota de débito (incluye IVA)
     * @param int $nroFacturaAsociada Número de la factura original
     * @return array Respuesta de AFIP con CAE y datos del comprobante
     */
    public function notaDebitoA($ptoVta, $cuitCliente, $importe, $nroFacturaAsociada)
    {
        \Log::info('AFIP - Iniciando creación de Nota de Débito A', [
            'ptoVta' => $ptoVta,
            'cuitCliente' => $cuitCliente,
            'importe' => $importe,
            'nroFacturaAsociada' => $nroFacturaAsociada
        ]);

        $lastVoucher = $this->getLastVoucher($ptoVta, 2); // Código 2 = Nota de Débito A
        $importeTotal = round($importe, 2);
        $importeNeto = round($importe / 1.21, 2);
        $importeIVA = round($importeTotal - $importeNeto, 2);

        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $ptoVta,
            'CbteTipo'  => 2, // Nota de Débito A
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
            'CbtesAsoc' => [
                [
                    'Tipo'  => 1, // Factura A
                    'PtoVta'=> $ptoVta,
                    'Nro'   => $nroFacturaAsociada,
                ]
            ],
        ];

        \Log::info('AFIP - Datos preparados para Nota de Débito A', $data);

        $result = $this->createVoucher($data);

        \Log::info('AFIP - Resultado de creación de Nota de Débito A', $result);

        return $result;
    }

    /**
     * Nota de Débito B
     * Se utiliza para ampliar el monto de una factura B (por ejemplo, por intereses de mora)
     *
     * @param int $ptoVta Punto de venta
     * @param float $importe Importe total de la nota de débito (incluye IVA)
     * @param int $nroFacturaAsociada Número de la factura original
     * @return array Respuesta de AFIP con CAE y datos del comprobante
     */
    public function notaDebitoB($ptoVta, $importe, $nroFacturaAsociada)
    {
        \Log::info('AFIP - Iniciando creación de Nota de Débito B', [
            'ptoVta' => $ptoVta,
            'importe' => $importe,
            'nroFacturaAsociada' => $nroFacturaAsociada
        ]);

        $lastVoucher = $this->getLastVoucher($ptoVta, 7); // Código 7 = Nota de Débito B
        $importeTotal = round($importe, 2);
        $importeNeto = round($importe / 1.21, 2);
        $importeIVA = round($importeTotal - $importeNeto, 2);

        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $ptoVta,
            'CbteTipo'  => 7, // Nota de Débito B
            'Concepto'  => 2, // Servicios
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
                    'Id'      => 5, // 21%
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

        \Log::info('AFIP - Datos preparados para Nota de Débito B', $data);

        $result = $this->createVoucher($data);

        \Log::info('AFIP - Resultado de creación de Nota de Débito B', $result);

        return $result;
    }

    /**
     * M�todos de prueba y consulta para testing
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
     * Obtener tipos de al�cuotas de IVA disponibles
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
     * Obtener informaci�n detallada de un comprobante
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

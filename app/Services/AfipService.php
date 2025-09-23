<?php

namespace App\Services;

use Afip;

class AfipService
{
    protected $afip;

    public function __construct()
    {
        $this->afip = new Afip([
            'CUIT' => env('AFIP_CUIT'),
            'production' => env('AFIP_PRODUCTION', false),
            'cert' => env('AFIP_CERT'),
            'key'  => env('AFIP_KEY'),
        ]);
    }

    /**
     * Crear comprobante AFIP (genérico)
     */
    private function createVoucher(array $data)
    {
        return $this->afip->ElectronicBilling->CreateVoucher($data);
    }

    /**
     * Obtener el último número de comprobante emitido
     */
    private function getLastVoucher($ptoVta, $cbteTipo)
    {
        return $this->afip->ElectronicBilling->GetLastVoucher($ptoVta, $cbteTipo);
    }

    /**
     * Factura A
     */
    public function facturaA($ptoVta, $cuitCliente, $importe)
    {
        $lastVoucher = $this->getLastVoucher($ptoVta, 1);

        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $ptoVta,
            'CbteTipo'  => 1, // Factura A
            'Concepto'  => 1, // Productos
            'DocTipo'   => 80, // CUIT
            'DocNro'    => $cuitCliente,
            'CbteDesde' => $lastVoucher + 1,
            'CbteHasta' => $lastVoucher + 1,
            'CbteFch'   => intval(date('Ymd')),
            'ImpTotal'  => $importe * 1.21,
            'ImpTotConc'=> 0,
            'ImpNeto'   => $importe,
            'ImpOpEx'   => 0,
            'ImpIVA'    => $importe * 0.21,
            'ImpTrib'   => 0,
            'MonId'     => 'PES',
            'MonCotiz'  => 1,
            'Iva'       => [
                [
                    'Id'      => 5, // 21%
                    'BaseImp' => $importe,
                    'Importe' => $importe * 0.21,
                ]
            ],
        ];

        return $this->createVoucher($data);
    }

    /**
     * Factura B
     */
    public function facturaB($ptoVta, $importe)
    {
        $lastVoucher = $this->getLastVoucher($ptoVta, 6);

        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $ptoVta,
            'CbteTipo'  => 6, // Factura B
            'Concepto'  => 1,
            'DocTipo'   => 99, // Consumidor Final
            'DocNro'    => 0,
            'CbteDesde' => $lastVoucher + 1,
            'CbteHasta' => $lastVoucher + 1,
            'CbteFch'   => intval(date('Ymd')),
            'ImpTotal'  => $importe * 1.21,
            'ImpTotConc'=> 0,
            'ImpNeto'   => $importe,
            'ImpOpEx'   => 0,
            'ImpIVA'    => $importe * 0.21,
            'ImpTrib'   => 0,
            'MonId'     => 'PES',
            'MonCotiz'  => 1,
            'Iva'       => [
                [
                    'Id'      => 5,
                    'BaseImp' => $importe,
                    'Importe' => $importe * 0.21,
                ]
            ],
        ];

        return $this->createVoucher($data);
    }

    /**
     * Nota de Crédito A
     */
    public function notaCreditoA($ptoVta, $cuitCliente, $importe, $nroFacturaAsociada)
    {
        $lastVoucher = $this->getLastVoucher($ptoVta, 3);

        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $ptoVta,
            'CbteTipo'  => 3, // Nota de Crédito A
            'Concepto'  => 1,
            'DocTipo'   => 80,
            'DocNro'    => $cuitCliente,
            'CbteDesde' => $lastVoucher + 1,
            'CbteHasta' => $lastVoucher + 1,
            'CbteFch'   => intval(date('Ymd')),
            'ImpTotal'  => $importe * 1.21,
            'ImpTotConc'=> 0,
            'ImpNeto'   => $importe,
            'ImpOpEx'   => 0,
            'ImpIVA'    => $importe * 0.21,
            'ImpTrib'   => 0,
            'MonId'     => 'PES',
            'MonCotiz'  => 1,
            'Iva'       => [
                [
                    'Id'      => 5,
                    'BaseImp' => $importe,
                    'Importe' => $importe * 0.21,
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

        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $ptoVta,
            'CbteTipo'  => 8, // Nota de Crédito B
            'Concepto'  => 1,
            'DocTipo'   => 99, // Consumidor Final
            'DocNro'    => 0,
            'CbteDesde' => $lastVoucher + 1,
            'CbteHasta' => $lastVoucher + 1,
            'CbteFch'   => intval(date('Ymd')),
            'ImpTotal'  => $importe * 1.21,
            'ImpTotConc'=> 0,
            'ImpNeto'   => $importe,
            'ImpOpEx'   => 0,
            'ImpIVA'    => $importe * 0.21,
            'ImpTrib'   => 0,
            'MonId'     => 'PES',
            'MonCotiz'  => 1,
            'Iva'       => [
                [
                    'Id'      => 5,
                    'BaseImp' => $importe,
                    'Importe' => $importe * 0.21,
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
}

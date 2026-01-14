<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class SaldoFavor extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'saldos_favor';

    protected $fillable = [
        'user_id',
        'factura_anulada_id',
        'nota_credito_id',
        'periodo',
        'importe_pagado',
        'importe_utilizado',
        'importe_disponible',
        'estado',
        'factura_nueva_id',
        'observaciones'
    ];

    protected $casts = [
        'importe_pagado' => 'float',
        'importe_utilizado' => 'float',
        'importe_disponible' => 'float',
    ];

    public $timestamps = true;

    /**
     * Relación con el usuario/cliente
     */
    public function usuario()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Relación con la factura anulada original
     */
    public function facturaAnulada()
    {
        return $this->belongsTo('App\Factura', 'factura_anulada_id');
    }

    /**
     * Relación con la nota de crédito generada
     */
    public function notaCredito()
    {
        return $this->belongsTo('App\NotaCredito', 'nota_credito_id');
    }

    /**
     * Relación con la factura nueva donde se aplicó el saldo
     */
    public function facturaNueva()
    {
        return $this->belongsTo('App\Factura', 'factura_nueva_id');
    }

    /**
     * Verificar si el saldo está completamente utilizado
     */
    public function isUtilizado()
    {
        return $this->importe_disponible <= 0;
    }

    /**
     * Aplicar parte del saldo a una nueva factura
     */
    public function aplicarSaldo($importe, $facturaId)
    {
        $importeAAplicar = min($importe, $this->importe_disponible);
        
        $this->importe_utilizado += $importeAAplicar;
        $this->importe_disponible -= $importeAAplicar;
        
        if ($this->importe_disponible <= 0) {
            $this->estado = 'utilizado';
        } else {
            $this->estado = 'parcial';
        }
        
        $this->factura_nueva_id = $facturaId;
        $this->save();
        
        return $importeAAplicar;
    }
}

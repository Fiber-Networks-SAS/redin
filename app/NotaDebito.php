<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class NotaDebito extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'notas_debito';

    protected $fillable = [
        'factura_id',
        'talonario_id',
        'nro_nota_debito',
        'importe_ampliacion',
        'importe_iva',
        'importe_total',
        'cae',
        'cae_vto',
        'fecha_emision',
        'motivo',
        'nro_cliente',
        'periodo'
    ];

    protected $dates = [
        'cae_vto',
        'fecha_emision',
        'created_at',
        'updated_at'
    ];

    /**
     * Relación con la factura original
     */
    public function factura()
    {
        return $this->belongsTo('App\Factura', 'factura_id');
    }

    /**
     * Relación con el talonario
     */
    public function talonario()
    {
        return $this->belongsTo('App\Talonario', 'talonario_id');
    }
}


<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentPreference extends Model
{
    protected $fillable = [
        'factura_id',
        'vencimiento_tipo', // 'primer', 'segundo', 'tercer'
        'preference_id',
        'init_point',
        'qr_code_base64',
        'amount',
        'external_reference',
        'status', // 'pending', 'approved', 'rejected', 'cancelled'
        'payment_id',
        'payment_status',
        'paid_at'
    ];

    protected $dates = ['created_at', 'updated_at', 'paid_at'];

    /**
     * Relación con la factura
     */
    public function factura()
    {
        return $this->belongsTo('App\Factura', 'factura_id');
    }

    /**
     * Scope para obtener preferencias pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para obtener preferencias aprobadas
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope para obtener por tipo de vencimiento
     */
    public function scopeByVencimiento($query, $tipo)
    {
        return $query->where('vencimiento_tipo', $tipo);
    }

    /**
     * Marcar como pagada
     */
    public function markAsPaid($paymentId)
    {
        $this->update([
            'payment_id' => $paymentId,
            'payment_status' => 'approved',
            'status' => 'approved',
            'paid_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marcar como rechazada
     */
    public function markAsRejected()
    {
        $this->update([
            'payment_status' => 'rejected',
            'status' => 'rejected'
        ]);
    }

    /**
     * Marcar como cancelada
     */
    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled'
        ]);
    }

    /**
     * Verificar si está pagada
     */
    public function isPaid()
    {
        return $this->status === 'approved' && !is_null($this->paid_at);
    }

    /**
     * Verificar si está pendiente
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Obtener el texto descriptivo del vencimiento
     */
    public function getVencimientoTextoAttribute()
    {
        $textos = [
            'primer' => 'Primer Vencimiento',
            'segundo' => 'Segundo Vencimiento',
            'tercer' => 'Tercer Vencimiento'
        ];

        return isset($textos[$this->vencimiento_tipo]) ? $textos[$this->vencimiento_tipo] : '';
    }
}
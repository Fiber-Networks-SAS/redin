<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

// laravel-auditing
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PagoInformado extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'pagos_informados';

    protected $fillable = [
        'factura_id',
        'user_id',
        'importe_informado',
        'fecha_pago_informado',
        'tipo_transferencia',
        'banco_origen',
        'numero_operacion',
        'cbu_origen',
        'titular_cuenta',
        'estado',
        'observaciones',
        'validado_por',
        'fecha_validacion',
        'comprobante_path'
    ];

    protected $dates = [
        'fecha_pago_informado',
        'fecha_validacion',
        'created_at',
        'updated_at'
    ];

    /**
     * Relación con la factura
     */
    public function factura()
    {
        return $this->belongsTo('App\Factura', 'factura_id');
    }

    /**
     * Relación con el usuario que informó el pago
     */
    public function usuario()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Relación con el usuario que validó el pago
     */
    public function validadoPor()
    {
        return $this->belongsTo('App\User', 'validado_por');
    }

    /**
     * Scopes para filtrar por estado
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', 'aprobado');
    }

    public function scopeRechazados($query)
    {
        return $query->where('estado', 'rechazado');
    }

    /**
     * Marcar como aprobado
     */
    public function aprobar($validadorId, $observaciones = null)
    {
        $this->update([
            'estado' => 'aprobado',
            'validado_por' => $validadorId,
            'fecha_validacion' => Carbon::now(),
            'observaciones' => $observaciones
        ]);
    }

    /**
     * Marcar como rechazado
     */
    public function rechazar($validadorId, $observaciones)
    {
        $this->update([
            'estado' => 'rechazado',
            'validado_por' => $validadorId,
            'fecha_validacion' => Carbon::now(),
            'observaciones' => $observaciones
        ]);
    }

    /**
     * Verificar si está pendiente
     */
    public function isPendiente()
    {
        return $this->estado === 'pendiente';
    }

    /**
     * Verificar si está aprobado
     */
    public function isAprobado()
    {
        return $this->estado === 'aprobado';
    }

    /**
     * Verificar si está rechazado
     */
    public function isRechazado()
    {
        return $this->estado === 'rechazado';
    }

    /**
     * Obtener el texto descriptivo del estado
     */
    public function getEstadoTextoAttribute()
    {
        $textos = [
            'pendiente' => 'Pendiente de Validación',
            'aprobado' => 'Aprobado',
            'rechazado' => 'Rechazado'
        ];

        return isset($textos[$this->estado]) ? $textos[$this->estado] : $this->estado;
    }

    /**
     * Obtener el texto descriptivo del tipo de transferencia
     */
    public function getTipoTransferenciaTextoAttribute()
    {
        $textos = [
            'CBU' => 'Transferencia CBU',
            'TRANSFERENCIA' => 'Transferencia Bancaria',
            'DEPOSITO' => 'Depósito Bancario'
        ];

        return isset($textos[$this->tipo_transferencia]) ? $textos[$this->tipo_transferencia] : $this->tipo_transferencia;
    }

    /**
     * Formatear fecha de pago informado para mostrar en vistas
     */
    public function getFechaPagoInformadoFormattedAttribute()
    {
        return $this->fecha_pago_informado instanceof \Carbon\Carbon 
            ? $this->fecha_pago_informado->format('d/m/Y') 
            : $this->fecha_pago_informado;
    }

    /**
     * Formatear fecha de validación para mostrar en vistas
     */
    public function getFechaValidacionFormattedAttribute()
    {
        return $this->fecha_validacion instanceof \Carbon\Carbon 
            ? $this->fecha_validacion->format('d/m/Y H:i') 
            : $this->fecha_validacion;
    }
}
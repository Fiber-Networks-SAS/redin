<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BonificacionServicio extends Model
{
    protected $table = 'bonificaciones_servicios';

    protected $fillable = [
        'service_id',
        'porcentaje_bonificacion',
        'periodos_bonificacion',
        'fecha_inicio',
        'activo',
        'descripcion'
    ];

    protected $dates = [
        'fecha_inicio',
        'created_at',
        'updated_at'
    ];

    /**
     * Relación con el modelo Servicio
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'service_id');
    }

    /**
     * Verifica si la bonificación está vigente para una fecha dada
     */
    public function esVigente($fecha = null)
    {
        if (!$this->activo) {
            return false;
        }

        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::now();
        $fechaInicio = Carbon::parse($this->fecha_inicio);
        
        // Calcular la fecha de fin basada en los períodos (asumiendo períodos mensuales)
        $fechaFin = $fechaInicio->copy()->addMonths($this->periodos_bonificacion);
        
        return $fecha->between($fechaInicio, $fechaFin);
    }

    /**
     * Obtiene el monto de bonificación para un precio dado
     */
    public function calcularBonificacion($precio)
    {
        if (!$this->esVigente()) {
            return 0;
        }

        return ($precio * $this->porcentaje_bonificacion) / 100;
    }

    /**
     * Obtiene el precio final después de aplicar la bonificación
     */
    public function aplicarBonificacion($precio)
    {
        $bonificacion = $this->calcularBonificacion($precio);
        return $precio - $bonificacion;
    }

    /**
     * Scope para obtener solo las bonificaciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener bonificaciones vigentes
     */
    public function scopeVigentes($query, $fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::now();
        
        return $query->where('activo', true)
                    ->where('fecha_inicio', '<=', $fecha)
                    ->where(function($q) use ($fecha) {
                        $q->whereRaw('DATE_ADD(fecha_inicio, INTERVAL periodos_bonificacion MONTH) >= ?', [$fecha]);
                    });
    }
}
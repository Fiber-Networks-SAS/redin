<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Encryption\DecryptException;

// laravel-auditing
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Factura extends Model implements AuditableContract
{

    use Auditable;
    use SoftDeletes;
    
	// define la tabla a utilizar
	protected $table = 'facturas';
	// define que todos los campos permiten entrada
	protected $fillable = [
        'cae',
        'cae_vto',
        'motivo_anulacion',
        'anulado_por',
        'fecha_anulacion',
    ];
    
    // campos de fecha para SoftDeletes
    protected $dates = ['deleted_at', 'cae_vto', 'fecha_anulacion'];
    //agregar casteos
    protected $casts = [
        'importe_total' => 'float',
        'importe_subtotal' => 'float',
        'importe_subtotal_iva' => 'float',
        'importe_iva' => 'float',
        'importe_bonificacion' => 'float',
        'importe_bonificacion_iva' => 'float',
        'cae_vto' => 'date',
    ];
	
	// crea el los campos created_at y updated_at
	public $timestamps = false;

    // for use MassAssignment - BillController: updateOrCreate() - https://laravel.com/docs/5.3/eloquent
    // protected $guarded = array();

    public function talonario()
    {
        return $this->belongsTo('App\Talonario', 'talonario_id');
    }

	public function detalle()
    {
        return $this->hasMany('App\FacturaDetalle');
    }

    public function cliente()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function paymentPreferences()
    {
        return $this->hasMany('App\PaymentPreference', 'factura_id');
    }

    public function getPaymentPreferenceByVencimiento($vencimientoTipo)
    {
        return $this->paymentPreferences()
            ->where('vencimiento_tipo', $vencimientoTipo)
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function notaCredito()
    {
        return $this->hasMany('App\NotaCredito', 'factura_id');
    }

    public function notaDebito()
    {
        return $this->hasMany('App\NotaDebito', 'factura_id');
    }

    public function bonificacionesPuntuales()
    {
        return $this->hasMany('App\BonificacionPuntual', 'factura_id');
    }

    public function saldosFavorGenerados()
    {
        return $this->hasMany('App\SaldoFavor', 'factura_anulada_id');
    }

    public function saldosFavorAplicados()
    {
        return $this->hasMany('App\SaldoFavor', 'factura_nueva_id');
    }

    public function pagosInformados()
    {
        return $this->hasMany('App\PagoInformado', 'factura_id');
    }

    /**
     * Formatear fecha de primer vencimiento para mostrar en vistas
     */
    public function getPrimerVtoFechaFormattedAttribute()
    {
        return $this->primer_vto_fecha instanceof \Carbon\Carbon 
            ? $this->primer_vto_fecha->format('d/m/Y') 
            : $this->primer_vto_fecha;
    }

    /**
     * Formatear fecha de segundo vencimiento para mostrar en vistas
     */
    public function getSegundoVtoFechaFormattedAttribute()
    {
        return $this->segundo_vto_fecha instanceof \Carbon\Carbon 
            ? $this->segundo_vto_fecha->format('d/m/Y') 
            : $this->segundo_vto_fecha;
    }

    /**
     * Formatear fecha de tercer vencimiento para mostrar en vistas
     */
    public function getTercerVtoFechaFormattedAttribute()
    {
        return $this->tercer_vto_fecha instanceof \Carbon\Carbon 
            ? $this->tercer_vto_fecha->format('d/m/Y') 
            : $this->tercer_vto_fecha;
    }

    /**
     * Calcular el importe original de la factura (antes de bonificaciones)
     */
    public function getImporteOriginalAttribute()
    {
        return $this->importe_subtotal + $this->importe_subtotal_iva;
    }

    /**
     * Obtener el total de bonificaciones puntuales aplicadas
     */
    public function getTotalBonificacionesPuntuales()
    {
        return $this->bonificacionesPuntuales()->sum('importe');
    }

    /**
     * Verificar si la factura tiene saldos a favor aplicados
     */
    public function tieneSaldosFavorAplicados()
    {
        return $this->saldosFavorAplicados()->exists();
    }

    /**
     * Obtener descripción de saldos aplicados
     */
    public function getDescripcionSaldosAplicados()
    {
        $saldos = $this->saldosFavorAplicados()->get();
        if ($saldos->isEmpty()) {
            return null;
        }

        $descripciones = [];
        foreach ($saldos as $saldo) {
            $descripciones[] = "Saldo de período {$saldo->periodo}: $" . number_format($saldo->importe_utilizado, 2);
        }
        
        return implode(', ', $descripciones);
    }
}
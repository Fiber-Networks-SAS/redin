<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;

// laravel-auditing
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Factura extends Model implements AuditableContract
{

    use Auditable;
    
	// define la tabla a utilizar
	protected $table = 'facturas';
	// define que todos los campos permiten entrada
	protected $fillable = [];
	
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
            ->first();
    }
}
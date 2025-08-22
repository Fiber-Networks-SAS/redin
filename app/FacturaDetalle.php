<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;

// laravel-auditing
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class FacturaDetalle extends Model implements AuditableContract
{

	use Auditable;
	
	// define la tabla a utilizar
	protected $table = 'facturas_detalle';

	// define que todos los campos permiten entrada
	protected $fillable = [];
	
	// crea el los campos created_at y updated_at
	public $timestamps = false;


    public function factura()
    {
        return $this->belongsTo('App\Factura', 'factura_id');
    }

	public function servicio()
    {
        return $this->hasOne('App\Servicio', 'id', 'servicio_id');
    }

}
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
	protected $fillable = [
		'factura_id',
		'servicio_id',
		'abono_mensual',
		'abono_proporcional',
		'dias_proporcional',
		'costo_instalacion',
		'instalacion_cuota',
		'instalacion_plan_pago',
		'iva_bonificacion',
		'importe_bonificacion',
		'bonificacion_detalle',
		'importe_iva',
		'pp_flag'
	];

	//agregar casteos
	// protected $casts = [
	// 	'importe_iva' => 'float',
	// 	'importe_bonificacion' => 'float',
	// 	'iva_bonificacion' => 'float',
	// 	'abono_mensual' => 'float',
	// 	'abono_proporcional' => 'float',
	// 	'costo_instalacion' => 'float',
	// ];

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

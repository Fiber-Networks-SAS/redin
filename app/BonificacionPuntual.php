<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;

// laravel-auditing
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class BonificacionPuntual extends Model implements AuditableContract
{

	use Auditable;

	// define la tabla a utilizar
	protected $table = 'bonificaciones_puntuales';

	// define que todos los campos permiten entrada
	protected $fillable = [
		'factura_id',
		'importe',
		'descripcion',
		'afip_response',
		'nota_credito_id',
	];

	//agregar casteos
	protected $casts = [
		'importe' => 'float',
		'afip_response' => 'array',
	];

	// crea el los campos created_at y updated_at
	public $timestamps = true;

	public function factura()
	{
		return $this->belongsTo('App\Factura', 'factura_id');
	}

	public function notaCredito()
	{
		return $this->belongsTo('App\NotaCredito', 'nota_credito_id');
	}
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;

// laravel-auditing
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ServicioUsuario extends Model implements AuditableContract
{

    use Auditable;

	// define la tabla a utilizar
	protected $table = 'servicios_usuarios';

	// define que todos los campos permiten entrada
	protected $fillable = [];
	
	// crea el los campos created_at y updated_at
	public $timestamps = false;


    public function servicio()
    {
        return $this->belongsTo('App\Servicio', 'servicio_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }   

}
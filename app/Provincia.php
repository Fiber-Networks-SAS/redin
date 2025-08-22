<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;

// laravel-auditing
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Provincia extends Model implements AuditableContract
{

    use Auditable;

	// define la tabla a utilizar
	protected $table = 'provincias';

	// define que todos los campos permiten entrada
	protected $fillable = [];
	
	// crea el los campos created_at y updated_at
	public $timestamps = false;


    public function pais()
    {
        return $this->belongsTo('App\Pais');
    } 

    public function localidades()
    {
        return $this->hasMany('App\Localidad');
    }    

}
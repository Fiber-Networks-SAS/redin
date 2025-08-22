<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;

class Localidad extends Model
{
	// define la tabla a utilizar
	protected $table = 'localidades';

	// define que todos los campos permiten entrada
	protected $fillable = [];
	
	// crea el los campos created_at y updated_at
	public $timestamps = false;


    public function provincia()
    {
        return $this->belongsTo('App\Provincia');
    }   

}
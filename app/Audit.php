<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model 
{

	// define la tabla a utilizar
	protected $table = 'audits';

	// define que todos los campos permiten entrada
	protected $fillable = [];
	
	// crea el los campos created_at y updated_at
	public $timestamps = true;

	public function user()
    {
        return $this->belongsTo('App\User');
    }

}
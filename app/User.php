<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

// laravel-auditing
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class User extends Authenticatable implements AuditableContract
{
    use Notifiable;
    use EntrustUserTrait; // add this trait to your user model
    use Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function detalleProvincia()
    {
        return $this->hasOne('App\Provincia', 'id', 'provincia');
    }

    public function detalleLocalidad()
    {
        return $this->hasOne('App\Localidad', 'id', 'localidad');
    }

    public function detalleTalonario()
    {
        return $this->hasOne('App\Talonario', 'id', 'talonario_id');
    }

    public function servicios()
    {
        return $this->hasMany('App\ServicioUsuario');
    }

    public function facturas()
    {
        return $this->hasMany('App\Factura');
    } 

    public function reclamos()
    {
        return $this->hasMany('App\Reclamo', 'user_from', 'id');
    }

    // for use MassAssignment - UserCategoriesController: updateOrCreate() - https://laravel.com/docs/5.3/eloquent
    // protected $guarded = array();
    
    // public function detalleUsuario()
    // {
    //     return $this->hasOne('App\Usuario', 'id', 'usuario_id');
    // }

    // public function polizas()
    // {
    //     return $this->hasMany('App\Poliza', 'usuario', 'usuario_id');
    // } 
 
    // public function documents()
    // {
    //     return $this->hasMany('App\Document', 'user_id', 'id');
    // }

    // public function hijos()
    // {
    //     return $this->hasMany('App\Hijo', 'user_id', 'id');
    // }


}
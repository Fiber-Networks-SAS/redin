<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HomeSetting extends Model
{
    protected $table = 'home_settings';
    protected $fillable = [
        'key', 'value', 'type',
    ];
    public $timestamps = true;
}

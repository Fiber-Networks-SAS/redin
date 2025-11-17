<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HomeContent extends Model
{
    protected $table = 'home_contents';

    protected $fillable = [
        'section',
        'title',
        'subtitle',
        'content',
        'image_path',
        'link_text',
        'link_url',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Scope para obtener solo secciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Obtener contenido por sección
     */
    public static function getBySection($section)
    {
        return self::where('section', $section)->active()->first();
    }

    /**
     * Obtener todos los contenidos activos ordenados
     */
    public static function getAllActive()
    {
        return self::active()->ordered()->get();
    }
}
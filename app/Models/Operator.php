<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mode',
        'logo_path',
        'is_active',
    ];

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute()
    {
        if (empty($this->logo_path)) {
            return null;
        }

        $basename = basename($this->logo_path);
        $defaults = [
            '2GO-Logo.png',
            'starlite-Logo.jfif',
            'Starlite_Logo.png',
            'Pal-Logo.jfif',
            'CebuPecific-Logo.png',
            'AirAsia-Logo.png'
        ];

        if (in_array($basename, $defaults)) {
            return asset('images/' . $basename);
        }

        return \Illuminate\Support\Facades\Storage::url($this->logo_path);
    }

    public function ferryRoutes()
    {
        return $this->hasMany(FerryRoute::class, 'operator_id');
    }

    protected static function booted(): void
    {
        $bust = fn() => static::bust();
        static::saved($bust);
        static::deleted($bust);
    }

    public static function bust(): void
    {
        try {
            \Illuminate\Support\Facades\Cache::flush();
        } catch (\Throwable) {
            // Ignore cache driver errors
        }
    }
}

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

    public function ferryRoutes()
    {
        return $this->hasMany(FerryRoute::class, 'operator_id');
    }
}

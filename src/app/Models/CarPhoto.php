<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarPhoto extends Model
{
    protected $fillable = ['car_id', 'photo_path', 'is_primary'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}

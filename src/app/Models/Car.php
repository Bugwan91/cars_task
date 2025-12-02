<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class Car extends Model
{
    protected $fillable = [
        'brand',
        'model',
        'year',
        'price',
        'description',
    ];

    public function photos()
    {
        return $this->hasMany(CarPhoto::class);
    }

    public function options()
    {
        return $this->belongsToMany(CarOption::class);
    }

    protected static function booted()
    {
        static::deleting(function ($car) {
            foreach ($car->photos as $photo) {
                if (Storage::exists($photo->photo_path)) {
                    Storage::delete($photo->photo_path);
                }
            }
        });
    }
}

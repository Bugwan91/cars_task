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
        return $this->hasMany(CarPhoto::class)
            ->orderByDesc('is_primary')
            ->orderBy('id');
    }

    public function options()
    {
        return $this->belongsToMany(CarOption::class);
    }

    protected static function booted()
    {
        static::deleting(function ($car) {
            foreach ($car->photos as $photo) {
                if ($photo->photo_path && Storage::disk('public')->exists($photo->photo_path)) {
                    Storage::disk('public')->delete($photo->photo_path);
                }
                if ($photo->thumbnail_path && Storage::disk('public')->exists($photo->thumbnail_path)) {
                    Storage::disk('public')->delete($photo->thumbnail_path);
                }
            }
        });
    }
}

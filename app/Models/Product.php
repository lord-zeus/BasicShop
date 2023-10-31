<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image', 'price', 'sku', 'slug'];

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }
    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }
    public function getImageAttribute($image): string
    {
        return Storage::url($image);
    }
}

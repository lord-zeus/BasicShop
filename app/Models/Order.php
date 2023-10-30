<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['comment', 'amount', 'phone'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }


}

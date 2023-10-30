<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image', 'price', 'sku', 'slug'];



//    public function getImageAttribute($image){
//        return Storage::url($image);
//    }
}

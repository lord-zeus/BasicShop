<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\APIResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductController extends Controller
{
    use APIResponse;
    public function index(){
        return $this->successResponse(Product::all());
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required',
            'image' => 'required',
            'price' => 'required|int',
            'sku' => 'required',
        ]);
        $slug = $this->generateSlug($request->name);
        $request = $request->merge(['slug', $slug]);
        $product = Product::create($request->all());
        return $this->successResponse($product, ResponseAlias::HTTP_CREATED);


    }

    public function generateSlug($name)
    {
        $slug=Str::slug($name);
        if (Product::where('slug',Str::slug($name))->exists()) {
            $max = Product::where('name','LIKE',$name)->latest()->value('slug');
            if(is_numeric($max[-1])) {
                return preg_replace_callback('/(\d+)$/', function($mathces) {
                    return $mathces[1] + 1;
                }, $max);
            }
            return "{$slug}-2";

        }
        return $slug;
    }

}

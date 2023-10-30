<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\APIResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
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
            'file' => 'required',
            'price' => 'required|numeric',
            'sku' => 'required',
        ]);
        $path = $request->file('file')->store('images', 'public');
        $image_path = "/storage/$path";
        $slug = $this->generateSlug($request->name);
        $request->merge(['slug' => $slug, 'image' => $image_path]);
        $product = Product::create($request->except(['file']));
        return $this->successResponse($product, ResponseAlias::HTTP_CREATED);
    }

    public function show($product_id){
        $product = Product::findOrFail($product_id);
        return $product;
    }

    public function showProduct($product_id){
        return $this->successResponse($this->show($product_id));
    }

    public function update(Request $request, $product_id){
        $request->validate([
            'price' => 'numeric',
        ]);
        $product = $this->show($product_id);
        if($request->file){
            $path = $request->file('file')->store('images', 'public');
            $image_path = "/storage/$path";
            $request->merge(['image' => $image_path]);
        }
        $product->fill($request->except(['file']));
        if($product->isClean()){
            return $this->errorResponse('At Least One Value Should be different', ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
        $product->save();
        return $this->successResponse($product);
    }

    public function destroy($product_id){
        $product = $this->show($product_id);
        $product->delete();
        return $this->successResponse($product);
    }

    public function filterProducts($page_number, $per_page){
        $sort = \request()->get('sort');
        $order = \request()->get('order');
        if(!empty($sort) && in_array($sort, ['price', 'created_at'])){
            if(!empty($order)){
                return $this->successResponse(Product::orderByDesc($sort)->paginate($per_page, ['*'], '', $page_number));
            }
            return $this->successResponse(Product::orderBy($sort)->paginate($per_page, ['*'], '', $page_number));
        }
        return $this->successResponse(Product::paginate($per_page, ['*'], '', $page_number));


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

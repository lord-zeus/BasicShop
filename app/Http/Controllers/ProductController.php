<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\APIResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductController extends Controller
{
    use APIResponse;

    public function index(): Response
    {
        return $this->successResponse(Product::all());
    }

    public function store(Request $request): Response
    {
        $request->validate([
            'name' => 'required',
            'file' => 'required',
            'price' => 'required|numeric',
            'sku' => 'required',
        ]);
        $path = $request->file('file')->store('images', 'public');
        $image_path = "public/$path";
        $slug = $this->generateSlug($request->name);
        $request->merge(['slug' => $slug, 'image' => $image_path]);
        $product = Product::create($request->except(['file']));

        return $this->successResponse($product, ResponseAlias::HTTP_CREATED);
    }

    public function show($product_id)
    {
        $product = Product::findOrFail($product_id);

        return $product;
    }

    public function showProduct($product_id): Response
    {
        return $this->successResponse($this->show($product_id));
    }

    public function update(Request $request, $product_id): Response|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'price' => 'numeric',
        ]);
        $product = $this->show($product_id);
        if ($request->file) {
            $path = $request->file('file')->store('images', 'public');
            $image_path = "public/$path";
            $request->merge(['image' => $image_path]);
        }
        $product->fill($request->except(['file']));
        if ($product->isClean()) {
            return $this->errorResponse('At Least One Value Should be different', ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
        $product->save();

        return $this->successResponse($product);
    }

    public function destroy($product_id): Response|\Illuminate\Http\JsonResponse
    {
        $product = DB::table('products')->where('id', $product_id);
        if (empty($product->first())) {
            return $this->errorResponse('Product Not Found', ResponseAlias::HTTP_NOT_FOUND);
        }
        $image = $product->first()->image;
        $product->delete();
        Storage::delete($image);

        return $this->successResponse('Product Deleted');

    }

    public function filterProducts($page_number, $per_page): Response
    {
        $sort = \request()->get('sort');
        $order = \request()->get('order');
        if (! empty($sort) && in_array($sort, ['price', 'created_at'])) {
            if (! empty($order)) {
                return $this->successResponse(Product::orderByDesc($sort)->paginate($per_page, ['*'], '', $page_number));
            }

            return $this->successResponse(Product::orderBy($sort)->paginate($per_page, ['*'], '', $page_number));
        }

        return $this->successResponse(Product::paginate($per_page, ['*'], '', $page_number));

    }

    public function generateSlug($name): array|string|null
    {
        $slug = Str::slug($name);
        if (Product::where('slug', Str::slug($name))->exists()) {
            $max = Product::where('name', 'LIKE', $name)->latest()->value('slug');
            if (is_numeric($max[-1])) {
                return preg_replace_callback('/(\d+)$/', function ($mathces) {
                    return $mathces[1] + 1;
                }, $max);
            }

            return "{$slug}-2";

        }

        return $slug;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Traits\APIResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class OrderController extends Controller
{
    use APIResponse;
    public function index(): Response
    {
        $orders =  Order::all();
        return $this->successResponse($orders);
    }

    public function store(Request $request): Response
    {
        $request->validate([
            'phone' => 'required',
            'comment' => 'required',
            'products' => 'required'
        ]);
        if(is_array(($request->products)))
            $finals = $request->products;
        else
            $finals = json_decode($request->products, true);
        $total_amount = 0;
        $order = Order::create($request->except(['products']));
        foreach ($finals as $pd){
            $product = Product::findOrFail($pd['id']);
            $amount = (int)$pd['quantity'] * $product->price;
            $total_amount += $amount;
            DB::table('order_product')->insert([
                'product_id' => $product->id,
                'order_id' => $order->id,
                'quantity' => $pd['quantity'],
                'amount' => $amount
            ]);
        }
        $order->amount = $total_amount;
        $order->save();
        return $this->successResponse($order, ResponseAlias::HTTP_CREATED);
    }

    public function show($order_id){
        $order = Order::with(['orderProducts.product'])->findOrFail($order_id);
        return $order;
    }

    public function showOrder($order_id): Response
    {
        $order = $this->show($order_id);
        return $this->successResponse($order);
    }

    public function filterOrders($page_number, $per_page): Response
    {
        $sort = \request()->get('sort');
        $order = \request()->get('order');
        if(!empty($sort) && in_array($sort, ['amount', 'created_at'])){
            if(!empty($order)){
                return $this->successResponse(Order::orderByDesc($sort)->paginate($per_page, ['*'], '', $page_number));
            }
            return $this->successResponse(Order::orderBy($sort)->paginate($per_page, ['*'], '', $page_number));
        }
        return $this->successResponse(Order::paginate($per_page, ['*'], '', $page_number));


    }

    public function destroy($order_id){
        $order = $this->show($order_id);
        DB::table('order_product')->where('order_id', $order_id)->delete();
        $order->delete();
        return $order;
    }
}

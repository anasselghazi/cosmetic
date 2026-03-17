<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    public function store(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        $order = Order::create([
            'user_id'     => auth()->id(),
            'product_id'  => $request->product_id,
            'quantity'    => $request->quantity,
            'total_price' => $product->price * $request->quantity,
            'status'      => 'en_attente',
        ]);

       
        return response()->json([
        'message' => 'Commande créée avec succès',
        'order' => $order
    ], 201);
    }
}

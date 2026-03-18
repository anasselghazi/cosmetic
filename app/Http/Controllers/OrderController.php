<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Middleware\IsEmployee;



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
            'status'     => $request->status ?? 'en_attente',
            
        ]);

       
        return response()->json([
        'message' => 'Commande créée avec succès',
        'order' => $order
    ], 201);
    }

    public function mesCommandes(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('product')
            ->get()
            ->map(function ($order) {
                return [
                    'id'       => $order->id,
                    'produit'  => $order->product->name,
                    'quantity' => $order->quantity,
                    'status'   => $order->status,
                    'total'    => $order->total_price,
                ];
            });

        return response()->json($orders);
    }

    public function cancel(Request $request, $id)
{
    $order = order::find($id);

    
    if (!$order) {
        return response()->json([
            'message' => 'Commande introuvable'
        ], 404);
    }

    
    if ($order->user_id !== $request->user()->id) {
        return response()->json([
            'message' => 'Action non autorisée'
        ], 403);
    }

    
    if ($order->status !== 'en_attente') {
        return response()->json([
            'message' => 'Impossible d\'annuler une commande ' . $order->status
        ], 400);
    }

    $order->update(['status' => 'annulee']);

    return response()->json([
        'message' => 'Commande annulée avec succès',
        'order'   => $order
    ]);
}   

   public function prepare(Request $request, $id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'message' => 'Commande introuvable'
        ], 404);
    }

    if ($order->status !== 'en_attente') {
        return response()->json([
            'message' => 'Impossible de préparer une commande ' . $order->status
        ], 400);
    }

    $order->update(['status' => 'en_preparation']);

    return response()->json([
        'message' => 'Commande en préparation',
        'order'   => $order
    ]);
}

}

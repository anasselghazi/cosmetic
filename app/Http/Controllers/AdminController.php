<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total'          => Order::count(),
            'en_attente'     => Order::where('status', 'en_attente')->count(),
            'en_preparation' => Order::where('status', 'en_preparation')->count(),
            'confirmee'        => Order::where('status', 'confirmee')->count(),
            'livree'        => Order::where('status', 'livree')->count(),
            'annulee'        => Order::where('status', 'annulee')->count(),
        ];

        $last_orders = Order::with(['user', 'product'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id'       => $order->id,
                    'client'   => $order->user->name,
                    'produit'  => $order->product->name,
                    'quantity' => $order->quantity,
                    'total'    => $order->total_price,
                    'status'   => $order->status,
                    'date'     => $order->created_at->format('d/m/Y'),
                ];
            });

        return response()->json([
            'stats'       => $stats,
            'last_orders' => $last_orders,
        ]);
    }
}

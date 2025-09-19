<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /**
     * Поиск клиента по номеру телефона
     * GET /api/customers?phone=...
     */
    public function searchByPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20'
        ]);

        $phone = $request->input('phone');
        
        //заказы по номеру телефона
        $orders = Order::where('phone', 'like', '%' . $phone . '%')
            ->orderBy('order_date', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'Клиент с таким номером телефона не найден',
                'customer' => null,
                'orders' => []
            ], 404);
        }

        $lastOrder = $orders->first();
        
        $customer = [
            'full_name' => $lastOrder->full_name,
            'phone' => $lastOrder->phone,
            'email' => $lastOrder->email,
            'inn' => $lastOrder->inn,
            'company_name' => $lastOrder->company_name,
            'address' => $lastOrder->address
        ];

        return response()->json([
            'customer' => $customer,
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_date' => Carbon::parse($order->order_date)->format('d.m.Y'),
                    'status' => $order->status,
                    'total_amount' => $order->products->sum(function ($product) {
                        return $product->pivot->quantity * $product->pivot->price;
                    })
                ];
            })
        ]);
    }
}
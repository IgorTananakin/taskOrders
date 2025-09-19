<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function searchByPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20'
        ]);

        $phone = $request->input('phone');
        
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
            'last_name' => $lastOrder->last_name,
            'first_name' => $lastOrder->first_name,
            'middle_name' => $lastOrder->middle_name,
            'full_name' => trim($lastOrder->last_name . ' ' . $lastOrder->first_name . ' ' . $lastOrder->middle_name),
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
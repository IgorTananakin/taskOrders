<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'inn' => 'required|string|max:12|min:10',
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'products' => 'required|array|min:1',
            'products.*.name' => 'required_with:products.*.quantity|string|max:255',
            'products.*.quantity' => 'required_with:products.*.name|integer|min:1|nullable',
            'products.*.unit' => 'required_with:products.*.name|string|max:20|nullable',
            'products.*.price' => 'required_with:products.*.name|numeric|min:0|nullable'
        ]);

        return DB::transaction(function () use ($validated) {

            $order = Order::create([
                'user_id' => Auth::id(),
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'inn' => $validated['inn'],
                'company_name' => $validated['company_name'],
                'address' => $validated['address'],
                'status' => 'new',
                'order_date' => now(),
            ]);

            // Добавляем товары к заказу
            foreach ($validated['products'] as $productData) {
                //пропуск пустых
                if (empty($productData['name']) || empty($productData['quantity'])) {
                    continue;
                }

                $product = Product::firstOrCreate(
                    ['name' => trim($productData['name'])],
                    ['unit' => $productData['unit'] ?? 'шт.']
                );

                $order->products()->attach($product->id, [
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($order->products()->count() === 0) {
                throw new \Exception('Необходимо добавить хотя бы один товар');
            }

            return redirect()->route('orders.create')
                ->with('success', 'Заказ №' . $order->id . ' успешно создан!');
        });
    }
}
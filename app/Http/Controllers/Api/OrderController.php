<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrderController extends Controller
{
    
    /**
     * получение списка заказов с фильтрацией по статусу
     * GET /api/orders?date=...&status=...
     */
    public function index(Request $request)
    {
        $query = Order::with(['products', 'user'])
            ->orderBy('order_date', 'desc');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        //по дате
        if ($request->has('date') && $request->date) {
            $query->whereDate('order_date', $request->date);
        }

        //по дате от
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        //по дате до
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        //поиск
        if ($request->has('search') && $request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('full_name', 'like', $searchTerm)
                  ->orWhere('phone', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhere('inn', 'like', $searchTerm)
                  ->orWhere('company_name', 'like', $searchTerm)
                  ->orWhere('address', 'like', $searchTerm)
                  ->orWhereHas('products', function($q) use ($searchTerm) {
                      $q->where('name', 'like', $searchTerm);
                  });
            });
        }

        $orders = $query->paginate($request->per_page ?? 10);

        //форматируем даты
        $formattedOrders = collect($orders->items())->map(function ($order) {
            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'full_name' => $order->full_name,
                'phone' => $order->phone,
                'email' => $order->email,
                'inn' => $order->inn,
                'company_name' => $order->company_name,
                'address' => $order->address,
                'status' => $order->status,
                'order_date' => Carbon::parse($order->order_date)->format('d.m.Y'),
                'created_at' => Carbon::parse($order->created_at)->format('d.m.Y H:i'),
                'updated_at' => Carbon::parse($order->updated_at)->format('d.m.Y H:i'),
                'products' => $order->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'unit' => $product->unit,
                        'quantity' => $product->pivot->quantity,
                        'price' => $product->pivot->price,
                        'total' => $product->pivot->quantity * $product->pivot->price
                    ];
                }),
                'user' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email
                ] : null
            ];
        });

        return response()->json([
            'orders' => $formattedOrders,
            'total' => $orders->total(),
            'current_page' => $orders->currentPage(),
            'per_page' => $orders->perPage(),
            'last_page' => $orders->lastPage(),
        ]);
    }

    /**
     * Создание нового заказа
     * POST /api/orders
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {

                $order = Order::create([
                    'user_id' => auth()->id() ?? null,
                    'full_name' => $request->full_name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'inn' => $request->inn,
                    'company_name' => $request->company_name,
                    'address' => $request->address,
                    'status' => 'new',
                    'order_date' => now(),
                ]);

                foreach ($request->products as $productData) {

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

                //загружаем данные заказа с товарами
                $order->load('products');

                $formattedOrder = [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'full_name' => $order->full_name,
                    'phone' => $order->phone,
                    'email' => $order->email,
                    'inn' => $order->inn,
                    'company_name' => $order->company_name,
                    'address' => $order->address,
                    'status' => $order->status,
                    'order_date' => Carbon::parse($order->order_date)->format('d.m.Y'),
                    'created_at' => Carbon::parse($order->created_at)->format('d.m.Y H:i'),
                    'updated_at' => Carbon::parse($order->updated_at)->format('d.m.Y H:i'),
                    'products' => $order->products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'unit' => $product->unit,
                            'quantity' => $product->pivot->quantity,
                            'price' => $product->pivot->price,
                            'total' => $product->pivot->quantity * $product->pivot->price
                        ];
                    })
                ];

                return response()->json([
                    'message' => 'Заказ успешно создан',
                    'order' => $formattedOrder,
                    'order_id' => $order->id
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при создании заказа',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
    * получение статистики по заказам
    * GET /api/orders/statistics
    */
    public function statistics(Request $request)
    {
        $query = Order::query();

        // Применяем фильтры, если они переданы
        if ($request->has('search') && $request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('full_name', 'like', $searchTerm)
                ->orWhere('phone', 'like', $searchTerm)
                ->orWhere('email', 'like', $searchTerm)
                ->orWhere('inn', 'like', $searchTerm)
                ->orWhere('company_name', 'like', $searchTerm)
                ->orWhere('address', 'like', $searchTerm)
                ->orWhereHas('products', function($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm);
                });
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        //по дате от
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        //по дате до
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        //по конкретной дате
        if ($request->has('date') && $request->date) {
            $query->whereDate('order_date', $request->date);
        }

        //получаем статистику
        $total = $query->count();
        $new = $query->clone()->where('status', 'new')->count();
        $inProgress = $query->clone()->where('status', 'in_progress')->count();
        $completed = $query->clone()->where('status', 'completed')->count();

        return response()->json([
            'total' => $total,
            'new' => $new,
            'in_progress' => $inProgress,
            'completed' => $completed
        ]);
    }

}